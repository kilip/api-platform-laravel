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

namespace ApiPlatformLaravel\Listeners;

use ApiPlatformLaravel\Event\ApplicationEvent;
use ApiPlatformLaravel\Events;
use ApiPlatformLaravel\Http\Controllers\PlaceholderAction;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router as LaravelRouter;
use Symfony\Bundle\FrameworkBundle\Routing\Router as SymfonyRouter;

class KernelEventSubscriber
{
    public function onBoot(ApplicationEvent $event)
    {
        $app = $event->getApplication();
        $kernel = $app->make('api_platform.kernel');
        $kernel->boot();

        $container = $kernel->getContainer();
        $symfony = $container->get('router');
        $laravel = $app->get('router');

        $this->registerSymfonyRouter($laravel, $symfony);
        $event->setContainer($container);
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(Events::BOOT, static::class.'@onBoot');
    }

    private function registerSymfonyRouter(LaravelRouter $laravel, SymfonyRouter $symfony)
    {
        /** @var \Symfony\Component\Routing\Route $route */
        foreach ($symfony->getRouteCollection() as $name => $route) {
            $uri = $this->normalizeUri($route->getPath());
            $methods = $this->normalizeMethods($route->getMethods());
            $laravel->addRoute(
                $methods,
                $uri,
               PlaceholderAction::class
            )->name($name);
        }
    }

    private function normalizeMethods($methods)
    {
        return !empty($methods) ? $methods : ['GET', 'HEAD', 'POST'];
    }

    private function normalizeUri($uri)
    {
        return str_replace('.{_format}', '{_format?}', $uri);
    }
}
