<?php

namespace Sweeper\Logger\test;

use Sweeper\Logger\Traits\LoggerTrait;

require_once '../vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/24 11:53
 */
class testObj
{

    use LoggerTrait;

    public function __construct()
    {
        $this->setLogFile('/webroot/php/logs/test.info.log');
        $this->debug('__construct');
    }

    public function __destruct()
    {
        $this->debug('__destruct');
    }

    public function run()
    {
        $this->info('run');
    }

}

$obj = new testObj();
$obj->run();