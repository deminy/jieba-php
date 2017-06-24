<?php

namespace Jieba\Serializer;

/**
 * Class Json
 *
 * @package Jieba\Serializer
 */
class Json implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function encode($data): string
    {
        return json_encode($data);
    }

    /**
     * @inheritdoc
     */
    public function decode(string $data)
    {
        return json_decode($data, true);
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return SerializerFactory::JSON;
    }

    /**
     * @inheritdoc
     */
    public static function available(): bool
    {
        return true;
    }
}
