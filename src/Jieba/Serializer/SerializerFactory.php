<?php

namespace Jieba\Serializer;

use Jieba\Exception;

/**
 * Class SerializerFactory
 *
 * @package Jieba
 */
class SerializerFactory
{
    const DEFAULT = 'default';
    const BSON    = 'bson';
    const JSON    = 'json';
    const MSGPACK = 'msgpack';

    const EXTENSIONS = [
        self::BSON    => 'bson',
        self::JSON    => 'json',
        self::MSGPACK => 'mp',
    ];

    /**
     * @var SerializerInterface
     */
    protected static $serializer;

    /**
     * @param string $type
     * @return SerializerInterface
     * @throws Exception
     */
    public static function getSerializer(string $type = self::DEFAULT): SerializerInterface
    {
        if (self::DEFAULT == $type) {
            if (!isset(self::$serializer)) {
                self::$serializer = self::getSerializer(self::getAllAvailableTypes()[0]);
            }

            return self::$serializer;
        }

        switch ($type) {
            case self::BSON:
                return new Bson();
                break;
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
     * Return a list of available serializer types ordered by performance. The first one is the fastest.
     *
     * @return array
     * @todo add benchmark on dictionary files.
     */
    public static function getAllAvailableTypes(): array
    {
        $serializers = [];

        if (extension_loaded('mongodb')) {
            $serializers[] = self::BSON;
        }
        $serializers[] = self::JSON;
        if (extension_loaded('msgpack')) {
            $serializers[] = self::MSGPACK;
        }

        return $serializers;
    }
}
