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
            DummyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }
}
