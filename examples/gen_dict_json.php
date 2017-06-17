#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\MultiArray;

$content = fopen(dirname(__DIR__) . '/src/dict/dict.big.txt', 'r');

$trie = new MultiArray(array());

while (($line = fgets($content)) !== false) {
    echo $line;

    $explode_line = explode(" ", trim($line));
    $word = $explode_line[0];
    $l = mb_strlen($word, 'UTF-8');
    $word_c = array();
    for ($i=0; $i<$l; $i++) {
        $c = mb_substr($word, $i, 1, 'UTF-8');
        array_push($word_c, $c);
    }
    $word_c_key = implode('.', $word_c);
    $trie->set($word_c_key, array("end"=>""));
}

file_put_contents(dirname(__DIR__) . '/src/dict/dict.big.txt.json', json_encode($trie->storage));
file_put_contents(dirname(__DIR__) . '/src/dict/dict.big.txt.cache.json', json_encode($trie->cache));
