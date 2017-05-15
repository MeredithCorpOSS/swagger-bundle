<?php

namespace DrakeRoll\SwaggerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DrakeRoll\SwaggerBundle\Exception\SwaggerException;
use DrakeRoll\SwaggerBundle\Swagger;

/**
 * Class ExportCommand.
 *
 * @author Andy Thorne <andy.thorne@DrakeRoll.com>
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
            )
            ->addOption(
                'alternative-host',
                null,
                InputOption::VALUE_REQUIRED,
                'Alternative host name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $force = $input->getOption('force');
        $alternativeHost = $input->getOption('alternative-host');

        if (is_dir($file)) {
            if ($alternativeHost !== null) {
                $file = rtrim($file, '/').'/swagger-'.$alternativeHost.'.json';
            } else {
                $file = rtrim($file, '/').'/swagger.json';
            }
        }

        if (file_exists($file) && !$force) {
            throw new SwaggerException('File already exists. Use --force to skip file checks.');
        }

        $this->swagger->save($file, $alternativeHost);
        $output->writeln('<fg=green>Written to '.realpath($file).'</>');
    }
}
