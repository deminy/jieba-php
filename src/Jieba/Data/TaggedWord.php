<?php

namespace Jieba\Data;

/**
 * Class TaggedWord
 *
 * @package Jieba\Data
 */
class TaggedWord extends Word
{
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
        parent::__construct($word);
        $this->setTag($tag);
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
     * @return TaggedWord
     */
    public function setTag(string $tag): TaggedWord
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray() + ['tag' => $this->getTag()];
    }
}
