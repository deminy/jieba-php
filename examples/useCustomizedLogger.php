#!/usr/bin/env php
<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Factory\LoggerFactory;
use Jieba\Jieba;
use Psr\Log\NullLogger;

// The logger object set here will be used by \Jieba classes.
LoggerFactory::setLogger(new NullLogger());

$jieba = new Jieba();
print_r($jieba->cut("怜香惜玉也得要看对象啊！"));
