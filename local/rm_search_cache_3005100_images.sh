#!/bin/bash

find $VUFIND_SHARE/cache/large    -name '*.jpg' -delete
find $VUFIND_SHARE/cache/medium   -name '*.jpg' -delete
find $VUFIND_SHARE/cache/small    -name '*.jpg' -delete
rm $VUFIND_SHARE/cache/xml/*
