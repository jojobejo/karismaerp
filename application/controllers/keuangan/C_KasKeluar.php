<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_KasKeluar extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }
        $this->load->model('M_KasKeluar');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);
    }

    public function index()
    {
        $data['page_title'] = 'Kas Keluar';
        
        // Fetch departments for filter modal
        $data['departments'] = $this->db->select('id, nama_departemen')
            ->order_by('nama_departemen', 'ASC')
            ->get('tb_departemen')
            ->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/kas_keluar_list.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function get_data()
    {
        $filters = [
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'search' => $this->input->post('search', true),
            'status' => $this->input->post('status', true),
        ];

        $rows = $this->M_KasKeluar->get_all($filters);

        foreach ($rows as &$row) {
            $row['tanggal_formatted'] = date('d/m/Y', strtotime($row['tanggal']));
            $row['nilai_formatted'] = 'Rp ' . number_format($row['total_amount'], 2, ',', '.');
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    public function form($id = null)
    {
        $data['page_title'] = 'Input Kas Keluar';
        $data['id'] = $id;

        // Fetch source cash/bank accounts
        $data['cash_accounts'] = $this->db->select('id_akun, kode_akun, nama_akun')
            ->where_in('tipe_kontrol', ['KAS', 'BANK'])
            ->where('tipe_akun', 'POSTING')
            ->where('is_active', 1)
            ->order_by('kode_akun', 'ASC')
            ->get('tbkeu_akun')
            ->result_array();

        $data['header'] = null;
        $data['next_ref'] = $this->M_KasKeluar->generate_ref_no();

        if ($id !== null) {
            $data['header'] = $this->M_KasKeluar->get_by_id((int)$id);
            if (!$data['header']) {
                $this->session->set_flashdata('error', 'Data tidak ditemukan.');
                redirect('keuangan/kas_keluar');
            }
            $data['next_ref'] = $data['header']['no_referensi'];
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/kas_keluar_form.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function save()
    {
        $post = $this->input->post();
        
        $id = isset($post['id_kas_keluar']) ? (int)$post['id_kas_keluar'] : 0;
        $no_referensi = trim((string)($post['no_referensi'] ?? ''));
        $id_akun_kas = (int)($post['id_akun_kas'] ?? 0);
        $penerima = trim((string)($post['penerima'] ?? ''));
        $tanggal = trim((string)($post['tanggal'] ?? date('Y-m-d')));
        $memo = trim((string)($post['memo'] ?? ''));
        $is_inclusive_tax = isset($post['is_inclusive_tax']) && $post['is_inclusive_tax'] == 1 ? 1 : 0;
        $is_giro = isset($post['is_giro']) && $post['is_giro'] == 1 ? 1 : 0;
        $postNow = isset($post['post_now']) && $post['post_now'] == 1;

        if (empty($no_referensi) || $id_akun_kas <= 0 || empty($penerima) || empty($tanggal)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Semua kolom bertanda bintang (*) wajib diisi.']));
        }

        $details = [];
        $total_amount = 0.0;
        if (!empty($post['details']) && is_array($post['details'])) {
            foreach ($post['details'] as $line) {
                if (empty($line['id_akun'])) continue;
                $nilai = isset($line['nilai']) ? (float)$line['nilai'] : 0.0;
                if ($nilai <= 0) continue;

                $total_amount += $nilai;
                $details[] = [
                    'id_akun' => (int)$line['id_akun'],
                    'nilai' => $nilai
                ];
            }
        }

        if (empty($details)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Minimal harus ada 1 alokasi dana dengan nilai lebih dari nol.']));
        }

        $userId = (int)($this->session->userdata('id_karyawan') 
            ?: $this->session->userdata('id') 
            ?: $this->session->userdata('id_user') 
            ?: 0);

        $header_data = [
            'no_referensi' => $no_referensi,
            'id_akun_kas' => $id_akun_kas,
            'penerima' => $penerima,
            'tanggal' => $tanggal,
            'memo' => $memo,
            'is_inclusive_tax' => $is_inclusive_tax,
            'is_giro' => $is_giro,
            'total_amount' => $total_amount,
            'status' => $postNow ? 'POSTED' : 'DRAFT',
            'created_by' => $userId ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($id > 0) {
            $header_data['id_kas_keluar'] = $id;
        } else {
            $header_data['created_at'] = date('Y-m-d H:i:s');
        }

        // Save Header & Details
        $saved_id = $this->M_KasKeluar->save($header_data, $details);

        if ($saved_id) {
            if ($postNow) {
                $post_result = $this->M_KasKeluar->post_to_journal($saved_id, $userId ?: null);
                if (!$post_result['success']) {
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['success' => false, 'message' => $post_result['message']]));
                }
            }

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'message' => 'Transaksi Kas Keluar berhasil disimpan.']));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi.']));
        }
    }

    public function delete()
    {
        $id = (int)$this->input->post('id_kas_keluar');
        if ($this->M_KasKeluar->delete($id)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'message' => 'Transaksi Kas Keluar berhasil dihapus/dibatalkan.']));
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Gagal menghapus transaksi.']));
    }

    public function ref_no_ajax()
    {
        $ref = $this->M_KasKeluar->generate_ref_no();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'ref_no' => $ref]));
    }

    public function terbilang_ajax()
    {
        $amount = (float)$this->input->post('amount');
        $word = $this->M_KasKeluar->terbilang($amount);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'spelled' => $word]));
    }

    public function accounts_lookup()
    {
        $search = $this->input->get('search', true);
        
        $this->db->select('id_akun, kode_akun, nama_akun');
        $this->db->from('tbkeu_akun');
        $this->db->where('tipe_akun', 'POSTING');
        $this->db->where('is_active', 1);
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('kode_akun', $search);
            $this->db->or_like('nama_akun', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('kode_akun', 'ASC');
        $rows = $this->db->get()->result_array();
        
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    public function print_receipt($id)
    {
        $data['header'] = $this->M_KasKeluar->get_by_id((int)$id);
        if (!$data['header']) {
            show_404();
        }
        $data['terbilang'] = $this->M_KasKeluar->terbilang($data['header']['total_amount']);

        // Render clean print layout
        $this->load->view('content/keuangan/kas_keluar_print.php', $data);
    }
}
