#!/usr/bin/env php
<?php
/**
 * This script is to encode/decode dictionary data with different serializers, like BSON, JSON, MessagePack, etc.
 *
 * If you want to use BSON, you need to have PHP extension "mongodb" installed first.
 * If you want to use MessagePack, you need to have PHP extension "msgpack" installed first.
 *
 * @see https://pecl.php.net/package/mongodb
 * @see https://pecl.php.net/package/msgpack
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Serializer\SerializerFactory;

foreach (SerializerFactory::getAllAvailableTypes() as $type) {
    SerializerFactory::setSerializer(SerializerFactory::getSerializer($type));

    echo "Now use {$type} as serializer to cut the sentence:\n";
        try {
            print_r((new Jieba())->cut("怜香惜玉也得要看对象啊！"));
        } catch (Exception $e) {
            echo
                "Error: please run following command first to generate dictionary files, then run this script again:\n",
                '       ', realpath( __DIR__ . '/../bin/gen_dict_json.php') . ' all',
                "\n";
            exit(-1);
        }
    echo "\n\n";
}
