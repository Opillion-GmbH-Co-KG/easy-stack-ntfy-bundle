<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Tests\Unit\System\EasyAdmin;

use Opillion\EasyStack\NtfyBundle\System\EasyAdmin\AdminMenuProvider;
use PHPUnit\Framework\TestCase;

final class AdminMenuProviderTest extends TestCase
{
    private ?string $chatEnv = null;
    private ?string $tokenEnv = null;
    private ?string $protectionEnv = null;
    private ?string $hostEnv = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chatEnv = $this->envOrNull('NTFY_CHAT');
        $this->tokenEnv = $this->envOrNull('NTFY_TOKEN');
        $this->protectionEnv = $this->envOrNull('NTFY_ZIP_PROTECTION');
        $this->hostEnv = $this->envOrNull('NTFY_HOST');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->restoreEnv('NTFY_CHAT', $this->chatEnv);
        $this->restoreEnv('NTFY_TOKEN', $this->tokenEnv);
        $this->restoreEnv('NTFY_ZIP_PROTECTION', $this->protectionEnv);
        $this->restoreEnv('NTFY_HOST', $this->hostEnv);
    }

    public function testHidesMenuWhenNtfyEnvMissing(): void
    {
        $this->restoreEnv('NTFY_CHAT', null);
        $this->restoreEnv('NTFY_TOKEN', null);
        $this->restoreEnv('NTFY_ZIP_PROTECTION', null);
        $this->restoreEnv('NTFY_HOST', null);

        $provider = new AdminMenuProvider();

        self::assertSame(
            [],
            iterator_to_array($provider->getMenuItems('root.after_library'))
        );
    }

    public function testShowsMenuWhenNtfyEnvPresent(): void
    {
        $this->setEnv('NTFY_CHAT', 'chat/topic');
        $this->setEnv('NTFY_HOST', 'https://ntfy.sh');
        $this->setEnv('NTFY_TOKEN', 'abc');
        $this->setEnv('NTFY_ZIP_PROTECTION', 'secret');

        $provider = new AdminMenuProvider();
        $items = iterator_to_array($provider->getMenuItems('root.after_library'));

        self::assertCount(2, $items);
        self::assertSame('Ntfy', $items[0]->getAsDto()->getLabel());
        self::assertSame('Chat', $items[1]->getAsDto()->getLabel());
    }

    public function testIgnoresOtherExtensionPoints(): void
    {
        $provider = new AdminMenuProvider();
        self::assertSame([], iterator_to_array($provider->getMenuItems('media')));
    }

    private function setEnv(string $name, string $value): void
    {
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
    }

    private function envOrNull(string $name): ?string
    {
        $value = getenv($name);

        return $value === false ? null : $value;
    }

    private function restoreEnv(string $name, ?string $value): void
    {
        if ($value === null || $value === false) {
            putenv($name);
            unset($_ENV[$name]);

            return;
        }

        $this->setEnv($name, $value);
    }
}
