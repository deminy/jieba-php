#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\JiebaAnalyse;
use Jieba\Options;
use Jieba\Option\Dict;

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::BIG)));
$tags = JiebaAnalyse::singleton()->extractTags(
    $jieba->cut(file_get_contents(dirname(__DIR__) . '/tests/dict/lyric.txt')),
    10
);
var_dump($tags);
