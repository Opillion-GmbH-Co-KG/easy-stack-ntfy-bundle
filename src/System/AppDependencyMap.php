<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\System;

final class AppDependencyMap
{
    public const array ROUTE_REQUIREMENTS = [
        'easy_stack_ntfy_chat' => [
            'NTFY_HOST',
            'NTFY_CHAT',
            'NTFY_TOKEN',
            'NTFY_ZIP_PROTECTION',
        ],
    ];

    public const array MENU_ENV_REQUIREMENTS = [
        'NTFY_HOST',
        'NTFY_CHAT',
        'NTFY_TOKEN',
        'NTFY_ZIP_PROTECTION',
    ];

    /**
     * @param list<string> $envNames
     */
    public static function routeRequirementsAvailable(string $routeName): bool
    {
        return self::hasEnvVariables(self::ROUTE_REQUIREMENTS[$routeName] ?? []);
    }

    public static function menuRequirementsAvailable(): bool
    {
        return self::hasEnvVariables(self::MENU_ENV_REQUIREMENTS);
    }

    /**
     * @param list<string> $envNames
     */
    private static function hasEnvVariables(array $envNames): bool
    {
        foreach ($envNames as $envName) {
            if (getenv($envName) === false || getenv($envName) === '') {
                return false;
            }
        }

        return true;
    }
}
