<?php

namespace Jieba\Constants;

/**
 * Class Config
 *
 * @package Jieba
 */
class JiebaConstant
{
    const MIN_FLOAT = -3.14e+100;

    const UTF8 = 'UTF-8';

    const REGEX_HAN         = '([\x{4E00}-\x{9FA5}]+)';
    const REGEX_SKIP        = '([a-zA-Z0-9+#\r\n]+)';
    const REGEX_PUNCTUATION = '([\x{ff5e}\x{ff01}\x{ff08}\x{ff09}\x{300e}\x{300c}' .
                              '\x{300d}\x{300f}\x{3001}\x{ff1a}\x{ff1b}\x{ff0c}\x{ff1f}\x{3002}]+)';
    const REGEX_ENG         = '[a-zA-Z+#]+';
    const REGEX_NUMBER      = '[0-9]+';

    const B = 'B';
    const M = 'M';
    const E = 'E';
    const S = 'S';

    /**
     * @see https://github.com/fxsjy/jieba/issues/7 模型的数据是如何生成的？(对BMES的解释）
     */
    const BMES = [
        self::B => self::B, // 开头
        self::M => self::M, // 中间
        self::E => self::E, // 结尾
        self::S => self::S, // 独立成词
    ];
}
