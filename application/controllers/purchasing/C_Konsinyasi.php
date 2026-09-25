<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Konsinyasi
 * Mengelola alur Penyelesaian Konsinyasi (Consignment Settlement):
 * - Rekonsiliasi barang konsinyasi yang telah terjual ke customer
 * - Input harga beli dan invoice resmi dari supplier
 * - Posting jurnal otomatis ke Utang Konsinyasi & HPP
 */
class C_Konsinyasi extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in') && !$this->session->userdata('username') && !$this->session->userdata('is_login')) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'  => false,
                        'message' => 'Sesi login Anda telah berakhir. Silakan login kembali.'
                    ]));
                return;
            }
            redirect('auth');
        }

        $this->load->model(['M_Konsinyasi']);
        $this->load->helper(['url', 'form']);
    }

    /**
     * Halaman Utama Modul Barang Konsinyasi (Penerimaan & Penyelesaian)
     */
    public function index()
    {
        // Jalankan sinkronisasi background untuk mendeteksi barang konsinyasi terjual terbaru
        $this->M_Konsinyasi->sync_pending_consignment_sales();

        $activeTab = $this->input->get('tab') ?: 'penyelesaian';

        $filters = [
            'status'     => $this->input->get('status') ?: 'PENDING',
            'kd_suplier' => $this->input->get('kd_suplier') ?: 'SEMUA',
            'date_from'  => $this->input->get('date_from') ?: '',
            'date_to'    => $this->input->get('date_to') ?: '',
            'search'     => $this->input->get('search') ?: ''
        ];

        $data['page_title']  = 'Barang Konsinyasi - Purchasing';
        $data['active_tab']  = $activeTab;
        $data['filters']     = $filters;
        $data['stats']       = $this->M_Konsinyasi->get_summary_stats();
        $data['suppliers']   = $this->M_Konsinyasi->get_suppliers_list();
        $data['settlements'] = $this->M_Konsinyasi->get_settlement_list($filters);

        // Data Penerimaan Barang Konsinyasi
        $filtersPenerimaan = [
            'kd_suplier' => $this->input->get('p_kd_suplier') ?: 'SEMUA',
            'date_from'  => $this->input->get('p_date_from') ?: '',
            'date_to'    => $this->input->get('p_date_to') ?: '',
            'search'     => $this->input->get('p_search') ?: ''
        ];
        $data['penerimaan_list'] = $this->M_Konsinyasi->get_penerimaan_list($filtersPenerimaan);
        $data['filters_p']       = $filtersPenerimaan;

        // Master supplier untuk dropdown modal penerimaan konsinyasi
        $data['all_suppliers']   = $this->db->select('kd_suplier, nama_suplier')->order_by('nama_suplier', 'ASC')->get('tbpo_suplier')->result_array();

        // Master barang untuk dropdown modal penerimaan konsinyasi
        $data['all_barangs']     = $this->db->select('kode_barang, nama_barang, satuan, kd_suplier')->where('status_hapus', '0')->or_where('status_hapus IS NULL', null, false)->order_by('nama_barang', 'ASC')->limit(1000)->get('tbpo_barang')->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/purchasing/konsinyasi/settlement_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * AJAX: Ambil detail settlement untuk modal form input harga
     */
    public function ajax_detail()
    {
        $idSettlement = (int) $this->input->get('id_settlement');
        $item = $this->M_Konsinyasi->get_settlement_by_id($idSettlement);

        if (!$item) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Data tidak ditemukan.']));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => true, 'data' => $item]));
    }

    /**
     * AJAX: Posting penyelesaian konsinyasi (input invoice & harga beli supplier)
     */
    public function ajax_post_settlement()
    {
        $idSettlement = (int) $this->input->post('id_settlement');
        $payload = [
            'tipe_pajak'           => trim((string) $this->input->post('tipe_pajak', TRUE)),
            'hrg_satuan_input'     => (float) $this->input->post('hrg_satuan_input'),
            'hrg_beli_satuan'      => (float) $this->input->post('hrg_satuan_input'),
            'no_invoice_supplier'  => trim((string) $this->input->post('no_invoice_supplier', TRUE)),
            'tgl_invoice_supplier' => trim((string) $this->input->post('tgl_invoice_supplier', TRUE)),
            'ppn_persen'           => (float) $this->input->post('ppn_persen'),
            'catatan'              => trim((string) $this->input->post('catatan', TRUE))
        ];

        $userId = (int) ($this->session->userdata('id') ?: $this->session->userdata('user_id') ?: 1);
        $userName = (string) ($this->session->userdata('nama') ?: $this->session->userdata('username') ?: 'PURCHASING');

        $result = $this->M_Konsinyasi->process_settlement($idSettlement, $payload, $userId, $userName);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * AJAX: Trigger sinkronisasi manual penjualan konsinyasi
     */
    public function ajax_sync()
    {
        $count = $this->M_Konsinyasi->sync_pending_consignment_sales();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => true,
                'message' => 'Sinkronisasi berhasil. ' . $count . ' data penjualan konsinyasi baru ditemukan.',
                'count'   => $count
            ]));
    }

    /**
     * AJAX: Simpan Transaksi Penerimaan Barang Konsinyasi Baru
     */
    public function ajax_save_penerimaan()
    {
        $header = [
            'kd_suplier'      => trim((string) $this->input->post('kd_suplier', TRUE)),
            'nama_suplier'    => trim((string) $this->input->post('nama_suplier', TRUE)),
            'tanggal_masuk'   => trim((string) $this->input->post('tanggal_masuk', TRUE)) ?: date('Y-m-d'),
            'gudang_id'       => (int) ($this->input->post('gudang_id') ?: 13),
            'no_surat_jalan'  => trim((string) $this->input->post('no_surat_jalan', TRUE)),
            'tgl_surat_jalan' => trim((string) $this->input->post('tgl_surat_jalan', TRUE)) ?: null,
            'keterangan'      => trim((string) $this->input->post('keterangan', TRUE))
        ];

        $itemsRaw = $this->input->post('items');
        $items = [];
        if (is_array($itemsRaw)) {
            foreach ($itemsRaw as $it) {
                if (!empty($it['kd_barang']) && (float)($it['qty'] ?? 0) > 0) {
                    $items[] = [
                        'kd_barang'    => trim((string)$it['kd_barang']),
                        'nama_barang'  => trim((string)($it['nama_barang'] ?? '')),
                        'satuan'       => trim((string)($it['satuan'] ?? 'PCS')),
                        'qty'          => (float)$it['qty'],
                        'no_lot'       => trim((string)($it['no_lot'] ?? '')),
                        'expired_date' => !empty($it['expired_date']) ? $it['expired_date'] : null,
                        'keterangan'   => trim((string)($it['keterangan'] ?? ''))
                    ];
                }
            }
        }

        if (empty($header['kd_suplier'])) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Supplier wajib dipilih.'
            ]));
            return;
        }

        if (empty($items)) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Minimal 1 item barang harus diinput dengan jumlah > 0.'
            ]));
            return;
        }

        $userName = (string) ($this->session->userdata('nama') ?: $this->session->userdata('username') ?: 'PURCHASING');
        $result = $this->M_Konsinyasi->create_penerimaan_konsinyasi($header, $items, $userName);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * AJAX: Detail Dokumen Penerimaan Konsinyasi
     */
    public function ajax_detail_penerimaan()
    {
        $idMasuk = (int) $this->input->get('id_masuk');
        $data = $this->M_Konsinyasi->get_penerimaan_by_id($idMasuk);

        if (!$data) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Dokumen penerimaan tidak ditemukan.'
            ]));
            return;
        }

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => true,
            'data'   => $data
        ]));
    }
}

