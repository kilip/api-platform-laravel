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

namespace ApiPlatformLaravel\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FilterServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->removeDefinition('api_platform.swagger.action.ui');

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
    }
}
