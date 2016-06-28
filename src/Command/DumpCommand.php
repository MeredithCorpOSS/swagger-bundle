<?php

namespace TimeInc\SwaggerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TimeInc\SwaggerBundle\Swagger;

/**
 * Class DumpCommand.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class DumpCommand extends Command
{
    /**
     * @var Swagger
     */
    private $swagger;

    /**
     * SwaggerCommand constructor.
     *
     * @param Swagger $swagger
     */
    public function __construct(Swagger $swagger)
    {
        $this->swagger = $swagger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('swagger:dump')
            ->setDescription('Dump the swagger schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write(
            $this->swagger->json()
        );
    }
}
