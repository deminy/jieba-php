<?php

namespace Jieba\Option;

use Jieba\Exception;

/**
 * Class Mode
 *
 * @package Jieba\Option
 */
class Mode extends AbstractOption
{
    const DEFAULT = 'default';
    const TEST    = 'test';

    const VALID_MODES = [
        self::DEFAULT => self::DEFAULT,
        self::TEST    => self::TEST,
    ];

    /**
     * @var string
     */
    protected $mode;

    /**
     * Mode constructor.
     *
     * @param string $mode
     */
    public function __construct(string $mode = self::DEFAULT)
    {
        $this->setMode($mode);
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return Mode
     * @throws Exception
     */
    public function setMode(string $mode): Mode
    {
        if (!$this->isValid($mode)) {
            throw new Exception("invalid mode '{$mode}' specified");
        }

        $this->mode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function isValid(string $value): bool
    {
        return array_key_exists($value, self::VALID_MODES);
    }
}
