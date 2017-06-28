<?php

namespace Jieba\Factory;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggerFactory
{
    /**
     * @var LoggerInterface
     */
    protected static $logger;

    /**
     * @param LoggerInterface $logger
     * @return LoggerInterface
     */
    public static function setLogger(LoggerInterface $logger):  LoggerInterface
    {
        self::$logger = $logger;

        return self::$logger;
    }

    /**
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        if (empty(self::$logger)) {
            if (class_exists('\Monolog\Logger')) {
                $handler = new StreamHandler(self::getLogFile('jieba.log'), Logger::DEBUG);
                $handler->setFormatter(new LineFormatter(null, null, true, true));

                self::setLogger(new Logger('jieba', [$handler]));
            } else {
                self::setLogger(new NullLogger());
            }
        }

        return self::$logger;
    }

    /**
     * @param string $filename
     * @return string
     */
    protected static function getLogFile(string $filename): string
    {
        return (sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename);
    }
}
