<?php


namespace ApiPlatformLaravel;


use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel as KernelContract;

class ApiPlatformServiceProvider extends ServiceProvider
{
    public function boot(Application $app)
    {
        if(!$app->eventsAreCached()){
            $this->registerEvents($app);
        }
        $app->singleton(Kernel::class, function(Application $app){
            $laravelKernel = $app->make(KernelContract::class);
            return new Kernel($laravelKernel);
        });
        $app->singleton('ApiPlatformContainer', function(Application $app){
            return $app->make(Kernel::class)->getContainer();
        });

        $app->singleton('registry', function(Application $app){
            return $app->make('ApiPlatformContainer')->get('doctrine');
        });
        $app->alias('registry', ManagerRegistry::class);

        $app->booted([$this,'afterBoot']);
    }

    public function afterBoot(Application $app)
    {
        $app->make(Kernel::class)->boot();
    }

    private function registerEvents(Application $app)
    {

    }
}