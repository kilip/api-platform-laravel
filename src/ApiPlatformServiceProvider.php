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

use ApiPlatformLaravel\Bridge\UrlGenerator;
use ApiPlatformLaravel\Event\ApplicationEvent;
use ApiPlatformLaravel\Exception\InvalidArgumentException;
use ApiPlatformLaravel\Helper\ApiHelper;
use ApiPlatformLaravel\Http\ApiPlatformMiddleware;
use ApiPlatformLaravel\Listeners\KernelEventSubscriber;
use ApiPlatformLaravel\Security\DoctrineUserProvider;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Foundation\Application as Application;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Support\ServiceProvider;

class ApiPlatformServiceProvider extends ServiceProvider
{
    public function boot(Application $app)
    {
        $this->registerServices($app);
        $this->registerEvents($app);
        $this->registerUrlGenerator($app);
        $this->registerMiddleware($app);
        $this->extendAuthManager($app);

        $app->booted(function (Application $app) {
            event(Events::BOOT, [$app->make(ApplicationEvent::class)]);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/api_platform.php',
            'api_platform'
        );

        config([
            'auth.guards.providers.users.driver' => 'doctrine',
            'auth.guards.providers.users.model' => config('api_platform.guards.model'),
            'auth.guards.api_platform.driver' => config('api_platform.guards.driver'),
            'auth.guards.api_platform.provider' => config('api_platform.guards.provider'),
        ]);
        $this->app->singleton('api', function () {
            return new ApiHelper();
        });
        $this->app->alias('api', ApiHelper::class);
    }

    private function registerServices(Application $app)
    {
        $app->singleton(Kernel::class, function (Application $app) {
            $laravelKernel = $app->make(KernelContract::class);

            return new Kernel($laravelKernel);
        });

        $app->alias(Kernel::class, 'api_platform.kernel');

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
        if ($app->bound('auth')) {
            $app->make('auth')->provider('doctrine', function ($app, $config) {
                $identifiers = $app['config']->get('api_platform.security.identifiers');
                $model = $config['model'];
                $em = app('registry')->getManagerForClass($model);
                if (!$em) {
                    throw new InvalidArgumentException("Can't get entity manager for ${model}");
                }

                return new DoctrineUserProvider(
                    $app['hash'],
                    $em,
                    $model,
                    $identifiers
                );
            });
        }
    }

    private function registerUrlGenerator(Application $app)
    {
        $app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            // @TODO: should be improved in future for this simple binding
            $generator = new UrlGenerator(
                $routes, $app->rebinding(
                    'request', $this->requestRebinder()
                ),
                $app['config']['app.asset_url']
            );
            $router = clone $app['ApiPlatformContainer']->get('router')->getGenerator();
            $generator->setSymfonyGenerator($router);

            return $generator;
        });
    }

    /**
     * Get the URL generator request rebinder.
     *
     * @return \Closure
     */
    private function requestRebinder()
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }

    private function registerMiddleware(Application $app)
    {
        $app->singleton(ApiPlatformMiddleware::class, function (Application $app) {
            /** @var \Symfony\Component\DependencyInjection\Container $container */
            $container = $app->get('ApiPlatformContainer');
            $kernel = $container->get('http_kernel');
            $session = null;
            $auth = $app->get('auth');
            $guard = 'api_platform';

            $config = config();

            if ($container->has('session')) {
                $session = $container->get('session');
            }

            return new ApiPlatformMiddleware(
                $kernel,
                $auth,
                $guard,
                $session
            );
        });

        $app->alias(ApiPlatformMiddleware::class, 'api_platform');
        $app->singleton('ApiPlaceholderAction', function (Application $app) {
            return $app['ApiPlatformContainer']->get('api_platform.action.placeholder');
        });
    }

    private function registerEvents(Application $app)
    {
        $events = $app->get('events');
        $events->subscribe(new KernelEventSubscriber());
        $app->singleton(ApplicationEvent::class, function ($app) {
            return new ApplicationEvent($app);
        });
    }
}
