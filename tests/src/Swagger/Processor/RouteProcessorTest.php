<?php

namespace TimeInc\SwaggerBundle\Tests\Swagger\Processor;

use Swagger\Analysis;
use Swagger\Annotations\Delete;
use Swagger\Annotations\Get;
use Swagger\Annotations\Patch;
use Swagger\Annotations\Path;
use Swagger\Annotations\Post;
use Swagger\Annotations\Put;
use Swagger\Annotations\Response;
use Swagger\Annotations\Swagger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use TimeInc\SwaggerBundle\Exception\SwaggerException;
use TimeInc\SwaggerBundle\Swagger\Annotation\Route;
use TimeInc\SwaggerBundle\Swagger\Processor\RouteProcessor;
use TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\Controller\FoodController;
use TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\Entity\Food;

/**
 * Class RouteProcessorTest.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class RouteProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Router|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var Analysis
     */
    private $analysis;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->router = $this->getMockBuilder(Router::class)
                             ->disableOriginalConstructor()
                             ->setMethods(['getRouteCollection'])
                             ->getMock();

        $this->routeCollection = new RouteCollection();
        $this->routeCollection->add('get_foods', new SymfonyRoute('/foods', [], [], [], '', [], ['GET']));
        $this->routeCollection->add('get_food', new SymfonyRoute('/food/{id}', [], [], [], '', [], ['GET']));
        $this->routeCollection->add('post_food', new SymfonyRoute('/food', [], [], [], '', [], ['POST']));
        $this->routeCollection->add('put_food', new SymfonyRoute('/food/{id}', [], [], [], '', [], ['PUT']));
        $this->routeCollection->add('patch_food', new SymfonyRoute('/food/{id}', [], [], [], '', [], ['PATCH']));
        $this->routeCollection->add('delete_food', new SymfonyRoute('/food/{id}', [], [], [], '', [], ['DELETE']));

        $this->analysis = new Analysis();
        $this->analysis->swagger = new Swagger([]);
    }

    public function processNewPathDataProvider()
    {
        return [
            [
                ['get' => ['get_foods', 'getFoods', Route::RETURNS_ENTITY]],
            ],
            [
                ['get' => ['get_food', 'getFood', Route::RETURNS_COLLECTION]],
            ],
            [
                ['get' => ['get_food', 'getFood', Route::RETURNS_ENTITY]],
            ],
            [
                ['post' => ['post_food', 'postFood', Route::RETURNS_ENTITY]],
            ],
            [
                ['put' => ['put_food', 'putFood', Route::RETURNS_ENTITY]],
            ],
            [
                ['patch' => ['patch_food', 'patchFood', Route::RETURNS_ENTITY]],
            ],
            [
                ['delete' => ['delete_food', 'deleteFood', Route::RETURNS_ENTITY]],
            ],
            [
                ['get' => ['get_foods', 'getFoods', Route::RETURNS_ENTITY]],
                ['get' => ['get_food', 'getFood', Route::RETURNS_COLLECTION]],
                ['get' => ['get_food', 'getFood', Route::RETURNS_ENTITY]],
                ['post' => ['post_food', 'postFood', Route::RETURNS_ENTITY]],
                ['put' => ['put_food', 'putFood', Route::RETURNS_ENTITY]],
                ['patch' => ['patch_food', 'patchFood', Route::RETURNS_ENTITY]],
                ['delete' => ['delete_food', 'deleteFood', Route::RETURNS_ENTITY]],
            ],
        ];
    }

    /**
     * Test the processor can be constructed.
     */
    public function testContstructor()
    {
        $this->router;
        $controllerOptions = [
            $this->createControllerOption(
                'test_route',
                'Test\TestController',
                'getTest',
                'Entity\Test',
                Route::RETURNS_ENTITY
            ),
        ];

        $processor = new RouteProcessor($this->router, $controllerOptions);
    }

    /**
     * Test adding a new GET path to an empty swagger instance.
     *
     * @dataProvider processNewPathDataProvider
     *
     * @param array $routes
     */
    public function testProcessNewSinglePath(array $routes)
    {
        $this->router->expects($this->once())
                     ->method('getRouteCollection')
                     ->willReturn($this->routeCollection);

        $controllerOptions = [];
        foreach ($routes as $type => $options) {
            list($route, $method, $returns) = $options;
            $controllerOptions[] = $this->createControllerOption(
                $route,
                FoodController::class,
                $method,
                Food::class,
                $returns
            );
        }

        $processor = new RouteProcessor($this->router, $controllerOptions);

        $this->assertCount(0, $this->analysis->swagger->paths);
        $processor($this->analysis);

        $this->assertCount(count($routes), $this->analysis->swagger->paths);

        /** @var Path $path */
        $path = end($this->analysis->swagger->paths);
        $this->assertInstanceOf(Path::class, $path);

        foreach ($routes as $type => $options) {
            list($routeName, $method, $returns) = $options;
            $route = $this->routeCollection->get($routeName);
            switch ($type) {
                case 'get':
                    $this->assertInstanceOf(Get::class, $path->get);
                    $this->assertEquals($route->getPath(), $path->path);

                    $this->assertEquals('get', $path->get->method);
                    if ($returns == Route::RETURNS_ENTITY) {
                        $this->assertEquals('Fetch a Food entity', $path->get->summary);
                    } else {
                        $this->assertEquals('Fetch a collection of Food entities', $path->get->summary);
                    }
                    $this->assertCount(2, $path->get->responses);
                    /** @var Response $response */
                    foreach ($path->get->responses as $response) {
                        switch ($response->response) {
                            case 200:
                                if ($returns == Route::RETURNS_ENTITY) {
                                    $this->assertEquals('Entity Found and Returned', $response->description);
                                    $this->assertTrue(isset($response->schema['$ref']));
                                    $this->assertEquals('#/definitions/Food', $response->schema['$ref']);
                                } else {
                                    $this->assertEquals('Entity Collection Found and Returned', $response->description);
                                    $this->assertEquals('array', $response->schema['type']);
                                    $this->assertEquals('#/definitions/Food', $response->schema['items']['$ref']);
                                }
                                break;

                            case 404:
                                $this->assertEquals('Entity Not Found', $response->description);
                                break;

                            default:
                                $this->fail('Unknown response code "'.$response->response.'"');
                                break;
                        }
                    }
                    break;

                case 'post':
                    $this->assertInstanceOf(Post::class, $path->post);
                    $this->assertEquals($route->getPath(), $path->path);

                    $this->assertEquals('post', $path->post->method);
                    $this->assertEquals('Create a Food entity', $path->post->summary);
                    $this->assertCount(2, $path->post->responses);
                    /** @var Response $response */
                    foreach ($path->post->responses as $response) {
                        switch ($response->response) {
                            case 201:
                                $this->assertEquals('Entity Created', $response->description);
                                break;

                            case 405:
                                $this->assertEquals('Validation Exception', $response->description);
                                break;

                            default:
                                $this->fail('Unknown response code "'.$response->response.'"');
                                break;
                        }
                    }
                    break;

                case 'put':
                    $this->assertInstanceOf(Put::class, $path->put);
                    $this->assertEquals($route->getPath(), $path->path);

                    $this->assertEquals('put', $path->put->method);
                    $this->assertEquals('Edit a Food entity', $path->put->summary);
                    $this->assertCount(4, $path->put->responses);
                    /** @var Response $response */
                    foreach ($path->put->responses as $response) {
                        switch ($response->response) {
                            case 204:
                                $this->assertEquals('Entity Updated', $response->description);
                                break;

                            case 400:
                                $this->assertEquals('Invalid ID', $response->description);
                                break;

                            case 404:
                                $this->assertEquals('Entity Not Found', $response->description);
                                break;

                            case 405:
                                $this->assertEquals('Validation Exception', $response->description);
                                break;

                            default:
                                $this->fail('Unknown response code "'.$response->response.'"');
                                break;
                        }
                    }
                    break;

                case 'patch':
                    $this->assertInstanceOf(Patch::class, $path->patch);
                    $this->assertEquals($route->getPath(), $path->path);

                    $this->assertEquals('patch', $path->patch->method);
                    $this->assertEquals('Edit fields of a Food entity', $path->patch->summary);
                    $this->assertCount(4, $path->patch->responses);
                    /** @var Response $response */
                    foreach ($path->patch->responses as $response) {
                        switch ($response->response) {
                            case 204:
                                $this->assertEquals('Entity Updated', $response->description);
                                break;

                            case 400:
                                $this->assertEquals('Invalid ID', $response->description);
                                break;

                            case 404:
                                $this->assertEquals('Entity Not Found', $response->description);
                                break;

                            case 405:
                                $this->assertEquals('Validation Exception', $response->description);
                                break;

                            default:
                                $this->fail('Unknown response code "'.$response->response.'"');
                                break;
                        }
                    }
                    break;

                case 'delete':
                    $this->assertInstanceOf(Delete::class, $path->delete);
                    $this->assertEquals($route->getPath(), $path->path);

                    $this->assertEquals('delete', $path->delete->method);
                    $this->assertEquals('Delete a Food entity', $path->delete->summary);
                    $this->assertCount(3, $path->delete->responses);
                    /** @var Response $response */
                    foreach ($path->delete->responses as $response) {
                        switch ($response->response) {
                            case 204:
                                $this->assertEquals('Entity Deleted', $response->description);
                                break;

                            case 400:
                                $this->assertEquals('Invalid ID', $response->description);
                                break;

                            case 404:
                                $this->assertEquals('Entity Not Found', $response->description);
                                break;

                            default:
                                $this->fail('Unknown response code "'.$response->response.'"');
                                break;
                        }
                    }
                    break;
            }
        }
    }

    public function testRouteNotFoundException()
    {
        $this->setExpectedException(SwaggerException::class);

        $this->router->expects($this->once())
                     ->method('getRouteCollection')
                     ->willReturn(new RouteCollection());

        $controllerOptions = [
            $this->createControllerOption('test', 'test', 'test', 'test', 'test'),
        ];

        $processor = new RouteProcessor($this->router, $controllerOptions);

        $processor($this->analysis);
    }

    /**
     * @param string $route
     * @param string $controller
     * @param string $method
     * @param string $entity
     * @param string $returns
     *
     * @return array
     */
    private function createControllerOption($route, $controller, $method, $entity, $returns)
    {
        $entityParts = explode('\\', $entity);
        $option = new Route();
        $option->route = $route;
        $option->controller = $controller;
        $option->method = $method;
        $option->entity = $entity;
        $option->entity_name = array_pop($entityParts);
        $option->returns = $returns;

        return (array) $option;
    }
}
