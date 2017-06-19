<?php

namespace Jieba\Option;

use Jieba\Exception;
use Jieba\Helper;

/**
 * Class Dict
 *
 * @package Jieba\Option
 */
class Dict extends AbstractOption
{
    const NORMAL  = 'normal';
    const SMALL   = 'small';
    const BIG     = 'big';

    const VALID_DICTIONARIES = [
        self::NORMAL => self::NORMAL,
        self::SMALL  => self::SMALL,
        self::BIG    => self::BIG,
    ];

    /**
     * @var string
     */
    protected $dict;

    /**
     * Dict constructor.
     *
     * @param string $dict
     */
    public function __construct(string $dict = self::NORMAL)
    {
        $this->setDict($dict);
    }

    /**
     * @param string|null $dict
     * @return string
     */
    public function getDictFileContent(string $dict = null): string
    {
        return file_get_contents($this->getDictFilePath($dict));
    }

    /**
     * @param string|null $dict
     * @return string
     */
    public function getDictFilePath(string $dict = null): string
    {
        return (Helper::getDictBasePath() . $this->getDictFileName($dict));
    }

    /**
     * @param string|null $dict
     * @return string
     */
    public function getDictFileName(string $dict = null): string
    {
        if (empty($dict)) {
            $dict = $this->getDict();
        }

        switch ($dict) {
            case self::SMALL:
                return 'dict.small.txt';
                break;
            case self::BIG:
                return 'dict.big.txt';
                break;
            case self::NORMAL:
            default:
                return 'dict.txt';
                break;
        }
    }

    /**
     * @return string
     */
    public function getDict(): string
    {
        return $this->dict;
    }

    /**
     * @param string $dict
     * @return Dict
     * @throws Exception
     */
    public function setDict(string $dict): Dict
    {
        if (!$this->isValid($dict)) {
            throw new Exception("invalid dictionary '{$dict}' specified");
        }

        $this->dict = $dict;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function isValid(string $value): bool
    {
        return array_key_exists($value, self::VALID_DICTIONARIES);
    }
}
