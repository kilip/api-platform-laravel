<?php


namespace ApiPlatformLaravel;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use ApiPlatformLaravel\Bridge\Bundle;
use ApiPlatformLaravel\Exception\InvalidArgumentException;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as KernelContract;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    public function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);

        $laravelApp = $this->laravelApp;
        $compilers = $laravelApp->get('api')->getOrmCompilersPass();
        foreach($compilers as $compiler){
            $container->addCompilerPass($compiler);
        }
    }

    /**
     * @return Application
     */
    public function getLaravelApplication(): Application
    {
        return $this->laravelApp;
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
        $filters = [
            'ApiPlatformServiceProvider',
            'EventServiceProvider'
        ];

        $bundles = [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new ApiPlatformBundle(),
            new LaravelBundle(),
        ];
        $providers = $this->laravelApp->getLoadedProviders();
        $classes = array_keys($providers);
        foreach($classes as $class){
            if(false !== strpos($class,'Illuminate\\')){
                continue;
            }
            $provider = $this->laravelApp->getProvider($class);
            $bundle = new Bundle($provider);
            if(in_array($bundle->getName(),$filters)){
                continue;
            }
            $bundles[] = $bundle;
        }

        return $bundles;
    }

    /**
     * @param ContainerBuilder|ContainerConfigurator $container
     * @param LoaderInterface $loader
     * @throws \Exception
     */
    protected function configureContainer($container, $loader): void
    {
        if($container instanceof ContainerConfigurator){
            $this->configureWithConfigurator($container);
        }else{;
            $container->setParameter('container.dumper.inline_class_loader', true);

            $paths = $this->getConfigPaths();
            foreach($paths as $confDir){
                $loader->load($confDir.'/*'.self::CONFIG_EXTS, 'glob');
                $loader->load($confDir.'/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
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

    private function configureWithConfigurator($container)
    {
        $paths = $this->getConfigPaths();
        foreach($paths as $dir){
            if(!is_dir($dir)){
                continue;
            }
            $container->import($dir.'/*.yaml');
            $envDir = $dir.'/'.$this->environment.'/*.yaml';
            if(is_dir($envDir)){
                $container->import($dir.'/'.$this->environment.'/*.yaml');
            }
        }
    }

    private function getConfigPaths()
    {
        return [
            realpath(__DIR__.'/../config/api_platform'),
            $this->getProjectDir().'/config/api_platform'
        ];
    }
}

