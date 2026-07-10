<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class M_Keuangan extends CI_Model
{

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

    private function master_barang_base_query($search = '')
    {
        $this->db->select("
            a.id_barang,
            a.kode_barang,
            a.kd_suplier,
            a.nama_barang,
            COALESCE(a.satuan, '') AS satuan,
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
            COALESCE(s.nama_suplier, '') AS nama_suplier
        ", false);
        $supplierSubquery = '(SELECT kd_suplier, MIN(nama_suplier) AS nama_suplier FROM tb_suplier GROUP BY kd_suplier)';
        $this->db->from('tbpo_barang a');
        $this->db->join($supplierSubquery . ' s', 's.kd_suplier = a.kd_suplier', 'left', false);

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('a.kode_barang', $search);
            $this->db->or_like('a.nama_barang', $search);
            $this->db->or_like('a.kelompok_barang', $search);
            $this->db->or_like('a.kategori_barang', $search);
            $this->db->or_like('a.merk_barang', $search);
            $this->db->or_like('s.nama_suplier', $search);
            $this->db->group_end();
        }
    }

    private function build_master_barang_payload($input)
    {
        return [
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
    }

    public function master_barang_all($search = '', $limit = 100, $offset = 0)
    {
        $this->master_barang_base_query($search);
        $this->db->order_by('a.kode_barang', 'ASC');
        $this->db->limit((int)$limit, (int)$offset);
        return $this->db->get()->result();
    }

    public function master_barang_count_all()
    {
        return $this->db->count_all('tbpo_barang');
    }

    public function master_barang_count_filtered($search = '')
    {
        $this->master_barang_base_query($search);
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
}
