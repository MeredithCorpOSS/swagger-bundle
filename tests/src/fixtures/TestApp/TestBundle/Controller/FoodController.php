<?php

namespace TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\Controller;

use TimeInc\SwaggerBundle\Swagger\Annotation\Route;

/**
 * Class FoodController.
 *
 * @author andy.thorne@timeinc.com
 *
 * @Route(
 *     method="getFoods",
 *     route="get_foods"
 * )
 */
class FoodController
{
    public function getFoods()
    {
    }

    /**
     * @param int $id
     *
     * @Route(
     *     route="get_food"
     * )
     */
    public function getFood($id)
    {
    }

    /**
     * @param int $id
     *
     * @Route("post_food")
     */
    public function postFood($id)
    {
    }

    /**
     * @param int $id
     *
     * @Route(
     *     route="put_food"
     * )
     */
    public function putFood($id)
    {
    }

    /**
     * @param int $id
     *
     * @Route(
     *     route="patch_food"
     * )
     */
    public function patchFood($id)
    {
    }

    /**
     * @param int $id
     *
     * @Route(
     *     route="delete_food"
     * )
     */
    public function deleteFood($id)
    {
    }
}
