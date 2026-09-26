<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_Konsinyasi
 * Mengelola siklus penuh barang konsinyasi (titipan supplier):
 * 1. Penerimaan Barang Konsinyasi dari Supplier → tabel tb_konsinyasi_masuk
 * 2. Stok masuk ke gudang konsinyasi (tanpa LPB, tanpa hutang)
 * 3. Sinkronisasi barang terjual dari SO/Faktur → tb_konsinyasi_settlement
 * 4. Penyelesaian (Settlement): input harga + invoice → jurnal Hutang + HPP
 */
class M_Konsinyasi extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema();
    }

    /**
     * Memastikan tabel tb_konsinyasi_settlement tersedia di database
     */
    public function ensure_schema()
    {
        if (!$this->db->table_exists('tb_konsinyasi_settlement')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_settlement` (
                  `id_settlement` int(11) NOT NULL AUTO_INCREMENT,
                  `no_settlement` varchar(50) NOT NULL,
                  `tanggal_settlement` date NOT NULL,
                  `kd_suplier` varchar(50) NOT NULL,
                  `nama_suplier` varchar(150) NOT NULL,
                  `gudang_id` int(11) NOT NULL DEFAULT 13,
                  `id_so` int(11) DEFAULT NULL,
                  `no_so` varchar(50) DEFAULT NULL,
                  `id_faktur` int(11) DEFAULT NULL,
                  `no_faktur` varchar(50) DEFAULT NULL,
                  `customer_name` varchar(150) DEFAULT NULL,
                  `id_lpb_asal` int(11) DEFAULT NULL,
                  `nomor_lpb_asal` varchar(50) DEFAULT NULL,
                  `kd_barang` varchar(50) NOT NULL,
                  `nama_barang` varchar(200) NOT NULL,
                  `no_lot` varchar(100) DEFAULT NULL,
                  `expired_date` date DEFAULT NULL,
                  `qty_terjual` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `qty_retur` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `qty_net` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `satuan` varchar(30) DEFAULT NULL,
                  `hrg_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `tipe_pajak` enum('EXCLUDE','INCLUDE','NON_PPN') NOT NULL DEFAULT 'EXCLUDE',
                  `hrg_satuan_input` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `subtotal_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `hrg_beli_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `subtotal_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `ppn_persen` decimal(5,2) NOT NULL DEFAULT 0.00,
                  `nilai_ppn` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `total_tagihan_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `no_invoice_supplier` varchar(100) DEFAULT NULL,
                  `tgl_invoice_supplier` date DEFAULT NULL,
                  `status` enum('PENDING','BILLED','CANCELLED') NOT NULL DEFAULT 'PENDING',
                  `id_jurnal_pembelian` bigint(20) unsigned DEFAULT NULL,
                  `settled_at` datetime DEFAULT NULL,
                  `settled_by` varchar(50) DEFAULT NULL,
                  `catatan` text DEFAULT NULL,
                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id_settlement`),
                  KEY `idx_status` (`status`),
                  KEY `idx_suplier` (`kd_suplier`),
                  KEY `idx_so` (`no_so`),
                  KEY `idx_faktur` (`no_faktur`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if (!$this->db->table_exists('tb_konsinyasi_masuk')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_masuk` (
                  `id_masuk` int(11) NOT NULL AUTO_INCREMENT,
                  `nomor_masuk` varchar(50) NOT NULL,
                  `tanggal_masuk` date NOT NULL,
                  `kd_suplier` varchar(50) NOT NULL,
                  `nama_suplier` varchar(150) NOT NULL,
                  `gudang_id` int(11) NOT NULL DEFAULT 13,
                  `no_surat_jalan` varchar(100) DEFAULT NULL,
                  `tgl_surat_jalan` date DEFAULT NULL,
                  `keterangan` text DEFAULT NULL,
                  `status` enum('RECEIVED','CANCELLED') NOT NULL DEFAULT 'RECEIVED',
                  `created_by` varchar(50) DEFAULT NULL,
                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id_masuk`),
                  UNIQUE KEY `idx_nomor_masuk` (`nomor_masuk`),
                  KEY `idx_suplier` (`kd_suplier`),
                  KEY `idx_tgl` (`tanggal_masuk`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if (!$this->db->table_exists('tb_konsinyasi_masuk_detail')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_masuk_detail` (
                  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
                  `id_masuk` int(11) NOT NULL,
                  `kd_barang` varchar(50) NOT NULL,
                  `nama_barang` varchar(200) NOT NULL,
                  `satuan` varchar(30) DEFAULT NULL,
                  `qty` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `no_lot` varchar(100) DEFAULT NULL,
                  `expired_date` date DEFAULT NULL,
                  `keterangan` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`id_detail`),
                  KEY `idx_id_masuk` (`id_masuk`),
                  KEY `idx_kd_barang` (`kd_barang`),
                  KEY `idx_no_lot` (`no_lot`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }
    }

    /**
     * Menghasilkan nomor referensi unik settlement
     * Format: KONS-SET-YYMMDD-XXXX
     */
    public function generate_settlement_no()
    {
        $prefix = 'KONS-SET-' . date('ymd') . '-';
        $lastRow = $this->db
            ->select('no_settlement')
            ->like('no_settlement', $prefix, 'after')
            ->order_by('id_settlement', 'DESC')
            ->limit(1)
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        $seq = 1;
        if (!empty($lastRow['no_settlement'])) {
            $lastNum = (int) substr($lastRow['no_settlement'], strlen($prefix));
            $seq = $lastNum + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mengambil ID gudang yang berkategori konsinyasi
     */
    public function get_consignment_warehouse_ids()
    {
        $rows = $this->db
            ->select('id_gudang')
            ->group_start()
                ->like('nama_gudang', 'Konsi', 'both')
                ->or_where('tipe', 'KONSINYASI')
                ->or_where('id_gudang', 13)
            ->group_end()
            ->get('tb_gudang')
            ->result_array();

        $ids = array_map(function ($r) {
            return (int) $r['id_gudang'];
        }, $rows);

        if (empty($ids)) {
            $ids = [13];
        }

        return array_unique($ids);
    }

    /**
     * Sinkronisasi data barang konsinyasi yang terjual dari SO / Faktur
     * Otomatis membaca penjualan yang mengambil stok dari gudang konsinyasi
     */
    public function sync_pending_consignment_sales()
    {
        $warehouseIds = $this->get_consignment_warehouse_ids();
        if (empty($warehouseIds)) {
            return 0;
        }

        // Ambil penjualan dari Sales Order yang menggunakan gudang konsinyasi
        $this->db->select("
            so.id_so,
            so.no_so,
            so.no_faktur,
            so.tanggal_transaksi,
            so.customer_name,
            so.gudang_id,
            sod.id AS id_so_detail,
            sod.kd_barang,
            sod.nama_barang,
            sod.qty,
            sod.satuan,
            sod.no_lot,
            sod.expired_date,
            sod.hrg_satuan,
            sod.subtotal_after_disc
        ");
        $this->db->from('tbso_sales_order so');
        $this->db->join('tbso_sales_order_detail sod', 'sod.id_so = so.id_so', 'inner');
        $this->db->where_in('so.gudang_id', $warehouseIds);
        $this->db->where_not_in('so.status', ['draft', 'cancelled']);
        $this->db->where('sod.qty >', 0);
        $salesItems = $this->db->get()->result_array();

        $insertedCount = 0;
        foreach ($salesItems as $item) {
            // Cek apakah transaksi SO detail ini sudah pernah dicatat di tabel settlement
            $exists = $this->db
                ->where('id_so', $item['id_so'])
                ->where('kd_barang', $item['kd_barang'])
                ->where('no_lot', trim((string) $item['no_lot']))
                ->get('tb_konsinyasi_settlement')
                ->row_array();

            if ($exists) {
                // Update nomor faktur jika sudah terbit belakangan
                if (empty($exists['no_faktur']) && !empty($item['no_faktur'])) {
                    $this->db->where('id_settlement', $exists['id_settlement'])->update('tb_konsinyasi_settlement', [
                        'no_faktur' => $item['no_faktur']
                    ]);
                }
                continue;
            }

            // Cari supplier asal dan nomor LPB konsinyasi dari penerimaan fisik
            $originLpb = $this->find_origin_consignment_lpb(
                $item['kd_barang'],
                $item['gudang_id'],
                $item['no_lot']
            );

            $kdSuplier = !empty($originLpb['kd_suplier']) ? $originLpb['kd_suplier'] : 'SUPP-KONS';
            $namaSuplier = !empty($originLpb['nama_suplier']) ? $originLpb['nama_suplier'] : 'Supplier Konsinyasi';
            $idLpbAsal = !empty($originLpb['id_lpb']) ? (int) $originLpb['id_lpb'] : null;
            $nomorLpbAsal = !empty($originLpb['nomor_lpb']) ? $originLpb['nomor_lpb'] : null;

            $qtyTerjual = (float) $item['qty'];
            $hrgJual = (float) $item['hrg_satuan'];
            $subtotalJual = (float) ($item['subtotal_after_disc'] > 0 ? $item['subtotal_after_disc'] : ($qtyTerjual * $hrgJual));

            $insertPayload = [
                'no_settlement'       => $this->generate_settlement_no(),
                'tanggal_settlement'  => $item['tanggal_transaksi'] ?: date('Y-m-d'),
                'kd_suplier'          => $kdSuplier,
                'nama_suplier'        => $namaSuplier,
                'gudang_id'           => (int) $item['gudang_id'],
                'id_so'               => (int) $item['id_so'],
                'no_so'               => $item['no_so'],
                'id_faktur'           => null,
                'no_faktur'           => $item['no_faktur'] ?: null,
                'customer_name'       => $item['customer_name'] ?: 'Customer Umum',
                'id_lpb_asal'         => $idLpbAsal,
                'nomor_lpb_asal'      => $nomorLpbAsal,
                'kd_barang'           => $item['kd_barang'],
                'nama_barang'         => $item['nama_barang'],
                'no_lot'              => trim((string) $item['no_lot']),
                'expired_date'        => !empty($item['expired_date']) ? $item['expired_date'] : null,
                'qty_terjual'         => $qtyTerjual,
                'qty_retur'           => 0.000,
                'qty_net'             => $qtyTerjual,
                'satuan'              => $item['satuan'] ?: 'PCS',
                'hrg_jual'            => $hrgJual,
                'subtotal_jual'       => $subtotalJual,
                'hrg_beli_satuan'     => 0.00,
                'subtotal_beli'       => 0.00,
                'ppn_persen'          => 0.00,
                'nilai_ppn'           => 0.00,
                'total_tagihan_beli'  => 0.00,
                'no_invoice_supplier' => null,
                'tgl_invoice_supplier'=> null,
                'status'              => 'PENDING',
                'created_at'          => date('Y-m-d H:i:s')
            ];

            $this->db->insert('tb_konsinyasi_settlement', $insertPayload);
            $insertedCount++;
        }

        return $insertedCount;
    }

    /**
     * Mencari data penerimaan asal untuk mengetahui pemilik/supplier barang titipan
     * Prioritas:
     * 1. tb_lpb resmi (LPB Konsinyasi hasil PO di Logistik)
     * 2. tbpo_barang (supplier default barang)
     */
    private function find_origin_consignment_lpb($kdBarang, $gudangId, $noLot = '')
    {
        // 1. Cek dari penerimaan resmi tb_lpb (diutamakan jenis LPB Konsinyasi)
        $this->db->select('h.id_lpb, h.nomor_lpb, h.kd_suplier, h.nama_suplier');
        $this->db->from('tb_lpb h');
        $this->db->join('tb_lpb_detail d', 'd.id_lpb = h.id_lpb', 'inner');
        $this->db->where('d.kd_barang', $kdBarang);
        $this->db->where('h.gudang_id', (int) $gudangId);
        if (!empty($noLot)) {
            $this->db->where('d.no_lot', trim((string) $noLot));
        }
        if ($this->db->field_exists('jenis_lpb', 'tb_lpb')) {
            $this->db->order_by("(CASE WHEN h.jenis_lpb = 'LPB Konsinyasi' THEN 1 ELSE 2 END)", 'ASC', false);
        }
        $this->db->order_by('h.id_lpb', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get()->row_array();

        if ($row && !empty($row['kd_suplier'])) {
            return $row;
        }

        // 2. Fallback: cari di tbpo_barang supplier default
        $barang = $this->db->select('kd_suplier, nama_suplier')->where('kode_barang', $kdBarang)->get('tbpo_barang')->row_array();
        if ($barang && !empty($barang['kd_suplier'])) {
            return [
                'id_lpb'       => null,
                'nomor_lpb'    => null,
                'kd_suplier'   => $barang['kd_suplier'],
                'nama_suplier' => $barang['nama_suplier'] ?: $barang['kd_suplier']
            ];
        }

        return null;
    }

    /**
     * Mengambil daftar settlement dengan filter
     */
    public function get_settlement_list(array $filters = [])
    {
        $this->db->select('s.*, g.nama_gudang, j.nomor_jurnal');
        $this->db->from('tb_konsinyasi_settlement s');
        $this->db->join('tb_gudang g', 'g.id_gudang = s.gudang_id', 'left');
        $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = s.id_jurnal_pembelian', 'left');

        if (!empty($filters['status']) && $filters['status'] !== 'SEMUA') {
            $this->db->where('s.status', strtoupper(trim((string) $filters['status'])));
        }

        if (!empty($filters['kd_suplier']) && $filters['kd_suplier'] !== 'SEMUA') {
            $this->db->where('s.kd_suplier', trim((string) $filters['kd_suplier']));
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('s.tanggal_settlement >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('s.tanggal_settlement <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $this->db->group_start();
                $this->db->like('s.no_settlement', $search);
                $this->db->or_like('s.nama_barang', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('s.customer_name', $search);
                $this->db->or_like('s.no_so', $search);
                $this->db->or_like('s.no_faktur', $search);
                $this->db->or_like('s.no_invoice_supplier', $search);
            $this->db->group_end();
        }

        $this->db->order_by('s.id_settlement', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil 1 baris settlement berdasarkan ID
     */
    public function get_settlement_by_id($idSettlement)
    {
        return $this->db
            ->select('s.*, g.nama_gudang, j.nomor_jurnal')
            ->from('tb_konsinyasi_settlement s')
            ->join('tb_gudang g', 'g.id_gudang = s.gudang_id', 'left')
            ->join('tbkeu_jurnal j', 'j.id_jurnal = s.id_jurnal_pembelian', 'left')
            ->where('s.id_settlement', (int) $idSettlement)
            ->get()
            ->row_array();
    }

    /**
     * Proses posting penyelesaian konsinyasi:
     * - Mengupdate harga beli resmi dari invoice supplier
     * - Menghitung total tagihan beli
     * - Memposting jurnal Hutang Konsinyasi & HPP
     */
    public function process_settlement($idSettlement, array $payload, $userId = null, $userName = 'SYSTEM')
    {
        $settlement = $this->get_settlement_by_id($idSettlement);
        if (!$settlement) {
            return ['status' => false, 'message' => 'Data settlement konsinyasi tidak ditemukan.'];
        }

        if ($settlement['status'] === 'BILLED') {
            return ['status' => false, 'message' => 'Transaksi ini sudah selesai diposting sebelumnya.'];
        }

        $hrgSatuanInput = (float) ($payload['hrg_satuan_input'] ?? ($payload['hrg_beli_satuan'] ?? 0));
        if ($hrgSatuanInput <= 0) {
            return ['status' => false, 'message' => 'Harga beli satuan dari supplier harus lebih besar dari 0.'];
        }

        $tipePajak = strtoupper(trim((string) ($payload['tipe_pajak'] ?? 'EXCLUDE')));
        if (!in_array($tipePajak, ['EXCLUDE', 'INCLUDE', 'NON_PPN'], true)) {
            $tipePajak = 'EXCLUDE';
        }

        $noInvoiceSupplier = trim((string) ($payload['no_invoice_supplier'] ?? ''));
        if ($noInvoiceSupplier === '') {
            return ['status' => false, 'message' => 'Nomor invoice/tagihan dari supplier wajib diisi.'];
        }

        $tglInvoiceSupplier = !empty($payload['tgl_invoice_supplier']) ? $payload['tgl_invoice_supplier'] : date('Y-m-d');
        $ppnPersen = ($tipePajak === 'NON_PPN') ? 0.00 : (!empty($payload['ppn_persen']) ? (float) $payload['ppn_persen'] : 11.00);
        $qtyNet = (float) $settlement['qty_net'];

        if ($tipePajak === 'INCLUDE') {
            // Jika harga dari supplier INCLUDE PPN (misal Rp 55.500 include PPN 11%):
            // Total tagihan = Qty * Harga Satuan Input
            $totalTagihanBeli = round($qtyNet * $hrgSatuanInput, 2);
            // DPP = Total Tagihan / (1 + (PPN% / 100))
            $divider = 1 + ($ppnPersen / 100);
            $subtotalBeli = ($ppnPersen > 0) ? round($totalTagihanBeli / $divider, 2) : $totalTagihanBeli;
            $nilaiPpn = round($totalTagihanBeli - $subtotalBeli, 2);
            $hrgBeliSatuan = ($qtyNet > 0) ? round($subtotalBeli / $qtyNet, 4) : 0;
        } elseif ($tipePajak === 'NON_PPN') {
            $ppnPersen = 0.00;
            $hrgBeliSatuan = $hrgSatuanInput;
            $subtotalBeli = round($qtyNet * $hrgBeliSatuan, 2);
            $nilaiPpn = 0.00;
            $totalTagihanBeli = $subtotalBeli;
        } else {
            // EXCLUDE PPN: Harga input adalah DPP murni sebelum PPN
            $hrgBeliSatuan = $hrgSatuanInput;
            $subtotalBeli = round($qtyNet * $hrgBeliSatuan, 2);
            $nilaiPpn = round(($subtotalBeli * $ppnPersen) / 100, 2);
            $totalTagihanBeli = round($subtotalBeli + $nilaiPpn, 2);
        }

        $this->db->trans_begin();

        $updateData = [
            'tipe_pajak'          => $tipePajak,
            'hrg_satuan_input'    => $hrgSatuanInput,
            'hrg_beli_satuan'     => $hrgBeliSatuan,
            'subtotal_beli'       => $subtotalBeli,
            'ppn_persen'          => $ppnPersen,
            'nilai_ppn'           => $nilaiPpn,
            'total_tagihan_beli'  => $totalTagihanBeli,
            'no_invoice_supplier' => $noInvoiceSupplier,
            'tgl_invoice_supplier'=> $tglInvoiceSupplier,
            'status'              => 'BILLED',
            'settled_at'          => date('Y-m-d H:i:s'),
            'settled_by'          => $userName,
            'catatan'             => trim((string) ($payload['catatan'] ?? ''))
        ];

        $this->db->where('id_settlement', (int) $idSettlement)->update('tb_konsinyasi_settlement', $updateData);

        // Eksekusi Posting Jurnal Akuntansi
        $this->load->library('Accounting_source_service');
        $journalRes = $this->accounting_source_service->post_consignment_settlement($idSettlement, $userId);

        if (!$journalRes['success']) {
            $this->db->trans_rollback();
            return [
                'status'  => false,
                'message' => 'Gagal memposting jurnal akuntansi: ' . ($journalRes['message'] ?? 'Error akuntansi.'),
                'errors'  => $journalRes['errors'] ?? []
            ];
        }

        $idJurnal = null;
        $nomorJurnal = null;
        if (!empty($journalRes['data'])) {
            if (is_array($journalRes['data'])) {
                $idJurnal = (int) ($journalRes['data']['id_jurnal'] ?? 0) ?: null;
                $nomorJurnal = $journalRes['data']['nomor_jurnal'] ?? null;
            } elseif (is_object($journalRes['data'])) {
                $idJurnal = (int) ($journalRes['data']->id_jurnal ?? 0) ?: null;
                $nomorJurnal = $journalRes['data']->nomor_jurnal ?? null;
            }
        }
        if (!$idJurnal && !empty($journalRes['data']['journal'])) {
            $idJurnal = (int) ($journalRes['data']['journal']->id_jurnal ?? 0) ?: null;
            $nomorJurnal = $journalRes['data']['journal']->nomor_jurnal ?? null;
        }

        if ($idJurnal) {
            $this->db->where('id_settlement', (int) $idSettlement)->update('tb_konsinyasi_settlement', [
                'id_jurnal_pembelian' => $idJurnal
            ]);
        }

        $this->db->trans_commit();

        return [
            'status'        => true,
            'message'       => 'Penyelesaian konsinyasi berhasil diposting. Jurnal Hutang Usaha & HPP telah tercatat.',
            'id_jurnal'     => $idJurnal,
            'nomor_jurnal'  => $nomorJurnal,
            'total_tagihan' => $totalTagihanBeli
        ];
    }

    /**
     * Mengambil ringkasan statistik konsinyasi
     */
    public function get_summary_stats()
    {
        $pending = $this->db
            ->select('COUNT(*) as total_item, COALESCE(SUM(qty_net), 0) as total_qty, COALESCE(SUM(subtotal_jual), 0) as total_omzet')
            ->where('status', 'PENDING')
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        $billed = $this->db
            ->select('COUNT(*) as total_item, COALESCE(SUM(qty_net), 0) as total_qty, COALESCE(SUM(total_tagihan_beli), 0) as total_hutang')
            ->where('status', 'BILLED')
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        return [
            'pending_count'  => (int) ($pending['total_item'] ?? 0),
            'pending_qty'    => (float) ($pending['total_qty'] ?? 0),
            'pending_omzet'  => (float) ($pending['total_omzet'] ?? 0),
            'billed_count'   => (int) ($billed['total_item'] ?? 0),
            'billed_qty'     => (float) ($billed['total_qty'] ?? 0),
            'billed_hutang'  => (float) ($billed['total_hutang'] ?? 0)
        ];
    }

    /**
     * Mengambil daftar supplier konsinyasi untuk filter
     */
    public function get_suppliers_list()
    {
        return $this->db
            ->select('kd_suplier, nama_suplier, COUNT(*) as total_transaksi')
            ->group_by(['kd_suplier', 'nama_suplier'])
            ->order_by('nama_suplier', 'ASC')
            ->get('tb_konsinyasi_settlement')
            ->result_array();
    }
}

