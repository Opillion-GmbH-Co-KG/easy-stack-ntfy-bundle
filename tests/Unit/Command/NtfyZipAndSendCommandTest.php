<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Tests\Unit\Command;

use Opillion\EasyStack\NtfyBundle\Command\NtfyZipAndSendCommand;
use Opillion\EasyStack\NtfyBundle\DataProvider\Ntfy\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

final class NtfyZipAndSendCommandTest extends TestCase
{
    public function testReturnsFailureWhenSourceFileIsMissing(): void
    {
        $storage = $this->createStub(Storage::class);
        $command = new NtfyZipAndSendCommand($storage);
        $tester = new CommandTester($command);

        $tester->execute(['file' => '/tmp/does-not-exist']);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('Source file not found', $tester->getDisplay());
    }

    public function testUploadsArchiveForExistingFile(): void
    {
        $sourceFile = tempnam(sys_get_temp_dir(), 'ntfy-test-source-');
        self::assertNotFalse($sourceFile);
        file_put_contents($sourceFile, 'data');

        $storage = $this->createMock(Storage::class);
        $storage
            ->expects($this->once())
            ->method('upload')
            ->with(
                'dumps',
                $this->callback(function (string $zipPath): bool {
                    return is_file($zipPath);
                }),
                'Zip archive sent',
                $this->callback(static function (array $headers): bool {
                    return isset($headers['Filename'], $headers['Tags']) && $headers['Tags'] === 'zip,ntfy';
                })
            );

        $command = new NtfyZipAndSendCommand($storage);
        $tester = new CommandTester($command);

        $tester->execute([
            'file' => $sourceFile,
            'topic' => 'dumps',
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('ZIP archive delivered.', $tester->getDisplay());

        unlink($sourceFile);
    }
}
