<?php

namespace TimeInc\SwaggerBundle\Swagger\Processor;

use Swagger\Analysis;
use Swagger\Annotations\Delete;
use Swagger\Annotations\Get;
use Swagger\Annotations\Operation;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Patch;
use Swagger\Annotations\Path;
use Swagger\Annotations\Post;
use Swagger\Annotations\Put;
use Swagger\Annotations\Response;
use Symfony\Component\Routing\RouterInterface;
use TimeInc\SwaggerBundle\Exception\SwaggerException;
use TimeInc\SwaggerBundle\Swagger\Annotation\Route;

/**
 * Class RouteProcessor.
 *
 * @author andy.thorne@timeinc.com
 */
class RouteProcessor
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $controllerOptions;

    /**
     * FosRestProcessor constructor.
     *
     * @param RouterInterface $router
     * @param array           $controllerOptions
     */
    public function __construct(RouterInterface $router, array $controllerOptions)
    {
        $this->router = $router;
        $this->controllerOptions = $controllerOptions;
    }

    /**
     * Inject the OpenAPI schema as defined in the config.yml.
     *
     * @param Analysis $analysis
     *
     * @throws SwaggerException
     */
    public function __invoke(Analysis $analysis)
    {
        $swagger = $analysis->swagger;
        $routes = $this->router->getRouteCollection();

        foreach ($this->controllerOptions as $options) {
            $route = $routes->get($options['route']);

            if (!$route instanceof \Symfony\Component\Routing\Route) {
                throw new SwaggerException(
                    'Route "'.$options['route'].'" does not exist (Defined in '.$options['controller'].'::'.$options['method'].').'
                );
            }

            if (!$swagger->paths) {
                $swagger->paths = [];
            }
            $path = null;

            // Check if the path is already defined
            foreach ($swagger->paths as $definedPath) {
                if ($definedPath->path == $route->getPath()) {
                    $path = $definedPath;
                }
            }

            // Create a path annotation if it doesn't exist already
            if (!$path instanceof Path) {
                $path = new Path(
                    [
                        'path' => $route->getPath(),
                    ]
                );
                $swagger->paths[] = $path;
            }

            // Map route variables into swagger parameters. Use PHP type hinting to try and determine
            // what data type it is.
            $compiledRoute = $route->compile();
            $reflectionMethod = new \ReflectionMethod($options['controller'], $options['method']);
            $methodParameters = $reflectionMethod->getParameters();

            foreach ($compiledRoute->getPathVariables() as $routeVariable) {
                // don't add the parameter if it's already defined
                if (isset($path->parameters[$routeVariable])) {
                    break;
                }
                $parameter = new Parameter(
                    [
                        'parameter' => $routeVariable,
                        'name' => $routeVariable,
                        'type' => 'string',
                        'in' => 'path',
                    ]
                );
                foreach ($methodParameters as $methodParameter) {
                    if ($routeVariable == $methodParameter->getName()) {
                        if (method_exists($methodParameter, 'hasType') && $methodParameter->hasType()) {
                            list($parameter->type, $parameter->format) = $this->mapType(
                                (string) $methodParameter->getType()
                            );
                            $parameter->required = !$methodParameter->isDefaultValueAvailable();
                            if (!$parameter->required) {
                                $parameter->default = gettype($methodParameter->getDefaultValue());
                            }
                        }
                        break;
                    }
                }
                $path->parameters[$routeVariable] = $parameter;
            }

            // query parameters
            foreach ($route->getDefaults() as $defaultKey => $defaultValue) {

                // ignore prefixed _ vars
                if (strpos($defaultKey, '_') === 0) {
                    continue;
                }

                if (!isset($path->parameters[$defaultKey])) {
                    $parameter = new Parameter(
                        [
                            'parameter' => $defaultKey,
                            'name' => $defaultKey,
                            'type' => $defaultValue ? gettype($defaultValue) : 'string',
                            'in' => 'query',
                            'default' => $defaultValue,
                        ]
                    );
                    $path->parameters[$defaultKey] = $parameter;
                }
            }

            // add annotation query params
            foreach ($options['queryParams'] as $queryKey => $queryDataType) {
                if (!isset($path->parameters[$queryKey])) {
                    $parameterData = [
                        'parameter' => $queryKey,
                        'name' => $queryKey,
                        'type' => $queryDataType,
                        'in' => 'query',
                    ];
                    if ($queryDataType == 'array') {
                        $parameterData['items'] = 'string';
                    }
                    $parameter = new Parameter($parameterData);
                    $path->parameters[$queryKey] = $parameter;
                }
            }

            // add annotation headers
            foreach ($options['headers'] as $headerKey => $headerDataType) {
                if (!isset($path->parameters[$headerKey])) {
                    $parameter = new Parameter(
                        [
                            'parameter' => $headerKey,
                            'name' => $headerKey,
                            'type' => $headerDataType,
                            'in' => 'header',
                        ]
                    );
                    $path->parameters[$headerKey] = $parameter;
                }
            }

            foreach ($route->getMethods() as $method) {
                $method = strtolower($method);

                switch ($method) {
                    case 'get':
                        if (!$path->get instanceof Get) {
                            $path->get = new Get([]);
                        }
                        $this->configureGet($path->get, $options);
                        break;
                    case 'post':
                        if (!$path->post instanceof Post) {
                            $path->post = new Post([]);
                        }
                        $this->configurePost($path->post, $options);
                        break;
                    case 'patch':
                        if (!$path->patch instanceof Patch) {
                            $path->patch = new Patch([]);
                        }
                        $this->configurePatch($path->patch, $options);
                        break;
                    case 'put':
                        if (!$path->put instanceof Put) {
                            $path->put = new Put([]);
                        }
                        $this->configurePut($path->put, $options);
                        break;
                    case 'delete':
                        if (!$path->delete instanceof Delete) {
                            $path->delete = new Delete([]);
                        }
                        $this->configureDelete($path->delete, $options);
                        break;
                }
            }
        }
    }

    /**
     * Configure a Get annotation with FosRest data.
     *
     * @param Get   $operation
     * @param array $options
     */
    private function configureGet(Get $operation, array $options)
    {
        if (!$operation->summary) {
            if ($options['returns'] == Route::RETURNS_ENTITY) {
                $operation->summary = 'Fetch a '.$options['entity_name'].' entity';
            } else {
                $operation->summary = 'Fetch a collection of '.$options['entity_name'].' entities';
            }
        }

        // verify that the x-collection property is set
        if ($options['returns'] == Route::RETURNS_COLLECTION) {
            $schema = [
                'type' => 'array',
                'items' => [
                    '$ref' => '#/definitions/'.$options['entity_name'],
                ],
            ];
        } else {
            $schema = [
                '$ref' => '#/definitions/'.$options['entity_name'],
            ];
        }

        if ($options['returns'] == Route::RETURNS_ENTITY) {
            $successText = 'Entity Found and Returned';
        } else {
            $successText = 'Entity Collection Found and Returned';
        }

        $this->appendResponses(
            $operation,
            [
                new Response(
                    [
                        'response' => 200,
                        'description' => $successText,
                        'schema' => $schema,
                    ]
                ),
                new Response(
                    [
                        'response' => 404,
                        'description' => 'Entity Not Found',
                    ]
                ),
            ]
        );
    }

    /**
     * Configure a Post annotation with FosRest data.
     *
     * @param Post  $operation
     * @param array $options
     */
    private function configurePost(Post $operation, array $options)
    {
        if (!$operation->summary) {
            $operation->summary = 'Create a '.$options['entity_name'].' entity';
        }

        if (!isset($operation->parameters['body'])) {
            $operation->parameters[] = new Parameter(
                [
                    'parameter' => 'body',
                    'in' => 'body',
                    'name' => 'body',
                    'required' => true,
                    'schema' => [
                        '$ref' => '#/definitions/'.$options['entity_name'],
                    ],
                ]
            );
        }

        $this->appendResponses(
            $operation,
            [
                new Response(
                    [
                        'response' => 201,
                        'description' => 'Entity Created',
                    ]
                ),
                new Response(
                    [
                        'response' => 400,
                        'description' => 'Validation Exception',
                    ]
                ),
            ]
        );
    }

    /**
     * Configure a Put annotation with FosRest data.
     *
     * @param Put   $operation
     * @param array $options
     */
    private function configurePut(Put $operation, array $options)
    {
        if (!$operation->summary) {
            $operation->summary = 'Edit a '.$options['entity_name'].' entity';
        }

        if (!isset($operation->parameters['body'])) {
            $operation->parameters[] = new Parameter(
                [
                    'parameter' => 'body',
                    'in' => 'body',
                    'name' => 'body',
                    'required' => true,
                    'schema' => [
                        '$ref' => '#/definitions/'.$options['entity_name'],
                    ],
                ]
            );
        }

        $this->appendResponses(
            $operation,
            [
                new Response(
                    [
                        'response' => 204,
                        'description' => 'Entity Updated',
                    ]
                ),
                new Response(
                    [
                        'response' => 400,
                        'description' => 'Validation Exception',
                    ]
                ),
                new Response(
                    [
                        'response' => 404,
                        'description' => 'Entity Not Found',
                    ]
                ),
            ]
        );
    }

    /**
     * Configure a Patch annotation with FosRest data.
     *
     * @param Patch $operation
     * @param array $options
     */
    private function configurePatch(Patch $operation, array $options)
    {
        if (!$operation->summary) {
            $operation->summary = 'Edit fields of a '.$options['entity_name'].' entity';
        }

        if (!isset($operation->parameters['body'])) {
            $operation->parameters[] = new Parameter(
                [
                    'parameter' => 'body',
                    'in' => 'body',
                    'name' => 'body',
                    'required' => true,
                    'schema' => [
                        '$ref' => '#/definitions/'.$options['entity_name'],
                    ],
                ]
            );
        }

        $this->appendResponses(
            $operation,
            [
                new Response(
                    [
                        'response' => 204,
                        'description' => 'Entity Updated',
                    ]
                ),
                new Response(
                    [
                        'response' => 400,
                        'description' => 'Validation Exception',
                    ]
                ),
                new Response(
                    [
                        'response' => 404,
                        'description' => 'Entity Not Found',
                    ]
                ),
            ]
        );
    }

    /**
     * Configure a Delete annotation with FosRest data.
     *
     * @param Delete $operation
     * @param array  $options
     */
    private function configureDelete(Delete $operation, array $options)
    {
        if (!$operation->summary) {
            $operation->summary = 'Delete a '.$options['entity_name'].' entity';
        }
        $this->appendResponses(
            $operation,
            [
                new Response(
                    [
                        'response' => 204,
                        'description' => 'Entity Deleted',
                    ]
                ),
                new Response(
                    [
                        'response' => 400,
                        'description' => 'Validation Exception',
                    ]
                ),
                new Response(
                    [
                        'response' => 404,
                        'description' => 'Entity Not Found',
                    ]
                ),
            ]
        );
    }

    /**
     * Map a PHP type to a swagger type and format.
     *
     * @param string $type
     *
     * @return array
     */
    private function mapType($type)
    {
        switch ($type) {
            case 'int':
                return ['number', 'integer'];
                break;
            case 'string':
                return ['string', null];
                break;
        }

        return ['string', null];
    }

    /**
     * Append responses onto an operation, checking first if they are already configured.
     *
     * @param Operation $operation
     * @param array     $newResponses
     */
    private function appendResponses(Operation $operation, array $newResponses)
    {
        foreach ($newResponses as $newResponse) {
            if (!$operation->responses) {
                $operation->responses = [];
            }

            $hasResponse = false;

            /** @var Response $response */
            foreach ($operation->responses as $response) {
                // check if one has already been set. If so, skip it.
                if ($response->response == $newResponse->response) {
                    $hasResponse = true;
                }
            }

            if (!$hasResponse) {
                $operation->responses[] = $newResponse;
            }
        }
    }
}
