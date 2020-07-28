<?php


namespace Tests\ApiPlatformLaravel\Unit;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Log\Logger as IlluminateLogger;
use Illuminate\Log\LogManager;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Dummy\DummyServiceProvider;

trait KernelMockTrait
{
    /**
     * @var MockObject|ApplicationContract
     */
    protected $app;

    /**
     * @var MockObject|KernelContract
     */
    protected $kernel;

    /**
     * @var MockObject|ServiceProvider
     */
    protected $provider;

    /**
     * @var MockObject|MonologLogger
     */
    protected $monologLogger;

    public function getKernelMock()
    {
        $app = $this->createMock(Application::class);
        $provider = new DummyServiceProvider($app);
        $monologLoger = $this->createMock(MonologLogger::class);
        $configRepository = $this->createMock(Repository::class);

        $logger = $this->createMock(IlluminateLogger::class);
        $logger->method('getLogger')
            ->willReturn($monologLoger);

        $configRepository
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['app.debug', true],
                ['app.locale','en'],
                ['app.env','test']
            ]);

        $app->method('basePath')
            ->willReturn(__DIR__.'/../sandbox');
        $app->method('storagePath')
            ->willReturn(__DIR__.'/../../build/storage');
        $app->method('environment')
            ->willReturn('testing');
        $app->method('getLoadedProviders')
            ->willReturn([DummyServiceProvider::class => true]);
        $app->method('getProvider')
            ->with(DummyServiceProvider::class)
            ->willReturn($provider);

        $app->method('get')
            ->willReturnMap([
                ['logger', $logger],
                ['config', $configRepository]
            ]);
        $kernel = $this->createMock(KernelContract::class);
        $kernel->method('getApplication')
            ->willReturn($app);

        $this->kernel = $kernel;
        $this->app = $app;
        $this->provider = $provider;
        $this->monologLogger = $monologLoger;
        return $kernel;
    }
}