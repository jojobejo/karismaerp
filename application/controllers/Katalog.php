<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Katalog extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Katalog");
        $this->load->library('form_validation');
    }

    function index()
    {
        if ($this->session->userdata('status') != "login") {
            redirect('login');
        }

        $data['barang'] = $this->M_Katalog->getAll();
        $data['statistik'] = $this->M_Katalog->getStatistik();

        $this->load->view("partial/katalog/header");
        $this->load->view("content/katalog/dashboard", $data);
        $this->load->view("partial/katalog/footer");
    }

    function getBarang()
    {
        $hak_akses = $this->session->userdata('hak_akses');
        $list = $this->M_Katalog->get_datatables();
        $data = array();

        foreach ($list as $field) {
            $row = array();
            $row[] = $field->kode_barang;
            $row[] = $field->produk_fokus;
            $row[] = $field->nama_barang;
            $row[] = $field->nama_suplier;
            $row[] = $field->kelompok;
            $row[] = $field->bahan_aktif;

            $imagePath = "images/produk/" . $field->gbr_produk;
            if (!file_exists($imagePath)) $imagePath = "images/Karisma.png";
            $row[] = '<img src="' . $imagePath . '" style="width:80px; height:80px; object-fit:cover; border-radius:12px;">';

            // Badge 3 platform
            $shopee_badge    = $field->shopee    
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> Shopee</span>'    
                : '<span class="badge badge-secondary mb-1">Shopee -</span>';
            $tokopedia_badge = $field->tokopedia 
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> Tokopedia</span>' 
                : '<span class="badge badge-secondary mb-1">Tokopedia -</span>';
            $kiushop_badge   = $field->kiushop   
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> KiuShop</span>'   
                : '<span class="badge badge-secondary mb-1">KiuShop -</span>';
            $row[] = $shopee_badge . '<br>' . $tokopedia_badge . '<br>' . $kiushop_badge;

            // Aksi
            $aksi = '<div class="desktop-action-group"><a href="' . base_url('pricelist?id=' . $field->kode_barang) . '" 
                        class="btn btn-primary btn-sm" target="_blank" title="Lihat Pricelist">
                        <i class="fa fa-eye"></i>
                    </a>';

            if ($hak_akses == '1') {
                $aksi .= '
                    <button class="btn btn-warning btn-sm btn-edit" title="Edit Barang"
                        data-id="'       . $field->id_barang   . '"
                        data-kode="'     . $field->kode_barang  . '"
                        data-nama="'     . $field->nama_barang  . '"
                        data-fokus="'    . $field->produk_fokus . '"
                        data-suplier="'  . $field->nama_suplier . '"
                        data-katagori="' . $field->bahan_aktif  . '">
                        <i class="fa fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-danger btn-sm btn-hapus" title="Hapus Barang"
                        data-id="'   . $field->id_barang  . '"
                        data-nama="' . $field->nama_barang . '">
                        <i class="fa fa-trash"></i>
                    </button>';
            }

            if ($hak_akses == '1' || $hak_akses == '4') {
                $aksi .= '
                    <button class="btn btn-info btn-sm btn-online" title="Update Online Shop"
                        data-id="'        . $field->id_barang   . '"
                        data-kode="'      . $field->kode_barang  . '"
                        data-nama="'      . $field->nama_barang  . '"
                        data-shopee="'    . $field->shopee       . '"
                        data-tokopedia="' . $field->tokopedia    . '"
                        data-kiushop="'   . $field->kiushop      . '">
                        <i class="fa fa-store"></i>
                    </button>';
            }

            $aksi .= '</div>';
            $row[] = $aksi;
            $data[] = $row;
        }

        $output = array(
            "draw"            => $_POST['draw'],
            "recordsTotal"    => $this->M_Katalog->count_all(),
            "recordsFiltered" => $this->M_Katalog->count_filtered(),
            "data"            => $data,
        );
        echo json_encode($output);
    }

    public function add()
    {
        $kd_barang  = $this->input->post("kode_barang_isi");
        $nama_bar   = $this->input->post("nama_barang_isi");
        $prdk_fks   = $this->input->post("produk_fokus_isi");
        $nama_sup   = $this->input->post("nama_suplier_isi");
        $ktg_prodk  = $this->input->post("katagori_isi");
        $bhn_aktif_isi  = $this->input->post("bhn_aktif_isi");

        $data = array(
            'kode_barang'   => $kd_barang,
            'nama_barang'   => $nama_bar,
            'nama_suplier'  => $nama_sup,
            'produk_fokus'  => $prdk_fks,
            'kelompok'      => $ktg_prodk,
            'bahan_aktif'    => $bhn_aktif_isi,
            'gbr_produk'    => '-',
            'gbr_promo1'    => '-',
            'gbr_promo2'    => '-',
            'gbr_promo3'    => '-'
        );

        $this->M_Katalog->adddata($data);
        redirect('dashboard');
    }

    public function editKat()
    {
        $id_barang  = $this->input->post("id_bar");
        $kd_barang  = $this->input->post("kode_barang_isi");
        $nama_bar   = $this->input->post("nama_barang_isi");
        $prdk_fks   = $this->input->post("produk_fokus_isi");
        $nama_sup   = $this->input->post("nama_suplier_isi");
        $ktg_prodk  = $this->input->post("katagori_isi");

        $data = array(
            'id_barang'     => $id_barang,
            'kode_barang'   => $kd_barang,
            'nama_barang'   => $nama_bar,
            'nama_suplier'  => $nama_sup,
            'produk_fokus'  => $prdk_fks,
            'bahan_aktif'    => $ktg_prodk
        );

        $this->M_Katalog->editdataKat($data, $id_barang);
        redirect('dashboard');
    }

    public function deleteDat($hapus)
    {
        $id_barang = $hapus;


        $this->M_Katalog->barangdel($id_barang);

        redirect("dashboard");
    }

    public function uploadProduk()
    {
        $id_barang  = $this->input->post("id_bar");
        $kd_barang  = $this->input->post("kode_barang_isi");
        $nama_bar   = $this->input->post("nama_barang_isi");
        $prdk_fks   = $this->input->post("produk_fokus_isi");
        $nama_sup   = $this->input->post("nama_suplier_isi");
        $ktg_prodk  = $this->input->post("katagori_isi");

        if (!empty($_FILES['gambar_1'])) {
            $config['upload_path'] = './images/produk/';
            $config['allowed_types'] = 'jpg|png|gif';
            $config['max_size'] = '10000';
            $config['max_width'] = '6000';
            $config['max_height'] = '6000';
            $config['overwrite'] = TRUE;
            $config['file_name'] = date('Y') . date('m') . date('U') .   '_' . $_FILES['gambar_1']['name'];
            $this->load->library('upload', $config);
            $this->upload->initialize($config);;

            if (!$this->upload->do_upload('gambar_1')) {
                $error = array('error' => $this->upload->display_errors());
                print_r($error);
                die;
            } else {
                if ($this->upload->do_upload('gambar_1')) {
                    $image_data1 = $this->upload->data();
                    $full_path1 = $config['file_name'];
                    $data["gbr_produk"] = $full_path1;
                }
            }
        }

        $data = array(
            'id_barang'     => $id_barang,
            'kode_barang'   => $kd_barang,
            'nama_barang'   => $nama_bar,
            'nama_suplier'  => $nama_sup,
            'produk_fokus'  => $prdk_fks,
            'bahan_aktif'    => $ktg_prodk,
            'gbr_produk'    => $image_data1['file_name']
        );

        $this->M_Katalog->editdataKat($data, $id_barang);
        redirect('dashboard');
    }

    public function updateOnlineShop()
    {
        $id_barang   = $this->input->post('id_barang');
        $kode_barang = $this->input->post('kode_barang');
        $shopee      = $this->input->post('shopee')    ? 1 : 0;
        $tokopedia   = $this->input->post('tokopedia') ? 1 : 0;
        $kiushop     = $this->input->post('kiushop')   ? 1 : 0;

        $data = array(
            'shopee'    => $shopee,
            'tokopedia' => $tokopedia,
            'kiushop'   => $kiushop
        );

        $this->M_Katalog->updateOnlineShop($data, $id_barang);

        $hak_akses = $this->session->userdata('hak_akses');
        if ($hak_akses == '4') {
            redirect('salesonline');
        } else {
            redirect('dashboard');
        }
    }
}
