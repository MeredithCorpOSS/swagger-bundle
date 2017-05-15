<?php

namespace DrakeRoll\SwaggerBundle\Tests\Analyser;

use Symfony\Component\Finder\Finder;
use DrakeRoll\SwaggerBundle\Analyser\ClassAnalyser;

/**
 * Class ClassAnalyserTest.
 *
 * @author Andy Thorne <andy.thorne@DrakeRoll.com>
 */
class ClassAnalyserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the Analyser with a single definition in one file.
     */
    public function testAnalyseSingleDefinition()
    {
        $analyser = new ClassAnalyser();
        $file = dirname(__DIR__).'/fixtures/TestApp/Component/Controller/TestController.php';

        $classes = $analyser->analyse($file);

        $this->assertCount(1, $classes);
        $this->assertEquals(
            'DrakeRoll\SwaggerBundle\Tests\fixtures\TestApp\Component\Controller\TestController',
            $classes[0]
        );
    }

    /**
     * Test the Analyser with multiple definitions in one file.
     */
    public function testAnalyseMultipleDefinitions()
    {
        $analyser = new ClassAnalyser();
        $file = dirname(__DIR__).'/fixtures/TestApp/Component/TestAnnotations.php';

        $classes = $analyser->analyse($file);

        $this->assertCount(2, $classes);
        $this->assertContains('AnotherNamespace\AnotherClass', $classes);
        $this->assertContains('DrakeRoll\SwaggerBundle\Tests\fixtures\TestApp\Component\TestAnnotations', $classes);
    }

    /**
     * Test the Analyser will append to an array if provided one.
     */
    public function testAnalyseDefinitionsAppend()
    {
        $analyser = new ClassAnalyser();
        $file = dirname(__DIR__).'/fixtures/TestApp/Component/TestAnnotations.php';

        $classes = [
            'class1',
            'class2',
        ];
        $classes = $analyser->analyse($file, $classes);

        $this->assertCount(4, $classes);
        $this->assertContains('class1', $classes);
        $this->assertContains('class2', $classes);
        $this->assertContains('AnotherNamespace\AnotherClass', $classes);
        $this->assertContains('DrakeRoll\SwaggerBundle\Tests\fixtures\TestApp\Component\TestAnnotations', $classes);
    }

    /**
     * Test the Analyser will find all classes in a path.
     */
    public function testAnalysePaths()
    {
        $analyser = new ClassAnalyser();
        $finder = new Finder();
        $finder->in(dirname(__DIR__).'/fixtures/TestApp/Component')
               ->files()
               ->name('*.php');

        $classes = $analyser->analysePaths($finder);

        $this->assertCount(3, $classes);
        $this->assertContains(
            'DrakeRoll\SwaggerBundle\Tests\fixtures\TestApp\Component\Controller\TestController',
            $classes
        );
        $this->assertContains('AnotherNamespace\AnotherClass', $classes);
        $this->assertContains('DrakeRoll\SwaggerBundle\Tests\fixtures\TestApp\Component\TestAnnotations', $classes);
    }
}
