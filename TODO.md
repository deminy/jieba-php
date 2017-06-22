# TODOs for Next Stable Release

* Each Travis CI build should finish in around 3 minutes.
* Comments in demo scripts.

# TODOs

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

# DONE

* Clean up dictionary files and model files; remove unnecessary  dictionary files and model files.
* Remove or update class \Jieba\JiebaCache.
* Add shell scripts.
* Move following files to proper locations:
    * dict/lyric.txt
    * dict/user_dict.txt
* Don't hardcode internal encoding.

# POSTPONED

# CANCELLED
