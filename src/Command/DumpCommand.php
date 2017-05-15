<?php

namespace DrakeRoll\SwaggerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DrakeRoll\SwaggerBundle\Swagger;

/**
 * Class DumpCommand.
 *
 * @author Andy Thorne <andy.thorne@DrakeRoll.com>
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
            ->setDescription('Dump the swagger schema')
            ->addOption(
                'alternative-host',
                null,
                InputOption::VALUE_REQUIRED,
                'Alternative host name'
            )
            ->addOption(
                'pretty',
                'p',
                InputOption::VALUE_NONE,
                'Output pretty JSON'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $alternativeHost = $input->getOption('alternative-host');
        $pretty = $input->getOption('pretty');

        $output->write(
            $this->swagger->json($alternativeHost, $pretty)
        );
    }
}
