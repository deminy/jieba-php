<?php

namespace Jieba\Helper;

use Closure;
use Jieba\Exception;
use Jieba\Options\Dict;
use Jieba\Serializer\SerializerFactory;

/**
 * Class Helper
 *
 * @package Jieba
 */
class Helper
{
    /**
     * @var array
     */
    protected static $userDictNames = [];

    /**
     * @param string $filename
     * @param Closure $callback
     * @param array ...$params
     * @return void
     * @throws Exception
     */
    public static function readFile(string $filename, Closure $callback, ...$params)
    {
        if (!is_file($filename)) {
            throw new Exception("path '{$filename}' does not point to a file");
        }
        if (!is_readable($filename)) {
            throw new Exception("file '{$filename}' is not readable");
        }

        $content = fopen($filename, 'r');
        while (($line = fgets($content)) !== false) {
            $callback($line, ...$params);
        }
        fclose($content);
    }

    /**
     * @param string $basename
     * @param string $basePath
     * @return mixed
     */
    public static function loadModel(string $basename, string $basePath = null)
    {
        return json_decode(self::getModelFileContent($basename, $basePath), true);
    }

    /**
     * @param string $basename
     * @param string $basePath
     * @return string
     */
    public static function getModelFileContent(string $basename, string $basePath = null): string
    {
        return file_get_contents(self::getModelFilePath($basename, $basePath));
    }

    /**
     * @param string $basename
     * @param string $basePath
     * @return string
     */
    public static function getModelFilePath(string $basename, string $basePath = null): string
    {
        $path = (($basePath ?: self::getDataBasePath()) . $basename . '.' . SerializerFactory::getExtension());
        self::createDirIfNeeded(pathinfo($path, PATHINFO_DIRNAME));

        return $path;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getDataBasePath(): string
    {
        return self::createDirIfNeeded(dirname(__DIR__, 3) . '/data/');
    }

    /**
     * @param string $basename
     * @param string $basePath
     * @return string
     */
    public static function getDictFilePath(string $basename, string $basePath = null): string
    {
        return (($basePath ?: self::getDictBasePath()) . $basename);
    }

    /**
     * @param string $fileType
     * @return string
     */
    public static function getDictBasePath(string $fileType = null): string
    {
        switch ($fileType) {
            case Dict::SERIALIZED:
            case Dict::SERIALIZED_AND_CACHED:
                $dir = dirname(__DIR__, 3) . '/data/dict/' . SerializerFactory::getSerializer()->getType() . '/';
                break;
            case Dict::DEFAULT:
            default:
                $dir = dirname(__DIR__, 3) . '/data/dict/';
                break;
        }

        return self::createDirIfNeeded($dir);
    }

    /**
     * @return array
     */
    public static function getUserDictNames(): array
    {
        return self::$userDictNames;
    }

    /**
     * @param array $userDictNames
     */
    public static function setUserDictNames(array $userDictNames)
    {
        self::$userDictNames = $userDictNames;
    }

    /**
     * @param string $userDictName
     * @return array
     */
    public static function addUserDictName(string $userDictName)
    {
        array_push(self::$userDictNames, $userDictName);

        return self::$userDictNames;
    }

    /**
     * @param string $dir
     * @return string
     */
    protected static function createDirIfNeeded(string $dir): string
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }
}
