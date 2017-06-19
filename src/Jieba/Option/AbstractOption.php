<?php

namespace Jieba\Option;

/**
 * Class AbstractOption
 *
 * @package Jieba\Option
 */
abstract class AbstractOption
{
    /**
     * @param string $value
     * @return bool
     */
    abstract protected function isValid(string $value): bool;
}
