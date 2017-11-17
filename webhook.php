<?php

error_reporting(E_ALL);

define('BASE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require(BASE_DIR . 'vendor/autoload.php');
$config = require(BASE_DIR . 'config.php');
$authToken = $config['authToken'];
$webhookUrl = $config['webhookUrl'];

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$dt = new \DateTime();
$logDir = BASE_DIR . 'logs/' . $dt->format('Y-m');
$logFile = $logDir."/hipchat_".$dt->format('Y-m-d').".log";

$log = new Logger('HipChat Webhook');
$log->pushHandler(new StreamHandler($logFile, Logger::INFO));

$input = file_get_contents('php://input');

$log->info($input);
$body = json_decode($input);

$service = new \Go1\Services\GSheetService($config);

switch ($body->webhook_id) {
    case $config['wh_menu_id']:
        $service->sendMenuImage();
        break;
    case $config['wh_order_id']:
        $service->processOrder($body);
        break;
}
