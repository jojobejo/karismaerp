<?php
defined('BASEPATH') or exit('No direct script access allowed');


class C_Inventaris extends CI_Controller
{
 
    function __construct() 
    { 
        parent::__construct();
        $this->require_authenticated_session();
        $this->load->model('M_Inventaris');
    }

    /**
     * Memastikan setiap route modul Inventaris hanya diakses oleh session lokal yang terotorisasi.
     */
    private function require_authenticated_session()
    {
        $isLoggedIn = $this->session->userdata('status') === 'is_login'
            && (int) $this->session->userdata('id') > 0
            && trim((string) $this->session->userdata('username')) !== ''
            && in_array((int) $this->session->userdata('akses_lv'), [1, 2, 3, 4, 5], true);

        if ($isLoggedIn) {
            return;
        }

        $this->session->set_flashdata('gagal', 'Silakan login terlebih dahulu.');
        redirect('Auth');
        exit;
    }

    public function index()
    {
        $data['page_title'] = 'KIU MAINTENANCE'; 
        $data['datainventaris']   = $this->M_Inventaris->getAllinventaris();

        $this->load->view('partial/main/header.php', $data); 
        $this->load->view('content/inventaris/bodyadmin.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/inventaris/ajaxinventaris.php');
    }

    public function addInventaris()
    {
        $kd_invetaris  = $this->input->post('kd_invetaris');
        $kd_pic   = $this->input->post('kd_pic');
        $dept     = $this->input->post('dept');
        $type_brg = $this->input->post('type_brg');
        $Desc     = $this->input->post('Desc');
        $tgl_beli = $this->input->post('tgl_beli');
        $stat     = $this->input->post('stat');
        $hist     = $this->input->post('hist');
        
        $data = array(
            // <kiri buat database > kanan nama di modalnya
            'kdinventaris' => $kd_invetaris,
            'kdpic'=>$kd_pic,
            'departement'=>$dept,
            'typebarang'=>$type_brg,
            'deskripsi'=>$Desc,
            'tglbeli'=>$tgl_beli,
            'status'=>$stat,
            'history'=>$hist,
        );
        $this->M_Inventaris->addInventaris($data);        
        redirect('inventaris');
    }
    
    public function editinventaris($data)
    { 
        $kd_invetaris  = $this->input->post('kd_invetaris');
        $id_invetaris  = $this->input->post('id_invetaris');
        $kd_pic   = $this->input->post('kd_pic');
        $dept     = $this->input->post('dept');
        $type_brg = $this->input->post('type_brg');
        $Desc     = $this->input->post('Desc');
        $tgl_beli = $this->input->post('tgl_beli');
        $stat     = $this->input->post('stat');
        $hist     = $this->input->post('hist');
        
        $data = array(
            // <kiri buat database > kanan nama di modalnya
            'kdinventaris' => $kd_invetaris,
            'kdpic'=>$kd_pic,
            'departement'=>$dept,
            'typebarang'=>$type_brg,
            'deskripsi'=>$Desc,
            'tglbeli'=>$tgl_beli,
            'status'=>$stat,
            'history'=>$hist,
        );
        $this->M_Inventaris->editinventaris($id_invetaris,$data);
        redirect('inventaris');
    }
    public function hapusinventaris()
    {
        $id     = $this->input->post('id_invetaris');

        $this->M_Inventaris->hapusinventaris($id);
        redirect('inventaris');
    
    }
    public function changeOwner()
    {
    }
    public function addKatagoriInventaris()
    {
    }
    public function editKatagoriInventaris()
    {
    }
    public function deleteKatagoriInventaris()
    {
    }
    public function viewUserInventaris()
    {
    }
}
