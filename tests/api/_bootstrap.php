<?php
if (!defined('HTTP_STATUS_OK')) {
    define('HTTP_STATUS_OK', 200);
}

if (!defined('HTTP_STATUS_CREATED')) {
    define('HTTP_STATUS_CREATED', 201);
}

if (!defined('HTTP_STATUS_NO_CONTENT')) {
    define('HTTP_STATUS_NO_CONTENT', 204);
}

if (!defined('HTTP_STATUS_NOT_FOUND')) {
    define('HTTP_STATUS_NOT_FOUND', 404);
}

if (!defined('HTTP_STATUS_METHOD_NOT_ALLOWED')) {
    define('HTTP_STATUS_METHOD_NOT_ALLOWED', 405);
}

if (!defined('HTTP_STATUS_CONFLICT')) {
    define('HTTP_STATUS_CONFLICT', 409);
}

if (!defined('HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE')) {
    define('HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE', 415);
}

if (!defined('HTTP_STATUS_UNPROCESSABLE_ENTITY')) {
    define('HTTP_STATUS_UNPROCESSABLE_ENTITY', 422);
}

Codeception\Util\Autoload::registerSuffix('Page', __DIR__ . DIRECTORY_SEPARATOR . '_pages');