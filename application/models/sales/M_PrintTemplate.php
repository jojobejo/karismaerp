<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model pengelola template cetak faktur LX-310 (Visual Template Designer).
 */
class M_PrintTemplate extends CI_Model
{
    private $table = 'tb_faktur_print_template';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema_and_seed();
    }

    /**
     * Memastikan tabel template tersedia dan memiliki minimal satu template default.
     */
    public function ensure_schema_and_seed()
    {
        if (!$this->db->table_exists($this->table)) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id_template` INT(11) NOT NULL AUTO_INCREMENT,
                `kode_template` VARCHAR(64) NOT NULL,
                `nama_template` VARCHAR(120) NOT NULL,
                `paper_width_mm` DECIMAL(8,2) NOT NULL DEFAULT 210.00,
                `paper_height_mm` DECIMAL(8,2) NOT NULL DEFAULT 140.00,
                `margin_top_mm` DECIMAL(8,2) NOT NULL DEFAULT 12.00,
                `margin_right_mm` DECIMAL(8,2) NOT NULL DEFAULT 1.00,
                `margin_bottom_mm` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
                `margin_left_mm` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
                `bands_json` TEXT NOT NULL,
                `layout_json` LONGTEXT NOT NULL,
                `is_default` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_template`),
                UNIQUE KEY `uniq_kode_template` (`kode_template`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->query($sql);
        }

        $count = (int) $this->db->count_all($this->table);
        if ($count === 0) {
            $default = $this->default_template_payload();
            $this->db->insert($this->table, [
                'kode_template'   => 'ttb_2022_q_lot_pajak',
                'nama_template'   => $default['nama_template'],
                'paper_width_mm'  => $default['paper_width_mm'],
                'paper_height_mm' => $default['paper_height_mm'],
                'margin_top_mm'   => $default['margin_top_mm'],
                'margin_right_mm' => $default['margin_right_mm'],
                'margin_bottom_mm'=> $default['margin_bottom_mm'],
                'margin_left_mm'  => $default['margin_left_mm'],
                'bands_json'      => json_encode($default['bands']),
                'layout_json'     => json_encode($default['elements']),
                'is_default'      => 1,
            ]);
        }
    }

    /**
     * Mengembalikan konfigurasi bawaan sesuai file FastReport TTB 2022 Q LOT PAJAK 1.11.fr3.
     */
    public function default_template_payload()
    {
        return [
            'nama_template'   => 'TTB 2022 Q LOT PAJAK 1.11',
            'paper_width_mm'  => 210.00,
            'paper_height_mm' => 140.00,
            'margin_top_mm'   => 12.00,
            'margin_right_mm' => 1.00,
            'margin_bottom_mm'=> 0.00,
            'margin_left_mm'  => 0.00,
            'bands' => [
                'page_header_h' => 13.76,
                'col_header_h'  => 21.17,
                'col_header_mt' => 2.00,
                'master_row_h'  => 10.00,
                'page_footer_h' => 34.78,
            ],
            'elements' => [
                // 1. PageHeader
                [
                    'key' => 'memo32_nofaktur', 'label' => 'Nomor Faktur (NOJURNAL)', 'band' => 'page_header',
                    'left_mm' => 161.66, 'top_mm' => 4.56, 'width_mm' => 43.75, 'height_mm' => 7.14,
                    'font_size_pt' => 14.25, 'bold' => true, 'italic' => false, 'align' => 'center',
                    'border' => false, 'visible' => true, 'sample_text' => 'FKT-2501-0001', 'custom_text' => '',
                ],
                // 2. ColumnHeader
                [
                    'key' => 'memo6_tgl', 'label' => 'Tanggal Transaksi', 'band' => 'column_header',
                    'left_mm' => 2.15, 'top_mm' => 5.32, 'width_mm' => 25.43, 'height_mm' => 13.94,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'center',
                    'border' => false, 'visible' => true, 'sample_text' => '21/11/2026', 'custom_text' => '',
                ],
                [
                    'key' => 'memo50_term', 'label' => 'Term Pembayaran', 'band' => 'column_header',
                    'left_mm' => 28.84, 'top_mm' => 5.32, 'width_mm' => 26.11, 'height_mm' => 13.91,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'center',
                    'border' => false, 'visible' => true, 'sample_text' => '30 Hari', 'custom_text' => '',
                ],
                [
                    'key' => 'memo18_jtempo', 'label' => 'Tanggal Jatuh Tempo', 'band' => 'column_header',
                    'left_mm' => 57.60, 'top_mm' => 5.32, 'width_mm' => 24.11, 'height_mm' => 13.94,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'center',
                    'border' => false, 'visible' => true, 'sample_text' => '21/12/2026', 'custom_text' => '',
                ],
                [
                    'key' => 'memo8_kontak', 'label' => 'Kontak Person / Pelanggan', 'band' => 'column_header',
                    'left_mm' => 81.97, 'top_mm' => 5.32, 'width_mm' => 46.47, 'height_mm' => 14.11,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'center',
                    'border' => false, 'visible' => true, 'sample_text' => 'UD. BAROKAH TANI', 'custom_text' => '',
                ],
                [
                    'key' => 'memo38_alamat', 'label' => 'Alamat Pengantaran', 'band' => 'column_header',
                    'left_mm' => 126.15, 'top_mm' => 2.21, 'width_mm' => 81.67, 'height_mm' => 17.29,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'center',
                    'border' => false, 'visible' => true, 'sample_text' => 'Jl. Raya Pertanian No. 12, Surabaya', 'custom_text' => '',
                ],
                // 3. MasterData
                [
                    'key' => 'memo52_lineno', 'label' => 'No. Urut Baris', 'band' => 'master_data',
                    'left_mm' => 2.15, 'top_mm' => 0.21, 'width_mm' => 6.09, 'height_mm' => 5.35,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'right',
                    'border' => false, 'visible' => true, 'sample_text' => '1.', 'custom_text' => '',
                ],
                [
                    'key' => 'memo45_namabarang', 'label' => 'Nama Barang', 'band' => 'master_data',
                    'left_mm' => 11.82, 'top_mm' => 0.21, 'width_mm' => 100.00, 'height_mm' => 5.29,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'left',
                    'border' => false, 'visible' => true, 'sample_text' => 'Round Up 486 SL 12 X 1 ltr', 'custom_text' => '',
                ],
                [
                    'key' => 'item_jumlah', 'label' => 'Jumlah (Qty)', 'band' => 'master_data',
                    'left_mm' => 112.48, 'top_mm' => 0.15, 'width_mm' => 21.00, 'height_mm' => 5.26,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'right',
                    'border' => false, 'visible' => true, 'sample_text' => '10', 'custom_text' => '',
                ],
                [
                    'key' => 'item_unit', 'label' => 'Satuan (Unit)', 'band' => 'master_data',
                    'left_mm' => 134.48, 'top_mm' => 0.15, 'width_mm' => 12.00, 'height_mm' => 5.32,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'left',
                    'border' => false, 'visible' => true, 'sample_text' => 'Btl', 'custom_text' => '',
                ],
                [
                    'key' => 'memo61_harga', 'label' => 'Harga Satuan', 'band' => 'master_data',
                    'left_mm' => 151.05, 'top_mm' => 0.21, 'width_mm' => 18.79, 'height_mm' => 5.35,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'right',
                    'border' => false, 'visible' => true, 'sample_text' => '75,000', 'custom_text' => '',
                ],
                [
                    'key' => 'memo54_subtotal', 'label' => 'Subtotal Item', 'band' => 'master_data',
                    'left_mm' => 172.69, 'top_mm' => 0.21, 'width_mm' => 32.28, 'height_mm' => 5.35,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'right',
                    'border' => false, 'visible' => true, 'sample_text' => '750,000', 'custom_text' => '',
                ],
                [
                    'key' => 'item_lot', 'label' => 'Nomor Lot & Expired', 'band' => 'master_data',
                    'left_mm' => 12.00, 'top_mm' => 5.00, 'width_mm' => 106.00, 'height_mm' => 5.00,
                    'font_size_pt' => 9.75, 'bold' => false, 'italic' => false, 'align' => 'left',
                    'border' => false, 'visible' => true, 'sample_text' => 'LOT2026A - 31/12/2027', 'custom_text' => '',
                ],
                // 4. PageFooter
                [
                    'key' => 'memo2_note', 'label' => 'Catatan (Note)', 'band' => 'page_footer',
                    'left_mm' => 11.91, 'top_mm' => 0.11, 'width_mm' => 118.44, 'height_mm' => 6.50,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'left',
                    'border' => false, 'visible' => true, 'sample_text' => 'Barang dikirim sesuai pesanan', 'custom_text' => '',
                ],
                [
                    'key' => 'memo60_salesman', 'label' => 'Salesman / Kode Pelanggan', 'band' => 'page_footer',
                    'left_mm' => 11.76, 'top_mm' => 5.56, 'width_mm' => 90.78, 'height_mm' => 6.03,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'left',
                    'border' => false, 'visible' => true, 'sample_text' => 'ARASY / CUST-001', 'custom_text' => '',
                ],
                [
                    'key' => 'memo44_user', 'label' => 'User & Jam Buat', 'band' => 'page_footer',
                    'left_mm' => 91.35, 'top_mm' => 5.61, 'width_mm' => 81.60, 'height_mm' => 6.50,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'left',
                    'border' => false, 'visible' => true, 'sample_text' => 'Admin, 11.21.2026 - 10:00', 'custom_text' => '',
                ],
                [
                    'key' => 'memo25_total', 'label' => 'Total Penjualan', 'band' => 'page_footer',
                    'left_mm' => 164.81, 'top_mm' => 5.56, 'width_mm' => 40.75, 'height_mm' => 6.35,
                    'font_size_pt' => 11.25, 'bold' => true, 'italic' => false, 'align' => 'right',
                    'border' => false, 'visible' => true, 'sample_text' => '750,000', 'custom_text' => '',
                ],
                [
                    'key' => 'tgldiantar_box', 'label' => 'Kotak Tgl Diantar', 'band' => 'page_footer',
                    'left_mm' => 4.50, 'top_mm' => 13.82, 'width_mm' => 20.00, 'height_mm' => 6.00,
                    'font_size_pt' => 9.75, 'bold' => false, 'italic' => false, 'align' => 'center',
                    'border' => true, 'visible' => true, 'sample_text' => '21/11/2026', 'custom_text' => '',
                ],
                [
                    'key' => 'jabatan_box', 'label' => 'Kotak Rute / Jabatan', 'band' => 'page_footer',
                    'left_mm' => 54.00, 'top_mm' => 13.82, 'width_mm' => 20.00, 'height_mm' => 6.00,
                    'font_size_pt' => 9.75, 'bold' => false, 'italic' => false, 'align' => 'center',
                    'border' => true, 'visible' => true, 'sample_text' => 'SBY', 'custom_text' => '',
                ],
                [
                    'key' => 'memo1_disclabel', 'label' => 'Label Diskon', 'band' => 'page_footer',
                    'left_mm' => 175.15, 'top_mm' => 19.31, 'width_mm' => 12.96, 'height_mm' => 5.91,
                    'font_size_pt' => 11.25, 'bold' => true, 'italic' => false, 'align' => 'left',
                    'border' => false, 'visible' => true, 'sample_text' => 'Disc :', 'custom_text' => 'Disc :',
                ],
                [
                    'key' => 'disc_amount', 'label' => 'Nominal Diskon Final', 'band' => 'page_footer',
                    'left_mm' => 176.74, 'top_mm' => 19.40, 'width_mm' => 29.00, 'height_mm' => 5.26,
                    'font_size_pt' => 11.25, 'bold' => false, 'italic' => false, 'align' => 'right',
                    'border' => false, 'visible' => true, 'sample_text' => '0', 'custom_text' => '',
                ],
                [
                    'key' => 'memo34_berat', 'label' => 'Total Berat (Kg)', 'band' => 'page_footer',
                    'left_mm' => 75.85, 'top_mm' => 21.90, 'width_mm' => 22.49, 'height_mm' => 7.97,
                    'font_size_pt' => 12.00, 'bold' => false, 'italic' => false, 'align' => 'right',
                    'border' => false, 'visible' => true, 'sample_text' => '12.50', 'custom_text' => '',
                ],
                [
                    'key' => 'memo55_rekening', 'label' => 'Info Rekening Transfer', 'band' => 'page_footer',
                    'left_mm' => 11.64, 'top_mm' => 28.00, 'width_mm' => 97.63, 'height_mm' => 4.76,
                    'font_size_pt' => 9.75, 'bold' => true, 'italic' => false, 'align' => 'left',
                    'border' => false, 'visible' => false, 'sample_text' => 'Trans : BCA. 385-3808080 an. A. Sindhudjoyo',
                    'custom_text' => 'Trans : BCA. 385-3808080 an. A. Sindhudjoyo',
                ],
            ],
        ];
    }

    /**
     * Mengambil semua template yang tersimpan.
     */
    public function get_all_templates()
    {
        return $this->db
            ->select('id_template, kode_template, nama_template, is_default, updated_at')
            ->from($this->table)
            ->order_by('is_default', 'DESC')
            ->order_by('id_template', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Mengambil satu template berdasarkan ID, atau template default bila ID kosong/tidak ditemukan.
     */
    public function get_active_template($id = 0)
    {
        $id = (int) $id;
        $row = null;
        if ($id > 0) {
            $row = $this->db->get_where($this->table, ['id_template' => $id])->row_array();
        }
        if (!$row) {
            $row = $this->db->order_by('is_default', 'DESC')->order_by('id_template', 'ASC')->get($this->table, 1)->row_array();
        }
        if (!$row) {
            $def = $this->default_template_payload();
            return [
                'id_template'     => 0,
                'kode_template'   => 'ttb_2022_q_lot_pajak',
                'nama_template'   => $def['nama_template'],
                'paper_width_mm'  => $def['paper_width_mm'],
                'paper_height_mm' => $def['paper_height_mm'],
                'margin_top_mm'   => $def['margin_top_mm'],
                'margin_right_mm' => $def['margin_right_mm'],
                'margin_bottom_mm'=> $def['margin_bottom_mm'],
                'margin_left_mm'  => $def['margin_left_mm'],
                'bands'           => $def['bands'],
                'elements'        => $def['elements'],
                'is_default'      => 1,
            ];
        }

        $row['bands']    = json_decode($row['bands_json'], true) ?: $this->default_template_payload()['bands'];
        $row['elements'] = json_decode($row['layout_json'], true) ?: $this->default_template_payload()['elements'];
        return $row;
    }

    /**
     * Menyimpan perubahan pada template yang sudah ada.
     */
    public function update_template($id, array $payload)
    {
        $id = (int) $id;
        $data = [
            'nama_template'   => trim((string) ($payload['nama_template'] ?? 'Template Faktur')),
            'paper_width_mm'  => (float) ($payload['paper_width_mm'] ?? 210),
            'paper_height_mm' => (float) ($payload['paper_height_mm'] ?? 140),
            'margin_top_mm'   => (float) ($payload['margin_top_mm'] ?? 12),
            'margin_right_mm' => (float) ($payload['margin_right_mm'] ?? 1),
            'margin_bottom_mm'=> (float) ($payload['margin_bottom_mm'] ?? 0),
            'margin_left_mm'  => (float) ($payload['margin_left_mm'] ?? 0),
            'bands_json'      => json_encode($payload['bands'] ?? []),
            'layout_json'     => json_encode($payload['elements'] ?? []),
        ];
        $this->db->where('id_template', $id)->update($this->table, $data);
        return $this->get_active_template($id);
    }

    /**
     * Membuat template baru (Simpan Sebagai Baru).
     */
    public function create_template(array $payload)
    {
        $nama = trim((string) ($payload['nama_template'] ?? 'Template Baru'));
        $kode = preg_replace('/[^a-z0-9_]+/', '_', strtolower($nama));
        $kode = trim($kode, '_') . '_' . substr((string) time(), -4);

        $data = [
            'kode_template'   => $kode,
            'nama_template'   => $nama,
            'paper_width_mm'  => (float) ($payload['paper_width_mm'] ?? 210),
            'paper_height_mm' => (float) ($payload['paper_height_mm'] ?? 140),
            'margin_top_mm'   => (float) ($payload['margin_top_mm'] ?? 12),
            'margin_right_mm' => (float) ($payload['margin_right_mm'] ?? 1),
            'margin_bottom_mm'=> (float) ($payload['margin_bottom_mm'] ?? 0),
            'margin_left_mm'  => (float) ($payload['margin_left_mm'] ?? 0),
            'bands_json'      => json_encode($payload['bands'] ?? []),
            'layout_json'     => json_encode($payload['elements'] ?? []),
            'is_default'      => 0,
        ];
        $this->db->insert($this->table, $data);
        return $this->get_active_template((int) $this->db->insert_id());
    }

    /**
     * Menjadikan satu template sebagai template default untuk cetak faktur.
     */
    public function set_default($id)
    {
        $id = (int) $id;
        $this->db->update($this->table, ['is_default' => 0]);
        $this->db->where('id_template', $id)->update($this->table, ['is_default' => 1]);
        return $this->get_active_template($id);
    }

    /**
     * Mengembalikan posisi template ke pengaturan awal TTB 2022 Q LOT PAJAK 1.11.
     */
    public function reset_to_default($id)
    {
        $id = (int) $id;
        $def = $this->default_template_payload();
        $this->db->where('id_template', $id)->update($this->table, [
            'paper_width_mm'  => $def['paper_width_mm'],
            'paper_height_mm' => $def['paper_height_mm'],
            'margin_top_mm'   => $def['margin_top_mm'],
            'margin_right_mm' => $def['margin_right_mm'],
            'margin_bottom_mm'=> $def['margin_bottom_mm'],
            'margin_left_mm'  => $def['margin_left_mm'],
            'bands_json'      => json_encode($def['bands']),
            'layout_json'     => json_encode($def['elements']),
        ]);
        return $this->get_active_template($id);
    }

    /**
     * Menghapus template non-default.
     */
    public function delete_template($id)
    {
        $id = (int) $id;
        $total = (int) $this->db->count_all($this->table);
        if ($total <= 1) {
            return false;
        }
        $row = $this->db->get_where($this->table, ['id_template' => $id])->row_array();
        if (!$row || (int) $row['is_default'] === 1) {
            return false;
        }
        $this->db->where('id_template', $id)->delete($this->table);
        return true;
    }
}
