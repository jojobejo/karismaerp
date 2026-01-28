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
        return $this->db->get_where('tb_master_barang_all', ['kd_barang' => $kd])->row();
    }

    public function updateWilayahByOpname($id, $id_wilayah)
    {
        return $this->db
            ->where('id', $id)
            ->update('tb_saldo_awal', [
                'koordinat_id' => $id_wilayah
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
        return $kd->nama_barang;
    }

    public function list_barang_ics_expdate($pic)
    {
        $where = "";
        if ($pic != "E") {
            $where = "WHERE x.barang_pic = '$pic'";
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
            SELECT id,nama_barang, exp_date, SUM(qty) AS saldo_awal_qty , barang_pic,kode_barang_zahir
            FROM tb_saldo_awal
            GROUP BY exp_date , kode_barang_zahir
        ) x
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_in , kd_barang
            FROM tb_ics_po
            GROUP BY exp_date , kd_barang
        ) p ON p.kd_barang = x.kode_barang_zahir AND p.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_out , kd_barang
            FROM tb_ics_do
            GROUP BY exp_date,kd_barang
        ) d ON d.kd_barang = x.kode_barang_zahir AND d.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_opname,kd_system
            FROM tb_ics
            GROUP BY exp_date , kd_system
        ) o ON o.kd_system = x.kode_barang_zahir AND o.exp_date = x.exp_date
        LEFT JOIN (
            SELECT nama_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , kd_barang as kd
            FROM tb_master_barang_all
            GROUP BY kd_barang
        ) mb ON mb.kd = x.kode_barang_zahir
        $where
        -- WHERE x.barang_pic = 'A'
        ORDER BY x.nama_barang, x.exp_date;")->result();
    }

    public function list_barang_ics_allbarang($pic)
    {
        $where = "";
        if ($pic != "E") {
            $where = "WHERE x.barang_pic = '$pic'";
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
            SELECT id,nama_barang,SUM(qty) AS saldo_awal_qty,barang_pic,kode_barang_zahir
            FROM tb_saldo_awal
            GROUP BY kode_barang_zahir
        ) x
        LEFT JOIN (
            SELECT nama_barang,SUM(qty) AS qty_in , kd_barang 
            FROM tb_ics_po
            GROUP BY kd_barang
        ) p ON p.kd_barang = x.kode_barang_zahir
        LEFT JOIN (
            SELECT nama_barang,SUM(qty) AS qty_out,kd_barang
            FROM tb_ics_do
            GROUP BY kd_barang
        ) d ON d.kd_barang = x.kode_barang_zahir
        LEFT JOIN (
            SELECT nama_barang,SUM(qty) AS qty_opname, kd_system
            FROM tb_ics
            GROUP BY kd_system, nama_barang
        ) o ON o.kd_system = x.kode_barang_zahir
        LEFT JOIN (
            SELECT nama_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , kd_barang
            FROM tb_master_barang_all
            GROUP BY kd_barang,nama_barang
        ) mb ON mb.kd_barang = x.kode_barang_zahir
        $where
        ORDER BY x.nama_barang;")->result();
    }

    public function get_master_barang_ics()
    {
        return $this->db->query("SELECT 
            a.*,
            IFNULL(s.jumlah_barang, 0) AS jumlah_barang,
            IFNULL(s.pic, '-') AS pic,
            IFNULL(s.kordinat, '-') AS kordinat,
            IFNULL(s.kordinat1, '-') AS kordinat1
            FROM tb_master_barang_all a
            LEFT JOIN (
            SELECT 
                nama_barang,
                COUNT(*) AS jumlah_barang,
                MAX(barang_pic) AS pic,
                MAX(koordinat_id) AS kordinat
            FROM tb_saldo_awal
            GROUP BY nama_barang
        ) s ON s.nama_barang = a.nama_barang
        ORDER BY a.kd_barang ASC;")->result();
    }

    public function get_barang_detail_by_kd($kd)
    {
        return $this->db->query("SELECT
        a.nm_barang,
        a.kode_barang,
        b.exp_date,
        b.qty,
        b.barang_pic as PIC,
        b.koordinat_id
        FROM tb_master_barang_all a 
        JOIN tb_saldo_awal b ON b.nama_barang = a.nama_barang
        WHERE a.kode_barang = '$kd'
        ")->result();
    }

    public function edit_mbarang_ics($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_master_barang_all', $data);
    }

    public function get_master_barang_log()
    {
        return $this->db->query("SELECT
        a.id as id,
        a.nama_barang AS nama_barang,
        b.nama_suplier as nama_suplier,
        a.bhn_aktif as bahan_aktif,
        a.satuan as satuan,
        (a.p*a.l*a.t) as dimensi,
        a.berat as berat,
        a.kubikasi as kubikasi
        FROM tb_master_barang_all a
        LEFT JOIN tb_suplier b ON b.kd_suplier = a.kd_suplier
        ")->result();
    }

    public function get_detail_barang($br)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_master_barang_all a
        WHERE a.kd_barang = '$br'
        ")->result();
    }

    public function get_exp_by_kdsys($kd)
    {
        return $this->db->query("SELECT
        a.exp_date
        FROM tb_saldo_awal a
        LEFT JOIN tb_master_barang_all b ON b.nama_barang = a.nama_barang
        WHERE b.kd_barang = '$kd'
        GROUP BY a.exp_date
        ")->result();
    }

    public function tracking_br_diffrent_by_expdate($nmbarang)
    {
        return $this->db->query("SELECT
            a.id,
            opname.id AS opname_id,
            b.kd_barang,
            b.p * b.l * b.t AS dimensi,
            a.nama_barang,
            a.exp_date AS expired,
            (COALESCE(a.qty,0) - COALESCE(mutasi.qty_mutasi,0)) AS qty,
            COALESCE(deliv.qty_out, 0) AS do,
            COALESCE(purchase.qty_po, 0) AS po,
            ((COALESCE(a.qty,0)-COALESCE(mutasi.qty_mutasi,0)) - COALESCE(deliv.qty_out, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
            COALESCE(opname.qty_opname, 0) - (((a.qty-COALESCE(mutasi.qty_mutasi,0)) - COALESCE(deliv.qty_out, 0)) + COALESCE(purchase.qty_po, 0)) AS selisih,
            COALESCE(opname.qty_opname, 0) AS ics,
            COALESCE(opname.qty_box,0)AS qty_box,
            COALESCE(opname.qty_pcs,0)AS qty_pcs,
            a.barang_pic as PIC,
            a.wilayah_id as id_gudang,  
			gdg.nama_gudang as nama_gudang,
            kr.nama_wilayah as nama_wilayah,
            IF(
                ((COALESCE(a.qty,0) - COALESCE(mutasi.qty_mutasi,0) - COALESCE(deliv.qty_out, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0),
                1, 0
            ) AS status
		
        FROM tb_saldo_awal a
        
        JOIN tb_master_barang_all b ON b.kd_barang = a.kode_barang_zahir                
        LEFT JOIN tb_gudang gdg ON gdg.id_gudang = a.wilayah_id
        LEFT JOIN tb_gudang_wilayah kr ON kr.id_wilayah = a.koordinat_id
        
        LEFT JOIN (
            SELECT kd_barang, nama_barang, exp_date, sum(qty) AS qty_po 
            FROM tb_ics_po
            GROUP BY kd_barang, exp_date
        ) purchase ON purchase.kd_barang = a.kode_barang_zahir AND purchase.exp_date = a.exp_date
        
        LEFT JOIN (
        SELECT kd_barang,nama_barang,exp_date,sum(qty) as qty_out
        FROM tb_ics_do 
        GROUP BY kd_barang,exp_date
    	) deliv ON deliv.kd_barang = a.kode_barang_zahir AND deliv.exp_date = a.exp_date
        
        LEFT JOIN (
        SELECT id,kd_system,nama_barang,exp_date,sum(qty) as qty_opname,qty_box,qty_pcs
        FROM tb_ics
	 	GROUP BY kd_system,exp_date
        ) opname ON opname.kd_system = a.kode_barang_zahir AND opname.exp_date = a.exp_date
        
        LEFT JOIN (
            SELECT kode_barang_zahir,exp_date,sum(qty) as qty_mutasi
            FROM tb_detail_mutasi
            WHERE gdg_mutasi != '2'
            GROUP BY kode_barang_zahir,exp_date
        ) mutasi ON mutasi.kode_barang_zahir = a.kode_barang_zahir AND mutasi.exp_date = a.exp_date
        
        WHERE a.nama_barang = '$nmbarang'
        GROUP BY a.kode_barang_zahir, a.exp_date")->result();
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
        COALESCE(mutasi.qty_mutasi,0) as qty_mutasi,
        COALESCE(p.qty_in, 0) AS qty_in,
        FLOOR(COALESCE(p.qty_in, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS in_box,
        MOD(COALESCE(p.qty_in, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS in_pcs,
        COALESCE(d.qty_out, 0) AS qty_out,
        FLOOR(COALESCE(d.qty_out, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS out_box,
        MOD(COALESCE(d.qty_out, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS out_pcs,
        (
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) AS saldo_akhir_qty,
        FLOOR((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) + 
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_box,
        MOD((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_pcs,
        COALESCE(o.qty_opname, 0) AS fisik_ics,
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
        (
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) AS qty_selisih,
        FLOOR((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_box,
        MOD((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_pcs,
        CASE
            WHEN (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            ) = COALESCE(o.qty_opname, 0)
            THEN 'KLOP'
            ELSE 'TIDAK'
        END AS status_kesesuaian
    FROM (
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(barang_pic) AS lokasi , MAX(koordinat_id) AS kordinat , kode_barang_zahir
        FROM tb_saldo_awal
        WHERE barang_pic = 'A'
        GROUP BY kode_barang_zahir, exp_date
    ) x
    LEFT JOIN (
        SELECT kd_barang,nama_barang, exp_date, SUM(qty) AS qty_in , lpb_status
        FROM tb_ics_po
        WHERE lpb_status = '2'
        GROUP BY kd_barang, exp_date
    ) p ON p.kd_barang = x.kode_barang_zahir AND p.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_barang, nama_barang, exp_date, SUM(qty) AS qty_out
        FROM tb_ics_do
        GROUP BY kd_barang, exp_date
    ) d ON d.kd_barang = x.kode_barang_zahir AND d.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_system,nama_barang,exp_date,sum(qty) AS qty_opname,pic,qty_box,qty_pcs
        FROM tb_ics
        WHERE pic = 'A'
        GROUP BY kd_system , exp_date
    ) o ON o.kd_system = x.kode_barang_zahir AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nama_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_barang) AS kd
        FROM tb_master_barang_all
        GROUP BY kd_barang
    ) mb ON mb.kd = x.kode_barang_zahir
    LEFT JOIN(
    	SELECT nama_barang , kode_barang_zahir , exp_date , sum(qty) as qty_mutasi
        FROM tb_detail_mutasi
        WHERE gdg_mutasi != '2'
        GROUP BY kode_barang_zahir,exp_date
    ) mutasi on mutasi.kode_barang_zahir = x.kode_barang_zahir AND mutasi.exp_date = x.exp_date
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        ((COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0)) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
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
        COALESCE(mutasi.qty_mutasi,0) as qty_mutasi,
        COALESCE(p.qty_in, 0) AS qty_in,
        FLOOR(COALESCE(p.qty_in, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS in_box,
        MOD(COALESCE(p.qty_in, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS in_pcs,
        COALESCE(d.qty_out, 0) AS qty_out,
        FLOOR(COALESCE(d.qty_out, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS out_box,
        MOD(COALESCE(d.qty_out, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS out_pcs,
        (
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) AS saldo_akhir_qty,
        FLOOR((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) + 
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_box,
        MOD((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_pcs,
        COALESCE(o.qty_opname, 0) AS fisik_ics,
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
        (
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) AS qty_selisih,
        FLOOR((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_box,
        MOD((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_pcs,
        CASE
            WHEN (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            ) = COALESCE(o.qty_opname, 0)
            THEN 'KLOP'
            ELSE 'TIDAK'
        END AS status_kesesuaian
    FROM (
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(barang_pic) AS lokasi , MAX(koordinat_id) AS kordinat , kode_barang_zahir
        FROM tb_saldo_awal
        WHERE barang_pic = 'B'
        GROUP BY kode_barang_zahir, exp_date
    ) x
    LEFT JOIN (
        SELECT kd_barang,nama_barang, exp_date, SUM(qty) AS qty_in , lpb_status
        FROM tb_ics_po
        WHERE lpb_status = '2'
        GROUP BY kd_barang, exp_date
    ) p ON p.kd_barang = x.kode_barang_zahir AND p.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_barang, nama_barang, exp_date, SUM(qty) AS qty_out
        FROM tb_ics_do
        GROUP BY kd_barang, exp_date
    ) d ON d.kd_barang = x.kode_barang_zahir AND d.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_system,nama_barang,exp_date,sum(qty) AS qty_opname,pic,qty_box,qty_pcs
        FROM tb_ics
        WHERE pic = 'B'
        GROUP BY kd_system , exp_date
    ) o ON o.kd_system = x.kode_barang_zahir AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nama_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_barang) AS kd
        FROM tb_master_barang_all
        GROUP BY kd_barang
    ) mb ON mb.kd = x.kode_barang_zahir
    LEFT JOIN(
    	SELECT nama_barang , kode_barang_zahir , exp_date , sum(qty) as qty_mutasi
        FROM tb_detail_mutasi
        WHERE gdg_mutasi != '2'
        GROUP BY kode_barang_zahir,exp_date
    ) mutasi on mutasi.kode_barang_zahir = x.kode_barang_zahir AND mutasi.exp_date = x.exp_date
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        ((COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0)) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
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
        COALESCE(mutasi.qty_mutasi,0) as qty_mutasi,
        COALESCE(p.qty_in, 0) AS qty_in,
        FLOOR(COALESCE(p.qty_in, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS in_box,
        MOD(COALESCE(p.qty_in, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS in_pcs,
        COALESCE(d.qty_out, 0) AS qty_out,
        FLOOR(COALESCE(d.qty_out, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS out_box,
        MOD(COALESCE(d.qty_out, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS out_pcs,
        (
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) AS saldo_akhir_qty,
        FLOOR((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) + 
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_box,
        MOD((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_pcs,
        COALESCE(o.qty_opname, 0) AS fisik_ics,
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
        (
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) AS qty_selisih,
        FLOOR((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_box,
        MOD((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_pcs,
        CASE
            WHEN (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            ) = COALESCE(o.qty_opname, 0)
            THEN 'KLOP'
            ELSE 'TIDAK'
        END AS status_kesesuaian
    FROM (
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(barang_pic) AS lokasi , MAX(koordinat_id) AS kordinat , kode_barang_zahir
        FROM tb_saldo_awal
        WHERE barang_pic = 'C'
        GROUP BY kode_barang_zahir, exp_date
    ) x
    LEFT JOIN (
        SELECT kd_barang,nama_barang, exp_date, SUM(qty) AS qty_in , lpb_status
        FROM tb_ics_po
        WHERE lpb_status = '2'
        GROUP BY kd_barang, exp_date
    ) p ON p.kd_barang = x.kode_barang_zahir AND p.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_barang, nama_barang, exp_date, SUM(qty) AS qty_out
        FROM tb_ics_do
        GROUP BY kd_barang, exp_date
    ) d ON d.kd_barang = x.kode_barang_zahir AND d.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_system,nama_barang,exp_date,sum(qty) AS qty_opname,pic,qty_box,qty_pcs
        FROM tb_ics
        WHERE pic = 'C'
        GROUP BY kd_system , exp_date
    ) o ON o.kd_system = x.kode_barang_zahir AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nama_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_barang) AS kd
        FROM tb_master_barang_all
        GROUP BY kd_barang
    ) mb ON mb.kd = x.kode_barang_zahir
    LEFT JOIN(
    	SELECT nama_barang , kode_barang_zahir , exp_date , sum(qty) as qty_mutasi
        FROM tb_detail_mutasi
        WHERE gdg_mutasi != '2'
        GROUP BY kode_barang_zahir,exp_date
    ) mutasi on mutasi.kode_barang_zahir = x.kode_barang_zahir AND mutasi.exp_date = x.exp_date
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        ((COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0)) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
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
        COALESCE(mutasi.qty_mutasi,0) as qty_mutasi,
        COALESCE(p.qty_in, 0) AS qty_in,
        FLOOR(COALESCE(p.qty_in, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS in_box,
        MOD(COALESCE(p.qty_in, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS in_pcs,
        COALESCE(d.qty_out, 0) AS qty_out,
        FLOOR(COALESCE(d.qty_out, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS out_box,
        MOD(COALESCE(d.qty_out, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS out_pcs,
        (
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) AS saldo_akhir_qty,
        FLOOR((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) + 
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_box,
        MOD((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_pcs,
        COALESCE(o.qty_opname, 0) AS fisik_ics,
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
        (
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) AS qty_selisih,
        FLOOR((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_box,
        MOD((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_pcs,
        CASE
            WHEN (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            ) = COALESCE(o.qty_opname, 0)
            THEN 'KLOP'
            ELSE 'TIDAK'
        END AS status_kesesuaian
    FROM (
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(barang_pic) AS lokasi , MAX(koordinat_id) AS kordinat , kode_barang_zahir
        FROM tb_saldo_awal
        WHERE barang_pic = 'D'
        GROUP BY kode_barang_zahir, exp_date
    ) x
    LEFT JOIN (
        SELECT kd_barang,nama_barang, exp_date, SUM(qty) AS qty_in , lpb_status
        FROM tb_ics_po
        WHERE lpb_status = '2'
        GROUP BY kd_barang, exp_date
    ) p ON p.kd_barang = x.kode_barang_zahir AND p.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_barang, nama_barang, exp_date, SUM(qty) AS qty_out
        FROM tb_ics_do
        GROUP BY kd_barang, exp_date
    ) d ON d.kd_barang = x.kode_barang_zahir AND d.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_system,nama_barang,exp_date,sum(qty) AS qty_opname,pic,qty_box,qty_pcs
        FROM tb_ics
        WHERE pic = 'D'
        GROUP BY kd_system , exp_date
    ) o ON o.kd_system = x.kode_barang_zahir AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nama_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_barang) AS kd
        FROM tb_master_barang_all
        GROUP BY kd_barang
    ) mb ON mb.kd = x.kode_barang_zahir
    LEFT JOIN(
    	SELECT nama_barang , kode_barang_zahir , exp_date , sum(qty) as qty_mutasi
        FROM tb_detail_mutasi
        WHERE gdg_mutasi != '2'
        GROUP BY kode_barang_zahir,exp_date
    ) mutasi on mutasi.kode_barang_zahir = x.kode_barang_zahir AND mutasi.exp_date = x.exp_date
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        ((COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0)) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
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
        COALESCE(mutasi.qty_mutasi,0) as qty_mutasi,
        COALESCE(p.qty_in, 0) AS qty_in,
        FLOOR(COALESCE(p.qty_in, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS in_box,
        MOD(COALESCE(p.qty_in, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS in_pcs,
        COALESCE(d.qty_out, 0) AS qty_out,
        FLOOR(COALESCE(d.qty_out, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS out_box,
        MOD(COALESCE(d.qty_out, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS out_pcs,
        (
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) AS saldo_akhir_qty,
        FLOOR((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) + 
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_box,
        MOD((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_pcs,
        COALESCE(o.qty_opname, 0) AS fisik_ics,
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
        (
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) AS qty_selisih,
        FLOOR((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_box,
        MOD((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_pcs,
        CASE
            WHEN (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            ) = COALESCE(o.qty_opname, 0)
            THEN 'KLOP'
            ELSE 'TIDAK'
        END AS status_kesesuaian
    FROM (
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(barang_pic) AS lokasi , MAX(koordinat_id) AS kordinat , kode_barang_zahir
        FROM tb_saldo_awal
        WHERE barang_pic = 'E'
        GROUP BY kode_barang_zahir, exp_date
    ) x
    LEFT JOIN (
        SELECT kd_barang,nama_barang, exp_date, SUM(qty) AS qty_in , lpb_status
        FROM tb_ics_po
        WHERE lpb_status = '2'
        GROUP BY kd_barang, exp_date
    ) p ON p.kd_barang = x.kode_barang_zahir AND p.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_barang, nama_barang, exp_date, SUM(qty) AS qty_out
        FROM tb_ics_do
        GROUP BY kd_barang, exp_date
    ) d ON d.kd_barang = x.kode_barang_zahir AND d.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_system,nama_barang,exp_date,sum(qty) AS qty_opname,pic,qty_box,qty_pcs
        FROM tb_ics
        WHERE pic = 'E'
        GROUP BY kd_system , exp_date
    ) o ON o.kd_system = x.kode_barang_zahir AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nama_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_barang) AS kd
        FROM tb_master_barang_all
        GROUP BY kd_barang
    ) mb ON mb.kd = x.kode_barang_zahir
    LEFT JOIN(
    	SELECT nama_barang , kode_barang_zahir , exp_date , sum(qty) as qty_mutasi
        FROM tb_detail_mutasi
        WHERE gdg_mutasi != '2'
        GROUP BY kode_barang_zahir,exp_date
    ) mutasi on mutasi.kode_barang_zahir = x.kode_barang_zahir AND mutasi.exp_date = x.exp_date
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        ((COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0)) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
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
        COALESCE(mutasi.qty_mutasi,0) as qty_mutasi,
        COALESCE(p.qty_in, 0) AS qty_in,
        FLOOR(COALESCE(p.qty_in, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS in_box,
        MOD(COALESCE(p.qty_in, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS in_pcs,
        COALESCE(d.qty_out, 0) AS qty_out,
        FLOOR(COALESCE(d.qty_out, 0) / NULLIF(mb.p * mb.l * mb.t, 0)) AS out_box,
        MOD(COALESCE(d.qty_out, 0), NULLIF(mb.p * mb.l * mb.t, 0)) AS out_pcs,
        (
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) AS saldo_akhir_qty,
        FLOOR((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) + 
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_box,
        MOD((
            COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
            COALESCE(p.qty_in, 0) -
            COALESCE(d.qty_out, 0)
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS saldo_akhir_pcs,
        COALESCE(o.qty_opname, 0) AS fisik_ics,
        COALESCE(o.qty_box, 0) AS fisik_box,
        COALESCE(o.qty_pcs, 0)AS fisik_pcs,     
        (
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) AS qty_selisih,
        FLOOR((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ) / NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_box,
        MOD((
            COALESCE(o.qty_opname, 0) -
            (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            )
        ), NULLIF(mb.p * mb.l * mb.t, 0)) AS selisih_pcs,
        CASE
            WHEN (
                COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0) +
                COALESCE(p.qty_in, 0) -
                COALESCE(d.qty_out, 0)
            ) = COALESCE(o.qty_opname, 0)
            THEN 'KLOP'
            ELSE 'TIDAK'
        END AS status_kesesuaian
    FROM (
        SELECT id, nama_barang, exp_date, SUM(qty) AS saldo_awal_qty, MAX(barang_pic) AS lokasi , MAX(koordinat_id) AS kordinat , kode_barang_zahir
        FROM tb_saldo_awal
        WHERE barang_pic = '0'
        GROUP BY kode_barang_zahir, exp_date
    ) x
    LEFT JOIN (
        SELECT kd_barang,nama_barang, exp_date, SUM(qty) AS qty_in , lpb_status
        FROM tb_ics_po
        WHERE lpb_status = '2'
        GROUP BY kd_barang, exp_date
    ) p ON p.kd_barang = x.kode_barang_zahir AND p.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_barang, nama_barang, exp_date, SUM(qty) AS qty_out
        FROM tb_ics_do
        GROUP BY kd_barang, exp_date
    ) d ON d.kd_barang = x.kode_barang_zahir AND d.exp_date = x.exp_date
    LEFT JOIN (
        SELECT kd_system,nama_barang,exp_date,sum(qty) AS qty_opname,pic,qty_box,qty_pcs
        FROM tb_ics
        WHERE pic = '0'
        GROUP BY kd_system , exp_date
    ) o ON o.kd_system = x.kode_barang_zahir AND o.exp_date = x.exp_date
    LEFT JOIN (
        SELECT nama_barang, MAX(p) AS p, MAX(l) AS l, MAX(t) AS t , MAX(kd_barang) AS kd
        FROM tb_master_barang_all
        GROUP BY kd_barang
    ) mb ON mb.kd = x.kode_barang_zahir
    LEFT JOIN(
    	SELECT nama_barang , kode_barang_zahir , exp_date , sum(qty) as qty_mutasi
        FROM tb_detail_mutasi
        WHERE gdg_mutasi != '2'
        GROUP BY kode_barang_zahir,exp_date
    ) mutasi on mutasi.kode_barang_zahir = x.kode_barang_zahir AND mutasi.exp_date = x.exp_date
    WHERE (
        COALESCE(o.qty_opname, 0) - 
        ((COALESCE(x.saldo_awal_qty, 0) - COALESCE(mutasi.qty_mutasi,0)) + COALESCE(p.qty_in, 0) - COALESCE(d.qty_out, 0))
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
        LEFT JOIN tb_master_barang_all m ON m.nama_barang = a.nama_barang
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
        LEFT JOIN tb_master_barang_all m ON m.nama_barang = a.nama_barang
        WHERE DATE(a.tgl_transaksi) = '$tgl'
        AND (m.p * m.l * m.t) > 0")->result();
    }

    public function list_po()
    {
        return $this->db->query("SELECT		
            a.id,
            a.kd_barang as kd_barang,
            a.kd_faktur_lpb as kd_faktur,
            a.tgl_transaksi,
            a.nama_barang,
            a.qty,
            a.exp_date,
            COALESCE((m.p * m.l * m.t),0) AS dimensi,
            FLOOR(a.qty / (m.p * m.l * m.t)) AS qty_box,
            MOD(a.qty, (m.p * m.l * m.t))    AS qty_pcs,
            a.lpb_note as note,
            a.lpb_status as status
        FROM tb_ics_po a
        LEFT JOIN tb_master_barang_all m ON m.kd_barang = a.kd_barang
        ")->result();
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
        JOIN tb_master_barang_all b ON b.nama_barang = a.nama_barang
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
                kode_barang_zahir as kd_barang,
                nama_barang,
                exp_date,
                barang_pic,
                COUNT(*) AS total
            FROM tb_saldo_awal
            WHERE barang_pic = '$lokasi'
            GROUP BY nama_barang, exp_date, barang_pic
            ORDER BY nama_barang, exp_date
        ")->result();
        }
        return [];
    }

    public function update_pic_saldo_awal($ids, $lokasi)
    {
        $this->db->where_in('id', $ids);
        return $this->db->update('tb_saldo_awal', [
            'barang_pic' => $lokasi
        ]);
    }

    public function update_pic_ics($kd_barang, $exp_date, $lokasi)
    {
        $this->db->where('kd_system', $kd_barang);
        $this->db->where('exp_date', $exp_date);
        return $this->db->update('tb_ics', [
            'pic' => $lokasi
        ]);
    }



    public function total_barang_pic()
    {
        return $this->db->query("SELECT
        barang_pic, COUNT(DISTINCT CONCAT(nama_barang, '-', exp_date)) AS total_barang
        FROM tb_saldo_awal
        GROUP BY barang_pic
        ORDER BY barang_pic
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

    public function barangper_gudang($id_gudang = null)
    {
        if ($id_gudang === null) {
            $induk = $this->get_gudang_induk();
            $id_gudang = $induk ? $induk->id_gudang : 0;
        }

        return $this->db->query("SELECT
            a.*,
            (b.p*b.l*b.t) AS dimensi,
            FLOOR(a.qty/(b.p*b.l*b.t)) AS qty_box,
            MOD(a.qty,(b.p*b.l*b.t)) AS qty_pcs
        FROM v_stock_per_gudang a
        JOIN tb_master_barang_all b 
            ON b.kd_barang = a.kode_barang
        WHERE a.gudang = ?
        ORDER BY a.nama_barang ASC
    ", [$id_gudang])->result();
    }


    public function get_gudang_induk()
    {
        return $this->db->query("SELECT id_gudang 
        FROM tb_gudang
        WHERE id_gudang = '2'
        LIMIT 1
    ")->row();
    }


    public function get_gudang()
    {
        return $this->db->query("SELECT * FROM tb_gudang
        ")->result();
    }

    public function get_barang_select2($search = '')
    {
        $this->db->select('nama_barang');
        $this->db->from('tb_master_barang_all');

        if ($search) {
            $this->db->like('nama_barang', $search);
        }

        $this->db->group_by('nama_barang');
        $this->db->order_by('nama_barang', 'ASC');
        $this->db->limit(20);

        return $this->db->get()->result();
    }


    public function get_expired_by_barang($nmbarang)
    {
        return $this->db->query("
        SELECT DISTINCT exp_date, id
        FROM tb_saldo_awal
        WHERE nama_barang = ?
        ORDER BY exp_date ASC
    ", [$nmbarang])->result();
    }

    public function get_barang_by_gudang_select2($id_gudang, $search = '')
    {
        $this->db->select('a.nama_barang');
        $this->db->from('v_stock_per_gudang a');
        $this->db->where('a.gudang', $id_gudang);

        if ($search) {
            $this->db->like('a.nama_barang', $search);
        }

        $this->db->group_by('a.nama_barang');
        $this->db->order_by('a.nama_barang', 'ASC');
        $this->db->limit(20);

        return $this->db->get()->result();
    }

    public function get_exp_by_gudang_barang($id_gudang, $nama_barang)
    {
        return $this->db->query("SELECT DISTINCT exp_date
            FROM v_stock_per_gudang
            WHERE gudang = ?
            AND nama_barang = ?
            ORDER BY exp_date ASC 
          ", [$id_gudang, $nama_barang])->result();
    }

    public function get_qty_by_gudang_barang_exp($id_gudang, $nama_barang, $expired_date)
    {
        $row = $this->db->query("SELECT
            x.gudang,
            x.nama_barang,
            x.exp_date,
            (
                COALESCE(x.saldo_awal_qty, 0)
                + COALESCE(p.qty_in, 0)
                - COALESCE(d.qty_out, 0)
            ) AS qtygudang
        FROM (
            SELECT
                gudang AS gudang,
                nama_barang,
                exp_date,
                SUM(qty) AS saldo_awal_qty
            FROM v_stock_per_gudang
            WHERE gudang = ?
            GROUP BY gudang, nama_barang, exp_date
        ) x
        LEFT JOIN (
            SELECT
                nama_barang,
                exp_date,
                SUM(qty) AS qty_in
            FROM tb_ics_po
            GROUP BY nama_barang, exp_date
        ) p 
        ON p.nama_barang = x.nama_barang
        AND p.exp_date = x.exp_date
        LEFT JOIN (
            SELECT
                nama_barang,
                exp_date,
                SUM(qty) AS qty_out
            FROM tb_ics_do
            GROUP BY nama_barang, exp_date
        ) d 
        ON d.nama_barang = x.nama_barang
        AND d.exp_date = x.exp_date
        WHERE x.nama_barang = ? AND x.exp_date = ?
        ORDER BY x.gudang, x.nama_barang, x.exp_date;

    ", [$id_gudang, $nama_barang, $expired_date])->row();

        return $row ? (int)$row->qtygudang : 0;
    }

    public function insert_tmp_mutasi($data)
    {
        return $this->db->insert('tb_tmp_mutasi', $data);
    }

    public function insert_mutasi($data)
    {
        return $this->db->insert('tb_mutasi', $data);
    }
    public function insert_log($data)
    {
        return $this->db->insert('tb_log_mutasi', $data);
    }
    public function clear_tmp($user)
    {
        return $this->db->where('user_inputer', $user)->delete('tb_tmp_mutasi');
    }

    public function get_tmp_mutasi_by_user($user_id)
    {

        $sql = "SELECT 
        a.id,b.kode_barang_system,b.kd_barang,a.nama_barang,a.exp_date,a.qty,a.satuan_id,a.user_inputer
        FROM tb_tmp_mutasi a
        LEFT JOIN tb_master_barang_all b ON b.nama_barang = a.nama_barang
        WHERE a.user_inputer = '$user_id'";
        return $this->db->query($sql)->result();
    }

    public function update_tmp_mutasi($id, $user_id, $data)
    {
        return $this->db->where([
            'id' => $id,
            'user_inputer' => $user_id
        ])->update('tb_tmp_mutasi', $data);
    }

    public function delete_tmp_mutasi($id, $user_id)
    {
        return $this->db->where([
            'id' => $id,
            'user_inputer' => $user_id
        ])->delete('tb_tmp_mutasi');
    }


    public function clear_tmp_mutasi($user_id)
    {
        return $this->db->where('user_inputer', $user_id)
            ->delete('tb_tmp_mutasi');
    }

    // LOGISTIK V2

    protected $view = 'v_saldo_stock';

    public function get_saldo($filter = [])
    {
        if (!empty($filter['kode_barang_system'])) {
            $this->db->where('kode_barang_system', $filter['kode_barang_system']);
        }

        if (!empty($filter['id_gudang'])) {
            $this->db->where('id_gudang', $filter['id_gudang']);
        }

        if (!empty($filter['no_lot'])) {
            $this->db->where('no_lot', $filter['no_lot']);
        }

        if (!empty($filter['exp_date'])) {
            $this->db->where('exp_date', $filter['exp_date']);
        }

        return $this->db->get($this->view)->result();
    }

    public function create_header_lpb($data)
    {
        return $this->db->insert('ics_lpb_header', $data);
    }

    public function create_detail_lpn($data)
    {
        return $this->db->insert_batch('ics_lpb_detail', $data);
    }

    public function post_lpb($kode_faktur)
    {
        $this->db->where('kode_faktur', $kode_faktur)
            ->update('ics_lpb_header', ['status' => 'POSTED']);
    }

    public function get_faktur_mutasi()
    {
        $sql = "SELECT 
        a.id,
        a.keterangan,
        a.noreff,
        a.tgl_transaksi,
        b.nama_gudang as gudang_a,
        c.nama_gudang as gudang_b,
        a.status,
        d.nm_karyawan
        FROM tb_mutasi a
        JOIN tb_gudang b ON b.id_gudang = a.gudang_asal
        JOIN tb_gudang c ON c.id_gudang = a.gudang_mutasi
        JOIN tb_karyawan d ON d.nik = a.inputer
        ";
        return $this->db->query($sql)->result();
    }

    public function filter_mutasi($gudang, $daterange, $status)
    {
        $this->db->select("
        a.*, 
        b.nama_gudang gudang_a,
        c.nama_gudang gudang_b,
        d.nm_karyawan
    ");
        $this->db->from('tb_mutasi a');
        $this->db->join('tb_gudang b', 'b.id_gudang=a.gudang_asal');
        $this->db->join('tb_gudang c', 'c.id_gudang=a.gudang_mutasi');
        $this->db->join('tb_karyawan d', 'd.nik=a.inputer');

        if ($gudang) $this->db->where('a.gudang_asal', $gudang);
        if ($status) $this->db->where('a.status', $status);

        if ($daterange) {
            [$start, $end] = explode(' - ', $daterange);
            $this->db->where('a.tgl_transaksi >=', date('Y-m-d', strtotime($start)));
            $this->db->where('a.tgl_transaksi <=', date('Y-m-d', strtotime($end)));
        }

        return $this->db->get()->result();
    }

    public function generate_noreff()
    {
        $date = date('Ymd');

        $this->db->like('noreff', "KIUMTSI$date", 'after');
        $this->db->order_by('id', 'DESC');
        $last = $this->db->get('tb_mutasi')->row();

        $no = 1;
        if ($last) {
            $no = (int) substr($last->noreff, -4) + 1;
        }

        return "KIUMTSI$date" . str_pad($no, 4, '0', STR_PAD_LEFT);
    }

    public function get_mutasi_header($noreff)
    {
        return $this->db
            ->select('
            h.*,
            gdga.nama_gudang AS nama_gudang_asal,
            gdgb.nama_gudang AS nama_gudang_tujuan
        ')
            ->from('tb_mutasi h')
            ->join('tb_gudang gdga', 'gdga.id_gudang = h.gudang_asal', 'left')
            ->join('tb_gudang gdgb', 'gdgb.id_gudang = h.gudang_mutasi', 'left')
            ->where('h.noreff', $noreff)
            ->get()
            ->row();
    }


    public function get_mutasi_detail($noreff, $status)
    {
        if ($status === 'HOLD') {
            return $this->db
                ->where('noref', $noreff)
                ->get('tb_stock_hold')
                ->result();
        }

        return $this->db
            ->where('noreff', $noreff)
            ->get('tb_detail_mutasi')
            ->result();
    }


    public function query_view_saldo()
    {
        return $this->db->query("SELECT
            g.id_gudang,
            g.nama_gudang,
            mb.kd_barang_zahir,
            mb.kd_barang,
            sa.nama_barang,
            sa.exp_date,
            IFNULL(sa.qty, 0) AS saldo_awal,
            IFNULL(po.qty_in, 0) AS qty_in,
            IFNULL(do.qty_out, 0) AS qty_do,
            IFNULL(mu.qty_mutasi_out, 0) AS qty_mutasi,
            (
                IFNULL(sa.qty, 0)
                + IFNULL(po.qty_in, 0)
                - IFNULL(do.qty_out, 0)
                - IFNULL(mu.qty_mutasi_out, 0)
            ) AS saldo_realtime
        FROM tb_saldo_awal sa
        JOIN tb_master_barang_all mb
            ON mb.kd_barang = sa.kode_barang_system
        JOIN tb_gudang_wilayah gw
            ON gw.id_wilayah = sa.wilayah_id
        JOIN tb_gudang g
            ON g.id_gudang = gw.id_gudang
        LEFT JOIN (
            SELECT
                nama_barang,
                exp_date,
                SUM(qty) AS qty_in
            FROM tb_ics_po
            WHERE lpb_status = '1'
            GROUP BY nama_barang, exp_date
        ) po
            ON po.nama_barang = sa.nama_barang
            AND po.exp_date = sa.exp_date
        LEFT JOIN (
            SELECT
                nama_barang,
                tgl_exp,
                SUM(qty) AS qty_out
            FROM tb_detail_do
            WHERE status = '4'
            GROUP BY nama_barang, tgl_exp
        ) do
            ON do.nama_barang = sa.nama_barang
            AND do.tgl_exp = sa.exp_date
        LEFT JOIN (
            SELECT
                kode_barang,
                exp_date,
                gdg_asal,
                SUM(qty) AS qty_mutasi_out
            FROM tb_detail_mutasi
            GROUP BY kode_barang, exp_date, gdg_asal
        ) mu
            ON mu.kode_barang = sa.kode_barang_system
            AND mu.exp_date = sa.exp_date
            AND mu.gdg_asal = g.id_gudang
        ")->result();
    }
}
