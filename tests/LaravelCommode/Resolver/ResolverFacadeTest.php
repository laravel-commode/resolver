<?php

namespace LaravelCommode\Resolver;

use PHPUnit_Framework_TestCase;

class ResolverFacadeTest extends PHPUnit_Framework_TestCase
{
    public function testFacade()
    {
        $reflectionMethod = new \ReflectionMethod(ResolverFacade::class, 'getFacadeAccessor');
        $reflectionMethod->setAccessible(true);
        $this->assertSame(ResolverServiceProvider::PROVIDES_RESOLVER, $reflectionMethod->invoke(null));
    }
}
