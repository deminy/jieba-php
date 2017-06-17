#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Finalseg;

Jieba::init(['mode' => 'default', 'dict' => 'big']);
Finalseg::init();

$seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = Jieba::cut("憐香惜玉也得要看對象啊！");
var_dump($seg_list);
