<?php

namespace Jieba;

/**
 * Class Word
 *
 * @package Jieba
 */
class Word
{
    /**
     * @var string
     */
    protected $word;

    /**
     * @var string
     */
    protected $tag;

    /**
     * Word constructor.
     *
     * @param string $word
     * @param string $tag
     */
    public function __construct(string $word, string $tag)
    {
        $this->setWord($word)->setTag($tag);
    }

    /**
     * @return string
     */
    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * @param string $word
     * @return Word
     */
    public function setWord(string $word): Word
    {
        $this->word = $word;

        return $this;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     * @return Word
     */
    public function setTag(string $tag): Word
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'word' => $this->getWord(),
            'tag'  => $this->getTag(),
        ];
    }
}
