<?php

use JetBrains\PhpStorm\Internal\ReturnTypeContract;

defined('BASEPATH') or exit('No direct script access allowed');


class M_Logistik extends CI_Model
{
    private function _ensureSoLoadingPlanColumns()
    {
        $this->load->dbforge();

        $columns = [
            'loading_tgl_pengiriman' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'kd_rute',
            ],
            'loading_jenis_pengiriman' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'expedisi_kantor',
                'null' => false,
                'after' => 'loading_tgl_pengiriman',
            ],
            'loading_driver' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'loading_jenis_pengiriman',
            ],
            'loading_nolambung' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'loading_driver',
            ],
            'loading_urutan' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => false,
                'after' => 'loading_nolambung',
            ],
        ];

        foreach ($columns as $field => $definition) {
            if (!$this->db->field_exists($field, 'tbso_sales_order')) {
                $this->dbforge->add_column('tbso_sales_order', [$field => $definition]);
            }
        }
    }

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

    public function insert_do_detail_ics($data)
    {
        return $this->db->insert('tb_ics_do', $data);
    }

    public function insert_batch_do_detail_ics($data)
    {
        return $this->db->insert_batch('tb_ics_do', $data);
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

    // public function get_data_penjualan_zahir()
    // {
    //     $this->db->select('
    //         a.tgl_inputer,
    //         a.kd_faktur,
    //         b.nama_customer,
    //         b.nama_kios,
    //         b.alamat_kios,
    //         b.regional,
    //         a.kd_rute,
    //         c.keterangan AS keterangan_rute,
    //         COUNT(DISTINCT a.kd_barang) AS total_barang,
    //         a.data_sts 
    //     ');
    //     $this->db->from('tb_pre_do a');
    //     $this->db->join('tb_customer b', 'b.kd_customer = a.kd_customer', 'inner');
    //     $this->db->join('tb_rutecs c', 'c.kd_rute = a.kd_rute', 'inner');
    //     $this->db->join('tb_detail_do d', 'd.kd_faktur = a.kd_faktur', 'left');
    //     $this->db->where('a.data_sts', 1);
    //     $this->db->where('d.kd_faktur IS NULL', null, false);
    //     $this->db->group_by('a.kd_faktur');
    //     $this->db->order_by('total_barang', 'DESC');

    //     $query = $this->db->get();
    //     return $query->result();
    // }

    public function get_data_penjualan_zahir()
    {
        $sql = "
            SELECT
                f.no_faktur                                         AS kd_faktur,
                f.tanggal_faktur                                    AS tgl_inputer,
                DATE_FORMAT(f.tanggal_faktur, '%d/%m/%Y')           AS tgl_inputer_fmt,
                f.kd_customer,
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM tb_detail_do d
                        WHERE d.kd_faktur = f.no_faktur
                        AND d.kd_customer = f.kd_customer
                    ) THEN 'proses_do'
                    WHEN EXISTS (
                        SELECT 1 FROM tb_tmp_detaildo t
                        WHERE t.kd_faktur = f.no_faktur
                        AND t.kd_customer = f.kd_customer
                    ) THEN 'in_delivery'
                    ELSE 'list_do'
                END                                                 AS data_sts,
                c.nama_customer,
                c.nama_kios,
                c.alamat_kios,
                c.regional,
                COALESCE(r.kd_rute, c.regional)                    AS kd_rute,
                COALESCE(r.keterangan, c.regional)                  AS keterangan_rute,
                COUNT(DISTINCT fd.kd_barang)                        AS total_barang
            FROM tbso_faktur_penjualan f
            INNER JOIN tbso_faktur_detail fd
                ON fd.id_faktur = f.id_faktur
            INNER JOIN tb_customer c
                ON c.kd_customer = f.kd_customer
            LEFT JOIN tb_rutecs r
                ON r.kd_rute = c.regional
            WHERE COALESCE(f.status, 'confirmed') <> 'cancelled'
            AND NOT EXISTS (
                SELECT 1 FROM tb_detail_do d
                WHERE d.kd_faktur = f.no_faktur
                AND d.kd_customer = f.kd_customer
            )
            AND NOT EXISTS (
                SELECT 1 FROM tb_tmp_detaildo t
                WHERE t.kd_faktur = f.no_faktur
                AND t.kd_customer = f.kd_customer
            )
            GROUP BY
                f.id_faktur, f.no_faktur, f.tanggal_faktur,
                f.kd_customer, f.status, c.nama_customer,
                c.nama_kios, c.alamat_kios, c.regional,
                r.kd_rute, r.keterangan
            ORDER BY f.tanggal_faktur DESC
        ";

        return $this->db->query($sql)->result();
    }

    public function finalize_ledger_do(array $detailRows, $kd_do, array $kd_faktur_list, $gudang_id = '6')
    {
        if (empty($detailRows)) return false;

        $now = date('Y-m-d H:i:s');

        // 1. Update tipe RESERVE → RELEASE untuk semua ledger SALES_ORDER lama
        if (!empty($kd_faktur_list)) {
            $this->db->where_in('ref_no', $kd_faktur_list);
            $this->db->where('ref_type', 'SALES_ORDER');
            $this->db->where('tipe', 'RESERVE');
            $this->db->update('tberp_stock_ledger', ['tipe' => 'RELEASE']);
        }

        // 2. Insert RESERVE baru dengan ref_type DELIVERY_ORDER
        $rows = [];
        foreach ($detailRows as $row) {
            $rows[] = [
                'kd_barang'    => $row['kd_barang'],
                'gudang_id'    => $gudang_id,
                'no_lot'       => $row['no_lot']   ?? null,
                'expired_date' => $row['tgl_exp']  ?? null,
                'qty'          => $row['qty'],
                'tipe'         => 'RESERVE',
                'ref_no'       => $kd_do,
                'ref_type'     => 'DELIVERY_ORDER',
                'created_at'   => $now,
            ];
        }

        return $this->db->insert_batch('tberp_stock_ledger', $rows);
    }

    public function get_detail_do_for_ledger($kd_do)
    {
        return $this->db->query("
            SELECT
                d.kd_barang,
                d.no_lot,
                d.tgl_exp,
                SUM(d.qty) AS qty
            FROM tb_detail_do d
            WHERE d.kd_do = ?
            GROUP BY d.kd_barang, d.no_lot, d.tgl_exp
        ", [$kd_do])->result_array();
    }

    public function get_master_barang_not_listed()
    {
        return $this->db->query("SELECT 
            a.tgl_inputer,
            DATE_FORMAT(STR_TO_DATE(a.tgl_inputer, '%e/%c/%Y'), '%d/%m/%Y') AS tgl_inputer_fmt,
            a.kd_faktur,
            b.nama_customer,
            b.nama_kios,
            a.kd_barang,
            a.nama_barang,
            COUNT(*) AS total_row
        FROM tb_pre_do a
        INNER JOIN tb_customer b
            ON b.kd_customer = a.kd_customer
        LEFT JOIN tbpo_barang m
            ON m.kode_barang = a.kd_barang
        WHERE m.kd_barang IS NULL
        GROUP BY a.kd_faktur, a.kd_barang
        ORDER BY STR_TO_DATE(a.tgl_inputer, '%e/%c/%Y') DESC, a.kd_faktur DESC;")->result();
    }

    public function update_kd_barang_pre_do_by_faktur($kd_faktur, $old_kd_barang, $new_kd_barang)
    {
        $this->db->where('kd_faktur', $kd_faktur);
        $this->db->where('kd_barang', $old_kd_barang);
        $this->db->update('tb_pre_do', [
            'kd_barang' => $new_kd_barang
        ]);

        return $this->db->affected_rows();
    }

    public function get_barang_not_listed()
    {
        return $this->db->query("SELECT
            a.tgl_inputer,
            DATE_FORMAT(
                STR_TO_DATE(a.tgl_inputer, '%e/%c/%Y'),
                '%d/%m/%Y'
            ) AS tgl_inputer_fmt,
            a.kd_faktur,
            b.nama_customer,
            b.nama_kios,
            b.alamat_kios,
            b.regional,
            a.kd_rute,
            c.keterangan AS keterangan_rute,
            COUNT(DISTINCT a.kd_barang) AS total_barang,
            a.data_sts,
            'Master Barang Tidak Ada' AS status_validasi
        FROM tb_pre_do a
        INNER JOIN tb_customer b
            ON b.kd_customer = a.kd_customer
        INNER JOIN tb_rutecs c
            ON c.kd_rute = a.kd_rute
        AND EXISTS (
            SELECT 1
            FROM tb_pre_do x
            LEFT JOIN tbpo_barang m 
                ON m.kode_barang = x.kd_barang
            WHERE x.kd_faktur = a.kd_faktur
            AND m.kd_barang IS NULL
        )
        GROUP BY 
            a.kd_faktur,
            a.tgl_inputer,
            b.nama_customer,
            b.nama_kios,
            b.alamat_kios,
            b.regional,
            a.kd_rute,
            c.keterangan,
            a.data_sts
        ORDER BY STR_TO_DATE(a.tgl_inputer, '%e/%c/%Y') DESC;")->result();
    }

    // public function get_list_by_rute($rute)
    // {
    //     $this->db->select('
    //         a.tgl_inputer,
    //         a.kd_faktur,
    //         b.nama_customer,
    //         b.nama_kios,
    //         b.alamat_kios,
    //         b.regional,
    //         a.kd_rute,
    //         c.keterangan AS keterangan_rute,
    //         COUNT(DISTINCT a.kd_barang) AS total_barang,
    //         a.data_sts 
    //     ');
    //     $this->db->from('tb_pre_do a');
    //     $this->db->join('tb_customer b', 'b.kd_customer = a.kd_customer', 'inner');
    //     $this->db->join('tb_rutecs c', 'c.kd_rute = a.kd_rute', 'inner');
    //     $this->db->join('tb_detail_do d', 'd.kd_faktur = a.kd_faktur', 'left');
    //     $this->db->where('a.data_sts', 1);
    //     $this->db->where('d.kd_faktur IS NULL', null, false);
    //     $this->db->where('a.kd_rute', $rute);
    //     $this->db->group_by('a.kd_faktur');

    //     $query = $this->db->get();
    //     return $query->result();
    // }

    public function get_list_by_rute()
    {
        return $this->db->query("
            SELECT
                DATE_FORMAT(f.tanggal_faktur, '%d/%m/%Y') AS tgl_inputer,
                f.no_faktur         AS kd_faktur,
                c.nama_customer,
                c.nama_kios,
                c.alamat_kios,
                c.regional,
                COALESCE(r.kd_rute, c.regional)     AS kd_rute,
                COALESCE(r.keterangan, c.regional)   AS keterangan_rute,
                COUNT(DISTINCT fd.kd_barang)         AS total_barang,
                ROUND(SUM(fd.qty * COALESCE(mb.berat, fd.berat_gram, 0)) / 1000000, 3) AS total_tonase_faktur,
                ROUND(SUM(fd.qty * COALESCE(mb.kubikasi, fd.kubikasi_m3, 0)), 4) AS total_kubikasi,
                CASE
                    WHEN d.kd_faktur IS NOT NULL THEN 'proses_do'
                    WHEN t.kd_faktur IS NOT NULL THEN 'in_delivery'
                    ELSE 'list_do'
                END                                  AS data_sts
            FROM tbso_faktur_penjualan f
            JOIN tbso_faktur_detail fd ON fd.id_faktur = f.id_faktur
            JOIN tb_customer c ON c.kd_customer = f.kd_customer
            LEFT JOIN tbpo_barang mb ON mb.kode_barang = fd.kd_barang
            LEFT JOIN tb_rutecs r ON r.kd_rute = c.regional
            LEFT JOIN tb_detail_do d
                ON d.kd_faktur = f.no_faktur
                AND d.kd_customer = f.kd_customer
            LEFT JOIN tb_tmp_detaildo t
                ON t.kd_faktur = f.no_faktur
                AND t.kd_customer = f.kd_customer
            WHERE COALESCE(f.status, 'confirmed') <> 'cancelled'
            AND d.kd_faktur IS NULL
            AND t.kd_faktur IS NULL
            GROUP BY f.id_faktur, f.no_faktur
        ")->result();
    }

    public function get_do_tonase_kubikasi_summary($kd_do)
    {
        return $this->db->query("
            SELECT
                ROUND(COALESCE(SUM(d.qty * COALESCE(m.berat, 0)), 0) / 1000000, 3) AS total_tonase_faktur,
                ROUND(COALESCE(SUM(d.qty * COALESCE(m.kubikasi, 0)), 0), 4) AS total_kubikasi
            FROM tb_detail_do d
            LEFT JOIN tbpo_barang m ON m.kode_barang = d.kd_barang
            WHERE d.kd_do = ?
        ", [$kd_do])->row();
    }

    public function get_do_cust($kd_faktur)
    {
        return $this->db->query("
            SELECT DISTINCT
                f.no_faktur             AS kd_faktur,
                f.kd_customer,
                c.regional              AS kd_rute,
                f.tanggal_faktur        AS tgl_inputer,
                f.status
            FROM tbso_faktur_penjualan f
            INNER JOIN tb_customer c
                ON c.kd_customer = f.kd_customer
            WHERE f.no_faktur = ?
            LIMIT 1
        ", [$kd_faktur])->result();
    }

    public function sync_faktur_status_by_do($kd_do, $status)
    {
        $faktur_list = $this->db
            ->select('kd_faktur')
            ->distinct()
            ->where('kd_do', $kd_do)
            ->get('tb_detail_do')
            ->result_array();

        if (empty($faktur_list)) {
            return false;
        }

        $kd_faktur_list = array_column($faktur_list, 'kd_faktur');

        $this->db->where_in('no_faktur', $kd_faktur_list);
        return $this->db->update('tbso_faktur_penjualan', ['status' => $status]);
    }

    /**
     * Buat DO berstatus On Delivery langsung dari faktur confirmed pada satu rute.
     * Faktur yang sudah masuk detail/tmp DO tidak ikut diproses lagi.
     */
    public function create_ready_do_from_faktur_rute($kd_rute, $note, $confirm_by)
    {
        $this->_ensureSoLoadingPlanColumns();
        $plan = $this->get_so_siap_loading_plan_by_rute($kd_rute);
        $rows = $this->db->query("
            SELECT
                f.id_faktur,
                f.no_faktur,
                f.tanggal_faktur,
                f.kd_customer,
                COALESCE(f.catatan, '') AS note_faktur,
                COALESCE(NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'TANPA_RUTE') AS kd_rute,
                fd.id AS id_faktur_detail,
                fd.kd_barang,
                COALESCE(NULLIF(fd.nama_barang, ''), mb.nama_barang, '') AS nama_barang,
                fd.qty,
                COALESCE(fd.satuan, 'PCS') AS satuan,
                COALESCE(fd.no_lot, '') AS no_lot,
                fd.expired_date,
                COALESCE(fd.hrg_satuan, 0) AS nominal_p,
                COALESCE(f.jtempo, 0) AS jtempo
            FROM tbso_faktur_penjualan f
            JOIN tbso_faktur_detail fd
                ON fd.id_faktur = f.id_faktur
            JOIN tb_customer c
                ON c.kd_customer = f.kd_customer
            LEFT JOIN tbso_sales_order so
                ON so.id_so = f.id_so
            LEFT JOIN tbpo_barang mb
                ON mb.kode_barang COLLATE utf8mb4_general_ci = fd.kd_barang
            WHERE f.status = 'confirmed'
            AND COALESCE(NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'TANPA_RUTE') = ?
            AND NOT EXISTS (
                SELECT 1 FROM tb_detail_do d
                WHERE d.kd_faktur = f.no_faktur
                AND d.kd_customer = f.kd_customer
            )
            AND NOT EXISTS (
                SELECT 1 FROM tb_tmp_detaildo t
                WHERE t.kd_faktur = f.no_faktur
                AND t.kd_customer = f.kd_customer
            )
            ORDER BY COALESCE(NULLIF(so.loading_urutan, 0), 999999) ASC, f.tanggal_faktur ASC, f.no_faktur ASC, fd.id ASC
        ", [$kd_rute])->result();

        if (empty($rows)) return false;

        date_default_timezone_set('Asia/Jakarta');
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $today_view = date('d/m/Y');
        $kd_do = $this->generate_kd_do();

        $detail_rows = [];
        $faktur_ids = [];
        $faktur_numbers = [];
        $faktur_order = [];

        foreach ($rows as $row) {
            $faktur_ids[(int)$row->id_faktur] = (int)$row->id_faktur;
            $faktur_numbers[$row->no_faktur] = $row->no_faktur;
            if (!isset($faktur_order[$row->no_faktur])) {
                $faktur_order[$row->no_faktur] = count($faktur_order) + 1;
            }

            $detail_rows[] = [
                'id_pre_do'     => (int)$row->id_faktur_detail,
                'kd_do'         => $kd_do,
                'kd_faktur'     => $row->no_faktur,
                'tgl_transaksi' => $row->tanggal_faktur,
                'kd_rute'       => $row->kd_rute,
                'kd_customer'   => $row->kd_customer,
                'kd_barang'     => $row->kd_barang,
                'nama_barang'   => $row->nama_barang,
                'qty'           => $row->qty,
                'satuan'        => $row->satuan,
                'no_lot'        => $row->no_lot,
                'tgl_exp'       => $row->expired_date,
                'norut'         => $faktur_order[$row->no_faktur],
                'nominal_p'     => $row->nominal_p,
                'jtempo'        => $row->jtempo,
                'note_faktur'   => $row->note_faktur,
                'dt_status'     => 1,
                'status'        => 1,
                'input_at'      => $today_view,
                'create_at'     => $now,
            ];
        }

        $this->db->trans_begin();

        $this->db->insert('tb_do', [
            'kd_do'                => $kd_do,
            'nolambung'            => $plan ? (string)$plan->loading_nolambung : '',
            'regional'             => $kd_rute,
            'driver'               => $plan ? (string)$plan->loading_driver : '',
            'tgl_pengiriman'       => ($plan && !empty($plan->loading_tgl_pengiriman)) ? $plan->loading_tgl_pengiriman : $today,
            'tgl_create'           => $now,
            'status'               => 5,
        ]);

        $this->db->insert('tb_log_confirm_sales', [
            'kd_do'      => $kd_do,
            'action'     => 'siap',
            'note'       => $note,
            'confirm_by' => $confirm_by,
            'confirm_at' => $now,
        ]);

        if ($this->db->trans_status() && !empty($detail_rows)) {
            $this->db->insert_batch('tb_detail_do', $detail_rows);
        }

        if ($this->db->trans_status() && !empty($faktur_ids)) {
            $this->db->where_in('id_faktur', array_values($faktur_ids));
            $this->db->update('tbso_faktur_penjualan', [
                'status'    => 'selesai_do',
                'update_by' => $confirm_by,
            ]);
        }

        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();

        return [
            'kd_do'        => $kd_do,
            'total_faktur' => count($faktur_numbers),
            'total_detail' => count($detail_rows),
        ];
    }

    public function sync_faktur_selesai_do_for_on_delivery()
    {
        $this->db->query("
            UPDATE tbso_faktur_penjualan f
            JOIN (
                SELECT DISTINCT d.kd_faktur
                FROM tb_detail_do d
                JOIN tb_do h ON h.kd_do = d.kd_do
                WHERE h.status = 5
            ) od ON od.kd_faktur = f.no_faktur
            SET f.status = 'selesai_do',
                f.update_by = COALESCE(f.update_by, 'system')
            WHERE f.status = 'proses_do'
        ");

        return $this->db->affected_rows();
    }

    public function has_remaining_so_ready_faktur_by_rute($kd_rute)
    {
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '') {
            return false;
        }

        $row = $this->db->query("
            SELECT COUNT(*) AS total
            FROM (
                SELECT
                    so.id_so,
                    SUM(GREATEST(COALESCE(sd.qty_siap_faktur, sd.qty) - COALESCE(sd.qty_faktur, 0), 0)) AS qty_ready_remaining
                FROM tbso_sales_order so
                JOIN tbso_sales_order_detail sd
                    ON sd.id_so = so.id_so
                LEFT JOIN tb_customer c
                    ON c.kd_customer = so.kd_customer
                WHERE so.status IN ('siap_faktur', 'partial', 'completed')
                AND COALESCE(NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'TANPA_RUTE') = ?
                GROUP BY so.id_so
                HAVING qty_ready_remaining > 0.001
            ) x
        ", [$kd_rute])->row_array();

        return (int)($row['total'] ?? 0) > 0;
    }

    public function has_so_loading_verification_by_rute($kd_rute)
    {
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '') {
            return false;
        }

        $row = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c
                ON c.kd_customer = so.kd_customer
            WHERE so.status = 'sedang_verifikasi'
            AND COALESCE(NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'TANPA_RUTE') = ?
        ", [$kd_rute])->row_array();

        return (int)($row['total'] ?? 0) > 0;
    }

    public function insert_tmp_detdo_batch($data)
    {
        return $this->db->insert_batch('tb_tmp_detaildo', $data);
    }

    public function insert_fakturfrom_draft_batch($data)
    {
        return $this->db->insert_batch('tb_detail_do', $data);
    }

    public function get_do_cust_byfaktur($kd_faktur)
    {
        return $this->db->query("
            SELECT
                fd.id                                               AS id,
                f.no_faktur                                         AS kd_faktur,
                f.kd_customer,
                COALESCE(r.kd_rute, c.regional)                    AS kd_rute,
                f.tanggal_faktur                                    AS tgl_inputer,
                COALESCE(f.catatan, '')                             AS note_faktur,
                fd.kd_barang,
                COALESCE(fd.nama_barang, mb.nama_barang)             AS nama_barang,
                fd.qty                                              AS qty,
                COALESCE(fd.satuan, 'PCS')                          AS satuan,
                COALESCE(fd.no_lot, '')                             AS no_lot,
                fd.expired_date                                     AS tgl_exp,
                COALESCE(fd.hrg_satuan, 0)                          AS nominal_p,
                0                                                   AS jtempo,
                1                                                   AS barang_sts
            FROM tbso_faktur_penjualan f
            INNER JOIN tbso_faktur_detail fd
                ON fd.id_faktur = f.id_faktur
            INNER JOIN tb_customer c
                ON c.kd_customer = f.kd_customer
            LEFT JOIN tb_rutecs r
                ON r.kd_rute = c.regional
            LEFT JOIN tbpo_barang mb
                ON mb.kode_barang = fd.kd_barang
            WHERE f.no_faktur = ?
        ", [$kd_faktur])->result();
    }

    public function get_detail_do_not_exist_saldo_awal($kd_faktur)
    {
        $sql = "SELECT
            m.kode_barang_system as kode_barang_system ,
            d.kd_barang AS kode_barang_zahir,
            d.nama_barang AS nama_barang,
            s.wilayah_id AS wilayah_id,
            s.koordinat_id AS koordinat_id,
            s.barang_pic AS barang_pic,
            d.qty AS qty,
            d.no_lot AS nolot,
            DATE_FORMAT(STR_TO_DATE(d.tgl_exp, '%m/%d/%Y'),'%m/%d/%Y') AS exp_date
        FROM tb_detail_do d
        LEFT JOIN tb_saldo_awal s
            ON s.kode_barang_zahir = d.kd_barang
        AND s.exp_date = DATE_FORMAT(
                STR_TO_DATE(d.tgl_exp, '%m/%d/%Y'),
                '%m/%d/%Y')
        LEFT JOIN tbpo_barang m
            ON m.kode_barang = d.kd_barang
        WHERE d.kd_do = '$kd_faktur'
        AND s.exp_date IS NULL;";
        return $this->db->query($sql, [$kd_faktur])->result();
    }


    public function get_do_cust_byfaktur_ics($kd_do)
    {
        return $this->db->query("
            SELECT
                a.kd_do,
                a.kd_faktur,
                a.tgl_transaksi,
                a.kd_barang,
                a.nama_barang,
                a.qty,
                COALESCE(a.nominal_p, 0) AS nominal_p,
                a.tgl_exp,
                a.no_lot
            FROM tb_detail_do a
            WHERE a.kd_do = ?
        ", [$kd_do])->result();
    }

    public function update_pre_do_delivery_at($kd_faktur_list, $dateprenow)
    {
        $this->db->where_in('kd_faktur', $kd_faktur_list);
        return $this->db->update('tb_pre_do', [
            'delivery_at' => $dateprenow
        ]);
    }


    public function det_do_cust($kd)
    {
        // Gunakan kembali logika yang sama dengan get_do_cust_byfaktur
        return $this->get_do_cust_byfaktur($kd);
    }

    public function insert_tmp_do($data)
    {
        return $this->db->insert('tb_tmp_do', $data);
    }

    public function insertlog_do($data)
    {
        return $this->db->insert('tb_log_do', $data);
    }

    public function insert_tmp_detdo($data)
    {
        if (isset($data['barang_sts']) && $data['barang_sts'] != 3) {
            return $this->db->insert('tb_tmp_detaildo', $data);
        }
    }

    public function update_sts_pre_do($kd_faktur, $data)
    {
        $status_map = [
            '1' => 'list_do',
            '2' => 'proses_do',     // masuk draft DO
            '3' => 'selesai_do',    // on delivery
            '4' => 'proses_do',
        ];

        $so_status = null;
        if (isset($data['data_sts'])) {
            $so_status = $status_map[$data['data_sts']] ?? null;
        }

        if ($so_status !== null) {
            $this->db->where('no_faktur', $kd_faktur);
            return $this->db->update('tbso_faktur_penjualan', ['status' => $so_status]);
        }

        return false;
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

    public function updated_repost_do($kd, $data)
    {
        $this->db->where('kd_do', $kd);
        return $this->db->update('tb_detail_do', $data);
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
        return $this->db->query("
            SELECT
                a.id,
                a.kd_faktur,
                a.norut_do,
                c.nama_customer,
                c.alamat_kios,
                c.regional,
                COALESCE(r.kd_rute, c.regional) AS kdrute,
                c.telp1,
                c.telp2,
                COALESCE(c.jam_buka_tutup, '-')        AS jam_buka_tutup,
                COALESCE(c.karakteristik_kios, '-')    AS toko
            FROM tb_tmp_do a
            JOIN tbso_faktur_penjualan f ON f.no_faktur = a.kd_faktur
            JOIN tb_customer c ON c.kd_customer = f.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = c.regional
            GROUP BY a.kd_faktur
        ")->result();
    }

    public function getkdfaktur($kd)
    {
        return $this->db->query("
            SELECT
                a.kd_faktur,
                a.norut_do
            FROM tb_tmp_do a 
            WHERE a.kd_faktur = ?
        ", [$kd]);
    }

    public function update_sts_detail_checker($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_detail_do', $data);
    }

    public function update_note_faktur($kd_faktur, $note_faktur)
    {
        $this->db->where('kd_faktur', $kd_faktur);
        return $this->db->update('tb_detail_do', [
            'note_faktur' => $note_faktur
        ]);
    }

    public function update_urutan_faktur_do($kd_do, array $urutan_faktur)
    {
        if (empty($urutan_faktur)) {
            return false;
        }

        $this->db->trans_start();
        foreach ($urutan_faktur as $index => $kd_faktur) {
            $this->db->where('kd_do', $kd_do);
            $this->db->where('kd_faktur', $kd_faktur);
            $this->db->update('tb_detail_do', [
                'norut' => $index + 1
            ]);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
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

    /**
     * Update status konfirmasi sales pada tb_do
     */
    // SESUDAH - status 3 = Proses DO (bukan On Delivery)
    public function update_sales_confirm($kd_do, $action, $confirm_by, $note = '')
    {
        $now = date('Y-m-d H:i:s');

        $this->db->where('kd_do', $kd_do);
        $this->db->update('tb_do', [
            'status' => ($action === 'siap') ? 3 : 2  // 3 = Proses DO
        ]);

        $this->db->insert('tb_log_confirm_sales', [
            'kd_do'      => $kd_do,
            'action'     => $action,
            'note'       => $note,
            'confirm_by' => $confirm_by,
            'confirm_at' => $now
        ]);

        return $this->db->affected_rows();
    }

    /**
     * Ambil list DO untuk halaman Sales — hanya status 2 (menunggu konfirmasi)
     * dan status 3 (sudah on delivery)
     */
    public function get_do_for_sales()
    {
        return $this->db->query("
            SELECT
                a.kd_do                     AS kddo,
                a.tgl_create                AS createat,
                a.tgl_pengiriman            AS tglkirim,
                a.nolambung                 AS nopol,
                a.regional                  AS rute,
                a.status,
                lcs.action                  AS sales_confirm_status,
                lcs.confirm_by              AS sales_confirm_by,
                lcs.confirm_at              AS sales_confirm_at,
                lcs.note                    AS sales_confirm_note,
                (
                    SELECT COUNT(DISTINCT kd_barang)
                    FROM tb_detail_do
                    WHERE kd_do = a.kd_do
                ) AS totalbarang,
                (
                    SELECT COUNT(DISTINCT kd_faktur)
                    FROM tb_detail_do
                    WHERE kd_do = a.kd_do
                ) AS totalfaktur
            FROM tb_do a
            LEFT JOIN tb_log_confirm_sales lcs
                ON lcs.id = (
                    SELECT l2.id
                    FROM tb_log_confirm_sales l2
                    WHERE l2.kd_do = a.kd_do
                    ORDER BY l2.confirm_at DESC, l2.id DESC
                    LIMIT 1
                )
            WHERE a.status IN (3, 5)
            AND (
                SELECT COUNT(DISTINCT kd_faktur)
                FROM tb_detail_do
                WHERE kd_do = a.kd_do
            ) > 0
            ORDER BY a.tgl_create DESC
        ")->result();
    }

    /**
     * Ambil list DO di body.php — sekarang status 1=draft, 2=menunggu sales, 3=on delivery
     */
    public function getdo()
    {
        return $this->db->query("
            SELECT
                a.kd_do                     AS kddo,
                a.tgl_create                AS createat,
                a.tgl_pengiriman            AS tglkirim,
                a.nolambung                 AS nopol,
                a.regional                  AS rute,
                a.status,
                lcs.action                  AS sales_confirm_status,
                lcs.confirm_by              AS sales_confirm_by,
                lcs.confirm_at              AS sales_confirm_at,
                lcs.note                    AS sales_confirm_note,
                (
                    SELECT COUNT(DISTINCT kd_barang)
                    FROM tb_detail_do
                    WHERE kd_do = a.kd_do
                ) AS totalbarang,
                (
                    SELECT COUNT(DISTINCT kd_faktur)
                    FROM tb_detail_do
                    WHERE kd_do = a.kd_do
                ) AS totalfaktur
            FROM tb_do a
            LEFT JOIN tb_log_confirm_sales lcs
                ON lcs.id = (
                    SELECT l2.id
                    FROM tb_log_confirm_sales l2
                    WHERE l2.kd_do = a.kd_do
                    ORDER BY l2.confirm_at DESC, l2.id DESC
                    LIMIT 1
                )
            WHERE (
                SELECT COUNT(DISTINCT kd_faktur)
                FROM tb_detail_do
                WHERE kd_do = a.kd_do
            ) > 0
            ORDER BY a.tgl_create DESC
        ")->result();
    }

    public function get_so_siap_loading()
    {
        $this->_ensureSoLoadingPlanColumns();
        return $this->db->query("
            SELECT
                so.id_so,
                so.no_so,
                so.tanggal_transaksi,
                so.status,
                so.customer_name,
                so.catatan,
                so.create_by,
                so.update_by,
                so.update_at,
                so.total_tonase,
                so.total_kubikasi,
                c.nama_customer,
                c.nama_kios,
                c.regional,
                c.kd_rute AS customer_kd_rute,
                so.kd_rute AS so_kd_rute,
                so.loading_tgl_pengiriman,
                so.loading_jenis_pengiriman,
                so.loading_driver,
                so.loading_nolambung,
                so.loading_urutan,
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
                COALESCE(d.jumlah_item, 0) AS jumlah_item,
                COALESCE(d.jumlah_item_terverifikasi, 0) AS jumlah_item_terverifikasi,
                COALESCE(d.total_qty_order, 0) AS total_qty_order,
                COALESCE(d.total_qty_faktur, 0) AS total_qty_faktur,
                COALESCE(d.total_qty_outstanding, 0) AS total_qty_outstanding,
                COALESCE(d.total_qty_siap_faktur, 0) AS total_qty_siap_faktur,
                COALESCE(d.total_qty_tidak_terkirim, 0) AS total_qty_tidak_terkirim
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c
                ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r
                ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            LEFT JOIN (
                SELECT
                    id_so,
                    COUNT(id) AS jumlah_item,
                    SUM(CASE WHEN verifikasi_loading_status = 'verified' THEN 1 ELSE 0 END) AS jumlah_item_terverifikasi,
                    SUM(qty) AS total_qty_order,
                    SUM(COALESCE(qty_faktur, 0)) AS total_qty_faktur,
                    SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS total_qty_outstanding,
                    SUM(COALESCE(qty_siap_faktur, 0)) AS total_qty_siap_faktur,
                    SUM(COALESCE(qty_tidak_terkirim, 0)) AS total_qty_tidak_terkirim
                FROM tbso_sales_order_detail
                GROUP BY id_so
            ) d ON d.id_so = so.id_so
            WHERE so.status = 'sedang_verifikasi'
            ORDER BY kd_rute ASC, COALESCE(NULLIF(so.loading_urutan, 0), 999999) ASC, so.update_at DESC, so.no_so DESC
        ")->result();
    }

    public function get_so_siap_loading_rute_summary()
    {
        $this->_ensureSoLoadingPlanColumns();
        return $this->db->query("
            SELECT
                g.kd_rute,
                g.nama_rute,
                g.total_so,
                g.total_tonase,
                g.total_kubikasi,
                g.total_qty_order,
                g.total_qty_outstanding,
                g.total_qty_siap_faktur,
                g.total_qty_tidak_terkirim,
                g.total_so_terverifikasi,
                lcs.note AS siap_loading_note,
                lcs.confirm_by AS siap_loading_confirm_by,
                lcs.confirm_at AS siap_loading_confirm_at
            FROM (
                SELECT
                    x.kd_rute,
                    MAX(x.nama_rute) AS nama_rute,
                    COUNT(*) AS total_so,
                    ROUND(COALESCE(SUM(x.total_tonase), 0), 3) AS total_tonase,
                    ROUND(COALESCE(SUM(x.total_kubikasi), 0), 4) AS total_kubikasi,
                    ROUND(COALESCE(SUM(x.total_qty_order), 0), 2) AS total_qty_order,
                    ROUND(COALESCE(SUM(x.total_qty_outstanding), 0), 2) AS total_qty_outstanding,
                    ROUND(COALESCE(SUM(x.total_qty_siap_faktur), 0), 2) AS total_qty_siap_faktur,
                    ROUND(COALESCE(SUM(x.total_qty_tidak_terkirim), 0), 2) AS total_qty_tidak_terkirim,
                    SUM(CASE WHEN x.jumlah_item > 0 AND x.jumlah_item_terverifikasi >= x.jumlah_item THEN 1 ELSE 0 END) AS total_so_terverifikasi
                FROM (
                    SELECT
                        so.id_so,
                        COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                        COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
                        COALESCE(so.total_tonase, 0) AS total_tonase,
                        COALESCE(so.total_kubikasi, 0) AS total_kubikasi,
                        COALESCE(d.jumlah_item, 0) AS jumlah_item,
                        COALESCE(d.jumlah_item_terverifikasi, 0) AS jumlah_item_terverifikasi,
                        COALESCE(d.total_qty_order, 0) AS total_qty_order,
                        COALESCE(d.total_qty_outstanding, 0) AS total_qty_outstanding,
                        COALESCE(d.total_qty_siap_faktur, 0) AS total_qty_siap_faktur,
                        COALESCE(d.total_qty_tidak_terkirim, 0) AS total_qty_tidak_terkirim
                    FROM tbso_sales_order so
                    LEFT JOIN tb_customer c
                        ON c.kd_customer = so.kd_customer
                    LEFT JOIN tb_rutecs r
                        ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
                    LEFT JOIN (
                        SELECT
                            id_so,
                            COUNT(id) AS jumlah_item,
                            SUM(CASE WHEN verifikasi_loading_status = 'verified' THEN 1 ELSE 0 END) AS jumlah_item_terverifikasi,
                            SUM(qty) AS total_qty_order,
                            SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS total_qty_outstanding,
                            SUM(COALESCE(qty_siap_faktur, 0)) AS total_qty_siap_faktur,
                            SUM(COALESCE(qty_tidak_terkirim, 0)) AS total_qty_tidak_terkirim
                        FROM tbso_sales_order_detail
                        GROUP BY id_so
                    ) d ON d.id_so = so.id_so
                    WHERE so.status = 'sedang_verifikasi'
                ) x
                GROUP BY x.kd_rute
            ) g
            LEFT JOIN tb_log_confirm_sales lcs
                ON lcs.id = (
                    SELECT l2.id
                    FROM tb_log_confirm_sales l2
                    WHERE l2.kd_do = g.kd_rute
                    AND l2.action = 'siap'
                    ORDER BY l2.confirm_at DESC, l2.id DESC
                    LIMIT 1
                )
            ORDER BY g.total_tonase DESC, g.total_so DESC, g.kd_rute ASC
        ")->result();
    }

    public function get_so_siap_loading_by_rute($kd_rute)
    {
        $this->_ensureSoLoadingPlanColumns();
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '') {
            return [];
        }

        return $this->db->query("
            SELECT
                so.id_so,
                so.no_so,
                so.tanggal_transaksi,
                so.status,
                so.customer_name,
                so.catatan,
                so.create_by,
                so.update_by,
                so.update_at,
                so.total_tonase,
                so.total_kubikasi,
                c.nama_customer,
                c.nama_kios,
                c.regional,
                c.kd_rute AS customer_kd_rute,
                so.kd_rute AS so_kd_rute,
                so.loading_tgl_pengiriman,
                so.loading_jenis_pengiriman,
                so.loading_driver,
                so.loading_nolambung,
                so.loading_urutan,
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
                COALESCE(d.jumlah_item, 0) AS jumlah_item,
                COALESCE(d.jumlah_item_terverifikasi, 0) AS jumlah_item_terverifikasi,
                COALESCE(d.total_qty_order, 0) AS total_qty_order,
                COALESCE(d.total_qty_faktur, 0) AS total_qty_faktur,
                COALESCE(d.total_qty_outstanding, 0) AS total_qty_outstanding,
                COALESCE(d.total_qty_siap_faktur, 0) AS total_qty_siap_faktur,
                COALESCE(d.total_qty_tidak_terkirim, 0) AS total_qty_tidak_terkirim
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c
                ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r
                ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            LEFT JOIN (
                SELECT
                    id_so,
                    COUNT(id) AS jumlah_item,
                    SUM(CASE WHEN verifikasi_loading_status = 'verified' THEN 1 ELSE 0 END) AS jumlah_item_terverifikasi,
                    SUM(qty) AS total_qty_order,
                    SUM(COALESCE(qty_faktur, 0)) AS total_qty_faktur,
                    SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS total_qty_outstanding,
                    SUM(COALESCE(qty_siap_faktur, 0)) AS total_qty_siap_faktur,
                    SUM(COALESCE(qty_tidak_terkirim, 0)) AS total_qty_tidak_terkirim
                FROM tbso_sales_order_detail
                GROUP BY id_so
            ) d ON d.id_so = so.id_so
            WHERE so.status = 'sedang_verifikasi'
            AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
            ORDER BY COALESCE(NULLIF(so.loading_urutan, 0), 999999) ASC, so.update_at DESC, so.no_so DESC
        ", [$kd_rute])->result();
    }

    public function get_so_siap_loading_plan_by_rute($kd_rute)
    {
        $this->_ensureSoLoadingPlanColumns();
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '') {
            return null;
        }

        // Hanya ambil plan dari SO yang BELUM masuk DO.
        // SO yang sudah jadi DO (fakturnya ada di tb_detail_do) tidak boleh dijadikan
        // referensi plan, supaya SO baru dengan rute yang sama tidak mewarisi
        // driver/kendaraan/tanggal dari pengiriman yang sudah selesai.
        return $this->db->query("
            SELECT
                so.loading_tgl_pengiriman,
                COALESCE(NULLIF(so.loading_jenis_pengiriman, ''), 'expedisi_kantor') AS loading_jenis_pengiriman,
                so.loading_driver,
                so.loading_nolambung
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            WHERE so.status IN ('sedang_verifikasi', 'siap_faktur', 'partial')
            AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
            AND (
                so.loading_tgl_pengiriman IS NOT NULL
                OR NULLIF(so.loading_driver, '') IS NOT NULL
                OR NULLIF(so.loading_nolambung, '') IS NOT NULL
            )
            AND NOT EXISTS (
                SELECT 1
                FROM tbso_faktur_penjualan fp
                JOIN tb_detail_do dd ON dd.kd_faktur = fp.no_faktur
                WHERE fp.id_so = so.id_so
            )
            ORDER BY so.loading_tgl_pengiriman IS NULL ASC, so.loading_urutan ASC, so.update_at DESC
            LIMIT 1
        ", [$kd_rute])->row();
    }

    public function save_so_siap_loading_plan_by_rute($kd_rute, array $plan)
    {
        $this->_ensureSoLoadingPlanColumns();
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '') {
            return false;
        }

        $data = [
            'loading_tgl_pengiriman' => $plan['tgl_pengiriman'] ?: null,
            'loading_jenis_pengiriman' => $plan['jenis_pengiriman'] ?: 'expedisi_kantor',
            'loading_driver' => $plan['driver'] ?: null,
            'loading_nolambung' => $plan['nolambung'] ?: null,
            'update_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->where('status', 'sedang_verifikasi');
        $this->db->where("COALESCE(NULLIF(kd_rute, ''), (SELECT c.kd_rute FROM tb_customer c WHERE c.kd_customer = tbso_sales_order.kd_customer LIMIT 1), 'TANPA_RUTE') = " . $this->db->escape($kd_rute), null, false);
        return $this->db->update('tbso_sales_order', $data);
    }

    public function update_urutan_so_siap_loading($kd_rute, array $urutan_so)
    {
        $this->_ensureSoLoadingPlanColumns();
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '' || empty($urutan_so)) {
            return false;
        }

        $existing = $this->db->query("
            SELECT so.id_so
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            WHERE so.status = 'sedang_verifikasi'
            AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
        ", [$kd_rute])->result_array();

        $existing_ids = array_map('intval', array_column($existing, 'id_so'));
        sort($existing_ids);
        $posted_ids = array_map('intval', $urutan_so);
        sort($posted_ids);

        if ($existing_ids !== $posted_ids) {
            return false;
        }

        $this->db->trans_start();
        foreach ($urutan_so as $index => $id_so) {
            $this->db->where('id_so', (int)$id_so);
            $this->db->where('status', 'sedang_verifikasi');
            $this->db->update('tbso_sales_order', [
                'loading_urutan' => $index + 1,
                'update_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function get_so_siap_loading_candidates()
    {
        return $this->db->query("
            SELECT
                so.id_so,
                so.no_so,
                so.tanggal_transaksi,
                so.status,
                so.customer_name,
                so.create_by,
                so.total_tonase,
                so.total_kubikasi,
                c.nama_customer,
                c.nama_kios,
                c.regional,
                c.kd_rute AS customer_kd_rute,
                so.kd_rute AS so_kd_rute,
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
                COALESCE(d.jumlah_item, 0) AS jumlah_item,
                COALESCE(d.total_qty_order, 0) AS total_qty_order,
                COALESCE(d.total_qty_faktur, 0) AS total_qty_faktur,
                COALESCE(d.total_qty_outstanding, 0) AS total_qty_outstanding,
                COALESCE(d.total_qty_tidak_terkirim, 0) AS total_qty_tidak_terkirim
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c
                ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r
                ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            LEFT JOIN (
                SELECT
                    id_so,
                    COUNT(id) AS jumlah_item,
                    SUM(qty) AS total_qty_order,
                    SUM(COALESCE(qty_faktur, 0)) AS total_qty_faktur,
                    SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS total_qty_outstanding,
                    SUM(COALESCE(qty_tidak_terkirim, 0)) AS total_qty_tidak_terkirim
                FROM tbso_sales_order_detail
                GROUP BY id_so
            ) d ON d.id_so = so.id_so
            WHERE so.status IN ('open', 'partial')
            AND COALESCE(d.total_qty_outstanding, 0) > 0
            ORDER BY
                FIELD(so.status, 'partial', 'open'),
                so.update_at DESC,
                so.tanggal_transaksi DESC,
                so.no_so DESC
        ")->result();
    }

    public function move_so_to_siap_loading($id_so, $update_by)
    {
        $so = $this->db->query("
            SELECT
                so.id_so,
                so.no_so,
                so.status,
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                COALESCE(d.total_qty_outstanding, 0) AS total_qty_outstanding
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN (
                SELECT id_so, SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS total_qty_outstanding
                FROM tbso_sales_order_detail
                GROUP BY id_so
            ) d ON d.id_so = so.id_so
            WHERE so.id_so = ?
            AND so.status IN ('open', 'partial')
            LIMIT 1
        ", [(int)$id_so])->row_array();

        if (!$so) {
            return ['errors' => ['SO tidak ditemukan atau statusnya bukan Open/Partial.']];
        }
        if ((float)$so['total_qty_outstanding'] <= 0.001) {
            return ['errors' => ['SO ini tidak memiliki sisa qty outstanding untuk diverifikasi.']];
        }

        $this->db->trans_start();

        $this->db->where('id_so', (int)$id_so);
        $this->db->where('GREATEST(qty - COALESCE(qty_faktur, 0), 0) >', 0, false);
        $this->db->update('tbso_sales_order_detail', [
            'qty_siap_faktur' => null,
            'qty_tidak_terkirim' => 0,
            'verifikasi_loading_status' => 'pending',
            'verifikasi_loading_note' => null,
            'verifikasi_loading_by' => null,
            'verifikasi_loading_at' => null,
        ]);

        $this->db->where('id_so', (int)$id_so);
        $this->db->where('GREATEST(qty - COALESCE(qty_faktur, 0), 0) <=', 0, false);
        $this->db->update('tbso_sales_order_detail', [
            'qty_tidak_terkirim' => 0,
            'verifikasi_loading_status' => 'verified',
            'verifikasi_loading_by' => $update_by,
            'verifikasi_loading_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->where('id_so', (int)$id_so);
        $this->db->where_in('status', ['open', 'partial']);
        $this->db->update('tbso_sales_order', [
            'status' => 'sedang_verifikasi',
            'update_by' => $update_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['errors' => ['Gagal menambahkan SO ke Siap Loading.']];
        }

        return [
            'success' => true,
            'no_so' => $so['no_so'],
            'kd_rute' => $so['kd_rute'],
        ];
    }

    public function get_so_siap_loading_verification($id_so)
    {
        return $this->db->query("
            SELECT
                so.*,
                c.nama_customer,
                c.nama_kios,
                c.regional,
                c.kd_rute AS customer_kd_rute,
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            WHERE so.id_so = ?
            AND so.status = 'sedang_verifikasi'
            LIMIT 1
        ", [(int)$id_so])->row();
    }

    public function get_so_siap_loading_verification_detail($id_so)
    {
        $rows = $this->db
            ->select('d.id AS id_so_detail, d.*')
            ->from('tbso_sales_order_detail d')
            ->where('d.id_so', (int)$id_so)
            ->order_by('d.id', 'ASC')
            ->get()
            ->result();

        foreach ($rows as $row) {
            $row->qty = (float)($row->qty ?? 0);
            $row->qty_faktur = (float)($row->qty_faktur ?? 0);
            $row->qty_outstanding = max(0, $row->qty - $row->qty_faktur);
            $qty_siap_total = $row->qty_siap_faktur === null
                ? $row->qty
                : (float)$row->qty_siap_faktur;
            $row->qty_siap_faktur_total = $qty_siap_total;
            $row->qty_siap_faktur = max(0, min($row->qty_outstanding, $qty_siap_total - $row->qty_faktur));
            $row->qty_tidak_terkirim = (float)($row->qty_tidak_terkirim ?? max(0, $row->qty_outstanding - $row->qty_siap_faktur));
            $row->verifikasi_loading_status = $row->verifikasi_loading_status ?: 'pending';
        }

        return $rows;
    }

    public function save_so_siap_loading_verification($id_so, array $items, $verified_by)
    {
        $details = $this->get_so_siap_loading_verification_detail($id_so);
        $detail_map = [];
        foreach ($details as $detail) {
            $detail_map[(int)$detail->id_so_detail] = $detail;
        }

        $errors = [];
        $updates = [];
        foreach ($items as $item) {
            $id_detail = (int)($item['id_so_detail'] ?? 0);
            if (!isset($detail_map[$id_detail])) {
                $errors[] = 'Item SO tidak valid.';
                continue;
            }

            $detail = $detail_map[$id_detail];
            $outstanding = max(0, (float)$detail->qty_outstanding);
            $qty_siap = (float)($item['qty_siap'] ?? 0);
            if ($qty_siap < 0) {
                $errors[] = 'Qty siap faktur untuk ' . htmlspecialchars($detail->nama_barang) . ' tidak boleh minus.';
                continue;
            }
            if ($qty_siap > $outstanding + 0.001) {
                $errors[] = 'Qty siap faktur untuk ' . htmlspecialchars($detail->nama_barang) . ' melebihi outstanding.';
                continue;
            }

            $updates[] = [
                'id_detail' => $id_detail,
                'qty_siap' => (float)$detail->qty_faktur + $qty_siap,
                'qty_tidak_terkirim' => max(0, $outstanding - $qty_siap),
                'note' => (string)($item['note'] ?? ''),
            ];
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }
        if (count($updates) !== count($detail_map)) {
            return ['errors' => ['Semua item SO harus diverifikasi.']];
        }

        $this->db->trans_start();
        foreach ($updates as $update) {
            $this->db->where('id', $update['id_detail']);
            $this->db->where('id_so', (int)$id_so);
            $this->db->update('tbso_sales_order_detail', [
                'qty_siap_faktur' => $update['qty_siap'],
                'qty_tidak_terkirim' => $update['qty_tidak_terkirim'],
                'verifikasi_loading_status' => 'verified',
                'verifikasi_loading_note' => $update['note'],
                'verifikasi_loading_by' => $verified_by,
                'verifikasi_loading_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->where('id_so', (int)$id_so);
        $this->db->update('tbso_sales_order', [
            'update_by' => $verified_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->trans_complete();

        return $this->db->trans_status() ? ['success' => true] : ['errors' => ['Gagal menyimpan verifikasi barang.']];
    }

    public function mark_so_siap_loading_route_ready_for_faktur($kd_rute, $update_by)
    {
        $so_list = $this->get_so_siap_loading_by_rute($kd_rute);
        if (empty($so_list)) {
            return ['errors' => ['Tidak ada SO Sedang Verifikasi pada rute ini.']];
        }

        $ids = array_map(function($so) {
            return (int)$so->id_so;
        }, $so_list);

        $this->db->trans_start();

        // Mark all items as verified if they are still pending
        $this->db->where_in('id_so', $ids);
        $this->db->where('verifikasi_loading_status', 'pending');
        $this->db->update('tbso_sales_order_detail', [
            'verifikasi_loading_status' => 'verified',
            'verifikasi_loading_by' => $update_by,
            'verifikasi_loading_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->where_in('id_so', $ids);
        $this->db->where('status', 'sedang_verifikasi');
        $this->db->update('tbso_sales_order', [
            'status' => 'siap_faktur',
            'update_by' => $update_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();

        return ['updated' => $this->db->trans_status() ? count($ids) : 0];
    }

    public function count_so_siap_loading()
    {
        return (int)$this->db
            ->where('status', 'sedang_verifikasi')
            ->count_all_results('tbso_sales_order');
    }

    public function get_so_siap_loading_by_id($id_so)
    {
        return $this->db
            ->where('id_so', $id_so)
            ->where('status', 'sedang_verifikasi')
            ->limit(1)
            ->get('tbso_sales_order')
            ->row_array();
    }

    public function kembalikan_so_siap_loading($id_so, $update_by, $catatan_logistik = '')
    {
        $status_awal = $this->db->query("
            SELECT
                CASE
                    WHEN SUM(COALESCE(qty_faktur, 0)) > 0 THEN 'partial'
                    ELSE 'open'
                END AS status_awal
            FROM tbso_sales_order_detail
            WHERE id_so = ?
        ", [(int)$id_so])->row_array();
        $target_status = $status_awal['status_awal'] ?? 'open';

        $this->db->trans_start();

        $this->db->where('id_so', (int)$id_so);
        $this->db->update('tbso_sales_order_detail', [
            'qty_siap_faktur' => null,
            'qty_tidak_terkirim' => 0,
            'verifikasi_loading_status' => 'pending',
            'verifikasi_loading_note' => trim((string)$catatan_logistik),
            'verifikasi_loading_by' => $update_by,
            'verifikasi_loading_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->where('id_so', $id_so);
        $this->db->where('status', 'sedang_verifikasi');
        $this->db->update('tbso_sales_order', [
            'status'    => $target_status,
            'update_by' => $update_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status()
            ? ['success' => true, 'status' => $target_status]
            : ['success' => false, 'status' => $target_status];
    }

    /**
     * Ambil log konfirmasi sales untuk satu DO
     */
    public function get_log_confirm_sales($kd_do)
    {
        return $this->db->query("
            SELECT * FROM tb_log_confirm_sales
            WHERE kd_do = ?
            ORDER BY confirm_at DESC
        ", [$kd_do])->result();
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

    public function insertics_det_do($data)
    {
        return $this->db->insert_batch('tb_ics_do', $data);
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
    function generate_kd_do_onsite()
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
        $kdnk1 = 'KIUDOOTS' . date('dmy') . $kd1;
        return $kdnk1;
    }

    public function edited_rute_do($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_do', $data);
    }

    public function get_rute_do_options()
    {
        $this->db->select('kd_rute, keterangan, jenis_rute');
        $this->db->from('tb_rutecs');
        $this->db->where_in('jenis_rute', ['LK', 'KK']);
        $this->db->order_by('jenis_rute', 'ASC');
        $this->db->order_by('kd_rute', 'ASC');
        return $this->db->get()->result();
    }

    public function get_rute_do($kd_rute)
    {
        return $this->db
            ->select('kd_rute, keterangan, jenis_rute')
            ->where('kd_rute', $kd_rute)
            ->where_in('jenis_rute', ['LK', 'KK'])
            ->limit(1)
            ->get('tb_rutecs')
            ->row();
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
        $this->db->join('tbpo_barang m', 'd.kd_barang = m.kode_barang');
        $this->db->where('d.kd_do', $kd_do);
        $this->db->group_by('d.kd_do');

        return $this->db->get()->result();
    }

    public function detail_fk($kd)
    {
        $result = $this->db->query("
            SELECT
                fd.id                               AS id,
                f.no_faktur                         AS kd_faktur,
                fd.kd_barang,
                COALESCE(fd.nama_barang, mb.nama_barang) AS nama_barang,
                fd.qty,
                mb.berat                            AS gr_berat,
                (COALESCE(mb.berat, fd.berat_gram) / 1000) AS convert_kg,
                (fd.qty * (COALESCE(mb.berat, fd.berat_gram) / 1000)) AS total_berat,
                fd.satuan,
                fd.no_lot,
                fd.expired_date                     AS tgl_exp,
                1                                   AS barang_sts
            FROM tbso_faktur_penjualan f
            JOIN tbso_faktur_detail fd ON fd.id_faktur = f.id_faktur
            LEFT JOIN tbpo_barang mb ON mb.kode_barang = fd.kd_barang
            WHERE f.no_faktur = ?
        ", [$kd])->result();

        // Fallback ke tb_pre_do jika kosong
        if (empty($result)) {
            $result = $this->db->query("
                SELECT
                    a.id, a.kd_faktur, a.kd_barang,
                    c.nama_barang,
                    a.qty,
                    c.berat     AS gr_berat,
                    (c.berat/1000)          AS convert_kg,
                    (a.qty * (c.berat/1000)) AS total_berat,
                    a.satuan, a.no_lot, a.tgl_exp, a.barang_sts
                FROM tb_pre_do a
                LEFT JOIN tbpo_barang c ON c.kode_barang = a.kd_barang
                WHERE a.kd_faktur = ?
                GROUP BY a.id
            ", [$kd])->result();
        }

        return $result;
    }

    public function det_customer($kd)
    {
        $result = $this->db->query("
            SELECT
                c.nama_customer,
                c.nama_kios,
                c.regional,
                f.status        AS upload_sts,
                f.status        AS data_sts,
                1               AS barang_sts
            FROM tbso_faktur_penjualan f
            JOIN tb_customer c ON c.kd_customer = f.kd_customer
            WHERE f.no_faktur = ?
            LIMIT 1
        ", [$kd])->result();

        // Fallback ke tb_pre_do
        if (empty($result)) {
            $result = $this->db->query("
                SELECT
                    b.nama_customer, b.nama_kios, b.regional,
                    a.upload_sts, a.data_sts, a.barang_sts
                FROM tb_pre_do a
                JOIN tb_customer b ON b.kd_customer = a.kd_customer
                WHERE a.kd_faktur = ?
                LIMIT 1
            ", [$kd])->result();
        }

        return $result;
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

    public function get_driver_name_by_kd($kd_driver)
    {
        return $this->db
            ->select('nama_driver')
            ->from('tb_op_driver')
            ->where('kd_driver', $kd_driver)
            ->limit(1)
            ->get()
            ->row();
    }

    public function get_truck_plate_by_id($truck_id)
    {
        return $this->db
            ->select('noplat')
            ->from('tb_op_plat')
            ->where('id', $truck_id)
            ->limit(1)
            ->get()
            ->row();
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
    JOIN tbpo_barang b ON b.nama_barang = a.nm_barang
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
            b.dimensi
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
            (SUM(a.qty) + COALESCE(pending.qty_pending, 0)) AS qty_zahirwith_pnd,
            COALESCE(pending.qty_pending, 0) AS qty_pending,
            COALESCE(supp.qty_supp, 0) AS qty_supp,
            COALESCE(opname.qty_fisik, 0) AS qty_fisik,
            IF((SUM(a.qty) + COALESCE(pending.qty_pending, 0)) - COALESCE(supp.qty_supp, 0) = COALESCE(opname.qty_fisik, 0), 1, 0) AS status
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
        LEFT JOIN (
            SELECT nama_barang, SUM(qty) AS qty_supp
            FROM tb_ics_supp
            GROUP BY nama_barang
        ) supp ON supp.nama_barang = a.nama_barang
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

    public function is_exist($kd_faktur)
    {
        $this->db->where('no_faktur', $kd_faktur);
        return $this->db->get('tbso_faktur_penjualan')->num_rows() > 0;
    }

    public function insert_data($data)
    {
        return $this->db->insert('tb_pre_do', $data);
    }

    public function insert_pnd_pre_batch($data)
    {
        return $this->db->insert_batch('tb_pre_do', $data);
    }

    public function insert_pnd_batch($data)
    {
        return $this->db->insert_batch('tb_pnd_do', $data);
    }

    public function get_detail_pnd_do($kd)
    {
        return $this->db->query("SELECT
            a.*
            FROM tb_pnd_do a
            WHERE a.kd_faktur = '$kd'
            GROUP BY a.kd_faktur
        ")->result();
    }

    public function get_do_cust_byfaktur_pnd($kd)
    {
        return $this->db->query("SELECT
            a.*,b.nama_barang
            FROM tb_pnd_do a
            JOIN tbpo_barang b ON b.kode_barang = a.kd_barang
            WHERE a.kd_faktur = '$kd'
        ")->result();
    }

    public function update_sts_pnd_detail($kd, $data)
    {
        $this->db->where('kd_faktur', $kd);
        return $this->db->update('tb_pnd_do', $data);
    }

    public function get_faktur_master($kd)
    {
        return $this->db->query("SELECT
            a.id,
            a.kd_faktur,
            a.kd_barang,
            c.nama_barang,
            a.qty,
            c.berat as gr_berat,
            (c.berat/1000) as convert_kg,
            (a.qty * (c.berat/1000)) AS total_berat,
            a.satuan,
            a.no_lot,
            a.tgl_exp,
            a.barang_sts
            FROM tb_pre_do a
            LEFT JOIN tb_customer b ON b.kd_customer = a.kd_customer
            LEFT JOIN tbpo_barang c ON c.kode_barang = a.kd_barang
            WHERE a.kd_faktur = '$kd'
        ")->result();
    }

    public function detail_pending_fk($kd)
    {
        return $this->db->query("SELECT
            a.id,
            a.kd_faktur,
            a.kd_barang,
            c.nama_barang,
            a.qty,
            c.berat as gr_berat,
            (c.berat/1000) as convert_kg,
            (a.qty * (c.berat/1000)) AS total_berat,
            a.satuan,
            a.no_lot,
            a.tgl_exp,
            a.barang_sts
            FROM tb_pnd_do a
            LEFT JOIN tbpo_barang c ON c.kode_barang = a.kd_barang
            WHERE a.kd_faktur = '$kd'
        ")->result();
    }

    public function get_faktur_bintang()
    {
        return $this->db->query("SELECT a.*
        FROM tb_pre_do a 
        WHERE a.kd_customer = 'BINT31' AND data_sts = '1'
        GROUP BY a.kd_faktur")->result();
    }

    public function get_cust_bintang($id)
    {
        return $this->db
            ->select('
            tb_pre_do.*,
            tb_customer.nama_customer,
            tb_customer.kd_customer
        ')
            ->from('tb_pre_do')
            ->join(
                'tb_customer',
                'tb_customer.kd_customer = tb_pre_do.kd_customer',
                'left'
            )
            ->where('tb_pre_do.id', $id)
            ->get()
            ->row();
    }

    public function get_customer_bintang($search = null)
    {
        if ($search) {
            $this->db->like('nama_customer', $search);
        }

        return $this->db
            ->limit(20)
            ->get('tb_customer')
            ->result();
    }

    public function update_customer_by_faktur($kd_faktur, $kd_customer)
    {
        return $this->db
            ->where('kd_faktur', $kd_faktur)
            ->update('tb_pre_do', [
                'kd_customer' => $kd_customer
            ]);
    }

    public function get_customer_by_kd($kd_customer)
    {
        return $this->db
            ->where('kd_customer', $kd_customer)
            ->get('tb_customer')
            ->row();
    }
    // model/M_logistik

    public function get_data_po($date1 = null, $date2 = null)
    {
        $sql = "SELECT
            pp.no_po,
            pp.kd_po,
            MAX(pp.tgl_transaksi) AS tgl_transaksi,
            pp.kd_suplier,
            MAX(s.nama_suplier) AS nama_suplier,
            COUNT(pp.kd_barang) AS jumlah_barang,
            SUM(CASE WHEN COALESCE(sub.qty_masuk,0) > 0 THEN 1 ELSE 0 END) AS jumlah_barang_masuk,
            MAX(sub.last_input) AS last_input,
            SUM(pp.qty) AS total_qty_order,
            SUM(COALESCE(sub.qty_masuk,0)) AS total_qty_masuk,
            CASE 
                WHEN SUM(COALESCE(sub.qty_masuk,0)) = 0 THEN 'OPEN'
                WHEN SUM(COALESCE(sub.qty_masuk,0)) < SUM(pp.qty) THEN 'PARTIAL'
                ELSE 'CLOSED'
            END AS status_po
        FROM tb_pre_po pp
        LEFT JOIN tb_suplier s 
            ON s.kd_suplier = pp.kd_suplier
        LEFT JOIN (
            SELECT 
                no_po, 
                kd_po,
                kd_barang,
                SUM(qty_diterima) AS qty_masuk,
                MAX(create_at) AS last_input
            FROM tb_po_received
            GROUP BY no_po, kd_po, kd_barang
        ) sub 
            ON sub.no_po = pp.no_po 
            AND sub.kd_po = pp.kd_po 
            AND sub.kd_barang = pp.kd_barang
        WHERE 1=1";

        $params = [];

        if (!empty($date1) && !empty($date2)) {
            $date1_formatted = date('Y-m-d', strtotime($date1));
            $date2_formatted = date('Y-m-d', strtotime($date2));

            $sql .= " AND STR_TO_DATE(pp.tgl_transaksi, '%d/%m/%Y') BETWEEN ? AND ?";
            $params[] = $date1_formatted;
            $params[] = $date2_formatted;
        }

        $sql .= " GROUP BY pp.no_po, pp.kd_po, pp.kd_suplier";
        $sql .= " HAVING total_qty_order > total_qty_masuk";
        $sql .= " ORDER BY MAX(pp.tgl_transaksi) DESC, pp.no_po DESC";

        return $this->db->query($sql, $params)->result_array();
    }

    public function get_lpb($date1 = null, $date2 = null)
    {
        $sql = "SELECT
            po.kd_po,
            DATE_FORMAT(po.tgl_transaksi, '%Y-%m-%d') AS tgl_transaksi,
            po.no_po,
            po.kdsupp,
            CASE
                WHEN po.nm_suplier IS NULL OR po.nm_suplier = '' THEN po.kdsupp
                ELSE po.nm_suplier
            END AS nm_suplier,
            po.total_barang_order,
            COALESCE(rcv.total_barang_diterima, 0) AS total_barang_diterima,
            po.total_qty_order,
            COALESCE(rcv.total_qty_diterima, 0) AS total_qty_diterima,
            CASE
                WHEN rcv.input_terakhir IS NULL OR rcv.input_terakhir = '' THEN '-'
                ELSE rcv.input_terakhir
            END AS input_terakhir,
            CASE
                WHEN po.total_qty_order <= 0 THEN 0
                WHEN COALESCE(rcv.total_qty_diterima, 0) >= po.total_qty_order THEN 100
                ELSE ROUND((COALESCE(rcv.total_qty_diterima, 0) / po.total_qty_order) * 100, 2)
            END AS progress_persen,
            CASE
                WHEN COALESCE(rcv.total_qty_diterima, 0) <= 0 THEN 'belum'
                WHEN COALESCE(rcv.total_qty_diterima, 0) < po.total_qty_order THEN 'partial'
                ELSE 'done'
            END AS status
        FROM (
            SELECT
                p.kd_po,
                MAX(p.tgl_transaksi) AS tgl_transaksi,
                MAX(p.no_po) AS no_po,
                MAX(p.kd_suplier) AS kdsupp,
                MAX(supp.nama_suplier) AS nm_suplier,
                CASE
                    WHEN COALESCE(MAX(p.jml_item), 0) > 0 THEN MAX(p.jml_item)
                    ELSE COUNT(DISTINCT d.kd_barang)
                END AS total_barang_order,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(d.qty_kecil, 0) > 0 THEN d.qty_kecil
                        ELSE d.qty
                    END
                ), 0) AS total_qty_order
            FROM tbpo_po p
            LEFT JOIN tbpo_suplier supp
                ON supp.kd_suplier = p.kd_suplier
            LEFT JOIN tbpo_detail_po d
                ON d.kd_po = p.kd_po
            WHERE 1=1";

        $params = [];

        if (!empty($date1) && !empty($date2)) {
            $date1_formatted = date('Y-m-d', strtotime($date1));
            $date2_formatted = date('Y-m-d', strtotime($date2));

            $sql .= " AND DATE(p.tgl_transaksi) BETWEEN ? AND ?";
            $params[] = $date1_formatted;
            $params[] = $date2_formatted;
        }

        $sql .= "
            GROUP BY p.kd_po
        ) po
        LEFT JOIN (
            SELECT
                h.kd_po,
                COUNT(DISTINCT d.kd_barang) AS total_barang_diterima,
                SUM(d.qty_diterima) AS total_qty_diterima,
                MAX(COALESCE(d.input_at, h.input_at)) AS input_terakhir
            FROM tb_lpb h
            INNER JOIN tb_lpb_detail d
                ON d.id_lpb = h.id_lpb
            GROUP BY h.kd_po
        ) rcv
            ON rcv.kd_po = po.kd_po
        ORDER BY po.tgl_transaksi DESC, po.no_po DESC";

        return $this->db->query($sql, $params)->result_array();
    }

    public function get_lpb_admin_po($date1 = null, $date2 = null)
    {
        $sql = "SELECT
            po.kd_po,
            DATE_FORMAT(po.tgl_transaksi, '%Y-%m-%d') AS tgl_transaksi,
            po.no_po,
            po.kdsupp,
            CASE
                WHEN po.nm_suplier IS NULL OR po.nm_suplier = '' THEN po.kdsupp
                ELSE po.nm_suplier
            END AS nm_suplier,
            po.total_barang_order,
            po.total_qty_order,
            COALESCE(rcv.total_qty_diterima, 0) AS total_qty_diterima,
            CASE
                WHEN po.total_qty_order <= 0 THEN 0
                WHEN COALESCE(rcv.total_qty_diterima, 0) >= po.total_qty_order THEN 100
                ELSE ROUND((COALESCE(rcv.total_qty_diterima, 0) / po.total_qty_order) * 100, 2)
            END AS progress_persen,
            CASE
                WHEN COALESCE(rcv.total_qty_diterima, 0) <= 0 THEN 'belum'
                WHEN COALESCE(rcv.total_qty_diterima, 0) < po.total_qty_order THEN 'partial'
                ELSE 'done'
            END AS status
        FROM (
            SELECT
                p.kd_po,
                MAX(p.tgl_transaksi) AS tgl_transaksi,
                MAX(p.no_po) AS no_po,
                MAX(p.kd_suplier) AS kdsupp,
                MAX(supp.nama_suplier) AS nm_suplier,
                CASE
                    WHEN COALESCE(MAX(p.jml_item), 0) > 0 THEN MAX(p.jml_item)
                    ELSE COUNT(DISTINCT d.kd_barang)
                END AS total_barang_order,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(d.qty_kecil, 0) > 0 THEN d.qty_kecil
                        ELSE d.qty
                    END
                ), 0) AS total_qty_order
            FROM tbpo_po p
            LEFT JOIN tbpo_suplier supp
                ON supp.kd_suplier = p.kd_suplier
            LEFT JOIN tbpo_detail_po d
                ON d.kd_po = p.kd_po
            WHERE 1=1";

        $params = [];

        if (!empty($date1) && !empty($date2)) {
            $date1_formatted = date('Y-m-d', strtotime($date1));
            $date2_formatted = date('Y-m-d', strtotime($date2));

            $sql .= " AND DATE(p.tgl_transaksi) BETWEEN ? AND ?";
            $params[] = $date1_formatted;
            $params[] = $date2_formatted;
        }

        $sql .= "
            GROUP BY p.kd_po
        ) po
        LEFT JOIN (
            SELECT
                h.kd_po,
                SUM(d.qty_diterima) AS total_qty_diterima
            FROM tb_lpb h
            INNER JOIN tb_lpb_detail d
                ON d.id_lpb = h.id_lpb
            GROUP BY h.kd_po
        ) rcv
            ON rcv.kd_po = po.kd_po
        ORDER BY
            CASE
                WHEN COALESCE(rcv.total_qty_diterima, 0) > 0
                    AND COALESCE(rcv.total_qty_diterima, 0) < po.total_qty_order THEN 1
                WHEN COALESCE(rcv.total_qty_diterima, 0) <= 0 THEN 2
                ELSE 3
            END ASC,
            po.tgl_transaksi DESC,
            po.no_po DESC";

        return $this->db->query($sql, $params)->result_array();
    }

    public function get_lpb_purchasing_view($date1 = null, $date2 = null)
    {
        $tanggalInvoiceSelect = $this->db->field_exists('tanggal_invoice', 'tb_lpb')
            ? "COALESCE(DATE_FORMAT(h.tanggal_invoice, '%Y-%m-%d'), CASE WHEN COALESCE(NULLIF(TRIM(h.no_invoice), ''), '-') = '-' THEN '-' ELSE COALESCE(DATE_FORMAT(invlog.tanggal_invoice, '%Y-%m-%d'), DATE_FORMAT(h.input_at, '%Y-%m-%d'), '-') END) AS tanggal_invoice"
            : "CASE
                    WHEN COALESCE(NULLIF(TRIM(h.no_invoice), ''), '-') = '-' THEN '-'
                    ELSE COALESCE(DATE_FORMAT(invlog.tanggal_invoice, '%Y-%m-%d'), DATE_FORMAT(h.input_at, '%Y-%m-%d'), '-')
                END AS tanggal_invoice";
        $kodeFakturSelect = $this->db->field_exists('kode_faktur_pajak', 'tb_lpb')
            ? "COALESCE(NULLIF(TRIM(h.kode_faktur_pajak), ''), '-') AS kode_faktur_pajak"
            : "'-' AS kode_faktur_pajak";
        $tanggalFakturSelect = $this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')
            ? "COALESCE(DATE_FORMAT(h.tanggal_faktur_pajak, '%Y-%m-%d'), '-') AS tgl_faktur"
            : "'-' AS tgl_faktur";
        $tanggalInvoiceGroup = $this->db->field_exists('tanggal_invoice', 'tb_lpb')
            ? ",
                h.tanggal_invoice"
            : "";
        $kodeFakturGroup = $this->db->field_exists('kode_faktur_pajak', 'tb_lpb')
            ? ",
                h.kode_faktur_pajak"
            : "";
        $tanggalFakturGroup = $this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')
            ? ",
                h.tanggal_faktur_pajak"
            : "";
        $jenisLpbSelect = $this->db->field_exists('jenis_lpb', 'tb_lpb')
            ? "NULLIF(TRIM(h.jenis_lpb), '') AS jenis_lpb"
            : "'' AS jenis_lpb";
        $nomorLpbSelect = $this->db->field_exists('nomor_lpb', 'tb_lpb')
            ? "COALESCE(NULLIF(h.nomor_lpb, ''), CONCAT('LPB-', h.id_lpb)) AS nomor_lpb"
            : "CONCAT('LPB-', h.id_lpb) AS nomor_lpb";
        $statusLpbSelect = $this->db->field_exists('status_lpb', 'tb_lpb')
            ? "h.status_lpb"
            : "NULL AS status_lpb";
        $jenisLpbGroup = $this->db->field_exists('jenis_lpb', 'tb_lpb')
            ? ",
                h.jenis_lpb"
            : "";
        $nomorLpbGroup = $this->db->field_exists('nomor_lpb', 'tb_lpb')
            ? ",
                h.nomor_lpb"
            : "";
        $statusLpbGroup = $this->db->field_exists('status_lpb', 'tb_lpb')
            ? ",
                h.status_lpb"
            : "";
        $checkerSelect = $this->db->field_exists('checker_name', 'tb_lpb')
            ? "COALESCE(NULLIF(TRIM(h.checker_name), ''), '-') AS checker_name"
            : "'-' AS checker_name";
        $checkerBySelect = $this->db->field_exists('checker_by', 'tb_lpb')
            ? "COALESCE(NULLIF(TRIM(h.checker_by), ''), '') AS checker_by"
            : "'' AS checker_by";
        $checkerAtSelect = $this->db->field_exists('checker_at', 'tb_lpb')
            ? "h.checker_at"
            : "NULL AS checker_at";
        $checkerGroup = "";
        if ($this->db->field_exists('checker_name', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_name";
        }
        if ($this->db->field_exists('checker_by', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_by";
        }
        if ($this->db->field_exists('checker_at', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_at";
        }

        $sql = "SELECT
                h.id_lpb,
                h.kd_po,
                h.no_po,
                COALESCE(DATE_FORMAT(h.input_at, '%Y-%m-%d'), '-') AS tgl_lpb,
                COALESCE(DATE_FORMAT(p.tgl_transaksi, '%Y-%m-%d'), DATE_FORMAT(h.input_at, '%Y-%m-%d'), '-') AS tgl_po,
                {$nomorLpbSelect},
                {$jenisLpbSelect},
                {$statusLpbSelect},
                h.nosj,
                h.tgl_sj,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), p.kd_suplier, '-') AS nama_suplier,
                COALESCE(NULLIF(TRIM(p.kd_suplier), ''), '') AS kd_suplier,
                CASE
                    WHEN COALESCE(ds.total_detail, 0) <= 0 THEN 0
                    ELSE ROUND((COALESCE(ds.total_verified, 0) / ds.total_detail) * 100, 2)
                END AS progress_persen,
                COALESCE(ds.total_detail, 0) AS total_detail,
                COALESCE(ds.total_verified, 0) AS total_verified,
                COALESCE(ds.grand_total_lpb, 0) AS grand_total_lpb,
                CASE
                    WHEN COALESCE(ds.total_detail, 0) <= 0 THEN 'belum'
                    WHEN COALESCE(ds.total_verified, 0) <= 0 THEN 'belum'
                    WHEN COALESCE(ds.total_verified, 0) < ds.total_detail THEN 'partial'
                    ELSE 'done'
                END AS progress_status,
                h.no_invoice,
                {$tanggalInvoiceSelect},
                {$kodeFakturSelect},
                {$tanggalFakturSelect},
                {$checkerSelect},
                {$checkerBySelect},
                {$checkerAtSelect},
                h.input_at
            FROM tb_lpb h
            LEFT JOIN tbpo_po p
                ON p.kd_po = h.kd_po
            LEFT JOIN tbpo_suplier s
                ON s.kd_suplier = p.kd_suplier
            LEFT JOIN (
                SELECT
                    id_lpb,
                    COUNT(id_detail_lpb) AS total_detail,
                    SUM(CASE WHEN harga_verified_at IS NOT NULL THEN 1 ELSE 0 END) AS total_verified,
                    SUM(COALESCE(total_harga, 0)) AS grand_total_lpb
                FROM tb_lpb_detail
                GROUP BY id_lpb
            ) ds
                ON ds.id_lpb = h.id_lpb
            LEFT JOIN (
                SELECT
                    kd_po,
                    no_invoice,
                    MAX(dilakukan_pada) AS tanggal_invoice
                FROM tb_lpb_log
                WHERE COALESCE(NULLIF(TRIM(no_invoice), ''), '-') <> '-'
                GROUP BY kd_po, no_invoice
            ) invlog
                ON invlog.kd_po = h.kd_po
                AND invlog.no_invoice = h.no_invoice
            WHERE 1=1";

        $params = [];

        if (!empty($date1) && !empty($date2)) {
            $date1_formatted = date('Y-m-d', strtotime($date1));
            $date2_formatted = date('Y-m-d', strtotime($date2));

            $sql .= " AND DATE(h.input_at) BETWEEN ? AND ?";
            $params[] = $date1_formatted;
            $params[] = $date2_formatted;
        }

        $sql .= "
            GROUP BY
                h.id_lpb,
                h.kd_po,
                h.no_po,
                p.tgl_transaksi,
                h.nosj,
                h.tgl_sj,
                s.nama_suplier,
                p.kd_suplier,
                ds.total_detail,
                ds.total_verified,
                ds.grand_total_lpb,
                h.no_invoice,
                invlog.tanggal_invoice,
                h.input_at
                {$tanggalInvoiceGroup}
                {$kodeFakturGroup}
                {$tanggalFakturGroup}
                {$jenisLpbGroup}
                {$nomorLpbGroup}
                {$statusLpbGroup}
                {$checkerGroup}
            ORDER BY h.input_at DESC, h.id_lpb DESC";

        return $this->append_lpb_operational_alerts($this->db->query($sql, $params)->result_array());
    }

    private function default_lpb_operational_alert()
    {
        return [
            'has_sales_transaction' => 0,
            'sales_invoice_count' => 0,
            'sales_qty_total' => 0,
            'sales_invoice_sample' => '',
            'latest_sales_at' => '',
            'has_active_lpb_journal' => 0,
            'lpb_journal_count' => 0,
            'lpb_active_journal_count' => 0,
            'lpb_journal_sample' => '',
            'lpb_operational_warning' => ''
        ];
    }

    private function append_lpb_operational_alerts(array $rows)
    {
        if (empty($rows)) {
            return [];
        }

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id_lpb'] ?? 0);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        if (empty($ids)) {
            return $rows;
        }

        $salesMap = $this->get_lpb_sales_usage_summary_map(array_values($ids));
        $journalMap = $this->get_lpb_goods_receipt_journal_summary_map(array_values($ids));

        foreach ($rows as &$row) {
            $id = (int) ($row['id_lpb'] ?? 0);
            $alert = $this->default_lpb_operational_alert();

            if (isset($salesMap[$id])) {
                $alert = array_merge($alert, $salesMap[$id]);
            }
            if (isset($journalMap[$id])) {
                $alert = array_merge($alert, $journalMap[$id]);
            }

            $warnings = [];
            if ((int) $alert['has_sales_transaction'] === 1) {
                $warnings[] = 'Sudah ada transaksi penjualan berdasarkan LPB ini.';
            }
            if ((int) $alert['has_active_lpb_journal'] === 1) {
                $warnings[] = 'Jurnal pembelian LPB sudah POSTED; koreksi harga perlu kontrol jurnal.';
            }
            $alert['lpb_operational_warning'] = implode(' ', $warnings);

            $row = array_merge($row, $alert);
        }
        unset($row);

        return $rows;
    }

    private function append_lpb_operational_alerts_to_row($row)
    {
        if (empty($row)) {
            return $row;
        }

        $rows = $this->append_lpb_operational_alerts([$row]);
        return $rows[0] ?? $row;
    }

    public function get_lpb_sales_usage_summary($idLpb)
    {
        $map = $this->get_lpb_sales_usage_summary_map([(int) $idLpb]);
        return $map[(int) $idLpb] ?? $this->default_lpb_operational_alert();
    }

    private function get_lpb_sales_usage_summary_map(array $idLpbs)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idLpbs), function ($id) {
            return $id > 0;
        })));

        if (empty($ids) || !$this->db->table_exists('tb_lpb_detail') || !$this->db->table_exists('tbso_faktur_detail')) {
            return [];
        }

        $idSql = implode(',', $ids);
        $hasBatch = $this->db->table_exists('tb_lpb_batch');
        $batchJoin = $hasBatch ? "LEFT JOIN tb_lpb_batch b ON b.id_detail_lpb = d.id_detail_lpb" : "";
        $lotExpr = $hasBatch
            ? "COALESCE(NULLIF(TRIM(b.no_lot), ''), NULLIF(TRIM(d.no_lot), ''), '')"
            : "COALESCE(NULLIF(TRIM(d.no_lot), ''), '')";
        $expiredExpr = $hasBatch
            ? "COALESCE(NULLIF(b.expired_date, '0000-00-00'), NULLIF(d.expired_date, '0000-00-00'), '0000-00-00')"
            : "COALESCE(NULLIF(d.expired_date, '0000-00-00'), '0000-00-00')";

        $sql = "SELECT
                    d.id_lpb,
                    COUNT(DISTINCT fd.no_faktur) AS sales_invoice_count,
                    COALESCE(SUM(fd.qty), 0) AS sales_qty_total,
                    SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT fd.no_faktur ORDER BY fd.create_at DESC SEPARATOR ', '), ', ', 5) AS sales_invoice_sample,
                    MAX(fd.create_at) AS latest_sales_at
                FROM tb_lpb_detail d
                {$batchJoin}
                INNER JOIN tbso_faktur_detail fd
                    ON fd.kd_barang = d.kd_barang
                    AND COALESCE(NULLIF(TRIM(fd.no_lot), ''), '') = {$lotExpr}
                    AND COALESCE(NULLIF(fd.expired_date, '0000-00-00'), '0000-00-00') = {$expiredExpr}
                WHERE d.id_lpb IN ({$idSql})
                GROUP BY d.id_lpb";

        $map = [];
        foreach ($this->db->query($sql)->result_array() as $row) {
            $id = (int) ($row['id_lpb'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $map[$id] = [
                'has_sales_transaction' => ((int) ($row['sales_invoice_count'] ?? 0) > 0) ? 1 : 0,
                'sales_invoice_count' => (int) ($row['sales_invoice_count'] ?? 0),
                'sales_qty_total' => (float) ($row['sales_qty_total'] ?? 0),
                'sales_invoice_sample' => (string) ($row['sales_invoice_sample'] ?? ''),
                'latest_sales_at' => (string) ($row['latest_sales_at'] ?? '')
            ];
        }

        return $map;
    }

    public function get_lpb_goods_receipt_journal_summary($idLpb)
    {
        $map = $this->get_lpb_goods_receipt_journal_summary_map([(int) $idLpb]);
        return $map[(int) $idLpb] ?? $this->default_lpb_operational_alert();
    }

    private function get_lpb_goods_receipt_journal_summary_map(array $idLpbs)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idLpbs), function ($id) {
            return $id > 0;
        })));

        if (empty($ids) || !$this->db->table_exists('tbkeu_jurnal')) {
            return [];
        }

        $idSql = implode(',', $ids);
        $activeExpr = $this->db->field_exists('reversed_at', 'tbkeu_jurnal')
            ? "CASE WHEN j.status = 'POSTED' AND j.reversed_at IS NULL THEN 1 ELSE 0 END"
            : "CASE WHEN j.status = 'POSTED' THEN 1 ELSE 0 END";

        $sql = "SELECT
                    CAST(j.source_id AS UNSIGNED) AS id_lpb,
                    COUNT(j.id_jurnal) AS lpb_journal_count,
                    SUM({$activeExpr}) AS lpb_active_journal_count,
                    SUBSTRING_INDEX(GROUP_CONCAT(j.nomor_jurnal ORDER BY j.id_jurnal DESC SEPARATOR ', '), ', ', 5) AS lpb_journal_sample
                FROM tbkeu_jurnal j
                WHERE j.source_module = 'LOGISTIK'
                    AND j.source_type = 'LPB_FINAL'
                    AND j.posting_event = 'GOODS_RECEIPT'
                    AND CAST(j.source_id AS UNSIGNED) IN ({$idSql})
                GROUP BY CAST(j.source_id AS UNSIGNED)";

        $map = [];
        foreach ($this->db->query($sql)->result_array() as $row) {
            $id = (int) ($row['id_lpb'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $activeCount = (int) ($row['lpb_active_journal_count'] ?? 0);
            $map[$id] = [
                'has_active_lpb_journal' => $activeCount > 0 ? 1 : 0,
                'lpb_journal_count' => (int) ($row['lpb_journal_count'] ?? 0),
                'lpb_active_journal_count' => $activeCount,
                'lpb_journal_sample' => (string) ($row['lpb_journal_sample'] ?? '')
            ];
        }

        return $map;
    }

    private function lpb_price_change_blocker($idLpb)
    {
        $sales = $this->get_lpb_sales_usage_summary($idLpb);
        if ((int) ($sales['has_sales_transaction'] ?? 0) === 1) {
            return [
                'blocked' => true,
                'code' => 'LPB_ALREADY_SOLD',
                'message' => 'Harga LPB tidak dapat diubah langsung karena sudah ada transaksi penjualan berdasarkan barang/lot/expired LPB ini. Gunakan workflow koreksi harga/jurnal agar HPP dan persediaan tetap terkendali.',
                'data' => $sales
            ];
        }

        $journal = $this->get_lpb_goods_receipt_journal_summary($idLpb);
        if ((int) ($journal['has_active_lpb_journal'] ?? 0) === 1) {
            return [
                'blocked' => true,
                'code' => 'LPB_ACTIVE_JOURNAL_EXISTS',
                'message' => 'Harga LPB tidak dapat diubah langsung karena jurnal pembelian LPB sudah POSTED. Lakukan reversal/koreksi jurnal terlebih dahulu sebelum reinput harga.',
                'data' => $journal
            ];
        }

        return ['blocked' => false, 'code' => '', 'message' => '', 'data' => []];
    }

    public function get_barang_by_po()
    {
        while (ob_get_level()) ob_end_clean();

        $no_po      = $this->input->get('no_po', TRUE);
        $kd_suplier = $this->input->get('kd_suplier', TRUE);
        $kd_po      = $this->input->get('kd_po', TRUE);

        if (empty($no_po) || empty($kd_po)) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
            exit;
        }

        // Query untuk mendapatkan detail barang berdasarkan kd_po yang spesifik
        $sql = "
            SELECT
                pp.kd_po,
                pp.kd_barang,
                COALESCE(mb.nama_barang, '-') AS nama_barang,
                pp.qty AS qty_order,
                pp.satuan,
                COALESCE(sub.qty_masuk, 0) AS qty_masuk,
                (pp.qty - COALESCE(sub.qty_masuk, 0)) AS sisa
            FROM tb_pre_po pp
            LEFT JOIN tbpo_barang mb ON mb.kode_barang = pp.kd_barang
            LEFT JOIN (
                SELECT kd_po, kd_barang, SUM(qty_diterima) AS qty_masuk
                FROM tb_po_received
                WHERE no_po = ? AND kd_po = ?
                GROUP BY kd_po, kd_barang
            ) sub ON sub.kd_po = pp.kd_po AND sub.kd_barang = pp.kd_barang
            WHERE pp.no_po = ? 
                AND pp.kd_po = ?
                AND pp.kd_suplier = ?
            HAVING sisa > 0
            ORDER BY pp.kd_barang
        ";

        $params = [$no_po, $kd_po, $no_po, $kd_po, $kd_suplier];
        $result = $this->db->query($sql, $params)->result_array();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    }

    public function save_detail_lpb($data)
    {
        $insert = [
            'no_po'        => $data['no_po'],
            'kd_barang'    => $data['kd_barang'],
            'qty_diterima' => (int) $data['qty_diterima'],
            'no_lot'       => $data['no_lot']   ?? null,
            'exp_date'     => !empty($data['exp_date']) ? $data['exp_date'] : null,
            'create_at'    => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('tb_po_received', $insert);
    }

    public function get_detail_by_po($no_po)
    {
        $this->db->select('
            dl.*, 
            mb.nama_barang
        ');
        $this->db->from('tb_po_received dl');
        $this->db->join(
            'tbpo_barang mb',
            'mb.kd_barang = dl.kd_barang',
            'left'
        );
        $this->db->where('dl.no_po', $no_po);
        $this->db->order_by('dl.id_detail_lpb', 'ASC');

        return $this->db->get()->result_array();
    }

    private function po_barang_conversion_join($alias = 'pb')
    {
        return "(SELECT kode_barang, MAX(NULLIF(isi, 0)) AS isi, MAX(NULLIF(kemasan, 0)) AS kemasan
                FROM tbpo_barang
                GROUP BY kode_barang) {$alias}";
    }

    private function po_conversion_factor_expr($detailAlias = 'pp', $barangAlias = 'pb')
    {
        $unitKeyExpr = $this->po_unit_key_expr($detailAlias);
        $storedFactorExpr = "(CASE
                    WHEN COALESCE({$detailAlias}.qty_kecil, 0) > 0 AND COALESCE({$detailAlias}.qty, 0) > 0
                        THEN {$detailAlias}.qty_kecil / {$detailAlias}.qty
                    ELSE 1
                END)";
        $isiExpr = "COALESCE(NULLIF({$barangAlias}.isi, 0), NULLIF({$detailAlias}.isi, 0), 0)";
        $kemasanExpr = "COALESCE(NULLIF({$barangAlias}.kemasan, 0), NULLIF({$detailAlias}.kemasan, 0), 0)";

        return "(CASE
                    WHEN {$unitKeyExpr} = 'box'
                        THEN CASE WHEN {$isiExpr} > 0 THEN {$isiExpr} ELSE {$storedFactorExpr} END
                    WHEN {$unitKeyExpr} IN ('ltr', 'kg')
                        THEN CASE WHEN {$kemasanExpr} > 0 THEN 1000 / {$kemasanExpr} ELSE {$storedFactorExpr} END
                    ELSE 1
                END)";
    }

    private function po_unit_key_expr($detailAlias = 'pp')
    {
        $satuanExpr = "LOWER(TRIM(COALESCE({$detailAlias}.satuan, '')))";

        return "(CASE
                    WHEN {$satuanExpr} = 'box' THEN 'box'
                    WHEN {$satuanExpr} IN ('kg', 'kgs', 'kilogram') THEN 'kg'
                    WHEN {$satuanExpr} IN ('ltr', 'lt', 'liter', 'litre', 'l') THEN 'ltr'
                    WHEN {$satuanExpr} IN ('pcs', 'pc', 'piece') THEN 'pcs'
                    ELSE 'pcs'
                END)";
    }

    private function po_unit_qty_expr($qtyKecilExpr, $factorExpr, $detailAlias, $unit)
    {
        $unit = strtolower($unit);
        $unitKeyExpr = $this->po_unit_key_expr($detailAlias);

        return "(CASE
                    WHEN {$unitKeyExpr} = '{$unit}'
                        THEN CASE WHEN {$factorExpr} > 0 THEN {$qtyKecilExpr} / {$factorExpr} ELSE {$qtyKecilExpr} END
                    ELSE 0
                END)";
    }

    public function detail_po_received($nopo, $kdsup)
    {
        $dimensiExpr = $this->po_conversion_factor_expr('a', 'pb');
        $isiExpr = "COALESCE(NULLIF(pb.isi, 0), NULLIF(a.isi, 0), 0)";
        $kemasanExpr = "COALESCE(NULLIF(pb.kemasan, 0), NULLIF(a.kemasan, 0), 0)";
        $qtyOrderKecilExpr = "(CASE
                    WHEN COALESCE(a.qty_kecil, 0) > 0 THEN COALESCE(a.qty_kecil, 0)
                    ELSE COALESCE(a.qty, 0) * {$dimensiExpr}
                END)";
        $qtyOrderBoxExpr = "(CASE WHEN {$isiExpr} > 0 THEN COALESCE(a.qty, 0) * {$isiExpr} ELSE 0 END)";
        $qtyOrderKemasanExpr = "(CASE WHEN {$kemasanExpr} > 0 THEN {$qtyOrderKecilExpr} / ({$kemasanExpr} / 1000) ELSE 0 END)";
        $qtyDiterimaKecilExpr = "COALESCE(r.qty_diterima, 0)";
        $qtyInKecilExpr = "(COALESCE(r.qty_diterima, 0) + COALESCE(tmp.qty_in, 0))";
        $qtyDiterimaBaseExpr = "(CASE WHEN {$dimensiExpr} > 0 THEN {$qtyDiterimaKecilExpr} / {$dimensiExpr} ELSE {$qtyDiterimaKecilExpr} END)";
        $qtyDiterimaBoxExpr = "(CASE WHEN {$isiExpr} > 0 THEN {$qtyDiterimaBaseExpr} * {$isiExpr} ELSE 0 END)";
        $qtyDiterimaKemasanExpr = "(CASE WHEN {$kemasanExpr} > 0 THEN {$qtyDiterimaKecilExpr} / ({$kemasanExpr} / 1000) ELSE 0 END)";

        $sql = "SELECT 
                a.id_det_po AS id,
                a.no_po,
                a.kd_po,
                a.kd_barang,
                COALESCE(NULLIF(a.nama_barang, ''), b.nama_barang, '-') AS nama_barang,
                {$dimensiExpr} AS dimensi_br,
                {$qtyOrderKecilExpr} AS qty_kecil,
                a.qty AS qty_besar,
                a.satuan,
                {$qtyOrderKecilExpr} AS qty_order_pcs,
                {$qtyOrderBoxExpr} AS qty_order_box,
                {$qtyOrderKemasanExpr} AS qty_order_kg,
                {$qtyOrderKemasanExpr} AS qty_order_ltr,
                a.hrg_satuan,
                a.hrg_total AS harga_total,
                CASE
                    WHEN {$dimensiExpr} > 0
                    THEN COALESCE(r.qty_diterima, 0) / {$dimensiExpr}
                    ELSE COALESCE(r.qty_diterima, 0)
                END AS qty_diterima,
                COALESCE(r.qty_diterima, 0) AS qty_kecil_diterima,
                {$qtyDiterimaKecilExpr} AS qty_diterima_pcs,
                {$qtyDiterimaBoxExpr} AS qty_diterima_box,
                {$qtyDiterimaKemasanExpr} AS qty_diterima_kg,
                {$qtyDiterimaKemasanExpr} AS qty_diterima_ltr,
                {$qtyInKecilExpr} AS qty_in,
                CASE
                    WHEN {$dimensiExpr} > 0
                    THEN GREATEST(({$qtyOrderKecilExpr} - COALESCE(r.qty_diterima, 0)) / {$dimensiExpr}, 0)
                    ELSE GREATEST({$qtyOrderKecilExpr} - COALESCE(r.qty_diterima, 0), 0)
                END AS qty_sisa,
                GREATEST({$qtyOrderKecilExpr} - COALESCE(r.qty_diterima, 0), 0) AS qty_kecil_sisa,
                COALESCE(r.total_lpb_record, 0) AS total_lpb_record,
                
                CASE 
                    WHEN COALESCE(r.qty_diterima, 0) <= 0 THEN 'BELUM'
                    WHEN COALESCE(r.qty_diterima, 0) < {$qtyOrderKecilExpr} THEN 'PARTIAL'
                    ELSE 'FULL'
                END AS status_barang

            FROM tbpo_detail_po a
            LEFT JOIN tbpo_barang b 
                ON b.kode_barang = a.kd_barang
            LEFT JOIN {$this->po_barang_conversion_join('pb')}
                ON pb.kode_barang = a.kd_barang
            LEFT JOIN (
                SELECT 
                    h.no_po,
                    h.kd_po,
                    d.kd_barang,
                    SUM(d.qty_diterima) AS qty_diterima,
                    COUNT(DISTINCT h.id_lpb) AS total_lpb_record
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb h ON h.id_lpb = d.id_lpb
                GROUP BY h.no_po, h.kd_po, d.kd_barang
            ) r 
                ON r.no_po = a.no_po
                AND r.kd_po = a.kd_po
                AND r.kd_barang = a.kd_barang
            LEFT JOIN (
                SELECT
                    kd_po,
                    kd_suplier,
                    kd_barang,
                    SUM(qty_diterima) AS qty_in
                FROM tb_tmp_po_received
                GROUP BY kd_po, kd_suplier, kd_barang
            ) tmp
                ON tmp.kd_po = a.kd_po
                AND tmp.kd_suplier = a.kd_suplier
                AND tmp.kd_barang = a.kd_barang
            WHERE a.no_po = ?
                AND a.kd_suplier = ?";

        return $this->db->query($sql, [$nopo, $kdsup])->result_array();
    }

    public function get_lpb_records_by_kd_po($kd_po)
    {
        $tanggalInvoiceSelect = $this->db->field_exists('tanggal_invoice', 'tb_lpb')
            ? "h.tanggal_invoice"
            : "NULL AS tanggal_invoice";
        $kodeFakturSelect = $this->db->field_exists('kode_faktur_pajak', 'tb_lpb')
            ? "h.kode_faktur_pajak"
            : "NULL AS kode_faktur_pajak";
        $tanggalFakturSelect = $this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')
            ? "h.tanggal_faktur_pajak"
            : "NULL AS tanggal_faktur_pajak";
        $tanggalInvoiceGroup = $this->db->field_exists('tanggal_invoice', 'tb_lpb')
            ? ",
                h.tanggal_invoice"
            : "";
        $kodeFakturGroup = $this->db->field_exists('kode_faktur_pajak', 'tb_lpb')
            ? ",
                h.kode_faktur_pajak"
            : "";
        $tanggalFakturGroup = $this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')
            ? ",
                h.tanggal_faktur_pajak"
            : "";
        $jenisLpbSelect = $this->db->field_exists('jenis_lpb', 'tb_lpb')
            ? "NULLIF(TRIM(h.jenis_lpb), '') AS jenis_lpb"
            : "'' AS jenis_lpb";
        $nomorLpbSelect = $this->db->field_exists('nomor_lpb', 'tb_lpb')
            ? "COALESCE(NULLIF(h.nomor_lpb, ''), '') AS nomor_lpb"
            : "'' AS nomor_lpb";
        $statusLpbSelect = $this->db->field_exists('status_lpb', 'tb_lpb')
            ? "COALESCE(h.status_lpb, 1) AS status_lpb"
            : "1 AS status_lpb";
        $jenisLpbGroup = $this->db->field_exists('jenis_lpb', 'tb_lpb')
            ? ",
                h.jenis_lpb"
            : "";
        $nomorLpbGroup = $this->db->field_exists('nomor_lpb', 'tb_lpb')
            ? ",
                h.nomor_lpb"
            : "";
        $statusLpbGroup = $this->db->field_exists('status_lpb', 'tb_lpb')
            ? ",
                h.status_lpb"
            : "";
        $checkerSelect = $this->db->field_exists('checker_name', 'tb_lpb')
            ? "COALESCE(NULLIF(TRIM(h.checker_name), ''), '-') AS checker_name"
            : "'-' AS checker_name";
        $checkerBySelect = $this->db->field_exists('checker_by', 'tb_lpb')
            ? "COALESCE(NULLIF(TRIM(h.checker_by), ''), '') AS checker_by"
            : "'' AS checker_by";
        $checkerAtSelect = $this->db->field_exists('checker_at', 'tb_lpb')
            ? "h.checker_at"
            : "NULL AS checker_at";
        $checkerGroup = "";
        if ($this->db->field_exists('checker_name', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_name";
        }
        if ($this->db->field_exists('checker_by', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_by";
        }
        if ($this->db->field_exists('checker_at', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_at";
        }

        $sql = "SELECT
                h.id_lpb,
                h.kd_po,
                h.nosj,
                h.tgl_sj,
                h.no_po,
                h.no_invoice,
                {$tanggalInvoiceSelect},
                {$kodeFakturSelect},
                {$tanggalFakturSelect},
                {$jenisLpbSelect},
                {$nomorLpbSelect},
                {$statusLpbSelect},
                {$checkerSelect},
                {$checkerBySelect},
                {$checkerAtSelect},
                h.gudang_id,
                COALESCE(g.nama_gudang, '-') AS nama_gudang,
                h.keterangan,
                h.input_at,
                COUNT(d.id_detail_lpb) AS total_baris,
                COUNT(DISTINCT d.kd_barang) AS total_item,
                COALESCE(SUM(d.qty_diterima), 0) AS total_qty
            FROM tb_lpb h
            LEFT JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
            LEFT JOIN tb_gudang g ON g.id_gudang = h.gudang_id
            WHERE h.kd_po = ?
            GROUP BY
                h.id_lpb,
                h.kd_po,
                h.nosj,
                h.tgl_sj,
                h.no_po,
                h.no_invoice,
                h.gudang_id,
                g.nama_gudang,
                h.keterangan,
                h.input_at
                {$tanggalInvoiceGroup}
                {$kodeFakturGroup}
                {$tanggalFakturGroup}
                {$jenisLpbGroup}
                {$nomorLpbGroup}
                {$statusLpbGroup}
                {$checkerGroup}
            ORDER BY h.input_at DESC, h.id_lpb DESC";

        return $this->append_lpb_operational_alerts($this->db->query($sql, [$kd_po])->result_array());
    }

    public function get_lpb_record_header($id_lpb)
    {
        $tanggalInvoiceSelect = $this->db->field_exists('tanggal_invoice', 'tb_lpb')
            ? "h.tanggal_invoice"
            : "NULL AS tanggal_invoice";
        $kodeFakturSelect = $this->db->field_exists('kode_faktur_pajak', 'tb_lpb')
            ? "h.kode_faktur_pajak"
            : "NULL AS kode_faktur_pajak";
        $tanggalFakturSelect = $this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')
            ? "h.tanggal_faktur_pajak"
            : "NULL AS tanggal_faktur_pajak";
        $tanggalInvoiceGroup = $this->db->field_exists('tanggal_invoice', 'tb_lpb')
            ? ",
                h.tanggal_invoice"
            : "";
        $kodeFakturGroup = $this->db->field_exists('kode_faktur_pajak', 'tb_lpb')
            ? ",
                h.kode_faktur_pajak"
            : "";
        $tanggalFakturGroup = $this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')
            ? ",
                h.tanggal_faktur_pajak"
            : "";
        $jenisLpbSelect = $this->db->field_exists('jenis_lpb', 'tb_lpb')
            ? "NULLIF(TRIM(h.jenis_lpb), '') AS jenis_lpb"
            : "'' AS jenis_lpb";
        $nomorLpbSelect = $this->db->field_exists('nomor_lpb', 'tb_lpb')
            ? "COALESCE(NULLIF(h.nomor_lpb, ''), '') AS nomor_lpb"
            : "'' AS nomor_lpb";
        $statusLpbSelect = $this->db->field_exists('status_lpb', 'tb_lpb')
            ? "COALESCE(h.status_lpb, 1) AS status_lpb"
            : "1 AS status_lpb";
        $jenisLpbGroup = $this->db->field_exists('jenis_lpb', 'tb_lpb')
            ? ",
                h.jenis_lpb"
            : "";
        $nomorLpbGroup = $this->db->field_exists('nomor_lpb', 'tb_lpb')
            ? ",
                h.nomor_lpb"
            : "";
        $statusLpbGroup = $this->db->field_exists('status_lpb', 'tb_lpb')
            ? ",
                h.status_lpb"
            : "";
        $checkerSelect = $this->db->field_exists('checker_name', 'tb_lpb')
            ? "COALESCE(NULLIF(TRIM(h.checker_name), ''), '-') AS checker_name"
            : "'-' AS checker_name";
        $checkerBySelect = $this->db->field_exists('checker_by', 'tb_lpb')
            ? "COALESCE(NULLIF(TRIM(h.checker_by), ''), '') AS checker_by"
            : "'' AS checker_by";
        $checkerAtSelect = $this->db->field_exists('checker_at', 'tb_lpb')
            ? "h.checker_at"
            : "NULL AS checker_at";
        $checkerGroup = "";
        if ($this->db->field_exists('checker_name', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_name";
        }
        if ($this->db->field_exists('checker_by', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_by";
        }
        if ($this->db->field_exists('checker_at', 'tb_lpb')) {
            $checkerGroup .= ",
                h.checker_at";
        }

        $sql = "SELECT
                h.id_lpb,
                h.kd_po,
                h.no_po,
                h.nosj,
                h.tgl_sj,
                h.no_invoice,
                {$tanggalInvoiceSelect},
                {$kodeFakturSelect},
                {$tanggalFakturSelect},
                {$jenisLpbSelect},
                {$nomorLpbSelect},
                {$statusLpbSelect},
                {$checkerSelect},
                {$checkerBySelect},
                {$checkerAtSelect},
                h.gudang_id,
                COALESCE(g.nama_gudang, '-') AS nama_gudang,
                h.keterangan,
                h.input_at,
                COUNT(d.id_detail_lpb) AS total_baris,
                COUNT(DISTINCT d.kd_barang) AS total_item,
                COALESCE(SUM(d.qty_diterima), 0) AS total_qty
            FROM tb_lpb h
            LEFT JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
            LEFT JOIN tb_gudang g ON g.id_gudang = h.gudang_id
            WHERE h.id_lpb = ?
            GROUP BY
                h.id_lpb,
                h.kd_po,
                h.no_po,
                h.nosj,
                h.tgl_sj,
                h.no_invoice,
                h.gudang_id,
                g.nama_gudang,
                h.keterangan,
                h.input_at
                {$tanggalInvoiceGroup}
                {$kodeFakturGroup}
                {$tanggalFakturGroup}
                {$jenisLpbGroup}
                {$nomorLpbGroup}
                {$statusLpbGroup}
                {$checkerGroup}
            LIMIT 1";

        return $this->append_lpb_operational_alerts_to_row($this->db->query($sql, [$id_lpb])->row_array());
    }

    public function get_lpb_record_detail_rows($id_lpb)
    {
        $qtyLpbExpr = "COALESCE(d.qty_diterima, 0)";
        $isiExpr = "COALESCE(NULLIF(pb.isi, 0), NULLIF(pp.isi, 0), 0)";
        $kemasanExpr = "COALESCE(NULLIF(pb.kemasan, 0), NULLIF(pp.kemasan, 0), 0)";
        $qtyLpbBoxExpr = "(CASE WHEN {$isiExpr} > 0 THEN {$qtyLpbExpr} / {$isiExpr} ELSE 0 END)";
        $qtyLpbKgLtrExpr = "(CASE WHEN {$kemasanExpr} > 0 THEN {$qtyLpbExpr} * ({$kemasanExpr} / 1000) ELSE 0 END)";
        $hargaSatuanExcludeExpr = "COALESCE(
                    NULLIF(pp.harga_satuan_kecil_exclude, 0),
                    NULLIF(pp.harga_satuan_exclude, 0),
                    NULLIF(d.harga_satuan, 0),
                    0
                )";
        $dppExpr = "({$qtyLpbExpr} * {$hargaSatuanExcludeExpr})";
        $dppNilaiLainExpr = "({$dppExpr} * (11 / 12))";
        $ppnExpr = "({$dppNilaiLainExpr} * (12 / 100))";
        $ppnModeExpr = "LOWER(COALESCE(NULLIF(TRIM(pp.keterangan_harga_ppn), ''), NULLIF(TRIM(po.keterangan_harga_ppn), ''), 'exclude'))";
        $totalHargaDisplayExpr = "(CASE
                    WHEN {$ppnModeExpr} = 'include'
                        AND COALESCE(pp.harga_satuan_kecil, 0) > 0
                    THEN {$qtyLpbExpr} * COALESCE(pp.harga_satuan_kecil, 0)
                    WHEN COALESCE(po.tax, 0) > 0
                    THEN {$dppExpr} + ((COALESCE(po.tax, 0) / 100) * {$dppExpr})
                    ELSE {$dppExpr}
                END)";
        $sql = "SELECT
                d.id_detail_lpb,
                d.id_lpb,
                d.kd_barang,
                COALESCE(mb.nama_barang, '-') AS nama_barang,
                CASE
                    WHEN COALESCE(pp.qty_kecil, 0) > 0 THEN pp.qty_kecil
                    ELSE COALESCE(pp.qty, 0)
                END AS qty_order,
                GREATEST(
                    (CASE WHEN COALESCE(pp.qty_kecil, 0) > 0 THEN pp.qty_kecil ELSE COALESCE(pp.qty, 0) END) - COALESCE((
                        SELECT SUM(d2.qty_diterima)
                        FROM tb_lpb_detail d2
                        INNER JOIN tb_lpb h2 ON h2.id_lpb = d2.id_lpb
                        WHERE h2.no_po = h.no_po
                            AND d2.kd_barang = d.kd_barang
                    ), 0),
                    0
                ) AS qty_sisa,
                COALESCE((
                    SELECT SUM(d2.qty_diterima)
                    FROM tb_lpb_detail d2
                    WHERE d2.id_lpb = d.id_lpb
                        AND d2.kd_barang = d.kd_barang
                ), 0) AS qty_lpb_total,
                d.qty_diterima,
                d.no_lot,
                d.expired_date,
                {$qtyLpbExpr} AS qty_in,
                {$qtyLpbBoxExpr} AS qty_satuan_box,
                {$qtyLpbKgLtrExpr} AS qty_satuan_kg_ltr,
                {$qtyLpbExpr} AS qty_satuan_pcs,
                d.input_at,
                COALESCE(d.harga_satuan_sebelumnya, 0) AS harga_satuan_sebelumnya,
                COALESCE(d.total_harga_sebelumnya, 0) AS total_harga_sebelumnya,
                {$hargaSatuanExcludeExpr} AS harga_satuan,
                {$hargaSatuanExcludeExpr} AS harga_satuan_exclude,
                {$dppExpr} AS dpp,
                {$dppNilaiLainExpr} AS dpp_nilai_lain,
                {$ppnExpr} AS ppn,
                {$dppExpr} AS total_harga,
                {$dppExpr} AS total_harga_exclude,
                {$totalHargaDisplayExpr} AS total_harga_display,
                d.harga_verified_at,
                d.harga_verified_by,
                CASE
                    WHEN d.harga_verified_at IS NOT NULL
                        AND COALESCE(d.harga_satuan, 0) > 0
                        AND COALESCE(d.total_harga, 0) > 0
                    THEN 1
                    ELSE 0
                END AS harga_terverifikasi
            FROM tb_lpb_detail d
            INNER JOIN tb_lpb h ON h.id_lpb = d.id_lpb
            LEFT JOIN tbpo_barang mb ON mb.kode_barang = d.kd_barang
            LEFT JOIN tbpo_detail_po pp
                ON pp.no_po = h.no_po
                AND pp.kd_po = h.kd_po
                AND pp.kd_barang = d.kd_barang
            LEFT JOIN tbpo_po po
                ON po.no_po = h.no_po
                AND po.kd_po = h.kd_po
            LEFT JOIN {$this->po_barang_conversion_join('pb')}
                ON pb.kode_barang = pp.kd_barang
            WHERE d.id_lpb = ?
            ORDER BY d.id_detail_lpb ASC";

        return $this->db->query($sql, [$id_lpb])->result_array();
    }

    public function validate_lpb_detail_qty_balance($idDetailLpb, $qtyLpbBaru)
    {
        $idDetailLpb = (int) $idDetailLpb;
        $qtyLpbBaru = (float) $qtyLpbBaru;

        if ($idDetailLpb <= 0 || $qtyLpbBaru <= 0) {
            return [
                'valid' => FALSE,
                'message' => 'Qty LPB harus lebih dari 0.'
            ];
        }

        $sql = "SELECT
                d.id_detail_lpb,
                d.id_lpb,
                d.kd_barang,
                COALESCE(d.qty_diterima, 0) AS qty_lama,
                h.no_po,
                h.kd_po,
                COALESCE((
                    SELECT SUM(d3.qty_diterima)
                    FROM tb_lpb_detail d3
                    WHERE d3.id_lpb = d.id_lpb
                        AND d3.kd_barang = d.kd_barang
                ), 0) AS qty_lpb_total,
                COALESCE((
                    SELECT SUM(d2.qty_diterima)
                    FROM tb_lpb_detail d2
                    WHERE d2.id_lpb = d.id_lpb
                        AND d2.kd_barang = d.kd_barang
                        AND d2.id_detail_lpb <> d.id_detail_lpb
                ), 0) AS qty_lpb_lain
            FROM tb_lpb_detail d
            INNER JOIN tb_lpb h ON h.id_lpb = d.id_lpb
            WHERE d.id_detail_lpb = ?
            LIMIT 1";

        $row = $this->db->query($sql, [$idDetailLpb])->row_array();

        if (!$row) {
            return [
                'valid' => FALSE,
                'message' => 'Detail LPB tidak ditemukan.'
            ];
        }

        $qtyTotal = (float) ($row['qty_lpb_total'] ?? 0);
        if ($qtyTotal <= 0) {
            return [
                'valid' => FALSE,
                'message' => 'Total LPB untuk kode barang ' . ($row['kd_barang'] ?? '-') . ' tidak ditemukan, Qty LPB tidak dapat divalidasi.'
            ];
        }

        $qtyLpbLain = (float) ($row['qty_lpb_lain'] ?? 0);
        $totalSetelahUpdate = $qtyLpbLain + $qtyLpbBaru;
        $selisih = $qtyTotal - $totalSetelahUpdate;
        $tolerance = 0.0001;

        if ($qtyLpbBaru > ($qtyTotal + $tolerance) || $totalSetelahUpdate > ($qtyTotal + $tolerance)) {
            return [
                'valid' => FALSE,
                'message' => 'Qty LPB tidak boleh melebihi total LPB berdasarkan kode barang ' . ($row['kd_barang'] ?? '-') . '. Total setelah update ' . $this->format_lpb_log_value($totalSetelahUpdate) . ', sedangkan total LPB/qty diterima ' . $this->format_lpb_log_value($qtyTotal) . '.'
            ];
        }

        $warning = '';
        if (abs($selisih) > $tolerance) {
            $warning = 'Perhatian: Qty LPB barang ' . ($row['kd_barang'] ?? '-') . ' belum balance dengan qty diterima. Total setelah update ' . $this->format_lpb_log_value($totalSetelahUpdate) . ', total LPB/qty diterima ' . $this->format_lpb_log_value($qtyTotal) . ', selisih ' . $this->format_lpb_log_value($selisih) . '.';
        }

        return [
            'valid' => TRUE,
            'warning' => $warning,
            'kd_barang' => $row['kd_barang'] ?? '',
            'qty_lama' => (float) ($row['qty_lama'] ?? 0),
            'qty_lpb_total' => $qtyTotal,
            'qty_lpb_lain' => $qtyLpbLain,
            'total_setelah_update' => $totalSetelahUpdate,
            'selisih' => $selisih
        ];
    }

    public function update_lpb_detail_price($payload)
    {
        $idDetailLpb = (int) $payload['id_detail_lpb'];
        $hargaSatuanBaru = (float) $payload['harga_satuan_baru'];
        $qtyLpbBaru = isset($payload['qty_lpb_baru']) ? (float) $payload['qty_lpb_baru'] : null;

        $sql = "SELECT
                d.id_detail_lpb,
                d.id_lpb,
                d.kd_barang,
                d.qty_diterima,
                COALESCE(d.harga_satuan, 0) AS harga_satuan,
                COALESCE(d.total_harga, 0) AS total_harga,
                COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0) AS harga_satuan_aktif,
                COALESCE(NULLIF(d.total_harga, 0), d.qty_diterima * COALESCE(NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0), 0) AS total_harga_aktif,
                h.kd_po,
                h.no_invoice,
                h.status_lpb
            FROM tb_lpb_detail d
            INNER JOIN tb_lpb h ON h.id_lpb = d.id_lpb
            LEFT JOIN tbpo_detail_po pp
                ON pp.no_po = h.no_po
                AND pp.kd_po = h.kd_po
                AND pp.kd_barang = d.kd_barang
            WHERE d.id_detail_lpb = ?
            LIMIT 1";

        $row = $this->db->query($sql, [$idDetailLpb])->row_array();

        if (!$row) {
            return FALSE;
        }

        $blocker = $this->lpb_price_change_blocker((int) $row['id_lpb']);
        if (!empty($blocker['blocked'])) {
            return [
                'status' => FALSE,
                'message' => $blocker['message'],
                'errors' => [$blocker['code']],
                'data' => $blocker['data']
            ];
        }

        $qty = (float) ($row['qty_diterima'] ?? 0);
        if ($qtyLpbBaru !== null && $qtyLpbBaru > 0) {
            $qty = $qtyLpbBaru;
        }
        $hargaSatuanLama = (float) ($row['harga_satuan_aktif'] ?? 0);
        $totalHargaLama = (float) ($row['total_harga_aktif'] ?? 0);
        $totalHargaBaru = $qty * $hargaSatuanBaru;

        $updated = $this->db
            ->where('id_detail_lpb', $idDetailLpb)
            ->update('tb_lpb_detail', [
                'harga_satuan_sebelumnya' => $hargaSatuanLama,
                'total_harga_sebelumnya'  => $totalHargaLama,
                'qty_diterima'            => $qty,
                'harga_satuan'            => $hargaSatuanBaru,
                'total_harga'             => $totalHargaBaru,
                'harga_update_by'         => $payload['dilakukan_oleh'],
                'harga_update_at'         => date('Y-m-d H:i:s'),
                'harga_verified_by'       => null,
                'harga_verified_at'       => null
            ]);

        if (!$updated) {
            return FALSE;
        }

        if ($this->db->table_exists('tb_lpb_batch')) {
            $this->db
                ->where('id_detail_lpb', $idDetailLpb)
                ->update('tb_lpb_batch', [
                    'qty' => $qty
                ]);
        }

        if (!$this->insert_lpb_activity_log([
            'id_lpb'         => (int) $row['id_lpb'],
            'kd_po'          => $row['kd_po'] ?? '',
            'no_invoice'     => $row['no_invoice'] ?? '',
            'action_type'    => 'UPDATE_LPB_DETAIL_PRICE',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'data_before'    => [
                'id_detail_lpb' => $idDetailLpb,
                'kd_barang' => $row['kd_barang'],
                'qty_diterima' => $row['qty_diterima'],
                'harga_satuan' => $hargaSatuanLama,
                'total_harga' => $totalHargaLama
            ],
            'data_after'     => [
                'id_detail_lpb' => $idDetailLpb,
                'kd_barang' => $row['kd_barang'],
                'qty_diterima' => $qty,
                'harga_satuan' => $hargaSatuanBaru,
                'total_harga' => $totalHargaBaru
            ],
            'keterangan'     => $this->describe_lpb_changes('Update harga detail LPB barang ' . ($row['kd_barang'] ?? ''), [
                'qty_diterima' => 'Qty LPB',
                'harga_satuan' => 'Harga Satuan',
                'total_harga' => 'Total Harga'
            ], [
                'qty_diterima' => $row['qty_diterima'],
                'harga_satuan' => $hargaSatuanLama,
                'total_harga' => $totalHargaLama
            ], [
                'qty_diterima' => $qty,
                'harga_satuan' => $hargaSatuanBaru,
                'total_harga' => $totalHargaBaru
            ]),
            'dilakukan_oleh' => $payload['dilakukan_oleh'] ?? 'SYSTEM'
        ])) {
            return FALSE;
        }

        return [
            'id_detail_lpb'            => $idDetailLpb,
            'id_lpb'                   => $row['id_lpb'],
            'kd_barang'                => $row['kd_barang'],
            'qty_diterima'             => $qty,
            'harga_satuan_sebelumnya'  => $hargaSatuanLama,
            'total_harga_sebelumnya'   => $totalHargaLama,
            'harga_satuan'             => $hargaSatuanBaru,
            'total_harga'              => $totalHargaBaru
        ];
    }

    public function accept_lpb_detail_price($payload)
    {
        $idDetailLpb = (int) $payload['id_detail_lpb'];

        $sql = "SELECT
                d.id_detail_lpb,
                d.id_lpb,
                d.kd_barang,
                d.qty_diterima,
                d.harga_verified_by,
                d.harga_verified_at,
                COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0) AS harga_satuan_aktif,
                COALESCE(NULLIF(d.total_harga, 0), d.qty_diterima * COALESCE(NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0), 0) AS total_harga_aktif,
                h.kd_po,
                h.no_invoice,
                h.status_lpb
            FROM tb_lpb_detail d
            INNER JOIN tb_lpb h ON h.id_lpb = d.id_lpb
            LEFT JOIN tbpo_detail_po pp
                ON pp.no_po = h.no_po
                AND pp.kd_po = h.kd_po
                AND pp.kd_barang = d.kd_barang
            WHERE d.id_detail_lpb = ?
            LIMIT 1";

        $row = $this->db->query($sql, [$idDetailLpb])->row_array();

        if (!$row) {
            return FALSE;
        }

        if ((int) ($row['status_lpb'] ?? 1) !== 0) {
            return FALSE;
        }

        $hargaSatuanAktif = (float) ($row['harga_satuan_aktif'] ?? 0);
        $totalHargaAktif = (float) ($row['total_harga_aktif'] ?? 0);

        if ($hargaSatuanAktif <= 0 || $totalHargaAktif <= 0) {
            return FALSE;
        }

        $updated = $this->db
            ->where('id_detail_lpb', $idDetailLpb)
            ->update('tb_lpb_detail', [
                'harga_satuan'      => $hargaSatuanAktif,
                'total_harga'       => $totalHargaAktif,
                'harga_verified_by' => $payload['dilakukan_oleh'],
                'harga_verified_at' => date('Y-m-d H:i:s')
            ]);

        if (!$updated) {
            return FALSE;
        }

        if (!$this->recalculate_lpb_status((int) $row['id_lpb'])) {
            return FALSE;
        }

        if (!$this->insert_lpb_activity_log([
            'id_lpb'         => (int) $row['id_lpb'],
            'kd_po'          => $row['kd_po'] ?? '',
            'no_invoice'     => $row['no_invoice'] ?? '',
            'action_type'    => 'ACCEPT_LPB_DETAIL_PRICE',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'data_before'    => [
                'id_detail_lpb' => $idDetailLpb,
                'kd_barang' => $row['kd_barang'],
                'harga_verified_by' => $row['harga_verified_by'] ?? '',
                'harga_verified_at' => $row['harga_verified_at'] ?? ''
            ],
            'data_after'     => [
                'id_detail_lpb' => $idDetailLpb,
                'kd_barang' => $row['kd_barang'],
                'harga_verified_by' => $payload['dilakukan_oleh'] ?? 'SYSTEM',
                'harga_verified_at' => date('Y-m-d H:i:s')
            ],
            'keterangan'     => 'Verifikasi harga detail LPB barang ' . ($row['kd_barang'] ?? '') . ' dengan harga satuan ' . $this->format_lpb_log_value($hargaSatuanAktif) . ' dan total harga ' . $this->format_lpb_log_value($totalHargaAktif) . '.',
            'dilakukan_oleh' => $payload['dilakukan_oleh'] ?? 'SYSTEM'
        ])) {
            return FALSE;
        }

        return [
            'id_detail_lpb' => $idDetailLpb,
            'id_lpb'        => $row['id_lpb'],
            'kd_barang'     => $row['kd_barang'],
            'harga_satuan'  => $hargaSatuanAktif,
            'total_harga'   => $totalHargaAktif
        ];
    }

    public function resolve_lpb_status($header, $detailSummary = null)
    {
        if ((int) ($header['status_lpb'] ?? 1) === 0) {
            return 0;
        }

        return 1;
    }

    public function get_lpb_detail_price_status_summary($idLpb)
    {
        $sql = "SELECT
                COUNT(*) AS total_rows,
                SUM(
                    CASE
                        WHEN harga_verified_at IS NOT NULL
                            AND COALESCE(harga_satuan, 0) > 0
                            AND COALESCE(total_harga, 0) > 0
                        THEN 0
                        ELSE 1
                    END
                ) AS unverified_rows
            FROM tb_lpb_detail
            WHERE id_lpb = ?";

        return $this->db->query($sql, [(int) $idLpb])->row_array();
    }

    public function recalculate_lpb_status($idLpb)
    {
        if (!$this->db->field_exists('status_lpb', 'tb_lpb')) {
            return TRUE;
        }

        $header = $this->db
            ->where('id_lpb', (int) $idLpb)
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$header) {
            return FALSE;
        }

        $statusLpb = $this->resolve_lpb_status($header, $this->get_lpb_detail_price_status_summary($idLpb));

        return $this->db
            ->where('id_lpb', (int) $idLpb)
            ->update('tb_lpb', ['status_lpb' => $statusLpb]);
    }

    public function get_purchasing_lpb_detail_rows($id_lpb)
    {
        $qtyLpbExpr = "COALESCE(d.qty_diterima, 0)";
        $isiExpr = "COALESCE(NULLIF(pb.isi, 0), NULLIF(pp.isi, 0), 0)";
        $kemasanExpr = "COALESCE(NULLIF(pb.kemasan, 0), NULLIF(pp.kemasan, 0), 0)";
        $qtyLpbBoxExpr = "(CASE WHEN {$isiExpr} > 0 THEN {$qtyLpbExpr} / {$isiExpr} ELSE 0 END)";
        $qtyLpbKgLtrExpr = "(CASE WHEN {$kemasanExpr} > 0 THEN {$qtyLpbExpr} * ({$kemasanExpr} / 1000) ELSE 0 END)";
        $hargaSatuanExcludeExpr = "COALESCE(
                    NULLIF(pp.harga_satuan_kecil_exclude, 0),
                    NULLIF(pp.harga_satuan_exclude, 0),
                    NULLIF(d.harga_satuan, 0),
                    0
                )";
        $dppExpr = "({$qtyLpbExpr} * {$hargaSatuanExcludeExpr})";
        $dppNilaiLainExpr = "({$dppExpr} * (11 / 12))";
        $ppnExpr = "({$dppNilaiLainExpr} * (12 / 100))";
        $ppnModeExpr = "LOWER(COALESCE(NULLIF(TRIM(pp.keterangan_harga_ppn), ''), NULLIF(TRIM(po.keterangan_harga_ppn), ''), 'exclude'))";
        $totalHargaDisplayExpr = "(CASE
                    WHEN {$ppnModeExpr} = 'include'
                        AND COALESCE(pp.harga_satuan_kecil, 0) > 0
                    THEN {$qtyLpbExpr} * COALESCE(pp.harga_satuan_kecil, 0)
                    WHEN COALESCE(po.tax, 0) > 0
                    THEN {$dppExpr} + ((COALESCE(po.tax, 0) / 100) * {$dppExpr})
                    ELSE {$dppExpr}
                END)";

        $sql = "SELECT
                d.id_detail_lpb,
                d.kd_barang,
                COALESCE(NULLIF(pp.nama_barang, ''), mb.nama_barang, '-') AS nama_barang,
                COALESCE(NULLIF(d.no_lot, ''), '-') AS no_lot,
                CASE
                    WHEN d.expired_date IS NULL THEN '-'
                    WHEN d.expired_date = '0000-00-00' THEN '-'
                    ELSE DATE_FORMAT(d.expired_date, '%d/%m/%Y')
                END AS expired_date,
                {$qtyLpbExpr} AS qty_in,
                {$qtyLpbBoxExpr} AS qty_satuan_box,
                {$qtyLpbKgLtrExpr} AS qty_satuan_kg_ltr,
                {$qtyLpbExpr} AS qty_satuan_pcs,
                CASE
                    WHEN COALESCE(pp.qty_kecil, 0) > 0 THEN pp.qty_kecil
                    ELSE COALESCE(pp.qty, 0)
                END AS qty_order,
                {$qtyLpbExpr} AS qty_lpb,
                GREATEST(
                    (CASE WHEN COALESCE(pp.qty_kecil, 0) > 0 THEN pp.qty_kecil ELSE COALESCE(pp.qty, 0) END) - COALESCE(rcv.qty_diterima, 0),
                    0
                ) AS qty_sisa,
                COALESCE((
                    SELECT SUM(d2.qty_diterima)
                    FROM tb_lpb_detail d2
                    WHERE d2.id_lpb = d.id_lpb
                        AND d2.kd_barang = d.kd_barang
                ), 0) AS qty_lpb_total,
                {$hargaSatuanExcludeExpr} AS harga_satuan_exclude,
                {$dppExpr} AS dpp,
                {$dppNilaiLainExpr} AS dpp_nilai_lain,
                {$ppnExpr} AS ppn,
                {$dppExpr} AS total_harga_exclude,
                {$hargaSatuanExcludeExpr} AS harga_satuan,
                {$dppExpr} AS total_harga,
                {$totalHargaDisplayExpr} AS total_harga_display,
                COALESCE(d.harga_satuan_sebelumnya, 0) AS harga_satuan_sebelumnya,
                COALESCE(d.total_harga_sebelumnya, 0) AS total_harga_sebelumnya,
                d.harga_verified_at,
                d.harga_verified_by,
                CASE
                    WHEN d.harga_verified_at IS NOT NULL
                        AND COALESCE(d.harga_satuan, 0) > 0
                        AND COALESCE(d.total_harga, 0) > 0
                    THEN 1
                    ELSE 0
                END AS harga_terverifikasi
            FROM tb_lpb h
            INNER JOIN tb_lpb_detail d
                ON d.id_lpb = h.id_lpb
            LEFT JOIN tbpo_detail_po pp
                ON pp.no_po = h.no_po
                AND pp.kd_po = h.kd_po
                AND pp.kd_barang = d.kd_barang
            LEFT JOIN tbpo_po po
                ON po.no_po = h.no_po
                AND po.kd_po = h.kd_po
            LEFT JOIN {$this->po_barang_conversion_join('pb')}
                ON pb.kode_barang = pp.kd_barang
            LEFT JOIN tbpo_barang mb ON mb.kode_barang = a.kd_barang
                ON mb.kd_barang = d.kd_barang
            LEFT JOIN (
                SELECT
                    h.no_po,
                    d.kd_barang,
                    SUM(d.qty_diterima) AS qty_diterima
                FROM tb_lpb h
                INNER JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
                GROUP BY h.no_po, d.kd_barang
            ) rcv
                ON rcv.no_po = h.no_po
                AND rcv.kd_barang = d.kd_barang
            WHERE h.id_lpb = ?
            ORDER BY d.id_detail_lpb ASC";

        return $this->db->query($sql, [$id_lpb])->result_array();
    }

    public function get_lpb_type_options()
    {
        return [
            'LPB CP' => [
                'label' => 'LPB CP',
                'prefix' => '',
                'suffix' => '',
                'example' => '2600001'
            ],
            'LPB Benih' => [
                'label' => 'LPB Benih',
                'prefix' => '',
                'suffix' => 'B',
                'example' => '2600001B'
            ],
            'LPB Konsinyasi' => [
                'label' => 'LPB Konsinyasi',
                'prefix' => '',
                'suffix' => 'K',
                'example' => '2600002K'
            ],
            'LPB Barang Non Pajak (A)' => [
                'label' => 'LPB Barang Non Pajak (A)',
                'prefix' => 'A',
                'suffix' => '',
                'example' => 'A2600001'
            ],
            'LPB Promosi' => [
                'label' => 'LPB Promosi',
                'prefix' => 'X',
                'suffix' => '',
                'example' => 'X2600001'
            ],
            'LPB Barang Pengganti Retur (RA)' => [
                'label' => 'LPB Barang Pengganti Retur (RA)',
                'prefix' => 'RA',
                'suffix' => '',
                'example' => 'RA2600001'
            ]
        ];
    }

    public function normalize_lpb_type($jenisLpb)
    {
        $jenisLpb = trim((string) $jenisLpb);
        $options = $this->get_lpb_type_options();

        return isset($options[$jenisLpb]) ? $jenisLpb : 'LPB CP';
    }

    public function generate_lpb_number($jenisLpb, $excludeIdLpb = 0)
    {
        if (!$this->db->field_exists('nomor_lpb', 'tb_lpb')) {
            return '';
        }

        $jenisLpb = $this->normalize_lpb_type($jenisLpb);
        $format = $this->get_lpb_type_options()[$jenisLpb];
        $period = date('y');
        $legacyPeriod = date('n') . date('y');
        $prefix = $format['prefix'];
        $suffix = $format['suffix'];
        $maxSequence = 0;

        $this->db->select('nomor_lpb');
        $this->db->from('tb_lpb');
        $this->db->where('jenis_lpb', $jenisLpb);
        $this->db->where('nomor_lpb IS NOT NULL', null, false);
        $this->db->where("TRIM(nomor_lpb) <> ''", null, false);

        if ((int) $excludeIdLpb > 0) {
            $this->db->where('id_lpb !=', (int) $excludeIdLpb);
        }

        $rows = $this->db->get()->result_array();

        foreach ($rows as $row) {
            $nomorLpb = trim((string) ($row['nomor_lpb'] ?? ''));

            if ($prefix !== '' && substr($nomorLpb, 0, strlen($prefix)) !== $prefix) {
                continue;
            }

            if ($suffix !== '' && substr($nomorLpb, -strlen($suffix)) !== $suffix) {
                continue;
            }

            $core = $nomorLpb;
            if ($prefix !== '') {
                $core = substr($core, strlen($prefix));
            }
            if ($suffix !== '') {
                $core = substr($core, 0, -strlen($suffix));
            }

            if (substr($core, 0, strlen($period)) === $period) {
                $sequenceText = substr($core, strlen($period));
            } elseif (substr($core, 0, strlen($legacyPeriod)) === $legacyPeriod) {
                $sequenceText = substr($core, strlen($legacyPeriod));
            } else {
                continue;
            }

            if (!ctype_digit($sequenceText)) {
                continue;
            }

            $sequence = (int) $sequenceText;
            if ($sequence > $maxSequence) {
                $maxSequence = $sequence;
            }
        }

        return $prefix . $period . str_pad((string) ($maxSequence + 1), 5, '0', STR_PAD_LEFT) . $suffix;
    }

    public function update_lpb_type($payload)
    {
        $row = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$row) {
            return FALSE;
        }

        $jenisLpb = $this->normalize_lpb_type($payload['jenis_lpb'] ?? '');
        $updateData = [];

        if ($this->db->field_exists('jenis_lpb', 'tb_lpb')) {
            $updateData['jenis_lpb'] = $jenisLpb;
        }

        if ($this->db->field_exists('nomor_lpb', 'tb_lpb')) {
            $currentJenisLpb = $this->normalize_lpb_type($row['jenis_lpb'] ?? '');
            $currentNomorLpb = trim((string) ($row['nomor_lpb'] ?? ''));

            $updateData['nomor_lpb'] = ($currentJenisLpb === $jenisLpb && $currentNomorLpb !== '')
                ? $currentNomorLpb
                : $this->generate_lpb_number($jenisLpb, (int) $payload['id_lpb']);
        }

        if (empty($updateData)) {
            return TRUE;
        }

        $updated = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->update('tb_lpb', $updateData);

        if (!$updated) {
            return FALSE;
        }

        if (!$this->recalculate_lpb_status((int) $payload['id_lpb'])) {
            return FALSE;
        }

        $updatedRow = array_merge($row, $updateData);
        $updatedRow['status_lpb'] = $this->resolve_lpb_status($updatedRow, $this->get_lpb_detail_price_status_summary((int) $payload['id_lpb']));

        if (!$this->insert_lpb_activity_log([
            'id_lpb'         => (int) $payload['id_lpb'],
            'kd_po'          => $row['kd_po'] ?? '',
            'no_invoice'     => $row['no_invoice'] ?? '',
            'action_type'    => 'UPDATE_LPB_TYPE',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($updatedRow['status_lpb'] ?? 1),
            'data_before'    => [
                'nomor_lpb' => $row['nomor_lpb'] ?? '',
                'jenis_lpb' => $row['jenis_lpb'] ?? ''
            ],
            'data_after'     => [
                'nomor_lpb' => $updatedRow['nomor_lpb'] ?? '',
                'jenis_lpb' => $updatedRow['jenis_lpb'] ?? ''
            ],
            'keterangan'     => $this->describe_lpb_changes('Update jenis LPB', [
                'nomor_lpb' => 'Nomor LPB',
                'jenis_lpb' => 'Jenis LPB'
            ], [
                'nomor_lpb' => $row['nomor_lpb'] ?? '',
                'jenis_lpb' => $row['jenis_lpb'] ?? ''
            ], [
                'nomor_lpb' => $updatedRow['nomor_lpb'] ?? '',
                'jenis_lpb' => $updatedRow['jenis_lpb'] ?? ''
            ]),
            'dilakukan_oleh' => $payload['dilakukan_oleh'] ?? 'SYSTEM'
        ])) {
            return FALSE;
        }

        return $updatedRow;
    }

    public function get_tmp_po_received_item($kd_po, $kd_barang)
    {
        $this->normalize_tmp_po_received_ids(['kd_po' => $kd_po, 'kd_barang' => $kd_barang]);
        $dimensiExpr = $this->po_conversion_factor_expr('pp', 'pb');

        $sql = "SELECT
                t.id_tmp_recieved,
                t.id_tmp_recieved AS id_tmp_received,
                t.kd_po,
                t.kd_suplier,
                t.kd_barang,
                CASE
                    WHEN {$dimensiExpr} > 0 THEN t.qty_diterima / {$dimensiExpr}
                    ELSE t.qty_diterima
                END AS qty_diterima,
                t.qty_diterima AS qty_diterima_kecil,
                t.satuan,
                t.no_lot,
                t.expired_date,
                t.harga_satuan,
                t.harga_satuan_kecil,
                t.total_harga,
                {$dimensiExpr} AS dimensi_br
            FROM tb_tmp_po_received t
            LEFT JOIN tbpo_detail_po pp
                ON pp.kd_po = t.kd_po
                AND pp.kd_barang = t.kd_barang
                AND pp.kd_suplier = t.kd_suplier
            LEFT JOIN {$this->po_barang_conversion_join('pb')}
                ON pb.kode_barang = t.kd_barang
            WHERE t.kd_po = ?
                AND t.kd_barang = ?
            ORDER BY t.id_tmp_recieved ASC";

        return $this->db->query($sql, [$kd_po, $kd_barang])->result_array();
    }

    public function replace_tmp_po_received_item($kd_po, $kd_barang, $rows)
    {
        $this->db->where('kd_po', $kd_po);
        $this->db->where('kd_barang', $kd_barang);
        $this->db->delete('tb_tmp_po_received');

        if (empty($rows)) {
            return TRUE;
        }

        $rows = $this->assign_tmp_po_received_ids($rows);

        return $this->db->insert_batch('tb_tmp_po_received', $rows);
    }

    private function next_tmp_po_received_id()
    {
        $row = $this->db
            ->select('COALESCE(MAX(id_tmp_recieved), 0) + 1 AS next_id', false)
            ->get('tb_tmp_po_received')
            ->row_array();

        return max(1, (int) ($row['next_id'] ?? 1));
    }

    private function assign_tmp_po_received_ids(array $rows, $forceNewIds = false)
    {
        $nextId = $this->next_tmp_po_received_id();

        foreach ($rows as &$row) {
            $currentId = (int) ($row['id_tmp_recieved'] ?? 0);
            if ($forceNewIds || $currentId <= 0) {
                $row['id_tmp_recieved'] = $nextId++;
            }
        }
        unset($row);

        return $rows;
    }

    public function normalize_tmp_po_received_ids(array $filters)
    {
        $this->db->select('t.*');
        $this->db->from('tb_tmp_po_received t');

        if (!empty($filters['no_po'])) {
            $this->db->join(
                'tbpo_detail_po pp',
                'pp.kd_po = t.kd_po AND pp.kd_barang = t.kd_barang AND pp.kd_suplier = t.kd_suplier',
                'inner'
            );
            $this->db->where('pp.no_po', $filters['no_po']);
        }

        if (!empty($filters['kd_suplier'])) {
            $this->db->where('t.kd_suplier', $filters['kd_suplier']);
        }

        if (!empty($filters['kd_po'])) {
            $this->db->where('t.kd_po', $filters['kd_po']);
        }

        if (!empty($filters['kd_barang'])) {
            $this->db->where('t.kd_barang', $filters['kd_barang']);
        }

        $rows = $this->db->order_by('t.kd_barang', 'ASC')->order_by('t.id_tmp_recieved', 'ASC')->get()->result_array();

        if (empty($rows)) {
            return TRUE;
        }

        $hasInvalidId = FALSE;
        foreach ($rows as $row) {
            if ((int) ($row['id_tmp_recieved'] ?? 0) <= 0) {
                $hasInvalidId = TRUE;
                break;
            }
        }

        if (!$hasInvalidId) {
            return TRUE;
        }

        $this->db->trans_begin();

        if (!empty($filters['no_po'])) {
            $sql = "DELETE t
                    FROM tb_tmp_po_received t
                    INNER JOIN tbpo_detail_po pp
                        ON pp.kd_po = t.kd_po
                        AND pp.kd_barang = t.kd_barang
                        AND pp.kd_suplier = t.kd_suplier
                    WHERE pp.no_po = ?";
            $params = [$filters['no_po']];

            if (!empty($filters['kd_suplier'])) {
                $sql .= " AND t.kd_suplier = ?";
                $params[] = $filters['kd_suplier'];
            }

            $this->db->query($sql, $params);
        } else {
            if (!empty($filters['kd_suplier'])) {
                $this->db->where('kd_suplier', $filters['kd_suplier']);
            }
            if (!empty($filters['kd_po'])) {
                $this->db->where('kd_po', $filters['kd_po']);
            }
            if (!empty($filters['kd_barang'])) {
                $this->db->where('kd_barang', $filters['kd_barang']);
            }
            $this->db->delete('tb_tmp_po_received');
        }

        $rows = $this->assign_tmp_po_received_ids($rows, TRUE);
        $this->db->insert_batch('tb_tmp_po_received', $rows);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();

        return TRUE;
    }

    public function get_po_exclude_price_by_item($no_po, $kd_barang, $kd_suplier = '', $kd_po = '')
    {
        $this->db->select('
            kd_po,
            no_po,
            kd_suplier,
            kd_barang,
            COALESCE(harga_satuan_exclude, 0) AS harga_satuan,
            COALESCE(harga_satuan_kecil_exclude, 0) AS harga_satuan_kecil
        ');
        $this->db->from('tbpo_detail_po');
        $this->db->where('no_po', $no_po);
        $this->db->where('kd_barang', $kd_barang);

        if ($kd_suplier !== '') {
            $this->db->where('kd_suplier', $kd_suplier);
        }

        if ($kd_po !== '') {
            $this->db->where('kd_po', $kd_po);
        }

        $this->db->limit(1);

        return $this->db->get()->row_array();
    }

    public function get_tmp_po_received_summary($no_po, $kd_suplier)
    {
        $this->normalize_tmp_po_received_ids(['no_po' => $no_po, 'kd_suplier' => $kd_suplier]);
        $dimensiExpr = $this->po_conversion_factor_expr('pp', 'pb');

        $sql = "SELECT
                t.id_tmp_recieved,
                t.id_tmp_recieved AS id_tmp_received,
                t.kd_po,
                t.kd_barang,
                COALESCE(NULLIF(pp.nama_barang, ''), mb.nama_barang, '-') AS nama_barang,
                CASE
                    WHEN {$dimensiExpr} > 0 THEN t.qty_diterima / {$dimensiExpr}
                    ELSE t.qty_diterima
                END AS qty_diterima,
                t.qty_diterima AS qty_diterima_kecil,
                t.satuan,
                t.no_lot,
                t.expired_date,
                t.harga_satuan,
                t.harga_satuan_kecil,
                t.total_harga,
                {$dimensiExpr} AS dimensi_br
            FROM tb_tmp_po_received t
            INNER JOIN tbpo_detail_po pp
                ON pp.kd_po = t.kd_po
                AND pp.kd_barang = t.kd_barang
                AND pp.kd_suplier = t.kd_suplier
            LEFT JOIN tbpo_barang mb
                ON mb.kode_barang = t.kd_barang
            LEFT JOIN {$this->po_barang_conversion_join('pb')}
                ON pb.kode_barang = t.kd_barang
            WHERE pp.no_po = ?
                AND t.kd_suplier = ?
            ORDER BY t.kd_barang ASC, t.id_tmp_recieved ASC";

        return $this->db->query($sql, [$no_po, $kd_suplier])->result_array();
    }

    public function get_tmp_po_received_summary_by_item($kd_po, $kd_barang)
    {
        $this->normalize_tmp_po_received_ids(['kd_po' => $kd_po, 'kd_barang' => $kd_barang]);
        $dimensiExpr = $this->po_conversion_factor_expr('pp', 'pb');

        $sql = "SELECT
                t.id_tmp_recieved,
                t.id_tmp_recieved AS id_tmp_received,
                t.kd_po,
                t.kd_barang,
                COALESCE(NULLIF(pp.nama_barang, ''), mb.nama_barang, '-') AS nama_barang,
                CASE
                    WHEN {$dimensiExpr} > 0 THEN t.qty_diterima / {$dimensiExpr}
                    ELSE t.qty_diterima
                END AS qty_diterima,
                t.qty_diterima AS qty_diterima_kecil,
                t.satuan,
                t.no_lot,
                t.expired_date,
                t.harga_satuan,
                t.harga_satuan_kecil,
                t.total_harga,
                {$dimensiExpr} AS dimensi_br
            FROM tb_tmp_po_received t
            LEFT JOIN tbpo_detail_po pp
                ON pp.kd_po = t.kd_po
                AND pp.kd_barang = t.kd_barang
                AND pp.kd_suplier = t.kd_suplier
            LEFT JOIN tbpo_barang mb
                ON mb.kode_barang = t.kd_barang
            LEFT JOIN {$this->po_barang_conversion_join('pb')}
                ON pb.kode_barang = t.kd_barang
            WHERE t.kd_po = ?
                AND t.kd_barang = ?
            ORDER BY t.id_tmp_recieved ASC";

        return $this->db->query($sql, [$kd_po, $kd_barang])->result_array();
    }

    public function delete_tmp_po_received_row($idTmpReceived, $kdSuplier = '', $noPo = '')
    {
        $idTmpReceived = (int) $idTmpReceived;

        if ($idTmpReceived <= 0) {
            return FALSE;
        }

        $this->db->from('tb_tmp_po_received t');
        $this->db->join(
            'tbpo_detail_po pp',
            'pp.kd_po = t.kd_po AND pp.kd_barang = t.kd_barang AND pp.kd_suplier = t.kd_suplier',
            'inner'
        );
        $this->db->where('t.id_tmp_recieved', $idTmpReceived);

        if ($kdSuplier !== '') {
            $this->db->where('t.kd_suplier', $kdSuplier);
        }

        if ($noPo !== '') {
            $this->db->where('pp.no_po', $noPo);
        }

        $row = $this->db->get()->row_array();

        if (!$row) {
            return FALSE;
        }

        $this->db->where('id_tmp_recieved', $idTmpReceived);

        return $this->db->delete('tb_tmp_po_received');
    }

    public function get_po_remaining_qty($no_po, $kd_suplier)
    {
        $dimensiExpr = $this->po_conversion_factor_expr('pp', 'pb');
        $qtyOrderKecilExpr = "(CASE
                    WHEN COALESCE(pp.qty_kecil, 0) > 0 THEN COALESCE(pp.qty_kecil, 0)
                    ELSE COALESCE(pp.qty, 0) * {$dimensiExpr}
                END)";

        $sql = "SELECT
                pp.kd_po,
                pp.kd_barang,
                {$qtyOrderKecilExpr} AS qty_order,
                COALESCE(rcv.qty_diterima, 0) AS qty_diterima,
                CASE
                    WHEN {$dimensiExpr} > 0
                    THEN GREATEST(({$qtyOrderKecilExpr} - COALESCE(rcv.qty_diterima, 0)) / {$dimensiExpr}, 0)
                    ELSE GREATEST({$qtyOrderKecilExpr} - COALESCE(rcv.qty_diterima, 0), 0)
                END AS qty_sisa,
                GREATEST({$qtyOrderKecilExpr} - COALESCE(rcv.qty_diterima, 0), 0) AS qty_kecil_sisa,
                {$dimensiExpr} AS dimensi_br
            FROM tbpo_detail_po pp
            LEFT JOIN {$this->po_barang_conversion_join('pb')}
                ON pb.kode_barang = pp.kd_barang
            LEFT JOIN (
                SELECT
                    h.no_po,
                    d.kd_barang,
                    SUM(d.qty_diterima) AS qty_diterima
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb h ON h.id_lpb = d.id_lpb
                GROUP BY h.no_po, d.kd_barang
            ) rcv
                ON rcv.no_po = pp.no_po
                AND rcv.kd_barang = pp.kd_barang
            WHERE pp.no_po = ?
                AND pp.kd_suplier = ?
        ";

        return $this->db->query($sql, [$no_po, $kd_suplier])->result_array();
    }

    public function get_po_remaining_qty_by_item($kd_po, $kd_barang)
    {
        $dimensiExpr = $this->po_conversion_factor_expr('pp', 'pb');
        $qtyOrderKecilExpr = "(CASE
                    WHEN COALESCE(pp.qty_kecil, 0) > 0 THEN COALESCE(pp.qty_kecil, 0)
                    ELSE COALESCE(pp.qty, 0) * {$dimensiExpr}
                END)";

        $sql = "SELECT
                pp.no_po,
                pp.kd_suplier,
                pp.kd_po,
                pp.kd_barang,
                pp.satuan,
                {$qtyOrderKecilExpr} AS qty_order,
                COALESCE(rcv.qty_diterima, 0) AS qty_diterima,
                CASE
                    WHEN {$dimensiExpr} > 0
                    THEN GREATEST(({$qtyOrderKecilExpr} - COALESCE(rcv.qty_diterima, 0)) / {$dimensiExpr}, 0)
                    ELSE GREATEST({$qtyOrderKecilExpr} - COALESCE(rcv.qty_diterima, 0), 0)
                END AS qty_sisa,
                GREATEST({$qtyOrderKecilExpr} - COALESCE(rcv.qty_diterima, 0), 0) AS qty_kecil_sisa,
                {$dimensiExpr} AS dimensi_br
            FROM tbpo_detail_po pp
            LEFT JOIN {$this->po_barang_conversion_join('pb')}
                ON pb.kode_barang = pp.kd_barang
            LEFT JOIN (
                SELECT
                    h.no_po,
                    d.kd_barang,
                    SUM(d.qty_diterima) AS qty_diterima
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb h ON h.id_lpb = d.id_lpb
                GROUP BY h.no_po, d.kd_barang
            ) rcv
                ON rcv.no_po = pp.no_po
                AND rcv.kd_barang = pp.kd_barang
            WHERE pp.kd_po = ?
                AND pp.kd_barang = ?
            LIMIT 1
        ";

        return $this->db->query($sql, [$kd_po, $kd_barang])->row_array();
    }

    public function get_tmp_po_received_posting_rows($no_po, $kd_suplier)
    {
        $this->db->select('
            t.id_tmp_recieved,
            t.kd_po,
            t.kd_suplier,
            t.kd_barang,
            t.qty_diterima,
            t.satuan,
            t.no_lot,
            t.expired_date,
            t.harga_satuan,
            t.harga_satuan_kecil,
            t.total_harga
        ');
        $this->db->from('tb_tmp_po_received t');
        $this->db->join(
            'tbpo_detail_po pp',
            'pp.kd_po = t.kd_po AND pp.kd_barang = t.kd_barang AND pp.kd_suplier = t.kd_suplier',
            'inner'
        );
        $this->db->where('pp.no_po', $no_po);
        $this->db->where('t.kd_suplier', $kd_suplier);
        $this->db->order_by('t.id_tmp_recieved', 'ASC');

        return $this->db->get()->result_array();
    }

    public function update_pre_po_status_by_kd_po($kd_po, $status)
    {
        $legacyStatus = $status;
        $poStatus = ((string) $status === '2') ? 'DONE' : (string) $status;
        $updated = TRUE;

        if ($this->db->table_exists('tbpo_po')) {
            $updated = $this->db
                ->where('kd_po', $kd_po)
                ->update('tbpo_po', ['status' => $poStatus]) && $updated;
        }

        if ($this->db->table_exists('tb_pre_po')) {
            $updated = $this->db
                ->where('kd_po', $kd_po)
                ->update('tb_pre_po', ['status' => $legacyStatus]) && $updated;
        }

        return $updated;
    }

    public function get_pre_po_adjustment($kd_po)
    {
        $this->db->select('
            pp.id_pre_po,
            pp.no_po,
            pp.kd_po,
            pp.kd_barang,
            COALESCE(mb.nama_barang, "-") AS nama_barang,
            pp.qty,
            pp.satuan,
            pp.hrg_satuan,
            pp.harga_total,
            pp.status
        ');
        $this->db->from('tb_pre_po pp');
        $this->db->join('tbpo_barang mb', 'mb.kode_barang = pp.kd_barang', 'left');
        $this->db->where('pp.kd_po', $kd_po);
        $this->db->order_by('pp.id_pre_po', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_pre_po_invoice_adjustment($kd_po)
    {
        $hasAdjustmentTable = $this->db->table_exists('tb_pre_po_invoice_adjustment');

        $this->db->select('
            pp.id_pre_po,
            pp.no_po,
            pp.kd_po,
            pp.kd_barang,
            COALESCE(mb.nama_barang, "-") AS nama_barang,
            ' . ($hasAdjustmentTable ? '
                COALESCE(adj.qty, pp.qty, 0) AS qty,
                COALESCE(adj.satuan, pp.satuan, "-") AS satuan,
                COALESCE(adj.harga_satuan, pp.hrg_satuan, 0) AS harga_satuan,
                COALESCE(adj.total_harga, (pp.qty * pp.hrg_satuan), 0) AS harga_total,
                COALESCE(adj.harga, pp.hrg_satuan, 0) AS harga,
                COALESCE(adj.harga_diskon, pp.hrg_satuan, 0) AS harga_diskon,
                COALESCE(adj.total_harga_diskon, (pp.qty * COALESCE(adj.harga_diskon, pp.hrg_satuan)), 0) AS harga_total_diskon,
                COALESCE(adj.tax_percent, 0) AS tax_percent,
                COALESCE(adj.tax, 0) AS tax,
                COALESCE(adj.tax_diskon, 0) AS tax_diskon,
                COALESCE(adj.grand_total, COALESCE(adj.total_harga, (pp.qty * pp.hrg_satuan)) + COALESCE(adj.tax, 0), 0) AS grand_total,
                COALESCE(adj.grand_total_diskon, COALESCE(adj.total_harga_diskon, (pp.qty * COALESCE(adj.harga_diskon, pp.hrg_satuan))) + COALESCE(adj.tax_diskon, 0), 0) AS grand_total_diskon
            ' : '
                pp.qty AS qty,
                pp.satuan AS satuan,
                pp.hrg_satuan AS harga_satuan,
                (pp.qty * pp.hrg_satuan) AS harga_total,
                pp.hrg_satuan AS harga,
                pp.hrg_satuan AS harga_diskon,
                (pp.qty * pp.hrg_satuan) AS harga_total_diskon,
                0 AS tax_percent,
                0 AS tax,
                0 AS tax_diskon,
                (pp.qty * pp.hrg_satuan) AS grand_total,
                (pp.qty * pp.hrg_satuan) AS grand_total_diskon
            ') . '
        ');
        $this->db->from('tb_pre_po pp');
        $this->db->join('tbpo_barang mb', 'mb.kode_barang = pp.kd_barang', 'left');
        if ($hasAdjustmentTable) {
            $this->db->join('tb_pre_po_invoice_adjustment adj', 'adj.kd_po = pp.kd_po AND adj.kd_barang = pp.kd_barang', 'left');
        }
        $this->db->where('pp.kd_po', $kd_po);
        $this->db->order_by('pp.id_pre_po', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_pre_po_invoice_adjustment_summary($kd_po, $rows = null)
    {
        if ($rows === null) {
            $rows = $this->get_pre_po_invoice_adjustment($kd_po);
        }

        $summary = [
            'total_harga' => 0,
            'total_harga_diskon' => 0,
            'tax_percent' => 0,
            'tax' => 0,
            'tax_diskon' => 0,
            'grand_total' => 0,
            'grand_total_diskon' => 0
        ];

        foreach ($rows as $row) {
            $summary['total_harga'] += (float) ($row['harga_total'] ?? 0);
            $summary['total_harga_diskon'] += (float) ($row['harga_total_diskon'] ?? 0);
            if ((float) ($row['tax_percent'] ?? 0) > 0) {
                $summary['tax_percent'] = (float) $row['tax_percent'];
            }
        }

        $summary['tax'] = ($summary['tax_percent'] / 100) * $summary['total_harga'];
        $summary['tax_diskon'] = ($summary['tax_percent'] / 100) * $summary['total_harga_diskon'];
        $summary['grand_total'] = $summary['total_harga'] + $summary['tax'];
        $summary['grand_total_diskon'] = $summary['total_harga_diskon'] + $summary['tax_diskon'];

        return $summary;
    }

    public function submit_adjustment($payload)
    {
        $row = $this->db
            ->where('kd_po', $payload['kd_po'])
            ->where('kd_barang', $payload['kd_barang'])
            ->limit(1)
            ->get('tb_pre_po')
            ->row_array();

        if (!$row) {
            return FALSE;
        }

        $qty = (float) $row['qty'];
        $hargaBaru = (float) $payload['harga_satuan_baru'];
        $totalBaru = $qty * $hargaBaru;

        $this->db->where('id_pre_po', $row['id_pre_po']);
        $updated = $this->db->update('tb_pre_po', [
            'hrg_satuan'  => $hargaBaru,
            'harga_total' => $totalBaru
        ]);

        if (!$updated) {
            return FALSE;
        }

        if (!$this->update_pre_po_status_by_kd_po($row['kd_po'], 2)) {
            return FALSE;
        }

        return $this->db->insert('tb_pre_po_adjustment_log', [
            'kd_po'              => $row['kd_po'],
            'kd_barang'          => $row['kd_barang'],
            'harga_satuan_lama'  => $row['hrg_satuan'],
            'harga_satuan_baru'  => $hargaBaru,
            'harga_total_lama'   => $row['harga_total'],
            'harga_total_baru'   => $totalBaru,
            'alasan'             => $payload['alasan'],
            'dilakukan_oleh'     => $payload['dilakukan_oleh'],
            'dilakukan_pada'     => date('Y-m-d H:i:s')
        ]);
    }

    public function get_history_adjustment($kd_po, $kd_barang = '')
    {
        $this->db->from('tb_pre_po_adjustment_log');
        $this->db->where('kd_po', $kd_po);

        if ($kd_barang !== '') {
            $this->db->where('kd_barang', $kd_barang);
        }

        $this->db->order_by('dilakukan_pada', 'DESC');
        if ($this->db->field_exists('id', 'tb_pre_po_adjustment_log')) {
            $this->db->order_by('id', 'DESC');
        }

        return $this->db->get()->result_array();
    }

    public function get_history_invoice($kd_po)
    {
        $this->db->where('kd_po', $kd_po);
        $this->db->order_by('dilakukan_pada', 'DESC');
        if ($this->db->field_exists('id', 'tb_lpb_log')) {
            $this->db->order_by('id', 'DESC');
        }

        return $this->db->get('tb_lpb_log')->result_array();
    }

    public function get_history_diskon_sync($kd_po)
    {
        if (!$this->db->table_exists('tb_pre_po_diskon_history')) {
            return [];
        }

        $this->db->where('kd_po', $kd_po);
        $this->db->order_by('id_diskon_source', 'ASC');
        $this->db->order_by('id', 'ASC');

        return $this->db->get('tb_pre_po_diskon_history')->result_array();
    }

    public function update_invoice_lpb($payload)
    {
        $row = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$row) {
            return FALSE;
        }

        $updateData = [
            'no_invoice' => $payload['no_invoice'],
        ];

        if ($this->db->field_exists('tanggal_invoice', 'tb_lpb')) {
            $updateData['tanggal_invoice'] = $this->_normalizeDate($payload['tanggal_invoice'] ?? '');
        }

        $updated = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->update('tb_lpb', $updateData);

        if (!$updated) {
            return FALSE;
        }

        $logged = $this->insert_lpb_activity_log([
            'id_lpb'         => (int) $payload['id_lpb'],
            'kd_po'          => $row['kd_po'],
            'no_invoice'     => $payload['no_invoice'],
            'action_type'    => 'UPDATE_INVOICE',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'data_before'    => [
                'no_invoice' => $row['no_invoice'] ?? '',
                'tanggal_invoice' => $row['tanggal_invoice'] ?? ''
            ],
            'data_after'     => [
                'no_invoice' => $payload['no_invoice'],
                'tanggal_invoice' => $payload['tanggal_invoice'] ?? ''
            ],
            'keterangan'     => $this->describe_lpb_changes('Update invoice LPB', [
                'no_invoice' => 'No Invoice',
                'tanggal_invoice' => 'Tanggal Invoice'
            ], [
                'no_invoice' => $row['no_invoice'] ?? '',
                'tanggal_invoice' => $row['tanggal_invoice'] ?? ''
            ], [
                'no_invoice' => $payload['no_invoice'],
                'tanggal_invoice' => $payload['tanggal_invoice'] ?? ''
            ]),
            'dilakukan_oleh' => $payload['dilakukan_oleh']
        ]);

        if (!$logged) {
            return FALSE;
        }

        if (!$this->recalculate_lpb_status((int) $payload['id_lpb'])) {
            return FALSE;
        }

        return $this->update_pre_po_status_by_kd_po($row['kd_po'], 2);
    }

    public function split_lpb_multiple_invoice($payload)
    {
        $idLpb = (int) ($payload['id_lpb'] ?? 0);
        $splits = $payload['splits'] ?? [];
        $dilakukanOleh = $payload['dilakukan_oleh'] ?? 'SYSTEM';
        $tolerance = 0.0001;

        if ($idLpb <= 0 || !is_array($splits) || count($splits) < 2) {
            return ['status' => FALSE, 'message' => 'Data split invoice belum lengkap.'];
        }

        $header = $this->db
            ->where('id_lpb', $idLpb)
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$header) {
            return ['status' => FALSE, 'message' => 'Data LPB tidak ditemukan.'];
        }

        $details = $this->db
            ->where('id_lpb', $idLpb)
            ->order_by('id_detail_lpb', 'ASC')
            ->get('tb_lpb_detail')
            ->result_array();

        if (empty($details)) {
            return ['status' => FALSE, 'message' => 'Detail LPB kosong.'];
        }

        $detailsById = [];
        foreach ($details as $detail) {
            $detailsById[(int) $detail['id_detail_lpb']] = $detail;
        }

        $normalizedSplits = [];
        $usedInvoices = [];
        foreach ($splits as $index => $split) {
            if (!is_array($split)) {
                return ['status' => FALSE, 'message' => 'Format split invoice tidak valid.'];
            }

            $noInvoice = trim((string) ($split['no_invoice'] ?? ''));
            $tanggalInvoice = $this->_normalizeDate($split['tanggal_invoice'] ?? '');
            if ($noInvoice === '' || $tanggalInvoice === null) {
                return ['status' => FALSE, 'message' => 'No invoice dan tanggal invoice wajib diisi.'];
            }

            $invoiceKey = strtoupper($noInvoice);
            if (isset($usedInvoices[$invoiceKey])) {
                return ['status' => FALSE, 'message' => 'No invoice tidak boleh duplikat dalam satu split.'];
            }
            $usedInvoices[$invoiceKey] = TRUE;

            $allocations = [];
            $splitTotalQty = 0;
            foreach (($split['details'] ?? []) as $allocation) {
                if (!is_array($allocation)) {
                    continue;
                }

                $idDetail = (int) ($allocation['id_detail_lpb'] ?? 0);
                $qty = (float) ($allocation['qty_diterima'] ?? 0);
                if ($idDetail <= 0 || !isset($detailsById[$idDetail])) {
                    return ['status' => FALSE, 'message' => 'Detail LPB pada split tidak valid.'];
                }
                if ($qty < 0) {
                    return ['status' => FALSE, 'message' => 'Qty split invoice tidak boleh minus.'];
                }

                $allocations[$idDetail] = $qty;
                $splitTotalQty += $qty;
            }

            if ($splitTotalQty <= 0) {
                return ['status' => FALSE, 'message' => 'Setiap invoice harus memiliki minimal satu qty barang.'];
            }

            $normalizedSplits[] = [
                'no_invoice'       => $noInvoice,
                'tanggal_invoice'  => $tanggalInvoice,
                'allocations'      => $allocations,
                'total_qty'        => $splitTotalQty
            ];
        }

        foreach ($detailsById as $idDetail => $detail) {
            $originalQty = (float) ($detail['qty_diterima'] ?? 0);
            $allocatedQty = 0;
            foreach ($normalizedSplits as $split) {
                $allocatedQty += (float) ($split['allocations'][$idDetail] ?? 0);
            }

            if (abs($allocatedQty - $originalQty) > $tolerance) {
                return [
                    'status' => FALSE,
                    'message' => 'Total qty split barang ' . ($detail['kd_barang'] ?? '') . ' harus sama dengan qty LPB awal.'
                ];
            }
        }

        $createdIds = [$idLpb];
        $firstSplit = $normalizedSplits[0];
        $this->db
            ->where('id_lpb', $idLpb)
            ->update('tb_lpb', [
                'no_invoice'       => $firstSplit['no_invoice'],
                'tanggal_invoice'  => $firstSplit['tanggal_invoice']
            ]);

        foreach ($detailsById as $idDetail => $detail) {
            $qty = (float) ($firstSplit['allocations'][$idDetail] ?? 0);
            if ($qty <= 0) {
                if ($this->db->table_exists('tb_lpb_batch')) {
                    $this->db->where('id_detail_lpb', $idDetail)->delete('tb_lpb_batch');
                }
                $this->db->where('id_detail_lpb', $idDetail)->delete('tb_lpb_detail');
                continue;
            }

            $hargaSatuan = (float) ($detail['harga_satuan'] ?? 0);
            $originalQty = (float) ($detail['qty_diterima'] ?? 0);
            if ($hargaSatuan <= 0 && $originalQty > 0) {
                $hargaSatuan = (float) ($detail['total_harga'] ?? 0) / $originalQty;
            }
            $updateDetail = [
                'qty_diterima'       => $qty,
                'total_harga'        => $qty * $hargaSatuan
            ];
            $this->db
                ->where('id_detail_lpb', $idDetail)
                ->update('tb_lpb_detail', $updateDetail);

            $this->replace_lpb_detail_batch($idDetail, $detail, $qty);
        }

        $this->insert_lpb_activity_log([
            'id_lpb'         => $idLpb,
            'kd_po'          => $header['kd_po'] ?? '',
            'no_invoice'     => $firstSplit['no_invoice'],
            'action_type'    => 'SPLIT_LPB_MULTIPLE_INVOICE',
            'status_before'  => $this->lpb_status_label($header['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($header['status_lpb'] ?? 1),
            'data_before'    => [
                'id_lpb' => $idLpb,
                'no_invoice' => $header['no_invoice'] ?? '',
                'details' => $details
            ],
            'data_after'     => [
                'id_lpb' => $idLpb,
                'no_invoice' => $firstSplit['no_invoice'],
                'tanggal_invoice' => $firstSplit['tanggal_invoice'],
                'total_qty' => $firstSplit['total_qty']
            ],
            'keterangan'     => 'LPB dipecah menjadi multiple invoice. Invoice pertama tetap pada LPB asal.',
            'dilakukan_oleh' => $dilakukanOleh
        ]);

        for ($i = 1; $i < count($normalizedSplits); $i++) {
            $split = $normalizedSplits[$i];
            $newHeader = [
                'kd_po'       => $header['kd_po'] ?? null,
                'nosj'        => $header['nosj'] ?? '',
                'tgl_sj'      => $this->_normalizeDate($header['tgl_sj'] ?? ''),
                'no_po'       => $header['no_po'] ?? null,
                'no_invoice'  => $split['no_invoice'],
                'gudang_id'   => $header['gudang_id'] ?? null,
                'keterangan'  => $header['keterangan'] ?? null,
                'input_at'    => date('Y-m-d H:i:s')
            ];

            if ($this->db->field_exists('tanggal_invoice', 'tb_lpb')) {
                $newHeader['tanggal_invoice'] = $split['tanggal_invoice'];
            }
            if ($this->db->field_exists('kode_faktur_pajak', 'tb_lpb')) {
                $newHeader['kode_faktur_pajak'] = null;
            }
            if ($this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')) {
                $newHeader['tanggal_faktur_pajak'] = null;
            }
            if ($this->db->field_exists('jenis_lpb', 'tb_lpb')) {
                $newHeader['jenis_lpb'] = $header['jenis_lpb'] ?? null;
            }
            if ($this->db->field_exists('nomor_lpb', 'tb_lpb')) {
                $newHeader['nomor_lpb'] = $header['nomor_lpb'] ?? null;
            }
            if ($this->db->field_exists('status_lpb', 'tb_lpb')) {
                $newHeader['status_lpb'] = (int) ($header['status_lpb'] ?? 1);
            }
            if ($this->db->field_exists('checker_name', 'tb_lpb')) {
                $newHeader['checker_name'] = $header['checker_name'] ?? null;
            }
            if ($this->db->field_exists('checker_by', 'tb_lpb')) {
                $newHeader['checker_by'] = $header['checker_by'] ?? null;
            }
            if ($this->db->field_exists('checker_at', 'tb_lpb')) {
                $newHeader['checker_at'] = $header['checker_at'] ?? null;
            }

            $this->db->insert('tb_lpb', $newHeader);
            $newIdLpb = (int) $this->db->insert_id();
            if ($newIdLpb <= 0) {
                return ['status' => FALSE, 'message' => 'Header LPB hasil split gagal dibuat.'];
            }

            $createdIds[] = $newIdLpb;
            foreach ($detailsById as $idDetail => $detail) {
                $qty = (float) ($split['allocations'][$idDetail] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $hargaSatuan = (float) ($detail['harga_satuan'] ?? 0);
                $originalQty = (float) ($detail['qty_diterima'] ?? 0);
                if ($hargaSatuan <= 0 && $originalQty > 0) {
                    $hargaSatuan = (float) ($detail['total_harga'] ?? 0) / $originalQty;
                }
                $newDetail = [
                    'id_lpb'          => $newIdLpb,
                    'kd_barang'       => $detail['kd_barang'] ?? '',
                    'qty_diterima'    => $qty,
                    'no_lot'          => $detail['no_lot'] ?? '',
                    'expired_date'    => $this->_normalizeDate($detail['expired_date'] ?? ''),
                    'input_at'        => date('Y-m-d H:i:s')
                ];

                foreach (['harga_satuan_sebelumnya', 'total_harga_sebelumnya', 'harga_satuan'] as $field) {
                    if ($this->db->field_exists($field, 'tb_lpb_detail')) {
                        $newDetail[$field] = $detail[$field] ?? 0;
                    }
                }
                if ($this->db->field_exists('total_harga', 'tb_lpb_detail')) {
                    $newDetail['total_harga'] = $qty * $hargaSatuan;
                }
                if ($this->db->field_exists('harga_update_by', 'tb_lpb_detail')) {
                    $newDetail['harga_update_by'] = $detail['harga_update_by'] ?? null;
                }
                if ($this->db->field_exists('harga_update_at', 'tb_lpb_detail')) {
                    $newDetail['harga_update_at'] = $detail['harga_update_at'] ?? null;
                }
                if ($this->db->field_exists('harga_verified_by', 'tb_lpb_detail')) {
                    $newDetail['harga_verified_by'] = $detail['harga_verified_by'] ?? null;
                }
                if ($this->db->field_exists('harga_verified_at', 'tb_lpb_detail')) {
                    $newDetail['harga_verified_at'] = $detail['harga_verified_at'] ?? null;
                }

                $this->db->insert('tb_lpb_detail', $newDetail);
                $newIdDetail = (int) $this->db->insert_id();
                if ($newIdDetail <= 0) {
                    return ['status' => FALSE, 'message' => 'Detail LPB hasil split gagal dibuat.'];
                }

                $this->replace_lpb_detail_batch($newIdDetail, $detail, $qty);
            }

            $this->insert_lpb_activity_log([
                'id_lpb'         => $newIdLpb,
                'kd_po'          => $newHeader['kd_po'] ?? '',
                'no_invoice'     => $newHeader['no_invoice'] ?? '',
                'action_type'    => 'CREATE_LPB_SPLIT_INVOICE',
                'status_before'  => null,
                'status_after'   => $this->lpb_status_label($newHeader['status_lpb'] ?? 1),
                'data_before'    => [
                    'source_id_lpb' => $idLpb
                ],
                'data_after'     => [
                    'id_lpb' => $newIdLpb,
                    'nomor_lpb' => $newHeader['nomor_lpb'] ?? '',
                    'no_invoice' => $newHeader['no_invoice'] ?? '',
                    'tanggal_invoice' => $newHeader['tanggal_invoice'] ?? '',
                    'total_qty' => $split['total_qty'],
                    'checker_name' => $newHeader['checker_name'] ?? ''
                ],
                'keterangan'     => 'LPB baru dibuat dari pecah multiple invoice LPB #' . $idLpb . '.',
                'dilakukan_oleh' => $dilakukanOleh,
                'checker_name'   => $newHeader['checker_name'] ?? null,
                'checker_by'     => $newHeader['checker_by'] ?? null
            ]);
        }

        return [
            'status' => TRUE,
            'id_lpb' => $createdIds
        ];
    }

    private function replace_lpb_detail_batch($idDetailLpb, $detail, $qty)
    {
        if (!$this->db->table_exists('tb_lpb_batch')) {
            return TRUE;
        }

        $this->db->where('id_detail_lpb', (int) $idDetailLpb)->delete('tb_lpb_batch');

        return $this->db->insert('tb_lpb_batch', [
            'id_detail_lpb' => (int) $idDetailLpb,
            'no_lot'        => $detail['no_lot'] ?? '',
            'expired_date'  => $this->_normalizeDate($detail['expired_date'] ?? ''),
            'qty'           => (float) $qty
        ]);
    }

    public function update_faktur_pajak_lpb($payload)
    {
        $row = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$row) {
            return FALSE;
        }

        $updateData = [];

        if ($this->db->field_exists('kode_faktur_pajak', 'tb_lpb')) {
            $updateData['kode_faktur_pajak'] = $payload['kode_faktur_pajak'];
        }

        if ($this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')) {
            $updateData['tanggal_faktur_pajak'] = $this->_normalizeDate($payload['tanggal_faktur_pajak'] ?? '');
        }

        if (empty($updateData)) {
            return FALSE;
        }

        $updated = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->update('tb_lpb', $updateData);

        if (!$updated) {
            return FALSE;
        }

        $logged = $this->insert_lpb_activity_log([
            'id_lpb'         => (int) $payload['id_lpb'],
            'kd_po'          => $row['kd_po'],
            'no_invoice'     => $row['no_invoice'] ?? '',
            'action_type'    => 'UPDATE_FAKTUR_PAJAK',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'data_before'    => [
                'kode_faktur_pajak' => $row['kode_faktur_pajak'] ?? '',
                'tanggal_faktur_pajak' => $row['tanggal_faktur_pajak'] ?? ''
            ],
            'data_after'     => [
                'kode_faktur_pajak' => $payload['kode_faktur_pajak'],
                'tanggal_faktur_pajak' => $payload['tanggal_faktur_pajak'] ?? ''
            ],
            'keterangan'     => $this->describe_lpb_changes('Update faktur pajak LPB', [
                'kode_faktur_pajak' => 'Kode Faktur Pajak',
                'tanggal_faktur_pajak' => 'Tanggal Faktur Pajak'
            ], [
                'kode_faktur_pajak' => $row['kode_faktur_pajak'] ?? '',
                'tanggal_faktur_pajak' => $row['tanggal_faktur_pajak'] ?? ''
            ], [
                'kode_faktur_pajak' => $payload['kode_faktur_pajak'],
                'tanggal_faktur_pajak' => $payload['tanggal_faktur_pajak'] ?? ''
            ]),
            'dilakukan_oleh' => $payload['dilakukan_oleh']
        ]);

        if (!$logged) {
            return FALSE;
        }

        return $this->recalculate_lpb_status((int) $payload['id_lpb']);
    }

    public function ensure_lpb_invoice_faktur_columns()
    {
        if (!$this->db->table_exists('tb_lpb')) {
            return FALSE;
        }

        $this->load->dbforge();
        $fields = [];

        if (!$this->db->field_exists('tanggal_invoice', 'tb_lpb')) {
            $fields['tanggal_invoice'] = [
                'type' => 'DATE',
                'null' => TRUE,
                'after' => 'no_invoice'
            ];
        }

        if (!$this->db->field_exists('kode_faktur_pajak', 'tb_lpb')) {
            $fields['kode_faktur_pajak'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE,
                'after' => 'tanggal_invoice'
            ];
        }

        if (!$this->db->field_exists('tanggal_faktur_pajak', 'tb_lpb')) {
            $fields['tanggal_faktur_pajak'] = [
                'type' => 'DATE',
                'null' => TRUE,
                'after' => 'kode_faktur_pajak'
            ];
        }

        if (!empty($fields) && !$this->dbforge->add_column('tb_lpb', $fields)) {
            return FALSE;
        }

        return TRUE;
    }

    public function ensure_lpb_workflow_columns()
    {
        if (!$this->db->table_exists('tb_lpb')) {
            return FALSE;
        }

        $this->load->dbforge();

        if (!$this->db->field_exists('status_lpb', 'tb_lpb')) {
            if (!$this->dbforge->add_column('tb_lpb', [
                'status_lpb' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                    'null'       => FALSE,
                    'after'      => $this->db->field_exists('nomor_lpb', 'tb_lpb') ? 'nomor_lpb' : 'input_at'
                ]
            ])) {
                return FALSE;
            }
        }

        if (!$this->db->field_exists('checker_name', 'tb_lpb')) {
            if (!$this->dbforge->add_column('tb_lpb', [
                'checker_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => TRUE,
                    'after'      => $this->db->field_exists('keterangan', 'tb_lpb') ? 'keterangan' : 'gudang_id'
                ]
            ])) {
                return FALSE;
            }
        }

        if (!$this->db->field_exists('checker_by', 'tb_lpb')) {
            if (!$this->dbforge->add_column('tb_lpb', [
                'checker_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => TRUE,
                    'after'      => 'checker_name'
                ]
            ])) {
                return FALSE;
            }
        }

        if (!$this->db->field_exists('checker_at', 'tb_lpb')) {
            if (!$this->dbforge->add_column('tb_lpb', [
                'checker_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'after' => 'checker_by'
                ]
            ])) {
                return FALSE;
            }
        }

        if (!$this->db->table_exists('tb_lpb_log')) {
            return TRUE;
        }

        if ($this->db->field_exists('action_type', 'tb_lpb_log')) {
            $this->db->query("ALTER TABLE `tb_lpb_log` MODIFY `action_type` varchar(50) NOT NULL");
        }

        if (!$this->db->field_exists('id_lpb', 'tb_lpb_log')) {
            if (!$this->dbforge->add_column('tb_lpb_log', [
                'id_lpb' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => TRUE,
                'after'      => $this->db->field_exists('id_log', 'tb_lpb_log') ? 'id_log' : 'kd_po'
                ]
            ])) {
                return FALSE;
            }
        }
        if (!$this->db->field_exists('status_before', 'tb_lpb_log')) {
            if (!$this->dbforge->add_column('tb_lpb_log', [
                'status_before' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => TRUE,
                'after'      => 'action_type'
                ]
            ])) {
                return FALSE;
            }
        }
        if (!$this->db->field_exists('status_after', 'tb_lpb_log')) {
            if (!$this->dbforge->add_column('tb_lpb_log', [
                'status_after' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => TRUE,
                'after'      => 'status_before'
                ]
            ])) {
                return FALSE;
            }
        }
        if (!$this->db->field_exists('data_before', 'tb_lpb_log')) {
            if (!$this->dbforge->add_column('tb_lpb_log', [
                'data_before' => [
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'status_after'
                ]
            ])) {
                return FALSE;
            }
        }
        if (!$this->db->field_exists('data_after', 'tb_lpb_log')) {
            if (!$this->dbforge->add_column('tb_lpb_log', [
                'data_after' => [
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'data_before'
                ]
            ])) {
                return FALSE;
            }
        }

        if (!$this->db->field_exists('checker_name', 'tb_lpb_log')) {
            if (!$this->dbforge->add_column('tb_lpb_log', [
                'checker_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => TRUE,
                    'after'      => 'dilakukan_oleh'
                ]
            ])) {
                return FALSE;
            }
        }

        if (!$this->db->field_exists('checker_by', 'tb_lpb_log')) {
            if (!$this->dbforge->add_column('tb_lpb_log', [
                'checker_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => TRUE,
                    'after'      => 'checker_name'
                ]
            ])) {
                return FALSE;
            }
        }

        return TRUE;
    }

    private function lpb_status_label($status)
    {
        return ((int) $status === 0) ? 'UNPOST' : 'POST';
    }

    private function describe_lpb_changes($subject, array $labels, array $before, array $after)
    {
        $changes = [];

        foreach ($labels as $field => $label) {
            $beforeValue = array_key_exists($field, $before) ? $before[$field] : null;
            $afterValue = array_key_exists($field, $after) ? $after[$field] : null;

            if ((string) $beforeValue === (string) $afterValue) {
                continue;
            }

            $changes[] = $label . ' dari "' . $this->format_lpb_log_value($beforeValue) . '" menjadi "' . $this->format_lpb_log_value($afterValue) . '"';
        }

        if (empty($changes)) {
            return $subject . ': tidak ada perubahan nilai.';
        }

        return $subject . ': ' . implode('; ', $changes) . '.';
    }

    private function format_lpb_log_value($value)
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_float($value) || is_int($value) || is_numeric($value)) {
            return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
        }

        return (string) $value;
    }

    public function insert_lpb_activity_log($payload)
    {
        if (!$this->db->table_exists('tb_lpb_log')) {
            return TRUE;
        }

        $checkerContext = [
            'checker_name' => $payload['checker_name'] ?? null,
            'checker_by' => $payload['checker_by'] ?? null
        ];
        $idLpbForContext = (int) ($payload['id_lpb'] ?? 0);
        if ($idLpbForContext > 0
            && ($checkerContext['checker_name'] === null || $checkerContext['checker_by'] === null)
            && $this->db->table_exists('tb_lpb')
            && ($this->db->field_exists('checker_name', 'tb_lpb') || $this->db->field_exists('checker_by', 'tb_lpb'))) {
            $select = [];
            if ($this->db->field_exists('checker_name', 'tb_lpb')) {
                $select[] = 'checker_name';
            }
            if ($this->db->field_exists('checker_by', 'tb_lpb')) {
                $select[] = 'checker_by';
            }
            $header = $this->db
                ->select(implode(',', $select))
                ->where('id_lpb', $idLpbForContext)
                ->limit(1)
                ->get('tb_lpb')
                ->row_array();
            if ($header) {
                if ($checkerContext['checker_name'] === null && array_key_exists('checker_name', $header)) {
                    $checkerContext['checker_name'] = $header['checker_name'];
                }
                if ($checkerContext['checker_by'] === null && array_key_exists('checker_by', $header)) {
                    $checkerContext['checker_by'] = $header['checker_by'];
                }
            }
        }

        $data = [
            'kd_po'          => $payload['kd_po'] ?? '',
            'no_invoice'     => $payload['no_invoice'] ?? '',
            'action_type'    => $payload['action_type'] ?? 'UPDATE_LPB',
            'keterangan'     => $payload['keterangan'] ?? '',
            'dilakukan_oleh' => $payload['dilakukan_oleh'] ?? 'SYSTEM',
            'dilakukan_pada' => date('Y-m-d H:i:s')
        ];

        if ($this->db->field_exists('id_lpb', 'tb_lpb_log')) {
            $data['id_lpb'] = (int) ($payload['id_lpb'] ?? 0);
        }
        if ($this->db->field_exists('status_before', 'tb_lpb_log')) {
            $data['status_before'] = $payload['status_before'] ?? null;
        }
        if ($this->db->field_exists('status_after', 'tb_lpb_log')) {
            $data['status_after'] = $payload['status_after'] ?? null;
        }
        if ($this->db->field_exists('data_before', 'tb_lpb_log')) {
            $data['data_before'] = isset($payload['data_before']) ? json_encode($payload['data_before']) : null;
        }
        if ($this->db->field_exists('data_after', 'tb_lpb_log')) {
            $data['data_after'] = isset($payload['data_after']) ? json_encode($payload['data_after']) : null;
        }
        if ($this->db->field_exists('checker_name', 'tb_lpb_log')) {
            $data['checker_name'] = $checkerContext['checker_name'];
        }
        if ($this->db->field_exists('checker_by', 'tb_lpb_log')) {
            $data['checker_by'] = $checkerContext['checker_by'];
        }

        return $this->db->insert('tb_lpb_log', $data);
    }

    public function get_lpb_activity_logs($idLpb)
    {
        if (!$this->db->table_exists('tb_lpb_log')) {
            return [];
        }

        $header = $this->db
            ->where('id_lpb', (int) $idLpb)
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$header) {
            return [];
        }

        if ($this->db->field_exists('id_lpb', 'tb_lpb_log')) {
            $this->db->where('id_lpb', (int) $idLpb);
        } else {
            $this->db->where('kd_po', $header['kd_po']);
        }

        $this->db->order_by('dilakukan_pada', 'DESC');
        if ($this->db->field_exists('id_log', 'tb_lpb_log')) {
            $this->db->order_by('id_log', 'DESC');
        } elseif ($this->db->field_exists('id', 'tb_lpb_log')) {
            $this->db->order_by('id', 'DESC');
        }

        return $this->db->get('tb_lpb_log')->result_array();
    }

    public function update_lpb_status($idLpb, $status, $dilakukanOleh, $keterangan = '')
    {
        $row = $this->db
            ->where('id_lpb', (int) $idLpb)
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$row || !$this->db->field_exists('status_lpb', 'tb_lpb')) {
            return FALSE;
        }

        $status = (int) $status === 0 ? 0 : 1;
        $updated = $this->db
            ->where('id_lpb', (int) $idLpb)
            ->update('tb_lpb', ['status_lpb' => $status]);

        if (!$updated) {
            return FALSE;
        }

        $after = array_merge($row, ['status_lpb' => $status]);

        if (!$this->insert_lpb_activity_log([
            'id_lpb'         => (int) $idLpb,
            'kd_po'          => $row['kd_po'] ?? '',
            'no_invoice'     => $row['no_invoice'] ?? '',
            'action_type'    => $status === 0 ? 'UNPOST_LPB' : 'POST_LPB',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($status),
            'data_before'    => ['status_lpb' => $row['status_lpb'] ?? 1],
            'data_after'     => ['status_lpb' => $status],
            'keterangan'     => $this->describe_lpb_changes($status === 0 ? 'UNPOST LPB' : 'Rekam LPB', [
                'status_lpb' => 'Status LPB'
            ], [
                'status_lpb' => $this->lpb_status_label($row['status_lpb'] ?? 1)
            ], [
                'status_lpb' => $this->lpb_status_label($status)
            ]) . ($keterangan !== '' ? ' Keterangan: ' . $keterangan : ''),
            'dilakukan_oleh' => $dilakukanOleh
        ])) {
            return FALSE;
        }

        return $after;
    }

    public function update_lpb_identity($payload)
    {
        $row = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$row) {
            return FALSE;
        }

        $updateData = [];
        if ($this->db->field_exists('nomor_lpb', 'tb_lpb')) {
            $updateData['nomor_lpb'] = trim((string) ($payload['nomor_lpb'] ?? ''));
        }
        if ($this->db->field_exists('jenis_lpb', 'tb_lpb')) {
            $updateData['jenis_lpb'] = $this->normalize_lpb_type($payload['jenis_lpb'] ?? '');
        }

        if (empty($updateData)) {
            return FALSE;
        }

        $updated = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->update('tb_lpb', $updateData);

        if (!$updated) {
            return FALSE;
        }

        $after = array_merge($row, $updateData);
        if (!$this->insert_lpb_activity_log([
            'id_lpb'         => (int) $payload['id_lpb'],
            'kd_po'          => $row['kd_po'] ?? '',
            'no_invoice'     => $row['no_invoice'] ?? '',
            'action_type'    => 'UPDATE_LPB_IDENTITY',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($after['status_lpb'] ?? 1),
            'data_before'    => [
                'nomor_lpb' => $row['nomor_lpb'] ?? '',
                'jenis_lpb' => $row['jenis_lpb'] ?? ''
            ],
            'data_after'     => [
                'nomor_lpb' => $after['nomor_lpb'] ?? '',
                'jenis_lpb' => $after['jenis_lpb'] ?? ''
            ],
            'keterangan'     => $this->describe_lpb_changes('Update nomor dan jenis LPB', [
                'nomor_lpb' => 'Nomor LPB',
                'jenis_lpb' => 'Jenis LPB'
            ], [
                'nomor_lpb' => $row['nomor_lpb'] ?? '',
                'jenis_lpb' => $row['jenis_lpb'] ?? ''
            ], [
                'nomor_lpb' => $after['nomor_lpb'] ?? '',
                'jenis_lpb' => $after['jenis_lpb'] ?? ''
            ]),
            'dilakukan_oleh' => $payload['dilakukan_oleh'] ?? 'SYSTEM'
        ])) {
            return FALSE;
        }

        return $after;
    }

    public function update_lpb_sj($payload)
    {
        $row = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->limit(1)
            ->get('tb_lpb')
            ->row_array();

        if (!$row) {
            return FALSE;
        }

        $updateData = [
            'nosj'   => trim((string) ($payload['nosj'] ?? '')),
            'tgl_sj' => $this->_normalizeDate($payload['tgl_sj'] ?? '')
        ];

        $updated = $this->db
            ->where('id_lpb', (int) $payload['id_lpb'])
            ->update('tb_lpb', $updateData);

        if (!$updated) {
            return FALSE;
        }

        $after = array_merge($row, $updateData);
        if (!$this->insert_lpb_activity_log([
            'id_lpb'         => (int) $payload['id_lpb'],
            'kd_po'          => $row['kd_po'] ?? '',
            'no_invoice'     => $row['no_invoice'] ?? '',
            'action_type'    => 'UPDATE_LPB_SJ',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($after['status_lpb'] ?? 1),
            'data_before'    => [
                'nosj' => $row['nosj'] ?? '',
                'tgl_sj' => $row['tgl_sj'] ?? ''
            ],
            'data_after'     => [
                'nosj' => $after['nosj'] ?? '',
                'tgl_sj' => $after['tgl_sj'] ?? ''
            ],
            'keterangan'     => $this->describe_lpb_changes('Update nomor SJ dan tanggal SJ', [
                'nosj' => 'Nomor SJ',
                'tgl_sj' => 'Tanggal SJ'
            ], [
                'nosj' => $row['nosj'] ?? '',
                'tgl_sj' => $row['tgl_sj'] ?? ''
            ], [
                'nosj' => $after['nosj'] ?? '',
                'tgl_sj' => $after['tgl_sj'] ?? ''
            ]),
            'dilakukan_oleh' => $payload['dilakukan_oleh'] ?? 'SYSTEM'
        ])) {
            return FALSE;
        }

        return $after;
    }

    public function update_lpb_detail_receipt($payload)
    {
        $sql = "SELECT
                d.id_detail_lpb,
                d.id_lpb,
                d.kd_barang,
                d.no_lot,
                d.expired_date,
                d.qty_diterima,
                h.kd_po,
                h.no_invoice,
                h.status_lpb
            FROM tb_lpb_detail d
            INNER JOIN tb_lpb h ON h.id_lpb = d.id_lpb
            WHERE d.id_detail_lpb = ?
            LIMIT 1";
        $row = $this->db->query($sql, [(int) $payload['id_detail_lpb']])->row_array();

        if (!$row) {
            return FALSE;
        }

        $updateData = [
            'no_lot'       => trim((string) ($payload['no_lot'] ?? '')),
            'expired_date' => $this->_normalizeDate($payload['expired_date'] ?? ''),
            'qty_diterima' => (float) ($payload['qty_diterima'] ?? 0)
        ];

        $updated = $this->db
            ->where('id_detail_lpb', (int) $payload['id_detail_lpb'])
            ->update('tb_lpb_detail', $updateData);

        if (!$updated) {
            return FALSE;
        }

        if ($this->db->table_exists('tb_lpb_batch')) {
            $this->db
                ->where('id_detail_lpb', (int) $payload['id_detail_lpb'])
                ->update('tb_lpb_batch', [
                    'no_lot'       => $updateData['no_lot'],
                    'expired_date' => $updateData['expired_date'],
                    'qty'          => $updateData['qty_diterima']
                ]);
        }

        $after = array_merge($row, $updateData);
        if (!$this->insert_lpb_activity_log([
            'id_lpb'         => (int) $row['id_lpb'],
            'kd_po'          => $row['kd_po'] ?? '',
            'no_invoice'     => $row['no_invoice'] ?? '',
            'action_type'    => 'UPDATE_LPB_DETAIL',
            'status_before'  => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'status_after'   => $this->lpb_status_label($row['status_lpb'] ?? 1),
            'data_before'    => [
                'id_detail_lpb' => $row['id_detail_lpb'],
                'kd_barang' => $row['kd_barang'],
                'no_lot' => $row['no_lot'],
                'expired_date' => $row['expired_date'],
                'qty_diterima' => $row['qty_diterima']
            ],
            'data_after'     => [
                'id_detail_lpb' => $after['id_detail_lpb'],
                'kd_barang' => $after['kd_barang'],
                'no_lot' => $after['no_lot'],
                'expired_date' => $after['expired_date'],
                'qty_diterima' => $after['qty_diterima']
            ],
            'keterangan'     => $this->describe_lpb_changes('Update detail LPB barang ' . ($row['kd_barang'] ?? ''), [
                'no_lot' => 'No Lot',
                'expired_date' => 'Expired Date',
                'qty_diterima' => 'Qty Diterima'
            ], [
                'no_lot' => $row['no_lot'],
                'expired_date' => $row['expired_date'],
                'qty_diterima' => $row['qty_diterima']
            ], [
                'no_lot' => $after['no_lot'],
                'expired_date' => $after['expired_date'],
                'qty_diterima' => $after['qty_diterima']
            ]),
            'dilakukan_oleh' => $payload['dilakukan_oleh'] ?? 'SYSTEM'
        ])) {
            return FALSE;
        }

        return $after;
    }

    private function _normalizeDate($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $raw, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        return $raw;
    }

    public function ensure_lpb_manual_schema()
    {
        $this->load->dbforge();

        if ($this->db->table_exists('tb_lpb')) {
            if (!$this->db->field_exists('source_type', 'tb_lpb')) {
                $this->dbforge->add_column('tb_lpb', [
                    'source_type' => [
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                        'null' => FALSE,
                        'default' => 'PO',
                        'after' => $this->db->field_exists('checker_at', 'tb_lpb') ? 'checker_at' : 'input_at'
                    ]
                ]);
            }

            if (!$this->db->field_exists('manual_ref_no', 'tb_lpb')) {
                $this->dbforge->add_column('tb_lpb', [
                    'manual_ref_no' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => TRUE,
                        'after' => 'source_type'
                    ]
                ]);
            }
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tb_lpb_manual_log` (
                `id_log` INT(11) NOT NULL AUTO_INCREMENT,
                `id_lpb` INT(11) DEFAULT NULL,
                `manual_ref_no` VARCHAR(50) DEFAULT NULL,
                `action_type` VARCHAR(50) NOT NULL,
                `status` VARCHAR(20) NOT NULL,
                `message` TEXT DEFAULT NULL,
                `payload` LONGTEXT DEFAULT NULL,
                `created_by` VARCHAR(100) DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (`id_log`),
                KEY `idx_lpb_manual_log_ref` (`manual_ref_no`),
                KEY `idx_lpb_manual_log_lpb` (`id_lpb`),
                KEY `idx_lpb_manual_log_status` (`status`),
                KEY `idx_lpb_manual_log_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        return TRUE;
    }

    public function generate_lpb_manual_ref()
    {
        $prefix = 'LPBM' . date('ymd');
        $row = $this->db
            ->select("MAX(CAST(SUBSTRING(manual_ref_no, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) AS max_seq", false)
            ->like('manual_ref_no', $prefix, 'after')
            ->get('tb_lpb')
            ->row_array();

        $next = ((int) ($row['max_seq'] ?? 0)) + 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function search_lpb_manual_barang($term = '', $limit = 30)
    {
        if (!$this->db->table_exists('tbpo_barang')) {
            return [];
        }

        $this->db->select('
            kode_barang,
            MAX(nama_barang) AS nama_barang,
            MAX(COALESCE(NULLIF(satuan, ""), "PCS")) AS satuan,
            MAX(COALESCE(isi, 0)) AS isi,
            MAX(COALESCE(kemasan, 0)) AS kemasan
        ', false);
        $this->db->from('tbpo_barang');

        if ($this->db->field_exists('is_active', 'tbpo_barang')) {
            $this->db->where('is_active', 'T');
        }

        if ($term !== '') {
            $this->db->group_start();
            $this->db->like('kode_barang', $term);
            $this->db->or_like('nama_barang', $term);
            $this->db->group_end();
        }

        $this->db->group_by('kode_barang');
        $this->db->order_by('kode_barang', 'ASC');
        $this->db->limit((int) $limit > 0 ? (int) $limit : 30);

        return $this->db->get()->result_array();
    }

    private function get_lpb_manual_barang_row($kodeBarang)
    {
        $kodeBarang = trim((string) $kodeBarang);
        if ($kodeBarang === '' || !$this->db->table_exists('tbpo_barang')) {
            return null;
        }

        $this->db->select('
            kode_barang,
            MAX(nama_barang) AS nama_barang,
            MAX(COALESCE(NULLIF(satuan, ""), "PCS")) AS satuan,
            MAX(COALESCE(isi, 0)) AS isi,
            MAX(COALESCE(kemasan, 0)) AS kemasan
        ', false);
        $this->db->from('tbpo_barang');
        $this->db->where('kode_barang', $kodeBarang);
        $this->db->group_by('kode_barang');
        $this->db->limit(1);

        return $this->db->get()->row_array();
    }

    public function validate_lpb_manual_payload(array $payload, array $detailRows)
    {
        if (trim((string) ($payload['tgl_lpb'] ?? '')) === '') {
            return ['status' => FALSE, 'message' => 'Tanggal LPB wajib diisi.'];
        }

        if (trim((string) ($payload['jenis_lpb'] ?? '')) === '') {
            return ['status' => FALSE, 'message' => 'Jenis LPB wajib dipilih.'];
        }

        if (trim((string) ($payload['gudang_id'] ?? '')) === '') {
            return ['status' => FALSE, 'message' => 'Gudang wajib dipilih.'];
        }

        if (empty($detailRows)) {
            return ['status' => FALSE, 'message' => 'Minimal harus ada 1 barang untuk LPB Manual.'];
        }

        $validatedRows = [];
        foreach ($detailRows as $index => $row) {
            $lineNo = $index + 1;
            $kodeBarang = trim((string) ($row['kd_barang'] ?? ''));
            $qty = (float) ($row['qty_diterima'] ?? 0);
            $noLot = trim((string) ($row['no_lot'] ?? ''));
            $expiredDate = trim((string) ($row['expired_date'] ?? ''));

            if ($kodeBarang === '') {
                return ['status' => FALSE, 'message' => 'Kode barang baris ' . $lineNo . ' wajib dipilih dari list barang.'];
            }
            if ($qty <= 0) {
                return ['status' => FALSE, 'message' => 'Qty diterima baris ' . $lineNo . ' harus lebih dari 0.'];
            }
            if ($noLot === '') {
                return ['status' => FALSE, 'message' => 'No lot baris ' . $lineNo . ' wajib diisi manual.'];
            }
            if ($expiredDate === '') {
                return ['status' => FALSE, 'message' => 'Expired date baris ' . $lineNo . ' wajib diisi manual.'];
            }

            $barang = $this->get_lpb_manual_barang_row($kodeBarang);
            if (!$barang) {
                return ['status' => FALSE, 'message' => 'Kode barang ' . $kodeBarang . ' tidak ditemukan di tbpo_barang.'];
            }

            $hargaSatuan = (float) ($row['harga_satuan'] ?? 0);
            $validatedRows[] = [
                'kd_barang' => $kodeBarang,
                'nama_barang' => $barang['nama_barang'] ?? '',
                'qty_diterima' => $qty,
                'satuan' => trim((string) ($row['satuan'] ?? '')) ?: ($barang['satuan'] ?? 'PCS'),
                'no_lot' => $noLot,
                'expired_date' => $this->_normalizeDate($expiredDate),
                'harga_satuan' => $hargaSatuan,
                'total_harga' => $qty * $hargaSatuan
            ];
        }

        return ['status' => TRUE, 'detail_rows' => $validatedRows];
    }

    public function create_lpb_manual(array $header, array $detailRows)
    {
        $this->ensure_lpb_manual_schema();

        $jenisLpb = $this->normalize_lpb_type($header['jenis_lpb'] ?? 'LPB CP');
        $manualRef = trim((string) ($header['manual_ref_no'] ?? ''));
        if ($manualRef === '') {
            $manualRef = $this->generate_lpb_manual_ref();
        }

        $headerInsert = [
            'kd_po' => $manualRef,
            'no_po' => $manualRef,
            'nosj' => trim((string) ($header['nosj'] ?? '')) !== '' ? trim((string) $header['nosj']) : '-',
            'tgl_sj' => $this->_normalizeDate($header['tgl_lpb'] ?? date('Y-m-d')),
            'no_invoice' => trim((string) ($header['no_invoice'] ?? '')) !== '' ? trim((string) $header['no_invoice']) : '-',
            'gudang_id' => (int) ($header['gudang_id'] ?? 0),
            'keterangan' => trim((string) ($header['keterangan'] ?? '')),
            'input_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->field_exists('jenis_lpb', 'tb_lpb')) {
            $headerInsert['jenis_lpb'] = $jenisLpb;
        }
        if ($this->db->field_exists('nomor_lpb', 'tb_lpb')) {
            $headerInsert['nomor_lpb'] = $this->generate_lpb_number($jenisLpb);
        }
        if ($this->db->field_exists('status_lpb', 'tb_lpb')) {
            $headerInsert['status_lpb'] = 1;
        }
        if ($this->db->field_exists('source_type', 'tb_lpb')) {
            $headerInsert['source_type'] = 'MANUAL';
        }
        if ($this->db->field_exists('manual_ref_no', 'tb_lpb')) {
            $headerInsert['manual_ref_no'] = $manualRef;
        }
        if ($this->db->field_exists('checker_name', 'tb_lpb')) {
            $headerInsert['checker_name'] = trim((string) ($header['checker_name'] ?? '')) ?: ($header['dilakukan_oleh'] ?? 'SYSTEM');
        }
        if ($this->db->field_exists('checker_by', 'tb_lpb')) {
            $headerInsert['checker_by'] = trim((string) ($header['checker_by'] ?? ''));
        }
        if ($this->db->field_exists('checker_at', 'tb_lpb')) {
            $headerInsert['checker_at'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('tb_lpb', $headerInsert);
        $idLpb = (int) $this->db->insert_id();
        if ($idLpb <= 0) {
            return FALSE;
        }

        foreach ($detailRows as $row) {
            $hargaSatuan = (float) ($row['harga_satuan'] ?? 0);
            $totalHarga = (float) ($row['total_harga'] ?? 0);
            $detailInsert = [
                'id_lpb' => $idLpb,
                'kd_barang' => $row['kd_barang'],
                'qty_diterima' => (float) $row['qty_diterima'],
                'no_lot' => $row['no_lot'],
                'expired_date' => $this->_normalizeDate($row['expired_date'] ?? ''),
                'input_at' => date('Y-m-d H:i:s')
            ];

            if ($this->db->field_exists('harga_satuan', 'tb_lpb_detail')) {
                $detailInsert['harga_satuan'] = $hargaSatuan;
            }
            if ($this->db->field_exists('total_harga', 'tb_lpb_detail')) {
                $detailInsert['total_harga'] = $totalHarga;
            }
            if ($this->db->field_exists('harga_verified_by', 'tb_lpb_detail')) {
                $detailInsert['harga_verified_by'] = $header['dilakukan_oleh'] ?? 'SYSTEM';
            }
            if ($this->db->field_exists('harga_verified_at', 'tb_lpb_detail')) {
                $detailInsert['harga_verified_at'] = date('Y-m-d H:i:s');
            }

            $this->db->insert('tb_lpb_detail', $detailInsert);
            $idDetailLpb = (int) $this->db->insert_id();
            if ($idDetailLpb <= 0) {
                return FALSE;
            }

            if ($this->db->table_exists('tb_lpb_batch')) {
                $this->db->insert('tb_lpb_batch', [
                    'id_detail_lpb' => $idDetailLpb,
                    'no_lot' => $row['no_lot'],
                    'expired_date' => $this->_normalizeDate($row['expired_date'] ?? ''),
                    'qty' => (float) $row['qty_diterima']
                ]);
            }

            $this->upsert_lpb_manual_stock($headerInsert['gudang_id'], $manualRef, $row);
        }

        $this->insert_lpb_activity_log([
            'id_lpb' => $idLpb,
            'kd_po' => $manualRef,
            'no_invoice' => $headerInsert['no_invoice'] ?? '-',
            'action_type' => 'CREATE_LPB_MANUAL',
            'status_before' => null,
            'status_after' => 'POST',
            'data_before' => null,
            'data_after' => [
                'id_lpb' => $idLpb,
                'manual_ref_no' => $manualRef,
                'nomor_lpb' => $headerInsert['nomor_lpb'] ?? '',
                'jenis_lpb' => $jenisLpb,
                'source_type' => 'MANUAL',
                'total_detail' => count($detailRows)
            ],
            'keterangan' => 'LPB Manual dibuat oleh Purchasing tanpa data PO dan langsung tercatat POST.',
            'dilakukan_oleh' => $header['dilakukan_oleh'] ?? 'SYSTEM',
            'checker_name' => $headerInsert['checker_name'] ?? null,
            'checker_by' => $headerInsert['checker_by'] ?? null
        ]);

        $this->insert_lpb_manual_system_log([
            'id_lpb' => $idLpb,
            'manual_ref_no' => $manualRef,
            'action_type' => 'CREATE_MANUAL_LPB',
            'status' => 'SUCCESS',
            'message' => 'LPB Manual tersimpan ke tb_lpb, tb_lpb_detail, batch, dan stock ledger.',
            'payload' => ['header' => $headerInsert, 'detail_rows' => $detailRows],
            'created_by' => $header['dilakukan_oleh'] ?? 'SYSTEM'
        ]);

        return $idLpb;
    }

    private function upsert_lpb_manual_stock($gudangId, $manualRef, array $row)
    {
        $stockQty = (float) ($row['qty_diterima'] ?? 0);
        $stockNoLot = trim((string) ($row['no_lot'] ?? ''));
        $expiredDate = $this->_normalizeDate($row['expired_date'] ?? '');

        if ($stockQty <= 0) {
            return FALSE;
        }

        if ($this->db->table_exists('tberp_stock_batch')) {
            $this->db->where('kd_barang', $row['kd_barang']);
            $this->db->where('gudang_id', (string) $gudangId);
            $this->db->where('no_lot', $stockNoLot);
            if ($expiredDate !== null) {
                $this->db->where('expired_date', $expiredDate);
            } else {
                $this->db->where('expired_date', null);
            }

            $existing = $this->db->get('tberp_stock_batch')->row_array();
            if ($existing) {
                $this->db->where('id', $existing['id']);
                $this->db->set('qty_on_hand', 'qty_on_hand + ' . $stockQty, FALSE);
                $this->db->set('update_at', date('Y-m-d H:i:s'));
                $this->db->update('tberp_stock_batch');
            } else {
                $this->db->insert('tberp_stock_batch', [
                    'kd_barang' => $row['kd_barang'],
                    'gudang_id' => (string) $gudangId,
                    'no_lot' => $stockNoLot,
                    'expired_date' => $expiredDate,
                    'qty_on_hand' => $stockQty,
                    'qty_reserved' => 0
                ]);
            }
        }

        if ($this->db->table_exists('tberp_stock_ledger')) {
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang' => $row['kd_barang'],
                'gudang_id' => (string) $gudangId,
                'no_lot' => $stockNoLot,
                'expired_date' => $expiredDate,
                'qty' => $stockQty,
                'tipe' => 'IN',
                'ref_no' => $manualRef,
                'ref_type' => 'LPB_MANUAL',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return TRUE;
    }

    public function insert_lpb_manual_system_log(array $payload)
    {
        $this->ensure_lpb_manual_schema();

        if (!$this->db->table_exists('tb_lpb_manual_log')) {
            return FALSE;
        }

        return $this->db->insert('tb_lpb_manual_log', [
            'id_lpb' => !empty($payload['id_lpb']) ? (int) $payload['id_lpb'] : null,
            'manual_ref_no' => $payload['manual_ref_no'] ?? null,
            'action_type' => $payload['action_type'] ?? 'LPB_MANUAL',
            'status' => $payload['status'] ?? 'INFO',
            'message' => $payload['message'] ?? '',
            'payload' => isset($payload['payload']) ? json_encode($payload['payload']) : null,
            'created_by' => $payload['created_by'] ?? 'SYSTEM',
            'created_at' => date('Y-m-d H:i:s'),
            'ip_address' => $payload['ip_address'] ?? null,
            'user_agent' => $payload['user_agent'] ?? null
        ]);
    }

    public function get_lpb_manual_system_logs($limit = 500)
    {
        $this->ensure_lpb_manual_schema();

        if (!$this->db->table_exists('tb_lpb_manual_log')) {
            return [];
        }

        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('id_log', 'DESC');
        $this->db->limit((int) $limit > 0 ? (int) $limit : 500);

        return $this->db->get('tb_lpb_manual_log')->result_array();
    }

    public function get_lpb_report_rows(array $filters = [])
    {
        $this->ensure_lpb_manual_schema();

        $source = strtolower(trim((string) ($filters['source'] ?? 'all')));
        $date1 = trim((string) ($filters['date1'] ?? ''));
        $date2 = trim((string) ($filters['date2'] ?? ''));
        $sourceExpr = $this->db->field_exists('source_type', 'tb_lpb')
            ? "COALESCE(NULLIF(h.source_type, ''), 'PO')"
            : "'PO'";
        $manualRefExpr = $this->db->field_exists('manual_ref_no', 'tb_lpb')
            ? "COALESCE(NULLIF(h.manual_ref_no, ''), h.kd_po)"
            : "h.kd_po";

        $sql = "SELECT
                h.id_lpb,
                h.input_at,
                h.tgl_sj AS tgl_lpb,
                h.kd_po,
                h.no_po,
                {$manualRefExpr} AS manual_ref_no,
                COALESCE(NULLIF(h.nomor_lpb, ''), '-') AS nomor_lpb,
                COALESCE(NULLIF(h.jenis_lpb, ''), '-') AS jenis_lpb,
                {$sourceExpr} AS source_type,
                CASE WHEN {$sourceExpr} = 'MANUAL' THEN 'LPB Manual Purchasing' ELSE 'LPB Logistik dari PO' END AS source_label,
                h.no_invoice,
                h.nosj,
                h.gudang_id,
                COALESCE(g.nama_gudang, '-') AS nama_gudang,
                h.keterangan,
                COUNT(d.id_detail_lpb) AS total_baris,
                COUNT(DISTINCT d.kd_barang) AS total_item,
                COALESCE(SUM(d.qty_diterima), 0) AS total_qty,
                COALESCE(SUM(d.total_harga), 0) AS total_harga
            FROM tb_lpb h
            LEFT JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
            LEFT JOIN tb_gudang g ON g.id_gudang = h.gudang_id
            WHERE 1 = 1";
        $params = [];

        if ($source === 'manual') {
            $sql .= " AND {$sourceExpr} = 'MANUAL'";
        } elseif ($source === 'logistik') {
            $sql .= " AND {$sourceExpr} <> 'MANUAL'";
        }

        if ($date1 !== '') {
            $sql .= " AND DATE(h.input_at) >= ?";
            $params[] = $date1;
        }
        if ($date2 !== '') {
            $sql .= " AND DATE(h.input_at) <= ?";
            $params[] = $date2;
        }

        $sql .= " GROUP BY
                h.id_lpb,
                h.input_at,
                h.tgl_sj,
                h.kd_po,
                h.no_po,
                h.nomor_lpb,
                h.jenis_lpb,
                h.no_invoice,
                h.nosj,
                h.gudang_id,
                g.nama_gudang,
                h.keterangan";
        if ($this->db->field_exists('source_type', 'tb_lpb')) {
            $sql .= ", h.source_type";
        }
        if ($this->db->field_exists('manual_ref_no', 'tb_lpb')) {
            $sql .= ", h.manual_ref_no";
        }
        $sql .= " ORDER BY h.input_at DESC, h.id_lpb DESC LIMIT 1000";

        return $this->db->query($sql, $params)->result_array();
    }

    public function create_lpb_from_tmp($header, $detailRows)
    {
        $headerInsert = [
            'kd_po'       => $header['kd_po'],
            'no_po'       => $header['no_po'],
            'nosj'        => $header['nosj'],
            'tgl_sj'      => $this->_normalizeDate($header['tgl_sj'] ?? ''),
            'no_invoice'  => $header['no_invoice'],
            'gudang_id'   => $header['gudang_id'],
            'keterangan'  => $header['keterangan'],
            'input_at'    => date('Y-m-d H:i:s')
        ];

        $jenisLpb = trim((string) ($header['jenis_lpb'] ?? ''));

        if ($this->db->field_exists('jenis_lpb', 'tb_lpb')) {
            $headerInsert['jenis_lpb'] = $jenisLpb !== '' ? $this->normalize_lpb_type($jenisLpb) : null;
        }

        if ($this->db->field_exists('nomor_lpb', 'tb_lpb')) {
            $headerInsert['nomor_lpb'] = $jenisLpb !== ''
                ? $this->generate_lpb_number($headerInsert['jenis_lpb'])
                : null;
        }

        if ($this->db->field_exists('status_lpb', 'tb_lpb')) {
            $headerInsert['status_lpb'] = 1;
        }

        $checkerName = trim((string) ($header['checker_name'] ?? $header['dilakukan_oleh'] ?? ''));
        $checkerBy = trim((string) ($header['checker_by'] ?? ''));
        if ($this->db->field_exists('checker_name', 'tb_lpb')) {
            $headerInsert['checker_name'] = $checkerName !== '' ? $checkerName : null;
        }
        if ($this->db->field_exists('checker_by', 'tb_lpb')) {
            $headerInsert['checker_by'] = $checkerBy !== '' ? $checkerBy : null;
        }
        if ($this->db->field_exists('checker_at', 'tb_lpb')) {
            $headerInsert['checker_at'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('tb_lpb', $headerInsert);
        $idLpb = $this->db->insert_id();

        if (!$idLpb) {
            return FALSE;
        }

        foreach ($detailRows as $row) {
            $hargaSatuan = (float) ($row['harga_satuan_kecil'] ?? 0);
            if ($hargaSatuan <= 0) {
                $hargaSatuan = (float) ($row['harga_satuan'] ?? 0);
            }
            $totalHarga = (float) ($row['total_harga'] ?? 0);
            if ($totalHarga <= 0 && $hargaSatuan > 0) {
                $totalHarga = (float) ($row['qty_diterima'] ?? 0) * $hargaSatuan;
            }

            $detailInsert = [
                'id_lpb'        => $idLpb,
                'kd_barang'     => $row['kd_barang'],
                'qty_diterima'  => $row['qty_diterima'],
                'no_lot'        => $row['no_lot'],
                'expired_date'  => $row['expired_date'],
                'input_at'      => date('Y-m-d H:i:s')
            ];

            if ($this->db->field_exists('harga_satuan', 'tb_lpb_detail')) {
                $detailInsert['harga_satuan'] = $hargaSatuan;
            }

            if ($this->db->field_exists('total_harga', 'tb_lpb_detail')) {
                $detailInsert['total_harga'] = $totalHarga;
            }

            $this->db->insert('tb_lpb_detail', $detailInsert);
            $idDetailLpb = $this->db->insert_id();

            if (!$idDetailLpb) {
                return FALSE;
            }

            $batchInsert = [
                'id_detail_lpb' => $idDetailLpb,
                'no_lot'        => $row['no_lot'],
                'expired_date'  => $row['expired_date'],
                'qty'           => $row['qty_diterima']
            ];

            $this->db->insert('tb_lpb_batch', $batchInsert);

            $stockQty = (float) $row['qty_diterima'];
            $normalizedExpired = $this->_normalizeDate($row['expired_date'] ?? '');
            $stockNoLot = trim((string) ($row['no_lot'] ?? ''));

            if ($this->db->table_exists('tberp_stock_batch')) {
                $this->db->where('kd_barang', $row['kd_barang']);
                $this->db->where('gudang_id', $header['gudang_id']);
                $this->db->where('no_lot', $stockNoLot);
                if ($normalizedExpired !== null) {
                    $this->db->where('expired_date', $normalizedExpired);
                } else {
                    $this->db->where('expired_date', null);
                }

                $existingStockBatch = $this->db->get('tberp_stock_batch')->row_array();

                if ($existingStockBatch) {
                    $this->db->where('id', $existingStockBatch['id']);
                    $this->db->set('qty_on_hand', 'qty_on_hand + ' . $stockQty, FALSE);
                    $this->db->set('update_at', date('Y-m-d H:i:s'));
                    $this->db->update('tberp_stock_batch');
                } else {
                    $this->db->insert('tberp_stock_batch', [
                        'kd_barang'    => $row['kd_barang'],
                        'gudang_id'    => $header['gudang_id'],
                        'no_lot'       => $stockNoLot,
                        'expired_date' => $normalizedExpired,
                        'qty_on_hand'  => $stockQty,
                        'qty_reserved' => 0,
                    ]);
                }
            }

            if ($this->db->table_exists('tberp_stock_ledger')) {
                $this->db->insert('tberp_stock_ledger', [
                    'kd_barang'    => $row['kd_barang'],
                    'gudang_id'    => $header['gudang_id'],
                    'no_lot'       => $stockNoLot,
                    'expired_date' => $normalizedExpired,
                    'qty'          => $stockQty,
                    'tipe'         => 'IN',
                    'ref_no'       => $header['kd_po'],
                    'ref_type'     => 'PO_RECEIVED',
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->insert_lpb_activity_log([
            'id_lpb'         => (int) $idLpb,
            'kd_po'          => $headerInsert['kd_po'] ?? '',
            'no_invoice'     => $headerInsert['no_invoice'] ?? '',
            'action_type'    => 'CREATE_LPB',
            'status_before'  => null,
            'status_after'   => 'POST',
            'data_before'    => null,
            'data_after'     => [
                'id_lpb' => (int) $idLpb,
                'nomor_lpb' => $headerInsert['nomor_lpb'] ?? '',
                'jenis_lpb' => $headerInsert['jenis_lpb'] ?? '',
                'nosj' => $headerInsert['nosj'] ?? '',
                'tgl_sj' => $headerInsert['tgl_sj'] ?? '',
                'status_lpb' => $headerInsert['status_lpb'] ?? 1,
                'checker_name' => $headerInsert['checker_name'] ?? ''
            ],
            'keterangan'     => 'Draft temporary penerimaan direkam otomatis menjadi POST',
            'dilakukan_oleh' => $header['dilakukan_oleh'] ?? 'SYSTEM',
            'checker_name'   => $headerInsert['checker_name'] ?? $checkerName,
            'checker_by'     => $headerInsert['checker_by'] ?? $checkerBy
        ]);

        $this->db->where('kd_suplier', $header['kd_suplier']);
        $this->db->where_in('id_tmp_recieved', array_column($detailRows, 'id_tmp_recieved'));
        $this->db->delete('tb_tmp_po_received');

        return $idLpb;
    }

    public function get_riwayat_barang_masuk($date1 = null, $date2 = null)
    {
        $this->db->select('
            dl.id_detail_lpb,
            dl.no_po,
            dl.kd_po,
            dl.kd_barang,
            mb.nama_barang,
            pp.kd_suplier,
            s.nama_suplier,
            dl.qty_diterima,
            dl.satuan,
            dl.no_lot,
            dl.exp_date,
            dl.create_at
        ');
        $this->db->from('tb_po_received dl');
        $this->db->join('tbpo_barang mb', 'mb.kode_barang = dl.kd_barang', 'left');

        // Join ke tb_pre_po untuk dapat kd_suplier
        $this->db->join('tb_pre_po pp', 'pp.no_po = dl.no_po AND pp.kd_barang = dl.kd_barang', 'left');

        // Join ke tb_suplier untuk nama_suplier
        $this->db->join('tb_suplier s', 's.kd_suplier = pp.kd_suplier', 'left');

        if (!empty($date1) && !empty($date2)) {
            $this->db->where('DATE(dl.create_at) >=', $date1);
            $this->db->where('DATE(dl.create_at) <=', $date2);
        }

        $this->db->order_by('dl.id_detail_lpb', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_lpb_print_headers_by_kd_po($kd_po)
    {
        $sql = "SELECT
                h.id_lpb,
                h.kd_po,
                h.no_po,
                h.nosj,
                h.tgl_sj,
                h.no_invoice,
                h.keterangan,
                h.input_at,
                COUNT(DISTINCT d.kd_barang) AS total_item,
                COUNT(d.id_detail_lpb) AS total_baris,
                COALESCE(SUM(d.qty_diterima), 0) AS total_qty
            FROM tb_lpb h
            LEFT JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
            WHERE h.kd_po = ?
            GROUP BY
                h.id_lpb,
                h.kd_po,
                h.no_po,
                h.nosj,
                h.tgl_sj,
                h.no_invoice,
                h.keterangan,
                h.input_at
            ORDER BY h.input_at DESC, h.id_lpb DESC";

        return $this->db->query($sql, [$kd_po])->result_array();
    }

    public function has_remaining_so_loading_checker_by_rute($kd_rute)
    {
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '') {
            return false;
        }

        // Cek apakah ada item yang TIDAK dimuat (checker_loaded = 2/X)
        // Jika ada item yang di-X, DO tidak boleh dibuat
        $ada_ditolak = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_sales_order_detail sd
            JOIN tbso_sales_order so ON so.id_so = sd.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            WHERE COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
            AND so.status IN ('siap_faktur', 'partial', 'completed')
            AND COALESCE(sd.qty_siap_faktur, sd.qty) > 0
            AND sd.checker_loaded = 2
            AND NOT EXISTS (
                SELECT 1 FROM tb_detail_do dd
                JOIN tbso_faktur_penjualan fp ON fp.no_faktur = dd.kd_faktur
                WHERE fp.id_so = so.id_so
            )
        ", [$kd_rute])->row_array();

        if ((int)($ada_ditolak['total'] ?? 0) > 0) {
            return true; // ada yang di-X, blok DO
        }

        // Cek apakah ada item yang belum dipilih sama sekali (checker_loaded = 0 atau NULL)
        $row = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_sales_order_detail sd
            JOIN tbso_sales_order so ON so.id_so = sd.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            WHERE COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
            AND so.status IN ('siap_faktur', 'partial', 'completed')
            AND COALESCE(sd.qty_siap_faktur, sd.qty) > 0
            AND (sd.checker_loaded IS NULL OR sd.checker_loaded = 0)
            AND NOT EXISTS (
                SELECT 1 FROM tb_detail_do dd
                JOIN tbso_faktur_penjualan fp ON fp.no_faktur = dd.kd_faktur
                WHERE fp.id_so = so.id_so
            )
        ", [$kd_rute])->row_array();

        return (int)($row['total'] ?? 0) > 0;
    }

    public function check_and_auto_create_do($kd_rute, $create_by, $bypass_checks = false)
    {
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '' || strtoupper($kd_rute) === 'TANPA_RUTE') {
            return false;
        }
        if (!$this->get_rute_do($kd_rute)) {
            return false;
        }
        if ($this->has_so_loading_verification_by_rute($kd_rute)) {
            return false;
        }
        // Jika $bypass_checks = true, lewati pemeriksaan has_remaining_so_* karena
        // pemanggil sudah melakukan matching eksplisit (skenario: barang tidak termuat
        // sudah di-repost oleh Admin SC dan DO perlu dibuat untuk item yang termuat).
        if (!$bypass_checks) {
            if ($this->has_remaining_so_ready_faktur_by_rute($kd_rute)) {
                return false;
            }
            if ($this->has_remaining_so_loading_checker_by_rute($kd_rute)) {
                return false;
            }
        }

        $note = 'DO otomatis dibuat setelah seluruh SO rute ' . $kd_rute . ' selesai difakturkan dan termuat semua.';
        $created = $this->create_ready_do_from_faktur_rute($kd_rute, $note, $create_by);
        if (!$created) {
            return false;
        }

        $this->insertlog_do([
            'kd_do'      => $created['kd_do'],
            'tgl_input'  => date('d/m/Y'),
            'keterangan' => 'AUTO DO RUTE ' . $kd_rute . ' dari faktur Admin SC & Checker oleh ' . $create_by,
            'inputer'    => $create_by,
        ]);

        $this->load->model('M_Checker');
        $this->M_Checker->sync_route_activity($kd_rute, 'siap_loading', $create_by);

        return $created;
    }
}
