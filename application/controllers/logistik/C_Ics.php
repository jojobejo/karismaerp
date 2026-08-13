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
        $this->load->model('M_ReturPembelian');
        $this->load->model('M_LpbPriceAdjustment');
        $this->load->helper('stock_helper');
        date_default_timezone_set('Asia/Jakarta');

        $method = $this->router->fetch_method();
        if ($this->is_retur_method($method)) {
            $this->require_retur_access(strpos($method, 'ajax_') === 0);
        }
    }

    private function is_retur_method($method)
    {
        return in_array($method, ['dash_retur', 'detail_retur', 'retur_pembelian', 'retur_pembelian_adjustment'], true)
            || strpos((string)$method, 'ajax_retur') === 0;
    }

    private function require_retur_access($json = false)
    {
        if (!$this->session->userdata('logged_in')) {
            if ($json) {
                $this->json_response(['status' => false, 'message' => 'Sesi login tidak ditemukan.', 'errors' => ['AUTH_REQUIRED']]);
                exit;
            }
            redirect('Auth');
            exit;
        }

        if ($this->has_retur_access()) {
            return;
        }

        if ($json) {
            $this->json_response(['status' => false, 'message' => 'Anda tidak memiliki akses ke module retur.', 'errors' => ['RETUR_ACCESS_DENIED']]);
            exit;
        }

        show_error('Anda tidak memiliki akses ke module retur.', 403, 'Akses Ditolak');
    }

    private function has_retur_access()
    {
        $username = strtolower(trim((string)$this->session->userdata('username')));
        $departemen = strtoupper(trim((string)($this->session->userdata('departemen') ?: $this->session->userdata('departement'))));
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));

        if ($username === 'admin' || (bool)$this->session->userdata('is_admin_dashboard')) {
            return true;
        }

        $allowedDepartemen = ['LOGISTIK', 'PURCHASING', 'KEUANGAN', 'FINANCE', 'ACCOUNTING', 'IT'];
        foreach ($allowedDepartemen as $allowed) {
            if ($departemen === $allowed || strpos($departemen, $allowed) !== false) {
                return true;
            }
        }

        $allowedJobdesk = [
            'LOGISTIK',
            'ADMINLOGLPB',
            'ADMLPB2',
            'ADMINICS',
            'ADMLOG',
            'ADMINPURCHASING',
            'ADMIN PO',
            'PURCHASING',
            'ADMINKEU',
            'ADMINKEUTC',
            'KIUKEU',
            'KEUANGAN',
            'ACCOUNTING',
            'FINANCE',
            'IT',
            'ADMINIT',
            'ADMIN IT',
            'PROGRAMMER',
            'DEVELOPMENT'
        ];

        return in_array($jobdesk, $allowedJobdesk, true);
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
            ->select('a.kode_barang_zahir, a.exp_date')
            ->from('tb_saldo_awal a')
            ->join('tbpo_barang b', 'b.kode_barang = a.kode_barang_zahir')
            ->where('a.kode_barang_zahir', $kdbarang)
            ->group_by('a.exp_date')
            ->get()
            ->row();

        if (!$data_barang) {
            show_404();
        }

        $kdmaster    = $data_barang->kode_barang_zahir;
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
            JOIN tbpo_barang b ON b.kode_barang = a.kode_barang_zahir
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_pending , kd_barang
                FROM tb_ics_do
                GROUP BY kd_barang, exp_date
            ) pending ON pending.kd_barang = a.kode_barang_zahir AND pending.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_po , kd_barang
                FROM tb_ics_po
                GROUP BY kd_barang, exp_date
            ) purchase ON purchase.kd_barang = a.kode_barang_zahir AND purchase.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_opname , kd_system
                FROM tb_ics
                GROUP BY kd_system, exp_date
            ) opname ON opname.kd_system = a.kode_barang_zahir AND opname.exp_date = a.exp_date
            WHERE a.kode_barang_zahir = ? AND a.exp_date = ?
            GROUP BY a.kode_barang_zahir, a.exp_date", array($kdmaster, $exp_date));

        $data['list_gudang'] = $this->db
            ->where('is_active', 1)
            ->get('tb_gudang')->result();

        $data['list_gudang_wilayah'] = $this->db
            ->where('is_active', 1)
            ->get('tb_gudang_wilayah')->result();

        $data['detail_stok']        = $query->result();
        $data['page_title']         = 'KARISMA - ICS';
        $data['get_barang']         = $this->M_Ics->get_detail_barang($kd);
        $data['list_stock_by_exp']  = $this->M_Ics->tracking_br_diffrent_by_expdate($kdmaster);

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

        $data_retur = $this->db
            ->select([
                'r.kd_faktur',
                'r.tgl_input AS tgl_transaksi',
                'm.nama_barang',
                'r.qty'
            ])
            ->from('tb_detail_retur_barang r')
            ->join(
                'tbpo_barang m',
                'm.kode_barang = r.kd_barang',
                'left'
            )
            ->where('r.kd_barang', $kd_barang)
            ->where('r.tgl_expired', $exp_date)
            ->get()
            ->result();

        echo json_encode([
            'data_do'  => $data_do,
            'data_po'  => $data_po,
            'data_retur'  => $data_retur,
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
        // $data['ics_do']             = $this->M_Ics->list_do_today($tgl);
        $data['ics_do']             = $this->M_Ics->list_all_do();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/icsdo.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_po()
    {
        $date1 = $this->input->post('date1');
        $date2 = $this->input->post('date2');
        $this->load->model('Api/M_Api', 'apiPo');
        $isAdminPo = $this->is_admin_po_jobdesk();
        $lpbPanelMode = $this->resolve_ics_po_panel_mode();
        $showLogistikPanel = $lpbPanelMode !== 'purchasing';
        $showPurchasingPanel = $lpbPanelMode !== 'logistik';
        $username = strtolower(trim((string) $this->session->userdata('username')));
        $canSyncPo = (
            ($this->session->userdata('lv') == '1' && strtoupper(trim((string) $this->session->userdata('jobdesk'))) !== 'ADMINICS')
            || $isAdminPo
            || $username === 'admpo'
        );

        $data['page_title'] = 'KARISMA - LOGISTIK';
        $data['is_admin_po'] = $isAdminPo;
        $data['can_sync_po'] = $canSyncPo;
        $data['can_lpb_manual'] = $this->can_access_lpb_manual();
        $data['can_lpb_report'] = $this->can_access_lpb_report();
        $data['lpb_panel_mode'] = $lpbPanelMode;
        $data['show_logistik_panel'] = $showLogistikPanel;
        $data['show_purchasing_panel'] = $showPurchasingPanel;
        $data['lpb']        = $showLogistikPanel
            ? $this->M_Logistik->get_lpb($date1, $date2)
            : [];
        $data['lpb_purchasing'] = $showPurchasingPanel
            ? $this->M_Logistik->get_lpb_purchasing_view($date1, $date2)
            : [];
        $data['date1']      = $date1;
        $data['date2']      = $date2;
        $data['sync_api_url'] = base_url('sync_pre_po_erp');
        $data['sync_rows']    = $this->apiPo->get_recent_pre_po(100);
        $data['last_sync']    = $this->apiPo->get_last_sync_info();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/icspo.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function data_lpb()
    {
        $date1 = $this->input->post('date1');
        $date2 = $this->input->post('date2');

        $data['page_title'] = 'KARISMA - Data LPB';
        $data['is_admin_po'] = $this->is_admin_po_jobdesk();
        $data['can_sync_po'] = FALSE;
        $data['can_lpb_manual'] = $this->can_access_lpb_manual();
        $data['can_lpb_report'] = $this->can_access_lpb_report();
        $data['is_data_lpb_page'] = TRUE;
        $data['is_admlpb_user'] = $this->is_admlpb_user();
        $data['lpb_panel_mode'] = 'logistik';
        $data['show_logistik_panel'] = TRUE;
        $data['show_purchasing_panel'] = FALSE;
        $data['hide_lpb_supplier_code'] = TRUE;
        $data['hide_lpb_last_input'] = TRUE;
        $data['lpb'] = $this->M_Logistik->get_lpb($date1, $date2);
        $data['lpb_purchasing'] = [];
        $data['date1'] = $date1;
        $data['date2'] = $date2;
        $data['sync_api_url'] = '';
        $data['sync_rows'] = [];
        $data['last_sync'] = [];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/icspo.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_po()
    {
        $no_po = $this->input->get('no_po');
        $kd_suplier = $this->input->get('kd_suplier');

        $nopo      = urldecode($no_po);
        $kdsuplier = urldecode($kd_suplier);

        if (empty($no_po)) redirect('ics/icspo');

        $data['page_title'] = 'KARISMA - Detail PO';
        $data['no_po']      = $no_po;
        $data['kd_suplier'] = $kd_suplier;
        $data['list_gudang'] = $this->db
            ->select('id_gudang, nama_gudang')
            ->order_by('nama_gudang', 'ASC')
            ->get('tb_gudang')
            ->result_array();
        $data['lpb_type_options'] = $this->M_Logistik->get_lpb_type_options();
        $data['detail']     = $this->M_Logistik->detail_po_received($nopo, $kdsuplier);
        $data['kd_po']      = !empty($data['detail'][0]['kd_po']) ? $data['detail'][0]['kd_po'] : '';
        $data['is_detail_po_purchasing'] = $this->resolve_ics_po_panel_mode() === 'purchasing';
        $data['is_admlpb_user']          = $this->is_admlpb_user();
        $data['is_admin_po']             = $this->is_admin_po_jobdesk();
        $data['show_checker_input']      = $data['is_admlpb_user'] || $data['is_admin_po'];
        if ($data['is_detail_po_purchasing']) {
            $lpbPoRecords = $this->M_Logistik->get_lpb_purchasing_view(null, null, $nopo, $data['kd_po'], FALSE);
            $data['lpb_po_records'] = $this->M_Logistik->group_lpb_purchasing_records_by_nomor_lpb($lpbPoRecords);
        } else {
            $data['lpb_po_records'] = [];
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/detail_po.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_get_detail_po_rows()
    {
        while (ob_get_level()) ob_end_clean();

        $no_po      = trim((string) $this->input->get('no_po', TRUE));
        $kd_suplier = trim((string) $this->input->get('kd_suplier', TRUE));

        header('Content-Type: application/json; charset=utf-8');

        if ($no_po === '' || $kd_suplier === '') {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Parameter no_po dan kd_suplier wajib diisi.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'rows'   => $this->M_Logistik->detail_po_received(urldecode($no_po), urldecode($kd_suplier))
        ]);
    }

    public function detail_record_lpb()
    {
        $kd_po = urldecode((string) $this->input->get('kd_po'));
        $no_po = urldecode((string) $this->input->get('no_po'));
        $kd_suplier = urldecode((string) $this->input->get('kd_suplier'));

        if ($kd_po === '') {
            redirect('ics/icspo');
        }

        $data['page_title'] = 'KARISMA - Record LPB PO';
        $data['kd_po']      = $kd_po;
        $data['no_po']      = $no_po;
        $data['kd_suplier'] = $kd_suplier;
        $data['initial_id_lpb'] = (int) $this->input->get('id_lpb', TRUE);
        $data['is_admin_po'] = $this->is_admin_po_jobdesk();
        $data['lpb_record_view_mode'] = $this->resolve_ics_po_panel_mode();
        $data['lpb_type_options'] = $this->M_Logistik->get_lpb_type_options();
        $this->M_Logistik->ensure_lpb_invoice_faktur_columns();
        $this->M_Logistik->ensure_lpb_workflow_columns();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/detail_record_lpb.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function lpb_manual()
    {
        if (!$this->can_access_lpb_manual()) {
            show_error('Akses input LPB Manual hanya untuk Purchasing, ADMIN PO, IT, atau Admin.', 403);
            return;
        }

        $this->M_Logistik->ensure_lpb_manual_schema();

        $data['page_title'] = 'KARISMA - Input LPB Manual';
        $data['list_gudang'] = $this->db
            ->select('id_gudang, nama_gudang')
            ->order_by('nama_gudang', 'ASC')
            ->get('tb_gudang')
            ->result_array();
        $data['lpb_type_options'] = $this->M_Logistik->get_lpb_type_options();
        $data['manual_ref'] = $this->M_Logistik->generate_lpb_manual_ref();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/lpb_manual.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_lpb_manual_barang()
    {
        if (!$this->can_access_lpb_manual()) {
            $this->json_response(['status' => 'error', 'message' => 'Akses ditolak.', 'results' => []]);
            return;
        }

        $term = trim((string) $this->input->get('q', TRUE));
        $rows = $this->M_Logistik->search_lpb_manual_barang($term, 30);
        $results = [];

        foreach ($rows as $row) {
            $results[] = [
                'id' => $row['kode_barang'],
                'text' => $row['kode_barang'] . ' - ' . $row['nama_barang'],
                'kode_barang' => $row['kode_barang'],
                'nama_barang' => $row['nama_barang'],
                'satuan' => $row['satuan'],
                'isi' => $row['isi'],
                'kemasan' => $row['kemasan']
            ];
        }

        $this->json_response(['status' => 'success', 'results' => $results]);
    }

    public function ajax_lpb_manual_store()
    {
        if (!$this->can_access_lpb_manual()) {
            $this->json_response(['status' => 'error', 'message' => 'Akses input LPB Manual ditolak.']);
            return;
        }

        $payload = [
            'manual_ref_no' => trim((string) $this->input->post('manual_ref_no', TRUE)),
            'tgl_lpb' => trim((string) $this->input->post('tgl_lpb', TRUE)),
            'jenis_lpb' => trim((string) $this->input->post('jenis_lpb', TRUE)),
            'gudang_id' => trim((string) $this->input->post('gudang_id', TRUE)),
            'nosj' => trim((string) $this->input->post('nosj', TRUE)),
            'no_invoice' => trim((string) $this->input->post('no_invoice', TRUE)),
            'keterangan' => trim((string) $this->input->post('keterangan', TRUE)),
            'dilakukan_oleh' => $this->active_user_name(),
            'checker_name' => $this->active_user_name(),
            'checker_by' => $this->active_user_code()
        ];

        if ($payload['manual_ref_no'] === '') {
            $payload['manual_ref_no'] = $this->M_Logistik->generate_lpb_manual_ref();
        }

        $kodeBarang = (array) $this->input->post('kd_barang');
        $qty = (array) $this->input->post('qty_diterima');
        $satuan = (array) $this->input->post('satuan');
        $noLot = (array) $this->input->post('no_lot');
        $expiredDate = (array) $this->input->post('expired_date');
        $hargaSatuan = (array) $this->input->post('harga_satuan');
        $detailRows = [];

        foreach ($kodeBarang as $index => $kode) {
            $kode = trim((string) $kode);
            $qtyValue = (float) str_replace(',', '.', (string) ($qty[$index] ?? 0));

            if ($kode === '' && $qtyValue <= 0) {
                continue;
            }

            $detailRows[] = [
                'kd_barang' => $kode,
                'qty_diterima' => $qtyValue,
                'satuan' => trim((string) ($satuan[$index] ?? '')),
                'no_lot' => trim((string) ($noLot[$index] ?? '')),
                'expired_date' => trim((string) ($expiredDate[$index] ?? '')),
                'harga_satuan' => (float) str_replace(',', '.', (string) ($hargaSatuan[$index] ?? 0))
            ];
        }

        $validation = $this->M_Logistik->validate_lpb_manual_payload($payload, $detailRows);
        if (empty($validation['status'])) {
            $this->M_Logistik->insert_lpb_manual_system_log([
                'action_type' => 'VALIDATE_MANUAL_LPB',
                'status' => 'FAILED',
                'manual_ref_no' => $payload['manual_ref_no'],
                'message' => $validation['message'] ?? 'Validasi LPB Manual gagal.',
                'payload' => ['header' => $payload, 'detail_rows' => $detailRows],
                'created_by' => $this->active_user_name(),
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent()
            ]);
            $this->json_response(['status' => 'error', 'message' => $validation['message'] ?? 'Data LPB Manual belum lengkap.']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->M_Logistik->ensure_lpb_manual_schema();
        $this->db->trans_begin();
        $idLpb = $this->M_Logistik->create_lpb_manual($payload, $validation['detail_rows']);

        if (!$idLpb || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->M_Logistik->insert_lpb_manual_system_log([
                'action_type' => 'CREATE_MANUAL_LPB',
                'status' => 'FAILED',
                'manual_ref_no' => $payload['manual_ref_no'],
                'message' => 'Gagal menyimpan LPB Manual ke tabel transaksi.',
                'payload' => ['header' => $payload, 'detail_rows' => $validation['detail_rows']],
                'created_by' => $this->active_user_name(),
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent()
            ]);
            $this->json_response(['status' => 'error', 'message' => 'Gagal menyimpan LPB Manual.']);
            return;
        }

        $this->db->trans_commit();

        $this->json_response([
            'status' => 'success',
            'message' => 'LPB Manual berhasil disimpan dan stok tercatat di batch serta ledger.',
            'id_lpb' => (int) $idLpb,
            'manual_ref_no' => $payload['manual_ref_no'],
            'redirect_url' => base_url('ics/lpb_report?source=manual')
        ]);
    }

    public function lpb_report()
    {
        if (!$this->can_access_lpb_report()) {
            show_error('Akses laporan LPB ditolak.', 403);
            return;
        }

        $this->load->model('M_LaporanPurchasing');
        $this->M_LaporanPurchasing->ensure_schema();

        $filters = [
            'source'        => trim((string) $this->input->get('source', TRUE)) ?: 'all',
            'status_lpb'    => trim((string) $this->input->get('status_lpb', TRUE)) ?: 'all',
            'aging_fp'      => trim((string) $this->input->get('aging_fp', TRUE)) ?: 'all',
            'jenis_lpb'     => trim((string) $this->input->get('jenis_lpb', TRUE)) ?: 'all',
            'aging_invoice' => trim((string) $this->input->get('aging_invoice', TRUE)) ?: 'all',
            'date1'         => trim((string) $this->input->get('date1', TRUE)),
            'date2'         => trim((string) $this->input->get('date2', TRUE))
        ];

        $data['page_title'] = 'KARISMA - Laporan Digital Purchasing & LPB';
        $data['filters']    = $filters;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/lpb_report.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_lpb_report_data()
    {
        if (!$this->can_access_lpb_report()) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Akses ditolak']));
            return;
        }

        $this->load->model('M_LaporanPurchasing');

        $draw   = (int) $this->input->post('draw');
        $start  = (int) $this->input->post('start');
        $length = (int) $this->input->post('length') ?: 25;
        $search = trim((string) ($this->input->post('search')['value'] ?? ''));

        $filters = [
            'source'        => trim((string) $this->input->post('source')) ?: 'all',
            'status_lpb'    => trim((string) $this->input->post('status_lpb')) ?: 'all',
            'aging_fp'      => trim((string) $this->input->post('aging_fp')) ?: 'all',
            'jenis_lpb'     => trim((string) $this->input->post('jenis_lpb')) ?: 'all',
            'aging_invoice' => trim((string) $this->input->post('aging_invoice')) ?: 'all',
            'date1'         => trim((string) $this->input->post('date1')),
            'date2'         => trim((string) $this->input->post('date2'))
        ];

        $orderArr = $this->input->post('order');
        $orderColIndex = $orderArr[0]['column'] ?? 0;
        $orderDir      = $orderArr[0]['dir'] ?? 'desc';

        $columnsMap = [
            0  => 'id_lpb',
            1  => 'tgl_po',
            2  => 'no_po',
            3  => 'tgl_perubahan_po',
            4  => 'top_days',
            5  => 'tgl_lpb',
            6  => 'nomor_lpb',
            7  => 'jenis_lpb',
            8  => 'source_type',
            9  => 'nosj',
            10 => 'no_invoice',
            11 => 'tanggal_invoice',
            12 => 'tgl_perubahan_invoice',
            13 => 'tgl_riil_invoice',
            14 => 'kd_supplier',
            15 => 'nama_supplier',
            16 => 'kd_barang',
            17 => 'nama_barang',
            18 => 'produsen',
            19 => 'spesifikasi_merk',
            20 => 'golongan',
            21 => 'kelompok',
            22 => 'komposisi',
            23 => 'grup',
            24 => 'no_batch',
            25 => 'exp_date',
            26 => 'qty_diterima',
            27 => 'harga_satuan',
            28 => 'total_harga',
            29 => 'sales_disc',
            30 => 'cbd',
            31 => 'foc',
            32 => 'insentif_cn',
            33 => 'dpp',
            34 => 'ppn_11',
            35 => 'ppn_12',
            36 => 'dpp_nilai_lain',
            37 => 'no_seri_fp',
            38 => 'tgl_fp',
            39 => 'tgl_terima_fp',
            40 => 'tgl_input_fp',
            41 => 'lapor_spt_masa',
            42 => 'lead_time_po_lpb',
            43 => 'lead_time_fp_today'
        ];

        $orderCol = $columnsMap[$orderColIndex] ?? 'id_lpb';

        try {
            $totalRecords = $this->M_LaporanPurchasing->get_total_records($filters);
            $rowsData     = $this->M_LaporanPurchasing->get_datatables_data($filters, $search, $start, $length, $orderCol, $orderDir);

            $response = [
                "draw"            => $draw,
                "recordsTotal"    => $totalRecords,
                "recordsFiltered" => $search !== '' ? count($rowsData) : $totalRecords,
                "data"            => $rowsData
            ];
        } catch (\Throwable $ex) {
            $response = [
                "draw"            => $draw,
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => [],
                "error"           => $ex->getMessage()
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Halaman Dashboard Ringkasan Hutang Purchasing (Summary Per Faktur/Invoice)
     */
    public function summary_hutang()
    {
        if (!$this->can_access_lpb_report()) {
            show_error('Akses dashboard summary hutang ditolak.', 403);
            return;
        }

        $this->load->model('M_LaporanPurchasing');
        $this->M_LaporanPurchasing->ensure_schema();

        $filters = [
            'date1' => trim((string) $this->input->get('date1', TRUE)),
            'date2' => trim((string) $this->input->get('date2', TRUE))
        ];

        $data['page_title'] = 'KARISMA - Dashboard Summary Hutang Purchasing (Per Faktur)';
        $data['filters']    = $filters;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/summary_hutang_purchasing.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Endpoint DataTables AJAX untuk Summary Hutang Purchasing Per Faktur
     */
    public function ajax_summary_hutang_data()
    {
        if (!$this->can_access_lpb_report()) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Akses ditolak']));
            return;
        }

        $this->load->model('M_LaporanPurchasing');

        $draw   = (int) $this->input->post('draw');
        $start  = (int) $this->input->post('start');
        $length = (int) $this->input->post('length') ?: 25;
        $search = trim((string) ($this->input->post('search')['value'] ?? ''));

        $filters = [
            'date1' => trim((string) $this->input->post('date1')),
            'date2' => trim((string) $this->input->post('date2'))
        ];

        $orderArr = $this->input->post('order');
        $orderColIndex = $orderArr[0]['column'] ?? 0;
        $orderDir      = $orderArr[0]['dir'] ?? 'desc';

        try {
            $totalRecords = $this->M_LaporanPurchasing->get_summary_hutang_total_records($filters);
            $rowsData     = $this->M_LaporanPurchasing->get_summary_hutang_data($filters, $search, $start, $length, $orderColIndex, $orderDir);

            $response = [
                "draw"            => $draw,
                "recordsTotal"    => $totalRecords,
                "recordsFiltered" => $search !== '' ? count($rowsData) : $totalRecords,
                "data"            => $rowsData
            ];
        } catch (\Throwable $ex) {
            $response = [
                "draw"            => $draw,
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => [],
                "error"           => $ex->getMessage()
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function export_lpb_report_excel()
    {
        if (!$this->can_access_lpb_report()) {
            show_error('Akses laporan LPB ditolak.', 403);
            return;
        }

        $this->load->model('M_LaporanPurchasing');

        $filters = [
            'source'        => trim((string) $this->input->get('source', TRUE)) ?: 'all',
            'aging_fp'      => trim((string) $this->input->get('aging_fp', TRUE)) ?: 'all',
            'jenis_lpb'     => trim((string) $this->input->get('jenis_lpb', TRUE)) ?: 'all',
            'aging_invoice' => trim((string) $this->input->get('aging_invoice', TRUE)) ?: 'all',
            'date1'         => trim((string) $this->input->get('date1', TRUE)),
            'date2'         => trim((string) $this->input->get('date2', TRUE))
        ];

        $rowsData = $this->M_LaporanPurchasing->get_report_data_for_export($filters);
        $filename = 'Laporan_Digital_Purchasing_LPB_' . date('Ymd_His') . '.xlsx';

        if (file_exists(APPPATH . 'libraries/PhpSpreadsheet.php')) {
            try {
                require_once APPPATH . 'libraries/PhpSpreadsheet.php';
                $ps = new PhpSpreadsheetLib();
                $spreadsheet = $ps->spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Laporan LPB');

                $headers = [
                    'No', 'Tgl PO', 'No PO', 'Tgl Perubahan PO', 'TOP (Hari)',
                    'Tgl LPB', 'No LPB', 'Jenis LPB', 'Sumber', 'No SJ',
                    'No Invoice', 'Tgl Invoice', 'Tgl Perubahan Invoice', 'Tgl Riil Invoice',
                    'Kode Supplier', 'Nama Supplier', 'Kode Barang', 'Nama Barang',
                    'Produsen', 'Spesifikasi Merk', 'Golongan', 'Kelompok', 'Komposisi', 'Grup',
                    'No Batch/Lot', 'Exp Date', 'Qty Diterima', 'Harga Satuan', 'Total Harga',
                    'Sales Disc', 'CBD', 'FOC', 'Insentif CN', 'DPP', 'PPN 11%', 'PPN 12%', 'DPP Nilai Lain',
                    'No Seri FP', 'Tgl FP', 'Tgl Terima FP', 'Tgl Input FP', 'SPT Masa',
                    'LT PO-LPB (Hari)', 'LT FP-Hari Ini (Hari)', 'Aging FP', 'Aging Invoice'
                ];

                $col = 1;
                foreach ($headers as $h) {
                    $sheet->setCellValueByColumnAndRow($col, 1, $h);
                    $col++;
                }

                $rowNum = 2;
                foreach ($rowsData as $index => $row) {
                    $c = 1;
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $index + 1);
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tgl_po'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['no_po'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tgl_perubahan_po'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, isset($row['top_days']) ? $row['top_days'] . ' Hari' : '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tgl_lpb'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['nomor_lpb'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['jenis_lpb'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['source_type'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['nosj'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['no_invoice'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tanggal_invoice'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tgl_perubahan_invoice'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tgl_riil_invoice'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['kd_supplier'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['nama_supplier'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['kd_barang'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['nama_barang'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['produsen'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['spesifikasi_merk'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['golongan'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['kelompok'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['komposisi'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['grup'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['no_batch'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['exp_date'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['qty_diterima'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['harga_satuan'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['total_harga'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['sales_disc'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['cbd'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['foc'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['insentif_cn'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['dpp'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['ppn_11'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['ppn_12'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, (float) ($row['dpp_nilai_lain'] ?? 0));
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['no_seri_fp'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tgl_fp'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tgl_terima_fp'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['tgl_input_fp'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['lapor_spt_masa'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['lead_time_po_lpb'] !== null ? $row['lead_time_po_lpb'] . ' Hari' : '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['lead_time_fp_today'] !== null ? $row['lead_time_fp_today'] . ' Hari' : '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['aging_fp_category'] ?? '-');
                    $sheet->setCellValueByColumnAndRow($c++, $rowNum, $row['aging_invoice_category'] ?? '-');
                    $rowNum++;
                }

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = $ps->writer($spreadsheet);
                $writer->save('php://output');
                exit;
            } catch (\Throwable $ex) {
                // Fallback
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo "<table border='1'>";
        echo "<thead><tr>";
        $headers = [
            'No', 'Tgl PO', 'No PO', 'Tgl Perubahan PO', 'TOP (Hari)',
            'Tgl LPB', 'No LPB', 'Jenis LPB', 'Sumber', 'No SJ',
            'No Invoice', 'Tgl Invoice', 'Tgl Perubahan Invoice', 'Tgl Riil Invoice',
            'Kode Supplier', 'Nama Supplier', 'Kode Barang', 'Nama Barang',
            'Produsen', 'Spesifikasi Merk', 'Golongan', 'Kelompok', 'Komposisi', 'Grup',
            'No Batch/Lot', 'Exp Date', 'Qty Diterima', 'Harga Satuan', 'Total Harga',
            'Sales Disc', 'CBD', 'FOC', 'Insentif CN', 'DPP', 'PPN 11%', 'PPN 12%', 'DPP Nilai Lain',
            'No Seri FP', 'Tgl FP', 'Tgl Terima FP', 'Tgl Input FP', 'SPT Masa',
            'LT PO-LPB (Hari)', 'LT FP-Hari Ini (Hari)', 'Aging FP', 'Aging Invoice'
        ];
        foreach ($headers as $h) echo "<th>" . htmlspecialchars($h) . "</th>";
        echo "</tr></thead><tbody>";
        foreach ($rowsData as $index => $row) {
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . htmlspecialchars($row['tgl_po'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['no_po'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['tgl_perubahan_po'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars(isset($row['top_days']) ? $row['top_days'] . ' Hari' : '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['tgl_lpb'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['nomor_lpb'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['jenis_lpb'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['source_type'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['nosj'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['no_invoice'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['tanggal_invoice'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['tgl_perubahan_invoice'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['tgl_riil_invoice'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['kd_supplier'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_supplier'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['kd_barang'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_barang'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['produsen'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['spesifikasi_merk'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['golongan'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['kelompok'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['komposisi'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['grup'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['no_batch'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['exp_date'] ?? '-') . "</td>";
            echo "<td>" . (float)($row['qty_diterima'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['harga_satuan'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['total_harga'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['sales_disc'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['cbd'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['foc'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['insentif_cn'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['dpp'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['ppn_11'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['ppn_12'] ?? 0) . "</td>";
            echo "<td>" . (float)($row['dpp_nilai_lain'] ?? 0) . "</td>";
            echo "<td>" . htmlspecialchars($row['no_seri_fp'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['tgl_fp'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['tgl_terima_fp'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['tgl_input_fp'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['lapor_spt_masa'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['lead_time_po_lpb'] !== null ? $row['lead_time_po_lpb'] . ' Hari' : '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['lead_time_fp_today'] !== null ? $row['lead_time_fp_today'] . ' Hari' : '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['aging_fp_category'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['aging_invoice_category'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        exit;
    }

    public function lpb_manual_log()
    {
        if (!$this->can_access_lpb_manual_log()) {
            show_error('Akses log sistem LPB Manual hanya untuk IT atau Admin.', 403);
            return;
        }

        $this->M_Logistik->ensure_lpb_manual_schema();

        $data['page_title'] = 'KARISMA - Log Sistem LPB Manual';
        $data['rows'] = $this->M_Logistik->get_lpb_manual_system_logs(500);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/lpb_manual_log.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_get_lpb_records_by_kd_po()
    {
        while (ob_get_level()) ob_end_clean();

        $kd_po = trim((string) $this->input->get('kd_po', TRUE));
        $id_lpb = (int) $this->input->get('id_lpb', TRUE);

        header('Content-Type: application/json; charset=utf-8');

        if ($kd_po === '') {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Parameter kd_po wajib diisi.'
            ]);
            return;
        }

        echo json_encode([
            'status'       => 'success',
            'rows'         => $this->M_Logistik->get_lpb_records_by_kd_po($kd_po, FALSE, $id_lpb),
            'invoice_rows' => $this->M_Logistik->get_lpb_invoice_records_by_kd_po($kd_po, $id_lpb)
        ]);
    }

    public function ajax_get_lpb_record_detail()
    {
        while (ob_get_level()) ob_end_clean();

        $id_lpb = (int) $this->input->get('id_lpb', TRUE);

        header('Content-Type: application/json; charset=utf-8');

        if ($id_lpb <= 0) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Parameter id_lpb tidak valid.'
            ]);
            return;
        }

        $header = $this->M_Logistik->get_lpb_record_header($id_lpb);

        if (empty($header)) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Data LPB tidak ditemukan.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'header' => $header,
            'rows'   => $this->M_Logistik->get_lpb_record_detail_rows($id_lpb),
            'logs'   => $this->M_Logistik->get_lpb_activity_logs($id_lpb)
        ]);
    }

    public function ajax_get_purchasing_po_detail()
    {
        while (ob_get_level()) ob_end_clean();

        $id_lpb = (int) $this->input->get('id_lpb', TRUE);

        header('Content-Type: application/json; charset=utf-8');

        if ($id_lpb <= 0) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Parameter id_lpb wajib diisi.'
            ]);
            return;
        }

        $header = $this->M_Logistik->get_lpb_record_header($id_lpb);

        if (empty($header)) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Data LPB tidak ditemukan.'
            ]);
            return;
        }

        $rows = $this->M_Logistik->get_purchasing_lpb_detail_rows($id_lpb);

        echo json_encode([
            'status'  => 'success',
            'message' => 'Data purchasing berhasil dimuat.',
            'header'  => $this->build_purchasing_lpb_summary($header, $rows),
            'rows'    => $rows,
            'logs'    => $this->M_Logistik->get_lpb_activity_logs($id_lpb)
        ]);
    }

    private function json_response($payload)
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    private function active_user_name()
    {
        return $this->session->userdata('nama')
            ?: $this->session->userdata('nama_user')
            ?: $this->session->userdata('username')
            ?: $this->session->userdata('nik')
            ?: 'SYSTEM';
    }

    private function active_user_code()
    {
        return $this->session->userdata('nik')
            ?: $this->session->userdata('username')
            ?: $this->session->userdata('id')
            ?: '';
    }

    private function is_admin_po_jobdesk()
    {
        $departemen = strtoupper(trim((string) ($this->session->userdata('departemen') ?: $this->session->userdata('departement'))));
        $jobdesk = strtoupper(trim((string) $this->session->userdata('jobdesk')));
        $username = strtolower(trim((string) $this->session->userdata('username')));
        $level = (string) $this->session->userdata('lv');

        return strpos($departemen, 'PURCHASING') !== FALSE
            || in_array($jobdesk, ['ADMINPURCHASING', 'ADMIN PO', 'ADMIN'], TRUE)
            || in_array($username, ['admpo', 'admin'], TRUE)
            || $level === '1';
    }

    private function resolve_ics_po_panel_mode()
    {
        $departemen = strtoupper(trim((string) ($this->session->userdata('departemen') ?: $this->session->userdata('departement'))));
        $jobdesk = strtoupper(trim((string) $this->session->userdata('jobdesk')));
        $username = strtolower(trim((string) $this->session->userdata('username')));

        if (strpos($departemen, 'PURCHASING') !== FALSE || in_array($jobdesk, ['ADMINPURCHASING', 'ADMIN PO'], TRUE) || $username === 'admpo') {
            return 'purchasing';
        }

        if (strpos($departemen, 'LOGISTIK') !== FALSE || in_array($jobdesk, ['LOGISTIK', 'ADMINLOGLPB', 'ADMLPB2', 'ADMINICS'], TRUE)) {
            return 'logistik';
        }

        return 'both';
    }

    private function is_admlpb_user()
    {
        $departemen = strtoupper(trim((string) ($this->session->userdata('departemen') ?: $this->session->userdata('departement'))));
        $jobdesk = strtoupper(trim((string) $this->session->userdata('jobdesk')));
        $username = strtolower(trim((string) $this->session->userdata('username')));

        return strpos($departemen, 'LOGISTIK') !== FALSE
            || in_array($jobdesk, ['ADMLPB', 'ADMINLOGLPB', 'ADMLPB2'], TRUE)
            || in_array($username, ['admlpb', 'adminloglpb', 'admlpb2'], TRUE);
    }

    private function reject_non_admin_po_ajax()
    {
        if ($this->is_admin_po_jobdesk()) {
            return FALSE;
        }

        $this->json_response([
            'status'  => 'error',
            'message' => 'Akses fitur ini hanya untuk ADMIN PO, Purchasing, atau Admin.',
            'html'    => ''
        ]);

        return TRUE;
    }

    private function can_access_lpb_manual()
    {
        return $this->is_admin_po_jobdesk() || $this->can_access_lpb_manual_log();
    }

    private function can_access_lpb_report()
    {
        $departemen = strtoupper(trim((string) ($this->session->userdata('departemen') ?: $this->session->userdata('departement'))));
        $jobdesk = strtoupper(trim((string) $this->session->userdata('jobdesk')));

        if ($this->is_admin_po_jobdesk() || $this->can_access_lpb_manual_log()) {
            return TRUE;
        }

        return strpos($departemen, 'LOGISTIK') !== FALSE
            || in_array($jobdesk, ['LOGISTIK', 'ADMINLOGLPB', 'ADMLPB2', 'ADMINICS'], TRUE);
    }

    private function can_access_lpb_manual_log()
    {
        $departemen = strtoupper(trim((string) ($this->session->userdata('departemen') ?: $this->session->userdata('departement'))));
        $jobdesk = strtoupper(trim((string) $this->session->userdata('jobdesk')));
        $username = strtolower(trim((string) $this->session->userdata('username')));
        $level = (string) $this->session->userdata('lv');

        return $username === 'admin'
            || $level === '1'
            || strpos($departemen, 'IT') !== FALSE
            || in_array($jobdesk, ['IT', 'ADMINIT', 'ADMIN IT', 'PROGRAMMER', 'DEVELOPMENT', 'ADMIN'], TRUE);
    }

    private function rupiah($value)
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function build_purchasing_lpb_summary($header, $rows)
    {
        return [
            'nomor_lpb'   => $header['nomor_lpb'] ?? '',
            'no_po'       => $header['no_po'] ?? '',
            'jenis_lpb'   => $header['jenis_lpb'] ?? '',
            'status_lpb'  => $header['status_lpb'] ?? 1,
            'nosj'        => $header['nosj'] ?? '',
            'tgl_sj'      => $header['tgl_sj'] ?? '',
            'no_invoice'  => $header['no_invoice'] ?? '',
            'tanggal_invoice' => $header['tanggal_invoice'] ?? '',
            'kode_faktur_pajak' => $header['kode_faktur_pajak'] ?? '',
            'tanggal_faktur_pajak' => $header['tanggal_faktur_pajak'] ?? '',
            'checker_name' => $header['checker_name'] ?? '',
            'checker_by' => $header['checker_by'] ?? '',
            'checker_at' => $header['checker_at'] ?? '',
            'has_sales_transaction' => $header['has_sales_transaction'] ?? 0,
            'sales_invoice_count' => $header['sales_invoice_count'] ?? 0,
            'sales_qty_total' => $header['sales_qty_total'] ?? 0,
            'sales_invoice_sample' => $header['sales_invoice_sample'] ?? '',
            'latest_sales_at' => $header['latest_sales_at'] ?? '',
            'has_active_lpb_journal' => $header['has_active_lpb_journal'] ?? 0,
            'lpb_journal_count' => $header['lpb_journal_count'] ?? 0,
            'lpb_active_journal_count' => $header['lpb_active_journal_count'] ?? 0,
            'lpb_journal_sample' => $header['lpb_journal_sample'] ?? '',
            'lpb_operational_warning' => $header['lpb_operational_warning'] ?? ''
        ];
    }

    private function render_pre_po_action_button($row)
    {
        $kdBarang = htmlspecialchars((string) ($row['kd_barang'] ?? ''), ENT_QUOTES, 'UTF-8');
        $namaBarang = htmlspecialchars((string) ($row['nama_barang'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $qty = (float) ($row['qty'] ?? 0);
        $satuan = htmlspecialchars((string) ($row['satuan'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $hargaSatuan = (float) ($row['harga_satuan'] ?? ($row['hrg_satuan'] ?? ($row['harga'] ?? 0)));
        $hargaTotal = (float) ($row['harga_total'] ?? ($qty * $hargaSatuan));

        return '
            <button type="button"
                class="btn btn-warning btn-sm js-open-adjustment"
                data-kd-barang="' . $kdBarang . '"
                data-nama-barang="' . $namaBarang . '"
                data-qty="' . htmlspecialchars((string) $qty, ENT_QUOTES, 'UTF-8') . '"
                data-satuan="' . $satuan . '"
                data-harga-satuan="' . htmlspecialchars((string) $hargaSatuan, ENT_QUOTES, 'UTF-8') . '"
                data-harga-total="' . htmlspecialchars((string) $hargaTotal, ENT_QUOTES, 'UTF-8') . '">
                <i class="fas fa-money-bill-wave mr-1"></i> Adjustment Harga
            </button>';
    }

    private function render_pre_po_adjustment_cards($standardRows, $invoiceRows = [], $summary = [])
    {
        if (empty($standardRows) && empty($invoiceRows)) {
            return '<div class="lpb-empty-state"><i class="fas fa-box-open fa-2x mb-2"></i><div>Data PRE PO untuk adjustment belum tersedia.</div></div>';
        }

        $html = '
            <ul class="nav nav-tabs" id="lpbInvoiceAdjustmentTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-data-lpb" data-toggle="tab" href="#pane-data-lpb" role="tab">
                        <i class="fas fa-boxes mr-1"></i> Data LPB
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-invoice-adjustment" data-toggle="tab" href="#pane-invoice-adjustment" role="tab">
                        <i class="fas fa-file-invoice-dollar mr-1"></i> Invoice & Adjustment Harga
                    </a>
                </li>
            </ul>
            <div class="tab-content pt-3" id="lpbInvoiceAdjustmentTabContent">
                <div class="tab-pane fade show active" id="pane-data-lpb" role="tabpanel" aria-labelledby="tab-data-lpb">
            <div class="table-responsive">
                <table class="table table-bordered table-hover lpb-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Harga Total</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        foreach ($standardRows as $row) {
            $kdBarang = htmlspecialchars((string) ($row['kd_barang'] ?? ''), ENT_QUOTES, 'UTF-8');
            $namaBarang = htmlspecialchars((string) ($row['nama_barang'] ?? '-'), ENT_QUOTES, 'UTF-8');
            $qty = (float) ($row['qty'] ?? 0);
            $satuan = htmlspecialchars((string) ($row['satuan'] ?? '-'), ENT_QUOTES, 'UTF-8');
            $hargaSatuan = (float) ($row['hrg_satuan'] ?? 0);
            $hargaTotal = $qty * $hargaSatuan;

            $html .= '
                <tr>
                    <td class="text-center">' . $no . '</td>
                    <td class="font-weight-bold">' . $kdBarang . '</td>
                    <td>' . $namaBarang . '</td>
                    <td class="text-center">' . htmlspecialchars(number_format($qty, 0, ',', '.'), ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="text-center">' . $satuan . '</td>
                    <td class="text-right">' . $this->rupiah($hargaSatuan) . '</td>
                    <td class="text-right">' . $this->rupiah($hargaTotal) . '</td>
                    <td class="text-center">' . $this->render_pre_po_action_button($row) . '</td>
                </tr>';
            $no++;
        }
        if (empty($standardRows)) {
            $html .= '<tr><td colspan="8" class="text-center text-muted">Data LPB belum tersedia.</td></tr>';
        }
        $html .= '</tbody></table></div></div>';

        $html .= '
                <div class="tab-pane fade" id="pane-invoice-adjustment" role="tabpanel" aria-labelledby="tab-invoice-adjustment">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover lpb-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-right">Harga Satuan</th>
                                    <th class="text-right">Harga Total</th>
                                    <th class="text-right">Harga Diskon</th>
                                    <th class="text-right">Harga Total Diskon</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>';

        $no = 1;
        foreach ($invoiceRows as $row) {
            $qty = (float) ($row['qty'] ?? 0);
            $hargaSatuan = (float) ($row['harga_satuan'] ?? 0);
            $hargaDiskon = (float) ($row['harga_diskon'] ?? 0);
            $hargaTotal = $qty * $hargaSatuan;
            $hargaTotalDiskon = $qty * $hargaDiskon;

            $html .= '
                <tr>
                    <td class="text-center">' . $no . '</td>
                    <td class="font-weight-bold">' . htmlspecialchars((string) ($row['kd_barang'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) ($row['nama_barang'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="text-center">' . htmlspecialchars(number_format($qty, 0, ',', '.'), ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="text-center">' . htmlspecialchars((string) ($row['satuan'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="text-right">' . $this->rupiah($hargaSatuan) . '</td>
                    <td class="text-right">' . $this->rupiah($hargaTotal) . '</td>
                    <td class="text-right">' . $this->rupiah($hargaDiskon) . '</td>
                    <td class="text-right">' . $this->rupiah($hargaTotalDiskon) . '</td>
                    <td class="text-center">' . $this->render_pre_po_action_button($row) . '</td>
                </tr>';
            $no++;
        }
        if (empty($invoiceRows)) {
            $html .= '<tr><td colspan="10" class="text-center text-muted">Data invoice & adjustment harga belum tersedia.</td></tr>';
        }
        $html .= '</tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Total Harga</th>
                                    <th class="text-right">' . $this->rupiah($summary['total_harga'] ?? 0) . '</th>
                                    <th class="text-right">Total Harga Diskon</th>
                                    <th class="text-right">' . $this->rupiah($summary['total_harga_diskon'] ?? 0) . '</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-right">Tax</th>
                                    <th class="text-right">' . $this->rupiah($summary['tax'] ?? 0) . '</th>
                                    <th class="text-right">Tax Diskon</th>
                                    <th class="text-right">' . $this->rupiah($summary['tax_diskon'] ?? 0) . '</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-right">Grand Total</th>
                                    <th class="text-right">' . $this->rupiah($summary['grand_total'] ?? 0) . '</th>
                                    <th class="text-right">Grand Total Diskon</th>
                                    <th class="text-right">' . $this->rupiah($summary['grand_total_diskon'] ?? 0) . '</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>';

        return $html;
    }

    private function render_history_invoice($rows)
    {
        if (empty($rows)) {
            return '<div class="lpb-empty-state"><i class="fas fa-file-invoice fa-2x mb-2"></i><div>History invoice belum tersedia.</div></div>';
        }

        $html = '<div class="list-group">';
        foreach ($rows as $row) {
            $html .= '<div class="list-group-item">
                <div class="d-flex justify-content-between" style="gap:12px; flex-wrap:wrap;">
                    <div class="font-weight-bold">' . htmlspecialchars((string) ($row['no_invoice'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</div>
                    <span class="badge badge-primary">' . htmlspecialchars((string) ($row['action_type'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</span>
                </div>
                <div class="text-muted small mt-2">' . htmlspecialchars((string) ($row['keterangan'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</div>
                <div class="small mt-2"><i class="fas fa-user mr-1"></i>' . htmlspecialchars((string) ($row['dilakukan_oleh'] ?? '-'), ENT_QUOTES, 'UTF-8') . ' <span class="text-muted ml-2"><i class="fas fa-clock mr-1"></i>' . htmlspecialchars((string) ($row['dilakukan_pada'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</span></div>
            </div>';
        }
        $html .= '</div>';

        return $html;
    }

    private function render_history_diskon($rows)
    {
        if (empty($rows)) {
            return '<div class="lpb-empty-state"><i class="fas fa-tags fa-2x mb-2"></i><div>History diskon hasil sync KIU_PO belum tersedia.</div></div>';
        }

        $html = '
            <div class="table-responsive">
                <table class="table table-bordered table-hover lpb-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Kode Supplier</th>
                            <th>Nama Supplier</th>
                            <th>Keterangan</th>
                            <th class="text-right">Nominal</th>
                            <th class="text-center">Synced At</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        foreach ($rows as $row) {
            $html .= '
                <tr>
                    <td class="text-center">' . $no . '</td>
                    <td>' . htmlspecialchars((string) ($row['kd_suplier'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) ($row['nama_suplier'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="history-diskon-keterangan">' . htmlspecialchars((string) ($row['keterangan'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="text-right">' . $this->rupiah($row['nominal'] ?? 0) . '</td>
                    <td class="text-center">' . htmlspecialchars((string) ($row['synced_at'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>
                </tr>';
            $no++;
        }

        $html .= '</tbody></table></div>';

        return $html;
    }

    private function render_history_adjustment($rows)
    {
        if (empty($rows)) {
            return '<div class="lpb-empty-state"><i class="fas fa-history fa-2x mb-2"></i><div>History adjustment belum tersedia.</div></div>';
        }

        $html = '<div class="list-group">';
        foreach ($rows as $row) {
            $html .= '<div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start" style="gap:12px; flex-wrap:wrap;">
                    <div>
                        <div class="font-weight-bold">' . htmlspecialchars((string) ($row['kd_barang'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</div>
                        <div class="text-muted small">' . htmlspecialchars((string) ($row['alasan'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</div>
                    </div>
                    <div class="text-right">
                        <div class="small">Harga: <strong>' . $this->rupiah($row['harga_satuan_lama'] ?? 0) . '</strong> ke <strong>' . $this->rupiah($row['harga_satuan_baru'] ?? 0) . '</strong></div>
                        <div class="small">Total: <strong>' . $this->rupiah($row['harga_total_lama'] ?? 0) . '</strong> ke <strong>' . $this->rupiah($row['harga_total_baru'] ?? 0) . '</strong></div>
                    </div>
                </div>
                <div class="small mt-2"><i class="fas fa-user mr-1"></i>' . htmlspecialchars((string) ($row['dilakukan_oleh'] ?? '-'), ENT_QUOTES, 'UTF-8') . ' <span class="text-muted ml-2"><i class="fas fa-clock mr-1"></i>' . htmlspecialchars((string) ($row['dilakukan_pada'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</span></div>
            </div>';
        }
        $html .= '</div>';

        return $html;
    }

    public function ajax_get_pre_po_adjustment()
    {
        if ($this->reject_non_admin_po_ajax()) return;

        $kd_po = trim((string) $this->input->get('kd_po', TRUE));

        if ($kd_po === '') {
            $this->json_response(['status' => 'error', 'message' => 'Parameter kd_po wajib diisi.', 'html' => '']);
            return;
        }

        $rows = $this->M_Logistik->get_pre_po_adjustment($kd_po);
        $invoiceRows = $this->M_Logistik->get_pre_po_invoice_adjustment($kd_po);
        $summary = $this->M_Logistik->get_pre_po_invoice_adjustment_summary($kd_po, $invoiceRows);
        $this->json_response([
            'status'  => 'success',
            'message' => 'Data PRE PO berhasil dimuat.',
            'html'    => $this->render_pre_po_adjustment_cards($rows, $invoiceRows, $summary),
            'rows'    => $rows,
            'invoice_rows' => $invoiceRows,
            'summary' => $summary
        ]);
    }

    public function ajax_submit_adjustment()
    {
        if ($this->reject_non_admin_po_ajax()) return;

        $kd_po = trim((string) $this->input->post('kd_po', TRUE));
        $kd_barang = trim((string) $this->input->post('kd_barang', TRUE));
        $hargaBaru = (float) $this->input->post('harga_satuan_baru', TRUE);
        $alasan = trim((string) $this->input->post('alasan', TRUE));

        if ($kd_po === '' || $kd_barang === '' || $hargaBaru < 0 || $alasan === '') {
            $this->json_response(['status' => 'error', 'message' => 'Data adjustment belum lengkap.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();
        $saved = $this->M_Logistik->submit_adjustment([
            'kd_po'              => $kd_po,
            'kd_barang'          => $kd_barang,
            'harga_satuan_baru'  => $hargaBaru,
            'alasan'             => $alasan,
            'dilakukan_oleh'     => $this->active_user_name()
        ]);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Adjustment harga gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $rows = $this->M_Logistik->get_pre_po_adjustment($kd_po);
        $invoiceRows = $this->M_Logistik->get_pre_po_invoice_adjustment($kd_po);
        $summary = $this->M_Logistik->get_pre_po_invoice_adjustment_summary($kd_po, $invoiceRows);
        $this->json_response([
            'status'  => 'success',
            'message' => 'Adjustment harga berhasil disimpan.',
            'html'    => $this->render_pre_po_adjustment_cards($rows, $invoiceRows, $summary)
        ]);
    }

    public function ajax_update_lpb_detail_price()
    {
        if ($this->reject_non_admin_po_ajax()) return;

        $idDetailLpb = (int) $this->input->post('id_detail_lpb', TRUE);
        $hargaSatuanBaru = (float) $this->input->post('harga_satuan_baru', TRUE);
        $qtyLpbBaru = (float) $this->input->post('qty_lpb', TRUE);

        if ($idDetailLpb <= 0 || $hargaSatuanBaru < 0 || $qtyLpbBaru <= 0) {
            $this->json_response(['status' => 'error', 'message' => 'Data harga detail LPB belum lengkap.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();

        $detail = $this->db
            ->select('h.status_lpb')
            ->from('tb_lpb_detail d')
            ->join('tb_lpb h', 'h.id_lpb = d.id_lpb', 'inner')
            ->where('d.id_detail_lpb', $idDetailLpb)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$detail) {
            $this->json_response(['status' => 'error', 'message' => 'Detail LPB tidak ditemukan.', 'html' => '']);
            return;
        }

        if ((int) ($detail['status_lpb'] ?? 1) !== 0) {
            $this->json_response(['status' => 'error', 'message' => 'Update harga detail LPB hanya bisa dilakukan saat status UNPOST.', 'html' => '']);
            return;
        }

        $qtyValidation = $this->M_Logistik->validate_lpb_detail_qty_balance($idDetailLpb, $qtyLpbBaru);
        if (empty($qtyValidation['valid'])) {
            $this->json_response([
                'status'  => 'error',
                'message' => $qtyValidation['message'] ?? 'Qty LPB tidak balance dengan qty yang diterima.',
                'html'    => ''
            ]);
            return;
        }

        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_lpb_detail_price([
            'id_detail_lpb'      => $idDetailLpb,
            'harga_satuan_baru'  => $hargaSatuanBaru,
            'qty_lpb_baru'       => $qtyLpbBaru,
            'dilakukan_oleh'     => $this->active_user_name()
        ]);

        if (is_array($saved) && array_key_exists('status', $saved) && empty($saved['status'])) {
            $this->db->trans_rollback();
            $this->json_response([
                'status'  => 'error',
                'message' => $saved['message'] ?? 'Update harga detail LPB tidak dapat diproses.',
                'errors'  => $saved['errors'] ?? [],
                'data'    => $saved['data'] ?? [],
                'html'    => ''
            ]);
            return;
        }

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Update harga detail LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'  => 'success',
            'message' => !empty($qtyValidation['warning'])
                ? 'Harga dan Qty LPB berhasil diperbarui. ' . $qtyValidation['warning']
                : 'Harga dan Qty LPB berhasil diperbarui.',
            'warning' => $qtyValidation['warning'] ?? '',
            'row'     => $saved,
            'html'    => ''
        ]);
    }

    public function ajax_accept_lpb_detail_price()
    {
        $idDetailLpb = (int) $this->input->post('id_detail_lpb', TRUE);

        if ($idDetailLpb <= 0) {
            $this->json_response(['status' => 'error', 'message' => 'Detail LPB tidak valid.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();
        $saved = $this->M_Logistik->accept_lpb_detail_price([
            'id_detail_lpb'  => $idDetailLpb,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Verifikasi harga detail LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'  => 'success',
            'message' => 'Harga detail LPB berhasil diverifikasi.',
            'row'     => $saved,
            'html'    => ''
        ]);
    }

    public function ajax_bulk_accept_lpb_detail_price()
    {
        $rawIds = $this->input->post('id_detail_lpb');
        $idList = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
        $idList = array_values(array_unique(array_filter(array_map('intval', $idList), function ($id) {
            return $id > 0;
        })));

        if (empty($idList)) {
            $this->json_response(['status' => 'error', 'message' => 'Tidak ada detail LPB yang bisa diverifikasi.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();

        $savedRows = [];
        $idLpb = 0;
        foreach ($idList as $idDetailLpb) {
            $saved = $this->M_Logistik->accept_lpb_detail_price([
                'id_detail_lpb'  => $idDetailLpb,
                'dilakukan_oleh' => $this->active_user_name()
            ]);

            if (!$saved) {
                $this->db->trans_rollback();
                $this->json_response([
                    'status'  => 'error',
                    'message' => 'Bulk verifikasi gagal. Detail LPB #' . $idDetailLpb . ' tidak valid atau belum memiliki harga.',
                    'html'    => ''
                ]);
                return;
            }

            $currentIdLpb = (int) ($saved['id_lpb'] ?? 0);
            if ($idLpb <= 0) {
                $idLpb = $currentIdLpb;
            } elseif ($currentIdLpb !== $idLpb) {
                $this->db->trans_rollback();
                $this->json_response([
                    'status'  => 'error',
                    'message' => 'Bulk verifikasi gagal. Detail LPB harus berasal dari LPB yang sama.',
                    'html'    => ''
                ]);
                return;
            }

            $savedRows[] = $saved;
        }

        $posted = $this->M_Logistik->update_lpb_status($idLpb, 1, $this->active_user_name(), 'LPB direkam menjadi POST setelah verifikasi harga detail.');
        if (!$posted) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Verifikasi harga berhasil, tetapi Rekam LPB menjadi POST gagal disimpan.', 'html' => '']);
            return;
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Bulk verifikasi harga detail LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'  => 'success',
            'message' => count($savedRows) . ' detail LPB berhasil diverifikasi dan LPB direkam menjadi POST.',
            'rows'    => $savedRows,
            'header'  => $posted,
            'html'    => ''
        ]);
    }

    public function ajax_history_adjustment()
    {
        if ($this->reject_non_admin_po_ajax()) return;

        $kd_po = trim((string) $this->input->get('kd_po', TRUE));
        $kd_barang = trim((string) $this->input->get('kd_barang', TRUE));

        if ($kd_po === '') {
            $this->json_response(['status' => 'error', 'message' => 'Parameter kd_po wajib diisi.', 'html' => '']);
            return;
        }

        $rows = $this->M_Logistik->get_history_adjustment($kd_po, $kd_barang);
        $this->json_response([
            'status'  => 'success',
            'message' => 'History adjustment berhasil dimuat.',
            'html'    => $this->render_history_adjustment($rows)
        ]);
    }

    public function ajax_history_invoice()
    {
        if ($this->reject_non_admin_po_ajax()) return;

        $kd_po = trim((string) $this->input->get('kd_po', TRUE));

        if ($kd_po === '') {
            $this->json_response(['status' => 'error', 'message' => 'Parameter kd_po wajib diisi.', 'html' => '']);
            return;
        }

        $rows = $this->M_Logistik->get_history_invoice($kd_po);
        $this->json_response([
            'status'  => 'success',
            'message' => 'History invoice berhasil dimuat.',
            'html'    => $this->render_history_invoice($rows)
        ]);
    }

    public function ajax_history_diskon()
    {
        if ($this->reject_non_admin_po_ajax()) return;

        $kd_po = trim((string) $this->input->get('kd_po', TRUE));

        if ($kd_po === '') {
            $this->json_response(['status' => 'error', 'message' => 'Parameter kd_po wajib diisi.', 'html' => '']);
            return;
        }

        $rows = $this->M_Logistik->get_history_diskon_sync($kd_po);
        $this->json_response([
            'status'  => 'success',
            'message' => 'History diskon berhasil dimuat.',
            'html'    => $this->render_history_diskon($rows)
        ]);
    }

    public function ajax_update_invoice()
    {
        if ($this->reject_non_admin_po_ajax()) {
            return;
        }

        $id_lpb = (int) $this->input->post('id_lpb', TRUE);
        $no_invoice = trim((string) $this->input->post('no_invoice', TRUE));
        $tanggal_invoice = trim((string) $this->input->post('tanggal_invoice', TRUE));

        if ($id_lpb <= 0 || $no_invoice === '' || $tanggal_invoice === '') {
            $this->json_response(['status' => 'error', 'message' => 'Data invoice belum lengkap.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_invoice_faktur_columns();
        $this->M_Logistik->ensure_lpb_workflow_columns();

        $header = $this->M_Logistik->get_lpb_record_header($id_lpb);
        if (!$header) {
            $this->json_response(['status' => 'error', 'message' => 'Data LPB tidak ditemukan.', 'html' => '']);
            return;
        }

        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_invoice_lpb([
            'id_lpb'         => $id_lpb,
            'no_invoice'     => $no_invoice,
            'tanggal_invoice' => $tanggal_invoice,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Update invoice LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'  => 'success',
            'message' => 'Invoice LPB berhasil diperbarui.',
            'html'    => ''
        ]);
    }

    public function ajax_split_lpb_multiple_invoice()
    {
        if ($this->reject_non_admin_po_ajax()) {
            return;
        }

        $id_lpb = (int) $this->input->post('id_lpb', TRUE);
        $rawSplits = (string) $this->input->post('splits', FALSE);
        $splits = json_decode($rawSplits, TRUE);

        if ($id_lpb <= 0 || !is_array($splits)) {
            $this->json_response(['status' => 'error', 'message' => 'Data split invoice belum lengkap.', 'html' => '']);
            return;
        }

        if (count($splits) < 2) {
            $this->json_response(['status' => 'error', 'message' => 'Minimal harus ada 2 invoice untuk pecah LPB.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_invoice_faktur_columns();
        $this->M_Logistik->ensure_lpb_workflow_columns();

        $header = $this->M_Logistik->get_lpb_record_header($id_lpb);
        if (!$header) {
            $this->json_response(['status' => 'error', 'message' => 'Data LPB tidak ditemukan.', 'html' => '']);
            return;
        }

        if ((int) ($header['status_lpb'] ?? 1) !== 0) {
            $this->json_response(['status' => 'error', 'message' => 'Pecah LPB multiple invoice hanya bisa dilakukan saat status UNPOST.', 'html' => '']);
            return;
        }

        $this->db->trans_begin();
        $saved = $this->M_Logistik->split_lpb_multiple_invoice([
            'id_lpb'         => $id_lpb,
            'splits'         => $splits,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (empty($saved['status']) || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $message = is_array($saved) && !empty($saved['message'])
                ? $saved['message']
                : 'Pecah LPB multiple invoice gagal disimpan.';
            $this->json_response(['status' => 'error', 'message' => $message, 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'  => 'success',
            'message' => 'LPB berhasil dipecah menjadi multiple invoice.',
            'id_lpb'  => $saved['id_lpb'] ?? [],
            'html'    => ''
        ]);
    }

    public function ajax_split_lpb_detail()
    {
        if ($this->reject_non_admin_po_ajax()) {
            return;
        }

        $idDetailLpb = (int) $this->input->post('id_detail_lpb', TRUE);
        $rawSplits = (string) $this->input->post('splits', FALSE);
        $splits = json_decode($rawSplits, TRUE);
        $keterangan = trim((string) $this->input->post('keterangan', TRUE));

        if ($idDetailLpb <= 0 || !is_array($splits) || count($splits) < 2) {
            $this->json_response(['status' => 'error', 'message' => 'Data split detail LPB belum lengkap.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $detail = $this->db
            ->select('d.id_lpb, d.qty_diterima, h.status_lpb')
            ->from('tb_lpb_detail d')
            ->join('tb_lpb h', 'h.id_lpb = d.id_lpb', 'inner')
            ->where('d.id_detail_lpb', $idDetailLpb)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$detail) {
            $this->json_response(['status' => 'error', 'message' => 'Detail LPB tidak ditemukan.', 'html' => '']);
            return;
        }

        if ((int) ($detail['status_lpb'] ?? 1) !== 0) {
            $this->json_response(['status' => 'error', 'message' => 'Split detail LPB hanya bisa dilakukan saat status UNPOST.', 'html' => '']);
            return;
        }

        $this->db->trans_begin();
        $saved = $this->M_Logistik->split_lpb_detail([
            'id_detail_lpb'  => $idDetailLpb,
            'splits'         => $splits,
            'keterangan'     => $keterangan,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (empty($saved['status']) || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $message = is_array($saved) && !empty($saved['message'])
                ? $saved['message']
                : 'Split detail LPB gagal disimpan.';
            $this->json_response(['status' => 'error', 'message' => $message, 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'  => 'success',
            'message' => 'Detail LPB berhasil di-split.',
            'row'     => $saved,
            'html'    => ''
        ]);
    }

    public function ajax_update_faktur_pajak()
    {
        if ($this->reject_non_admin_po_ajax()) {
            return;
        }

        $id_lpb = (int) $this->input->post('id_lpb', TRUE);
        $kode_faktur_pajak = trim((string) $this->input->post('kode_faktur_pajak', TRUE));
        $tanggal_faktur_pajak = trim((string) $this->input->post('tanggal_faktur_pajak', TRUE));

        if ($id_lpb <= 0 || $kode_faktur_pajak === '' || $tanggal_faktur_pajak === '') {
            $this->json_response(['status' => 'error', 'message' => 'Data faktur pajak belum lengkap.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_invoice_faktur_columns();
        $this->M_Logistik->ensure_lpb_workflow_columns();

        $header = $this->M_Logistik->get_lpb_record_header($id_lpb);
        if (!$header) {
            $this->json_response(['status' => 'error', 'message' => 'Data LPB tidak ditemukan.', 'html' => '']);
            return;
        }

        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_faktur_pajak_lpb([
            'id_lpb' => $id_lpb,
            'kode_faktur_pajak' => $kode_faktur_pajak,
            'tanggal_faktur_pajak' => $tanggal_faktur_pajak,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Update faktur pajak gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status' => 'success',
            'message' => 'Faktur pajak berhasil diperbarui.',
            'html' => ''
        ]);
    }

    public function ajax_update_lpb_type()
    {
        $id_lpb = (int) $this->input->post('id_lpb', TRUE);
        $jenis_lpb = trim((string) $this->input->post('jenis_lpb', TRUE));

        if ($id_lpb <= 0 || $jenis_lpb === '') {
            $this->json_response(['status' => 'error', 'message' => 'Data jenis LPB belum lengkap.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_lpb_type([
            'id_lpb'         => $id_lpb,
            'jenis_lpb'      => $jenis_lpb,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Update jenis LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'    => 'success',
            'message'   => 'Jenis LPB berhasil diperbarui.',
            'jenis_lpb' => $saved['jenis_lpb'] ?? $jenis_lpb,
            'nomor_lpb' => $saved['nomor_lpb'] ?? '',
            'html'      => ''
        ]);
    }

    public function ajax_unpost_lpb()
    {
        $id_lpb = (int) $this->input->post('id_lpb', TRUE);
        $keterangan = trim((string) $this->input->post('keterangan', TRUE));

        if ($id_lpb <= 0) {
            $this->json_response(['status' => 'error', 'message' => 'Data LPB tidak valid.', 'html' => '']);
            return;
        }

        if ($keterangan === '') {
            $this->json_response(['status' => 'error', 'message' => 'Keterangan UNPOST wajib diisi.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_lpb_status($id_lpb, 0, $this->active_user_name(), $keterangan);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'UNPOST LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();

        if ($this->db->table_exists('tbkeu_jurnal')) {
            $activeJournals = $this->db->select('id_jurnal')
                ->from('tbkeu_jurnal')
                ->where('source_module', 'LOGISTIK')
                ->where('source_type', 'LPB_FINAL')
                ->where('posting_event', 'GOODS_RECEIPT')
                ->where('source_id', (string) $id_lpb)
                ->where('status', 'POSTED')
                ->where('reversed_at IS NULL', null, false)
                ->get()
                ->result_array();

            if (!empty($activeJournals)) {
                $this->load->library('Accounting_service');
                foreach ($activeJournals as $jRow) {
                    $this->accounting_service->reverse_journal((int) $jRow['id_jurnal'], 'UNPOST LPB: ' . $keterangan, (int) $this->session->userdata('id') ?: null);
                }
            }
        }

        $this->json_response([
            'status'  => 'success',
            'message' => 'LPB berhasil di-UNPOST.',
            'html'    => ''
        ]);
    }

    public function ajax_post_lpb()
    {
        $id_lpb = (int) $this->input->post('id_lpb', TRUE);

        if ($id_lpb <= 0) {
            $this->json_response(['status' => 'error', 'message' => 'Data LPB tidak valid.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_lpb_status($id_lpb, 1, $this->active_user_name(), 'LPB direkam ulang menjadi POST.');

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Rekam LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();

        $this->load->library('Accounting_source_service');
        $accountingResult = $this->accounting_source_service->post_goods_receipt(
            $id_lpb,
            (int) $this->session->userdata('id') ?: null
        );

        $journalSummary = $this->M_Logistik->get_lpb_goods_receipt_journal_summary($id_lpb);
        $hasJournal = !empty($journalSummary['has_active_lpb_journal']);
        $journalSample = !empty($journalSummary['lpb_journal_sample']) ? $journalSummary['lpb_journal_sample'] : '';

        $message = 'LPB berhasil direkam menjadi POST.';
        if ($hasJournal) {
            $message .= ' Jurnal LPB aktif: ' . $journalSample;
        } elseif (!empty($accountingResult['message'])) {
            $message .= ' Note Akuntansi: ' . $accountingResult['message'];
        }

        $this->json_response([
            'status'  => 'success',
            'message' => $message,
            'accounting' => $accountingResult,
            'has_active_lpb_journal' => $hasJournal ? 1 : 0,
            'lpb_journal_sample' => $journalSample,
            'html'    => ''
        ]);
    }

    public function ajax_update_lpb_identity()
    {
        $id_lpb = (int) $this->input->post('id_lpb', TRUE);
        $nomor_lpb = trim((string) $this->input->post('nomor_lpb', TRUE));
        $jenis_lpb = trim((string) $this->input->post('jenis_lpb', TRUE));

        if ($id_lpb <= 0 || $nomor_lpb === '' || $jenis_lpb === '') {
            $this->json_response(['status' => 'error', 'message' => 'Nomor dan jenis LPB wajib diisi.', 'html' => '']);
            return;
        }

        $header = $this->M_Logistik->get_lpb_record_header($id_lpb);
        if (empty($header)) {
            $this->json_response(['status' => 'error', 'message' => 'Data LPB tidak ditemukan.', 'html' => '']);
            return;
        }
        if ((int) ($header['status_lpb'] ?? 1) !== 0) {
            $this->json_response(['status' => 'error', 'message' => 'Update nomor dan jenis LPB hanya bisa dilakukan saat status UNPOST.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_lpb_identity([
            'id_lpb'         => $id_lpb,
            'nomor_lpb'      => $nomor_lpb,
            'jenis_lpb'      => $jenis_lpb,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Update nomor dan jenis LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'    => 'success',
            'message'   => 'Nomor dan jenis LPB berhasil diperbarui.',
            'nomor_lpb' => $saved['nomor_lpb'] ?? $nomor_lpb,
            'jenis_lpb' => $saved['jenis_lpb'] ?? $jenis_lpb,
            'html'      => ''
        ]);
    }

    public function ajax_update_lpb_sj()
    {
        $id_lpb = (int) $this->input->post('id_lpb', TRUE);
        $nosj = trim((string) $this->input->post('nosj', TRUE));
        $tgl_sj = trim((string) $this->input->post('tgl_sj', TRUE));

        if ($id_lpb <= 0 || $nosj === '' || $tgl_sj === '') {
            $this->json_response(['status' => 'error', 'message' => 'Nomor SJ dan tanggal SJ wajib diisi.', 'html' => '']);
            return;
        }

        $header = $this->M_Logistik->get_lpb_record_header($id_lpb);
        if (empty($header)) {
            $this->json_response(['status' => 'error', 'message' => 'Data LPB tidak ditemukan.', 'html' => '']);
            return;
        }
        if ((int) ($header['status_lpb'] ?? 1) !== 0) {
            $this->json_response(['status' => 'error', 'message' => 'Update SJ hanya bisa dilakukan saat status UNPOST.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_lpb_sj([
            'id_lpb'         => $id_lpb,
            'nosj'           => $nosj,
            'tgl_sj'         => $tgl_sj,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Update nomor SJ dan tanggal SJ gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'  => 'success',
            'message' => 'Nomor SJ dan tanggal SJ berhasil diperbarui.',
            'html'    => ''
        ]);
    }

    public function ajax_update_lpb_detail_receipt()
    {
        $idDetailLpb = (int) $this->input->post('id_detail_lpb', TRUE);
        $noLot = trim((string) $this->input->post('no_lot', TRUE));
        $expiredDate = trim((string) $this->input->post('expired_date', TRUE));
        $qtyDiterima = (float) $this->input->post('qty_diterima', TRUE);

        if ($idDetailLpb <= 0 || $qtyDiterima <= 0) {
            $this->json_response(['status' => 'error', 'message' => 'Data detail LPB belum lengkap.', 'html' => '']);
            return;
        }

        $detail = $this->db
            ->select('h.status_lpb')
            ->from('tb_lpb_detail d')
            ->join('tb_lpb h', 'h.id_lpb = d.id_lpb', 'inner')
            ->where('d.id_detail_lpb', $idDetailLpb)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($detail)) {
            $this->json_response(['status' => 'error', 'message' => 'Detail LPB tidak ditemukan.', 'html' => '']);
            return;
        }
        if ((int) ($detail['status_lpb'] ?? 1) !== 0) {
            $this->json_response(['status' => 'error', 'message' => 'Update detail LPB hanya bisa dilakukan saat status UNPOST.', 'html' => '']);
            return;
        }

        $this->M_Logistik->ensure_lpb_workflow_columns();
        $this->db->trans_begin();
        $saved = $this->M_Logistik->update_lpb_detail_receipt([
            'id_detail_lpb'  => $idDetailLpb,
            'no_lot'         => $noLot,
            'expired_date'   => $expiredDate,
            'qty_diterima'   => $qtyDiterima,
            'dilakukan_oleh' => $this->active_user_name()
        ]);

        if (!$saved || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_response(['status' => 'error', 'message' => 'Update detail LPB gagal disimpan.', 'html' => '']);
            return;
        }

        $this->db->trans_commit();
        $this->json_response([
            'status'  => 'success',
            'message' => 'Detail LPB berhasil diperbarui.',
            'row'     => $saved,
            'html'    => ''
        ]);
    }

    public function ajax_generate_lpb_number()
    {
        $jenis_lpb = trim((string) $this->input->get('jenis_lpb', TRUE));

        if ($jenis_lpb === '') {
            $this->json_response([
                'status'  => 'error',
                'message' => 'Jenis PO / LPB wajib dipilih.',
                'nomor_lpb' => ''
            ]);
            return;
        }

        $jenis_lpb = $this->M_Logistik->normalize_lpb_type($jenis_lpb);
        $nomor_lpb = $this->M_Logistik->generate_lpb_number($jenis_lpb);

        $this->json_response([
            'status'    => 'success',
            'jenis_lpb' => $jenis_lpb,
            'nomor_lpb' => $nomor_lpb
        ]);
    }

    public function print_lpb_record($id_lpb = 0)
    {
        $id_lpb = (int) $id_lpb;

        if ($id_lpb <= 0) {
            show_error('Parameter id_lpb tidak valid.', 400);
            return;
        }

        $header = $this->M_Logistik->get_lpb_record_header($id_lpb);

        if (empty($header)) {
            show_error('Data LPB tidak ditemukan.', 404);
            return;
        }

        $data['page_title'] = 'Print LPB #' . $id_lpb;
        $data['print_mode'] = 'single';
        $data['records'] = [
            [
                'header' => $header,
                'rows'   => $this->M_Logistik->get_lpb_record_detail_rows($id_lpb)
            ]
        ];

        $this->load->view('content/logistik/ics/print_record_lpb.php', $data);
    }

    public function print_lpb_records_all()
    {
        $kd_po = trim((string) $this->input->get('kd_po', TRUE));
        $no_po = trim((string) $this->input->get('no_po', TRUE));

        if ($kd_po === '') {
            show_error('Parameter kd_po wajib diisi.', 400);
            return;
        }

        $headers = $this->M_Logistik->get_lpb_print_headers_by_kd_po($kd_po);

        if (empty($headers)) {
            show_error('Record LPB untuk KD PO ini belum tersedia.', 404);
            return;
        }

        $records = [];
        foreach ($headers as $header) {
            $header = $this->M_Logistik->get_lpb_record_header((int) $header['id_lpb']) ?: $header;
            $records[] = [
                'header' => $header,
                'rows'   => $this->M_Logistik->get_lpb_record_detail_rows((int) $header['id_lpb'])
            ];
        }

        $data['page_title'] = 'Print Semua Record LPB';
        $data['print_mode'] = 'all';
        $data['kd_po']      = $kd_po;
        $data['no_po']      = $no_po !== '' ? $no_po : ($headers[0]['no_po'] ?? '-');
        $data['records']    = $records;

        $this->load->view('content/logistik/ics/print_record_lpb.php', $data);
    }

    public function ajax_get_tmp_po_received_item()
    {
        while (ob_get_level()) ob_end_clean();

        $kd_po      = trim((string) $this->input->get('kd_po', TRUE));
        $kd_barang  = trim((string) $this->input->get('kd_barang', TRUE));

        header('Content-Type: application/json; charset=utf-8');

        if ($kd_po === '' || $kd_barang === '') {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Parameter kd_po dan kd_barang wajib diisi.'
            ]);
            return;
        }

        $rows = $this->M_Logistik->get_tmp_po_received_item($kd_po, $kd_barang);

        echo json_encode([
            'status' => 'success',
            'rows'   => $rows
        ]);
    }

    public function ajax_get_tmp_po_received_summary()
    {
        while (ob_get_level()) ob_end_clean();

        $no_po      = trim((string) $this->input->get('no_po', TRUE));
        $kd_suplier = trim((string) $this->input->get('kd_suplier', TRUE));

        header('Content-Type: application/json; charset=utf-8');

        if ($no_po === '' || $kd_suplier === '') {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Parameter no_po dan kd_suplier wajib diisi.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'rows'   => $this->M_Logistik->get_tmp_po_received_summary($no_po, $kd_suplier)
        ]);
    }

    public function ajax_save_tmp_po_received()
    {
        while (ob_get_level()) ob_end_clean();

        $payload = [
            'kd_po'      => trim((string) $this->input->post('kd_po', TRUE)),
            'kd_suplier' => trim((string) $this->input->post('kd_suplier', TRUE)),
            'kd_barang'  => trim((string) $this->input->post('kd_barang', TRUE)),
            'rows'       => $this->input->post('rows')
        ];

        header('Content-Type: application/json; charset=utf-8');

        if ($payload['kd_po'] === '' || $payload['kd_suplier'] === '' || $payload['kd_barang'] === '') {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Data barang yang dipilih belum lengkap.'
            ]);
            return;
        }

        $rows = is_array($payload['rows']) ? $payload['rows'] : [];
        $insertRows = [];
        $totalQty = 0;

        $qtyInfo = $this->M_Logistik->get_po_remaining_qty_by_item($payload['kd_po'], $payload['kd_barang']);

        if (!$qtyInfo) {
            echo json_encode([
                'status'  => 'error',
                'step'    => 'validate_item',
                'message' => 'Data barang PO tidak ditemukan untuk draft ini.'
            ]);
            return;
        }

        $poSatuan = trim((string) ($qtyInfo['satuan'] ?? ''));
        $dimensiBr = (float) ($qtyInfo['dimensi_br'] ?? 1);

        if ($dimensiBr <= 0) {
            $dimensiBr = 1;
        }

        if ($poSatuan === '') {
            echo json_encode([
                'status'  => 'error',
                'step'    => 'validate_satuan',
                'message' => 'Satuan pada data PO belum tersedia untuk barang ini.'
            ]);
            return;
        }

        foreach ($rows as $row) {
            $qtyBesar = (float) ($row['qty_diterima'] ?? 0);
            $satuan = trim((string) ($row['satuan'] ?? ''));
            $noLot = trim((string) ($row['no_lot'] ?? ''));
            $expiredDate = trim((string) ($row['expired_date'] ?? ''));

            if ($qtyBesar <= 0) {
                continue;
            }

            if ($satuan === '') {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Satuan wajib dipilih untuk setiap baris yang diisi.'
                ]);
                return;
            }

            $qtyKecil = $qtyBesar * $dimensiBr;
            $totalQty += $qtyKecil;
            $insertRows[] = [
                'kd_po'         => $payload['kd_po'],
                'kd_suplier'    => $payload['kd_suplier'],
                'kd_barang'     => $payload['kd_barang'],
                'qty_diterima'  => $qtyKecil,
                'satuan'        => $satuan,
                'no_lot'        => ($noLot !== '') ? $noLot : null,
                'expired_date'  => ($expiredDate !== '') ? $expiredDate : null
            ];
        }

        foreach ($insertRows as &$insertRow) {
            if (strcasecmp(trim((string) $insertRow['satuan']), $poSatuan) !== 0) {
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'validate_satuan',
                    'message' => 'Satuan draft harus sesuai dengan satuan pada data PO (' . $poSatuan . ').'
                ]);
                return;
            }

            $insertRow['satuan'] = $poSatuan;
        }
        unset($insertRow);

        $priceInfo = $this->M_Logistik->get_po_exclude_price_by_item(
            $qtyInfo['no_po'] ?? '',
            $payload['kd_barang'],
            $payload['kd_suplier'],
            $payload['kd_po']
        );

        if (!$priceInfo) {
            $priceInfo = [
                'harga_satuan' => (float) ($qtyInfo['hrg_satuan'] ?? 0),
                'harga_satuan_kecil' => (float) ($qtyInfo['hrg_satuan'] ?? 0) / ($dimensiBr > 0 ? $dimensiBr : 1)
            ];
        }

        $hargaSatuan = (float) ($priceInfo['harga_satuan'] ?? 0);
        $hargaSatuanKecil = (float) ($priceInfo['harga_satuan_kecil'] ?? 0);

        foreach ($insertRows as &$insertRow) {
            $insertRow['harga_satuan'] = $hargaSatuan;
            $insertRow['harga_satuan_kecil'] = $hargaSatuanKecil;
            $insertRow['total_harga'] = ((float) $insertRow['qty_diterima']) * $hargaSatuanKecil;
        }
        unset($insertRow);

        if ($totalQty > (float) $qtyInfo['qty_kecil_sisa'] + 0.0001) {
            echo json_encode([
                'status'  => 'error',
                'step'    => 'validate_qty',
                'message' => 'Total qty draft melebihi Qty Sisa PO.',
                'debug'   => [
                    'qty_kecil_sisa' => (float) $qtyInfo['qty_kecil_sisa'],
                    'total_draft' => $totalQty,
                    'kd_po'       => $payload['kd_po'],
                    'kd_barang'   => $payload['kd_barang']
                ]
            ]);
            return;
        }

        $this->db->trans_begin();
        $result = $this->M_Logistik->replace_tmp_po_received_item(
            $payload['kd_po'],
            $payload['kd_barang'],
            $insertRows
        );

        if (!$result || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                'status'  => 'error',
                'step'    => 'save_tmp',
                'message' => 'Draft penerimaan gagal disimpan.'
            ]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode([
            'status'       => 'success',
            'step'         => 'save_tmp',
            'message'      => empty($insertRows) ? 'Draft dikosongkan.' : 'Draft penerimaan berhasil disimpan.',
            'rows'         => $this->M_Logistik->get_tmp_po_received_item($payload['kd_po'], $payload['kd_barang']),
            'summary_rows' => $this->M_Logistik->get_tmp_po_received_summary_by_item($payload['kd_po'], $payload['kd_barang'])
        ]);
    }

    public function ajax_delete_tmp_po_received_row()
    {
        while (ob_get_level()) ob_end_clean();

        header('Content-Type: application/json; charset=utf-8');

        $idTmpReceived = (int) $this->input->post('id_tmp_recieved', TRUE);
        if ($idTmpReceived <= 0) {
            $idTmpReceived = (int) $this->input->post('id_tmp_received', TRUE);
        }
        if ($idTmpReceived <= 0) {
            $idTmpReceived = (int) $this->input->post('id', TRUE);
        }
        $noPo = trim((string) $this->input->post('no_po', TRUE));
        $kdSuplier = trim((string) $this->input->post('kd_suplier', TRUE));

        if ($idTmpReceived <= 0 || $noPo === '' || $kdSuplier === '') {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Parameter hapus draft belum lengkap.'
            ]);
            return;
        }

        $this->db->trans_begin();

        $deleted = $this->M_Logistik->delete_tmp_po_received_row($idTmpReceived, $kdSuplier, $noPo);

        if (!$deleted || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                'status'  => 'error',
                'message' => 'Baris draft gagal dihapus atau data tidak ditemukan.'
            ]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode([
            'status'  => 'success',
            'message' => 'Baris draft berhasil dihapus.'
        ]);
    }

    public function ajax_finalize_tmp_po_received()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $payload = [
                'no_po'       => trim((string) $this->input->post('no_po', TRUE)),
                'kd_po'       => trim((string) $this->input->post('kd_po', TRUE)),
                'kd_suplier'  => trim((string) $this->input->post('kd_suplier', TRUE)),
                'nosj'        => trim((string) $this->input->post('nosj', TRUE)),
                'tgl_sj'      => trim((string) $this->input->post('tgl_sj', TRUE)),
                'no_invoice'  => trim((string) $this->input->post('no_invoice', TRUE)),
                'jenis_lpb'   => trim((string) $this->input->post('jenis_lpb', TRUE)),
                'gudang_id'   => trim((string) $this->input->post('gudang_id', TRUE)),
                'keterangan'  => trim((string) $this->input->post('keterangan', TRUE)),
                'dilakukan_oleh' => $this->active_user_name(),
                'checker_name' => trim((string) $this->input->post('checker_name', TRUE)) ?: $this->active_user_name(),
                'checker_by' => trim((string) $this->input->post('checker_by', TRUE)) ?: $this->active_user_code()
            ];

            if ($payload['no_po'] === '' || $payload['kd_suplier'] === '') {
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'validate_header',
                    'message' => 'Parameter utama draft penerimaan tidak lengkap.'
                ]);
                return;
            }

            if ($payload['no_invoice'] === '') {
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'validate_header',
                    'message' => 'Nomor invoice wajib diisi.'
                ]);
                return;
            }

            if ($payload['jenis_lpb'] === '') {
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'validate_header',
                    'message' => 'Jenis PO / LPB wajib dipilih.'
                ]);
                return;
            }

            if ($payload['gudang_id'] === '') {
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'validate_header',
                    'message' => 'Gudang wajib dipilih.'
                ]);
                return;
            }

            $tmpRows = $this->M_Logistik->get_tmp_po_received_posting_rows($payload['no_po'], $payload['kd_suplier']);

            if (empty($tmpRows)) {
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'validate_detail',
                    'message' => 'Minimal harus ada 1 draft detail sebelum simpan.'
                ]);
                return;
            }

            $remainingRows = $this->M_Logistik->get_po_remaining_qty($payload['no_po'], $payload['kd_suplier']);
            $remainingMap = [];
            foreach ($remainingRows as $item) {
                $remainingMap[$item['kd_po'] . '||' . $item['kd_barang']] = (float) $item['qty_kecil_sisa'];
            }

            $draftMap = [];
            foreach ($tmpRows as $row) {
                $key = $row['kd_po'] . '||' . $row['kd_barang'];
                $draftMap[$key] = ($draftMap[$key] ?? 0) + (float) $row['qty_diterima'];
            }

            foreach ($draftMap as $key => $draftQty) {
                $qtySisa = $remainingMap[$key] ?? 0;
                if ($draftQty > $qtySisa + 0.0001) {
                    list($kdPo, $kdBarang) = explode('||', $key);
                    echo json_encode([
                        'status'  => 'error',
                        'step'    => 'validate_qty',
                        'message' => 'Ada qty draft yang melebihi Qty Sisa PO.',
                        'debug'   => [
                            'kd_po'       => $kdPo,
                            'kd_barang'   => $kdBarang,
                            'qty_kecil_sisa' => $qtySisa,
                            'total_draft' => $draftQty
                        ]
                    ]);
                    return;
                }
            }

            $payload['kd_po'] = trim((string) ($tmpRows[0]['kd_po'] ?? ''));
            $payload['detail_rows'] = $tmpRows;

            if ($payload['kd_po'] === '') {
                $payload['kd_po'] = trim((string) ($tmpRows[0]['kd_po'] ?? ''));
            }

            if ($payload['kd_po'] === '') {
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'validate_header',
                    'message' => 'kd_po dari draft temporary tidak ditemukan.'
                ]);
                return;
            }

            $this->M_Logistik->ensure_lpb_workflow_columns();
            $this->db->trans_begin();
            $idLpb = $this->M_Logistik->create_lpb_from_tmp($payload, $tmpRows);

            if (!$idLpb || $this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'save_final',
                    'message' => 'Gagal menyimpan penerimaan final ke tabel LPB.'
                ]);
                return;
            }

            $updatedPrePoStatus = $this->M_Logistik->update_pre_po_status_by_kd_po($payload['kd_po'], 2);

            if (!$updatedPrePoStatus || $this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode([
                    'status'  => 'error',
                    'step'    => 'update_pre_po_status',
                    'message' => 'LPB berhasil dibuat, tetapi status PO gagal diperbarui.'
                ]);
                return;
            }

            $this->db->trans_commit();

            $accountingResult = null;
            try {
                $this->load->library('Accounting_source_service');
                $accountingResult = $this->accounting_source_service->post_goods_receipt(
                    $idLpb,
                    (int)$this->session->userdata('id') ?: null
                );
            } catch (Throwable $eAcc) {
                $accountingResult = ['status' => 'error', 'message' => $eAcc->getMessage()];
            }

            echo json_encode([
                'status'  => 'success',
                'step'    => 'save_final',
                'message' => 'Penerimaan berhasil disimpan ke LPB dengan status POST, status PO diperbarui, dan draft temporary sudah dibersihkan.',
                'accounting' => $accountingResult,
                'debug'   => [
                    'id_lpb'       => $idLpb,
                    'no_po'        => $payload['no_po'],
                    'kd_po'        => $payload['kd_po'],
                    'total_detail' => count($tmpRows)
                ]
            ]);
        } catch (Throwable $e) {
            if ($this->db->trans_status() !== FALSE) {
                @$this->db->trans_rollback();
            }
            echo json_encode([
                'status'  => 'error',
                'step'    => 'exception',
                'message' => 'Terjadi kesalahan sistem saat menyimpan LPB final: ' . $e->getMessage()
            ]);
        }
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
        $data['page_title']  = 'KARISMA - LOGISTIK';
        $data['list_satuan'] = $this->db->order_by('nm_satuan', 'ASC')->get('tb_satuan')->result_array();
        $date1 = $this->input->post('date1');
        $date2 = $this->input->post('date2');
        $data['lpb'] = $this->M_Logistik->get_data_po($date1, $date2);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/dtalbp.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function sync_po_pre_do()
    {
        $url = "http://localhost/kiu_po/get_data_pre_po_erp";
        $this->load->model('Api/M_Api', 'apiPo');

        try {
            $result = $this->apiPo->sync_pre_po_from_remote($url);

            if (empty($result['status'])) {
                $this->session->set_flashdata('error', $result['message'] ?? 'Sinkronisasi PO gagal.');
                redirect('data_lpb_zahir');
                return;
            }

            $this->session->set_flashdata(
                'success',
                "Sync berhasil. Baru: {$result['inserted']}, Update: {$result['updated']}, Dilewati: {$result['skipped']}."
            );
        } catch (Throwable $e) {
            log_message('error', 'sync_po_pre_do exception: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal sinkronisasi PO: ' . $e->getMessage());
        }

        redirect('data_lpb_zahir');
    }

    public function save_qty_diterima()
    {
        $no_po = $this->input->post('no_po', TRUE);
        $kd_po = $this->input->post('kd_po', TRUE);
        $rows  = $this->input->post('rows');

        if (empty($no_po) || empty($rows)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap.');
            redirect('data_lpb_zahir');
            return;
        }

        $insert_batch = [];

        foreach ($rows as $item) {
            $kd_barang = $item['kd_barang'] ?? '';
            $sub_rows  = $item['sub']       ?? [];

            if (empty($kd_barang) || empty($sub_rows)) continue;

            foreach ($sub_rows as $sub) {
                if (empty($sub['qty_diterima'])) continue;

                $insert_batch[] = [
                    'no_po'        => $no_po,
                    'kd_po'        => $kd_po ?: null,
                    'kd_barang'    => $kd_barang,
                    'qty_diterima' => (int) $sub['qty_diterima'],
                    'satuan'       => $sub['satuan']   ?? null,
                    'no_lot'       => $sub['no_lot']   ?: null,
                    'exp_date'     => !empty($sub['exp_date']) ? $sub['exp_date'] : null,
                    'create_at'    => date('Y-m-d H:i:s'),
                ];
            }
        }

        if (!empty($insert_batch)) {
            $this->db->insert_batch('tb_po_received', $insert_batch);
            $this->session->set_flashdata('success', count($insert_batch) . ' baris penerimaan berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data valid untuk disimpan.');
        }

        redirect('data_lpb_zahir');
    }

    public function get_barang_by_po()
    {
        while (ob_get_level()) ob_end_clean();

        $no_po      = $this->input->get('no_po',      TRUE);
        $kd_suplier = $this->input->get('kd_suplier', TRUE); // TAMBAH INI

        if (empty($no_po)) {
            echo json_encode(['status' => 'error', 'message' => 'no_po kosong']);
            exit;
        }

        $sql = "
            SELECT
                pp.kd_barang,
                COALESCE(mb.nama_barang, '-')        AS nama_barang,
                pp.qty                               AS qty_order,
                pp.satuan,
                COALESCE(sub.qty_masuk, 0)           AS qty_masuk,
                (pp.qty - COALESCE(sub.qty_masuk,0)) AS sisa
            FROM tb_pre_po pp
            LEFT JOIN tbpo_barang mb
                ON mb.kode_barang = pp.kd_barang
            LEFT JOIN (
                SELECT kd_barang, SUM(qty_diterima) AS qty_masuk
                FROM tb_po_received
                WHERE no_po = ?
                GROUP BY kd_barang
            ) sub ON sub.kd_barang = pp.kd_barang
            WHERE pp.no_po = ?
            AND pp.kd_suplier = ?
            HAVING sisa > 0
            ORDER BY pp.kd_barang
        ";

        $result = $this->db->query($sql, [$no_po, $no_po, $kd_suplier])->result_array();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    }

    public function po_selesai()
    {
        $date1 = $this->input->post('date1');
        $date2 = $this->input->post('date2');

        $data['page_title'] = 'KARISMA - PO Selesai';
        $data['lpb']        = $this->M_Logistik->get_data_po($date1, $date2);
        $data['date1']      = $date1;
        $data['date2']      = $date2;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/po_selesai.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function riwayat_barang_masuk()
    {
        $date1 = $this->input->post('date1');
        $date2 = $this->input->post('date2');

        $data['page_title'] = 'KARISMA - Riwayat Barang Masuk';
        $data['riwayat']    = $this->M_Logistik->get_riwayat_barang_masuk($date1, $date2);
        $data['date1']      = $date1;
        $data['date2']      = $date2;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/riwayat_barang_masuk.php', $data);
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

    public function api_stock_per_gudang()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Method not allowed'
                ]));
        }
        $gudang = $this->input->get('gudang', true);
        $data = $this->M_Ics->get_stock_per_gudang_view($gudang);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $data
            ]));
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
            $this->input->get_post('gudang'),
            $this->input->get_post('daterange') ?: $this->input->get_post('tanggal'),
            $this->input->get_post('status')
        );

        foreach ($data as $fm) {
            $status = (string) $fm->status;
            if ($status === 'POSTED') {
                $badge = "<span class='badge badge-success'><i class='fas fa-check-circle'></i> POSTED</span>";
                $actions = "
                    <button class='btn btn-sm btn-info btn-detail' data-id='" . html_escape($fm->noreff) . "'><i class='fas fa-eye'></i></button>
                    <button class='btn btn-sm btn-warning btn-unpost' data-id='" . html_escape($fm->noreff) . "'><i class='fas fa-undo'></i></button>
                    <button class='btn btn-sm btn-danger btn-delete' data-id='" . html_escape($fm->noreff) . "'><i class='fas fa-ban'></i></button>
                ";
            } elseif ($status === 'UNPOST') {
                $badge = "<span class='badge badge-info'><i class='fas fa-clock'></i> UNPOST</span>";
                $actions = "
                    <button class='btn btn-sm btn-info btn-detail' data-id='" . html_escape($fm->noreff) . "'><i class='fas fa-eye'></i></button>
                    <button class='btn btn-sm btn-danger btn-delete' data-id='" . html_escape($fm->noreff) . "'><i class='fas fa-ban'></i></button>
                ";
            } elseif ($status === 'HOLD') {
                $badge = "<span class='badge badge-warning'><i class='fas fa-pause'></i> HOLD</span>";
                $actions = "
                    <button class='btn btn-sm btn-info btn-detail' data-id='" . html_escape($fm->noreff) . "'><i class='fas fa-eye'></i></button>
                    <button class='btn btn-sm btn-success btn-rollback' data-id='" . html_escape($fm->noreff) . "'><i class='fas fa-thumbs-up'></i></button>
                    <button class='btn btn-sm btn-danger btn-delete' data-id='" . html_escape($fm->noreff) . "'><i class='fas fa-ban'></i></button>
                ";
            } else {
                $badge = "<span class='badge badge-danger'><i class='fas fa-undo'></i> " . html_escape($status) . "</span>";
                $actions = '';
            }

            echo "<tr>
                <td>" . html_escape($fm->tgl_transaksi) . "</td>
                <td>" . html_escape($fm->noreff) . "</td>
                <td>" . html_escape($fm->gudang_a) . "</td>
                <td>" . html_escape($fm->gudang_b) . "</td>
                <td>" . html_escape($fm->keterangan) . "</td>
                <td>" . html_escape($fm->nm_karyawan) . "</td>
                <td>{$badge}</td>
                <td class='text-center'>{$actions}</td>
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
        $this->M_Ics->ensure_mutasi_barang_schema();

        $data['page_title'] = 'KARISMA - LOGISTIK';
        $data['tanggal']    = date('Y-m-d');
        $data['ref_mutasi'] = $this->M_Ics->generate_noreff();
        $data['gudang']     = $this->M_Ics->get_gudang();
        $data['gudang_aktif'] = 2;
        $data['gudang_tujuan_aktif'] = (int) $this->input->get('tujuangdg', true);
        $data['satuan_options'] = $this->M_Ics->get_mutasi_satuan_options();


        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/input_mutasi_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function list_barang_mutasi()
    {
        $this->M_Ics->ensure_mutasi_barang_schema();

        $idGudang = (int) $this->input->get('fromgdg', true);
        if (!$idGudang) {
            $idGudang = 2;
        }

        $data['page_title']    = 'KARISMA - LOGISTIK';
        $data['gudang_asal']   = $idGudang;
        $data['gudang_tujuan'] = (int) $this->input->get('tujuangdg', true);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/list_barang_mutasi.php', $data);
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

    public function ajax_list_barang_mutasi_gudang()
    {
        $id_gudang = $this->input->get('id_gudang', true);
        $search    = trim((string) $this->input->get('term', true));
        $page      = max(1, (int) $this->input->get('page', true));
        $perPage   = (int) $this->input->get('per_page', true);
        $perPage   = ($perPage > 0 && $perPage <= 50) ? $perPage : 10;
        $offset    = ($page - 1) * $perPage;

        if (!$id_gudang) {
            echo json_encode([
                'status' => true,
                'data' => [],
                'pagination' => [
                    'page' => 1,
                    'per_page' => $perPage,
                    'total_rows' => 0,
                    'total_pages' => 1
                ]
            ]);
            return;
        }

        $this->M_Ics->ensure_mutasi_barang_schema();
        $totalRows = $this->M_Ics->count_barang_mutasi_by_gudang($id_gudang, $search);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        echo json_encode([
            'status' => true,
            'data' => $this->M_Ics->get_barang_mutasi_by_gudang($id_gudang, $search, $perPage, $offset),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_rows' => $totalRows,
                'total_pages' => $totalPages
            ]
        ]);
    }

    public function ajax_lot_tmp_mutasi()
    {
        $id        = $this->input->get('id', true);
        $id_gudang = $this->input->get('id_gudang', true);
        $search    = trim((string) $this->input->get('term', true));
        $page      = max(1, (int) $this->input->get('page', true));
        $perPage   = (int) $this->input->get('per_page', true);
        $perPage   = ($perPage > 0 && $perPage <= 50) ? $perPage : 10;
        $offset    = ($page - 1) * $perPage;
        $user      = $this->session->userdata('nik');

        if (!$id || !$id_gudang || !$user) {
            echo json_encode(['status' => false, 'msg' => 'Data tidak valid']);
            return;
        }

        $this->M_Ics->ensure_mutasi_barang_schema();
        $tmp = $this->M_Ics->get_tmp_mutasi_item($id, $user);
        if (!$tmp) {
            echo json_encode(['status' => false, 'msg' => 'Data temporary tidak ditemukan']);
            return;
        }

        $totalRows = $this->M_Ics->count_mutasi_lot_options(
            $id_gudang,
            (string) $tmp->kode_barang_system,
            (string) $tmp->nama_barang,
            $search
        );
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $data = $this->M_Ics->get_mutasi_lot_options(
            $id_gudang,
            (string) $tmp->kode_barang_system,
            (string) $tmp->nama_barang,
            $search,
            $perPage,
            $offset
        );

        echo json_encode([
            'status' => true,
            'item'   => $tmp,
            'data'   => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_rows' => $totalRows,
                'total_pages' => $totalPages
            ]
        ]);
    }

    public function ajax_mutasi_lot_select2()
    {
        $idGudang = $this->input->get('id_gudang', true);
        $kodeSystem = trim((string) $this->input->get('kode_barang_system', true));
        $namaBarang = trim((string) $this->input->get('nama_barang', true));
        $search = trim((string) $this->input->get('term', true));

        $rows = $this->M_Ics->get_mutasi_lot_select2($idGudang, $kodeSystem, $namaBarang, $search);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => $row->no_lot,
                'text' => $row->no_lot,
                'qty_gudang' => (int) $row->qty_gudang
            ];
        }

        echo json_encode(['results' => $result]);
    }

    public function ajax_mutasi_exp_select2()
    {
        $idGudang = $this->input->get('id_gudang', true);
        $kodeSystem = trim((string) $this->input->get('kode_barang_system', true));
        $namaBarang = trim((string) $this->input->get('nama_barang', true));
        $noLot = trim((string) $this->input->get('no_lot', true));
        $search = trim((string) $this->input->get('term', true));

        $rows = $this->M_Ics->get_mutasi_exp_select2($idGudang, $kodeSystem, $namaBarang, $noLot, $search);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => $row->exp_date,
                'text' => $row->exp_date,
                'qty_gudang' => (int) $row->qty_gudang
            ];
        }

        echo json_encode(['results' => $result]);
    }

    public function ajax_mutasi_lot_qty()
    {
        $idGudang = $this->input->get('id_gudang', true);
        $kodeSystem = trim((string) $this->input->get('kode_barang_system', true));
        $namaBarang = trim((string) $this->input->get('nama_barang', true));
        $noLot = trim((string) $this->input->get('no_lot', true));
        $expDate = trim((string) $this->input->get('exp_date', true));

        $qty = $this->M_Ics->get_mutasi_lot_qty($idGudang, $kodeSystem, $namaBarang, $noLot, $expDate);
        echo json_encode([
            'status' => true,
            'qty_gudang' => (int) $qty
        ]);
    }

    public function ajax_add_tmp_mutasi()
    {
        $this->M_Ics->ensure_mutasi_barang_schema();

        $qty = (int) $this->input->post('qty');
        $data = [
            'nama_barang'  => $this->input->post('nama_barang', true),
            'exp_date'     => (string) $this->input->post('exp_date', true),
            'qty'          => $qty,
            'satuan_id'    => (int) $this->input->post('satuan_id'),
            'user_inputer' => $this->session->userdata('nik')
        ];

        if ($this->db->field_exists('kode_barang', 'tb_tmp_mutasi')) {
            $data['kode_barang'] = $this->input->post('kode_barang', true);
        }
        if ($this->db->field_exists('kode_barang_system', 'tb_tmp_mutasi')) {
            $data['kode_barang_system'] = $this->input->post('kode_barang_system', true);
        }
        if ($this->db->field_exists('no_lot', 'tb_tmp_mutasi')) {
            $data['no_lot'] = (string) $this->input->post('no_lot', true);
        }
        if ($this->db->field_exists('gudang_asal', 'tb_tmp_mutasi')) {
            $data['gudang_asal'] = (int) $this->input->post('gudang_asal');
        }

        if (!$data['nama_barang'] || $data['qty'] <= 0) {
            echo json_encode(['status' => false, 'msg' => 'Data tidak valid']);
            return;
        }
        if ($this->db->field_exists('gudang_asal', 'tb_tmp_mutasi') && empty($data['gudang_asal'])) {
            echo json_encode(['status' => false, 'msg' => 'Dari Gudang wajib dipilih']);
            return;
        }

        if (!empty($data['gudang_asal']) && !empty($data['no_lot']) && !empty($data['exp_date'])) {
            $qtyGudang = $this->M_Ics->get_mutasi_lot_qty(
                $data['gudang_asal'],
                (string) ($data['kode_barang_system'] ?? ''),
                (string) $data['nama_barang'],
                (string) $data['no_lot'],
                (string) $data['exp_date']
            );

            if ($data['qty'] > $qtyGudang) {
                echo json_encode(['status' => false, 'msg' => 'Qty yang diminta melebihi stok database']);
                return;
            }
        }

        $this->M_Ics->insert_tmp_mutasi($data);

        echo json_encode(['status' => true, 'id' => $this->db->insert_id()]);
    }

    public function ajax_list_tmp_mutasi()
    {
        $this->M_Ics->ensure_mutasi_barang_schema();
        $user_id = $this->session->userdata('nik') ?: $this->session->userdata('user_id') ?: $this->session->userdata('username') ?: 'system';
        $data = $this->M_Ics->get_tmp_mutasi_by_user($user_id);

        echo json_encode($data);
    }

    public function ajax_update_tmp_mutasi()
    {
        $this->M_Ics->ensure_mutasi_barang_schema();

        $id   = $this->input->post('id');
        $user = $this->session->userdata('nik');
        $id_gudang = $this->input->post('id_gudang');
        $exp_date = trim((string) $this->input->post('exp_date', true));
        $no_lot = trim((string) $this->input->post('no_lot', true));
        $qty = (int) $this->input->post('qty');

        if (!$id || !$user) {
            echo json_encode(['status' => false, 'msg' => 'Data tidak valid']);
            return;
        }

        if ($exp_date === '' || $no_lot === '' || $qty <= 0) {
            echo json_encode(['status' => false, 'msg' => 'Expired Date, No Lot, dan Qty wajib diisi']);
            return;
        }

        $tmp = $this->M_Ics->get_tmp_mutasi_item($id, $user);
        if (!$tmp) {
            echo json_encode(['status' => false, 'msg' => 'Data temporary tidak ditemukan']);
            return;
        }

        if ($id_gudang) {
            $qtyGudang = $this->M_Ics->get_mutasi_lot_qty(
                $id_gudang,
                (string) $tmp->kode_barang_system,
                (string) $tmp->nama_barang,
                $no_lot,
                $exp_date
            );
            if ($qty > $qtyGudang) {
                echo json_encode(['status' => false, 'msg' => 'Qty melebihi stok gudang']);
                return;
            }
        }

        $updateData = [
            'exp_date'  => $exp_date,
            'qty'       => $qty,
            'satuan_id' => (int)$this->input->post('satuan_id')
        ];
        if ($this->db->field_exists('no_lot', 'tb_tmp_mutasi')) {
            $updateData['no_lot'] = $no_lot;
        }

        $this->M_Ics->update_tmp_mutasi(
            $id,
            $user,
            $updateData
        );

        echo json_encode(['status' => true, 'msg' => 'Update sukses']);
    }

    public function ajax_update_tmp_mutasi_field()
    {
        $this->M_Ics->ensure_mutasi_barang_schema();

        $id        = $this->input->post('id');
        $field     = trim((string) $this->input->post('field', true));
        $value     = $this->input->post('value', true);
        $id_gudang = $this->input->post('id_gudang');
        $user      = $this->session->userdata('nik');

        if (!$id || !$user || !in_array($field, ['qty', 'satuan_id'], true)) {
            echo json_encode(['status' => false, 'msg' => 'Data update tidak valid']);
            return;
        }

        $tmp = $this->M_Ics->get_tmp_mutasi_item($id, $user);
        if (!$tmp) {
            echo json_encode(['status' => false, 'msg' => 'Data temporary tidak ditemukan']);
            return;
        }

        if (!$id_gudang && !empty($tmp->gudang_asal)) {
            $id_gudang = $tmp->gudang_asal;
        }

        if ($field === 'qty') {
            $qty = (int) $value;
            if ($qty <= 0) {
                echo json_encode(['status' => false, 'msg' => 'Jumlah harus lebih dari 0']);
                return;
            }

            if ($id_gudang) {
                if (trim((string) $tmp->no_lot) !== '' && trim((string) $tmp->exp_date) !== '') {
                    $qtyGudang = $this->M_Ics->get_mutasi_lot_qty(
                        $id_gudang,
                        (string) $tmp->kode_barang_system,
                        (string) $tmp->nama_barang,
                        (string) $tmp->no_lot,
                        (string) $tmp->exp_date
                    );
                } else {
                    $qtyGudang = $this->M_Ics->get_mutasi_item_total_qty(
                        $id_gudang,
                        (string) $tmp->kode_barang_system,
                        (string) $tmp->nama_barang
                    );
                }

                if ($qty > $qtyGudang) {
                    echo json_encode(['status' => false, 'msg' => 'Jumlah melebihi stok database']);
                    return;
                }
            }

            $this->M_Ics->update_tmp_mutasi($id, $user, ['qty' => $qty]);
            echo json_encode(['status' => true, 'msg' => 'Jumlah berhasil diperbarui']);
            return;
        }

        $satuanId = (int) $value;
        if ($satuanId <= 0 || !$this->M_Ics->is_mutasi_satuan_exists($satuanId)) {
            echo json_encode(['status' => false, 'msg' => 'Satuan tidak valid']);
            return;
        }

        $this->M_Ics->update_tmp_mutasi($id, $user, ['satuan_id' => $satuanId]);
        echo json_encode(['status' => true, 'msg' => 'Satuan berhasil diperbarui']);
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
        $this->M_Ics->ensure_mutasi_barang_schema();

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

        if ((string) $post['fromgdg'] === (string) $post['tujuangdg']) {
            echo json_encode(['status' => false, 'msg' => 'Dari Gudang dan Ke Gudang tidak boleh sama']);
            return;
        }

        $tmp = $this->M_Ics->get_tmp_mutasi_by_user($user);
        if (!$tmp) {
            echo json_encode(['status' => false, 'msg' => 'Data mutasi kosong']);
            return;
        }

        foreach ($tmp as $t) {
            if (trim((string) $t->exp_date) === '' || trim((string) $t->no_lot) === '') {
                echo json_encode(['status' => false, 'msg' => 'Plot No Lot dan Expired Date semua barang terlebih dahulu']);
                return;
            }

            $qtyGudang = $this->M_Ics->get_mutasi_lot_qty(
                $post['fromgdg'],
                (string) $t->kode_barang_system,
                (string) $t->nama_barang,
                (string) $t->no_lot,
                (string) $t->exp_date
            );

            if ((float) $t->qty > ((float) $qtyGudang + 0.0001)) {
                echo json_encode([
                    'status' => false,
                    'msg' => 'Qty ' . $t->nama_barang . ' lot ' . $t->no_lot . ' melebihi stok database'
                ]);
                return;
            }
        }

        $stockValidation = $this->M_Ics->validate_mutasi_stock_available($tmp, $post['fromgdg'], $post['tujuangdg']);
        if (!$stockValidation['status']) {
            echo json_encode(['status' => false, 'msg' => $stockValidation['msg']]);
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
                if ($this->db->field_exists('no_lot', 'tb_stock_hold')) {
                    $hold[count($hold) - 1]['no_lot'] = $t->no_lot;
                }
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
                if ($this->db->field_exists('no_lot', 'tb_detail_mutasi')) {
                    $detail[count($detail) - 1]['no_lot'] = $t->no_lot;
                }
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
                if ($this->db->field_exists('no_lot', 'tb_detail_mutasi')) {
                    $detail[count($detail) - 1]['no_lot'] = $t->no_lot;
                }
            }
            $this->db->insert_batch('tb_detail_mutasi', $detail);

            $stockPost = $this->M_Ics->post_mutasi_stock($tmp, $post['fromgdg'], $post['tujuangdg'], $post['nofresnsi']);
            if (!$stockPost['status']) {
                $this->db->trans_rollback();
                echo json_encode(['status' => false, 'msg' => $stockPost['msg']]);
                return;
            }
        }

        $this->db->where('user_inputer', $user)->delete('tb_tmp_mutasi');

        $this->db->insert('tb_log_mutasi', [
            'noreff'     => $post['nofresnsi'],
            'aksi'       => $STATUS,
            'keterangan' => $IS_HOLD
                ? 'MUTASI HOLD GUDANG ' . $post['fromgdg']
                : 'MUTASI POSTED GUDANG ' . $post['fromgdg'],
            'user'       => $user,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'msg' => 'Gagal merekam mutasi']);
            return;
        }

        $this->db->trans_commit();

        echo json_encode([
            'status' => true,
            'msg'    => $IS_HOLD
                ? 'Mutasi berhasil direkam sebagai HOLD'
                : 'Mutasi berhasil direkam',
            'noreff' => $post['nofresnsi'],
            'new_ref' => $this->M_Ics->generate_noreff()
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

        $header = $this->M_Ics->get_mutasi_header($noreff);
        if (!$header) {
            echo json_encode(['status' => false, 'msg' => 'Data mutasi tidak ditemukan']);
            return;
        }

        $this->db->trans_begin();

        if ($header->status === 'POSTED') {
            $reverse = $this->M_Ics->reverse_mutasi_stock($noreff);
            if (!$reverse['status']) {
                $this->db->trans_rollback();
                echo json_encode(['status' => false, 'msg' => $reverse['msg']]);
                return;
            }
        }

        $this->db
            ->where('noreff', $noreff)
            ->update('tb_mutasi', [
                'status' => 'UNPOST'
            ]);

        $this->db->insert('tb_log_mutasi', [
            'noreff' => $noreff,
            'aksi' => 'UNPOST',
            'keterangan' => 'UNPOST MUTASI',
            'user' => $this->session->userdata('nik'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'msg' => 'Gagal unpost mutasi']);
            return;
        }

        $this->db->trans_commit();

        echo json_encode(['status' => true, 'msg' => 'Mutasi berhasil di-unpost']);
    }

    public function ajax_delete_mutasi()
    {
        $noreff = $this->input->post('noreff');
        if (!$noreff) {
            echo json_encode(['status' => false, 'msg' => 'No ref tidak valid']);
            return;
        }

        $header = $this->M_Ics->get_mutasi_header($noreff);
        if (!$header) {
            echo json_encode(['status' => false, 'msg' => 'Data mutasi tidak ditemukan']);
            return;
        }

        $this->db->trans_begin();

        if ($header->status === 'POSTED') {
            $reverse = $this->M_Ics->reverse_mutasi_stock($noreff);
            if (!$reverse['status']) {
                $this->db->trans_rollback();
                echo json_encode(['status' => false, 'msg' => $reverse['msg']]);
                return;
            }
        }

        $this->db->where('noreff', $noreff)->delete('tb_detail_mutasi');
        $this->db->where('noreff', $noreff)->delete('tb_mutasi');
        $this->db->where('noref', $noreff)->delete('tb_stock_hold');

        $this->db->insert('tb_log_mutasi', [
            'noreff' => $noreff,
            'aksi' => 'DELETE',
            'keterangan' => 'DELETE MUTASI',
            'user' => $this->session->userdata('nik'),
            'created_at' => date('Y-m-d H:i:s')
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
        $header = $this->M_Ics->get_mutasi_header($noreff);
        if (!$header) {
            echo json_encode(['status' => false, 'msg' => 'Data mutasi tidak ditemukan']);
            return;
        }

        if ($header->status !== 'HOLD') {
            echo json_encode(['status' => false, 'msg' => 'Rollback hanya dapat diproses dari status HOLD']);
            return;
        }

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

        $details = $this->db
            ->where('noreff', $noreff)
            ->get('tb_detail_mutasi')
            ->result();

        $stockPost = $this->M_Ics->post_mutasi_stock($details, $header->gudang_asal, $header->gudang_mutasi, $noreff);
        if (!$stockPost['status']) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'msg' => $stockPost['msg']]);
            return;
        }

        $this->db
            ->where('noref', $noreff)
            ->update('tb_stock_hold', [
                'status' => 'RELEASE',
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

    // RETUR
    public function dash_retur()
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';
        $this->M_ReturPembelian->ensure_schema();
        $data['retur_all']  = $this->M_Ics->get_retur_dashboard();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/dashretur.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_retur($kd_retur = null)
    {
        $kd_retur = $kd_retur ? $kd_retur : $this->input->get('kd_retur', true);
        if (!$kd_retur) {
            show_404();
        }

        $data['page_title'] = 'KARISMA - LOGISTIK';
        $data['kd_retur'] = $kd_retur;
        $data['detail_retur'] = $this->M_Ics->get_retur_detail_by_kd($kd_retur);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/detail_retur.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function retur_pembelian()
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';
        $this->M_ReturPembelian->ensure_schema();
        $data['retur_pembelian_rows'] = $this->M_ReturPembelian->header_rows(100);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/returform_pembelian.php', $data);
        $this->load->view('content/logistik/ics/ajax_retur_pembelian.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_retur_pembelian_lpb_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $rows = $this->M_ReturPembelian->lpb_options($this->input->get('term', true));
        $result = [];
        foreach ($rows as $row) {
            $supplier = trim((string)($row['nama_suplier'] ?? ''));
            $result[] = [
                'id' => (int)$row['id_lpb'],
                'text' => trim((string)$row['nomor_lpb']) . ' | PO ' . trim((string)$row['no_po']) . ($supplier !== '' ? ' | ' . $supplier : ''),
                'kd_po' => $row['kd_po'],
                'no_po' => $row['no_po'],
                'kd_supplier' => $row['kd_suplier'],
                'nama_supplier' => $supplier,
                'gudang_id' => $row['gudang_id'],
            ];
        }

        $this->json_response($result);
    }

    public function ajax_retur_pembelian_lpb_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $idLpb = (int)$this->input->get('id_lpb', true);
        if ($idLpb <= 0) {
            $this->json_response(['status' => false, 'message' => 'LPB tidak valid.', 'rows' => []]);
            return;
        }

        $this->json_response([
            'status' => true,
            'message' => 'Detail LPB berhasil dimuat.',
            'rows' => $this->M_ReturPembelian->lpb_detail_rows($idLpb)
        ]);
    }

    public function ajax_retur_pembelian_create_draft()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $detailsRaw = $this->input->post('details', false);
        $details = json_decode((string)$detailsRaw, true);
        if (!is_array($details)) {
            $details = [];
        }

        $result = $this->M_ReturPembelian->create_draft([
            'id_lpb' => $this->input->post('id_lpb', true),
            'tanggal_retur' => $this->input->post('tanggal_retur', true),
            'jenis_penyelesaian' => $this->input->post('jenis_penyelesaian', true),
            'alasan_retur' => $this->input->post('alasan_retur', true),
        ], $details, $this->active_user_name());

        $this->json_response([
            'status' => (bool)$result['success'],
            'message' => $result['message'],
            'data' => $result['data'],
            'errors' => $result['errors']
        ]);
    }

    public function ajax_retur_pembelian_submit()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $result = $this->M_ReturPembelian->submit((int)$this->input->post('id_retur_pembelian', true), $this->active_user_name());
        $this->json_response(['status' => (bool)$result['success'], 'message' => $result['message'], 'data' => $result['data'], 'errors' => $result['errors']]);
    }

    public function ajax_retur_pembelian_verify_purchasing()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $result = $this->M_ReturPembelian->verify_purchasing(
            (int)$this->input->post('id_retur_pembelian', true),
            $this->input->post('catatan', true),
            $this->active_user_name()
        );
        $this->json_response(['status' => (bool)$result['success'], 'message' => $result['message'], 'data' => $result['data'], 'errors' => $result['errors']]);
    }

    public function ajax_retur_pembelian_verify_accounting()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $result = $this->M_ReturPembelian->verify_accounting(
            (int)$this->input->post('id_retur_pembelian', true),
            $this->input->post('catatan', true),
            $this->active_user_name()
        );
        $this->json_response(['status' => (bool)$result['success'], 'message' => $result['message'], 'data' => $result['data'], 'errors' => $result['errors']]);
    }

    public function ajax_retur_pembelian_post()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $result = $this->M_ReturPembelian->post((int)$this->input->post('id_retur_pembelian', true), $this->active_user_name());
        $this->json_response(['status' => (bool)$result['success'], 'message' => $result['message'], 'data' => $result['data'], 'errors' => $result['errors']]);
    }

    public function ajax_retur_pembelian_void()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $result = $this->M_ReturPembelian->void_posted(
            (int)$this->input->post('id_retur_pembelian', true),
            $this->input->post('alasan', true),
            $this->active_user_name()
        );
        $this->json_response(['status' => (bool)$result['success'], 'message' => $result['message'], 'data' => $result['data'], 'errors' => $result['errors']]);
    }

    public function retur_pembelian_adjustment()
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';
        $this->M_LpbPriceAdjustment->ensure_schema();
        $data['adjustment_rows'] = $this->M_LpbPriceAdjustment->rows(100);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/returform_pembelian_adjustment.php', $data);
        $this->load->view('content/logistik/ics/ajax_retur_pembelian_adjustment.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_retur_pembelian_adjustment_lpb_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $rows = $this->M_LpbPriceAdjustment->lpb_options($this->input->get('term', true));
        $result = [];
        foreach ($rows as $row) {
            $supplier = trim((string)($row['nama_suplier'] ?? ''));
            $result[] = [
                'id' => (int)$row['id_lpb'],
                'text' => trim((string)$row['nomor_lpb']) . ' | PO ' . trim((string)$row['no_po']) . ($supplier !== '' ? ' | ' . $supplier : ''),
                'nomor_lpb' => $row['nomor_lpb'],
                'kd_po' => $row['kd_po'],
                'no_po' => $row['no_po'],
                'no_invoice' => $row['no_invoice'],
                'kd_supplier' => $row['kd_suplier'],
                'nama_supplier' => $supplier,
                'gudang_id' => $row['gudang_id'],
            ];
        }

        $this->json_response($result);
    }

    public function ajax_retur_pembelian_adjustment_lpb_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $idLpb = (int)$this->input->get('id_lpb', true);
        if ($idLpb <= 0) {
            $this->json_response(['status' => false, 'message' => 'LPB tidak valid.', 'rows' => []]);
            return;
        }

        $this->json_response([
            'status' => true,
            'message' => 'Detail LPB berhasil dimuat.',
            'rows' => $this->M_LpbPriceAdjustment->lpb_detail_rows($idLpb),
            'adjustment_lot' => M_LpbPriceAdjustment::ADJ_LOT,
            'adjustment_expired' => M_LpbPriceAdjustment::ADJ_EXPIRED,
        ]);
    }

    public function ajax_retur_pembelian_adjustment_post()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $detailsRaw = $this->input->post('details', false);
        $details = json_decode((string)$detailsRaw, true);
        if (!is_array($details)) {
            $details = [];
        }

        $result = $this->M_LpbPriceAdjustment->create_and_post([
            'id_lpb' => $this->input->post('id_lpb', true),
            'tanggal_adjustment' => $this->input->post('tanggal_adjustment', true),
            'alasan_adjustment' => $this->input->post('alasan_adjustment', true),
        ], $details, $this->active_user_name(), (int)$this->session->userdata('id') ?: null);

        $this->json_response([
            'status' => (bool)$result['success'],
            'message' => $result['message'],
            'data' => $result['data'],
            'errors' => $result['errors'],
        ]);
    }

    public function ajax_retur_pembelian_faktur_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search = $this->input->get('term', true);
        $data   = $this->M_Ics->get_retur_pembelian_faktur_select2($search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->kd_faktur,
                'text' => $row->kd_faktur
            ];
        }

        echo json_encode($result);
    }

    public function ajax_retur_pembelian_barang_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search    = $this->input->get('term', true);
        $kd_faktur = $this->input->get('kd_faktur', true);

        if (!$kd_faktur) {
            echo json_encode([]);
            return;
        }

        $data = $this->M_Ics->get_retur_pembelian_barang_by_faktur_select2($kd_faktur, $search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->kd_barang,
                'text' => $row->nama_barang
            ];
        }

        echo json_encode($result);
    }

    public function ajax_retur_pembelian_exp_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search    = $this->input->get('term', true);
        $kd_faktur = $this->input->get('kd_faktur', true);
        $kd_barang = $this->input->get('kd_barang', true);

        if (!$kd_faktur || !$kd_barang) {
            echo json_encode([]);
            return;
        }

        $data = $this->M_Ics->get_retur_pembelian_exp_by_faktur_barang($kd_faktur, $kd_barang, $search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->exp_date,
                'text' => $row->exp_date
            ];
        }

        echo json_encode($result);
    }

    public function ajax_retur_pembelian_add_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $kd_faktur   = $this->input->post('kd_faktur', true);
        $kd_barang   = $this->input->post('kd_barang', true);
        $tgl_expired = $this->input->post('tgl_expired', true);
        $qty         = (int)$this->input->post('qty', true);
        $no_lot      = $this->input->post('no_lot', true);

        if (!$kd_faktur || !$kd_barang || !$tgl_expired || $qty <= 0) {
            echo json_encode(['status' => false, 'message' => 'Data belum lengkap']);
            return;
        }

        $retur_type = 1;
        $kd_retur = $this->M_Ics->generate_kd_retur_by_type($retur_type);

        $data = [
            'kd_retur'    => $kd_retur,
            'retur_type'  => 1,
            'kd_faktur'   => $kd_faktur,
            'kd_barang'   => $kd_barang,
            'no_lot'      => $no_lot ? $no_lot : '-',
            'tgl_expired' => $tgl_expired,
            'qty'         => $qty,
            'status_data' => 2,
            'tgl_input'   => date('Y-m-d H:i:s')
        ];

        $ok = $this->M_Ics->insert_retur_detail($data);

        echo json_encode([
            'status'  => (bool)$ok,
            'message' => $ok ? 'Data retur tersimpan' : 'Gagal menyimpan data'
        ]);
    }

    public function ajax_retur_pembelian_list_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $data = $this->M_Ics->get_retur_detail(1, 2);
        echo json_encode($data);
    }

    public function ajax_retur_pembelian_delete_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id', true);
        if (!$id) {
            echo json_encode(['status' => false, 'message' => 'ID tidak valid']);
            return;
        }

        $ok = $this->M_Ics->delete_retur_detail($id);
        echo json_encode([
            'status' => (bool)$ok,
            'message' => $ok ? 'Data terhapus' : 'Gagal menghapus data'
        ]);
    }

    public function ajax_retur_faktur_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search = $this->input->get('term', true);
        $data   = $this->M_Ics->get_retur_faktur_select2($search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->kd_faktur,
                'text' => $row->kd_faktur
            ];
        }

        echo json_encode($result);
    }

    public function ajax_retur_barang_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search    = $this->input->get('term', true);
        $kd_faktur = $this->input->get('kd_faktur', true);

        if (!$kd_faktur) {
            echo json_encode([]);
            return;
        }

        $data = $this->M_Ics->get_retur_barang_by_faktur_select2($kd_faktur, $search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->kd_barang,
                'text' => $row->nama_barang
            ];
        }

        echo json_encode($result);
    }

    public function ajax_retur_lot_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search    = $this->input->get('term', true);
        $kd_faktur = $this->input->get('kd_faktur', true);
        $kd_barang = $this->input->get('kd_barang', true);

        if (!$kd_faktur || !$kd_barang) {
            echo json_encode([]);
            return;
        }

        $data = $this->M_Ics->get_retur_lot_by_faktur_barang($kd_faktur, $kd_barang, $search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->no_lot,
                'text' => $row->no_lot
            ];
        }

        echo json_encode($result);
    }

    public function ajax_retur_exp_select2()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search    = $this->input->get('term', true);
        $kd_faktur = $this->input->get('kd_faktur', true);
        $kd_barang = $this->input->get('kd_barang', true);
        $no_lot    = $this->input->get('no_lot', true);

        if (!$kd_faktur || !$kd_barang || !$no_lot) {
            echo json_encode([]);
            return;
        }

        $data = $this->M_Ics->get_retur_exp_by_faktur_barang_lot($kd_faktur, $kd_barang, $no_lot, $search);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'   => $row->exp_date,
                'text' => $row->exp_date
            ];
        }

        echo json_encode($result);
    }

    public function ajax_retur_add_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $kd_faktur   = $this->input->post('kd_faktur', true);
        $kd_barang   = $this->input->post('kd_barang', true);
        $no_lot      = $this->input->post('no_lot', true);
        $tgl_expired = $this->input->post('tgl_expired', true);
        $qty         = (int)$this->input->post('qty', true);

        if (!$kd_faktur || !$kd_barang || !$no_lot || !$tgl_expired || $qty <= 0) {
            echo json_encode(['status' => false, 'message' => 'Data belum lengkap']);
            return;
        }

        $retur_type = 2;
        $kd_retur = $this->M_Ics->generate_kd_retur_by_type($retur_type);

        $data = [
            'kd_retur'    => $kd_retur,
            'retur_type'  => 2,
            'kd_faktur'   => $kd_faktur,
            'kd_barang'   => $kd_barang,
            'no_lot'      => $no_lot,
            'tgl_expired' => $tgl_expired,
            'qty'         => $qty,
            'status_data' => 2,
            'tgl_input'   => date('Y-m-d H:i:s')
        ];

        $ok = $this->M_Ics->insert_retur_detail($data);

        echo json_encode([
            'status'  => (bool)$ok,
            'message' => $ok ? 'Data retur tersimpan' : 'Gagal menyimpan data'
        ]);
    }

    public function ajax_retur_list_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $data = $this->M_Ics->get_retur_detail(2, 2);
        echo json_encode($data);
    }

    public function ajax_retur_delete_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id', true);
        if (!$id) {
            echo json_encode(['status' => false, 'message' => 'ID tidak valid']);
            return;
        }

        $ok = $this->M_Ics->delete_retur_detail($id);
        echo json_encode([
            'status' => (bool)$ok,
            'message' => $ok ? 'Data terhapus' : 'Gagal menghapus data'
        ]);
    }

    public function ajax_retur_rekam_penjualan()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $kd_retur = $this->input->post('kd_retur', true);
        $keterangan = $this->input->post('keterangan', true);
        $tgl_transaksi = $this->input->post('tgl_transaksi', true);

        if (!$kd_retur) {
            echo json_encode(['status' => false, 'message' => 'No referensi belum diisi']);
            return;
        }

        $this->db->trans_begin();

        $updated = $this->M_Ics->update_retur_detail_status_by_kd_retur($kd_retur, 2, '1');
        if ($updated > 0) {
            $inputAt = $tgl_transaksi ? ($tgl_transaksi . ' ' . date('H:i:s')) : date('Y-m-d H:i:s');
            $data = [
                'type_retur' => '2',
                'kd_retur'   => $kd_retur,
                'keterangan' => $keterangan,
                'status'     => '1',
                'input_by'   => $this->session->userdata('nik'),
                'input_at'   => $inputAt,
                'create_at'  => date('Y-m-d H:i:s')
            ];
            $okInsert = $this->M_Ics->insert_retur_header($data);
        } else {
            $okInsert = false;
        }

        if ($this->db->trans_status() === FALSE || !$okInsert) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'message' => 'Gagal rekam retur penjualan']);
            return;
        }

        $this->db->trans_commit();
        echo json_encode(['status' => true, 'message' => 'Retur penjualan tersimpan']);
    }

    public function ajax_retur_rekam_pembelian()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $kd_retur = $this->input->post('kd_retur', true);
        $keterangan = $this->input->post('keterangan', true);
        $tgl_transaksi = $this->input->post('tgl_transaksi', true);

        if (!$kd_retur) {
            echo json_encode(['status' => false, 'message' => 'No referensi belum diisi']);
            return;
        }

        $this->db->trans_begin();

        $updated = $this->M_Ics->update_retur_detail_status_by_kd_retur($kd_retur, 1, '1');
        if ($updated > 0) {
            $inputAt = $tgl_transaksi ? ($tgl_transaksi . ' ' . date('H:i:s')) : date('Y-m-d H:i:s');
            $data = [
                'type_retur' => '1',
                'kd_retur'   => $kd_retur,
                'keterangan' => $keterangan,
                'status'     => '1',
                'input_by'   => $this->session->userdata('nik'),
                'input_at'   => $inputAt,
                'create_at'  => date('Y-m-d H:i:s')
            ];
            $okInsert = $this->M_Ics->insert_retur_header($data);
        } else {
            $okInsert = false;
        }

        if ($this->db->trans_status() === FALSE || !$okInsert) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'message' => 'Gagal rekam retur pembelian']);
            return;
        }

        $this->db->trans_commit();
        echo json_encode(['status' => true, 'message' => 'Retur pembelian tersimpan']);
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

    public function api_stock($params)
    {
        $params = [
            'gudang'      => $this->input->get('gudang'),
        ];

        if (!empty($gudang) && $gudang != '1') {
            $params['gudang'] = $gudang;
        }

        $data = $this->M_Ics->get_stock($params);

        echo json_encode([
            'status' => $data ? true : false,
            'data'   => $data
        ]);
    }
}
