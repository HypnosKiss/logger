<?php

namespace Sweeper\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * 日志记录器
 * Created by Administrator PhpStorm.
 * Author: Sweeper <wili.lixiang@gmail.com>
 * DateTime: 2023/10/15 22:54
 * @Package \Sweeper\Logger\Logger
 * @method self emergency (...$messages)
 * @method self alert (...$messages)
 * @method self critical (...$messages)
 * @method self error (...$messages)
 * @method self warning (...$messages)
 * @method self notice (...$messages)
 * @method self info (...$messages)
 * @method self debug (...$messages)
 * @method static self emergency (...$messages)
 * @method static self alert (...$messages)
 * @method static self critical (...$messages)
 * @method static self error (...$messages)
 * @method static self warning (...$messages)
 * @method static self notice (...$messages)
 * @method static self info (...$messages)
 * @method static self debug (...$messages)
 */
class Logger implements LoggerInterface
{

    use LoggerTrait;

    /** @var string 默认日志ID */
    public const DEFAULT_LOG_ID = 'default';

    /** @var string 日志ID */
    private $id;

    /** @var array 配置信息 */
    private $config;

    /** @var self[] 实例列表 */
    private static $instanceList = [];

    /** @var array event handler store [[$processor, $collectingLevel, $loggerId, $withTraceInfo, $lastOccursIndex],...] */
    private static $handlers = [];

    /** @var array event while handler store [[$triggerLevel, $processor, $collectingLevel, $loggerId, $withTraceInfo, $lastOccursIndex],...] */
    private static $whileHandlers = [];

    /** @var array log dumps */
    private static $logDumps = [];

    /**
     * 初始化操作
     * @param string|null $logId
     * @param array       $config
     */
    private function __construct(string $logId = null, array $config = [])
    {
        $this->id = $logId ?? static::DEFAULT_LOG_ID;
        $this->setConfig($config);
    }

    /**
     * 禁止克隆
     * User: Sweeper
     * Time: 2023/7/20 16:42
     * @return void
     */
    private function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * call as function default level debug
     * User: Sweeper
     * Time: 2023/7/20 16:44
     * @param ...$messages
     * @return mixed
     */
    public function __invoke(...$messages)
    {
        return call_user_func_array([$this, LoggerLevel::DEBUG], $messages);
    }

    /**
     * 在对象中调用一个不可访问方法时，__call() 会被调用
     * User: Sweeper
     * Time: 2023/7/20 16:50
     * @param string $name
     * @param array  $arguments
     * @return $this
     */
    public function __call(string $name, array $arguments)
    {
        $levelMethod = strtoupper($name);
        if (defined(LoggerLevel::class . "::$levelMethod")) {
            $level = constant(LoggerLevel::class . "::$levelMethod");

            $this->trigger($level, $arguments);

            return $this;
        }
        if ($levelMethod === 'TRIGGER') {
            $level = array_shift($arguments);

            $this->trigger($level, $arguments);

            return $this;
        }

        throw new LoggerException("Logger level no exists:{$levelMethod}");
    }

    /**
     * call static log method via default logger instance 在静态上下文中调用一个不可访问方法时，__callStatic() 会被调用。
     * User: Sweeper
     * Time: 2023/7/20 16:52
     * @param string $name
     * @param array  $arguments
     * @return false|null
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $levelMethod = strtoupper($name);
        if (defined(LoggerLevel::class . "::$levelMethod")) {
            $level = constant(LoggerLevel::class . "::$levelMethod");

            return static::instance()->trigger($level, $arguments);
        }
        if ($levelMethod === 'TRIGGER') {
            $level = array_shift($arguments);

            return static::instance()->trigger($level, $arguments);
        }

        throw new LoggerException("Logger level no exists:{$levelMethod}");
    }

    /**
     * 实例化对象(从实例化列表取出当前调用类的实例)
     * User: Sweeper
     * Time: 2023/7/20 16:56
     * @param string|null $id
     * @param array       $config
     * @param string|null $alias
     * @param bool        $dynamic
     * @return self
     */
    public static function instance(string $id = self::DEFAULT_LOG_ID, array $config = [], string $alias = null, bool $dynamic = true): self
    {
        return static::getInstance($id, $config, $alias, $dynamic);
    }

