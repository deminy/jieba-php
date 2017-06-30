#!/usr/bin/env php
<?php
/**
 * Generate IDF files.
 *
 * Usage:
 *     ./bin/generateIdfFile.php                   # generate IDF files in first available format only.
 *     ./bin/generateIdfFile.php bson              # generate IDF files in BSON format only.
 *     ./bin/generateIdfFile.php json              # generate IDF files in JSON format only.
 *     ./bin/generateIdfFile.php msgpack           # generate IDF files in Msgpack format only.
 *     ./bin/generateIdfFile.php bson json msgpack # generate IDF files in BSON, JSON and Msgpack formats.
 *     ./bin/generateIdfFile.php all               # generate IDF files in all available formats.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Helper\Helper;
use Jieba\Options\Dict;
use Jieba\Serializer\SerializerFactory;
use League\Csv\Reader;

$idfFreq = [];
Reader::createFromPath(Helper::getDictFilePath('idf.txt'))
    ->setDelimiter(' ')
    ->fetchAll(
        function (array $row) use (&$idfFreq) {
            if (!empty($row)) {
                $word   = $row[0];
                $freq   = (float) $row[1];

                $idfFreq[$word] = $freq;
            }
        }
    );

$dict     = new Dict();
$fileType = Dict::SERIALIZED;
foreach (SerializerFactory::getAvailableTypes(array_slice($argv, 1)) as $type) {
    $serializer = SerializerFactory::setSerializer(SerializerFactory::getSerializer($type));
    $file       = Helper::getDictBasePath($fileType) . 'idf.' . SerializerFactory::EXTENSIONS[$type];
    file_put_contents($file, $serializer->encode($idfFreq));
    echo "    file generated: {$file}\n";
}
