#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Serializer\Json;
use Jieba\Serializer\Msgpack;
use Jieba\Serializer\SerializerFactory;

SerializerFactory::setSerializer(new Json());
var_dump((new Jieba())->cut("怜香惜玉也得要看对象啊！"));

if (extension_loaded('msgpack')) {
    SerializerFactory::setSerializer(new Msgpack());
    var_dump((new Jieba())->cut("怜香惜玉也得要看对象啊！"));
}
