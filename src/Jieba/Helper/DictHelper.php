<?php

namespace Jieba\Helper;

use Closure;
use Jieba\Constants\JiebaConstant;
use Jieba\Data\MultiArray;
use Jieba\Data\MultiByteString;
use Jieba\Data\Word;
use Jieba\Data\Words;
use Jieba\Data\Viterbi;
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
                (new MultiByteString($word))->buildMultiArrayKey(),
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
     * @param string $sentence
     * @param Closure $callback
     * @return Words
     */
    public static function cutSentence(string $sentence, Closure $callback): Words
    {
        $words = new Words();
        $begin = 0;
        $next  = 0;

        /** @var Viterbi $viterbi */
        $viterbi = $callback($sentence);
        $length  = mb_strlen($sentence);

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($sentence, $i, 1);
            switch ($viterbi->getPositionAt($i)) {
                case JiebaConstant::B:
                    $begin = $i;
                    break;
                case JiebaConstant::E:
                    $words->addWord(
                        new Word(mb_substr($sentence, $begin, (($i + 1) - $begin)), $viterbi->getTagAt($i))
                    );
                    $next = $i + 1;
                    break;
                case JiebaConstant::S:
                    $words->addWord(new Word($char, $viterbi->getTagAt($i)));
                    $next = $i + 1;
                    break;
                case JiebaConstant::M:
                default:
                    break;
            }
        }

        if ($next < $length) {
            $words->addWord(new Word(mb_substr($sentence, $next), $viterbi->getTagAt($next)));
        }

        return $words;
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
     * @return array
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
