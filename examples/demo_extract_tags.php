#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Finalseg;
use Jieba\JiebaAnalyse;

Jieba::init(array('mode'=>'test','dict'=>'big'));
Finalseg::init();
JiebaAnalyse::init();

$top_k   = 10;
$content = file_get_contents(dirname(__DIR__) . '/src/dict/lyric.txt', 'r');
$tags    = JiebaAnalyse::extractTags($content, $top_k);

var_dump($tags);
