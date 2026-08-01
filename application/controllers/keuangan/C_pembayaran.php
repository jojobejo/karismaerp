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

        // Fetch returns linked by Collection to this invoice
        $data['linked_returs'] = $this->db
            ->select('h.no_retur, h.tipe_retur, h.tanggal_retur, h.status_retur, COALESCE(SUM(d.qty_retur * d.harga_satuan), 0) AS total_retur')
            ->from('tbrp_retur_penjualan_header h')
            ->join('tbrp_retur_penjualan_detail d', 'd.id_retur = h.id_retur', 'left')
            ->where('h.no_faktur_potong', $faktur['no_faktur'])
            ->where_in('h.status_retur', ['menunggu_collection', 'menunggu_kasir', 'selesai'])
            ->group_by('h.id_retur')
            ->get()
            ->result_array();

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
        $allowed_accounts = [
            'Q Kas', 'A Kas', 'Bank', 'Q BCA 1588', 'Q BCA On Line', 'Q Danamon', 'Q Mandiri', 'Q Deposito', 'Q BRI',
            'Q Mandiri 143-00-8389898-9', 'Q BRI 300300', 'Q BRI 999300', 'Q Mandiri Giro 143 0029 298989',
            'A BCA 1088', 'A BCA 3688', 'A BCA (Annelia)', 'A BCA (Yuanita)', 'A BCA (IB)', 'A BCA (DKS)',
            'A BRI', 'A Mandiri', 'A Bukopin', 'A Danamon', 'A BCA 1588', 'A Mandiri 8181', 'A BRI 9305',
            'A BCA (Yuanita Giro)', 'A BRI 8303', 'A BRI 4626-01-012498-53-4', 'A BRI 5305', 'A Deposito',
            'Q Mandiri 8989', 'Q BRI 2567', 'Q BNI 0080', 'Q BRI 5534', 'Q CIMB Niaga', 'Q BRI 004575-56-6',
            'Q BRI 555888-56-9', 'A BRI 8568', 'A CIMB 9100'
        ];
        if ($metode_pembayaran !== 'Q Hutang Non Dagang' && $metode_pembayaran !== 'bg' && !in_array($metode_pembayaran, $allowed_accounts, true)) {
            $this->session->set_flashdata('error', 'Metode pembayaran tidak valid.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($metode_pembayaran === 'Q Hutang Non Dagang') {
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
        $cara_pembayaran_faktur = strtolower(trim((string)$this->input->post('cara_pembayaran_faktur', true)));
        $is_pending = ($cara_pembayaran_faktur === 'bg');

        if ($is_pending && $this->M_pembayaran->get_pending_bg_payment($faktur['id_faktur'])) {
            $this->session->set_flashdata('warning', 'Masih ada pembayaran yang belum cair. Klik Bayar lalu tekan tombol BG Sudah Cair.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($is_pending && empty($tanggal_bg_cair)) {
            $this->session->set_flashdata('error', 'Tanggal cair wajib diisi jika cara pembayaran faktur adalah BG.');
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
            $cara_pembayaran_faktur = strtolower(trim((string)$this->input->post('cara_pembayaran_faktur', true)));
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

    private function _get_valid_faktur($id_faktur, $allow_lunas = false)
    {
        $faktur = $this->M_pembayaran->get_faktur_summary($id_faktur);

        if (!$faktur || $faktur['status'] !== 'selesai_do') {
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
}
