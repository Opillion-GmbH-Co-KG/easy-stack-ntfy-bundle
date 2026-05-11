<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\DataProvider\Ntfy;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Opillion\EasyStack\NtfyBundle\DataProvider\StorageException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Throwable;
use function http_build_query;
use function is_string;
use function json_decode;
use function preg_replace;
use function sprintf;
use function ltrim;

class Storage
{
    public function __construct(
        private readonly ?string $ntfyHost,
        private readonly ?string $ntfyToken,
        private ?ClientInterface $client = null,
        private readonly int $timeout = 30
    ) {
        $this->client ??= new Client([
            'base_uri' => rtrim($this->ntfyHost ?? '', '/') . '/',
            'timeout' => $this->timeout,
        ]);
    }

    /**
     * @throws StorageException
     */
    public function publish(string $topic, string $message, array $headers = []): void
    {
        try {
            $mergedHeaders = $headers + ['Content-Type' => 'text/plain'];

            $this->request(
                Request::METHOD_POST,
                $topic,
                [
                    'headers' => $this->withAuth($mergedHeaders),
                    'body' => $message,
                ]
            );
        } catch (Throwable $e) {
            throw new StorageException($e);
        }
    }

    /**
     * @throws StorageException
     */
    public function subscribe(string $topic, callable $handler, array $query = []): void
    {
        try {
            $path = ltrim($topic, '/') . '/sse';
            if ($query !== []) {
                $path .= '?' . http_build_query($query);
            }

            $response = $this->client->request(Request::METHOD_GET, $path, [
                'headers' => $this->withAuth(['Accept' => 'text/event-stream']),
                'stream' => true,
                'read_timeout' => $this->timeout,
            ]);

            $this->streamSse($response, $handler);
        } catch (Throwable $e) {
            throw new StorageException($e);
        }
    }

    /**
     * @throws StorageException
     */
    public function upload(
        string $topic,
        string $filePath,
        ?string $message = null,
        array $headers = []
    ): void {
        try {
            $stream = fopen($filePath, 'r');
            if ($stream === false) {
                throw new StorageException(new \RuntimeException(sprintf('Unable to open file: %s', $filePath)));
            }

            $filename = basename($filePath);
            $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

            $payloadHeaders = $headers + [
                'Filename' => $filename,
                'Title' => $message ?? 'Upload',
                'Tags' => 'file',
                'Content-Type' => $mimeType,
            ];

            $this->client->request(
                Request::METHOD_PUT,
                ltrim($topic, '/'),
                [
                    'headers' => $this->withAuth($payloadHeaders),
                    'body' => $stream,
                ]
            );
        } catch (Throwable $e) {
            throw new StorageException($e);
        } finally {
            if (isset($stream) && is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function withAuth(array $headers): array
    {
        if ($this->ntfyToken !== null && $this->ntfyToken !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->ntfyToken;
        }

        return $headers;
    }

    /**
     * @throws StorageException
     */
    private function request(string $method, string $topic, array $options = []): void
    {
        try {
            if ($this->ntfyHost === null || $this->ntfyHost === '') {
                throw new RuntimeException('NTFY_HOST is required for ntfy requests.');
            }

            $this->client->request($method, ltrim($topic, '/'), $options);
        } catch (GuzzleException $e) {
            throw new StorageException($e);
        }
    }

    private function streamSse(ResponseInterface $response, callable $handler): void
    {
        $body = $response->getBody();
        $buffer = '';
        $separator = "\n\n";

        while (!$body->eof()) {
            $buffer .= $body->read(4096);

            while (($position = strpos($buffer, $separator)) !== false) {
                $chunk = substr($buffer, 0, $position);
                $buffer = substr($buffer, $position + strlen($separator));

                foreach (\explode("\n", $chunk) as $line) {
                    if (!str_starts_with($line, 'data:')) {
                        continue;
                    }

                    $json = trim(substr($line, 5));
                    if ($json === '') {
                        continue;
                    }

                    $payload = $this->sanitizePayload(json_decode($json, true));
                    $handler($payload);
                }
            }
        }
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array<string, mixed>
     */
    private function sanitizePayload(?array $payload): array
    {
        if ($payload === null) {
            return [];
        }

        array_walk_recursive($payload, static function (mixed &$value): void {
            if (is_string($value)) {
                $value = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $value) ?? $value;
            }
        });

        return $payload;
    }
}
