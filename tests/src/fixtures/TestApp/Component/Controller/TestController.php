<?php

namespace TimeInc\SwaggerBundle\Tests\fixtures\TestApp\Component\Controller;

use TimeInc\SwaggerBundle\Swagger\Annotation\Route;

/**
 * Class TestController.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 *
 * @Route(
 *     method="testAction",
 *     route="test_wine",
 *     entity="TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\Entity\Wine",
 *     queryParams={
 *          "test_string": "string",
 *          "test_array": "array",
 *          "test_integer": "integer"
 *     }
 * )
 */
class TestController
{
    public function testAction()
    {
    }
}
