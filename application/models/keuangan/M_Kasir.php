<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model Kasir
 * Mengelola data transaksi kasir, saldo kasir, dan pilihan kategori.
 */
class M_Kasir extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_check_tables();
    }

    /**
     * Memastikan tabel pendukung kasir otomatis dibuat jika belum ada di database
     */
    private function _check_tables()
    {
        if (!$this->db->table_exists('tbkeu_kasir_saldo')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tbkeu_kasir_saldo` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `id_akun` int(11) NOT NULL,
                  `kode_akun` varchar(50) DEFAULT NULL,
                  `nama_akun` varchar(150) DEFAULT NULL,
                  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
                  `created_by` varchar(100) DEFAULT NULL,
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if (!$this->db->table_exists('tbkeu_transaksi_kasir')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tbkeu_transaksi_kasir` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `no_transaksi` varchar(50) NOT NULL,
                  `tanggal` date NOT NULL,
                  `jenis_transaksi` varchar(20) NOT NULL,
                  `pilihan` varchar(150) DEFAULT NULL,
                  `nominal` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `keterangan` text DEFAULT NULL,
                  `id_user` int(11) DEFAULT NULL,
                  `id_saldo_kasir` int(11) DEFAULT NULL,
                  `nama_user` varchar(150) DEFAULT NULL,
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if (!$this->db->table_exists('tbkeu_kasir_pilihan')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tbkeu_kasir_pilihan` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `nama_pilihan` varchar(150) NOT NULL,
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $default_pilihan = [
                'Setor Bank',
                'Tarik Tunai',
                'Operasional Kasir',
                'Pengeluaran Kecil',
                'Penerimaan Kasir',
                'Lain-lain'
            ];
            foreach ($default_pilihan as $p) {
                $this->db->insert('tbkeu_kasir_pilihan', [
                    'nama_pilihan' => $p,
                    'created_at'   => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    // =====================================================
    // AKUN KAS
    // =====================================================

    /** Ambil semua akun dengan tipe_kontrol KAS */
    public function get_akun_kas()
    {
        return $this->db
            ->select('id_akun, kode_akun, nama_akun, tipe_kontrol')
            ->where('tipe_kontrol', 'KAS')
            ->where('tipe_akun', 'POSTING')
            ->where('is_active', 1)
            ->order_by('kode_akun', 'ASC')
            ->get('tbkeu_akun')
            ->result();
    }

    /** Ambil akun berdasarkan id_akun */
    public function get_akun_by_id($id_akun)
    {
        return $this->db
            ->select('id_akun, kode_akun, nama_akun')
            ->where('id_akun', (int)$id_akun)
            ->get('tbkeu_akun')
            ->row();
    }

    /**
     * Hitung saldo akun dari tbkeu_jurnal_detail (debit - kredit = saldo normal debit)
     * Untuk akun KAS: saldo = saldo_awal_debit - saldo_awal_kredit + total_debit_jurnal - total_kredit_jurnal
     */
    public function hitung_saldo_akun($id_akun)
    {
        $id_akun = (int)$id_akun;

        // Saldo awal
        $saldo_awal = $this->db->query("
            SELECT
                COALESCE(SUM(debit), 0) - COALESCE(SUM(kredit), 0) AS saldo_awal
            FROM tbkeu_saldo_awal_akun
            WHERE id_akun = ?
        ", [$id_akun])->row();

        $saldo_jurnal_debit  = 0;
        $saldo_jurnal_kredit = 0;

        // Cek apakah tabel jurnal_detail ada
        if ($this->db->table_exists('tbkeu_jurnal_detail')) {
            $jurnal = $this->db->query("
                SELECT
                    COALESCE(SUM(CASE WHEN jd.debit > 0 THEN jd.debit ELSE 0 END), 0) AS total_debit,
                    COALESCE(SUM(CASE WHEN jd.kredit > 0 THEN jd.kredit ELSE 0 END), 0) AS total_kredit
                FROM tbkeu_jurnal_detail jd
                INNER JOIN tbkeu_jurnal j ON j.id_jurnal = jd.id_jurnal
                WHERE jd.id_akun = ?
                  AND j.status = 'POSTED'
            ", [$id_akun])->row();

            $saldo_jurnal_debit  = (float)($jurnal->total_debit ?? 0);
            $saldo_jurnal_kredit = (float)($jurnal->total_kredit ?? 0);
        }

        $saldo_awal_val = (float)($saldo_awal->saldo_awal ?? 0);
        return $saldo_awal_val + $saldo_jurnal_debit - $saldo_jurnal_kredit;
    }

    // =====================================================
    // SALDO KASIR (konfigurasi akun)
    // =====================================================

    /** Ambil konfigurasi saldo kasir yang aktif */
    public function get_saldo_kasir_aktif()
    {
        return $this->db
            ->where('is_aktif', 1)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tbkeu_kasir_saldo')
            ->row();
    }

    /** Set akun untuk saldo kasir (nonaktifkan yang lama) */
    public function set_saldo_kasir($id_akun, $kode_akun, $nama_akun, $created_by = null)
    {
        $this->db->trans_begin();
        try {
            // Nonaktifkan semua saldo kasir yang lama
            $this->db->update('tbkeu_kasir_saldo', ['is_aktif' => 0]);

            // Cek apakah akun sudah pernah didaftarkan
            $exists = $this->db->where('id_akun', $id_akun)->count_all_results('tbkeu_kasir_saldo');

            if ($exists > 0) {
                $this->db->where('id_akun', $id_akun)->update('tbkeu_kasir_saldo', [
                    'is_aktif'   => 1,
                    'kode_akun'  => $kode_akun,
                    'nama_akun'  => $nama_akun,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->db->insert('tbkeu_kasir_saldo', [
                    'id_akun'    => $id_akun,
                    'kode_akun'  => $kode_akun,
                    'nama_akun'  => $nama_akun,
                    'is_aktif'   => 1,
                    'created_by' => $created_by,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            $this->db->trans_commit();
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return false;
        }
    }

    // =====================================================
    // TRANSAKSI KASIR
    // =====================================================

    /** Generate nomor transaksi unik */
    public function generate_no_transaksi($prefix, $tanggal)
    {
        $tgl_str = date('ymd', strtotime($tanggal));
        $like    = $prefix . $tgl_str . '-%';

        $last = $this->db->query("
            SELECT no_transaksi FROM tbkeu_transaksi_kasir
            WHERE no_transaksi LIKE ?
            ORDER BY id DESC LIMIT 1
        ", [$like])->row();

        $urut = 1;
        if ($last) {
            $parts = explode('-', $last->no_transaksi);
            $urut  = (int)end($parts) + 1;
        }

        return $prefix . $tgl_str . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    /** Simpan transaksi baru */
    public function simpan_transaksi($data)
    {
        return $this->db->insert('tbkeu_transaksi_kasir', $data);
    }

    /** Hapus transaksi */
    public function hapus_transaksi($id)
    {
        return $this->db->where('id', (int)$id)->delete('tbkeu_transaksi_kasir');
    }

    /**
     * Ambil transaksi berdasarkan bulan (format: Y-m)
     * Opsional filter jenis: kas_masuk | kas_keluar | '' (semua)
     */
    public function get_transaksi_bulan($bulan, $jenis = '')
    {
        // Validasi format bulan Y-m
        if (!preg_match('/^\d{4}-\d{2}$/', (string)$bulan)) {
            $bulan = date('Y-m');
        }

        $sql = "
            SELECT t.*,
                DATE_FORMAT(t.tanggal, '%d/%m/%Y') AS tanggal_fmt,
                DATE_FORMAT(t.created_at, '%H:%i') AS jam_input
            FROM tbkeu_transaksi_kasir t
            WHERE DATE_FORMAT(t.tanggal, '%Y-%m') = ?
        ";
        $params = [$bulan];

        if (in_array($jenis, ['kas_masuk', 'kas_keluar'], true)) {
            $sql .= " AND t.jenis_transaksi = ?";
            $params[] = $jenis;
        }

        $sql .= " ORDER BY t.tanggal DESC, t.id DESC";

        return $this->db->query($sql, $params)->result_array();
    }

    /** Hitung total kas masuk atau kas keluar pada bulan tertentu */
    public function total_bulan($bulan, $jenis)
    {
        $row = $this->db->query("
            SELECT COALESCE(SUM(nominal), 0) AS total
            FROM tbkeu_transaksi_kasir
            WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
              AND jenis_transaksi = ?
        ", [$bulan, $jenis])->row();

        return (float)($row->total ?? 0);
    }

    // =====================================================
    // PILIHAN KATEGORI
    // =====================================================

    /** Ambil semua pilihan kategori transaksi */
    public function get_all_pilihan()
    {
        return $this->db
            ->order_by('nama_pilihan', 'ASC')
            ->get('tbkeu_kasir_pilihan')
            ->result_array();
    }
}
