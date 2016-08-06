<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use UAParser\Parser;

class UAParserServiceProvider extends ServiceProvider


{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Parser::class,function($app){
            return Parser::create();
        });
    }
}
