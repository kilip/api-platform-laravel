<?php


namespace ApiPlatformLaravel;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use ApiPlatformLaravel\Bridge\Bundle;
use ApiPlatformLaravel\Exception\InvalidArgumentException;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Application as ApplicationContract;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    private $laravelKernel;

    private $laravelApp;

    public function __construct(
        KernelContract $kernelContract
    )
    {
        $app = $kernelContract->getApplication();
        $environment = $app->get('config')->get('app.env') ?? 'local';
        $debug = $app->get('config')->get('app.debug') ?? true;

        $this->laravelApp = $app;
        $this->laravelKernel = $kernelContract;
        parent::__construct($environment, $debug);
    }


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

    public function locateResource(string $name)
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
        if(file_exists($file = $bundle->getPath().'/../'.$path)){
            return $file;
        }
        throw new InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new ApiPlatformBundle(),
            new LaravelBundle(),
        ];
        $providers = $this->laravelApp->getLoadedProviders();
        $classes = array_keys($providers);
        foreach($classes as $class){
            $provider = $this->laravelApp->getProvider($class);
            $bundle = new Bundle($provider);
            $bundles[] = $bundle;
        }

        return $bundles;
    }

    protected function configureContainer($container, $loader): void
    {
        if($container instanceof ContainerConfigurator){
            $this->configureWithConfigurator($container, $loader);
        }else{;
            $container->setParameter('container.dumper.inline_class_loader', true);
            $confDir = __DIR__.'/../config';

            $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
            $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
            $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
            $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
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

    private function configureWithConfigurator($container)
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import('../config/{services}.yaml');
            $container->import('../config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }
}

