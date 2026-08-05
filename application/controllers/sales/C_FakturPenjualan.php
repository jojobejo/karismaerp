<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_FakturPenjualan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('username')) {
            redirect('login');
        }
        $this->load->database();
    }

    private function _getUser()
    {
        return [
            'id'       => $this->session->userdata('id'),
            'nama'     => $this->session->userdata('nama'),
            'username' => $this->session->userdata('username'),
            'jobdesk'  => strtoupper((string)$this->session->userdata('jobdesk')),
            'lv'       => $this->session->userdata('lv'),
        ];
    }

    private function _isAdmPnj()
    {
        $user = $this->_getUser();
        // Admin Penjualan, Admin, etc
        return in_array($user['jobdesk'], ['ADMPNJ', 'ADMIN', 'KIUSCC', 'SC', 'MANAGERSC', 'KADEPSC']);
    }

    public function index()
    {
        if (!$this->_isAdmPnj()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
            redirect('dashboard');
            return;
        }

        $filter = [
            'date1'  => $this->input->get('date1') ?: date('Y-m-01'),
            'date2'  => $this->input->get('date2') ?: date('Y-m-t'),
            'status' => $this->input->get('status') ?: 'all'
        ];

        $this->db->select('fp.*, so.nama_sales as so_salesman, c.nama_customer as master_customer_name');
        $this->db->from('tbso_faktur_penjualan fp');
        $this->db->join('tbso_sales_order so', 'so.id_so = fp.id_so', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = fp.kd_customer', 'left');
        $this->db->where('fp.tanggal_faktur >=', $filter['date1']);
        $this->db->where('fp.tanggal_faktur <=', $filter['date2']);
        if ($filter['status'] !== 'all') {
            $this->db->where('fp.status', $filter['status']);
        }
        $this->db->order_by('fp.tanggal_faktur', 'DESC');
        $this->db->order_by('fp.id_faktur', 'DESC');
        $fakturs = $this->db->get()->result_array();

        $data['page_title'] = 'KARISMA — Semua Faktur Penjualan';
        $data['user']       = $this->_getUser();
        $data['fakturs']    = $fakturs;
        $data['filter']     = $filter;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_penjualan_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function edit_qty($id_faktur)
    {
        if (!$this->_isAdmPnj()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses.');
            redirect('faktur_penjualan');
            return;
        }

        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$faktur) {
            $this->session->set_flashdata('error', 'Faktur tidak ditemukan.');
            redirect('faktur_penjualan');
            return;
        }

        $details = $this->db->get_where('tbso_faktur_detail', ['id_faktur' => $id_faktur])->result_array();

        $data['page_title'] = 'KARISMA — Edit Qty Faktur ' . $faktur['no_faktur'];
        $data['user']       = $this->_getUser();
        $data['faktur']     = $faktur;
        $data['details']    = $details;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_penjualan_edit_qty.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update_qty($id_faktur)
    {
        if (!$this->_isAdmPnj() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('faktur_penjualan');
            return;
        }

        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$faktur) {
            $this->session->set_flashdata('error', 'Faktur tidak ditemukan.');
            redirect('faktur_penjualan');
            return;
        }

        $id_detail_arr = $this->input->post('id_detail') ?: [];
        $qty_arr       = $this->input->post('qty') ?: [];
        $catatan       = $this->input->post('catatan_revisi');
        $user          = $this->_getUser();

        $this->db->trans_start();

        foreach ($id_detail_arr as $i => $id_detail) {
            $new_qty = (float)str_replace(',', '', ($qty_arr[$i] ?? 0));
            $detail = $this->db->get_where('tbso_faktur_detail', ['id' => $id_detail, 'id_faktur' => $id_faktur])->row_array();
            
            if ($detail && $new_qty >= 0) {
                $hrg_satuan = (float)$detail['hrg_satuan'];
                $disc = (float)$detail['disc'];
                $pajak = (float)$detail['pajak'];

                // Hitung subtotal baru berdasarkan qty baru (tanpa mengubah harga)
                $sub_before = $new_qty * $hrg_satuan;
                $disc_val   = $sub_before * ($disc / 100);
                $sub_after  = $sub_before - $disc_val;
                $pajak_val  = $sub_after * ($pajak / 100);
                $total_harga = $sub_after + $pajak_val;

                $this->db->where('id', $id_detail);
                $this->db->update('tbso_faktur_detail', [
                    'qty'                  => $new_qty,
                    'subtotal_before_disc' => $sub_before,
                    'subtotal_after_disc'  => $sub_after,
                    'total_harga'          => $total_harga,
                ]);
            }
        }

        // Catat Log 
        $this->db->insert('tb_editlog_faktur', [
            'no_faktur'  => $faktur['no_faktur'],
            'edit_by'    => $user['nama'],
            'edit_date'  => date('Y-m-d H:i:s'),
            'catatan'    => "Revisi Qty (Retur Revisi). Catatan: " . $catatan
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal mengupdate qty faktur.');
        } else {
            $this->session->set_flashdata('success', "Qty Faktur <strong>{$faktur['no_faktur']}</strong> berhasil diupdate.");
        }

        redirect('faktur_penjualan');
    }
}
