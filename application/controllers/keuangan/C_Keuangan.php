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
            fgetcsv($file_data); // Skip header row

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
                        'qty'         => $row[5],
                        'satuan'      => $row[6],
                        'no_lot'      => $row[7],
                        'tgl_exp'     => $row[8],
                        'nominal_p'   => $row[9],
                        'jtempo'      => $row[10],
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
}
