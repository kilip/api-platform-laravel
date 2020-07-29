<?php


namespace ApiPlatformLaravel\Testing;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;

trait InteractsWithORM
{
    /**
     * Refresh database
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function refreshDatabase()
    {
        $registry = $this->getRegistry();

        foreach($registry->getManagers() as $em){
            $metas = $em->getMetadataFactory()->getAllMetadata();
            $tool = new SchemaTool($em);
            $tool->dropSchema($metas);
            $tool->createSchema($metas);
        }
    }

    /**
     * @param $class
     * @return \Doctrine\Persistence\ObjectRepository
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getRepository($class)
    {
        return $this->getManagerForClass($class)->getRepository($class);
    }

    /**
     * @param $name
     * @return \Doctrine\Persistence\ObjectManager|EntityManagerInterface
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getManager($name)
    {
        return $this->getRegistry()->getManager($name);
    }

    /**
     * @param $className
     * @return \Doctrine\Persistence\ObjectManager|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getManagerForClass($className)
    {
        return $this->getRegistry()->getManagerForClass($className);
    }

    /**
     * @return ManagerRegistry
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getRegistry()
    {
        return app()->make('registry');
    }
}