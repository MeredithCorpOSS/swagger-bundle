<?php

namespace TimeInc\SwaggerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AnalysisCompilerPass.
 *
 * @author andy.thorne@timeinc.com
 */
class AnalysisCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $swaggerProcessors = $container->findTaggedServiceIds('swagger.processor');
        $swaggerDefinition = $container->findDefinition('swagger');

        $processorDefinitions = [];
        foreach ($swaggerProcessors as $sId => $tags) {
            $processorDefinitions[] = new Reference($sId);
        }

        $swaggerDefinition->replaceArgument(2, $processorDefinitions);
    }
}