    /**
     * 定义获取对象实例的入口，返回该实例
     * User: Sweeper
     * Time: 2023/7/20 17:02
     * @param string      $id
     * @param array       $config
     * @param string|null $alias
     * @param bool        $dynamic 根据配置动态变化
     * @return self
     */
    public static function getInstance(string $id = self::DEFAULT_LOG_ID, array $config = [], string $alias = null, bool $dynamic = true): self
    {
        $logId = $id ?? static::DEFAULT_LOG_ID;
        $alias = $logId . '-' . ($alias ?? static::class);
        if ($dynamic) {
            $alias .= ':' . serialize($config);
        }
        // 判断是否已经存在实例化对象
        if (!isset(self::$instanceList[$alias])) {
            self::$instanceList[$alias] = new static($id, $config);// 不存在，则实例化一个
        }

        return self::$instanceList[$alias];
    }

    /**
     * 获取配置信息
     * User: Sweeper
     * Time: 2023/7/20 17:08
     * @param string|null $key
     * @return array|mixed|null
     */
    public function getConfig(string $key = null)
    {
        return $key ? ($this->config[$key] ?? null) : $this->config;
    }

    /**
     * 设置配置信息
     * User: Sweeper
     * Time: 2023/7/20 17:08
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * register handler
     * User: Sweeper
     * Time: 2023/7/20 17:30
     * @param LoggerInterface $handler
     * @param string          $collectingLevel
     * @param bool            $withTraceInfo
     * @param string|null     $uniqueKey
     * @return $this
     */
    public function register(LoggerInterface $handler, string $collectingLevel = LoggerLevel::INFO, bool $withTraceInfo = false, string $uniqueKey = null): self
    {
        self::$handlers[$uniqueKey ?? md5(microtime(true))] = [$handler, $collectingLevel, $this->id, $withTraceInfo];

        return $this;
    }

    /**
     * register global handler
     * User: Sweeper
     * Time: 2023/7/20 17:36
     * @param LoggerInterface   $handler
     * @param string            $collectingLevel
     * @param array|string|null $loggerId specified logger instance id, or id list
     * @param bool              $withTraceInfo
     * @param string|null       $uniqueKey
     * @return void
     */
    public static function registerGlobal(LoggerInterface $handler, string $collectingLevel = LoggerLevel::INFO, $loggerId = null, bool $withTraceInfo = false, string $uniqueKey = null): void
    {
        self::$handlers[$uniqueKey ?? md5(microtime(true))] = [$handler, $collectingLevel, $loggerId, $withTraceInfo];
    }

    /**
     * register while handler
     * User: Sweeper
     * Time: 2023/7/20 17:39
     * @param string          $triggerLevel
     * @param LoggerInterface $handler
     * @param string          $collectingLevel
     * @param bool            $withTraceInfo
     * @param string|null     $uniqueKey
     * @return $this
     */
    public function registerWhile(string $triggerLevel, LoggerInterface $handler, string $collectingLevel = LoggerLevel::INFO, bool $withTraceInfo = false, string $uniqueKey = null): self
    {
        self::$whileHandlers[$uniqueKey ?? md5(microtime(true))] = [$triggerLevel, $handler, $collectingLevel, $this->id, $withTraceInfo, 0];

        return $this;
    }

