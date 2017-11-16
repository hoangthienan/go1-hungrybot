<?php

namespace Go1\Command;

use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BrowsershotCommand extends BaseServiceCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('go1:shot')
            ->setDescription('Executes')
            ->setHelp(
                ''
            );
    }

    protected function initService(InputInterface $input, OutputInterface $output)
    {
        Browsershot::html('<h1>Hello world!!</h1>')
            ->windowSize(640, 480)
            ->save('images/hello-world.png');
    }
}
