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
