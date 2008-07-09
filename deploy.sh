#!/bin/sh

rsync "$@" -a \
  --exclude="`basename $0`" \
  --exclude=".DS_Store" \
  --exclude=".git*" \
  $HOME/Sites/home/ papango:public_html
