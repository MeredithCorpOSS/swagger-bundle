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
     * @param null|string $alternativeHost
     *
     * @return $this
     */
    public function build($alternativeHost = null)
    {
        // Crawl directory and parse all files
        $finder = Util::finder($this->directories, []);
        foreach ($finder as $file) {
            $this->analysis->addAnalysis($this->analyser->fromFile($file->getPathname()));
        }

        // Set alternative host
        $this->analysis->alternativeHost = $alternativeHost;

        // Post processing
        $this->analysis->process($this->processors);

        // Validation (Generate notices & warnings)
        $this->analysis->validate();

        return $this;
    }

    /**
     * Build and return OpenAPI json.
     *
     * @param null|string $alternativeHost
     * @param bool $pretty
     *
     * @return \Swagger\Annotations\Swagger
     */
    public function json($alternativeHost = null, $pretty = false)
    {
        $this->build($alternativeHost);

        $jsonOptions = JSON_UNESCAPED_SLASHES;

        if ($pretty) {
            $jsonOptions |= JSON_PRETTY_PRINT;
        }

        return json_encode($this->analysis->swagger, $jsonOptions);
    }

    /**
     * Build and save OpenAPI json to file.
     *
     * @param string $filename
     * @param null|string $alternativeHost
     */
    public function save($filename, $alternativeHost = null)
    {
        $this->build($alternativeHost);

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
