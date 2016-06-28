<?php

namespace TimeInc\SwaggerBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\FilesystemCache;
use SplFileInfo;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use TimeInc\SwaggerBundle\Analyser\ClassAnalyser;
use TimeInc\SwaggerBundle\Exception\SwaggerException;
use TimeInc\SwaggerBundle\Swagger\Annotation\Route;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SwaggerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load all whitelisted annotations
        AnnotationRegistry::registerLoader(
            function ($class) {
                $dir = dirname(__DIR__);
                if (file_exists($dir.'/Swagger/Annotation/'.$class.'.php')) {
                    class_exists('TimeInc\\SwaggerBundle\\Swagger\\Annotation\\'.$class);
                }

                return false;
            }
        );

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('swagger.config', $config['info']);

        $dirs = [];
        $bundles = $container->getParameter('kernel.bundles');
        foreach ($bundles as $bundle => $class) {
            if (in_array($bundle, $config['bundles'])) {
                $refl = new \ReflectionClass($class);
                $bundleDir = dirname($refl->getFileName());
                $checkDirs = [
                    $bundleDir.'/Controller',
                    $bundleDir.'/Document',
                    $bundleDir.'/Entity',
                    $bundleDir.'/Form',
                    $bundleDir.'/Model',
                    $bundleDir.'/Swagger',
                ];

                foreach ($checkDirs as $dir) {
                    if (is_dir($dir)) {
                        $dirs[] = $dir;
                    }
                }
            }
        }

        foreach ($config['paths'] as $path) {
            $dirs[] = $path;
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->loadRouteProcessor($container, $config);

        $swaggerDefinition = $container->findDefinition('swagger');
        $swaggerDefinition->replaceArgument(2, []);
        $swaggerDefinition->replaceArgument(3, $dirs);
    }

    private function loadRouteProcessor(ContainerBuilder $container, array $config)
    {
        $reader = new CachedReader(
            new AnnotationReader(),
            new FilesystemCache(
                $container->getParameter('kernel.cache_dir').'/swagger/annotations'
            )
        );

        $routeDefinitions = [];
        $bundles = $container->getParameter('kernel.bundles');
        foreach ($bundles as $bundle => $class) {
            if (!in_array($bundle, $config['bundles'])) {
                continue;
            }
            $bundleReflClass = new \ReflectionClass($class);
            $controllerDir = dirname($bundleReflClass->getFileName()).'/Controller';
            if (is_dir($controllerDir)) {
                $finder = new Finder();
                $finder->in(dirname($bundleReflClass->getFileName()).'/Controller');
                $finder->name('*Controller.php');
                $finder->files();

                $controllerNamespace = $bundleReflClass->getNamespaceName().'\\Controller';
                /** @var SplFileInfo $file */
                foreach ($finder as $file) {
                    try {
                        $relativeNamespace = str_replace([$controllerDir, '/'], ['', '\\'], $file->getPath());
                        $controllerReflClass = new \ReflectionClass(
                            $controllerNamespace.$relativeNamespace.'\\'.$file->getBasename('.php')
                        );
                        $routeDefinitions = array_merge(
                            $routeDefinitions,
                            $this->buildRouteDefinition($reader, $controllerReflClass)
                        );
                    } catch (\ReflectionException $e) {
                        // ignore reflection errors. class may be abstract or an interface...
                    }
                }
            }
        }

        // Search paths
        $analyser = new ClassAnalyser();
        foreach ($config['paths'] as $dir) {
            $finder = new Finder();
            $finder->in($dir);
            $finder->name('*.php');
            $finder->files();

            $classes = $analyser->analysePaths($finder);

            foreach ($classes as $class) {
                if (class_exists($class)) {
                    $routeDefinitions = array_merge(
                        $routeDefinitions,
                        $this->buildRouteDefinition($reader, new \ReflectionClass($class))
                    );
                }
            }
        }

        $processorDefinition = $container->findDefinition('swagger.processor.route');
        $processorDefinition->replaceArgument(1, $config['formats']);
        $processorDefinition->replaceArgument(2, $routeDefinitions);
    }

    /**
     * @param CachedReader     $reader
     * @param \ReflectionClass $controllerReflClass
     *
     * @return array
     */
    private function buildRouteDefinition(CachedReader $reader, \ReflectionClass $controllerReflClass)
    {
        $routeDefinitions = [];

        if ($controllerReflClass->isInstantiable()) {
            $annotations = $reader->getClassAnnotations($controllerReflClass);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Route) {
                    $annotation->controller = $controllerReflClass->getName();
                    $this->autoDetectEntity($annotation);
                    $routeDefinitions[] = (array) $annotation;
                }
            }

            foreach ($controllerReflClass->getMethods() as $method) {
                $annotations = $reader->getMethodAnnotations($method);
                foreach ($annotations as $annotation) {
                    if ($annotation instanceof Route) {
                        $annotation->controller = $controllerReflClass->getName();
                        $annotation->method = $method->getName();
                        $this->autoDetectEntity($annotation);
                        $routeDefinitions[] = (array) $annotation;
                    }
                }
            }
        }

        return $routeDefinitions;
    }

    /**
     * @param Route $route
     *
     * @throws SwaggerException
     */
    private function autoDetectEntity(Route $route)
    {
        if ($route->entity) {
            $entityReflection = new \ReflectionClass($route->entity);
            $route->entity_name = $entityReflection->getShortName();
        } else {
            // if we have not been given an entity, try and find an entity of the same name...
            $namespaceParts = explode('\\', $route->controller);
            $entityName = str_replace('Controller', '', array_pop($namespaceParts));
            while (count($namespaceParts)) {
                $endPart = array_pop($namespaceParts);
                if ($endPart == 'Controller') {
                    break;
                }
            }

            $guessEntityClass = implode('\\', $namespaceParts).'\\Entity\\'.$entityName;

            if (class_exists($guessEntityClass)) {
                $route->entity = $guessEntityClass;
                $route->entity_name = $entityName;
            } else {
                throw new SwaggerException(
                    'Entity for route "'.$route->route.'" was not defined and could not be guessed'
                );
            }
        }
    }
}
