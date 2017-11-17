<?php

require_once __DIR__ . '/vendor/autoload.php';

$config = require('./config.php');
$service = new \Go1\Services\GSheetService($config);
//$data = $service->getMenuData();

$data = [
    [1, 'Heo quay kho trứng', '30'],
    [2, 'Đậu hủ kho nấm rơm', '30'],
    [3, 'Đậu hủ kho nấm rơm', '30'],
    [4, 'Đậu hủ kho nấm rơm', '30'],
    [5, 'Đậu hủ kho nấm rơm', '30'],
    [6, 'Đậu hủ kho nấm rơm', '30'],
];

include './html/menu.twig.php';

