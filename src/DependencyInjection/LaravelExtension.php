<?php

/*
 * This file is part of the Api Platform Laravel project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiPlatformLaravel\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class LaravelExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $resolved = $container->getParameter('laravel.orm.resolve_target_entities');
        if (!\is_array($resolved)) {
            $resolved = [];
        }
        $definition = $container->findDefinition('laravel.orm.listeners.resolve_target_entity');
        foreach ($resolved as $abstract => $concrete) {
            $definition->addMethodCall('addResolveTargetEntity', [
                $abstract,
                $concrete,
                [],
            ]);
        }
        $definition->addTag('doctrine.event_subscriber');

        $dirs = $container->getParameter('api_platform.resource_class_directories');
        $dirs = array_merge($dirs, [
            __DIR__.'/../../tests/fixtures/dummy/src/Model',
            __DIR__.'/../../tests/fixtures/parent/src/Model',
        ]);
        $container->setParameter('api_platform.resource_class_directories', $dirs);
    }
}
