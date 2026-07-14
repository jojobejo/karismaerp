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

        if (strtoupper((string)$this->session->userdata('jobdesk')) !== 'KIUKEU') {
            $this->session->set_flashdata('error', 'Halaman pembayaran faktur hanya untuk user KIU KEU.');
            redirect('dashboard');
        }
    }

    public function index()
    {
        $keyword = trim((string)$this->input->get('q', true));

        $data['page_title'] = 'KARISMA - PEMBAYARAN FAKTUR';
        $data['keyword'] = $keyword;
        $data['customers'] = $this->M_pembayaran->get_customers_with_unpaid_faktur($keyword);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_customer.php', $data);
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
        $faktur = $this->_get_valid_faktur($id_faktur);

        $data['page_title'] = 'KARISMA - INPUT PEMBAYARAN FAKTUR';
        $data['faktur'] = $faktur;
        $data['history'] = $this->M_pembayaran->get_payment_history($faktur['id_faktur']);
        $data['pending_bg'] = $this->M_pembayaran->get_pending_bg_payment($faktur['id_faktur']);
        $data['saldo_retur'] = $this->M_pembayaran->get_customer_saldo_retur($faktur['kd_customer']);

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
        $metode_pembayaran = strtolower(trim((string)$this->input->post('metode_pembayaran', true)));
        if (!in_array($metode_pembayaran, ['cash', 'transfer', 'tempo', 'bg', 'retur'], true)) {
            $this->session->set_flashdata('error', 'Metode pembayaran tidak valid.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($metode_pembayaran === 'retur') {
            $saldo_retur = $this->M_pembayaran->get_customer_saldo_retur($faktur['kd_customer']);
            if ($jumlah_pembayaran > $saldo_retur) {
                $this->session->set_flashdata('error', 'Jumlah pembayaran retur (' . number_format($jumlah_pembayaran, 0, ',', '.') . ') melebihi saldo retur customer (' . number_format($saldo_retur, 0, ',', '.') . ').');
                redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
            }
        }
        $tanggal_bg_cair = $this->input->post('tanggal_bg_cair', true);
        $keterangan = trim((string)$this->input->post('keterangan', true));

        if ($metode_pembayaran === 'bg' && $this->M_pembayaran->get_pending_bg_payment($faktur['id_faktur'])) {
            $this->session->set_flashdata('warning', 'Masih ada pembayaran BG yang belum cair. Klik Bayar lalu tekan tombol BG Sudah Cair.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($metode_pembayaran === 'bg' && empty($tanggal_bg_cair)) {
            $this->session->set_flashdata('error', 'Tanggal BG cair wajib diisi untuk pembayaran BG.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($jumlah_pembayaran <= 0) {
            $this->session->set_flashdata('error', 'Jumlah pembayaran harus lebih dari 0.');
            redirect('keuangan/pembayaran/bayar/' . $faktur['id_faktur']);
        }

        if ($jumlah_pembayaran > (float)$faktur['sisa_tagihan']) {
            $this->session->set_flashdata('error', 'Jumlah pembayaran tidak boleh melebihi sisa tagihan.');
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
            'metode_pembayaran'   => $metode_pembayaran !== '' ? $metode_pembayaran : null,
            'tanggal_bg_cair'     => $metode_pembayaran === 'bg' ? $tanggal_bg_cair : null,
            'status_bg'           => $metode_pembayaran === 'bg' ? 'pending' : 'not_bg',
            'keterangan'          => $keterangan !== '' ? $keterangan : null,
            'create_by'           => $created_by,
            'create_at'           => date('Y-m-d H:i:s'),
        ];

        if ($this->M_pembayaran->insert_payment($data)) {
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

        if (strtolower((string)$payment['metode_pembayaran']) !== 'bg') {
            $this->session->set_flashdata('error', 'Hanya pembayaran BG yang bisa ditandai cair.');
            redirect('keuangan/pembayaran/bayar/' . $payment['id_faktur']);
        }

        if (($payment['status_bg'] ?? '') === 'cair') {
            $this->session->set_flashdata('warning', 'Pembayaran BG tersebut sudah ditandai cair.');
            redirect('keuangan/pembayaran/bayar/' . $payment['id_faktur']);
        }

        $user = $this->session->userdata('nm_karyawan')
            ?: $this->session->userdata('nama')
            ?: $this->session->userdata('username')
            ?: 'system';

        if ($this->M_pembayaran->mark_bg_cair($payment['id_pembayaran'], $user)) {
            $this->session->set_flashdata('success', 'Pembayaran BG berhasil ditandai sudah cair dan masuk ke total pembayaran.');
        } else {
            $this->session->set_flashdata('error', 'Status BG gagal diperbarui.');
        }

        redirect('keuangan/pembayaran/bayar/' . $payment['id_faktur']);
    }

    private function _get_valid_faktur($id_faktur)
    {
        $faktur = $this->M_pembayaran->get_faktur_summary((int)$id_faktur);

        if (!$faktur || $faktur['status'] !== 'selesai_do') {
            $this->session->set_flashdata('error', 'Faktur tidak ditemukan atau belum selesai DO.');
            redirect('keuangan/pembayaran');
        }

        if ((float)$faktur['sisa_tagihan'] <= 0) {
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
