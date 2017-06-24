<?php

namespace Jieba\Options;

use Jieba\Exception;

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
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function getOptions(array $options = []): array
    {
        $options += self::DEFAULT_OPTIONS;

        if (!$this->isDictionaryValid($options[self::DICT])) {
            throw new Exception("invalid dictionary '{$options[self::DICT]}' specified");
        }

        return $options;
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
     * @return $this
     */
    public function setDict(Dict $dict): Options
    {
        $this->dict = $dict;

        return $this;
    }
}
