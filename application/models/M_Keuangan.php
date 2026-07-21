<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class M_Keuangan extends CI_Model
{
    private $masterBarangHasKelompokDagang = null;
    private $masterKelompokDagangTableExists = null;
    private $masterBarangFieldExists = [];

    private function master_barang_has_kelompok_dagang()
    {
        if ($this->masterBarangHasKelompokDagang === null) {
            $this->masterBarangHasKelompokDagang = $this->db->field_exists('kelompok_dagang', 'tbpo_barang');
        }

        return $this->masterBarangHasKelompokDagang;
    }

    private function master_kelompok_dagang_table_exists()
    {
        if ($this->masterKelompokDagangTableExists === null) {
            $this->masterKelompokDagangTableExists = $this->db->table_exists('tbkeu_kelompok_dagang');
        }

        return $this->masterKelompokDagangTableExists;
    }

    private function master_barang_field_exists($field)
    {
        if (!isset($this->masterBarangFieldExists[$field])) {
            $this->masterBarangFieldExists[$field] = $this->db->field_exists($field, 'tbpo_barang');
        }

        return $this->masterBarangFieldExists[$field];
    }

    private function master_barang_tf_select($field, $default)
    {
        if ($this->master_barang_field_exists($field)) {
            return "COALESCE(a.`{$field}`, '{$default}') AS {$field}";
        }

        return "'{$default}' AS {$field}";
    }

    private function master_barang_text_select($field, $default = '')
    {
        if ($this->master_barang_field_exists($field)) {
            return "COALESCE(a.`{$field}`, '{$default}') AS {$field}";
        }

        return "'{$default}' AS {$field}";
    }

    // function daily_stock()
    // {
    //     return $this->db->query("SELECT
    //     c.nama_suplier AS nmsuplier,
    //     b.nm_barang AS nmbarang,
    //     a.gudang AS nmgudang,
    //     b.satuan AS satuan,
    //     a.qty AS qty,
    //     b.p AS p,
    //     b.l AS l,
    //     b.t AS t,
    //     b.qty_min AS qtymin
    //     FROM tb_dailystock a
    //     JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
    //     JOIN tb_suplier c ON c.kd_suplier = a.kd_suplier
    //     WHERE a.qty > b.qty_min AND a.gudang = 'Gdg. Rusak'
    //     ")->result();
    // }

    public function get_data_gdg()
    {
        return $this->db->query("SELECT
        COALESCE(x.gdg_rusak,0) + COALESCE(x.gdg_induk,0) + COALESCE(x.gdg_global,0) AS total_data,
        COALESCE(x.gdg_rusak,0) AS rusak,
        COALESCE(x.gdg_induk,0) AS induk,
        COALESCE(x.gdg_global,0) AS global
        FROM
        (
            SELECT
            (SELECT COUNT(b.kd_barang) FROM tb_dailystock b WHERE b.gudang = 'Gdg. Rusak') AS gdg_rusak,
            (SELECT COUNT(c.kd_barang) FROM tb_dailystock c WHERE c.gudang = 'Gdg. Induk') AS gdg_induk,
            (SELECT COUNT(d.kd_barang) FROM tb_dailystock_global d WHERE d.gudang = 'Global') AS gdg_global
            FROM tb_dailystock a
        ) AS x
        LIMIT 1
        ")->result();
    }

    public function insertupdate($data)
    {
        $this->db->insert('tb_stock_status', $data);
    }

    public function countbarang()
    {
        return $this->db->query("SELECT
        COUNT(a.id) AS jumlah
        FROM tb_dailystock a
        ")->result();
    }

    public function generate_update()
    {
        $cd = $this->db->query("SELECT MAX(RIGHT(kd_update,4)) AS kd_max FROM tb_stock_status WHERE DATE(create_at)=CURDATE()");
        $kd = "";
        if ($cd->num_rows() > 0) {
            foreach ($cd->result() as $k) {
                $tmp = ((int)$k->kd_max) + 1;
                $kd = sprintf("%04s", $tmp);
            }
        } else {
            $kd = "0001";
        }
        date_default_timezone_set('Asia/Jakarta');
        return 'UPD' . date('dmy') . $kd;
    }
    public function get_last_update($id)
    {
        return $this->db->query("SELECT 
        a.kd_update AS kd,
        a.last_update AS lastupdated,
        a.gudangid AS gdgid
        FROM tb_stock_status a 
        WHERE a.gudangid = '$id'
        ORDER BY a.id DESC LIMIT 1
        ")->result();
    }

    public function get_updated_upload()
    {
        return $this->db->query("SELECT 
        a.kd_update AS kd,
        a.last_update AS lastupdated
        FROM tb_stock_status a 
        ORDER BY a.id DESC LIMIT 1
        ")->result();
    }

    public function deleteupdateed($kd)
    {
        $this->db->where('kd_update', $kd);
        return $this->db->delete('tb_stock_status');
    }
    public function truncateitm($id)
    {
        if ($id == '1') {
            $this->db->empty_table('tb_dailystock_global');
        } elseif ($id == '2') {
            $gdg    = 'Gdg. Induk';
            $this->db->where('gudang', $gdg);
            return $this->db->delete('tb_dailystock');
        } elseif ($id == '3') {
            $gdg    = 'Gdg. Rusak';
            $this->db->where('gudang', $gdg);
            return $this->db->delete('tb_dailystock');
        } elseif ($id == '4') {
            $gdg    = 'Gdg. Rusak';
            $this->db->where('gudang', $gdg);
            return $this->db->delete('tb_dailystock');
        } elseif ($id == '5') {
            $this->db->empty_table('tb_po_pending');
        } elseif ($id == '6') {
            $this->db->empty_table('tb_pre_do');
        }
    }
    public function insert_batch($data)
    {
        $this->db->insert_batch('tb_dailystock', $data);
    }
    public function insert_batch_lot($data)
    {
        $this->db->insert_batch('tb_qty_lot', $data);
    }
    public function insert_po_pending($data)
    {
        $this->db->insert_batch('tb_po_pending', $data);
    }
    public function batch_global($data)
    {
        $this->db->insert_batch('tb_dailystock_global', $data);
    }
    public function insert_batch_logistik($data)
    {
        $this->db->insert_batch('tb_pre_do', $data);
    }
    public function get_stock_global()
    {
        return $this->db->query("SELECT 
        b.nama_suplier AS nmsuplier, 
        c.nm_barang AS nmbarang, 
        c.satuan AS satuan, 
        a.qty AS qty, 
        c.qty_min AS qty_min,
        round(c.qty_min / (c.p*c.l*c.t)) AS qty_box,
        c.qty_min - ((round(c.qty_min / (c.p*c.l*c.t)) * (c.p*c.l*c.t))) AS qty_pcs
        FROM tb_dailystock_global a
        JOIN tb_suplier b ON b.kd_suplier = a.kd_suplier
        JOIN tb_master_barang c ON c.kode_barang = a.kd_barang
        WHERE a.qty < c.qty_min")->result();
    }
    public function get_stockmin_gdg($gdg)
    {
        return $this->db->query("SELECT
        c.nama_suplier AS nmsuplier,
        b.nm_barang AS nmbarang,
        b.satuan AS satuan,
        a.qty AS qty,
        b.qty_min AS qtymin
        FROM tb_dailystock a
        JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
        JOIN tb_suplier c ON c.kd_suplier = a.kd_suplier
        WHERE a.qty < b.qty_min AND a.gudang = '$gdg'
        ")->result();
    }
    public function get_updated()
    {
        return $this->db->query("SELECT
        a.kd_update AS kdupdate,
        a.gudangid AS gdgid,
        a.gudang,
        a.last_update AS updated
        FROM tb_stock_status a
        GROUP BY a.gudang
        ")->result();
    }
    public function get_stock_by_sup_global($kd)
    {
        return $this->db->query("SELECT
        b.nm_barang AS nmbarang,
        b.satuan AS satuan,
        SUM(a.qty) AS qty,
        FLOOR(SUM(a.qty) / COALESCE(b.p*b.l*b.t)) as qty_box,
        (SUM(a.qty) - (FLOOR(SUM(a.qty) / COALESCE(b.p*b.l*b.t)) * COALESCE(b.p*b.l*b.t))) AS qty_pcs
        FROM tb_dailystock_global a
        JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
        JOIN tb_suplier c ON c.kd_suplier = a.kd_suplier
        WHERE a.kd_suplier = '$kd' AND a.qty > 0
        GROUP BY a.kd_barang
        ORDER BY a.qty DESC
        ")->result();
    }

    public function get_stock_by_sup($kd, $gdg)
    {
        return $this->db->query("SELECT
        b.nm_barang AS nmbarang,
        b.satuan AS satuan,
        SUM(a.qty) AS qty,
        FLOOR(SUM(a.qty) / COALESCE(b.p*b.l*b.t)) as qty_box,
        (SUM(a.qty) - (FLOOR(SUM(a.qty) / COALESCE(b.p*b.l*b.t)) * COALESCE(b.p*b.l*b.t))) AS qty_pcs
        FROM tb_dailystock a
        JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
        JOIN tb_suplier c ON c.kd_suplier = a.kd_suplier
        WHERE a.kd_suplier = '$kd' AND a.gudang = '$gdg'
            ")->result();
    }

    public function get_list_po_pending()
    {
        return $this->db->query("SELECT
            a.nopo AS po,
            a.tanggal as tgl,
            c.nama_suplier as nmsuplier,
            b.nm_barang as nmbarang,
            a.qty_order as qtyorder,
            a.qty_order_success as qtydone,
            a.qty_kurang as qtykurang
            FROM tb_po_pending a
            JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
            JOIN tb_suplier c ON c.kd_suplier = a.kd_sup
            WHERE a.qty_kurang > 0
        ")->result();
    }

    public function get_list_stock_lot()
    {
        $this->db->select("
        x.kd_system,
        x.kode_barang,
        x.nama_barang,
        x.tot_qty,
        x.dimensi,
        FLOOR(x.tot_qty / x.dimensi) as qty_box,
        (x.tot_qty - (FLOOR(x.tot_qty / x.dimensi) * x.dimensi)) AS qty_pcs");
        $this->db->from("(SELECT 
            a.kd_barang AS kode_barang,
            a.nm_barang AS nama_barang,
    		b.kd_system AS kd_system,
            (SELECT SUM(d.qty) FROM tb_qty_lot d WHERE d.nm_barang = a.nm_barang GROUP BY d.nm_barang) AS tot_qty,
            (b.p * b.l * b.t) AS dimensi
            FROM tb_qty_lot a
            JOIN tb_master_barang b ON b.nm_barang = a.nm_barang
            JOIN tb_suplier c ON c.kd_suplier = a.suplier
            GROUP BY a.nm_barang) AS x", false);

        $query = $this->db->get();
        return $query->result();
    }

    public function get_detail_lot($kd)
    {
        return $this->db->query("SELECT
        x.nmsuplier,
		x.nmbarang,
        x.nolot,
        x.expdate,
        x.qty_lot,
        x.satuan,
        x.nmgudang 
        FROM
        (
            SELECT
            a.no_lot AS nolot,
            a.exp_date AS expdate,
            b.kd_system AS kdsystem,
            (SELECT SUM(c.qty) FROM tb_qty_lot c WHERE c.nm_barang = a.nm_barang AND c.no_lot = a.no_lot AND c.exp_date = a.exp_date GROUP BY c.no_lot , c.exp_date ) AS qty_lot,
            a.unit AS satuan,
            a.gudang AS nmgudang,
            b.nm_barang AS nmbarang,
            d.nama_suplier AS nmsuplier
            FROM tb_qty_lot a
            JOIN tb_master_barang b ON b.nm_barang = a.nm_barang
            JOIN tb_suplier d ON d.kd_suplier = b.kd_suplier
            WHERE b.kd_system = '$kd'
            GROUP BY a.no_lot , a.exp_date
        )AS x
        WHERE x.qty_lot > '0'; 
        ")->result();
    }

    public function getsuplierlot($kd)
    {
        return $this->db->query("SELECT
        x.nmsuplier,
		x.nmbarang,
        x.nolot,
        x.expdate,
        x.qty_lot,
        x.satuan,
        x.nmgudang 
        FROM
        (
            SELECT
            a.no_lot AS nolot,
            a.exp_date AS expdate,
            b.kd_system AS kdsystem,
            (SELECT SUM(c.qty) FROM tb_qty_lot c WHERE c.nm_barang = a.nm_barang AND c.no_lot = a.no_lot AND c.exp_date = a.exp_date GROUP BY c.no_lot , c.exp_date ) AS qty_lot,
            a.unit AS satuan,
            a.gudang AS nmgudang,
            b.nm_barang AS nmbarang,
            d.nama_suplier AS nmsuplier
            FROM tb_qty_lot a
            JOIN tb_master_barang b ON b.nm_barang = a.nm_barang
            JOIN tb_suplier d ON d.kd_suplier = b.kd_suplier
            WHERE b.kd_system = '$kd'
            GROUP BY a.no_lot , a.exp_date
        )AS x
        LIMIT 1
        ")->result();
    }
    public function get_by_faktur_barang($kd_faktur, $kd_barang, $qty, $nolot, $tgl_exp)
    {
        return $this->db
            ->where('kd_faktur', $kd_faktur)
            ->where('kd_barang', $kd_barang)
            ->where('qty', $qty)
            ->where('no_lot', $nolot)
            ->where('tgl_exp', $tgl_exp)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tb_pre_do')
            ->row();
    }

    public function update_by_faktur($kd_faktur, $kd_barang, $data)
    {
        return $this->db
            ->where('kd_faktur', $kd_faktur)
            ->where('kd_barang', $kd_barang)
            ->update('tb_pre_do', $data);
    }

    public function insert($data)
    {
        return $this->db->insert('tb_pre_do', $data);
    }

    public function insert_batch_pre_do($data)
    {
        if (!empty($data)) {
            $this->db->insert_batch('tb_pre_do', $data);
        }
    }

    public function get_pricelist()
    {
        return $this->db->query("SELECT * 
        FROM so_pricelist_barang
        ")->result();
    }

    private function master_barang_base_query($search = '', $kelompokDagang = '')
    {
        $hasKelompokDagangColumn = $this->master_barang_has_kelompok_dagang();
        $hasKelompokDagangMaster = $this->master_kelompok_dagang_table_exists();

        if ($hasKelompokDagangColumn && $hasKelompokDagangMaster) {
            $kelompokDagangSelect = "
                COALESCE(a.kelompok_dagang, '') AS kelompok_dagang,
                COALESCE(kd.DESKRIPSI, a.kelompok_dagang, '') AS kelompok_dagang_label,
            ";
        } elseif ($hasKelompokDagangColumn) {
            $kelompokDagangSelect = "
                COALESCE(a.kelompok_dagang, '') AS kelompok_dagang,
                COALESCE(a.kelompok_dagang, '') AS kelompok_dagang_label,
            ";
        } else {
            $kelompokDagangSelect = "
                '' AS kelompok_dagang,
                '' AS kelompok_dagang_label,
            ";
        }

        $this->db->select("
            a.id_barang,
            a.kode_barang,
            a.kd_suplier,
            a.nama_barang,
            COALESCE(a.satuan, '') AS satuan,
            {$kelompokDagangSelect}
            COALESCE(a.kelompok_barang, '') AS kelompok_barang,
            COALESCE(a.kategori_barang, '') AS kategori_barang,
            COALESCE(a.bhn_aktif, '') AS bahan_aktif,
            COALESCE(a.merk_barang, '') AS merk_barang,
            COALESCE(a.produk_fokus, '') AS produk_fokus,
            COALESCE(a.stock_minimum, 0) AS stock_minimum,
            COALESCE(a.panjang, 0) AS panjang,
            COALESCE(a.lebar, 0) AS lebar,
            COALESCE(a.tinggi, 0) AS tinggi,
            COALESCE(a.berat, 0) AS berat,
            COALESCE(a.isi, 0) AS isi,
            COALESCE(a.kemasan, 0) AS kemasan,
            COALESCE(a.is_lot, 'F') AS is_lot,
            COALESCE(a.is_active, 'T') AS is_active,
            " . $this->master_barang_tf_select('is_inventori', 'T') . ",
            " . $this->master_barang_tf_select('is_beli', 'T') . ",
            " . $this->master_barang_tf_select('is_jual', 'T') . ",
            " . $this->master_barang_tf_select('hpp_average', 'T') . ",
            " . $this->master_barang_tf_select('hpp_fifo', 'F') . ",
            " . $this->master_barang_tf_select('hpp_lifo', 'F') . ",
            " . $this->master_barang_text_select('kode_akun_harga_pokok', '51030') . ",
            " . $this->master_barang_text_select('kode_akun_penjualan', '41032') . ",
            " . $this->master_barang_text_select('kode_akun_persediaan', '14030') . ",
            " . $this->master_barang_text_select('kode_akun_pengiriman_beli', '51032') . ",
            " . $this->master_barang_text_select('kode_akun_pengiriman_jual', '64030') . ",
            " . $this->master_barang_text_select('kode_akun_retur_penjualan', '41034') . ",
            COALESCE(s.nama_suplier, '') AS nama_suplier
        ", false);
        $supplierSubquery = '(SELECT kd_suplier, MIN(nama_suplier) AS nama_suplier FROM tb_suplier GROUP BY kd_suplier)';
        $this->db->from('tbpo_barang a');
        $this->db->join($supplierSubquery . ' s', 's.kd_suplier = a.kd_suplier', 'left', false);
        if ($hasKelompokDagangColumn && $hasKelompokDagangMaster) {
            $this->db->join('tbkeu_kelompok_dagang kd', 'CAST(kd.NOINDEX AS CHAR) = a.kelompok_dagang', 'left', false);
        }

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('a.kode_barang', $search);
            $this->db->or_like('a.nama_barang', $search);
            if ($hasKelompokDagangColumn) {
                $this->db->or_like('a.kelompok_dagang', $search);
            }
            if ($hasKelompokDagangColumn && $hasKelompokDagangMaster) {
                $this->db->or_like('kd.DESKRIPSI', $search);
            }
            $this->db->or_like('a.kelompok_barang', $search);
            $this->db->or_like('a.kategori_barang', $search);
            $this->db->or_like('a.merk_barang', $search);
            $this->db->or_like('s.nama_suplier', $search);
            $this->db->group_end();
        }

        if ($kelompokDagang !== '') {
            if ($hasKelompokDagangColumn) {
                $this->db->where('a.kelompok_dagang', $kelompokDagang);
            } else {
                $this->db->where('1 = 0', null, false);
            }
        }
    }

    private function build_master_barang_payload($input)
    {
        $data = [
            'kode_barang'      => $input['kode_barang'],
            'kd_suplier'       => $input['kd_suplier'],
            'nama_barang'      => $input['nama_barang'],
            'satuan'           => $input['satuan'],
            'panjang'          => $input['panjang'],
            'lebar'            => $input['lebar'],
            'tinggi'           => $input['tinggi'],
            'berat'            => $input['berat'],
            'isi'              => $input['isi'],
            'kemasan'          => $input['kemasan'],
            'stock_minimum'    => $input['stock_minimum'],
            'merk_barang'      => $input['merk_barang'],
            'kelompok_barang'  => $input['kelompok_barang'],
            'kategori_barang'  => $input['kategori_barang'],
            'bhn_aktif'        => $input['bahan_aktif'],
            'produk_fokus'     => $input['produk_fokus'],
            'is_active'        => $input['is_active'],
            'is_lot'           => $input['is_lot'],
        ];

        if ($this->master_barang_has_kelompok_dagang()) {
            $data['kelompok_dagang'] = $input['kelompok_dagang'];
        }

        foreach ([
            'is_inventori',
            'is_beli',
            'is_jual',
            'hpp_average',
            'hpp_fifo',
            'hpp_lifo',
            'kode_akun_harga_pokok',
            'kode_akun_penjualan',
            'kode_akun_persediaan',
            'kode_akun_pengiriman_beli',
            'kode_akun_pengiriman_jual',
            'kode_akun_retur_penjualan',
        ] as $field) {
            if ($this->master_barang_field_exists($field)) {
                $data[$field] = $input[$field];
            }
        }

        return $data;
    }

    public function master_barang_kelompok_dagang_options()
    {
        if (!$this->master_kelompok_dagang_table_exists()) {
            return [];
        }

        return $this->db->select('
                NOINDEX AS noindex,
                DESKRIPSI AS deskripsi,
                KODESALES AS kode_sales,
                KODEINVENTORI AS kode_inventori,
                KODEHARGAPOKOK AS kode_harga_pokok,
                KODEPENGIRIMANBELI AS kode_pengiriman_beli,
                KODEPENGIRIMANJUAL AS kode_pengiriman_jual
            ', false)
            ->from('tbkeu_kelompok_dagang')
            ->order_by('NOINDEX', 'ASC')
            ->get()
            ->result();
    }

    public function master_barang_kelompok_dagang_exists($noindex)
    {
        if (!$this->master_kelompok_dagang_table_exists()) {
            return true;
        }

        if (!preg_match('/^[0-9]+$/', (string)$noindex)) {
            return false;
        }

        return $this->db->where('NOINDEX', (int)$noindex)
            ->from('tbkeu_kelompok_dagang')
            ->count_all_results() > 0;
    }

    public function master_barang_akun_options()
    {
        if (!$this->db->table_exists('tbkeu_akun')) {
            return [];
        }

        $this->db->select('kode_akun, nama_akun');
        $this->db->from('tbkeu_akun');
        if ($this->db->field_exists('is_active', 'tbkeu_akun')) {
            $this->db->where('is_active', 1);
        }
        if ($this->db->field_exists('tipe_akun', 'tbkeu_akun')) {
            $this->db->where('tipe_akun', 'POSTING');
        }
        $this->db->order_by('kode_akun', 'ASC');
        return $this->db->get()->result();
    }

    public function master_barang_akun_exists($kodeAkun)
    {
        if (!$this->db->table_exists('tbkeu_akun')) {
            return true;
        }

        if (trim((string)$kodeAkun) === '') {
            return true;
        }

        $this->db->from('tbkeu_akun');
        $this->db->where('kode_akun', $kodeAkun);
        if ($this->db->field_exists('is_active', 'tbkeu_akun')) {
            $this->db->where('is_active', 1);
        }
        if ($this->db->field_exists('tipe_akun', 'tbkeu_akun')) {
            $this->db->where('tipe_akun', 'POSTING');
        }

        return $this->db->count_all_results() > 0;
    }

    public function master_barang_all($search = '', $kelompokDagang = '', $limit = 100, $offset = 0)
    {
        $this->master_barang_base_query($search, $kelompokDagang);
        $this->db->order_by('a.kode_barang', 'ASC');
        $this->db->limit((int)$limit, (int)$offset);
        return $this->db->get()->result();
    }

    public function master_barang_count_all()
    {
        return $this->db->count_all('tbpo_barang');
    }

    public function master_barang_count_filtered($search = '', $kelompokDagang = '')
    {
        $this->master_barang_base_query($search, $kelompokDagang);
        return $this->db->count_all_results();
    }

    public function master_barang_by_id($id)
    {
        $this->master_barang_base_query();
        $this->db->where('a.id_barang', (int)$id);
        return $this->db->get()->row();
    }

    public function master_barang_by_kode($kodeBarang, $excludeId = 0)
    {
        $this->db->from('tbpo_barang');
        $this->db->where('kode_barang', $kodeBarang);
        if ((int)$excludeId > 0) {
            $this->db->where('id_barang !=', (int)$excludeId);
        }
        return $this->db->get()->row();
    }

    public function master_barang_store($input)
    {
        $data = $this->build_master_barang_payload($input);
        return $this->db->insert('tbpo_barang', $data);
    }

    public function master_barang_update($id, $input)
    {
        $data = $this->build_master_barang_payload($input);
        return $this->db->where('id_barang', (int)$id)->update('tbpo_barang', $data);
    }

    public function master_barang_update_info_lain($id, $input)
    {
        $data = [
            'panjang' => $input['panjang'],
            'lebar'   => $input['lebar'],
            'tinggi'  => $input['tinggi'],
            'berat'   => $input['berat'],
            'isi'     => $input['isi'],
            'kemasan' => $input['kemasan'],
        ];

        return $this->db->where('id_barang', (int)$id)->update('tbpo_barang', $data);
    }

    public function master_barang_delete($id)
    {
        return $this->db->where('id_barang', (int)$id)->delete('tbpo_barang');
    }

    public function master_barang_supplier_options()
    {
        return $this->db->select('kd_suplier, nama_suplier')
            ->from('tb_suplier')
            ->order_by('nama_suplier', 'ASC')
            ->get()
            ->result();
    }

    private function master_customer_base_query($search = '')
    {
        $this->db->select('
            id,
            COALESCE(kd_customer, "") AS kd_customer,
            COALESCE(nama_customer, "") AS nama_customer,
            COALESCE(nama_kios, "") AS nama_kios,
            COALESCE(alamat_kios, "") AS alamat_kios,
            COALESCE(telp1, "") AS telp1,
            COALESCE(telp2, "") AS telp2,
            COALESCE(regional, "") AS regional,
            COALESCE(jam_buka_tutup, "") AS jam_buka_tutup,
            COALESCE(karakteristik_kios, "") AS karakteristik_kios
        ', false);
        $this->db->from('tb_customer');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('kd_customer', $search);
            $this->db->or_like('nama_customer', $search);
            $this->db->or_like('nama_kios', $search);
            $this->db->or_like('alamat_kios', $search);
            $this->db->or_like('telp1', $search);
            $this->db->or_like('telp2', $search);
            $this->db->or_like('regional', $search);
            $this->db->or_like('jam_buka_tutup', $search);
            $this->db->or_like('karakteristik_kios', $search);
            $this->db->group_end();
        }
    }

    public function master_customer_all($limit = null, $offset = 0, $search = '')
    {
        $this->master_customer_base_query($search);
        $this->db->order_by('nama_customer', 'ASC');

        if ($limit !== null && (int)$limit > 0) {
            $this->db->limit((int)$limit, (int)$offset);
        }

        return $this->db->get()->result();
    }

    public function master_customer_count_all()
    {
        return $this->db->count_all('tb_customer');
    }

    public function master_customer_count_filtered($search = '')
    {
        $this->master_customer_base_query($search);
        return $this->db->count_all_results();
    }

    public function master_customer_by_id($id)
    {
        return $this->db->query("
            SELECT
                id,
                COALESCE(kd_customer, '') AS kd_customer,
                COALESCE(nama_customer, '') AS nama_customer,
                COALESCE(nama_kios, '') AS nama_kios,
                COALESCE(alamat_kios, '') AS alamat_kios,
                COALESCE(telp1, '') AS telp1,
                COALESCE(telp2, '') AS telp2,
                COALESCE(regional, '') AS regional,
                COALESCE(jam_buka_tutup, '') AS jam_buka_tutup,
                COALESCE(karakteristik_kios, '') AS karakteristik_kios
            FROM tb_customer
            WHERE id = ?
            LIMIT 1
        ", [(int)$id])->row();
    }

    public function master_customer_store($input)
    {
        return $this->db->insert('tb_customer', [
            'kd_customer'        => $input['kd_customer'],
            'nama_customer'      => $input['nama_customer'],
            'nama_kios'          => $input['nama_kios'],
            'alamat_kios'        => $input['alamat_kios'],
            'telp1'              => $input['telp1'],
            'telp2'              => $input['telp2'],
            'regional'           => $input['regional'],
            'jam_buka_tutup'     => $input['jam_buka_tutup'],
            'karakteristik_kios' => $input['karakteristik_kios'],
        ]);
    }

    public function master_customer_update($id, $input)
    {
        return $this->db->where('id', (int)$id)->update('tb_customer', [
            'kd_customer'        => $input['kd_customer'],
            'nama_customer'      => $input['nama_customer'],
            'nama_kios'          => $input['nama_kios'],
            'alamat_kios'        => $input['alamat_kios'],
            'telp1'              => $input['telp1'],
            'telp2'              => $input['telp2'],
            'regional'           => $input['regional'],
            'jam_buka_tutup'     => $input['jam_buka_tutup'],
            'karakteristik_kios' => $input['karakteristik_kios'],
        ]);
    }

    public function master_customer_delete($id)
    {
        return $this->db->where('id', (int)$id)->delete('tb_customer');
    }

    public function accounting_schema_ready()
    {
        return $this->db->table_exists('tbkeu_klasifikasi_akun')
            && $this->db->table_exists('tbkeu_akun');
    }

    public function accounting_support_schema_ready()
    {
        return $this->db->table_exists('tbkeu_saldo_normal')
            && $this->db->table_exists('tbkeu_tipe_kontrol');
    }

    public function accounting_journal_schema_ready()
    {
        return $this->db->table_exists('tbkeu_jurnal')
            && $this->db->table_exists('tbkeu_jurnal_detail');
    }

    private function accounting_default_saldo_normal_options()
    {
        return [
            (object)['kode_saldo' => 'DEBIT', 'nama_saldo' => 'Debit', 'keterangan' => 'Saldo normal debit', 'urutan' => 10, 'is_active' => 1],
            (object)['kode_saldo' => 'KREDIT', 'nama_saldo' => 'Kredit', 'keterangan' => 'Saldo normal kredit', 'urutan' => 20, 'is_active' => 1],
        ];
    }

    private function accounting_default_tipe_kontrol_options()
    {
        $rows = [
            ['NONE', 'None', 'Akun biasa tanpa kontrol khusus', 10],
            ['KAS', 'Kas', 'Akun kas tunai', 20],
            ['BANK', 'Bank', 'Akun rekening bank', 30],
            ['PIUTANG', 'Piutang', 'Akun piutang customer', 40],
            ['HUTANG', 'Hutang', 'Akun hutang supplier', 50],
            ['PERSEDIAAN', 'Persediaan', 'Akun persediaan barang', 60],
            ['GRNI', 'GRNI', 'Barang diterima belum ditagih', 70],
            ['PAJAK_MASUKAN', 'Pajak Masukan', 'PPN masukan', 80],
            ['PAJAK_KELUARAN', 'Pajak Keluaran', 'PPN keluaran', 90],
            ['UANG_MUKA_CUSTOMER', 'Uang Muka Customer', 'Uang muka dari customer', 100],
            ['UANG_MUKA_SUPPLIER', 'Uang Muka Supplier', 'Uang muka ke supplier', 110],
            ['LABA_DITAHAN', 'Laba Ditahan', 'Akun laba ditahan', 120],
        ];

        return array_map(function ($row) {
            return (object)[
                'kode_tipe_kontrol' => $row[0],
                'nama_tipe_kontrol' => $row[1],
                'keterangan' => $row[2],
                'urutan' => $row[3],
                'is_active' => 1,
            ];
        }, $rows);
    }

    public function accounting_klasifikasi_options()
    {
        if (!$this->db->table_exists('tbkeu_klasifikasi_akun')) {
            return [];
        }

        return $this->db
            ->where('is_active', 1)
            ->order_by('urutan', 'ASC')
            ->get('tbkeu_klasifikasi_akun')
            ->result();
    }

    public function accounting_klasifikasi_by_id($id)
    {
        if (!$this->db->table_exists('tbkeu_klasifikasi_akun')) {
            return null;
        }

        return $this->db
            ->where('id_klasifikasi', (int)$id)
            ->get('tbkeu_klasifikasi_akun')
            ->row();
    }

    public function accounting_saldo_normal_options()
    {
        if (!$this->db->table_exists('tbkeu_saldo_normal')) {
            return $this->accounting_default_saldo_normal_options();
        }

        return $this->db
            ->where('is_active', 1)
            ->order_by('urutan', 'ASC')
            ->order_by('kode_saldo', 'ASC')
            ->get('tbkeu_saldo_normal')
            ->result();
    }

    public function accounting_saldo_normal_by_code($kode)
    {
        $kode = strtoupper(trim((string)$kode));
        if ($kode === '') {
            return null;
        }

        if (!$this->db->table_exists('tbkeu_saldo_normal')) {
            foreach ($this->accounting_default_saldo_normal_options() as $row) {
                if ($row->kode_saldo === $kode) {
                    return $row;
                }
            }
            return null;
        }

        return $this->db
            ->where('kode_saldo', $kode)
            ->where('is_active', 1)
            ->get('tbkeu_saldo_normal')
            ->row();
    }

    public function accounting_tipe_kontrol_options()
    {
        if (!$this->db->table_exists('tbkeu_tipe_kontrol')) {
            return $this->accounting_default_tipe_kontrol_options();
        }

        return $this->db
            ->where('is_active', 1)
            ->order_by('urutan', 'ASC')
            ->order_by('kode_tipe_kontrol', 'ASC')
            ->get('tbkeu_tipe_kontrol')
            ->result();
    }

    public function accounting_tipe_kontrol_by_code($kode)
    {
        $kode = strtoupper(trim((string)$kode));
        if ($kode === '') {
            return null;
        }

        if (!$this->db->table_exists('tbkeu_tipe_kontrol')) {
            foreach ($this->accounting_default_tipe_kontrol_options() as $row) {
                if ($row->kode_tipe_kontrol === $kode) {
                    return $row;
                }
            }
            return null;
        }

        return $this->db
            ->where('kode_tipe_kontrol', $kode)
            ->where('is_active', 1)
            ->get('tbkeu_tipe_kontrol')
            ->row();
    }

    public function accounting_account_summary()
    {
        $summary = [
            'total' => 0,
            'header' => 0,
            'posting' => 0,
            'active' => 0,
            'inactive' => 0,
        ];

        if (!$this->accounting_schema_ready()) {
            return $summary;
        }

        $row = $this->db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN tipe_akun = 'HEADER' THEN 1 ELSE 0 END) AS header,
                SUM(CASE WHEN tipe_akun = 'POSTING' THEN 1 ELSE 0 END) AS posting,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS inactive
            FROM tbkeu_akun
        ")->row_array();

        foreach ($summary as $key => $value) {
            $summary[$key] = (int)($row[$key] ?? 0);
        }

        return $summary;
    }

    private function accounting_account_select()
    {
        $usedSql = $this->db->table_exists('tbkeu_jurnal_detail')
            ? '(SELECT COUNT(*) FROM tbkeu_jurnal_detail jd WHERE jd.id_akun = a.id_akun)'
            : '0';
        $eligibleSql = $this->db->field_exists('is_transaction_eligible', 'tbkeu_akun')
            ? 'a.is_transaction_eligible'
            : 'CASE WHEN a.tipe_akun = "POSTING" AND a.is_active = 1 THEN 1 ELSE 0 END';

        $this->db->select("
            a.id_akun,
            a.kode_akun,
            a.nama_akun,
            a.id_klasifikasi,
            a.parent_id,
            a.level_akun,
            a.saldo_normal,
            a.tipe_akun,
            a.tipe_kontrol,
            a.allow_manual_journal,
            {$eligibleSql} AS is_transaction_eligible,
            a.is_active,
            a.created_by,
            a.created_at,
            a.updated_by,
            a.updated_at,
            k.kode_klasifikasi,
            k.nama_klasifikasi,
            k.alias_klasifikasi,
            p.kode_akun AS parent_kode_akun,
            p.nama_akun AS parent_nama_akun,
            (SELECT COUNT(*) FROM tbkeu_akun c WHERE c.parent_id = a.id_akun) AS child_count,
            {$usedSql} AS transaction_count
        ", false);
        $this->db->from('tbkeu_akun a');
        $this->db->join('tbkeu_klasifikasi_akun k', 'k.id_klasifikasi = a.id_klasifikasi', 'left');
        $this->db->join('tbkeu_akun p', 'p.id_akun = a.parent_id', 'left');
    }

    public function accounting_accounts($search = '', $klasifikasiId = 0)
    {
        if (!$this->accounting_schema_ready()) {
            return [];
        }

        $this->accounting_account_select();
        if ((int)$klasifikasiId > 0) {
            $this->db->where('a.id_klasifikasi', (int)$klasifikasiId);
        }

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('a.kode_akun', $search);
            $this->db->or_like('a.nama_akun', $search);
            $this->db->or_like('k.nama_klasifikasi', $search);
            $this->db->group_end();
        }

        $this->db->order_by('a.kode_akun', 'ASC');
        return $this->db->get()->result();
    }

    public function accounting_account_journal_rows($idAkun)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $journalDate = $this->db->field_exists('tanggal_transaksi', 'tbkeu_jurnal') ? 'j.tanggal_transaksi'
            : ($this->db->field_exists('tanggal_jurnal', 'tbkeu_jurnal') ? 'j.tanggal_jurnal'
                : ($this->db->field_exists('tanggal', 'tbkeu_jurnal') ? 'j.tanggal' : 'j.created_at'));
        if ($this->db->field_exists('source_no', 'tbkeu_jurnal') && $this->db->field_exists('nomor_jurnal', 'tbkeu_jurnal')) {
            $journalRef = "COALESCE(NULLIF(j.source_no, ''), j.nomor_jurnal)";
        } else {
            $journalRef = $this->db->field_exists('nomor_jurnal', 'tbkeu_jurnal') ? 'j.nomor_jurnal'
                : ($this->db->field_exists('no_jurnal', 'tbkeu_jurnal') ? 'j.no_jurnal'
                    : ($this->db->field_exists('no_referensi', 'tbkeu_jurnal') ? 'j.no_referensi'
                        : ($this->db->field_exists('kode_jurnal', 'tbkeu_jurnal') ? 'j.kode_jurnal' : 'CAST(j.id_jurnal AS CHAR)')));
        }
        $journalNote = $this->db->field_exists('keterangan', 'tbkeu_jurnal') ? 'j.keterangan'
            : ($this->db->field_exists('catatan', 'tbkeu_jurnal') ? 'j.catatan' : '""');
        $detailNote = $this->db->field_exists('keterangan', 'tbkeu_jurnal_detail') ? 'jd.keterangan'
            : ($this->db->field_exists('catatan', 'tbkeu_jurnal_detail') ? 'jd.catatan' : '""');
        $debitField = $this->db->field_exists('debit', 'tbkeu_jurnal_detail') ? 'jd.debit'
            : ($this->db->field_exists('debet', 'tbkeu_jurnal_detail') ? 'jd.debet' : '0');
        $kreditField = $this->db->field_exists('kredit', 'tbkeu_jurnal_detail') ? 'jd.kredit' : '0';

        $this->db->select("
            j.id_jurnal,
            jd.id_jurnal_detail,
            {$journalDate} AS tanggal_jurnal,
            {$journalRef} AS no_referensi,
            COALESCE(NULLIF({$detailNote}, ''), {$journalNote}, '') AS catatan,
            COALESCE({$debitField}, 0) AS debit,
            COALESCE({$kreditField}, 0) AS kredit
        ", false);
        $this->db->from('tbkeu_jurnal_detail jd');
        $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = jd.id_jurnal', 'left');
        $this->db->where('jd.id_akun', (int)$idAkun);
        $this->db->order_by($journalDate, 'DESC', false);
        $this->db->order_by('j.id_jurnal', 'DESC');
        $this->db->limit(200);

        return $this->db->get()->result();
    }

    public function accounting_sales_journal_rows($search = '', $limit = 100)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            j.source_id AS no_faktur,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(f.no_so, '') AS no_so,
            COALESCE(f.customer_name, '') AS pelanggan,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbso_faktur_penjualan f', 'f.no_faktur = j.source_id', 'left');
        $this->db->where('j.source_module', 'SALES');
        $this->db->where('j.source_type', 'FAKTUR_PENJUALAN');
        $this->db->where('j.posting_event', 'SALES_INVOICE');
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('j.source_no', $search);
            $this->db->or_like('j.nomor_jurnal', $search);
            $this->db->or_like('f.no_so', $search);
            $this->db->or_like('f.customer_name', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $this->db->limit((int)$limit > 0 ? (int)$limit : 100);

        return $this->db->get()->result();
    }

    public function accounting_purchase_journal_rows($search = '', $limit = 100)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            j.source_id AS id_lpb,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(h.nomor_lpb, j.source_no, '') AS nomor_lpb,
            COALESCE(h.no_po, '') AS no_po,
            COALESCE(s.nama_suplier, '') AS supplier,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tb_lpb h', 'h.id_lpb = CAST(j.source_id AS UNSIGNED)', 'left', false);
        $this->db->join('tbpo_po p', 'p.kd_po = h.kd_po AND p.no_po = h.no_po', 'left');
        $this->db->join('tbpo_suplier s', 's.kd_suplier = p.kd_suplier', 'left');
        $this->db->where('j.source_module', 'LOGISTIK');
        $this->db->where('j.source_type', 'LPB_FINAL');
        $this->db->where('j.posting_event', 'GOODS_RECEIPT');
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('j.source_no', $search);
            $this->db->or_like('j.nomor_jurnal', $search);
            $this->db->or_like('h.no_po', $search);
            $this->db->or_like('h.nomor_lpb', $search);
            $this->db->or_like('s.nama_suplier', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $this->db->limit((int)$limit > 0 ? (int)$limit : 100);

        return $this->db->get()->result();
    }

    public function accounting_sales_journal_detail($idJurnal)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return null;
        }

        $this->db->select("
            j.*,
            COALESCE(f.no_so, '') AS no_so,
            COALESCE(f.customer_name, '') AS pelanggan,
            COALESCE(
                NULLIF(k.nm_karyawan, ''),
                NULLIF(u.nama_user, ''),
                NULLIF(p.create_by, ''),
                NULLIF(f.create_by, ''),
                CASE WHEN j.created_by = 0 THEN '' ELSE CONCAT('User #', j.created_by) END,
                'system'
            ) AS created_by_name,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbso_faktur_penjualan f', '(j.source_module = "SALES" AND f.no_faktur = j.source_id) OR (j.source_module = "KEUANGAN" AND f.no_faktur = j.source_no)', 'left');
        $this->db->join('tb_karyawan k', 'j.created_by = k.id', 'left');
        $this->db->join('tb_user u', 'j.created_by = u.id', 'left');
        $this->db->join('tbkeu_pembayaran_faktur p', 'j.source_module = "KEUANGAN" AND CAST(j.source_id AS UNSIGNED) = p.id_pembayaran', 'left');
        $this->db->where('j.id_jurnal', (int)$idJurnal);
        $journal = $this->db->get()->row();
        if (!$journal) {
            return null;
        }

        $this->db->select("d.*, a.kode_akun, a.nama_akun, COALESCE(ref.kode_rekening_display, a.kode_akun) AS kode_rekening_display", false);
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->db->join('tbkeu_akun_karismaerp_ref ref', 'ref.id_akun = a.id_akun', 'left');
        $this->db->where('d.id_jurnal', (int)$idJurnal);
        $this->db->order_by('d.nomor_baris', 'ASC');

        return [
            'journal' => $journal,
            'details' => $this->db->get()->result(),
        ];
    }

    public function accounting_purchase_journal_detail($idJurnal)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return null;
        }

        $this->db->select("
            j.*,
            jj.kode_jenis_jurnal,
            COALESCE(h.nomor_lpb, j.source_no, '') AS nomor_lpb,
            COALESCE(h.no_po, '') AS no_po,
            COALESCE(s.nama_suplier, '') AS supplier,
            COALESCE(NULLIF(k.nm_karyawan, ''), NULLIF(u.nama_lngkp, ''), IF(j.created_by IS NULL, '', CONCAT('User #', j.created_by))) AS created_by_name,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
        $this->db->join('tb_lpb h', 'h.id_lpb = CAST(j.source_id AS UNSIGNED)', 'left', false);
        $this->db->join('tbpo_po p', 'p.kd_po = h.kd_po AND p.no_po = h.no_po', 'left');
        $this->db->join('tbpo_suplier s', 's.kd_suplier = p.kd_suplier', 'left');
        $this->db->join('tb_karyawan k', 'k.id = j.created_by', 'left');
        $this->db->join('tb_users u', 'u.id = j.created_by', 'left');
        $this->db->where('j.id_jurnal', (int)$idJurnal);
        $this->db->where('j.source_module', 'LOGISTIK');
        $this->db->where('j.source_type', 'LPB_FINAL');
        $this->db->where('j.posting_event', 'GOODS_RECEIPT');
        $journal = $this->db->get()->row();
        if (!$journal) {
            return null;
        }

        $this->db->select("
            d.*,
            a.kode_akun,
            COALESCE(NULLIF(a.nama_akun, ''), NULLIF(ref.nama_karismaerp, ''), NULLIF(ref.alias_karismaerp, ''), d.keterangan, '') AS nama_akun,
            COALESCE(ref.kode_rekening_display, a.kode_akun) AS kode_rekening_display
        ", false);
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->db->join('tbkeu_akun_karismaerp_ref ref', 'ref.id_akun = a.id_akun', 'left');
        $this->db->where('d.id_jurnal', (int)$idJurnal);
        $this->db->order_by('d.nomor_baris', 'ASC');

        return [
            'journal' => $journal,
            'details' => $this->db->get()->result(),
        ];
    }

    public function accounting_account_by_id($id)
    {
        if (!$this->accounting_schema_ready()) {
            return null;
        }

        $this->accounting_account_select();
        $this->db->where('a.id_akun', (int)$id);
        return $this->db->get()->row();
    }

    public function accounting_account_by_code($kodeAkun, $excludeId = 0)
    {
        if (!$this->db->table_exists('tbkeu_akun')) {
            return null;
        }

        $this->db->where('kode_akun', $kodeAkun);
        if ((int)$excludeId > 0) {
            $this->db->where('id_akun !=', (int)$excludeId);
        }

        return $this->db->get('tbkeu_akun')->row();
    }

    private function accounting_account_level($parentId)
    {
        if ((int)$parentId <= 0) {
            return 1;
        }

        $parent = $this->db
            ->select('level_akun')
            ->where('id_akun', (int)$parentId)
            ->get('tbkeu_akun')
            ->row();

        return $parent ? ((int)$parent->level_akun + 1) : 1;
    }

    private function accounting_account_payload($input, $userId, $isCreate = true)
    {
        $data = [
            'kode_akun' => $input['kode_akun'],
            'nama_akun' => $input['nama_akun'],
            'id_klasifikasi' => (int)$input['id_klasifikasi'],
            'parent_id' => (int)$input['parent_id'] > 0 ? (int)$input['parent_id'] : null,
            'level_akun' => $this->accounting_account_level((int)$input['parent_id']),
            'saldo_normal' => $input['saldo_normal'],
            'tipe_akun' => $input['tipe_akun'],
            'tipe_kontrol' => $input['tipe_kontrol'] ?: 'NONE',
            'allow_manual_journal' => (int)$input['allow_manual_journal'],
            'is_active' => (int)$input['is_active'],
            'updated_by' => $userId ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($data['tipe_akun'] === 'HEADER') {
            $data['allow_manual_journal'] = 0;
        }

        if ($this->db->field_exists('is_transaction_eligible', 'tbkeu_akun')) {
            $data['is_transaction_eligible'] = ($data['tipe_akun'] === 'POSTING' && (int)$data['is_active'] === 1) ? 1 : 0;
        }

        if ($isCreate) {
            $data['created_by'] = $userId ?: null;
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    public function accounting_account_store($input, $userId = null)
    {
        if (!$this->accounting_schema_ready()) {
            return false;
        }

        $data = $this->accounting_account_payload($input, $userId, true);
        $ok = $this->db->insert('tbkeu_akun', $data);
        return $ok ? $this->db->insert_id() : false;
    }

    public function accounting_account_update($id, $input, $userId = null)
    {
        if (!$this->accounting_schema_ready()) {
            return false;
        }

        $data = $this->accounting_account_payload($input, $userId, false);
        return $this->db
            ->where('id_akun', (int)$id)
            ->update('tbkeu_akun', $data);
    }

    public function accounting_account_deactivate($id, $userId = null)
    {
        if (!$this->db->table_exists('tbkeu_akun')) {
            return false;
        }

        return $this->db
            ->where('id_akun', (int)$id)
            ->update('tbkeu_akun', [
                'is_active' => 0,
                'updated_by' => $userId ?: null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function accounting_account_delete($id)
    {
        if (!$this->db->table_exists('tbkeu_akun')) {
            return false;
        }

        return $this->db->where('id_akun', (int)$id)->delete('tbkeu_akun');
    }

    public function accounting_account_used($id)
    {
        if (!$this->db->table_exists('tbkeu_jurnal_detail')) {
            return false;
        }

        return $this->db
            ->where('id_akun', (int)$id)
            ->count_all_results('tbkeu_jurnal_detail') > 0;
    }

    public function accounting_account_has_children($id)
    {
        if (!$this->db->table_exists('tbkeu_akun')) {
            return false;
        }

        return $this->db
            ->where('parent_id', (int)$id)
            ->count_all_results('tbkeu_akun') > 0;
    }

    public function accounting_klasifikasi_duplicate($idKlasifikasi, $kodeKlasifikasi, $excludeId = 0)
    {
        if (!$this->db->table_exists('tbkeu_klasifikasi_akun')) {
            return false;
        }

        $this->db->from('tbkeu_klasifikasi_akun');
        $this->db->group_start()
            ->where('id_klasifikasi', (int)$idKlasifikasi)
            ->or_where('kode_klasifikasi', $kodeKlasifikasi)
            ->group_end();
        if ((string)$excludeId !== '' && (int)$excludeId > 0) {
            $this->db->where('id_klasifikasi !=', (int)$excludeId);
        }

        return $this->db->count_all_results() > 0;
    }

    public function accounting_saldo_normal_duplicate($kodeSaldo, $excludeKode = '')
    {
        if (!$this->db->table_exists('tbkeu_saldo_normal')) {
            return false;
        }

        $this->db->where('kode_saldo', $kodeSaldo);
        if ((string)$excludeKode !== '') {
            $this->db->where('kode_saldo !=', $excludeKode);
        }

        return $this->db->count_all_results('tbkeu_saldo_normal') > 0;
    }

    public function accounting_tipe_kontrol_duplicate($kodeTipeKontrol, $excludeKode = '')
    {
        if (!$this->db->table_exists('tbkeu_tipe_kontrol')) {
            return false;
        }

        $this->db->where('kode_tipe_kontrol', $kodeTipeKontrol);
        if ((string)$excludeKode !== '') {
            $this->db->where('kode_tipe_kontrol !=', $excludeKode);
        }

        return $this->db->count_all_results('tbkeu_tipe_kontrol') > 0;
    }

    public function accounting_master_rows($master)
    {
        if ($master === 'klasifikasi') {
            if (!$this->db->table_exists('tbkeu_klasifikasi_akun')) {
                return [];
            }

            return $this->db
                ->order_by('urutan', 'ASC')
                ->order_by('id_klasifikasi', 'ASC')
                ->get('tbkeu_klasifikasi_akun')
                ->result();
        }

        if ($master === 'saldo-normal') {
            if (!$this->db->table_exists('tbkeu_saldo_normal')) {
                return $this->accounting_default_saldo_normal_options();
            }

            return $this->db
                ->order_by('urutan', 'ASC')
                ->order_by('kode_saldo', 'ASC')
                ->get('tbkeu_saldo_normal')
                ->result();
        }

        if ($master === 'tipe-kontrol') {
            if (!$this->db->table_exists('tbkeu_tipe_kontrol')) {
                return $this->accounting_default_tipe_kontrol_options();
            }

            return $this->db
                ->order_by('urutan', 'ASC')
                ->order_by('kode_tipe_kontrol', 'ASC')
                ->get('tbkeu_tipe_kontrol')
                ->result();
        }

        if ($master === 'parent-subclass') {
            if (!$this->accounting_schema_ready()) {
                return [];
            }

            $this->accounting_account_select();
            $this->db->where('a.tipe_akun', 'HEADER');
            $this->db->order_by('a.kode_akun', 'ASC');
            return $this->db->get()->result();
        }

        return [];
    }

    public function accounting_master_row($master, $id)
    {
        if ($master === 'klasifikasi') {
            return $this->accounting_klasifikasi_by_id($id);
        }

        if ($master === 'saldo-normal') {
            if (!$this->db->table_exists('tbkeu_saldo_normal')) {
                return null;
            }

            return $this->db->where('kode_saldo', $id)->get('tbkeu_saldo_normal')->row();
        }

        if ($master === 'tipe-kontrol') {
            if (!$this->db->table_exists('tbkeu_tipe_kontrol')) {
                return null;
            }

            return $this->db->where('kode_tipe_kontrol', $id)->get('tbkeu_tipe_kontrol')->row();
        }

        if ($master === 'parent-subclass') {
            $row = $this->accounting_account_by_id((int)$id);
            return $row && $row->tipe_akun === 'HEADER' ? $row : null;
        }

        return null;
    }

    public function accounting_master_store($master, $input, $userId = null)
    {
        if ($master === 'klasifikasi') {
            $data = [
                'id_klasifikasi' => (int)$input['id_klasifikasi'],
                'kode_klasifikasi' => $input['kode_klasifikasi'],
                'nama_klasifikasi' => $input['nama_klasifikasi'],
                'alias_klasifikasi' => $input['alias_klasifikasi'],
                'jenis_laporan' => $input['jenis_laporan'],
                'saldo_normal' => $input['saldo_normal'],
                'urutan' => (int)$input['urutan'],
                'is_active' => (int)$input['is_active'],
            ];
            return $this->db->insert('tbkeu_klasifikasi_akun', $data) ? $input['id_klasifikasi'] : false;
        }

        if ($master === 'saldo-normal') {
            $data = [
                'kode_saldo' => $input['kode_saldo'],
                'nama_saldo' => $input['nama_saldo'],
                'keterangan' => $input['keterangan'],
                'urutan' => (int)$input['urutan'],
                'is_active' => (int)$input['is_active'],
            ];
            return $this->db->insert('tbkeu_saldo_normal', $data) ? $input['kode_saldo'] : false;
        }

        if ($master === 'tipe-kontrol') {
            $data = [
                'kode_tipe_kontrol' => $input['kode_tipe_kontrol'],
                'nama_tipe_kontrol' => $input['nama_tipe_kontrol'],
                'keterangan' => $input['keterangan'],
                'urutan' => (int)$input['urutan'],
                'is_active' => (int)$input['is_active'],
            ];
            return $this->db->insert('tbkeu_tipe_kontrol', $data) ? $input['kode_tipe_kontrol'] : false;
        }

        if ($master === 'parent-subclass') {
            return $this->accounting_account_store($input, $userId);
        }

        return false;
    }

    public function accounting_master_update($master, $id, $input, $userId = null)
    {
        if ($master === 'klasifikasi') {
            return $this->db->where('id_klasifikasi', (int)$id)->update('tbkeu_klasifikasi_akun', [
                'kode_klasifikasi' => $input['kode_klasifikasi'],
                'nama_klasifikasi' => $input['nama_klasifikasi'],
                'alias_klasifikasi' => $input['alias_klasifikasi'],
                'jenis_laporan' => $input['jenis_laporan'],
                'saldo_normal' => $input['saldo_normal'],
                'urutan' => (int)$input['urutan'],
                'is_active' => (int)$input['is_active'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($master === 'saldo-normal') {
            return $this->db->where('kode_saldo', $id)->update('tbkeu_saldo_normal', [
                'nama_saldo' => $input['nama_saldo'],
                'keterangan' => $input['keterangan'],
                'urutan' => (int)$input['urutan'],
                'is_active' => (int)$input['is_active'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($master === 'tipe-kontrol') {
            return $this->db->where('kode_tipe_kontrol', $id)->update('tbkeu_tipe_kontrol', [
                'nama_tipe_kontrol' => $input['nama_tipe_kontrol'],
                'keterangan' => $input['keterangan'],
                'urutan' => (int)$input['urutan'],
                'is_active' => (int)$input['is_active'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($master === 'parent-subclass') {
            return $this->accounting_account_update((int)$id, $input, $userId);
        }

        return false;
    }

    public function accounting_master_used($master, $id)
    {
        if ($master === 'klasifikasi') {
            return $this->db->where('id_klasifikasi', (int)$id)->count_all_results('tbkeu_akun') > 0;
        }

        if ($master === 'saldo-normal') {
            return $this->db->where('saldo_normal', $id)->count_all_results('tbkeu_akun') > 0
                || $this->db->where('saldo_normal', $id)->count_all_results('tbkeu_klasifikasi_akun') > 0;
        }

        if ($master === 'tipe-kontrol') {
            return $this->db->where('tipe_kontrol', $id)->count_all_results('tbkeu_akun') > 0;
        }

        if ($master === 'parent-subclass') {
            return $this->accounting_account_used((int)$id) || $this->accounting_account_has_children((int)$id);
        }

        return true;
    }

    public function accounting_master_delete($master, $id)
    {
        if ($master === 'klasifikasi') {
            return $this->db->where('id_klasifikasi', (int)$id)->delete('tbkeu_klasifikasi_akun');
        }

        if ($master === 'saldo-normal') {
            return $this->db->where('kode_saldo', $id)->delete('tbkeu_saldo_normal');
        }

        if ($master === 'tipe-kontrol') {
            return $this->db->where('kode_tipe_kontrol', $id)->delete('tbkeu_tipe_kontrol');
        }

        if ($master === 'parent-subclass') {
            return $this->accounting_account_delete((int)$id);
        }

        return false;
    }
}
