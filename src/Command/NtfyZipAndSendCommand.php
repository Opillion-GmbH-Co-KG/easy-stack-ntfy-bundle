<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Command;

use Opillion\EasyStack\NtfyBundle\DataProvider\Ntfy\Storage;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;
use Throwable;
use ZipArchive;

#[AsCommand(
    name: 'ntfy:zip-and-send',
    description: 'Zip a file and send it via ntfy.'
)]
final class NtfyZipAndSendCommand extends Command
{
    private const string DefaultTopic = 'dumps';

    public function __construct(
        private readonly Storage $ntfy
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'File path to zip and send.')
            ->addArgument('topic', InputArgument::OPTIONAL, 'Target ntfy topic', self::DefaultTopic)
            ->addOption('title', null, InputOption::VALUE_OPTIONAL, 'Message title', 'Zip archive sent');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sourceFile = (string) $input->getArgument('file');
        $topic = (string) $input->getArgument('topic');
        $title = (string) $input->getOption('title');
        $topic = $topic === '' ? self::DefaultTopic : $topic;
        $zipPass = getenv('NTFY_ZIP_PROTECTION');

        if (!is_file($sourceFile)) {
            $io->error(sprintf('Source file not found: %s', $sourceFile));
            return Command::FAILURE;
        }

        try {
            $zipPath = $this->createArchive($sourceFile, $zipPass === false ? null : (string) $zipPass);
            $this->ntfy->upload(
                $topic,
                $zipPath,
                $title,
                ['Filename' => basename($zipPath), 'Tags' => 'zip,ntfy']
            );
            $io->success('ZIP archive delivered.');
            @unlink($zipPath);

            return Command::SUCCESS;
        } catch (Throwable $exception) {
            $io->error('Failed to zip and send: ' . $exception->getMessage());
            return Command::FAILURE;
        }
    }

    private function createArchive(string $sourceFile, ?string $password): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'ntfy-zip-');
        if ($zipPath === false) {
            throw new RuntimeException('Could not create temporary archive file.');
        }

        $zipPath .= '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create ZIP archive.');
        }

        if (!$zip->addFile($sourceFile, basename($sourceFile))) {
            $zip->close();
            throw new RuntimeException('Could not add file to ZIP archive.');
        }

        if ($password !== null && $password !== '') {
            if (method_exists($zip, 'setPassword') && method_exists($zip, 'setEncryptionName')) {
                $zip->setPassword($password);
                $zip->setEncryptionName(basename($sourceFile), ZipArchive::EM_AES_256, $password);
            }
        }

        $zip->close();

        return $zipPath;
    }
}

