<?php

namespace Sweeper\Logger\traits;

/**
 * 单例模式
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/24 11:15
 * @Path \Sweeper\Logger\traits\SinglePattern
 */
trait SinglePattern
{

    /** @var array 配置信息 */
    private $config;

    /** @var array 实例列表 */
    private static $instanceList = [];

    /**
     * 初始化操作
     * @param array $config
     */
    private function __construct(array $config = [])
    {
        $this->config = $config;
    }

    private function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * 实例化对象(从实例化列表取出当前调用类的实例)
     * User: Sweeper
     * Time: 2023/1/9 11:01
     * @param array       $config
     * @param string|null $name
     * @param bool        $dynamic
     * @return static
     */
    public static function instance(array $config = [], string $name = null, bool $dynamic = true): self
    {
        return static::getInstance($config, $name, $dynamic);
    }

    /**
     * 定义获取对象实例的入口，返回该实例
     * User: Sweeper
     * Time: 2023/1/9 11:01
     * @param array       $config
     * @param string|null $alias
     * @param bool        $dynamic 根据配置动态变化
     * @return mixed|static
     */
    public static function getInstance(array $config = [], string $alias = null, bool $dynamic = true)
    {

        $alias = $alias ?? static::class;
        if ($dynamic) {
            $alias .= ':' . md5(json_encode($config));
        }
        // 判断是否已经存在实例化对象
        if (!isset(self::$instanceList[$alias])) {
            self::$instanceList[$alias] = new static($config);// 不存在，则实例化一个
        }

        return self::$instanceList[$alias];
    }

    /**
     * User: Sweeper
     * Time: 2023/7/24 11:17
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->getConfig('version') ?: '';
    }

    /**
     * User: Sweeper
     * Time: 2023/1/9 11:02
     * @param string $version
     * @return static
     */
    public function setVersion(string $version): self
    {
        $this->setConfig(array_replace($this->getConfig(), ['version' => $version]));

        return $this;
    }

    /**
     * 获取配置信息
     * User: Sweeper
     * Time: 2023/1/9 11:02
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
     * Time: 2023/1/9 11:02
     * @param array $config
     * @return static
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

}
