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
        $this->load->view("partials/main/header/header_pricelist");
        $this->load->view("partials/main/navigation");
        $this->load->view("content/katalog/dashboard");
        $this->load->view("partials/main/footer");
    }

    function getBarang()
    {
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
            if (!file_exists($imagePath))
                $imagePath = "images/Karisma.png";
            $row[] = '<img src="' . $imagePath . '" style="width:100px; height:100px">';
            $row[] =
                '<a href="' . base_url('') . 'pricelist?id=' . $field->kode_barang . '" class="btn btn-primary btn-sm ">
            <i class="fa fa-solid fa-eye"></i></a>' . ' ' . '';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Katalog->count_all(),
            "recordsFiltered" => $this->M_Katalog->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }

    public function add()
    {
        // $barang_id  = $this->input->post("id_barang_isi");
        $kd_barang  = $this->input->post("kode_barang_isi");
        $nama_bar   = $this->input->post("nama_barang_isi");
        $nama_sup   = $this->input->post("nama_suplier_isi");
        $kel        = $this->input->post("kelompok_isi");
        $bhn        = $this->input->post("bahan_isi");


        if (!empty($_FILES['gambar_1'])) {
            $config['upload_path'] = './images/';
            $config['allowed_types'] = 'jpg|png|gif';
            $config['max_size'] = '10000';
            $config['max_width'] = '6000';
            $config['max_height'] = '6000';
            $config['overwrite'] = TRUE;
            $config['file_name'] = date('U') . '_' . $_FILES['gambar_1']['name'];
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
                    $data["gbr_promo1"] = $full_path1;
                }
            }
        }

        if (!empty($_FILES['gambar_2'])) {
            $config['upload_path'] = './images/';
            $config['allowed_types'] = 'jpg|png|gif';
            $config['max_size'] = '10000';
            $config['max_width'] = '6000';
            $config['max_height'] = '6000';
            $config['overwrite'] = TRUE;
            $config['file_name'] = date('U') . '_' . $_FILES['gambar_2']['name'];
            $this->load->library('upload', $config);
            $this->upload->initialize($config);;

            if (!$this->upload->do_upload('gambar_2')) {
                $error = array('error' => $this->upload->display_errors());
                print_r($error);
                die;
            } else {
                if ($this->upload->do_upload('gambar_2')) {
                    $image_data2 = $this->upload->data();
                    $full_path2 = $config['file_name'];
                    $data["gbr_promo2"] = $full_path2;
                }
            }
        }


        $data = array(
            'kode_barang'   => $kd_barang,
            'nama_barang'   => $nama_bar,
            'nama_suplier'  => $nama_sup,
            'kelompok'      => $kel,
            'bhn_aktif'     => $bhn,
            'gbr_promo1'    => $image_data1['file_name'],
            'gbr_promo2'    => $image_data2['file_name']
        );

        $this->M_Katalog->adddata($data);
        redirect('admin/katalog');
    }

    public function editKat()
    {
        $id_barang  = $this->input->post("id_bar");
        $kd_barang  = $this->input->post("kd_bar");
        $nama_bar   = $this->input->post("nama_bar");
        $nama_sup   = $this->input->post("nama_sup");
        $kel        = $this->input->post("kelo");
        $bhn        = $this->input->post("bhn");


        if (!empty($_FILES['gambar_1']['name'])) {
            $config['upload_path'] = './images/';
            $config['allowed_types'] = 'jpg|png|gif';
            $config['max_size'] = '10000';
            $config['max_width'] = '6000';
            $config['max_height'] = '6000';
            $config['overwrite'] = TRUE;
            $config['file_name'] = date('U') . '_' . $_FILES['gambar_1']['name'];
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
                    $data["gbr_promo1"] = $full_path1;
                }
            }
        }

        if (!empty($_FILES['gambar_2'])) {
            $config['upload_path'] = './images/';
            $config['allowed_types'] = 'jpg|png|gif';
            $config['max_size'] = '10000';
            $config['max_width'] = '6000';
            $config['max_height'] = '6000';
            $config['overwrite'] = TRUE;
            $config['file_name'] = date('U') . '_' . $_FILES['gambar_2']['name'];
            $this->load->library('upload', $config);
            $this->upload->initialize($config);;

            if (!$this->upload->do_upload('gambar_2')) {
                $error = array('error' => $this->upload->display_errors());
                print_r($error);
                die;
            } else {
                if ($this->upload->do_upload('gambar_2')) {
                    $image_data2 = $this->upload->data();
                    $full_path2 = $config['file_name'];
                    $data["gbr_promo2"] = $full_path2;
                }
            }

            $data = array(
                'id_barang'     => $id_barang,
                'kode_barang'   => $kd_barang,
                'nama_barang'   => $nama_bar,
                'nama_suplier'  => $nama_sup,
                'kelompok'      => $kel,
                'bhn_aktif'     => $bhn,
                'gbr_promo1'    => $image_data1['file_name'],
                'gbr_promo2'    => $image_data2['file_name']
            );

            $this->M_Katalog->editdataKat($data, $id_barang);
            redirect('admin/katalog');
        }
    }

    public function deleteDat($hapus)
    {
        $id_barang = $hapus;


        $this->M_Katalog->barangdel($id_barang);

        redirect("admin/katalog");
    }
}
