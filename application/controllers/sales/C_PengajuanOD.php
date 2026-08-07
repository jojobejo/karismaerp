<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PengajuanOD extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('username')) {
            redirect('login');
        }
        $this->load->model('M_PengajuanOD');
        $this->load->database();
        $this->load->library('upload');
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
        $filters = [
            'status' => $this->input->get('status') ?: 'all',
            'jobdesk' => $user['jobdesk'],
            'exclude_approved' => true
        ];
        
        $data['page_title'] = 'Daftar Pengajuan OD (Aktif)';
        $data['user'] = $user;
        $data['is_history'] = false;
        $data['pengajuan'] = $this->M_PengajuanOD->get_all_pengajuan($filters);
        
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pengajuan_od/list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function history()
    {
        $user = $this->_getUser();
        $filters = [
            'status' => $this->input->get('status') ?: 'all',
            'jobdesk' => $user['jobdesk'],
            'only_history' => true
        ];
        
        $data['page_title'] = 'Riwayat Pengajuan OD (Selesai)';
        $data['user'] = $user;
        $data['is_history'] = true;
        $data['pengajuan'] = $this->M_PengajuanOD->get_all_pengajuan($filters);
        
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pengajuan_od/list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function create()
    {
        $user = $this->_getUser();
        if (!in_array($user['jobdesk'], ['SC', 'ADMINSC', 'ADMIN'])) {
            $this->session->set_flashdata('error', 'Hanya SC yang dapat membuat pengajuan OD.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $data['page_title'] = 'Buat Pengajuan OD';
        $data['user'] = $user;
        // Load M_pembayaran to get unpaid invoices
        $this->load->model('M_pembayaran');
        $data['faktur_list'] = $this->M_pembayaran->get_all_unpaid_fakturs();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pengajuan_od/form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail($id)
    {
        $user = $this->_getUser();
        $pengajuan = $this->M_PengajuanOD->get_pengajuan_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $data['page_title'] = 'Detail Pengajuan OD';
        $data['user'] = $user;
        $data['pengajuan'] = $pengajuan;

        // Fetch invoice details for items table
        $id_fakturs = array_column($pengajuan['fakturs'], 'id_faktur');
        if (empty($id_fakturs)) {
            $data['details'] = [];
        } else {
            $this->db->select('d.*, f.no_faktur, f.tanggal_faktur, po.tanggal_jatuh_tempo_baru, po.tempo_baru');
            $this->db->from('tbso_faktur_detail d');
            $this->db->join('tb_pengajuan_od_faktur po', 'po.id_faktur = d.id_faktur AND po.id_pengajuan = '.$id, 'left');
            $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = d.id_faktur', 'left');
            $this->db->where_in('d.id_faktur', $id_fakturs);
            $this->db->order_by('f.tanggal_faktur', 'ASC');
            $this->db->order_by('d.id_faktur', 'ASC');
            $data['details'] = $this->db->get()->result_array();
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pengajuan_od/detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_get_faktur_detail($id_faktur)
    {
        $details = $this->db->get_where('tbso_faktur_detail', ['id_faktur' => $id_faktur])->result_array();
        echo json_encode($details);
    }

    public function ajax_get_multi_faktur_detail()
    {
        $id_fakturs = $this->input->post('id_fakturs');
        if (empty($id_fakturs)) {
            echo json_encode([]);
            return;
        }

        $this->db->select('d.*, f.no_faktur, f.tanggal_faktur, f.customer_name');
        $this->db->from('tbso_faktur_detail d');
        $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = d.id_faktur', 'left');
        $this->db->where_in('d.id_faktur', $id_fakturs);
        $details = $this->db->get()->result_array();
        echo json_encode($details);
    }

    public function ajax_modal_detail($id)
    {
        $data['pengajuan'] = $this->M_PengajuanOD->get_pengajuan_by_id($id);
        if (!$data['pengajuan']) {
            echo 'Data tidak ditemukan.'; 
            return;
        }

        // Get all details for these invoices
        $id_fakturs = array_column($data['pengajuan']['fakturs'], 'id_faktur');
        if (empty($id_fakturs)) {
            $data['details'] = [];
        } else {
            $this->db->select('d.*, f.no_faktur, f.tanggal_faktur');
            $this->db->from('tbso_faktur_detail d');
            $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = d.id_faktur', 'left');
            $this->db->where_in('d.id_faktur', $id_fakturs);
            $this->db->order_by('f.tanggal_faktur', 'ASC');
            $this->db->order_by('d.id_faktur', 'ASC');
            $data['details'] = $this->db->get()->result_array();
        }
        
        $this->load->view('content/sales/pengajuan_od/modal_detail', $data);
    }

    public function store()
    {
        $user = $this->_getUser();
        if (!in_array($user['jobdesk'], ['SC', 'ADMINSC', 'ADMIN'])) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $id_faktur_arr = $this->input->post('id_faktur');
        $id_customer = $this->input->post('id_customer');
        $customer_name = $this->input->post('customer_name');
        $tgl_jtempo_baru = $this->input->post('tanggal_jatuh_tempo_baru');
        
        if (empty($id_faktur_arr)) {
            $this->session->set_flashdata('error', 'Silakan pilih minimal 1 faktur.');
            redirect('sales/C_PengajuanOD/create');
            return;
        }

        // Upload lampiran SC
        $lampiran_sc = '';
        if (!empty($_FILES['lampiran_sc']['name'])) {
            $config['upload_path']   = './assets/uploads/pengajuan_od/';
            $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
            $config['max_size']      = 2048; // 2MB
            $config['file_name']     = 'SC_OD_' . $id_customer . '_' . time();

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->upload->initialize($config);
            if ($this->upload->do_upload('lampiran_sc')) {
                $uploadData = $this->upload->data();
                $lampiran_sc = 'assets/uploads/pengajuan_od/' . $uploadData['file_name'];
            } else {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('sales/C_PengajuanOD/create');
                return;
            }
        }

        $data_insert = [
            'id_customer' => $id_customer,
            'customer_name' => $customer_name,
            'tanggal_pengajuan' => date('Y-m-d'),
            'target_tanggal_jatuh_tempo' => $tgl_jtempo_baru,
            'catatan' => $this->input->post('catatan'),
            'lampiran_sc' => $lampiran_sc,
            'status' => 'pending_mngsc',
            'create_by' => $user['nama'],
            'create_at' => date('Y-m-d H:i:s')
        ];

        $id_pengajuan = $this->M_PengajuanOD->insert_pengajuan($data_insert);
        
        // Simpan setiap faktur
        $faktur_details = [];
        $this->db->where_in('id_faktur', $id_faktur_arr);
        $fakturs = $this->db->get('tbso_faktur_penjualan')->result_array();
        
        foreach ($fakturs as $fk) {
            // Calculate tempo baru: Diff between tanggal_jatuh_tempo_baru and tanggal_faktur
            $d1 = new DateTime($fk['tanggal_faktur']);
            $d2 = new DateTime($tgl_jtempo_baru);
            $diff = $d1->diff($d2);
            $tempo_baru = $diff->days;
            // Jika tanggal jatuh tempo baru lebih kecil dari tanggal faktur, tempo baru bisa negatif, tapi biasanya positif.
            if ($d2 < $d1) {
                $tempo_baru = -$tempo_baru;
            }

            $faktur_details[] = [
                'id_pengajuan' => $id_pengajuan,
                'id_faktur' => $fk['id_faktur'],
                'no_faktur' => $fk['no_faktur'],
                'tempo_lama' => (int)$fk['tempo'],
                'tempo_baru' => (int)$tempo_baru,
                'tanggal_jatuh_tempo_baru' => $tgl_jtempo_baru
            ];
        }
        
        if (!empty($faktur_details)) {
            $this->M_PengajuanOD->insert_pengajuan_faktur($faktur_details);
        }

        $this->session->set_flashdata('success', 'Pengajuan OD berhasil dibuat.');
        redirect('sales/C_PengajuanOD');
    }

    public function edit($id)
    {
        $user = $this->_getUser();
        if (!in_array($user['jobdesk'], ['SC', 'ADMINSC', 'ADMIN'])) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $pengajuan = $this->M_PengajuanOD->get_pengajuan_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data Pengajuan OD tidak ditemukan.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        if (!in_array($pengajuan['status'], ['pending_mngsc', 'rejected'])) {
            $this->session->set_flashdata('error', 'Pengajuan ini telah disetujui/diproses dan tidak dapat diedit lagi.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $data['page_title'] = 'Edit Pengajuan OD';
        $data['user'] = $user;
        $data['is_edit'] = true;
        $data['pengajuan'] = $pengajuan;

        $this->load->model('M_pembayaran');
        $data['faktur_list'] = $this->M_pembayaran->get_all_unpaid_fakturs();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pengajuan_od/form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id)
    {
        $user = $this->_getUser();
        if (!in_array($user['jobdesk'], ['SC', 'ADMINSC', 'ADMIN'])) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $pengajuan = $this->M_PengajuanOD->get_pengajuan_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data Pengajuan OD tidak ditemukan.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        if (!in_array($pengajuan['status'], ['pending_mngsc', 'rejected'])) {
            $this->session->set_flashdata('error', 'Pengajuan ini telah disetujui/diproses dan tidak dapat diedit lagi.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $id_faktur_arr = $this->input->post('id_faktur');
        $id_customer = $this->input->post('id_customer');
        $customer_name = $this->input->post('customer_name');
        $tgl_jtempo_baru = $this->input->post('tanggal_jatuh_tempo_baru');

        if (empty($id_faktur_arr)) {
            $this->session->set_flashdata('error', 'Silakan pilih minimal 1 faktur.');
            redirect('sales/C_PengajuanOD/edit/' . $id);
            return;
        }

        $lampiran_sc = $pengajuan['lampiran_sc'];
        if (!empty($_FILES['lampiran_sc']['name'])) {
            $config['upload_path']   = './assets/uploads/pengajuan_od/';
            $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
            $config['max_size']      = 2048; // 2MB
            $config['file_name']     = 'SC_OD_' . $id_customer . '_' . time();

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->upload->initialize($config);
            if ($this->upload->do_upload('lampiran_sc')) {
                $uploadData = $this->upload->data();
                $lampiran_sc = 'assets/uploads/pengajuan_od/' . $uploadData['file_name'];
            } else {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('sales/C_PengajuanOD/edit/' . $id);
                return;
            }
        }

        $data_update = [
            'id_customer' => $id_customer,
            'customer_name' => $customer_name,
            'target_tanggal_jatuh_tempo' => $tgl_jtempo_baru,
            'catatan' => $this->input->post('catatan'),
            'lampiran_sc' => $lampiran_sc,
            'update_by' => $user['nama'],
            'update_at' => date('Y-m-d H:i:s')
        ];

        if ($pengajuan['status'] == 'rejected') {
            $data_update['status'] = 'pending_mngsc';
            $data_update['approval_mngsc_by'] = null;
            $data_update['approval_mngsc_at'] = null;
            $data_update['catatan_mngsc'] = null;
            $data_update['approval_mngtc_by'] = null;
            $data_update['approval_mngtc_at'] = null;
            $data_update['catatan_mngtc'] = null;
            $data_update['approval_kadepsc_by'] = null;
            $data_update['approval_kadepsc_at'] = null;
            $data_update['catatan_kadepsc'] = null;
        }

        $this->M_PengajuanOD->update_pengajuan($id, $data_update);

        $this->M_PengajuanOD->delete_pengajuan_faktur($id);

        $faktur_details = [];
        $this->db->where_in('id_faktur', $id_faktur_arr);
        $fakturs = $this->db->get('tbso_faktur_penjualan')->result_array();

        foreach ($fakturs as $fk) {
            $d1 = new DateTime($fk['tanggal_faktur']);
            $d2 = new DateTime($tgl_jtempo_baru);
            $diff = $d1->diff($d2);
            $tempo_baru = $diff->days;

            $faktur_details[] = [
                'id_pengajuan' => $id,
                'id_faktur' => $fk['id_faktur'],
                'no_faktur' => $fk['no_faktur'],
                'tempo_lama' => $fk['tempo'],
                'tempo_baru' => $tempo_baru,
                'tanggal_jatuh_tempo_baru' => $tgl_jtempo_baru
            ];
        }

        if (!empty($faktur_details)) {
            $this->M_PengajuanOD->insert_pengajuan_faktur($faktur_details);
        }

        $this->session->set_flashdata('success', 'Pengajuan OD berhasil diperbarui.');
        redirect('sales/C_PengajuanOD');
    }

    public function approval($id)
    {
        $user = $this->_getUser();
        $pengajuan = $this->M_PengajuanOD->get_pengajuan_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $data['page_title'] = 'Persetujuan Pengajuan OD';
        $data['user'] = $user;
        $data['pengajuan'] = $pengajuan;

        // Fetch invoice details for items table
        $id_fakturs = array_column($pengajuan['fakturs'], 'id_faktur');
        if (empty($id_fakturs)) {
            $data['details'] = [];
        } else {
            $this->db->select('d.*, f.no_faktur, f.tanggal_faktur, po.tanggal_jatuh_tempo_baru, po.tempo_baru');
            $this->db->from('tbso_faktur_detail d');
            $this->db->join('tb_pengajuan_od_faktur po', 'po.id_faktur = d.id_faktur AND po.id_pengajuan = '.$id, 'left');
            $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = d.id_faktur', 'left');
            $this->db->where_in('d.id_faktur', $id_fakturs);
            $this->db->order_by('f.tanggal_faktur', 'ASC');
            $this->db->order_by('d.id_faktur', 'ASC');
            $data['details'] = $this->db->get()->result_array();
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pengajuan_od/approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function approve($id)
    {
        $user = $this->_getUser();
        $pengajuan = $this->M_PengajuanOD->get_pengajuan_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('sales/C_PengajuanOD');
            return;
        }

        $action = $this->input->post('action'); // approve / reject
        $catatan_approval = $this->input->post('catatan_approval');

        $update_data = [];
        $status_baru = $pengajuan['status'];

        if ($action == 'approve') {
            if ($pengajuan['status'] == 'pending_mngsc' && in_array($user['jobdesk'], ['MANAGERSC', 'MNGSC', 'ADMIN'])) {
                $status_baru = 'pending_mngtc';
                $update_data['approval_mngsc_by'] = $user['nama'];
                $update_data['approval_mngsc_at'] = date('Y-m-d H:i:s');
                $update_data['catatan_mngsc'] = $catatan_approval;
            } 
            elseif ($pengajuan['status'] == 'pending_mngtc' && in_array($user['jobdesk'], ['MANAGERTC', 'MNGTC', 'ADMIN'])) {
                // Upload lampiran Mng TC (Optional)
                $lampiran_mngtc = '';
                if (!empty($_FILES['lampiran_mngtc']['name'])) {
                    $config['upload_path']   = './assets/uploads/pengajuan_od/';
                    $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
                    $config['max_size']      = 2048; // 2MB
                    $config['file_name']     = 'TC_OD_' . $pengajuan['id_customer'] . '_' . time();

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('lampiran_mngtc')) {
                        $uploadData = $this->upload->data();
                        $lampiran_mngtc = 'assets/uploads/pengajuan_od/' . $uploadData['file_name'];
                        $update_data['lampiran_mngtc'] = $lampiran_mngtc;
                    } else {
                        $this->session->set_flashdata('error', $this->upload->display_errors());
                        redirect('sales/C_PengajuanOD');
                        return;
                    }
                }

                // Determine next status based on max_tempo_baru across all invoices
                $max_tempo_baru = 0;
                foreach ($pengajuan['fakturs'] as $fk) {
                    if ($fk['tempo_baru'] > $max_tempo_baru) {
                        $max_tempo_baru = $fk['tempo_baru'];
                    }
                }

                if ($max_tempo_baru > 90) {
                    $status_baru = 'pending_kadepsc';
                } else {
                    $status_baru = 'approved';
                }
                $update_data['approval_mngtc_by'] = $user['nama'];
                $update_data['approval_mngtc_at'] = date('Y-m-d H:i:s');
                $update_data['catatan_mngtc'] = $catatan_approval;
            }
            elseif ($pengajuan['status'] == 'pending_kadepsc' && in_array($user['jobdesk'], ['KADEPSC', 'ADMIN'])) {
                $status_baru = 'approved';
                $update_data['approval_kadepsc_by'] = $user['nama'];
                $update_data['approval_kadepsc_at'] = date('Y-m-d H:i:s');
                $update_data['catatan_kadepsc'] = $catatan_approval;
            }
        } elseif ($action == 'reject') {
            $status_baru = 'rejected';
            if ($pengajuan['status'] == 'pending_mngsc') {
                $update_data['approval_mngsc_by'] = $user['nama'];
                $update_data['approval_mngsc_at'] = date('Y-m-d H:i:s');
                $update_data['catatan_mngsc'] = $catatan_approval;
            } elseif ($pengajuan['status'] == 'pending_mngtc') {
                $update_data['approval_mngtc_by'] = $user['nama'];
                $update_data['approval_mngtc_at'] = date('Y-m-d H:i:s');
                $update_data['catatan_mngtc'] = $catatan_approval;
            } elseif ($pengajuan['status'] == 'pending_kadepsc') {
                $update_data['approval_kadepsc_by'] = $user['nama'];
                $update_data['approval_kadepsc_at'] = date('Y-m-d H:i:s');
                $update_data['catatan_kadepsc'] = $catatan_approval;
            }
        }

        $update_data['status'] = $status_baru;
        $this->M_PengajuanOD->update_pengajuan($id, $update_data);

        // Jika statusnya approved (sudah Selesai)
        if ($status_baru == 'approved') {
            // Update tbso_faktur_penjualan for all fakturs
            foreach ($pengajuan['fakturs'] as $fk) {
                $this->db->where('id_faktur', $fk['id_faktur']);
                $this->db->update('tbso_faktur_penjualan', [
                    'tempo' => $fk['tempo_baru'],
                    'jtempo' => $fk['tempo_baru'],
                    'tanggal_jatuh_tempo' => $fk['tanggal_jatuh_tempo_baru']
                ]);
            }
            $this->session->set_flashdata('success', 'Pengajuan disetujui, tempo faktur telah diperbarui otomatis.');
        } else {
            $msg = $action == 'approve' ? 'Berhasil melakukan persetujuan.' : 'Pengajuan ditolak.';
            $this->session->set_flashdata('success', $msg);
        }

        redirect('sales/C_PengajuanOD');
    }

    public function activity_log()
    {
        $data['page_title'] = 'KARISMA - Activity Log Pengajuan OD';
        $data['logs'] = $this->M_PengajuanOD->get_activity_log();
        $data['user'] = $this->session->userdata();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pengajuan_od/activity_log.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
