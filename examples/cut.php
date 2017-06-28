#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Posseg;

$seg_list = (new Posseg(new Jieba()))->cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。")->toArray();
print_r($seg_list);
