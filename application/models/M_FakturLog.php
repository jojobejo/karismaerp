<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_FakturLog.php
 * Model untuk mencatat setiap aktivitas Admin SC / Sales Counter pada SO & Faktur Penjualan.
 */
class M_FakturLog extends CI_Model
{
    const TABLE = 'tbso_faktur_log';

    public function __construct()
    {
        parent::__construct();
        $this->_ensureTableExists();
    }

    /**
     * Memastikan tabel tbso_faktur_log telah terbuat secara otomatis.
     */
    private function _ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::TABLE . "` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `no_so` VARCHAR(50) NULL,
            `no_faktur` VARCHAR(50) NULL,
            `id_faktur` INT NULL,
            `aksi` VARCHAR(50) NOT NULL,
            `keterangan` TEXT NULL,
            `detail_produk` TEXT NULL,
            `dilakukan_oleh` VARCHAR(100) NOT NULL DEFAULT 'system',
            `ip_address` VARCHAR(45) NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_no_so` (`no_so`),
            INDEX `idx_no_faktur` (`no_faktur`),
            INDEX `idx_id_faktur` (`id_faktur`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->db->query($sql);
    }

    /**
     * Catat satu baris activity log Admin SC.
     *
     * @param string|null $no_so
     * @param string|null $no_faktur
     * @param int|null $id_faktur
     * @param string $aksi
     * @param string $keterangan
     * @param string $oleh
     * @param string $detail_produk
     */
    public function log($no_so, $no_faktur = null, $id_faktur = null, $aksi = 'LOG', $keterangan = '', $oleh = 'system', $detail_produk = '')
    {
        return $this->db->insert(self::TABLE, [
            'no_so'          => $no_so          ?: null,
            'no_faktur'      => $no_faktur      ?: null,
            'id_faktur'      => $id_faktur      ? (int)$id_faktur : null,
            'aksi'           => strtoupper($aksi),
            'keterangan'     => $keterangan     ?: null,
            'detail_produk'  => $detail_produk  ?: null,
            'dilakukan_oleh' => $oleh           ?: 'system',
            'ip_address'     => $this->input->ip_address(),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ambil log berdasarkan ID Faktur atau No Faktur atau No SO.
     */
    public function get_by_faktur($id_faktur = null, $no_faktur = null, $no_so = null, $limit = 50)
    {
        $id_faktur = !empty($id_faktur) ? (int)$id_faktur : null;
        if (empty($id_faktur) && empty($no_faktur) && empty($no_so)) {
            return [];
        }

        $this->db->group_start();
        if (!empty($id_faktur)) {
            $this->db->or_where('id_faktur', $id_faktur);
        }
        if (!empty($no_faktur)) {
            $this->db->or_where('no_faktur', $no_faktur);
        }
        if (!empty($no_so)) {
            $this->db->or_where('no_so', $no_so);
        }
        $this->db->group_end();

        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get(self::TABLE)
            ->result_array();
    }

    /**
     * Filter log dengan berbagai kriteria (misal search keyword, tanggal, dll).
     */
    public function get_filtered($filter = [], $limit = 100, $offset = 0)
    {
        $this->_applyFilter($filter);
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get(self::TABLE)
            ->result_array();
    }

    public function count_filtered($filter = [])
    {
        $this->_applyFilter($filter);
        return $this->db->count_all_results(self::TABLE);
    }

    private function _applyFilter($filter)
    {
        if (!empty($filter['no_so']))     $this->db->where('no_so', $filter['no_so']);
        if (!empty($filter['no_faktur'])) $this->db->where('no_faktur', $filter['no_faktur']);
        if (!empty($filter['id_faktur'])) $this->db->where('id_faktur', (int)$filter['id_faktur']);
        if (!empty($filter['aksi']))      $this->db->where('aksi', strtoupper($filter['aksi']));
        if (!empty($filter['tanggal']))   $this->db->where('DATE(created_at)', $filter['tanggal']);
        if (!empty($filter['keyword'])) {
            $this->db->group_start();
            $this->db->like('keterangan',     $filter['keyword']);
            $this->db->or_like('dilakukan_oleh', $filter['keyword']);
            $this->db->or_like('no_faktur',   $filter['keyword']);
            $this->db->or_like('no_so',       $filter['keyword']);
            $this->db->group_end();
        }
    }
}
