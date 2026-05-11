<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Tests\Unit\System\Routing;

use Opillion\EasyStack\NtfyBundle\System\Routing\RouteProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteProviderTest extends TestCase
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

    public function testFiltersNtfyRouteWhenEnvironmentMissing(): void
    {
        $this->restoreEnv('NTFY_CHAT', null);
        $this->restoreEnv('NTFY_TOKEN', null);
        $this->restoreEnv('NTFY_ZIP_PROTECTION', null);
        $this->restoreEnv('NTFY_HOST', null);

        $routes = new RouteCollection();
        $routes->add('easy_stack_ntfy_chat', new Route('/chat'));
        $routes->add('other', new Route('/other'));

        (new RouteProvider())->filterRoutes($routes);

        self::assertNull($routes->get('easy_stack_ntfy_chat'));
        self::assertInstanceOf(Route::class, $routes->get('other'));
    }

    public function testKeepsNtfyRouteWhenEnvironmentPresent(): void
    {
        $this->setEnv('NTFY_HOST', 'https://ntfy.sh');
        $this->setEnv('NTFY_CHAT', 'topic');
        $this->setEnv('NTFY_TOKEN', 'token');
        $this->setEnv('NTFY_ZIP_PROTECTION', 'secret');

        $routes = new RouteCollection();
        $routes->add('easy_stack_ntfy_chat', new Route('/chat'));

        (new RouteProvider())->filterRoutes($routes);

        self::assertInstanceOf(Route::class, $routes->get('easy_stack_ntfy_chat'));
    }

    private function setEnv(string $name, string $value): void
    {
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
    }

    private function restoreEnv(string $name, ?string $value): void
    {
        if ($value === null) {
            putenv($name);
            unset($_ENV[$name]);

            return;
        }

        $this->setEnv($name, $value);
    }

    private function envOrNull(string $name): ?string
    {
        $value = getenv($name);

        return $value === false ? null : $value;
    }
}
