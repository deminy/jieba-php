<?php

namespace Jieba;

use Closure;

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

        if (Constant::UTF8 != $encoding) {
            mb_internal_encoding('UTF-8');
        }

        $result = $callback(...$params);

        if (Constant::UTF8 != $encoding) {
            mb_internal_encoding($encoding);
        }

        return $result;
    }
}
