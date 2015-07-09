#!/bin/bash

find $VUFIND_CACHE_CACHE_DIR/cache/large    -name '*.jpg' -delete
find $VUFIND_CACHE_CACHE_DIR/cache/medium   -name '*.jpg' -delete
find $VUFIND_CACHE_CACHE_DIR/cache/small    -name '*.jpg' -delete
rm $VUFIND_CACHE_CACHE_DIR/cache/xml/*
