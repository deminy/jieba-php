<?php

namespace Jieba\ModelConverters\Finalseg;

use Jieba\ModelConverters\AbstractConverter;

/**
 * Class ProbTrans
 *
 * @package Jieba\ModelConverters\Finalseg
 */
class ProbTrans extends AbstractConverter
{
    /**
     * @inheritdoc
     */
    protected function process(): AbstractConverter
    {
        return $this->replaceQuotes();
    }
}
