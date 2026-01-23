<?php
defined('BASEPATH') or exit('No direct script access allowed');


class M_Distribusi extends CI_Model
{

    public function tonase_all_do_done()
    {
        return $this->db->query("SELECT
            r.kd_rute AS rute,
            COALESCE(ROUND(SUM(CASE WHEN p.data_sts = '3' AND p.delivery_at <> '0000-00-00' THEN (b.berat * p.qty) ELSE 0 END ) / 1000000, 3), 0) AS tonase_terkirim,
            COALESCE(ROUND(SUM(CASE WHEN p.data_sts <> '3'THEN (b.berat * p.qty)ELSE 0 END) / 1000000, 3),0) AS tonase_belum_terkirim,
            COALESCE(ROUND(SUM(b.berat * p.qty) / 1000000, 3),0) AS total_tonase,
            COUNT(DISTINCT CASE WHEN p.data_sts = '3' AND p.delivery_at <> '0000-00-00' THEN p.kd_faktur END) AS total_faktur_terkirim,
            COUNT(DISTINCT CASE WHEN p.data_sts != '3' THEN p.kd_faktur END) AS total_faktur_pending,
            COUNT(DISTINCT p.kd_faktur) AS total_faktur 
        FROM tb_rutecs r
        LEFT JOIN tb_pre_do p
            ON p.kd_rute = r.kd_rute
        LEFT JOIN tb_master_barang_all b
            ON b.kd_barang = p.kd_barang
        GROUP BY r.kd_rute
        ORDER BY tonase_terkirim DESC;
        ")->result();
    }
    public function total_tonase_by_rute()
    {
        return $this->db->query("SELECT
            r.kd_rute AS rute,
            COALESCE(ROUND(SUM(CASE WHEN p.data_sts = '3' AND p.delivery_at BETWEEN '2026-01-20' AND '2026-01-22' THEN (b.berat * p.qty) ELSE 0 END ) / 1000000, 3),0) AS tonase_terkirim,
            COALESCE(ROUND(SUM(CASE WHEN p.data_sts <> '3' AND p.delivery_at BETWEEN '2026-01-20' AND '2026-01-22' THEN (b.berat * p.qty) ELSE 0 END) / 1000000, 3),0) AS tonase_belum_terkirim,
            COALESCE(ROUND(SUM(CASE WHEN p.delivery_at BETWEEN '2026-01-20' AND '2026-01-22' THEN (b.berat * p.qty) ELSE 0 END) / 1000000, 3),0) AS total_tonase,
            COUNT(DISTINCT CASE WHEN p.data_sts = '3' AND p.delivery_at BETWEEN '2026-01-20' AND '2026-01-22' THEN p.kd_faktur END) AS total_faktur_terkirim,
            COUNT(DISTINCT CASE WHEN p.data_sts <> '3' AND p.delivery_at BETWEEN '2026-01-20' AND '2026-01-22' THEN p.kd_faktur END) AS total_faktur_pending,
            COUNT(DISTINCT CASE WHEN p.delivery_at BETWEEN '2026-01-20' AND '2026-01-22' THEN p.kd_faktur END) AS total_faktur
        FROM tb_rutecs r
        LEFT JOIN tb_pre_do p
            ON p.kd_rute = r.kd_rute
        LEFT JOIN tb_master_barang_all b
            ON b.kd_barang = p.kd_barang
        GROUP BY r.kd_rute
        ORDER BY tonase_terkirim DESC;")->result();
    }

    public function get_driver_rute_matrix($tanggal)
    {
        $rute = $this->db->get('tb_rutecs')->result();
        $driver = $this->db->get('tb_op_driver')->result();
        $this->db->select('driver, regional, COUNT(*) as total');
        $this->db->from('tb_do');
        $this->db->where('status', '2');

        if (!empty($tanggal)) {
            $tgl = explode(' - ', $tanggal);
            $this->db->where('tgl_pengiriman >=', $tgl[0]);
            $this->db->where('tgl_pengiriman <=', $tgl[1]);
        }

        $this->db->group_by(['driver', 'regional']);
        $do = $this->db->get()->result();

        $map = [];
        foreach ($do as $d) {
            $map[$d->driver][$d->regional] = $d->total;
        }

        $data = [];
        foreach ($driver as $drv) {
            $row = [
                'kd_driver'   => $drv->kd_driver,
                'nama_driver' => $drv->nama_driver,
                'rute'        => $map[$drv->kd_driver] ?? []
            ];
            $data[] = $row;
        }

        return [
            'rute' => $rute,
            'data' => $data
        ];
    }


    public function all_driver()
    {
        return $this->db->query("SELECT * 
        FROM `tb_op_driver`
        ")->result();
    }

    public function all_rute()
    {
        return $this->db->query("SELECT * FROM `tb_rutecs`")->result();
    }

    public function ploting_rute($rute, $tanggal)
    {
        $this->db->select("
        d.kd_driver,
        d.nama_driver AS nama,
        COALESCE(do.tgl_pengiriman, 'BELUM ADA') AS tanggal_pengiriman
    ", false);

        $this->db->from('tb_op_driver d');
        $join = '
        do.driver = d.kd_driver
        AND do.status = "2"
        AND do.regional = ' . $this->db->escape($rute);

        if (!empty($tanggal)) {
            $tgl = explode(' - ', $tanggal);
            $join .= ' AND do.tgl_pengiriman BETWEEN '
                . $this->db->escape($tgl[0])
                . ' AND '
                . $this->db->escape($tgl[1]);
        }

        $this->db->join('tb_do do', $join, 'left');

        $this->db->order_by('d.nama_driver', 'ASC');

        return $this->db->get()->result();
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

    public function driver_histori($rute)
    {
        return $this->db->query("SELECT 
            d.kd_driver,
            d.nama_driver AS nama,
            COALESCE(do.tgl_pengiriman, 'BELUM ADA') AS tanggal_pengiriman
        FROM tb_op_driver d
        LEFT JOIN tb_do do 
            ON do.driver = d.kd_driver
            AND do.regional = '$rute'
            AND do.status = '2'
            AND YEARWEEK(do.tgl_pengiriman, 1) = YEARWEEK(CURDATE(), 1)
        ORDER BY d.nama_driver;")->result();
    }

    public function tmplate()
    {
        return $this->db->query("")->result();
    }

    public function total_tonase()
    {
        return $this->db->query("SELECT 
            ROUND(SUM(b.berat * d.qty) / 1000000, 3) AS total_tonase
        FROM tb_detail_do d
        JOIN tb_master_barang_all b 
            ON b.kd_barang = d.kd_barang
        JOIN tb_do o 
            ON o.kd_do = d.kd_do
        WHERE o.tgl_pengiriman BETWEEN '2026-01-01' AND '2026-01-31' AND o.status = '2'")->result();
    }

    public function tonase_terkirim()
    {
        return $this->db->query("SELECT 
            ROUND(SUM(b.berat * d.qty) / 1000000, 3) AS tonase_terkirim
        FROM tb_detail_do d
        JOIN tb_master_barang_all b 
            ON b.kd_barang = d.kd_barang
        JOIN tb_do o 
            ON o.kd_do = d.kd_do
        WHERE d.status = '4'
        AND o.tgl_pengiriman BETWEEN '2026-01-01' AND '2026-01-31' AND o.status = '2';
        ")->result();
    }
}
