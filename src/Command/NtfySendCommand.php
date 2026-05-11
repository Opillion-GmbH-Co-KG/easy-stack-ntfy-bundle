<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Command;

use Opillion\EasyStack\NtfyBundle\DataProvider\Ntfy\Storage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'ntfy:send',
    description: 'Sends a message to the configured ntfy topic.'
)]
final class NtfySendCommand extends Command
{
    public function __construct(
        private readonly Storage $ntfy
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('message', InputArgument::REQUIRED, 'Message payload.');
        $this->addOption(
            'topic',
            't',
            InputOption::VALUE_OPTIONAL,
            'NTFY topic (defaults to NTFY_TOPIC).'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $message = (string) $input->getArgument('message');
        $topic = (string) $input->getOption('topic');
        $topic = $topic === '' ? (string) getenv('NTFY_TOPIC') : $topic;

        if ($topic === '') {
            $io->error('NTFY_TOPIC is required to send messages.');

            return Command::FAILURE;
        }

        try {
            $this->ntfy->publish($topic, $message);
            $io->success(sprintf('Message sent to ntfy topic "%s".', $topic));

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->error('Unable to send ntfy message: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}

