<?php

namespace Jieba\Data;

use Closure;
use Jieba\Constants\JiebaConstant;

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
     * @return int
     */
    public function strlen(): int
    {
        return ($this->getString() ? mb_strlen($this->getString()) : 0);
    }

    /**
     * @param Closure $callback
     * @return array
     */
    public function cut(Closure $callback): array
    {
        preg_match_all(
            '/(' . JiebaConstant::REGEX_HAN . '|' . JiebaConstant::REGEX_SKIP . ')/u',
            $this->getString(),
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        $segmentList = [];
        foreach ($blocks as $blk) {
            if (preg_match('/' . JiebaConstant::REGEX_HAN . '/u', $blk)) {
                $words = $callback($blk);
                foreach ($words as $word) {
                    $segmentList[] = $word;
                }
            } else {
                $segmentList[] = $blk;
            }
        }

        return $segmentList;
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
