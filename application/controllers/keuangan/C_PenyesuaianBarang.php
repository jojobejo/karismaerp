<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Penyesuaian Barang (Penyesuaian Persediaan)
 * Referensi: Zahir Accounting > Persediaan > Penyesuaian Persediaan
 */
class C_PenyesuaianBarang extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }
        $this->load->model('M_PenyesuaianBarang');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);
    }

    /**
     * Halaman list transaksi penyesuaian barang
     */
    public function index()
    {
        $data['page_title'] = 'Pemakaian / Penyesuaian Barang';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/penyesuaian_barang_list.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * AJAX: Ambil data list penyesuaian barang
     */
    public function get_data()
    {
        $filters = [
            'date_from' => $this->input->post('date_from', true),
            'date_to'   => $this->input->post('date_to', true),
            'search'    => $this->input->post('search', true),
            'status'    => $this->input->post('status', true),
        ];

        $rows = $this->M_PenyesuaianBarang->get_all($filters);

        foreach ($rows as $k => $row) {
            $rows[$k]['tanggal_formatted'] = date('d/m/Y', strtotime($row['tanggal']));
            $rows[$k]['nilai_formatted'] = 'Rp ' . number_format((float)$row['total_nilai'], 6, '.', '');
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    /**
     * Halaman form input penyesuaian barang (baru / edit)
     */
    public function form($id = null)
    {
        $data['page_title'] = 'Penyesuaian Persediaan';
        $data['id'] = $id;
        $data['gudang_list'] = $this->M_PenyesuaianBarang->lookup_gudang();
        $data['gudangs'] = $data['gudang_list'];
        $data['header'] = null;
        $data['next_ref'] = $this->M_PenyesuaianBarang->generate_ref_no();

        if ($id !== null) {
            $data['header'] = $this->M_PenyesuaianBarang->get_by_id((int)$id);
            if (!$data['header']) {
                $this->session->set_flashdata('error', 'Data tidak ditemukan.');
                redirect('persediaan/penyesuaian_barang');
            }
            $data['next_ref'] = $data['header']['no_referensi'];
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/penyesuaian_barang_form.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * AJAX: Simpan transaksi penyesuaian barang
     */
    public function save()
    {
        $post = $this->input->post();

        $id = isset($post['id_penyesuaian']) ? (int)$post['id_penyesuaian'] : 0;
        $no_referensi = trim((string)($post['no_referensi'] ?? ''));
        $tanggal = trim((string)($post['tanggal'] ?? date('Y-m-d')));
        $keterangan = trim((string)($post['keterangan'] ?? 'Penyesuaian Persediaan'));
        $id_gudang_dari = !empty($post['id_gudang_dari']) ? (int)$post['id_gudang_dari'] : null;
        $id_gudang_ke = !empty($post['id_gudang_ke']) ? (int)$post['id_gudang_ke'] : null;
        $postNow = isset($post['post_now']) && $post['post_now'] == 1;

        if (empty($no_referensi) || empty($tanggal)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Nomor referensi dan tanggal wajib diisi.']));
        }

        if (empty($id_gudang_dari) || empty($id_gudang_ke)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Dari Gudang dan Ke Gudang wajib dipilih.']));
        }

        // Parse detail baris
        $details = [];
        $total_nilai = 0.0;
        if (!empty($post['details']) && is_array($post['details'])) {
            foreach ($post['details'] as $line) {
                if (empty($line['kd_barang'])) continue;
                $jumlah = isset($line['jumlah']) ? (float)$line['jumlah'] : 0;
                if ($jumlah == 0) continue;

                $total_nilai += abs($jumlah);
                $details[] = [
                    'kd_barang'    => trim($line['kd_barang']),
                    'nm_barang'    => trim($line['nm_barang'] ?? ''),
                    'jumlah'       => $jumlah,
                    'satuan'       => trim($line['satuan'] ?? ''),
                    'id_akun'      => !empty($line['id_akun']) ? (int)$line['id_akun'] : null,
                    'no_lot'       => trim($line['no_lot'] ?? ''),
                    'expired_date' => !empty($line['expired_date']) ? trim($line['expired_date']) : null,
                    'lot_data'     => !empty($line['lot_data']) ? (is_array($line['lot_data']) ? json_encode($line['lot_data']) : $line['lot_data']) : (!empty($line['lots']) ? json_encode($line['lots']) : null),
                ];
            }
        }

        if (empty($details)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Minimal harus ada 1 baris barang dengan jumlah tidak nol.']));
        }

        $userId = (int)($this->session->userdata('id_karyawan')
            ?: $this->session->userdata('id')
            ?: $this->session->userdata('id_user')
            ?: 0);

        $header_data = [
            'no_referensi'  => $no_referensi,
            'tanggal'       => $tanggal,
            'keterangan'    => $keterangan,
            'id_gudang_dari' => $id_gudang_dari,
            'id_gudang_ke'  => $id_gudang_ke,
            'total_nilai'   => $total_nilai,
            'status'        => 'DRAFT',
            'created_by'    => $userId ?: null,
            'updated_at'    => date('Y-m-d H:i:s')
        ];

        if ($id > 0) {
            $header_data['id_penyesuaian'] = $id;
        } else {
            $header_data['created_at'] = date('Y-m-d H:i:s');
        }

        // Simpan header & detail
        $saved_id = $this->M_PenyesuaianBarang->save($header_data, $details);

        if ($saved_id) {
            // Jika Rekam (bukan draft), langsung posting jurnal
            if ($postNow) {
                $post_result = $this->M_PenyesuaianBarang->post_to_journal($saved_id, $userId ?: null);
                if ($post_result === false) {
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['success' => false, 'message' => 'Transaksi sudah diposting atau tidak valid.']));
                } elseif (!$post_result['success']) {
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['success' => false, 'message' => $post_result['message']]));
                }
            }

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'message' => 'Transaksi Penyesuaian Barang berhasil disimpan.']));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi.']));
        }
    }

    /**
     * AJAX: Hapus transaksi penyesuaian barang
     */
    public function delete()
    {
        $id = (int)$this->input->post('id_penyesuaian');
        if ($this->M_PenyesuaianBarang->delete($id)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'message' => 'Transaksi berhasil dihapus.']));
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Gagal menghapus transaksi.']));
    }

    /**
     * AJAX: Unpost transaksi (kembalikan ke DRAFT)
     */
    public function unpost()
    {
        $id = (int)$this->input->post('id_penyesuaian');
        if ($this->M_PenyesuaianBarang->unpost($id)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'message' => 'Transaksi berhasil di-unpost.']));
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Gagal unpost transaksi.']));
    }

    /**
     * AJAX: Generate nomor referensi baru
     */
    public function ref_no_ajax()
    {
        $ref = $this->M_PenyesuaianBarang->generate_ref_no();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'ref_no' => $ref]));
    }

    /**
     * AJAX: Lookup barang persediaan
     */
    public function barang_lookup()
    {
        $search = $this->input->get('search', true);
        $gudang_id = $this->input->get('gudang_id', true);

        $rows = $this->M_PenyesuaianBarang->lookup_barang($search, $gudang_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    /**
     * AJAX: Lookup lot barang persediaan
     */
    public function lot_lookup()
    {
        $kd_barang = $this->input->get('kd_barang', true);
        $gudang_id = $this->input->get('gudang_id', true);
        $search    = $this->input->get('search', true);

        $rows = $this->M_PenyesuaianBarang->lookup_lot_barang($kd_barang, $gudang_id, $search);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    /**
     * AJAX: Lookup akun perkiraan
     */
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

    /**
     * AJAX: Lookup gudang aktif
     */
    public function gudang_lookup()
    {
        $rows = $this->M_PenyesuaianBarang->lookup_gudang();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    /**
     * AJAX: Detail jurnal yang sudah diposting
     */
    public function detail_jurnal_ajax($id_penyesuaian)
    {
        $this->db->select('id_jurnal, created_by');
        $pb = $this->db->where('id_penyesuaian', (int)$id_penyesuaian)->get('tbkeu_penyesuaian_barang')->row_array();
        if (!$pb || empty($pb['id_jurnal'])) {
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false, 'message' => 'Jurnal belum terposting atau tidak ditemukan.'
            ]));
        }

        $id_jurnal = $pb['id_jurnal'];
        $jurnal = $this->db->where('id_jurnal', $id_jurnal)->get('tbkeu_jurnal')->row_array();

        $this->db->select('d.*, a.kode_akun, a.nama_akun');
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->db->where('d.id_jurnal', $id_jurnal);
        $this->db->order_by('d.nomor_baris', 'ASC');
        $details = $this->db->get()->result_array();

        // Ambil nama user
        $user_name = '-';
        $userId = !empty($pb['created_by']) ? (int)$pb['created_by'] : (!empty($jurnal['created_by']) ? (int)$jurnal['created_by'] : 0);
        if ($userId > 0) {
            $karyawan = $this->db->select('nm_karyawan, username')->where('id', $userId)->get('tb_karyawan')->row_array();
            if ($karyawan && !empty($karyawan['nm_karyawan'])) {
                $user_name = $karyawan['nm_karyawan'];
            } else if ($karyawan && !empty($karyawan['username'])) {
                $user_name = $karyawan['username'];
            } else {
                $user = $this->db->select('nama_user, username')->where('id', $userId)->get('tb_user')->row_array();
                if ($user && !empty($user['nama_user'])) {
                    $user_name = $user['nama_user'];
                } else if ($user && !empty($user['username'])) {
                    $user_name = $user['username'];
                }
            }
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success' => true, 'header' => $jurnal, 'details' => $details, 'user' => $user_name
        ]));
    }

    /**
     * Halaman cetak bukti transaksi penyesuaian barang
     */
    public function print_receipt($id)
    {
        $data['header'] = $this->M_PenyesuaianBarang->get_by_id((int)$id);
        if (!$data['header']) {
            show_404();
        }
        $this->load->view('content/keuangan/penyesuaian_barang_print.php', $data);
    }
}
