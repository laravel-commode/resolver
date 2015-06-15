<?php

namespace LaravelCommode\Resolver;

use PHPUnit_Framework_MockObject_MockObject as Mock;

class ResolverServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mock|\Illuminate\Foundation\Application
     */
    private $application;

    /**
     * @var ResolverServiceProvider
     */
    private $testInstance;

    protected function setUp()
    {
        $this->application = $this->getMock(
            'Illuminate\Foundation\Application',
            ['bind']
        );
        $this->testInstance = new ResolverServiceProvider($this->application);

        parent::setUp();
    }

    public function testRegister()
    {
        $this->application->expects($this->any())->method('bind')
            ->with(ResolverServiceProvider::PROVIDES_RESOLVER, $this->callback(function ($closure) {
                return $closure($this->application) instanceof Resolver;
            }));

        $this->testInstance->register();
    }

    public function testProvides()
    {
        $this->application->expects($this->any())->method('bind')
            ->with(ResolverServiceProvider::PROVIDES_RESOLVER, $this->callback(function ($closure) {
                return $closure($this->application) instanceof Resolver;
            }));

        $this->assertContains(ResolverServiceProvider::PROVIDES_RESOLVER, $this->testInstance->provides());
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
