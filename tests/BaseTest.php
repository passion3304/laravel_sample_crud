<?php

namespace Backpack\CRUD\Tests;

use Backpack\CRUD\BackpackServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class BaseTest extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            BackpackServiceProvider::class,
        ];
    }
    
     // allow us to run crud panel private/protected methods like `inferFieldTypeFromDbColumnType`
     public function invokeMethod(&$object, $methodName, array $parameters = [])
     {
         $reflection = new \ReflectionClass(get_class($object));
         $method = $reflection->getMethod($methodName);
         $method->setAccessible(true);
 
         return $method->invokeArgs($object, $parameters);
     }
}
