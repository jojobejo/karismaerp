<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['plafon_api_base_url'] = getenv('PLAFON_API_BASE_URL') ?: 'https://plafon.kiu.co.id';
$config['plafon_api_key'] = getenv('PLAFON_API_KEY') ?: '';
$config['plafon_api_timeout'] = 5;
$config['plafon_api_cache_ttl'] = 60;
$config['plafon_api_max_pages'] = 100;

$local_config = APPPATH . 'config/plafon_api_local.php';
if (is_file($local_config)) {
    include $local_config;
}
