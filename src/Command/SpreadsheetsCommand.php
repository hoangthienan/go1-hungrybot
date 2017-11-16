<?php

namespace Go1\Command;

use Google_Service_Sheets;
use Google_Service_Sheets_Spreadsheet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpreadsheetsCommand extends BaseServiceCommand
{

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
        $menu = $this->service->getMenuData();
        foreach ($menu as $row) {
            $output->writeln(sprintf("%s \t %s \t %s", $row[0], $row[1], $row[2]));
        }
    }
}
