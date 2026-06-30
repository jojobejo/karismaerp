<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['sso_enabled'] = TRUE;
$config['sso_portal_secret'] = getenv('KARISMA_SSO_SECRET') ?: '';
$config['sso_code_ttl'] = 60;
$config['sso_session_ttl'] = 7200;
$config['sso_cookie_domain'] = getenv('KARISMA_SSO_COOKIE_DOMAIN') ?: '';
$config['sso_allowed_redirect_hosts'] = array();
