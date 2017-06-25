#!/usr/bin/env php
<?php
/**
 * This script is to generate class \Jieba\Constants\PosTagConstant from text file /dict/pos_tag_readable.txt.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Helper\DictHelper;
use Jieba\Helper\Helper;

$template = <<<EOC
<?php

namespace Jieba\Constants;

/**
 * Class PosTagConstant. This file is generated with script /bin/genPosTagConstant.php.
 *
 * @package Jieba
 * @see https://github.com/deminy/jieba-php/blob/master/bin/genPosTagConstant.php
 */
class PosTagConstant
{
{{# keys }}
    const {{ keyWithPadding }} = '{{ key }}';
{{/ keys }}

    const TAGS = [
{{# names }}
        self::{{ keyWithPadding }} => '{{ name }}',
{{/ names }}
    ];
}

EOC;

$pos_tag_readable = DictHelper::getPosTagReadable(Helper::getDictFilePath('pos_tag_readable.txt'));
if (count(array_unique(array_keys($pos_tag_readable))) != count($pos_tag_readable)) {
    echo "Error: tags in file 'pos_tag_readable.txt' is not unique.\n";
    exit(1);
}

$maxLen  = 0;
foreach ($pos_tag_readable as $key => $name) {
    $maxLen = max($maxLen, strlen($key));
}

$context = [];
foreach ($pos_tag_readable as $key => $name) {
    $keyWithPadding = sprintf("%-{$maxLen}s", strtoupper($key));
    $context['keys'][]  = ['keyWithPadding' => $keyWithPadding, 'key' => $key];
    $context['names'][] = ['keyWithPadding' => $keyWithPadding, 'key' => $key, 'name' => $name];
}

$m = new Mustache_Engine;
file_put_contents(dirname(__DIR__)  . '/src/Jieba/Constants/PosTagConstant.php', $m->render($template, $context));
