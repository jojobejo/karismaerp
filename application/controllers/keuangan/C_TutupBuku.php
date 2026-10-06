<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_TutupBuku extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'form']);
        $this->load->library('Accounting_closing_service');
        $this->load->model('M_TutupBuku');
    }

    public function index()
    {
        if (!$this->require_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - TUTUP BUKU & CUT-OFF';
        $data['schema_ready'] = $this->accounting_closing_service->schema_ready();
        $data['metrics'] = $this->M_TutupBuku->get_closing_dashboard_metrics();
        $data['periods'] = $this->M_TutupBuku->get_fiscal_periods_with_closing_status();
        $data['closings'] = $this->accounting_closing_service->get_closing_list(50);
        $data['reopens'] = $this->accounting_closing_service->get_reopen_requests(null, 50);
        $data['can_approve'] = $this->can_approve_closing();
        $data['user_id'] = (int)$this->session->userdata('id');
        $data['user_name'] = $this->session->userdata('name') ?: $this->session->userdata('username');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/tutup_buku/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * AJAX: Menjalankan validasi pre-closing
     */
    public function pre_closing_check()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $idPeriode = (int)$this->input->post('id_periode', true);
        $tipeClosing = trim((string)$this->input->post('tipe_closing', true)) ?: 'BULANAN';

        if ($idPeriode <= 0) {
            return $this->json_response(false, 'Periode fiskal tidak valid.', null, ['INVALID_PERIOD_ID'], 422);
        }

        $result = $this->accounting_closing_service->validate_pre_closing($idPeriode, $tipeClosing, $this->user_id());

        return $this->json_response(
            (bool)$result['success'],
            $result['message'],
            $result['data'],
            $result['errors'],
            $result['success'] ? 200 : 422
        );
    }

    /**
     * AJAX: Eksekusi Tutup Buku (Closing Execution)
     */
    public function closing_execute()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $idPeriode = (int)$this->input->post('id_periode', true);
        $tipeClosing = trim((string)$this->input->post('tipe_closing', true)) ?: 'BULANAN';
        $catatan = trim((string)$this->input->post('catatan', true));
        $bypassWarning = (bool)$this->input->post('bypass_warning', true);

        if ($idPeriode <= 0) {
            return $this->json_response(false, 'Periode fiskal tidak valid.', null, ['INVALID_PERIOD_ID'], 422);
        }

        $payload = [
            'tipe_closing' => $tipeClosing,
            'catatan' => $catatan,
            'bypass_warning' => $bypassWarning,
        ];

        $result = $this->accounting_closing_service->execute_closing($idPeriode, $payload, $this->user_id());

        return $this->json_response(
            (bool)$result['success'],
            $result['message'],
            $result['data'],
            $result['errors'],
            $result['success'] ? 200 : 422
        );
    }

    /**
     * AJAX: Mengambil rincian snapshot closing
     */
    public function closing_detail($idClosing)
    {
        if (!$this->require_access(true)) {
            return;
        }

        $idClosing = (int)$idClosing;
        $detail = $this->accounting_closing_service->get_closing_detail($idClosing);

        if (!$detail) {
            return $this->json_response(false, 'Snapshot closing tidak ditemukan.', null, ['CLOSING_NOT_FOUND'], 404);
        }

        return $this->json_response(true, 'Detail snapshot closing berhasil dimuat.', $detail);
    }

    /**
     * AJAX: Mengajukan permohonan buka kembali periode (Reopen Request)
     */
    public function reopen_request()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $idPeriode = (int)$this->input->post('id_periode', true);
        $alasan = trim((string)$this->input->post('alasan', true));

        if ($idPeriode <= 0) {
            return $this->json_response(false, 'Periode fiskal tidak valid.', null, ['INVALID_PERIOD_ID'], 422);
        }

        $result = $this->accounting_closing_service->request_reopen($idPeriode, $alasan, $this->user_id());

        return $this->json_response(
            (bool)$result['success'],
            $result['message'],
            $result['data'],
            $result['errors'],
            $result['success'] ? 201 : 422
        );
    }

    /**
     * AJAX: Persetujuan / Penolakan pembukaan kembali periode oleh Manajerial
     */
    public function reopen_action()
    {
        if (!$this->require_access(true)) {
            return;
        }

        if (!$this->can_approve_closing()) {
            return $this->json_response(false, 'Hanya Manager Akuntansi atau Direktur yang dapat menyetujui pembukaan kembali periode.', null, ['FORBIDDEN'], 403);
        }

        $idReopen = (int)$this->input->post('id_reopen', true);
        $action = strtoupper(trim((string)$this->input->post('action', true)));
        $catatan = trim((string)$this->input->post('catatan', true));

        $result = $this->accounting_closing_service->approve_reopen($idReopen, $action, $catatan, $this->user_id());

        return $this->json_response(
            (bool)$result['success'],
            $result['message'],
            $result['data'],
            $result['errors'],
            $result['success'] ? 200 : 422
        );
    }

    /**
     * Ekspor Snapshot Closing ke CSV
     */
    public function export_closing_snapshot($idClosing, $type = 'accounts')
    {
        if (!$this->require_access()) {
            return;
        }

        $idClosing = (int)$idClosing;
        $detail = $this->accounting_closing_service->get_closing_detail($idClosing);

        if (!$detail) {
            show_error('Data snapshot closing tidak ditemukan.', 404, 'Data Not Found');
            return;
        }

        $header = $detail['header'];
        $filename = "snapshot_{$type}_{$header->kode_periode}_v{$header->closing_version}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Tulis UTF-8 BOM untuk Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        if ($type === 'accounts') {
            fputcsv($output, ['KODE AKUN', 'NAMA AKUN', 'JENIS LAPORAN', 'SALDO NORMAL', 'SALDO AWAL', 'MUTASI DEBIT', 'MUTASI KREDIT', 'SALDO AKHIR']);
            foreach ($detail['accounts'] as $row) {
                fputcsv($output, [
                    $row->kode_akun,
                    $row->nama_akun,
                    $row->jenis_laporan,
                    $row->saldo_normal,
                    $row->saldo_awal,
                    $row->mutasi_debit,
                    $row->mutasi_kredit,
                    $row->saldo_akhir,
                ]);
            }
        } elseif ($type === 'inventory') {
            fputcsv($output, ['KODE BARANG', 'NAMA BARANG', 'GUDANG', 'BATCH / NO LOT', 'EXPIRED DATE', 'QTY AKHIR', 'HPP RATA-RATA', 'TOTAL NILAI STOK']);
            foreach ($detail['inventory'] as $row) {
                fputcsv($output, [
                    $row->kd_barang,
                    $row->nama_barang,
                    $row->gudang_id,
                    $row->batch_no,
                    $row->expired_date,
                    $row->qty_akhir,
                    $row->hpp_rata_rata,
                    $row->total_nilai_stok,
                ]);
            }
        } elseif ($type === 'ar') {
            fputcsv($output, ['KODE CUSTOMER', 'NAMA CUSTOMER', 'NO FAKTUR', 'TANGGAL FAKTUR', 'JATUH TEMPO', 'TOTAL TAGIHAN', 'TOTAL BAYAR', 'SISA PIUTANG']);
            foreach ($detail['ar'] as $row) {
                fputcsv($output, [
                    $row->kd_customer,
                    $row->customer_name,
                    $row->no_faktur,
                    $row->tanggal_faktur,
                    $row->tanggal_jatuh_tempo,
                    $row->total_tagihan,
                    $row->total_bayar,
                    $row->sisa_piutang,
                ]);
            }
        } elseif ($type === 'ap') {
            fputcsv($output, ['KODE SUPPLIER', 'NAMA SUPPLIER', 'NO DOKUMEN / LPB', 'TANGGAL FAKTUR', 'JATUH TEMPO', 'TOTAL TAGIHAN', 'TOTAL BAYAR', 'SISA HUTANG']);
            foreach ($detail['ap'] as $row) {
                fputcsv($output, [
                    $row->kd_suplier,
                    $row->nama_suplier,
                    $row->nomor_dokumen,
                    $row->tanggal_faktur,
                    $row->tanggal_jatuh_tempo,
                    $row->total_tagihan,
                    $row->total_bayar,
                    $row->sisa_hutang,
                ]);
            }
        }

        fclose($output);
        exit;
    }

    // =========================================================================
    // SECURITY & HELPERS
    // =========================================================================

    private function require_access($json = false)
    {
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $username = strtolower(trim((string)$this->session->userdata('username')));
        $level = (int)$this->session->userdata('lv');

        $isAllowed = $username === 'admin'
            || (bool)$this->session->userdata('is_admin_dashboard')
            || ($level === 1 && in_array($jobdesk, ['ADMIN', 'ADMINKEU', 'ADMINKEUTC', 'DIREKTUR', 'MANAGERKEU'], true));

        if ($isAllowed) {
            return true;
        }

        if ($json) {
            $this->json_response(false, 'Akses modul tutup buku hanya untuk admin, keuangan, dan manajerial.', null, ['FORBIDDEN'], 403);
            return false;
        }

        show_error('Akses modul tutup buku hanya untuk admin, keuangan, dan manajerial.', 403, 'Akses Ditolak');
        return false;
    }

    private function can_approve_closing()
    {
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $username = strtolower(trim((string)$this->session->userdata('username')));

        return $username === 'admin'
            || (bool)$this->session->userdata('is_admin_dashboard')
            || in_array($jobdesk, ['ADMIN', 'DIREKTUR', 'MANAGERKEU'], true);
    }

    private function user_id()
    {
        return (int)$this->session->userdata('id') ?: 1;
    }

    private function json_response($success, $message, $data = null, $errors = [], $code = 200)
    {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'success' => $success,
                'message' => $message,
                'data' => $data,
                'errors' => $errors,
                'meta' => [
                    'timestamp' => date('c'),
                ],
            ]));
    }
}
