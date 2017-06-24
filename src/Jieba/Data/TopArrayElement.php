<?php

namespace Jieba\Data;

/**
 * Class TopArrayElement
 *
 * @package Jieba
 */
class TopArrayElement
{
    /**
     * @var mixed
     */
    protected $key;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * TopArrayElement constructor.
     *
     * @param array $array
     * @throws Exception
     */
    public function __construct(array $array)
    {
        if (empty($array)) {
            throw new Exception('class not designed for empty array');
        }
        arsort($array);

        $this->setValue(reset($array))->setKey(key($array));

        unset($array);
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return TopArrayElement
     */
    public function setKey($key): TopArrayElement
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return TopArrayElement
     */
    public function setValue($value): TopArrayElement
    {
        $this->value = $value;

        return $this;
    }
}
