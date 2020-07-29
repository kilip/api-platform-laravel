<?php


namespace Tests\Parent;


use ApiPlatformLaravel\Facades\Api;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ParentServiceProvider extends ServiceProvider
{
    public function register()
    {
        Api::registerAnnotationMapping(__NAMESPACE__.'\\Model',__DIR__.'/Model');
    }
}