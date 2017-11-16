<?php

error_reporting(E_ALL);

define('BASE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require(BASE_DIR . 'vendor/autoload.php');
$config = require(BASE_DIR . './config.php');
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
$log->info(file_get_contents('php://input'));
