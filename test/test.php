<?php
/**
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/24 11:09
 */

use Sweeper\Logger\Lib\LogLogic;
use Sweeper\Logger\LoggerLevel;

require_once '../vendor/autoload.php';

$logger = LogLogic::instance(array_replace(['logFile' => './test.log']))->logger($logId ?? 'test', true, LoggerLevel::DEBUG, LoggerLevel::INFO);
$logger->debug('123');
$logger->debug('456');
$logger->info('789');