    /**
     * register while log happens on specified trigger level
     * User: Sweeper
     * Time: 2023/7/20 17:41
     * @param string            $triggerLevel
     * @param LoggerInterface   $handler
     * @param string            $collectingLevel
     * @param array|string|null $loggerId specified logger instance id, or id list
     * @param bool              $withTraceInfo
     * @param string|null       $uniqueKey
     * @return void
     */
    public static function registerWhileGlobal(string $triggerLevel, LoggerInterface $handler, string $collectingLevel = LoggerLevel::INFO, $loggerId = null, bool $withTraceInfo = false, string $uniqueKey = null): void
    {
        self::$whileHandlers[$uniqueKey ?? md5(microtime(true))] = [$triggerLevel, $handler, $collectingLevel, $loggerId, $withTraceInfo, 0];
    }

    /**
     * clear log dumps
     * User: Sweeper
     * Time: 2022/12/16 11:44
     * @return void
     */
    public static function clearDump(): void
    {
        self::$logDumps = [];
    }

    /**
     * trigger log action
     * User: Sweeper
     * Time: 2023/7/20 17:11
     * @param string $level
     * @param array  $messages
     * @return bool
     */
    private function trigger(string $level, array $messages): bool
    {
        $traceInfo = [];
        // trace信息补全，取出注册 日志处理器 是否附加 trace 信息
        if (in_array(true, array_column(self::$handlers, 3), true) || in_array(true, array_column(self::$whileHandlers, 4), true)) {
            $tmp       = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $this->getConfig('debug_backtrace_limit') ?? 3);
            $traceInfo = $tmp[1];
        }

        // 普通单次绑定事件触发
        foreach (self::$handlers as [$handler, $collectingLevel, $loggerId, $withTraceInfo]) {
            $matchId = !$loggerId || (is_array($loggerId) && in_array($this->id, $loggerId, true)) || $loggerId === $this->id;
            if ($matchId && LoggerLevel::levelCompare($level, $collectingLevel) >= 0 && $handler($messages, $level, $this->id, $traceInfo) === false) {
                return false;
            }
        }

        // 条件绑定事件触发
        if (self::$whileHandlers) {
            // 注意注册白名单会有内存泄漏风险
            self::$logDumps[] = [$messages, $level, $traceInfo, $this->id];
            foreach (self::$whileHandlers as $k => [$triggerLevel, $handler, $collectingLevel, $loggerId, $withTraceInfo, $lastOccursIndex]) {
                $matchId = !$loggerId || (is_array($loggerId) && in_array($this->id, $loggerId, true)) || $loggerId === $this->id;
                if ($matchId && LoggerLevel::levelCompare($level, $triggerLevel) >= 0) {
                    $dumps = array_slice(self::$logDumps, $lastOccursIndex);
                    // update last trigger dumping data index
                    self::$whileHandlers[$k][5] = $lastOccursIndex + count($dumps);
                    array_walk($dumps, function($data) use ($collectingLevel, $handler) {
                        [$message, $level, $traceInfo, $loggerId] = $data;
                        if (LoggerLevel::levelCompare($level, $collectingLevel) >= 0) {
                            $handler($message, $level, $loggerId, $traceInfo);
                        }
                    });
                }
            }
        }

        return $this->save($level, $messages);
    }

    /**
     * Logs with an arbitrary level.
     * User: Sweeper
     * Time: 2023/10/11 23:03
     * @param       $level
     * @param       $message
     * @param array $context
     * @return bool
     */
    public function log($level, $message, array $context = [])
    {
        if (is_string($message) && !empty($context)) {
            $replace = [];
            foreach ($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }
            $message = strtr($message, $replace);
        }

        return $this->trigger($level, (array)$message);
    }

    /**
     * 保存日志
     * User: Sweeper
     * Time: 2023/7/20 19:01
     * @param $level
     * @param $messages
     * @return bool
     */
    public function save($level, $messages): bool
    {
        return true;
    }

    /**
     * debugger
     * User: Sweeper
     * Time: 2023/7/21 17:34
     * @return array
     */
    public function __debugInfo()
    {
        return [
            '$instanceList'  => self::$instanceList,
            '$handlers'      => self::$handlers,
            '$whileHandlers' => self::$whileHandlers,
            '$logDumps'      => self::$logDumps,
        ];
    }

}
