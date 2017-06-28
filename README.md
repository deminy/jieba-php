[deminy/jieba-php](https://github.com/deminy/jieba-php)
================
[![Build Status](https://travis-ci.org/deminy/jieba-php.svg?branch=master)](https://travis-ci.org/deminy/jieba-php)
[![Latest Stable Version](https://poser.pugx.org/deminy/jieba-php/v/stable.png)](https://packagist.org/packages/deminy/jieba-php)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/26534521d345458a998eecd3b3008620)](https://www.codacy.com/app/deminy/jieba-php)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)
[![PSR-4](https://img.shields.io/badge/cs-PSR--4-yellow.svg)](http://www.php-fig.org/psr/psr-4/)
[![License](https://poser.pugx.org/deminy/jieba-php/license.svg)](https://packagist.org/packages/deminy/jieba-php)

"结巴中文分词"PHP版本："[结巴中文分词](https://github.com/fxsjy/jieba)"是Sun Junyi开发的Python版的中文分词组件，后来衍生了多种语言实现，包括C++、Java、.NET、Go等等。这份PHP版本基于[fukuball](https://github.com/fukuball/jieba-php)之前做的PHP实现（v0.25）而作了各种更新和改进，包括使用PHP 7的新功能重构代码、使用PSR-4管理autoloading、使用依赖注射等设计模式、更新单元测试的实现、代码升级、以及更多的性能优化和代码更新等等。如果你需要相应的PHP扩展的版本，可考虑jonnywang写的[结巴中文分词之PHP扩展](https://github.com/jonnywang/phpjieba)。

有关算法、词典生成等方面的问题，请参考[结巴中文分词](https://github.com/fxsjy/jieba)相关文档。

# 特点

* 支持三种分词模式：
    * 精确模式，试图将句子最精确地切开，适合文本分析。
    * 全模式，把句子中所有的可以成词的词语都扫描出来, 速度非常快，但是不能解决歧义。
    * 搜索引擎模式，在精确模式的基础上，对长词再次切分，提高召回率，适合用于搜索引擎分词。
* 支持繁体分词。
* 支持自定义词典。

# 安装使用

使用本库你需要至少给PHP分配1G内存限制或更多，主要是用来存储词典信息。随着进一步的优化（包括缓存方面的优化），内存消耗将会降低。

```bash
composer require deminy/jieba-php:dev-master
```

```php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;

var_dump((new Jieba())->cut("怜香惜玉也得要看对象啊！"));
```

# 字符编码处理

本库默认假设用户使用的字符编码为UTF-8。如果运行环境非UTF-8，你可以参考下面的代码文件解决相关编码问题：

> ./examples/encoding.php

# 用法介绍

## 分词

请参考下面的代码文件：

> ./examples/default.php

## 添加自定义词典

开发者可以指定自己自定义的词典，以便包含jieba词库裡没有的词。虽然jieba有新词识别能力，但是自行添加新词可以保证更高的正确率。请参考下面的代码文件：

> ./examples/useUserDict.php

## 关键词提取

请参考下面的代码文件：

> ./examples/extractTags.php

## 词性分词

有关词性说明，请参考《[词性标记](https://gist.github.com/luw2007/6016931)》这篇文档。下面的代码文件可以提供更多的理解：

> ./examples/posseg.php

## 切换成繁体字典

请参考下面的代码文件：

> ./examples/big5.php

## 使用不同的序列化格式（BSON, JSON, MessagePack等）

请参考下面的代码文件：

> ./examples/useDifferentSerializers.php

# 常见问题

1. [模型的数据是如何生成的？](https://github.com/fxsjy/jieba/issues/7)
