<?php

namespace Sweeper\Logger\output;

use Sweeper\Logger\LoggerLevel;

/**
 * 控制台输出
 * Created by PhpStorm.
 * User: Sweeper
 * Time: 2023/7/21 15:32
 * @Path \logger\output\ConsoleOutput
 */
class ConsoleOutput extends CommonAbstract
{

    private $colorless;

    private const FORE_COLOR_MAP = [
        'default'      => '0:39',
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    ];

    private const BACK_COLOR_MAP = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];

    private const LEVEL_COLORS = [
        LoggerLevel::DEBUG     => ['dark_gray', null],
        LoggerLevel::INFO      => ['white', null],
        LoggerLevel::NOTICE    => ['brown', null],
        LoggerLevel::WARNING   => ['yellow', null],
        LoggerLevel::ERROR     => ['red', null],
        LoggerLevel::CRITICAL  => ['purple', null],
        LoggerLevel::ALERT     => ['light_cyan', null],
        LoggerLevel::EMERGENCY => ['cyan', null],
    ];

    /**
     * get console text colorize
     * @param      $text
     * @param null $foreColor
     * @param null $backColor
     * @return string
     */
    public static function consoleColor($text, $foreColor = null, $backColor = null): string
    {
        $colorStr = '';
        if ($foreColor) {
            $colorStr .= "\033[" . static::FORE_COLOR_MAP[$foreColor] . 'm';
        }
        if ($backColor) {
            $colorStr .= "\033[" . static::BACK_COLOR_MAP[$backColor] . 'm';
        }
        if ($colorStr) {
            return $colorStr . $text . "\033[0m";
        }

        return $text;
    }

    /**
     * ConsoleOutput constructor.
     * @param bool $colorless
     */
    public function __construct(bool $colorless = false)
    {
        $this->colorless = $colorless;
    }

    public function output($messages, string $level, string $loggerId, array $traceInfo)
    {
        $lv_str = strtoupper($level);
        if (!$this->colorless) {
            $lv_str = static::consoleColor($lv_str, static::LEVEL_COLORS[$level][0], static::LEVEL_COLORS[$level][1]);
        }
        echo self::formatAsText($messages, $lv_str, $loggerId, $traceInfo), PHP_EOL;
    }

}
