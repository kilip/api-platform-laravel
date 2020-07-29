<?php


namespace ApiPlatformLaravel;

use ApiPlatformLaravel\DependencyInjection\Compiler\FilterServicePass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LaravelBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FilterServicePass());

        $container->addCompilerPass(DoctrineOrmMappingsPass::createAnnotationMappingDriver(
            ['Tests\\Dummy\\Model'],
            [__DIR__.'/../tests/package/src/Model']
        ));
    }
}