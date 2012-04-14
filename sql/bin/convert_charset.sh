#!/bin/sh

iconv -f iso-8859-15 -t utf8 $1 > hhh
mv hhh $1

