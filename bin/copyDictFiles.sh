#!/usr/bin/env bash

if [ -z "$CURRENT_SCRIPT_PATH" ] ; then
    # Switch to directory where this shell script sits.
    pushd `dirname $0` > /dev/null
    CURRENT_SCRIPT_PATH=`pwd -P`
    # Switch back to current directory.
    popd > /dev/null
fi

if [ -d "${CURRENT_SCRIPT_PATH}/../vendor/fxsjy/jieba" ] ; then
    cd  "${CURRENT_SCRIPT_PATH}/../vendor/fxsjy/jieba"

    cp extra_dict/*          "${CURRENT_SCRIPT_PATH}/../data/dict/."
    cp jieba/analyse/idf.txt "${CURRENT_SCRIPT_PATH}/../data/dict/."
    cp jieba/dict.txt        "${CURRENT_SCRIPT_PATH}/../data/dict/."

    echo "Dictionary files copied to under folder dict/."
else
    echo "Package fxsjy/jieba not installed. No dictionary files copied."
fi
