<?php
declare(strict_types=1);
namespace ApTeles\Tests\Helpers;

use ReflectionClass;

class Helper
{
    public static function turnMethodPublic(string $clasName, string $methodName)
    {
        /** @var Reflector $class */
        $class = new ReflectionClass($clasName);
       
        $method = $class->getMethod($methodName);
        
        $method->setAccessible(true);
        return $method;
    }
}
