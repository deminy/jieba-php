<?php

namespace Jieba\ModelConverters\Finalseg;

use Jieba\ModelConverters\AbstractConverter;

/**
 * Class ProbEmit
 *
 * @package Jieba\ModelConverters\Finalseg
 */
class ProbEmit extends AbstractConverter
{
    /**
     * @inheritdoc
     */
    protected function process(): AbstractConverter
    {
        return $this->fixArrayKeys()->replaceQuotes();
    }
}
