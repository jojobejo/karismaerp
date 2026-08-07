<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_ImportLpb extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Membaca dan memvalidasi file Excel
     * 
     * @param string $file_path
     * @return array
     */
    public function parse_excel($file_path) {
        require_once APPPATH . 'libraries/PhpSpreadsheet.php';
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file_path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file_path);
        $sheet = $spreadsheet->getActiveSheet();
        
        $highest_row = $sheet->getHighestRow();
        $highest_column = $sheet->getHighestColumn();
        
        $parsed_rows = [];
        $summary = [
            'total' => 0,
            'valid' => 0,
            'warning' => 0,
            'error' => 0
        ];
        
        // Membaca header
        $header = $sheet->rangeToArray('A1:' . $highest_column . '1', NULL, TRUE, FALSE)[0];
        
        // Mulai membaca dari baris kedua (data)
        for ($row = 2; $row <= $highest_row; $row++) {
            $row_data = $sheet->rangeToArray('A' . $row . ':' . $highest_column . $row, NULL, TRUE, FALSE)[0];
            
            // Melewati baris yang kosong
            if (empty(array_filter($row_data))) {
                continue;
            }
            
            $data = [
                'row_number' => $row - 1,
                'no_lpb' => isset($row_data[0]) ? trim($row_data[0]) : '',
                'no_po' => isset($row_data[1]) ? trim($row_data[1]) : '',
                'tgl_lpb' => isset($row_data[2]) ? trim($row_data[2]) : '',
                'id_supplier' => isset($row_data[3]) ? trim($row_data[3]) : '',
                'nama_supplier' => isset($row_data[4]) ? trim($row_data[4]) : '',
                'no_sj_supplier' => isset($row_data[5]) ? trim($row_data[5]) : '',
                'no_invoice' => isset($row_data[6]) ? trim($row_data[6]) : '',
                'no_faktur_pajak' => isset($row_data[7]) ? trim($row_data[7]) : '',
                'dpp' => isset($row_data[8]) ? floatval($row_data[8]) : 0,
                'ppn' => isset($row_data[9]) ? floatval($row_data[9]) : 0,
                'grand_total' => isset($row_data[10]) ? floatval($row_data[10]) : 0,
                'status_lpb' => isset($row_data[11]) ? trim($row_data[11]) : '',
            ];
            
            $status = 'valid';
            $messages = [];
            
            // ===================================================
            // TAHAP 1 - Validasi Format
            // ===================================================
            if (empty($data['no_lpb'])) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'No LPB tidak boleh kosong.'];
            } elseif (!preg_match('/^LPB-\d{6}-\d{3}$/', $data['no_lpb'])) {
                // Hanya warning untuk format yang berbeda, bukan error total
                if ($status !== 'error') $status = 'warning';
                $messages[] = ['type' => 'warning', 'text' => 'Format No LPB tidak standar (LPB-YYYYMM-NNN).'];
            }
            
            if (empty($data['no_po'])) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'No PO tidak boleh kosong.'];
            }
            
            // Validasi tanggal LPB (format dan <= hari ini)
            if (!empty($data['tgl_lpb'])) {
                // Periksa apakah format serial number Excel
                if (is_numeric($data['tgl_lpb'])) {
                    $unix_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($data['tgl_lpb']);
                    $data['tgl_lpb'] = date('Y-m-d', $unix_date);
                }
                
                $d = DateTime::createFromFormat('Y-m-d', $data['tgl_lpb']);
                if ($d && $d->format('Y-m-d') === $data['tgl_lpb']) {
                    if (strtotime($data['tgl_lpb']) > strtotime(date('Y-m-d'))) {
                        $status = 'error';
                        $messages[] = ['type' => 'error', 'text' => 'Tanggal LPB tidak boleh melebihi hari ini.'];
                    }
                } else {
                    $status = 'error';
                    $messages[] = ['type' => 'error', 'text' => 'Format Tanggal LPB tidak valid (gunakan YYYY-MM-DD).'];
                }
            } else {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'Tanggal LPB tidak boleh kosong.'];
            }
            
            if (empty($data['id_supplier'])) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'ID Supplier tidak boleh kosong.'];
            }
            if (empty($data['nama_supplier'])) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'Nama Supplier tidak boleh kosong.'];
            }
            if (empty($data['no_sj_supplier'])) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'No SJ Supplier tidak boleh kosong.'];
            }
            if ($data['dpp'] < 0) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'DPP tidak boleh negatif.'];
            }
            if ($data['ppn'] < 0) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'PPN tidak boleh negatif.'];
            }
            if ($data['grand_total'] <= 0) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'Grand Total harus lebih dari 0.'];
            }
            
            $allowed_statuses = ['UNPOST', 'POSTED', 'VOID'];
            $data['status_lpb'] = strtoupper($data['status_lpb']);
            if (!in_array($data['status_lpb'], $allowed_statuses)) {
                $status = 'error';
                $messages[] = ['type' => 'error', 'text' => 'Status LPB tidak valid (harus UNPOST/POSTED/VOID).'];
            }
            
            // ===================================================
            // TAHAP 2 - Validasi Referensi Master Data
            // ===================================================
            if (!empty($data['no_po'])) {
                $po_query = $this->db->query("SELECT id_po, kd_po, kd_suplier FROM tbpo_po WHERE no_po = ?", [$data['no_po']]);
                $po_data = $po_query->row();
                if (!$po_data) {
                    $status = 'error';
                    $messages[] = ['type' => 'error', 'text' => 'No PO "' . $data['no_po'] . '" tidak ditemukan di database.'];
                }
            } else {
                $po_data = null;
            }
            
            if (!empty($data['id_supplier'])) {
                $sup_query = $this->db->query("SELECT kd_suplier, nama_suplier FROM tbpo_suplier WHERE kd_suplier = ?", [$data['id_supplier']]);
                $sup_data = $sup_query->row();
                if (!$sup_data) {
                    // Coba pencarian berdasarkan nama supplier sebagai fallback
                    $sup_query2 = $this->db->query("SELECT kd_suplier, nama_suplier FROM tbpo_suplier WHERE nama_suplier LIKE ?", ['%' . $data['nama_supplier'] . '%']);
                    $sup_data2 = $sup_query2->row();
                    if ($sup_data2) {
                        if ($status !== 'error') $status = 'warning';
                        $messages[] = ['type' => 'warning', 'text' => 'ID Supplier tidak ditemukan, namun nama supplier cocok dengan "' . $sup_data2->nama_suplier . '" (kd: ' . $sup_data2->kd_suplier . ').'];
                        $data['id_supplier_resolved'] = $sup_data2->kd_suplier;
                    } else {
                        $status = 'error';
                        $messages[] = ['type' => 'error', 'text' => 'Supplier "' . $data['id_supplier'] . '" tidak ditemukan di database.'];
                    }
                }
            } else {
                $sup_data = null;
            }
            
            // Cross-check PO supplier match
            if (isset($po_data) && $po_data && isset($sup_data) && $sup_data) {
                if ($po_data->kd_suplier != $sup_data->kd_suplier) {
                    if ($status !== 'error') $status = 'warning';
                    $messages[] = ['type' => 'warning', 'text' => 'Supplier pada PO (' . $po_data->kd_suplier . ') berbeda dengan ID Supplier pada baris ini (' . $data['id_supplier'] . ').'];
                }
            }
            
            // Cek duplikat LPB di database
            if (!empty($data['no_lpb'])) {
                $lpb_check = $this->db->query("SELECT id_lpb, status_lpb FROM tb_lpb WHERE nomor_lpb = ?", [$data['no_lpb']]);
                if ($lpb_check->row()) {
                    $existing_status = $this->map_status_lpb_reverse($lpb_check->row()->status_lpb);
                    $messages[] = ['type' => 'info', 'text' => 'LPB sudah ada di database (status: ' . $existing_status . '). Data akan di-UPDATE.'];
                } else {
                    $messages[] = ['type' => 'info', 'text' => 'LPB baru, akan di-INSERT ke database.'];
                }
            }
            
            // ===================================================
            // TAHAP 3 - Validasi Kalkulasi Keuangan
            // ===================================================
            $calc_total = $data['dpp'] + $data['ppn'];
            if (abs($data['grand_total'] - $calc_total) > 1) {
                if ($status !== 'error') $status = 'warning';
                $selisih = $data['grand_total'] - $calc_total;
                $messages[] = ['type' => 'warning', 'text' => 'Grand Total tidak sama dengan DPP + PPN (selisih: Rp ' . number_format($selisih, 0, ',', '.') . ').'];
            }
            
            if ($data['ppn'] != 0) {
                $calc_ppn = round($data['dpp'] * 0.11, 2);
                if (abs($data['ppn'] - $calc_ppn) > 1) {
                    if ($status !== 'error') $status = 'warning';
                    $messages[] = ['type' => 'warning', 'text' => 'Nilai PPN (' . number_format($data['ppn'], 0, ',', '.') . ') tidak sesuai dengan 11% DPP (' . number_format($calc_ppn, 0, ',', '.') . ').'];
                }
            }
            
            // Menyusun hasil baris dengan struktur flat untuk frontend
            $data['validation_status'] = $status;
            $data['messages'] = $messages;
            $parsed_rows[] = $data;
            
            // Menambah ringkasan
            $summary['total']++;
            $summary[$status]++;
        }
        
        return [
            'rows' => $parsed_rows,
            'summary' => $summary
        ];
    }

    /**
     * Memproses data import ke dalam database
     * 
     * @param array $data Data yang divalidasi
     * @param string $username Username pengguna yang melakukan import
     * @return array Status dan perhitungan
     */
    public function process_import($data, $username) {
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        
        $this->db->trans_start(); // Memulai transaksi database
        
        foreach ($data as $item) {
            $row = $item['row_data'] ?? $item; // Handle struktur data array
            
            // Cek apakah LPB sudah ada
            $lpb_query = $this->db->query("SELECT id_lpb, status_lpb, no_invoice, kode_faktur_pajak FROM tb_lpb WHERE nomor_lpb = ?", [$row['no_lpb']]);
            $existing_lpb = $lpb_query->row();
            
            $mapped_status = $this->map_status_lpb($row['status_lpb']);
            
            if ($existing_lpb) {
                // Skenario A - Update Administrasi
                $update_data = [
                    'no_invoice' => $row['no_invoice'],
                    'kode_faktur_pajak' => $row['no_faktur_pajak'],
                    'status_lpb' => $mapped_status
                ];
                
                $this->db->where('nomor_lpb', $row['no_lpb']);
                $this->db->update('tb_lpb', $update_data);
                
                // Menyimpan log pembaruan
                $log_data = [
                    'nomor_lpb' => $row['no_lpb'],
                    'action_type' => 'IMPORT_UPDATE',
                    'data_sebelum' => json_encode($existing_lpb),
                    'data_sesudah' => json_encode($update_data),
                    'dilakukan_oleh' => $username,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('tb_lpb_log', $log_data);
                
                $updated++;
            } else {
                // Skenario B - Insert Baru
                $po_query = $this->db->query("SELECT kd_po FROM tbpo_po WHERE no_po = ?", [$row['no_po']]);
                $po_data = $po_query->row();
                
                if ($po_data) {
                    $insert_data = [
                        'kd_po' => $po_data->kd_po,
                        'no_po' => $row['no_po'],
                        'nosj' => $row['no_sj_supplier'],
                        'tgl_sj' => $row['tgl_lpb'],
                        'no_invoice' => $row['no_invoice'],
                        'kode_faktur_pajak' => $row['no_faktur_pajak'],
                        'nomor_lpb' => $row['no_lpb'],
                        'status_lpb' => $mapped_status,
                        'source_type' => 'PO',
                        'input_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->db->insert('tb_lpb', $insert_data);
                    
                    // Menyimpan log pembuatan baru
                    $log_data = [
                        'nomor_lpb' => $row['no_lpb'],
                        'action_type' => 'IMPORT_NEW',
                        'data_sebelum' => null,
                        'data_sesudah' => json_encode($insert_data),
                        'dilakukan_oleh' => $username,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('tb_lpb_log', $log_data);
                    
                    $imported++;
                } else {
                    $skipped++;
                }
            }
        }
        
        $this->db->trans_complete(); // Menyelesaikan transaksi database
        
        $status = $this->db->trans_status();
        
        return [
            'status' => $status,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped
        ];
    }

    /**
     * Memetakan status string ke integer
     * 
     * @param string $status_string
     * @return int
     */
    private function map_status_lpb($status_string) {
        $status_string = strtoupper($status_string);
        switch ($status_string) {
            case 'POSTED':
                return 2;
            case 'VOID':
                return 0;
            case 'UNPOST':
            default:
                return 1;
        }
    }

    /**
     * Memetakan status integer ke string
     * 
     * @param int $status_int
     * @return string
     */
    private function map_status_lpb_reverse($status_int) {
        switch ($status_int) {
            case 2:
                return 'POSTED';
            case 0:
                return 'VOID';
            case 1:
            default:
                return 'UNPOST';
        }
    }
}
