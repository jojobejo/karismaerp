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
        
        $data['kasbon']           = $this->M_Kasbon->get_kasbon_for_user($user);
        $data['approval_history'] = $this->M_Kasbon->get_approval_history_for_user($user);
        $data['is_approver']       = $this->M_Kasbon->is_approver_user($user);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kasbon/list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Halaman Riwayat Approval Kas Bon (khusus pengajuan yang telah disetujui / diproses sebelumnya).
     */
    public function history()
    {
        $user = $this->_getUser();
        $data['page_title']       = 'Riwayat Approval Kas Bon';
        $data['user']             = $user;
        $data['approval_history'] = $this->M_Kasbon->get_approval_history_for_user($user);
        $data['is_approver']       = $this->M_Kasbon->is_approver_user($user);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kasbon/history.php', $data);
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
        
        $rawNominal = (string)$this->input->post('nominal');
        $cleanNominal = str_replace(['Rp', 'rp', ' ', '.'], '', $rawNominal);
        $cleanNominal = str_replace(',', '.', $cleanNominal);
        $nominal = (float)$cleanNominal;
        
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

        // Tentukan rantai approval berdasarkan jobdesk pemohon (dari session tb_karyawan)
        $jobdesk_pemohon = strtoupper(trim((string)$user['jobdesk']));
        $chain           = $this->M_Kasbon->get_approval_chain($jobdesk_pemohon);

        $approver_1    = isset($chain[0]) ? $chain[0] : null;
        $approver_2    = isset($chain[1]) ? $chain[1] : null;
        $workflow_type = $jobdesk_pemohon; // Simpan jobdesk pemohon sebagai tipe workflow

        // Tentukan status awal berdasarkan rantai yang ada
        if ($approver_1) {
            $status = 'pending_atasan'; // Menunggu approval approver_1
        } else {
            $status = 'approved'; // Tidak ada approver → langsung siap cair
        }

        $data_insert = [
            'no_kasbon'         => $no_kasbon,
            'id_user'           => $user['id'],
            'nama_pemohon'      => $user['nama'],
            'nominal'           => $nominal,
            'keterangan'        => $keterangan,
            'lampiran'          => $lampiran_file,
            'tanggal_pengajuan' => date('Y-m-d'),
            'workflow_type'     => $workflow_type,
            'approver_1'        => $approver_1,
            'approver_2'        => $approver_2,
            'status'            => $status,
            'created_at'        => date('Y-m-d H:i:s')
        ];

        $this->M_Kasbon->insert_kasbon($data_insert);

        $this->session->set_flashdata('success', 'Pengajuan Kas Bon berhasil dibuat dengan nomor: ' . $no_kasbon);
        redirect('C_Kasbon');
    }

    /**
     * Menyetujui pengajuan kasbon (oleh atasan / penilai).
     */
    public function approve($id)
    {
        $user = $this->_getUser();
        $kasbon = $this->M_Kasbon->get_kasbon_by_id($id);
        
        if (!$kasbon) {
            $this->session->set_flashdata('error', 'Pengajuan Kas Bon tidak ditemukan.');
            redirect('C_Kasbon');
            return;
        }

        // Memeriksa hak akses approval
        if (!$this->M_Kasbon->can_approve($kasbon, $user)) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki hak akses untuk menyetujui pengajuan ini.');
            redirect('C_Kasbon');
            return;
        }

        $next_status = 'approved';
        $data_update = [];

        if ($kasbon['status'] === 'pending_atasan') {
            // Approver 1 menyetujui — cek apakah ada approver 2
            if (!empty($kasbon['approver_2'])) {
                $next_status = 'pending_penilai'; // Lanjut ke approver 2
            } else {
                $next_status = 'approved'; // Tidak ada approver 2 → langsung approved
            }
            $data_update['approved_atasan_by'] = $user['nama'];
            $data_update['approved_atasan_at'] = date('Y-m-d H:i:s');

        } else if ($kasbon['status'] === 'pending_penilai') {
            // Approver 2 menyetujui → selalu approved (sudah final)
            $next_status = 'approved';
            $data_update['approved_penilai_by'] = $user['nama'];
            $data_update['approved_penilai_at'] = date('Y-m-d H:i:s');
        }

        $data_update['status'] = $next_status;
        $this->M_Kasbon->update_kasbon($id, $data_update);

        $label_next = ($next_status === 'pending_penilai')
            ? 'dan diteruskan ke ' . $this->M_Kasbon->get_approver_label($kasbon['approver_2'])
            : 'dan siap dicairkan oleh Kasir';

        $this->session->set_flashdata('success', 'Pengajuan Kas Bon ' . $kasbon['no_kasbon'] . ' berhasil disetujui ' . $label_next . '.');
        redirect('C_Kasbon');
    }

    /**
     * Menampilkan halaman detail pengajuan kasbon.
     */
    public function detail($id)
    {
        $user = $this->_getUser();
        $kasbon = $this->M_Kasbon->get_kasbon_by_id($id);

        if (!$kasbon) {
            $this->session->set_flashdata('error', 'Pengajuan Kas Bon tidak ditemukan.');
            redirect('C_Kasbon');
            return;
        }

        $data['page_title'] = 'Detail Kas Bon #' . $kasbon['no_kasbon'];
        $data['user']       = $user;
        $data['kasbon']     = $kasbon;
        $data['can_approve'] = $this->M_Kasbon->can_approve($kasbon, $user);
        $data['can_cair']    = $this->M_Kasbon->can_cair($kasbon, $user);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kasbon/detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Menolak pengajuan kasbon.
     */
    public function reject($id)
    {
        $user = $this->_getUser();
        $kasbon = $this->M_Kasbon->get_kasbon_by_id($id);
        
        if (!$kasbon) {
            $this->session->set_flashdata('error', 'Pengajuan Kas Bon tidak ditemukan.');
            redirect('C_Kasbon');
            return;
        }

        // Memeriksa hak akses approval
        if (!$this->M_Kasbon->can_approve($kasbon, $user)) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki hak akses untuk menolak pengajuan ini.');
            redirect('C_Kasbon');
            return;
        }

        $reason = $this->input->post('rejected_reason') ?: 'Ditolak oleh atasan / penilai';

        $data_update = [
            'status'          => 'rejected',
            'rejected_by'     => $user['nama'],
            'rejected_at'     => date('Y-m-d H:i:s'),
            'rejected_reason' => $reason
        ];

        $this->M_Kasbon->update_kasbon($id, $data_update);

        $this->session->set_flashdata('success', 'Pengajuan Kas Bon ' . $kasbon['no_kasbon'] . ' telah ditolak.');
        redirect('C_Kasbon');
    }

    /**
     * Mencairkan kasbon dan mencatat transaksi Kas Keluar harian (oleh Kasir).
     */
    public function cairkan($id)
    {
        $user = $this->_getUser();
        $kasbon = $this->M_Kasbon->get_kasbon_by_id($id);
        
        if (!$kasbon) {
            $this->session->set_flashdata('error', 'Pengajuan Kas Bon tidak ditemukan.');
            redirect('C_Kasbon');
            return;
        }

        // Memeriksa hak akses pencairan
        if (!$this->M_Kasbon->can_cair($kasbon, $user)) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki hak akses untuk mencairkan pengajuan ini.');
            redirect('C_Kasbon');
            return;
        }

        $this->db->trans_begin();
        try {
            // 1. Ambil model M_Kasir
            $this->load->model('keuangan/M_Kasir');
            
            // Pastikan pilihan "Kas Bon" tersedia di kategori transaksi kasir
            $cek_pilihan = $this->db->where('nama_pilihan', 'Kas Bon')->count_all_results('tbkeu_kasir_pilihan');
            if ($cek_pilihan === 0) {
                $this->db->insert('tbkeu_kasir_pilihan', [
                    'nama_pilihan' => 'Kas Bon',
                    'created_at'   => date('Y-m-d H:i:s')
                ]);
            }

            // 2. Ambil saldo kasir aktif
            $saldo_kasir = $this->M_Kasir->get_saldo_kasir_aktif();
            $id_saldo_kasir = $saldo_kasir ? $saldo_kasir->id : null;

            // 3. Generate nomor transaksi Kas Keluar (KK)
            $no_transaksi = $this->M_Kasir->generate_no_transaksi('KK', date('Y-m-d'));

            // 4. Catat transaksi Kas Keluar
            $data_transaksi = [
                'no_transaksi'    => $no_transaksi,
                'tanggal'         => date('Y-m-d'),
                'jenis_transaksi' => 'kas_keluar',
                'pilihan'         => 'Kas Bon',
                'nominal'         => $kasbon['nominal'],
                'nominal_kembali' => 0,
                'keterangan'      => 'Pencairan Kas Bon ' . $kasbon['no_kasbon'] . ' - ' . $kasbon['nama_pemohon'] . ': ' . $kasbon['keterangan'],
                'id_user'         => $user['id'],
                'id_saldo_kasir'  => $id_saldo_kasir,
                'nama_user'       => $user['nama'],
                'created_at'      => date('Y-m-d H:i:s'),
            ];
            
            $this->M_Kasir->simpan_transaksi($data_transaksi);

            // 5. Update status Kas Bon menjadi cair
            $data_update = [
                'status'  => 'cair',
                'cair_by' => $user['nama'],
                'cair_at' => date('Y-m-d H:i:s')
            ];
            $this->M_Kasbon->update_kasbon($id, $data_update);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Gagal mencairkan Kas Bon.');
            } else {
                $this->db->trans_commit();
                $this->session->set_flashdata('success', 'Kas Bon berhasil dicairkan. Transaksi Kas Keluar ' . $no_transaksi . ' telah otomatis dicatat di kasir.');
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Gagal mencairkan Kas Bon: ' . $e->getMessage());
        }

        redirect('C_Kasbon');
    }
}
