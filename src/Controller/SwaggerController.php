<?php

namespace DrakeRoll\SwaggerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;

class SwaggerController extends Controller
{
    public function schemaAction($alternativeHost = null)
    {
        $config = $this->getParameter('swagger.config');
        $pretty = $config['pretty_json'];

        $swagger = $this->get('swagger');

        return new Response($swagger->json($alternativeHost, $pretty), 200, ['Content-Type' => 'application/json']);
    }
}
