<?php

namespace Jieba\Tests\Jieba\Helper;

use Jieba\Helper\ModelSingleton;
use PHPUnit\Framework\TestCase;

class ModelHelperTest extends TestCase
{
    /**
     * @covers \Jieba\Helper\ModelSingleton::getProbStart()
     */
    public function testGetProbStart()
    {
        $this->assertCount(4, ModelSingleton::singleton()->getProbStart());
    }

    /**
     * @covers \Jieba\Helper\ModelSingleton::getPosProbStart()
     */
    public function testGetPosProbStart()
    {
        $this->assertCount(256, ModelSingleton::singleton()->getPosProbStart());
    }
}
