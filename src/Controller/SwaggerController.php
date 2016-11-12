<?php

namespace TimeInc\SwaggerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SwaggerController extends Controller
{
    public function schemaAction()
    {
        $swagger = $this->get('swagger');

        return new Response($swagger->json(), 200, ['Content-Type' => 'application/json']);
    }
}
