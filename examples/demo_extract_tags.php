#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Helper;
use Jieba\Jieba;
use Jieba\JiebaAnalyse;
use Jieba\Options;
use Jieba\Option\Dict;
use Jieba\Option\Mode;

$jieba = new Jieba(
    (new Options())->setDict(new Dict(Dict::BIG))->setMode(new Mode(Mode::TEST))
);
$tags = JiebaAnalyse::singleton()->extractTags($jieba->cut(Helper::getDictFileContent('lyric.txt')), 10);
var_dump($tags);
