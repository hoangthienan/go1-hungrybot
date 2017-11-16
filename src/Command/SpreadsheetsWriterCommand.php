<?php

namespace Go1\Command;

use Google_Service_Sheets;
use Google_Service_Sheets_Spreadsheet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpreadsheetsWriterCommand extends BaseServiceCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('go1:sheet:write')
            ->setDescription('Testing write')
            ->setHelp(
                ''
            );
    }

    protected function initService(InputInterface $input, OutputInterface $output)
    {
        $data = [
            [1, ['Phuong Huynh', 'Vu Nguyen', 'Nguyen Ng']],
            [4, ['An Hoang']],
            [7, ['Chau Pham']],
        ];
        $result = $this->service->writeData($data);

        $output->writeln("<info>Updated rows: {$result->updatedRows}</info>");
    }
}
