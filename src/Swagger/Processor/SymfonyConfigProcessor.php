<?php

namespace TimeInc\SwaggerBundle\Swagger\Processor;

use Swagger\Analysis;
use Swagger\Annotations\Info;

/**
 * Class SymfonyConfigProcessor.
 *
 * @author andy.thorne@timeinc.com
 */
class SymfonyConfigProcessor
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * SymfonyProcessor constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Inject the OpenAPI schema as defined in the config.yml.
     *
     * @param Analysis $analysis
     */
    public function __invoke(Analysis $analysis)
    {
        $swagger = $analysis->swagger;

        if (!$swagger->info instanceof Info) {
            $swagger->info = new Info([]);
        }
        $swagger->info->title = $this->config['info']['title'];
        $swagger->info->description = $this->config['info']['description'];
        $swagger->info->version = $this->config['info']['version'];

        $swagger->host = $this->config['host'];
        $swagger->basePath = $this->config['base_path'];
        $swagger->produces = $this->config['produces'];
        $swagger->consumes = $this->config['consumes'];
        $swagger->schemes = $this->config['schemes'];
    }
}
