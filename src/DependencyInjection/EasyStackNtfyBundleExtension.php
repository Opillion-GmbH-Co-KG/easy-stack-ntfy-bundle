<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\DependencyInjection;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EasyStackNtfyBundleExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('twig')) {
            $container->prependExtensionConfig('twig', [
                'paths' => [
                    __DIR__ . '/../Resources/views' => 'EasyStackNtfyBundle',
                ],
            ]);
        }

        if ($container->hasExtension('framework')) {
            $container->prependExtensionConfig('framework', [
                'translator' => [
                    'paths' => [
                        __DIR__ . '/../Resources/translations',
                    ],
                ],
            ]);
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if (class_exists(Fixture::class)) {
            $loader->load('fixtures.yaml');
        }
    }

    public function getAlias(): string
    {
        return 'easy_stack_ntfy';
    }
}
