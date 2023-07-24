<?php
/**
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/5/17 22:15
 */

namespace Sweeper\Logger\traits;

use ReflectionClass;
use Sweeper\Logger\lib\Log;
use Sweeper\Logger\Logger;
use Sweeper\Logger\LoggerException;
use Sweeper\Logger\LoggerLevel;

/**
 * 日志服务
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/24 11:33
 * @Path \Sweeper\Logger\traits\LogService
 * @mixin Logger
 * @mixin Log
 */
trait LogService
{

    /** @var array 日志服务配置 */
    protected $logServiceConfig = [];

    /** @var Log 日志服务 */

    protected $logService;

    /** @var array 日志配置 */
    protected $loggerConfig = [];

    /** @var Logger 日志记录器 */
    protected $logger;

    /**
     * @return array
     */
    public function getLogServiceConfig(): array
    {
        return $this->logServiceConfig;
    }

    /**
     * User: Sweeper
     * Time: 2023/5/18 9:21
     * @param array $logServiceConfig
     * @return $this
     */
    public function setLogServiceConfig(array $logServiceConfig): self
    {
        $this->logServiceConfig = $logServiceConfig;

        return $this;
    }

    /**
     * @return Log
     */
    public function getLogService(): Log
    {
        return $this->logService;
    }

    /**
     * User: Sweeper
     * Time: 2023/5/18 9:06
     * @param Log $logService
     * @return $this
     */
    public function setLogService(Log $logService): self
    {
        $this->logService = $logService;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoggerConfig()
    {
        return $this->loggerConfig;
    }

    /**
     * User: Sweeper
     * Time: 2023/5/18 8:59
     * @param $loggerConfig
     * @return $this
     */
    public function setLoggerConfig($loggerConfig): self
    {
        $this->loggerConfig = $loggerConfig;

        return $this;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * User: Sweeper
     * Time: 2023/5/18 9:07
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * 初始化日志服务
     * User: Sweeper
     * Time: 2023/5/18 9:01
     * @return $this
     */
    public function initializeLogService(array $config = []): self
    {
        if (!($this->logService instanceof Log)) {
            $this->logService = Log::instance(array_replace([], $this->getLoggerConfig(), $config));
        }

        return $this;
    }

    /**
     * 初始化日志记录器
     * User: Sweeper
     * Time: 2023/5/17 22:26
     * @param array       $config
     * @param string|null $logId
     * @param bool        $isUnique
     * @param string|null $consoleLevel
     * @param string|null $fileLevel
     * @return LogService
     */
    public function initializeLogger(array $config = [], string $logId = null, bool $isUnique = true, string $consoleLevel = null, string $fileLevel = null): self
    {
        if (!($this->logger instanceof Logger)) {
            $this->logger = $this->initializeLogService($config)->logger($logId ?? static::class, $isUnique, $consoleLevel, $fileLevel);
        }

        return $this;
    }

    /**
     * User: Sweeper
     * Time: 2023/7/24 11:35
     * @param string $methodName
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $methodName, array $arguments)
    {
        $method = strtoupper($methodName);
        $class  = new ReflectionClass(static::class);
        if (method_exists(Log::class, $method)) {

            $this->initializeLogService();

            return $this->logService->{$method}(...$arguments);
        }
        if (defined(LoggerLevel::class . "::$method")) {
            $level = constant(LoggerLevel::class . "::$method");

            $filename = $class->getShortName();
            $argument = end($arguments);
            if (is_array($argument) && !empty($argument['filename'])) {
                $filename = array_pop($arguments)['filename'];
            }

            $this->initializeLogger(['logFile' => $filename], static::class);

            return $this->logger->{$level}(...$arguments);
        }
        throw new LoggerException("Method no exists:" . $method);
    }

}
