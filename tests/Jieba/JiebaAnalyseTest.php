<?php

namespace Jieba\Tests\Jieba;

use Jieba\Jieba;
use Jieba\JiebaAnalyse;
use Jieba\Options;
use Jieba\Option\Dict;
use Jieba\Option\Mode;
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
     * @return array
     */
    public function arrayExtractTags(): array
    {
        return [
            [
                Dict::NORMAL,
                [
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
                ],
            ],
            [
                Dict::BIG,
                [
                    '沒有' => 1.2317056147342,
                    '所謂' => 1.0557476697722,
                    '是否' => 0.66385043195443,
                    '一般' => 0.54607060161899,
                    '雖然' => 0.35191588992405,
                    '來說' => 0.35191588992405,
                    '肌迫' => 0.35191588992405,
                    '退縮' => 0.35191588992405,
                    '矯作' => 0.35191588992405,
                    '怯懦' => 0.24364586159392,
                ],
            ],
        ];
    }

    /**
     * @dataProvider arrayExtractTags
     * @covers \Jieba\JiebaAnalyse::extractTags()
     * @param string $dict
     * @param array $expected
     */
    public function testExtractTags(string $dict, array $expected)
    {
        $jieba = new Jieba((new Options())->setDict(new Dict($dict))->setMode(new Mode(Mode::TEST)));
        $this->assertEquals(
            $expected,
            JiebaAnalyse::singleton()->extractTags(
                $jieba->cut(file_get_contents(dirname(__DIR__) . '/dict/lyric.txt')),
                10
            )
        );
    }
}
