<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_pembayaran extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_pembayaran');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);
        $this->load->database();
        $this->_ensure_access();
    }

    private function _ensure_access()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }

        // Menonaktifkan sementara blokir jobdesk KIUKEU untuk testing/akses fleksibel
        // if (strtoupper((string)$this->session->userdata('jobdesk')) !== 'KIUKEU') {
        //     $this->session->set_flashdata('error', 'Halaman pembayaran faktur hanya untuk user KIU KEU.');
        //     redirect('dashboard');
        // }
    }

    public function index()
    {
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        if ($jobdesk === 'COLLECTION') {
            redirect('keuangan/pembayaran/collection');
        }

        $keyword = trim((string)$this->input->get('q', true));

        $data['page_title'] = 'KARISMA - PEMBAYARAN FAKTUR';
        $data['keyword'] = $keyword;
        $data['customers'] = $this->M_pembayaran->get_customers_with_unpaid_faktur($keyword);
        $data['due_payments'] = $this->M_pembayaran->get_due_pending_payments();
        $data['pending_kasir'] = $this->M_pembayaran->get_pending_kasir_payments();
        
        $pending_retur_query = "SELECT h.*, c.nama_customer, f.id_faktur FROM tbrp_retur_penjualan_header h LEFT JOIN tb_customer c ON h.kd_customer = c.kd_customer LEFT JOIN tbso_faktur_penjualan f ON h.no_faktur_potong = f.no_faktur WHERE h.no_faktur_potong IS NOT NULL AND h.no_faktur_potong != '' AND NOT EXISTS (SELECT 1 FROM tbkeu_pembayaran_faktur p WHERE p.no_faktur = h.no_faktur_potong AND p.metode_pembayaran = 'retur')";
        $pending_returs = $this->db->query($pending_retur_query)->result_array();
        
        foreach ($pending_returs as &$pr) {
            $pr['sisa_tagihan'] = 0;
            if (!empty($pr['id_faktur'])) {
                $faktur_summary = $this->M_pembayaran->get_faktur_summary($pr['id_faktur']);
                if ($faktur_summary) {
                    $pr['sisa_tagihan'] = $faktur_summary['sisa_tagihan'];
                }
            }
        }
        
        $data['pending_returs'] = $pending_returs;
        $data['pending_retur_count'] = count($pending_returs);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_customer.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function collection()
    {
        $data['page_title'] = 'KARISMA - PIUTANG CUSTOMER (COLLECTION)';
        $data['fakturs'] = $this->M_pembayaran->get_all_unpaid_fakturs();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/piutang_collection.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function export_excel()
    {
        require_once APPPATH . 'libraries/PhpSpreadsheet.php';
        $fakturs = $this->M_pembayaran->get_all_unpaid_fakturs();

        $ps = new PhpSpreadsheetLib();
        $spreadsheet = $ps->spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'No Faktur', 'Tanggal Faktur', 'Tanggal Tempo', 'Sisa Hari', 'Customer',
            'Total Piutang', 'Total Pembayaran', 'BG Belum Cair', 'Sisa Piutang',
            'Status Bayar', 'Overdue', 'Frekuensi Bayar', 'Cara Pembayaran'
        ];

        $col = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($col, 1, $h);
            $col++;
        }

        $rowNum = 2;
        foreach ($fakturs as $faktur) {
            $status_bayar = strtolower($faktur['status_pembayaran']);
            $status_label = [
                'lunas'       => 'Lunas',
                'sebagian'    => 'Sebagian',
                'belum_lunas' => 'Belum Lunas',
            ][$status_bayar] ?? $faktur['status_pembayaran'];
            
            $sisa = (int)$faktur['sisa_hari'];
            $sisa_label = $sisa . ' hari';

            // Get payment history details
            $history = $this->M_pembayaran->get_payment_history($faktur['id_faktur']);
            $frekuensi = count($history);
            
            $methods = [];
            foreach ($history as $h_item) {
                $m = $h_item['cara_pembayaran'] ?: $h_item['metode_pembayaran'] ?: '-';
                if ($m !== '-' && in_array(strtolower($m), ['cash', 'transfer', 'bg', 'tempo'], true)) {
                    $m = ucfirst(strtolower($m));
                }
                if (!in_array($m, $methods, true)) {
                    $methods[] = $m;
                }
            }
            $methods_str = empty($methods) ? '-' : implode(', ', $methods);

            $sheet->setCellValueByColumnAndRow(1, $rowNum, $faktur['no_faktur']);
            $sheet->setCellValueByColumnAndRow(2, $rowNum, !empty($faktur['tanggal_faktur']) ? date('d/m/Y', strtotime($faktur['tanggal_faktur'])) : '-');
            $sheet->setCellValueByColumnAndRow(3, $rowNum, !empty($faktur['tanggal_jatuh_tempo']) ? date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) : '-');
            $sheet->setCellValueByColumnAndRow(4, $rowNum, $sisa_label);
            $sheet->setCellValueByColumnAndRow(5, $rowNum, $faktur['nama_customer'] . " (" . $faktur['kd_customer'] . ")");
            $sheet->setCellValueByColumnAndRow(6, $rowNum, (float)$faktur['total_tagihan']);
            $sheet->setCellValueByColumnAndRow(7, $rowNum, (float)$faktur['total_pembayaran']);
            $sheet->setCellValueByColumnAndRow(8, $rowNum, (float)($faktur['total_bg_pending'] ?? 0));
            $sheet->setCellValueByColumnAndRow(9, $rowNum, (float)$faktur['sisa_tagihan']);
            $sheet->setCellValueByColumnAndRow(10, $rowNum, $status_label);
            $sheet->setCellValueByColumnAndRow(11, $rowNum, $faktur['status_overdue']);
            $sheet->setCellValueByColumnAndRow(12, $rowNum, $frekuensi);
            $sheet->setCellValueByColumnAndRow(13, $rowNum, $methods_str);

            $rowNum++;
        }

        $filename = 'laporan_piutang_customer_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = $ps->writer($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function get_payment_history_json()
    {
        $id_faktur = $this->input->get('id_faktur', true);
        if (empty($id_faktur)) {
            echo json_encode(['status' => false, 'message' => 'ID Faktur tidak valid']);
            return;
        }
        $history = $this->M_pembayaran->get_payment_history($id_faktur);
        echo json_encode(['status' => true, 'data' => $history]);
    }

    public function history()
    {
        $keyword = trim((string)$this->input->get('q', true));

        $data['page_title'] = 'KARISMA - HISTORI PEMBAYARAN FAKTUR';
        $data['keyword'] = $keyword;
        $data['recent_payments'] = $this->M_pembayaran->get_recent_payments($keyword, 250);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_history.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function customer($kd_customer = null)
    {
        if (empty($kd_customer)) {
            $this->session->set_flashdata('error', 'Customer tidak ditemukan.');
            redirect('keuangan/pembayaran');
        }

        $kd_customer = rawurldecode($kd_customer);
        $fakturs = $this->M_pembayaran->get_unpaid_faktur_by_customer($kd_customer);

        $data['page_title'] = 'KARISMA - DETAIL PEMBAYARAN CUSTOMER';
        $data['kd_customer'] = $kd_customer;
        $data['fakturs'] = $fakturs;
        $data['customer_name'] = !empty($fakturs) ? $fakturs[0]['nama_customer'] : $kd_customer;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_faktur_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function bayar($id_faktur = null)
    {
        $faktur = $this->_get_valid_faktur($id_faktur, true);

        $data['page_title'] = 'KARISMA - INPUT PEMBAYARAN FAKTUR';
        $data['faktur'] = $faktur;
        $data['is_lunas'] = (float)$faktur['sisa_tagihan'] <= 0;
        $data['history'] = $this->M_pembayaran->get_payment_history($faktur['id_faktur']);
        $data['pending_bg'] = $this->M_pembayaran->get_pending_bg_payment($faktur['id_faktur']);
        $data['saldo_retur'] = $this->M_pembayaran->get_customer_saldo_retur($faktur['kd_customer']);

        $validasi_kasir_id = $this->input->get('validasi_kasir');
        $data['validasi_kasir'] = null;
        $data['is_validasi_kasir_mode'] = false;
        if ($validasi_kasir_id) {
            $data['validasi_kasir'] = $this->db->get_where('tbkeu_pembayaran_faktur', [
                'id_pembayaran' => $validasi_kasir_id, 
                'status_kasir' => 'pending_kasir'
            ])->row_array();
            if ($data['validasi_kasir']) {
                $data['is_validasi_kasir_mode'] = true;
            }
        }

        // Mode konfirmasi pencairan BG hanya aktif jika diakses dengan parameter ?cair_bg=id_pembayaran
        $cair_bg_id = $this->input->get('cair_bg');
        $data['is_bg_cair_mode'] = false;
        if ($cair_bg_id) {
            $cair_bg = null;
            if (is_numeric($cair_bg_id) && (int)$cair_bg_id > 0) {
                $cair_bg = $this->db->get_where('tbkeu_pembayaran_faktur', [
                    'id_pembayaran' => (int)$cair_bg_id,
                    'id_faktur'     => (int)$faktur['id_faktur'],
                    'status_bg'     => 'pending',
                ])->row_array();
            }
            if (!$cair_bg) {
                $cair_bg = $this->M_pembayaran->get_pending_bg_payment($faktur['id_faktur']);
            }
            if ($cair_bg) {
                $data['is_bg_cair_mode'] = true;
                $data['pending_bg']      = $cair_bg;
            }
        }

        // Fetch returns linked by Collection to this invoice
        $data['linked_returs'] = $this->db
            ->select('h.id_retur, h.no_retur, h.no_spr, h.tipe_retur, h.tanggal_retur, h.status_retur, h.catatan_collection, COALESCE(SUM(d.qty_retur * d.harga_satuan), 0) AS total_retur')
            ->from('tbrp_retur_penjualan_header h')
            ->join('tbrp_retur_penjualan_detail d', 'd.id_retur = h.id_retur', 'left')
            ->where('h.no_faktur_potong', $faktur['no_faktur'])
            ->where_in('h.status_retur', ['menunggu_collection', 'menunggu_kasir', 'selesai'])
            ->group_by('h.id_retur')
            ->get()
            ->result_array();

        $data['akun_harta'] = $this->M_pembayaran->get_harta_accounts();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan($id_faktur = null)
    {
        $faktur = $this->_get_valid_faktur($id_faktur);

        $this->form_validation->set_rules('tanggal_pembayaran', 'Tanggal Pembayaran', 'required');
        $this->form_validation->set_rules('jumlah_pembayaran', 'Jumlah Pembayaran', 'required');
        $this->form_validation->set_rules('metode_pembayaran', 'Metode Pembayaran', 'required');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors('', '<br>'));
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        $tanggal_pembayaran = $this->input->post('tanggal_pembayaran', true);
        $jumlah_pembayaran = $this->_normalize_amount($this->input->post('jumlah_pembayaran', true));
        $jumlah_diskon = $this->_normalize_amount($this->input->post('jumlah_diskon', true));
        $metode_pembayaran = trim((string)$this->input->post('metode_pembayaran', true));

        // Validasi metode pembayaran secara dinamis berdasarkan akun Harta aktif di database
        $harta_accounts = $this->M_pembayaran->get_harta_accounts();
        $allowed_account_names = !empty($harta_accounts) ? array_column($harta_accounts, 'nama_akun') : [];

        $is_retur_metode = (
            $metode_pembayaran === 'Q Hutang Non Dagang (Retur Penjualan yg blm dipot)' ||
            $metode_pembayaran === 'Q Hutang Non Dagang' ||
            strtolower($metode_pembayaran) === 'retur'
        );

        $is_valid_metode = (
            $is_retur_metode ||
            $metode_pembayaran === 'bg' ||
            in_array($metode_pembayaran, $allowed_account_names, true)
        );

        // Fallback jika ada akun aktif di master tbkeu_akun
        if (!$is_valid_metode && !empty($metode_pembayaran) && $this->db->table_exists('tbkeu_akun')) {
            $is_valid_metode = $this->db->where('nama_akun', $metode_pembayaran)->where('is_active', 1)->count_all_results('tbkeu_akun') > 0;
        }

        if (!$is_valid_metode) {
            $this->session->set_flashdata('error', 'Metode pembayaran tidak valid.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($is_retur_metode) {
            $saldo_retur = $this->M_pembayaran->get_customer_saldo_retur($faktur['kd_customer']);
            if ($jumlah_pembayaran + $jumlah_diskon > $saldo_retur) {
                $this->session->set_flashdata('error', 'Jumlah pembayaran retur dan diskon melebihi saldo retur customer.');
                redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
            }
        }
        $tanggal_bg_cair = $this->input->post('tanggal_bg_cair', true);
        $no_bg = trim((string)$this->input->post('no_bg', true));
        $nama_bank = trim((string)$this->input->post('nama_bank', true));
        $keterangan = trim((string)$this->input->post('keterangan', true));
        $is_bg_checked = !empty($this->input->post('is_bg', true));
        $is_pending = ($is_bg_checked || $metode_pembayaran === 'bg');
        $cara_pembayaran_faktur = $is_pending ? 'bg' : strtolower(trim((string)($faktur['cara_pembayaran'] ?? 'cash')));

        if ($is_pending && $this->M_pembayaran->get_pending_bg_payment($faktur['id_faktur'])) {
            $this->session->set_flashdata('warning', 'Masih ada pembayaran BG yang belum cair untuk faktur ini. Anda dapat melakukan pembayaran menggunakan metode lain (seperti Kas atau Transfer) atau mencairkan BG yang ada terlebih dahulu.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($is_pending && empty($tanggal_bg_cair)) {
            $this->session->set_flashdata('error', 'Tanggal cair wajib diisi jika pembayaran adalah BG.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($jumlah_pembayaran <= 0) {
            $this->session->set_flashdata('error', 'Jumlah pembayaran harus lebih dari 0.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($jumlah_pembayaran + $jumlah_diskon > (float)$faktur['sisa_tagihan'] + 1) { // Adding small buffer for floating point
            $this->session->set_flashdata('error', 'Jumlah pembayaran dan diskon tidak boleh melebihi sisa tagihan.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        $created_by = $this->session->userdata('nm_karyawan')
            ?: $this->session->userdata('nama')
            ?: $this->session->userdata('username')
            ?: 'system';

        $data = [
            'id_faktur'           => $faktur['id_faktur'],
            'no_faktur'           => $faktur['no_faktur'],
            'tanggal_pembayaran'  => $tanggal_pembayaran,
            'jumlah_pembayaran'   => $jumlah_pembayaran,
            'jumlah_diskon'       => $jumlah_diskon,
            'metode_pembayaran'   => $metode_pembayaran !== '' ? $metode_pembayaran : null,
            'no_bg'               => $no_bg !== '' ? $no_bg : null,
            'nama_bank'           => $nama_bank !== '' ? $nama_bank : null,
            'cara_pembayaran'     => !empty($cara_pembayaran_faktur) ? $cara_pembayaran_faktur : null,
            'tanggal_bg_cair'     => $is_pending ? $tanggal_bg_cair : null,
            'status_bg'           => $is_pending ? 'pending' : 'not_bg',
            'keterangan'          => $keterangan !== '' ? $keterangan : null,
            'create_by'           => $created_by,
            'create_at'           => date('Y-m-d H:i:s'),
        ];

        if ($this->M_pembayaran->insert_payment($data)) {
            // Restore plafon
            if (!$is_pending && ($jumlah_pembayaran + $jumlah_diskon) > 0) {
                $customer_check = $this->db->get_where('tb_customer', ['kd_customer' => $faktur['kd_customer']])->row_array();
                if ($customer_check && isset($customer_check['plafon_aktif']) && (float)$customer_check['plafon_aktif'] != 1000) {
                    $restore_amount = (float)($jumlah_pembayaran + $jumlah_diskon);
                    $this->db->set('plafon_aktif', 'plafon_aktif + ' . $restore_amount, FALSE);
                    $this->db->where('kd_customer', $faktur['kd_customer']);
                    $this->db->update('tb_customer');
                }
            }

            if (!empty($cara_pembayaran_faktur) && in_array($cara_pembayaran_faktur, ['cash', 'transfer', 'bg', 'tempo'], true)) {
                $this->M_pembayaran->update_cara_pembayaran($faktur['id_faktur'], $cara_pembayaran_faktur);
            }
            $this->session->set_flashdata('success', 'Pembayaran faktur berhasil disimpan.');
            redirect('keuangan/pembayaran/customer/' . rawurlencode($faktur['kd_customer']));
        }

        $this->session->set_flashdata('error', 'Pembayaran gagal disimpan.');
        redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
    }

    public function cair($id_pembayaran = null)
    {
        $payment = $this->M_pembayaran->get_payment((int)$id_pembayaran);

        if (!$payment) {
            $this->session->set_flashdata('error', 'Data pembayaran tidak ditemukan.');
            redirect('keuangan/pembayaran');
        }

        if (($payment['status_bg'] ?? 'not_bg') === 'not_bg') {
            $this->session->set_flashdata('error', 'Hanya pembayaran tunda/BG yang bisa ditandai cair.');
            redirect('keuangan/pembayaran/bayar/' . $payment['id_faktur']);
        }

        if (($payment['status_bg'] ?? '') === 'cair') {
            $this->session->set_flashdata('warning', 'Pembayaran tunda/BG tersebut sudah ditandai cair.');
            redirect('keuangan/pembayaran/bayar/' . $payment['id_faktur']);
        }

        $user = $this->session->userdata('nm_karyawan')
            ?: $this->session->userdata('nama')
            ?: $this->session->userdata('username')
            ?: 'system';

        $faktur = $this->M_pembayaran->get_faktur_summary((int)$payment['id_faktur']);
        $kd_customer = $faktur ? $faktur['kd_customer'] : '';

        $update_data = [];
        if ($this->input->method() === 'post') {
            $tanggal_pembayaran = $this->input->post('tanggal_pembayaran', true);
            $jumlah_pembayaran = $this->_normalize_amount($this->input->post('jumlah_pembayaran', true));
            $metode_pembayaran = trim((string)$this->input->post('metode_pembayaran', true));
            $no_bg = trim((string)$this->input->post('no_bg', true));
            $nama_bank = trim((string)$this->input->post('nama_bank', true));
            $tanggal_bg_cair = $this->input->post('tanggal_bg_cair', true);
            $keterangan = trim((string)$this->input->post('keterangan', true));
            $cara_pembayaran_faktur = strtolower(trim((string)$this->input->post('cara_pembayaran_faktur', true)));

            if (!empty($tanggal_pembayaran)) {
                $update_data['tanggal_pembayaran'] = $tanggal_pembayaran;
            }
            if ($jumlah_pembayaran > 0) {
                $update_data['jumlah_pembayaran'] = $jumlah_pembayaran;
            }
            if (!empty($metode_pembayaran)) {
                $update_data['metode_pembayaran'] = $metode_pembayaran;
            }
            $update_data['no_bg'] = $no_bg !== '' ? $no_bg : null;
            $update_data['nama_bank'] = $nama_bank !== '' ? $nama_bank : null;
            if (!empty($tanggal_bg_cair)) {
                $update_data['tanggal_bg_cair'] = $tanggal_bg_cair;
            }
            if (!empty($keterangan)) {
                $update_data['keterangan'] = $keterangan;
            }

            if (!empty($cara_pembayaran_faktur) && in_array($cara_pembayaran_faktur, ['cash', 'transfer', 'bg', 'tempo'], true)) {
                $this->M_pembayaran->update_cara_pembayaran($payment['id_faktur'], $cara_pembayaran_faktur);
            }
        }

        if ($this->M_pembayaran->mark_bg_cair($payment['id_pembayaran'], $user, $update_data)) {
            // Restore plafon
            $final_jumlah = isset($update_data['jumlah_pembayaran']) ? (float)$update_data['jumlah_pembayaran'] : (float)$payment['jumlah_pembayaran'];
            $diskon = (float)$payment['jumlah_diskon']; // assuming discount is not updated during cair
            $restore_amount = $final_jumlah + $diskon;
            
            if ($restore_amount > 0 && !empty($kd_customer)) {
                $customer_check = $this->db->get_where('tb_customer', ['kd_customer' => $kd_customer])->row_array();
                if ($customer_check && isset($customer_check['plafon_aktif']) && (float)$customer_check['plafon_aktif'] != 1000) {
                    $this->db->set('plafon_aktif', 'plafon_aktif + ' . $restore_amount, FALSE);
                    $this->db->where('kd_customer', $kd_customer);
                    $this->db->update('tb_customer');
                }
            }

            $this->session->set_flashdata('success', 'Pembayaran BG berhasil ditandai sudah cair dan masuk ke total pembayaran.');
        } else {
            $this->session->set_flashdata('error', 'Status BG gagal diperbarui.');
        }

        if (!empty($kd_customer)) {
            redirect('keuangan/pembayaran/customer/' . rawurlencode($kd_customer));
        } else {
            redirect('keuangan/pembayaran/bayar/' . $payment['id_faktur']);
        }
    }

    public function approve_kasir($id_pembayaran)
    {
        $user = $this->session->userdata('nm_karyawan')
            ?: $this->session->userdata('nama')
            ?: $this->session->userdata('username')
            ?: 'system';
        
        // Cek apakah ada POST data (dari form validasi)
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $jumlah_pembayaran = $this->_normalize_amount($this->input->post('jumlah_pembayaran', true));
            $tanggal_pembayaran = $this->input->post('tanggal_pembayaran', true);
            $keterangan = trim((string)$this->input->post('keterangan', true));
            
            // Update the pending payment first
            $this->db->where('id_pembayaran', $id_pembayaran);
            $this->db->where('status_kasir', 'pending_kasir');
            $this->db->update('tbkeu_pembayaran_faktur', [
                'jumlah_pembayaran' => $jumlah_pembayaran,
                'tanggal_pembayaran' => $tanggal_pembayaran,
                'keterangan' => $keterangan !== '' ? $keterangan : null
            ]);
        }

        if ($this->M_pembayaran->approve_kasir_payment($id_pembayaran, $user)) {
            $this->session->set_flashdata('success', 'Pembayaran kasir berhasil divalidasi dan jurnal diterbitkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memvalidasi pembayaran kasir.');
        }
        
        $payment = $this->db->get_where('tbkeu_pembayaran_faktur', ['id_pembayaran' => $id_pembayaran])->row_array();
        if ($payment && isset($payment['id_faktur'])) {
            redirect('keuangan/pembayaran/bayar/' . $payment['id_faktur']);
        } else {
            redirect('keuangan/pembayaran');
        }
    }

    private function _get_valid_faktur($id_faktur, $allow_lunas = false)
    {
        $faktur = $this->M_pembayaran->get_faktur_summary($id_faktur);

        if (!$faktur || !in_array($faktur['status'], ['selesai', 'selesai_do'], true)) {
            $this->session->set_flashdata('error', 'Faktur tidak ditemukan atau belum selesai DO.');
            redirect('keuangan/pembayaran');
        }

        if (!$allow_lunas && (float)$faktur['sisa_tagihan'] <= 0) {
            $this->session->set_flashdata('warning', 'Faktur tersebut sudah lunas.');
            redirect('keuangan/pembayaran/customer/' . rawurlencode($faktur['kd_customer']));
        }

        return $faktur;
    }

    private function _normalize_amount($value)
    {
        $value = trim((string)$value);
        $value = str_replace(['Rp', 'rp', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);

        return (float)$value;
    }

    // --- KASIR METHODS ---

    public function kasir()
    {
        $keyword = trim((string)$this->input->get('q', true));

        $data['page_title'] = 'KARISMA - KASIR PEMBAYARAN CASH';
        $data['keyword'] = $keyword;
        // Get all unpaid faktur directly instead of grouped by customer
        $data['fakturs'] = $this->M_pembayaran->get_all_unpaid_fakturs_kasir($keyword);
        
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/kasir_customer.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function kasir_customer($kd_customer = null)
    {
        if (empty($kd_customer)) {
            $this->session->set_flashdata('error', 'Customer tidak ditemukan.');
            redirect('keuangan/pembayaran/kasir');
        }

        $kd_customer = rawurldecode($kd_customer);
        $fakturs = $this->M_pembayaran->get_unpaid_faktur_by_customer($kd_customer);

        $data['page_title'] = 'KARISMA - DETAIL PEMBAYARAN KASIR';
        $data['kd_customer'] = $kd_customer;
        $data['fakturs'] = $fakturs;
        $data['customer_name'] = !empty($fakturs) ? $fakturs[0]['nama_customer'] : $kd_customer;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/kasir_faktur_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function kasir_bayar($id_faktur = null)
    {
        $faktur = $this->_get_valid_faktur_kasir($id_faktur, true);

        $data['page_title'] = 'KARISMA - INPUT PEMBAYARAN CASH KASIR';
        $data['faktur'] = $faktur;
        $data['is_lunas'] = (float)$faktur['sisa_tagihan'] <= 0;
        $data['history'] = $this->M_pembayaran->get_payment_history($faktur['id_faktur']);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/kasir_pembayaran_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function kasir_simpan($id_faktur = null)
    {
        $faktur = $this->_get_valid_faktur_kasir($id_faktur);

        $this->form_validation->set_rules('tanggal_pembayaran', 'Tanggal Pembayaran', 'required');
        $this->form_validation->set_rules('jumlah_pembayaran', 'Jumlah Pembayaran', 'required');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors('', '<br>'));
            redirect('keuangan/pembayaran/kasir_bayar/' . $faktur['id_faktur']);
        }

        $tanggal_pembayaran = $this->input->post('tanggal_pembayaran', true);
        $jumlah_pembayaran = $this->_normalize_amount($this->input->post('jumlah_pembayaran', true));
        $keterangan = trim((string)$this->input->post('keterangan', true));

        if ($jumlah_pembayaran <= 0) {
            $this->session->set_flashdata('error', 'Jumlah pembayaran harus lebih dari 0.');
            redirect('keuangan/pembayaran/kasir_bayar/' . $faktur['id_faktur']);
        }

        if ($jumlah_pembayaran > (float)$faktur['sisa_tagihan'] + 1) { 
            $this->session->set_flashdata('error', 'Jumlah pembayaran tidak boleh melebihi sisa tagihan.');
            redirect('keuangan/pembayaran/kasir_bayar/' . $faktur['id_faktur']);
        }

        $created_by = $this->session->userdata('nm_karyawan')
            ?: $this->session->userdata('nama')
            ?: $this->session->userdata('username')
            ?: 'system';

        $data = [
            'id_faktur'           => $faktur['id_faktur'],
            'no_faktur'           => $faktur['no_faktur'],
            'tanggal_pembayaran'  => $tanggal_pembayaran,
            'jumlah_pembayaran'   => $jumlah_pembayaran,
            'jumlah_diskon'       => 0,
            'metode_pembayaran'   => 'Q Kas',
            'cara_pembayaran'     => 'cash',
            'status_bg'           => 'not_bg',
            'status_kasir'        => 'pending_kasir',
            'keterangan'          => $keterangan !== '' ? $keterangan : null,
            'create_by'           => $created_by,
            'create_at'           => date('Y-m-d H:i:s'),
        ];

        if ($this->M_pembayaran->insert_payment($data)) {
            $this->session->set_flashdata('success', 'Pembayaran cash berhasil disubmit. Menunggu validasi dari KIU KEU.');
            redirect('keuangan/pembayaran/kasir_customer/' . rawurlencode($faktur['kd_customer']));
        }

        $this->session->set_flashdata('error', 'Pembayaran gagal disimpan.');
        redirect('keuangan/pembayaran/kasir_bayar/' . $faktur['id_faktur']);
    }

    private function _get_valid_faktur_kasir($id_faktur, $allow_lunas = false)
    {
        $faktur = $this->M_pembayaran->get_faktur_summary($id_faktur);

        if (!$faktur || !in_array($faktur['status'], ['selesai', 'selesai_do'], true)) {
            $this->session->set_flashdata('error', 'Faktur tidak ditemukan atau belum selesai DO.');
            redirect('keuangan/pembayaran/kasir');
        }

        if (!$allow_lunas && (float)$faktur['sisa_tagihan'] <= 0) {
            $this->session->set_flashdata('warning', 'Faktur tersebut sudah lunas.');
            redirect('keuangan/pembayaran/kasir_customer/' . rawurlencode($faktur['kd_customer']));
        }

        return $faktur;
    }
}
