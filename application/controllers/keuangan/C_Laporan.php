<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Laporan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url']);
        $this->_ensure_access();
    }

    private function _ensure_access()
    {
        if (!$this->session->userdata('logged_in')) {
            if ($this->input->is_ajax_request() || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
                    ]));
                $this->output->_display();
                exit;
            }
            redirect('Auth');
        }
    }

    // ====================================================
    // LANDING — Laporan utama (pilih kategori)
    // ====================================================
    public function index()
    {
        $data['page_title'] = 'KARISMA - LAPORAN';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Keuangan
    // ====================================================
    public function keuangan()
    {
        $data['page_title'] = 'KARISMA - LAPORAN KEUANGAN';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Penjualan & Piutang
    // ====================================================
    public function penjualan()
    {
        $data['page_title'] = 'KARISMA - LAPORAN PENJUALAN & PIUTANG';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Pembelian & Hutang
    // ====================================================
    public function pembelian()
    {
        $data['page_title'] = 'KARISMA - LAPORAN PEMBELIAN & HUTANG';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_pembelian_hutang/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Barang / Inventori
    // ====================================================
    public function barang()
    {
        $data['page_title'] = 'KARISMA - LAPORAN BARANG';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_barang/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Lainnya
    // ====================================================
    public function lainnya()
    {
        $data['page_title'] = 'KARISMA - LAPORAN LAINNYA';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_lainnya/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Halaman Report Spesifik
    // ====================================================
    public function jurnal_penjualan_report()
    {
        $data['page_title'] = 'KARISMA - LAPORAN JURNAL PENJUALAN';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/jurnal_penjualan_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function kartu_stock_gudang_report()
    {
        $data['page_title'] = 'KARISMA - KARTU STOK PER GUDANG';
        
        $data['warehouses'] = $this->db->get_where('tb_gudang', ['is_active' => 1])->result_array();
        
        $data['groups'] = $this->db->select('NOINDEX as id, DESKRIPSI as kelompok')
                                   ->from('tbkeu_kelompok_dagang')
                                   ->where('NOINDEX >', 0)
                                   ->get()->result_array();
                                   
        $data['products'] = $this->db->select('kode_barang, nama_barang')
                                     ->from('tbpo_barang')
                                     ->where('is_active', 'T')
                                     ->get()->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_barang/kartu_stock_gudang.php', $data);
        $this->load->view('partial/main/footergdg.php');
        }

    // ====================================================
    // Laporan Jurnal Transaksi
    // ====================================================
    public function jurnal_transaksi_report()
    {
        $data['page_title'] = 'KARISMA - LAPORAN JURNAL TRANSAKSI';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/jurnal_transaksi.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function jurnal_transaksi_data()
    {
        $start_date = $this->input->post('start_date', true);
        $end_date   = $this->input->post('end_date', true);
        $this->db->select('j.tanggal_transaksi as tanggal, j.nomor_jurnal, a.kode_akun as kd_akun, a.nama_akun, d.debit, d.kredit, j.keterangan');
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_jurnal_detail d', 'j.id_jurnal = d.id_jurnal', 'left');
        $this->db->join('tbkeu_akun a', 'd.id_akun = a.id_akun', 'left');
        if ($start_date) {
            $this->db->where('j.tanggal_transaksi >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('j.tanggal_transaksi <=', $end_date);
        }
        $this->db->order_by('j.tanggal_transaksi', 'ASC');
        $data = $this->db->get()->result_array();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $data]));
    }


    public function kartu_stock_gudang_data()
    {
        $kd_barang = $this->input->post('kd_barang', true);
        $kelompok_barang = $this->input->post('kelompok_barang', true);
        $id_gudang = $this->input->post('id_gudang', true);
        $start_date = $this->input->post('start_date', true);
        $end_date = $this->input->post('end_date', true);

        // Ambil ID Gudang Konsinyasi, Gudang Titipan di Kios, dan Gudang Konsinyasi Lunas
        $wh_konsinyasi = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->group_start()
                ->where('id_gudang', 13)
                ->or_like('nama_gudang', 'Konsi')
            ->group_end()
            ->limit(1)->get()->row_array();
        $id_wh_konsinyasi = $wh_konsinyasi ? (int)$wh_konsinyasi['id_gudang'] : 13;

        $wh_titipan_kios = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Titipan di Kios')
            ->limit(1)->get()->row_array();
        $id_wh_titipan_kios = $wh_titipan_kios ? (int)$wh_titipan_kios['id_gudang'] : 14;

        $wh_konsinyasi_lunas = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Konsinyasi Lunas')
            ->limit(1)->get()->row_array();
        $id_wh_konsinyasi_lunas = $wh_konsinyasi_lunas ? (int)$wh_konsinyasi_lunas['id_gudang'] : 15;

        // Ambil master gudang aktif
        $warehouses_raw = $this->db->get_where('tb_gudang', ['is_active' => 1])->result_array();
        $warehouses_map = [];
        foreach ($warehouses_raw as $w) {
            $warehouses_map[(int)$w['id_gudang']] = $w['nama_gudang'];
        }

        $wh_target_id = ($id_gudang && $id_gudang !== 'all') ? (int)$id_gudang : null;

        // Master deskripsi kelompok barang
        $groups_raw = $this->db->get('tbkeu_kelompok_dagang')->result_array();
        $groups_map = [];
        foreach ($groups_raw as $g) {
            $groups_map[$g['NOINDEX']] = $g['DESKRIPSI'];
        }

        // Filter produk dan kelompok untuk klausul WHERE
        $prod_filter_clause = "";
        if ($kd_barang && $kd_barang !== 'all') {
            $prod_filter_clause .= " AND b.kode_barang = " . $this->db->escape($kd_barang);
        }
        if ($kelompok_barang && $kelompok_barang !== 'all') {
            $prod_filter_clause .= " AND b.kelompok_dagang = " . $this->db->escape($kelompok_barang);
        }

        // 1. Query LPB (Penerimaan Barang / Masuk)
        $sql_lpb = "SELECT 
                        ld.kd_barang,
                        l.gudang_id,
                        l.tgl_sj AS tanggal,
                        l.nomor_lpb AS referensi,
                        ld.qty_diterima AS qty,
                        ld.harga_satuan AS harga,
                        'IN' AS type,
                        'PJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        b.kelompok_dagang
                    FROM tb_lpb l
                    JOIN tb_lpb_detail ld ON l.id_lpb = ld.id_lpb
                    JOIN tbpo_barang b ON ld.kd_barang = b.kode_barang
                    WHERE l.status_lpb = 1 AND l.tgl_sj <= " . $this->db->escape($end_date) . "
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_lpb .= " AND l.gudang_id = {$wh_target_id}";
        }

        // 2. Query Retur Penjualan (Masuk)
        $sql_retur = "SELECT 
                        b.kode_barang AS kd_barang,
                        r.gudang_id,
                        r.tanggal_retur AS tanggal,
                        r.no_retur AS referensi,
                        rd.qty_retur AS qty,
                        0 AS harga,
                        'IN' AS type,
                        'RJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        b.kelompok_dagang
                    FROM tbrp_retur_penjualan_header r
                    JOIN tbrp_retur_penjualan_detail rd ON r.id_retur = rd.id_retur
                    JOIN tbpo_barang b ON (
                        (rd.kd_barang IS NOT NULL AND rd.kd_barang != '' AND rd.kd_barang = b.kode_barang) OR
                        (TRIM(LOWER(rd.nama_barang)) = TRIM(LOWER(b.nama_barang)))
                    )
                    WHERE r.status_retur NOT IN ('ditolak', 'batal') AND r.tanggal_retur <= " . $this->db->escape($end_date) . "
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_retur .= " AND r.gudang_id = {$wh_target_id}";
        }

        // 3. Query Faktur Penjualan (Keluar)
        $sql_faktur = "SELECT 
                        fd.kd_barang,
                        f.gudang_id,
                        f.tanggal_faktur AS tanggal,
                        f.no_faktur AS referensi,
                        fd.qty,
                        fd.hrg_satuan AS harga,
                        'OUT' AS type,
                        'SJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        b.kelompok_dagang
                    FROM tbso_faktur_penjualan f
                    JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                    JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                    WHERE f.status NOT IN ('draft', 'cancelled') AND f.tanggal_faktur <= " . $this->db->escape($end_date) . "
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_faktur .= " AND f.gudang_id = {$wh_target_id}";
        }

        $queries = [
            "($sql_lpb)",
            "($sql_retur)",
            "($sql_faktur)"
        ];

        // 4. Query Retur Pembelian (Keluar) jika tabel ada
        if ($this->db->table_exists('tb_retur_pembelian') && $this->db->table_exists('tb_retur_pembelian_detail')) {
            $sql_retur_beli = "SELECT 
                                rbd.kd_barang,
                                rb.gudang_id,
                                rb.tanggal_retur AS tanggal,
                                rb.no_retur_pembelian AS referensi,
                                rbd.qty_retur AS qty,
                                rbd.harga_satuan AS harga,
                                'OUT' AS type,
                                'RB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tb_retur_pembelian rb
                            JOIN tb_retur_pembelian_detail rbd ON rb.id_retur_pembelian = rbd.id_retur_pembelian
                            JOIN tbpo_barang b ON rbd.kd_barang = b.kode_barang
                            WHERE rb.status NOT IN ('DRAFT', 'REJECTED', 'BATAL') AND rb.tanggal_retur <= " . $this->db->escape($end_date) . "
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_retur_beli .= " AND rb.gudang_id = {$wh_target_id}";
            }
            $queries[] = "($sql_retur_beli)";
        }

        // 5. Khusus Konsinyasi Titipan di Kios
        if (!$wh_target_id || $wh_target_id == $id_wh_titipan_kios) {
            // IN: Faktur Penjualan yang dititipkan ke Kios bersumber dari Gudang Konsinyasi
            $sql_kios_in = "SELECT 
                                fd.kd_barang,
                                {$id_wh_titipan_kios} AS gudang_id,
                                f.tanggal_faktur AS tanggal,
                                CONCAT(f.no_faktur, ' (', COALESCE(f.customer_name, 'Kios'), ')') AS referensi,
                                fd.qty,
                                fd.hrg_satuan AS harga,
                                'IN' AS type,
                                'TITIP-IN ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tbso_faktur_penjualan f
                            JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                            JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                            WHERE f.status NOT IN ('draft', 'cancelled') 
                              AND f.gudang_id = {$id_wh_konsinyasi}
                              AND f.tanggal_faktur <= " . $this->db->escape($end_date) . "
                              {$prod_filter_clause}";
            $queries[] = "($sql_kios_in)";

            // OUT: Titipan di Kios saat dilunasi / dibeli ke supplier (status BILLED)
            if ($this->db->table_exists('tb_konsinyasi_settlement')) {
                $sql_kios_out = "SELECT 
                                    s.kd_barang,
                                    {$id_wh_titipan_kios} AS gudang_id,
                                    COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) AS tanggal,
                                    CONCAT(s.no_settlement, ' / Pelunasan ', COALESCE(s.customer_name, 'Kios')) AS referensi,
                                    s.qty_net AS qty,
                                    s.hrg_beli_satuan AS harga,
                                    'OUT' AS type,
                                    'TITIP-OUT ' AS ref_prefix,
                                    b.satuan,
                                    b.nama_barang,
                                    b.kelompok_dagang
                                FROM tb_konsinyasi_settlement s
                                JOIN tbpo_barang b ON s.kd_barang = b.kode_barang
                                WHERE s.status = 'BILLED'
                                  AND COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) <= " . $this->db->escape($end_date) . "
                                  {$prod_filter_clause}";
                $queries[] = "($sql_kios_out)";
            }
        }

        // 6. Khusus Konsinyasi Lunas
        if ((!$wh_target_id || $wh_target_id == $id_wh_konsinyasi_lunas) && $this->db->table_exists('tb_konsinyasi_settlement')) {
            $sql_lunas_in = "SELECT 
                                s.kd_barang,
                                {$id_wh_konsinyasi_lunas} AS gudang_id,
                                COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) AS tanggal,
                                CONCAT(s.no_settlement, ' (', s.nama_suplier, ' - INV: ', COALESCE(s.no_invoice_supplier, '-'), ')') AS referensi,
                                s.qty_net AS qty,
                                s.hrg_beli_satuan AS harga,
                                'IN' AS type,
                                'LUNAS-IN ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tb_konsinyasi_settlement s
                            JOIN tbpo_barang b ON s.kd_barang = b.kode_barang
                            WHERE s.status = 'BILLED'
                              AND COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) <= " . $this->db->escape($end_date) . "
                              {$prod_filter_clause}";
            $queries[] = "($sql_lunas_in)";
        }

        // 7. Query Penyesuaian Barang
        if ($this->db->table_exists('tbkeu_penyesuaian_barang') && $this->db->table_exists('tbkeu_penyesuaian_barang_detail')) {
            // A. Penyesuaian satu gudang / gudang tujuan
            $sql_adj_in = "SELECT 
                                pbd.kd_barang,
                                IF(pb.id_gudang_ke IS NOT NULL AND pb.id_gudang_ke > 0, pb.id_gudang_ke, pb.id_gudang_dari) AS gudang_id,
                                pb.tanggal,
                                pb.no_referensi AS referensi,
                                ABS(pbd.jumlah) AS qty,
                                0 AS harga,
                                'IN' AS type,
                                'PB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tbkeu_penyesuaian_barang pb
                            JOIN tbkeu_penyesuaian_barang_detail pbd ON pb.id_penyesuaian = pbd.id_penyesuaian
                            JOIN tbpo_barang b ON pbd.kd_barang = b.kode_barang
                            WHERE pb.status = 'POSTED' AND pbd.jumlah > 0 AND pb.tanggal <= " . $this->db->escape($end_date) . "
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_adj_in .= " AND IF(pb.id_gudang_ke IS NOT NULL AND pb.id_gudang_ke > 0, pb.id_gudang_ke, pb.id_gudang_dari) = {$wh_target_id}";
            }
            $queries[] = "($sql_adj_in)";

            $sql_adj_out = "SELECT 
                                pbd.kd_barang,
                                IF(pb.id_gudang_dari IS NOT NULL AND pb.id_gudang_dari > 0, pb.id_gudang_dari, pb.id_gudang_ke) AS gudang_id,
                                pb.tanggal,
                                pb.no_referensi AS referensi,
                                ABS(pbd.jumlah) AS qty,
                                0 AS harga,
                                'OUT' AS type,
                                'PB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tbkeu_penyesuaian_barang pb
                            JOIN tbkeu_penyesuaian_barang_detail pbd ON pb.id_penyesuaian = pbd.id_penyesuaian
                            JOIN tbpo_barang b ON pbd.kd_barang = b.kode_barang
                            WHERE pb.status = 'POSTED' AND pbd.jumlah < 0 AND pb.tanggal <= " . $this->db->escape($end_date) . "
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_adj_out .= " AND IF(pb.id_gudang_dari IS NOT NULL AND pb.id_gudang_dari > 0, pb.id_gudang_dari, pb.id_gudang_ke) = {$wh_target_id}";
            }
            $queries[] = "($sql_adj_out)";
        }

        // Eksekusi satu batch UNION ALL query
        $union_sql = implode(" UNION ALL ", $queries) . " ORDER BY tanggal ASC";
        $all_tx = $this->db->query($union_sql)->result_array();

        // Kelompokkan hasil per [kd_barang][gudang_id]
        $grouped = [];
        $product_info = [];

        foreach ($all_tx as $tx) {
            $kd = $tx['kd_barang'];
            $wh = (int)$tx['gudang_id'];

            if (!isset($product_info[$kd])) {
                $product_info[$kd] = [
                    'nama_barang' => $tx['nama_barang'],
                    'satuan' => $tx['satuan'],
                    'kelompok_dagang' => $tx['kelompok_dagang']
                ];
            }

            $grouped[$kd][$wh][] = $tx;
        }

        $all_products_data = [];

        // Urutkan berdasarkan kode produk
        ksort($grouped);

        foreach ($grouped as $kd => $byWarehouse) {
            ksort($byWarehouse);

            $prod_meta = $product_info[$kd];
            $grp_desc = $groups_map[$prod_meta['kelompok_dagang']] ?? 'Tanpa Kelompok';

            foreach ($byWarehouse as $wh_id => $txs) {
                $wh_name = $warehouses_map[$wh_id] ?? ('Gudang ' . $wh_id);

                // Urutkan transaksi dalam gudang ini
                usort($txs, function($a, $b) {
                    $t1 = strtotime($a['tanggal']);
                    $t2 = strtotime($b['tanggal']);
                    if ($t1 === $t2) {
                        return ($a['type'] === 'IN') ? -1 : 1;
                    }
                    return $t1 < $t2 ? -1 : 1;
                });

                $qty_saldo = 0.0;
                $nilai_saldo = 0.0;
                $average_hpp = 0.0;

                $opening_balance = [
                    'tanggal' => '',
                    'referensi' => 'O/B Opening Balance',
                    'unit' => !empty($txs) ? $txs[0]['satuan'] : $prod_meta['satuan'],
                    'masuk_qty' => 0.0,
                    'masuk_harga' => 0.0,
                    'masuk_nilai' => 0.0,
                    'keluar_qty' => 0.0,
                    'keluar_harga' => 0.0,
                    'keluar_nilai' => 0.0,
                    'saldo_qty' => 0.0,
                    'saldo_harga' => 0.0,
                    'saldo_nilai' => 0.0
                ];

                $report_rows = [];
                $sub_masuk_qty = 0.0;
                $sub_masuk_nilai = 0.0;
                $sub_keluar_qty = 0.0;
                $sub_keluar_nilai = 0.0;

                foreach ($txs as $tx) {
                    $qty = (float)$tx['qty'];
                    $harga = (float)$tx['harga'];
                    $tx_date = $tx['tanggal'];

                    if ($tx['type'] === 'IN') {
                        $in_harga = ($harga > 0) ? $harga : $average_hpp;
                        $qty_saldo += $qty;
                        $nilai_saldo += ($qty * $in_harga);
                        $average_hpp = $qty_saldo > 0 ? ($nilai_saldo / $qty_saldo) : 0.0;
                    } else {
                        $qty_saldo -= $qty;
                        $nilai_saldo -= ($qty * $average_hpp);
                    }

                    if (strtotime($tx_date) < strtotime($start_date)) {
                        // Saldo awal sebelum periode filter
                        $opening_balance['saldo_qty'] = $qty_saldo;
                        $opening_balance['saldo_harga'] = $average_hpp;
                        $opening_balance['saldo_nilai'] = $nilai_saldo;
                    } else if (strtotime($tx_date) <= strtotime($end_date)) {
                        // Masuk ke baris transaksi periode
                        $refPrefix = $tx['ref_prefix'] ?? ($tx['type'] === 'IN' ? 'PJ ' : 'SJ ');
                        $row = [
                            'tanggal' => $tx['tanggal'],
                            'referensi' => $refPrefix . $tx['referensi'],
                            'unit' => $tx['satuan'],
                            'masuk_qty' => 0.0,
                            'masuk_harga' => 0.0,
                            'masuk_nilai' => 0.0,
                            'keluar_qty' => 0.0,
                            'keluar_harga' => 0.0,
                            'keluar_nilai' => 0.0,
                            'saldo_qty' => $qty_saldo,
                            'saldo_harga' => $average_hpp,
                            'saldo_nilai' => $nilai_saldo
                        ];

                        if ($tx['type'] === 'IN') {
                            $in_harga = ($harga > 0) ? $harga : $average_hpp;
                            $row['masuk_qty'] = $qty;
                            $row['masuk_harga'] = $in_harga;
                            $row['masuk_nilai'] = $qty * $in_harga;
                            $sub_masuk_qty += $qty;
                            $sub_masuk_nilai += ($qty * $in_harga);
                        } else {
                            $row['keluar_qty'] = $qty;
                            $row['keluar_harga'] = $average_hpp;
                            $row['keluar_nilai'] = $qty * $average_hpp;
                            $sub_keluar_qty += $qty;
                            $sub_keluar_nilai += ($qty * $average_hpp);
                        }
                        $report_rows[] = $row;
                    }
                }

                if (!empty($report_rows)) {
                    $opening_balance['unit'] = $report_rows[0]['unit'];
                }

                // Hanya sertakan kartu stok gudang ini jika ada mutasi dalam periode atau saldo awal/akhir != 0
                if (!empty($report_rows) || $opening_balance['saldo_qty'] != 0 || $qty_saldo != 0) {
                    $all_products_data[] = [
                        'id_gudang'       => $wh_id,
                        'warehouse_name'  => $wh_name,
                        'product_code'    => $kd,
                        'product_name'    => $prod_meta['nama_barang'],
                        'kelompok_barang' => $grp_desc,
                        'opening_balance' => $opening_balance,
                        'rows'            => $report_rows,
                        'sub_masuk_qty'   => $sub_masuk_qty,
                        'sub_masuk_harga' => $sub_masuk_qty > 0 ? ($sub_masuk_nilai / $sub_masuk_qty) : 0.0,
                        'sub_masuk_nilai' => $sub_masuk_nilai,
                        'sub_keluar_qty'  => $sub_keluar_qty,
                        'sub_keluar_harga'=> $sub_keluar_qty > 0 ? ($sub_keluar_nilai / $sub_keluar_qty) : 0.0,
                        'sub_keluar_nilai'=> $sub_keluar_nilai,
                        'final_saldo_qty' => $qty_saldo,
                        'final_saldo_harga' => $average_hpp,
                        'final_saldo_nilai' => $nilai_saldo
                    ];
                }
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $all_products_data
            ]));
    }

    public function repost_retur_journal($id_retur = null)
    {
        $this->load->model('M_Journal');
        if ($id_retur) {
            $result = $this->M_Journal->post_jurnal_retur_penjualan((int)$id_retur);
            echo json_encode(['success' => (bool)$result, 'id_retur' => $id_retur]);
        } else {
            $all = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'selesai'])->result_array();
            $count = 0;
            foreach ($all as $r) {
                if ($this->M_Journal->post_jurnal_retur_penjualan((int)$r['id_retur'])) {
                    $count++;
                }
            }
            echo json_encode(['success' => true, 'processed' => $count]);
        }
    }
}

