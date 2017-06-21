[deminy/jieba-php](https://github.com/deminy/jieba-php)
================
[![Build Status](https://travis-ci.org/deminy/jieba-php.svg?branch=master)](https://travis-ci.org/deminy/jieba-php)
[![codecov.io](http://codecov.io/github/deminy/jieba-php/coverage.svg?branch=master)](http://codecov.io/github/deminy/jieba-php?branch=master)
[![Latest Stable Version](https://poser.pugx.org/deminy/jieba-php/v/stable.png)](https://packagist.org/packages/deminy/jieba-php)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/26534521d345458a998eecd3b3008620)](https://www.codacy.com/app/deminy/jieba-php)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)
[![PSR-4](https://img.shields.io/badge/cs-PSR--4-yellow.svg)](http://www.php-fig.org/psr/psr-4/)

"[结巴中文分词](https://github.com/fxsjy/jieba)"PHP版本：基于[fukuball](https://github.com/fukuball/jieba-php)的PHP实现而作的各种更新和改进，包括使用PHP 7的新功能重构代码、使用PSR-4管理autoloading、使用依赖注射等设计模式、更新单元测试的实现、以及更多的性能优化和代码更新等等。

# 功能

支持三种分词模式：

1. 默认精确模式：试图将句子最精确地切开，适合文本分析。
2. 全模式：把句子中所有的可以成词的词语都扫描出来，但是不能解决歧义。（需要充足的字典）
3. 搜寻引擎模式：在精确模式的基础上，对长词再次切分，提高召回率，适合用于搜寻引擎分词。

# 安装使用

使用本库你需要至少给PHP分配1G内存限制(memory_limit >= 1G)。

```bash
composer require deminy/jieba-php:dev-master
```

```php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;

var_dump((new Jieba())->cut("怜香惜玉也得要看对象啊！"));
```

# 算法

* 基于 Trie 树结构实现高效的词图扫描，生成句子中汉字所有可能成词情况所构成的有向无环图（DAG)
* 採用了动态规划查找最大概率路径, 找出基于词频的最大切分组合
* 对于未登录词，採用了基于汉字成词能力的 HMM 模型，使用了 Viterbi 算法
* BEMS 的解释 [https://github.com/fxsjy/jieba/issues/7](https://github.com/fxsjy/jieba/issues/7)

# 接口

* 组件只提供 jieba.cut 方法用于分词。
* cut 方法接受两个输入参数: 1) 第一个参数为需要分词的字符串 2）cut_all 参数用来控制分词模式。
* 待分词的字符串可以是 utf-8 字符串。
* jieba.cut 返回的结构是一个可迭代的数组。

# 字符编码处理

本库默认假设用户使用的字符编码为UTF-8。如果运行环境非UTF-8，你可以参考下面的代码解决相关编码问题：

```php
#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Wrapper;

function testCut() {
    var_dump((new Jieba())->cut("怜香惜玉也得要看对象啊！"));
}

// Here we set default encoding to "iso-8859-1". Under this encoding Chinese characters won't be parsed properly.
mb_internal_encoding('iso-8859-1');

// This function call prints garbled Chinese characters because of improper encoding "iso-8859-1" used.
testCut();

// This function call prints Chinese characters as expected because the function call is executed where UTF-8 is set
// as the internal encoding.
// After the execution, previous internal encoding (iso-8859-1) will be recovered.
Wrapper::run(
    function () {
        testCut();
    }
);

// This function call prints garbled Chinese characters because of improper encoding "iso-8859-1" used.
testCut();
```

# 用法介绍

## 分词

* `cut` 方法接受想个输入参数： 1) 第一个参数为需要分词的字符串 2）cut_all 参数用来控制分词模式
* `cutForSearch` 方法接受一个参数：需要分词的字符串，该方法适合用于搜索引擎构建倒排索引的分词，粒度比较细
    * 注意：待分词的字符串是 utf-8 字符串
* `cut` 以及 `cutForSearch` 返回的结构是一个可迭代的数据。

代码示例：

```php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;

$jieba = new Jieba();

$seg_list = $jieba->cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = $jieba->cut("我来到北京清华大学", true);
var_dump($seg_list); #全模式

$seg_list = $jieba->cut("我来到北京清华大学", false);
var_dump($seg_list); #默认精确模式

$seg_list = $jieba->cut("他来到了网易杭研大厦");
var_dump($seg_list);

$seg_list = $jieba->cutForSearch("小明硕士毕业于中国科学院计算所，后在日本京都大学深造"); #搜索引擎模式
var_dump($seg_list);
```

输出：

```text
array(7) {
  [0]=>
  string(12) "怜香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "对象"
  [6]=>
  string(3) "啊"
}

Full Mode:
array(15) {
  [0]=>
  string(3) "我"
  [1]=>
  string(3) "来"
  [2]=>
  string(6) "来到"
  [3]=>
  string(3) "到"
  [4]=>
  string(3) "北"
  [5]=>
  string(6) "北京"
  [6]=>
  string(3) "京"
  [7]=>
  string(3) "清"
  [8]=>
  string(6) "清华"
  [9]=>
  string(12) "清华大学"
  [10]=>
  string(3) "华"
  [11]=>
  string(6) "华大"
  [12]=>
  string(3) "大"
  [13]=>
  string(6) "大学"
  [14]=>
  string(3) "学"
}

Default Mode:
array(4) {
  [0]=>
  string(3) "我"
  [1]=>
  string(6) "来到"
  [2]=>
  string(6) "北京"
  [3]=>
  string(12) "清华大学"
}
array(6) {
  [0]=>
  string(3) "他"
  [1]=>
  string(6) "来到"
  [2]=>
  string(3) "了"
  [3]=>
  string(6) "网易"
  [4]=>
  string(6) "杭研"
  [5]=>
  string(6) "大厦"
}
(此处，“杭研“并没有在词典中，但是也被 Viterbi 算法识别出来了)

Search Engine Mode:
array(18) {
  [0]=>
  string(6) "小明"
  [1]=>
  string(6) "硕士"
  [2]=>
  string(6) "毕业"
  [3]=>
  string(3) "于"
  [4]=>
  string(6) "中国"
  [5]=>
  string(6) "科学"
  [6]=>
  string(6) "学院"
  [7]=>
  string(9) "科学院"
  [8]=>
  string(15) "中国科学院"
  [9]=>
  string(6) "计算"
  [10]=>
  string(9) "计算所"
  [11]=>
  string(3) "后"
  [12]=>
  string(3) "在"
  [13]=>
  string(6) "日本"
  [14]=>
  string(6) "京都"
  [15]=>
  string(6) "大学"
  [16]=>
  string(18) "日本京都大学"
  [17]=>
  string(6) "深造"
}
```

## 添加自定义词典

开发者可以指定自己自定义的词典，以便包含 jieba 词库裡没有的词。虽然 jieba 有新词识别能力，但是自行添加新词可以保证更高的正确率。

用法： Jieba::loadUserDict(file_name) # file_name 为自定义词典的绝对路径

词典格式和 dict.txt 一样，一个词佔一行；每一行分为三部分，一部分为词语，一部分为词频，一部分为词性，用空格隔开。

范例：

```text
云计算 5 n
李小福 2 n
创新办 3 n
```
* 之前： 李小福 / 是 / 创新 / 办 / 主任 / 也 / 是 / 云 / 计算 / 方面 / 的 / 专家 /
* 加载自定义词库后：　李小福 / 是 / 创新办 / 主任 / 也 / 是 / 云计算 / 方面 / 的 / 专家 /

说明："通过用户自定义词典来增强歧义纠错能力" --- https://github.com/fxsjy/jieba/issues/14

## 关键词提取

* JiebaAnalyse::extractTags($content, $top_k)
* content 为待提取的文本
* top_k 为返回几个 TF/IDF 权重最大的关键词，默认值为 20

代码示例：

```php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\JiebaAnalyse;
use Jieba\Options;
use Jieba\Option\Dict;
use Jieba\Option\Mode;

$jieba = new Jieba(
    (new Options())->setDict(new Dict(Dict::SMALL))->setMode(new Mode(Mode::TEST))
);
$tags = JiebaAnalyse::singleton()->extractTags(
    $jieba->cut(file_get_contents(dirname(__DIR__) . '/tests/dict/lyric.txt')),
    10
);
var_dump($tags);
```

输出：

```text
array(10) {
  ["是否"]=>
  float(1.2196321889395)
  ["一般"]=>
  float(1.0032459890209)
  ["肌迫"]=>
  float(0.64654314660465)
  ["怯懦"]=>
  float(0.44762844339349)
  ["藉口"]=>
  float(0.32327157330233)
  ["逼不得已"]=>
  float(0.32327157330233)
  ["不安全感"]=>
  float(0.26548304656279)
  ["同感"]=>
  float(0.23929673812326)
  ["有把握"]=>
  float(0.21043366018744)
  ["空洞"]=>
  float(0.20598261709442)
}
```

## 词性分词

* 词性说明：[https://gist.github.com/luw2007/6016931](https://gist.github.com/luw2007/6016931)

代码示例：

```php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Posseg;

$seg_list = (new Posseg(new Jieba()))->cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。");
var_dump($seg_list);
```

输出:

```text
array(21) {
  [0]=>
  array(2) {
    ["word"]=>
    string(3) "这"
    ["tag"]=>
    string(1) "r"
  }
  [1]=>
  array(2) {
    ["word"]=>
    string(3) "是"
    ["tag"]=>
    string(1) "v"
  }
  [2]=>
  array(2) {
    ["word"]=>
    string(6) "一个"
    ["tag"]=>
    string(1) "m"
  }
  [3]=>
  array(2) {
    ["word"]=>
    string(18) "伸手不见五指"
    ["tag"]=>
    string(1) "i"
  }
  [4]=>
  array(2) {
    ["word"]=>
    string(3) "的"
    ["tag"]=>
    string(2) "uj"
  }
  [5]=>
  array(2) {
    ["word"]=>
    string(6) "黑夜"
    ["tag"]=>
    string(1) "n"
  }
  [6]=>
  array(2) {
    ["word"]=>
    string(3) "。"
    ["tag"]=>
    string(1) "x"
  }
  [7]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [8]=>
  array(2) {
    ["word"]=>
    string(3) "叫"
    ["tag"]=>
    string(1) "v"
  }
  [9]=>
  array(2) {
    ["word"]=>
    string(9) "孙悟空"
    ["tag"]=>
    string(2) "nr"
  }
  [10]=>
  array(2) {
    ["word"]=>
    string(3) "，"
    ["tag"]=>
    string(1) "x"
  }
  [11]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [12]=>
  array(2) {
    ["word"]=>
    string(3) "爱"
    ["tag"]=>
    string(1) "v"
  }
  [13]=>
  array(2) {
    ["word"]=>
    string(6) "北京"
    ["tag"]=>
    string(2) "ns"
  }
  [14]=>
  array(2) {
    ["word"]=>
    string(3) "，"
    ["tag"]=>
    string(1) "x"
  }
  [15]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [16]=>
  array(2) {
    ["word"]=>
    string(3) "爱"
    ["tag"]=>
    string(1) "v"
  }
  [17]=>
  array(2) {
    ["word"]=>
    string(6) "Python"
    ["tag"]=>
    string(3) "eng"
  }
  [18]=>
  array(2) {
    ["word"]=>
    string(3) "和"
    ["tag"]=>
    string(1) "c"
  }
  [19]=>
  array(2) {
    ["word"]=>
    string(3) "C++"
    ["tag"]=>
    string(3) "eng"
  }
  [20]=>
  array(2) {
    ["word"]=>
    string(3) "。"
    ["tag"]=>
    string(1) "x"
  }
}
```

## 切换成繁体字典

代码示例：

```php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Jieba\Jieba;
use Jieba\Options;
use Jieba\Option\Dict;

$jieba = new Jieba((new Options())->setDict(new Dict(Dict::BIG)));

$seg_list = $jieba->cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = $jieba->cut("憐香惜玉也得要看對象啊！");
var_dump($seg_list);
```

输出:

```text
array(7) {
  [0]=>
  string(12) "怜香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "对象"
  [6]=>
  string(3) "啊"
}
array(7) {
  [0]=>
  string(12) "憐香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "對象"
  [6]=>
  string(3) "啊"
}
```

# 常见问题

1. 模型的数据是如何生成的？ https://github.com/fxsjy/jieba/issues/7
2. 这个库的授权是？ https://github.com/fxsjy/jieba/issues/2
