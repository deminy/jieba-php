<?php

namespace Jieba;

use League\Csv\Reader;

/**
 * Class DictHelper
 *
 * @package Jieba
 */
class DictHelper
{
    /**
     * Evaluate given row from a dictionary and adjust frequency of the word in it accordingly.
     *
     * @param string $line
     * @param string $word
     * @param array $originalFreq
     * @param float $total
     */
    public static function readDictLine(string $line, &$word, array &$originalFreq, float &$total)
    {
        $line = trim($line);
        if (!empty($line)) {
            $array  = explode(' ', $line);
            $word   = $array[0];
            $freq   = (float) $array[1];
            // $tag = $array[2];

            if (isset($originalFreq[$word])) {
                $total -= $originalFreq[$word];
            }
            $originalFreq[$word] = $freq;
            $total += $freq;
        }
    }

    /**
     * @param string $line
     * @param MultiArray $trie
     */
    public static function parseDictLineForTrie(string $line, MultiArray $trie)
    {
        $line = trim($line);
        if (!empty($line)) {
            $array = explode(' ', $line);
            $word  = $array[0];
            $trie->set(
                implode('.', MultiByteString::toArray($word)),
                [
                    'end' => '',
                ]
            );
        }
    }

    /**
     * Go through words in given dictionary and return word frequency back.
     *
     * @param string $basename
     * @return array
     */
    public static function getIdfFreq(string $basename)
    {
        $idfFreq = [];

        Reader::createFromPath(Helper::getDictFilePath($basename))
            ->setDelimiter(' ')
            ->fetchAll(
                function (array $row) use (&$idfFreq) {
                    if (!empty($row)) {
                        $word   = $row[0];
                        $freq   = (float) $row[1];
                        // $tag = $row[2];

                        $idfFreq[$word] = $freq;
                    }
                }
            );

        return $idfFreq;
    }

    /**
     * Go through words in given dictionary and add parsed tags to variable $wordTags.
     *
     * @param string $filename
     * @param array $wordTags
     * @return void
     */
    public static function addWordTags(string $filename, array &$wordTags)
    {
        Reader::createFromPath($filename)
            ->setDelimiter(' ')
            ->fetchAll(
                function (array $row) use (&$wordTags) {
                    if (!empty($row)) {
                        $word    = $row[0];
                        // $freq = (float) $row[1];
                        $tag     = $row[2];

                        $wordTags[$word] = $tag;
                    }
                }
            );
    }

    /**
     * @param array $data
     * @param float $total
     * @return array
     */
    public static function calculateFrequency(array $data, float $total): array
    {
        return array_map(
            function (float $value) use ($total): float {
                return log($value / $total);
            },
            $data
        );
    }

    /**
     * @param array $array
     * @param int $topK
     * @param bool $preserveKeys
     * @return array
     */
    public static function getTopK(array $array, int $topK, bool $preserveKeys = false): array
    {
        arsort($array);

        return array_slice($array, 0, $topK, $preserveKeys);
    }

    /**
     * @param string $filename
     * @return void
     */
    public static function getPosTagReadable(string $filename)
    {
        $posTagReadable = [];
        Reader::createFromPath($filename)
            ->setDelimiter(' ')
            ->fetchAll(
                function (array $row) use (&$posTagReadable) {
                    if (!empty($row)) {
                        $tag     = $row[0];
                        $meaning = $row[1];
                        $posTagReadable[$tag] = $meaning;
                    }
                }
            );

        return $posTagReadable;
    }
}
