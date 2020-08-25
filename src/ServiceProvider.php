<?php

namespace Wavpa\Snowflake;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Snowflake::class, function(){
            return new Snowflake(config('services.snowflake.key'));
        });

        $this->app->alias(Snowflake::class, 'snowflake');
    }

    public function provides()
    {
        return [Snowflake::class, 'snowflake'];
    }
}