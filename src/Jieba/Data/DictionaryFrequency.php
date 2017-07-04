<?php

namespace Jieba\Data;

/**
 * Class DictionaryFrequency
 *
 * @package Jieba\Data
 */
class DictionaryFrequency
{
    /**
     * @var array
     */
    protected $frequencies = [];

    /**
     * @var float
     */
    protected $total = 0.0;

    /**
     * @param string $word
     * @param float $frequency
     * @return DictionaryFrequency
     */
    public function addWord(string $word, float $frequency): DictionaryFrequency
    {
        // NOTE: in package "fxsjy/jieba" there is no such if statement in place.
        if (isset($this->frequencies[$word])) {
            $this->total -= $this->frequencies[$word];
        }
        foreach ((new MultiByteString($word))->toArray() as $char) {
            if (!isset($this->frequencies[$char])) {
                $this->frequencies[$char] = 0.0;
            }
        }

        $this->frequencies[$word]  = $frequency;
        $this->total              += $frequency;

        return $this;
    }

    /**
     * @param string $word
     * @return bool
     */
    public function exists(string $word): bool
    {
        return isset($this->frequencies[$word]);
    }

    /**
     * @param string $word
     * @param float $default
     * @return float
     */
    public function getFrequency(string $word, float $default = 1.0): float
    {
        return ($this->frequencies[$word] ?? $default);
    }

    /**
     * @return array
     */
    public function getFrequencies(): array
    {
        return $this->frequencies;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @return float
     */
    public function getMinimumFrequency(): float
    {
        return min($this->frequencies);
    }
}
