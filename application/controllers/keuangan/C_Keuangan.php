<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Keuangan extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Keuangan');
        $this->load->model('M_Logistik');
        $this->load->model('M_Stockbuffer');
        $this->load->model('M_Bufferstockglobal');
        $this->load->helper(array('form', 'url'));
        $this->load->library('upload');
        $this->load->library('Accounting_service');
        $this->load->database();
    }

    public function index()
    {
        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['count_gudang']   = $this->M_Keuangan->get_data_gdg();
        $data['updated']        = $this->M_Keuangan->get_updated();
        $data['listdo']         = $this->M_Logistik->getdo();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/body.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function insertmodule()
    {
        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['kd']             = $this->M_Keuangan->generate_update();
        $data['updated']        = $this->M_Keuangan->get_updated_upload();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/coba1.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function insermodule_lot()
    {
        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['kd']             = $this->M_Keuangan->generate_update();
        $data['updated']        = $this->M_Keuangan->get_updated_upload();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/updt_lot.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function import()
    {
        $session    = $this->session->userdata('jobdesk');

        if ($session == 'ADMINKEU') {
            $file_data = fopen($_FILES['csv_file']['tmp_name'], 'r');
            fgetcsv($file_data);

            $data = [];
            while ($row = fgetcsv($file_data)) {
                $data[] = [
                    'kd_suplier'    => $row[0],
                    'kd_barang'     => $row[1],
                    'gudang'        => $row[2],
                    'qty'           => $row[3]
                ];
            }

            $gdgid   = $this->input->post('gdgid');

            if (!empty($data) && $gdgid != '1') {
                $this->update_data();
                $this->M_Keuangan->insert_batch($data);
                $this->session->set_flashdata('message', 'Data imported successfully.');
            } else if (!empty($data) && $gdgid == '1') {
                $this->update_data();
                $this->M_Keuangan->batch_global($data);
                $this->session->set_flashdata('message', 'Data imported successfully.');
            } else {
                $this->session->set_flashdata('message', 'Failed to import data.');
            }
            redirect('insertmodule');
        } elseif ($session == 'LOGISTIK') {
            if (isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['size'] > 0) {
                $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
                $header = fgetcsv($file); // skip header

                $insertCount = 0;
                $revisiCount = 0;

                while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {

                    if (count($row) < 9) {
                        continue;
                    }

                    $kd_faktur = $row[1];
                    $kd_barang = $row[4];
                    $qty       = $row[5];
                    $nolot     = $row[7];
                    $tgl_exp   = $row[8];

                    // Cek apakah data sudah ada
                    $kdupdate = $this->input->post('kdgenerates');
                    $existing = $this->M_Keuangan->get_by_faktur_barang($kd_faktur, $kd_barang, $qty, $nolot, $tgl_exp);

                    $newData = [
                        'kdupdate'    => $kdupdate,
                        'tgl_inputer' => $row[0],
                        'kd_faktur'   => $row[1],
                        'kd_rute'     => $row[2],
                        'kd_customer' => $row[3],
                        'kd_barang'   => $row[4],
                        'nama_barang' => $row[5],
                        'qty'         => $row[6],
                        'satuan'      => $row[7],
                        'no_lot'      => $row[8],
                        'tgl_exp'     => $row[9],
                        'nominal_p'   => $row[10],
                        'jtempo'      => $row[11],
                        'upload_sts'  => 1,
                        'data_sts'    => 1,
                        'barang_sts'  => 1,
                        'create_at'   => date('Y-m-d H:i:s'),
                    ];

                    if ($existing) {

                        unset($existing->kd_faktur);
                        $existing_arr = (array) $existing;

                        $diff = array_diff_assoc($newData, $existing_arr);
                        if (!empty($diff)) {
                            // Ada perbedaan → update
                            $newData['barang_sts'] = 1;
                            $newData['upload_sts'] = 2;

                            $this->M_Keuangan->update_by_faktur($kd_faktur, $kd_barang, $newData);
                            $revisiCount++;
                        }
                    } else {
                        // Belum ada → insert baru
                        $this->M_Keuangan->insert($newData);
                        $insertCount++;
                    }
                }

                fclose($file);
                $this->session->set_flashdata('message', "Import selesai: {$insertCount} data baru, {$revisiCount} direvisi.");
            } else {
                $this->session->set_flashdata('message', 'File tidak valid!');
            }
            redirect('logistik');
        } else {
            $this->session->set_flashdata("SESI BERAKHIR", "Silahkan login lagi ");
            redirect('Auth');
        }
    }

    public function preview_csv()
    {
        if (!isset($_FILES['file_csv']['name']) || $_FILES['file_csv']['size'] <= 0) {
            $this->session->set_flashdata('error', 'Pilih file CSV terlebih dahulu.');
            redirect('pre_do');
        }

        $file = fopen($_FILES['file_csv']['tmp_name'], 'r');
        $header = fgetcsv($file); // skip header

        $data_baru = [];
        $data_duplikat = [];

        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            $kd_faktur = trim($row[0]);
            $tgl_faktur = trim($row[1]);
            $nama_customer = trim($row[2]);
            $alamat = trim($row[3]);

            $row_data = [
                'kd_faktur' => $kd_faktur,
                'tgl_faktur' => $tgl_faktur,
                'nama_customer' => $nama_customer,
                'alamat' => $alamat
            ];

            if ($this->Pre_do_model->is_exist($kd_faktur)) {
                $data_duplikat[] = $row_data;
            } else {
                $data_baru[] = $row_data;
            }
        }
        fclose($file);

        $data['data_baru'] = $data_baru;
        $data['data_duplikat'] = $data_duplikat;

        // Simpan data_baru ke session untuk konfirmasi insert
        $this->session->set_userdata('data_baru_csv', $data_baru);

        $this->load->view('pre_do_preview', $data);
    }

    public function insertmodule_pnd()
    {
        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['kd']             = $this->M_Keuangan->generate_update();
        $data['updated']        = $this->M_Keuangan->get_updated_upload();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/update_stock_pnd.php', $data);
        $this->load->view('partial/main/footer.php');
    }
    public function csv_import_po_pnd()
    {
        $session    = $this->session->userdata('jobdesk');

        if ($session == 'ADMINKEU') {
            $file_data = fopen($_FILES['csv_file']['tmp_name'], 'r');
            fgetcsv($file_data); // Skip header row

            $data = [];
            while ($row = fgetcsv($file_data)) {
                $data[] = [
                    'nopo'                  => $row[0],
                    'tanggal'               => $row[1],
                    'kd_sup'                => $row[2],
                    'kd_barang'             => $row[3],
                    'qty_order'             => $row[4],
                    'qty_order_success'     => $row[5],
                    'qty_kurang'            => $row[6],
                ];
            }

            $gdgid   = $this->input->post('gdgid');

            if (!empty($data) && $gdgid != '1') {
                $this->update_data();
                $this->M_Keuangan->insert_po_pending($data);
                $this->session->set_flashdata('message', 'Data imported successfully.');
            } else if (!empty($data) && $gdgid == '1') {
                $this->update_data();
                $this->M_Keuangan->batch_global($data);
                $this->session->set_flashdata('message', 'Data imported successfully.');
            } else {
                $this->session->set_flashdata('message', 'Failed to import data.');
            }
            redirect('insertmodule_pnd');
        } else {
            $this->session->set_flashdata("SESI BERAKHIR", "Silahkan login lagi ");
            redirect('Auth');
        }
    }

    public function csv_import_lot()
    {
        $session    = $this->session->userdata('jobdesk');

        if ($session == 'ADMINKEU') {
            $file_data = fopen($_FILES['csv_file']['tmp_name'], 'r');
            fgetcsv($file_data); // Skip header row

            $data = [];
            while ($row = fgetcsv($file_data)) {
                $data[] = [
                    'kd_barang' => $row[0],
                    'nm_barang' => $row[1],
                    'gudang'    => $row[2],
                    'qty'       => $row[3],
                    'unit'      => $row[4],
                    'no_lot'    => $row[5],
                    'exp_date'  => $row[6],
                    'suplier'   => $row[7]
                ];
            }

            $gdgid   = $this->input->post('gdgid');

            if (!empty($data) && $gdgid != '1') {
                $this->update_data();
                $this->M_Keuangan->insert_batch_lot($data);
                $this->session->set_flashdata('message', 'Data imported successfully.');
            } else if (!empty($data) && $gdgid == '1') {
                $this->update_data();
                $this->M_Keuangan->batch_global($data);
                $this->session->set_flashdata('message', 'Data imported successfully.');
            } else {
                $this->session->set_flashdata('message', 'Failed to import data.');
            }
            redirect('insermodule_lot');
        } else {
            $this->session->set_flashdata("SESI BERAKHIR", "Silahkan login lagi ");
            redirect('Auth');
        }
    }

    private function update_data()
    {
        $kd      = $this->input->post('kdgenerates');
        $gdgid   = $this->input->post('gdgid');
        $date    = $this->input->post('dateupload');

        if ($gdgid == 1) {
            $gudang = 'Global';
        } else if ($gdgid == 2) {
            $gudang = 'Gdg. Induk';
        } else if ($gdgid == 3) {
            $gudang = 'Gdg. Rusak';
        } else if ($gdgid == 4) {
            $gudang = 'exp_lot';
        } else if ($gdgid == 5) {
            $gudang = 'pendingpo';
        } else if ($gdgid == 6) {
            $gudang = 'Preparation DO';
        }


        $data  = array(
            'kd_update'     => $kd,
            'gudangid'      => $gdgid,
            'gudang'        => $gudang,
            'last_update'   => $date
        );

        $this->M_Keuangan->insertupdate($data);
    }

    public function truncateitm($kd, $id)
    {
        $this->M_Keuangan->truncateitm($id);
        $this->M_Keuangan->deleteupdateed($kd);

        redirect('keuangan');
    }
    public function deletedata($id)
    {
        $this->M_Keuangan->truncateitm($id);

        redirect('keuangan');
    }

    function get_stock_a($id)
    {
        $list = $this->M_Stockbuffer->get_datatables($id);
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $field) {
            $no++;
            $row = array();

            $row[] = $field->nmsuplier;
            $row[] = $field->nmbarang;
            $row[] = $field->qty_box;
            $row[] = $field->qty_pcs;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Stockbuffer->count_all($id),
            "recordsFiltered" => $this->M_Stockbuffer->count_filtered($id),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }

    //  GUDANG KARISMAERP
    public function gudang($id)
    {
        if ($id == '1') {

            $gudangid = $id;
            $gudang = 'Global';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/gudang.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($id == '2') {

            $gudangid = $id;
            $gudang = 'Gdg. Induk';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/gudang.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($id == '3') {

            $gudangid = $id;
            $gudang = 'Gdg. Rusak';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/gudang.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($id == '4') {

            $gudangid = $id;
            $gudang = 'Stock Expired & LOT';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/gudang.php', $data);
            $this->load->view('partial/main/footergdg.php');
        }
    }

    public function get_data_global()
    {
        $list = $this->M_Bufferstockglobal->get_datatables();
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $field) {
            $no++;
            $row = array();

            $row[] = $field->nmsuplier;
            $row[] = $field->nmbarang;
            $row[] = $field->qty_box;
            $row[] = $field->qty_pcs;
            $row[] = '<a href="' . base_url('detail_stock/') . $field->kdbarang . '"class="btn btn-primary" style="align-items: center;"><i class="fas fa-eye"></i></a>';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Bufferstockglobal->count_all(),
            "recordsFiltered" => $this->M_Bufferstockglobal->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }

    public function list_stock_minimum($id)
    {
        if ($id == '1') {
            $gudangid = $id;
            $gudang = 'STOCK MINIMUM - Global';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock']          = $this->M_Keuangan->get_stock_global();

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_minimum.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } elseif ($id == '2') {
            $gudangid = $id;
            $gudang = 'STOCK MINIMUM - Induk';
            $gdg    = 'Gdg. Induk';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock']          = $this->M_Keuangan->get_stockmin_gdg($gdg);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_minimum.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } elseif ($id == '3') {
            $gudangid = $id;
            $gudang = 'STOCK MINIMUM - Rusak';
            $gdg    = 'Gdg. Rusak';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock']          = $this->M_Keuangan->get_stockmin_gdg($gdg);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_minimum.php', $data);
            $this->load->view('partial/main/footergdg.php');
        }
    }

    public function stock_suplier($gdg, $id)
    {
        if ($id == '1') {
            $kd = 'BASFI01';
        } elseif ($id == '2') {
            $kd = 'SYNGE01';
        } elseif ($id == '3') {
            $kd = 'NUFAR01';
        } elseif ($id == '4') {
            $kd = 'BAYER01';
        }

        if ($gdg == '1') {

            $gudangid = $id;
            $suplier = $kd;
            $gudang = 'Global';
            $gudangs = $gdg;

            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangs']        = $gudangs;
            $data['gudangid']       = $gudangid;
            $data['suplier']        = $suplier;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock_sup']      = $this->M_Keuangan->get_stock_by_sup_global($suplier);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_by_suplier.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($gdg == '2') {

            $gudangid = $id;
            $suplier = $kd;
            $gudang = 'Gdg. Induk';
            $gudangs = $gdg;

            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangs']        = $gudangs;
            $data['gudangid']       = $gudangid;
            $data['suplier']        = $suplier;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock_sup']      = $this->M_Keuangan->get_stock_by_sup($suplier, $gudang);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_by_suplier.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($gdg == '3') {

            $gudangid = $id;
            $suplier = $kd;
            $gudang = 'Gdg. Rusak';
            $gudangs = $gdg;

            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangs']        = $gudangs;
            $data['gudangid']       = $gudangid;
            $data['suplier']        = $suplier;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock_sup']      = $this->M_Keuangan->get_stock_by_sup($suplier, $gudang);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_by_suplier.php', $data);
            $this->load->view('partial/main/footergdg.php');
        }
    }

    public function pendingpo()
    {
        $data['page_title']     = 'KARISMA';
        $data['stocklist']      = $this->M_Keuangan->get_list_po_pending();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pendingpo.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }
    public function daily_stock_lot()
    {
        $data['page_title']     = 'KARISMA';
        $data['stocklist']      = $this->M_Keuangan->get_list_stock_lot();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/stock_exp_lot.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function detail_lot($kd)
    {
        $data['page_title']     = 'KARISMA';
        $data['detail_lot']      = $this->M_Keuangan->get_detail_lot($kd);
        $data['barang']      = $this->M_Keuangan->getsuplierlot($kd);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/detail_lot.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function master_barang()
    {
        $data['page_title']         = 'KARISMA';
        $data['gudangid']           = 0;
        $data['supplier_options']   = $this->M_Keuangan->master_barang_supplier_options();
        $data['kelompok_dagang_options'] = $this->M_Keuangan->master_barang_kelompok_dagang_options();
        $data['kelompok_barang_filter_options'] = $data['kelompok_dagang_options'];
        $data['akun_options']       = $this->M_Keuangan->master_barang_akun_options();
        $data['master_barang_access'] = [
            'jobdesk' => (string)$this->session->userdata('jobdesk'),
            'lv' => (int)$this->session->userdata('lv'),
            'can_full_edit' => $this->can_full_edit_master_barang(),
            'can_info_lain_edit' => $this->can_edit_info_lain_master_barang(),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/master_barang.php', $data);
        $this->load->view('partial/main/footergdg.php');
        $this->load->view('content/keuangan/ajax/ajax_master_barang.php', $data);
    }

    private function response_json($payload = [], $code = 200)
    {
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function master_barang_payload()
    {
        return [
            'kode_barang' => trim((string)$this->input->post('kode_barang', true)),
            'kd_suplier'  => trim((string)$this->input->post('kd_suplier', true)),
            'nama_barang' => trim((string)$this->input->post('nama_barang', true)),
            'bahan_aktif' => trim((string)$this->input->post('bahan_aktif', true)),
            'satuan'      => trim((string)$this->input->post('satuan', true)),
            'stock_minimum' => (int)$this->input->post('stock_minimum', true),
            'merk_barang' => trim((string)$this->input->post('merk_barang', true)),
            'kelompok_dagang' => trim((string)$this->input->post('kelompok_dagang', true)),
            'kelompok_barang' => trim((string)$this->input->post('kelompok_barang', true)),
            'kategori_barang' => trim((string)$this->input->post('kategori_barang', true)),
            'produk_fokus' => trim((string)$this->input->post('produk_fokus', true)),
            'panjang'     => (int)$this->input->post('panjang', true),
            'lebar'       => (int)$this->input->post('lebar', true),
            'tinggi'      => (int)$this->input->post('tinggi', true),
            'berat'       => (float)$this->input->post('berat', true),
            'isi'         => (float)$this->input->post('isi', true),
            'kemasan'     => (float)$this->input->post('kemasan', true),
            'is_active'   => $this->input->post('is_active', true) === 'F' ? 'F' : 'T',
            'is_lot'      => $this->input->post('is_lot', true) === 'T' ? 'T' : 'F',
            'is_inventori' => $this->input->post('is_inventori', true) === 'F' ? 'F' : 'T',
            'is_beli'      => $this->input->post('is_beli', true) === 'F' ? 'F' : 'T',
            'is_jual'      => $this->input->post('is_jual', true) === 'F' ? 'F' : 'T',
            'hpp_average'  => $this->input->post('hpp_average', true) === 'T' ? 'T' : 'F',
            'hpp_fifo'     => $this->input->post('hpp_fifo', true) === 'T' ? 'T' : 'F',
            'hpp_lifo'     => $this->input->post('hpp_lifo', true) === 'T' ? 'T' : 'F',
            'kode_akun_harga_pokok' => trim((string)$this->input->post('kode_akun_harga_pokok', true)),
            'kode_akun_penjualan' => trim((string)$this->input->post('kode_akun_penjualan', true)),
            'kode_akun_persediaan' => trim((string)$this->input->post('kode_akun_persediaan', true)),
            'kode_akun_pengiriman_beli' => trim((string)$this->input->post('kode_akun_pengiriman_beli', true)),
            'kode_akun_pengiriman_jual' => trim((string)$this->input->post('kode_akun_pengiriman_jual', true)),
            'kode_akun_retur_penjualan' => trim((string)$this->input->post('kode_akun_retur_penjualan', true)),
        ];
    }

    private function can_full_edit_master_barang()
    {
        $jobdesk = (string)$this->session->userdata('jobdesk');
        $level = (int)$this->session->userdata('lv');

        return in_array($jobdesk, ['ADMINKEU', 'ADMINKEUTC', 'ADMINPURCHASING'], true) || $level === 1;
    }

    private function can_edit_info_lain_master_barang()
    {
        return $this->can_full_edit_master_barang() || (string)$this->session->userdata('jobdesk') === 'LOGISTIK';
    }

    private function validate_master_barang_kelompok_dagang($payload)
    {
        if ($payload['kelompok_dagang'] === '') {
            return true;
        }

        return $this->M_Keuangan->master_barang_kelompok_dagang_exists($payload['kelompok_dagang']);
    }

    private function validate_master_barang_hpp($payload)
    {
        $checked = 0;
        foreach (['hpp_average', 'hpp_fifo', 'hpp_lifo'] as $field) {
            if ($payload[$field] === 'T') {
                $checked++;
            }
        }

        return $checked === 1;
    }

    private function validate_master_barang_akun($payload)
    {
        foreach ([
            'kode_akun_harga_pokok' => 'Harga Pokok',
            'kode_akun_penjualan' => 'Penjualan',
            'kode_akun_persediaan' => 'Persediaan',
            'kode_akun_pengiriman_beli' => 'Pengiriman Beli',
            'kode_akun_pengiriman_jual' => 'Pengiriman Jual',
            'kode_akun_retur_penjualan' => 'Retur Penjualan',
        ] as $field => $label) {
            if (!$this->M_Keuangan->master_barang_akun_exists($payload[$field])) {
                return $label;
            }
        }

        return true;
    }

    public function master_barang_list()
    {
        $search = trim((string)$this->input->post('search', true));
        $kelompokDagang = trim((string)$this->input->post('kelompok_barang', true));
        $limit = (int)$this->input->post('limit', true);
        if ($limit <= 0) {
            $limit = 100;
        }

        $rows = $this->M_Keuangan->master_barang_all($search, $kelompokDagang, $limit, 0);
        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'id'          => (int)$row->id_barang,
                'kode_barang' => $row->kode_barang,
                'nama_barang' => $row->nama_barang,
                'nama_suplier' => $row->nama_suplier,
                'satuan'      => $row->satuan,
                'kelompok_dagang' => $row->kelompok_dagang,
                'kelompok_dagang_label' => $row->kelompok_dagang_label,
                'is_active'   => $row->is_active,
                'is_lot'      => $row->is_lot,
            ];
        }

        $this->response_json([
            'status'   => true,
            'total'    => $this->M_Keuangan->master_barang_count_all(),
            'filtered' => $this->M_Keuangan->master_barang_count_filtered($search, $kelompokDagang),
            'data'     => $data,
        ]);
    }

    public function master_barang_detail()
    {
        $id = (int)$this->input->post('id', true);
        if ($id <= 0) {
            return $this->response_json([
                'status' => false,
                'message' => 'ID tidak valid.',
            ], 422);
        }

        $detail = $this->M_Keuangan->master_barang_by_id($id);
        if (!$detail) {
            return $this->response_json([
                'status' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $this->response_json([
            'status' => true,
            'data' => $detail,
        ]);
    }

    public function master_barang_store()
    {
        if (!$this->can_full_edit_master_barang()) {
            return $this->response_json([
                'status' => false,
                'message' => 'Akses anda tidak diizinkan menambah master barang.',
            ], 403);
        }

        $payload = $this->master_barang_payload();
        if ($payload['kode_barang'] === '' || $payload['nama_barang'] === '' || $payload['kd_suplier'] === '') {
            return $this->response_json([
                'status' => false,
                'message' => 'Kode barang, nama barang, dan supplier utama wajib diisi.',
            ], 422);
        }

        if (!$this->validate_master_barang_kelompok_dagang($payload)) {
            return $this->response_json([
                'status' => false,
                'message' => 'Kelompok dagang tidak valid.',
            ], 422);
        }

        if (!$this->validate_master_barang_hpp($payload)) {
            return $this->response_json([
                'status' => false,
                'message' => 'Harga Pokok wajib memilih tepat satu metode: Average, FIFO, atau LIFO.',
            ], 422);
        }

        $invalidAkun = $this->validate_master_barang_akun($payload);
        if ($invalidAkun !== true) {
            return $this->response_json([
                'status' => false,
                'message' => 'Kode akun ' . $invalidAkun . ' tidak valid.',
            ], 422);
        }

        if ($this->M_Keuangan->master_barang_by_kode($payload['kode_barang'])) {
            return $this->response_json([
                'status' => false,
                'message' => 'Kode barang sudah terdaftar pada master barang komersil.',
            ], 422);
        }

        $ok = $this->M_Keuangan->master_barang_store($payload);

        $this->response_json([
            'status' => (bool)$ok,
            'message' => $ok ? 'Data master barang berhasil disimpan.' : 'Gagal menyimpan data.',
        ], $ok ? 200 : 500);
    }

    public function master_barang_update()
    {
        $id = (int)$this->input->post('id', true);
        if ($id <= 0) {
            return $this->response_json([
                'status' => false,
                'message' => 'ID tidak valid.',
            ], 422);
        }

        if (!$this->can_edit_info_lain_master_barang()) {
            return $this->response_json([
                'status' => false,
                'message' => 'Akses anda tidak diizinkan mengubah master barang.',
            ], 403);
        }

        $payload = $this->master_barang_payload();
        if ($this->can_full_edit_master_barang()) {
            if ($payload['kode_barang'] === '' || $payload['nama_barang'] === '' || $payload['kd_suplier'] === '') {
                return $this->response_json([
                    'status' => false,
                    'message' => 'Kode barang, nama barang, dan supplier utama wajib diisi.',
                ], 422);
            }

            if (!$this->validate_master_barang_kelompok_dagang($payload)) {
                return $this->response_json([
                    'status' => false,
                    'message' => 'Kelompok dagang tidak valid.',
                ], 422);
            }

            if (!$this->validate_master_barang_hpp($payload)) {
                return $this->response_json([
                    'status' => false,
                    'message' => 'Harga Pokok wajib memilih tepat satu metode: Average, FIFO, atau LIFO.',
                ], 422);
            }

            $invalidAkun = $this->validate_master_barang_akun($payload);
            if ($invalidAkun !== true) {
                return $this->response_json([
                    'status' => false,
                    'message' => 'Kode akun ' . $invalidAkun . ' tidak valid.',
                ], 422);
            }

            if ($this->M_Keuangan->master_barang_by_kode($payload['kode_barang'], $id)) {
                return $this->response_json([
                    'status' => false,
                    'message' => 'Kode barang sudah digunakan pada data lain.',
                ], 422);
            }

            $ok = $this->M_Keuangan->master_barang_update($id, $payload);
        } else {
            $ok = $this->M_Keuangan->master_barang_update_info_lain($id, $payload);
        }

        $this->response_json([
            'status' => (bool)$ok,
            'message' => $ok ? 'Data master barang berhasil diupdate.' : 'Gagal update data.',
        ], $ok ? 200 : 500);
    }

    public function master_barang_delete()
    {
        if (!$this->can_full_edit_master_barang()) {
            return $this->response_json([
                'status' => false,
                'message' => 'Akses anda tidak diizinkan menghapus master barang.',
            ], 403);
        }

        $id = (int)$this->input->post('id', true);
        if ($id <= 0) {
            return $this->response_json([
                'status' => false,
                'message' => 'ID tidak valid.',
            ], 422);
        }

        $ok = $this->M_Keuangan->master_barang_delete($id);

        $this->response_json([
            'status' => (bool)$ok,
            'message' => $ok ? 'Data master barang berhasil dihapus.' : 'Gagal menghapus data.',
        ], $ok ? 200 : 500);
    }

    public function master_customer()
    {
        $data['page_title'] = 'KARISMA';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/master_customer.php', $data);
        $this->load->view('partial/main/footergdg.php');
        $this->load->view('content/keuangan/ajax/ajax_master_customer.php', $data);
    }

    private function master_customer_payload()
    {
        return [
            'kd_customer'         => trim((string)$this->input->post('kd_customer', true)),
            'nama_customer'       => trim((string)$this->input->post('nama_customer', true)),
            'nama_kios'           => trim((string)$this->input->post('nama_kios', true)),
            'alamat_kios'         => trim((string)$this->input->post('alamat_kios', true)),
            'telp1'               => trim((string)$this->input->post('telp1', true)),
            'telp2'               => trim((string)$this->input->post('telp2', true)),
            'regional'            => trim((string)$this->input->post('regional', true)),
            'jam_buka_tutup'      => trim((string)$this->input->post('jam_buka_tutup', true)),
            'karakteristik_kios'  => trim((string)$this->input->post('karakteristik_kios', true)),
        ];
    }

    public function master_customer_list()
    {
        $draw   = (int)$this->input->post('draw');
        $start  = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $searchInput = $this->input->post('search');
        $search = '';
        if (is_array($searchInput) && isset($searchInput['value'])) {
            $search = trim((string)$searchInput['value']);
        }

        if ($length <= 0) {
            $length = 10;
        }

        $rows = $this->M_Keuangan->master_customer_all($length, $start, $search);
        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'kd_customer'        => $row->kd_customer,
                'nama_customer'      => $row->nama_customer,
                'nama_kios'          => $row->nama_kios,
                'alamat_kios'        => $row->alamat_kios,
                'telp1'              => $row->telp1,
                'telp2'              => $row->telp2,
                'regional'           => $row->regional,
                'jam_buka_tutup'     => $row->jam_buka_tutup,
                'karakteristik_kios' => $row->karakteristik_kios,
                'aksi'               => '<button type="button" class="btn btn-sm btn-warning btn-edit-customer mr-1" data-id="' . (int)$row->id . '"><i class="fas fa-pen"></i></button>
                                          <button type="button" class="btn btn-sm btn-danger btn-delete-customer" data-id="' . (int)$row->id . '" data-nama="' . html_escape($row->nama_customer) . '"><i class="fas fa-trash"></i></button>',
            ];
        }

        $this->response_json([
            'draw'            => $draw,
            'recordsTotal'    => $this->M_Keuangan->master_customer_count_all(),
            'recordsFiltered' => $this->M_Keuangan->master_customer_count_filtered($search),
            'data'            => $data,
        ]);
    }

    public function master_customer_detail()
    {
        $id = (int)$this->input->post('id', true);
        if ($id <= 0) {
            return $this->response_json([
                'status' => false,
                'message' => 'ID tidak valid.',
            ], 422);
        }

        $detail = $this->M_Keuangan->master_customer_by_id($id);
        if (!$detail) {
            return $this->response_json([
                'status' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $this->response_json([
            'status' => true,
            'data' => $detail,
        ]);
    }

    public function master_customer_store()
    {
        $payload = $this->master_customer_payload();
        if ($payload['kd_customer'] === '' || $payload['nama_customer'] === '') {
            return $this->response_json([
                'status' => false,
                'message' => 'Kode customer dan nama customer wajib diisi.',
            ], 422);
        }

        $ok = $this->M_Keuangan->master_customer_store($payload);

        $this->response_json([
            'status' => (bool)$ok,
            'message' => $ok ? 'Data master customer berhasil disimpan.' : 'Gagal menyimpan data.',
        ], $ok ? 200 : 500);
    }

    public function master_customer_update()
    {
        $id = (int)$this->input->post('id', true);
        if ($id <= 0) {
            return $this->response_json([
                'status' => false,
                'message' => 'ID tidak valid.',
            ], 422);
        }

        $payload = $this->master_customer_payload();
        if ($payload['kd_customer'] === '' || $payload['nama_customer'] === '') {
            return $this->response_json([
                'status' => false,
                'message' => 'Kode customer dan nama customer wajib diisi.',
            ], 422);
        }

        $ok = $this->M_Keuangan->master_customer_update($id, $payload);

        $this->response_json([
            'status' => (bool)$ok,
            'message' => $ok ? 'Data master customer berhasil diupdate.' : 'Gagal update data.',
        ], $ok ? 200 : 500);
    }

    public function master_customer_delete()
    {
        $id = (int)$this->input->post('id', true);
        if ($id <= 0) {
            return $this->response_json([
                'status' => false,
                'message' => 'ID tidak valid.',
            ], 422);
        }

        $ok = $this->M_Keuangan->master_customer_delete($id);

        $this->response_json([
            'status' => (bool)$ok,
            'message' => $ok ? 'Data master customer berhasil dihapus.' : 'Gagal menghapus data.',
        ], $ok ? 200 : 500);
    }

    private function accounting_ajax_response($success, $message, $data = [], $errors = [], $code = 200)
    {
        return $this->response_json([
            'success' => (bool)$success,
            'message' => $message,
            'data' => $success ? $data : null,
            'errors' => $success ? (object)[] : $errors,
            'meta' => [
                'request_id' => uniqid('acct_', true),
                'timestamp' => date('c'),
            ],
        ], $code);
    }

    private function can_access_jurnal()
    {
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $username = strtolower(trim((string)$this->session->userdata('username')));
        $level = (int)$this->session->userdata('lv');

        return $username === 'admin'
            || (bool)$this->session->userdata('is_admin_dashboard')
            || ($level === 1 && in_array($jobdesk, ['ADMIN', 'ADMINKEU', 'ADMINKEUTC'], true));
    }

    private function require_jurnal_access($json = false)
    {
        if ($this->can_access_jurnal()) {
            return true;
        }

        if ($json) {
            $this->accounting_ajax_response(false, 'Akses modul jurnal hanya untuk admin dan keuangan.', null, [
                'code' => 'FORBIDDEN',
                'details' => [],
            ], 403);
            return false;
        }

        show_error('Akses modul jurnal hanya untuk admin dan keuangan.', 403, 'Akses Ditolak');
        return false;
    }

    private function jurnal_payload()
    {
        return [
            'kode_akun' => trim((string)$this->input->post('kode_akun', true)),
            'nama_akun' => trim((string)$this->input->post('nama_akun', true)),
            'id_klasifikasi' => (int)$this->input->post('id_klasifikasi', true),
            'parent_id' => (int)$this->input->post('parent_id', true),
            'saldo_normal' => strtoupper(trim((string)$this->input->post('saldo_normal', true))),
            'tipe_akun' => strtoupper(trim((string)$this->input->post('tipe_akun', true))),
            'tipe_kontrol' => strtoupper(trim((string)$this->input->post('tipe_kontrol', true))),
            'allow_manual_journal' => (int)$this->input->post('allow_manual_journal', true) === 1 ? 1 : 0,
            'is_active' => (int)$this->input->post('is_active', true) === 0 ? 0 : 1,
        ];
    }

    private function validate_jurnal_payload($payload, $id = 0)
    {
        if (!$this->M_Keuangan->accounting_schema_ready()) {
            return 'Schema accounting belum tersedia. Jalankan SQL migration modul jurnal terlebih dahulu.';
        }

        if ($payload['kode_akun'] === '' || $payload['nama_akun'] === '') {
            return 'Kode akun dan nama akun wajib diisi.';
        }

        if ($payload['id_klasifikasi'] <= 0 || !$this->M_Keuangan->accounting_klasifikasi_by_id($payload['id_klasifikasi'])) {
            return 'Klasifikasi akun wajib dipilih.';
        }

        if (!$this->M_Keuangan->accounting_saldo_normal_by_code($payload['saldo_normal'])) {
            return 'Saldo normal tidak valid.';
        }

        if (!in_array($payload['tipe_akun'], ['HEADER', 'POSTING'], true)) {
            return 'Tipe akun tidak valid.';
        }

        if (!$this->M_Keuangan->accounting_tipe_kontrol_by_code($payload['tipe_kontrol'])) {
            return 'Tipe kontrol tidak valid.';
        }

        if ($this->M_Keuangan->accounting_account_by_code($payload['kode_akun'], $id)) {
            return 'Kode akun sudah digunakan.';
        }

        if ($payload['parent_id'] > 0) {
            if ((int)$payload['parent_id'] === (int)$id) {
                return 'Akun tidak boleh menjadi parent bagi dirinya sendiri.';
            }

            $parent = $this->M_Keuangan->accounting_account_by_id($payload['parent_id']);
            if (!$parent) {
                return 'Parent akun tidak ditemukan.';
            }

            if ($parent->tipe_akun !== 'HEADER') {
                return 'Parent akun harus bertipe HEADER.';
            }
        }

        if ($id > 0 && $payload['tipe_akun'] === 'POSTING' && $this->M_Keuangan->accounting_account_has_children($id)) {
            return 'Akun yang memiliki child account harus bertipe HEADER.';
        }

        return '';
    }

    private function accounting_support_cards()
    {
        return [
            [
                'key' => 'klasifikasi',
                'title' => 'Klasifikasi',
                'icon' => 'fas fa-layer-group',
                'description' => 'Kelola kelompok laporan dan saldo normal dasar akun.',
            ],
            [
                'key' => 'saldo-normal',
                'title' => 'Saldo Normal',
                'icon' => 'fas fa-balance-scale',
                'description' => 'Kelola master DEBIT/KREDIT yang dipakai akun.',
            ],
            [
                'key' => 'tipe-kontrol',
                'title' => 'Tipe Kontrol',
                'icon' => 'fas fa-sliders-h',
                'description' => 'Kelola fungsi bisnis akun seperti kas, bank, piutang, dan hutang.',
            ],
            [
                'key' => 'parent-subclass',
                'title' => 'Parent / Subclass',
                'icon' => 'fas fa-sitemap',
                'description' => 'Kelola akun HEADER sebagai parent/subclass.',
            ],
        ];
    }

    public function jurnal()
    {
        if (!$this->require_jurnal_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - JURNAL';
        $data['schema_ready'] = $this->M_Keuangan->accounting_schema_ready();
        $data['support_schema_ready'] = $this->M_Keuangan->accounting_support_schema_ready();
        $data['klasifikasi_options'] = $this->M_Keuangan->accounting_klasifikasi_options();
        $data['saldo_normal_options'] = $this->M_Keuangan->accounting_saldo_normal_options();
        $data['tipe_kontrol_options'] = $this->M_Keuangan->accounting_tipe_kontrol_options();
        $data['support_cards'] = $this->accounting_support_cards();
        $data['summary'] = $this->M_Keuangan->accounting_account_summary();
        $data['fiscal_periods'] = $data['schema_ready'] ? $this->accounting_service->fiscal_period_rows('', 18) : [];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/jurnal.php', $data);
        $this->load->view('partial/main/footergdg.php');
        $this->load->view('content/keuangan/ajax/ajax_jurnal.php', $data);
    }

    public function jurnal_pembelian()
    {
        if (!$this->require_jurnal_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - JURNAL PEMBELIAN';
        $data['schema_ready'] = $this->M_Keuangan->accounting_schema_ready();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/jurnal_pembelian.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function jurnal_penjualan()
    {
        if (!$this->require_jurnal_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - JURNAL PENJUALAN';
        $data['schema_ready'] = $this->M_Keuangan->accounting_schema_ready();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/jurnal_penjualan.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function jurnal_pembayaran()
    {
        if (!$this->require_jurnal_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - JURNAL PEMBAYARAN';
        $data['schema_ready'] = $this->M_Keuangan->accounting_schema_ready();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/jurnal_pembayaran.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function jurnal_retur_penjualan()
    {
        if (!$this->require_jurnal_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - JURNAL RETUR PENJUALAN';
        $data['schema_ready'] = $this->M_Keuangan->accounting_schema_ready();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/jurnal_retur_penjualan.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function menu_pembelian()
    {
        if (!$this->require_jurnal_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - MENU PEMBELIAN';
        $data['schema_ready'] = $this->M_Keuangan->accounting_schema_ready();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/menu_pembelian.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function menu_penjualan()
    {
        if (!$this->require_jurnal_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - MENU PENJUALAN';
        $data['schema_ready'] = $this->M_Keuangan->accounting_schema_ready();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/menu_penjualan.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function jurnal_neraca()
    {
        $this->jurnal_financial_report('neraca');
    }

    public function jurnal_laba_rugi()
    {
        $this->jurnal_financial_report('laba_rugi');
    }

    private function jurnal_financial_report($type)
    {
        if (!$this->require_jurnal_access()) {
            return;
        }

        $type = $type === 'laba_rugi' ? 'laba_rugi' : 'neraca';
        $dateTo = trim((string)$this->input->get('date_to', true));
        $dateFrom = trim((string)$this->input->get('date_from', true));
        if (!$this->valid_report_date($dateTo)) {
            $dateTo = date('Y-m-d');
        }
        if (!$this->valid_report_date($dateFrom)) {
            $dateFrom = $type === 'laba_rugi' ? date('Y-m-01', strtotime($dateTo)) : date('Y-01-01', strtotime($dateTo));
        }

        $schemaReady = $this->M_Keuangan->accounting_schema_ready() && $this->M_Keuangan->accounting_journal_schema_ready();
        $rows = $schemaReady ? $this->accounting_service->reports($type, $dateFrom, $dateTo) : [];
        $incomeRows = $schemaReady ? $this->accounting_service->reports('laba_rugi', $dateFrom, $dateTo) : [];
        $prepared = $type === 'laba_rugi'
            ? $this->prepare_income_statement($rows)
            : $this->prepare_balance_sheet($rows, $incomeRows);

        $data['page_title'] = $type === 'laba_rugi' ? 'KARISMA - LABA RUGI' : 'KARISMA - NERACA';
        $data['report_type'] = $type;
        $data['report_title'] = $type === 'laba_rugi' ? 'Laporan Laba Rugi' : 'Laporan Neraca';
        $data['date_from'] = $dateFrom;
        $data['date_to'] = $dateTo;
        $data['schema_ready'] = $schemaReady;
        $data['sections'] = $prepared['sections'];
        $data['totals'] = $prepared['totals'];
        $data['audit_notes'] = $prepared['audit_notes'];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/jurnal_laporan.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    private function valid_report_date($date)
    {
        $date = trim((string)$date);
        if ($date === '') {
            return false;
        }
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    private function prepare_income_statement($rows)
    {
        $sections = $this->group_financial_rows($rows);
        $totalRevenue = 0.0;
        $totalCost = 0.0;
        $totalOperatingExpense = 0.0;
        $totalOtherRevenue = 0.0;
        $totalOtherExpense = 0.0;

        foreach ($sections as $section) {
            $name = strtolower($section['name']);
            $amount = (float)$section['total'];
            if (strpos($name, 'atas pendapatan') !== false || strpos($name, 'hpp') !== false || strpos($name, 'cost of revenue') !== false) {
                $totalCost += $amount;
            } elseif (strpos($name, 'beban lain') !== false || strpos($name, 'non operasional') !== false || strpos($name, 'other expense') !== false) {
                $totalOtherExpense += $amount;
            } elseif (strpos($name, 'pendapatan lain') !== false || strpos($name, 'other revenue') !== false) {
                $totalOtherRevenue += $amount;
            } elseif (strpos($name, 'pendapatan') !== false || strpos($name, 'revenue') !== false) {
                $totalRevenue += $amount;
            } else {
                $totalOperatingExpense += $amount;
            }
        }

        $grossProfit = $totalRevenue - $totalCost;
        $operatingProfit = $grossProfit - $totalOperatingExpense;
        $netIncome = $operatingProfit + $totalOtherRevenue - $totalOtherExpense;

        return [
            'sections' => $sections,
            'totals' => [
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'gross_profit' => $grossProfit,
                'total_operating_expense' => $totalOperatingExpense,
                'operating_profit' => $operatingProfit,
                'total_other_revenue' => $totalOtherRevenue,
                'total_other_expense' => $totalOtherExpense,
                'net_income' => $netIncome,
            ],
            'audit_notes' => [
                'Laporan membaca jurnal berstatus POSTED pada rentang periode yang dipilih.',
                'Akun bersaldo normal KREDIT dihitung kredit dikurangi debit; akun DEBIT dihitung debit dikurangi kredit.',
                'Laba bersih dihitung dari pendapatan dikurangi beban sesuai klasifikasi akun laba rugi.',
            ],
        ];
    }

    private function prepare_balance_sheet($balanceRows, $incomeRows)
    {
        $sections = $this->group_financial_rows($balanceRows);
        $income = $this->prepare_income_statement($incomeRows);
        $netIncome = (float)$income['totals']['net_income'];

        $asset = 0.0;
        $liability = 0.0;
        $equity = 0.0;
        $equityIndex = null;

        foreach ($sections as $index => $section) {
            $name = strtolower($section['name']);
            $code = (string)($section['code'] ?? '');
            $codePrefix = substr($code, 0, 1);
            $amount = (float)$section['total'];
            if ($codePrefix === '1' || strpos($name, 'harta') !== false || strpos($name, 'asset') !== false) {
                $asset += $amount;
            } elseif ($codePrefix === '2' || strpos($name, 'kewajiban') !== false || strpos($name, 'liabilit') !== false || strpos($name, 'hutang') !== false) {
                $liability += $amount;
            } elseif ($codePrefix === '3' || strpos($name, 'modal') !== false || strpos($name, 'equity') !== false || strpos($name, 'ekuitas') !== false) {
                $equity += $amount;
                $equityIndex = $index;
            }
        }

        if ($equityIndex === null) {
            $sections[] = [
                'name' => 'Modal / Ekuitas',
                'code' => '3',
                'alias' => 'Equity',
                'normal_balance' => 'KREDIT',
                'rows' => [],
                'debit' => 0.0,
                'kredit' => 0.0,
                'total' => 0.0,
            ];
            $equityIndex = count($sections) - 1;
        }

        $sections[$equityIndex]['rows'][] = [
            'kode_akun' => '',
            'nama_akun' => 'Laba/Rugi Berjalan',
            'saldo_normal' => 'KREDIT',
            'debit' => 0.0,
            'kredit' => 0.0,
            'amount' => $netIncome,
            'is_summary' => true,
        ];
        $sections[$equityIndex]['total'] = (float)$sections[$equityIndex]['total'] + $netIncome;
        $equity += $netIncome;

        $liabilityEquity = $liability + $equity;
        $difference = $asset - $liabilityEquity;

        return [
            'sections' => $sections,
            'totals' => [
                'asset' => $asset,
                'liability' => $liability,
                'equity' => $equity,
                'liability_equity' => $liabilityEquity,
                'difference' => $difference,
                'net_income' => $netIncome,
            ],
            'audit_notes' => [
                'Neraca membaca seluruh jurnal POSTED sampai tanggal akhir/cut-off.',
                'Laba/Rugi Berjalan ditambahkan ke ekuitas agar persamaan Aset = Kewajiban + Ekuitas dapat diaudit sebelum closing.',
                'Selisih neraca idealnya 0,00; jika tidak, periksa mapping klasifikasi, saldo normal, atau jurnal yang belum POSTED.',
            ],
        ];
    }

    private function group_financial_rows($rows)
    {
        $sections = [];
        foreach ($rows as $row) {
            $name = trim((string)($row->nama_klasifikasi ?? 'Tanpa Klasifikasi'));
            if ($name === '') {
                $name = 'Tanpa Klasifikasi';
            }
            if (!isset($sections[$name])) {
                $sections[$name] = [
                    'name' => $name,
                    'code' => trim((string)($row->kode_klasifikasi ?? '')),
                    'alias' => trim((string)($row->alias_klasifikasi ?? '')),
                    'normal_balance' => strtoupper(trim((string)($row->klasifikasi_saldo_normal ?? $row->saldo_normal ?? ''))),
                    'rows' => [],
                    'debit' => 0.0,
                    'kredit' => 0.0,
                    'total' => 0.0,
                ];
            }

            $debit = (float)($row->debit ?? 0);
            $kredit = (float)($row->kredit ?? 0);
            $amount = $this->financial_row_amount($row);
            $sections[$name]['debit'] += $debit;
            $sections[$name]['kredit'] += $kredit;
            $sections[$name]['total'] += $amount;
            $sections[$name]['rows'][] = [
                'kode_akun' => trim((string)($row->kode_akun ?? '')),
                'nama_akun' => trim((string)($row->nama_akun ?? '')),
                'saldo_normal' => strtoupper(trim((string)($row->saldo_normal ?? ''))),
                'debit' => $debit,
                'kredit' => $kredit,
                'amount' => $amount,
                'is_summary' => false,
            ];
        }

        return array_values($sections);
    }

    private function financial_row_amount($row)
    {
        $debit = (float)($row->debit ?? 0);
        $kredit = (float)($row->kredit ?? 0);
        $saldoNormal = strtoupper(trim((string)($row->saldo_normal ?? 'DEBIT')));
        return $saldoNormal === 'KREDIT' ? ($kredit - $debit) : ($debit - $kredit);
    }

    public function jurnal_list()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        if (!$this->M_Keuangan->accounting_schema_ready()) {
            return $this->accounting_ajax_response(false, 'Schema accounting belum tersedia.', null, [
                'code' => 'SCHEMA_NOT_READY',
                'details' => [],
            ], 409);
        }

        $search = trim((string)$this->input->post('search', true));
        $klasifikasiId = (int)$this->input->post('id_klasifikasi', true);
        $rows = $this->M_Keuangan->accounting_accounts($search, $klasifikasiId);

        return $this->accounting_ajax_response(true, 'Data akun berhasil dimuat.', [
            'rows' => $rows,
            'summary' => $this->M_Keuangan->accounting_account_summary(),
            'klasifikasi_options' => $this->M_Keuangan->accounting_klasifikasi_options(),
            'saldo_normal_options' => $this->M_Keuangan->accounting_saldo_normal_options(),
            'tipe_kontrol_options' => $this->M_Keuangan->accounting_tipe_kontrol_options(),
        ]);
    }

    public function jurnal_detail()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id', true);
        $row = $id > 0 ? $this->M_Keuangan->accounting_account_by_id($id) : null;
        if (!$row) {
            return $this->accounting_ajax_response(false, 'Data akun tidak ditemukan.', null, [
                'code' => 'ACCOUNT_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        return $this->accounting_ajax_response(true, 'Detail akun berhasil dimuat.', ['row' => $row]);
    }

    public function jurnal_account_journal()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        if (!$this->M_Keuangan->accounting_schema_ready()) {
            return $this->accounting_ajax_response(false, 'Schema accounting belum tersedia.', null, [
                'code' => 'SCHEMA_NOT_READY',
                'details' => [],
            ], 409);
        }

        $id = (int)$this->input->post('id_akun', true);
        $row = $id > 0 ? $this->M_Keuangan->accounting_account_by_id($id) : null;
        if (!$row) {
            return $this->accounting_ajax_response(false, 'Data akun tidak ditemukan.', null, [
                'code' => 'ACCOUNT_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        return $this->accounting_ajax_response(true, 'Data jurnal akun berhasil dimuat.', [
            'account' => $row,
            'rows' => $this->M_Keuangan->accounting_account_journal_rows($id),
            'journal_schema_ready' => $this->M_Keuangan->accounting_journal_schema_ready(),
        ]);
    }

    public function jurnal_sales_list()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $this->load->model('M_Journal');
        if (!$this->M_Journal->accounting_journal_schema_ready()) {
            return $this->accounting_ajax_response(false, 'Schema jurnal accounting belum tersedia.', null, [
                'code' => 'SCHEMA_NOT_READY',
                'details' => [],
            ], 409);
        }

        $this->load->model('M_Journal');
        $search = trim((string)$this->input->post('search', true));
        return $this->accounting_ajax_response(true, 'Daftar jurnal penjualan berhasil dimuat.', [
            'rows' => $this->M_Journal->accounting_sales_journal_rows($search, 150),
        ]);
    }

    public function jurnal_sales_report_data()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $this->load->model('M_Journal');
        if (!$this->M_Journal->accounting_journal_schema_ready()) {
            return $this->accounting_ajax_response(false, 'Schema jurnal accounting belum tersedia.', null, [
                'code' => 'SCHEMA_NOT_READY',
                'details' => [],
            ], 409);
        }

        $start_date = trim((string)$this->input->post('start_date', true));
        $end_date = trim((string)$this->input->post('end_date', true));

        // Basic validation if dates are empty, maybe default to current month
        if (empty($start_date)) {
            $start_date = date('Y-m-01');
        }
        if (empty($end_date)) {
            $end_date = date('Y-m-t');
        }

        return $this->accounting_ajax_response(true, 'Data laporan jurnal penjualan berhasil dimuat.', [
            'data' => $this->M_Journal->accounting_sales_journal_report($start_date, $end_date),
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }

    public function jurnal_payment_list()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $this->load->model('M_Journal');
        if (!$this->M_Journal->accounting_journal_schema_ready()) {
            return $this->accounting_ajax_response(false, 'Schema jurnal accounting belum tersedia.', null, [
                'code' => 'SCHEMA_NOT_READY',
                'details' => [],
            ], 409);
        }

        $search = trim((string)$this->input->post('search', true));
        return $this->accounting_ajax_response(true, 'Daftar jurnal pembayaran berhasil dimuat.', [
            'rows' => $this->M_Journal->accounting_payment_journal_rows($search, 150),
        ]);
    }

    public function jurnal_retur_list()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $this->load->model('M_Journal');
        if (!$this->M_Journal->accounting_journal_schema_ready()) {
            return $this->accounting_ajax_response(false, 'Schema jurnal accounting belum tersedia.', null, [
                'code' => 'SCHEMA_NOT_READY',
                'details' => [],
            ], 409);
        }

        $search = trim((string)$this->input->post('search', true));
        return $this->accounting_ajax_response(true, 'Daftar jurnal retur berhasil dimuat.', [
            'rows' => $this->M_Journal->accounting_retur_journal_rows($search, 150),
        ]);
    }

    public function jurnal_purchase_list()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        if (!$this->M_Keuangan->accounting_journal_schema_ready()) {
            return $this->accounting_ajax_response(false, 'Schema jurnal accounting belum tersedia.', null, [
                'code' => 'SCHEMA_NOT_READY',
                'details' => [],
            ], 409);
        }

        $search = trim((string)$this->input->post('search', true));
        return $this->accounting_ajax_response(true, 'Daftar jurnal pembelian berhasil dimuat.', [
            'rows' => $this->M_Keuangan->accounting_purchase_journal_rows($search, 150),
        ]);
    }

    public function jurnal_sales_detail()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $this->load->model('M_Journal');
        $id = (int)$this->input->post('id_jurnal', true);
        $detail = $id > 0 ? $this->M_Journal->accounting_sales_journal_detail($id) : null;
        if (!$detail) {
            return $this->accounting_ajax_response(false, 'Jurnal penjualan tidak ditemukan.', null, [
                'code' => 'JOURNAL_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        return $this->accounting_ajax_response(true, 'Detail jurnal penjualan berhasil dimuat.', $detail);
    }

    public function jurnal_purchase_detail()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id_jurnal', true);
        $detail = $id > 0 ? $this->M_Keuangan->accounting_purchase_journal_detail($id) : null;
        if (!$detail) {
            return $this->accounting_ajax_response(false, 'Jurnal pembelian tidak ditemukan.', null, [
                'code' => 'JOURNAL_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        return $this->accounting_ajax_response(true, 'Detail jurnal pembelian berhasil dimuat.', $detail);
    }

    public function jurnal_period_store()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $result = $this->accounting_service->save_fiscal_period([
            'id_periode' => (int)$this->input->post('id_periode', true),
            'kode_periode' => trim((string)$this->input->post('kode_periode', true)),
            'nama_periode' => trim((string)$this->input->post('nama_periode', true)),
            'tanggal_mulai' => trim((string)$this->input->post('tanggal_mulai', true)),
            'tanggal_selesai' => trim((string)$this->input->post('tanggal_selesai', true)),
            'reason' => trim((string)$this->input->post('reason', true)),
        ], (int)$this->session->userdata('id') ?: null);

        return $this->accounting_ajax_response(
            (bool)$result['success'],
            $result['message'],
            $result['data'],
            $result['errors'],
            $result['success'] ? 201 : 422
        );
    }

    public function jurnal_period_action()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $result = $this->accounting_service->change_fiscal_period_status(
            (int)$this->input->post('id_periode', true),
            trim((string)$this->input->post('action', true)),
            trim((string)$this->input->post('reason', true)),
            (int)$this->session->userdata('id') ?: null
        );

        return $this->accounting_ajax_response(
            (bool)$result['success'],
            $result['message'],
            $result['data'],
            $result['errors'],
            $result['success'] ? 200 : 422
        );
    }

    public function jurnal_store()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $payload = $this->jurnal_payload();
        $error = $this->validate_jurnal_payload($payload);
        if ($error !== '') {
            return $this->accounting_ajax_response(false, $error, null, [
                'code' => 'VALIDATION_ERROR',
                'details' => [$error],
            ], 422);
        }

        $id = $this->M_Keuangan->accounting_account_store($payload, $this->session->userdata('id'));
        if (!$id) {
            return $this->accounting_ajax_response(false, 'Gagal menyimpan akun jurnal.', null, [
                'code' => 'DATABASE_ERROR',
                'details' => [],
            ], 500);
        }

        return $this->accounting_ajax_response(true, 'Akun jurnal berhasil disimpan.', ['id_akun' => $id], [], 201);
    }

    public function jurnal_update()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id_akun', true);
        if ($id <= 0 || !$this->M_Keuangan->accounting_account_by_id($id)) {
            return $this->accounting_ajax_response(false, 'Data akun tidak ditemukan.', null, [
                'code' => 'ACCOUNT_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        $payload = $this->jurnal_payload();
        $error = $this->validate_jurnal_payload($payload, $id);
        if ($error !== '') {
            return $this->accounting_ajax_response(false, $error, null, [
                'code' => 'VALIDATION_ERROR',
                'details' => [$error],
            ], 422);
        }

        $ok = $this->M_Keuangan->accounting_account_update($id, $payload, $this->session->userdata('id'));
        return $this->accounting_ajax_response((bool)$ok, $ok ? 'Akun jurnal berhasil diupdate.' : 'Gagal update akun jurnal.', ['id_akun' => $id], [
            'code' => 'DATABASE_ERROR',
            'details' => [],
        ], $ok ? 200 : 500);
    }

    public function jurnal_deactivate()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id_akun', true);
        if ($id <= 0 || !$this->M_Keuangan->accounting_account_by_id($id)) {
            return $this->accounting_ajax_response(false, 'Data akun tidak ditemukan.', null, [
                'code' => 'ACCOUNT_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        $ok = $this->M_Keuangan->accounting_account_deactivate($id, $this->session->userdata('id'));
        return $this->accounting_ajax_response((bool)$ok, $ok ? 'Akun jurnal berhasil dinonaktifkan.' : 'Gagal menonaktifkan akun.', ['id_akun' => $id], [
            'code' => 'DATABASE_ERROR',
            'details' => [],
        ], $ok ? 200 : 500);
    }

    public function jurnal_delete()
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id_akun', true);
        if ($id <= 0 || !$this->M_Keuangan->accounting_account_by_id($id)) {
            return $this->accounting_ajax_response(false, 'Data akun tidak ditemukan.', null, [
                'code' => 'ACCOUNT_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        if ($this->M_Keuangan->accounting_account_used($id) || $this->M_Keuangan->accounting_account_has_children($id)) {
            return $this->accounting_ajax_response(false, 'Akun sudah dipakai atau memiliki child account. Gunakan nonaktifkan.', null, [
                'code' => 'ACCOUNT_DELETE_RESTRICTED',
                'details' => [],
            ], 409);
        }

        $ok = $this->M_Keuangan->accounting_account_delete($id);
        return $this->accounting_ajax_response((bool)$ok, $ok ? 'Akun jurnal berhasil dihapus.' : 'Gagal menghapus akun.', [], [
            'code' => 'DATABASE_ERROR',
            'details' => [],
        ], $ok ? 200 : 500);
    }

    private function jurnal_master_payload($master)
    {
        if ($master === 'klasifikasi') {
            return [
                'id_klasifikasi' => (int)$this->input->post('id_klasifikasi', true),
                'kode_klasifikasi' => trim((string)$this->input->post('kode_klasifikasi', true)),
                'nama_klasifikasi' => trim((string)$this->input->post('nama_klasifikasi', true)),
                'alias_klasifikasi' => trim((string)$this->input->post('alias_klasifikasi', true)),
                'jenis_laporan' => strtoupper(trim((string)$this->input->post('jenis_laporan', true))),
                'saldo_normal' => strtoupper(trim((string)$this->input->post('saldo_normal', true))),
                'urutan' => (int)$this->input->post('urutan', true),
                'is_active' => (int)$this->input->post('is_active', true) === 0 ? 0 : 1,
            ];
        }

        if ($master === 'saldo-normal') {
            return [
                'kode_saldo' => strtoupper(trim((string)$this->input->post('kode_saldo', true))),
                'nama_saldo' => trim((string)$this->input->post('nama_saldo', true)),
                'keterangan' => trim((string)$this->input->post('keterangan', true)),
                'urutan' => (int)$this->input->post('urutan', true),
                'is_active' => (int)$this->input->post('is_active', true) === 0 ? 0 : 1,
            ];
        }

        if ($master === 'tipe-kontrol') {
            return [
                'kode_tipe_kontrol' => strtoupper(trim((string)$this->input->post('kode_tipe_kontrol', true))),
                'nama_tipe_kontrol' => trim((string)$this->input->post('nama_tipe_kontrol', true)),
                'keterangan' => trim((string)$this->input->post('keterangan', true)),
                'urutan' => (int)$this->input->post('urutan', true),
                'is_active' => (int)$this->input->post('is_active', true) === 0 ? 0 : 1,
            ];
        }

        if ($master === 'parent-subclass') {
            $payload = $this->jurnal_payload();
            $payload['tipe_akun'] = 'HEADER';
            $payload['allow_manual_journal'] = 0;
            return $payload;
        }

        return [];
    }

    private function validate_jurnal_master_payload($master, $payload, $id = 0)
    {
        if (!$this->M_Keuangan->accounting_schema_ready()) {
            return 'Schema accounting belum tersedia.';
        }

        if (in_array($master, ['saldo-normal', 'tipe-kontrol'], true) && !$this->M_Keuangan->accounting_support_schema_ready()) {
            return 'Schema master pendukung belum tersedia. Jalankan SQL migration master pendukung jurnal.';
        }

        if ($master === 'klasifikasi') {
            if ((int)$id > 0 && (int)$payload['id_klasifikasi'] !== (int)$id) {
                return 'ID klasifikasi tidak dapat diubah. Buat data baru jika membutuhkan ID lain.';
            }

            if ((int)$payload['id_klasifikasi'] <= 0 || $payload['kode_klasifikasi'] === '' || $payload['nama_klasifikasi'] === '') {
                return 'ID, kode, dan nama klasifikasi wajib diisi.';
            }

            if (!in_array($payload['jenis_laporan'], ['NERACA', 'LABA_RUGI'], true)) {
                return 'Jenis laporan tidak valid.';
            }

            if (!$this->M_Keuangan->accounting_saldo_normal_by_code($payload['saldo_normal'])) {
                return 'Saldo normal klasifikasi tidak valid.';
            }

            if ($this->M_Keuangan->accounting_klasifikasi_duplicate($payload['id_klasifikasi'], $payload['kode_klasifikasi'], $id)) {
                return 'ID atau kode klasifikasi sudah digunakan.';
            }

            return '';
        }

        if ($master === 'saldo-normal') {
            if ((string)$id !== '' && $payload['kode_saldo'] !== (string)$id) {
                return 'Kode saldo normal tidak dapat diubah. Buat data baru jika membutuhkan kode lain.';
            }

            if ($payload['kode_saldo'] === '' || $payload['nama_saldo'] === '') {
                return 'Kode dan nama saldo normal wajib diisi.';
            }

            if ($this->M_Keuangan->accounting_saldo_normal_duplicate($payload['kode_saldo'], $id)) {
                return 'Kode saldo normal sudah digunakan.';
            }

            return '';
        }

        if ($master === 'tipe-kontrol') {
            if ((string)$id !== '' && $payload['kode_tipe_kontrol'] !== (string)$id) {
                return 'Kode tipe kontrol tidak dapat diubah. Buat data baru jika membutuhkan kode lain.';
            }

            if ($payload['kode_tipe_kontrol'] === '' || $payload['nama_tipe_kontrol'] === '') {
                return 'Kode dan nama tipe kontrol wajib diisi.';
            }

            if ($this->M_Keuangan->accounting_tipe_kontrol_duplicate($payload['kode_tipe_kontrol'], $id)) {
                return 'Kode tipe kontrol sudah digunakan.';
            }

            return '';
        }

        if ($master === 'parent-subclass') {
            return $this->validate_jurnal_payload($payload, $id);
        }

        return 'Master tidak valid.';
    }

    public function jurnal_master_list($master)
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $rows = $this->M_Keuangan->accounting_master_rows($master);
        return $this->accounting_ajax_response(true, 'Data master berhasil dimuat.', ['rows' => $rows]);
    }

    public function jurnal_master_detail($master)
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $id = $this->input->post('id', true);
        $row = $this->M_Keuangan->accounting_master_row($master, $id);
        if (!$row) {
            return $this->accounting_ajax_response(false, 'Data master tidak ditemukan.', null, [
                'code' => 'MASTER_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        return $this->accounting_ajax_response(true, 'Detail master berhasil dimuat.', ['row' => $row]);
    }

    public function jurnal_master_store($master)
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $payload = $this->jurnal_master_payload($master);
        $error = $this->validate_jurnal_master_payload($master, $payload);
        if ($error !== '') {
            return $this->accounting_ajax_response(false, $error, null, [
                'code' => 'VALIDATION_ERROR',
                'details' => [$error],
            ], 422);
        }

        $id = $this->M_Keuangan->accounting_master_store($master, $payload, $this->session->userdata('id'));
        return $this->accounting_ajax_response((bool)$id, $id ? 'Data master berhasil disimpan.' : 'Gagal menyimpan master.', ['id' => $id], [
            'code' => 'DATABASE_ERROR',
            'details' => [],
        ], $id ? 201 : 500);
    }

    public function jurnal_master_update($master)
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $id = $this->input->post('id', true);
        if (!$this->M_Keuangan->accounting_master_row($master, $id)) {
            return $this->accounting_ajax_response(false, 'Data master tidak ditemukan.', null, [
                'code' => 'MASTER_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        $payload = $this->jurnal_master_payload($master);
        $error = $this->validate_jurnal_master_payload($master, $payload, $id);
        if ($error !== '') {
            return $this->accounting_ajax_response(false, $error, null, [
                'code' => 'VALIDATION_ERROR',
                'details' => [$error],
            ], 422);
        }

        $ok = $this->M_Keuangan->accounting_master_update($master, $id, $payload, $this->session->userdata('id'));
        return $this->accounting_ajax_response((bool)$ok, $ok ? 'Data master berhasil diupdate.' : 'Gagal update master.', ['id' => $id], [
            'code' => 'DATABASE_ERROR',
            'details' => [],
        ], $ok ? 200 : 500);
    }

    public function jurnal_master_delete($master)
    {
        if (!$this->require_jurnal_access(true)) {
            return;
        }

        $id = $this->input->post('id', true);
        if (!$this->M_Keuangan->accounting_master_row($master, $id)) {
            return $this->accounting_ajax_response(false, 'Data master tidak ditemukan.', null, [
                'code' => 'MASTER_NOT_FOUND',
                'details' => [],
            ], 404);
        }

        if ($this->M_Keuangan->accounting_master_used($master, $id)) {
            return $this->accounting_ajax_response(false, 'Data master sudah digunakan. Nonaktifkan data, jangan dihapus.', null, [
                'code' => 'MASTER_DELETE_RESTRICTED',
                'details' => [],
            ], 409);
        }

        $ok = $this->M_Keuangan->accounting_master_delete($master, $id);
        return $this->accounting_ajax_response((bool)$ok, $ok ? 'Data master berhasil dihapus.' : 'Gagal hapus master.', [], [
            'code' => 'DATABASE_ERROR',
            'details' => [],
        ], $ok ? 200 : 500);
    }

    public function pricelist_online()
    {
        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['pricelist']      = $this->M_Keuangan->get_pricelist();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/body_pl.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
