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

namespace ApiPlatformLaravel\Testing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;

trait InteractsWithORM
{
    /**
     * Refresh database.
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function refreshDatabase()
    {
        $registry = $this->getRegistry();

        foreach ($registry->getManagers() as $em) {
            $metas = $em->getMetadataFactory()->getAllMetadata();
            $tool = new SchemaTool($em);
            $tool->dropSchema($metas);
            $tool->createSchema($metas);
        }
    }

    public function store(object $entity, $andFlush = true)
    {
        $class = get_class($entity);
        $em = $this->getManagerForClass($class);

        $em->persist($entity);
        $em->flush();
    }

    /**
     * @param $class
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return \Doctrine\Persistence\ObjectRepository
     */
    public function getRepository($class)
    {
        return $this->getManagerForClass($class)->getRepository($class);
    }

    /**
     * @param $name
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return \Doctrine\Persistence\ObjectManager|EntityManagerInterface
     */
    public function getManager($name)
    {
        return $this->getRegistry()->getManager($name);
    }

    /**
     * @param $className
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return \Doctrine\Persistence\ObjectManager|null
     */
    public function getManagerForClass($className)
    {
        return $this->getRegistry()->getManagerForClass($className);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return ManagerRegistry
     */
    public function getRegistry()
    {
        return app()->make('registry');
    }
}
