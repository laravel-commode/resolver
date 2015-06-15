<?php

namespace LaravelCommode\Resolver;

use Closure;
use Illuminate\Foundation\Application;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

/**
 * Class Resolver
 *
 * Resolver is a helper class that can resolve
 * closures and class methods or turn them into
 * resolvable closures.
 *
 * @author Volynov Andrey
 * @package LaravelCommode\Common\Resolver
 */
class Resolver
{
    /**
     * Laravel application instance. Is used as an
     * access to laravel's application IoC
     *
     * @var \Illuminate\Foundation\Application
     */
    private $laraApp;

    /**
     * Class __constructor
     * @param Application $laraApp  Laravel application instance.
     * If it's value is null, it will be initialized from app() function
     */
    public function __construct(Application $laraApp = null)
    {
        /**
         * Initialize laravel IoC
         */
        $this->laraApp = !is_null($laraApp) ? $laraApp : app();
    }

    /**
     * Determines if passed ReflectionParameter $parameter can be resolved from IoC.
     * If $parameter can be resolved and it's bound in IoC it's bound class/interface name
     * will be returned.
     *
     * @param ReflectionParameter $parameter
     * @param $reflectionKey
     * @param array $params
     * @return bool|string
     */
    protected function isResolvable(ReflectionParameter $parameter, $reflectionKey, $params = array())
    {
        /**
         * check if parameter available from $params with $reflectionKey key
         */
        if (array_key_exists($reflectionKey, $params)) {
            return false;
        }

        /**
         * check if $parameter has type
         */
        preg_match('/\[\s\<\w+?>\s([\w\\\\]+)/s', (string) $parameter, $matches);

        if (count($matches) < 1) {
            return false;
        }

        /**
         * check if $parameter type is not array and can be treated as class/interface
         */
        $canBeCreated = array_key_exists(1, $matches) && !$parameter->isArray() && is_string($matches[1]);

        /**
         * check if $parameter class/interface exists or it's been bound
         */
        $existsOrBound = class_exists($matches[1]) || $this->laraApp->bound($matches[1]);

        return ($canBeCreated && $existsOrBound) ? $matches[1] : false;
    }

    /**
     * Returns array of values, resolved from ReflectionParameter[] $reflectionParams
     *
     * @param ReflectionParameter[] $reflectionParams array of ReflectionParameter
     * @param array $params array of parameters with known value
     * @return array
     */
    public function resolve($reflectionParams, array $params = [])
    {
        foreach ($reflectionParams as $key => $reflectionParam) {
            if ($registryName = $this->isResolvable($reflectionParam, $key, $params)) {
                $params[$key] = $this->laraApp->make($registryName);//\App ::make();
            }
        }

        return $params;
    }

    /**
     * Resolves class method parameters and returns them.
     *
     * @param string|object $class class instance or class name which method needs to be resolved
     * @param string $method name of method that needs to be resolved
     * @param array $params Array of default/known values.
     * @return mixed returns result of execution resolved $class::$method
     * @return array
     */
    public function resolveMethodParameters($class, $method, array $params = [])
    {
        return $this->resolve((new ReflectionMethod($class, $method))->getParameters(), $params);
    }

    /**
     * Resolves class method and calls it.
     *
     * @param string|object $class class instance or class name which method needs to be resolved.
     * @param string $method name of method that needs to be resolved.
     * @param array $params Array of default/known values.
     * @param bool $forceScopeCalls suppresses scope exceptions and calls method even if it's
     * protected or private.
     * @throws \Exception
     * @return mixed returns result of execution resolved $class::$method.
     */
    public function method($class, $method, array $params = [], $forceScopeCalls = false)
    {
        $reflectionClass = new \ReflectionClass(is_string($class) ? $this->laraApp->make($class) : $class);

        $reflectionMethod = $reflectionClass->getMethod($method);

        $resolved = $this->resolve($reflectionMethod->getParameters(), $params);

        try {
            $result = call_user_func_array([$class, $method], $resolved);
        } catch (\Exception $exception) {
            if ($forceScopeCalls && ($reflectionMethod->isPrivate() || $reflectionMethod->isProtected())) {
                $reflectionMethod->setAccessible(true);
                $result = $reflectionMethod->invokeArgs($class, $resolved);
                $reflectionMethod->setAccessible(false);
            } else {
                throw $exception;
            }

        }

        return $result;
    }

    /**
     * Returns wrapped into resolvable closure that would call and resolve class method.
     *
     * @param string|object $class class instance or class name which method needs to be resolved
     * @param string $method name of method that needs to be resolved
     * @return Closure
     */
    public function methodToClosure($class, $method)
    {
        return function () use ($class, $method) {
            return $this->method($class, $method, func_get_args());
        };
    }

    /**
     * Resolves $closure and executes it
     *
     * @param Closure $closure Closure that needs to be resolved.
     * @param array $params Array of default/known values.
     * @return mixed Result of $closure execution
     */
    public function closure(Closure $closure, array $params = [])
    {
        $reflection = new \ReflectionFunction($closure);
        $resolved = $this->resolve($reflection->getParameters(), $params);
        return call_user_func_array($closure, $resolved);
    }

    /**
     * Wraps make closure resolvable on execution.
     *
     * @param Closure $closure Closure that needs to be resolved.
     * @return Closure
     */
    public function makeClosure(Closure $closure)
    {
        return function () use ($closure) {
            return $this->closure($closure, func_get_args());
        };
    }
}
