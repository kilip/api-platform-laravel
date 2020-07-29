<?php


namespace ApiPlatformLaravel\DependencyInjection;



use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class LaravelExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        //$container->setParameter('kernel.secret', getenv('APP_KEY'));

        $container->setParameter('use_cache','false');
        $container->setParameter('doctrine.orm.auto_generate_proxy_classes',true);
    }
}