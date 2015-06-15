<?php

namespace LaravelCommode\Resolver;

use Illuminate\Support\Facades\Facade;

class ResolverFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResolverServiceProvider::PROVIDES_RESOLVER;
    }
}
