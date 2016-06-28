<?php

namespace TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\Entity;

use Swagger\Annotations\Definition;
use Swagger\Annotations\Property;

/**
 * Class Wine.
 *
 * @author andy.thorne@timeinc.com
 *
 * @Definition()
 */
class Wine
{
    /**
     * @var int
     *
     * @Property()
     */
    private $id;

    /**
     * @var string
     *
     * @Property()
     */
    private $name;
}
