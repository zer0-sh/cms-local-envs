<?php

/**
 * Drupal settings.php - loads config from environment variables.
 */

$databases['default']['default'] = [
  'database' => $_ENV['DB_NAME'] ?? 'drupal',
  'username' => $_ENV['DB_USER'] ?? 'drupal',
  'password' => $_ENV['DB_PASSWORD'] ?? '',
  'host' => $_ENV['DB_HOST'] ?? 'db',
  'port' => $_ENV['DB_PORT'] ?? '3306',
  'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];

$settings['hash_salt'] = $_ENV['DRUPAL_HASH_SALT'] ?? 'change_me';

$settings['trusted_host_patterns'] = array_filter(
  explode(',', $_ENV['DRUPAL_TRUSTED_HOST_PATTERNS'] ?? '^localhost$')
);

$settings['config_sync_directory'] = '../config/sync';

$settings['file_private_path'] = '../private';

/**
 * Environment-specific overrides.
 */
$env = $_ENV['DRUPAL_ENV'] ?? 'production';

if ($env === 'development') {
  $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
  $config['system.performance']['css']['preprocess'] = FALSE;
  $config['system.performance']['js']['preprocess'] = FALSE;
  $settings['cache']['bins']['render'] = 'cache.backend.null';
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
  $settings['cache']['bins']['page'] = 'cache.backend.null';
}

/**
 * Load local overrides if they exist.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
