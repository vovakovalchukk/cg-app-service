#!/bin/sh
set -e
if [ ! -d "vendor/" ]; then
  COMPOSER_CACHE_DIR=/tmp/composer composer install --no-dev --no-interaction
fi