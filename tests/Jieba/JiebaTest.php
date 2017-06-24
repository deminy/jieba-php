<?php

namespace Jieba\Tests\Jieba;

use Jieba\Jieba;
use Jieba\Options\Options;
use Jieba\Options\Dict;
use PHPUnit\Framework\TestCase;

class JiebaTest extends TestCase
{
    /**
     * @covers \Jieba\Jieba::__construct()
     */
    public function testConstruct()
    {
        $this->assertGreaterThan(0, (new Jieba())->total);
    }

    /**
     * @covers \Jieba\Jieba::cut()
     */
    public function testCut()
    {
        $jieba = new Jieba();

        $case_array = array(
            "怜香惜玉",
            "也",
            "得",
            "要",
            "看",
            "对象",
            "啊",
        );

        $seg_list = $jieba->cut("怜香惜玉也得要看对象啊！");
        $this->assertSame($case_array, $seg_list);

        $case_array = array(
            "我",
            "来到",
            "北京",
            "清华大学",
        );

        $seg_list = $jieba->cut("我来到北京清华大学");
        $this->assertSame($case_array, $seg_list);

        $case_array = array(
            "他",
            "来到",
            "了",
            "网易",
            "杭研",
            "大厦",
        );

        $seg_list = $jieba->cut("他来到了网易杭研大厦");
        $this->assertSame($case_array, $seg_list);
    }

    /**
     * @covers \Jieba\Jieba::cut()
     * @covers \Jieba\Options\Options::setDict()
     * @covers \Jieba\Options\Dict
     */
    public function testCutOnBig5()
    {
        $this->assertSame(
            [
                "憐香惜玉",
                "也",
                "得",
                "要",
                "看",
                "對象",
                "啊",
            ],
            (new Jieba((new Options())->setDict(new Dict(Dict::BIG))))->cut("憐香惜玉也得要看對象啊！")
        );
    }

    /**
     * @covers \Jieba\Jieba::cut()
     */
    public function testCutAll()
    {
        $case_array = array(
            "我",
            "来到",
            "北京",
            "清华",
            "清华大学",
            "华大",
            "大学",
        );

        $seg_list = (new Jieba())->cut("我来到北京清华大学", true);
        $this->assertSame($case_array, $seg_list);
    }

    /**
     * @covers \Jieba\Jieba::cutForSEarch()
     */
    public function testCutForSearch()
    {
        $case_array = array(
            "小明",
            "硕士",
            "毕业",
            "于",
            "中国",
            "科学",
            "学院",
            "科学院",
            "中国科学院",
            "计算",
            "计算所",
            "后",
            "在",
            "日本",
            "京都",
            "大学",
            "日本京都大学",
            "深造",
        );

        $seg_list = (new Jieba())->cutForSEarch("小明硕士毕业于中国科学院计算所，后在日本京都大学深造");
        $this->assertSame($case_array, $seg_list);
    }

    /**
     * @covers \Jieba\Helper::getDictFilePath()
     * @covers \Jieba\Jieba::loadUserDict()
     * @covers \Jieba\Jieba::cut()
     */
    public function testLoadUserDict()
    {
        $jieba    = new Jieba();
        $sentence = "李小福是创新办主任也是云计算方面的专家";

        $this->assertSame(
            [
                "李小福",
                "是",
                "创新",
                "办",
                "主任",
                "也",
                "是",
                "云",
                "计算",
                "方面",
                "的",
                "专家",
            ],
            $jieba->cut($sentence)
        );

        $jieba->loadUserDict(dirname(__DIR__) . '/dict/user_dict.txt');
        $this->assertSame(
            [
                "李小福",
                "是",
                "创新办",
                "主任",
                "也",
                "是",
                "云计算",
                "方面",
                "的",
                "专家",
            ],
            $jieba->cut($sentence)
        );
    }
}
