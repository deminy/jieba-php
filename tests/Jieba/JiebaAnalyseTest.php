<?php

namespace Jieba\Tests\Jieba;

use Jieba\Jieba;
use Jieba\JiebaAnalyse;
use PHPUnit\Framework\TestCase;

class JiebaAnalyseTest extends TestCase
{
    /**
     * @covers \Jieba\JiebaAnalyse::__construct()
     */
    public function testConstruct()
    {
        $this->assertGreaterThan(0, JiebaAnalyse::singleton()->getMaxIdf());
    }

    /**
     * @covers \Jieba\JiebaAnalyse::extractTags()
     */
    public function testExtractTags()
    {
        $case_array = array(
            "所謂" => 1.1425214508493,
            "沒有" => 0.76168096723288,
            "是否" => 0.71841348115616,
            "一般" => 0.59095311682055,
            "肌迫" => 0.38084048361644,
            "雖然" => 0.38084048361644,
            "退縮" => 0.38084048361644,
            "矯作" => 0.38084048361644,
            "怯懦" => 0.26367154884822,
            "滿肚" => 0.19042024180822,
        );

        $tags = JiebaAnalyse::singleton()->extractTags(
            (new Jieba())->cut(file_get_contents(dirname(__DIR__) . '/dict/lyric.txt')),
            10
        );
        $this->assertEquals($case_array, $tags);
    }
}
