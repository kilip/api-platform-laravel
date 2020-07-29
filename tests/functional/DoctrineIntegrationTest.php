<?php

namespace Tests\ApiPlatformLaravel\Functional;

use ApiPlatformLaravel\Testing\InteractsWithORM;
use Tests\Dummy\Model\User;

class DoctrineIntegrationTest extends TestCase
{
    use InteractsWithORM;

    public function testBoot()
    {
        /* @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
        $this->refreshDatabase();

        $this->assertNotNull($this->getRegistry()->getManagerForClass(User::class));
    }
}
