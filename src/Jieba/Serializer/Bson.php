<?php

namespace Jieba\Serializer;

/**
 * Class Bson
 *
 * @package Jieba\Serializer
 * @see http://php.net/mongodb
 */
class Bson implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function encode($data): string
    {
        return \MongoDB\BSON\fromPHP($data);
    }

    /**
     * @inheritdoc
     * @see http://php.net/manual/en/mongodb.persistence.deserialization.php
     */
    public function decode(string $data)
    {
        return \MongoDB\BSON\toPHP($data, ['root' => 'array', 'document' => 'array']);
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return SerializerFactory::BSON;
    }

    /**
     * @inheritdoc
     */
    public static function available(): bool
    {
        return extension_loaded('mongodb');
    }
}
