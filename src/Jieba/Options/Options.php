<?php

namespace Jieba\Options;

/**
 * Class Options
 *
 * @package Jieba
 */
class Options
{
    const DICT = 'dict';

    /**
     * @var Dict
     */
    protected $dict;

    /**
     * Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setDict(!empty($options[self::DICT]) ? new Dict($options[self::DICT]) : new Dict());
    }

    /**
     * @return Dict
     */
    public function getDict(): Dict
    {
        return $this->dict;
    }

    /**
     * @param Dict $dict
     * @return Options
     */
    public function setDict(Dict $dict): Options
    {
        $this->dict = $dict;

        return $this;
    }
}
