#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Options;
use Jieba\Option\Dict;

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::SMALL)));
$seg_list = $jieba->cut("李小福是创新办主任也是云计算方面的专家");
var_dump($seg_list);

$jieba->loadUserDict(dirname(__DIR__) . '/tests/dict/user_dict.txt');

$seg_list = $jieba->cut("李小福是创新办主任也是云计算方面的专家");
var_dump($seg_list);
