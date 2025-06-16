<?php

use JetBrains\PhpStorm\Internal\ReturnTypeContract;

defined('BASEPATH') or exit('No direct script access allowed');


class M_Logistik extends CI_Model
{
    //Truck Plat Config
    public function getallplat()
    {
        return $this->db->get('tb_op_plat')->result();
    }
    public function addnoplatbaru($data)
    {
        return $this->db->insert('tb_op_plat', $data);
    }
    public function editnoplat($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_op_plat', $data);
    }
    public function deletenoplat($id)
    {
        return $this->db->delete('tb_op_plat', array("id" => $id));
    }
    //Helper Config
    public function getallhelper()
    {
        return $this->db->get('tb_op_helper')->result();
    }
    public function addhelperbaru($data)
    {
        return $this->db->insert('tb_op_helper', $data);
    }
    public function edithelper($id, $data)
    {
        $this->db->where('kd_helper', $id);
        return $this->db->update('tb_op_helper', $data);
    }
    public function hapushelper($id)
    {
        return $this->db->delete('tb_op_helper', array("id" => $id));
    }
    function kd_helper()
    {
        $cd = $this->db->query("SELECT MAX(RIGHT(kd_helper,4)) AS kd_max FROM tb_op_helper WHERE DATE(create_at)=CURDATE()");
        $kd = "";
        if ($cd->num_rows() > 0) {
            foreach ($cd->result() as $k) {
                $tmp = ((int)$k->kd_max) + 1;
                $kd = sprintf("%04s", $tmp);
            }
        } else {
            $kd = "0001";
        }
        date_default_timezone_set('Asia/Jakarta');
        return 'KIUH' . date('dmy') . $kd;
    }
    //Driver Config
    public function getalldriver()
    {
        return $this->db->get('tb_op_driver')->result();
    }
    public function getnorutdriveractive()
    {
        $this->db->select('*');
        $this->db->from('tb_op_driver');
        $this->db->where('status', 'ACTIVE');
        $this->db->order_by('no_urut_hr_i', 'ASC');
        return $this->db->get()->result();
    }
    public function adddriverbaru($data)
    {
        return $this->db->insert('tb_op_driver', $data);
    }
    public function editdriver($id, $data)
    {
        $this->db->where('kd_driver', $id);
        return $this->db->update('tb_op_driver', $data);
    }
    public function hapusdriver($id)
    {
        return $this->db->delete('tb_op_driver', array("id" => $id));
    }
    function kd_driver()
    {
        $cd = $this->db->query("SELECT MAX(RIGHT(kd_driver,4)) AS kd_max FROM tb_op_driver WHERE DATE(create_at)=CURDATE()");
        $kd = "";
        if ($cd->num_rows() > 0) {
            foreach ($cd->result() as $k) {
                $tmp = ((int)$k->kd_max) + 1;
                $kd = sprintf("%04s", $tmp);
            }
        } else {
            $kd = "0001";
        }
        date_default_timezone_set('Asia/Jakarta');
        return 'KIU' . date('dmy') . $kd;
    }
    function get_kd_order()
    {
        $cd = $this->db->query("SELECT MAX(RIGHT(kd_order,4)) as kd_max FROM tb_order_tracking_driver WHERE DATE(create_at)=CURDATE()");
        $kd = "";
        if ($cd->num_rows() > 0) {
            foreach ($cd->result() as $k) {
                $tmp = ((int)$k->kd_max) + 1;
                $kd = sprintf("%04s", $tmp);
            }
        } else {
            $kd = "0001";
        }
        date_default_timezone_set('Asia/Jakarta');
        return 'KIUD' . date('dmy') . $kd;
    }
    function insert_detail_order_driver($data)
    {
        return $this->db->insert('tb_det_tracking_driver', $data);
    }
    function insert_deliveri_order($data)
    {
        return $this->db->insert('tb_order_tracking_driver', $data);
    }

