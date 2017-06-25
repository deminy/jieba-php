<?php

namespace Jieba\Data;

use Jieba\Exception;

/**
 * Class Viterbi
 *
 * @package Jieba\Data
 * @see https://en.wikipedia.org/wiki/Viterbi_algorithm
 */
class Viterbi
{
    /**
     * @var float
     */
    protected $probability;

    /**
     * @var array an array in the format of
     *      array(
     *          "B",
     *          "E",
     *          "S",
     *          "S",
     *          //...
     *      );
     *      OR
     *      array(
     *          "('S', 'g')",
     *          "('S', 'r')",
     *          //...
     *      );
     * @see ./model/pos/prob_trans.json
     */
    protected $positions;

    /**
     * Viterbi constructor.
     *
     * @param float $probability
     * @param array $positions
     */
    public function __construct(float $probability = 0.0, array $positions = [])
    {
        $this->setProbability($probability)->setPositions($positions);
    }

    /**
     * @param int $index
     * @return string
     * @throws Exception
     */
    public function getPositionAt(int $index)
    {
        if (!array_key_exists($index, $this->positions)) {
            throw new Exception("no position found at location '{$index}'");
        }

        return ((strlen($this->positions[$index]) == 1) ? $this->positions[$index] : $this->positions[$index][2]);
    }

    /**
     * @param int $index
     * @return string
     * @throws Exception
     */
    public function getTagAt(int $index)
    {
        if (!array_key_exists($index, $this->positions)) {
            throw new Exception("no position found at location '{$index}'");
        }

        return substr($this->positions[$index], 7, -2);
    }

    /**
     * @return float
     */
    public function getProbability(): float
    {
        return $this->probability;
    }

    /**
     * @param float $probability
     * @return Viterbi
     */
    public function setProbability(float $probability): Viterbi
    {
        $this->probability = $probability;

        return $this;
    }

    /**
     * @return array
     */
    public function getPositions(): array
    {
        return $this->positions;
    }

    /**
     * @param array $positions
     * @return Viterbi
     */
    public function setPositions(array $positions): Viterbi
    {
        $this->positions = $positions;

        return $this;
    }
}
