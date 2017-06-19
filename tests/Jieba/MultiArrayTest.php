<?php

use Jieba\MultiArray;
use PHPUnit\Framework\TestCase;

class MultiArrayTest extends TestCase
{
    /**
     * @return array
     */
    public function dataExists()
    {
        $array = [
            'key11' => [
                'key21' => '',
                'key22' => null,
                'key23' => [
                    'key31' => true,
                ],
            ],
        ];

        return [
            [
                true,
                $array,
                '.',
                'key11.key21',
                'key21 exists inside key11',
            ],
            [
                false,
                $array,
                '.',
                'key11.key22',
                'key22 exists inside key11 but has value null',
            ],
            [
                false,
                $array,
                '.',
                'key11.key25',
                'key25 does not exist inside key11',
            ],
            [
                true,
                $array,
                '.',
                'key11.key23.key31',
                'key31 exists inside key11.key23',
            ],
            [
                false,
                $array,
                '.',
                'key11.key23.key32',
                'key32 does not exist inside key11.key23',
            ],
        ];
    }

    /**
     * @dataProvider dataExists
     * @covers \Jieba\MultiArray::exists()
     * @param bool $expected
     * @param array $array
     * @param string $keyDelimiter
     * @param string $keyString
     * @param string $message
     * @return void
     */
    public function testExists(bool $expected, array $array, string $keyDelimiter, string $keyString, string $message)
    {
        $this->assertSame($expected, (new MultiArray($array, $keyDelimiter))->exists($keyString), $message);
    }
}
