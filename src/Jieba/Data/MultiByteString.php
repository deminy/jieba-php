<?php

namespace Jieba\Data;

/**
 * Class MultiByteString
 *
 * @package Jieba
 */
class MultiByteString
{
    /**
     * @var string a UTF-8 string
     */
    protected $string;

    /**
     * MultiByteString constructor.
     *
     * @param string $string a UTF-8 string
     */
    public function __construct(string $string = '')
    {
        $this->setString($string);
    }

    /**
     * @return string
     * @see \Jieba\Data\MultiArray
     */
    public function buildMultiArrayKey(): string
    {
        return implode('.', $this->toArray());
    }

    /**
     * Extract all characters from a UTF-8 string to array of individual characters.
     *
     * @return array
     * @see http://php.net/manual/en/function.mb-split.php#117588
     */
    public function toArray(): array
    {
        return preg_split('//u', $this->getString(), null, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @param string $string
     * @return MultiByteString
     */
    public function setString(string $string): MultiByteString
    {
        $this->string = $string;

        return $this;
    }
}
