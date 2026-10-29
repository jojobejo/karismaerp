<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SsoGuide extends CI_Controller
{
    public function index()
    {
        $this->load->view('portal/sso_guide');
    }
}
