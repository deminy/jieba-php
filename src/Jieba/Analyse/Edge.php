<?php

namespace Jieba\Analyse;

/**
 * Class Edge
 *
 * @package Jieba\Analyse
 */
class Edge
{
    /**
     * @var string
     */
    protected $start;

    /**
     * @var string
     */
    protected $end;

    /**
     * @var int
     */
    protected $weight;

    /**
     * Edge constructor.
     *
     * @param string $start
     * @param string $end
     * @param int $weight
     */
    public function __construct(string $start, string $end, int $weight)
    {
        $this->setStart($start)->setEnd($end)->setWeight($weight);
    }

    /**
     * @return string
     */
    public function getStart(): string
    {
        return $this->start;
    }

    /**
     * @param string $start
     * @return Edge
     */
    public function setStart(string $start): Edge
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnd(): string
    {
        return $this->end;
    }

    /**
     * @param string $end
     * @return Edge
     */
    public function setEnd(string $end): Edge
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return Edge
     */
    public function setWeight(int $weight): Edge
    {
        $this->weight = $weight;

        return $this;
    }
}
