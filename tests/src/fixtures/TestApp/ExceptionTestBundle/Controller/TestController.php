<?php

namespace DrakeRoll\SwaggerBundle\Tests\fixtures\TestApp\ExceptionTestBundle\Controller;

use DrakeRoll\SwaggerBundle\Swagger\Annotation\Route;

/**
 * Class TestController.
 *
 * @author Andy Thorne <andy.thorne@DrakeRoll.com>
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
