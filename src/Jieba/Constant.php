<?php

namespace Jieba;

/**
 * Class Config
 *
 * @package Jieba
 */
class Constant
{
    const MIN_FLOAT = -3.14e+100;

    const B = 'B';
    const M = 'M';
    const E = 'E';
    const S = 'S';

    const BMES = [
        self::B => self::B,
        self::M => self::M,
        self::E => self::E,
        self::S => self::S,
    ];
}
