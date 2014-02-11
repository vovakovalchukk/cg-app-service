#!/bin/sh
set -e
if [ ! -d "vendor/" ]; then
  COMPOSER_CACHE_DIR=/tmp/composer composer install --no-dev --no-interaction
  touch /tmp/composer-exectued-on-test-deploy-script.txt
fi
touch /tmp/test-deploy-script-complete.txt