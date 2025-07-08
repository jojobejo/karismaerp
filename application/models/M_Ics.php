<?php
class M_Ics extends CI_Model
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

    public function list_barang_ics()
    {
        return $this->db->query("SELECT 
            i.nama_barang,
            i.exp_date,

            COALESCE(FLOOR(od.qty_out / (mb.p * mb.l * mb.t)), 0) AS out_box,
            COALESCE(MOD(od.qty_out, (mb.p * mb.l * mb.t)), 0)    AS out_pcs,

            FLOOR(i.qty / (mb.p * mb.l * mb.t)) AS saldo_awal_box,
            MOD(i.qty, (mb.p * mb.l * mb.t))    AS saldo_awal_pcs,

            COALESCE(FLOOR(op.qty_op / (mb.p * mb.l * mb.t)), 0) AS opname_box,
            COALESCE(MOD(op.qty_op, (mb.p * mb.l * mb.t)), 0)    AS opname_pcs,

            FLOOR((i.qty - COALESCE(od.qty_out, 0)) / (mb.p * mb.l * mb.t)) AS saldo_akhir_box,
            MOD((i.qty - COALESCE(od.qty_out, 0)), (mb.p * mb.l * mb.t))    AS saldo_akhir_pcs,

            CASE
                WHEN
                    -- kondisi 1: hasil opname = saldo akhir
                    FLOOR((i.qty - COALESCE(od.qty_out, 0)) / (mb.p * mb.l * mb.t)) = COALESCE(FLOOR(op.qty_op / (mb.p * mb.l * mb.t)), 0)
                    AND MOD((i.qty - COALESCE(od.qty_out, 0)), (mb.p * mb.l * mb.t)) = COALESCE(MOD(op.qty_op, (mb.p * mb.l * mb.t)), 0)
                THEN 'KLOP'
                WHEN
                    -- kondisi 2: tidak ada penjualan & tidak ada opname, saldo tetap
                    od.qty_out IS NULL AND op.qty_op IS NULL
                    AND FLOOR(i.qty / (mb.p * mb.l * mb.t)) = FLOOR(i.qty / (mb.p * mb.l * mb.t)) -- saldo_awal_box = saldo_akhir_box
                    AND MOD(i.qty, (mb.p * mb.l * mb.t)) = MOD(i.qty, (mb.p * mb.l * mb.t))       -- saldo_awal_pcs = saldo_akhir_pcs
                THEN 'KLOP'
                ELSE 'TIDAK'
            END AS klop

        FROM tb_ics i

        LEFT JOIN tb_mbarang mb ON mb.nm_barang = i.nama_barang

        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_out
            FROM tb_ics_do
            WHERE DATE(tgl_transaksi) = @report_date
            GROUP BY nama_barang, exp_date
        ) od ON od.nama_barang = i.nama_barang AND od.exp_date = i.exp_date

        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_op
            FROM tb_ics_opname
            WHERE DATE(input_at) = @report_date
            GROUP BY nama_barang, exp_date
        ) op ON op.nama_barang = i.nama_barang AND op.exp_date = i.exp_date

        WHERE mb.p * mb.l * mb.t > 0
        ORDER BY i.nama_barang, i.exp_date;")->result();
    }

    public function update_cell($id, $field, $value)
    {
        $allowed = ['kd_barang', 'nama_barang', 'qty', 'exp_date'];
        if (!in_array($field, $allowed)) return false;

        return $this->db->update('tb_stok', [$field => $value], ['id' => $id]);
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
