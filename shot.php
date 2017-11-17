<?php

include './vendor/autoload.php';

$config = require './config.php';

use dawood\phpChrome\Chrome;
$chrome = new Chrome($config['template_url'], $config['chrome_path']);
$chrome->setArgument('--no-sandbox', '');
$chrome->setOutputDirectory(__DIR__.'/images');
$chrome->setWindowSize(610, 800);
$chrome->getScreenShot(__DIR__ .'/images/menu.jpg');

