#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Data\MultiArray;
use Jieba\DictHelper;
use Jieba\Helper;
use Jieba\Options\Dict;
use Jieba\Serializer\SerializerFactory;

$dict       = new Dict();
foreach (Dict::VALID_DICTIONARIES as $dictName) {
    $trie = new MultiArray();

    Helper::readFile(
        $dict->setDict($dictName)->getDictFilePath(),
        function (string $line) use ($trie) {
            DictHelper::parseDictLineForTrie($line, $trie);
        }
    );

    foreach (SerializerFactory::getAllAvailableTypes() as $type) {
        $serializer = SerializerFactory::setSerializer(SerializerFactory::getSerializer($type));

        $file = $dict->getDictFilePath(Dict::EXT_JSON);
        file_put_contents($file, $serializer->encode($trie->storage));
        echo "    file generated: {$file}\n";

        $file = $dict->getDictFilePath(Dict::EXT_CACHE_JSON);
        file_put_contents($file, $serializer->encode($trie->cache));
        echo "    file generated: {$file}\n";
    }
}
