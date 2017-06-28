<?php

namespace Jieba\Tests\Jieba\Data;

use Jieba\Data\MultiByteString;
use PHPUnit\Framework\TestCase;

class MultiByteStringTest extends TestCase
{
    /**
     * @return array
     */
    public function dataToArray(): array
    {
        return [
            [
                ["我", "们"],
                "我们",
                'test Chinese characters',
            ],
            [
                ["a", "我", "1", "们", "c"],
                "a我1们c",
                'test mixed Chinese, English and number characters',
            ],
            [
                ["a", "我", "1", "们", "c", "!", ".", "*"],
                "a我1们c!.*",
                'test mixed Chinese, English and number characters, plus punctuations',
            ],
        ];
    }

    /**
     * @dataProvider dataToArray
     * @covers \Jieba\Data\MultiByteString::toArray()
     * @param array $expected
     * @param string $string
     * @param string $message
     * @return void
     */
    public function testToArray(array $expected, string $string, string $message)
    {
        $this->assertSame($expected, (new MultiByteString($string))->toArray(), $message);
    }
}
