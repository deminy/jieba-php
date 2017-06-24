#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Options\Options;
use Jieba\Options\Dict;

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::BIG)));

$seg_list = $jieba->cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = $jieba->cut("憐香惜玉也得要看對象啊！");
var_dump($seg_list);

echo "Full Mode: \n";
$seg_list = $jieba->cut("我来到北京清华大学", true);
var_dump($seg_list);

echo "Full Mode: \n";
$seg_list = $jieba->cut("我來到北京清華大學", true);
var_dump($seg_list);

echo "Default Mode: \n";
$seg_list = $jieba->cut("我来到北京清华大学", false);
var_dump($seg_list);

echo "Default Mode: \n";
$seg_list = $jieba->cut("我來到北京清華大學", false);
var_dump($seg_list);

$seg_list = $jieba->cut("他来到了网易杭研大厦");
var_dump($seg_list);

$seg_list = $jieba->cut("他來到了網易杭研大廈");
var_dump($seg_list);

$seg_list = $jieba->cutForSearch("小明硕士毕业于中国科学院计算所，后在日本京都大学深造");
var_dump($seg_list);

$seg_list = $jieba->cutForSearch("小明碩士畢業于中國科學院計算所，後在日本京都大學深造");
var_dump($seg_list);
