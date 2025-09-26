<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Stockopname extends CI_Model
{
    public function get_stockopname()
    {
        return $this->db->query("SELECT
            m.id,
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
        return $this->db->get_where('tb_mbarang', ['nm_barang' => $nama])->row();
    }

    public function getBarangByNamamaster($nama)
    {
        return $this->db->get_where('stockopname_master', ['nama_barang' => $nama])->row();
    }

    public function getDimensi($nama)
    {
        $barang = $this->getBarangByNama($nama);
        if ($barang) {
            return $barang->dimensi;
        }
        return 0;
    }

    public function getId($nm)
    {
        $barang = $this->getBarangByNamamaster($nm);
        if ($barang) {
            return $barang->id;
        }
        return 0;
    }
    public function insertOpname($data)
    {
        $this->db->insert('stockopname_opname', $data);
    }

    public function logInput($log)
    {
        $this->db->insert('tb_log_ics', $log);
    }

    public function search_barang($keyword)
    {
        return $this->db->select('nama_barang AS id, nama_barang AS text')
            ->like('nama_barang', $keyword)
            ->group_by('nama_barang')
            ->limit(20)
            ->get('stockopname_master')
            ->result();
    }

    public function insert_opname($data)
    {
        return $this->db->insert('stockopname_opname', $data);
    }
    public function insert_log($data)
    {
        return $this->db->insert('tb_log_ics', $data);
    }

    public function get_detail_inputer($id)
    {
        return $this->db->query("SELECT
        a.id, 
        a.qty,
        a.qty_pcs,
        a.qty_box,
        a.input_by,
        a.input_at,
        a.wilayah
        FROM stockopname_opname a
        WHERE a.kode_barang = '$id'
        ")->result();
    }

    public function get_info_barang($id)
    {
        return $this->db->query("SELECT a.*
        FROM stockopname_master a
        WHERE a.id = '$id'
        ")->result();
    }

    public function get_opname_result()
    {
        $this->db->select("
            m.id,
            m.nama_barang,
            m.qty AS qty_master,
            IFNULL(SUM(o.qty),0) AS qty_opname,
            ROUND((IFNULL(SUM(o.qty),0) / m.qty) * 100,2) AS persen_match
        ");
        $this->db->from("stockopname_master m");
        $this->db->join("stockopname_opname o", "o.kode_barang = m.id", "left");
        $this->db->group_by("m.id, m.nama_barang, m.qty");

        return $this->db->get()->result();
    }

    public function get_summary_match()
    {
        $result = $this->get_opname_result();

        $total_barang   = count($result);
        $total_match    = 0;
        $total_notmatch = 0;

        foreach ($result as $row) {
            if ($row->qty_master == $row->qty_opname) {
                $total_match++;
            } else {
                $total_notmatch++;
            }
        }

        return [
            'total_barang'   => $total_barang,
            'total_match'    => $total_match,
            'total_notmatch' => $total_notmatch,
            'persen_match'   => $total_barang > 0 ? round(($total_match / $total_barang) * 100, 2) : 0,
            'persen_notmatch' => $total_barang > 0 ? round(($total_notmatch / $total_barang) * 100, 2) : 0,
        ];
    }
}
