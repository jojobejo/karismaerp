<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Data_customers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Cek session login
        if (!$this->session->userdata('id')) {
            redirect('auth');
        }
    }

    public function index()
    {
        $data['page_title'] = 'KARISMA ERP - Data Customers';

        // Ambil semua customer, urutkan berdasarkan nama_kios atau nama_customer
        $data['customers'] = $this->db->query("
            SELECT
                id,
                COALESCE(kd_customer, '') AS kd_customer,
                COALESCE(nama_customer, '') AS nama_customer,
                COALESCE(nama_kios, '') AS nama_kios,
                COALESCE(nama_sales, '') AS nama_sales,
                COALESCE(kd_rute, '') AS kd_rute,
                COALESCE(telp1, '') AS telp1,
                COALESCE(telp2, '') AS telp2,
                COALESCE(regional, '') AS regional,
                COALESCE(jam_buka_tutup, '') AS jam_buka_tutup,
                COALESCE(karakteristik_kios, '') AS karakteristik_kios,
                COALESCE(alamat_kios, '') AS alamat_kios,
                COALESCE(plafon_aktif, 0) AS plafon_aktif
            FROM tb_customer
            ORDER BY
                CASE WHEN COALESCE(nama_kios, '') = '' THEN 1 ELSE 0 END,
                COALESCE(nama_kios, nama_customer, '') ASC
        ")->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/data_customers/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function get_detail()
    {
        $id = (int)$this->input->get('id');
        if (!$id) {
            echo json_encode(null);
            return;
        }

        $row = $this->db->query("
            SELECT
                id,
                COALESCE(kd_customer, '') AS kd_customer,
                COALESCE(nama_customer, '') AS nama_customer,
                COALESCE(nama_kios, '') AS nama_kios,
                COALESCE(nama_sales, '') AS nama_sales,
                COALESCE(kd_rute, '') AS kd_rute,
                COALESCE(telp1, '') AS telp1,
                COALESCE(telp2, '') AS telp2,
                COALESCE(regional, '') AS regional,
                COALESCE(jam_buka_tutup, '') AS jam_buka_tutup,
                COALESCE(karakteristik_kios, '') AS karakteristik_kios,
                COALESCE(alamat_kios, '') AS alamat_kios,
                COALESCE(plafon_aktif, 0) AS plafon_aktif
            FROM tb_customer
            WHERE id = ?
            LIMIT 1
        ", [$id])->row();

        echo json_encode($row);
    }

    public function store()
    {
        $id = (int)$this->input->post('id');

        // Bersihkan format angka ribuan sebelum simpan
        $plafonRaw = str_replace(['.', ','], ['', '.'], (string)$this->input->post('plafon_aktif'));
        $plafon    = is_numeric($plafonRaw) ? (float)$plafonRaw : 0;

        $data = [
            'kd_customer'        => $this->input->post('kd_customer'),
            'nama_kios'          => $this->input->post('nama_kios'),
            'nama_sales'         => $this->input->post('nama_sales'),
            'nama_customer'      => $this->input->post('nama_customer'),
            'kd_rute'            => $this->input->post('kd_rute'),
            'telp1'              => $this->input->post('telp1'),
            'telp2'              => $this->input->post('telp2'),
            'plafon_aktif'       => $plafon,
            'alamat_kios'        => $this->input->post('alamat_kios'),
            'regional'           => $this->input->post('regional'),
            'jam_buka_tutup'     => $this->input->post('jam_buka_tutup'),
            'karakteristik_kios' => $this->input->post('karakteristik_kios'),
        ];

        if ($id > 0) {
            $ok = $this->db->where('id', $id)->update('tb_customer', $data);
        } else {
            $ok = $this->db->insert('tb_customer', $data);
        }

        echo json_encode(['status' => $ok ? 'success' : 'error']);
    }

    public function delete()
    {
        $id = (int)$this->input->post('id');
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
            return;
        }
        $ok = $this->db->where('id', $id)->delete('tb_customer');
        echo json_encode(['status' => $ok ? 'success' : 'error']);
    }
}
