<?php

namespace TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\Entity;

use Swagger\Annotations\Definition;
use Swagger\Annotations\Property;

/**
 * Class Food.
 *
 * @author andy.thorne@timeinc.com
 *
 * @Definition()
 */
class Food
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
