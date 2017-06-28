<?php

namespace Jieba;

use Closure;
use Jieba\Constants\JiebaConstant;

class Wrapper
{
    /**
     * @param Closure $callback
     * @param array ...$params
     * @return mixed
     * @todo add unit tests.
     */
    public static function run(Closure $callback, ...$params)
    {
        $encoding = mb_internal_encoding();

        if (JiebaConstant::UTF8 != $encoding) {
            mb_internal_encoding(JiebaConstant::UTF8);
        }

        $result = $callback(...$params);

        if (JiebaConstant::UTF8 != $encoding) {
            mb_internal_encoding($encoding);
        }

        return $result;
    }
}
