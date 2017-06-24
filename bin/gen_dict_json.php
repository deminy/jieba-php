#!/usr/bin/env php
<?php
/**
 * Generate dictionary files.
 *
 * Usage:
 *     ./bin/gen_dict_json.php                   # generate dictionary files in first available format only.
 *     ./bin/gen_dict_json.php bson              # generate dictionary files in BSON format only.
 *     ./bin/gen_dict_json.php json              # generate dictionary files in JSON format only.
 *     ./bin/gen_dict_json.php msgpack           # generate dictionary files in Msgpack format only.
 *     ./bin/gen_dict_json.php bson json msgpack # generate dictionary files in BSON, JSON and Msgpack formats.
 *     ./bin/gen_dict_json.php all               # generate dictionary files in all available formats.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Data\MultiArray;
use Jieba\DictHelper;
use Jieba\Helper;
use Jieba\Options\Dict;
use Jieba\Serializer\SerializerFactory;

$allTypes = SerializerFactory::getAllAvailableTypes();
$types    = $argv;
array_shift($types);
if (!empty($types)) {
    foreach ($types as &$type) {
        $type = strtolower(trim($type));
    }
    unset($type);

    $types = (in_array('all', $types) ? $allTypes : array_intersect($allTypes, $types));
} else {
    $types = [$allTypes[0]];
}

$dict = new Dict();
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
