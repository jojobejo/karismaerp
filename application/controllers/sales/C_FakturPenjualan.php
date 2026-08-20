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

        $this->db->select('fp.*, c.nama_customer as master_customer_name, c.nama_sales as master_sales_name, (SELECT COALESCE(SUM(fd.total_harga), 0) FROM tbso_faktur_detail fd WHERE fd.id_faktur = fp.id_faktur) as total_faktur', FALSE);
        $this->db->from('tbso_faktur_penjualan fp');
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

        $mode = $this->input->get('mode') ?: 'all'; // 'qty', 'harga', atau 'all'

        if ($mode === 'qty') {
            $data['page_title'] = 'KARISMA — Edit Qty Faktur ' . $faktur['no_faktur'];
        } elseif ($mode === 'harga') {
            $data['page_title'] = 'KARISMA — Edit Total Harga Faktur ' . $faktur['no_faktur'];
        } else {
            $data['page_title'] = 'KARISMA — Edit Qty & Total Faktur ' . $faktur['no_faktur'];
        }

        $data['user']       = $this->_getUser();
        $data['faktur']     = $faktur;
        $data['details']    = $details;
        $data['mode']       = $mode;

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

        $id_detail_arr   = $this->input->post('id_detail') ?: [];
        $qty_arr         = $this->input->post('qty') ?: [];
        $total_harga_arr = $this->input->post('total_harga') ?: [];
        $mode            = $this->input->post('mode') ?: 'all';
        $catatan         = $this->input->post('catatan_revisi');
        $user            = $this->_getUser();

        $this->db->trans_start();

        foreach ($id_detail_arr as $i => $id_detail) {
            $detail = $this->db->get_where('tbso_faktur_detail', ['id' => $id_detail, 'id_faktur' => $id_faktur])->row_array();
            
            if ($detail) {
                $new_qty = isset($qty_arr[$i]) ? (float)str_replace(',', '', $qty_arr[$i]) : (float)$detail['qty'];
                $hrg_satuan = (float)$detail['hrg_satuan'];
                $disc       = (float)$detail['disc'];
                $pajak      = (float)$detail['pajak'];

                // Hitung subtotal
                $sub_before = $new_qty * $hrg_satuan;
                $disc_val   = $sub_before * ($disc / 100);
                $sub_after  = $sub_before - $disc_val;

                if ($mode === 'qty') {
                    // Mode Qty: total_harga dihitung otomatis dari Qty
                    $pajak_val  = $sub_after * ($pajak / 100);
                    $final_total_harga = $sub_after + $pajak_val;
                } else {
                    // Mode Harga atau All: gunakan total_harga diinput jika ada
                    if (isset($total_harga_arr[$i]) && $total_harga_arr[$i] !== '') {
                        $final_total_harga = (float)str_replace([',', ' '], '', $total_harga_arr[$i]);
                    } else {
                        $pajak_val  = $sub_after * ($pajak / 100);
                        $final_total_harga = $sub_after + $pajak_val;
                    }
                }

                $this->db->where('id', $id_detail);
                $this->db->update('tbso_faktur_detail', [
                    'qty'                  => $new_qty,
                    'subtotal_before_disc' => $sub_before,
                    'subtotal_after_disc'  => $sub_after,
                    'total_harga'          => $final_total_harga,
                ]);
            }
        }

        // Sinkronkan Jurnal Penjualan (tbso_faktur_jurnal & tbkeu_jurnal / jurnal_penjualan & jurnal_laporan)
        $this->_syncFakturJurnal($id_faktur, $faktur);

        // Catat Log ke tbso_faktur_log
        $log_label = ($mode === 'qty') ? 'Revisi Qty Faktur' : (($mode === 'harga') ? 'Revisi Total Harga Faktur' : 'Revisi Qty & Total Harga Faktur');
        $aksi_code = ($mode === 'qty') ? 'EDIT_QTY' : (($mode === 'harga') ? 'EDIT_HARGA' : 'EDIT_QTY_HARGA');

        $this->db->insert('tbso_faktur_log', [
            'no_so'          => $faktur['no_so'],
            'no_faktur'      => $faktur['no_faktur'],
            'id_faktur'      => $faktur['id_faktur'],
            'aksi'           => $aksi_code,
            'keterangan'     => "{$log_label}. Catatan: " . $catatan,
            'detail_produk'  => null,
            'dilakukan_oleh' => $user['nama'] . ' (' . $user['username'] . ')',
            'ip_address'     => $this->input->ip_address(),
            'created_at'     => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal mengupdate faktur.');
        } else {
            $this->session->set_flashdata('success', "Faktur <strong>{$faktur['no_faktur']}</strong> dan jurnal terkait berhasil diupdate.");
        }

        redirect('faktur_penjualan');
    }

    private function _syncFakturJurnal($id_faktur, $faktur)
    {
        $no_faktur = $faktur['no_faktur'];

        // 1. Hitung total baru dari detail faktur
        $sum_detail = $this->db->query(
            "SELECT COALESCE(SUM(total_harga), 0) AS grand_total,
                    COALESCE(MAX(pajak), 0) AS tax_rate
             FROM tbso_faktur_detail
             WHERE id_faktur = ?",
            [(int)$id_faktur]
        )->row_array();

        $grand_total = (float)($sum_detail['grand_total'] ?? 0);
        $tax_rate    = (float)($sum_detail['tax_rate'] ?? 0);
        $div_factor  = ($tax_rate > 0) ? (1 + ($tax_rate / 100)) : 1;

        $jurnal_piutang    = round($grand_total, 2);
        $jurnal_penjualan  = round($jurnal_piutang / $div_factor, 2);
        $jurnal_ppn_keluar = round($jurnal_piutang - $jurnal_penjualan, 2);

        // 2. Update atau Insert pada tabel tbso_faktur_jurnal
        if ($this->db->table_exists('tbso_faktur_jurnal')) {
            $existing_fj = $this->db->where('id_faktur', $id_faktur)
                                    ->or_where('no_faktur', $no_faktur)
                                    ->get('tbso_faktur_jurnal')
                                    ->row_array();
            if ($existing_fj) {
                $this->db->where('id', $existing_fj['id']);
                $this->db->update('tbso_faktur_jurnal', [
                    'piutang_dagang' => $jurnal_piutang,
                    'penjualan'      => $jurnal_penjualan,
                    'ppn_keluar'     => $jurnal_ppn_keluar,
                ]);
            } else {
                $this->db->insert('tbso_faktur_jurnal', [
                    'id_faktur'      => $id_faktur,
                    'no_faktur'      => $no_faktur,
                    'piutang_dagang' => $jurnal_piutang,
                    'penjualan'      => $jurnal_penjualan,
                    'ppn_keluar'     => $jurnal_ppn_keluar,
                    'created_at'     => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 3. Sync Jurnal Keuangan (tbkeu_jurnal & tbkeu_jurnal_detail)
        if ($this->db->table_exists('tbkeu_jurnal') && $this->db->table_exists('tbkeu_jurnal_detail')) {
            $this->load->library('Accounting_source_service');
            $current_user_id = (int)($this->session->userdata('id_karyawan') ?: $this->session->userdata('id') ?: 0);
            
            // Post / Update via Accounting Source Service
            $this->accounting_source_service->post_sales_invoice(
                $no_faktur,
                '',
                $current_user_id ?: null,
                true
            );

            // Update langsung tbkeu_jurnal & tbkeu_jurnal_detail jika sudah ada row jurnal terposting
            $jurnal_header = $this->db->group_start()
                ->where('source_id', $no_faktur)
                ->or_where('source_no', $no_faktur)
                ->group_end()
                ->where('source_module', 'SALES')
                ->get('tbkeu_jurnal')
                ->result_array();

            foreach ($jurnal_header as $j) {
                $this->db->where('id_jurnal', $j['id_jurnal']);
                $this->db->update('tbkeu_jurnal', [
                    'total_debit'  => $jurnal_piutang,
                    'total_kredit' => $jurnal_piutang,
                    'updated_at'   => date('Y-m-d H:i:s')
                ]);

                // Update detail baris jurnal (Debit: Piutang Usaha, Kredit: Penjualan & PPN)
                $details = $this->db->get_where('tbkeu_jurnal_detail', ['id_jurnal' => $j['id_jurnal']])->result_array();
                foreach ($details as $d) {
                    if ((float)$d['debit'] > 0) {
                        // Sisi Debit (Piutang)
                        $this->db->where('id_detail', $d['id_detail']);
                        $this->db->update('tbkeu_jurnal_detail', ['debit' => $jurnal_piutang]);
                    } elseif ((float)$d['kredit'] > 0) {
                        // Sisi Kredit (Penjualan / PPN)
                        $akun = $this->db->get_where('tbkeu_akun', ['id_akun' => $d['id_akun']])->row_array();
                        if ($akun && (stripos($akun['nama_akun'], 'PPN') !== false || stripos($akun['nama_akun'], 'Pajak') !== false)) {
                            $this->db->where('id_detail', $d['id_detail']);
                            $this->db->update('tbkeu_jurnal_detail', ['kredit' => $jurnal_ppn_keluar]);
                        } else {
                            $this->db->where('id_detail', $d['id_detail']);
                            $this->db->update('tbkeu_jurnal_detail', ['kredit' => $jurnal_penjualan]);
                        }
                    }
                }
            }
        }
    }

    public function activity_log()
    {
        if (!$this->_isAdmPnj()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
            redirect('dashboard');
            return;
        }

        $filter = [
            'date1'  => $this->input->get('date1') ?: date('Y-m-01'),
            'date2'  => $this->input->get('date2') ?: date('Y-m-t'),
            'search' => $this->input->get('search') ?: ''
        ];

        $this->db->select('log.*, fp.customer_name as fp_customer_name, c.nama_customer as master_customer_name');
        $this->db->from('tbso_faktur_log log');
        $this->db->join('tbso_faktur_penjualan fp', 'fp.id_faktur = log.id_faktur OR fp.no_faktur = log.no_faktur', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = fp.kd_customer', 'left');
        
        // Filter khusus log aktivitas edit Qty dan Edit Harga Admin Penjualan
        $this->db->where_in('log.aksi', ['EDIT_QTY', 'EDIT_HARGA', 'EDIT_QTY_HARGA']);

        if (!empty($filter['date1'])) {
            $this->db->where('DATE(log.created_at) >=', $filter['date1']);
        }
        if (!empty($filter['date2'])) {
            $this->db->where('DATE(log.created_at) <=', $filter['date2']);
        }
        if (!empty($filter['search'])) {
            $this->db->group_start();
            $this->db->like('log.no_faktur', $filter['search']);
            $this->db->or_like('log.dilakukan_oleh', $filter['search']);
            $this->db->or_like('log.keterangan', $filter['search']);
            $this->db->or_like('fp.customer_name', $filter['search']);
            $this->db->or_like('c.nama_customer', $filter['search']);
            $this->db->group_end();
        }
        
        $this->db->order_by('log.id', 'DESC');
        $logs = $this->db->get()->result_array();

        $data['page_title'] = 'KARISMA — Activity Log Edit Faktur (Admin Penjualan)';
        $data['user']       = $this->_getUser();
        $data['logs']       = $logs;
        $data['filter']     = $filter;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_penjualan_activity_log.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
