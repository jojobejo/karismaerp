<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Stockopname extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Stockopname');
        $this->load->helper('stock_helper');
        $this->load->helper('login_auth');
        is_logged_in();
    }

    public function index()
    {
        $data['page_title']     = 'KARISMA - LOGISTIK';
        // $data['resultopname']   = $this->M_Stockopname->get_result_opname();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/dashboard_opname.php', $data);
        $this->load->view('partial/main/footeropname.php');
    }

    public function compare_opname_all()
    {
        $data['page_title']         = 'KARISMA - ICS';
        $data['compare_all']        = $this->M_Stockopname->get_stockopname();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/compare_opname.php', $data);
        $this->load->view('partial/main/footer.php');
        // $this->load->view('content/logistik/ics/ajaxics.php', $data);
    }

    public function compare_opname_exp()
    {
        $data['page_title']         = 'KARISMA - ICS';
        $data['compare_exp']        = $this->M_Stockopname->get_stockopname_exp();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/compare_opname_exp.php', $data);
        $this->load->view('partial/main/footer.php');
        // $this->load->view('content/logistik/ics/ajaxics.php', $data);
    }

    public function usrstockopname()
    {
        $data['page_title']         = 'KARISMA - ICS';
        // $data['data_ics'] = $this->M_Logistik->getAllICS();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/stockopname_user.php', $data);
        $this->load->view('partial/main/footeropname.php');
    }

    public function stockopname()
    {
        $data['page_title'] = 'KARISMA - ICS';


        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/stockopname_user.php', $data);
        $this->load->view('partial/main/footeropname.php');
    }

    public function searchbarang()
    {
        $search = $this->input->get('search');
        $result = $this->db->like('nama_barang', $search)
            ->group_by('nama_barang')
            ->get('stockopname_master')->result();
        $data = [];
        foreach ($result as $row) {
            $data[] = ['id' => $row->nama_barang, 'text' => $row->nama_barang];
        }
        echo json_encode($data);
    }

    public function op_search_get_exp_date()
    {
        $nama_barang = $this->input->post('nama_barang');

        $exp_dates = $this->db->select('expired_date')
            ->where('nama_barang', $nama_barang)
            ->group_by('expired_date')
            ->order_by('expired_date', 'ASC')
            ->get('stockopname_master')
            ->result();

        $dimensi = $this->db->select('p, l, t')
            ->where('nm_barang', $nama_barang)
            ->get('tb_master_barang')
            ->row();

        $data_dimensi = [
            'p' => $dimensi ? $dimensi->p : null,
            'l' => $dimensi ? $dimensi->l : null,
            't' => $dimensi ? $dimensi->t : null,
        ];

        $result = [
            'exp_dates' => $exp_dates,
            'dimensi' => $data_dimensi
        ];
        echo json_encode($result);
    }

    public function request_opname()
    {
        $nmbarang   = $this->input->post('nama_barang');
        $box        = $this->input->post('qty_box_manual');
        $pcs        = $this->input->post('qty_pcs_manual');

        $dimensi    = $this->M_Stockopname->getDimensi($nmbarang);
        $total_qty  = hitung_qty($box, $pcs, $dimensi);

        $opname = [
            'nama_barang'   => $nmbarang,
            'exp_date'      => $this->input->post('exp_date_manual'),
            'qty'           => $total_qty,
            'qty_box'       => $box,
            'qty_pcs'       => $pcs,
            'inputer'       => $this->session->userdata('nama'),
            'tim'           => $this->session->userdata('tim'),
            'status'        => '1',
            'acc_with'      => '-',
            'input_at'      => date('d/m/Y')
        ];

        $log = [
            'nama_user'     => $this->session->userdata('nama'),
            'nama_barang'   => $nmbarang,
            'qty'           => $total_qty,
            'qty_box'       => $box,
            'qty_pcs'       => $pcs,
            'no_lot'        => '-',
            'exp_date'      => $this->input->post('exp_date_manual'),
            'inputer'       => $this->session->userdata('nik'),
            'tgl_input'     => date('Y-m-d'),
            'keterangan'    => 'expired-date tidak-ada'
        ];

        $this->db->insert('tb_req_opname', $opname);
        $this->db->insert('tb_log_ics', $log);
        echo 'ok';
    }

    public function save_opname()
    {
        $nmbarang   = $this->input->post('nama_barang');
        $box        = $this->input->post('qty_box');
        $pcs        = $this->input->post('qty_pcs');

        $dimensi    = $this->M_Stockopname->getDimensi($nmbarang);
        $total_qty  = hitung_qty($box, $pcs, $dimensi);

        $opname = [
            'nama_barang'   => $nmbarang,
            'expired_date'  => $this->input->post('exp_date'),
            'qty'           => $total_qty,
            'qty_pcs'       => $pcs,
            'qty_box'       => $box,
            'nolot'         => $this->session->userdata('nama'),
            'input_by'      => $this->session->userdata('nama'),
            'input_at'      => date('d/m/Y'),
            'wilayah'       => $this->session->userdata('tim'),
        ];

        $log = [
            'nama_user'     => $this->session->userdata('nama'),
            'nama_barang'   => $nmbarang,
            'qty'           => $total_qty,
            'qty_box'       => $box,
            'qty_pcs'       => $pcs,
            'no_lot'        => '-',
            'exp_date'      => $this->input->post('exp_date'),
            'inputer'       => $this->session->userdata('nik'),
            'tgl_input'     => date('Y-m-d'),
            'keterangan'    => 'Stock Opname'
        ];

        $this->db->insert('stockopname_opname', $opname);
        $this->db->insert('tb_log_ics', $log);
        echo 'ok';
    }
}
