<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Finalseg;
use Jieba\JiebaAnalyse;
use Jieba\Posseg;

Jieba::init();
Finalseg::init();
JiebaAnalyse::init();
Posseg::init();
