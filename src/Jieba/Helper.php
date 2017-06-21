<?php

namespace Jieba;

use Closure;

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
    public static function getDictFileContent(string $basename, string $basePath = null): string
    {
        return file_get_contents(self::getDictFilePath($basename, $basePath));
    }

    /**
     * @param string $basename
     * @param string $basePath
     * @return string
     */
    public static function getModelFilePath(string $basename, string $basePath = null): string
    {
        return (($basePath ?: self::getModelBasePath()) . $basename);
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
     * @return string
     */
    public static function getModelBasePath(): string
    {
        return dirname(__DIR__, 2) . '/model/';
    }

    /**
     * @return string
     */
    public static function getDictBasePath(): string
    {
        return dirname(__DIR__, 2) . '/dict/';
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
}
