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

    public function getBarangByKode($kd)
    {
        return $this->db->get_where('tb_master_barang', ['kd_system' => $kd])->row();
    }

    public function updateWilayahByOpname($id, $id_wilayah)
    {
        return $this->db
            ->where('id', $id)
            ->update('tb_saldo_awal', [
                'kordinat' => $id_wilayah
            ]);
    }

    public function updateGudangByOpname($opname_id, $id_gudang)
    {
        return $this->db
            ->where('id', $opname_id)
            ->update('tb_saldo_awal', [
                'wilayah_id' => $id_gudang
            ]);
    }



    public function getnmbarang($kd)
    {
        $kd = $this->getBarangByKode($kd);
        return $kd->nm_barang;
    }

    public function list_barang_ics_expdate($pic)
    {
        $where = "";
        if ($pic != "E") {
            $where = "WHERE x.lokasi = '$pic'";
        }

        return $this->db->query("SELECT
        mb.kd,
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
            SELECT id,nama_barang, exp_date, SUM(qty) AS saldo_awal_qty , lokasi
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
            SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , kd_system as kd
            FROM tb_master_barang
            GROUP BY nm_barang
        ) mb ON mb.nm_barang = x.nama_barang
        $where
        ORDER BY x.nama_barang, x.exp_date;")->result();
    }

    public function list_barang_ics_allbarang($pic)
    {
        $where = "";
        if ($pic != "E") {
            $where = "WHERE x.lokasi = '$pic'";
        }

        return $this->db->query("SELECT
        x.id,
        x.nama_barang,
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
            SELECT id,nama_barang,SUM(qty) AS saldo_awal_qty,lokasi
            FROM tb_saldo_awal
            GROUP BY nama_barang
        ) x
        LEFT JOIN (
            SELECT nama_barang,SUM(qty) AS qty_in
            FROM tb_ics_po
            GROUP BY nama_barang
        ) p ON p.nama_barang = x.nama_barang
        LEFT JOIN (
            SELECT nama_barang,SUM(qty) AS qty_out
            FROM tb_ics_do
            GROUP BY nama_barang
        ) d ON d.nama_barang = x.nama_barang
        LEFT JOIN (
            SELECT nama_barang,SUM(qty) AS qty_opname
            FROM tb_ics_opname
            GROUP BY nama_barang
        ) o ON o.nama_barang = x.nama_barang
        LEFT JOIN (
            SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t
            FROM tb_master_barang
            GROUP BY nm_barang
        ) mb ON mb.nm_barang = x.nama_barang
        $where
        ORDER BY x.nama_barang")->result();
    }

    public function get_master_barang_ics()
    {
        return $this->db->query("SELECT 
            a.*,
            IFNULL(s.jumlah_barang, 0) AS jumlah_barang,
            IFNULL(s.pic, '-') AS pic,
            IFNULL(s.kordinat, '-') AS kordinat,
            IFNULL(s.kordinat1, '-') AS kordinat1
            FROM tb_master_barang a
            LEFT JOIN (
            SELECT 
                nama_barang,
                COUNT(*) AS jumlah_barang,
                MAX(lokasi) AS pic,
                MAX(kordinat) AS kordinat,
                MAX(kordinat1) AS kordinat1
            FROM tb_saldo_awal
            GROUP BY nama_barang
        ) s ON s.nama_barang = a.nm_barang
        ORDER BY a.kd_system ASC;")->result();
    }

    public function get_barang_detail_by_kd($kd)
    {
        return $this->db->query("SELECT
        a.nm_barang,
        a.kode_barang,
        b.exp_date,
        b.qty,
        b.lokasi as PIC,
        b.kordinat,
        b.kordinat1
        FROM tb_master_barang a 
        JOIN tb_saldo_awal b ON b.nama_barang = a.nm_barang
        WHERE a.kode_barang = '$kd'
        ")->result();
    }

    public function edit_mbarang_ics($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_master_barang', $data);
    }

    public function get_master_barang_log()
    {
        return $this->db->query("SELECT
        a.id as id,
        a.nm_barang AS nama_barang,
        b.nama_suplier as nama_suplier,
        a.bhn_aktif as bahan_aktif,
        a.satuan as satuan,
        (a.p*a.l*a.t) as dimensi,
        a.berat as berat,
        a.kubikasi as kubikasi
        FROM tb_master_barang a
        LEFT JOIN tb_suplier b ON b.kd_suplier = a.kd_suplier
        ")->result();
    }

    public function get_detail_barang($br)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_master_barang a
        WHERE a.kd_system = '$br'
        ")->result();
    }

    public function get_exp_by_kdsys($kd)
    {
        return $this->db->query("SELECT
        a.exp_date
        FROM tb_saldo_awal a
        LEFT JOIN tb_master_barang b ON b.nm_barang = a.nama_barang
        WHERE b.kd_system = '$kd'
        GROUP BY a.exp_date
        ")->result();
    }

    public function tracking_br_diffrent_by_expdate($nmbarang)
    {
        return $this->db->query("SELECT
            a.id,
            opname.id AS opname_id,
            b.kd_system,
            b.p * b.l * b.t AS dimensi,
            a.nama_barang,
            a.exp_date AS expired,
            a.kordinat,
            a.qty AS qty,
            COALESCE(pending.qty_pending, 0) AS do,
            COALESCE(purchase.qty_po, 0) AS po,
            (a.qty - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
            COALESCE(opname.qty_opname, 0) - ((a.qty - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) AS selisih,
            COALESCE(opname.qty_opname, 0) AS ics,
            COALESCE(opname.qty_box,0)AS qty_box,
            COALESCE(opname.qty_pcs,0)AS qty_pcs,
            a.lokasi as PIC,
            a.wilayah_id as id_gudang,
			gdg.nama_gudang as nama_gudang,
            kr.nama_wilayah as nama_wilayah,
            a.kordinat1 as id_kordinat1,
            IF(
                ((a.qty - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0),
                1, 0
            ) AS status
        FROM tb_saldo_awal a
        JOIN tb_master_barang b ON b.nm_barang = a.nama_barang
        LEFT JOIN tb_gudang gdg ON gdg.id_gudang = a.wilayah_id
        LEFT JOIN tb_gudang_wilayah kr ON kr.id_wilayah = a.kordinat
        LEFT JOIN (
            SELECT nama_barang, exp_date, sum(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang, exp_date
        ) pending ON pending.nama_barang = a.nama_barang AND pending.exp_date = a.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, sum(qty) AS qty_po
            FROM tb_ics_po
            GROUP BY nama_barang, exp_date
        ) purchase ON purchase.nama_barang = a.nama_barang AND purchase.exp_date = a.exp_date
        LEFT JOIN (
            SELECT id,nama_barang, exp_date, sum(qty) AS qty_opname , qty_box , qty_pcs
            FROM tb_ics_opname
            GROUP BY nama_barang, exp_date
        ) opname ON opname.nama_barang = a.nama_barang AND opname.exp_date = a.exp_date
        WHERE a.nama_barang = '$nmbarang'
        GROUP BY a.nama_barang, a.exp_date;")->result();
    }

    public function get_exp_detail($nama_barang, $exp_date)
    {
        $sql = "SELECT
                a.id,
                a.nama_barang,
                a.exp_date as expired,
                (b.p*b.l*b.t) AS dimensi,
                SUM(a.qty) AS qty,
                COALESCE(pending.qty_pending, 0) AS do,
                COALESCE(purchase.qty_po, 0) AS po,
                COALESCE(opname.qty_opname, 0) AS ics,
                (SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
                COALESCE(opname.qty_opname, 0) - ((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) AS selisih,
                IF(((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0), 1, 0) AS status
            FROM tb_saldo_awal a
            JOIN tb_mbarang b ON b.nm_barang = a.nama_barang
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_pending
                FROM tb_ics_do
                GROUP BY nama_barang, exp_date
            ) pending ON pending.nama_barang = a.nama_barang AND pending.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_po
                FROM tb_ics_po
                GROUP BY nama_barang, exp_date
            ) purchase ON purchase.nama_barang = a.nama_barang AND purchase.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_opname
                FROM tb_ics_opname
                GROUP BY nama_barang, exp_date
            ) opname ON opname.nama_barang = a.nama_barang AND opname.exp_date = a.exp_date
            WHERE a.nama_barang = ? AND a.exp_date = ?
            GROUP BY a.nama_barang, a.exp_date";

        return $this->db->query($sql, [$nama_barang, $exp_date])->result();
    }



    public function list_barang_ics_diffrent_a()
    {
        return $this->db->query("SELECT
        mb.kd,
        x.nama_barang,
        x.exp_date,
        x.kordinat,
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
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
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
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(lokasi) AS lokasi , MAX(kordinat) AS kordinat
        FROM tb_saldo_awal
        WHERE lokasi = 'A'
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
        SELECT nama_barang, exp_date, SUM(qty) AS qty_opname ,qty_box , qty_pcs
        FROM tb_ics_opname
        WHERE wilayah = 'A'
        GROUP BY nama_barang, exp_date
    ) o ON o.nama_barang = x.nama_barang AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_system) AS kd
        FROM tb_master_barang
        GROUP BY nm_barang
    ) mb ON mb.nm_barang = x.nama_barang
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        (COALESCE(x.saldo_awal_qty, 0) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
    ) != 0
    ORDER BY x.nama_barang, x.exp_date")->result();
    }

    public function list_barang_ics_diffrent_b()
    {
        return $this->db->query("SELECT
        mb.kd,
        x.nama_barang,
        x.exp_date,
        x.kordinat,
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
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
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
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(lokasi) AS lokasi , MAX(kordinat) AS kordinat
        FROM tb_saldo_awal
        WHERE lokasi = 'B'
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
        SELECT nama_barang, exp_date, SUM(qty) AS qty_opname ,qty_box , qty_pcs
        FROM tb_ics_opname
        WHERE wilayah = 'B'
        GROUP BY nama_barang, exp_date
    ) o ON o.nama_barang = x.nama_barang AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_system) AS kd
        FROM tb_master_barang
        GROUP BY nm_barang
    ) mb ON mb.nm_barang = x.nama_barang
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        (COALESCE(x.saldo_awal_qty, 0) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
    ) != 0
    ORDER BY x.nama_barang, x.exp_date")->result();
    }

    public function list_barang_ics_diffrent_c()
    {
        return $this->db->query("SELECT
        mb.kd,
        x.nama_barang,
        x.exp_date,
        x.kordinat,
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
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
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
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(lokasi) AS lokasi , MAX(kordinat) AS kordinat
        FROM tb_saldo_awal
        WHERE lokasi = 'C'
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
        SELECT nama_barang, exp_date, SUM(qty) AS qty_opname ,qty_box , qty_pcs
        FROM tb_ics_opname
        WHERE wilayah = 'C'
        GROUP BY nama_barang, exp_date
    ) o ON o.nama_barang = x.nama_barang AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_system) AS kd
        FROM tb_master_barang
        GROUP BY nm_barang
    ) mb ON mb.nm_barang = x.nama_barang
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        (COALESCE(x.saldo_awal_qty, 0) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
    ) != 0
    ORDER BY x.nama_barang, x.exp_date")->result();
    }

    public function list_barang_ics_diffrent_d()
    {
        return $this->db->query("SELECT
        mb.kd,
        x.nama_barang,
        x.exp_date,
        x.kordinat,
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
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
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
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(lokasi) AS lokasi , MAX(kordinat) AS kordinat
        FROM tb_saldo_awal
        WHERE lokasi = 'D'
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
        SELECT nama_barang, exp_date, SUM(qty) AS qty_opname ,qty_box , qty_pcs
        FROM tb_ics_opname
        WHERE wilayah = 'D'
        GROUP BY nama_barang, exp_date
    ) o ON o.nama_barang = x.nama_barang AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_system) AS kd
        FROM tb_master_barang
        GROUP BY nm_barang
    ) mb ON mb.nm_barang = x.nama_barang
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        (COALESCE(x.saldo_awal_qty, 0) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
    ) != 0
    ORDER BY x.nama_barang, x.exp_date")->result();
    }

    public function list_barang_ics_diffrent_e()
    {
        return $this->db->query("SELECT
        mb.kd,
        x.nama_barang,
        x.exp_date,
        x.kordinat,
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
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
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
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(lokasi) AS lokasi , MAX(kordinat) AS kordinat
        FROM tb_saldo_awal
        WHERE lokasi = 'E'
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
        SELECT nama_barang, exp_date, SUM(qty) AS qty_opname ,qty_box , qty_pcs
        FROM tb_ics_opname
        WHERE wilayah = 'E'
        GROUP BY nama_barang, exp_date
    ) o ON o.nama_barang = x.nama_barang AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_system) AS kd
        FROM tb_master_barang
        GROUP BY nm_barang
    ) mb ON mb.nm_barang = x.nama_barang
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        (COALESCE(x.saldo_awal_qty, 0) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
    ) != 0
    ORDER BY x.nama_barang, x.exp_date")->result();
    }

    public function list_barang_ics_diffrent_0()
    {
        return $this->db->query("SELECT
        mb.kd,
        x.nama_barang,
        x.exp_date,
        x.kordinat,
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
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
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
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(lokasi) AS lokasi , MAX(kordinat) AS kordinat
        FROM tb_saldo_awal
        WHERE lokasi = '0'
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
        SELECT nama_barang, exp_date, SUM(qty) AS qty_opname ,qty_box , qty_pcs
        FROM tb_ics_opname
        WHERE wilayah = '0'
        GROUP BY nama_barang, exp_date
    ) o ON o.nama_barang = x.nama_barang AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nm_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_system) AS kd
        FROM tb_master_barang
        GROUP BY nm_barang
    ) mb ON mb.nm_barang = x.nama_barang
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        (COALESCE(x.saldo_awal_qty, 0) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
    ) != 0
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
            a.exp_date,
            c.nama_kios as nm_kios,
            b.kd_rute as rute
        FROM tb_ics_do a
        LEFT JOIN tb_master_barang m ON m.nm_barang = a.nama_barang
        LEFT JOIN tb_detail_do b ON b.kd_faktur = a.kd_faktur
        LEFT JOIN tb_customer c ON c.kd_customer = b.kd_customer
        WHERE 
            a.input_at = '$tgl'
            AND (m.p * m.l * m.t) > 0
            GROUP BY a.kd_faktur , a.nama_barang , a.exp_date, a.no_lot, b.kd_rute , b.kd_customer 
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
            MOD(a.qty, (m.p * m.l * m.t))    AS qty_pcs,
            a.lpb_note as note
        FROM tb_ics_po a
        LEFT JOIN tb_master_barang m ON m.nm_barang = a.nama_barang
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

    public function get_do_by_expdate($nmbarang, $expdate)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_ics_do a
        WHERE a.nama_barang = '$nmbarang' AND a.exp_date = '$expdate'
        ")->result();
    }

    public function get_po_by_expdate($nmbarang, $expdate)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_ics_po a
        WHERE a.nama_barang = '$nmbarang' AND a.exp_date = '$expdate'
        ")->result();
    }

    // public function get_list_barang_pic($lokasi = null)
    // {
    //     if ($lokasi !== null) {
    //         $this->db->where('lokasi', $lokasi);
    //     } else {
    //         $this->db->where('1=0');
    //     }
    //     return $this->db->get('tb_saldo_awal')->result();
    // }

    public function get_list_barang_pic($lokasi = null)
    {
        if ($lokasi !== null) {
            return $this->db->query("SELECT 
                GROUP_CONCAT(id) AS daftar_id,
                nama_barang,
                exp_date,
                lokasi,
                COUNT(*) AS total
            FROM tb_saldo_awal
            WHERE lokasi = '$lokasi'
            GROUP BY nama_barang, exp_date, lokasi
            ORDER BY nama_barang, exp_date
        ")->result();
        }
        return [];
    }

    public function total_barang_pic()
    {
        return $this->db->query("SELECT
        lokasi, COUNT(DISTINCT CONCAT(nama_barang, '-', exp_date)) AS total_barang
        FROM tb_saldo_awal
        GROUP BY lokasi
        ORDER BY lokasi
    ")->result();
    }

    public function getGudangServerSide()
    {
        $this->db->select('*')
            ->from('tb_gudang')
            ->where('is_active', 1);

        if (!empty($_POST['search']['value'])) {
            $this->db->like('nama_gudang', $_POST['search']['value']);
        }

        $total = $this->db->count_all_results('', false);

        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }

        $data = $this->db->get()->result();

        return [
            "draw" => intval($_POST['draw']),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        ];
    }

    public function insertGudang($data)
    {
        $this->db->insert('tb_gudang', $data);
    }

    public function getWilayahByGudang($id_gudang)
    {
        return $this->db->where('id_gudang', $id_gudang)
            ->where('is_active', 1)
            ->get('tb_gudang_wilayah')
            ->result();
    }

    public function getBarangByGudangWilayah()
    {
        $this->db->select('b.*, g.nama_gudang, w.nama_wilayah')
            ->from('tb_master_barang_all b')
            ->join('tb_gudang g', 'g.id_gudang=b.id_gudang', 'left')
            ->join('tb_gudang_wilayah w', 'w.id_wilayah=b.id_wilayah', 'left');

        if ($this->input->post('id_gudang')) {
            $this->db->where('b.id_gudang', $this->input->post('id_gudang'));
        }

        if ($this->input->post('id_wilayah')) {
            $this->db->where('b.id_wilayah', $this->input->post('id_wilayah'));
        }

        return $this->db->get()->result();
    }

    public function getGudangById($id_gudang)
    {
        return $this->db
            ->where('id_gudang', $id_gudang)
            ->get('tb_gudang')
            ->row();
    }
}
