<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\System\Routing;

use Opillion\EasyStack\NtfyBundle\System\AppDependencyMap;
use Symfony\Component\Routing\RouteCollection;

final class RouteProvider
{
    public function getRouteResources(): iterable
    {
        yield [
            'resource' => dirname(__DIR__, 2) . '/Resources/config/routes.yaml',
            'type' => 'yaml',
        ];
    }

    public function filterRoutes(RouteCollection $routes): void
    {
        foreach (AppDependencyMap::ROUTE_REQUIREMENTS as $routeName => $routeEnvRequirements) {
            if (!$this->hasRequiredEnv($routeEnvRequirements)) {
                $routes->remove($routeName);
            }
        }
    }

    /**
     * @param list<string> $envRequirements
     */
    private function hasRequiredEnv(array $envRequirements): bool
    {
        foreach ($envRequirements as $envName) {
            if (getenv($envName) === false || getenv($envName) === '') {
                return false;
            }
        }

        return true;
    }
}
