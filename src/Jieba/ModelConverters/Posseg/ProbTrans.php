<?php

namespace Jieba\ModelConverters\Posseg;

use Jieba\ModelConverters\AbstractConverter;

/**
 * Class ProbTrans
 *
 * @package Jieba\ModelConverters\Posseg
 */
class ProbTrans extends AbstractConverter
{
    /**
     * @inheritdoc
     */
    protected function process(): AbstractConverter
    {
        return $this->fixArrayKeys();
    }
}
