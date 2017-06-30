# TODOs for Next Stable Release

* move method \Jieba\Helper\DictHelper::DictHelper() into class \Jieba\Data\TopArrayElement, and rename class \Jieba\Data\TopArrayElement if needed.

# TODOs

* There are two different types of method cutSentence(). Rename them properly.
* Each Travis CI build should finish in around 3 minutes.
* Comments in demo scripts.
* Upgrade from 0.25 to latest version 0.36 of https://github.com/fxsjy/jieba
* Better support on caching.
* Support persistent storage (e.g., MySQL, Couchbase, etc).
* Clean up examples.
* Sample code on caching:
    * Sample code on how to enable/disable cache.
    * Sample code on how to use different backend engines for caching.
* Allow to choose different encoding methods (JSON or MessagePack) when packing via script _./bin/gen_dict_json.php_.
* Unit test for different serializer:
    * JSON
    * MessagePack
* Optimize model files.

# DONE

* Clean up dictionary files and model files; remove unnecessary  dictionary files and model files.
* Remove or update class \Jieba\JiebaCache.
* Add shell scripts.
* Move following files to proper locations:
    * dict/lyric.txt
    * dict/user_dict.txt
* Don't hardcode internal encoding.
* Create BSON/JSON/Msgpack files for ./dict/idf.txt.

# POSTPONED

# CANCELLED
