<?php

namespace TimeInc\SwaggerBundle\Tests\Swagger\Annotation;

use TimeInc\SwaggerBundle\Swagger\Annotation\AnnotationLoader;
use TimeInc\SwaggerBundle\Swagger\Annotation\Route;

/**
 * Class AnnotationRegistryTest.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class AnnotationRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testRouteAnnotation()
    {
        $this->assertTrue(
            AnnotationLoader::load(Route::class)
        );
    }

    public function testUnknownAnnotation()
    {
        $this->assertFalse(
            AnnotationLoader::load('\An\Unknown\Random\Class')
        );
    }
}
