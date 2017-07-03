<?php

namespace Jieba\ModelConverters\Posseg;

use Jieba\ModelConverters\AbstractConverter;

/**
 * Class CharState
 *
 * @package Jieba\ModelConverters\Posseg
 */
class CharState extends AbstractConverter
{
    /**
     * @inheritdoc
     */
    protected function process(): AbstractConverter
    {
        return $this->fixArrayKeys()->replaceQuotes()->fixArrays();
    }
}
