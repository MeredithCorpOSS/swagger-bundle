<?php

namespace TimeInc\SwaggerBundle\Tests\fixtures\TestApp\ExceptionTestBundle\Controller;

use TimeInc\SwaggerBundle\Swagger\Annotation\Route;

/**
 * Class TestController.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 *
 * @Route(
 *     route="test_page",
 *     method="testAction"
 * )
 */
class TestController
{
    public function testAction()
    {
    }
}
