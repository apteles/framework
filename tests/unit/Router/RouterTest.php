<?php
namespace ApTeles\Tests\Router;

use Exception;
use RuntimeException;
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
    public function testItCanAddRoutes()
    {
        $this->router->add('get', '/users/edit/(\d+)', function () {
            print 'foo';
        });

        $this->router->run('get', '/users/edit/10');

        $this->assertCount(1, $this->router->getRoutes());
    }

    public function testItShouldReturnArrayWithCallableInvokerAndParams()
    {
        $this->router->group('/admin', function (Router $route) {
            $route->add('GET', '/manager/users/(\d+)', function ($p) {
                return 'foo ' . $p;
            });
        });

        $result = $this->router->run('get', '/admin/manager/users/10');
        $this->assertArrayHasKey('invoker', $result);
        $this->assertArrayHasKey('action', $result);
        $this->assertArrayHasKey('params', $result);
    }

    public function testItShouldGroupRoute()
    {
        $this->router->group('/admin', function (Router $route) {
            $route->add('GET', '/manager/users', function () {
                return 'foo';
            });
        });

        $result = $this->router->run('get', '/admin/manager/users');

        $expected = $result['invoker']->call($result['action']);

        $this->assertEquals('foo', $expected);
    }

    public function testItCanCreateRouteAndReceiveParams()
    {
        $this->router->add('GET', '/edit/user/(\d+)', function ($id) {
            return 'foo ' . $id;
        });

        $result = $this->router->run('get', '/edit/user/10');

        $expected = $result['invoker']->call($result['action'], $result['params']);

        $this->assertEquals('foo 10', $expected);
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

        $this->assertArrayHasKey('isValidRoute', $foo);
        $this->assertArrayHasKey('params', $foo);
        $this->assertEmpty($foo['params']);
        $this->assertCount(1, $bar['params']);
        $this->assertEquals("10", $bar['params'][0]);
        $this->assertCount(2, $barFoo);
        $this->assertEquals(["10","20"], $barFoo['params']);
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

        $this->router->add('get', '/', function () {
            return 'foo';
        });
        $this->router->run('get', '/');

        $foo = $getCurrentMethodInRequest->invokeArgs($this->router, []);
        $expected = 'get';

        $this->assertEquals($expected, $foo);

        $this->router->add('post', '/', function () {
            return 'bar';
        });
        $this->router->run('post', '/');

        $foo = $getCurrentMethodInRequest->invokeArgs($this->router, []);
        $expected = 'post';

        $this->assertEquals($expected, $foo);
    }

    public function testItCanRunRoutes()
    {
        $this->router->add('get', '/', function () {
            return 'foo';
        });

        $result = $this->router->run('get', '/');

        $this->assertArrayHasKey('invoker', $result);
        $this->assertArrayHasKey('action', $result);
        $this->assertArrayHasKey('params', $result);

        $this->assertEquals('foo', $result['invoker']->call($result['action'], $result['params']));
    }

    public function testItCanRunRoutePassingArgumentLikeCallable()
    {
        $this->router->add('get', '/', [FooController::class, 'index']);

        $result = $this->router->run('get', '/');

        if (!\method_exists($result['invoker'], 'call')) {
            $result = $result['invoker']($result['action'], null, null, $result['params']);
            $this->assertEquals([10,20], $result);
        }

        $result = $result['invoker']->call($result['action'], $result['params']);

        $this->assertEquals([10,20], $result);
    }

    public function testItCanGetRouteByName()
    {
        $this->router->add('get', '/users/edit/(\d+)/name/(\w+)/group/(\d+)', function () {
            print 'foo';
        }, 'users.some.thing.else.edit');

        $this->router->add('get', '/bar/baz', function () {
            print 'barBaz';
        }, 'bar.baz');

        $result = $this->router->route('users.some.thing.else.edit', [10,'slug', 20]);
        $this->assertEquals('/users/edit/10/name/slug/group/20', $result);

        $result2 = $this->router->route('bar.baz');
        $this->assertEquals('/bar/baz', $result2);
    }

    public function testItCanThrowAnErrorIfRunWithoutDefineRoutes()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Route not defined yet.');

        $this->router->run('GET', '/home');
    }

    public function testItCanDefineMultiplesGroupRoutes()
    {
        $this->router->group('/admin', function (Router $router) {
            $router->add('GET', '/groups', function () {
                return '/admin/groups';
            });
        });

        $this->router->group('/customer', function (Router $router) {
            $router->add('GET', '/cart', function () {
                return '/customer/cart';
            });
        });

        $dispatched = $this->router->run('GET', '/admin/groups');

        $result = $dispatched['invoker']->call($dispatched['action'], $dispatched['params']);

        $this->assertEquals("/admin/groups", $result);

        $dispatched = $this->router->run('GET', '/customer/cart');

        $result = $dispatched['invoker']->call($dispatched['action'], $dispatched['params']);

        $this->assertEquals("/customer/cart", $result);
    }

    public function testItShouldThrowErrorIfRouteDoesNotMatchWithAnyPattern()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route /admin/grouped not found.');

        $this->router->group('/admin', function (Router $router) {
            $router->add('GET', '/groups', function () {
                return '/admin/groups';
            });
        });

        $this->router->run('GET', '/admin/grouped');
    }
}

class FooController
{
    public function index()
    {
        return [10,20];
    }
}
