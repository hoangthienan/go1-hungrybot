<?php

namespace Go1\Command;

use Knp\Snappy\Image;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SnappyCommand extends BaseServiceCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('go1:snappy')
            ->setDescription('Executes')
            ->setHelp(
                ''
            );
    }

    protected function initService(InputInterface $input, OutputInterface $output)
    {
        $snappy = new Image(BASE_PATH . '/wkhtmltopdf/bin/wkhtmltoimage');
        $snappy->setOption('width', '600');
        $snappy->generateFromHtml(file_get_contents(BASE_PATH . '/html/01.html'), 'images/hello-snappy.jpg', [], true);
    }
}
