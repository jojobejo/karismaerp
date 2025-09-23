<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Stockopname extends CI_Model
{
    public function get_stockopname()
    {
        return $this->db->query("SELECT
            m.kode_barang AS kode_barang,
            m.nama_barang,
            COALESCE(o.qty, 0) AS qty_fisik,
            COALESCE(m.qty, 0) AS qty_sistem,
            COALESCE(d.qty_pending, 0) AS qty_pending,
            (COALESCE(m.qty, 0) + COALESCE(d.qty_pending, 0)) AS total_sistem_pending,
            CASE 
                WHEN COALESCE(o.qty, 0) = (COALESCE(m.qty, 0) + COALESCE(d.qty_pending, 0)) 
                THEN 'match' 
                ELSE 'not match' 
            END AS status
        FROM stockopname_master m
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty
            FROM stockopname_opname
            GROUP BY nama_barang
        ) o ON m.nama_barang = o.nama_barang
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang
        ) d ON m.nama_barang = d.nama_barang")->result();
    }
}
