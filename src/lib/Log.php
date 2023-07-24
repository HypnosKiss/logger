<?php

namespace Sweeper\Logger\lib;

use Sweeper\Logger\Logger;
use Sweeper\Logger\LoggerException;
use Sweeper\Logger\LoggerLevel;
use Sweeper\Logger\output\ConsoleOutput;
use Sweeper\Logger\output\FileOutput;
use Sweeper\Logger\traits\SinglePattern;

/**
 * 提供便捷的 log 注册方法
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/24 11:14
 * @Path \Sweeper\Logger\lib\Log
 */
class Log
{

    use SinglePattern;

    /** @var Logger */
    private $logger;

    /** @var string 日志文件(包含路径) */
    private $logFile;

    /**
     * @return string
     */
    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    /**
     * User: Sweeper
     * Time: 2023/3/7 18:00
     * @param string $logFile
     * @return $this
     */
    public function setLogFile(string $logFile): self
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * User: Sweeper
     * Time: 2023/1/9 11:48
     * @param string $methodName
     * @param array  $arguments
     * @return mixed
     * @throws LoggerException
     */
    public function __call(string $methodName, array $arguments)
    {
        $levelMethod = strtoupper($methodName);
        if (defined(LoggerLevel::class . "::$levelMethod")) {
            $level = constant(LoggerLevel::class . "::$levelMethod");

            return $this->logger->{$level}(...$arguments);
        }
        throw new LoggerException("Logger level no exists:" . $levelMethod);
    }

    /**
     * 设置 && 返回 通用的日志记录器【需要更多功能直接使用 Logger 类初始化即可】
     * User: Sweeper
     * Time: 2023/1/9 11:49
     * 支持的配置：[isUnique, consoleLevel, fileLevel, logFile, format]
     * @param string      $logId
     * @param bool        $isUnique
     * @param string|null $consoleLevel
     * @param string|null $fileLevel
     * @return Logger
     */
    public function logger(string $logId, bool $isUnique = true, string $consoleLevel = null, string $fileLevel = null): Logger
    {
        $isUnique         = $isUnique ?? $this->config['isUnique'];
        $uniqueKeyConsole = null;
        $uniqueKeyFile    = null;
        if ($isUnique) {
            $uniqueKeyConsole = md5("{$logId}-ConsoleOutput");
            $uniqueKeyFile    = md5("{$logId}-FileOutput");
        }
        // 日志等级
        $consoleLevel = $consoleLevel ?? ($this->config['consoleLevel'] ?? LoggerLevel::DEBUG);
        $fileLevel    = $fileLevel ?? ($this->config['fileLevel'] ?? LoggerLevel::INFO);
        // 日志文件
        $logFile = $this->getLogFile() ?? ($this->config['logFile'] ?? "/tmp/{$logId}.{$fileLevel}.log");
        // 初始化日志记录器
        $this->logger = Logger::instance($logId)
                              ->register(new ConsoleOutput(), $consoleLevel, false, $uniqueKeyConsole)
                              ->register(new FileOutput($logFile, true, $this->config['format'] ?? ''), $fileLevel, false, $uniqueKeyFile);

        return $this->logger;
    }

}
