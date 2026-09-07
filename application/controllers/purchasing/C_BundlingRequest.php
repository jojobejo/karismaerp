<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_BundlingRequest
 * Modul Purchasing untuk mengelola Request Pembuatan Paket Bundling
 * Fitur:
 * - Menentukan komposisi isi 1 paket bundling
 * - Menentukan jumlah paket yang diminta
 * - Sistem otomatis menghitung total kebutuhan komponen
 * - Monitoring status realisasi dari Logistik
 * - Master Formula Paket Bundling
 */
class C_BundlingRequest extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('is_login')) {
            redirect('auth');
        }
        $this->load->model(['M_Bundling', 'M_Ics']);
        $this->load->helper(['url', 'form']);
        $this->M_Bundling->ensure_bundling_schema();
    }

    /**
     * Dashboard / List Request Paket Bundling
     */
    public function index()
    {
        $filters = [
            'status'    => $this->input->get('status') ?: 'SEMUA',
            'search'    => $this->input->get('search') ?: '',
            'date_from' => $this->input->get('date_from') ?: '',
            'date_to'   => $this->input->get('date_to') ?: ''
        ];

        $data['page_title'] = 'Request Paket Bundling - Purchasing';
        $data['filters']    = $filters;
        $data['requests']   = $this->M_Bundling->get_requests($filters);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/purchasing/bundling/request_list.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Form Input Request Paket Bundling
     */
    public function create()
    {
        $data['page_title'] = 'Buat Request Paket Bundling';
        $data['no_request'] = $this->M_Bundling->generate_request_number();
        $data['formulas']   = $this->M_Bundling->get_all_formulas();
        $data['gudangs']    = $this->db->where('is_active', 1)->get('tb_gudang')->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/purchasing/bundling/request_create.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Simpan Request Paket Bundling
     */
    public function save()
    {
        $user = $this->session->userdata('nik') ?: $this->session->userdata('username') ?: 'PURCHASING';
        $post = $this->input->post();

        if (empty($post['nama_paket']) || empty($post['qty_request'])) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Nama paket dan jumlah request wajib diisi']));
            return;
        }

        $header = [
            'no_request'       => trim($post['no_request']),
            'tanggal_request'  => $post['tanggal_request'] ?: date('Y-m-d'),
            'kode_paket'       => trim($post['kode_paket']),
            'nama_paket'       => trim($post['nama_paket']),
            'id_gudang_tujuan' => !empty($post['id_gudang_tujuan']) ? (int)$post['id_gudang_tujuan'] : 12,
            'id_gudang_asal'   => !empty($post['id_gudang_asal']) ? (int)$post['id_gudang_asal'] : 2,
            'qty_request'      => (float)$post['qty_request'],
            'satuan'           => $post['satuan'] ?: 'Box',
            'keterangan'       => $post['keterangan'] ?? ''
        ];

        $rawDetails = $post['komponen'] ?? [];
        $details = [];
        if (is_array($rawDetails)) {
            foreach ($rawDetails as $item) {
                if (!empty($item['kode_barang_komponen'])) {
                    $details[] = [
                        'kode_barang_komponen' => trim($item['kode_barang_komponen']),
                        'nama_barang_komponen' => trim($item['nama_barang_komponen'] ?? ''),
                        'qty_per_paket'        => (float)($item['qty_per_paket'] ?? 1),
                        'satuan'               => $item['satuan'] ?? 'Pcs'
                    ];
                }
            }
        }

        // Jika dicentang simpan sebagai formula baru
        if (!empty($post['simpan_sebagai_formula']) && $post['simpan_sebagai_formula'] == '1') {
            $this->M_Bundling->save_formula([
                'kode_paket'   => $header['kode_paket'],
                'nama_paket'   => $header['nama_paket'],
                'satuan_paket' => $header['satuan'],
                'keterangan'   => 'Formula dari Request ' . $header['no_request']
            ], array_map(function($d) {
                return [
                    'kode_barang_komponen' => $d['kode_barang_komponen'],
                    'nama_barang_komponen' => $d['nama_barang_komponen'],
                    'qty_komponen'         => $d['qty_per_paket'],
                    'satuan'               => $d['satuan']
                ];
            }, $details));
        }

        $res = $this->M_Bundling->create_request($header, $details, $user);
        $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    /**
     * Detail Request Paket Bundling
     */
    public function detail($id_request)
    {
        $request = $this->M_Bundling->get_request_by_id($id_request);
        if (!$request) {
            show_404();
        }

        $data['page_title'] = 'Detail Request Bundling #' . $request['no_request'];
        $data['request']    = $request;
        $data['stock_status'] = $this->M_Bundling->get_component_stock_status($id_request);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/purchasing/bundling/request_detail.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Batalkan Request
     */
    public function cancel($id_request)
    {
        $user = $this->session->userdata('nik') ?: $this->session->userdata('username') ?: 'PURCHASING';
        $res = $this->M_Bundling->cancel_request($id_request, $user);
        $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    // =========================================================================
    // MASTER FORMULA BUNDLING
    // =========================================================================

    public function formula()
    {
        $search = $this->input->get('search') ?: '';
        $data['page_title'] = 'Master Formula Paket Bundling';
        $data['formulas']   = $this->M_Bundling->get_all_formulas($search);
        $data['search']     = $search;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/purchasing/bundling/formula_list.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function formula_save()
    {
        $post = $this->input->post();
        if (empty($post['kode_paket']) || empty($post['nama_paket'])) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Kode paket dan nama paket wajib diisi']));
            return;
        }

        $data = [
            'id_formula'   => !empty($post['id_formula']) ? (int)$post['id_formula'] : 0,
            'kode_paket'   => trim($post['kode_paket']),
            'nama_paket'   => trim($post['nama_paket']),
            'satuan_paket' => $post['satuan_paket'] ?: 'Box',
            'keterangan'   => $post['keterangan'] ?? ''
        ];

        $rawDetails = $post['komponen'] ?? [];
        $details = [];
        if (is_array($rawDetails)) {
            foreach ($rawDetails as $item) {
                if (!empty($item['kode_barang_komponen'])) {
                    $details[] = [
                        'kode_barang_komponen' => trim($item['kode_barang_komponen']),
                        'nama_barang_komponen' => trim($item['nama_barang_komponen'] ?? ''),
                        'qty_komponen'         => (float)($item['qty_komponen'] ?? 1),
                        'satuan'               => $item['satuan'] ?? 'Pcs'
                    ];
                }
            }
        }

        $res = $this->M_Bundling->save_formula($data, $details);
        $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    // =========================================================================
    // AJAX HELPERS
    // =========================================================================

    public function ajax_get_formula_detail()
    {
        $idFormula = $this->input->get('id_formula');
        $kodePaket = $this->input->get('kode_paket');

        if ($idFormula) {
            $formula = $this->M_Bundling->get_formula_by_id($idFormula);
        } elseif ($kodePaket) {
            $formula = $this->M_Bundling->get_formula_by_kode_paket($kodePaket);
        } else {
            $formula = null;
        }

        if (!$formula) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'msg' => 'Formula tidak ditemukan']));
            return;
        }

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => true, 'data' => $formula]));
    }

    public function ajax_search_barang()
    {
        $term = $this->input->get('q') ?: '';
        $this->db->select('kode_barang, nama_barang, satuan')
            ->from('tbpo_barang')
            ->where('is_active', 'T');

        if (!empty($term)) {
            $this->db->group_start()
                ->like('kode_barang', $term)
                ->or_like('nama_barang', $term)
                ->group_end();
        }

        $items = $this->db->limit(30)->get()->result_array();
        $this->output->set_content_type('application/json')->set_output(json_encode($items));
    }
}
