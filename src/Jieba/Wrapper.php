<?php

namespace Jieba;

use Closure;

class Wrapper
{
    /**
     * @param Closure $op
     * @param array ...$params
     * @return mixed
     */
    public static function run(Closure $op, ...$params)
    {
        $encoding = mb_internal_encoding();

        if (Constant::UTF8 != $encoding) {
            mb_internal_encoding('UTF-8');
        }

        $result = $op(...$params);

        if (Constant::UTF8 != $encoding) {
            mb_internal_encoding($encoding);
        }

        return $result;
    }
}
