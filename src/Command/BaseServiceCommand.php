<?php

namespace Go1\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseServiceCommand extends Command
{
    protected $client;

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

            $this->getClient();

            $this->initService($input, $output);
        }
        catch (\Exception $ex) {
            $output->writeln("<error> [" . $ex->getMessage() . "] </error>");
        }
    }

    /**
     * Returns an authorized API client.
     *
     * @return Google_Client the authorized client object
     */
    protected function getClient()
    {

    }
}