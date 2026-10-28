<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_TestMarket extends CI_Model
{
    const WAREHOUSE_NAME = 'Gdg. Test Market di Kios';
    const WAREHOUSE_KIOS_NAME = 'Gdg. Test Market di Kios';
    const TRANSIT_WAREHOUSE_NAME = 'Gudang Test Market';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema();
    }

    public function ensure_schema()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tb_test_market_position` (
                `id_position` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_faktur` INT NOT NULL,
                `id_faktur_detail` INT NOT NULL,
                `id_so` INT DEFAULT NULL,
                `no_faktur` VARCHAR(30) NOT NULL,
                `no_so` VARCHAR(30) DEFAULT NULL,
                `kd_customer` VARCHAR(50) NOT NULL,
                `customer_name` VARCHAR(150) DEFAULT NULL,
                `kd_barang` VARCHAR(50) NOT NULL,
                `nama_barang` VARCHAR(200) DEFAULT NULL,
                `no_lot` VARCHAR(50) DEFAULT NULL,
                `expired_date` DATE DEFAULT NULL,
                `satuan` VARCHAR(20) DEFAULT NULL,
                `gudang_asal_id` VARCHAR(30) DEFAULT NULL,
                `gudang_test_market_id` INT NOT NULL,
                `qty_titip` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
                `qty_terjual` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
                `qty_sisa` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
                `harga_jual` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                `harga_pokok` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                `status` ENUM('DI_KIOS','SEBAGIAN','HABIS','DIBATALKAN') NOT NULL DEFAULT 'DI_KIOS',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_position`),
                UNIQUE KEY `uk_test_market_faktur_detail` (`id_faktur_detail`),
                KEY `idx_test_market_customer` (`kd_customer`, `status`),
                KEY `idx_test_market_barang` (`kd_barang`, `status`),
                KEY `idx_test_market_faktur` (`id_faktur`, `no_faktur`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tb_test_market_realisasi` (
                `id_realisasi` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_position` BIGINT UNSIGNED NOT NULL,
                `id_pembayaran` INT UNSIGNED NOT NULL,
                `id_faktur` INT NOT NULL,
                `id_faktur_detail` INT NOT NULL,
                `no_faktur` VARCHAR(30) NOT NULL,
                `kd_customer` VARCHAR(50) NOT NULL,
                `kd_barang` VARCHAR(50) NOT NULL,
                `qty` DECIMAL(15,3) NOT NULL,
                `harga_satuan` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                `subtotal` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                `tanggal_realisasi` DATE NOT NULL,
                `status` ENUM('POSTED','DIBATALKAN') NOT NULL DEFAULT 'POSTED',
                `created_by` VARCHAR(100) DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_realisasi`),
                UNIQUE KEY `uk_test_market_pembayaran` (`id_pembayaran`),
                KEY `idx_test_market_realisasi_position` (`id_position`, `status`),
                KEY `idx_test_market_realisasi_tanggal` (`tanggal_realisasi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        if ($this->db->table_exists('tb_test_market_realisasi') && !$this->db->field_exists('no_faktur_realisasi', 'tb_test_market_realisasi')) {
            $this->db->query("ALTER TABLE `tb_test_market_realisasi` ADD COLUMN `no_faktur_realisasi` VARCHAR(50) NULL DEFAULT NULL AFTER `no_faktur`, ADD INDEX `idx_no_faktur_realisasi` (`no_faktur_realisasi`)");
        }

        $this->_align_table_collation('tb_test_market_position', 'tbso_faktur_detail');
        $this->_align_table_collation('tb_test_market_realisasi', 'tbkeu_pembayaran_faktur');
        $this->get_transit_warehouse_id();
        return $this->get_warehouse_id();
    }

    public function get_transit_warehouse_id()
    {
        $row = $this->db->group_start()
            ->where('nama_gudang', self::TRANSIT_WAREHOUSE_NAME)
            ->or_where('nama_gudang', 'Gdg. Test Market')
            ->group_end()
            ->get('tb_gudang')
            ->row_array();

        if ($row) {
            if (empty($row['is_active'])) {
                $this->db->where('id_gudang', (int)$row['id_gudang'])->update('tb_gudang', ['is_active' => 1]);
            }
            return (int)$row['id_gudang'];
        }

        $this->db->insert('tb_gudang', [
            'nama_gudang' => self::TRANSIT_WAREHOUSE_NAME,
            'tipe'        => 'INDUK',
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    public function get_warehouse_id()
    {
        $row = $this->db->where('nama_gudang', self::WAREHOUSE_NAME)->get('tb_gudang')->row_array();
        if ($row) {
            if (empty($row['is_active'])) {
                $this->db->where('id_gudang', (int)$row['id_gudang'])->update('tb_gudang', ['is_active' => 1]);
            }
            return (int)$row['id_gudang'];
        }

        $this->db->insert('tb_gudang', [
            'nama_gudang' => self::WAREHOUSE_NAME,
            'tipe'        => 'INDUK',
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    public function is_test_market_faktur(array $faktur)
    {
        return !empty($faktur['is_test_market'])
            || strtoupper(substr((string)($faktur['no_faktur'] ?? ''), 0, 1)) === 'T';
    }

    public function register_invoice($idFaktur)
    {
        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => (int)$idFaktur])->row_array();
        if (!$faktur || !$this->is_test_market_faktur($faktur)) {
            return true;
        }

        $warehouseId = $this->get_warehouse_id();
        $items = $this->db->order_by('id', 'ASC')->get_where('tbso_faktur_detail', ['id_faktur' => (int)$idFaktur])->result_array();

        foreach ($items as $item) {
            $existing = $this->db->get_where('tb_test_market_position', ['id_faktur_detail' => (int)$item['id']])->row_array();
            if ($existing) {
                continue;
            }

            $qty = (float)$item['qty'];
            $hargaJual = $qty > 0 ? round((float)$item['total_harga'] / $qty, 2) : (float)$item['hrg_satuan'];
            $position = [
                'id_faktur'             => (int)$faktur['id_faktur'],
                'id_faktur_detail'      => (int)$item['id'],
                'id_so'                 => !empty($faktur['id_so']) ? (int)$faktur['id_so'] : null,
                'no_faktur'             => $faktur['no_faktur'],
                'no_so'                 => $faktur['no_so'] ?? null,
                'kd_customer'           => $faktur['kd_customer'],
                'customer_name'         => $faktur['customer_name'] ?? null,
                'kd_barang'             => $item['kd_barang'],
                'nama_barang'           => $item['nama_barang'] ?? null,
                'no_lot'                => $item['no_lot'] ?? null,
                'expired_date'          => $item['expired_date'] ?? null,
                'satuan'                => $item['satuan'] ?? null,
                'gudang_asal_id'        => $item['gudang_id'] ?? $faktur['gudang_id'] ?? null,
                'gudang_test_market_id' => $warehouseId,
                'qty_titip'             => $qty,
                'qty_terjual'           => 0,
                'qty_sisa'              => $qty,
                'harga_jual'            => $hargaJual,
                'harga_pokok'           => (float)($item['hrg_pokok'] ?? 0),
                'status'                => $qty > 0 ? 'DI_KIOS' : 'HABIS',
                'created_at'            => $faktur['create_at'] ?? date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('tb_test_market_position', $position);

            if (!$this->_add_virtual_stock($item, $warehouseId, $qty, $faktur['no_faktur'], 'TEST MARKET TITIP')) {
                return false;
            }
        }

        return true;
    }

    public function sync_all_invoices()
    {
        $this->db->select('id_faktur');
        $this->db->group_start()
            ->where('is_test_market', 1)
            ->or_like('no_faktur', 'T', 'after')
            ->group_end();
        $rows = $this->db->get('tbso_faktur_penjualan')->result_array();
        foreach ($rows as $row) {
            if (!$this->register_invoice((int)$row['id_faktur'])) {
                return false;
            }
        }
        return true;
    }

    public function get_payment_items($idFaktur)
    {
        $this->register_invoice((int)$idFaktur);
        return $this->db
            ->select('p.*, d.pajak, d.disc')
            ->from('tb_test_market_position p')
            ->join('tbso_faktur_detail d', 'd.id = p.id_faktur_detail', 'left')
            ->where('p.id_faktur', (int)$idFaktur)
            ->where('p.status !=', 'DIBATALKAN')
            ->order_by('p.id_position', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_position($idFakturDetail, $idFaktur = null)
    {
        $this->db->where('id_faktur_detail', (int)$idFakturDetail);
        if ($idFaktur !== null) {
            $this->db->where('id_faktur', (int)$idFaktur);
        }
        return $this->db->get('tb_test_market_position')->row_array();
    }

    public function realize_payment($idPembayaran, array $payment)
    {
        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => (int)($payment['id_faktur'] ?? 0)])->row_array();
        if (!$faktur || !$this->is_test_market_faktur($faktur)) {
            return true;
        }

        $this->register_invoice((int)$faktur['id_faktur']);
        $idDetail = (int)($payment['id_faktur_detail'] ?? 0);
        $qty = (float)($payment['qty_realisasi'] ?? $payment['qty_konsinyasi'] ?? 0);
        $position = $this->get_position($idDetail, (int)$faktur['id_faktur']);
        if (!$position || $qty <= 0) {
            return false;
        }

        $existing = $this->db->get_where('tb_test_market_realisasi', ['id_pembayaran' => (int)$idPembayaran])->row_array();
        if ($existing && $existing['status'] === 'POSTED') {
            return true;
        }
        if ($qty > (float)$position['qty_sisa'] + 0.0001) {
            return false;
        }

        $warehouseId = (int)$position['gudang_test_market_id'];
        $stockItem = [
            'kd_barang'    => $position['kd_barang'],
            'no_lot'       => $position['no_lot'],
            'expired_date' => $position['expired_date'],
        ];
        if (!$this->_subtract_virtual_stock($stockItem, $warehouseId, $qty, 'TM-PAY-' . (int)$idPembayaran, 'TEST MARKET TERJUAL')) {
            return false;
        }

        $qtyTerjual = (float)$position['qty_terjual'] + $qty;
        $qtySisa = max(0, (float)$position['qty_titip'] - $qtyTerjual);
        $this->db->where('id_position', (int)$position['id_position'])->update('tb_test_market_position', [
            'qty_terjual' => $qtyTerjual,
            'qty_sisa'    => $qtySisa,
            'status'      => $qtySisa <= 0.0001 ? 'HABIS' : 'SEBAGIAN',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $subtotal = (float)($payment['jumlah_pembayaran'] ?? 0) + (float)($payment['jumlah_diskon'] ?? 0);
        $payload = [
            'id_position'         => (int)$position['id_position'],
            'id_pembayaran'       => (int)$idPembayaran,
            'id_faktur'           => (int)$faktur['id_faktur'],
            'id_faktur_detail'    => $idDetail,
            'no_faktur'           => $faktur['no_faktur'],
            'no_faktur_realisasi' => !empty($payment['no_faktur_konsinyasi']) ? $payment['no_faktur_konsinyasi'] : null,
            'kd_customer'         => $faktur['kd_customer'],
            'kd_barang'           => $position['kd_barang'],
            'qty'                 => $qty,
            'harga_satuan'        => $qty > 0 ? round($subtotal / $qty, 2) : 0,
            'subtotal'            => $subtotal,
            'tanggal_realisasi'   => $payment['tanggal_pembayaran'] ?? date('Y-m-d'),
            'status'              => 'POSTED',
            'created_by'          => $payment['create_by'] ?? 'system',
            'updated_at'          => date('Y-m-d H:i:s'),
        ];
        if ($existing) {
            $this->db->where('id_realisasi', (int)$existing['id_realisasi'])->update('tb_test_market_realisasi', $payload);
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tb_test_market_realisasi', $payload);
        }
        return true;
    }

    public function reverse_payment($idPembayaran)
    {
        $realization = $this->db->get_where('tb_test_market_realisasi', [
            'id_pembayaran' => (int)$idPembayaran,
            'status'        => 'POSTED',
        ])->row_array();
        if (!$realization) {
            return true;
        }

        $position = $this->db->get_where('tb_test_market_position', ['id_position' => (int)$realization['id_position']])->row_array();
        if (!$position) {
            return false;
        }

        $qty = (float)$realization['qty'];
        $item = [
            'kd_barang'    => $position['kd_barang'],
            'no_lot'       => $position['no_lot'],
            'expired_date' => $position['expired_date'],
        ];
        if (!$this->_add_virtual_stock($item, (int)$position['gudang_test_market_id'], $qty, 'TM-PAY-' . (int)$idPembayaran, 'TEST MARKET BATAL JUAL')) {
            return false;
        }

        $qtyTerjual = max(0, (float)$position['qty_terjual'] - $qty);
        $qtySisa = max(0, (float)$position['qty_titip'] - $qtyTerjual);
        $this->db->where('id_position', (int)$position['id_position'])->update('tb_test_market_position', [
            'qty_terjual' => $qtyTerjual,
            'qty_sisa'    => $qtySisa,
            'status'      => $qtyTerjual > 0 ? 'SEBAGIAN' : 'DI_KIOS',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('id_realisasi', (int)$realization['id_realisasi'])->update('tb_test_market_realisasi', [
            'status'     => 'DIBATALKAN',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function get_summary(array $filters = [])
    {
        $this->_apply_filters($filters);
        return $this->db
            ->select('COUNT(DISTINCT p.id_faktur) AS total_faktur, COUNT(DISTINCT p.kd_customer) AS total_kios, COALESCE(SUM(p.qty_titip),0) AS qty_titip, COALESCE(SUM(p.qty_terjual),0) AS qty_terjual, COALESCE(SUM(p.qty_sisa),0) AS qty_sisa, COALESCE(SUM(p.qty_terjual * p.harga_jual),0) AS nilai_terjual, COALESCE(SUM(p.qty_sisa * p.harga_jual),0) AS nilai_sisa', false)
            ->get('tb_test_market_position p')
            ->row_array();
    }

    public function get_positions(array $filters = [])
    {
        $this->_apply_filters($filters);
        return $this->db
            ->select('p.*, f.tanggal_faktur')
            ->from('tb_test_market_position p')
            ->join('tbso_faktur_penjualan f', 'f.id_faktur = p.id_faktur', 'left')
            ->order_by('p.qty_sisa > 0', 'DESC', false)
            ->order_by('f.tanggal_faktur', 'DESC')
            ->order_by('p.id_position', 'DESC')
            ->get()
            ->result_array();
    }

    public function count_grouped_items(array $filters = [])
    {
        $this->_apply_filters($filters);
        $row = $this->db
            ->select('COUNT(DISTINCT p.kd_barang) AS total', false)
            ->from('tb_test_market_position p')
            ->get()
            ->row_array();
        return (int)($row['total'] ?? 0);
    }

    public function get_grouped_items(array $filters = [], $limit = 20, $offset = 0)
    {
        $this->_apply_filters($filters);
        return $this->db
            ->select('p.kd_barang, MAX(COALESCE(NULLIF(p.nama_barang, \'\'), p.kd_barang)) AS nama_barang, MAX(p.satuan) AS satuan, COALESCE(SUM(p.qty_titip), 0) AS qty_titip, COALESCE(SUM(p.qty_terjual), 0) AS qty_terjual, COALESCE(SUM(p.qty_sisa), 0) AS qty_sisa, COUNT(DISTINCT p.kd_customer) AS total_kios, COUNT(DISTINCT p.id_faktur) AS total_faktur', false)
            ->from('tb_test_market_position p')
            ->group_by('p.kd_barang')
            ->order_by('nama_barang', 'ASC')
            ->limit(max(1, (int)$limit), max(0, (int)$offset))
            ->get()
            ->result_array();
    }

    public function get_recent_realizations(array $filters = [], $limit = 100, $offset = 0)
    {
        $this->db->select('r.*, p.customer_name, p.nama_barang, p.satuan, pay.metode_pembayaran, pay.create_by');
        $this->db->from('tb_test_market_realisasi r');
        $this->db->join('tb_test_market_position p', 'p.id_position = r.id_position', 'left');
        $this->db->join('tbkeu_pembayaran_faktur pay', 'pay.id_pembayaran = r.id_pembayaran', 'left');
        $this->_apply_realization_filters($filters);
        return $this->db->order_by('r.tanggal_realisasi', 'DESC')->order_by('r.id_realisasi', 'DESC')->limit(max(1, (int)$limit), max(0, (int)$offset))->get()->result_array();
    }

    public function count_realizations(array $filters = [])
    {
        $this->db->from('tb_test_market_realisasi r');
        $this->db->join('tb_test_market_position p', 'p.id_position = r.id_position', 'left');
        $this->_apply_realization_filters($filters);
        return (int)$this->db->count_all_results();
    }

    public function get_customer_options()
    {
        return $this->db->select('kd_customer, MAX(customer_name) AS customer_name', false)
            ->from('tb_test_market_position')
            ->group_by('kd_customer')
            ->order_by('customer_name', 'ASC')
            ->get()
            ->result_array();
    }

    private function _apply_filters(array $filters)
    {
        $this->db->where('p.status !=', 'DIBATALKAN');
        if (!empty($filters['kd_barang'])) {
            $this->db->where('p.kd_barang', $filters['kd_barang']);
        }
        if (!empty($filters['kd_customer'])) {
            $this->db->where('p.kd_customer', $filters['kd_customer']);
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'tersedia') {
                $this->db->where('p.qty_sisa >', 0);
            } elseif ($filters['status'] === 'habis') {
                $this->db->where('p.qty_sisa <=', 0);
            }
        }
        if (!empty($filters['keyword'])) {
            $this->db->group_start()
                ->like('p.no_faktur', $filters['keyword'])
                ->or_like('p.no_so', $filters['keyword'])
                ->or_like('p.customer_name', $filters['keyword'])
                ->or_like('p.nama_barang', $filters['keyword'])
                ->or_like('p.kd_barang', $filters['keyword'])
                ->group_end();
        }
    }

    private function _apply_realization_filters(array $filters)
    {
        $this->db->where('r.status', 'POSTED');
        if (!empty($filters['kd_barang'])) {
            $this->db->where('r.kd_barang', $filters['kd_barang']);
        }
        if (!empty($filters['kd_customer'])) {
            $this->db->where('r.kd_customer', $filters['kd_customer']);
        }
        if (!empty($filters['keyword'])) {
            $this->db->group_start()
                ->like('r.no_faktur', $filters['keyword'])
                ->or_like('p.customer_name', $filters['keyword'])
                ->or_like('p.nama_barang', $filters['keyword'])
                ->or_like('r.kd_barang', $filters['keyword'])
                ->group_end();
        }
    }

    private function _align_table_collation($targetTable, $sourceTable)
    {
        $source = $this->db->select('TABLE_COLLATION')
            ->where('TABLE_SCHEMA', $this->db->database)
            ->where('TABLE_NAME', $sourceTable)
            ->get('information_schema.TABLES')
            ->row_array();
        $target = $this->db->select('TABLE_COLLATION')
            ->where('TABLE_SCHEMA', $this->db->database)
            ->where('TABLE_NAME', $targetTable)
            ->get('information_schema.TABLES')
            ->row_array();
        $collation = (string)($source['TABLE_COLLATION'] ?? '');
        if ($collation === '' || $collation === (string)($target['TABLE_COLLATION'] ?? '') || !preg_match('/^[a-z0-9_]+$/i', $collation)) {
            return;
        }
        $charset = strpos($collation, '_') !== false ? substr($collation, 0, strpos($collation, '_')) : 'utf8mb4';
        $this->db->query("ALTER TABLE `{$targetTable}` CONVERT TO CHARACTER SET {$charset} COLLATE {$collation}");
    }

    private function _stock_batch(array $item, $warehouseId)
    {
        $this->db->where('kd_barang', $item['kd_barang'])->where('gudang_id', (string)$warehouseId);
        if (!empty($item['no_lot'])) {
            $this->db->where('no_lot', $item['no_lot']);
        } else {
            $this->db->group_start()->where('no_lot IS NULL', null, false)->or_where('no_lot', '')->group_end();
        }
        if (!empty($item['expired_date'])) {
            $this->db->where('expired_date', $item['expired_date']);
        } else {
            $this->db->where('expired_date IS NULL', null, false);
        }
        return $this->db->order_by('id', 'ASC')->get('tberp_stock_batch')->row_array();
    }

    private function _add_virtual_stock(array $item, $warehouseId, $qty, $refNo, $refType)
    {
        if ($qty <= 0) {
            return true;
        }
        $batch = $this->_stock_batch($item, $warehouseId);
        if ($batch) {
            $this->db->where('id', (int)$batch['id'])->set('qty_on_hand', 'qty_on_hand + ' . (float)$qty, false)->set('update_at', date('Y-m-d H:i:s'))->update('tberp_stock_batch');
        } else {
            $this->db->insert('tberp_stock_batch', [
                'kd_barang'    => $item['kd_barang'],
                'gudang_id'    => (string)$warehouseId,
                'no_lot'       => $item['no_lot'] ?? null,
                'expired_date' => $item['expired_date'] ?? null,
                'qty_on_hand'  => $qty,
                'qty_reserved' => 0,
                'created_at'   => date('Y-m-d H:i:s'),
                'update_at'    => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->insert('tberp_stock_ledger', [
            'kd_barang'    => $item['kd_barang'],
            'gudang_id'    => (string)$warehouseId,
            'no_lot'       => $item['no_lot'] ?? null,
            'expired_date' => $item['expired_date'] ?? null,
            'qty'          => $qty,
            'tipe'         => 'IN',
            'ref_no'       => $refNo,
            'ref_type'     => $refType,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return $this->db->trans_status();
    }

    private function _subtract_virtual_stock(array $item, $warehouseId, $qty, $refNo, $refType)
    {
        $batch = $this->_stock_batch($item, $warehouseId);
        if (!$batch || (float)$batch['qty_on_hand'] + 0.0001 < $qty) {
            return false;
        }
        $this->db->where('id', (int)$batch['id'])->set('qty_on_hand', 'qty_on_hand - ' . (float)$qty, false)->set('update_at', date('Y-m-d H:i:s'))->update('tberp_stock_batch');
        $this->db->insert('tberp_stock_ledger', [
            'kd_barang'    => $item['kd_barang'],
            'gudang_id'    => (string)$warehouseId,
            'no_lot'       => $item['no_lot'] ?? null,
            'expired_date' => $item['expired_date'] ?? null,
            'qty'          => $qty,
            'tipe'         => 'OUT',
            'ref_no'       => $refNo,
            'ref_type'     => $refType,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return $this->db->trans_status();
    }

    /**
     * Memutasikan stok barang dari Gudang Induk ke Gudang Test Market ketika SO dibuat dan direkam (Open).
     *
     * @param int|string $id_so
     * @param string $user
     * @return bool
     */
    public function transfer_so_to_test_market($id_so, $user = 'system')
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => (int)$id_so])->row_array();
        if (!$so || empty($so['is_test_market'])) {
            return true;
        }

        $transitWarehouseId = $this->get_transit_warehouse_id();

        // Cek apakah mutasi untuk SO ini sudah pernah dicatat di stock ledger
        $alreadyMutated = $this->db
            ->where('ref_no', $so['no_so'])
            ->where('ref_type', 'MUTASI TEST MARKET')
            ->count_all_results('tberp_stock_ledger');

        if ($alreadyMutated > 0 && (int)$so['gudang_id'] === (int)$transitWarehouseId) {
            return true;
        }

        $sourceGudangId = (int)$so['gudang_id'];
        if ($sourceGudangId <= 0 || $sourceGudangId === (int)$transitWarehouseId) {
            $sourceGudangId = 2; // Default Gdg. Induk
        }

        $details = $this->db->get_where('tbso_sales_order_detail', ['id_so' => (int)$id_so])->result_array();
        if (empty($details)) {
            return true;
        }

        foreach ($details as $d) {
            $qty = (float)$d['qty'];
            if ($qty <= 0) {
                continue;
            }

            $item = [
                'kd_barang'    => $d['kd_barang'],
                'no_lot'       => !empty($d['no_lot']) ? $d['no_lot'] : null,
                'expired_date' => !empty($d['expired_date']) ? $d['expired_date'] : null,
            ];

            // 1. Kurangi stok fisik & reservasi di Gudang Asal (Gudang Induk)
            $batchAsal = $this->_stock_batch($item, $sourceGudangId);
            if ($batchAsal) {
                $this->db->where('id', (int)$batchAsal['id'])
                    ->set('qty_on_hand', 'GREATEST(0, qty_on_hand - ' . $qty . ')', false)
                    ->set('qty_reserved', 'GREATEST(0, qty_reserved - ' . $qty . ')', false)
                    ->set('update_at', date('Y-m-d H:i:s'))
                    ->update('tberp_stock_batch');
            }

            // Ledger OUT dari Gudang Asal
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $item['kd_barang'],
                'gudang_id'    => (string)$sourceGudangId,
                'no_lot'       => $item['no_lot'],
                'expired_date' => $item['expired_date'],
                'qty'          => $qty,
                'tipe'         => 'OUT',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'MUTASI TEST MARKET',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            // 2. Tambah stok fisik & reservasi di Gudang Test Market (Transit)
            $batchTransit = $this->_stock_batch($item, $transitWarehouseId);
            if ($batchTransit) {
                $this->db->where('id', (int)$batchTransit['id'])
                    ->set('qty_on_hand', 'qty_on_hand + ' . $qty, false)
                    ->set('qty_reserved', 'qty_reserved + ' . $qty, false)
                    ->set('update_at', date('Y-m-d H:i:s'))
                    ->update('tberp_stock_batch');
            } else {
                $this->db->insert('tberp_stock_batch', [
                    'kd_barang'    => $item['kd_barang'],
                    'gudang_id'    => (string)$transitWarehouseId,
                    'no_lot'       => $item['no_lot'],
                    'expired_date' => $item['expired_date'],
                    'qty_on_hand'  => $qty,
                    'qty_reserved' => $qty,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'update_at'    => date('Y-m-d H:i:s'),
                ]);
            }

            // Ledger IN mutasi masuk ke Gudang Test Market
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $item['kd_barang'],
                'gudang_id'    => (string)$transitWarehouseId,
                'no_lot'       => $item['no_lot'],
                'expired_date' => $item['expired_date'],
                'qty'          => $qty,
                'tipe'         => 'IN',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'MUTASI TEST MARKET',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            // Ledger RESERVE untuk SO pada Gudang Test Market
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $item['kd_barang'],
                'gudang_id'    => (string)$transitWarehouseId,
                'no_lot'       => $item['no_lot'],
                'expired_date' => $item['expired_date'],
                'qty'          => $qty,
                'tipe'         => 'RESERVE',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'SALES_ORDER',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        // 3. Update lokasi gudang SO ke Gudang Test Market
        $this->db->where('id_so', (int)$id_so)->update('tbso_sales_order', [
            'gudang_id' => (string)$transitWarehouseId,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Mengembalikan mutasi barang dari Gudang Test Market ke Gudang Induk jika SO dibatalkan.
     *
     * @param int|string $id_so
     * @param string $user
     * @return bool
     */
    public function reverse_transfer_so_test_market($id_so, $user = 'system')
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => (int)$id_so])->row_array();
        if (!$so || empty($so['is_test_market'])) {
            return true;
        }

        $transitWarehouseId = $this->get_transit_warehouse_id();
        if ((int)$so['gudang_id'] !== (int)$transitWarehouseId) {
            return true;
        }

        $defaultIndukId = 2; // Gudang Induk
        $details = $this->db->get_where('tbso_sales_order_detail', ['id_so' => (int)$id_so])->result_array();

        foreach ($details as $d) {
            $qty = (float)$d['qty'] - (float)($d['qty_faktur'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $item = [
                'kd_barang'    => $d['kd_barang'],
                'no_lot'       => !empty($d['no_lot']) ? $d['no_lot'] : null,
                'expired_date' => !empty($d['expired_date']) ? $d['expired_date'] : null,
            ];

            // 1. Kurangi dari Gudang Test Market
            $batchTransit = $this->_stock_batch($item, $transitWarehouseId);
            if ($batchTransit) {
                $this->db->where('id', (int)$batchTransit['id'])
                    ->set('qty_on_hand', 'GREATEST(0, qty_on_hand - ' . $qty . ')', false)
                    ->set('qty_reserved', 'GREATEST(0, qty_reserved - ' . $qty . ')', false)
                    ->set('update_at', date('Y-m-d H:i:s'))
                    ->update('tberp_stock_batch');
            }

            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $item['kd_barang'],
                'gudang_id'    => (string)$transitWarehouseId,
                'no_lot'       => $item['no_lot'],
                'expired_date' => $item['expired_date'],
                'qty'          => $qty,
                'tipe'         => 'OUT',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'MUTASI TEST MARKET BATAL',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $item['kd_barang'],
                'gudang_id'    => (string)$transitWarehouseId,
                'no_lot'       => $item['no_lot'],
                'expired_date' => $item['expired_date'],
                'qty'          => $qty,
                'tipe'         => 'RELEASE',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'SALES_ORDER_CANCEL',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            // 2. Kembalikan ke Gudang Induk
            $batchInduk = $this->_stock_batch($item, $defaultIndukId);
            if ($batchInduk) {
                $this->db->where('id', (int)$batchInduk['id'])
                    ->set('qty_on_hand', 'qty_on_hand + ' . $qty, false)
                    ->set('update_at', date('Y-m-d H:i:s'))
                    ->update('tberp_stock_batch');
            } else {
                $this->db->insert('tberp_stock_batch', [
                    'kd_barang'    => $item['kd_barang'],
                    'gudang_id'    => (string)$defaultIndukId,
                    'no_lot'       => $item['no_lot'],
                    'expired_date' => $item['expired_date'],
                    'qty_on_hand'  => $qty,
                    'qty_reserved' => 0,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'update_at'    => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $item['kd_barang'],
                'gudang_id'    => (string)$defaultIndukId,
                'no_lot'       => $item['no_lot'],
                'expired_date' => $item['expired_date'],
                'qty'          => $qty,
                'tipe'         => 'IN',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'MUTASI TEST MARKET BATAL',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->where('id_so', (int)$id_so)->update('tbso_sales_order', [
            'gudang_id' => (string)$defaultIndukId,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
