<?php

namespace Jieba\Data;

/**
 * Class Words
 *
 * @package Jieba
 */
class Words implements ArrayableInterface
{
    /**
     * @var Word[]
     */
    protected $words;

    /**
     * Words constructor.
     *
     * @param array $words
     */
    public function __construct(array $words = [])
    {
        $this->setWords($words);
    }

    /**
     * @param Word $word
     * @return Words
     */
    public function addWord(Word $word): Words
    {
        $this->words[] = $word;

        return $this;
    }

    /**
     * @return Word[]
     */
    public function getWords(): array
    {
        return $this->words;
    }

    /**
     * @param array $words
     * @return Words
     */
    public function setWords(array $words): Words
    {
        $this->words = [];
        foreach ($words as $word) {
            $this->addWord($word);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->getWords() as $word) {
            $data[] = $word->toArray();
        }

        return $data;
    }
}
