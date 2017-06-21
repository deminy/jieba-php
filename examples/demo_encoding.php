#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Wrapper;

function testCut() {
    var_dump((new Jieba())->cut("怜香惜玉也得要看对象啊！"));
}

// Here we set default encoding to "iso-8859-1". Under this encoding Chinese characters won't be parsed properly.
mb_internal_encoding('iso-8859-1');

// This function call prints garbled Chinese characters because of improper encoding "iso-8859-1" used.
testCut();

// This function call prints Chinese characters as expected because the function call is executed where UTF-8 is set
// as the internal encoding.
// After the execution, previous internal encoding (iso-8859-1) will be recovered.
Wrapper::run(
    function () {
        testCut();
    }
);

// This function call prints garbled Chinese characters because of improper encoding "iso-8859-1" used.
testCut();
