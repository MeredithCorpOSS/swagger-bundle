<?php

namespace TimeInc\SwaggerBundle\Swagger\Processor;

use Swagger\Analysis;
use Swagger\Annotations\Header;
use Swagger\Annotations\Operation;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Path;
use Swagger\Annotations\Response;
use TimeInc\SwaggerBundle\Exception\SwaggerException;

/**
 * Class ApiGatewayProcessor.
 *
 * @author andy.thorne@timeinc.com
 */
class ApiGatewayProcessor
{
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

        if (!$swagger->host) {
            throw new SwaggerException('Host must be defined');
        }

        $endpointBase = $swagger->host;
        if ($swagger->basePath) {
            $endpointBase .= $swagger->basePath;
        }
        if ($swagger->schemes) {
            if (in_array('https', $swagger->schemes)) {
                $endpointBase = 'https://'.$endpointBase;
            } else {
                $endpointBase = 'http://'.$endpointBase;
            }
        }

        $swagger->host = null;
        $swagger->basePath = null;
        $swagger->schemes = ['https'];

        $securityParameters = [];
        if ($swagger->securityDefinitions) {
            foreach ($swagger->securityDefinitions as $securityDefinition) {
                if ($securityDefinition->in == 'header') {
                    $securityParameters[$securityDefinition->securityDefinition] = new Parameter(
                        [
                            'type' => 'string',
                            'name' => $securityDefinition->name,
                            'in' => 'header',
                            'required' => true,
                        ]
                    );
                }
            }
        }

        $globalSecurityParameters = [];
        // check if there is a global security option
        if ($swagger->security) {
            foreach ($swagger->security as $securityOptions) {
                foreach ($securityParameters as $securityParameter => $securityDefinition) {
                    if (isset($securityOptions[$securityParameter])) {
                        $globalSecurityParameters[$securityParameter] = $securityDefinition;
                        unset($securityParameters[$securityParameter]);
                    }
                }
            }
        }

        foreach ($swagger->paths as $path) {

            // API Gateway likes parameters defined for every operation rather than on the paths
            $pathParameters = $path->parameters;
            $path->parameters = null;

            $operations = ['get', 'post', 'put', 'patch', 'delete'];
            foreach ($operations as $operationKey) {
                $operation = $path->{$operationKey};
                if ($operation instanceof Operation) {

                    if ($pathParameters) {
                        foreach ($pathParameters as $pathParameter) {
                            $add = true;
                            if ($operation->parameters) {
                                foreach ($operation->parameters as $opParameter) {
                                    if ($opParameter->parameter == $pathParameter->parameter) {
                                        $add = false;
                                    }
                                }
                            }

                            if ($add) {
                                $operation->parameters[] = $pathParameter;
                            }
                        }
                    }

                    // add global security parameters to each method
                    foreach ($globalSecurityParameters as $globalSecurityParameterKey => $globalSecurityParameter) {
                        $operation->parameters[] = $globalSecurityParameter;
                    }

                    if ($operation->security) {
                        foreach ($operation->security as $securityKey => $securityOptions) {
                            if (isset($securityParameters[$securityKey])) {
                                $operation->parameters[] = $securityParameters[$securityKey];
                            }
                        }
                    }

                    $this->createIntegration($path, $operation, $endpointBase);
                }
            }
        }
    }

    protected function createIntegration(Path $path, Operation $operation, $endpointBase)
    {
        $config = [
            'type' => 'http',
            'passthroughBehavior' => 'when_no_templates',
            'uri' => $endpointBase.$path->path,
            'httpMethod' => strtoupper($operation->method),
            'responses' => [],
            'requestParameters' => [],
            'responseParameters' => [],
        ];

        $defaultCode = 200;
        switch ($operation->method) {
            case 'get':
                $defaultCode = 200;
                break;
            case 'post':
                $defaultCode = 201;
                break;
            case 'put':
            case 'patch':
            case 'delete':
                $defaultCode = 204;
                break;

        }

        /** @var Response $response */
        foreach ($operation->responses as $response) {
            if ($response->response == $defaultCode) {
                $code = 'default';
            } else {
                $code = $response->response;
            }
            $config['responses'][$code] = [
                'statusCode' => $response->response,
                'responseParameters' => [],
            ];

            if ($response->headers) {
                $methodParameters = $this->createIntegrationParameters($response->headers, false);
                foreach ($methodParameters as $methodParameterKey => $methodParameterValue) {
                    $config['responses'][$code]['responseParameters'][$methodParameterKey] = $methodParameterValue;
                }
            }
        }

        if ($operation->parameters) {
            $methodParameters = $this->createIntegrationParameters($operation->parameters);
            foreach ($methodParameters as $methodParameterKey => $methodParameterValue) {
                $config['requestParameters'][$methodParameterKey] = $methodParameterValue;
            }
        }

        if (!count($config['requestParameters'])) {
            unset($config['requestParameters']);
        }

        $operation->x["amazon-apigateway-integration"] = $config;
    }

    /**
     * @param Parameter[] $parameters
     * @param bool        $request If true (default), will generate a parameter for the request. false is response.
     *
     * @return array
     */
    protected function createIntegrationParameters(array $parameters, $request = true)
    {
        $requestParameters = [];

        foreach ($parameters as $parameter) {
            $type = $name = null;

            switch (true) {
                case ($parameter instanceof Parameter):
                    $name = $parameter->name;

                    switch ($parameter->in) {
                        case 'query':
                            $type = 'querystring';
                            break;
                        case 'header':
                        case 'path':
                            $type = $parameter->in;
                            break;
                        case 'body':
                        default:
                            break;
                    }

                    break;
                case ($parameter instanceof Header):
                    $name = $parameter->header;
                    $type = 'header';
                    break;
            }

            if ($type && $name) {
                $httpResponse = $request ? 'request' : 'response';
                $requestParameters['integration.'.$httpResponse.'.'.$type.'.'.$name] = 'method.'.$httpResponse.'.'.$type.'.'.$name;
            }
        }

        if (!$request) {
            $requestParameters = array_flip($requestParameters);
        }

        return $requestParameters;
    }
}
