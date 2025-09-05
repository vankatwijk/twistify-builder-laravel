<?php

return [
  'sites_root'      => env('SITES_ROOT', base_path('sites')),
  'builder_api_key' => env('BUILDER_API_KEY', null),
  'max_concurrency' => (int) env('MAX_CONCURRENCY', 6),

  // Apache Configuration
  'apache' => [
    'sites_available' => env('APACHE_SITES_AVAILABLE', '/etc/apache2/sites-available'),
    'sites_enabled'   => env('APACHE_SITES_ENABLED', '/etc/apache2/sites-enabled'),
    'reload_command'  => env('APACHE_RELOAD_COMMAND', 'sudo systemctl reload apache2'),
    'enabled'         => env('APACHE_INTEGRATION_ENABLED', true),
  ],
];
