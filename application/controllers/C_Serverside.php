<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Serverside extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("M_Server"); //load model data mahasiswa
    }

    //method pertama yang akan di eksekusi
    public function index()
    {
        $data["title"] = "List Data Mahasiswa";
        
        $this->load->view("partial/katalog/header");
        $this->load->view("content/katalog/dashboard", $data);
        $this->load->view("partial/katalog/footer");
    }

    //method yang digunakan untuk request data mahasiswa
    public function ajax_list()
    {
        header('Content-Type: application/json');
        $list = $this->M_Server->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        //looping data mahasiswa
        foreach ($list as $Data_mahasiswa) {
            $no++;
            $row = array();
            //row pertama akan kita gunakan untuk btn edit dan delete
            $row[] =  '<a class="btn btn-success btn-sm"><i class="fa fa-edit"></i> </a>
            <a class="btn btn-danger btn-sm "><i class="fa fa-trash"></i> </a>';
            $row[] = $Data_mahasiswa->Nama;
            $row[] = $Data_mahasiswa->Alamat;
            $row[] = $Data_mahasiswa->Email;
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->Data_mahasiswa_model->count_all(),
            "recordsFiltered" => $this->Data_mahasiswa_model->count_filtered(),
            "data" => $data,
        );
        //output to json format
        $this->output->set_output(json_encode($output));
    }

}