#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Analyse\Analyse;
use Jieba\Jieba;
use Jieba\Options\Options;
use Jieba\Options\Dict;

$sentence = file_get_contents(dirname(__DIR__) . '/tests/dict/lyric.txt');
foreach ([Dict::BIG, Dict::SMALL] as $dict) {
    $tags = Analyse::singleton()->extractTags(
        (new Jieba((new Options())->setDict(new Dict(Dict::BIG))))->cut($sentence),
        10
    );
    echo "\ncut sentence with dictionary '{$dict}':\n";
    print_r($tags);
}
