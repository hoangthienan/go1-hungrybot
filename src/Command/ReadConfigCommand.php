<?php

namespace Go1\Command;

use Google_Service_Sheets;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReadConfigCommand extends BaseServiceCommand
{
    protected $appConfigPath;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->appConfigPath = ROOT_DIR . '/cache/config.json';
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('go1:sheet:config')
            ->setDescription('Read config')
            ->setHelp(
                ''
            );
    }

    protected function initService(InputInterface $input, OutputInterface $output)
    {
        $sheetId = $this->config['spreadsheetId'];

        $service = new Google_Service_Sheets($this->service->getClient());

        // read meta
        $sheets = $service->spreadsheets->get($sheetId)->getSheets();

        $firstSheet = $sheets[0];

        $range = "{$firstSheet->getProperties()->title}!G2:H2";
        $response = $service->spreadsheets_values->get($sheetId, $range);
        $values = $response->getValues();

        if (count($values) > 0) {
            var_dump($values[0]);

            file_put_contents($this->appConfigPath, json_encode($values));
        }
    }
}
