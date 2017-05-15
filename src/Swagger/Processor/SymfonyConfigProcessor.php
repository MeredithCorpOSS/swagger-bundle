<?php

namespace DrakeRoll\SwaggerBundle\Swagger\Processor;

use Swagger\Analysis;
use Swagger\Annotations\Info;

/**
 * Class SymfonyConfigProcessor.
 *
 * @author andy.thorne@DrakeRoll.com
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
     *
     * @throws \InvalidArgumentException
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
        $swagger->schemes = $this->config['schemes'];

        if ($analysis->alternativeHost !== null) {
            // override data with alternative host data
            $alternativeHost = [];

            if (isset($this->config['alternative_hosts'])) {
                foreach ($this->config['alternative_hosts'] as $alternativeHostConfig) {
                    if ($alternativeHostConfig['name'] === $analysis->alternativeHost) {
                        $alternativeHost = $alternativeHostConfig;
                        break;
                    }
                }
            }

            if (empty($alternativeHost)) {
                throw new \InvalidArgumentException(
                    sprintf('Unknown alternative host [%s].', $analysis->alternativeHost)
                );
            }

            if(isset($alternativeHost['host'])) {
                $swagger->host = $alternativeHost['host'];
            }

            if(isset($alternativeHost['base_path'])) {
                $swagger->basePath = $alternativeHost['base_path'];
            }

            if(isset($alternativeHost['schemes'])) {
                $swagger->schemes = $alternativeHost['schemes'];
            }
        }

        $swagger->produces = $this->config['produces'];
        $swagger->consumes = $this->config['consumes'];
    }
}
