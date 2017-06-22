<?php

namespace Jieba\Serializer;

/**
 * Class SerializerFactory
 *
 * @package Jieba
 */
class SerializerFactory
{
    const DEFAULT = 'default';
    const JSON    = 'json';
    const MSGPACK = 'msgpack';

    /**
     * @var SerializerInterface
     */
    protected static $serializer;

    /**
     * @param string $type
     * @return SerializerInterface
     * @throws Exception
     */
    public static function getSerializer(string $type = self::DEFAULT)
    {
        if (self::DEFAULT == $type) {
            if (!isset(self::$serializer)) {
                self::$serializer = self::getSerializer((extension_loaded('msgpack') ? self::MSGPACK : self::JSON));
            }

            return self::$serializer;
        }

        switch ($type) {
            case self::JSON:
                return new Json();
                break;
            case self::MSGPACK:
                return new Msgpack();
                break;
            default:
                throw new Exception("invalid serializer type '{$type}'");
                break;
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @return SerializerInterface
     */
    public static function setSerializer(SerializerInterface $serializer): SerializerInterface
    {
        self::$serializer = $serializer;

        return $serializer;
    }

    /**
     * @return array
     */
    public static function getAllAvailableTypes(): array
    {
        $serializers = [self::JSON];

        if (extension_loaded('msgpack')) {
            $serializers[] = self::MSGPACK;
        }

        return $serializers;
    }
}
