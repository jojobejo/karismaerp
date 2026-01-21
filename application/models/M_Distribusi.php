<?php
defined('BASEPATH') or exit('No direct script access allowed');


class M_Distribusi extends CI_Model
{
    public function total_tonase_by_rute()
    {
        return $this->db->query("SELECT
            r.kd_rute AS rute,
            COALESCE(ROUND(SUM(CASE WHEN p.data_sts = '3' THEN (b.berat * p.qty)ELSE 0 END) / 1000000, 3),0) AS tonase_terkirim,
            COALESCE(ROUND(SUM(CASE WHEN p.data_sts <> '3'THEN (b.berat * p.qty)ELSE 0 END) / 1000000, 3),0) AS tonase_belum_terkirim,
            COALESCE(ROUND(SUM(b.berat * p.qty) / 1000000, 3),0) AS total_tonase,
            COUNT(DISTINCT CASE WHEN p.data_sts = '3' THEN p.kd_faktur END) AS total_faktur_terkirim,
            COUNT(DISTINCT CASE WHEN p.data_sts <> '3' THEN p.kd_faktur END) AS total_faktur_pending,
            COUNT(DISTINCT p.kd_faktur) AS total_faktur 
            FROM tb_rutecs r
            LEFT JOIN tb_pre_do p
            ON p.kd_rute = r.kd_rute
        LEFT JOIN tb_master_barang_all b
            ON b.kd_barang = p.kd_barang
        GROUP BY r.kd_rute
        ORDER BY tonase_terkirim DESC;")->result();
    }

    public function detail_faktur($kdrute)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_pre_do a
        WHERE a.kd_rute = '$kdrute'
        GROUP BY kd_faktur
        ")->result();
        
    }
}
