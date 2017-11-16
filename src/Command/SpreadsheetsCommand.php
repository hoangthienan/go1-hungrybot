<?php

namespace Go1\Command;

use Google_Service_Sheets;
use Google_Service_Sheets_Spreadsheet;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
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

        // send to hipchat
        // $message = 'ready to get menu :D';
        // <p><img src="https://i.imgur.com/XksvoId.jpg"/></p>
        $html = '<table>';

        foreach ($menu as $menuRow) {
            $id = htmlentities(str_pad($menuRow[0], 4, ' ', STR_PAD_RIGHT));
            $row = "<tr><td width=\"70\">{$id}</td><td>{$menuRow[1]}</td><td><{$menuRow[2]}></td></tr>";
            $html .= $row;
        }
        $html .= '</table>';

        $params = [
            'id'             => $this->config['roomId'],
            'from'           => '',
            'message'        => $html,
            'notify'         => true,
            'color'          => 'green',
            'message_format' => 'html',
            'date'           => null,
        ];
        $messageObj = new Message($params);

        $authToken = $this->config['authToken'];
        $auth = new OAuth2($authToken);
        $client = new Client($auth);
        $roomApi = new RoomAPI($client);
        $roomApi->sendRoomNotification($this->config['roomId'], $messageObj);
    }
}
