<?php


namespace Tests\Dummy\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DummyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    }
}