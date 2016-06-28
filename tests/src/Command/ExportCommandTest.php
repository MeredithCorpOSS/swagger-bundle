<?php

namespace TimeInc\SwaggerBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TimeInc\SwaggerBundle\Command\ExportCommand;

/**
 * Class ExportCommandTest.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class ExportCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    public static $tmpFile = '/tmp/swagger.json';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        if (file_exists(self::$tmpFile)) {
            unlink(self::$tmpFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (file_exists(self::$tmpFile)) {
            unlink(self::$tmpFile);
        }
    }

    /**
     * Test that the command requires a file.
     */
    public function testExecuteExpectsFile()
    {
        $this->setExpectedException(\RuntimeException::class);

        $kernel = self::$kernel;
        $swagger = $kernel->getContainer()->get('swagger');

        $application = new Application();
        $application->add(new ExportCommand($swagger));

        $command = $application->find('swagger:export');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));
    }

    /**
     * Test that the command WILL NOT overwrite an existing file without --force.
     */
    public function testExecuteAlreadyExistsNoForce()
    {
        $this->setExpectedException(\RuntimeException::class);

        $kernel = self::$kernel;
        $swagger = $kernel->getContainer()->get('swagger');

        touch(self::$tmpFile);

        $application = new Application();
        $application->add(new ExportCommand($swagger));

        $command = $application->find('swagger:export');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'file' => self::$tmpFile,
                '--force' => false,
            ]
        );
    }

    /**
     * Test that the command WILL overwrite an existing file with --force.
     */
    public function testExecuteAlreadyExistsWithForce()
    {
        $kernel = self::$kernel;
        $swagger = $kernel->getContainer()->get('swagger');

        touch(self::$tmpFile);

        $application = new Application();
        $application->add(new ExportCommand($swagger));

        $command = $application->find('swagger:export');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'file' => self::$tmpFile,
                '--force' => true,
            ]
        );

        $this->assertRegExp('{Written to '.static::$tmpFile.'}', $commandTester->getDisplay());

        $json = json_decode(file_get_contents(static::$tmpFile), true);

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

    /**
     * Test that the command WILL overwrite an existing file with --force.
     */
    public function testExecuteDirOnly()
    {
        $kernel = self::$kernel;
        $swagger = $kernel->getContainer()->get('swagger');

        $application = new Application();
        $application->add(new ExportCommand($swagger));

        $this->assertFalse(file_exists(static::$tmpFile));

        $command = $application->find('swagger:export');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'file' => dirname(self::$tmpFile),
            ]
        );

        $this->assertRegExp('{Written to '.static::$tmpFile.'}', $commandTester->getDisplay());

        $json = json_decode(file_get_contents(static::$tmpFile), true);

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
