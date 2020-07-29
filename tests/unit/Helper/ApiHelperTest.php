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

namespace Tests\ApiPlatformLaravel\Unit\Helper;

use ApiPlatformLaravel\Helper\ApiHelper;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tests\Dummy\Model\User as ConcreteUser;
use Tests\Parent\Model\UserTrait as AbstractUser;

class ApiHelperTest extends TestCase
{
    private $xmlExpectationCallback;
    private $yamlExpectationCallback;

    private $annotationExpectationCallback;

    protected function setUp(): void
    {
        parent::setUp();
        $this->xmlExpectationCallback = function ($args) {
            $this->assertInstanceOf(Definition::class, $args[0]);
            $this->assertEquals(XmlDriver::class, $args[0]->getClass());
            $this->assertEquals(__NAMESPACE__, $args[1]);

            return true;
        };

        $this->yamlExpectationCallback = function ($args) {
            $this->assertInstanceOf(Definition::class, $args[0]);
            $this->assertEquals(YamlDriver::class, $args[0]->getClass());
            $this->assertEquals(__NAMESPACE__, $args[1]);

            return true;
        };

        $this->annotationExpectationCallback = function ($args) {
            $this->assertInstanceOf(Definition::class, $args[0]);
            $this->assertEquals(AnnotationDriver::class, $args[0]->getClass());
            $this->assertEquals(__NAMESPACE__, $args[1]);

            return true;
        };
    }

    public function testRegisterXmlMapping()
    {
        $helper = new ApiHelper();

        $compiler = $helper->registerXmlMapping(__NAMESPACE__, __DIR__);
        $builder = $this->getBuilder($this->xmlExpectationCallback);

        $compiler->process($builder);
        $this->assertCount(1, $helper->getOrmCompilersPass());
    }

    public function testRegisterYamlMapping()
    {
        $helper = new ApiHelper();

        $compiler = $helper->registerYamlMapping(__NAMESPACE__, __DIR__);
        $builder = $this->getBuilder($this->yamlExpectationCallback);

        $compiler->process($builder);
    }

    public function testRegisterAnnotationMapping()
    {
        $helper = new ApiHelper();

        $compiler = $helper->registerAnnotationMapping(__NAMESPACE__, __DIR__);
        $builder = $this->getBuilder($this->annotationExpectationCallback);

        $compiler->process($builder);
    }

    public function testResolveTargetEntities()
    {
        $helper = new ApiHelper();
        $helper->resolveTargetEntities(AbstractUser::class, ConcreteUser::class);
        $resolved = $helper->getResolvedEntities();

        $this->assertArrayHasKey(AbstractUser::class, $resolved);
        $this->assertEquals(ConcreteUser::class, $resolved[AbstractUser::class]);
    }

    /**
     * @param \Closure $callback
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|ContainerBuilder
     */
    private function getBuilder($callback)
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $chainDriver = $this->createMock(Definition::class);

        $builder->method('getDefinition')
            ->willReturnMap([
                ['doctrine.orm.default_metadata_driver', $chainDriver],
            ]);
        $builder->method('hasParameter')
            ->willReturnMap([
                ['doctrine.default_entity_manager', true],
            ]);
        $builder->method('getParameter')
            ->willReturnMap([
                ['doctrine.default_entity_manager', 'default'],
            ]);

        $chainDriver->expects($this->once())
            ->method('addMethodCall')
            ->with('addDriver', $this->callback($callback));

        return $builder;
    }
}
