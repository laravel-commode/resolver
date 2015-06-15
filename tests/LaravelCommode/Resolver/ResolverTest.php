<?php

namespace LaravelCommode\Resolver;

use Exception;
use Illiminate\Foundation\Application;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var \Illuminate\Foundation\Application|Mock
     */
    private $applicationMock;

    protected function setUp()
    {
        $this->applicationMock = $this->getMock(
            'Illuminate\Foundation\Application',
            ['make']
        );

        $this->resolver = new Resolver($this->applicationMock);

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    protected function prepareApplication()
    {
        /**
         * @param $whatToMake
         * @return $this
         * @throws Exception
         */
        $makeWill = function ($whatToMake) {
            switch ($whatToMake)
            {
                case 'LaravelCommode\Resolver\ResolverTest':
                    return $this;
                default:
                    throw new Exception("Unexpected {$whatToMake}");
            }
        };

        $this->applicationMock->expects($this->any())
            ->method('make')
            ->will($this->returnCallback($makeWill));
    }

    public function testClosures()
    {
        $this->prepareApplication();

        $testValue = uniqid('testValue');

        $closureNoArgs = function () {
            return null;
        };

        $closureSimple = function ($integer) {
            return $integer;
        };

        $closureResolved = function ($integer, ResolverTest $test = null) {
            return [$integer, $test];
        };

        $this->assertNull($this->resolver->closure($closureNoArgs));
        $this->assertSame($testValue, $this->resolver->closure($closureSimple, [$testValue]));
        $this->assertSame([$testValue, $this], $this->resolver->closure($closureResolved, [$testValue]));

        $newResolvedClosure = $this->resolver->makeClosure($closureResolved, [$testValue]);

        $this->assertSame([$testValue, $this], $newResolvedClosure($testValue));
    }

    public function testMethod()
    {
        $this->prepareApplication();

        $testValue = uniqid("testValue");

        $expect = [$testValue, $this];
        $pass = [$testValue];

        $this->assertSame($expect, $this->resolver->method($this, 'injectionTesting', $pass));
        $this->assertSame($expect, $this->resolver->method($this, 'scopeInjectionTesting', $pass, true));

        try {
            $this->assertSame($expect, $this->resolver->method($this, 'scopeInjectionTesting', $pass));
        } catch (\Exception $e) {
            $this->assertInstanceOf('Exception', $e);
        }

        $closure = $this->resolver->methodToClosure($this, 'injectionTesting');

        $this->assertSame($expect, $closure($testValue));

        $this->assertSame($expect, $this->resolver->resolveMethodParameters($this, 'injectionTesting', $pass));
    }


    public function injectionTesting($integer, ResolverTest $test = null, $emptyValue = null)
    {
        return [$integer, $test];
    }


    protected function scopeInjectionTesting($integer, ResolverTest $test = null, $emptyValue = null)
    {
        return [$integer, $test];
    }
}
