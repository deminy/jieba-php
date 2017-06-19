#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Helper;
use Jieba\MultiArray;

$trie = new MultiArray();

Helper::readFile(
    Helper::getDictFilePath('dict.big.txt'),
    function (string $line, MultiArray $trie) {
        $explode_line = explode(' ', trim($line));
        $word         = $explode_line[0];
        $l            = mb_strlen($word, 'UTF-8');
        $word_c       = [];
        for ($i = 0; $i < $l; $i++) {
            $c = mb_substr($word, $i, 1, 'UTF-8');
            array_push($word_c, $c);
        }
        $word_c_key = implode('.', $word_c);
        $trie->set($word_c_key, ['end' => '']);
    },
    $trie
);

file_put_contents(Helper::getDictFilePath('dict.big.txt.json'),       json_encode($trie->storage));
file_put_contents(Helper::getDictFilePath('dict.big.txt.cache.json'), json_encode($trie->cache));
