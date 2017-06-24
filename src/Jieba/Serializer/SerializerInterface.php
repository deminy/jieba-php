<?php

namespace Jieba\Serializer;

/**
 * Class AbstractSerializer
 *
 * @package Jieba\Serializer
 */
interface SerializerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function encode($data): string;

    /**
     * @param string $data
     * @return mixed
     */
    public function decode(string $data);

    /**
     * @return string
     * @see \Jieba\Serializer\SerializerFactory::JSON
     * @see \Jieba\Serializer\SerializerFactory::MSGPACK
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public static function available(): bool;
}
