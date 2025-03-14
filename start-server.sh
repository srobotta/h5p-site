#!/bin/bash

# Start the built in PHP webserver. Optional parameter is the port.

DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
PORT=3000
MSIZE=100M
if [ ! -z $1 ]; then
  PORT=$1
fi

php -S localhost:$PORT \
  -d upload_max_filesize=$MSIZE \
  -d post_max_size=$MSIZE \
  -t $DIR/html