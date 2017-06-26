<?php

namespace Jieba\Data;

/**
 * Class Idf
 *
 * @package Jieba\Data
 */
class Idf extends TaggedWord
{
    /**
     * @var float
     */
    protected $frequency;

    /**
     * Idf constructor.
     *
     * @param string $word
     * @param float $frequency
     * @param string $tag
     */
    public function __construct(string $word, float $frequency, string $tag)
    {
        parent::__construct($word, $tag);
        $this->setFrequency($frequency);
    }

    /**
     * @return float
     */
    public function getFrequency(): float
    {
        return $this->frequency;
    }

    /**
     * @param float $frequency
     * @return Idf
     */
    public function setFrequency(float $frequency): Idf
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray() + ['freq' => $this->getFrequency()];
    }
}
