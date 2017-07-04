<?php

namespace Jieba\Analyse;

use Jieba\Exception;

/**
 * Class StopWords
 *
 * @package Jieba\Analyse
 * @see https://github.com/fxsjy/jieba/blob/v0.36/jieba/analyse/__init__.py
 */
class StopWords
{
    /**
     * @var array
     */
    protected $stopWords;

    /**
     * StopWords constructor.
     *
     * @param array $stopWords
     */
    public function __construct(array $stopWords)
    {
        $this->setStopWords($stopWords);
    }

    /**
     * @param string $word
     * @return bool
     */
    public function in(string $word): bool
    {
        return array_key_exists($word, $this->stopWords);
    }
    /**
     * @param string $filename
     * @return StopWords
     * @throws Exception
     */
    public function setStopWordsFromFile(string $filename): StopWords
    {
        return $this->setStopWords($this->getStopWordsFromFile($filename));
    }

    /**
     * @param string $filename
     * @return StopWords
     * @throws Exception
     */
    public function addStopWordsFromFile(string $filename): StopWords
    {
        return $this->addStopWords($this->getStopWordsFromFile($filename));
    }

    /**
     * @return array
     */
    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    /**
     * @param array $stopWords
     * @return StopWords
     */
    public function setStopWords(array $stopWords): StopWords
    {
        $this->stopWords = [];

        return $this->addStopWords($stopWords);
    }

    /**
     * @param array $stopWords
     * @return StopWords
     */
    public function addStopWords(array $stopWords): StopWords
    {
        foreach ($stopWords as $stopWord) {
            $this->addStopWord($stopWord);
        }

        return $this;
    }

    /**
     * @param string $stopWord
     * @return StopWords
     */
    public function addStopWord(string $stopWord): StopWords
    {
        $this->stopWords[$stopWord] = $stopWord;

        return $this;
    }

    /**
     * @param string $filename
     * @return StopWords
     * @throws Exception
     */
    public function getStopWordsFromFile(string $filename): StopWords
    {
        if (!is_readable($filename)) {
            throw new Exception("Stop words file '{$filename}' not readable");
        }

        return array_filter(array_map('trim', explode("\n", file_get_contents($filename))));
    }
}
