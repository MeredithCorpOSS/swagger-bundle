<?php

namespace TimeInc\SwaggerBundle\Swagger\Annotation;

/**
 * Class Route.
 *
 * @author andy.thorne@timeinc.com
 *
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Route
{
    const RETURNS_ENTITY = 'entity';
    const RETURNS_COLLECTION = 'collection';

    /**
     * @var string
     */
    public $controller;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $route;

    /**
     * @var string
     */
    public $entity;

    /**
     * @var string
     */
    public $entity_name;

    /**
     * @var array
     */
    public $queryParams = [];

    /**
     * @var array
     */
    public $headers = [];

    /**
     * @var string
     */
    public $returns = 'entity';

    /**
     * Route constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if(isset($data['value'])){
            $this->route = $data['value'];
        }

        foreach($data as $key => $value){
            $this->{$key} = $value;
        }
    }

}
