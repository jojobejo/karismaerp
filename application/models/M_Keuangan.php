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
            (SELECT COUNT(d.kd_barang) FROM tb_dailystock d WHERE d.gudang = 'Global') AS gdg_global
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
        a.last_update AS lastupdated
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
    public function truncateitm()
    {
        $this->db->empty_table('tb_dailystock');
    }
    public function insert_batch($data)
    {
        $this->db->insert_batch('tb_dailystock', $data);
    }
}
