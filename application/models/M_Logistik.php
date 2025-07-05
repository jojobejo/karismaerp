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

    public function list_item_ics()
    {
        return $this->db->query("SELECT
            a.*
            FROM v_ics_all a
        ");
    }

    public function getAllICS()
    {
        return $this->db->query("SELECT
        x.nama_barang,
        x.exp_date,
        x.dimensi
        FROM
        (
            SELECT
            a.nama_barang,
            a.exp_date,
            (b.p*b.l*b.t) AS dimensi
            FROM tb_ics a
            JOIN tb_mbarang b ON b.nm_barang = a.nama_barang
                
        ) AS x
        ")->result();
    }
    public function getinputopname($user)
    {
        return $this->db->query("SELECT * FROM `tb_ics_opname` WHERE inputer = '$user'
        ")->result();
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
    public function insertOpname($data)
    {
        $this->db->insert('tb_ics_opname', $data);
    }

    public function logInput($log)
    {
        $this->db->insert('tb_log_ics', $log);
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

    public function querysql_not_ci()
    {
        // public function compareFEFO()
        // {
        //     $sql = "SELECT 
        //         COALESCE(a.nama_barang, b.nama_barang) AS nama_barang,
        //         COALESCE(a.exp_date, b.exp_date) AS exp_date,
        //         IFNULL(a.qty_fisik, 0) AS qty_fisik,
        //         IFNULL(b.qty_saldo, 0) AS qty_saldo,
        //         CASE 
        //             WHEN IFNULL(a.qty_fisik, 0) = IFNULL(b.qty_saldo, 0) THEN 'MATCH'
        //             ELSE 'NOT MATCH'
        //         END AS status
        //     FROM 
        //         (
        //             SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik 
        //             FROM tb_ics_opname 
        //             GROUP BY nama_barang, exp_date
        //         ) a
        //     LEFT JOIN 
        //         (
        //             SELECT nama_barang, exp_date, SUM(qty) AS qty_saldo 
        //             FROM tb_ics 
        //             GROUP BY nama_barang, exp_date
        //         ) b 
        //         ON a.nama_barang = b.nama_barang AND a.exp_date = b.exp_date

        //     UNION

        //     SELECT 
        //         COALESCE(a.nama_barang, b.nama_barang) AS nama_barang,
        //         COALESCE(a.exp_date, b.exp_date) AS exp_date,
        //         IFNULL(a.qty_fisik, 0) AS qty_fisik,
        //         IFNULL(b.qty_saldo, 0) AS qty_saldo,
        //         CASE 
        //             WHEN IFNULL(a.qty_fisik, 0) = IFNULL(b.qty_saldo, 0) THEN 'MATCH'
        //             ELSE 'NOT MATCH'
        //         END AS status
        //     FROM 
        //         (
        //             SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik 
        //             FROM tb_ics_opname 
        //             GROUP BY nama_barang, exp_date
        //         ) a
        //     RIGHT JOIN 
        //         (
        //             SELECT nama_barang, exp_date, SUM(qty) AS qty_saldo 
        //             FROM tb_ics 
        //             GROUP BY nama_barang, exp_date
        //         ) b 
        //         ON a.nama_barang = b.nama_barang AND a.exp_date = b.exp_date

        //     ORDER BY nama_barang, exp_date;";

        //     return $this->db->query($sql)->result();
        // }

        // by All Barang (tanpa exp_date)
        // public function compareAllBarang()
        // {
        //     $sql = "SELECT 
        //         COALESCE(a.nama_barang, b.nama_barang) AS nama_barang,
        //         IFNULL(a.qty_fisik, 0) AS qty_fisik,
        //         IFNULL(b.qty_saldo, 0) AS qty_saldo,
        //         CASE 
        //             WHEN IFNULL(a.qty_fisik, 0) = IFNULL(b.qty_saldo, 0) THEN 'MATCH'
        //             ELSE 'NOT MATCH'
        //         END AS STATUS
        //     FROM 
        //         (
        //             SELECT nama_barang, SUM(qty) AS qty_fisik 
        //             FROM tb_ics_opname 
        //             GROUP BY nama_barang
        //         ) a
        //     LEFT JOIN 
        //         (
        //             SELECT nama_barang, SUM(qty) AS qty_saldo 
        //             FROM tb_ics 
        //             GROUP BY nama_barang
        //         ) b ON a.nama_barang = b.nama_barang

        //     UNION

        //     SELECT 
        //         COALESCE(a.nama_barang, b.nama_barang) AS nama_barang,
        //         IFNULL(a.qty_fisik, 0) AS qty_fisik,
        //         IFNULL(b.qty_saldo, 0) AS qty_saldo,
        //         CASE 
        //             WHEN IFNULL(a.qty_fisik, 0) = IFNULL(b.qty_saldo, 0) THEN 'MATCH'
        //             ELSE 'NOT MATCH'
        //         END AS STATUS
        //     FROM 
        //         (
        //             SELECT nama_barang, SUM(qty) AS qty_fisik 
        //             FROM tb_ics_opname 
        //             GROUP BY nama_barang
        //         ) a
        //     RIGHT JOIN 
        //         (
        //             SELECT nama_barang, SUM(qty) AS qty_saldo 
        //             FROM tb_ics 
        //             GROUP BY nama_barang
        //         ) b ON a.nama_barang = b.nama_barang

        //     ORDER BY nama_barang;
        //     ";
        //     return $this->db->query($sql)->result();
        // }
    }

    public function admin_compareuser_exp()
    {
        return $this->db->query("SELECT 
            COALESCE(m.kd_system, '-') AS kd_barang,
            base.nama_barang,
            base.exp_date,
            COALESCE(t1.qty_fisik_tim1, 0) AS qty_fisik_tim1,
            COALESCE(t2.qty_fisik_tim2, 0) AS qty_fisik_tim2,
            COALESCE(base.qty_zahir, 0) AS qty_zahir,
            COALESCE(p.qty_pending, 0) AS qty_pending,
            COALESCE(supp.qty_supp, 0) AS qty_supp,
            (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(supp.qty_supp, 0)) AS qty_sistem,
            (COALESCE(t1.qty_fisik_tim1, 0) - (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(supp.qty_supp, 0))) AS selisih_tim1,
            (COALESCE(t2.qty_fisik_tim2, 0) - (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(supp.qty_supp, 0))) AS selisih_tim2,
            CASE
                WHEN COALESCE(t1.qty_fisik_tim1, 0) = 
                    (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(supp.qty_supp, 0))
                THEN 'MATCH' ELSE 'NOT MATCH'
            END AS status_tim1,
            CASE
                WHEN COALESCE(t2.qty_fisik_tim2, 0) = 
                    (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(supp.qty_supp, 0))
                THEN 'MATCH' ELSE 'NOT MATCH'
            END AS status_tim2
        FROM (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_zahir
            FROM tb_ics
            GROUP BY nama_barang, exp_date
        ) base
        LEFT JOIN tb_mbarang m ON base.nama_barang = m.nm_barang
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang, exp_date
        ) p ON p.nama_barang = base.nama_barang AND p.exp_date = base.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_supp
            FROM tb_ics_supp
            GROUP BY nama_barang, exp_date
        ) supp ON supp.nama_barang = base.nama_barang AND supp.exp_date = base.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik_tim1
            FROM tb_ics_opname
            WHERE tim = '1'
            GROUP BY nama_barang, exp_date
        ) t1 ON t1.nama_barang = base.nama_barang AND t1.exp_date = base.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik_tim2
            FROM tb_ics_opname
            WHERE tim = '2'
            GROUP BY nama_barang, exp_date
        ) t2 ON t2.nama_barang = base.nama_barang AND t2.exp_date = base.exp_date
        ORDER BY base.nama_barang, base.exp_date;")->result();
    }

    public function opname_pending()
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_ics_do a ")->result();
    }

    public function admin_compareuser_all()
    {
        return $this->db->query("SELECT 
            COALESCE(m.kd_system, '-') AS kd_barang,
            i.nama_barang,
            COALESCE(t1.qty_fisik_tim1, 0) AS qty_fisik_tim1,
            COALESCE(t2.qty_fisik_tim2, 0) AS qty_fisik_tim2,
            COALESCE(s.qty_zahir, 0) AS qty_zahir,
            COALESCE(p.qty_pending, 0) AS qty_pending,
            COALESCE(sp.qty_supp, 0) AS qty_supp,
            (COALESCE(s.qty_zahir, 0) + COALESCE(p.qty_pending, 0)) AS qty_sistem,
            (COALESCE(s.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(sp.qty_supp, 0)) AS qty_sistem_final,
            (COALESCE(t1.qty_fisik_tim1, 0) - (COALESCE(s.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(sp.qty_supp, 0))) AS selisih_qty_tim1,
            (COALESCE(t2.qty_fisik_tim2, 0) - (COALESCE(s.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(sp.qty_supp, 0))) AS selisih_qty_tim2,
            CASE
                WHEN COALESCE(t1.qty_fisik_tim1, 0) = 
                    (COALESCE(s.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(sp.qty_supp, 0))
                THEN 'MATCH' ELSE 'NOT MATCH'
            END AS status_tim1,
            CASE
                WHEN COALESCE(t2.qty_fisik_tim2, 0) = 
                    (COALESCE(s.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(sp.qty_supp, 0))
                THEN 'MATCH' ELSE 'NOT MATCH'
            END AS status_tim2
        FROM (
            SELECT DISTINCT nama_barang
            FROM tb_ics
        ) i
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_zahir
            FROM tb_ics
            GROUP BY nama_barang
        ) s ON s.nama_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang
        ) p ON p.nama_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_supp
            FROM tb_ics_supp
            GROUP BY nama_barang
        ) sp ON sp.nama_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_fisik_tim1
            FROM tb_ics_opname
            WHERE tim = '1'
            GROUP BY nama_barang
        ) t1 ON t1.nama_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_fisik_tim2
            FROM tb_ics_opname
            WHERE tim = '2'
            GROUP BY nama_barang
        ) t2 ON t2.nama_barang = i.nama_barang
        LEFT JOIN tb_mbarang m ON m.nm_barang = i.nama_barang
        ORDER BY i.nama_barang;")->result();
    }

    public function compareinputer($tim)
    {
        // Fisik opname hanya dari User StockOpname 1
        $this->db->select('nama_barang, SUM(qty) AS qty_fisik');
        $this->db->from('tb_ics_opname');
        $this->db->where('tim', $tim);
        $this->db->group_by('nama_barang');
        $subquery_fisik = $this->db->get_compiled_select();
        $this->db->reset_query();

        // Saldo dari tb_ics (qty buku)
        $this->db->select('nama_barang, SUM(qty) AS qty_buku');
        $this->db->from('tb_ics');
        $this->db->group_by('nama_barang');
        $subquery_buku = $this->db->get_compiled_select();
        $this->db->reset_query();

        // Saldo dari tb_pending
        $this->db->select('nama_barang, SUM(qty) AS qty_pending');
        $this->db->from('tb_ics_do');
        $this->db->group_by('nama_barang');
        $subquery_pending = $this->db->get_compiled_select();
        $this->db->reset_query();

        $sql = "SELECT 
                COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
                IFNULL(f.qty_fisik, 0) AS qty_fisik,
                IFNULL(b.qty_buku, 0) AS qty_buku,
                IFNULL(p.qty_pending, 0) AS qty_pending,
                CASE 
                    WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                    ELSE 'NOT MATCH'
                END AS status
            FROM ($subquery_fisik) f
            LEFT JOIN ($subquery_buku) b ON f.nama_barang = b.nama_barang
            LEFT JOIN ($subquery_pending) p ON f.nama_barang = p.nama_barang

            UNION

            SELECT 
                COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
                IFNULL(f.qty_fisik, 0) AS qty_fisik,
                IFNULL(b.qty_buku, 0) AS qty_buku,
                IFNULL(p.qty_pending, 0) AS qty_pending,
                CASE 
                    WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                    ELSE 'NOT MATCH'
                END AS status
            FROM ($subquery_buku) b
            LEFT JOIN ($subquery_fisik) f ON b.nama_barang = f.nama_barang
            LEFT JOIN ($subquery_pending) p ON b.nama_barang = p.nama_barang

            UNION

            SELECT 
                COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
                IFNULL(f.qty_fisik, 0) AS qty_fisik,
                IFNULL(b.qty_buku, 0) AS qty_buku,
                IFNULL(p.qty_pending, 0) AS qty_pending,
                CASE 
                    WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                    ELSE 'NOT MATCH'
                END AS status
            FROM ($subquery_pending) p
            LEFT JOIN ($subquery_fisik) f ON p.nama_barang = f.nama_barang
            LEFT JOIN ($subquery_buku) b ON p.nama_barang = b.nama_barang

            ORDER BY nama_barang
            ";

        return $this->db->query($sql)->result();
    }

    public function list_final_data()
    {
        return $this->db->query("SELECT 
            i.nama_barang,
            COALESCE(m.kd_system, '-') AS kd_barang,
            COALESCE(z.qty_zahir, 0) AS qty_zahir,
            COALESCE(p.qty_pending, 0) AS qty_pending,
            COALESCE(s.qty_supp, 0) AS qty_supp,
            (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)) AS qty_sistem,
            COALESCE(t1.qty_fisik_tim1, 0) AS qty_fisik_tim1,
            COALESCE(t2.qty_fisik_tim2, 0) AS qty_fisik_tim2,
            CASE
                WHEN COALESCE(t1.qty_fisik_tim1, 0) = 
                    (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status_tim1,
            CASE
                WHEN COALESCE(t2.qty_fisik_tim2, 0) = 
                    (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status_tim2,
            CASE
                WHEN COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    AND COALESCE(t2.qty_fisik_tim2, 0) != (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'Ambil dari Tim 1'     
                WHEN COALESCE(t1.qty_fisik_tim1, 0) != (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    AND COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'Ambil dari Tim 2'
                WHEN COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    AND COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'MATCH KEDUANYA'
                ELSE 'CEK ULANG'
            END AS keterangan
        FROM (
            SELECT DISTINCT nama_barang FROM tb_ics
        ) i
        LEFT JOIN tb_mbarang m ON m.nm_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_zahir
            FROM tb_ics
            GROUP BY nama_barang
        ) z ON z.nama_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang
        ) p ON p.nama_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_supp
            FROM tb_ics_supp
            GROUP BY nama_barang
        ) s ON s.nama_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_fisik_tim1
            FROM tb_ics_opname
            WHERE tim = '1'
            GROUP BY nama_barang
        ) t1 ON t1.nama_barang = i.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_fisik_tim2
            FROM tb_ics_opname
            WHERE tim = '2'
            GROUP BY nama_barang
        ) t2 ON t2.nama_barang = i.nama_barang
        WHERE 
            (COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)))
            OR
            (COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)))
            OR
            (COALESCE(t1.qty_fisik_tim1, 0) != (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
            AND COALESCE(t2.qty_fisik_tim2, 0) != (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)))
        ORDER BY i.nama_barang;")->result();
    }

    public function list_final_datafefo()
    {
        return $this->db->query("SELECT 
            base.nama_barang,
            base.exp_date,
            COALESCE(m.kd_system, '-') AS kd_barang,
            COALESCE(base.qty_zahir, 0) AS qty_zahir,
            COALESCE(p.qty_pending, 0) AS qty_pending,
            COALESCE(s.qty_supp, 0) AS qty_supp,
            (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)) AS qty_sistem,
            COALESCE(t1.qty_fisik_tim1, 0) AS qty_fisik_tim1,
            COALESCE(t2.qty_fisik_tim2, 0) AS qty_fisik_tim2,
            CASE
                WHEN COALESCE(t1.qty_fisik_tim1, 0) = 
                    (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status_tim1,
            CASE
                WHEN COALESCE(t2.qty_fisik_tim2, 0) = 
                    (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status_tim2,
            CASE
                WHEN COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    AND COALESCE(t2.qty_fisik_tim2, 0) != (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'Ambil dari Tim 1'
                WHEN COALESCE(t1.qty_fisik_tim1, 0) != (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    AND COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'Ambil dari Tim 2'
                WHEN COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    AND COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                THEN 'MATCH KEDUANYA'
                ELSE 'CEK ULANG'
            END AS keterangan
        FROM (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_zahir
            FROM tb_ics
            GROUP BY nama_barang, exp_date
        ) base
        LEFT JOIN tb_mbarang m ON m.nm_barang = base.nama_barang
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang, exp_date
        ) p ON p.nama_barang = base.nama_barang AND p.exp_date = base.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_supp
            FROM tb_ics_supp
            GROUP BY nama_barang, exp_date
        ) s ON s.nama_barang = base.nama_barang AND s.exp_date = base.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik_tim1
            FROM tb_ics_opname
            WHERE tim = '1'
            GROUP BY nama_barang, exp_date
        ) t1 ON t1.nama_barang = base.nama_barang AND t1.exp_date = base.exp_date
        LEFT JOIN (
            SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik_tim2
            FROM tb_ics_opname
            WHERE tim = '2'
            GROUP BY nama_barang, exp_date
        ) t2 ON t2.nama_barang = base.nama_barang AND t2.exp_date = base.exp_date
        WHERE 
            (COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)))
            OR
            (COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)))
            OR
            (COALESCE(t1.qty_fisik_tim1, 0) != (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)) AND COALESCE(t2.qty_fisik_tim2, 0) != (COALESCE(base.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)))
        ORDER BY base.nama_barang, base.exp_date;")->result();
    }

    public function final_opname_expdate_statis()
    {
        return $this->db->query("SELECT
            COUNT(*) AS total_barang,
            SUM(CASE
                WHEN keterangan IN ('MATCH KEDUANYA', 'Ambil dari Tim 1', 'Ambil dari Tim 2') THEN 1
                ELSE 0
            END) AS total_match,
            SUM(CASE
                WHEN keterangan = 'CEK ULANG' THEN 1
                ELSE 0
            END) AS total_notmatch
        FROM (
            SELECT 
                base.nama_barang,
                base.exp_date,
                COALESCE(z.qty_zahir, 0) AS qty_zahir,
                COALESCE(p.qty_pending, 0) AS qty_pending,
                COALESCE(s.qty_supp, 0) AS qty_supp,
                (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)) AS qty_sistem,
                COALESCE(t1.qty_fisik_tim1, 0) AS qty_fisik_tim1,
                COALESCE(t2.qty_fisik_tim2, 0) AS qty_fisik_tim2,

                -- Keterangan berdasarkan kecocokan qty_fisik dan qty_sistem
                CASE
                    WHEN COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                        AND COALESCE(t2.qty_fisik_tim2, 0) != (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    THEN 'Ambil dari Tim 1'

                    WHEN COALESCE(t1.qty_fisik_tim1, 0) != (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                        AND COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    THEN 'Ambil dari Tim 2'

                    WHEN COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                        AND COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    THEN 'MATCH KEDUANYA'

                    ELSE 'CEK ULANG'
                END AS keterangan
            FROM (
                SELECT nama_barang, exp_date FROM tb_ics GROUP BY nama_barang, exp_date
            ) base
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_zahir
                FROM tb_ics
                GROUP BY nama_barang, exp_date
            ) z ON z.nama_barang = base.nama_barang AND z.exp_date = base.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_pending
                FROM tb_ics_do
                GROUP BY nama_barang, exp_date
            ) p ON p.nama_barang = base.nama_barang AND p.exp_date = base.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_supp
                FROM tb_ics_supp
                GROUP BY nama_barang, exp_date
            ) s ON s.nama_barang = base.nama_barang AND s.exp_date = base.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik_tim1
                FROM tb_ics_opname
                WHERE tim = '1'
                GROUP BY nama_barang, exp_date
            ) t1 ON t1.nama_barang = base.nama_barang AND t1.exp_date = base.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik_tim2
                FROM tb_ics_opname
                WHERE tim = '2'
                GROUP BY nama_barang, exp_date
            ) t2 ON t2.nama_barang = base.nama_barang AND t2.exp_date = base.exp_date
        ) hasil;")->result();
    }

    public function final_opname_allbarang_statis()
    {
        return $this->db->query("SELECT
            COUNT(*) AS total_barang,
            SUM(CASE
                WHEN keterangan IN ('MATCH KEDUANYA', 'Ambil dari Tim 1', 'Ambil dari Tim 2') THEN 1
                ELSE 0
            END) AS total_match,
            SUM(CASE
                WHEN keterangan = 'CEK ULANG' THEN 1
                ELSE 0
            END) AS total_notmatch
        FROM (
            SELECT 
                i.nama_barang,
                COALESCE(z.qty_zahir, 0) AS qty_zahir,
                COALESCE(p.qty_pending, 0) AS qty_pending,
                COALESCE(s.qty_supp, 0) AS qty_supp,
                (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0)) AS qty_sistem,
                COALESCE(t1.qty_fisik_tim1, 0) AS qty_fisik_tim1,
                COALESCE(t2.qty_fisik_tim2, 0) AS qty_fisik_tim2,
                CASE
                    WHEN COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                        AND COALESCE(t2.qty_fisik_tim2, 0) != (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    THEN 'Ambil dari Tim 1'

                    WHEN COALESCE(t1.qty_fisik_tim1, 0) != (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                        AND COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    THEN 'Ambil dari Tim 2'

                    WHEN COALESCE(t1.qty_fisik_tim1, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                        AND COALESCE(t2.qty_fisik_tim2, 0) = (COALESCE(z.qty_zahir, 0) + COALESCE(p.qty_pending, 0) - COALESCE(s.qty_supp, 0))
                    THEN 'MATCH KEDUANYA'

                    ELSE 'CEK ULANG'
                END AS keterangan
            FROM (
                SELECT DISTINCT nama_barang FROM tb_ics
            ) i
            LEFT JOIN (
                SELECT nama_barang, SUM(qty) AS qty_zahir
                FROM tb_ics
                GROUP BY nama_barang
            ) z ON z.nama_barang = i.nama_barang
            LEFT JOIN (
                SELECT nama_barang, SUM(qty) AS qty_pending
                FROM tb_ics_do
                GROUP BY nama_barang
            ) p ON p.nama_barang = i.nama_barang
            LEFT JOIN (
                SELECT nama_barang, SUM(qty) AS qty_supp
                FROM tb_ics_supp
                GROUP BY nama_barang
            ) s ON s.nama_barang = i.nama_barang
            LEFT JOIN (
                SELECT nama_barang, SUM(qty) AS qty_fisik_tim1
                FROM tb_ics_opname
                WHERE tim = '1'
                GROUP BY nama_barang
            ) t1 ON t1.nama_barang = i.nama_barang
            LEFT JOIN (
                SELECT nama_barang, SUM(qty) AS qty_fisik_tim2
                FROM tb_ics_opname
                WHERE tim = '2'
                GROUP BY nama_barang
            ) t2 ON t2.nama_barang = i.nama_barang
        ) hasil
        ")->result();
    }

    public function compareAllBarang()
    {

        $this->db->select('nama_barang, SUM(qty) AS qty_fisik');
        $this->db->from('tb_ics_opname');
        $this->db->group_by('nama_barang');
        $subquery_fisik = $this->db->get_compiled_select();
        $this->db->reset_query();

        $this->db->select('nama_barang, SUM(qty) AS qty_buku');
        $this->db->from('tb_ics');
        $this->db->group_by('nama_barang');
        $subquery_buku = $this->db->get_compiled_select();
        $this->db->reset_query();

        $this->db->select('nama_barang, SUM(qty) AS qty_pending');
        $this->db->from('tb_ics_do');
        $this->db->group_by('nama_barang');
        $subquery_pending = $this->db->get_compiled_select();
        $this->db->reset_query();

        $sql = "
        SELECT 
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
            IFNULL(f.qty_fisik, 0) AS qty_fisik,
            IFNULL(b.qty_buku, 0) AS qty_buku,
            IFNULL(p.qty_pending, 0) AS qty_pending,
            CASE 
                WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status
        FROM ($subquery_fisik) f
        LEFT JOIN ($subquery_buku) b ON f.nama_barang = b.nama_barang
        LEFT JOIN ($subquery_pending) p ON f.nama_barang = p.nama_barang

        UNION

        SELECT 
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
            IFNULL(f.qty_fisik, 0) AS qty_fisik,
            IFNULL(b.qty_buku, 0) AS qty_buku,
            IFNULL(p.qty_pending, 0) AS qty_pending,
            CASE 
                WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status
        FROM ($subquery_buku) b
        LEFT JOIN ($subquery_fisik) f ON b.nama_barang = f.nama_barang
        LEFT JOIN ($subquery_pending) p ON b.nama_barang = p.nama_barang

        UNION

        SELECT 
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
            IFNULL(f.qty_fisik, 0) AS qty_fisik,
            IFNULL(b.qty_buku, 0) AS qty_buku,
            IFNULL(p.qty_pending, 0) AS qty_pending,
            CASE 
                WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status
        FROM ($subquery_pending) p
        LEFT JOIN ($subquery_fisik) f ON p.nama_barang = f.nama_barang
        LEFT JOIN ($subquery_buku) b ON p.nama_barang = b.nama_barang

        ORDER BY nama_barang
    ";
        return $this->db->query($sql)->result();
    }

    public function compareinputerexp($tim)
    {

        $this->db->select('nama_barang, exp_date, SUM(qty) AS qty_fisik');
        $this->db->from('tb_ics_opname');
        $this->db->where('tim', $tim);
        $this->db->group_by(['nama_barang', 'exp_date']);
        $sub_fisik = $this->db->get_compiled_select();
        $this->db->reset_query();

        $this->db->select('nama_barang, exp_date, SUM(qty) AS qty_buku');
        $this->db->from('tb_ics');
        $this->db->group_by(['nama_barang', 'exp_date']);
        $sub_buku = $this->db->get_compiled_select();
        $this->db->reset_query();

        $this->db->select('nama_barang, exp_date, SUM(qty) AS qty_pending');
        $this->db->from('tb_ics_do');
        $this->db->group_by(['nama_barang', 'exp_date']);
        $sub_pending = $this->db->get_compiled_select();
        $this->db->reset_query();

        $sql = "
        SELECT
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
            COALESCE(f.exp_date ,  b.exp_date ,  p.exp_date )     AS exp_date,
            IFNULL(f.qty_fisik , 0)                               AS qty_fisik,
            IFNULL(b.qty_buku , 0)                                AS qty_buku,
            IFNULL(p.qty_pending , 0)                             AS qty_pending,
            '$tim'                                                AS tim,
            CASE
                WHEN IFNULL(f.qty_fisik ,0) =
                     (IFNULL(b.qty_buku ,0) + IFNULL(p.qty_pending ,0))
                THEN 'MATCH' ELSE 'NOT MATCH'
            END                                                   AS status
        FROM ($sub_fisik)     f
        LEFT JOIN ($sub_buku) b ON b.nama_barang = f.nama_barang
                               AND b.exp_date   = f.exp_date
        LEFT JOIN ($sub_pending) p ON p.nama_barang = f.nama_barang
                                   AND p.exp_date   = f.exp_date

        UNION

        SELECT
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang),
            COALESCE(f.exp_date ,  b.exp_date ,  p.exp_date ),
            IFNULL(f.qty_fisik , 0),
            IFNULL(b.qty_buku , 0),
            IFNULL(p.qty_pending , 0),
            '$tim',
            CASE
                WHEN IFNULL(f.qty_fisik ,0) =
                     (IFNULL(b.qty_buku ,0) + IFNULL(p.qty_pending ,0))
                THEN 'MATCH' ELSE 'NOT MATCH'
            END
        FROM ($sub_buku)      b
        LEFT JOIN ($sub_fisik) f ON f.nama_barang = b.nama_barang
                                AND f.exp_date   = b.exp_date
        LEFT JOIN ($sub_pending) p ON p.nama_barang = b.nama_barang
                                   AND p.exp_date   = b.exp_date

        UNION

        SELECT
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang),
            COALESCE(f.exp_date ,  b.exp_date ,  p.exp_date ),
            IFNULL(f.qty_fisik , 0),
            IFNULL(b.qty_buku , 0),
            IFNULL(p.qty_pending , 0),
            '$tim',
            CASE
                WHEN IFNULL(f.qty_fisik ,0) =
                     (IFNULL(b.qty_buku ,0) + IFNULL(p.qty_pending ,0))
                THEN 'MATCH' ELSE 'NOT MATCH'
            END
        FROM ($sub_pending)   p
        LEFT JOIN ($sub_fisik) f ON f.nama_barang = p.nama_barang
                                AND f.exp_date   = p.exp_date
        LEFT JOIN ($sub_buku)  b ON b.nama_barang = p.nama_barang
                                AND b.exp_date   = p.exp_date
        ORDER BY nama_barang, exp_date
    ";

        return $this->db->query($sql)->result();
    }



    public function compareFEFO()
    {
        // Fisik opname
        $this->db->select('nama_barang, exp_date, SUM(qty) AS qty_fisik');
        $this->db->from('tb_ics_opname');
        $this->db->group_by(['nama_barang', 'exp_date']);
        $subquery_fisik = $this->db->get_compiled_select();
        $this->db->reset_query();

        // Saldo buku dari tb_ics
        $this->db->select('nama_barang, exp_date, SUM(qty) AS qty_buku');
        $this->db->from('tb_ics');
        $this->db->group_by(['nama_barang', 'exp_date']);
        $subquery_buku = $this->db->get_compiled_select();
        $this->db->reset_query();

        // Pending berdasarkan nama_barang + exp_date
        $this->db->select('nama_barang, exp_date, SUM(qty) AS qty_pending');
        $this->db->from('tb_ics_do');
        $this->db->group_by(['nama_barang', 'exp_date']);
        $subquery_pending = $this->db->get_compiled_select();
        $this->db->reset_query();

        $sql = "
        SELECT 
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
            COALESCE(f.exp_date, b.exp_date, p.exp_date) AS exp_date,
            IFNULL(f.qty_fisik, 0) AS qty_fisik,
            IFNULL(b.qty_buku, 0) AS qty_buku,
            IFNULL(p.qty_pending, 0) AS qty_pending,
            CASE 
                WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status
        FROM ($subquery_fisik) f
        LEFT JOIN ($subquery_buku) b ON f.nama_barang = b.nama_barang AND f.exp_date = b.exp_date
        LEFT JOIN ($subquery_pending) p ON f.nama_barang = p.nama_barang AND f.exp_date = p.exp_date

        UNION

        SELECT 
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
            COALESCE(f.exp_date, b.exp_date, p.exp_date) AS exp_date,
            IFNULL(f.qty_fisik, 0) AS qty_fisik,
            IFNULL(b.qty_buku, 0) AS qty_buku,
            IFNULL(p.qty_pending, 0) AS qty_pending,
            CASE 
                WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status
        FROM ($subquery_buku) b
        LEFT JOIN ($subquery_fisik) f ON b.nama_barang = f.nama_barang AND b.exp_date = f.exp_date
        LEFT JOIN ($subquery_pending) p ON b.nama_barang = p.nama_barang AND b.exp_date = p.exp_date

        UNION

        SELECT 
            COALESCE(f.nama_barang, b.nama_barang, p.nama_barang) AS nama_barang,
            COALESCE(f.exp_date, b.exp_date, p.exp_date) AS exp_date,
            IFNULL(f.qty_fisik, 0) AS qty_fisik,
            IFNULL(b.qty_buku, 0) AS qty_buku,
            IFNULL(p.qty_pending, 0) AS qty_pending,
            CASE 
                WHEN IFNULL(f.qty_fisik, 0) = (IFNULL(b.qty_buku, 0) + IFNULL(p.qty_pending, 0)) THEN 'MATCH'
                ELSE 'NOT MATCH'
            END AS status
        FROM ($subquery_pending) p
        LEFT JOIN ($subquery_fisik) f ON p.nama_barang = f.nama_barang AND p.exp_date = f.exp_date
        LEFT JOIN ($subquery_buku) b ON p.nama_barang = b.nama_barang AND p.exp_date = b.exp_date

        ORDER BY nama_barang, exp_date
    ";

        return $this->db->query($sql)->result();
    }
    public function statistikFEFO()
    {
        $sql = "SELECT
    COUNT(*) AS total,
    SUM(IF(qty_fisik = qty_saldo, 1, 0)) AS match_count,
    SUM(IF(qty_fisik != qty_saldo, 1, 0)) AS not_match_count
FROM (
    SELECT 
        o.nama_barang,
        o.exp_date,
        SUM(o.qty) AS qty_fisik,
        IFNULL(SUM(i.qty), 0) AS qty_saldo
    FROM tb_ics_opname o
    LEFT JOIN tb_ics i ON o.nama_barang = i.nama_barang AND o.exp_date = i.exp_date
    GROUP BY o.nama_barang, o.exp_date
) AS sub

    ";
        $result = $this->db->query($sql)->result();

        return $this->_hitungStatistik($result);
    }

    public function statistikAllBarang()
    {
        $sql = "SELECT 
            o.nama_barang,
            SUM(o.qty) AS qty_fisik,
            IFNULL(SUM(i.qty), 0) AS qty_saldo,
            IF(SUM(o.qty) = IFNULL(SUM(i.qty), 0), 'MATCH', 'NOT MATCH') AS status
        FROM tb_ics_opname o
        LEFT JOIN tb_ics i ON o.nama_barang = i.nama_barang
        GROUP BY o.nama_barang
    ";

        $result = $this->db->query($sql)->result();
        return $this->_hitungStatistik($result);
    }

    public function getallopnametodo($kdbr)
    {
        return $this->db->query("SELECT
        a.nama_barang,
        a.exp_date,
        SUM(a.qty) AS qty_zahir,
        COALESCE(pending.qty_pending, 0) AS qty_pending,
        SUM(a.qty) + COALESCE(pending.qty_pending, 0) AS qty_final,
        COALESCE(op1.qtyinput_1, 0) AS qtyinput_1,
        COALESCE(op2.qtyinput_2, 0) AS qtyinput_2,
        CASE
            WHEN (SUM(a.qty) + COALESCE(pending.qty_pending, 0)) = COALESCE(op1.qtyinput_1, 0) THEN 'Match'
            ELSE 'Not Match'
        END AS status_tim1,
        CASE
            WHEN (SUM(a.qty) + COALESCE(pending.qty_pending, 0)) = COALESCE(op2.qtyinput_2, 0) THEN 'Match'
            ELSE 'Not Match'
        END AS status_tim2
        FROM
        tb_ics AS a
        JOIN tb_mbarang AS b ON b.nm_barang = a.nama_barang
        LEFT JOIN (
            SELECT
                d.nama_barang,
                d.exp_date,
                SUM(d.qty) AS qty_pending
            FROM
                tb_ics_do d
                JOIN tb_mbarang mb ON mb.nm_barang = d.nama_barang
            WHERE
                mb.kd_system = '$kdbr'
            GROUP BY
                d.nama_barang,
                d.exp_date
        ) AS pending ON pending.nama_barang = a.nama_barang
        AND pending.exp_date = a.exp_date
        LEFT JOIN (
            SELECT
                nama_barang,
                exp_date,
                SUM(qty) AS qtyinput_1
            FROM
                tb_ics_opname
            WHERE
                tim = 1
            GROUP BY
                nama_barang,
                exp_date
        ) AS op1 ON op1.nama_barang = a.nama_barang
        AND op1.exp_date = a.exp_date
        LEFT JOIN (
            SELECT
                nama_barang,
                exp_date,
                SUM(qty) AS qtyinput_2
            FROM
                tb_ics_opname
            WHERE
                tim = 2
            GROUP BY
                nama_barang,
                exp_date
        ) AS op2 ON op2.nama_barang = a.nama_barang
        AND op2.exp_date = a.exp_date
        WHERE
            b.kd_system = '$kdbr'
        GROUP BY
            a.nama_barang,
            a.exp_date
        ORDER BY
            a.nama_barang,
            a.exp_date;")->result();
    }

    public function rekapopnamebarang($kdbr, $tim)
    {
        return $this->db->query("SELECT
            COUNT(*) AS total_data,
            SUM(CASE
                WHEN (qty_final = qtyinput_1) THEN 1
                ELSE 0
            END) AS total_match,
            SUM(CASE
                WHEN (qtyinput_1 IS NOT NULL AND qty_final != qtyinput_1) THEN 1
                ELSE 0
            END) AS total_not_match
        FROM (
            SELECT
                a.nama_barang,
                a.exp_date,
                SUM(a.qty) + COALESCE(pending.qty_pending, 0) AS qty_final,
                COALESCE(op1.qtyinput_1, 0) AS qtyinput_1
            FROM tb_ics a
            JOIN tb_mbarang b ON b.nm_barang = a.nama_barang
            LEFT JOIN (
                SELECT d.nama_barang, d.exp_date, SUM(d.qty) AS qty_pending
                FROM tb_ics_do d
                JOIN tb_mbarang mb ON mb.nm_barang = d.nama_barang
                WHERE mb.kd_system = '$kdbr'
                GROUP BY d.nama_barang, d.exp_date
            ) AS pending
            ON pending.nama_barang = a.nama_barang AND pending.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qtyinput_1
                FROM tb_ics_opname
                WHERE tim = '$tim'
                GROUP BY nama_barang, exp_date
            ) AS op1
            ON op1.nama_barang = a.nama_barang AND op1.exp_date = a.exp_date
            WHERE b.kd_system = '$kdbr'
            GROUP BY a.nama_barang, a.exp_date) AS hasil ")->result();
    }

    public function detail_opname_barang($kdbr, $tim)
    {
        return $this->db->query("SELECT 
        b.kd_system AS kd_barang,
        a.nama_barang,
        SUM(a.qty) AS qty_zahir,
        (SUM(a.qty)+COALESCE(pending.qty_pending, 0)) AS qty_zahirwith_pnd,
        COALESCE(pending.qty_pending, 0) AS qty_pending,
        COALESCE(opname.qty_fisik, 0) AS qty_fisik,
        IF(SUM(a.qty) + COALESCE(pending.qty_pending, 0) = COALESCE(opname.qty_fisik, 0),1,0) AS status
        FROM tb_ics a
        JOIN tb_mbarang b ON b.nm_barang = a.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang
        ) pending ON pending.nama_barang = a.nama_barang
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_fisik
            FROM tb_ics_opname
            WHERE tim = '$tim'
            GROUP BY nama_barang
        ) opname ON opname.nama_barang = a.nama_barang
        WHERE b.kd_system = '$kdbr'
        GROUP BY a.nama_barang")->result();
    }

    private function _hitungStatistik($data)
    {
        $total = count($data);
        $match = 0;
        $not_match = 0;

        foreach ($data as $row) {
            if (isset($row->status) && $row->status === 'MATCH') {
                $match++;
            } else {
                $not_match++;
            }
        }

        $persen_match = $total > 0 ? round(($match / $total) * 100, 2) : 0;
        $persen_not = 100 - $persen_match;

        return [
            'total' => $total,
            'match' => $match,
            'not_match' => $not_match,
            'persen_match' => $persen_match,
            'persen_not' => $persen_not
        ];
    }

    public function trackingopname($user)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_ics_opname a
        WHERE a.inputer = '$user'
        ")->result();
    }

    public function adm_trackingopname($tim)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_ics_opname a
        WHERE a.tim = '$tim'
        ")->result();
    }

    public function get_requestbr($id)
    {
        return $this->db->get_where('tb_req_opname', ['id' => $id])->row();
    }

    public function opname_req_user()
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_req_opname a
        WHERE a.status = '1'
        ")->result();
    }

    public function all_barang_match_t1()
    {
        return $this->db->query("SELECT
            COUNT(DISTINCT ics.nama_barang) AS total_barang,
            SUM((COALESCE(op.qty_input, 0) = (ics.qty_buku + COALESCE(pending.qty_pending, 0))) + 0) AS total_match,
            SUM((COALESCE(op.qty_input, 0) != (ics.qty_buku + COALESCE(pending.qty_pending, 0))) + 0) AS total_notmatch
        FROM (
            SELECT 
                nama_barang, 
                SUM(qty) AS qty_buku
            FROM tb_ics
            GROUP BY nama_barang
        ) AS ics
        LEFT JOIN (
            SELECT 
                nama_barang, 
                SUM(qty) AS qty_input
            FROM tb_ics_opname
            WHERE tim = '1'
            GROUP BY nama_barang
        ) AS op ON ics.nama_barang = op.nama_barang
        LEFT JOIN (
            SELECT
                nama_barang,
                SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang
        ) AS pending ON pending.nama_barang = ics.nama_barang;")->result();
    }

    public function all_barang_match_t2()
    {
        return $this->db->query("SELECT
            COUNT(DISTINCT ics.nama_barang) AS total_barang,
            SUM((COALESCE(op.qty_input, 0) = (ics.qty_buku + COALESCE(pending.qty_pending, 0))) + 0) AS total_match,
            SUM((COALESCE(op.qty_input, 0) != (ics.qty_buku + COALESCE(pending.qty_pending, 0))) + 0) AS total_notmatch
        FROM (
            SELECT 
                nama_barang, 
                SUM(qty) AS qty_buku
            FROM tb_ics
            GROUP BY nama_barang
        ) AS ics
        LEFT JOIN (
            SELECT 
                nama_barang, 
                SUM(qty) AS qty_input
            FROM tb_ics_opname
            WHERE tim = '2'
            GROUP BY nama_barang
        ) AS op ON ics.nama_barang = op.nama_barang
        LEFT JOIN (
            SELECT
                nama_barang,
                SUM(qty) AS qty_pending
            FROM tb_ics_do
            GROUP BY nama_barang
        ) AS pending ON pending.nama_barang = ics.nama_barang;")->result();
    }

    public function get_wilayah()
    {
        return $this->db->query("SELECT
        a.nm_wilayah as wilayah,
        a.id AS id
        FROM tb_gdg_kordinat a
        ")->result();
    }

    public function list_opname_user_wilayah($wilayah)
    {
        return $this->db->query("SELECT
            nama_barang,
            exp_date,
            SUM(CASE WHEN tim = '1' THEN qty ELSE 0 END) AS fisik_tim1,
            SUM(CASE WHEN tim = '2' THEN qty ELSE 0 END) AS fisik_tim2
        FROM tb_ics_opname
        WHERE wilayah = '$wilayah'
        GROUP BY nama_barang, exp_date;")->result();
    }

    public function get_nmbarang($kdbarang)
    {
        return $this->db->query("SELECT
        a.nm_barang AS nama_barang,
        a.kd_system AS kdbarang
        FROM tb_mbarang a
        WHERE a.kd_system = '$kdbarang'
        ")->result();
    }

    public function getreqbr_opname($kdbarang, $tim)
    {
        return $this->db->query("SELECT
        a.*
        FROM tb_req_opname a
        LEFT JOIN tb_mbarang b ON a.nama_barang = b.nm_barang
        WHERE b.kd_system = '$kdbarang' AND a.tim = '$tim' AND a.status = '1'
        ")->result();
    }

    public function deleteopnameinputuser($id)
    {
        return $this->db->delete('tb_ics_opname', array("id" => $id));
    }

    public function getdataopname($id)
    {
        return $this->db->select('a.inputer, a.nama_barang, a.exp_date, a.qty, a.qty_box, a.qty_pcs')
            ->from('tb_ics_opname a')
            ->join('tb_mbarang b', 'b.nm_barang = a.nama_barang')
            ->where('a.id', $id)
            ->get()
            ->row();
    }

    public function countrequseropname($kd_system, $tim)
    {
        $this->db->select('COUNT(a.id) AS total_request');
        $this->db->from('tb_req_opname a');
        $this->db->join('tb_mbarang b', 'b.nm_barang = a.nama_barang', 'left');
        $this->db->where('b.kd_system', $kd_system);
        $this->db->where('a.tim', $tim);
        $this->db->where('a.status', '1');

        $query = $this->db->get();
        return $query->row()->total_request;
    }

    public function list_inputer_by_allbarang($kdbarang, $tim)
    {
        return $this->db->query("SELECT	
        a.id,
        b.kd_system,
        a.nama_barang,
        a.qty,
        a.qty_box,
        a.qty_pcs,
        a.tim,
        (b.p*b.l*b.t) AS dimensi,
        a.inputer
        FROM tb_ics_opname a
        LEFT JOIN tb_mbarang b ON a.nama_barang = b.nm_barang
        WHERE b.kd_system = '$kdbarang' AND a.tim = '$tim'
        ")->result();
    }
    public function list_inputer_by_expdate($kdbarang, $tim)
    {
        return $this->db->query("SELECT	
            a.id,
            b.kd_system,
            a.nama_barang,
            a.exp_date,
            COALESCE(zahir.qty_zahir, 0) AS qty_zahir,
            COALESCE(pending.qty_pending, 0) AS qty_pending,
            (COALESCE(zahir.qty_zahir, 0) + COALESCE(pending.qty_pending, 0)-COALESCE(supp.qty_supp,0)) AS qty_with_pending,
            COALESCE(opname.qty_fisik, 0) AS qty_fisik,
            COALESCE(supp.qty_supp,0) AS qty_supp,
            IF(COALESCE(zahir.qty_zahir, 0) + COALESCE(pending.qty_pending, 0) = COALESCE(opname.qty_fisik, 0),1,0) AS status,
            a.qty,
            a.qty_box,
            a.qty_pcs,
            a.tim,
            (b.p * b.l * b.t) AS dimensi,
            a.inputer,
            log.keterangan
            FROM tb_ics_opname a
            JOIN tb_mbarang b ON b.nm_barang = a.nama_barang
            LEFT JOIN(
                SELECT nama_barang, exp_date , keterangan
                FROM tb_log_ics
                GROUP BY nama_barang, exp_date
            ) AS log ON log.nama_barang = a.nama_barang AND log.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_zahir
                FROM tb_ics
                GROUP BY nama_barang, exp_date
            ) AS zahir ON zahir.nama_barang = a.nama_barang AND zahir.exp_date = a.exp_date
            LEFT JOIN (
                SELECT d.nama_barang, d.exp_date, SUM(d.qty) AS qty_pending
                FROM tb_ics_do d
                JOIN tb_ics i ON i.nama_barang = d.nama_barang AND i.exp_date = d.exp_date
                GROUP BY d.nama_barang, d.exp_date
            ) AS pending ON pending.nama_barang = a.nama_barang AND pending.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_fisik
                FROM tb_ics_opname
                WHERE tim = '$tim'
                GROUP BY nama_barang, exp_date
            ) AS opname ON opname.nama_barang = a.nama_barang AND opname.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_supp
                FROM tb_ics_supp
                GROUP BY nama_barang, exp_date
                ) AS supp ON supp.nama_barang = a.nama_barang AND supp.exp_date = a.exp_date
            WHERE b.kd_system = '$kdbarang'
            AND a.tim = '$tim'
            ORDER BY a.nama_barang, a.exp_date")->result();
    }

    public function fefo_match_t1()
    {
        return $this->db->query("SELECT
            COUNT(*) AS total_barang,
            SUM((COALESCE(op.qty_input, 0) = (ics.qty_buku + COALESCE(pending.qty_pending, 0))) + 0) AS total_match,
            SUM((COALESCE(op.qty_input, 0) != (ics.qty_buku + COALESCE(pending.qty_pending, 0))) + 0) AS total_notmatch
        FROM (
            SELECT 
                nama_barang, 
                exp_date,
                SUM(qty) AS qty_buku
            FROM tb_ics
            GROUP BY nama_barang, exp_date
        ) AS ics
        LEFT JOIN (
            SELECT 
                nama_barang, 
                exp_date,
                SUM(qty) AS qty_input
            FROM tb_ics_opname
            WHERE tim = '1'
            GROUP BY nama_barang, exp_date
        ) AS op ON ics.nama_barang = op.nama_barang AND ics.exp_date = op.exp_date
        LEFT JOIN (
            SELECT
                nama_barang,
                exp_date,
                SUM(qty) AS qty_pending
            FROM tb_ics_do 
            GROUP BY nama_barang, exp_date
        ) AS pending ON pending.nama_barang = ics.nama_barang AND pending.exp_date = ics.exp_date;")->result();
    }

    public function fefo_match_t2()
    {
        return $this->db->query("SELECT
            COUNT(*) AS total_barang,
            SUM((COALESCE(op.qty_input, 0) = (ics.qty_buku + COALESCE(pending.qty_pending, 0))) + 0) AS total_match,
            SUM((COALESCE(op.qty_input, 0) != (ics.qty_buku + COALESCE(pending.qty_pending, 0))) + 0) AS total_notmatch
        FROM (
            SELECT 
                nama_barang, 
                exp_date,
                SUM(qty) AS qty_buku
            FROM tb_ics
            GROUP BY nama_barang, exp_date
        ) AS ics
        LEFT JOIN (
            SELECT 
                nama_barang, 
                exp_date,
                SUM(qty) AS qty_input
            FROM tb_ics_opname
            WHERE tim = '1'
            GROUP BY nama_barang, exp_date
        ) AS op ON ics.nama_barang = op.nama_barang AND ics.exp_date = op.exp_date
        LEFT JOIN (
            SELECT
                nama_barang,
                exp_date,
                SUM(qty) AS qty_pending
            FROM tb_ics_do 
            GROUP BY nama_barang, exp_date
        ) AS pending ON pending.nama_barang = ics.nama_barang AND pending.exp_date = ics.exp_date;")->result();
    }

    public function create_view_ics()
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
        END AS keterangan,

        mb.dimensi,
        FLOOR((IFNULL(s.qty, 0) + IFNULL(p.qty, 0) - IFNULL(d.total_do, 0)) / NULLIF(mb.dimensi, 0)) AS qty_box,
        ((IFNULL(s.qty, 0) + IFNULL(p.qty, 0) - IFNULL(d.total_do, 0)) % NULLIF(mb.dimensi, 0)) AS qty_pcs

    FROM (
        SELECT nama_barang, exp_date FROM tb_ics
        UNION
        SELECT nama_barang, exp_date FROM tb_ics_po
        UNION
        SELECT nama_barang, tgl_exp AS exp_date FROM tb_detail_do
        UNION
        SELECT nama_barang, exp_date FROM tb_ics_opname
    ) AS x

    LEFT JOIN (
        SELECT nama_barang, exp_date, SUM(qty) AS qty
        FROM tb_ics
        GROUP BY nama_barang, exp_date
    ) s ON x.nama_barang = s.nama_barang AND x.exp_date = s.exp_date

    LEFT JOIN (
        SELECT nama_barang, exp_date, SUM(qty) AS qty
        FROM tb_ics_po
        GROUP BY nama_barang, exp_date
    ) p ON x.nama_barang = p.nama_barang AND x.exp_date = p.exp_date

    LEFT JOIN (
        SELECT nama_barang, tgl_exp AS exp_date, SUM(qty) AS total_do
        FROM tb_detail_do
        GROUP BY nama_barang, tgl_exp
    ) d ON x.nama_barang = d.nama_barang AND x.exp_date = d.exp_date

    LEFT JOIN (
        SELECT nama_barang, exp_date, SUM(qty) AS qty
        FROM tb_ics_opname
        GROUP BY nama_barang, exp_date
    ) o ON x.nama_barang = o.nama_barang AND x.exp_date = o.exp_date

    LEFT JOIN (
        SELECT nm_barang, MIN(p * l * t) AS dimensi
        FROM tb_master_barang
        GROUP BY nm_barang
    ) mb ON x.nama_barang = mb.nm_barang

    ORDER BY x.nama_barang, x.exp_date
        ");
    }
}
