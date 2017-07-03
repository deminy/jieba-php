<?php

namespace Jieba\ModelConverters\Posseg;

use Jieba\ModelConverters\AbstractConverter;

/**
 * Class ProbStart
 *
 * @package Jieba\ModelConverters\Posseg
 */
class ProbStart extends AbstractConverter
{
    /**
     * @inheritdoc
     */
    protected function process(): AbstractConverter
    {
        return $this->fixArrayKeys();
    }
}
