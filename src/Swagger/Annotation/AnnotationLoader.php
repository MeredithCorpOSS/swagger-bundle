<?php

namespace TimeInc\SwaggerBundle\Swagger\Annotation;

/**
 * Class AnnotationLoader.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class AnnotationLoader
{
    /**
     * Load annotation classes for doctrine
     *
     * @param string $class
     *
     * @return bool
     */
    public static function load($class)
    {
        $annotationClasses = [
            Route::class,
        ];

        if (in_array($class, $annotationClasses)) {
            return class_exists($class);
        }

        return false;
    }
}
