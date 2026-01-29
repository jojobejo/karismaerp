<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Ics extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Ics');
        $this->load->model('M_Logistik');
        $this->load->model('M_Keuangan');
        $this->load->helper('stock_helper');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        $data['page_title']     = 'KARISMA - LOGISTIK';
        $data['kdgenerate']     = $this->M_Keuangan->generate_update();
        $data['list_faktur']    = $this->M_Logistik->get_data_penjualan_zahir();
        $data['updated']        = $this->M_Logistik->get_updated_data_preparation();
        $data['listdo']         = $this->M_Logistik->getdo();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_by_expdate()
    {
        $pic    = $this->session->userdata('tim');

        if ($pic == '1') {
            $rpic = "A";
        } elseif ($pic == '2') {
            $rpic = "B";
        } elseif ($pic == '3') {
            $rpic = "C";
        } elseif ($pic == '4') {
            $rpic = "D";
        } elseif ($pic == '0') {
            $rpic = "E";
        }

        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['barang_ics']         = $this->M_Ics->list_barang_ics_expdate($rpic);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_by_expdate.php', $data);
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_by_allbarang()
    {

        $pic    = $this->session->userdata('tim');

        if ($pic == '1') {
            $rpic = "A";
        } elseif ($pic == '2') {
            $rpic = "B";
        } elseif ($pic == '3') {
            $rpic = "C";
        } elseif ($pic == '4') {
            $rpic = "D";
        } elseif ($pic == '0') {
            $rpic = "E";
        }

        $data['page_title']         = 'KARISMA - ICS';
        $data['barang_ics']         = $this->M_Ics->list_barang_ics_allbarang($rpic);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_by_allbarang.php', $data);
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_diffrent()
    {
        $akses = $this->session->userdata('nama');

        $data['page_title']         = 'KARISMA - ICS';

        if ($akses == 'Admin ICS') {
            $data['barang_ics_a']         = $this->M_Ics->list_barang_ics_diffrent_a();
            $data['barang_ics_b']         = $this->M_Ics->list_barang_ics_diffrent_b();
            $data['barang_ics_c']         = $this->M_Ics->list_barang_ics_diffrent_c();
            $data['barang_ics_d']         = $this->M_Ics->list_barang_ics_diffrent_d();
            $data['barang_ics_e']         = $this->M_Ics->list_barang_ics_diffrent_e();
            $data['barang_ics_0']         = $this->M_Ics->list_barang_ics_diffrent_0();
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/logistik/ics/ics_show_diff.php', $data);
            $this->load->view('content/logistik/ics/ajaxics.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($akses == 'Admin ICS 1') {
            $data['barang_ics_a']         = $this->M_Ics->list_barang_ics_diffrent_a();
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/logistik/ics/ics_show_diff.php', $data);
            $this->load->view('content/logistik/ics/ajaxics.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($akses == 'Admin ICS 2') {
            $data['barang_ics_b']         = $this->M_Ics->list_barang_ics_diffrent_b();
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/logistik/ics/ics_show_diff.php', $data);
            $this->load->view('content/logistik/ics/ajaxics.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($akses == 'Admin ICS 3') {
            $data['barang_ics_c']         = $this->M_Ics->list_barang_ics_diffrent_c();
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/logistik/ics/ics_show_diff.php', $data);
            $this->load->view('content/logistik/ics/ajaxics.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($akses == 'Admin ICS 4') {
            $data['barang_ics_d']         = $this->M_Ics->list_barang_ics_diffrent_d();
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/logistik/ics/ics_show_diff.php', $data);
            $this->load->view('content/logistik/ics/ajaxics.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($akses == 'Admin ICS 5') {
            $data['barang_ics_e']         = $this->M_Ics->list_barang_ics_diffrent_e();
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/logistik/ics/ics_show_diff.php', $data);
            $this->load->view('content/logistik/ics/ajaxics.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($akses == 'Admin ICS 6') {
            $data['barang_ics_0']         = $this->M_Ics->list_barang_ics_diffrent_0();
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/logistik/ics/ics_show_diff.php', $data);
            $this->load->view('content/logistik/ics/ajaxics.php', $data);
            $this->load->view('partial/main/footer.php');
        }
    }

    public function by_allbarang_ics($pic)
    {
        $data['page_title']         = 'KARISMA - ICS';

        if ($pic == '1') {
            $rpic = "A";
        } elseif ($pic == '2') {
            $rpic = "B";
        } elseif ($pic == '3') {
            $rpic = "C";
        } elseif ($pic == '4') {
            $rpic = "D";
        } elseif ($pic == '0') {
            $rpic = "E";
        }

        $data['tim']                = $pic;
        $data['barang_ics']         = $this->M_Ics->list_barang_ics_allbarang($rpic);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_by_allbarang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function by_expdate_ics($pic)
    {
        $data['page_title']         = 'KARISMA - ICS';

        if ($pic == '1') {
            $rpic = "A";
        } elseif ($pic == '2') {
            $rpic = "B";
        } elseif ($pic == '3') {
            $rpic = "C";
        } elseif ($pic == '4') {
            $rpic = "D";
        } elseif ($pic == '0') {
            $rpic = "E";
        }
        $data['tim']                = $pic;
        $data['barang_ics']         = $this->M_Ics->list_barang_ics_expdate($rpic);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_by_expdate.php', $data);
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function stock_by_kodebr($kd)
    {
        $kdbarang = $this->M_Ics->getnmbarang($kd);
        $data_barang = $this->db
            ->select('a.nama_barang, a.exp_date')
            ->from('tb_saldo_awal a')
            ->join('tb_master_barang_all b', 'b.nama_barang = a.nama_barang')
            ->where('a.nama_barang', $kdbarang)
            ->get()
            ->row();

        if (!$data_barang) {
            show_404();
        }

        $nama_barang = $data_barang->nama_barang;
        $exp_date    = $data_barang->exp_date;

        $query = $this->db->query("SELECT
            a.id,
            b.kd_barang,
            a.nama_barang,
            a.exp_date as exp_date,
            (b.p*b.l*b.t) AS dimensi,
            SUM(a.qty) AS qty_awal,
            COALESCE(pending.qty_pending, 0) AS DO,
            COALESCE(purchase.qty_po, 0) AS PO,
            COALESCE(opname.qty_opname, 0) AS ICS,
            (SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
            ((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0))- COALESCE(opname.qty_opname, 0) AS selisih,
            IF(((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0), 1, 0) AS status
            FROM tb_saldo_awal a
            JOIN tb_master_barang_all b ON b.nama_barang = a.nama_barang
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
            GROUP BY a.nama_barang, a.exp_date", array($nama_barang, $exp_date));

        $data['list_gudang'] = $this->db
            ->where('is_active', 1)
            ->get('tb_gudang')->result();

        $data['list_gudang_wilayah'] = $this->db
            ->where('is_active', 1)
            ->get('tb_gudang_wilayah')->result();

        $data['detail_stok']        = $query->result();
        $data['page_title']         = 'KARISMA - ICS';
        $data['get_barang']         = $this->M_Ics->get_detail_barang($kd);
        $data['list_stock_by_exp']  = $this->M_Ics->tracking_br_diffrent_by_expdate($kdbarang);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/stock_by_kodebr.php', $data);
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update_gudang()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $opname_id = $this->input->post('opname_id');
        $id_gudang = $this->input->post('id_gudang');

        if (empty($opname_id) || empty($id_gudang)) {
            echo json_encode([
                'status' => false,
                'message' => 'Data tidak lengkap'
            ]);
            return;
        }

        $update = $this->M_Ics->updateGudangByOpname($opname_id, $id_gudang);

        echo json_encode([
            'status' => $update,
            'message' => $update ? 'Gudang berhasil diperbarui' : 'Gagal update gudang'
        ]);
    }

    public function update_wilayah()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $opname_id  = $this->input->post('opname_id');
        $id_wilayah = $this->input->post('id_wilayah');

        if (empty($opname_id) || empty($id_wilayah)) {
            echo json_encode([
                'status' => false,
                'message' => 'Data tidak lengkap'
            ]);
            return;
        }

        $update = $this->M_Ics->updateWilayahByOpname($opname_id, $id_wilayah);

        echo json_encode([
            'status' => $update,
            'message' => $update ? 'Wilayah berhasil diperbarui' : 'Gagal update wilayah'
        ]);
    }

    public function get_wilayah_by_gudang()
    {
        $id_gudang = $this->input->post('id_gudang');
        log_message('error', 'ID GUDANG: ' . $id_gudang);

        $data = $this->M_Ics->getWilayahByGudang($id_gudang);
        log_message('error', json_encode($data));

        echo json_encode($data);
    }

    public function get_detail_by_exp()
    {
        $nama_barang = $this->input->post('nama_barang', true);
        $exp_date    = $this->input->post('exp_date', true);
        $kd_barang   = $this->input->post('kd_barang', true);

        $subquery = $this->db
            ->select([
                'a.kd_faktur',
                'a.tgl_transaksi',
                'a.kd_barang',
                'a.exp_date',
                'SUM(a.qty) AS qty'
            ])
            ->from('tb_ics_do a')
            ->where('a.kd_barang', $kd_barang)
            ->where('a.exp_date', $exp_date)
            ->group_by(['a.kd_faktur', 'a.kd_barang', 'a.exp_date'])
            ->get_compiled_select();

        $data_do = $this->db
            ->select([
                'x.kd_faktur',
                'x.tgl_transaksi',
                'x.qty',
                'c.nama_customer AS nm_customer',
                'x.kd_barang',
                'x.exp_date'
            ])
            ->from("($subquery) x", false)
            ->join('tb_pre_do b', 'b.kd_faktur = x.kd_faktur', 'left')
            ->join('tb_customer c', 'c.kd_customer = b.kd_customer', 'left')
            ->group_by(['x.kd_faktur', 'x.kd_barang', 'x.exp_date'])
            ->get()
            ->result();

        $data_po = $this->db
            ->select([
                'kd_faktur_lpb',
                'tgl_transaksi',
                'qty',
                'input_at'
            ])
            ->from('tb_ics_po')
            ->where('nama_barang', $nama_barang)
            ->where('exp_date', $exp_date)
            ->get()
            ->result();

        $data_log = $this->db
            ->select([
                'inputer',
                'tgl_input',
                'qty',
                'keterangan'
            ])
            ->from('tb_log_ics')
            ->where('nama_barang', $nama_barang)
            ->where('exp_date', $exp_date)
            ->get()
            ->result();

        echo json_encode([
            'data_do'  => $data_do,
            'data_po'  => $data_po,
            'data_log' => $data_log
        ]);
    }

    public function get_detail_by_exp_date()
    {
        $exp_date = $this->input->post('exp_date');
        $nama_barang = $this->input->post('nama_barang');
        $data = $this->M_Ics->get_exp_detail($nama_barang, $exp_date);
        echo json_encode($data);
    }

    public function master_barang()
    {
        $data['page_title']         = 'KARISMA - MASTER BARANG';

        $userlog = $this->session->userdata("jobdesk");

        if ($userlog == 'ADMINICS') {
            $data['mbarang']            = $this->M_Ics->get_master_barang_ics();
        } elseif ($userlog == 'LOGISTIK') {
            $data['mbarang']            = $this->M_Ics->get_master_barang_log();
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/master_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function get_detail_mbarang()
    {
        $id = $this->input->post('id');
        $this->db->select('i.id as id,i.nm_barang as nama_barang,i.bhn_aktif AS bahan_aktif,i.satuan AS satuan,i.berat AS berat,i.kubikasi AS kubikasi,i.kode_barang AS kode_barang');
        $this->db->from('tb_master_barang i');
        $this->db->where('i.id', $id);
        $query = $this->db->get()->row();

        echo json_encode($query);
    }

    public function edit_master_barang()
    {
        $idbarang   = $this->input->post('modal_id');
        $nmbarang   = $this->input->post('modal_nama_barang');
        $kdbarang   = $this->input->post('modal_kode_barang');
        $bhn_aktif  = $this->input->post('modal_bahan_aktif');
        $satuan     = $this->input->post('modal_satuan');
        $tonase     = $this->input->post('modal_berat');
        $kubikasi   = $this->input->post('modal_kubikasi');

        $svbarang = [
            'nm_barang'   => $nmbarang,
            'kode_barang' => $kdbarang,
            'bhn_aktif'   => $bhn_aktif,
            'satuan'      => $satuan,
            'berat'       => $tonase,
            'kubikasi'    => $kubikasi,
        ];

        $this->M_Ics->edit_mbarang_ics($idbarang, $svbarang);
        redirect('ics/master_barang');
    }


    public function update_inline()
    {
        $nama_barang = $this->input->post('nama_barang');
        $exp_date = $this->input->post('exp_date');
        $field = $this->input->post('field');
        $value = (int) $this->input->post('value');
        $datenow = date('d/m/Y');

        $barang = $this->db->get_where('tb_mbarang', ['nm_barang' => $nama_barang])->row();
        if (!$barang) {
            echo json_encode(['status' => 'failed', 'message' => 'Barang tidak ditemukan']);
            return;
        }

        $unit_size = $barang->p * $barang->l * $barang->t;

        $current = $this->db->get_where('tb_ics_opname', [
            'nama_barang' => $nama_barang,
            'exp_date' => $exp_date,
            'DATE(input_at)' => $datenow
        ])->row();

        if ($field == 'opname_box') {
            $new_qty = $value * $unit_size;

            if ($current) {
                $sisa_pcs = $current->qty % $unit_size;
                $this->db->set('qty', $new_qty + $sisa_pcs);
                $this->db->set('qty_box', $value);
                $this->db->where('id', $current->id);
                $this->db->update('tb_ics_opname');
            } else {
                $this->db->insert('tb_ics_opname', [
                    'nama_barang' => $nama_barang,
                    'exp_date' => $exp_date,
                    'qty' => $new_qty,
                    'qty_box' => $value,
                    'qty_pcs' => 0,
                    'inputer' => $this->session->userdata('nama'),
                    'tim' => '0',
                    'wilayah' => '-',
                    'input_at' => $datenow
                ]);
            }
        } elseif ($field == 'opname_pcs') {
            $new_qty = $value;

            if ($current) {
                $box_qty = floor($current->qty / $unit_size);
                $this->db->set('qty', ($box_qty * $unit_size) + $new_qty);
                $this->db->set('qty_pcs', $value);
                $this->db->where('id', $current->id);
                $this->db->update('tb_ics_opname');
            } else {
                $this->db->insert('tb_ics_opname', [
                    'nama_barang' => $nama_barang,
                    'exp_date' => $exp_date,
                    'qty' => $new_qty,
                    'qty_box' => 0,
                    'qty_pcs' => $value,
                    'inputer' => $this->session->userdata('nama'),
                    'tim' => '0',
                    'wilayah' => '-',
                    'input_at' => $datenow
                ]);
            }
        }
        echo json_encode(['status' => 'success']);
    }

    public function get_data()
    {
        $data = $this->M_Ics->list_barang_ics();
        echo json_encode($data);
    }

    public function inline_update()
    {
        $id    = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');

        if ($field === 'qty')  $value = (int)$value;

        $ok = $this->Stok_model->update_cell($id, $field, $value);
        echo json_encode(['success' => $ok]);
    }

    public function ics_do()
    {
        date_default_timezone_set('Asia/Jakarta');

        $data['page_title']         = 'KARISMA - LOGISTIK';
        $tgl                        = date('d/m/Y');
        $data['tanggal_now']        = date('d/m/Y');
        $data['ics_do']             = $this->M_Ics->list_do_today($tgl);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/icsdo.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_po()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $tgl                        = date('d/m/Y');
        $data['tanggal_now']        = date('d/m/Y');
        // $data['ics_po']             = $this->M_Ics->list_po_today($tgl);
        $data['ics_po']             = $this->M_Ics->list_po();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/icspo.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan_opname()
    {
        $this->load->database();
        $today = date('d/m/Y');
        $this->db->set('input_at', $today);
        $this->db->update('tb_ics');
        $data_ics = $this->db->get('tb_ics')->result_array();
        if (empty($data_ics)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tb_ics kosong.']);
            return;
        }
        $insert_batch = [];
        foreach ($data_ics as $row) {
            $insert_batch[] = [
                'nama_barang' => $row['nama_barang'],
                'exp_date'    => $row['exp_date'],
                'qty'         => $row['qty'],
                'qty_box'     => $row['qty_box'],
                'qty_pcs'     => $row['qty_pcs'],
                'input_at'    => $today
            ];
        }

        $this->db->insert_batch('tb_ics_opname', $insert_batch);
        echo json_encode(['status' => 'success']);
    }

    public function ics_stock_controller($idbarang)
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['nmbarang']           = $this->M_Ics->get_br_name($idbarang);
        $data_barang = $this->db
            ->select('a.nama_barang, a.exp_date')
            ->from('tb_saldo_awal a')
            ->join('tb_mbarang b', 'b.nm_barang = a.nama_barang')
            ->where('a.id', $idbarang)
            ->get()
            ->row();

        if (!$data_barang) {
            show_404();
        }

        $nama_barang = $data_barang->nama_barang;
        $exp_date    = $data_barang->exp_date;

        $query = $this->db->query("SELECT
            a.id,
            a.nama_barang,
            a.exp_date as exp_date,
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
            GROUP BY a.nama_barang, a.exp_date", array($nama_barang, $exp_date));

        $data['detail_stok']        = $query->result();
        $data['detail_allbarang']   = $this->M_Ics->ics_get_all_qty_barang($nama_barang);
        $data['input_log']          = $this->M_Ics->ics_log_input($nama_barang, $exp_date);
        $data['data_do']            = $this->M_Ics->get_do_by_expdate($nama_barang, $exp_date);
        $data['data_po']            = $this->M_Ics->get_po_by_expdate($nama_barang, $exp_date);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_stock_controller.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
    }

    public function get_detail_barang()
    {
        $id = $this->input->post('id');
        $this->db->select('i.id, i.nama_barang, i.exp_date, (m.p * m.l * m.t) AS dimensi');
        $this->db->from('tb_ics i');
        $this->db->join('tb_mbarang m', 'm.nm_barang = i.nama_barang', 'left');
        $this->db->where('i.id', $id);
        $query = $this->db->get()->row();

        echo json_encode($query);
    }

    public function view_detail_master_barang($kd)
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['barang']             = $this->M_Ics->get_barang_detail_by_kd($kd);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/master_barang_detail.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
    }

    public function ics_detail_allbarang($nmbarang)
    {
        $data['ics_detail_all'] = $this->M_Ics->get_detail_ics_allbarang($nmbarang);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_detail_allbarang.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
    }

    public function simpan_input_opname()
    {
        $dimensi   = (int) $this->input->post('dimensi');
        $qty_box   = (int) $this->input->post('qty_box');
        $qty_pcs   = (int) $this->input->post('qty_pcs');
        $qty_total = ($qty_box * $dimensi) + $qty_pcs;
        $today = date('d/m/Y');

        $data = [
            'nama_barang' => $this->input->post('nama_barang'),
            'exp_date'    => $this->input->post('exp_date'),
            'qty'         => $qty_total,
            'qty_box'     => $qty_box,
            'qty_pcs'     => $qty_pcs,
            'dimensi'     => $dimensi,
            'input_at'    => $today,
            'create_at'   => date('Y-m-d H:i:s')
        ];

        if ($this->db->insert('tb_ics_opname', $data)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insert gagal']);
        }
    }

    public function save_opname_ics()
    {
        $action      = $this->input->post('action');
        $id          = $this->input->post('id');
        $kdbarang    = $this->input->post('kdbarang');
        $nama_barang = $this->input->post('nama_barang');
        $exp_date    = $this->input->post('exp_date');
        $dimensi     = $this->input->post('dimensi');
        $qty_box     = $this->input->post('qty_box');
        $qty_pcs     = $this->input->post('qty_pcs');
        $keterangan  = $this->input->post('keterangan_isi');
        $qty_total   = ($qty_box * $dimensi) + $qty_pcs;
        $expawal     = date('d/m/Y', strtotime($exp_date));

        $data = [
            'kd_system'   => $kdbarang,
            'nama_barang' => $nama_barang,
            'exp_date'    => $exp_date,
            'qty'         => $qty_total,
            'qty_box'     => $qty_box,
            'qty_pcs'     => $qty_pcs,
            'input_at'    => date('d/m/Y'),
        ];

        $dataawal = [
            'kd_system'   => $kdbarang,
            'nama_barang' => $nama_barang,
            'exp_date'    => $expawal,
            'qty_box'     => '0',
            'qty_pcs'     => '0',
            'qty'         => '0',
            'inputer'     => $this->session->userdata('nama'),
            'input_at'    => date('d/m/Y'),
            'create_at'   => date('Y-m-d H:i:s')
        ];

        $newopname = [
            'kd_system'   => $kdbarang,
            'nama_barang' => $nama_barang,
            'exp_date'    => $expawal,
            'qty_box'     => $qty_box,
            'qty_pcs'     => $qty_pcs,
            'qty'         => $qty_total,
            'inputer'     => $this->session->userdata('nama'),
            'input_at'    => date('d/m/Y'),
            'create_at'   => date('Y-m-d H:i:s')
        ];

        $data_awal = [
            'nama_barang' => $nama_barang,
            'exp_date'    => $expawal,
            'qty'         => '0',
            'qty_box'     => '0',
            'qty_pcs'     => '0',
            'input_at'    => date('d/m/Y'),
            'create_at'   => date('Y-m-d H:i:s')
        ];

        $logics = [
            'nama_barang'   => $nama_barang,
            'qty'           => $qty_total,
            'qty_box'       => $qty_box,
            'qty_pcs'       => $qty_pcs,
            'no_lot'        => '-',
            'exp_date'      => $exp_date,
            'keterangan'    => $keterangan,
            'inputer'       => $this->session->userdata('nama'),
            'tgl_input'     => date('d/m/Y'),
            'create_at'     => date('Y-m-d H:i:s')
        ];

        $logicsawal_new = [
            'nama_barang'   => $nama_barang,
            'qty'           => $qty_total,
            'qty_box'       => $qty_box,
            'qty_pcs'       => $qty_pcs,
            'no_lot'        => '-',
            'exp_date'      => $exp_date,
            'keterangan'    => 'expired_new_opname',
            'inputer'       => $this->session->userdata('nama'),
            'tgl_input'     => date('d/m/Y'),
            'create_at'     => date('Y-m-d H:i:s')
        ];

        $logicsawal = [
            'nama_barang'   => $nama_barang,
            'qty'           => $qty_total,
            'qty_box'       => $qty_box,
            'qty_pcs'       => $qty_pcs,
            'no_lot'        => '-',
            'exp_date'      => $expawal,
            'keterangan'    => $keterangan,
            'inputer'       => $this->session->userdata('nama'),
            'tgl_input'     => date('d/m/Y'),
            'create_at'     => date('Y-m-d H:i:s')
        ];

        switch ($action) {
            case 'dashboard':
                $this->db->insert('tb_ics', $data);
                $this->db->insert('tb_log_ics', $logics);
                $this->session->set_flashdata('success', 'Data opname berhasil disimpan.');
                redirect('ics');
                break;
            case 'formdetail':
                $this->db->insert('tb_ics', $data);
                $this->db->insert('tb_log_ics', $logics);
                $this->session->set_flashdata('success', 'Data opname berhasil disimpan.');
                redirect('ics/ics_stock_controller/' . $id);
                break;
            case 'formbyexp':
                $this->db->update('tb_ics', $data, ['id' => $id]);
                $this->db->insert('tb_log_ics', $logics);
                $this->session->set_flashdata('success', 'Data opname berhasil diperbarui.');
                redirect('ics/stock_by_kodebr/' . $kdbarang);
                break;
            case 'new_expired':
                $this->db->insert('tb_ics', $dataawal);
                $this->db->insert('tb_log_ics', $logicsawal);
                $this->db->insert('tb_saldo_awal', $data_awal);
                $this->session->set_flashdata('success', 'Data opname berhasil diperbarui.');
                redirect('ics/stock_by_kodebr/' . $kdbarang);
                break;
            case 'newopname':
                $this->db->insert('tb_ics', $newopname);
                $this->db->insert('tb_log_ics', $logicsawal_new);
                $this->session->set_flashdata('success', 'Data opname berhasil diperbarui.');
                redirect('ics/stock_by_kodebr/' . $kdbarang);
                break;
            case 'diffrent':
                $this->db->insert('tb_ics', $data);
                $this->db->insert('tb_log_ics', $logics);
                $this->session->set_flashdata('success', 'Data opname berhasil disimpan.');
                redirect('ics/ics_diffrent');
                break;
        }
    }

    public function import_csv_po()
    {
        $this->load->library('upload');
        $this->load->helper('file');

        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['file_name']     = 'import_po_' . time();
        $config['overwrite']     = true;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_csv')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('ics/icspo');
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        $handle = fopen($filePath, "r");
        $header = fgetcsv($handle);

        // $data_import = [];
        // $data_opname = [];
        // $data_saldo_awal = [];

        $now = date('d/m/Y');

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

            $exp_raw = trim($row[4]);
            $exp_date = null;

            if (preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $exp_raw)) {
                $date_obj = DateTime::createFromFormat('d/m/Y', $exp_raw);
                $exp_date = $date_obj ? $date_obj->format('d/m/Y') : null;
            }

            $nama_barang = trim($row[3]);

            $data_import[] = [
                'tgl_transaksi'   => $row[0],
                'kd_faktur_lpb'   => $row[1],
                'kd_barang'       => $row[2],
                'nama_barang'     => $nama_barang,
                'exp_date'        => $exp_date,
                'qty'             => $row[5],
                'lpb_note'        => $row[6],
                'input_at'        => $now,
                'lpb_status'      => '2'
            ];

            // $exists_opname = $this->db->get_where('tb_ics', [
            //     'nama_barang' => $nama_barang,
            //     'exp_date'    => $exp_date
            // ])->num_rows();

            // $opname_dt = $this->db->get_where('tb_ics', [
            //     'nama_barang' => $nama_barang,
            // ])->row();

            // if ($exists_opname == 0) {

            //     $data_opname[] = [
            //         'kd_system'     => $opname_dt->kd_system,
            //         'nama_barang'   => $nama_barang,
            //         'exp_date'      => $exp_date,
            //         'qty'           => '0',
            //         'qty_box'       => '0',
            //         'qty_pcs'       => '0',
            //         'inputer'       => 'Admin ICS',
            //         'tim'           => '0',
            //         'wilayah'       => '-',
            //         'input_at'      => $now
            //     ];
            // }

            // $exists_saldo = $this->db->get_where('tb_saldo_awal', [
            //     'nama_barang' => $nama_barang,
            //     'exp_date'    => $exp_date
            // ])->num_rows();

            // if ($exists_saldo == 0) {
            //     $data_saldo_awal[] = [
            //         'nama_barang'   => $nama_barang,
            //         'exp_date'      => $exp_date,
            //         'qty'           => '0',
            //         'qty_box'       => '0',
            //         'qty_pcs'       => '0',
            //         'lokasi'        => '0',
            //         'kordinat'      => '-',
            //         'kordinat1'     => '-',
            //         'input_at'      => $now
            //     ];
            // }
        }
        fclose($handle);

        if (!empty($data_import)) {
            $this->db->insert_batch('tb_ics_po', $data_import);
        }

        // if (!empty($data_opname)) {
        //     $this->db->insert_batch('tb_ics', $data_opname);
        // }

        // if (!empty($data_saldo_awal)) {
        //     $this->db->insert_batch('tb_saldo_awal', $data_saldo_awal);
        // }

        if (!empty($data_import)) {
            $this->session->set_flashdata('success', 'Import berhasil!');
        } else {
            $this->session->set_flashdata('error', 'File kosong atau format tidak sesuai.');
        }

        redirect('ics/icspo');
    }


    public function history_ics_do()
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/do_histori.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function sc_do_by_date_range()
    {
        $this->load->helper('date');

        $tgl1 = $this->input->post('tgl1');
        $tgl2 = $this->input->post('tgl2');

        $start_date = DateTime::createFromFormat('d/m/Y', $tgl1);
        $end_date = DateTime::createFromFormat('d/m/Y', $tgl2);

        if (!$start_date || !$end_date) {

            $this->session->set_flashdata('error', 'Format tanggal salah');
            redirect('ics/by_expdate');
            return;
        }

        $start_date_str = $start_date->format('Y-m-d');
        $end_date_str = $end_date->format('Y-m-d');

        $this->db->where('tgl_transaksi >=', $start_date_str);
        $this->db->where('tgl_transaksi <=', $end_date_str);
        $query = $this->db->get('tb_ics_do');

        $data['page_title'] = 'Hasil Pencarian DO by Tanggal';
        $data['results'] = $query->result();
        $data['tgl1'] = $tgl1;
        $data['tgl2'] = $tgl2;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/do_date_range_view.php', $data);
        $this->load->view('partial/main/footer.php');
    }
    public function kalkulator_operan()
    {
        $expression = $this->input->post('expression');
        if (!preg_match('/^[0-9+\-*/(). ]+$/', $expression)) {
            echo json_encode(['error' => 'Ekspresi tidak valid']);
            return;
        }
        try {
            $result = eval("return ($expression);");
            echo json_encode(['result' => $result]);
        } catch (Throwable $e) {
            echo json_encode(['error' => 'Perhitungan error']);
        }
    }

    public function data_lpb_zahir()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/dtalbp.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // public function get_lpb()
    // {
    //     $data['page_title']         = 'KARISMA - LOGISTIK';

    //     $this->load->view('partial/main/header.php', $data);
    //     $this->load->view('content/logistik/ics/a.php', $data);
    //     $this->load->view('partial/main/footer.php');
    // }

    public function get_lpb()
    {
        // Ambil data dari POST form/filter
        $periode1 = '2025-10-31';
        $periode2 = '2025-10-31';

        if (empty($periode1) || empty($periode2)) {
            echo json_encode(['status' => 'error', 'message' => 'Periode belum diisi']);
            return;
        }

        // URL endpoint lokal PHP Zahir Digital
        $url = "https://10.10.10.12/ZahirDigital/LOGISTIK/get_lpb.php";

        // Kirim data POST ke get_lpb.php pakai CURL internal

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'periode1' => $periode1,
            'periode2' => $periode2
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
            return;
        }

        curl_close($ch);

        // Pastikan responnya valid JSON
        $json = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status' => 'error', 'message' => 'Respon tidak valid dari server LPB']);
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($json));
    }

    public function pic_barang($pic = null)
    {
        date_default_timezone_set('Asia/Jakarta');

        $data['page_title'] = 'KARISMA - LOGISTIK';

        $lokasi = $pic;

        $data['itemtotal']  = $this->M_Ics->total_barang_pic();
        $data['piclist']    = $this->M_Ics->get_list_barang_pic($lokasi);
        $data['lokasi']     = $lokasi;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/pic_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update_pic_lokasi()
    {
        $id_list   = $this->input->post('list_id'); // comma separated id
        $kd_barang = $this->input->post('kd_barang');
        $exp_date  = $this->input->post('expdate');
        $lokasi    = $this->input->post('lokasi');

        if (!$id_list || !$kd_barang || !$exp_date) {
            echo json_encode([
                'status' => false,
                'msg' => 'Data tidak lengkap'
            ]);
            return;
        }

        $ids = explode(',', $id_list);

        $this->db->trans_begin();

        $this->M_Ics->update_pic_saldo_awal($ids, $lokasi);
        $this->M_Ics->update_pic_ics($kd_barang, $exp_date, $lokasi);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                'status' => false,
                'msg' => 'Update gagal'
            ]);
        } else {
            $this->db->trans_commit();
            echo json_encode([
                'status' => true,
                'msg' => 'PIC berhasil diupdate'
            ]);
        }
        redirect('ics/barangpic');
    }


    public function master_gudang()
    {
        date_default_timezone_set('Asia/Jakarta');

        $data['page_title'] = 'KARISMA - LOGISTIK';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/master_gudang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function gudang_list()
    {
        $list = $this->M_Ics->getGudangServerSide();
        echo json_encode($list);
    }

    public function gudang_save()
    {
        $data = [
            'nama_gudang' => $this->input->post('nama_gudang'),
            'tipe'        => $this->input->post('tipe'),
        ];
        $this->M_Ics->insertGudang($data);
        echo json_encode(['status' => true]);
    }

    public function wilayah_by_gudang($id_gudang)
    {
        $data = $this->M_Ics->getWilayahByGudang($id_gudang);
        echo json_encode($data);
    }

    public function barang_per_gudang()
    {
        $list = $this->M_Ics->getBarangByGudangWilayah();
        echo json_encode($list);
    }

    public function detail_wilayah($id_gudang)
    {
        if (!$id_gudang) show_404();

        $data['page_title'] = 'KARISMA - LOGISTIK';

        $data['gudang']  = $this->M_Ics->getGudangById($id_gudang);
        $data['wilayah'] = $this->M_Ics->getWilayahByGudang($id_gudang);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/detail_gudang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function barangpergudang()
    {
        $induk = $this->M_Ics->get_gudang_induk();

        $data['page_title'] = 'KARISMA - LOGISTIK';
        $data['gudang'] = $this->M_Ics->get_gudang();
        $data['id_gudang_induk'] = $induk ? $induk->id_gudang : null;

        $this->load->view('partial/main/header', $data);
        $this->load->view('content/logistik/ics/barang_pergudang', $data);
        $this->load->view('partial/main/footer');
    }

    public function ajax_barang_pergudang()
    {
        $id_gudang = $this->input->post('id_gudang');

        if (!$id_gudang || $id_gudang == 'undefined') {
            $induk = $this->M_Ics->get_gudang_induk();
            $id_gudang = $induk ? $induk->id_gudang : null;
        }

        $data = $this->M_Ics->barangper_gudang($id_gudang);
        echo json_encode($data);
    }



    public function mutasi_barang()
    {

        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['faktur_mutasi']      = $this->M_Ics->get_faktur_mutasi();
        $data['gudang']             = $this->M_Ics->get_gudang();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/mutasi_barang.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/ics/ajax_mutasi.php');
    }

    public function ajax_filter_mutasi()
    {
        $data = $this->M_Ics->filter_mutasi(
            $this->input->post('gudang'),
            $this->input->post('daterange'),
            $this->input->post('status')
        );

        foreach ($data as $fm) {
            echo "<tr>
            <td>{$fm->tgl_transaksi}</td>
            <td>{$fm->noreff}</td>
            <td>{$fm->gudang_a}</td>
            <td>{$fm->gudang_b}</td>
            <td>{$fm->keterangan}</td>
            <td>{$fm->nm_karyawan}</td>
            <td>{$fm->status}</td>
            <td>
                <button class='btn btn-sm btn-warning btn-edit' data-id='{$fm->id}'>Edit</button>
                <button class='btn btn-sm btn-danger btn-rollback' data-id='{$fm->id}' data-ref='{$fm->noreff}'>Rollback</button>
                <button class='btn btn-sm btn-secondary btn-unpost' data-id='{$fm->id}'>Unpost</button>
            </td>
        </tr>";
        }
    }

    public function rollback()
    {
        $id = $this->input->post('id');

        $this->db->trans_start();

        $detail = $this->db->get_where('tb_detail_mutasi', ['noref' => $id])->result();
        foreach ($detail as $d) {
            $this->db->set('qty', 'qty+' . $d->qty, false)
                ->where('kode_barang_system', $d->kode_barang)
                ->update('tb_saldo_awal');
        }

        $this->db->where('id', $id)
            ->update('tb_mutasi', [
                'status' => 'ROLLBACK',
                'rollback_at' => date('Y-m-d H:i:s')
            ]);

        $this->db->trans_complete();
    }



    public function ajax_barang_select2()
    {
        $search = $this->input->get('term');
        $data   = $this->M_Ics->get_barang_select2($search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->nama_barang,
                'text' => $row->nama_barang
            ];
        }

        echo json_encode($result);
    }

    public function ajax_expired_by_barang()
    {
        $id_barang = $this->input->get('id_barang');

        $data = $this->M_Ics->get_expired_by_barang($id_barang);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->id,
                'text' => $row->exp_date
            ];
        }

        echo json_encode($result);
    }

    public function input_mutasi_barang()
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';
        $data['tanggal']    = date('Y-m-d');
        $data['ref_mutasi'] = $this->M_Ics->generate_noreff();
        $data['gudang']     = $this->M_Ics->get_gudang();
        $data['gudang_aktif'] = 2;


        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/input_mutasi_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_barang_by_gudang()
    {
        $search     = $this->input->get('term');
        $id_gudang  = $this->input->get('id_gudang');

        if (!$id_gudang) {
            echo json_encode([]);
            return;
        }

        $data = $this->M_Ics->get_barang_by_gudang_select2($id_gudang, $search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->nama_barang,
                'text' => $row->nama_barang
            ];
        }

        echo json_encode($result);
    }

    public function ajax_exp_by_gudang_barang()
    {
        $nama_barang = $this->input->get('nama_barang');
        $id_gudang   = $this->input->get('id_gudang');

        if (!$nama_barang || !$id_gudang) {
            echo json_encode([]);
            return;
        }

        $data = $this->M_Ics->get_exp_by_gudang_barang($id_gudang, $nama_barang);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->exp_date,     // ← HARUS TANGGAL
                'text' => $row->exp_date
            ];
        }

        echo json_encode($result);
    }

    public function ajax_get_qty_gudang()
    {
        $id_gudang    = $this->input->get('id_gudang');
        $nama_barang  = $this->input->get('nama_barang');
        $expired_date = $this->input->get('expired_date');


        $qty = $this->M_Ics->get_qty_by_gudang_barang_exp(
            $id_gudang,
            $nama_barang,
            $expired_date
        );

        echo json_encode(['qty' => (int) $qty]);
    }

    public function ajax_add_tmp_mutasi()
    {
        $data = [
            'nama_barang'  => $this->input->post('nama_barang'),
            'exp_date'     => $this->input->post('exp_date'),
            'qty'          => (int)$this->input->post('qty'),
            'satuan_id'    => $this->input->post('satuan_id'),
            'user_inputer' => $this->session->userdata('nik')
        ];

        if (!$data['nama_barang'] || !$data['exp_date'] || $data['qty'] <= 0) {
            echo json_encode(['status' => false, 'msg' => 'Data tidak valid']);
            return;
        }

        $this->M_Ics->insert_tmp_mutasi($data);

        echo json_encode(['status' => true]);
    }

    public function ajax_list_tmp_mutasi()
    {
        $user_id = $this->session->userdata('nik');
        $data = $this->M_Ics->get_tmp_mutasi_by_user($user_id);

        echo json_encode($data);
    }

    public function ajax_update_tmp_mutasi()
    {
        $id   = $this->input->post('id');
        $user = $this->session->userdata('nik');

        if (!$id || !$user) {
            echo json_encode(['status' => false, 'msg' => 'Data tidak valid']);
            return;
        }

        $this->M_Ics->update_tmp_mutasi(
            $id,
            $user,
            [
                'exp_date'  => $this->input->post('exp_date'),
                'qty'       => (int)$this->input->post('qty'),
                'satuan_id' => (int)$this->input->post('satuan_id')
            ]
        );

        echo json_encode(['status' => true, 'msg' => 'Update sukses']);
    }


    public function ajax_delete_tmp_mutasi()
    {
        $id   = $this->input->post('id');
        $user = $this->session->userdata('nik');

        $this->M_Ics->delete_tmp_mutasi($id, $user);
        echo json_encode(['status' => true]);
    }

    public function ajax_rekam_mutasi()
    {
        $user = $this->session->userdata('nik');
        $post = $this->input->post();

        if (!$post || !$user) {
            echo json_encode(['status' => false, 'msg' => 'Data tidak valid']);
            return;
        }

        if (
            empty($post['tgl_transaksi']) ||
            empty($post['fromgdg']) ||
            empty($post['tujuangdg'])
        ) {
            echo json_encode(['status' => false, 'msg' => 'Form mutasi belum lengkap']);
            return;
        }

        $tmp = $this->M_Ics->get_tmp_mutasi_by_user($user);
        if (!$tmp) {
            echo json_encode(['status' => false, 'msg' => 'Data mutasi kosong']);
            return;
        }

        $IS_HOLD = ($post['tujuangdg'] == '10');
        $STATUS  = $IS_HOLD ? 'HOLD' : 'POSTED';

        $this->db->trans_begin();

        $header = [
            'noreff'        => $post['nofresnsi'],
            'tgl_transaksi' => $post['tgl_transaksi'],
            'gudang_asal'   => $post['fromgdg'],
            'gudang_mutasi' => $post['tujuangdg'],
            'keterangan'    => $post['keterangan_mutasi'],
            'inputer'       => $user,
            'status'        => $STATUS,
            'input_at'      => date('Y-m-d H:i:s'),
            'last_action'   => 'CREATE'
        ];
        $this->db->insert('tb_mutasi', $header);

        if ($IS_HOLD) {

            $hold = [];
            $detail = [];
            foreach ($tmp as $t) {
                $hold[] = [
                    'noref'         => $post['nofresnsi'],
                    'kode_barang'   => $t->kd_barang,
                    'nama_barang'   => $t->nama_barang,
                    'gudang_asal'   => $post['fromgdg'],
                    'gudang_tujuan' => $post['tujuangdg'],
                    'exp_date'      => $t->exp_date,
                    'qty'           => $t->qty,
                    'satuan'        => $t->satuan_id,
                    'sumber'        => 'MUTASI',
                    'status'        => 'HOLD',
                    'input_by'      => $user,
                    'created_at'    => date('Y-m-d H:i:s')
                ];
                $detail[] = [
                    'noreff'        => $post['nofresnsi'],
                    'tgl_transaksi' => $post['tgl_transaksi'],
                    'gdg_asal'      => $post['fromgdg'],
                    'gdg_mutasi'    => $post['tujuangdg'],
                    'kode_barang'   => $t->kode_barang_system,
                    'kode_barang_zahir'   => $t->kd_barang,
                    'nama_barang'   => $t->nama_barang,
                    'exp_date'      => $t->exp_date,
                    'qty'           => $t->qty,
                    'satuan'        => $t->satuan_id,
                    'input_by'      => $user,
                    'create_at'     => date('Y-m-d H:i:s'),
                    'last_action'   => 'CREATE'
                ];
            }
            $this->db->insert_batch('tb_stock_hold', $hold);
            $this->db->insert_batch('tb_detail_mutasi', $detail);
        } else {

            $detail = [];
            foreach ($tmp as $t) {
                $detail[] = [
                    'noreff'        => $post['nofresnsi'],
                    'tgl_transaksi' => $post['tgl_transaksi'],
                    'gdg_asal'      => $post['fromgdg'],
                    'gdg_mutasi'    => $post['tujuangdg'],
                    'kode_barang'   => $t->kode_barang_system,
                    'kode_barang_zahir'   => $t->kd_barang,
                    'nama_barang'   => $t->nama_barang,
                    'exp_date'      => $t->exp_date,
                    'qty'           => $t->qty,
                    'satuan'        => $t->satuan_id,
                    'input_by'      => $user,
                    'create_at'     => date('Y-m-d H:i:s'),
                    'last_action'   => 'CREATE'
                ];
            }
            $this->db->insert_batch('tb_detail_mutasi', $detail);
        }

        $this->db->where('user_inputer', $user)->delete('tb_tmp_mutasi');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'msg' => 'Gagal merekam mutasi']);
            return;
        }

        $this->db->trans_commit();

        $this->db->insert('tb_log_mutasi', [
            'noreff'     => $post['nofresnsi'],
            'aksi'       => $STATUS,
            'keterangan' => $IS_HOLD
                ? 'MUTASI HOLD GUDANG ' . $post['fromgdg']
                : 'MUTASI POSTED GUDANG ' . $post['fromgdg'],
            'user'       => $user,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode([
            'status' => true,
            'msg'    => $IS_HOLD
                ? 'Mutasi berhasil direkam sebagai HOLD'
                : 'Mutasi berhasil direkam',
            'noreff' => $post['nofresnsi']
        ]);
    }

    public function ajax_detail_mutasi()
    {
        $noreff = $this->input->post('noreff');

        if (!$noreff) {
            echo json_encode(['status' => false, 'msg' => 'No Ref tidak valid']);
            return;
        }

        $header = $this->M_Ics->get_mutasi_header($noreff);
        if (!$header) {
            echo json_encode(['status' => false, 'msg' => 'Data mutasi tidak ditemukan']);
            return;
        }

        $detail = $this->M_Ics->get_mutasi_detail($noreff, $header->status);

        echo json_encode([
            'status' => true,
            'header' => $header,
            'detail' => $detail
        ]);
    }

    public function ajax_unpost_mutasi()
    {
        $noreff = $this->input->post('noreff');

        if (!$noreff) {
            echo json_encode(['status' => false, 'msg' => 'No ref tidak valid']);
            return;
        }

        $this->db->where('noreff', $noreff)
            ->update('tb_mutasi', [
                'status' => 'UNPOST'
            ]);

        $this->db->insert('tb_log_mutasi', [
            'noreff' => $noreff,
            'aksi' => 'UNPOST',
            'keterangan' => 'UNPOST MUTASI',
            'user' => $this->session->userdata('nik')
        ]);

        echo json_encode(['status' => true, 'msg' => 'Mutasi berhasil di-unpost']);
    }

    public function ajax_delete_mutasi()
    {
        $noreff = $this->input->post('noreff');
        if (!$noreff) {
            echo json_encode(['status' => false, 'msg' => 'No ref tidak valid']);
            return;
        }

        $this->db->trans_begin();

        $this->db->where('noreff', $noreff)->delete('tb_detail_mutasi');
        $this->db->where('noreff', $noreff)->delete('tb_mutasi');
        $this->db->where('noref', $noreff)->delete('tb_stock_hold');

        $this->db->insert('tb_log_mutasi', [
            'noreff' => $noreff,
            'aksi' => 'DELETE',
            'keterangan' => 'DELETE MUTASI',
            'user' => $this->session->userdata('nik')
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'msg' => 'Gagal menghapus mutasi']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => true, 'msg' => 'Mutasi berhasil dihapus']);
        }
    }

    public function ajax_rollback_mutasi()
    {
        $noreff = $this->input->post('noreff');
        if (!$noreff) {
            echo json_encode(['status' => false, 'msg' => 'No ref tidak valid']);
            return;
        }

        $user = $this->session->userdata('nik');

        $this->db->trans_begin();

        $stock_hold = $this->db
            ->where('noref', $noreff)
            ->where('released_at IS NULL', null, false)
            ->get('tb_stock_hold')
            ->result();

        if (empty($stock_hold)) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'msg' => 'Data stock hold tidak ditemukan / sudah direlease']);
            return;
        }

        $this->db
            ->where('noreff', $noreff)
            ->update('tb_detail_mutasi', [
                'gdg_mutasi' => '2'
            ]);

        $this->db
            ->where('noref', $noreff)
            ->update('tb_stock_hold', [
                'status' => 'RELEASED',
                'released_at' => date('Y-m-d H:i:s')
            ]);

        $this->db
            ->where('noreff', $noreff)
            ->update('tb_mutasi', [
                'status' => 'POSTED'
            ]);

        $this->db->insert('tb_log_mutasi', [
            'noreff'     => $noreff,
            'aksi'       => 'ROLLBACK',
            'keterangan' => 'Rollback dari stock hold',
            'user'       => $user,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'msg' => 'Rollback mutasi gagal']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => true, 'msg' => 'Rollback mutasi berhasil']);
        }
    }


    // LOGISTIK V2
    public function saldo_stock()
    {

        $data['page_title'] = 'KARISMA - LOGISTIK';
        $data['saldo'] = $this->M_Ics->get_saldo();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/stock_saldo.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_negatice()
    {
        $data['saldo'] = $this->saldo->get_negative_stock();
        $this->load->view('stock/saldo_index', $data);
    }

    // LPB 

    public function create_lpb()
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/lpbform.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function store_lpb()
    {
        $header = [
            'kode_faktur' => $this->input->post('kode_faktur'),
            'id_gudang'   => $this->input->post('id_gudang'),
            'tanggal'     => date('Y-m-d'),
            'status'      => 'DRAFT'
        ];

        $detail[] = [
            'kode_faktur'       => $header['kode_faktur'],
            'kode_barang_system' => $this->input->post('kode_barang_system'),
            'no_lot'            => $this->input->post('no_lot'),
            'exp_date'          => $this->input->post('exp_date'),
            'qty'               => $this->input->post('qty')
        ];

        $this->db->trans_begin();

        $this->M_Ics->create_header($header);
        $this->M_Ics->create_detail($detail);
        $this->M_Ics->post_lpb($header['kode_faktur']);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo "Gagal simpan LPB";
        } else {
            $this->db->trans_commit();
            redirect('stock/saldo');
        }
    }
}
