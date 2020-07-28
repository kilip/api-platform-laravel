<?php

namespace Tests\ApiPlatformLaravel\Unit\Bridge;

use ApiPlatformLaravel\Exception\InvalidArgumentException;
use ApiPlatformLaravel\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Tests\ApiPlatformLaravel\Unit\KernelMockTrait;

class KernelTest extends TestCase
{
    use KernelMockTrait;

    public function testInitializeBundle()
    {
        $kernel = $this->createKernel();
        $this->assertInstanceOf(BundleInterface::class,$bundle = $kernel->getBundle('DummyServiceProvider'));
        $this->assertEquals('DummyServiceProvider',$bundle->getName());
        $this->assertEquals(realpath(__DIR__.'/../../package/src'), $bundle->getPath());

        $this->assertInstanceOf(BundleInterface::class, $bundle = $kernel->getBundle('ApiPlatformBundle'));

        $this->assertIsObject($container = $kernel->getContainer());
        $this->assertTrue($container->has('api_platform.action.placeholder'));
    }

    /**
     * @param string $file
     * @param string $assertExceptionMsgRegex
     * @dataProvider getLocateResourceTestData
     */
    public function testLocateResource($file, string $assertExceptionMsgRegex = null)
    {
        $kernel = $this->createKernel();

        if(!is_null($assertExceptionMsgRegex)){
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessageMatches($assertExceptionMsgRegex);
        }
        $this->assertFileExists($kernel->locateResource($file));
    }

    public function getLocateResourceTestData()
    {
        return [
            ['DummyServiceProvider/config/test.php', '#A resource name must start with#'],
            ['@DummyServiceProvider/../foo','#../foo\" contains invalid characters#'],
            ['@DummyServiceProvider/config/test.php'],
            ['@DummyServiceProvider/Resources/foo.php'],
        ];
    }

    private function createKernel()
    {
        $kernel = new Kernel($this->getKernelMock());
        $kernel->boot();

        return $kernel;
    }
}
