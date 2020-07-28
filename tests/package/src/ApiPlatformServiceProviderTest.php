<?php

namespace Tests\ApiPlatformLaravel\Functional;

use ApiPlatformLaravel\ApiPlatformServiceProvider;
use Doctrine\Persistence\ManagerRegistry;
use Tests\ApiPlatformLaravel\Functional\TestCase;

class ApiPlatformServiceProviderTest extends TestCase
{
    public function testBoot()
    {
        $this->assertIsObject(app()->make('ApiPlatformContainer'));

        /* @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
        $container = app()->make('ApiPlatformContainer');
        $container->get('doctrine');

    }
}
