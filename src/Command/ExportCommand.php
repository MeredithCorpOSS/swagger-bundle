<?php

namespace TimeInc\SwaggerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TimeInc\SwaggerBundle\Exception\SwaggerException;
use TimeInc\SwaggerBundle\Swagger;

/**
 * Class ExportCommand.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class ExportCommand extends Command
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
            ->setName('swagger:export')
            ->setDescription('Dump the swagger schema')
            ->addArgument('file', InputArgument::REQUIRED, 'File path to export schema to')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force write the file, overwritting if it already exists'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $force = $input->getOption('force');

        if (is_dir($file)) {
            $file = rtrim($file, '/').'/swagger.json';
        }

        if (file_exists($file) && !$force) {
            throw new SwaggerException('File already exists. Use --force to skip file checks.');
        }

        $this->swagger->save($file);
        $output->writeln('<fg=green>Written to '.realpath($file).'</>');
    }
}
