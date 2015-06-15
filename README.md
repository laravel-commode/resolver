#Commode: Resolver

[![Build Status](https://travis-ci.org/laravel-commode/resolver.svg?branch=master)](https://travis-ci.org/laravel-commode/resolver)
[![Code Climate](https://codeclimate.com/github/laravel-commode/resolver/badges/gpa.svg)](https://codeclimate.com/github/laravel-commode/resolver)
[![Coverage Status](https://coveralls.io/repos/laravel-commode/resolver/badge.svg?branch=master)](https://coveralls.io/r/laravel-commode/resolver?branch=master)

>**_laravel-commode/resolver_** is a [method|closure] resolver class/service for laravel-commode package 
environment or for laravel 5.1 standalone. 

####Contents

+ <a href="#installing">Installing</a>
+ <a href="#usage">Usage examples</a>
+ <a href="#aliases">Alias references table</a>


##<a name="installing">Installing</a>

You can install ___laravel-commode/resolver___ using composer:

```json
"require": {
    "laravel-commode/resolver": "dev-master"
}
```
    
To enable package you need to register ``LaravelCommode\Resolver\ResolverServiceProvider`` service provider in 
your `app.php` configuration file.

```php
<?php
    // apppath/config/app.php
    return [
        // config code...
        
        'providers' => [
            // your app providers... ,
            LaravelCommode\Resolver\ResolverServiceProvider::class
            
        ]
    ];
```

##<a name="usage">Usage</a>

Resolver is a small, but useful class for building something flexible or for something that requires resolving.
It is available through ``CommodeResolver`` facade, or - if you are a facade hater you can find it registered in
IoC container through alias "laravel-commode.resolver" or can initialize new instance as
``new \LaravelCommode\Resolver\Resolver($laravelApplicationInstance)``.

For example, let's say that you have some structure for your security module like ISecurityUser and it's bound
to your configured eloquent auth model.

```php
<?php namespace App\System\Security\Abstractions;

    interface ISecurityUser
    {
        public function hasPermission($permission);
        public function hasPermissions(array $permissions);
    }
```

<br />

```php
<?php namespace App\DAL\Concrete\Eloquent\Models;

    use Illuminate\Database\Eloquent\Model;

    class Account extends Model implements ISecurityUser
    {
        /* your eloquent model code */
    }
```

<br />

```php
<?php namespace App\ServiceProviders;

    use LaravelCommode\SilentService\SilentService;
    use MyApp\System\Security\Abstractions\ISecurityUser;

    class ACLServiceProvider extends SilentService
    {
        public function launching() {}

        public function registering()
        {
            $this->app->bind(ISecurityUser::class, function ($app)
            {
                return app('auth')->user(); // note that returned value might be null
            });
        }
    }
```

``CommodeResolver`` can resolve closures and class methods or turn them into resolvable closures. 
Here's an example of using it.

###Resolver and closures:
```php
<?php
    use App\System\Security\Abstractions\ISecurityUser;

    $closureThatNeedsToBeResolved = function ($knownParameter1, $knownParameterN, ISecurityUser $needsToBeResolved = null)
    {
        return func_get_args();
    };

    $resolver = new \LaravelCommode\Resolver\Resolver(); // or app('laravel-commode.resolver');

    $knownParameter1 = 'Known';
    $knownParameter2 = 'Parameter';

    /**
    *   Resolving closure and running it
    **/
    $result = $resolver->closure($closureThatNeedsToBeResolved, [$knownParameter1, $knownParameter2]);
    $resultClosure = $resolver->makeClosure($closureThatNeedsToBeResolved);

    var_dump(
        $result, $resultClosure($knownParameter1, $knownParameter2),
        $result === $resultClosure($knownParameter1, $knownParameter2)
    );

    // outputs
    //  array (size=3)
    //      0 => string 'Known' (length=5)
    //      1 => string 'Parameter' (length=9)
    //      2 =>  object(MyApp\DAL\Concrete\Eloquent\Models\Account)
    //  array (size=3)
    //      0 => string 'Known' (length=5)
    //      1 => string 'Parameter' (length=9)
    //      2 =>  object(MyApp\DAL\Concrete\Eloquent\Models\Account)
    //  boolean true
```

###Resolver and class methods:

```php
<?php
    use App\System\Security\Abstractions\ISecurityUser;

    class SomeClass
    {
        public function methodThatNeedsToBeResolved($knownParameter1, $knownParameterN, ISecurityUser $needsToBeResolved = null)
        {
            return func_get_args();
        }
    }

    $resolver = new \LaravelCommode\Resolver\Resolver(); // or app('laravel-commode.resolver');
    $someClass = new SomeClass();

    $knownParameter1 = 'Known';
    $knownParameter2 = 'Parameter';

    $result = $resolver->method($someClass, 'methodThatNeedsToBeResolved', [$knownParameter1, $knownParameter2]);
                //  or ->method(SomeClass::class, ..., ...) for calling static method or resolving class through
                //                                          app IOC

    $resultClosure = $resolver->methodToClosure($someClass, 'methodThatNeedsToBeResolved');
                //  or ->method(SomeClass::class, ..., ...) for calling static method or resolving class through
                //                                          app IOC

    var_dump(
        $result, $resultClosure($knownParameter1, $knownParameter2),
        $result === $resultClosure($knownParameter1, $knownParameter2)
    );

    // outputs
    //  array (size=3)
    //      0 => string 'Known' (length=5)
    //      1 => string 'Parameter' (length=9)
    //      2 =>  object(MyApp\DAL\Concrete\Eloquent\Models\Account)
    //  array (size=3)
    //      0 => string 'Known' (length=5)
    //      1 => string 'Parameter' (length=9)
    //      2 =>  object(MyApp\DAL\Concrete\Eloquent\Models\Account)
    //  boolean true
```

##<a name="aliases">Alias references table</a>

<table width="100%">
    <thead>
    <tr>
        <th>Class</th>
        <th>Service alias</th>
        <th>Facade</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><code>LaravelCommode\Resolver\Resolver</code></td>
        <td>laravel-commode.resolver</td>
        <td><code>CommodeResolver</code></td>
    </tr>
    </tbody>
</table>