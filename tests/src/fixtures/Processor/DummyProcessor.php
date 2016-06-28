<?php

namespace TimeInc\SwaggerBundle\Tests\fixtures\Processor;

use Swagger\Analysis;

/**
 * Class DummyProcessor.
 *
 * @author andy.thorne@timeinc.com
 */
class DummyProcessor
{
    /**
     * Flag to check if the processor has been called.
     *
     * @var bool
     */
    public $called = false;

    /**
     * {@inheritdoc}
     */
    public function __invoke(Analysis $analysis)
    {
        $this->called = true;
    }
}
