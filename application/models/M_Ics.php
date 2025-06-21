<?php
class M_Opname extends CI_Model
{

    public function getAllICS()
    {
        return $this->db->get('tb_ics')->result();
    }

    public function insertOpname($data)
    {
        $this->db->insert('tb_ics_opname', $data);
    }

    public function logInput($log)
    {
        $this->db->insert('tb_ics_log', $log);
    }

    public function compareOpname()
    {
        $sql = "SELECT 
                    o.nama_barang, o.exp_date, o.qty AS qty_fisik,
                    i.qty AS qty_saldo,
                    IF(o.qty = i.qty, 'MATCH', 'NOT MATCH') AS status
                FROM tb_ics_opname o
                LEFT JOIN tb_ics i ON o.nama_barang = i.nama_barang AND o.exp_date = i.exp_date";
        return $this->db->query($sql)->result();
    }

    public function getBarangByNama($nama)
    {
        return $this->db->get_where('tb_mbarang', ['nm_barang' => $nama])->row();
    }

    public function getDimensi($nama)
    {
        $barang = $this->getBarangByNama($nama);
        return $barang->p * $barang->l * $barang->t;
    }

    public function compareFEFO()
    {
        $sql = "
        SELECT 
            o.nama_barang, 
            o.exp_date,
            SUM(o.qty) AS qty_fisik,
            IFNULL(SUM(i.qty), 0) AS qty_saldo,
            IF(SUM(o.qty) = IFNULL(SUM(i.qty), 0), 'MATCH', 'NOT MATCH') AS status
        FROM tb_ics_opname o
        LEFT JOIN tb_ics i 
            ON o.nama_barang = i.nama_barang AND o.exp_date = i.exp_date
        GROUP BY o.nama_barang, o.exp_date
    ";
        return $this->db->query($sql)->result();
    }

    // by All Barang (tanpa exp_date)
    public function compareAllBarang()
    {
        $sql = "
        SELECT 
            o.nama_barang,
            SUM(o.qty) AS qty_fisik,
            IFNULL(SUM(i.qty), 0) AS qty_saldo,
            IF(SUM(o.qty) = IFNULL(SUM(i.qty), 0), 'MATCH', 'NOT MATCH') AS status
        FROM tb_ics_opname o
        LEFT JOIN tb_ics i 
            ON o.nama_barang = i.nama_barang
        GROUP BY o.nama_barang
    ";
        return $this->db->query($sql)->result();
    }
}
