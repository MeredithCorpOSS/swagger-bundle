<?php

namespace DrakeRoll\SwaggerBundle\Tests\Swagger\Annotation;

use DrakeRoll\SwaggerBundle\Swagger\Annotation\Route;

/**
 * Class RouteTest.
 *
 * @author Andy Thorne <andy.thorne@DrakeRoll.com>
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorValue()
    {
        $route = new Route([
            'value' => 'test_route'
        ]);

        $this->assertEquals('test_route', $route->route);
    }
    public function testConstructorValueOverride()
    {
        $route = new Route([
            'value' => 'test_route',
            'route' => 'override',
        ]);

        $this->assertEquals('override', $route->route);
    }
}
