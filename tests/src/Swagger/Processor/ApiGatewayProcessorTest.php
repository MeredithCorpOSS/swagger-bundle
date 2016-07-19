<?php

namespace TimeInc\SwaggerBundle\Tests\Swagger\Processor;

use Swagger\Analysis;
use Swagger\Annotations\Delete;
use Swagger\Annotations\Get;
use Swagger\Annotations\Header;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Patch;
use Swagger\Annotations\Path;
use Swagger\Annotations\Post;
use Swagger\Annotations\Put;
use Swagger\Annotations\Response;
use Swagger\Annotations\SecurityScheme;
use Swagger\Annotations\Swagger;
use TimeInc\SwaggerBundle\Exception\SwaggerException;
use TimeInc\SwaggerBundle\Swagger\Processor\ApiGatewayProcessor;

/**
 * Class ApiGatewayProcessorTest.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class ApiGatewayProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Analysis
     */
    private $analysis;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->analysis = new Analysis();
        $this->analysis->swagger = new Swagger([]);
    }

    /**
     * Test that the host is required.
     */
    public function testRequiresHost()
    {
        $this->setExpectedException(SwaggerException::class, 'Host must be defined');

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);
    }

    /**
     * Test that http is used when https is not available.
     */
    public function testHttp()
    {
        $swagger = $this->analysis->swagger;
        $swagger->host = 'testhost';
        $swagger->basePath = '/v1';
        $swagger->schemes = [
            'http',
        ];
        $swagger->paths = [
            new Path(
                [
                    'path' => '/test',
                    'get' => new Get(
                        [
                            'method' => 'get',
                            'responses' => [
                                new Response(
                                    [
                                        'response' => 200,
                                    ]
                                ),
                                new Response(
                                    [
                                        'response' => 404,
                                    ]
                                ),
                            ],
                        ]
                    ),
                ]
            ),
        ];

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);

        $xVars = $swagger->paths[0]->get->x;

        $this->assertCount(1, $xVars);
        $this->assertArrayHasKey('amazon-apigateway-integration', $xVars);

        $integration = $xVars['amazon-apigateway-integration'];
        $this->assertEquals('http', $integration['type']);
        $this->assertEquals('when_no_templates', $integration['passthroughBehavior']);
        $this->assertEquals('http://testhost/v1/test', $integration['uri']);
        $this->assertCount(2, $integration['responses']);
        $this->assertArrayHasKey('default', $integration['responses']);
        $this->assertArrayHasKey(404, $integration['responses']);
        $this->assertEquals('GET', $integration['httpMethod']);
        $this->assertArrayNotHasKey('requestParameters', $integration);
    }

    /**
     * Test a static path.
     */
    public function testSimplePath()
    {
        $swagger = $this->analysis->swagger;
        $swagger->host = 'testhost';
        $swagger->basePath = '/v1';
        $swagger->schemes = [
            'https',
            'http',
        ];
        $swagger->paths = [
            new Path(
                [
                    'path' => '/test',
                    'get' => new Get(
                        [
                            'method' => 'get',
                            'responses' => [
                                new Response(
                                    [
                                        'response' => 200,
                                        'headers' => [
                                            new Header(
                                                [
                                                    'header' => 'X-EXAMPLE',
                                                    'type' => 'string',
                                                ]
                                            ),
                                        ],
                                    ]
                                ),
                                new Response(
                                    [
                                        'response' => 404,
                                    ]
                                ),
                            ],
                        ]
                    ),
                ]
            ),
        ];

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);

        $xVars = $swagger->paths[0]->get->x;

        $this->assertCount(1, $xVars);
        $this->assertArrayHasKey('amazon-apigateway-integration', $xVars);

        $integration = $xVars['amazon-apigateway-integration'];
        $this->assertEquals('http', $integration['type']);
        $this->assertEquals('when_no_templates', $integration['passthroughBehavior']);
        $this->assertEquals('https://testhost/v1/test', $integration['uri']);
        $this->assertEquals('GET', $integration['httpMethod']);
        $this->assertCount(2, $integration['responses']);
        $this->assertArrayHasKey('default', $integration['responses']);
        $this->assertArrayHasKey(404, $integration['responses']);

        $this->assertArrayHasKey('responseParameters', $integration['responses']['default']);
        $this->assertArrayHasKey(
            'method.response.header.X-EXAMPLE',
            $integration['responses']['default']['responseParameters']
        );
        $this->assertEquals(
            'integration.response.header.X-EXAMPLE',
            $integration['responses']['default']['responseParameters']['method.response.header.X-EXAMPLE']
        );

        $this->assertArrayNotHasKey('requestParameters', $integration);
    }

    /**
     * Tests path with parameters.
     */
    public function testParameterisedPath()
    {
        $swagger = $this->analysis->swagger;
        $swagger->host = 'testhost';
        $swagger->basePath = '/v1';
        $swagger->schemes = [
            'https',
            'http',
        ];
        $swagger->paths = [
            new Path(
                [
                    'path' => '/test/{entity}/{id}',
                    'get' => new Get(
                        [
                            'parameters' => [
                                'entity' => new Parameter(
                                    [
                                        'parameter' => 'entity',
                                        'name' => 'entity',
                                        'in' => 'path',
                                    ]
                                ),
                            ],
                            'method' => 'get',
                            'responses' => [
                                new Response(
                                    [
                                        'response' => 200,
                                    ]
                                ),
                            ],
                        ]
                    ),
                    'parameters' => [
                        'id' => new Parameter(
                            [
                                'parameter' => 'id',
                                'name' => 'id',
                                'in' => 'path',
                            ]
                        ),
                        'query' => new Parameter(
                            [
                                'parameter' => 'query',
                                'name' => 'query',
                                'in' => 'query',
                            ]
                        ),
                    ],
                ]
            ),
        ];

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);

        $xVars = $swagger->paths[0]->get->x;

        $this->assertCount(1, $xVars);
        $this->assertArrayHasKey('amazon-apigateway-integration', $xVars);

        $integration = $xVars['amazon-apigateway-integration'];
        $this->assertEquals('http', $integration['type']);
        $this->assertEquals('when_no_templates', $integration['passthroughBehavior']);
        $this->assertEquals('https://testhost/v1/test/{entity}/{id}', $integration['uri']);
        $this->assertEquals('GET', $integration['httpMethod']);
        $this->assertArrayHasKey('requestParameters', $integration);
        $this->assertCount(3, $integration['requestParameters']);
        $this->assertSame(
            [
                'integration.request.path.entity' => 'method.request.path.entity',
                'integration.request.path.id' => 'method.request.path.id',
                'integration.request.querystring.query' => 'method.request.querystring.query',
            ],
            $integration['requestParameters']
        );
        $this->assertNull($swagger->paths[0]->parameters);
    }

    /**
     * Tests path with parameters.
     */
    public function testSecuredPath()
    {
        $swagger = $this->analysis->swagger;
        $swagger->host = 'testhost';
        $swagger->basePath = '/v1';
        $swagger->schemes = [
            'https',
            'http',
        ];
        $swagger->paths = [
            new Path(
                [
                    'path' => '/test',
                    'get' => new Get(
                        [
                            'method' => 'get',
                            'responses' => [
                                new Response(
                                    [
                                        'response' => 200,
                                    ]
                                ),
                            ],
                            'security' => ['api_token' => []],
                        ]
                    ),
                ]
            ),
        ];
        $swagger->securityDefinitions = [
            new SecurityScheme(
                [
                    'securityDefinition' => 'api_token',
                    'type' => 'apiKey',
                    'name' => 'X-AUTH-TOKEN',
                    'in' => 'header',
                ]
            ),
        ];
        $swagger->security = [
            ['api_token' => []],
        ];

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);

        $xVars = $swagger->paths[0]->get->x;

        $this->assertCount(1, $xVars);
        $this->assertArrayHasKey('amazon-apigateway-integration', $xVars);

        $integration = $xVars['amazon-apigateway-integration'];
        $this->assertEquals('http', $integration['type']);
        $this->assertEquals('when_no_templates', $integration['passthroughBehavior']);
        $this->assertEquals('https://testhost/v1/test', $integration['uri']);
        $this->assertEquals('GET', $integration['httpMethod']);

        $this->assertArrayHasKey('requestParameters', $integration);
        $this->assertCount(1, $integration['requestParameters']);
        $this->assertArrayHasKey('integration.request.header.X-AUTH-TOKEN', $integration['requestParameters']);
        $this->assertEquals(
            'method.request.header.X-AUTH-TOKEN',
            $integration['requestParameters']['integration.request.header.X-AUTH-TOKEN']
        );
        $this->assertNull($swagger->paths[0]->parameters);
    }

    /**
     * Tests PUT path.
     */
    public function testPutPath()
    {
        $swagger = $this->analysis->swagger;
        $swagger->host = 'testhost';
        $swagger->basePath = '/v1';
        $swagger->schemes = [
            'https',
            'http',
        ];
        $swagger->paths = [
            new Path(
                [
                    'path' => '/test',
                    'put' => new Put(
                        [
                            'method' => 'put',
                            'responses' => [
                                new Response(
                                    [
                                        'response' => 204,
                                    ]
                                ),
                            ],
                        ]
                    ),
                ]
            ),
        ];

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);

        $xVars = $swagger->paths[0]->put->x;

        $this->assertCount(1, $xVars);
        $this->assertArrayHasKey('amazon-apigateway-integration', $xVars);

        $integration = $xVars['amazon-apigateway-integration'];
        $this->assertEquals('http', $integration['type']);
        $this->assertEquals('when_no_templates', $integration['passthroughBehavior']);
        $this->assertEquals('https://testhost/v1/test', $integration['uri']);
        $this->assertEquals('PUT', $integration['httpMethod']);

        $this->assertArrayNotHasKey('requestParameters', $integration);
    }

    /**
     * Tests PATCH path.
     */
    public function testPatchPath()
    {
        $swagger = $this->analysis->swagger;
        $swagger->host = 'testhost';
        $swagger->basePath = '/v1';
        $swagger->schemes = [
            'https',
            'http',
        ];
        $swagger->paths = [
            new Path(
                [
                    'path' => '/test',
                    'patch' => new Patch(
                        [
                            'method' => 'patch',
                            'responses' => [
                                new Response(
                                    [
                                        'response' => 204,
                                    ]
                                ),
                            ],
                        ]
                    ),
                ]
            ),
        ];

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);

        $xVars = $swagger->paths[0]->patch->x;

        $this->assertCount(1, $xVars);
        $this->assertArrayHasKey('amazon-apigateway-integration', $xVars);

        $integration = $xVars['amazon-apigateway-integration'];
        $this->assertEquals('http', $integration['type']);
        $this->assertEquals('when_no_templates', $integration['passthroughBehavior']);
        $this->assertEquals('https://testhost/v1/test', $integration['uri']);
        $this->assertEquals('PATCH', $integration['httpMethod']);

        $this->assertArrayNotHasKey('requestParameters', $integration);
    }

    /**
     * Tests POST path.
     */
    public function testPostPath()
    {
        $swagger = $this->analysis->swagger;
        $swagger->host = 'testhost';
        $swagger->basePath = '/v1';
        $swagger->schemes = [
            'https',
            'http',
        ];
        $swagger->paths = [
            new Path(
                [
                    'path' => '/test',
                    'post' => new Post(
                        [
                            'method' => 'post',
                            'responses' => [
                                new Response(
                                    [
                                        'response' => 204,
                                    ]
                                ),
                            ],
                            'parameters' => [
                                'body' => new Parameter(
                                    [
                                        'parameter' => 'body',
                                        'name' => 'body',
                                        'in' => 'body',
                                    ]
                                ),
                            ],
                        ]
                    ),
                ]
            ),
        ];

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);

        $xVars = $swagger->paths[0]->post->x;

        $this->assertCount(1, $xVars);
        $this->assertArrayHasKey('amazon-apigateway-integration', $xVars);

        $integration = $xVars['amazon-apigateway-integration'];
        $this->assertEquals('http', $integration['type']);
        $this->assertEquals('when_no_templates', $integration['passthroughBehavior']);
        $this->assertEquals('https://testhost/v1/test', $integration['uri']);
        $this->assertEquals('POST', $integration['httpMethod']);

        $this->assertArrayNotHasKey('requestParameters', $integration);
    }

    /**
     * Tests DELETE path.
     */
    public function testDeletePath()
    {
        $swagger = $this->analysis->swagger;
        $swagger->host = 'testhost';
        $swagger->basePath = '/v1';
        $swagger->schemes = [
            'https',
            'http',
        ];
        $swagger->paths = [
            new Path(
                [
                    'path' => '/test',
                    'delete' => new Delete(
                        [
                            'method' => 'delete',
                            'responses' => [
                                new Response(
                                    [
                                        'response' => 204,
                                    ]
                                ),
                            ],
                        ]
                    ),
                ]
            ),
        ];

        $processor = new ApiGatewayProcessor();
        $processor($this->analysis);

        $xVars = $swagger->paths[0]->delete->x;

        $this->assertCount(1, $xVars);
        $this->assertArrayHasKey('amazon-apigateway-integration', $xVars);

        $integration = $xVars['amazon-apigateway-integration'];
        $this->assertEquals('http', $integration['type']);
        $this->assertEquals('when_no_templates', $integration['passthroughBehavior']);
        $this->assertEquals('https://testhost/v1/test', $integration['uri']);
        $this->assertEquals('DELETE', $integration['httpMethod']);

        $this->assertArrayNotHasKey('requestParameters', $integration);
    }
}
