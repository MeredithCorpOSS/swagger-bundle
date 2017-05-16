<?php

namespace DrakeRoll\SwaggerBundle\Tests;

use Swagger\Analyser;
use Swagger\Analysis;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use DrakeRoll\SwaggerBundle\Swagger;

/**
 * Class SwaggerTest.
 *
 * @author andy.thorne@DrakeRoll.com
 */
class SwaggerTest extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (self::$kernel) {
            $fs = new Filesystem();
            $fs->remove(self::$kernel->getCacheDir());
        }
    }

    /**
     * Test Swagger can be constructed.
     */
    public function testConstruct()
    {
        $analysis = new Analysis();
        $analyser = new Analyser();

        $swagger = new Swagger($analysis, $analyser, [], ['./fixtures']);
    }

    /**
     * Test that bundle and explicit paths are being set from the config.
     */
    public function testSwaggerSearchDirectories()
    {
        self::bootKernel();
        $kernel = self::$kernel;
        $container = $kernel->getContainer();
        $swagger = $container->get('swagger');

        $directories = $swagger->getDirectories();

        $this->assertCount(7, $directories);
        foreach ($directories as $directory) {
            $this->assertThat(
                $directory,
                $this->logicalOr(
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Controller'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Document'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Entity'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Form'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Model'),
                    $this->stringEndsWith('/fixtures/TestApp/TestBundle/Swagger'),
                    $this->stringEndsWith('/fixtures/TestApp/Component')
                )
            );
        }
    }

    /**
     * Test that bundle and explicit paths are being set from the config.
     */
    public function testSwaggerAnnotationOverrides()
    {
        self::bootKernel([
            'environment' => 'test_annotation_override',
        ]);

        $kernel = self::$kernel;
        $container = $kernel->getContainer();
        $swagger = $container->get('swagger');

        $this->assertJson($swagger->json());
        $json = json_decode($swagger->json(), true);

        $this->assertCount(1, $json['paths']);
        $this->assertTrue(isset($json['paths']['/test']));
    }

    /**
     * Test the just response from swagger.
     */
    public function testJson()
    {
        self::bootKernel();
        $kernel = self::$kernel;
        $swagger = $kernel->getContainer()->get('swagger');

        $json = json_decode($swagger->json(), true);

        $this->assertInternalType('array', $json);
        $this->assertEquals('2.0', $json['swagger']);
        $this->assertEquals('My API', $json['info']['title']);
        $this->assertEquals('My API Description', $json['info']['description']);
        $this->assertEquals('1.0', $json['info']['version']);

        $this->assertCount(3, $json['paths']);
        $this->assertTrue(isset($json['paths']['/foods']));
        $this->assertTrue(isset($json['paths']['/foods/{id}']));
        $this->assertTrue(isset($json['paths']['/wine/{id}']));

        $this->assertCount(2, $json['definitions']);
    }
}
