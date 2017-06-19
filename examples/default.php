#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;

$jieba = new Jieba();

$seg_list = $jieba->cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = $jieba->cut("我来到北京清华大学", true);
var_dump($seg_list); #全模式

$seg_list = $jieba->cut("我来到北京清华大学", false);
var_dump($seg_list); #默认精确模式

$seg_list = $jieba->cut("他来到了网易杭研大厦");
var_dump($seg_list);

$seg_list = $jieba->cutForSearch("小明硕士毕业于中国科学院计算所，后在日本京都大学深造"); #搜索引擎模式
var_dump($seg_list);
