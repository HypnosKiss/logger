<?php

namespace Sweeper\Logger\Logic;

use ReflectionClass;
use Sweeper\DesignPattern\Traits\MultiPattern;
use Sweeper\Logger\Logger;
use Sweeper\Logger\LoggerLevel;
use Sweeper\Logger\Output\ConsoleOutput;
use Sweeper\Logger\Output\FileOutput;
use Sweeper\Logger\Traits\LoggerTrait;

/**
 * 提供便捷的 log 注册方法
 * Created by Administrator PhpStorm.
 * Author: Sweeper <wili.lixiang@gmail.com>
 * DateTime: 2023/10/15 0:43
 * @Package \Sweeper\Logger\Lib\LogLogic
 */
class Log
{

    use MultiPattern, LoggerTrait;

    /**
     * 设置 && 返回 通用的日志记录器【需要更多功能直接使用 Logger 类初始化即可】
     * User: Sweeper
     * Time: 2023/1/9 11:49
     * 支持的配置：[isUnique, consoleLevel, fileLevel, format]
     * @param string|null $logId
     * @param string|null $consoleLevel
     * @param string|null $fileLevel
     * @param bool        $isUnique
     * @return Logger
     */
    public function logger(string $logId = null, string $consoleLevel = null, string $fileLevel = null, bool $isUnique = true): Logger
    {
        $uniqueKeyConsole = null;
        $uniqueKeyFile    = null;
        $isUnique         = $isUnique ?? $this->config['isUnique'];
        if ($isUnique) {
            $uniqueKeyConsole = md5("{$logId}-Console-Output");
            $uniqueKeyFile    = md5("{$logId}-File-Output");
        }
        // 日志等级
        $consoleLevel = $consoleLevel ?? ($this->config['consoleLevel'] ?? LoggerLevel::DEBUG);
        $fileLevel    = $fileLevel ?? ($this->config['fileLevel'] ?? LoggerLevel::INFO);
        // 日志文件
        $class    = new ReflectionClass(static::class);
        $name     = $this->getLoggerName() ?: $class->getName();
        $filename = $this->getFilename() ?: $class->getShortName();
        $logPath  = $this->getLogPath() ?: trim(APP_PATH, DIRECTORY_SEPARATOR) . implode(DIRECTORY_SEPARATOR, ['runtime', 'log']);
        $logFile  = $this->getLogFile() ?: implode(DIRECTORY_SEPARATOR, [$logPath, date('Ymd'), "$filename.log"]);
        $logId    = $logId ?: $name;
        // 初始化日志记录器
        $logger = Logger::instance($logId)
                        ->register(new ConsoleOutput(), $consoleLevel, false, $uniqueKeyConsole)
                        ->register(new FileOutput($logFile, true, $this->config['format'] ?? ''), $fileLevel, false, $uniqueKeyFile);

        return $this->setLogger($logger)->getLogger();
    }

}
