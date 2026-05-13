<!-- controller/C_Logistik.php -->
<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class C_Opname extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Opname');
    }

    public function index()
    {
        $data['page_title'] = 'OPNAME';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/opname/dashboard_opname.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
