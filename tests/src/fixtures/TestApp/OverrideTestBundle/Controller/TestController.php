<?php

namespace TimeInc\SwaggerBundle\Tests\fixtures\TestApp\OverrideTestBundle\Controller;

use Swagger\Annotations as SWG;

/**
 * Class TestController.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 *
 * @SWG\Path(
 *      path="/test"
 * )
 */
class TestController
{
    /**
     */
    public function testAction()
    {
    }
}
