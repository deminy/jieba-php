#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Options\Options;
use Jieba\Options\Dict;

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::SMALL)));

$seg_list = $jieba->cut("憐香惜玉也得要看對象啊！");
echo "\n使用小词典对繁体句子进行分词（成语\"憐香惜玉\"无法被正确切分）：\n";
print_r($seg_list);

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::NORMAL)));

$seg_list = $jieba->cut("憐香惜玉也得要看對象啊！");
echo "\n使用默认词典对繁体句子进行分词（成语\"憐香惜玉\"无法被正确切分）：\n";
print_r($seg_list);

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::BIG)));

$seg_list = $jieba->cut("憐香惜玉也得要看對象啊！");
echo "\n使用繁体词典对繁体句子进行分词（成语\"憐香惜玉\"可以被正确切分）：\n";
print_r($seg_list);
