<?php


namespace Tests\Dummy;


use ApiPlatformLaravel\Facades\Api;
use Illuminate\Support\ServiceProvider;

class DummyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Api::registerAnnotationMapping(__NAMESPACE__.'\\Model',__DIR__.'/Model');
    }
}