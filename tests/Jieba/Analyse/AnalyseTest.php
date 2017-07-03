<?php

namespace Jieba\Tests\Jieba;

use Jieba\Analyse\Analyse;
use Jieba\Jieba;
use Jieba\Options\Options;
use Jieba\Options\Dict;
use PHPUnit\Framework\TestCase;

class AnalyseTest extends TestCase
{
    /**
     * @covers \Jieba\Analyse\Analyse::__construct()
     */
    public function testConstruct()
    {
        $this->assertGreaterThan(0, Analyse::singleton()->getMaxIdf());
    }

    /**
     * @return array
     */
    public function arrayExtractTags(): array
    {
        return [
            [
                Dict::NORMAL,
                10,
                [
                    "所謂" => 1.1747051536901,
                    "是否" => 0.73865048062535,
                    "一般" => 0.60759968349155,
                    "矯作" => 0.39156838456338,
                    "雖然" => 0.39156838456338,
                    "退縮" => 0.39156838456338,
                    "肌迫" => 0.39156838456338,
                    "沒有" => 0.39156838456338,
                    "怯懦" => 0.27109891642141,
                    "勇氣" => 0.19578419228169,
                ],
            ],
            [
                Dict::BIG,
                9,
                [
                    "沒有" => 1.2317056147342,
                    "所謂" => 1.0557476697722,
                    "是否" => 0.66385043195443,
                    "一般" => 0.54607060161899,
                    "雖然" => 0.35191588992405,
                    "來說" => 0.35191588992405,
                    "肌迫" => 0.35191588992405,
                    "退縮" => 0.35191588992405,
                    "矯作" => 0.35191588992405,
                ],
            ],
        ];
    }

    /**
     * @dataProvider arrayExtractTags
     * @covers \Jieba\Analyse\Analyse::extractTags()
     * @param string $dict
     * @param int $topK
     * @param array $expected
     */
    public function testExtractTags(string $dict, int $topK, array $expected)
    {
        $jieba = new Jieba((new Options())->setDict(new Dict($dict)));
        $this->assertEquals(
            $expected,
            Analyse::singleton()->extractTags(
                $jieba->cut(file_get_contents(dirname(__DIR__, 2) . '/dict/lyric.txt')),
                $topK
            )
        );
    }
}
