<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Kasbon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('username')) {
            redirect('Auth');
        }
        $this->load->model('M_Kasbon');
        $this->load->library('session');
    }

    private function _getUser()
    {
        return [
            'id'       => $this->session->userdata('id'),
            'nama'     => $this->session->userdata('nama'),
            'username' => $this->session->userdata('username'),
            'jobdesk'  => strtoupper((string)$this->session->userdata('jobdesk')),
            'lv'       => $this->session->userdata('lv'),
        ];
    }

    public function index()
    {
        $user = $this->_getUser();
        $data['page_title'] = 'Daftar Kas Bon';
        $data['user'] = $user;
        
        if (in_array($user['jobdesk'], ['ADMIN', 'HRD', 'KADEPSC', 'MANAGERSC', 'MANAGERTC'])) {
            $data['kasbon'] = $this->M_Kasbon->get_all_kasbon();
        } else {
            $data['kasbon'] = $this->M_Kasbon->get_kasbon_by_user($user['id']);
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kasbon/list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function create()
    {
        $user = $this->_getUser();
        $data['page_title'] = 'Form Pengajuan Kas Bon';
        $data['user'] = $user;
        
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kasbon/form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function store()
    {
        $user = $this->_getUser();
        
        $nominal = $this->input->post('nominal');
        $nominal = str_replace(['.', ','], '', $nominal);
        
        $keterangan = $this->input->post('keterangan');

        if (empty($nominal) || empty($keterangan)) {
            $this->session->set_flashdata('error', 'Nominal dan keterangan harus diisi.');
            redirect('C_Kasbon/create');
            return;
        }

        $no_kasbon = $this->M_Kasbon->generate_no_kasbon();

        $lampiran_file = null;
        if (!empty($_FILES['lampiran']['name'])) {
            $config['upload_path']   = './assets/uploads/kasbon/';
            $config['allowed_types'] = 'jpg|jpeg|png|pdf';
            $config['max_size']      = 2048;
            $config['file_name']     = 'KB_' . time() . '_' . rand(1000, 9999);

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('lampiran')) {
                $uploadData = $this->upload->data();
                $lampiran_file = $uploadData['file_name'];
            } else {
                $this->session->set_flashdata('error', 'Gagal upload lampiran: ' . $this->upload->display_errors('',''));
                redirect('C_Kasbon/create');
                return;
            }
        }

        $data_insert = [
            'no_kasbon'         => $no_kasbon,
            'id_user'           => $user['id'],
            'nama_pemohon'      => $user['nama'],
            'nominal'           => $nominal,
            'keterangan'        => $keterangan,
            'lampiran'          => $lampiran_file,
            'tanggal_pengajuan' => date('Y-m-d'),
            'status'            => 'pending',
            'created_at'        => date('Y-m-d H:i:s')
        ];

        $this->M_Kasbon->insert_kasbon($data_insert);
        
        $this->session->set_flashdata('success', 'Pengajuan Kas Bon berhasil dibuat dengan nomor: ' . $no_kasbon);
        redirect('C_Kasbon');
    }
}
