<?php

namespace Jieba\Traits;

use Jieba\Options\Options;

/**
 * Trait OptionsTrait
 *
 * @package Jieba\Traits
 * @todo merge it to a class if used only by that single class.
 */
trait OptionsTrait
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * @return Options
     */
    public function getOptions(): Options
    {
        return $this->options;
    }

    /**
     * @param Options $options
     * @return $this
     */
    public function setOptions(Options $options)
    {
        $this->options = $options;

        return $this;
    }
}
