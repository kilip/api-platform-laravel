<?php


namespace ApiPlatformLaravel\Facades;


use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Illuminate\Support\Facades\Facade;

/**
 * Class Configurator
 *
 * @method static DoctrineOrmMappingsPass registerXmlMapping($namespace, array|string $paths, string $managerName=null)
 * @method static DoctrineOrmMappingsPass registerAnnotationMapping($namespace, array|string $paths, string $managerName=null)
 * @method static DoctrineOrmMappingsPass registerYamlMapping($namespace, array|string $paths, string $managerName=null)
 * @method static DoctrineOrmMappingsPass[] getOrmCompilersPass()
 * @package ApiPlatformLaravel\Facades
 */
class Api extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'api';
    }
}