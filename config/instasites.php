<?php

return [
  'sites_root'      => env('SITES_ROOT', base_path('sites')),
  'builder_api_key' => env('BUILDER_API_KEY', null),
  'max_concurrency' => (int) env('MAX_CONCURRENCY', 6),
];