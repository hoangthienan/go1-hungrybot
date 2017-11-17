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

        $startHour = 9;
        $startMinute = 30;
        $endHour = 10;
        $endMinute = 30;
        $config = [
            'started' => false,
            'finished' => false,
        ];

        if (file_exists($this->appConfigPath)) {
            $tmpconfig = json_decode(file_get_contents($this->appConfigPath), true);

            if (isset($tmpconfig['started']) && isset($tmpconfig['finished'])) {
                $config = $tmpconfig;
            }
        }

        if (count($values) > 0) {
//            var_dump($values[0]);
            $startTimeParts = explode(':', $values[0][0]);
            $endTimeParts = explode(':', $values[0][1]);

            $isValid = count($startTimeParts) >= 2 && intval($startTimeParts[0]) > 0 && intval($startTimeParts[0]) < 60
                && intval($startTimeParts[1]) > 0 && intval($startTimeParts[1]) < 60
                && count($endTimeParts) >= 2
                && intval($endTimeParts[0]) > 0 && intval($endTimeParts[0]) < 60
                && intval($endTimeParts[1]) > 0 && intval($endTimeParts[1]) < 60;

            if ($isValid) {
                $startHour = intval($startTimeParts[0]);
                $startMinute = intval($startTimeParts[1]);
                $endHour = intval($endTimeParts[0]);
                $endMinute = intval($endTimeParts[1]);

                $now = new \DateTime();

                $startTime = new \DateTime();
                $startTime->setTime($startHour, $startMinute);

                $endTime = new \DateTime();
                $endTime->setTime($endHour, $endMinute);


                if ($startTime > $now && !$config['started']) {
                    $config['started'] = true;
                    // call start event

                    $this->service->sendRoomMessage('@here Start order, please');
                    $this->service->sendMenuImage();
                }


                if ($endTime < $now && !$config['finished']) {
                    $config['finished'] = true;
                    // call end event
                    $this->service->sendRoomMessage("@here Order timeout.");
                }

                file_put_contents($this->appConfigPath, json_encode($config));
            }
            else {
                $writeValues = [['Invalid setup']];
                $body = new \Google_Service_Sheets_ValueRange(['values' => $writeValues]);
                $params = ['valueInputOption' => 'RAW'];

                return $service->spreadsheets_values->update($sheetId, "{$firstSheet->getProperties()->title}!I2:I3", $body, $params);
            }

//            file_put_contents($this->appConfigPath, json_encode($values[0]));
        }
    }
}
