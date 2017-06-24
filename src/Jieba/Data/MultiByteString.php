<?php

namespace Jieba\Data;

/**
 * Class MultiByteString
 *
 * @package Jieba
 */
class MultiByteString
{
    /**
     * Extract all characters from a UTF-8 string to array of individual characters.
     *
     * @param string $string
     * @return array
     * @see http://php.net/manual/en/function.mb-split.php#117588
     */
    public static function toArray(string $string): array
    {
        return preg_split('//u', $string, null, PREG_SPLIT_NO_EMPTY);
    }
}
