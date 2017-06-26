<?php

namespace Jieba\Data;

/**
 * Class Idf
 *
 * @package Jieba\Data
 */
interface ArrayableInterface
{
    /**
     * @return array
     */
    public function toArray(): array;
}
