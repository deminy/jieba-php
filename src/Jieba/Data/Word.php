<?php

namespace Jieba\Data;

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
     * Word constructor.
     *
     * @param string $word
     */
    public function __construct(string $word)
    {
        $this->setWord($word);
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
     * @return array
     */
    public function toArray(): array
    {
        return ['word' => $this->getWord()];
    }
}
