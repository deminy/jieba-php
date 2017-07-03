<?php

namespace Jieba\ModelConverters\Posseg;

use Jieba\Exception;
use Jieba\Helper\Helper;
use Jieba\ModelConverters\AbstractConverter;

/**
 * Class ProbEmit
 *
 * @package Jieba\ModelConverters\Posseg
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
