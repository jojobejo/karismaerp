<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

// Hook otomatis pencatatan log perangkat dan akses halaman (Hostinger & Local)
$hook['post_controller_constructor'][] = array(
    'class'    => 'Access_logger',
    'function' => 'log_request',
    'filename' => 'Access_logger.php',
    'filepath' => 'hooks'
);

