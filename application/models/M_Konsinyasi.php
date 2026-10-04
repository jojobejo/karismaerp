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
                  `id_pembayaran` int(11) DEFAULT NULL,
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
                  `status` enum('DI_KIOS','PENDING','LAKU','BILLED','CANCELLED') NOT NULL DEFAULT 'DI_KIOS',
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
        } else {
            // Pastikan enum kolom status sudah mendukung DI_KIOS dan LAKU jika tabel sudah ada sebelumnya
            $col = $this->db->query("SHOW COLUMNS FROM tb_konsinyasi_settlement LIKE 'status'")->row_array();
            if ($col && strpos((string)$col['Type'], 'DI_KIOS') === false) {
                $this->db->query("ALTER TABLE tb_konsinyasi_settlement MODIFY COLUMN status ENUM('DI_KIOS','PENDING','LAKU','BILLED','CANCELLED') NOT NULL DEFAULT 'DI_KIOS'");
            }
            if (!$this->db->field_exists('id_pembayaran', 'tb_konsinyasi_settlement')) {
                $this->db->query("ALTER TABLE tb_konsinyasi_settlement ADD COLUMN id_pembayaran INT(11) NULL DEFAULT NULL AFTER id_faktur, ADD INDEX idx_pembayaran (id_pembayaran)");
            }
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

        if (!$this->db->table_exists('tb_konsinyasi_faktur')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_faktur` (
                  `id_faktur_konsinyasi` int(11) NOT NULL AUTO_INCREMENT,
                  `no_faktur_konsinyasi` varchar(50) NOT NULL,
                  `id_faktur_induk` int(11) NOT NULL,
                  `no_faktur_induk` varchar(50) NOT NULL,
                  `id_pembayaran` int(11) NOT NULL,
                  `id_settlement` int(11) DEFAULT NULL,
                  `id_so` int(11) DEFAULT NULL,
                  `no_so` varchar(50) DEFAULT NULL,
                  `termin_ke` int(11) NOT NULL DEFAULT 1,
                  `tanggal_faktur` date NOT NULL,
                  `kd_customer` varchar(50) DEFAULT NULL,
                  `nama_customer` varchar(150) DEFAULT NULL,
                  `kd_suplier` varchar(50) DEFAULT NULL,
                  `nama_suplier` varchar(150) DEFAULT NULL,
                  `gudang_id` int(11) DEFAULT 13,
                  `kd_barang` varchar(50) NOT NULL,
                  `nama_barang` varchar(200) NOT NULL,
                  `no_lot` varchar(100) DEFAULT NULL,
                  `expired_date` date DEFAULT NULL,
                  `qty` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `satuan` varchar(30) DEFAULT 'PCS',
                  `hrg_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `jumlah_bayar` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `metode_pembayaran` varchar(50) DEFAULT NULL,
                  `status` enum('LAKU','BILLED','CANCELLED') NOT NULL DEFAULT 'LAKU',
                  `created_by` varchar(100) DEFAULT NULL,
                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id_faktur_konsinyasi`),
                  UNIQUE KEY `idx_no_faktur_konsinyasi` (`no_faktur_konsinyasi`),
                  KEY `idx_faktur_induk` (`id_faktur_induk`),
                  KEY `idx_pembayaran` (`id_pembayaran`),
                  KEY `idx_settlement` (`id_settlement`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if ($this->db->table_exists('tbkeu_pembayaran_faktur') && !$this->db->field_exists('no_faktur_konsinyasi', 'tbkeu_pembayaran_faktur')) {
            $this->db->query("ALTER TABLE tbkeu_pembayaran_faktur ADD COLUMN no_faktur_konsinyasi VARCHAR(50) NULL DEFAULT NULL AFTER no_faktur, ADD INDEX idx_no_faktur_konsinyasi (no_faktur_konsinyasi)");
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

        // Jalankan update sinkronisasi nomor faktur dan id faktur yang baru terbit untuk transaksi yang sudah tercatat
        $this->db->query("
            UPDATE tb_konsinyasi_settlement s
            JOIN tbso_faktur_penjualan fp ON fp.id_so = s.id_so
            SET s.no_faktur = fp.no_faktur, s.id_faktur = fp.id_faktur
            WHERE ((s.no_faktur IS NULL OR s.no_faktur = '') AND (s.no_faktur NOT LIKE '%-%'))
               OR (s.id_faktur IS NULL OR s.id_faktur = 0)
        ");

        // Hapus data konsinyasi_settlement yang berasal dari SO tapi belum ada faktur (karena rule baru: baru masuk kios ketika sudah jadi faktur)
        $this->db->query("
            DELETE s FROM tb_konsinyasi_settlement s
            WHERE s.id_so IS NOT NULL 
              AND (s.id_faktur IS NULL OR s.id_faktur = 0)
              AND s.status = 'DI_KIOS'
        ");

        $this->db->select("
            so.id_so,
            so.no_so,
            COALESCE(fp.no_faktur, so.no_faktur) AS no_faktur,
            fp.id_faktur,
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
        // Ubah join ke inner agar HANYA SO yang sudah terbit faktur yang masuk ke konsinyasi
        $this->db->join('tbso_faktur_penjualan fp', 'fp.id_so = so.id_so', 'inner');
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
                $updates = [];
                if (empty($exists['no_faktur']) && !empty($item['no_faktur'])) {
                    $updates['no_faktur'] = $item['no_faktur'];
                }
                if (empty($exists['id_faktur']) && !empty($item['id_faktur'])) {
                    $updates['id_faktur'] = (int) $item['id_faktur'];
                }
                if (!empty($updates)) {
                    $this->db->where('id_settlement', $exists['id_settlement'])->update('tb_konsinyasi_settlement', $updates);
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
                'id_faktur'           => !empty($item['id_faktur']) ? (int) $item['id_faktur'] : null,
                'no_faktur'           => !empty($item['no_faktur']) ? $item['no_faktur'] : null,
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
                'status'              => 'DI_KIOS',
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

        // 2. Fallback: cari di tbpo_barang supplier default dan join ke tbpo_suplier untuk mendapatkan nama_suplier
        $barang = $this->db
            ->select('b.kd_suplier, COALESCE(s.nama_suplier, b.kd_suplier) AS nama_suplier')
            ->from('tbpo_barang b')
            ->join('tbpo_suplier s', 's.kd_suplier = b.kd_suplier', 'left')
            ->where('b.kode_barang', $kdBarang)
            ->limit(1)
            ->get()
            ->row_array();

        if ($barang && !empty($barang['kd_suplier'])) {
            return [
                'id_lpb'       => null,
                'nomor_lpb'    => null,
                'kd_suplier'   => $barang['kd_suplier'],
                'nama_suplier' => !empty($barang['nama_suplier']) ? trim((string)$barang['nama_suplier']) : $barang['kd_suplier']
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
            $st = strtoupper(trim((string) $filters['status']));
            if ($st === 'LAKU' || $st === 'PENDING') {
                $this->db->where_in('s.status', ['LAKU', 'PENDING']);
            } else {
                $this->db->where('s.status', $st);
            }
        } elseif (!empty($filters['status']) && $filters['status'] === 'SEMUA') {
            $this->db->where_in('s.status', ['LAKU', 'BILLED', 'PENDING']);
        } else {
            // Default di tab penyelesaian: tampilkan barang laku yang menunggu tagihan supplier
            $this->db->where_in('s.status', ['LAKU', 'PENDING']);
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
            ->select("
                s.*, 
                g.nama_gudang, 
                j.nomor_jurnal,
                lpb.no_po AS po_no_po,
                CASE 
                    WHEN LOWER(po.keterangan_harga_ppn) = 'include' THEN 'INCLUDE'
                    WHEN LOWER(po.keterangan_harga_ppn) = 'non_ppn' THEN 'NON_PPN'
                    ELSE 'EXCLUDE'
                END AS po_tipe_pajak,
                COALESCE(pod.hrg_satuan, lpbd.harga_satuan, 0) AS po_hrg_satuan,
                COALESCE(pod.harga_satuan_exclude, lpbd.harga_satuan, 0) AS po_hrg_satuan_exclude
            ", false)
            ->from('tb_konsinyasi_settlement s')
            ->join('tb_gudang g', 'g.id_gudang = s.gudang_id', 'left')
            ->join('tbkeu_jurnal j', 'j.id_jurnal = s.id_jurnal_pembelian', 'left')
            ->join('tb_lpb lpb', '(lpb.id_lpb = s.id_lpb_asal OR (s.id_lpb_asal IS NULL AND lpb.nomor_lpb = s.nomor_lpb_asal))', 'left')
            ->join('tbpo_po po', 'po.no_po = lpb.no_po', 'left')
            ->join('tbpo_detail_po pod', 'pod.no_po = po.no_po AND pod.kd_barang = s.kd_barang', 'left')
            ->join('tb_lpb_detail lpbd', 'lpbd.id_lpb = lpb.id_lpb AND lpbd.kd_barang = s.kd_barang', 'left')
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
        if ($hrgSatuanInput <= 0 && !empty($settlement['po_hrg_satuan'])) {
            $hrgSatuanInput = (float) $settlement['po_hrg_satuan'];
        }

        if ($hrgSatuanInput <= 0) {
            return ['status' => false, 'message' => 'Harga beli satuan dari supplier harus lebih besar dari 0.'];
        }

        $tipePajak = !empty($payload['tipe_pajak']) 
            ? strtoupper(trim((string) $payload['tipe_pajak'])) 
            : (!empty($settlement['po_tipe_pajak']) ? $settlement['po_tipe_pajak'] : ($settlement['tipe_pajak'] ?? 'EXCLUDE'));

        if (!in_array($tipePajak, ['EXCLUDE', 'INCLUDE', 'NON_PPN'], true)) {
            $tipePajak = 'EXCLUDE';
        }

        $noInvoiceSupplier = trim((string) ($payload['no_invoice_supplier'] ?? ''));
        if ($noInvoiceSupplier === '') {
            return ['status' => false, 'message' => 'Nomor invoice/tagihan dari supplier wajib diisi.'];
        }

        $tglInvoiceSupplier = !empty($payload['tgl_invoice_supplier']) ? $payload['tgl_invoice_supplier'] : date('Y-m-d');
        $ppnPersen = ($tipePajak === 'NON_PPN') ? 0.00 : (!empty($payload['ppn_persen']) ? (float) $payload['ppn_persen'] : 11.00);

        // Qty yang dilaporkan laku / dibeli oleh kios
        $qtyNetOriginal = (float) $settlement['qty_net'];
        $qtyLaku = !empty($payload['qty_laku']) ? (float) $payload['qty_laku'] : $qtyNetOriginal;
        if ($qtyLaku <= 0 || $qtyLaku > $qtyNetOriginal) {
            $qtyLaku = $qtyNetOriginal;
        }

        $isPartial = ($qtyLaku < $qtyNetOriginal);
        $qtySisaDiKios = round($qtyNetOriginal - $qtyLaku, 3);
        $qtyNet = $qtyLaku;

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

        // 1. Jika penjualan hanya laku sebagian (misal dari 100 baru laku 50),
        // buat record titipan baru untuk sisa barang yang masih berada di kios (status PENDING)
        if ($isPartial) {
            $sisaPayload = [
                'no_settlement'       => $this->generate_settlement_no(),
                'tanggal_settlement'  => $settlement['tanggal_settlement'],
                'kd_suplier'          => $settlement['kd_suplier'],
                'nama_suplier'        => $settlement['nama_suplier'],
                'gudang_id'           => (int) $settlement['gudang_id'],
                'id_so'               => !empty($settlement['id_so']) ? (int) $settlement['id_so'] : null,
                'no_so'               => $settlement['no_so'],
                'id_faktur'           => !empty($settlement['id_faktur']) ? (int) $settlement['id_faktur'] : null,
                'no_faktur'           => $settlement['no_faktur'],
                'customer_name'       => $settlement['customer_name'],
                'id_lpb_asal'         => !empty($settlement['id_lpb_asal']) ? (int) $settlement['id_lpb_asal'] : null,
                'nomor_lpb_asal'      => $settlement['nomor_lpb_asal'],
                'kd_barang'           => $settlement['kd_barang'],
                'nama_barang'         => $settlement['nama_barang'],
                'no_lot'              => $settlement['no_lot'],
                'expired_date'        => $settlement['expired_date'],
                'qty_terjual'         => $qtySisaDiKios,
                'qty_retur'           => 0.000,
                'qty_net'             => $qtySisaDiKios,
                'satuan'              => $settlement['satuan'],
                'hrg_jual'            => (float) $settlement['hrg_jual'],
                'subtotal_jual'       => round($qtySisaDiKios * (float) $settlement['hrg_jual'], 2),
                'hrg_satuan_input'    => 0.00,
                'hrg_beli_satuan'     => 0.00,
                'subtotal_beli'       => 0.00,
                'ppn_persen'          => 0.00,
                'nilai_ppn'           => 0.00,
                'total_tagihan_beli'  => 0.00,
                'no_invoice_supplier' => null,
                'tgl_invoice_supplier'=> null,
                'status'              => 'DI_KIOS',
                'created_at'          => date('Y-m-d H:i:s'),
                'catatan'             => 'Sisa titipan di kios setelah pelunasan ' . $qtyLaku . ' ' . $settlement['satuan']
            ];
            $this->db->insert('tb_konsinyasi_settlement', $sisaPayload);
        }

        // 2. Baris settlement ini diupdate dengan qty yang laku dibeli kios & status menjadi BILLED
        $updateData = [
            'qty_terjual'         => $qtyLaku,
            'qty_net'             => $qtyLaku,
            'subtotal_jual'       => round($qtyLaku * (float) $settlement['hrg_jual'], 2),
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

        // Eksekusi Posting Jurnal Akuntansi untuk barang yang resmi dibeli kios
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

        $pesanSukses = 'Penyelesaian konsinyasi berhasil diposting. Qty laku: ' . $qtyLaku . ' ' . $settlement['satuan'] . ' telah diakui sebagai pembelian.';
        if ($isPartial) {
            $pesanSukses .= ' Sisa ' . $qtySisaDiKios . ' ' . $settlement['satuan'] . ' tetap tercatat sebagai titipan di kios.';
        }

        return [
            'status'        => true,
            'message'       => $pesanSukses,
            'id_jurnal'     => $idJurnal,
            'nomor_jurnal'  => $nomorJurnal,
            'total_tagihan' => $totalTagihanBeli,
            'qty_laku'      => $qtyLaku,
            'qty_sisa_kios' => $qtySisaDiKios
        ];
    }

    /**
     * Ringkasan global tracking posisi barang konsinyasi:
     * 1. Total di Gudang Konsinyasi (Stok fisik kita yang belum dikirim)
     * 2. Total di Kios (Barang yang sudah difakturkan ke kios tapi belum laku/dibeli)
     * 3. Total Laku (Barang yang telah resmi dibeli oleh kios & diselesaikan)
     * 4. Total Keseluruhan Barang Konsinyasi
     */
    public function get_tracking_summary()
    {
        $warehouseIds = $this->get_consignment_warehouse_ids();
        $whList = !empty($warehouseIds) ? implode(',', array_map('intval', $warehouseIds)) : '13';

        // 1. Total Stok Fisik di Gudang Konsinyasi Kita
        $qGudang = $this->db
            ->select('COALESCE(SUM(qty_on_hand), 0) AS total_gudang, COUNT(DISTINCT kd_barang) AS item_gudang')
            ->where_in('gudang_id', $warehouseIds)
            ->where('qty_on_hand >', 0)
            ->get('tberp_stock_batch')
            ->row_array();

        // 2. Total Barang di Kios (Status DI_KIOS / PENDING = Titipan di Kios, belum dibeli kios)
        $qKios = $this->db
            ->select('COALESCE(SUM(qty_net), 0) AS total_kios, COUNT(DISTINCT kd_barang) AS item_kios, COUNT(DISTINCT customer_name) AS total_kios_count')
            ->where_in('status', ['DI_KIOS', 'PENDING'])
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        // 3. Total Barang Laku (Status LAKU = Sudah dibeli kios & menunggu tagihan supplier, berkurang saat status BILLED)
        $qLaku = $this->db
            ->select('COALESCE(SUM(qty_net), 0) AS total_laku, COUNT(DISTINCT kd_barang) AS item_laku, COALESCE(SUM(subtotal_jual), 0) AS total_nominal_laku')
            ->where('status', 'LAKU')
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        $totalGudang = (float) ($qGudang['total_gudang'] ?? 0);
        $totalKios   = (float) ($qKios['total_kios'] ?? 0);
        $totalLaku   = (float) ($qLaku['total_laku'] ?? 0);

        return [
            'total_di_gudang'      => $totalGudang,
            'item_di_gudang'       => (int) ($qGudang['item_gudang'] ?? 0),
            'total_di_kios'        => $totalKios,
            'item_di_kios'         => (int) ($qKios['item_kios'] ?? 0),
            'total_kios_count'     => (int) ($qKios['total_kios_count'] ?? 0),
            'total_laku'           => $totalLaku,
            'item_laku'            => (int) ($qLaku['item_laku'] ?? 0),
            'total_nominal_laku'   => (float) ($qLaku['total_nominal_laku'] ?? 0),
            'total_semua'          => $totalGudang + $totalKios + $totalLaku
        ];
    }

    /**
     * Mengambil daftar inventaris barang konsinyasi dengan posisi stok:
     * - Di Gudang Konsinyasi
     * - Di Kios (Titipan Aktif)
     * - Barang Laku
     * - Total Stok
     */
    public function get_tracking_barang_list(array $filters = [])
    {
        $warehouseIds = $this->get_consignment_warehouse_ids();
        $whList = !empty($warehouseIds) ? implode(',', array_map('intval', $warehouseIds)) : '13';

        $this->db->select("
            b.kode_barang,
            b.nama_barang,
            b.satuan,
            COALESCE(lpb_info.nomor_lpb, '') AS nomor_lpb,
            COALESCE(sup.kd_suplier, sub_sup.kd_suplier, '') AS kd_suplier,
            COALESCE(sup.nama_suplier, sub_sup.nama_suplier, 'Supplier Konsinyasi') AS nama_suplier,
            COALESCE(gudang.stok_gudang, 0) AS stok_gudang,
            COALESCE(kios.stok_kios, 0) AS stok_kios,
            COALESCE(kios.jml_kios, 0) AS jml_kios,
            COALESCE(laku.stok_laku, 0) AS stok_laku,
            (COALESCE(gudang.stok_gudang, 0) + COALESCE(kios.stok_kios, 0) + COALESCE(laku.stok_laku, 0)) AS total_stok
        ", false);

        $this->db->from('tbpo_barang b');

        // Subquery stok gudang
        $this->db->join("
            (SELECT kd_barang, SUM(qty_on_hand) AS stok_gudang 
             FROM tberp_stock_batch 
             WHERE gudang_id IN ($whList) 
             GROUP BY kd_barang) gudang
        ", "gudang.kd_barang = b.kode_barang", "left", false);

        // Subquery stok di kios (DI_KIOS / PENDING)
        $this->db->join("
            (SELECT kd_barang, SUM(qty_net) AS stok_kios, COUNT(DISTINCT customer_name) AS jml_kios
             FROM tb_konsinyasi_settlement 
             WHERE status IN ('DI_KIOS', 'PENDING') 
             GROUP BY kd_barang) kios
        ", "kios.kd_barang = b.kode_barang", "left", false);

        // Subquery stok laku (Hanya yang status LAKU / belum diinput tagihan supplier)
        $this->db->join("
            (SELECT kd_barang, SUM(qty_net) AS stok_laku 
             FROM tb_konsinyasi_settlement 
             WHERE status = 'LAKU' 
             GROUP BY kd_barang) laku
        ", "laku.kd_barang = b.kode_barang", "left", false);

        // Subquery nomor LPB konsinyasi
        $this->db->join("
            (SELECT kd_barang, MAX(nomor_lpb) AS nomor_lpb
             FROM (
                 SELECT kd_barang, nomor_lpb_asal AS nomor_lpb 
                 FROM tb_konsinyasi_settlement 
                 WHERE nomor_lpb_asal IS NOT NULL AND nomor_lpb_asal != ''
                 UNION
                 SELECT d.kd_barang, h.nomor_lpb 
                 FROM tb_lpb h 
                 JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb 
                 WHERE (h.gudang_id IN ($whList) OR h.jenis_lpb LIKE '%konsin%') 
                   AND h.nomor_lpb IS NOT NULL AND h.nomor_lpb != ''
             ) lpb_all GROUP BY kd_barang) lpb_info
        ", "lpb_info.kd_barang = b.kode_barang", "left", false);

        // Subquery supplier
        $this->db->join("
            (SELECT kd_barang, MAX(kd_suplier) AS kd_suplier, MAX(nama_suplier) AS nama_suplier
             FROM (
                 SELECT kd_barang, kd_suplier, nama_suplier FROM tb_konsinyasi_settlement
                 UNION
                 SELECT d.kd_barang, h.kd_suplier, h.nama_suplier FROM tb_lpb h JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb WHERE h.gudang_id IN ($whList)
             ) s_all GROUP BY kd_barang) sub_sup
        ", "sub_sup.kd_barang = b.kode_barang", "left", false);

        $this->db->join('tbpo_suplier sup', 'sup.kd_suplier = b.kd_suplier', 'left');

        // Filter hanya barang yang memiliki stok di gudang, di kios, atau laku
        $this->db->where("(COALESCE(gudang.stok_gudang, 0) > 0 OR COALESCE(kios.stok_kios, 0) > 0 OR COALESCE(laku.stok_laku, 0) > 0)", null, false);

        if (!empty($filters['kd_suplier']) && $filters['kd_suplier'] !== 'SEMUA') {
            $kdSup = $this->db->escape(trim((string)$filters['kd_suplier']));
            $this->db->where("(COALESCE(sup.kd_suplier, sub_sup.kd_suplier) = $kdSup)", null, false);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $this->db->group_start();
                $this->db->like('b.kode_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('sup.nama_suplier', $search);
                $this->db->or_like('sub_sup.nama_suplier', $search);
            $this->db->group_end();
        }

        $this->db->order_by('b.nama_barang', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil detail daftar kios yang memegang titipan barang konsinyasi tertentu
     */
    public function get_tracking_kios_by_barang($kdBarang)
    {
        return $this->db
            ->select("
                s.id_settlement,
                s.no_settlement,
                s.tanggal_settlement,
                s.customer_name,
                s.no_so,
                COALESCE(fp.no_faktur, s.no_faktur, '-') AS no_faktur,
                s.no_lot,
                s.expired_date,
                s.qty_terjual,
                s.qty_net,
                s.satuan,
                s.hrg_jual,
                s.subtotal_jual,
                s.status,
                s.no_invoice_supplier,
                s.total_tagihan_beli
            ")
            ->from('tb_konsinyasi_settlement s')
            ->join('tbso_faktur_penjualan fp', 'fp.id_so = s.id_so', 'left')
            ->where('s.kd_barang', trim((string) $kdBarang))
            ->order_by("(CASE WHEN s.status IN ('DI_KIOS', 'PENDING') THEN 1 WHEN s.status = 'LAKU' THEN 2 ELSE 3 END)", 'ASC', false)
            ->order_by('s.id_settlement', 'DESC')
            ->get()
            ->result_array();
    }

    /**
     * Mengambil ringkasan statistik konsinyasi
     */
    public function get_summary_stats()
    {
        $pending = $this->db
            ->select('COUNT(*) as total_item, COALESCE(SUM(qty_net), 0) as total_qty, COALESCE(SUM(subtotal_jual), 0) as total_omzet')
            ->where_in('status', ['LAKU', 'PENDING'])
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

    /**
     * Menerbitkan entitas faktur konsinyasi baru saat pelunasan kios
     * Sesuai termin dan kuantitas barang yang dibeli kios.
     * TIDAK disimpan di tbso_faktur_penjualan (disimpan mandiri di tb_konsinyasi_faktur)
     */
    public function terbitkan_faktur_konsinyasi($id_faktur, $id_pembayaran, array $extra = [])
    {
        $this->ensure_schema();

        $id_pembayaran = (int) $id_pembayaran;
        $id_faktur = (int) $id_faktur;

        $pembayaran = $this->db->get_where('tbkeu_pembayaran_faktur', ['id_pembayaran' => $id_pembayaran])->row_array();
        if (!$pembayaran) {
            return null;
        }

        // Ambil data faktur induk
        $fakturInduk = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$fakturInduk) {
            return null;
        }

        $customer = $this->db->get_where('tb_customer', ['kd_customer' => $fakturInduk['kd_customer']])->row_array();
        $namaCustomer = $customer['nama_customer'] ?? $fakturInduk['nama_customer'] ?? $fakturInduk['kd_customer'];

        // Hitung termin ke berapa untuk pembayaran konsinyasi ini
        $allPayments = $this->db
            ->select('id_pembayaran')
            ->where('id_faktur', $id_faktur)
            ->where('status !=', 'CANCELLED')
            ->order_by('id_pembayaran', 'ASC')
            ->get('tbkeu_pembayaran_faktur')
            ->result_array();

        $terminKe = 1;
        foreach ($allPayments as $idx => $p) {
            if ((int) $p['id_pembayaran'] === $id_pembayaran) {
                $terminKe = $idx + 1;
                break;
            }
        }

        $noFakturKonsinyasi = $fakturInduk['no_faktur'] . '-' . $terminKe;

        // Update nomor faktur konsinyasi di tabel pembayaran
        $this->db->where('id_pembayaran', $id_pembayaran)->update('tbkeu_pembayaran_faktur', [
            'no_faktur_konsinyasi' => $noFakturKonsinyasi
        ]);

        // Cari settlement terkait (berdasarkan id_pembayaran atau settlement aktif)
        $settlement = null;
        if (!empty($extra['id_settlement'])) {
            $settlement = $this->db->get_where('tb_konsinyasi_settlement', ['id_settlement' => (int) $extra['id_settlement']])->row_array();
        }
        if (!$settlement) {
            $settlement = $this->db->get_where('tb_konsinyasi_settlement', ['id_pembayaran' => $id_pembayaran])->row_array();
        }
        if (!$settlement) {
            $settlement = $this->db
                ->where('id_faktur', $id_faktur)
                ->where_in('status', ['LAKU', 'BILLED', 'DI_KIOS', 'PENDING'])
                ->order_by('id_settlement', 'DESC')
                ->get('tb_konsinyasi_settlement')
                ->row_array();
        }

        $fakturDetail = $this->db->get_where('tbso_faktur_detail', ['id_faktur' => $id_faktur])->row_array();

        $kdBarang   = $settlement['kd_barang'] ?? $fakturDetail['kd_barang'] ?? 'KONS';
        $namaBarang = $settlement['nama_barang'] ?? $fakturDetail['nama_barang'] ?? 'Barang Konsinyasi';
        $satuan     = $settlement['satuan'] ?? $fakturDetail['satuan'] ?? 'PCS';
        $hrgSatuan  = (float) ($settlement['hrg_jual'] ?? $fakturDetail['hrg_satuan'] ?? 0);
        $qty        = (float) (!empty($pembayaran['qty_konsinyasi']) && (float)$pembayaran['qty_konsinyasi'] > 0
                        ? $pembayaran['qty_konsinyasi']
                        : ($settlement['qty_net'] ?? 0));

        if ($qty <= 0 && $hrgSatuan > 0) {
            $qty = round((float) $pembayaran['jumlah_pembayaran'] / $hrgSatuan, 3);
        }
        $subtotal = round($qty * $hrgSatuan, 2);
        if ($subtotal <= 0) {
            $subtotal = (float) $pembayaran['jumlah_pembayaran'];
        }

        $kdSuplier   = $settlement['kd_suplier'] ?? null;
        $namaSuplier = $settlement['nama_suplier'] ?? 'Supplier Konsinyasi';
        $noLot       = $settlement['no_lot'] ?? ($fakturDetail['no_lot'] ?? null);
        $expDate     = $settlement['expired_date'] ?? null;
        $idSettlement = !empty($settlement['id_settlement']) ? (int) $settlement['id_settlement'] : null;

        $existing = $this->db->get_where('tb_konsinyasi_faktur', ['no_faktur_konsinyasi' => $noFakturKonsinyasi])->row_array();
        $payloadFaktur = [
            'no_faktur_konsinyasi' => $noFakturKonsinyasi,
            'id_faktur_induk'      => $id_faktur,
            'no_faktur_induk'      => $fakturInduk['no_faktur'],
            'id_pembayaran'        => $id_pembayaran,
            'id_settlement'        => $idSettlement,
            'id_so'                => !empty($fakturInduk['id_so']) ? (int) $fakturInduk['id_so'] : null,
            'no_so'                => $fakturInduk['no_so'],
            'termin_ke'            => $terminKe,
            'tanggal_faktur'       => $pembayaran['tanggal_pembayaran'],
            'kd_customer'          => $fakturInduk['kd_customer'],
            'nama_customer'        => $namaCustomer,
            'kd_suplier'           => $kdSuplier,
            'nama_suplier'         => $namaSuplier,
            'gudang_id'            => (int) ($fakturInduk['gudang_id'] ?: 13),
            'kd_barang'            => $kdBarang,
            'nama_barang'          => $namaBarang,
            'no_lot'               => $noLot,
            'expired_date'         => $expDate,
            'qty'                  => $qty,
            'satuan'               => $satuan,
            'hrg_satuan'           => $hrgSatuan,
            'subtotal'             => $subtotal,
            'jumlah_bayar'         => (float) $pembayaran['jumlah_pembayaran'],
            'metode_pembayaran'    => $pembayaran['metode_pembayaran'],
            'status'               => ($settlement['status'] ?? 'LAKU') === 'BILLED' ? 'BILLED' : 'LAKU',
            'created_by'           => $pembayaran['create_by'] ?? 'system',
            'created_at'           => $pembayaran['create_at'] ?? date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->db->where('id_faktur_konsinyasi', $existing['id_faktur_konsinyasi'])->update('tb_konsinyasi_faktur', $payloadFaktur);
            $idFakturKonsinyasi = $existing['id_faktur_konsinyasi'];
        } else {
            $this->db->insert('tb_konsinyasi_faktur', $payloadFaktur);
            $idFakturKonsinyasi = $this->db->insert_id();
        }

        // Update juga settlement agar no_faktur dan id_pembayaran sinkron
        if ($idSettlement) {
            $this->db->where('id_settlement', $idSettlement)->update('tb_konsinyasi_settlement', [
                'no_faktur'     => $noFakturKonsinyasi,
                'id_pembayaran' => $id_pembayaran
            ]);
        }

        $payloadFaktur['id_faktur_konsinyasi'] = $idFakturKonsinyasi;
        return $payloadFaktur;
    }

    /**
     * Mengambil entitas faktur konsinyasi berdasarkan id_pembayaran
     */
    public function get_faktur_konsinyasi_by_payment($id_pembayaran)
    {
        $this->ensure_schema();
        $row = $this->db->get_where('tb_konsinyasi_faktur', ['id_pembayaran' => (int) $id_pembayaran])->row_array();
        if ($row) {
            return $row;
        }

        // Jika belum ada di tabel tapi ada pembayaran, otomatis terbitkan
        $pembayaran = $this->db->get_where('tbkeu_pembayaran_faktur', ['id_pembayaran' => (int) $id_pembayaran])->row_array();
        if ($pembayaran) {
            return $this->terbitkan_faktur_konsinyasi($pembayaran['id_faktur'], $id_pembayaran);
        }

        return null;
    }

    /**
     * Mengambil entitas faktur konsinyasi berdasarkan nomor faktur konsinyasi
     */
    public function get_faktur_konsinyasi_by_no($noFakturKonsinyasi)
    {
        $this->ensure_schema();
        return $this->db->get_where('tb_konsinyasi_faktur', ['no_faktur_konsinyasi' => trim((string)$noFakturKonsinyasi)])->row_array();
    }
}

