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
     *          "Sg",  // TODO: describe data format here
     *          "Bmq", // TODO: describe data format here
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
     * @return string Return first character back. So: if position is "M" it returns "M" back; if position is "Bmq" it
     * returns "B" back.
     * @throws Exception
     */
    public function getPositionAt(int $index)
    {
        if (!array_key_exists($index, $this->positions)) {
            throw new Exception("no position found at location '{$index}'");
        }

        return $this->positions[$index][0];
    }

    /**
     * @param int $index
     * @return string Return an empty string if no tag included. In this case tag information is useless. So: if
     * position is "M" it returns "" back; if position is "Bmq" it returns "mq" back.
     * returns "B" back.
     * @throws Exception
     */
    public function getTagAt(int $index)
    {
        if (!array_key_exists($index, $this->positions)) {
            throw new Exception("no position found at location '{$index}'");
        }

        return ((strlen($this->positions[$index]) == 1) ? '' : substr($this->positions[$index], 1));
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
