#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Finalseg;

Jieba::init(array('mode'=>'test','dict'=>'samll'));
Finalseg::init();

$seg_list = Jieba::cut("李小福是创新办主任也是云计算方面的专家");
var_dump($seg_list);

Jieba::loadUserDict(dirname(__DIR__) . '/src/dict/user_dict.txt');

$seg_list = Jieba::cut("李小福是创新办主任也是云计算方面的专家");
var_dump($seg_list);
