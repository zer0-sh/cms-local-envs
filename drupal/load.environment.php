<?php

/**
 * Load environment variables from .env file.
 *
 * This file is included by Drupal's settings.php to load
 * environment variables before Drupal bootstraps.
 */

use Dotenv\Dotenv;

$autoloader = require __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

return $autoloader;
