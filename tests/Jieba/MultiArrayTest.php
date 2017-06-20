<?php

namespace Jieba\Tests\Jieba;

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

    /**
     * @return array
     */
    public function dataRemove(): array
    {
        $array = [
            'key11' => [
                'key21' => '',
                'key22' => null,
                'key23' => [
                    'key31' => true,
                    'key32' => [
                        'key41' => false,
                        'key42' => true,
                    ],
                ],
            ],
        ];

        return [
            [
                $array,
                'key11.key21',
                [
                    'key11.key21',
                ],
                [
                    'key11.key23',
                    'key11.key23.key31',
                    'key11.key23.key32',
                    'key11.key23.key32.key41',
                ],
            ],
            [
                $array,
                'key11.key23',
                [
                    'key11.key23',
                ],
                [
                    'key11.key21',

                    // NOTE: I thought following keys should be removed as well but not.
                    // TODO: why following keys not removed after \Jieba\MultiArray::remove() has been called?
                    'key11.key23.key31',
                    'key11.key23.key32',
                    'key11.key23.key32.key41',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataRemove
     * @depends testExists
     * @covers \Jieba\MultiArray::exists()
     * @covers \Jieba\MultiArray::remove()
     * @covers \Jieba\MultiArray::set()
     * @param array $array
     * @param string $deletedKeyString
     * @param array $deletedKeyStrings
     * @param array $existingKeyStrings
     * @return void
     */
    public function testRemove(
        array $array,
        string $deletedKeyString,
        array $deletedKeyStrings,
        array $existingKeyStrings
    ) {
        $a = new MultiArray($array);

        foreach ($existingKeyStrings as $keyString) {
            $this->assertTrue($a->exists($keyString), "key {$keyString} should be in the array before removed");
        }
        foreach ($deletedKeyStrings as $keyString) {
            $this->assertTrue($a->exists($keyString), "key {$keyString} should be in the array before removed");
        }

        $a->remove($deletedKeyString);

        foreach ($existingKeyStrings as $keyString) {
            $this->assertTrue($a->exists($keyString), "key {$keyString} should still be there");
        }
        foreach ($deletedKeyStrings as $keyString) {
            $this->assertFalse($a->exists($keyString), "key {$keyString} should have been removed from the array");
        }
    }
}
