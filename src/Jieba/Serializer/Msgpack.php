<?php

namespace Jieba\Serializer;

use Jieba\Exception;

/**
 * Class Msgpack
 *
 * @package Jieba\Serializer
 */
class Msgpack implements SerializerInterface
{
    /**
     * Msgpack constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        if (!extension_loaded('msgpack')) {
            throw new Exception('PHP extension "msgpack" not enabled');
        }
    }

    /**
     * @inheritdoc
     */
    public function encode($data): string
    {
        return msgpack_pack($data);
    }

    /**
     * @inheritdoc
     */
    public function decode(string $data)
    {
        return msgpack_unpack($data);
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return SerializerFactory::MSGPACK;
    }
}
