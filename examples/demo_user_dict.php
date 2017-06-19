#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Helper;
use Jieba\Jieba;
use Jieba\Options;
use Jieba\Option\Dict;
use Jieba\Option\Mode;

$jieba = new Jieba(
    (new Options())->setDict(new Dict(Dict::SMALL))->setMode(new Mode(Mode::TEST))
);
$seg_list = $jieba->cut("李小福是创新办主任也是云计算方面的专家");
var_dump($seg_list);

$jieba->loadUserDict(Helper::getDictFilePath('user_dict.txt'));

$seg_list = $jieba->cut("李小福是创新办主任也是云计算方面的专家");
var_dump($seg_list);
