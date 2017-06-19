#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Options;
use Jieba\Option\Dict;

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::BIG)));

$seg_list = $jieba->cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = $jieba->cut("憐香惜玉也得要看對象啊！");
var_dump($seg_list);
