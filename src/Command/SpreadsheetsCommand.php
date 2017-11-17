<?php

namespace Go1\Command;

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
//        $html = $this->service->getMenuRawText();
//
//        $html = "<pre>{$html}</pre>";
//
//        $params = [
//            'id'             => $this->config['roomId'],
//            'from'           => '',
//            'message'        => $html,
//            'notify'         => true,
//            'color'          => 'green',
//            'message_format' => 'html',
//            'date'           => null,
//        ];
//        $messageObj = new Message($params);
//
//        $authToken = $this->config['authToken'];
//        $auth = new OAuth2($authToken);
//        $client = new Client($auth);
//        $roomApi = new RoomAPI($client);
//        $roomApi->sendRoomNotification($this->config['roomId'], $messageObj);

        $data = $this->service->getMenuData();
        print_r($data);
    }
}
