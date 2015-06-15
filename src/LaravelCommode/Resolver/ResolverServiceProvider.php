<?php

namespace LaravelCommode\Resolver;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ResolverServiceProvider extends ServiceProvider
{
    const PROVIDES_RESOLVER = 'laravel-commode.resolver';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(self::PROVIDES_RESOLVER, function (Application $application) {
            return new Resolver($application);
        });

        $this->app->alias('CommodeResolver', ResolverFacade::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [self::PROVIDES_RESOLVER];
    }
}
