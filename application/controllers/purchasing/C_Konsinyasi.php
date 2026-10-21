<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Konsinyasi
 * Mengelola alur Penyelesaian Konsinyasi (Consignment Settlement):
 * - Rekonsiliasi barang konsinyasi yang telah terjual ke customer
 * - Input harga beli dan invoice resmi dari supplier
 * - Posting jurnal pembelian otomatis ke Persediaan, PPN Masukan, dan Hutang Usaha
 */
class C_Konsinyasi extends CI_Controller
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
                        'status'  => false,
                        'message' => 'Sesi login Anda telah berakhir. Silakan login kembali.'
                    ]));
                return;
            }
            redirect('auth');
        }

        $this->load->model(['M_Konsinyasi']);
        $this->load->helper(['url', 'form']);
    }

    /**
     * Halaman Utama Modul Barang Konsinyasi (Penerimaan, Tracking Lokasi & Penyelesaian)
     */
    public function index()
    {
        // Jalankan sinkronisasi background untuk mendeteksi barang konsinyasi terjual terbaru
        $this->M_Konsinyasi->sync_pending_consignment_sales();

        $filters = [
            'status'     => $this->input->get('status') ?: 'LAKU',
            'kd_suplier' => $this->input->get('kd_suplier') ?: 'SEMUA',
            'date_from'  => $this->input->get('date_from') ?: '',
            'date_to'    => $this->input->get('date_to') ?: '',
            'search'     => $this->input->get('search') ?: '',
            'tab'        => $this->input->get('tab') ?: 'tracking'
        ];

        $data['page_title']       = 'Tracking & Penyelesaian Barang Konsinyasi - Purchasing';
        $data['filters']          = $filters;
        $data['stats']            = $this->M_Konsinyasi->get_summary_stats($filters);
        $data['tracking_summary'] = $this->M_Konsinyasi->get_tracking_summary();
        $data['tracking_barang']  = $this->M_Konsinyasi->get_tracking_barang_list($filters);
        $data['suppliers']        = $this->M_Konsinyasi->get_suppliers_list();

        $isLakuAllSuppliers = (($filters['status'] === 'LAKU' || $filters['status'] === 'PENDING') && $filters['kd_suplier'] === 'SEMUA');
        $perPage = 20;
        $allSettlements = [];
        $allLakuSuppliers = [];
        if ($filters['tab'] === 'penyelesaian') {
            if ($isLakuAllSuppliers) {
                $allLakuSuppliers = $this->M_Konsinyasi->get_laku_suppliers_summary($filters);
            } else {
                $allSettlements = $this->M_Konsinyasi->get_settlement_list($filters);
            }
        }
        $totalRows = $isLakuAllSuppliers ? count($allLakuSuppliers) : count($allSettlements);
        $currentPage = $this->normalize_page($this->input->get('page_penyelesaian'), $totalRows, $perPage);
        $offset = ($currentPage - 1) * $perPage;

        $data['settlements'] = $isLakuAllSuppliers ? $allSettlements : array_slice($allSettlements, $offset, $perPage);
        $data['laku_suppliers'] = $isLakuAllSuppliers ? array_slice($allLakuSuppliers, $offset, $perPage) : $allLakuSuppliers;
        $data['settlement_pagination'] = $this->pagination_data($totalRows, $currentPage, $perPage);

        $data['selected_supplier_name'] = '';
        if (!empty($filters['kd_suplier']) && $filters['kd_suplier'] !== 'SEMUA') {
            foreach ($data['suppliers'] as $sup) {
                if ($sup['kd_suplier'] === $filters['kd_suplier']) {
                    $data['selected_supplier_name'] = $sup['nama_suplier'];
                    break;
                }
            }
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/purchasing/konsinyasi/settlement_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    private function normalize_page($requestedPage, $totalRows, $perPage)
    {
        $totalPages = max(1, (int)ceil((int)$totalRows / (int)$perPage));
        return min(max(1, (int)$requestedPage), $totalPages);
    }

    private function pagination_data($totalRows, $currentPage, $perPage)
    {
        $totalRows = (int)$totalRows;
        return [
            'total_rows'   => $totalRows,
            'current_page' => (int)$currentPage,
            'per_page'     => (int)$perPage,
            'total_pages'  => max(1, (int)ceil($totalRows / (int)$perPage)),
            'from'         => $totalRows > 0 ? (((int)$currentPage - 1) * (int)$perPage) + 1 : 0,
            'to'           => min((int)$currentPage * (int)$perPage, $totalRows),
        ];
    }

    /**
     * AJAX: Ambil detail settlement untuk modal form input harga
     */
    public function ajax_detail()
    {
        $idSettlement = (int) $this->input->get('id_settlement');
        $allIdsStr = trim((string) $this->input->get('all_ids'));

        if (!empty($allIdsStr) && strpos($allIdsStr, ',') !== false) {
            $ids = array_unique(array_filter(array_map('intval', explode(',', $allIdsStr))));
            $item = $this->M_Konsinyasi->get_grouped_settlement_by_ids($ids);
        } else {
            $item = $this->M_Konsinyasi->get_settlement_by_id($idSettlement);
        }

        if (!$item) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Data tidak ditemukan.']));
            return;
        }

        // Harga penjualan merupakan informasi Sales dan tidak boleh dikirim
        // ke pengguna Purchasing, termasuk melalui respons AJAX detail.
        unset($item['hrg_jual'], $item['subtotal_jual']);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => true, 'data' => $item]));
    }

    /**
     * AJAX: Posting penyelesaian konsinyasi (input invoice & harga beli supplier)
     */
    public function ajax_post_settlement()
    {
        $idSettlement = (int) $this->input->post('id_settlement');
        $allIdsStr = trim((string) $this->input->post('all_settlement_ids'));

        $payload = [
            'qty_laku'             => (float) $this->input->post('qty_laku'),
            'tipe_pajak'           => trim((string) $this->input->post('tipe_pajak', TRUE)),
            'hrg_satuan_input'     => (float) $this->input->post('hrg_satuan_input'),
            'hrg_beli_satuan'      => (float) $this->input->post('hrg_satuan_input'),
            'no_invoice_supplier'  => trim((string) $this->input->post('no_invoice_supplier', TRUE)),
            'tgl_invoice_supplier' => trim((string) $this->input->post('tgl_invoice_supplier', TRUE)),
            'ppn_persen'           => (float) $this->input->post('ppn_persen'),
            'catatan'              => trim((string) $this->input->post('catatan', TRUE))
        ];

        $userId = (int) ($this->session->userdata('id') ?: $this->session->userdata('user_id') ?: 1);
        $userName = (string) ($this->session->userdata('nama') ?: $this->session->userdata('username') ?: 'PURCHASING');

        $ids = [];
        if (!empty($allIdsStr)) {
            $ids = array_unique(array_filter(array_map('intval', explode(',', $allIdsStr))));
        }

        if (count($ids) > 1) {
            $result = $this->M_Konsinyasi->process_settlement_batch($ids, $payload, $userId, $userName);
        } else {
            $result = $this->M_Konsinyasi->process_settlement($idSettlement, $payload, $userId, $userName);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * AJAX: Update nomor faktur & tanggal invoice supplier untuk barang konsinyasi lunas
     */
    public function ajax_update_invoice()
    {
        $idSettlement = (int) $this->input->post('id_settlement');
        $allIdsStr = trim((string)$this->input->post('all_settlement_ids'));
        $noInvoice = trim((string) $this->input->post('no_invoice_supplier', TRUE));
        $tglInvoice = trim((string) $this->input->post('tgl_invoice_supplier', TRUE));

        if ($idSettlement <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'ID settlement tidak valid.']));
            return;
        }

        if ($noInvoice === '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Nomor Faktur / Invoice Supplier wajib diisi.']));
            return;
        }

        if ($tglInvoice === '') {
            $tglInvoice = date('Y-m-d');
        }

        $userId = (int) ($this->session->userdata('id') ?: $this->session->userdata('user_id') ?: 1);
        $userName = (string) ($this->session->userdata('nama') ?: $this->session->userdata('username') ?: 'PURCHASING');

        $ids = array_unique(array_filter(array_map('intval', explode(',', $allIdsStr))));
        if (count($ids) > 1) {
            $result = $this->M_Konsinyasi->update_invoice_supplier_batch($idSettlement, $ids, $noInvoice, $tglInvoice, $userId, $userName);
        } else {
            $result = $this->M_Konsinyasi->update_invoice_supplier($idSettlement, $noInvoice, $tglInvoice, $userId, $userName);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * AJAX: Ambil detail kios pemegang barang konsinyasi tertentu
     */
    public function ajax_tracking_kios()
    {
        $kdBarang = trim((string) $this->input->get('kd_barang'));
        if (empty($kdBarang)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Kode barang tidak valid.']));
            return;
        }

        $list = $this->M_Konsinyasi->get_tracking_kios_by_barang($kdBarang);

        foreach ($list as &$row) {
            unset($row['hrg_jual'], $row['subtotal_jual']);
        }
        unset($row);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data'   => $list
            ]));
    }

    /**
     * AJAX: Trigger sinkronisasi manual penjualan konsinyasi
     */
    public function ajax_sync()
    {
        $count = $this->M_Konsinyasi->sync_pending_consignment_sales();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => true,
                'message' => 'Sinkronisasi berhasil. ' . $count . ' data penjualan konsinyasi baru ditemukan.',
                'count'   => $count
            ]));
    }

    /**
     * AJAX: Ambil detail jurnal akuntansi terkait transaksi settlement konsinyasi
     */
    public function ajax_view_journal()
    {
        $idSettlement = (int) $this->input->get('id_settlement');
        $settlement = $this->M_Konsinyasi->get_settlement_by_id($idSettlement);

        if (!$settlement) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Data settlement tidak ditemukan.']));
            return;
        }

        // Cari jurnal dari id_jurnal_pembelian atau dari source_id / source_no
        $jurnal = null;
        if (!empty($settlement['id_jurnal_pembelian'])) {
            $jurnal = $this->db
                ->where('id_jurnal', (int) $settlement['id_jurnal_pembelian'])
                ->get('tbkeu_jurnal')
                ->row_array();
        }

        if (!$jurnal) {
            $jurnal = $this->db
                ->group_start()
                    ->where('source_id', (string) $idSettlement)
                    ->where('source_type', 'CONSIGNMENT_SETTLEMENT')
                ->group_end()
                ->or_where('source_no', $settlement['no_settlement'])
                ->or_where('idempotency_key', 'CONSIGNMENT_SETTLEMENT-' . $idSettlement)
                ->order_by('id_jurnal', 'DESC')
                ->get('tbkeu_jurnal')
                ->row_array();
        }

        if (!$jurnal) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Jurnal akuntansi belum ditemukan untuk transaksi ini (mungkin belum diinput tagihan atau belum selesai diposting).'
                ]));
            return;
        }

        // Ambil detail baris debit dan kredit
        $details = $this->db
            ->select('d.*, a.kode_akun, a.nama_akun')
            ->from('tbkeu_jurnal_detail d')
            ->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left')
            ->where('d.id_jurnal', (int) $jurnal['id_jurnal'])
            ->order_by('d.nomor_baris', 'ASC')
            ->get()
            ->result_array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'     => true,
                'settlement' => $settlement,
                'header'     => $jurnal,
                'details'    => $details
            ]));
    }

    public function export_barang_laku()
    {
        $this->export_settlement_excel('LAKU');
    }

    public function export_riwayat_lunas()
    {
        $this->export_settlement_excel('BILLED');
    }

    private function export_settlement_excel($status)
    {
        $status = strtoupper((string)$status) === 'BILLED' ? 'BILLED' : 'LAKU';
        $filters = [
            'status'      => $status,
            'kd_suplier' => trim((string)($this->input->get('kd_suplier', true) ?: 'SEMUA')),
            'date_from'   => trim((string)$this->input->get('date_from', true)),
            'date_to'     => trim((string)$this->input->get('date_to', true)),
            'search'      => trim((string)$this->input->get('search', true)),
        ];
        $rows = $this->M_Konsinyasi->get_settlement_list($filters);

        if ($status === 'LAKU') {
            $rows = array_values(array_filter($rows, static function ($row) {
                return (float)($row['qty_net'] ?? 0) > 0;
            }));
        }

        require_once APPPATH . 'libraries/PhpSpreadsheet.php';
        $spreadsheetLib = new PhpSpreadsheetLib();
        $spreadsheet = $spreadsheetLib->spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $isBilled = $status === 'BILLED';
        $title = $isBilled ? 'Riwayat Barang Konsinyasi Lunas' : 'Barang Konsinyasi Laku';
        $sheet->setTitle($isBilled ? 'Riwayat Lunas' : 'Barang Laku');

        $includeNoFaktur = $isBilled; // Kolom No. Faktur Kios ditiadakan untuk export Barang Laku

        $headers = [
            'No.', 'Tanggal Laku', 'No. Settlement', 'Kode Supplier', 'Nama Supplier',
            'Kode Barang', 'Nama Barang', 'Lot', 'Expired', 'No. LPB', 'No. PO',
            'Kios / Customer', 'No. SO'
        ];
        if ($includeNoFaktur) {
            $headers[] = 'No. Faktur';
        }
        $headers = array_merge($headers, [
            'Qty LPB Masuk', 'Qty Laku',
            'Satuan', 'Sisa Belum Lunas', 'Harga PO', 'Nilai Tagihan',
            'Invoice Supplier', 'Tanggal Invoice', 'No. Jurnal', 'Status'
        ]);

        $totalColumns = count($headers);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $this->export_filter_description($filters));

        foreach ($headers as $columnIndex => $header) {
            $sheet->setCellValueByColumnAndRow($columnIndex + 1, 4, $header);
        }

        $excelRow = 5;
        foreach ($rows as $index => $row) {
            $values = [
                $index + 1,
                $this->export_date($row['tanggal_settlement'] ?? ''),
                $row['no_settlement'] ?? '',
                $row['kd_suplier'] ?? '',
                $row['nama_suplier'] ?? '',
                $row['kd_barang'] ?? '',
                $row['nama_barang'] ?? '',
                $row['no_lot'] ?? '',
                $this->export_date($row['expired_date'] ?? ''),
                $row['lpb_no'] ?? ($row['nomor_lpb_asal'] ?? ''),
                $row['po_no_po'] ?? '',
                $row['customer_name'] ?? '',
                $row['no_so'] ?? '',
            ];
            if ($includeNoFaktur) {
                $values[] = $row['no_faktur'] ?? '';
            }
            $values = array_merge($values, [
                (float)($row['qty_lpb_masuk'] ?? 0),
                (float)($row['qty_net'] ?? 0),
                $row['satuan'] ?? '',
                (float)($row['qty_sisa_belum_lunas'] ?? 0),
                (float)($row['po_hrg_satuan'] ?? 0),
                (float)($row['total_tagihan_beli'] ?? 0),
                $row['no_invoice_supplier'] ?? '',
                $this->export_date($row['tgl_invoice_supplier'] ?? ''),
                $row['nomor_jurnal'] ?? '',
                $row['status'] ?? $status,
            ]);

            foreach ($values as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $excelRow, $value);
            }
            $excelRow++;
        }

        $lastRow = max(4, $excelRow - 1);
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $isBilled ? '15803D' : 'D97706']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getStyle("A4:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        if ($lastRow >= 5) {
            $qtyLpbCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(array_search('Qty LPB Masuk', $headers) + 1);
            $qtyLakuCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(array_search('Qty Laku', $headers) + 1);
            $sisaCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(array_search('Sisa Belum Lunas', $headers) + 1);
            $tagihanCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(array_search('Nilai Tagihan', $headers) + 1);

            $sheet->getStyle("{$qtyLpbCol}5:{$qtyLakuCol}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("{$sisaCol}5:{$tagihanCol}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:{$lastCol}{$lastRow}");

        for ($c = 1; $c <= $totalColumns; $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        $filename = ($isBilled ? 'riwayat_barang_konsinyasi_lunas_' : 'barang_konsinyasi_laku_') . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = $spreadsheetLib->writer($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function export_filter_description(array $filters)
    {
        $parts = [];
        $parts[] = $filters['kd_suplier'] === 'SEMUA' ? 'Supplier: Semua' : 'Supplier: ' . $filters['kd_suplier'];
        if ($filters['date_from'] !== '' || $filters['date_to'] !== '') {
            $parts[] = 'Periode: ' . ($filters['date_from'] ?: 'Awal') . ' s/d ' . ($filters['date_to'] ?: 'Sekarang');
        }
        if ($filters['search'] !== '') {
            $parts[] = 'Pencarian: ' . $filters['search'];
        }
        $parts[] = 'Diekspor: ' . date('d/m/Y H:i');
        return implode(' | ', $parts);
    }

    private function export_date($date)
    {
        $date = trim((string)$date);
        if ($date === '' || $date === '0000-00-00') {
            return '';
        }
        $timestamp = strtotime($date);
        return $timestamp ? date('d/m/Y', $timestamp) : $date;
    }

    /**
     * Cetak Faktur Laporan Realisasi Penjualan Konsinyasi untuk dilaporkan ke Supplier
     */
    public function print_faktur($idSettlement)
    {
        $idSettlement = (int) $idSettlement;
        $settlement = $this->M_Konsinyasi->get_settlement_by_id($idSettlement);
        if (!$settlement) {
            show_404();
            return;
        }

        $fakturDetail = null;
        if (!empty($settlement['id_faktur'])) {
            $fakturDetail = $this->db
                ->where('id_faktur', (int) $settlement['id_faktur'])
                ->where('kd_barang', $settlement['kd_barang'])
                ->get('tbso_faktur_detail')
                ->row_array();
        }

        $fakturKonsinyasi = null;
        if (!empty($settlement['id_pembayaran'])) {
            $fakturKonsinyasi = $this->M_Konsinyasi->get_faktur_konsinyasi_by_payment($settlement['id_pembayaran']);
        }
        if (!$fakturKonsinyasi && !empty($settlement['id_settlement'])) {
            $fakturKonsinyasi = $this->db->get_where('tb_konsinyasi_faktur', ['id_settlement' => (int) $settlement['id_settlement']])->row_array();
        }
        if (!$fakturKonsinyasi && !empty($settlement['no_faktur'])) {
            $fakturKonsinyasi = $this->M_Konsinyasi->get_faktur_konsinyasi_by_no($settlement['no_faktur']);
        }

        $noFakturKonsinyasi = !empty($fakturKonsinyasi['no_faktur_konsinyasi'])
            ? $fakturKonsinyasi['no_faktur_konsinyasi']
            : ($settlement['no_faktur'] ?? $settlement['no_settlement']);

        $settlement['no_faktur_konsinyasi'] = $noFakturKonsinyasi;
        $data['page_title']           = 'Faktur Realisasi Penjualan Konsinyasi - ' . $noFakturKonsinyasi;
        $data['no_faktur_konsinyasi'] = $noFakturKonsinyasi;
        $data['faktur_konsinyasi']    = $fakturKonsinyasi;
        $data['settlement']           = $settlement;
        $data['faktur_detail']        = $fakturDetail;

        $this->load->view('content/purchasing/konsinyasi/faktur_konsinyasi_print.php', $data);
    }
}


