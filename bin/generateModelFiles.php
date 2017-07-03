#!/usr/bin/env php
<?php
/**
 * Convert and generate model files.
 *
 * Usage:
 *     ./bin/generateModelFiles.php                   # generate model files.
 *     ./bin/generateModelFiles.php posseg/prob_trans # generate model file for "posseg/prob_trans" only.
 *
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Factory\LoggerFactory;
use Jieba\ModelConverters\ModelConverter;

(new ModelConverter(LoggerFactory::getLogger()))->convert(array_slice($argv, 1));
