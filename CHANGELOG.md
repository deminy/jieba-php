0.25.3 / TBD
============

  * New class \Jieba\PosTagConstant from text file /dict/pos_tag_readable.txt.
  * Standardize data structure with new class \Jieba\Word and \Jieba\Words.
  * Remove class \Jieba\Option\Mode.
  * Classes better organized.
  * Support [BSON](http://bsonspec.org) as serializer.

0.25.2 / 2017-06-22
===================

  * Allow to cache dictionary data and other data.
  * Use package "league/csv" to read/parse dictionary files.
  * Removed useless class \Jieba\JiebaCache.
  * More unit tests.
  * Move/remove non-core dictionary files.
  * Support to use the library where UTF-8 is not the internal encoding.
  * Move dictionary trie files out from Git.
  * Support [MessagePack](http://msgpack.org) as serializer; allow to use different serializers.

0.25.1 / 2017-06-19
===================

  * Rewritten with PHP 7; use PSR-4 and dependency injection to organize classes.
  * Added logging functionality following PSR-3.
  * More unit tests.
  * File structure reorganization.
  * Many other updates, adjustments and clean up.

0.25 and earlier versions
=========================

  * Forked from https://github.com/fukuball/jieba-php
