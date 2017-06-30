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
     * @var int
     */
    protected $strlen;

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
     * @param Closure $callback Callback function that returns a \Jieba\Data\Words object back.
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
        foreach ($blocks as $block) {
            if (preg_match('/' . JiebaConstant::REGEX_HAN . '/u', $block)) {
                /** @var Words $words */
                $words = $callback($block);
                foreach ($words->getWords() as $word) {
                    $segmentList[] = $word->getWord();
                }
            } else {
                $segmentList[] = $block;
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
     * @param int $index
     * @return string
     */
    public function get(int $index): string
    {
        return mb_substr($this->string, $index, 1);
    }

    /**
     * @return int
     */
    public function strlen(): int
    {
        return $this->strlen;
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
        $this->strlen = ($string ? mb_strlen($string) : 0);

        return $this;
    }
}
