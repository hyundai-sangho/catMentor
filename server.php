<?php

/**
 * Laravel - 웹 장인을 위한 PHP 프레임워크
 *
 * @package 라라벨
 * @author 테일러 오트웰 <taylor@laravel.com>
 */

$uri = urldecode(
  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
  return false;
}

require_once __DIR__ . '/public/index.php';
