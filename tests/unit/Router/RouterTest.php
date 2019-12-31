<?php
namespace ApTeles\Tests\Router;

use ApTeles\Router\Router;
use PHPUnit\Framework\TestCase;
use ApTeles\Tests\Helpers\Helper;

class RouterTest extends TestCase
{
    /**
     *
     * @var Router
     */
    private $router;

    public function setUp(): void
    {
        $this->router = new Router;
    }
    public function testItCanGetURI()
    {
        $this->assertEquals('/', $this->router->uri());
    }

    public function testItCanAddRoutes()
    {
        $this->router->add('get', '/foo', function () {
            print 'foo';
        });

        $this->router->add('get', '/bar', function () {
            print 'bar';
        });

        $this->assertCount(2, $this->router->getRoutes());
    }

    public function testItShouldBeCallable()
    {
        $this->router->add('get', '/foo', function () {
            print 'foo';
        });

        foreach ($this->router->getRoutes() as $route) {
            $this->assertIsCallable($route);
        }
    }

    public function testItShouldConvertUriToValidRegexPattern()
    {
        $transformUriTORegexPattern = Helper::turnMethodPublic(Router::class, 'transformUriTORegexPattern');

        $foo = $transformUriTORegexPattern->invokeArgs($this->router, ['/foo']);
        $bar = $transformUriTORegexPattern->invokeArgs($this->router, ['/bar/(\d+)']);
        $fooBar = $transformUriTORegexPattern->invokeArgs($this->router, ['/bar/(\w+)']);

        $this->assertEquals("/^\/foo$/", $foo);
        $this->assertRegExp($foo, '/foo');
        $this->assertEquals("/^\/bar\/(\d+)$/", $bar);
        $this->assertRegExp($bar, '/bar/10');
        $this->assertEquals("/^\/bar\/(\w+)$/", $fooBar);
        $this->assertRegExp($fooBar, '/bar/slug');
    }

    public function testItCanParseURIRegexAndReturnParams()
    {
        $parseUriRegexPattern = Helper::turnMethodPublic(Router::class, 'parseUriRegexPattern');

        $foo = $parseUriRegexPattern->invokeArgs($this->router, ["/^\/foo$/",'/foo']);
        $bar = $parseUriRegexPattern->invokeArgs($this->router, ["/^\/bar\/(\d+)$/",'/bar/10']);
        $barFoo = $parseUriRegexPattern->invokeArgs($this->router, ["/^\/bar\/(\d+)\/foo\/(\d+)$/",'/bar/10/foo/20']);

        $this->assertEmpty($foo);
        $this->assertCount(1, $bar);
        $this->assertCount(2, $barFoo);
    }

    public function testItCanParseMethod()
    {
        $parseMethod = Helper::turnMethodPublic(Router::class, 'parseMethod');

        $foo = $parseMethod->invokeArgs($this->router, ["get"]);
        $bar = $parseMethod->invokeArgs($this->router, ["GET"]);
        $fooBar = $parseMethod->invokeArgs($this->router, ["gEt"]);
        $expected = 'get';

        $this->assertEquals($expected, $foo);
        $this->assertEquals($expected, $bar);
        $this->assertEquals($expected, $fooBar);
    }

    public function testItShouldBeAbleGetMethodFromCurrentRequest()
    {
        $getCurrentMethodInRequest = Helper::turnMethodPublic(Router::class, 'getCurrentMethodInRequest');

        $foo = $getCurrentMethodInRequest->invokeArgs($this->router, []);
        $bar = $getCurrentMethodInRequest->invokeArgs($this->router, []);
        $fooBar = $getCurrentMethodInRequest->invokeArgs($this->router, []);
        $expected = 'get';

        $this->assertEquals($expected, $foo);
        $this->assertEquals($expected, $bar);
        $this->assertEquals($expected, $fooBar);
    }

    public function testItCanRunRoutes()
    {
        $this->router->add('get', '/', function () {
            return 'foo';
        });

        $result = $this->router->run();

        $this->assertEquals('foo', $result);
    }
}
