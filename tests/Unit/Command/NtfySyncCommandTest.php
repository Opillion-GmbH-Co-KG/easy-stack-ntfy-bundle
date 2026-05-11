<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Tests\Unit\Command;

use Opillion\EasyStack\NtfyBundle\Command\NtfySyncCommand;
use Opillion\EasyStack\NtfyBundle\DataProvider\Ntfy\Storage;
use Symfony\Component\Console\Command\Command;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\ExceptionInterface;

final class NtfySyncCommandTest extends TestCase
{
    public function testReturnsFailureWhenEnvironmentMissing(): void
    {
        putenv('NTFY_CHAT');
        putenv('NTFY_TOPIC');
        putenv('NTFY_TOPIC_WEB');

        $storage = $this->createMock(Storage::class);
        $command = new NtfySyncCommand($storage);
        $tester = new CommandTester($command);

        $status = $tester->execute([]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('No NTFY topic env var set.', $tester->getDisplay());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testSyncCommandPublishesFromConfiguredTopics(): void
    {
        putenv('NTFY_CHAT=chat-topic');
        putenv('NTFY_TOPIC=');
        putenv('NTFY_TOPIC_WEB=');

        $storage = $this->createMock(Storage::class);
        $storage
            ->expects($this->once())
            ->method('subscribe')
            ->with(
                $this->equalTo('chat-topic'),
                $this->isType('callable'),
                $this->equalTo(['since' => 'all', 'poll' => 1])
            )
            ->willReturnCallback(function (string $_topic, callable $handler): void {
                $handler(['message' => 'x']);
            });

        $command = new NtfySyncCommand($storage);
        $tester = new CommandTester($command);

        $status = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('Sync complete', $tester->getDisplay());
    }
}
