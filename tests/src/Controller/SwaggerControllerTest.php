<?php

namespace TimeInc\SwaggerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SwaggerControllerTest extends WebTestCase
{
    protected function setUp()
    {
        self::bootKernel();
    }

    /**
     * Test the schema action returns json.
     */
    public function testSchema()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/_swagger/swagger.json');

        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));

        $content = json_decode($client->getResponse()->getContent(), true);
    }
}
