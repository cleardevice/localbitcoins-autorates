#!/bin/sh
DIR=$(dirname $(realpath $0))
php $DIR/run.php -o screen -t buy -c rub --lt 17500 | grep QIWI
