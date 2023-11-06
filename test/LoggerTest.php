<?php

namespace Sweeper\Logger\Test;

use Sweeper\Logger\Logger;
use Sweeper\Logger\LoggerLevel;
use Sweeper\Logger\Output\ConsoleOutput;
use Sweeper\Logger\Output\FileOutput;

require_once dirname(__DIR__) . '/vendor/autoload.php';

//print all log to screen
Logger::registerGlobal(new ConsoleOutput, LoggerLevel::DEBUG);

//log only level bigger than INFO to file
Logger::registerGlobal(new FileOutput(__DIR__ . '/log/info.log'), LoggerLevel::INFO);

//log by id(class name)
Logger::registerGlobal(new FileOutput(__DIR__ . '/log/debug.log'), LoggerLevel::DEBUG, LoggerTest::class);

//log on warning happens
Logger::registerWhileGlobal(LoggerLevel::WARNING, new FileOutput(__DIR__ . '/log/while_warning.debug.log'), LoggerLevel::DEBUG);
Logger::registerWhileGlobal(LoggerLevel::ERROR, new ConsoleOutput(), LoggerLevel::INFO);
Logger::registerWhileGlobal(LoggerLevel::WARNING, new FileOutput(__DIR__ . '/log/while_warning.debug.log'), LoggerLevel::DEBUG);

class LoggerTest
{

    private $logger;

    public function __construct()
    {
        $this->logger = Logger::instance(__CLASS__);
        $this->logger->debug('2 class construct.'); //对象内日志记录
    }

    public function foo()
    {
        $this->logger->info("4 I'm calling foo()"); //对象内日志记录
    }

    public function castWarning()
    {
        $this->logger->warning('5 warning happens'); //对象内日志记录
    }

    public function castError()
    {
        $this->logger->error('6 error happens'); //对象内日志记录
    }

    public function __destruct()
    {
        $this->logger->warning('7 class destruct.'); //对象内日志记录
    }

}

//全局日志记录
Logger::instance()->debug('1 Global logging start...');

$obj = new LoggerTest();
Logger::instance()->info('3 Object created');

$obj->foo();
$obj->castWarning();
$obj->castError();
unset($obj);

Logger::instance()->debug('more object re-construct');
$obj2 = new LoggerTest();
Logger::instance()->info('Object 2 created');
$obj2->castError();

//全局日志记录
Logger::instance()->warning('8 Object destructed');