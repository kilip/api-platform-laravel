<?php


namespace Tests\ApiPlatformLaravel\Functional;


use ApiPlatformLaravel\ApiPlatformServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ApiPlatformServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }
}