<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Data_customers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Cek session login
        if (!$this->session->userdata('id')) {
            redirect('auth');
        }
    }

    public function index()
    {
        $data['page_title'] = 'KARISMA ERP - Data Customers';

        // Hitung total customer secara cepat tanpa memuat seluruh baris
        $data['total_customers'] = (int)$this->db->count_all('tb_customer');

        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');
        $collectionSelect = $hasCollectionCol ? "COALESCE(nama_collection, '') AS nama_collection," : "'' AS nama_collection,";

        // Initial load dibatasi 50 data pertama agar halaman terbuka instan dan ringan
        $data['customers'] = $this->db->query("
            SELECT
                id,
                COALESCE(kd_customer, '') AS kd_customer,
                COALESCE(nama_customer, '') AS nama_customer,
                COALESCE(nama_kios, '') AS nama_kios,
                COALESCE(nama_sales, '') AS nama_sales,
                {$collectionSelect}
                COALESCE(kd_rute, '') AS kd_rute,
                COALESCE(telp1, '') AS telp1
            FROM tb_customer
            ORDER BY
                CASE WHEN COALESCE(nama_kios, '') = '' THEN 1 ELSE 0 END,
                COALESCE(nama_kios, nama_customer, '') ASC
            LIMIT 50
        ")->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/data_customers/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Endpoint AJAX untuk search dan pagination (infinite scroll) data customer
     */
    public function get_list()
    {
        $search = trim((string)$this->input->get('q'));
        $offset = max(0, (int)$this->input->get('offset'));
        $limit  = min(100, max(10, (int)($this->input->get('limit') ?: 50)));

        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');
        $collectionSelect = $hasCollectionCol ? "COALESCE(nama_collection, '') AS nama_collection," : "'' AS nama_collection,";

        $this->db->select("
            id,
            COALESCE(kd_customer, '') AS kd_customer,
            COALESCE(nama_customer, '') AS nama_customer,
            COALESCE(nama_kios, '') AS nama_kios,
            COALESCE(nama_sales, '') AS nama_sales,
            {$collectionSelect}
            COALESCE(kd_rute, '') AS kd_rute,
            COALESCE(telp1, '') AS telp1
        ");
        $this->db->from('tb_customer');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('nama_kios', $search);
            $this->db->or_like('nama_customer', $search);
            $this->db->or_like('kd_customer', $search);
            $this->db->or_like('kd_rute', $search);
            $this->db->or_like('nama_sales', $search);
            if ($hasCollectionCol) {
                $this->db->or_like('nama_collection', $search);
            }
            $this->db->or_like('telp1', $search);
            $this->db->group_end();
        }

        $this->db->order_by("CASE WHEN COALESCE(nama_kios, '') = '' THEN 1 ELSE 0 END", "ASC", false);
        $this->db->order_by("COALESCE(nama_kios, nama_customer, '')", "ASC", false);
        $this->db->limit($limit, $offset);

        $data = $this->db->get()->result();

        echo json_encode([
            'status'   => 'success',
            'data'     => $data,
            'count'    => count($data),
            'offset'   => $offset,
            'limit'    => $limit,
            'has_more' => count($data) >= $limit
        ]);
    }

    public function get_detail()
    {
        $id = (int)$this->input->get('id');
        if (!$id) {
            echo json_encode(null);
            return;
        }

        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');
        $collectionSelect = $hasCollectionCol ? "COALESCE(nama_collection, '') AS nama_collection," : "'' AS nama_collection,";

        $row = $this->db->query("
            SELECT
                id,
                COALESCE(kd_customer, '') AS kd_customer,
                COALESCE(nama_customer, '') AS nama_customer,
                COALESCE(nama_kios, '') AS nama_kios,
                COALESCE(nama_sales, '') AS nama_sales,
                {$collectionSelect}
                COALESCE(kd_rute, '') AS kd_rute,
                COALESCE(telp1, '') AS telp1,
                COALESCE(telp2, '') AS telp2,
                COALESCE(regional, '') AS regional,
                COALESCE(jam_buka_tutup, '') AS jam_buka_tutup,
                COALESCE(karakteristik_kios, '') AS karakteristik_kios,
                COALESCE(alamat_kios, '') AS alamat_kios,
                COALESCE(plafon_aktif, 0) AS plafon_aktif
            FROM tb_customer
            WHERE id = ?
            LIMIT 1
        ", [$id])->row();

        echo json_encode($row);
    }

    public function store()
    {
        $id = (int)$this->input->post('id');

        // Bersihkan format angka ribuan sebelum simpan
        $plafonRaw = str_replace(['.', ','], ['', '.'], (string)$this->input->post('plafon_aktif'));
        $plafon    = is_numeric($plafonRaw) ? (float)$plafonRaw : 0;

        $data = [
            'kd_customer'        => $this->input->post('kd_customer'),
            'nama_kios'          => $this->input->post('nama_kios'),
            'nama_sales'         => $this->input->post('nama_sales'),
            'nama_customer'      => $this->input->post('nama_customer'),
            'kd_rute'            => $this->input->post('kd_rute'),
            'telp1'              => $this->input->post('telp1'),
            'telp2'              => $this->input->post('telp2'),
            'plafon_aktif'       => $plafon,
            'alamat_kios'        => $this->input->post('alamat_kios'),
            'regional'           => $this->input->post('regional'),
            'jam_buka_tutup'     => $this->input->post('jam_buka_tutup'),
            'karakteristik_kios' => $this->input->post('karakteristik_kios'),
        ];

        if ($this->db->field_exists('nama_collection', 'tb_customer')) {
            $data['nama_collection'] = $this->input->post('nama_collection');
        }

        if ($id > 0) {
            $ok = $this->db->where('id', $id)->update('tb_customer', $data);
        } else {
            $ok = $this->db->insert('tb_customer', $data);
        }

        echo json_encode(['status' => $ok ? 'success' : 'error']);
    }

    public function delete()
    {
        $id = (int)$this->input->post('id');
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
            return;
        }
        $ok = $this->db->where('id', $id)->delete('tb_customer');
        echo json_encode(['status' => $ok ? 'success' : 'error']);
    }

    /**
     * Ambil daftar unik Sales, TC (Collection), dan Rute untuk filter template export.
     */
    public function get_filter_options()
    {
        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');

        $salesRows = $this->db->query("
            SELECT DISTINCT TRIM(nama_sales) AS val
            FROM tb_customer
            WHERE COALESCE(TRIM(nama_sales), '') <> ''
            ORDER BY val ASC
        ")->result_array();

        $collectionRows = [];
        if ($hasCollectionCol) {
            $collectionRows = $this->db->query("
                SELECT DISTINCT TRIM(nama_collection) AS val
                FROM tb_customer
                WHERE COALESCE(TRIM(nama_collection), '') <> ''
                ORDER BY val ASC
            ")->result_array();
        }

        $ruteRows = $this->db->query("
            SELECT DISTINCT TRIM(kd_rute) AS val
            FROM tb_customer
            WHERE COALESCE(TRIM(kd_rute), '') <> ''
            ORDER BY val ASC
        ")->result_array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'     => 'success',
                'sales'      => array_column($salesRows, 'val'),
                'collection' => array_column($collectionRows, 'val'),
                'rute'       => array_column($ruteRows, 'val'),
            ]));
    }

    /**
     * Normalisasi parameter filter multi-value (array atau string) menjadi array bersih.
     */
    private function _normalize_filter_values($raw)
    {
        if (is_array($raw)) {
            $vals = $raw;
        } else {
            $str = trim((string)$raw);
            $vals = ($str === '') ? [] : explode(',', $str);
        }

        $clean = [];
        foreach ($vals as $v) {
            $v = trim((string)$v);
            if ($v !== '' && $v !== '__ALL__') {
                $clean[] = $v;
            }
        }
        return array_values(array_unique($clean));
    }

    /**
     * Terapkan filter Sales (mendukung multi-pilih), TC (Collection), dan Rute pada query tb_customer.
     */
    private function _apply_bulk_filter($filterSalesList, $filterCollection, $filterRute, $hasCollectionCol)
    {
        if (!empty($filterSalesList)) {
            $hasEmpty   = in_array('__EMPTY__', $filterSalesList, true);
            $salesNames = array_values(array_diff($filterSalesList, ['__EMPTY__']));

            $this->db->group_start();
            if (!empty($salesNames)) {
                $escapedNames = implode(', ', array_map(function ($name) {
                    return $this->db->escape($name);
                }, $salesNames));
                $this->db->where("TRIM(nama_sales) IN ({$escapedNames})", null, false);
                if ($hasEmpty) {
                    $this->db->or_where("COALESCE(TRIM(nama_sales), '') =", '');
                }
            } elseif ($hasEmpty) {
                $this->db->where("COALESCE(TRIM(nama_sales), '') =", '');
            }
            $this->db->group_end();
        }

        if ($hasCollectionCol) {
            if ($filterCollection === '__EMPTY__') {
                $this->db->where("COALESCE(TRIM(nama_collection), '') =", '');
            } elseif ($filterCollection !== '') {
                $this->db->where('TRIM(nama_collection) =', $filterCollection);
            }
        }

        if ($filterRute === '__EMPTY__') {
            $this->db->where("COALESCE(TRIM(kd_rute), '') =", '');
        } elseif ($filterRute !== '') {
            $this->db->where('TRIM(kd_rute) =', $filterRute);
        }
    }

    /**
     * Hitung jumlah customer yang akan di-export sesuai filter pilihan user.
     */
    public function count_export_targets()
    {
        $salesAll         = (string)$this->input->get('sales_all') === '1';
        $filterSalesList  = $salesAll ? [] : $this->_normalize_filter_values($this->input->get('sales'));
        $filterCollection = trim((string)$this->input->get('collection'));
        $filterRute       = trim((string)$this->input->get('rute'));
        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');

        if (!$salesAll && empty($filterSalesList)) {
            $this->_json_response([
                'status' => 'success',
                'total'  => 0,
            ]);
            return;
        }

        $this->db->from('tb_customer');
        $this->_apply_bulk_filter($filterSalesList, $filterCollection, $filterRute, $hasCollectionCol);
        $total = (int)$this->db->count_all_results();

        $this->_json_response([
            'status' => 'success',
            'total'  => $total,
        ]);
    }

    /**
     * Export template Excel (.xlsx) untuk pergantian massal Sales dan TC (Collection).
     */
    public function export_sales_tc_template()
    {
        if (!file_exists(FCPATH . 'vendor/autoload.php')) {
            show_error('Library PhpSpreadsheet tidak ditemukan.', 500);
            return;
        }
        require_once FCPATH . 'vendor/autoload.php';

        $salesAll         = (string)$this->input->get('sales_all') === '1';
        $filterSalesList  = $salesAll ? [] : $this->_normalize_filter_values($this->input->get('sales'));
        $filterCollection = trim((string)$this->input->get('collection'));
        $filterRute       = trim((string)$this->input->get('rute'));
        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');

        $this->db->select("
            id,
            COALESCE(kd_customer, '') AS kd_customer,
            COALESCE(nama_kios, '') AS nama_kios,
            COALESCE(nama_customer, '') AS nama_customer,
            COALESCE(kd_rute, '') AS kd_rute,
            COALESCE(regional, '') AS regional,
            COALESCE(nama_sales, '') AS nama_sales,
            " . ($hasCollectionCol ? "COALESCE(nama_collection, '') AS nama_collection" : "'' AS nama_collection") . "
        ", false);
        $this->db->from('tb_customer');
        $this->_apply_bulk_filter($filterSalesList, $filterCollection, $filterRute, $hasCollectionCol);

        $this->db->order_by("CASE WHEN COALESCE(nama_kios, '') = '' THEN 1 ELSE 0 END", 'ASC', false);
        $this->db->order_by("COALESCE(nama_kios, nama_customer, '')", 'ASC', false);
        $rows = $this->db->get()->result_array();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Sales & TC');

        // Header kolom
        $headers = [
            'A1' => 'ID',
            'B1' => 'Kode Customer',
            'C1' => 'Nama Kios',
            'D1' => 'Nama Customer',
            'E1' => 'Rute',
            'F1' => 'Regional',
            'G1' => 'Sales Saat Ini',
            'H1' => 'TC / Collection Saat Ini',
            'I1' => 'Sales Baru (Isi jika ganti)',
            'J1' => 'TC / Collection Baru (Isi jika ganti)',
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Styling header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('I1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '111827']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FDE047'],
            ],
            'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Isi data customer
        $rowIdx = 2;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $rowIdx, (int)$r['id']);
            $sheet->setCellValueExplicit('B' . $rowIdx, (string)$r['kd_customer'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowIdx, (string)$r['nama_kios']);
            $sheet->setCellValue('D' . $rowIdx, (string)$r['nama_customer']);
            $sheet->setCellValue('E' . $rowIdx, (string)$r['kd_rute']);
            $sheet->setCellValue('F' . $rowIdx, (string)$r['regional']);
            $sheet->setCellValue('G' . $rowIdx, (string)$r['nama_sales']);
            $sheet->setCellValue('H' . $rowIdx, (string)$r['nama_collection']);
            $sheet->setCellValue('I' . $rowIdx, '');
            $sheet->setCellValue('J' . $rowIdx, '');
            $rowIdx++;
        }

        if ($rowIdx > 2) {
            $sheet->getStyle('I2:J' . ($rowIdx - 1))->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FEF9C3'],
                ],
            ]);
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        // Sheet 2: Referensi Nama Sales & TC + Petunjuk Pengisian
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi & Petunjuk');
        $refSheet->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE PERGANTIAN SALES & TC');
        $refSheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $refSheet->setCellValue('A2', '1. Jangan mengubah kolom ID dan Kode Customer.');
        $refSheet->setCellValue('A3', '2. Isi kolom I (Sales Baru) dan/atau kolom J (TC / Collection Baru) hanya pada baris customer yang ingin diganti.');
        $refSheet->setCellValue('A4', '3. Jika kolom I atau J dibiarkan kosong, maka Sales / TC lama tetap dipertahankan (tidak berubah).');
        $refSheet->setCellValue('A5', '4. Ketik tanda minus (-) jika ingin mengosongkan/menghapus Sales atau TC pada customer tertentu.');
        $refSheet->setCellValue('A6', '5. Upload kembali file ini melalui tombol Import Sales & TC untuk melihat preview sebelum disimpan.');

        $refSheet->setCellValue('A8', 'Daftar Sales Terdaftar');
        $refSheet->setCellValue('B8', 'Daftar TC / Collection Terdaftar');
        $refSheet->getStyle('A8:B8')->getFont()->setBold(true);

        $salesList = $this->db->query("SELECT DISTINCT TRIM(nama_sales) AS val FROM tb_customer WHERE COALESCE(TRIM(nama_sales), '') <> '' ORDER BY val ASC")->result_array();
        $collList  = $hasCollectionCol ? $this->db->query("SELECT DISTINCT TRIM(nama_collection) AS val FROM tb_customer WHERE COALESCE(TRIM(nama_collection), '') <> '' ORDER BY val ASC")->result_array() : [];

        $maxRef = max(count($salesList), count($collList));
        for ($i = 0; $i < $maxRef; $i++) {
            if (isset($salesList[$i])) {
                $refSheet->setCellValue('A' . (9 + $i), $salesList[$i]['val']);
            }
            if (isset($collList[$i])) {
                $refSheet->setCellValue('B' . (9 + $i), $collList[$i]['val']);
            }
        }
        $refSheet->getColumnDimension('A')->setAutoSize(true);
        $refSheet->getColumnDimension('B')->setAutoSize(true);

        $spreadsheet->setActiveSheetIndex(0);

        $nameParts = ['Template'];
        if (!empty($filterSalesList)) {
            if (count($filterSalesList) <= 2) {
                $slugs = array_map(function ($s) {
                    return $s === '__EMPTY__' ? 'Kosong' : preg_replace('/[^A-Za-z0-9_-]+/', '_', $s);
                }, $filterSalesList);
                $nameParts[] = 'Sales_' . implode('_', $slugs);
            } else {
                $nameParts[] = count($filterSalesList) . '_Sales';
            }
        }
        if ($filterCollection !== '') {
            $nameParts[] = 'TC_' . ($filterCollection === '__EMPTY__' ? 'Kosong' : preg_replace('/[^A-Za-z0-9_-]+/', '_', $filterCollection));
        }
        if ($filterRute !== '') {
            $nameParts[] = 'Rute_' . ($filterRute === '__EMPTY__' ? 'Kosong' : preg_replace('/[^A-Za-z0-9_-]+/', '_', $filterRute));
        }
        if (count($nameParts) === 1) {
            $nameParts[] = 'Semua_Sales_TC';
        }
        $filename = implode('_', $nameParts) . '_' . date('Ymd_His') . '.xlsx';
        if (ob_get_level() > 0) {
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
     * Preview hasil pembacaan file Excel/CSV sebelum update massal Sales & TC diterapkan.
     */
    public function import_sales_tc_preview()
    {
        if (empty($_FILES['file_excel']['tmp_name'])) {
            $this->_json_response(['status' => 'error', 'message' => 'File Excel belum dipilih.']);
            return;
        }

        if (!file_exists(FCPATH . 'vendor/autoload.php')) {
            $this->_json_response(['status' => 'error', 'message' => 'Library PhpSpreadsheet tidak ditemukan.']);
            return;
        }
        require_once FCPATH . 'vendor/autoload.php';

        $tmpPath = $_FILES['file_excel']['tmp_name'];
        $ext     = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            $this->_json_response(['status' => 'error', 'message' => 'Format file harus .xlsx, .xls, atau .csv.']);
            return;
        }

        try {
            $prevErr     = error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
            $sheet       = $spreadsheet->getSheet(0);
            $rows        = $sheet->toArray(null, true, true, false);
            error_reporting($prevErr);
        } catch (\Throwable $e) {
            $this->_json_response(['status' => 'error', 'message' => 'Gagal membaca file: ' . $e->getMessage()]);
            return;
        }

        if (count($rows) < 2) {
            $this->_json_response(['status' => 'error', 'message' => 'File kosong atau tidak memiliki baris data.']);
            return;
        }

        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');

        // Muat peta customer berdasarkan ID & Kode Customer agar pencarian cepat O(1)
        $dbCustomers = $this->db->select("id, kd_customer, nama_kios, nama_customer, nama_sales" . ($hasCollectionCol ? ", nama_collection" : ""))
            ->from('tb_customer')
            ->get()
            ->result_array();

        $byId   = [];
        $byKode = [];
        foreach ($dbCustomers as $c) {
            $byId[(int)$c['id']] = $c;
            $kdKey = strtoupper(trim((string)$c['kd_customer']));
            if ($kdKey !== '') {
                $byKode[$kdKey] = $c;
            }
        }

        $preview        = [];
        $updates        = [];
        $totalRows      = 0;
        $changedCount   = 0;
        $unchangedCount = 0;
        $notFoundCount  = 0;

        // Lewati baris header (index 0)
        for ($i = 1; $i < count($rows); $i++) {
            $r = $rows[$i];
            $rawId       = trim((string)($r[0] ?? ''));
            $rawKode     = trim((string)($r[1] ?? ''));
            $colSalesCur = trim((string)($r[6] ?? ''));
            $colTcCur    = trim((string)($r[7] ?? ''));
            $newSalesRaw = trim((string)($r[8] ?? ''));
            $newTcRaw    = trim((string)($r[9] ?? ''));

            if ($rawId === '' && $rawKode === '') {
                continue;
            }
            $totalRows++;

            $cust = null;
            if ($rawId !== '' && ctype_digit($rawId) && isset($byId[(int)$rawId])) {
                $cust = $byId[(int)$rawId];
            } elseif ($rawKode !== '' && isset($byKode[strtoupper($rawKode)])) {
                $cust = $byKode[strtoupper($rawKode)];
            }

            if (!$cust) {
                $notFoundCount++;
                $preview[] = [
                    'row_num'     => $i + 1,
                    'id'          => $rawId,
                    'kd_customer' => $rawKode,
                    'nama_kios'   => (string)($r[2] ?? ''),
                    'old_sales'   => '',
                    'new_sales'   => $newSalesRaw !== '' ? $newSalesRaw : $colSalesCur,
                    'old_tc'      => '',
                    'new_tc'      => $newTcRaw !== '' ? $newTcRaw : $colTcCur,
                    'status'      => 'NOT_FOUND',
                    'note'        => 'Customer tidak ditemukan di database',
                ];
                continue;
            }

            $oldSales = trim((string)($cust['nama_sales'] ?? ''));
            $oldTc    = $hasCollectionCol ? trim((string)($cust['nama_collection'] ?? '')) : '';

            // Dukung pengisian melalui kolom "Sales Baru" (I) maupun edit langsung di kolom "Sales Saat Ini" (G)
            $effectiveSalesInput = $newSalesRaw !== '' ? $newSalesRaw : ($colSalesCur !== $oldSales ? $colSalesCur : '');
            $targetSales  = $oldSales;
            $salesChanged = false;
            if ($effectiveSalesInput !== '') {
                $resolvedSales = ($effectiveSalesInput === '-') ? '' : $effectiveSalesInput;
                if ($resolvedSales !== $oldSales) {
                    $targetSales  = $resolvedSales;
                    $salesChanged = true;
                }
            }

            // Dukung pengisian melalui kolom "TC Baru" (J) maupun edit langsung di kolom "TC Saat Ini" (H)
            $effectiveTcInput = $newTcRaw !== '' ? $newTcRaw : ($colTcCur !== $oldTc ? $colTcCur : '');
            $targetTc  = $oldTc;
            $tcChanged = false;
            if ($hasCollectionCol && $effectiveTcInput !== '') {
                $resolvedTc = ($effectiveTcInput === '-') ? '' : $effectiveTcInput;
                if ($resolvedTc !== $oldTc) {
                    $targetTc  = $resolvedTc;
                    $tcChanged = true;
                }
            }

            if (!$salesChanged && !$tcChanged) {
                $unchangedCount++;
                continue;
            }

            $changedCount++;
            $updates[] = [
                'id'              => (int)$cust['id'],
                'nama_sales'      => $salesChanged ? ($targetSales === '' ? null : $targetSales) : null,
                'update_sales'    => $salesChanged,
                'nama_collection' => $tcChanged ? ($targetTc === '' ? null : $targetTc) : null,
                'update_tc'       => $tcChanged,
            ];

            $preview[] = [
                'row_num'     => $i + 1,
                'id'          => (int)$cust['id'],
                'kd_customer' => $cust['kd_customer'],
                'nama_kios'   => $cust['nama_kios'] ?: $cust['nama_customer'],
                'old_sales'   => $oldSales,
                'new_sales'   => $salesChanged ? $targetSales : $oldSales,
                'sales_changed' => $salesChanged,
                'old_tc'      => $oldTc,
                'new_tc'      => $tcChanged ? $targetTc : $oldTc,
                'tc_changed'  => $tcChanged,
                'status'      => 'CHANGE',
                'note'        => trim(($salesChanged ? 'Sales ' : '') . ($tcChanged ? 'TC' : '')),
            ];
        }

        $this->_json_response([
            'status'          => 'success',
            'total_rows'      => $totalRows,
            'changed_count'   => $changedCount,
            'unchanged_count' => $unchangedCount,
            'not_found_count' => $notFoundCount,
            'preview'         => $preview,
            'updates'         => $updates,
        ]);
    }

    /**
     * Terapkan perubahan massal Sales dan TC (Collection) ke tb_customer.
     */
    public function import_sales_tc_apply()
    {
        $rawUpdates = $this->input->post('updates');
        $updates    = is_string($rawUpdates) ? json_decode($rawUpdates, true) : $rawUpdates;

        if (!is_array($updates) || empty($updates)) {
            $this->_json_response(['status' => 'error', 'message' => 'Tidak ada perubahan yang siap disimpan.']);
            return;
        }

        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');
        $updatedRows = 0;

        $this->db->trans_start();
        foreach ($updates as $u) {
            $id = (int)($u['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $dataUpdate = [];
            if (!empty($u['update_sales'])) {
                $dataUpdate['nama_sales'] = $u['nama_sales'];
            }
            if ($hasCollectionCol && !empty($u['update_tc'])) {
                $dataUpdate['nama_collection'] = $u['nama_collection'];
            }

            if (!empty($dataUpdate)) {
                $this->db->where('id', $id)->update('tb_customer', $dataUpdate);
                $updatedRows++;
            }
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->_json_response(['status' => 'error', 'message' => 'Gagal menyimpan perubahan ke database.']);
            return;
        }

        $this->_json_response([
            'status'       => 'success',
            'updated_rows' => $updatedRows,
            'message'      => "Berhasil memperbarui Sales / TC untuk {$updatedRows} customer.",
        ]);
    }

    private function _json_response($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }
}
