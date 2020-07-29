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

namespace ApiPlatformLaravel;

use ApiPlatformLaravel\Helper\ApiHelper;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ApiPlatformServiceProvider extends ServiceProvider
{
    public function boot(Application $app)
    {
        if (!$app->eventsAreCached()) {
            $this->registerEvents($app);
        }
        $app->singleton(Kernel::class, function (Application $app) {
            $laravelKernel = $app->make(KernelContract::class);

            return new Kernel($laravelKernel);
        });

        $app->singleton('ApiPlatformContainer', function (Application $app) {
            return $app->make(Kernel::class)->getContainer();
        });

        $app->singleton('registry', function (Application $app) {
            return $app->make('ApiPlatformContainer')->get('doctrine');
        });
        $app->alias('registry', ManagerRegistry::class);

        $app->booted([$this, 'afterBoot']);
    }

    public function register()
    {
        $this->app->singleton('api', function () {
            return new ApiHelper();
        });
        $this->app->alias('api', ApiHelper::class);
    }

    public function afterBoot(Application $app)
    {
        $app->make(Kernel::class)->boot();
    }

    public static function publishableProviders()
    {
        return [
            'api',
            'ApiPlatformContainer',
        ];
    }

    private function registerEvents(Application $app)
    {
    }
}
