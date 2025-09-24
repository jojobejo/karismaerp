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
            COALESCE(sum(m.qty), 0) AS qty_sistem,
            COALESCE(d.qty_pending, 0) AS qty_pending,
            (COALESCE(sum(m.qty), 0) + COALESCE(d.qty_pending, 0)) AS total_sistem_pending,
            CASE 
                WHEN COALESCE(o.qty, 0) = (COALESCE(sum(m.qty), 0) + COALESCE(d.qty_pending, 0)) 
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
        ) d ON m.nama_barang = d.nama_barang
        GROUP BY m.nama_barang")->result();
    }

    public function get_stockopname_exp()
    {
        return $this->db->query("SELECT
            m.kode_barang AS kode_barang,
            m.nama_barang,
            m.expired_date AS exp_date,
            COALESCE(o.qty, 0) AS qty_fisik,
            COALESCE(sum(m.qty), 0) AS qty_sistem,
            COALESCE(d.qty_pending, 0) AS qty_pending,
            (COALESCE(sum(m.qty), 0) + COALESCE(d.qty_pending, 0)) AS total_sistem_pending,
            CASE 
                WHEN COALESCE(o.qty, 0) = (COALESCE(sum(m.qty), 0) + COALESCE(d.qty_pending, 0)) 
                THEN 'match' 
                ELSE 'not match' 
            END AS status
        FROM stockopname_master m
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty , expired_date
            FROM stockopname_opname o
            GROUP BY o.nama_barang , o.expired_date
        ) o ON m.nama_barang = o.nama_barang AND m.expired_date = o.expired_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_pending
            FROM tb_ics_do d
            GROUP BY d.nama_barang , d.exp_date 
        ) d ON m.nama_barang = d.nama_barang AND m.expired_date = d.exp_date
        GROUP BY m.nama_barang , m.expired_date")->result();
    }

    public function getinputopname($user)
    {
        return $this->db->query("SELECT * FROM `stockopname_opname` WHERE input_by = '$user'
        ")->result();
    }
    public function getBarangByNama($nama)
    {
        return $this->db->get_where('tb_master_barang', ['nm_barang' => $nama])->row();
    }
    public function getDimensi($nama)
    {
        $barang = $this->getBarangByNama($nama);
        return $barang->p * $barang->l * $barang->t;
    }
    public function insertOpname($data)
    {
        $this->db->insert('stockopname_opname', $data);
    }

    public function logInput($log)
    {
        $this->db->insert('tb_log_ics', $log);
    }
}
