<?php


namespace Tests\ApiPlatformLaravel\Functional;


use ApiPlatformLaravel\ApiPlatformServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Tests\Dummy\DummyServiceProvider;
use Tests\Parent\ParentServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ApiPlatformServiceProvider::class,
            ParentServiceProvider::class,
            DummyServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }
}