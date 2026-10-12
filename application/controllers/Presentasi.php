<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Presentasi Karisma ERP
 * Menyajikan slide deck presentasi sistem terpadu & ekosistem bisnis digital.
 * Dapat diakses secara interaktif, fullscreen, dan remote-ready.
 */
class Presentasi extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // $route['presentasi_erp']                        = 'Presentasi/ppt_erp';
    // $route['presentasi_karismaonline']              = 'Presentasi/ppt_karismaonline';

    public function ppt_erp()
    {
        $this->load->view('presentasi/ppt_erp');
    }
        public function ppt_karismaonline()
    {
        $this->load->view('presentasi/ppt_karismaonline');
    }
}
