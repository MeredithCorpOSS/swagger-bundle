<?php

namespace TimeInc\SwaggerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class SwaggerController extends Controller
{
    public function schemaAction()
    {
        $swagger = $this->get('swagger');

        return new JsonResponse($swagger->json());
    }
}
