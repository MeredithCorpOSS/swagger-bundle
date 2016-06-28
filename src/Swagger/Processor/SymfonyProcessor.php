<?php

namespace TimeInc\SwaggerBundle\Swagger\Processor;

use Swagger\Analysis;
use Swagger\Annotations\Info;

/**
 * Class SymfonyProcessor.
 *
 * @author andy.thorne@timeinc.com
 */
class SymfonyProcessor
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
        $swagger->info->title = $this->config['title'];
        $swagger->info->description = $this->config['description'];
        $swagger->info->version = $this->config['version'];
    }
}
