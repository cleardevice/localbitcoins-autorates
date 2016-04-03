#!/bin/sh
DIR=$(dirname $(realpath $0))
EXPIRY=180
CMD="php $DIR/run.php $@"
HASH=$(echo "$CMD" | md5sum | awk '{print $1}')
CACHE="$DIR/cache/$HASH"
test -f "${CACHE}" && [ $(expr $(date +%s) - $(date -r "$CACHE" +%s)) -le $EXPIRY ] || eval "$CMD" > "${CACHE}"
cat "${CACHE}"
