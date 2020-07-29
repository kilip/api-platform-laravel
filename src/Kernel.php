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
use ApiPlatformLaravel\Bridge\Bundle;
use ApiPlatformLaravel\Exception\InvalidArgumentException;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader as ContainerPhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

if (version_compare(BaseKernel::VERSION, '5', '<')) {
    abstract class CompatKernel extends BaseKernel
    {
        public function locateResource($name)
        {
            return $this->doLocateResource($name);
        }

        abstract protected function doLocateResource($name);
    }
} else {
    abstract class CompatKernel extends BaseKernel
    {
        public function locateResource(string $name)
        {
            return $this->doLocateResource($name);
        }

        abstract protected function doLocateResource($name);
    }
}

class Kernel extends CompatKernel
{
    use MicroKernelTrait;

    public const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    private $laravelKernel;

    private $laravelApp;

    public function __construct(
        KernelContract $kernelContract
    ) {
        $app = $kernelContract->getApplication();
        $environment = $app->get('config')->get('app.env') ?? 'local';
        $debug = $app->get('config')->get('app.debug') ?? true;

        $this->laravelApp = $app;
        $this->laravelKernel = $kernelContract;
        parent::__construct($environment, $debug);
    }

    public function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);
        $this->configureDoctrine($container);
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {
            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => 'kernel::loadRoutes',
                    'type' => 'service',
                ],
            ]);

            $kernelClass = false !== strpos(static::class, "@anonymous\0") ? parent::class : static::class;

            if (!$container->hasDefinition('kernel')) {
                $container->register('kernel', $kernelClass)
                    ->addTag('controller.service_arguments')
                    ->setAutoconfigured(true)
                    ->setSynthetic(true)
                    ->setPublic(true);
            }

            $kernelDefinition = $container->getDefinition('kernel');
            $kernelDefinition->addTag('routing.route_loader');

            $container->addObjectResource($this);
            $container->fileExists($this->getProjectDir().'/config/bundles.php');

            try {
                $configureContainer = new \ReflectionMethod($this, 'configureContainer');
            } catch (\ReflectionException $e) {
                throw new \LogicException(sprintf('"%s" uses "%s", but does not implement the required method "protected function configureContainer(ContainerConfigurator $c): void".', get_debug_type($this), MicroKernelTrait::class), 0, $e);
            }

            $configuratorClass = $configureContainer->getNumberOfParameters() > 0 && ($type = $configureContainer->getParameters()[0]->getType()) && !$type->isBuiltin() ? $type->getName() : null;

            if ($configuratorClass && !is_a(ContainerConfigurator::class, $configuratorClass, true)) {
                $this->configureContainer($container, $loader);

                return;
            }

            // the user has opted into using the ContainerConfigurator
            /** @var ContainerPhpFileLoader $kernelLoader */
            $kernelLoader = $loader->getResolver()->resolve($file = $configureContainer->getFileName());
            $kernelLoader->setCurrentDir(\dirname($file));
            $instanceof = &\Closure::bind(function &() { return $this->instanceof; }, $kernelLoader, $kernelLoader)();

            $valuePreProcessor = AbstractConfigurator::$valuePreProcessor;
            AbstractConfigurator::$valuePreProcessor = function ($value) {
                return $this === $value ? new Reference('kernel') : $value;
            };

            try {
                $this->configureContainer(new ContainerConfigurator($container, $kernelLoader, $instanceof, $file, $file), $loader);
            } finally {
                $instanceof = [];
                $kernelLoader->registerAliasesForSinglyImplementedInterfaces();
                AbstractConfigurator::$valuePreProcessor = $valuePreProcessor;
            }

            $container->setAlias($kernelClass, 'kernel')->setPublic(true);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir()
    {
        return $this->laravelApp->basePath();
    }

    public function getLogDir()
    {
        return $this->laravelApp->storagePath().'/api-platform/logs';
    }

    public function getCacheDir()
    {
        return $this->laravelApp->storagePath().'/api-platform/cache';
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
        $filters = [
            'ApiPlatformServiceProvider',
            'EventServiceProvider',
        ];

        $bundles = [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new ApiPlatformBundle(),
            new LaravelBundle(),
        ];
        $providers = $this->laravelApp->getLoadedProviders();
        $classes = array_keys($providers);
        foreach ($classes as $class) {
            if (false !== strpos($class, 'Illuminate\\')) {
                continue;
            }
            $provider = $this->laravelApp->getProvider($class);
            $bundle = new Bundle($provider);
            if (\in_array($bundle->getName(), $filters, true)) {
                continue;
            }
            $bundles[] = $bundle;
        }

        return $bundles;
    }

    /**
     * @param ContainerBuilder|ContainerConfigurator $container
     * @param LoaderInterface                        $loader
     *
     * @throws \Exception
     */
    protected function configureContainer(ContainerConfigurator $container, $loader): void
    {
        if ($container instanceof ContainerConfigurator) {
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
        } else {
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
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/{routes}.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }

    private function getConfigPaths()
    {
        return [
            realpath(__DIR__.'/../config/api_platform'),
            $this->getProjectDir().'/config/api_platform',
        ];
    }

    private function configureDoctrine(ContainerBuilder $container)
    {
        // doctrine related service
        $laravelApp = $this->laravelApp;
        $helper = $laravelApp->get('api');
        $compilers = $helper->getOrmCompilersPass();
        foreach ($compilers as $compiler) {
            $container->addCompilerPass($compiler);
        }

        $resolved = $helper->getResolvedEntities();
        $container->setParameter('laravel.orm.resolve_target_entities', $resolved);
        $container->addObjectResource($helper);
        $container->addObjectResource($laravelApp);
    }
}
