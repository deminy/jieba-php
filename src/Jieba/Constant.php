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

    const UTF8 = 'UTF-8';

    const REGEX_HAN         = '([\x{4E00}-\x{9FA5}]+)';
    const REGEX_SKIP        = '([a-zA-Z0-9+#\r\n]+)';
    const REGEX_PUNCTUATION = '([\x{ff5e}\x{ff01}\x{ff08}\x{ff09}\x{300e}\x{300c}\x{300d}\x{300f}\x{3001}\x{ff1a}\x{ff1b}\x{ff0c}\x{ff1f}\x{3002}]+)';
    const REGEX_ENG         = '[a-zA-Z+#]+';
    const REGEX_NUMBER      = '[0-9]+';

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
