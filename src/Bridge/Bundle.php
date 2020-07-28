<?php


namespace ApiPlatformLaravel\Bridge;


use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Bundle implements BundleInterface
{
    use ContainerAwareTrait;

    protected $class;
    protected $name;
    protected $extension;
    protected $path;
    private $namespace;

    public function __construct(ServiceProvider $provider)
    {
        $this->class = get_class($provider);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
    }

    /**
     * {@inheritdoc}
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     */
    public function build(ContainerBuilder $container)
    {
    }

    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     *
     * @throws \LogicException
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $extension = $this->createContainerExtension();

            if (null !== $extension) {
                if (!$extension instanceof ExtensionInterface) {
                    throw new \LogicException(sprintf('Extension %s must implement Symfony\Component\DependencyInjection\Extension\ExtensionInterface.', \get_class($extension)));
                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        return $this->extension ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        if (null === $this->namespace) {
            $this->parseClassName();
        }

        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        if (null === $this->path) {
            $reflected = new \ReflectionClass($this->class);
            $this->path = \dirname($reflected->getFileName());
        }

        return $this->path;
    }

    /**
     * Returns the bundle name (the class short name).
     *
     * @return string The Bundle name
     */
    final public function getName()
    {
        if (null === $this->name) {
            $this->parseClassName();
        }

        return $this->name;
    }

    public function registerCommands(Application $application)
    {
    }

    /**
     * Returns the bundle's container extension class.
     *
     * @return string
     */
    protected function getContainerExtensionClass()
    {
        $basename = preg_replace('/Provider$/', '', $this->getName());
        $class = $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
        if(!class_exists($class)){
            $basename = preg_replace('/ServiceProvider$/', '', $this->getName());
            $class = $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
        }

        return $class;
    }

    /**
     * Creates the bundle's container extension.
     *
     * @return ExtensionInterface|null
     */
    protected function createContainerExtension()
    {
        return class_exists($class = $this->getContainerExtensionClass()) ? new $class() : null;
    }

    private function parseClassName()
    {
        $class = $this->class;
        $pos = strrpos($class, '\\');
        $this->namespace = false === $pos ? '' : substr($class, 0, $pos);
        if (null === $this->name) {
            $this->name = false === $pos ? $class : substr($class, $pos + 1);
        }
    }

}