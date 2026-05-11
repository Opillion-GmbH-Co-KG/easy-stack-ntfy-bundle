<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Tests\Unit\Command;

use Opillion\EasyStack\NtfyBundle\Command\NtfySendCommand;
use Opillion\EasyStack\NtfyBundle\DataProvider\Ntfy\Storage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Exception\ExceptionInterface;
use PHPUnit\Framework\MockObject\Exception;

final class NtfySendCommandTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testReturnsFailureWhenTopicMissing(): void
    {
        putenv('NTFY_TOPIC');

        $ntfy = $this->createMock(Storage::class);
        $command = new NtfySendCommand($ntfy);
        $tester = new CommandTester($command);

        $tester->execute(['message' => 'hello']);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('NTFY_TOPIC is required', $tester->getDisplay());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testSendsMessageOnConfiguredTopic(): void
    {
        putenv('NTFY_TOPIC=chat-topic');

        $ntfy = $this->createMock(Storage::class);
        $ntfy
            ->expects($this->once())
            ->method('publish')
            ->with('chat-topic', 'hello');

        $command = new NtfySendCommand($ntfy);
        $tester = new CommandTester($command);

        $tester->execute(['message' => 'hello']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('Message sent', $tester->getDisplay());
    }
}
