#!/usr/bin/env php
<?php
/**
 * Generate dictionary files.
 *
 * Usage:
 *     ./bin/generateDictFiles.php                   # generate dictionary files in first available format only.
 *     ./bin/generateDictFiles.php bson              # generate dictionary files in BSON format only.
 *     ./bin/generateDictFiles.php json              # generate dictionary files in JSON format only.
 *     ./bin/generateDictFiles.php msgpack           # generate dictionary files in Msgpack format only.
 *     ./bin/generateDictFiles.php bson json msgpack # generate dictionary files in BSON, JSON and Msgpack formats.
 *     ./bin/generateDictFiles.php all               # generate dictionary files in all available formats.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Data\MultiArray;
use Jieba\Helper\DictHelper;
use Jieba\Helper\Helper;
use Jieba\Options\Dict;
use Jieba\Serializer\SerializerFactory;

$types = SerializerFactory::getAvailableTypes(array_slice($argv, 1));
$dict  = new Dict();
foreach (Dict::VALID_DICTIONARIES as $dictName) {
    $trie = new MultiArray();

    Helper::readFile(
        $dict->setDict($dictName)->getDictFilePath(),
        function (string $line) use ($trie) {
            DictHelper::parseDictLineForTrie($line, $trie);
        }
    );

    foreach ($types as $type) {
        $serializer = SerializerFactory::setSerializer(SerializerFactory::getSerializer($type));

        $file = $dict->getDictFilePath(Dict::SERIALIZED);
        file_put_contents($file, $serializer->encode($trie->storage));
        echo "    file generated: {$file}\n";

        $file = $dict->getDictFilePath(Dict::SERIALIZED_AND_CACHED);
        file_put_contents($file, $serializer->encode($trie->cache));
        echo "    file generated: {$file}\n";
    }
}
