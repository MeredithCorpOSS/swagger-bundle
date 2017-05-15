<?php

namespace DrakeRoll\SwaggerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AnalysisCompilerPass.
 *
 * @author andy.thorne@DrakeRoll.com
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
            foreach($tags as $tag) {
                if(!isset($tag['priority'])){
                    $tag['priority'] = 0;
                }
                $processorDefinitions[$tag['priority']][] = new Reference($sId);
            }
        }
        ksort($processorDefinitions);
        $processorDefinitionPrioritised = [];
        foreach($processorDefinitions as $sortedProcessorDefinitions){
            foreach($sortedProcessorDefinitions as $sortedProcessorDefinition){
                $processorDefinitionPrioritised[] = $sortedProcessorDefinition;
            }
        }

        $swaggerDefinition->replaceArgument(2, $processorDefinitionPrioritised);
    }
}
