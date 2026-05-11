<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Command;

use Opillion\EasyStack\NtfyBundle\DataProvider\Ntfy\Storage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'ntfy:sync',
    description: 'Fetches ntfy messages and prints received payloads.'
)]
final class NtfySyncCommand extends Command
{
    public function __construct(
        private readonly Storage $ntfy
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $topics = array_filter([
            getenv('NTFY_TOPIC') ?: null,
            getenv('NTFY_TOPIC_WEB') ?: null,
            getenv('NTFY_CHAT') ?: null,
        ]);

        if (count($topics) === 0) {
            $io->error('No NTFY topic env var set.');
            return Command::FAILURE;
        }

        $io->title('Syncing ntfy messages');
        $io->text('Topics: ' . implode(', ', $topics));

        $received = 0;
        foreach ($topics as $topic) {
            $payloads = [];

            try {
                $this->ntfy->subscribe(
                    $topic,
                    static function (array $message) use (&$payloads): void {
                        $payloads[] = $message;
                    },
                    ['since' => 'all', 'poll' => 1]
                );
            } catch (Throwable $exception) {
                $io->error(sprintf('Sync failed for topic "%s": %s', $topic, $exception->getMessage()));
                return Command::FAILURE;
            }

            $io->text(sprintf('Received %d messages for topic "%s"', count($payloads), $topic));
            $received += count($payloads);
        }

        $io->success(sprintf('Sync complete. Received total: %d', $received));

        return Command::SUCCESS;
    }
}
