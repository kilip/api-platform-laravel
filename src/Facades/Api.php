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

namespace ApiPlatformLaravel\Facades;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Illuminate\Support\Facades\Facade;

/**
 * Class Configurator.
 *
 * @method static void resolveTargetEntities(string $abstractClass, string $concreteClass)
 * @method static DoctrineOrmMappingsPass registerXmlMapping($namespace, array|string $paths, string $managerName=null)
 * @method static DoctrineOrmMappingsPass registerAnnotationMapping($namespace, array|string $paths, string $managerName=null)
 * @method static DoctrineOrmMappingsPass registerYamlMapping($namespace, array|string $paths, string $managerName=null)
 * @method static DoctrineOrmMappingsPass[] getOrmCompilersPass()
 */
class Api extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'api';
    }
}
