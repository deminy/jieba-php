#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Helper;
use Jieba\MultiArray;
use Jieba\Option\Dict;

$dict = new Dict();
foreach (Dict::VALID_DICTIONARIES as $dictName) {
    $trie = new MultiArray();

    Helper::readFile(
        $dict->setDict($dictName)->getDictFilePath(),
        function (string $line, MultiArray $trie) {
            $explode_line = explode(' ', trim($line));
            $word         = $explode_line[0];
            $l            = mb_strlen($word);
            $word_c       = [];
            for ($i = 0; $i < $l; $i++) {
                $c = mb_substr($word, $i, 1);
                array_push($word_c, $c);
            }
            $word_c_key = implode('.', $word_c);
            $trie->set($word_c_key, ['end' => '']);
        },
        $trie
    );

    file_put_contents(Helper::getDictFilePath("{$dict->getDictFileName()}.json"),       json_encode($trie->storage));
    file_put_contents(Helper::getDictFilePath("{$dict->getDictFileName()}.cache.json"), json_encode($trie->cache));
}
