<?php


namespace ApiPlatformLaravel;

use ApiPlatformLaravel\DependencyInjection\Compiler\FilterServicePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LaravelBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FilterServicePass());
    }
}