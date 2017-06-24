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

    const CLASSES = [
        self::BSON    => Bson::class,
        self::JSON    => Json::class,
        self::MSGPACK => Msgpack::class,
    ];

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
     * @see \Jieba\Serializer\SerializerInterface::available()
     */
    public static function getSerializer(string $type = self::DEFAULT): SerializerInterface
    {
        if (self::DEFAULT == $type) {
            if (!isset(self::$serializer)) {
                self::$serializer = self::getSerializer(self::getAllAvailableTypes()[0]);
            }

            return self::$serializer;
        }

        if (array_key_exists($type, self::CLASSES)) {
            $className = self::CLASSES[$type];
            if ($className::available()) {
                return new $className();
            }

            throw new Exception("serializer '{$type}' not available");
        } else {
            throw new Exception("invalid serializer type '{$type}'");
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
     * @see \Jieba\Serializer\SerializerInterface::available()
     * @todo add benchmark on dictionary files.
     */
    public static function getAllAvailableTypes(): array
    {
        $serializers = [];

        foreach (self::CLASSES as $type => $className) {
            if ($className::available()) {
                $serializers[] = $type;
            }
        }

        return $serializers;
    }
}
