#!/bin/sh
COMPOSER_CACHE_DIR=/tmp/composer composer install --no-dev --no-interaction
php vendor/bin/phinx migrate -c phinx/phinx.yml
