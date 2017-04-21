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
        $this->assertTrue(is_array($content));
        $this->assertEquals('testhost', $content['host']);
        $this->assertEquals('/base', $content['basePath']);
        $this->assertTrue(in_array('http', $content['schemes']));
        $this->assertTrue(in_array('https', $content['schemes']));
    }

    public function testAlternativeSchema()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/_swagger/swagger-production.json');

        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($content));
        $this->assertEquals('productionhost', $content['host']);
        $this->assertEquals('/api', $content['basePath']);
        $this->assertFalse(in_array('http', $content['schemes']));
        $this->assertTrue(in_array('https', $content['schemes']));
    }
}
