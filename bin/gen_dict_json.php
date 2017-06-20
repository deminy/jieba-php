#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\DictHelper;
use Jieba\Helper;
use Jieba\MultiArray;
use Jieba\MultiByteString;
use Jieba\Option\Dict;

$dict = new Dict();
foreach (Dict::VALID_DICTIONARIES as $dictName) {
    $trie = new MultiArray();

    Helper::readFile(
        $dict->setDict($dictName)->getDictFilePath(),
        function (string $line) use ($trie) {
            DictHelper::parseDictLineForTrie($line, $trie);
        }
    );

    file_put_contents(Helper::getDictFilePath("{$dict->getDictFileName()}.json"),       json_encode($trie->storage));
    file_put_contents(Helper::getDictFilePath("{$dict->getDictFileName()}.cache.json"), json_encode($trie->cache));
}
