<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_ImportLpb extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Memuat model M_ImportLpb
        $this->load->model('M_ImportLpb');
        // Set zona waktu ke Asia/Jakarta
        date_default_timezone_set('Asia/Jakarta');
    }

    /**
     * Mengecek akses pengguna untuk fitur Import LPB
     * 
     * @return bool
     */
    private function can_access_import_lpb() {
        $department = strtoupper($this->session->userdata('departemen'));
        $level = $this->session->userdata('lv');
        
        $allowed_departments = ['PURCHASING', 'ADMIN ERP', 'ADMLPB', 'ADMINLOGLPB'];
        
        if (in_array($department, $allowed_departments) || $level == 1) {
            return true;
        }
        
        return false;
    }

    /**
     * Halaman utama untuk Import Excel Purchasing LPB
     */
    public function index() {
        // Cek akses pengguna
        if (!$this->can_access_import_lpb()) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
            return;
        }

        $data['page_title'] = 'KARISMA - Import Excel Purchasing LPB';

        // Memuat tampilan
        $this->load->view('partial/main/header', $data);
        $this->load->view('content/logistik/ics/import_lpb', $data);
        $this->load->view('partial/main/footer');
    }

    /**
     * Menangani proses unggah file Excel
     */
    public function upload() {
        // Hanya menerima metode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(405)
                ->set_output(json_encode(['status' => false, 'message' => 'Metode request tidak diizinkan.']));
        }

        // Cek akses pengguna
        if (!$this->can_access_import_lpb()) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(403)
                ->set_output(json_encode(['status' => false, 'message' => 'Akses ditolak.']));
        }

        $upload_path = './uploads/import_lpb/';
        
        // Membuat direktori jika belum ada
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        // Konfigurasi upload library
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size'] = 5120; // 5MB
        $config['file_name'] = 'import_lpb_' . date('YmdHis');

        $this->load->library('upload', $config);

        // Proses unggah (nama field sesuai dengan FormData di frontend)
        if (!$this->upload->do_upload('file_excel')) {
            $error = $this->upload->display_errors('', '');
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => $error]));
        } else {
            $upload_data = $this->upload->data();
            $file_path = $upload_data['full_path'];

            try {
                // Mengurai file Excel yang diunggah
                $result = $this->M_ImportLpb->parse_excel($file_path);
                
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => true,
                        'data' => $result['rows'],
                        'summary' => $result['summary']
                    ]));
            } catch (Exception $e) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => false,
                        'message' => 'Gagal mengurai file Excel: ' . $e->getMessage()
                    ]));
            }
        }
    }

    /**
     * Memproses data yang sudah divalidasi ke dalam database
     */
    public function process() {
        // Hanya menerima metode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(405)
                ->set_output(json_encode(['status' => false, 'message' => 'Metode request tidak diizinkan.']));
        }

        // Cek akses pengguna
        if (!$this->can_access_import_lpb()) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(403)
                ->set_output(json_encode(['status' => false, 'message' => 'Akses ditolak.']));
        }

        // Menerima data JSON dari frontend (format: { rows: [...] })
        $json_data = file_get_contents('php://input');
        $payload = json_decode($json_data, true);

        // Mengambil array rows dari payload
        $validated_data = isset($payload['rows']) ? $payload['rows'] : $payload;

        if (empty($validated_data) || !is_array($validated_data)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Data tidak valid atau kosong.']));
        }

        $username = $this->session->userdata('username');

        try {
            // Memproses import data
            $result = $this->M_ImportLpb->process_import($validated_data, $username);
            
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => $result['status'],
                    'message' => 'Proses import selesai. ' . $result['imported'] . ' data baru, ' . $result['updated'] . ' data diperbarui, ' . $result['skipped'] . ' data dilewati.',
                    'imported' => $result['imported'],
                    'updated' => $result['updated'],
                    'skipped' => $result['skipped']
                ]));
        } catch (Exception $e) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Gagal memproses import: ' . $e->getMessage()
                ]));
        }
    }

    /**
     * Mengunduh file template Excel untuk Import LPB
     */
    public function download_template() {
        require_once APPPATH . 'libraries/PhpSpreadsheet.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Menyiapkan header kolom
        $headers = [
            'A1' => 'no_lpb',
            'B1' => 'no_po',
            'C1' => 'tgl_lpb',
            'D1' => 'id_supplier',
            'E1' => 'nama_supplier',
            'F1' => 'no_sj_supplier',
            'G1' => 'no_invoice',
            'H1' => 'no_faktur_pajak',
            'I1' => 'dpp',
            'J1' => 'ppn',
            'K1' => 'grand_total',
            'L1' => 'status_lpb'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Menerapkan styling pada header
        $header_style = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF000080'], // Biru Navy
            ]
        ];

        $sheet->getStyle('A1:L1')->applyFromArray($header_style);

        // Menambahkan satu baris data contoh
        $sample_data = [
            'LPB-231015-001',
            'PO/23/10/0001',
            '2023-10-15',
            'SUP001',
            'PT Supplier Contoh',
            'SJ-12345',
            'INV-98765',
            '010.000-23.00000001',
            1000000,
            110000,
            1110000,
            'POSTED'
        ];

        $column = 'A';
        foreach ($sample_data as $value) {
            $sheet->setCellValue($column . '2', $value);
            $column++;
        }

        // Set ukuran kolom agar otomatis
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Menyimpan dan mengunduh file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'template_import_lpb.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
