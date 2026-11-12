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

        // Hitung total customer secara cepat tanpa memuat seluruh baris
        $data['total_customers'] = (int)$this->db->count_all('tb_customer');

        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');
        $collectionSelect = $hasCollectionCol ? "COALESCE(nama_collection, '') AS nama_collection," : "'' AS nama_collection,";

        // Initial load dibatasi 50 data pertama agar halaman terbuka instan dan ringan
        $data['customers'] = $this->db->query("
            SELECT
                id,
                COALESCE(kd_customer, '') AS kd_customer,
                COALESCE(nama_customer, '') AS nama_customer,
                COALESCE(nama_kios, '') AS nama_kios,
                COALESCE(nama_sales, '') AS nama_sales,
                {$collectionSelect}
                COALESCE(kd_rute, '') AS kd_rute,
                COALESCE(telp1, '') AS telp1
            FROM tb_customer
            ORDER BY
                CASE WHEN COALESCE(nama_kios, '') = '' THEN 1 ELSE 0 END,
                COALESCE(nama_kios, nama_customer, '') ASC
            LIMIT 50
        ")->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/data_customers/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Endpoint AJAX untuk search dan pagination (infinite scroll) data customer
     */
    public function get_list()
    {
        $search = trim((string)$this->input->get('q'));
        $offset = max(0, (int)$this->input->get('offset'));
        $limit  = min(100, max(10, (int)($this->input->get('limit') ?: 50)));

        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');
        $collectionSelect = $hasCollectionCol ? "COALESCE(nama_collection, '') AS nama_collection," : "'' AS nama_collection,";

        $this->db->select("
            id,
            COALESCE(kd_customer, '') AS kd_customer,
            COALESCE(nama_customer, '') AS nama_customer,
            COALESCE(nama_kios, '') AS nama_kios,
            COALESCE(nama_sales, '') AS nama_sales,
            {$collectionSelect}
            COALESCE(kd_rute, '') AS kd_rute,
            COALESCE(telp1, '') AS telp1
        ");
        $this->db->from('tb_customer');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('nama_kios', $search);
            $this->db->or_like('nama_customer', $search);
            $this->db->or_like('kd_customer', $search);
            $this->db->or_like('kd_rute', $search);
            $this->db->or_like('nama_sales', $search);
            if ($hasCollectionCol) {
                $this->db->or_like('nama_collection', $search);
            }
            $this->db->or_like('telp1', $search);
            $this->db->group_end();
        }

        $this->db->order_by("CASE WHEN COALESCE(nama_kios, '') = '' THEN 1 ELSE 0 END", "ASC", false);
        $this->db->order_by("COALESCE(nama_kios, nama_customer, '')", "ASC", false);
        $this->db->limit($limit, $offset);

        $data = $this->db->get()->result();

        echo json_encode([
            'status'   => 'success',
            'data'     => $data,
            'count'    => count($data),
            'offset'   => $offset,
            'limit'    => $limit,
            'has_more' => count($data) >= $limit
        ]);
    }

    public function get_detail()
    {
        $id = (int)$this->input->get('id');
        if (!$id) {
            echo json_encode(null);
            return;
        }

        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');
        $collectionSelect = $hasCollectionCol ? "COALESCE(nama_collection, '') AS nama_collection," : "'' AS nama_collection,";

        $row = $this->db->query("
            SELECT
                id,
                COALESCE(kd_customer, '') AS kd_customer,
                COALESCE(nama_customer, '') AS nama_customer,
                COALESCE(nama_kios, '') AS nama_kios,
                COALESCE(nama_sales, '') AS nama_sales,
                {$collectionSelect}
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

        if ($this->db->field_exists('nama_collection', 'tb_customer')) {
            $data['nama_collection'] = $this->input->post('nama_collection');
        }

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
