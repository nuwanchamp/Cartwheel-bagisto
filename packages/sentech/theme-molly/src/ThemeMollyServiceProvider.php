<?php

namespace Sentech\ThemeMolly;

use Illuminate\Support\ServiceProvider;

class ThemeMollyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
    public function register()
    {
    }
}
