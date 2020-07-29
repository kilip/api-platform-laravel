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

use ApiPlatformLaravel\Exception\InvalidArgumentException;
use ApiPlatformLaravel\Helper\ApiHelper;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\Auth\DoctrineUserProvider;

class ApiPlatformServiceProvider extends ServiceProvider
{
    public function boot(Application $app)
    {
        if (!$app->eventsAreCached()) {
            $this->registerEvents($app);
        }

        $this->registerServices($app);
        $this->extendAuthManager($app);

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

    private function registerServices(Application $app)
    {
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
    }

    private function extendAuthManager(Application $app)
    {
        if($app->bound('auth')){
            /* @var \Illuminate\Foundation\Application $app */
            /* @var \Illuminate\Config\Repository $config */
            $app->make('auth')->provider('doctrine',function($app, $config){
                $model = $config['model'];
                $em = app('registry')->getManagerForClass($model);
                if(!$em){
                    throw new InvalidArgumentException("Can't get entity manager for ${model}");
                }

                return new DoctrineUserProvider(
                    $app['hash'],
                    $em,
                    $model
                );
            });
        }
    }
}
