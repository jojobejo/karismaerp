<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Transaksi (Admin & ADMPNJ Transaction Hub)
 * Mengelola semua jenis transaksi di Karisma ERP khusus untuk Admin & Admin Penjualan (ADMPNJ)
 * Termasuk audit, repost, edit dengan auto-sync jurnal, delete, dan activity audit log.
 */
class C_Transaksi extends CI_Controller
{
    private $user_context;

    public function __construct()
    {
        parent::__construct();

        // 1. Validasi Login
        if (!$this->session->userdata('logged_in')) {
            if ($this->input->is_ajax_request()) {
                $this->_json(['success' => false, 'message' => 'Sesi login telah berakhir. Silakan login kembali.'], 401);
            }
            redirect('Auth');
            return;
        }

        $this->load->model('M_Dashboard');
        $this->load->model('admin/M_Transaksi', 'm_transaksi');

        // 2. Validasi Hak Akses Admin & ADMPNJ
        $this->user_context = $this->M_Dashboard->current_user_context();
        $is_admin = !empty($this->user_context['is_admin']);
        $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
        $is_admpnj = in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true);

        if (!$is_admin && !$is_admpnj) {
            if ($this->input->is_ajax_request()) {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Modul transaksi ini hanya dapat diakses oleh Administrator atau Admin Penjualan.'], 403);
            }
            $this->session->set_flashdata('gagal', 'Anda tidak memiliki hak akses untuk membuka modul Transaksi.');
            redirect('dashboard');
            return;
        }
    }

    /**
     * Halaman Utama Modul Semua Transaksi Admin / Faktur Penjualan ADMPNJ
     */
    public function index()
    {
        $is_admin = !empty($this->user_context['is_admin']);
        $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
        $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

        $data['page_title'] = $is_admpnj_only ? 'Faktur Penjualan - Transaksi Penjualan' : 'Semua Transaksi - Admin Transaction Hub';
        $data['user_context'] = $this->user_context;
        $data['is_admpnj_only'] = $is_admpnj_only;
        $data['today'] = date('Y-m-d');
        $data['first_day_of_month'] = date('Y-m-01');

        // Ambil data faktur yang butuh repost terkait request revisi harga LPB
        $this->load->model('M_LpbRevisionRequest');
        $data['lpb_pending_repost_invoices'] = $this->M_LpbRevisionRequest->get_pending_repost_invoices();
        $data['lpb_active_requests'] = $this->M_LpbRevisionRequest->get_active_notifications_for_admpnj();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/transaksi/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Endpoint AJAX: Mengambil list transaksi dengan filter & paginasi
     */
    public function ajax_get_transactions()
    {
        try {
            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            $category = $this->input->get_post('category') ?: ($is_admpnj_only ? 'penjualan' : 'all');
            if ($is_admpnj_only && !in_array($category, ['penjualan', 'faktur_penjualan', 'faktur_konsinyasi'], true)) {
                $category = 'penjualan';
            }

            $filters = [
                'date_from' => $this->input->get_post('date_from'),
                'date_to'   => $this->input->get_post('date_to'),
                'search'    => $this->input->get_post('search'),
                'status'    => $this->input->get_post('status'),
            ];
            $limit  = (int)($this->input->get_post('limit') ?: 50);
            $offset = (int)($this->input->get_post('offset') ?: 0);

            $result = $this->m_transaksi->get_transactions($category, $filters, $limit, $offset);
            $this->_json(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Mengambil rincian transaksi beserta jurnal lengkap
     */
    public function ajax_get_detail($category = '', $idTransaksi = '')
    {
        try {
            $category = trim((string)$category) ?: $this->input->get_post('category');
            $idTransaksi = trim((string)$idTransaksi) ?: $this->input->get_post('id_transaksi');

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Parameter kategori atau ID transaksi tidak valid.'], 400);
                return;
            }

            $detail = $this->m_transaksi->get_transaction_detail($category, $idTransaksi);
            if (empty($detail['header'])) {
                $this->_json(['success' => false, 'message' => 'Data transaksi tidak ditemukan.'], 404);
                return;
            }

            $this->_json(['success' => true, 'data' => $detail]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Mengambil preview live jurnal akuntansi
     */
    public function ajax_get_journal_preview($category = '', $idTransaksi = '')
    {
        try {
            $category = trim((string)$category) ?: $this->input->get_post('category');
            $idTransaksi = trim((string)$idTransaksi) ?: $this->input->get_post('id_transaksi');

            $detail = $this->m_transaksi->get_transaction_detail($category, $idTransaksi);
            $this->_json([
                'success' => true,
                'journal' => $detail['journal'],
                'journal_lines' => $detail['journal_lines'],
                'journals' => $detail['journals'] ?? [],
            ]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Mengambil data form edit transaksi
     */
    public function ajax_get_edit_data($category = '', $idTransaksi = '')
    {
        try {
            $category = trim((string)$category) ?: $this->input->get_post('category');
            $idTransaksi = trim((string)$idTransaksi) ?: $this->input->get_post('id_transaksi');

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && !in_array($category, ['penjualan', 'faktur_penjualan', 'faktur_konsinyasi'], true)) {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat mengedit Faktur Penjualan.'], 403);
                return;
            }

            $detail = $this->m_transaksi->get_transaction_detail($category, $idTransaksi);
            if (empty($detail['header'])) {
                $this->_json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
                return;
            }

            $this->_json(['success' => true, 'header' => $detail['header'], 'items' => $detail['items'], 'category' => $category]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Menyimpan perubahan transaksi dan sinkronisasi jurnal
     */
    public function ajax_update_transaction()
    {
        try {
            $category = trim((string)$this->input->post('category'));
            $idTransaksi = trim((string)$this->input->post('id_transaksi'));

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && !in_array($category, ['penjualan', 'faktur_penjualan', 'faktur_konsinyasi'], true)) {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat mengedit Faktur Penjualan.'], 403);
                return;
            }

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Kategori dan ID transaksi wajib diisi.'], 400);
                return;
            }

            $userId = (int)($this->session->userdata('id_karyawan') 
                ?: $this->session->userdata('id') 
                ?: $this->session->userdata('id_user') 
                ?: 1);

            $payload = [
                'tanggal_transaksi' => $this->input->post('tanggal_transaksi'),
                'keterangan'        => $this->input->post('keterangan'),
                'total_nominal'     => $this->input->post('total_nominal'),
                'jumlah_diskon'     => $this->input->post('jumlah_diskon'),
                'metode_pembayaran' => $this->input->post('metode_pembayaran'),
                'items'             => $this->input->post('items'),
            ];

            $res = $this->m_transaksi->update_transaction_with_journal_sync($category, $idTransaksi, $payload, $userId);
            $this->_json($res, $res['success'] ? 200 : 422);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Memproses repost transaksi & regenerasi jurnal
     */
    public function ajax_repost_transaction()
    {
        try {
            $category = trim((string)$this->input->post('category'));
            $idTransaksi = trim((string)$this->input->post('id_transaksi'));

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && !in_array($category, ['penjualan', 'faktur_penjualan', 'faktur_konsinyasi'], true)) {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat memproses Faktur Penjualan.'], 403);
                return;
            }

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Kategori dan ID transaksi wajib diisi.'], 400);
                return;
            }

            $userId = (int)($this->session->userdata('id_karyawan') 
                ?: $this->session->userdata('id') 
                ?: $this->session->userdata('id_user') 
                ?: 1);

            $res = $this->m_transaksi->repost_transaction_with_journal_sync($category, $idTransaksi, $userId);
            $this->_json($res, $res['success'] ? 200 : 422);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Memproses unpost transaksi — bersihkan jurnal & kembalikan status ke draft
     */
    public function ajax_unpost_transaction()
    {
        try {
            $category    = trim((string)$this->input->post('category'));
            $idTransaksi = trim((string)$this->input->post('id_transaksi'));

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk  = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && !in_array($category, ['penjualan', 'faktur_penjualan', 'faktur_konsinyasi'], true)) {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat memproses Faktur Penjualan.'], 403);
                return;
            }

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Kategori dan ID transaksi wajib diisi.'], 400);
                return;
            }

            $userId = (int)($this->session->userdata('id_karyawan')
                ?: $this->session->userdata('id')
                ?: $this->session->userdata('id_user')
                ?: 1);

            $res = $this->m_transaksi->unpost_transaction($category, $idTransaksi, $userId);
            $this->_json($res, $res['success'] ? 200 : 422);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Memproses delete/void transaksi & pembersihan jurnal
     */
    public function ajax_delete_transaction()
    {
        try {
            $category = trim((string)$this->input->post('category'));
            $idTransaksi = trim((string)$this->input->post('id_transaksi'));
            $reason = trim((string)$this->input->post('reason'));

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && !in_array($category, ['penjualan', 'faktur_penjualan', 'faktur_konsinyasi'], true)) {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat menghapus Faktur Penjualan.'], 403);
                return;
            }

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Kategori dan ID transaksi wajib diisi.'], 400);
                return;
            }

            $userId = (int)($this->session->userdata('id_karyawan') 
                ?: $this->session->userdata('id') 
                ?: $this->session->userdata('id_user') 
                ?: 1);

            $res = $this->m_transaksi->delete_transaction_with_journal_sync($category, $idTransaksi, $userId, $reason);
            $this->_json($res, $res['success'] ? 200 : 422);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Mengambil riwayat Activity Log perubahan faktur / transaksi
     */
    public function ajax_get_activity_logs()
    {
        try {
            $filters = [
                'date_from' => $this->input->get_post('date_from'),
                'date_to'   => $this->input->get_post('date_to'),
                'search'    => $this->input->get_post('search'),
            ];
            $limit  = (int)($this->input->get_post('limit') ?: 50);
            $offset = (int)($this->input->get_post('offset') ?: 0);

            $result = $this->m_transaksi->get_activity_logs($filters, $limit, $offset);
            $this->_json(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Export Excel (.xlsx) transaksi sesuai filter aktif dengan sheet terpisah per jenis transaksi
     */
    public function export_excel()
    {
        if (!file_exists(FCPATH . 'vendor/autoload.php')) {
            show_error('Library PhpSpreadsheet tidak ditemukan.', 500);
            return;
        }
        require_once FCPATH . 'vendor/autoload.php';

        $is_admin = !empty($this->user_context['is_admin']);
        $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
        $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

        $allCategories = [
            'penjualan'           => 'Penjualan (Faktur)',
            'faktur_konsinyasi'   => 'Faktur Konsinyasi',
            'faktur_test_market'  => 'Faktur Test Market',
            'pembelian'           => 'Pembelian (LPB)',
            'pembayaran_customer' => 'Pembayaran Customer',
            'pembayaran_supplier' => 'Pembayaran Supplier',
            'retur_penjualan'     => 'Retur Penjualan',
            'retur_pembelian'     => 'Retur Pembelian',
        ];
        if ($is_admpnj_only) {
            $allCategories = array_intersect_key($allCategories, array_flip([
                'penjualan', 'faktur_konsinyasi', 'faktur_test_market',
            ]));
        }

        $category = strtolower(trim((string)($this->input->get('category', true) ?: ($is_admpnj_only ? 'penjualan' : 'all'))));
        if ($category !== 'all' && !isset($allCategories[$category])) {
            $category = $is_admpnj_only ? 'penjualan' : 'all';
        }

        $today = date('Y-m-d');
        $filters = [
            'date_from' => trim((string)($this->input->get('date_from', true) ?: $today)),
            'date_to'   => trim((string)($this->input->get('date_to', true) ?: $today)),
            'status'    => trim((string)($this->input->get('status', true) ?: 'all')),
            'search'    => trim((string)($this->input->get('search', true) ?: '')),
        ];

        $targetCategories = ($category === 'all')
            ? $allCategories
            : [$category => $allCategories[$category]];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('KARISMA ERP')
            ->setTitle('Export Data Transaksi');

        $summaryRows = [];
        $sheetIndex = 0;

        // Jika memilih Semua Transaksi, buat sheet Ringkasan di awal terlebih dahulu
        if ($category === 'all') {
            $summarySheet = $spreadsheet->getActiveSheet();
            $summarySheet->setTitle('Ringkasan');
            $sheetIndex++;
        }

        foreach ($targetCategories as $catKey => $catLabel) {
            $result = $this->m_transaksi->get_transactions($catKey, $filters, null, 0);
            $rows = $result['data'] ?? [];
            $summary = $result['summary'] ?? [
                'total_count'    => 0,
                'total_nominal'  => 0,
                'total_posted'   => 0,
                'total_unposted' => 0,
            ];
            $summaryRows[] = [
                'label'   => $catLabel,
                'summary' => $summary,
            ];

            $sheet = ($sheetIndex === 0)
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet($sheetIndex);
            $sheetIndex++;

            // Nama sheet Excel maksimal 31 karakter dan bebas karakter terlarang
            $sheetTitle = mb_substr(preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', '-', $catLabel), 0, 31);
            $sheet->setTitle($sheetTitle);
            $ids = array_column($rows, 'id_transaksi');
            $itemsMap = $this->m_transaksi->get_transaction_items_map($catKey, $ids);
            $this->_writeTransactionSheet($sheet, $catLabel, $rows, $filters, $itemsMap);
        }

        if ($category === 'all' && isset($summarySheet)) {
            $this->_writeSummarySheet($summarySheet, $summaryRows, $filters);
            $spreadsheet->setActiveSheetIndex(0);
        }

        $slugCat = preg_replace('/[^A-Za-z0-9_-]+/', '_', $category);
        $filename = 'Transaksi_' . $slugCat . '_' . $filters['date_from'] . '_sd_' . $filters['date_to'] . '_' . date('Ymd_His') . '.xlsx';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $prevErr = error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        error_reporting($prevErr);
        exit;
    }

    /**
     * Mengisi sheet Ringkasan saat export Semua Transaksi
     */
    private function _writeSummarySheet($sheet, array $summaryRows, array $filters)
    {
        $sheet->setCellValue('A1', 'RINGKASAN EXPORT SEMUA TRANSAKSI');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $infoFilter = 'Periode: ' . $filters['date_from'] . ' s/d ' . $filters['date_to']
            . ' | Status: ' . ($filters['status'] ?: 'all')
            . ($filters['search'] !== '' ? ' | Pencarian: ' . $filters['search'] : '');
        $sheet->setCellValue('A2', $infoFilter);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        $headers = ['No', 'Kategori Transaksi (Sheet)', 'Jumlah Transaksi', 'Total Nominal (Rp)', 'Terposting Jurnal', 'Belum Posting'];
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $rowNum = 5;
        $no = 1;
        $grandCount = 0;
        $grandNominal = 0.0;
        $grandPosted = 0;
        $grandUnposted = 0;

        foreach ($summaryRows as $item) {
            $s = $item['summary'];
            $count = (int)($s['total_count'] ?? 0);
            $nominal = (float)($s['total_nominal'] ?? 0);
            $posted = (int)($s['total_posted'] ?? 0);
            $unposted = (int)($s['total_unposted'] ?? 0);

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $item['label']);
            $sheet->setCellValue('C' . $rowNum, $count);
            $sheet->setCellValue('D' . $rowNum, $nominal);
            $sheet->setCellValue('E' . $rowNum, $posted);
            $sheet->setCellValue('F' . $rowNum, $unposted);

            $grandCount += $count;
            $grandNominal += $nominal;
            $grandPosted += $posted;
            $grandUnposted += $unposted;
            $rowNum++;
        }

        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, 'GRAND TOTAL');
        $sheet->setCellValue('C' . $rowNum, $grandCount);
        $sheet->setCellValue('D' . $rowNum, $grandNominal);
        $sheet->setCellValue('E' . $rowNum, $grandPosted);
        $sheet->setCellValue('F' . $rowNum, $grandUnposted);
        $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');

        $sheet->getStyle('C5:C' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D5:D' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E5:F' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A4:F' . $rowNum)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('B', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(6);
        $sheet->freezePane('A5');
    }

    /**
     * Mengisi satu sheet transaksi per kategori beserta rincian kode & nama barang
     */
    private function _writeTransactionSheet($sheet, $title, array $rows, array $filters, array $itemsMap = [])
    {
        $sheet->setCellValue('A1', 'DATA TRANSAKSI - ' . strtoupper($title));
        $sheet->mergeCells('A1:Q1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $infoFilter = 'Periode: ' . $filters['date_from'] . ' s/d ' . $filters['date_to']
            . ' | Status: ' . ($filters['status'] ?: 'all')
            . ($filters['search'] !== '' ? ' | Pencarian: ' . $filters['search'] : '');
        $sheet->setCellValue('A2', $infoFilter);
        $sheet->mergeCells('A2:Q2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        $headers = [
            'No',
            'Tanggal',
            'No Dokumen',
            'No Referensi',
            'Kategori Transaksi',
            'Pihak / Entitas',
            'Kode Barang',
            'Nama Barang',
            'Qty',
            'Satuan',
            'Harga Satuan (Rp)',
            'Subtotal Barang (Rp)',
            'Total Transaksi (Rp)',
            'Keterangan',
            'Status Transaksi',
            'No Jurnal',
            'Status Jurnal',
        ];
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:Q4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $rowNum = 5;
        $no = 1;
        $totalSubtotalBarang = 0.0;
        $totalNominal = 0.0;

        if (empty($rows)) {
            $sheet->setCellValue('A' . $rowNum, 'Tidak ada data transaksi sesuai filter yang dipilih.');
            $sheet->mergeCells('A' . $rowNum . ':Q' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $rowNum)->getFont()->setItalic(true);
            $rowNum++;
        } else {
            foreach ($rows as $r) {
                $txId = (int)($r['id_transaksi'] ?? 0);
                $items = $itemsMap[$txId] ?? [];
                $nominal = (float)($r['total_nominal'] ?? 0);

                // Jika tidak ada rincian item (mis. pembayaran hutang/piutang), tulis 1 baris dengan kode/nama barang '-'
                if (empty($items)) {
                    $items = [[
                        'kd_barang'    => '-',
                        'nama_barang'  => '-',
                        'qty'          => 0,
                        'satuan'       => '-',
                        'harga_satuan' => 0,
                        'subtotal'     => 0,
                    ]];
                }

                $txNo = $no++;
                foreach ($items as $itemIdx => $item) {
                    $isFirstItem = ($itemIdx === 0);
                    $qty = (float)($item['qty'] ?? 0);
                    $hargaSatuan = (float)($item['harga_satuan'] ?? 0);
                    $subtotalBarang = (float)($item['subtotal'] ?? 0);

                    $sheet->setCellValue('A' . $rowNum, $isFirstItem ? $txNo : '');
                    $sheet->setCellValue('B' . $rowNum, (string)($r['tanggal_transaksi'] ?? ''));
                    $sheet->setCellValueExplicit('C' . $rowNum, (string)($r['no_dokumen'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('D' . $rowNum, (string)($r['no_referensi'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValue('E' . $rowNum, (string)($r['trans_category_label'] ?? $title));
                    $sheet->setCellValue('F' . $rowNum, (string)($r['nama_entitas'] ?? ''));
                    $sheet->setCellValueExplicit('G' . $rowNum, (string)($item['kd_barang'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValue('H' . $rowNum, (string)($item['nama_barang'] ?? '-'));
                    $sheet->setCellValue('I' . $rowNum, $qty);
                    $sheet->setCellValue('J' . $rowNum, (string)($item['satuan'] ?? '-'));
                    $sheet->setCellValue('K' . $rowNum, $hargaSatuan);
                    $sheet->setCellValue('L' . $rowNum, $subtotalBarang);
                    $sheet->setCellValue('M' . $rowNum, $isFirstItem ? $nominal : 0);
                    $sheet->setCellValue('N' . $rowNum, (string)($r['keterangan'] ?? ''));
                    $sheet->setCellValue('O' . $rowNum, (string)($r['status_transaksi'] ?? ''));
                    $sheet->setCellValueExplicit('P' . $rowNum, (string)($r['nomor_jurnal'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValue('Q' . $rowNum, (string)($r['status_jurnal'] ?? 'BELUM ADA'));

                    $totalSubtotalBarang += $subtotalBarang;
                    if ($isFirstItem) {
                        $totalNominal += $nominal;
                    }
                    $rowNum++;
                }
            }

            // Baris total per sheet
            $sheet->setCellValue('K' . $rowNum, 'TOTAL (' . count($rows) . ' transaksi)');
            $sheet->setCellValue('L' . $rowNum, $totalSubtotalBarang);
            $sheet->setCellValue('M' . $rowNum, $totalNominal);
            $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
        }

        $sheet->getStyle('I5:I' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.##');
        $sheet->getStyle('K5:M' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A4:Q' . $rowNum)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('B', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(6);
        $sheet->freezePane('A5');
    }

    /**
     * Helper Respon JSON
     */
    private function _json($data, $statusCode = 200)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
