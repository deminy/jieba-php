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
     * @return LoggerInterface
     * @todo refactor it to support customized logger.
     */
    public static function getLogger(): LoggerInterface
    {
        if (empty(self::$logger)) {
            if (class_exists('\Monolog\Logger')) {
                $handler = new StreamHandler(self::getLogFile('jieba.log'), Logger::DEBUG);
                $handler->setFormatter(new LineFormatter(null, null, true, true));

                self::$logger = new Logger('jieba', [$handler]);
            } else {
                self::$logger = new NullLogger();
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
