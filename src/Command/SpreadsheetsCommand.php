<?php

namespace Go1\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpreadsheetsCommand extends BaseServiceCommand
{
    protected $spreadsheetId = '';

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('go1:sheet')
            ->setDescription('Executes')
            ->setHelp(
                ''
            );
    }

    protected function initService(InputInterface $input, OutputInterface $output)
    {
        try {

            $output->writeln("<info> [ GO1 ] </info>");

        }
        catch (\Exception $ex) {
            $output->writeln("<error> [" . $ex->getMessage() . "] </error>");
        }
    }
}