<?php

namespace Jieba;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Logger
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
                $handler = new StreamHandler(self::getLogFile('jieba.log'), MonologLogger::DEBUG);
                $handler->setFormatter(new LineFormatter(null, null, true, true));

                self::$logger = new MonologLogger('jieba', [$handler]);
            } else {
                self::$logger = new NullLogger();
            }
        }

        return self::$logger;
    }

    /**
     * @return string
     */
    protected static function getLogFile($filename): string
    {
        return (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $filename);
    }
}
