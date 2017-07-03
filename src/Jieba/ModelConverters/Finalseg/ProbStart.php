<?php

namespace Jieba\ModelConverters\Finalseg;

use Jieba\ModelConverters\AbstractConverter;

/**
 * Class ProbStart
 *
 * @package Jieba\ModelConverters\Finalseg
 */
class ProbStart extends AbstractConverter
{
    /**
     * @inheritdoc
     */
    protected function process(): AbstractConverter
    {
        return $this->replaceQuotes();
    }
}
