<?php

namespace TimeInc\SwaggerBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TimeInc\SwaggerBundle\Command\DumpCommand;

/**
 * Class DumpCommandTest.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class DumpCommandTest extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::bootKernel();
    }

    public function testExecute()
    {
        $kernel = self::$kernel;
        $swagger = $kernel->getContainer()->get('swagger');

        $application = new Application();
        $application->add(new DumpCommand($swagger));

        $command = $application->find('swagger:dump');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $output = $commandTester->getDisplay();

        $json = json_decode($output, true);

        $this->assertInternalType('array', $json);
        $this->assertEquals('2.0', $json['swagger']);
        $this->assertEquals('My API', $json['info']['title']);
        $this->assertEquals('My API Description', $json['info']['description']);
        $this->assertEquals('1.0', $json['info']['version']);

        $this->assertCount(3, $json['paths']);
        $this->assertTrue(isset($json['paths']['/foods']));
        $this->assertTrue(isset($json['paths']['/foods/{id}']));
        $this->assertTrue(isset($json['paths']['/wine/{id}']));

        $this->assertCount(2, $json['definitions']);
    }
}
