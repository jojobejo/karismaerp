<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_BundlingLogistik
 * Modul Logistik untuk:
 * 1. Monitoring Request Bundling dari Purchasing
 * 2. Cek Ketersediaan Stok Fisik Komponen (Gudang Induk vs Bundling)
 * 3. Mutasi Bahan dari Gudang Induk ke Gudang Bundling
 * 4. Realisasi Pembuatan Paket Bundling (Assembly) dengan Partial Fulfillment
 * 5. Pembongkaran Paket Bundling (Disassembly / Unbundling) untuk Penjualan Eceran
 * 6. Histori & Audit Trail Persediaan
 */
class C_BundlingLogistik extends CI_Controller
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
                        'status'       => false,
                        'auth_timeout' => true,
                        'msg'          => 'Sesi login Anda telah berakhir. Silakan login kembali.'
                    ]));
                return;
            }
            redirect('auth');
        }
        $this->load->model(['M_Bundling', 'M_Ics', 'M_Stock', 'M_PenyesuaianBarang']);
        $this->load->helper(['url', 'form']);
        $this->M_Bundling->ensure_bundling_schema();
    }

    /**
     * Halaman Utama Logistik: Monitoring Request Bundling
     */
    public function index()
    {
        $filters = [
            'status'    => $this->input->get('status') ?: 'SEMUA',
            'search'    => $this->input->get('search') ?: '',
            'date_from' => $this->input->get('date_from') ?: '',
            'date_to'   => $this->input->get('date_to') ?: ''
        ];

        $data['page_title'] = 'Monitoring & Realisasi Paket Bundling - Logistik';
        $data['filters']    = $filters;
        $data['requests']   = $this->M_Bundling->get_requests($filters);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bundling/monitoring_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Detail Request & Ketersediaan Stok Bahan
     */
    public function detail($id_request)
    {
        $req = $this->M_Bundling->get_request_by_id($id_request);
        if (!$req) {
            show_404();
        }

        $data['page_title']   = 'Realisasi Paket Bundling #' . $req['no_request'];
        $data['request']      = $req;
        $data['stock_status'] = $this->M_Bundling->get_component_stock_status($id_request);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bundling/monitoring_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Form Mutasi Bahan dari Gudang Induk ke Gudang Bundling
     */
    public function mutasi_bahan($id_request)
    {
        $req = $this->M_Bundling->get_request_by_id($id_request);
        if (!$req) {
            show_404();
        }

        $stockStatus = $this->M_Bundling->get_component_stock_status($id_request);

        // Ambil batch lot yang tersedia di gudang asal
        foreach ($stockStatus as &$stk) {
            $stk['batches'] = $this->M_Bundling->get_stock_batches_by_gudang($stk['kode_barang'], $req['id_gudang_asal']);
        }
        unset($stk);

        $sisaRequest = max(0, (float)$req['qty_request'] - (float)$req['qty_realisasi']);

        $data['page_title']   = 'Mutasi Bahan ke Gudang Bundling - Ref #' . $req['no_request'];
        $data['request']      = $req;
        $data['sisa_request'] = $sisaRequest;
        $data['stock_status'] = $stockStatus;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bundling/mutasi_bahan.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Eksekusi Mutasi Bahan
     */
    public function execute_mutasi()
    {
        $user = $this->session->userdata('nik') ?: $this->session->userdata('username') ?: 'LOGISTIK';
        $post = $this->input->post();

        $idRequest = (int)($post['id_request'] ?? 0);
        $itemsRaw = $post['items'] ?? [];

        if (!$idRequest || empty($itemsRaw)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Data mutasi tidak valid']));
            return;
        }

        $items = [];
        foreach ($itemsRaw as $item) {
            $qty = (float)($item['qty_mutasi'] ?? 0);
            if ($qty > 0 && !empty($item['kode_barang'])) {
                $items[] = [
                    'kode_barang'  => trim($item['kode_barang']),
                    'nama_barang'  => trim($item['nama_barang'] ?? ''),
                    'no_lot'       => trim($item['no_lot'] ?? '-'),
                    'expired_date' => !empty($item['expired_date']) ? $item['expired_date'] : null,
                    'qty_mutasi'   => $qty
                ];
            }
        }

        if (empty($items)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Pilih kuantitas dan lot barang yang akan dimutasi']));
            return;
        }

        $res = $this->M_Bundling->execute_mutasi_bahan_bundling($idRequest, $items, $user);
        $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    /**
     * Form Pembuatan Paket Bundling (Assembly)
     */
    public function form_assembly($id_request)
    {
        $req = $this->M_Bundling->get_request_by_id($id_request);
        if (!$req) {
            show_404();
        }

        $sisaRequest = max(0, (float)$req['qty_request'] - (float)$req['qty_realisasi']);
        if ($sisaRequest <= 0) {
            $this->session->set_flashdata('error', 'Request ini sudah terealisasi penuh.');
            redirect('logistik/bundling/detail/' . $id_request);
            return;
        }

        // Ambil batch lot komponen yang tersedia di Gudang Bundling
        $components = $req['details'];
        foreach ($components as &$comp) {
            $comp['batches'] = $this->M_Bundling->get_stock_batches_by_gudang($comp['kode_barang_komponen'], $req['id_gudang_tujuan']);
            $comp['stok_bundling'] = $this->M_Bundling->get_stock_available_by_gudang($comp['kode_barang_komponen'], $req['id_gudang_tujuan']);
        }
        unset($comp);

        $data['page_title']   = 'Pembuatan Paket Bundling (Assembly) #' . $req['no_request'];
        $data['request']      = $req;
        $data['sisa_request'] = $sisaRequest;
        $data['components']   = $components;
        $data['no_assembly']  = $this->M_Bundling->generate_assembly_number();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bundling/assembly_create.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Simpan Realisasi Pembuatan Paket (Assembly)
     */
    public function save_assembly()
    {
        $user = $this->session->userdata('nik') ?: $this->session->userdata('username') ?: 'LOGISTIK';
        $post = $this->input->post();

        $qtyAssembly = (float)($post['qty_assembly'] ?? 0);
        if ($qtyAssembly <= 0) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Kuantitas pembuatan paket harus lebih dari 0']));
            return;
        }

        $header = [
            'id_request'         => !empty($post['id_request']) ? (int)$post['id_request'] : null,
            'tanggal'            => !empty($post['tanggal']) ? $post['tanggal'] : date('Y-m-d'),
            'kode_paket'         => trim($post['kode_paket']),
            'nama_paket'         => trim($post['nama_paket']),
            'id_gudang'          => !empty($post['id_gudang']) ? (int)$post['id_gudang'] : 12,
            'qty_assembly'       => $qtyAssembly,
            'satuan'             => $post['satuan'] ?: 'Box',
            'no_lot_paket'       => !empty($post['no_lot_paket']) ? trim($post['no_lot_paket']) : '',
            'expired_date_paket' => !empty($post['expired_date_paket']) ? $post['expired_date_paket'] : null,
            'keterangan'         => $post['keterangan'] ?? ''
        ];

        $rawComponents = $post['komponen'] ?? [];
        $components = [];
        if (is_array($rawComponents)) {
            foreach ($rawComponents as $comp) {
                $qtyPakai = (float)($comp['qty_digunakan'] ?? 0);
                if ($qtyPakai > 0 && !empty($comp['kode_barang'])) {
                    $components[] = [
                        'kode_barang'   => trim($comp['kode_barang']),
                        'nama_barang'   => trim($comp['nama_barang'] ?? ''),
                        'no_lot'        => trim($comp['no_lot'] ?? '-'),
                        'expired_date'  => !empty($comp['expired_date']) ? $comp['expired_date'] : null,
                        'qty_digunakan' => $qtyPakai,
                        'satuan'        => $comp['satuan'] ?? 'Pcs'
                    ];
                }
            }
        }

        if (empty($components)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Komponen yang digunakan belum dipilih']));
            return;
        }

        $res = $this->M_Bundling->process_assembly($header, $components, $user);
        $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    // =========================================================================
    // PEMBONGKARAN PAKET (DISASSEMBLY / UNBUNDLING UNTUK ECERAN)
    // =========================================================================

    /**
     * Halaman Form Pembongkaran Paket Bundling (Disassembly)
     */
    public function disassembly()
    {
        $data['page_title']      = 'Pembongkaran Paket Bundling (Unbundling Eceran)';
        $data['no_disassembly']  = $this->M_Bundling->generate_disassembly_number();
        $data['gudangs']         = $this->db->where('is_active', 1)->get('tb_gudang')->result_array();
        $data['formulas']        = $this->M_Bundling->get_all_formulas();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bundling/disassembly_create.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Eksekusi Pembongkaran Paket Bundling
     */
    public function save_disassembly()
    {
        $user = $this->session->userdata('nik') ?: $this->session->userdata('username') ?: 'LOGISTIK';
        $post = $this->input->post();

        $qtyDsb = (float)($post['qty_disassembly'] ?? 0);
        if ($qtyDsb <= 0) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Qty paket yang dibongkar harus lebih dari 0']));
            return;
        }

        if (empty($post['alasan'])) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Alasan pembongkaran wajib diisi']));
            return;
        }

        $header = [
            'tanggal'            => !empty($post['tanggal']) ? $post['tanggal'] : date('Y-m-d'),
            'kode_paket'         => trim($post['kode_paket']),
            'nama_paket'         => trim($post['nama_paket']),
            'id_gudang'          => !empty($post['id_gudang']) ? (int)$post['id_gudang'] : 12,
            'qty_disassembly'    => $qtyDsb,
            'satuan'             => $post['satuan'] ?: 'Box',
            'no_lot_paket'       => !empty($post['no_lot_paket']) ? trim($post['no_lot_paket']) : '-',
            'expired_date_paket' => !empty($post['expired_date_paket']) ? $post['expired_date_paket'] : null,
            'alasan'             => trim($post['alasan'])
        ];

        $rawComponents = $post['komponen'] ?? [];
        $components = [];
        if (is_array($rawComponents)) {
            foreach ($rawComponents as $comp) {
                $qtyKembali = (float)($comp['qty_kembali'] ?? 0);
                if ($qtyKembali > 0 && !empty($comp['kode_barang'])) {
                    $components[] = [
                        'kode_barang'  => trim($comp['kode_barang']),
                        'nama_barang'  => trim($comp['nama_barang'] ?? ''),
                        'no_lot'       => trim($comp['no_lot'] ?? '-'),
                        'expired_date' => !empty($comp['expired_date']) ? $comp['expired_date'] : null,
                        'qty_kembali'  => $qtyKembali,
                        'satuan'       => $comp['satuan'] ?? 'Pcs'
                    ];
                }
            }
        }

        if (empty($components)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Komponen hasil pembongkaran belum ditentukan']));
            return;
        }

        $res = $this->M_Bundling->process_disassembly($header, $components, $user);
        $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    // =========================================================================
    // HISTORI & AUDIT TRAIL
    // =========================================================================

    public function history()
    {
        $filters = [
            'search'    => $this->input->get('search') ?: '',
            'date_from' => $this->input->get('date_from') ?: '',
            'date_to'   => $this->input->get('date_to') ?: ''
        ];

        $data['page_title']    = 'Riwayat Perakitan & Pembongkaran Bundling';
        $data['filters']       = $filters;
        $data['assemblies']    = $this->M_Bundling->get_assembly_history($filters);
        $data['disassemblies'] = $this->M_Bundling->get_disassembly_history($filters);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bundling/history_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_assembly($id)
    {
        $row = $this->M_Bundling->get_assembly_by_id($id);
        if (!$row) show_404();

        $data['page_title'] = 'Bukti Perakitan Paket #' . $row['no_assembly'];
        $data['assembly']   = $row;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bundling/assembly_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_disassembly($id)
    {
        $row = $this->M_Bundling->get_disassembly_by_id($id);
        if (!$row) show_404();

        $data['page_title']    = 'Bukti Pembongkaran Paket #' . $row['no_disassembly'];
        $data['disassembly']   = $row;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bundling/disassembly_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // =========================================================================
    // AJAX LOOKUPS
    // =========================================================================

    public function ajax_get_paket_stock_batch()
    {
        $kdPaket = $this->input->get('kode_paket');
        $gudangId = (int)($this->input->get('id_gudang') ?: 12);

        $batches = $this->M_Bundling->get_stock_batches_by_gudang($kdPaket, $gudangId);
        $available = $this->M_Bundling->get_stock_available_by_gudang($kdPaket, $gudangId);

        $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'status'    => true,
                'available' => $available,
                'batches'   => $batches
            ]));
    }
}
