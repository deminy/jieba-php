#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Options\Options;
use Jieba\Options\Dict;
use Jieba\Posseg;

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::BIG)));
$posseg = new Posseg($jieba);

$seg_list = $posseg->cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。")->toArray();
var_dump($seg_list);

$seg_list = $posseg->posTagReadable($seg_list);
var_dump($seg_list);

$seg_list = $posseg->cut("這是一個伸手不見五指的黑夜。我叫孫悟空，我愛北京，我愛Python和C++")->toArray();
var_dump($seg_list);

$seg_list = $posseg->posTagReadable($seg_list);
var_dump($seg_list);
