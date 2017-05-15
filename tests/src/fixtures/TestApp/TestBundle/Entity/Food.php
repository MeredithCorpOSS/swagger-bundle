<?php

namespace DrakeRoll\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\Entity;

use Swagger\Annotations\Definition;
use Swagger\Annotations\Property;

/**
 * Class Food.
 *
 * @author andy.thorne@DrakeRoll.com
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
