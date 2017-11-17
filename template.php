<?php

require_once __DIR__ . '/vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/html');
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__ . '/cache',
]);

$template = $twig->load('01.html');
$config = require('./config.php');
$service = new \Go1\Services\GSheetService($config);
//$data = $service->getMenuData();
echo $template->render();
