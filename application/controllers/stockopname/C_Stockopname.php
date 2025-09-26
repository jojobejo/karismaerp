<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Stockopname extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Stockopname", "opname");
        $this->load->helper('stock_helper');
        $this->load->helper('login_auth');
        is_logged_in();
    }

    public function index()
    {
        $data['page_title']     = 'KARISMA - LOGISTIK';
        $data['result']  = $this->opname->get_opname_result();
        $data['summary'] = $this->opname->get_summary_match();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/dashboard_opname.php', $data);
        $this->load->view('partial/main/footeropname.php');
    }

    function hitung_qty($qty_box, $qty_pcs, $dimensi)
    {
        return ($qty_box * $dimensi) + $qty_pcs;
    }

    public function compare_opname_all()
    {
        $data['page_title']         = 'KARISMA - ICS';
        $data['compare_all']        = $this->opname->get_stockopname();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/compare_opname.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function compare_opname_exp()
    {
        $data['page_title']         = 'KARISMA - ICS';
        $data['compare_exp']        = $this->opname->get_stockopname_exp();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/compare_opname_exp.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function usrstockopname()
    {
        $data['page_title']         = 'KARISMA - ICS';

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
        $result = $this->opname->search_barang($search);
        echo json_encode($result);
    }

    public function save_opname()
    {
        $nmbarang   = $this->input->post('nama_barang');
        $box        = (int) $this->input->post('qty_box');
        $pcs        = (int) $this->input->post('qty_pcs');

        $dimensi    = $this->opname->getDimensi($nmbarang);
        $idbarang   = $this->opname->getId($nmbarang);
        $total_qty  = $this->hitung_qty($box, $pcs, $dimensi);

        $data = [
            'kode_barang'   => $idbarang,
            'nama_barang'   => $nmbarang,
            'expired_date'  => '1/1/1000',
            'qty'           => $total_qty,
            'qty_pcs'       => $pcs,
            'qty_box'       => $box,
            'nolot'         => '-',
            'input_by'      => $this->session->userdata('nama'),
            'input_at'      => date('Y-m-d H:i:s'),
            'wilayah'       => $this->session->userdata('tim'),
        ];

        $log = [
            'nama_user'     => $this->session->userdata('nama'),
            'nama_barang'   => $nmbarang,
            'qty'           => $total_qty,
            'qty_box'       => $box,
            'qty_pcs'       => $pcs,
            'no_lot'        => '-',
            'exp_date'      => '-',
            'inputer'       => $this->session->userdata('nik'),
            'tgl_input'     => date('Y-m-d'),
            'keterangan'    => 'Stock Opname'
        ];

        $this->opname->insert_opname($data);
        $this->opname->insert_log($log);

        echo json_encode(['status' => 'ok', 'message' => 'Data berhasil disimpan']);
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

        $dimensi    = $this->opname->getDimensi($nmbarang);
        $total_qty  = $this->hitung_qty($box, $pcs, $dimensi);

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

    public function detail_inputer($id)
    {
        $data['page_title']         = 'KARISMA - ICS';
        $data['item_info']          = $this->opname->get_info_barang($id);
        $data['item_detail']        = $this->opname->get_detail_inputer($id);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/detail_inputer.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
