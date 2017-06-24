<?php

namespace Jieba;

use Closure;

class StringHelper
{
    /**
     * @param string $sentence
     * @param Closure $callback
     * @return array
     */
    public static function cut(string $sentence, Closure $callback): array
    {
        preg_match_all(
            '/(' . Constant::REGEX_HAN . '|' . Constant::REGEX_SKIP . ')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        $seg_list = [];
        foreach ($blocks as $blk) {
            if (preg_match('/' . Constant::REGEX_HAN . '/u', $blk)) {
                $words = $callback($blk);
                foreach ($words as $word) {
                    $seg_list[] = $word;
                }
            } else {
                $seg_list[] = $blk;
            }
        }

        return $seg_list;
    }

    /**
     * @param string $str
     * @return int
     */
    public static function strlen(string $str): int
    {
        return ($str ? mb_strlen($str) : 0);
    }
}
