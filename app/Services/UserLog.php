<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Formatter\LineFormatter;

/**
 * Created by PhpStorm.
 * User: Sunlong
 * Date: 2017/7/12
 * Time: 17:27
 */
class UserLog
{
    /**
     * write
     * @return void
     */
    public static function debug($log)
    {
        self::write($log, Logger::DEBUG);
    }

    public static function info($log)
    {
        self::write($log, Logger::INFO);
    }

    public static function notice($log)
    {
        self::write($log, Logger::NOTICE);
    }

    public static function warning($log)
    {
        self::write($log, Logger::WARNING);
    }

    public static function error($log)
    {
        self::write($log, Logger::ERROR);
    }

    public static function critical($log)
    {
        self::write($log, Logger::CRITICAL);
    }

    public static function alert($log)
    {
        self::write($log, Logger::ALERT);
    }

    public static function emergency($log)
    {
        self::write($log, Logger::EMERGENCY);
    }

    private static function write($logtext = '', $level = Logger::INFO)
    {
        if (config('app.userlog')) {
            $log = new Logger('userlog');
            // handler init, making days separated logs
            $handler = new RotatingFileHandler(config('app.userlog_path'), 0, $level);
            // formatter, ordering log rows
            $handler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %extra% %context%\n"));
            // add handler to the logger
            $log->pushHandler($handler);
            // processor, adding URI, IP address etc. to the log
            $log->pushProcessor(new WebProcessor);
            // processor, memory usage
            $log->pushProcessor(new MemoryUsageProcessor);

            //如果没有用户登录则显示空
            $userinfo = " [] ";
            $user = Auth::user();
            if ($user) {
                $userinfo = ' [USERID:' . $user->id . '] ';
            }
            $log->addRecord($level, $logtext . $userinfo);
        }
    }
}