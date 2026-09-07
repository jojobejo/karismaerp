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
        if (!$this->session->userdata('logged_in') && !$this->session->userdata('username') && !$this->session->userdata('is_login')) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'       => false,
                        'auth_timeout' => true,
                        'msg'          => 'Sesi login Anda telah berakhir. Silakan login kembali.'
                    ]));
                return;
            }
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
        $this->load->view('partial/main/footer.php');
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
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Simpan Request Paket Bundling
     */
    public function save()
    {
        try {
            $user = $this->session->userdata('nik') ?: $this->session->userdata('username') ?: 'PURCHASING';
            $post = $this->input->post();

            if (empty($post['nama_paket']) || empty($post['qty_request'])) {
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['status' => false, 'msg' => 'Nama paket dan jumlah request wajib diisi']));
                return;
            }

            $namaPaket = trim($post['nama_paket']);
            $kodePaket = !empty($post['kode_paket']) ? trim($post['kode_paket']) : '';
            if ($kodePaket === '') {
                $kodePaket = 'PKT-' . date('ymd') . '-' . substr(str_shuffle('0123456789ABCDEF'), 0, 4);
            }

            $rawDetails = $post['komponen'] ?? [];
            $details = [];
            if (is_array($rawDetails)) {
                foreach ($rawDetails as $item) {
                    if (!empty($item['kode_barang_komponen'])) {
                        $qtyPerPaket = (float)($item['qty_per_paket'] ?? 1);
                        if ($qtyPerPaket > 0) {
                            $details[] = [
                                'kode_barang_komponen' => trim($item['kode_barang_komponen']),
                                'nama_barang_komponen' => trim($item['nama_barang_komponen'] ?? ''),
                                'qty_per_paket'        => $qtyPerPaket,
                                'satuan'               => !empty($item['satuan']) ? $item['satuan'] : 'Pcs'
                            ];
                        }
                    }
                }
            }

            if (empty($details)) {
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['status' => false, 'msg' => 'Minimal 1 barang komponen isi paket wajib diisi']));
                return;
            }

            $header = [
                'no_request'       => trim($post['no_request'] ?? ''),
                'tanggal_request'  => !empty($post['tanggal_request']) ? $post['tanggal_request'] : date('Y-m-d'),
                'kode_paket'       => $kodePaket,
                'nama_paket'       => $namaPaket,
                'id_gudang_tujuan' => !empty($post['id_gudang_tujuan']) ? (int)$post['id_gudang_tujuan'] : 12,
                'id_gudang_asal'   => !empty($post['id_gudang_asal']) ? (int)$post['id_gudang_asal'] : 2,
                'qty_request'      => (float)$post['qty_request'],
                'satuan'           => !empty($post['satuan']) ? $post['satuan'] : 'Box',
                'keterangan'       => $post['keterangan'] ?? ''
            ];

            // Jika dicentang simpan sebagai formula baru
            if (!empty($post['simpan_sebagai_formula']) && $post['simpan_sebagai_formula'] == '1') {
                $this->M_Bundling->save_formula([
                    'kode_paket'   => $header['kode_paket'],
                    'nama_paket'   => $header['nama_paket'],
                    'satuan_paket' => $header['satuan'],
                    'keterangan'   => 'Formula dari Request ' . ($header['no_request'] ?: 'Baru')
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
        } catch (Throwable $e) {
            log_message('error', 'Error save request bundling: ' . $e->getMessage());
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => false,
                'msg'    => 'Gagal menyimpan request: ' . $e->getMessage()
            ]));
        }
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
        $this->load->view('partial/main/footer.php');
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
        $this->load->view('partial/main/footer.php');
    }

    public function formula_save()
    {
        try {
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
                'satuan_paket' => !empty($post['satuan_paket']) ? $post['satuan_paket'] : 'Box',
                'keterangan'   => $post['keterangan'] ?? ''
            ];

            $rawDetails = $post['komponen'] ?? [];
            $details = [];
            if (is_array($rawDetails)) {
                foreach ($rawDetails as $item) {
                    if (!empty($item['kode_barang_komponen'])) {
                        $qtyKomponen = (float)($item['qty_komponen'] ?? 1);
                        if ($qtyKomponen > 0) {
                            $details[] = [
                                'kode_barang_komponen' => trim($item['kode_barang_komponen']),
                                'nama_barang_komponen' => trim($item['nama_barang_komponen'] ?? ''),
                                'qty_komponen'         => $qtyKomponen,
                                'satuan'               => !empty($item['satuan']) ? $item['satuan'] : 'Pcs'
                            ];
                        }
                    }
                }
            }

            if (empty($details)) {
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['status' => false, 'msg' => 'Minimal 1 barang komponen isi paket wajib diisi']));
                return;
            }

            $res = $this->M_Bundling->save_formula($data, $details);
            $this->output->set_content_type('application/json')->set_output(json_encode($res));
        } catch (Throwable $e) {
            log_message('error', 'Error save formula: ' . $e->getMessage());
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => false,
                'msg'    => 'Gagal menyimpan formula: ' . $e->getMessage()
            ]));
        }
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
        $term = trim($this->input->get('q') ?: '');

        // Query ke tberp_stock_batch join tbpo_barang
        $this->db->select("
            sb.kd_barang AS kode_barang,
            COALESCE(b.nama_barang, sb.kd_barang) AS nama_barang,
            COALESCE(NULLIF(b.satuan, ''), 'Pcs') AS satuan,
            ROUND(SUM(sb.qty_on_hand), 2) AS total_stok,
            ROUND(SUM(sb.qty_on_hand - GREATEST(COALESCE(sb.qty_reserved, 0), 0)), 2) AS stok_tersedia,
            COUNT(DISTINCT sb.no_lot) AS jml_lot
        ", false);
        $this->db->from('tberp_stock_batch sb');
        $this->db->join('tbpo_barang b', 'b.kode_barang = sb.kd_barang', 'left');

        if (!empty($term)) {
            $this->db->group_start();
            $this->db->like('sb.kd_barang', $term);
            $this->db->or_like('b.nama_barang', $term);
            $this->db->group_end();
        }

        $this->db->group_by(['sb.kd_barang', 'b.nama_barang', 'b.satuan']);
        $this->db->order_by('total_stok', 'DESC');
        $this->db->order_by('b.nama_barang', 'ASC');
        $this->db->limit(35);
        $items = $this->db->get()->result_array();

        // Fallback jika pencarian ada keyword dan hasil di batch kurang dari 15
        if (!empty($term) && count($items) < 15) {
            $existingCodes = array_column($items, 'kode_barang');
            $this->db->select("
                kode_barang,
                nama_barang,
                COALESCE(NULLIF(satuan, ''), 'Pcs') AS satuan,
                0.00 AS total_stok,
                0.00 AS stok_tersedia,
                0 AS jml_lot
            ", false);
            $this->db->from('tbpo_barang');
            $this->db->where('is_active', 'T');
            if (!empty($existingCodes)) {
                $this->db->where_not_in('kode_barang', $existingCodes);
            }
            $this->db->group_start();
            $this->db->like('kode_barang', $term);
            $this->db->or_like('nama_barang', $term);
            $this->db->group_end();
            $this->db->limit(15);
            $fallbackItems = $this->db->get()->result_array();
            $items = array_merge($items, $fallbackItems);
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($items));
    }
}
