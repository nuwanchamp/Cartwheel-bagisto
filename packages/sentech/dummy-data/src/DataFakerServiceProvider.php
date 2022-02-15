<?php
namespace Sentech\DummyData;

use Illuminate\Support\ServiceProvider;

class DataFakerServiceProvider extends ServiceProvider
{
    public function boot(){
        $this->publishes([
            __DIR__."/Database/Factories" => database_path('factories'),
        ]);
    }
    public function register()
    {
        parent::register(); // TODO: Change the autogenerated stub
    }
}