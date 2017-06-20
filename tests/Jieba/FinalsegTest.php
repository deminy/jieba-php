<?php

namespace Jieba\Tests\Jieba;

use Jieba\Finalseg;
use PHPUnit\Framework\TestCase;

class FinalsegTest extends TestCase
{
    /**
     * @covers \Jieba\Finalseg::__construct()
     */
    public function testConstruct()
    {
        $this->assertCount(4, Finalseg::singleton()->getProbStart());
    }

    /**
     * @covers \Jieba\Finalseg::cut()
     */
    public function testCut()
    {
        $case_array = array(
            "怜香惜",
            "玉",
            "也",
            "得",
            "要",
            "看",
            "对象",
            "啊",
        );

        $seg_list = Finalseg::singleton()->cut("怜香惜玉也得要看对象啊！");
        $this->assertEquals($case_array, $seg_list);
    }
}
