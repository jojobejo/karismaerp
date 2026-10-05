<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Konsinyasi
 * Mengelola alur Penyelesaian Konsinyasi (Consignment Settlement):
 * - Rekonsiliasi barang konsinyasi yang telah terjual ke customer
 * - Input harga beli dan invoice resmi dari supplier
 * - Posting jurnal pembelian otomatis ke Persediaan, PPN Masukan, dan Hutang Usaha
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
     * Halaman Utama Modul Barang Konsinyasi (Penerimaan, Tracking Lokasi & Penyelesaian)
     */
    public function index()
    {
        // Jalankan sinkronisasi background untuk mendeteksi barang konsinyasi terjual terbaru
        $this->M_Konsinyasi->sync_pending_consignment_sales();

        $filters = [
            'status'     => $this->input->get('status') ?: 'LAKU',
            'kd_suplier' => $this->input->get('kd_suplier') ?: 'SEMUA',
            'date_from'  => $this->input->get('date_from') ?: '',
            'date_to'    => $this->input->get('date_to') ?: '',
            'search'     => $this->input->get('search') ?: '',
            'tab'        => $this->input->get('tab') ?: 'tracking'
        ];

        $data['page_title']       = 'Tracking & Penyelesaian Barang Konsinyasi - Purchasing';
        $data['filters']          = $filters;
        $data['stats']            = $this->M_Konsinyasi->get_summary_stats();
        $data['tracking_summary'] = $this->M_Konsinyasi->get_tracking_summary();
        $data['tracking_barang']  = $this->M_Konsinyasi->get_tracking_barang_list($filters);
        $data['suppliers']        = $this->M_Konsinyasi->get_suppliers_list();
        $data['settlements']      = $this->M_Konsinyasi->get_settlement_list($filters);

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
            'qty_laku'             => (float) $this->input->post('qty_laku'),
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
     * AJAX: Ambil detail kios pemegang barang konsinyasi tertentu
     */
    public function ajax_tracking_kios()
    {
        $kdBarang = trim((string) $this->input->get('kd_barang'));
        if (empty($kdBarang)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Kode barang tidak valid.']));
            return;
        }

        $list = $this->M_Konsinyasi->get_tracking_kios_by_barang($kdBarang);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data'   => $list
            ]));
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
     * AJAX: Ambil detail jurnal akuntansi terkait transaksi settlement konsinyasi
     */
    public function ajax_view_journal()
    {
        $idSettlement = (int) $this->input->get('id_settlement');
        $settlement = $this->M_Konsinyasi->get_settlement_by_id($idSettlement);

        if (!$settlement) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Data settlement tidak ditemukan.']));
            return;
        }

        // Cari jurnal dari id_jurnal_pembelian atau dari source_id / source_no
        $jurnal = null;
        if (!empty($settlement['id_jurnal_pembelian'])) {
            $jurnal = $this->db
                ->where('id_jurnal', (int) $settlement['id_jurnal_pembelian'])
                ->get('tbkeu_jurnal')
                ->row_array();
        }

        if (!$jurnal) {
            $jurnal = $this->db
                ->group_start()
                    ->where('source_id', (string) $idSettlement)
                    ->where('source_type', 'CONSIGNMENT_SETTLEMENT')
                ->group_end()
                ->or_where('source_no', $settlement['no_settlement'])
                ->or_where('idempotency_key', 'CONSIGNMENT_SETTLEMENT-' . $idSettlement)
                ->order_by('id_jurnal', 'DESC')
                ->get('tbkeu_jurnal')
                ->row_array();
        }

        if (!$jurnal) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Jurnal akuntansi belum ditemukan untuk transaksi ini (mungkin belum diinput tagihan atau belum selesai diposting).'
                ]));
            return;
        }

        // Ambil detail baris debit dan kredit
        $details = $this->db
            ->select('d.*, a.kode_akun, a.nama_akun')
            ->from('tbkeu_jurnal_detail d')
            ->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left')
            ->where('d.id_jurnal', (int) $jurnal['id_jurnal'])
            ->order_by('d.nomor_baris', 'ASC')
            ->get()
            ->result_array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'     => true,
                'settlement' => $settlement,
                'header'     => $jurnal,
                'details'    => $details
            ]));
    }

    /**
     * Cetak Faktur Laporan Realisasi Penjualan Konsinyasi untuk dilaporkan ke Supplier
     */
    public function print_faktur($idSettlement)
    {
        $idSettlement = (int) $idSettlement;
        $settlement = $this->M_Konsinyasi->get_settlement_by_id($idSettlement);
        if (!$settlement) {
            show_404();
            return;
        }

        $fakturDetail = null;
        if (!empty($settlement['id_faktur'])) {
            $fakturDetail = $this->db
                ->where('id_faktur', (int) $settlement['id_faktur'])
                ->where('kd_barang', $settlement['kd_barang'])
                ->get('tbso_faktur_detail')
                ->row_array();
        }

        $fakturKonsinyasi = null;
        if (!empty($settlement['id_pembayaran'])) {
            $fakturKonsinyasi = $this->M_Konsinyasi->get_faktur_konsinyasi_by_payment($settlement['id_pembayaran']);
        }
        if (!$fakturKonsinyasi && !empty($settlement['id_settlement'])) {
            $fakturKonsinyasi = $this->db->get_where('tb_konsinyasi_faktur', ['id_settlement' => (int) $settlement['id_settlement']])->row_array();
        }
        if (!$fakturKonsinyasi && !empty($settlement['no_faktur'])) {
            $fakturKonsinyasi = $this->M_Konsinyasi->get_faktur_konsinyasi_by_no($settlement['no_faktur']);
        }

        $noFakturKonsinyasi = !empty($fakturKonsinyasi['no_faktur_konsinyasi'])
            ? $fakturKonsinyasi['no_faktur_konsinyasi']
            : ($settlement['no_faktur'] ?? $settlement['no_settlement']);

        $settlement['no_faktur_konsinyasi'] = $noFakturKonsinyasi;
        $data['page_title']           = 'Faktur Realisasi Penjualan Konsinyasi - ' . $noFakturKonsinyasi;
        $data['no_faktur_konsinyasi'] = $noFakturKonsinyasi;
        $data['faktur_konsinyasi']    = $fakturKonsinyasi;
        $data['settlement']           = $settlement;
        $data['faktur_detail']        = $fakturDetail;

        $this->load->view('content/purchasing/konsinyasi/faktur_konsinyasi_print.php', $data);
    }
}


