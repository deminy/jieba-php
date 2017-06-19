<?php

use Jieba\Jieba;
use Jieba\Posseg;
use PHPUnit\Framework\TestCase;

class PossegTest extends TestCase
{
    /**
     * @covers \Jieba\Posseg::__construct()
     */
    public function testConstruct()
    {
        $this->assertCount(256, (new Posseg(new Jieba()))->prob_start);
    }

    /**
     * @covers \Jieba\Posseg::cut()
     */
    public function testCut()
    {
        $case_array = array(
            array(
                "word" => "这",
                "tag" => "r",
            ),
            array(
                "word" => "是",
                "tag" => "v",
            ),
            array(
                "word" => "一个",
                "tag" => "m",
            ),
            array(
                "word" => "伸手不见五指",
                "tag" => "i",
            ),
            array(
                "word" => "的",
                "tag" => "uj",
            ),
            array(
                "word" => "黑夜",
                "tag" => "n",
            ),
            array(
                "word" => "。",
                "tag" => "w",
            ),
            array(
                "word" => "我",
                "tag" => "r",
            ),
            array(
                "word" => "叫",
                "tag" => "v",
            ),
            array(
                "word" => "孙悟空",
                "tag" => "nr",
            ),
            array(
                "word" => "，",
                "tag" => "w",
            ),
            array(
                "word" => "我",
                "tag" => "r",
            ),
            array(
                "word" => "爱",
                "tag" => "v",
            ),
            array(
                "word" => "北京",
                "tag" => "ns",
            ),
            array(
                "word" => "，",
                "tag" => "w",
            ),
            array(
                "word" => "我",
                "tag" => "r",
            ),
            array(
                "word" => "爱",
                "tag" => "v",
            ),
            array(
                "word" => "Python",
                "tag" => "eng",
            ),
            array(
                "word" => "和",
                "tag" => "c",
            ),
            array(
                "word" => "C++",
                "tag" => "eng",
            ),
            array(
                "word" => "。",
                "tag" => "w",
            )
        );

        $seg_list = (new Posseg(new Jieba()))->cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。");

        $this->assertEquals($case_array, $seg_list);
    }
}
