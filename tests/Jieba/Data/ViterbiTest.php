<?php

namespace Jieba\Tests\Jieba\Data;

use Jieba\Data\Viterbi;
use PHPUnit\Framework\TestCase;

class ViterbiTest extends TestCase
{
    /**
     * @return array
     */
    public function dataGetPositionAt(): array
    {
        return [
            // set 1: simple cases
            [
                'B',
                ['B', 'M', 'E', 'S'],
                0,
                'get first position (simple case)',
            ],
            [
                'M',
                ['B', 'M', 'E', 'S'],
                1,
                'get second position (simple case)',
            ],
            [
                'E',
                ['B', 'M', 'E', 'S'],
                2,
                'get third position (simple case)',
            ],
            [
                'S',
                ['B', 'M', 'E', 'S'],
                3,
                'get last position (simple case)',
            ],

            // set 2: complex cases
            [
                'B',
                ["('B', 'nrt')", "('M', 'e')", "('E', 'df')", "('S', 'g')"],
                0,
                'get first position (complex case)',
            ],
            [
                'M',
                ["('B', 'nrt')", "('M', 'e')", "('E', 'df')", "('S', 'g')"],
                1,
                'get second position (complex case)',
            ],
            [
                'E',
                ["('B', 'nrt')", "('M', 'e')", "('E', 'df')", "('S', 'g')"],
                2,
                'get third position (complex case)',
            ],
            [
                'S',
                ["('B', 'nrt')", "('M', 'e')", "('E', 'df')", "('S', 'g')"],
                3,
                'get last position (complex case)',
            ],
        ];
    }

    /**
     * @dataProvider dataGetPositionAt
     * @covers \Jieba\Data\Viterbi::getPositionAt()
     * @param string $expected
     * @param array $positions
     * @param int $index
     * @param string $message
     * @return void
     */
    public function testGetPositionAt(string $expected, array $positions, int $index, string $message)
    {
        $this->assertSame($expected, (new Viterbi(0.0, $positions))->getPositionAt($index), $message);
    }
    /**
     * @return array
     */
    public function dataGetTagAt(): array
    {
        return [
            [
                'nrt',
                ["('B', 'nrt')", "('M', 'e')", "('E', 'df')", "('S', 'g')"],
                0,
                'get first tag',
            ],
            [
                'e',
                ["('B', 'nrt')", "('M', 'e')", "('E', 'df')", "('S', 'g')"],
                1,
                'get second tag',
            ],
            [
                'df',
                ["('B', 'nrt')", "('M', 'e')", "('E', 'df')", "('S', 'g')"],
                2,
                'get third tag',
            ],
            [
                'g',
                ["('B', 'nrt')", "('M', 'e')", "('E', 'df')", "('S', 'g')"],
                3,
                'get last tag',
            ],
        ];
    }

    /**
     * @dataProvider dataGetTagAt
     * @covers \Jieba\Data\Viterbi::getTagAt()
     * @param string $expected
     * @param array $positions
     * @param int $index
     * @param string $message
     * @return void
     */
    public function testGetTagAt(string $expected, array $positions, int $index, string $message)
    {
        $this->assertSame($expected, (new Viterbi(0.0, $positions))->getTagAt($index), $message);
    }
}
