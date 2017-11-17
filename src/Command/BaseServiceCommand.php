<?php

namespace Go1\Command;

use Go1\Services\GSheetService;
use Google_Client;
use Google_Service_Sheets;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseServiceCommand extends Command
{
    /**
     * @var GSheetService
     */
    protected $service;

    protected $config;

    public function __construct($name = null)
    {
        $this->config = include __DIR__ . './../../config.php';

        parent::__construct($name);
    }

    abstract protected function initService(InputInterface $input, OutputInterface $output);

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (php_sapi_name() != 'cli') {
                throw new \Exception('This application must be run on the command line.');
            }

            $this->service = new GSheetService($this->config);

            $this->initService($input, $output);
        }
        catch (\Exception $ex) {

            var_dump($ex);
            $output->writeln("<error> [" . $ex->getMessage() . "] </error>");
        }

        return true;
    }
}
