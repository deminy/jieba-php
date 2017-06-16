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
    /**
     * @param array|string $jsonOrArray
     * @param string $delimiter
     * @return MultiArray
     */
    public function make($jsonOrArray, string $delimiter = '.'): MultiArray
    {
        return new MultiArray($jsonOrArray, $delimiter);
    }
}
