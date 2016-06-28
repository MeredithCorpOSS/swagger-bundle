<?php

namespace TimeInc\SwaggerBundle;

use TimeInc\SwaggerBundle\DependencyInjection\Compiler\AnalysisCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SwaggerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AnalysisCompilerPass());
    }
}
