<?php

namespace Sweeper\Test;

use Sweeper\Logger\LoggerLevel;
use Sweeper\Logger\Logic\Log;
use Sweeper\Logger\Traits\LoggerTrait;

require_once '../vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/24 11:53
 */
class testLogger
{

    use LoggerTrait;

    public function __construct()
    {
        // $this->setLogFile(__DIR__ . '/logs/test.info.log');
        $this->debug('__construct');
    }

    public function __destruct()
    {
        $this->debug('__destruct');
    }

    public function run()
    {
        $this->info('run', 123, 456);
    }

}

$logger = Log::instance()->logger($logId ?? 'test', LoggerLevel::DEBUG, LoggerLevel::INFO);
$logger->debug('123');
$logger->debug('456');
$logger->info('789');

$obj = new testLogger();
$obj->run();