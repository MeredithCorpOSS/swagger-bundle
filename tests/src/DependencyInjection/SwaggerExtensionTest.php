<?php

namespace TimeInc\SwaggerBundle\Tests\src\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use TimeInc\SwaggerBundle\DependencyInjection\SwaggerExtension;
use TimeInc\SwaggerBundle\Exception\SwaggerException;
use TimeInc\SwaggerBundle\Tests\fixtures\TestApp\ExceptionTestBundle\ExceptionTestBundle;
use TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\TestBundle;

/**
 * Class SwaggerExtensionTest.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class SwaggerExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->baseDir = dirname(dirname(__DIR__));

        $cacheDir = $this->baseDir.'/var/cache';
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.bundles', ['TestBundle' => TestBundle::class]);
        $this->container->setParameter('kernel.cache_dir', $cacheDir);
        $this->container->register('swagger');

        if (is_dir($cacheDir)) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }
    }

    /**
     * Test the extension will search bundle dirs for annotations.
     */
    public function testLoadWithBundle()
    {
        $extension = new SwaggerExtension();
        $extension->load(
            $this->createConfig(
                [
                    'bundles' => [
                        'TestBundle',
                    ],
                ]
            ),
            $this->container
        );

        $swagger = $this->container->getDefinition('swagger');
        $annotationDirs = $swagger->getArgument(3);

        $this->assertInternalType('array', $annotationDirs);
        $this->assertCount(6, $annotationDirs);
        foreach ($annotationDirs as $annotationDir) {
            $this->assertThat(
                $annotationDir,
                $this->logicalOr(
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Controller'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Document'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Entity'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Form'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Model'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Swagger')
                )
            );
        }
    }

    /**
     * Test the extension will search bundle dirs for annotations.
     */
    public function testLoadWithPaths()
    {
        $extension = new SwaggerExtension();
        $extension->load(
            $this->createConfig(
                [
                    'paths' => [
                        $this->baseDir.'/src/fixtures/TestApp/Component',
                    ],
                ]
            ),
            $this->container
        );

        $swagger = $this->container->getDefinition('swagger');
        $annotationDirs = $swagger->getArgument(3);

        $this->assertInternalType('array', $annotationDirs);
        $this->assertCount(1, $annotationDirs);
        $this->assertStringEndsWith('/fixtures/TestApp/Component', $annotationDirs[0]);
    }

    /**
     * Test the extension will search bundle dirs for annotations.
     */
    public function testLoadEntityAutoDetectFails()
    {
        $this->setExpectedException(SwaggerException::class);

        $this->container->setParameter('kernel.bundles', ['ExceptionTestBundle' => ExceptionTestBundle::class]);
        $extension = new SwaggerExtension();
        $extension->load(
            $this->createConfig(
                [
                    'bundles' => [
                        'ExceptionTestBundle',
                    ],
                ]
            ),
            $this->container
        );
    }

    /**
     * Create a config.
     *
     * @param array $config
     *
     * @return array
     */
    private function createConfig(array $config = [])
    {
        $configs = [
            [
                'version' => '2.0',
                'info' => [
                    'title' => 'Test App',
                    'description' => 'Unit Tests',
                    'version' => '1.0',
                ],
                'formats' => [],
                'bundles' => [],
                'paths' => [],
            ],
        ];

        if ($config) {
            $configs[] = $config;
        }

        return $configs;
    }
}
