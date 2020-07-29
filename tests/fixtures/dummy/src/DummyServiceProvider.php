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

namespace Tests\Dummy;

use ApiPlatformLaravel\Facades\Api;
use Illuminate\Support\ServiceProvider;

use Tests\Dummy\Model\Group;
use Tests\Parent\Model\GroupInterface;
use Tests\Parent\Model\UserInterface as AbstractUser;
use Tests\Dummy\Model\User as ConcreteUser;

class DummyServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        Api::registerAnnotationMapping(__NAMESPACE__.'\\Model', __DIR__.'/Model');
        Api::resolveTargetEntities(AbstractUser::class, ConcreteUser::class);
        Api::resolveTargetEntities(GroupInterface::class,Group::class);
    }
}
