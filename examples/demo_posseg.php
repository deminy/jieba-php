#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Finalseg;
use Jieba\Posseg;

Jieba::init(array('mode'=>'test','dict'=>'big'));
Finalseg::init();
Posseg::init();

$seg_list = Posseg::cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。");
var_dump($seg_list);

$seg_list = Posseg::posTagReadable($seg_list);
var_dump($seg_list);

$seg_list = Posseg::cut("這是一個伸手不見五指的黑夜。我叫孫悟空，我愛北京，我愛Python和C++");
var_dump($seg_list);

$seg_list = Posseg::posTagReadable($seg_list);
var_dump($seg_list);
