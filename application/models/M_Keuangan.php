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
        }
    }
    public function insert_batch($data)
    {
        $this->db->insert_batch('tb_dailystock', $data);
    }
    public function batch_global($data)
    {
        $this->db->insert_batch('tb_dailystock_global', $data);
    }
    public function get_stock_global()
    {
        $this->db->select('b.nama_suplier AS nmsuplier, c.nm_barang AS nmbarang, c.satuan AS satuan, a.qty AS qty, c.qty_min AS qty_min');
        $this->db->from('tb_dailystock_global a');
        $this->db->join('tb_suplier b', 'b.kd_suplier = a.kd_suplier');
        $this->db->join('tb_master_barang c', 'c.kode_barang = a.kd_barang');
        $this->db->where('a.qty < c.qty_min');
        $this->db->group_by('c.nm_barang');
        $query = $this->db->get();
        return $query->result();
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
        a.qty AS qty,
        b.qty_min AS qtymin
        FROM tb_dailystock_global a
        JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
        JOIN tb_suplier c ON c.kd_suplier = a.kd_suplier
        WHERE a.kd_suplier = '$kd'
        GROUP BY a.kd_barang
        ORDER BY a.qty DESC
        ")->result();
    }
    
    public function get_stock_by_sup($kd, $gdg)
    {
        return $this->db->query("SELECT
        b.nm_barang AS nmbarang,
        b.satuan AS satuan,
        a.qty AS qty,
        b.qty_min AS qtymin
        FROM tb_dailystock a
        JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
        JOIN tb_suplier c ON c.kd_suplier = a.kd_suplier
        WHERE a.kd_suplier = '$kd' AND a.gudang = '$gdg'
            ")->result();
    }
}
