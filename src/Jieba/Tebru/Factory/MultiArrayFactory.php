<?php

namespace Jieba\Tebru\Factory;

use Jieba\Tebru\MultiArray;

/**
 * Class MultiArrayFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MultiArrayFactory
{
    public function make($jsonOrArray, $delimiter = '.')
    {
        return new MultiArray($jsonOrArray, $delimiter);
    }
}
