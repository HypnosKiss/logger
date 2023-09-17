<?php
/**
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/5/17 22:15
 */

namespace Sweeper\Logger\Traits;

use ReflectionClass;
use Sweeper\Logger\Lib\LogLogic;
use Sweeper\Logger\Logger;
use Sweeper\Logger\LoggerException;
use Sweeper\Logger\LoggerLevel;

/**
 * Logger 复用特征
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/24 11:33
 * @Path \Sweeper\Logger\Traits\LoggerTrait
 * @mixin Logger
 * @mixin LogLogic
 */
trait LoggerTrait
{

    /** @var LogLogic 日志服务 */

    protected $logService;

    /** @var array 日志配置 */
    protected $loggerConfig = [];

    /** @var Logger 日志记录器 */
    protected $logger;

    /**
     * @return LogLogic
     */
    public function getLogService(): LogLogic
    {
        return $this->logService;
    }

    /**
     * User: Sweeper
     * Time: 2023/5/18 9:06
     * @param LogLogic $logService
     * @return $this
     */
    public function setLogService(LogLogic $logService): self
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
        if (!($this->logService instanceof LogLogic)) {
            $this->logService = LogLogic::instance(array_replace([], $this->getLoggerConfig(), $config));
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
     * @return LoggerTrait
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
        if (method_exists(LogLogic::class, $method)) {

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
