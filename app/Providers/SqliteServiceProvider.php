<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SqliteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (DB::getDriverName() === 'sqlite') {
            $path = DB::getConfig('database');
            if (!file_exists($path) && is_dir(dirname($path))) {
                touch($path);
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
