<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Ics extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Ics');
        $this->load->helper('stock_helper');
    }

    public function index()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['barang_ics']         = $this->M_Ics->list_barang_ics();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update_inline()
    {
        $nama_barang = $this->input->post('nama_barang');
        $exp_date = $this->input->post('exp_date');
        $field = $this->input->post('field');
        $value = (int) $this->input->post('value');

        $barang = $this->db->get_where('tb_mbarang', ['nm_barang' => $nama_barang])->row();
        if (!$barang) {
            echo json_encode(['status' => 'failed', 'message' => 'Barang tidak ditemukan']);
            return;
        }

        $unit_size = $barang->p * $barang->l * $barang->t;
        $current = $this->db->get_where('tb_ics_opname', [
            'nama_barang' => $nama_barang,
            'exp_date' => $exp_date,
            'DATE(input_at)' => date('Y-m-d')
        ])->row();

        if ($field == 'opname_box') {
            $new_qty = $value * $unit_size;
            if ($current) {
                $this->db->set('qty', $new_qty + ($current->qty % $unit_size));
                $this->db->where('id', $current->id);
                $this->db->update('tb_ics_opname');
            } else {
                $this->db->insert('tb_ics_opname', [
                    'nama_barang' => $nama_barang,
                    'exp_date' => $exp_date,
                    'qty' => $new_qty,
                    'input_at' => date('Y-m-d H:i:s')
                ]);
            }
        } elseif ($field == 'opname_pcs') {
            $new_qty = $value;
            if ($current) {
                $this->db->set('qty', floor($current->qty / $unit_size) * $unit_size + $new_qty);
                $this->db->where('id', $current->id);
                $this->db->update('tb_ics_opname');
            } else {
                $this->db->insert('tb_ics_opname', [
                    'nama_barang' => $nama_barang,
                    'exp_date' => $exp_date,
                    'qty' => $new_qty,
                    'input_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        echo json_encode(['status' => 'success']);
    }

    public function get_data()
    {
        $data = $this->M_Ics->list_barang_ics();
        echo json_encode($data);
    }

    public function inline_update()
    {
        $id    = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');

        if ($field === 'qty')  $value = (int)$value;

        $ok = $this->Stok_model->update_cell($id, $field, $value);
        echo json_encode(['success' => $ok]);
    }
}
