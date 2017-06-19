<?php

namespace Jieba;

use Jieba\Option\Dict;
use Jieba\Option\Mode;

/**
 * Class Options
 *
 * @package Jieba
 */
class Options
{
    const DICT = 'dict';
    const MODE = 'mode';

    /**
     * @var Dict
     */
    protected $dict;

    /**
     * @var Mode
     */
    protected $mode;

    /**
     * Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this
            ->setDict(!empty($options[self::DICT]) ? new Dict($options[self::DICT]) : new Dict())
            ->setMode(!empty($options[self::MODE]) ? new Mode($options[self::MODE]) : new Mode());
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
        if (!$this->isModeValid($options[self::MODE])) {
            throw new Exception("invalid mode '{$options[self::MODE]}' specified");
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

    /**
     * @return Mode
     */
    public function getMode(): Mode
    {
        return $this->mode;
    }

    /**
     * @param Mode $mode
     * @return $this
     */
    public function setMode(Mode $mode): Options
    {
        $this->mode = $mode;

        return $this;
    }
}
