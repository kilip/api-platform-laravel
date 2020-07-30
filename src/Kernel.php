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

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use ApiPlatform\Core\Bridge\Symfony\Routing\ApiLoader;
use ApiPlatformLaravel\Bridge\Bundle;
use ApiPlatformLaravel\Compat\CompatKernel;
use ApiPlatformLaravel\Exception\InvalidArgumentException;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Support\Facades\Config;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends CompatKernel
{
    use MicroKernelTrait;

    public const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @var KernelContract
     */
    private $laravelKernel;

    /**
     * @var ApiLoader
     */
    protected $apiLoader;

    public function __construct(
        KernelContract $kernelContract
    ) {
        $this->laravelKernel = $kernelContract;
        $config = $this->getLaravelConfig();
        $environment = $config->get('app.env') ?? 'local';
        $debug = $config->get('app.debug') ?? true;
        parent::__construct($environment, $debug);
    }

    public function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir()
    {
        return $this->laravelKernel->getApplication()->basePath();
    }

    public function getLogDir()
    {
        return $this->laravelKernel->getApplication()->storagePath().'/api-platform/logs';
    }

    public function getCacheDir()
    {
        return $this->laravelKernel->getApplication()->storagePath().'/api-platform/cache';
    }

    protected function doLocateResource($name)
    {
        if ('@' !== $name[0]) {
            throw new InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
        }

        if (false !== strpos($name, '..')) {
            throw new InvalidArgumentException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $bundleName = substr($name, 1);
        $path = '';
        if (false !== strpos($bundleName, '/')) {
            list($bundleName, $path) = explode('/', $bundleName, 2);
        }

        $bundle = $this->getBundle($bundleName);
        if (file_exists($file = $bundle->getPath().'/'.$path)) {
            return $file;
        }

        // find in config path
        if (file_exists($file = $bundle->getPath().'/../'.$path)) {
            return $file;
        }
        throw new InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): iterable
    {
        $app = $this->laravelKernel->getApplication();
        $config = $this->getLaravelConfig();
        $classes = $config->get('app.providers');
        $filters = [
            'ApiPlatformServiceProvider',
            'EventServiceProvider',
        ];
        $bundles = [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new ApiPlatformBundle(),
            new LaravelBundle($app),
        ];

        foreach ($classes as $class) {
            if (false !== strpos($class, 'Illuminate\\')) {
                continue;
            }
            /** @var \Illuminate\Support\ServiceProvider $provider */
            $provider = $app->resolveProvider($class);
            $bundle = new Bundle($provider);
            if (\in_array($bundle->getName(), $filters, true)) {
                continue;
            }
            $bundles[] = $bundle;
        }

        return $bundles;
    }

    public function loadRoutes(LoaderInterface $loader)
    {
        $routes = new RouteCollectionBuilder($loader);
        $this->configureRoutes($routes);

        return $routes->build();
    }

    protected function initializeContainer()
    {
        parent::initializeContainer();
        $this->getContainer()->set('laravel', $this);
    }

    protected function configureContainer($container, $loader = null)
    {
        if ($container instanceof ContainerConfigurator) {
            $this->configureWithConfigurator($container);
        } else {
            $this->configureWithBuilder($container, $loader);
        }
    }

    /**
     * @param ContainerConfigurator $container
     *
     * @throws \Exception
     */
    protected function configureWithConfigurator(ContainerConfigurator $container): void
    {
        $paths = $this->getConfigPaths();
        foreach ($paths as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $container->import($dir.'/*.yaml');
            $envDir = $dir.'/'.$this->environment.'/*.yaml';
            if (is_dir($envDir)) {
                $container->import($dir.'/'.$this->environment.'/*.yaml');
            }
        }
    }

    protected function configureWithBuilder($container, LoaderInterface $loader)
    {
        $container->setParameter('container.dumper.inline_class_loader', true);

        $paths = $this->getConfigPaths();
        foreach ($paths as $confDir) {
            if (!is_dir($confDir)) {
                continue;
            }
            $loader->load($confDir.'/*'.self::CONFIG_EXTS, 'glob');

            $envDir = $confDir.'/'.$this->environment;
            if (is_dir($envDir)) {
                $loader->load($envDir.'/**/*'.self::CONFIG_EXTS, 'glob');
            }
        }
    }

    protected function configureRoutes($routes): void
    {
        if ($routes instanceof RoutingConfigurator) {
            $this->routesWithConfigurator($routes);
        } else {
            $this->routesWithBuilder($routes);
        }
    }

    protected function routesWithBuilder(RouteCollectionBuilder $routes)
    {
        $paths = $this->getRouteConfigPaths();
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }
            $routes->import($path.'/*'.self::CONFIG_EXTS, '/', 'glob');
            $routes->import($path.'/'.self::CONFIG_EXTS, '/', 'glob');

            $env = $path.'/'.$this->environment;
            if (is_dir($env)) {
                $routes->import($env.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
            }
        }
    }

    protected function routesWithConfigurator(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/{routes}.yaml');
        }
    }

    private function getConfigPaths()
    {
        return [
            realpath(__DIR__.'/../config/packages'),
            $this->getProjectDir().'/config/packages',
        ];
    }

    private function getRouteConfigPaths()
    {
        return [
            realpath(__DIR__.'/../config/routes'),
            $this->getProjectDir().'/config/routes',
        ];
    }

    /**
     * @return ConfigRepository|Config
     */
    public function getLaravelConfig()
    {
        return $this->laravelKernel->getApplication()->get('config');
    }
}