    function get_deliv_logistik()
    {
        return $this->db->query("SELECT 
        *, 
        COUNT(CASE WHEN  b.sts_driver = 'READY' then 1 ELSE NULL END) as 'd_ready' ,
        COUNT(CASE WHEN  b.sts_driver = 'PENDING' then 1 ELSE NULL END) as 'd_pending',
        COUNT(CASE WHEN b.sts_driver = 'ON THE ROAD' THEN 1 ELSE NULL END) as 'd_otr',
        COUNT(CASE WHEN b.sts_driver = 'WAITING' THEN 1 ELSE NULL END) as 'd_wait'
        FROM tb_order_tracking_driver a
        JOIN tb_det_tracking_driver b ON b.kd_deliveri = a.kd_order
        GROUP BY a.kd_order
        ORDER BY a.id
        ");
    }
    function get_all_do()
    {
        return $this->db->query("SELECT 
       *, 
       COUNT(CASE WHEN  b.sts_driver = 'READY' then 1 ELSE NULL END) as 'd_ready' ,
       COUNT(CASE WHEN  b.sts_driver = 'PENDING' then 1 ELSE NULL END) as 'd_pending',
       COUNT(CASE WHEN b.sts_driver = 'ON THE ROAD' THEN 1 ELSE NULL END) as 'd_otr'
       FROM tb_order_tracking_driver a
       JOIN tb_det_tracking_driver b ON b.kd_deliveri = a.kd_order
       WHERE  YEARWEEK(a.create_at, 1) = YEARWEEK(CURDATE(), 1)
       GROUP BY a.kd_order
       ORDER BY a.id
       ");
    }
    public function get_driver($kduser)
    {
        $this->db->select('*');
        $this->db->limit('6');
        $this->db->from('tb_op_driver');
        $this->db->like('nama_driver', $kduser);
        return $this->db->get()->result_array();
    }
    function get_order($kd)
    {
        $this->db->select('*');
        $this->db->from('tb_order_tracking_driver');
        $this->db->where('kd_order', $kd);
        $this->db->limit(1);
        return $this->db->get()->result();
    }
    function get_det_deliv($kd)
    {
        return $this->db->query("SELECT 
        a.kd_helper,a.jml_kios,a.tonase,a.kubikasi,d.nama_helper,a.id,a.norut,a.tgl_jalan, a.nm_toko, a.kd_deliveri , a.kd_driver ,b.nama_driver, a.kd_truk , COALESCE(c.noplat,'-') as noplat , a.destinasi , a.sts_driver , COALESCE(NULLIF(a.keterangan,''),'-') as keterangan 
        FROM tb_det_tracking_driver a JOIN tb_op_driver b ON b.kd_driver = a.kd_driver LEFT JOIN tb_op_plat c ON c.nm_truk = a.kd_truk LEFT JOIN tb_op_helper d ON d.kd_helper = a.kd_helper 
        WHERE a.kd_deliveri = '$kd' 
        GROUP BY a.kd_driver");
    }
    function export_tracking()
    {
        return $this->db->query("SELECT a.id,a.norut,a.tgl_jalan, a.nm_toko, a.kd_deliveri,b.nama_driver,d.nama_helper ,a.kd_truk , COALESCE(c.noplat,'-') as noplat , a.destinasi ,a.jml_kios,a.tonase,a.kubikasi ,a.sts_driver , COALESCE(NULLIF(a.keterangan,''),'-') as keterangan 
        FROM tb_det_tracking_driver a 
        JOIN tb_op_driver b ON b.kd_driver = a.kd_driver 
        JOIN tb_op_helper d ON d.kd_helper = a.kd_helper
        LEFT JOIN tb_op_plat c ON c.nm_truk = a.kd_truk");
    }
    function get_det_jalan_driver($kdorder, $driver)
    {
        return $this->db->query("SELECT a.id, a.kd_deliveri , a.destinasi,a.tgl_jalan ,a.kd_driver ,b.nama_driver, a.kd_truk , c.noplat , a.nm_toko FROM tb_det_tracking_driver a JOIN tb_op_driver b ON b.kd_driver = a.kd_driver JOIN tb_op_plat c ON c.nm_truk = a.kd_truk WHERE a.kd_deliveri = '$kdorder' AND a.kd_driver = '$driver';");
    }
    public function get_kd($kd)
    {
        return $this->db->query("SELECT a.id, a.kd_deliveri , a.destinasi,a.tgl_jalan ,a.kd_driver ,b.nama_driver, a.kd_truk , c.noplat , a.nm_toko FROM tb_det_tracking_driver a JOIN tb_op_driver b ON b.kd_driver = a.kd_driver JOIN tb_op_plat c ON c.nm_truk = a.kd_truk WHERE a.kd_deliveri = '$kd' LIMIT 1");
    }
    function insert_pnd_driver($data)
    {
        return $this->db->insert_batch('tb_driver_pending', $data);
    }
    function delete_tr_detail_driver($id)
    {
        return $this->db->delete('tb_det_tracking_driver', array("id" => $id));
    }
    function get_pnd_driver()
    {
        return $this->db->query("SELECT a.kd_deliveri , a.tgl_jalan , c.nama_driver , b.noplat , a.kd_truk , a.destinasi , COUNT(a.nm_toko) AS jml_toko ,a.note_pending, a.kd_driver
        FROM tb_driver_pending a
        join tb_op_plat b ON b.nm_truk = a.kd_truk
        JOIN tb_op_driver c ON c.kd_driver = a.kd_driver
        GROUP BY a.kd_deliveri , a.kd_driver
        ");
    }
    function get_det_driver_pnd($kd1, $kd2)
    {
        return $this->db->query("SELECT a.id,a.kd_deliveri , a.tgl_jalan , c.nama_driver , b.noplat , a.kd_truk , a.destinasi ,a.nm_toko ,a.note_pending, a.kd_driver
        FROM tb_driver_pending a
        join tb_op_plat b ON b.nm_truk = a.kd_truk
        JOIN tb_op_driver c ON c.kd_driver = a.kd_driver
        WHERE a.kd_deliveri = '$kd1' AND a.kd_driver = '$kd2'");
    }
    public function get_kd_det_pnd($kd)
    {
        return $this->db->query("SELECT a.id, a.kd_deliveri , a.destinasi,a.tgl_jalan ,a.kd_driver ,b.nama_driver, a.kd_truk , c.noplat , a.nm_toko FROM tb_det_tracking_driver a JOIN tb_op_driver b ON b.kd_driver = a.kd_driver JOIN tb_op_plat c ON c.nm_truk = a.kd_truk WHERE a.kd_deliveri = '$kd' LIMIT 1");
    }
    public function get_all_driver()
    {
        $this->db->select('*');
        $this->db->from('tb_op_driver');
        $this->db->where('status', 'ACTIVE');
        $this->db->order_by("no_urut_hr_i", "asc");
        return $this->db->get()->result();
    }
    public function select_kd_truk()
    {
        $this->db->select('*');
        $this->db->from('tb_op_plat');
        return $this->db->get()->result();
    }
    public function select_kd_helper()
    {
        $this->db->select('*');
        $this->db->from('tb_op_helper');
        $this->db->where('status', 'ACTIVE');
        return $this->db->get()->result();
    }
    public function get_data_driver()
    {
        return $this->db->query("SELECT b.nama_driver , b.kd_driver, 
        COUNT(CASE WHEN  a.sts_driver = 'READY' then 1 ELSE NULL END) + COUNT(CASE WHEN  a.sts_driver = 'ON THE ROAD' then 1 ELSE NULL END)  as 'd_ready',
        COUNT(CASE WHEN  a.sts_driver = 'PENDING' then 1 ELSE NULL END) as 'd_pending',
        ROUND((COUNT(CASE WHEN  a.sts_driver = 'READY' then 1 ELSE NULL END) + COUNT(CASE WHEN  a.sts_driver = 'ON THE ROAD' then 1 ELSE NULL END)) / (COUNT(CASE WHEN  a.sts_driver = 'READY' then 1 ELSE NULL END) + COUNT(CASE WHEN  a.sts_driver = 'ON THE ROAD' then 1 ELSE NULL END) + COUNT(CASE WHEN  a.sts_driver = 'PENDING' then 1 ELSE NULL END)) * 100 , 2) AS persentase
        FROM tb_det_tracking_driver a
        JOIN tb_op_driver b ON b.kd_driver = a.kd_driver
        GROUP BY a.kd_driver         
        ");
    }
    public function get_det_tracking($kd)
    {
        return $this->db->query("SELECT a.sts_driver,d.nama_helper,a.kd_deliveri , a.tgl_jalan ,a.kd_truk , COALESCE(c.noplat,'-') AS noplat , a.destinasi,COALESCE(NULLIF(a.keterangan,''),'-') AS keterangan
        FROM tb_det_tracking_driver a
        LEFT JOIN tb_op_driver b ON b.kd_driver = a.kd_driver
        LEFT JOIN tb_op_plat c on c.nm_truk = a.kd_truk
        LEFT JOIN tb_op_helper d on d.kd_helper = a.kd_helper
        WHERE b.kd_driver = '$kd'
        GROUP BY a.kd_deliveri
        ");
    }
    public function get_det_data_driver($kd)
    {
        return $this->db->query("SELECT b.nama_driver , b.kd_driver
        FROM tb_det_tracking_driver a
        JOIN tb_op_driver b ON b.kd_driver = a.kd_driver
        WHERE b.kd_driver = '$kd' 
        GROUP BY a.kd_driver 
        ");
    }
    public function get_deliv($kd)
    {
        $this->db->select('*');
        $this->db->from('tb_det_tracking_driver a');
        $this->db->join('tb_op_driver b', 'b.kd_driver = a.kd_driver', 'left');
        $this->db->join('tb_op_plat c', 'c.nm_truk = a.kd_truk', 'left');
        $this->db->join('tb_op_helper d', 'd.kd_helper = a.kd_helper', 'left');
        $this->db->where('kd_deliveri', $kd);
        return $this->db->get()->result();
    }
    public function detail_deliv($kd)
    {
        $this->db->select('*');
        $this->db->from('tb_order_tracking_driver');
        $this->db->where('kd_order', $kd);
        return $this->db->get()->result();
    }
    public function edit_detail_order_driver($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_det_tracking_driver', $data);
    }
    public function deleteorder($id)
    {
        return $this->db->delete('tb_order_tracking_driver', array("kd_order" => $id));
    }
    public function deletedetailorder($id)
    {
        return $this->db->delete('tb_det_tracking_driver', array('kd_deliveri' => $id));
    }
    public function export_lap_distribusi()
    {
        return $this->db->get('tb_lap_distribusi')->result();
    }

    function get_tmp_distribusi()
    {
        return $this->db->query("SELECT a.*,b.noplat,b.nm_truk,c.nama_driver,d.nama_helper
        FROM tb_tmp_lap_distribusi a
        join tb_op_plat b ON b.nm_truk = a.kd_truk
        JOIN tb_op_driver c ON c.kd_driver = a.kd_driver
        JOIN tb_op_helper d ON d.kd_helper = a.kd_helper
        WHERE a.status ='ready'
        ");
    }
    public function insert_lap_distribusi($data)
    {
        return $this->db->insert('tb_lap_distribusi', $data);
    }
    public function edited_tmp_lap_dis($id, $data)
    {
        $this->db->where('id_lap_dis', $id);
        return $this->db->update('tb_tmp_lap_distribusi', $data);
    }

    public function delete_tmp_lap_dis($id)
    {
        return $this->db->delete('tb_tmp_lap_distribusi', array('id_lap_dis' => $id));
    }

    public function get_grouped_regions()
    {
        $this->db->select('nama_regional, GROUP_CONCAT(id SEPARATOR ",") as ids');
        $this->db->from('your_table'); // Ganti dengan nama tabel yang sesuai
        $this->db->group_by('nama_regional');
        return $this->db->get()->result();
    }

    public function get_updated_data_preparation()
    {
        return $this->db->query("SELECT
        a.*,
        b.data_sts AS statusdata
        FROM tb_stock_status a
        JOIN tb_pre_do b ON b.kdupdate = a.kd_update
        WHERE a.gudangid = '6'
        LIMIT 1
        ")->result();
    }

    public function get_data_penjualan_zahir()
    {
        $this->db->select('
            a.tgl_inputer,
            a.kd_faktur,
            b.nama_customer,
            b.nama_kios,
            b.alamat_kios,
            b.regional,
            a.kd_rute,
            c.keterangan AS keterangan_rute,
            COUNT(DISTINCT a.kd_barang) AS total_barang,
            a.data_sts 
        ');
        $this->db->from('tb_pre_do a');
        $this->db->join('tb_customer b', 'b.kd_customer = a.kd_customer', 'inner');
        $this->db->join('tb_rutecs c', 'c.kd_rute = a.kd_rute', 'inner');
        $this->db->join('tb_detail_do d', 'd.kd_faktur = a.kd_faktur', 'left');
        $this->db->where('a.data_sts', 1);
        $this->db->where('d.kd_faktur IS NULL', null, false);
        $this->db->group_by('a.kd_faktur');

        $query = $this->db->get();
        return $query->result();
    }

    public function get_list_by_rute($rute)
    {
        $this->db->select('
            a.tgl_inputer,
            a.kd_faktur,
            b.nama_customer,
            b.nama_kios,
            b.alamat_kios,
            b.regional,
            a.kd_rute,
            c.keterangan AS keterangan_rute,
            COUNT(DISTINCT a.kd_barang) AS total_barang,
            a.data_sts 
        ');
        $this->db->from('tb_pre_do a');
        $this->db->join('tb_customer b', 'b.kd_customer = a.kd_customer', 'inner');
        $this->db->join('tb_rutecs c', 'c.kd_rute = a.kd_rute', 'inner');
        $this->db->join('tb_detail_do d', 'd.kd_faktur = a.kd_faktur', 'left');
        $this->db->where('a.data_sts', 1);
        $this->db->where('d.kd_faktur IS NULL', null, false);
        $this->db->where('a.kd_rute', $rute);
        $this->db->group_by('a.kd_faktur');

        $query = $this->db->get();
        return $query->result();
    }

    public function get_do_cust($kd)
    {
        return $this->db->query("SELECT
            a.*
            FROM tb_pre_do a
            WHERE a.kd_faktur = '$kd'
            GROUP BY a.kd_faktur
        ")->result();
    }

    public function insert_tmp_detdo_batch($data)
    {
        return $this->db->insert_batch('tb_tmp_detaildo', $data);
    }

    public function insert_fakturfrom_draft_batch($data)
    {
        return $this->db->insert_batch('tb_detail_do', $data);
    }

    public function get_do_cust_byfaktur($kd)
    {
        return $this->db->query("SELECT
            a.*,b.nm_barang
            FROM tb_pre_do a
            JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
            WHERE a.kd_faktur = '$kd'
        ")->result();
    }
    public function det_do_cust($kd)
    {
        return $this->db->query("SELECT
            a.*,b.nm_barang
        FROM
            tb_pre_do a
        JOIN tb_master_barang b ON b.kode_barang = a.kd_barang
        WHERE
            a.kd_faktur = '$kd'
        ")->result();
    }

    public function insert_tmp_do($data)
    {
        return $this->db->insert('tb_tmp_do', $data);
    }

    public function insert_tmp_detdo($data)
    {
        if (isset($data['barang_sts']) && $data['barang_sts'] != 3) {
            return $this->db->insert('tb_tmp_detaildo', $data);
        }
    }

    public function update_sts_pre_do($kd, $data)
    {
        $this->db->where('kd_faktur', $kd);
        $this->db->where('kd_barang !=', '3');
        return $this->db->update('tb_pre_do', $data);
    }

    public function updatedsts($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_pre_do', $data);
    }

    public function edit_faktur_customer($kd, $data)
    {
        $this->db->where('kd_faktur', $kd);
        return $this->db->update('tb_pre_do', $data);
    }

    public function deletetmp_detdo($kd)
    {
        return $this->db->delete('tb_tmp_do', array("kd_do" => $kd));
    }
    public function delete_faktur_cus($kd)
    {
        return $this->db->delete('tb_detail_do', array("kd_faktur" => $kd));
    }
    public function delete_faktur_customer($kd)
    {
        return $this->db->delete('tb_pre_do', array("kd_faktur" => $kd));
    }

    public function deletetmp_do($kd)
    {
        return $this->db->delete('tb_tmp_detaildo', array("kd_do" => $kd));
    }
    public function del_tmp_do($kd)
    {
        return $this->db->delete('tb_tmp_do', array("kd_faktur" => $kd));
    }

    public function del_tmp_do_det($kd)
    {
        return $this->db->delete('tb_tmp_detaildo', array("kd_faktur" => $kd));
    }

    public function truncateitm($kd, $sts)
    {
        return $this->db->delete('tb_pre_do', array("kdupdate" => $kd, "upload_sts" => $sts));
    }
    public function truncatests($kd)
    {
        return $this->db->delete('tb_stock_status', array("kd_update" => $kd));
    }

    public function get_tmp_do()
    {
        return $this->db->query("SELECT
            a.id,
            a.kd_faktur,
            a.norut_do,
            c.nama_customer,
            c.alamat_kios,
            c.regional,
            b.kd_rute as kdrute,
            c.telp1,
            c.telp2,
            COALESCE(c.jam_buka_tutup,'-') AS jam_buka_tutup,
            COALESCE(c.karakteristik_kios,'-') AS toko
            FROM tb_tmp_do a
            JOIN tb_pre_do b ON b.kd_faktur = a.kd_faktur
            JOIN tb_customer c ON c.kd_customer = b.kd_customer
            GROUP by a.kd_faktur
        ")->result();
    }
    public function getkdfaktur($kd)
    {
        return $this->db->query("SELECT
            a.kd_faktur,
            a.norut_do
            FROM tb_tmp_do a 
            WHERE a.kd_faktur = '$kd'
        ");
    }

    public function update_sts_detail_checker($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_detail_do', $data);
    }

    public function update_checker_detail_done($kd, $sts, $data)
    {
        $this->db->where('kd_do', $kd);
        $this->db->where('status', $sts);
        return $this->db->update('tb_detail_do', $data);
    }
    public function update_checker_done($kd, $data)
    {
        $this->db->where('kd_do', $kd);
        return $this->db->update('tb_do', $data);
    }

    public function getdo()
    {
        return $this->db->query("SELECT 
        a.kd_do AS kddo,
        a.tgl_create AS createat,
        a.tgl_pengiriman AS tglkirim,
        a.nolambung AS nopol,
        a.regional AS rute,
        (
            SELECT COUNT(DISTINCT kd_barang) 
            FROM tb_detail_do 
            WHERE kd_do = a.kd_do
        ) AS totalbarang,
        (
            SELECT COUNT(DISTINCT kd_faktur) 
            FROM tb_detail_do 
            WHERE kd_do = a.kd_do
        ) AS totalfaktur,
        a.status AS status
        FROM tb_do a
        WHERE (
            SELECT COUNT(DISTINCT kd_faktur) 
            FROM tb_detail_do 
            WHERE kd_do = a.kd_do
        ) > 0
        ")->result();
    }

    public function get_tmp_dokd($kd)
    {
        $query = $this->db->get_where('tb_tmp_detaildo', ['kd_do' => $kd]);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function insert_det_do($data)
    {
        return $this->db->insert('tb_detail_do', $data);
    }

    public function insert_do($data)
    {
        return $this->db->insert('tb_do', $data);
    }

    function generate_kd_do()
    {
        $cd1 = $this->db->query("SELECT MAX(RIGHT(kd_do,4)) AS kd_max FROM tb_do WHERE DATE(tgl_create)=CURDATE()");
        $kd1 = "";
        if ($cd1->num_rows() > 0) {
            foreach ($cd1->result() as $k) {
                $tmp = ((int)$k->kd_max) + 1;
                $kd1 = sprintf("%04s", $tmp);
            }
        } else {
            $kd1 = "0001";
        }
        date_default_timezone_set('Asia/Jakarta');
        $kdnk1 = 'KIUDO' . date('dmy') . $kd1;
        return $kdnk1;
    }

    public function get_tonase_kubikasi($kd_do = null)
    {
        if (!$kd_do) {
            $this->db->select('kd_do');
            $this->db->from('tb_tmp_detaildo');
            $this->db->order_by('id_pre_do', 'DESC');
            $this->db->limit(1);
            $last_kd_do = $this->db->get()->row();
            $kd_do = $last_kd_do ? $last_kd_do->kd_do : null;
        }

        if (!$kd_do) {
            return [];
        }

        $this->db->select('d.kd_do, 
                       SUM(d.qty * m.berat) AS total_tonase_kg, 
                       SUM(d.qty * m.kubikasi) AS total_kubikasi_m3');
        $this->db->from('tb_tmp_detaildo d');
        $this->db->join('tb_master_barang m', 'd.kd_barang = m.kode_barang');
        $this->db->where('d.kd_do', $kd_do);
        $this->db->group_by('d.kd_do');

        return $this->db->get()->result();
    }

    public function detail_fk($kd)
    {
        return $this->db->query("SELECT
            a.id,
            a.kd_faktur,
            a.kd_barang,
            c.nm_barang,
            a.qty,
            a.satuan,
            a.no_lot,
            a.tgl_exp,
            a.barang_sts
            FROM tb_pre_do a
            JOIN tb_customer b ON b.kd_customer = a.kd_customer
            JOIN tb_master_barang c ON c.kode_barang = a.kd_barang
            WHERE a.kd_faktur = '$kd'
        ")->result();
    }

    public function det_customer($kd)
    {
        return $this->db->query("SELECT
            b.nama_customer,
            b.nama_kios,
            b.regional,
            a.upload_sts,
            a.data_sts,
            a.barang_sts
            FROM tb_pre_do a
            JOIN tb_customer b ON b.kd_customer = a.kd_customer
            WHERE a.kd_faktur = '$kd'
            LIMIT 1
        ")->result();
    }

    public function select_driver()
    {
        $this->db->select('*');
        $this->db->from('tb_op_driver');
        return $this->db->get()->result_array();
    }

    public function select_plat()
    {
        $this->db->select('*');
        $this->db->from('tb_op_plat');
        return $this->db->get()->result_array();
    }

    public function getbarangics()
    {
        return $this->db->query("SELECT 
        b.kd_system,
        a.nm_barang AS nama_barang,
        (b.p * b.l * b.t) AS dimensi,
        a.exp_date,
        SUM(a.qty) AS tot_qty,
        FLOOR(SUM(a.qty) / (b.p * b.l * b.t)) AS qty_box,
        (SUM(a.qty) - FLOOR(SUM(a.qty) / (b.p * b.l * b.t)) * (b.p * b.l * b.t)) AS qty_pcs
    FROM tb_qty_lot a
    JOIN tb_master_barang b ON b.nm_barang = a.nm_barang
    JOIN tb_suplier c ON c.kd_suplier = a.suplier
    GROUP BY a.nm_barang, a.exp_date, b.kd_system, b.p, b.l, b.t ")->result();
    }

    public function detailbrics($kdbarang)
    {
        return $this->db->query("
        ");
    }

    public function detailics()
    {
        return $this->db->query("SELECT 
        x.nama_barang,
        x.exp_date,
        IFNULL(s.qty, 0) AS saldo_awal,
        IFNULL(p.qty, 0) AS qty_masuk,
        IFNULL(d.total_do, 0) AS qty_keluar,
        
        (IFNULL(s.qty, 0) + IFNULL(p.qty, 0) - IFNULL(d.total_do, 0)) AS sistem_qty,
        IFNULL(o.qty, 0) AS fisik_qty,
        
        ((IFNULL(s.qty, 0) + IFNULL(p.qty, 0) - IFNULL(d.total_do, 0)) - IFNULL(o.qty, 0)) AS selisih,

        CASE 
            WHEN ((IFNULL(s.qty, 0) + IFNULL(p.qty, 0) - IFNULL(d.total_do, 0)) = IFNULL(o.qty, 0)) 
            THEN 'sama'
            ELSE 'beda'
        END AS keterangan

    FROM (
        SELECT nama_barang, exp_date FROM tb_ics
        UNION
        SELECT nama_barang, exp_date FROM tb_ics_po
        UNION
        SELECT nama_barang, tgl_exp AS exp_date FROM tb_detail_do
        UNION
        SELECT nama_barang, exp_date FROM tb_ics_opname
    ) AS x

    LEFT JOIN tb_ics s 
        ON x.nama_barang = s.nama_barang AND x.exp_date = s.exp_date

    LEFT JOIN tb_ics_po p 
        ON x.nama_barang = p.nama_barang AND x.exp_date = p.exp_date

    LEFT JOIN (
        SELECT nama_barang, tgl_exp AS exp_date, SUM(qty) AS total_do
        FROM tb_detail_do
        GROUP BY nama_barang, tgl_exp
    ) d ON x.nama_barang = d.nama_barang AND x.exp_date = d.exp_date

    LEFT JOIN tb_ics_opname o 
        ON x.nama_barang = o.nama_barang AND x.exp_date = o.exp_date

    ORDER BY x.nama_barang, x.exp_date
        ");
    }
}
