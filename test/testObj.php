<?php

namespace Sweeper\Logger\test;

use Sweeper\Logger\traits\LogService;

require_once '../vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/24 11:53
 */
class testObj
{

    use LogService;

    public function __construct()
    {
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