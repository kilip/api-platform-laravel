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

namespace Tests\Parent;

use ApiPlatformLaravel\Facades\Api;
use Illuminate\Support\ServiceProvider;

class ParentServiceProvider extends ServiceProvider
{
    public function register()
    {
        Api::registerAnnotationMapping(__NAMESPACE__.'\\Model', __DIR__.'/Model');
    }
}
