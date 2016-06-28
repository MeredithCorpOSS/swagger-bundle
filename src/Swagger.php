<?php

namespace TimeInc\SwaggerBundle;

use Swagger\Analyser;
use Swagger\Analysis;
use Swagger\StaticAnalyser;
use Swagger\Util;

/**
 * Class Swagger.
 *
 * @author andy.thorne@timeinc.com
 */
class Swagger
{
    /**
     * @var Analysis
     */
    private $analysis;

    /**
     * @var Analyser|StaticAnalyser
     */
    private $analyser;

    /**
     * @var array
     */
    private $processors = [];

    /**
     * @var array
     */
    private $directories;

    /**
     * Swagger constructor.
     *
     * @param Analysis                $analysis
     * @param Analyser|StaticAnalyser $analyser
     * @param array                   $processors
     * @param array                   $directories
     */
    public function __construct(Analysis $analysis, $analyser, array $processors, array $directories)
    {
        $this->analysis = $analysis;
        $this->analyser = $analyser;
        $this->processors = $processors;
        $this->directories = $directories;
    }

    /**
     * Search for annotations, process and validate definitions.
     *
     * @return $this
     */
    public function build()
    {
        // Crawl directory and parse all files
        $finder = Util::finder($this->directories, []);
        foreach ($finder as $file) {
            $this->analysis->addAnalysis($this->analyser->fromFile($file->getPathname()));
        }

        // Post processing
        $this->analysis->process($this->processors);

        // Validation (Generate notices & warnings)
        $this->analysis->validate();

        return $this;
    }

    /**
     * Build and return OpenAPI json.
     *
     * @return \Swagger\Annotations\Swagger
     */
    public function json()
    {
        $this->build();

        return json_encode($this->analysis->swagger);
    }

    /**
     * Build and save OpenAPI json to file.
     *
     * @param string $filename
     */
    public function save($filename)
    {
        $this->build();

        $this->analysis->swagger->saveAs($filename);
    }

    /**
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }
}
