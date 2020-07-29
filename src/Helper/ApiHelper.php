<?php


namespace ApiPlatformLaravel\Helper;


use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

class ApiHelper
{
    /**
     * @var DoctrineOrmMappingsPass[]
     */
    private $ormMappingPasses = [];

    /**
     * @param string $namespace
     * @param string|array $paths
     * @param string $managerName
     * @return DoctrineOrmMappingsPass
     */
    public function registerXmlMapping($namespace, $paths, $managerName=null)
    {
        $managerName = !is_null($managerName) ?:'default';
        $compiler = DoctrineOrmMappingsPass::createXmlMappingDriver(
            $this->getMappings($namespace, $paths),[$managerName]
        );
        $this->ormMappingPasses[] = $compiler;
        return $compiler;
    }

    public function registerYamlMapping($namespace, $paths, $managerName=null)
    {
        $managerName = !is_null($managerName) ?:'default';
        $compiler = DoctrineOrmMappingsPass::createYamlMappingDriver(
            $this->getMappings($namespace, $paths),
            [$managerName]
        );
        $this->ormMappingPasses[] = $compiler;

        return $compiler;
    }

    public function registerAnnotationMapping($namespace, $paths, $managerName=null)
    {
        $namespace = is_array($namespace) ?: [$namespace];
        $paths = !is_string($paths) ?: [$paths];
        $managerName = !is_null($managerName) ?:'default';
        $compiler = DoctrineOrmMappingsPass::createAnnotationMappingDriver(
            $namespace,
            $paths,
            [$managerName]
        );
        $this->ormMappingPasses[] = $compiler;

        return $compiler;
    }

    /**
     * @return DoctrineOrmMappingsPass[]
     */
    public function getOrmCompilersPass()
    {
        return $this->ormMappingPasses;
    }

    /**
     * @param string $namespace
     * @param string|array $paths
     * @return array
     */
    private function getMappings($namespace, $paths)
    {
        $paths = !is_string($paths) ?: [$paths];
        $mappings = [];
        foreach($paths as $path){
            $mappings[$path] = $namespace;
        }

        return $mappings;
    }


}