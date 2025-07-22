<?php

use JetBrains\PhpStorm\Internal\ReturnTypeContract;

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
        x.id,
        x.nama_barang,
        x.exp_date,
        COALESCE(x.saldo_awal_qty, 0) AS saldo_awal_qty,
        FLOOR(COALESCE(x.saldo_awal_qty, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_awal_box,
        MOD(COALESCE(x.saldo_awal_qty, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_awal_pcs,
        COALESCE(p.qty_in, 0) AS qty_in,
        FLOOR(COALESCE(p.qty_in, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS in_box,
        MOD(COALESCE(p.qty_in, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS in_pcs,
        COALESCE(d.qty_out, 0) AS qty_out,
        FLOOR(COALESCE(d.qty_out, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS out_box,
        MOD(COALESCE(d.qty_out, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS out_pcs,
        (
            COALESCE(x.saldo_awal_qty, 0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) AS saldo_akhir_qty,
        FLOOR((
            COALESCE(x.saldo_awal_qty, 0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_box,
        MOD((
            COALESCE(x.saldo_awal_qty, 0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_pcs,
        COALESCE(o.qty_opname, 0) AS fisik_ics,
        FLOOR(COALESCE(o.qty_opname, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS fisik_box,
        MOD(COALESCE(o.qty_opname, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS fisik_pcs,
        (
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) AS qty_selisih,
        FLOOR((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_box,
        MOD((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_pcs,
        CASE
            WHEN (
                COALESCE(x.saldo_awal_qty, 0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            ) = COALESCE(o.qty_opname, 0)
            THEN 'KLOP'
            ELSE 'TIDAK'
        END AS status_kesesuaian
        FROM (
            SELECT id,nama_barang, exp_date, SUM(qty) AS saldo_awal_qty
            FROM tb_saldo_awal
            GROUP BY nama_barang, exp_date
        ) x
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_in
            FROM tb_ics_po
            GROUP BY nama_barang, exp_date
        ) p ON p.nama_barang = x.nama_barang AND p.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_out
            FROM tb_ics_do
            GROUP BY nama_barang, exp_date
        ) d ON d.nama_barang = x.nama_barang AND d.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_opname
            FROM tb_ics_opname
            GROUP BY nama_barang, exp_date
        ) o ON o.nama_barang = x.nama_barang AND o.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t
            FROM tb_mbarang
            GROUP BY nm_barang
        ) mb ON mb.nm_barang = x.nama_barang
        ORDER BY x.nama_barang, x.exp_date;")->result();
    }


    public function list_barang_ics_diffrent()
    {
        return $this->db->query("SELECT
            x.id,
            x.nama_barang,
            x.exp_date,
            x.saldo_awal_qty,
            FLOOR(x.saldo_awal_qty / (mb.p * mb.l * mb.t)) AS saldo_awal_box,
            MOD(x.saldo_awal_qty, (mb.p * mb.l * mb.t)) AS saldo_awal_pcs,
            COALESCE(d.qty_out, 0) AS qty_out,
            FLOOR(COALESCE(d.qty_out, 0) / (mb.p * mb.l * mb.t)) AS out_box,
            MOD(COALESCE(d.qty_out, 0), (mb.p * mb.l * mb.t)) AS out_pcs,
            COALESCE(p.qty_in, 0) AS qty_in,
            FLOOR(COALESCE(p.qty_in, 0) / (mb.p * mb.l * mb.t)) AS in_box,
            MOD(COALESCE(p.qty_in, 0), (mb.p * mb.l * mb.t)) AS in_pcs,
            COALESCE(o.adjustment, 0) AS adjustment,
            FLOOR(COALESCE(o.adjustment, 0) / (mb.p * mb.l * mb.t)) AS adj_box,
            MOD(COALESCE(o.adjustment, 0), (mb.p * mb.l * mb.t)) AS adj_pcs,
            (
                x.saldo_awal_qty
                - COALESCE(d.qty_out, 0)
                + COALESCE(p.qty_in, 0)
                + COALESCE(o.adjustment, 0)
            ) AS saldo_akhir_qty,
            FLOOR((
                x.saldo_awal_qty
                - COALESCE(d.qty_out, 0)
                + COALESCE(p.qty_in, 0)
                + COALESCE(o.adjustment, 0)
            ) / (mb.p * mb.l * mb.t)) AS saldo_akhir_box,
            MOD((
                x.saldo_awal_qty
                - COALESCE(d.qty_out, 0)
                + COALESCE(p.qty_in, 0)
                + COALESCE(o.adjustment, 0)
            ), (mb.p * mb.l * mb.t)) AS saldo_akhir_pcs,
            (
                (
                    x.saldo_awal_qty
                    - COALESCE(d.qty_out, 0)
                    + COALESCE(p.qty_in, 0)
                    + COALESCE(o.adjustment, 0)
                ) - x.saldo_awal_qty
            ) AS qty_selisih,
            FLOOR((
                (
                    x.saldo_awal_qty
                    - COALESCE(d.qty_out, 0)
                    + COALESCE(p.qty_in, 0)
                    + COALESCE(o.adjustment, 0)
                ) - x.saldo_awal_qty
            ) / (mb.p * mb.l * mb.t)) AS selisih_box,
            MOD((
                (
                    x.saldo_awal_qty
                    - COALESCE(d.qty_out, 0)
                    + COALESCE(p.qty_in, 0)
                    + COALESCE(o.adjustment, 0)
                ) - x.saldo_awal_qty
            ), (mb.p * mb.l * mb.t)) AS selisih_pcs,
            CASE 
                WHEN (
                    x.saldo_awal_qty
                    - COALESCE(d.qty_out, 0)
                    + COALESCE(p.qty_in, 0)
                    + COALESCE(o.adjustment, 0)
                ) = x.saldo_awal_qty THEN 'KLOP'
                ELSE 'TIDAK'
            END AS status_kesesuaian
        FROM (
            SELECT id,nama_barang, exp_date, SUM(qty) AS saldo_awal_qty
            FROM tb_saldo_awal
            GROUP BY nama_barang, exp_date
        ) x
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_out
            FROM tb_ics_do
            GROUP BY nama_barang, exp_date
        ) d ON d.nama_barang = x.nama_barang AND d.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_in
            FROM tb_ics_po
            GROUP BY nama_barang, exp_date
        ) p ON p.nama_barang = x.nama_barang AND p.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS adjustment
            FROM tb_ics_opname
            GROUP BY nama_barang, exp_date
        ) o ON o.nama_barang = x.nama_barang AND o.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t
            FROM tb_mbarang
            GROUP BY nm_barang
        ) mb ON mb.nm_barang = x.nama_barang
        WHERE (
            x.saldo_awal_qty
            - COALESCE(d.qty_out, 0)
            + COALESCE(p.qty_in, 0)
            + COALESCE(o.adjustment, 0)
        ) != x.saldo_awal_qty
        ORDER BY x.nama_barang, x.exp_date")->result();
    }
    public function update_cell($id, $field, $value)
    {
        $allowed = ['kd_barang', 'nama_barang', 'qty', 'exp_date'];
        if (!in_array($field, $allowed)) return false;

        return $this->db->update('tb_stok', [$field => $value], ['id' => $id]);
    }

    public function compareFEFO()
    {
        $sql = "SELECT 
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

    public function list_do_today($tgl)
    {
        return $this->db->query("SELECT
            a.id,
            a.tgl_transaksi,
            a.kd_faktur,
            a.nama_barang,
            a.qty,
            FLOOR(a.qty / (m.p * m.l * m.t)) AS qty_box,
            MOD(a.qty, (m.p * m.l * m.t))    AS qty_pcs,
            a.no_lot,
            a.exp_date
        FROM tb_ics_do a
        LEFT JOIN tb_mbarang m ON m.nm_barang = a.nama_barang
        WHERE 
            DATE(a.tgl_transaksi) = '$tgl'
            AND (m.p * m.l * m.t) > 0
        ")->result();
    }
    public function list_po_today($tgl)
    {
        return $this->db->query("SELECT
            a.id,
            a.kd_faktur_lpb as kd_faktur,
            a.tgl_transaksi,
            a.nama_barang,
            a.qty,
            a.exp_date,
            (m.p * m.l * m.t) AS dimensi,
            FLOOR(a.qty / (m.p * m.l * m.t)) AS qty_box,
            MOD(a.qty, (m.p * m.l * m.t))    AS qty_pcs
        FROM tb_ics_po a
        LEFT JOIN tb_mbarang m ON m.nm_barang = a.nama_barang
        WHERE DATE(a.tgl_transaksi) = '$tgl'
        AND (m.p * m.l * m.t) > 0")->result();
    }

    public function get_br_name($idbarang)
    {
        return $this->db->query("SELECT
        a.exp_date AS exp_date,
        b.nm_barang as nama_barang
        FROM tb_saldo_awal a
        JOIN tb_mbarang b ON b.nm_barang = a.nama_barang
        WHERE a.id = '$idbarang'
        LIMIT 1
        ")->result();
    }

    public function ics_log_input($nmbarang, $exp)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_log_ics a
        WHERE a.nama_barang = '$nmbarang' AND a.exp_date = '$exp'")->result();
    }

    public function compare_ics_allbarang($kdbarang)
    {
        return $this->db->query("SELECT
        a.nama_barang,
        b.kode_barang,
        SUM(a.qty) AS qty_awal,
        COALESCE(pending.qty_pending, 0) AS DO,
        COALESCE(purchase.qty_po, 0) AS PO,
        COALESCE(opname.qty_opname, 0) AS ICS,
        (SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
        COALESCE(opname.qty_opname, 0) -((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) AS selisih,
        IF(((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0),1,0) AS status
        FROM tb_ics a
        JOIN tb_master_barang b ON b.nm_barang = a.nama_barang
        LEFT JOIN 
        (	SELECT nama_barang, SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang
        ) 	pending ON pending.nama_barang = a.nama_barang
        LEFT JOIN 
        (	SELECT nama_barang, SUM(qty) AS qty_po
            FROM tb_ics_po
            GROUP BY nama_barang
        ) 	purchase ON purchase.nama_barang = a.nama_barang
        LEFT JOIN 
        (	SELECT nama_barang, SUM(qty) AS qty_opname
            FROM tb_ics_opname
            GROUP BY nama_barang
        ) opname ON opname.nama_barang = a.nama_barang
        WHERE b.kode_barang = '$kdbarang'
        ")->result();
    }

    public function ics_get_all_qty_barang($nmbarang)
    {
        return $this->db->query("SELECT
            a.id,
            a.nama_barang,
            (b.p*b.l*b.t) AS dimensi,
            SUM(a.qty) AS qty_awal,
            COALESCE(pending.qty_pending, 0) AS DO,
            COALESCE(purchase.qty_po, 0) AS PO,
            COALESCE(opname.qty_opname, 0) AS ICS,
            (SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
            COALESCE(opname.qty_opname, 0) - ((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) AS selisih,
            IF(((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0), 1, 0) AS status
            FROM tb_saldo_awal a
            JOIN tb_mbarang b ON b.nm_barang = a.nama_barang
            LEFT JOIN (
                SELECT nama_barang,SUM(qty) AS qty_pending
                FROM tb_ics_do
                GROUP BY nama_barang
            ) pending ON pending.nama_barang = a.nama_barang
            LEFT JOIN (
                SELECT nama_barang,SUM(qty) AS qty_po
                FROM tb_ics_po
                GROUP BY nama_barang
            ) purchase ON purchase.nama_barang = a.nama_barang
            LEFT JOIN (
                SELECT nama_barang, SUM(qty) AS qty_opname
                FROM tb_ics_opname
                GROUP BY nama_barang
            ) opname ON opname.nama_barang = a.nama_barang
            WHERE a.nama_barang = '$nmbarang'
            GROUP BY a.nama_barang;")->result();
    }
}
