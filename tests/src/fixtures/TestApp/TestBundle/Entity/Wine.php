<?php

namespace DrakeRoll\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\Entity;

use Swagger\Annotations\Definition;
use Swagger\Annotations\Property;

/**
 * Class Wine.
 *
 * @author andy.thorne@DrakeRoll.com
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
