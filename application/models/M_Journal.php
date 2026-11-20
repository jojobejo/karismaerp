<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Journal extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Generate Nomor Referensi Jurnal
     */
    public function generate_no_jurnal($metode, $prefix_default = 'MR')
    {
        $prefix = $prefix_default;
        if (strtolower($metode) === 'q kas' || strtolower($metode) === 'a kas') {
            $prefix = 'KM';
        }
        
        $dateStr = date('dmy'); // tgl bulan tahun 2-digit (DDMMYY)
        $pattern = $prefix . '-' . $dateStr;
        
        // Cari nomor jurnal terakhir pada hari ini dengan prefix ini
        $this->db->select('nomor_jurnal');
        $this->db->like('nomor_jurnal', $pattern, 'after');
        $this->db->order_by('nomor_jurnal', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('tbkeu_jurnal');
        
        $next_num = 1;
        if ($query->num_rows() > 0) {
            $last_no = $query->row()->nomor_jurnal;
            // Formatnya: PREFIX-DDMMYYXXXXX
            // Kita ambil bagian XXXXX (5 digit terakhir)
            $parts = explode('-', $last_no);
            if (isset($parts[1])) {
                $seq_part = substr($parts[1], 6); // setelah DDMMYY (6 karakter)
                if (is_numeric($seq_part)) {
                    $next_num = (int)$seq_part + 1;
                }
            }
        }
        
        return $pattern . sprintf('%05d', $next_num);
    }

    /**
     * Proses Jurnal Pembayaran Faktur
     */
    public function post_jurnal_pembayaran($id_pembayaran, $data_pembayaran)
    {
        if (!$this->db->table_exists('tbkeu_jurnal') || !$this->db->table_exists('tbkeu_jurnal_detail')) {
            return false;
        }

        $metode = $data_pembayaran['metode_pembayaran'] ?? '';
        $jumlah = (float)($data_pembayaran['jumlah_pembayaran'] ?? 0);
        $diskon = (float)($data_pembayaran['jumlah_diskon'] ?? 0);
        $total_piutang = $jumlah + $diskon;
        $no_faktur = $data_pembayaran['no_faktur'] ?? '';
        $tanggal = $data_pembayaran['tanggal_pembayaran'] ?? date('Y-m-d');
        $nomor_jurnal = $this->generate_no_jurnal($metode);

        $userId = (int)($this->session->userdata('id_karyawan') 
            ?: $this->session->userdata('id') 
            ?: $this->session->userdata('id_user') 
            ?: 0);
        if ($userId <= 0) {
            $userId = null;
        }

        $customerName = '';
        if (!empty($no_faktur)) {
            $faktur = $this->db
                ->select('c.nama_customer')
                ->from('tbso_faktur_penjualan f')
                ->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left')
                ->where('f.no_faktur', $no_faktur)
                ->get()
                ->row();
            if ($faktur) {
                $customerName = trim($faktur->nama_customer);
            }
        }

        $prefix = (strtolower($metode) === 'q kas' || strtolower($metode) === 'a kas') ? 'KM' : 'MR';
        $jenis = $this->db->get_where('tbkeu_jenis_jurnal', ['kode_jenis_jurnal' => $prefix])->row();
        if (!$jenis) {
            $this->db->insert('tbkeu_jenis_jurnal', [
                'kode_jenis_jurnal' => $prefix,
                'nama_jenis_jurnal' => $prefix === 'KM' ? 'Kas Masuk' : 'Masuk Rekening',
                'is_active' => 1
            ]);
            $id_jenis_jurnal = $this->db->insert_id();
        } else {
            $id_jenis_jurnal = $jenis->id_jenis_jurnal;
        }

        $jurnal_data = [
            'nomor_jurnal' => $nomor_jurnal,
            'id_jenis_jurnal' => $id_jenis_jurnal,
            'tanggal_transaksi' => $tanggal,
            'keterangan' => 'Penerimaan dari ' . ($customerName !== '' ? $customerName : 'Customer') . ' via ' . $metode,
            'status' => 'POSTED',
            'source_module' => 'KEUANGAN',
            'source_type' => 'PEMBAYARAN_FAKTUR',
            'source_id' => $id_pembayaran,
            'source_no' => $no_faktur,
            'total_debit' => $total_piutang,
            'total_kredit' => $total_piutang,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbkeu_jurnal', $jurnal_data);
        $id_jurnal = $this->db->insert_id();

        // Cari ID Akun Debit (berdasarkan nama metode pembayaran)
        $is_retur_metode = (
            strtolower($metode) === 'retur' ||
            $metode === 'Q Hutang Non Dagang (Retur Penjualan yg blm dipot)' ||
            $metode === 'Q Hutang Non Dagang' ||
            stripos($metode, 'retur') !== false
        );

        if ($is_retur_metode) {
            $akun_debit = $this->db->get_where('tbkeu_akun', ['kode_akun' => '21017'])->row_array();
            if (!$akun_debit) {
                $this->db->like('nama_akun', 'Retur Penjualan yg blm dipot');
                $akun_debit = $this->db->get('tbkeu_akun')->row_array();
            }
        } else {
            $akun_debit = $this->db->get_where('tbkeu_akun', ['nama_akun' => $metode])->row_array();
        }
        $id_akun_debit = $akun_debit ? $akun_debit['id_akun'] : null;

        // Cari ID Akun Diskon
        $id_akun_diskon = null;
        if ($diskon > 0) {
            $akun_diskon = $this->db->get_where('tbkeu_akun', ['kode_akun' => '41097'])->row_array();
            if (!$akun_diskon) {
                $this->db->like('nama_akun', 'Potongan Penjualan');
                $akun_diskon = $this->db->get('tbkeu_akun')->row_array();
            }
            $id_akun_diskon = $akun_diskon ? $akun_diskon['id_akun'] : null;
        }

        // Cari ID Akun Kredit (Piutang Usaha)
        $akun_kredit = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13099'])->row_array();
        if (!$akun_kredit) {
            $this->db->like('nama_akun', 'Piutang Usaha');
            $akun_kredit = $this->db->get('tbkeu_akun')->row_array();
        }
        $id_akun_kredit = $akun_kredit ? $akun_kredit['id_akun'] : null;

        if ($id_akun_debit || $id_akun_kredit) {
            $baris = 1;
            // Baris Debit (Pembayaran)
            if ($jumlah > 0) {
                $nama_akun_debit_label = $akun_debit ? $akun_debit['nama_akun'] : $metode;
                $this->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal' => $id_jurnal,
                    'nomor_baris' => $baris++,
                    'id_akun' => $id_akun_debit,
                    'keterangan' => 'Penerimaan dari ' . ($customerName !== '' ? $customerName : 'Customer'),
                    'debit' => $jumlah,
                    'kredit' => 0
                ]);
            }

            // Baris Debit (Diskon)
            if ($diskon > 0) {
                $this->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal' => $id_jurnal,
                    'nomor_baris' => $baris++,
                    'id_akun' => $id_akun_diskon,
                    'keterangan' => 'Potongan Penjualan Faktur ' . $no_faktur,
                    'debit' => $diskon,
                    'kredit' => 0
                ]);
            }

            // Baris Kredit
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal,
                'nomor_baris' => $baris++,
                'id_akun' => $id_akun_kredit,
                'keterangan' => 'Penerimaan dari ' . ($customerName !== '' ? $customerName : 'Customer'),
                'debit' => 0,
                'kredit' => $total_piutang
            ]);
        }

        $this->_post_jurnal_pecahan_faktur_z($id_pembayaran, $data_pembayaran, $id_akun_debit, $id_akun_kredit, $userId, $id_jenis_jurnal);

        return true;
    }

    /**
     * Posting Jurnal Penjualan Konsinyasi yang Terpending saat Faktur Dilunasi/Dibayar
     * Sesuai alur konsinyasi: saat faktur K terbit jurnal di-pending, dan diakui sebagai
     * penjualan resmi saat kios membayar/melunasi sesuai nominal yang dibeli kios.
     *
     * @param int $id_pembayaran
     * @param array $data_pembayaran
     * @return int|bool ID Jurnal Penjualan jika berhasil, false jika bukan konsinyasi/gagal
     */
    public function post_jurnal_penjualan_konsinyasi($id_pembayaran, $data_pembayaran)
    {
        if (!$this->db->table_exists('tbkeu_jurnal') || !$this->db->table_exists('tbkeu_jurnal_detail')) {
            return false;
        }

        $no_faktur = trim((string)($data_pembayaran['no_faktur'] ?? ''));
        $id_faktur = (int)($data_pembayaran['id_faktur'] ?? 0);
        if ($id_faktur <= 0 && !empty($no_faktur)) {
            $fakturRow = $this->db->select('id_faktur')->where('no_faktur', $no_faktur)->get('tbso_faktur_penjualan')->row();
            if ($fakturRow) {
                $id_faktur = (int)$fakturRow->id_faktur;
            }
        }
        if ($id_faktur <= 0) {
            return false;
        }

        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$faktur) {
            return false;
        }

        // Aturan bisnis aktif: jurnal realisasi pengakuan penjualan dan HPP saat pembayaran
        // berlaku untuk nomor faktur yang berawalan K (Konsinyasi) atau T / is_test_market (Barang Test Market).
        $no_faktur_upper = strtoupper((string)($faktur['no_faktur'] ?? ''));
        $prefix_faktur   = substr($no_faktur_upper, 0, 1);
        $is_faktur_t     = ($prefix_faktur === 'T' || !empty($faktur['is_test_market']));
        $is_faktur_k     = ($prefix_faktur === 'K');

        if (!$is_faktur_k && !$is_faktur_t) {
            return false;
        }

        $jumlah = (float)($data_pembayaran['jumlah_pembayaran'] ?? 0);
        $diskon = (float)($data_pembayaran['jumlah_diskon'] ?? 0);
        $total_nominal = $jumlah + $diskon;
        if ($total_nominal <= 0) {
            return false;
        }

        $tipe_label = $is_faktur_t ? 'Test Market' : 'Konsinyasi';
        $source_type_val = $is_faktur_t ? 'FAKTUR_PENJUALAN_TEST_MARKET' : 'FAKTUR_PENJUALAN_KONSINYASI';
        $idempotency_sj = 'SALES_INVOICE-' . ($is_faktur_t ? 'TESTMARKET-' : 'KONSINYASI-') . $faktur['no_faktur'] . '-' . $id_pembayaran;
        $idempotency_gi = 'GOODS_ISSUE-' . ($is_faktur_t ? 'TESTMARKET-' : 'KONSINYASI-') . $faktur['no_faktur'] . '-' . $id_pembayaran;

        // Cek apakah jurnal penjualan untuk pembayaran ini sudah pernah dibuat sebelumnya
        $existing_sj = $this->db
            ->where('source_module', 'SALES')
            ->group_start()
                ->where('source_type', 'FAKTUR_PENJUALAN')
                ->or_where('source_type', 'FAKTUR_PENJUALAN_KONSINYASI')
                ->or_where('source_type', 'FAKTUR_PENJUALAN_TEST_MARKET')
            ->group_end()
            ->group_start()
                ->where('idempotency_key', $idempotency_sj)
                ->or_where('idempotency_key', 'SALES_INVOICE-KONSINYASI-' . $faktur['no_faktur'] . '-' . $id_pembayaran)
                ->or_where('source_id', $faktur['no_faktur'] . '-' . $id_pembayaran)
                ->or_where('source_id', (string)$id_pembayaran)
            ->group_end()
            ->get('tbkeu_jurnal')
            ->row_array();

        if ($existing_sj) {
            return (int)$existing_sj['id_jurnal'];
        }

        // Ambil detail barang pada faktur
        $items = $this->db->query("
            SELECT d.*, b.kelompok_dagang, b.kode_akun_penjualan, b.kode_akun_harga_pokok, b.kode_akun_persediaan
            FROM tbso_faktur_detail d
            LEFT JOIN tbpo_barang b ON d.kd_barang = b.kode_barang
            WHERE d.id_faktur = ?
        ", [$id_faktur])->result_array();

        if (empty($items)) {
            return false;
        }

        // Pembayaran Faktur T/K dapat menunjuk satu barang tertentu. Tempatkan
        // barang yang dipilih sebagai item utama untuk perhitungan harga dan HPP.
        $selectedDetailId = (int)($data_pembayaran['id_faktur_detail'] ?? 0);
        $selectedKdBarang = trim((string)($data_pembayaran['kd_barang'] ?? ''));
        foreach ($items as $index => $item) {
            $isSelected = ($selectedDetailId > 0 && (int)$item['id'] === $selectedDetailId)
                || ($selectedDetailId <= 0 && $selectedKdBarang !== '' && $item['kd_barang'] === $selectedKdBarang);
            if ($isSelected && $index > 0) {
                unset($items[$index]);
                array_unshift($items, $item);
                $items = array_values($items);
                break;
            }
        }

        // Menghitung pajak/PPN jika barang dikenakan pajak
        $tax_rate = 0.0;
        foreach ($items as $it) {
            if ((float)($it['pajak'] ?? 0) > $tax_rate) {
                $tax_rate = (float)$it['pajak'];
            }
        }

        $div_factor = ($tax_rate > 0) ? 1 + ($tax_rate / 100) : 1;
        $nilai_penjualan = round($total_nominal / $div_factor, 2);
        $nilai_ppn = round($total_nominal - $nilai_penjualan, 2);

        $customer_name = !empty($faktur['customer_name']) ? trim($faktur['customer_name']) : 'Kios Customer';
        $tanggal = $data_pembayaran['tanggal_pembayaran'] ?? date('Y-m-d');
        $no_faktur_konsinyasi = trim((string)($data_pembayaran['no_faktur_konsinyasi'] ?? ''));
        if ($no_faktur_konsinyasi === '') {
            $paymentRow = $this->db
                ->select('no_faktur_konsinyasi')
                ->where('id_pembayaran', (int)$id_pembayaran)
                ->get('tbkeu_pembayaran_faktur')
                ->row_array();
            $no_faktur_konsinyasi = trim((string)($paymentRow['no_faktur_konsinyasi'] ?? ''));
        }
        if ($no_faktur_konsinyasi === '') {
            $no_faktur_konsinyasi = $faktur['no_faktur'];
        }
        $nomor_jurnal_sj = $this->generate_no_jurnal('penjualan', 'SJ');

        // Cari ID Jenis Jurnal SJ (Sales)
        $jenis_sj = $this->db->get_where('tbkeu_jenis_jurnal', ['kode_jenis_jurnal' => 'SJ'])->row();
        $id_jenis_jurnal = $jenis_sj ? (int)$jenis_sj->id_jenis_jurnal : 10;

        $userId = (int)($this->session->userdata('id_karyawan') 
            ?: $this->session->userdata('id') 
            ?: $this->session->userdata('id_user') 
            ?: 0);
        if ($userId <= 0) {
            $userId = null;
        }

        $jurnal_penjualan_data = [
            'nomor_jurnal'      => $nomor_jurnal_sj,
            'id_jenis_jurnal'   => $id_jenis_jurnal,
            'tanggal_transaksi' => $tanggal,
            'keterangan'        => 'Penjualan ' . $tipe_label . ': ' . $no_faktur_konsinyasi . ' - ' . $customer_name . ' (Realisasi Pembelian Kios)',
            'status'            => 'POSTED',
            'source_module'     => 'SALES',
            'source_type'       => $source_type_val,
            'source_id'         => $faktur['no_faktur'] . '-' . $id_pembayaran,
            'source_no'         => $no_faktur_konsinyasi,
            'posting_event'     => 'SALES_INVOICE',
            'idempotency_key'   => $idempotency_sj,
            'total_debit'       => $total_nominal,
            'total_kredit'      => $total_nominal,
            'created_by'        => $userId,
            'created_at'        => date('Y-m-d H:i:s'),
            'posted_by'         => $userId,
            'posted_at'         => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tbkeu_jurnal', $jurnal_penjualan_data);
        $id_jurnal_sj = $this->db->insert_id();

        // 1. Akun Debit: Piutang Usaha
        $akun_piutang = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13099'])->row_array();
        if (!$akun_piutang) {
            $this->db->like('nama_akun', 'Piutang Usaha');
            $akun_piutang = $this->db->get('tbkeu_akun')->row_array();
        }
        $id_akun_piutang = $akun_piutang ? (int)$akun_piutang['id_akun'] : null;

        // 2. Akun Kredit: Penjualan
        $kode_penjualan = !empty($items[0]['kode_akun_penjualan']) ? $items[0]['kode_akun_penjualan'] : '41011';
        $akun_penjualan = $this->db->get_where('tbkeu_akun', ['kode_akun' => $kode_penjualan])->row_array();
        if (!$akun_penjualan) {
            $akun_penjualan = $this->db->get_where('tbkeu_akun', ['kode_akun' => '41010'])->row_array();
        }
        if (!$akun_penjualan) {
            $this->db->like('nama_akun', 'Penjualan');
            $akun_penjualan = $this->db->get('tbkeu_akun')->row_array();
        }
        $id_akun_penjualan = $akun_penjualan ? (int)$akun_penjualan['id_akun'] : null;

        // 3. Akun Kredit: PPN Keluaran jika ada pajak
        $id_akun_ppn = null;
        if ($nilai_ppn > 0) {
            $akun_ppn = $this->db->get_where('tbkeu_akun', ['kode_akun' => '23011'])->row_array();
            if (!$akun_ppn) {
                $this->db->like('nama_akun', 'PPN Keluaran');
                $akun_ppn = $this->db->get('tbkeu_akun')->row_array();
            }
            $id_akun_ppn = $akun_ppn ? (int)$akun_ppn['id_akun'] : null;
        }

        $baris = 1;
        // Baris Debit: Piutang Usaha
        $this->db->insert('tbkeu_jurnal_detail', [
            'id_jurnal'   => $id_jurnal_sj,
            'nomor_baris' => $baris++,
            'id_akun'     => $id_akun_piutang,
            'keterangan'  => 'Piutang Penjualan ' . $tipe_label . ' ' . $no_faktur_konsinyasi,
            'debit'       => $total_nominal,
            'kredit'      => 0
        ]);

        // Baris Kredit: Penjualan
        $this->db->insert('tbkeu_jurnal_detail', [
            'id_jurnal'   => $id_jurnal_sj,
            'nomor_baris' => $baris++,
            'id_akun'     => $id_akun_penjualan,
            'keterangan'  => 'Pendapatan Penjualan ' . $tipe_label . ' ' . $no_faktur_konsinyasi,
            'debit'       => 0,
            'kredit'      => $nilai_penjualan
        ]);

        // Baris Kredit: PPN Keluaran
        if ($nilai_ppn > 0) {
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal'   => $id_jurnal_sj,
                'nomor_baris' => $baris++,
                'id_akun'     => $id_akun_ppn,
                'keterangan'  => 'PPN Keluaran Penjualan ' . $tipe_label . ' ' . $no_faktur_konsinyasi,
                'debit'       => 0,
                'kredit'      => $nilai_ppn
            ]);
        }

        // Catat ke tbso_faktur_jurnal jika ada tabelnya
        if ($this->db->table_exists('tbso_faktur_jurnal')) {
            $this->db->insert('tbso_faktur_jurnal', [
                'id_faktur'      => $id_faktur,
                'no_faktur'      => $faktur['no_faktur'],
                'piutang_dagang' => $total_nominal,
                'penjualan'      => $nilai_penjualan,
                'ppn_keluar'     => $nilai_ppn,
                'created_at'     => date('Y-m-d H:i:s')
            ]);
        }

        // Hitung Qty dan HPP (Harga Pokok Penjualan)
        $qty_konsinyasi_input = isset($data_pembayaran['qty_realisasi']) && (float)$data_pembayaran['qty_realisasi'] > 0
            ? (float)$data_pembayaran['qty_realisasi']
            : (isset($data_pembayaran['qty_konsinyasi']) && (float)$data_pembayaran['qty_konsinyasi'] > 0
                ? (float)$data_pembayaran['qty_konsinyasi']
                : null);

        $qty_laku = $qty_konsinyasi_input;
        if ($qty_laku === null || $qty_laku <= 0) {
            $hrg_satuan = (float)($items[0]['hrg_satuan'] ?? 0);
            $qty_laku = ($hrg_satuan > 0) ? round($total_nominal / $hrg_satuan, 3) : 0;
        }

        $kd_barang_konsinyasi = !empty($data_pembayaran['kd_barang']) 
            ? $data_pembayaran['kd_barang'] 
            : (!empty($items[0]['kd_barang']) ? $items[0]['kd_barang'] : '');

        $selected_id_settlement = !empty($data_pembayaran['id_settlement'])
            ? (int)$data_pembayaran['id_settlement']
            : null;

        $total_cogs = 0.0;
        if ($is_faktur_t) {
            // Untuk Barang Test Market: barang bersumber dari Gudang Induk (milik sendiri).
            // HPP dihitung dari harga pokok item faktur / master barang.
            $hrg_pokok = (float)($items[0]['hrg_pokok'] ?? 0);
            $total_cogs = round($qty_laku * $hrg_pokok, 2);
        } else {
            // Untuk Barang Konsinyasi (Faktur K): HPP dihitung menggunakan FIFO berdasarkan penerimaan LPB konsinyasi
            $this->load->model('M_Konsinyasi');
            $fifoResult = $this->M_Konsinyasi->allocate_fifo_consignment_cogs(
                $kd_barang_konsinyasi,
                $qty_laku,
                (int)$id_pembayaran,
                $id_faktur,
                $no_faktur_konsinyasi
            );

            $total_cogs = (float)($fifoResult['total_cogs'] ?? 0);
            if ($total_cogs <= 0) {
                // Fallback jika tidak ditemukan data batch LPB konsinyasi
                $hrg_pokok = (float)($items[0]['hrg_pokok'] ?? 0);
                $total_cogs = round($qty_laku * $hrg_pokok, 2);
            }
        }

        // Jika ada HPP > 0, terbitkan jurnal GOODS_ISSUE (HPP & Persediaan)
        if ($total_cogs > 0) {
            $kode_cogs = !empty($items[0]['kode_akun_harga_pokok']) ? $items[0]['kode_akun_harga_pokok'] : '51010';
            $akun_cogs = $this->db->get_where('tbkeu_akun', ['kode_akun' => $kode_cogs])->row_array();
            if (!$akun_cogs) {
                $this->db->like('nama_akun', 'Harga Pokok Penjualan');
                $akun_cogs = $this->db->get('tbkeu_akun')->row_array();
            }
            $id_akun_cogs = $akun_cogs ? (int)$akun_cogs['id_akun'] : null;

            $kode_inv = !empty($items[0]['kode_akun_persediaan']) ? $items[0]['kode_akun_persediaan'] : '14010';
            $akun_inv = $this->db->get_where('tbkeu_akun', ['kode_akun' => $kode_inv])->row_array();
            if (!$akun_inv) {
                $this->db->like('nama_akun', 'Persediaan');
                $akun_inv = $this->db->get('tbkeu_akun')->row_array();
            }
            $id_akun_inv = $akun_inv ? (int)$akun_inv['id_akun'] : null;

            if ($id_akun_cogs && $id_akun_inv) {
                $nomor_jurnal_gi = $this->generate_no_jurnal('penjualan', 'SJ');
                $jurnal_gi_data = [
                    'nomor_jurnal'      => $nomor_jurnal_gi,
                    'id_jenis_jurnal'   => $id_jenis_jurnal,
                    'tanggal_transaksi' => $tanggal,
                    'keterangan'        => 'Penyesuaian persediaan ' . strtolower($tipe_label) . ', untuk ' . $nomor_jurnal_gi,
                    'status'            => 'POSTED',
                    'source_module'     => 'SALES',
                    'source_type'       => $source_type_val,
                    'source_id'         => $faktur['no_faktur'] . '-' . $id_pembayaran,
                    'source_no'         => $no_faktur_konsinyasi,
                    'posting_event'     => 'GOODS_ISSUE',
                    'idempotency_key'   => $idempotency_gi,
                    'total_debit'       => $total_cogs,
                    'total_kredit'      => $total_cogs,
                    'created_by'        => $userId,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'posted_by'         => $userId,
                    'posted_at'         => date('Y-m-d H:i:s')
                ];
                $this->db->insert('tbkeu_jurnal', $jurnal_gi_data);
                $id_jurnal_gi = $this->db->insert_id();

                // Baris Debit: HPP
                $this->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal'   => $id_jurnal_gi,
                    'nomor_baris' => 1,
                    'id_akun'     => $id_akun_cogs,
                    'keterangan'  => 'Harga Pokok Penjualan ' . $tipe_label . ' ' . $no_faktur_konsinyasi,
                    'debit'       => $total_cogs,
                    'kredit'      => 0
                ]);

                // Baris Kredit: Persediaan
                $persediaan_ket = $is_faktur_t
                    ? ('Persediaan Barang Dagang (Test Market) ' . $no_faktur_konsinyasi)
                    : ('Persediaan Konsinyasi ' . $no_faktur_konsinyasi);

                $this->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal'   => $id_jurnal_gi,
                    'nomor_baris' => 2,
                    'id_akun'     => $id_akun_inv,
                    'keterangan'  => $persediaan_ket,
                    'debit'       => 0,
                    'kredit'      => $total_cogs
                ]);

                // Tautkan ID Jurnal GOODS_ISSUE ke jejak audit alokasi FIFO (hanya untuk konsinyasi Faktur K)
                if (!$is_faktur_t && isset($this->M_Konsinyasi)) {
                    $this->M_Konsinyasi->update_cogs_journal_id((int)$id_pembayaran, $id_jurnal_gi);
                }
            }
        }

        // Sinkronisasi posisi stok konsinyasi hanya untuk Faktur K (konsinyasi murni)
        // Faktur T (Test Market) TIDAK masuk ke tb_konsinyasi_settlement karena barang milik sendiri
        if (!$is_faktur_t) {
            $selected_kd_barang = !empty($data_pembayaran['kd_barang']) ? $data_pembayaran['kd_barang'] : null;
            $this->_sync_settlement_on_payment($id_faktur, $faktur, $items[0], $total_nominal, $userId, $qty_konsinyasi_input, $id_pembayaran, $selected_kd_barang, $selected_id_settlement);
        } else {
            // Untuk Faktur T: terbitkan faktur realisasi mandiri ke tb_konsinyasi_faktur
            if (!empty($id_pembayaran)) {
                $this->load->model('M_Konsinyasi');
                $this->M_Konsinyasi->terbitkan_faktur_konsinyasi($id_faktur, $id_pembayaran, [
                    'is_test_market' => true
                ]);
            }
        }

        return $id_jurnal_sj;
    }

    /**
     * Sinkronisasi data konsinyasi (tb_konsinyasi_settlement) saat kios melakukan pembayaran
     */
    private function _sync_settlement_on_payment($id_faktur, $faktur, $firstItem, $total_nominal, $userId, $qty_konsinyasi_input = null, $id_pembayaran = null, $selected_kd_barang = null, $selected_id_settlement = null)
    {
        if (!$this->db->table_exists('tb_konsinyasi_settlement')) {
            return;
        }

        $settlement = null;
        if (!empty($selected_id_settlement)) {
            $settlement = $this->db
                ->where('id_settlement', (int)$selected_id_settlement)
                ->where_in('status', ['DI_KIOS', 'PENDING'])
                ->get('tb_konsinyasi_settlement')
                ->row_array();
        }

        if (!$settlement) {
            $this->db
                ->where('id_faktur', (int)$id_faktur)
                ->where_in('status', ['DI_KIOS', 'PENDING']);
            if (!empty($selected_kd_barang)) {
                $this->db->where('kd_barang', $selected_kd_barang);
            }
            $settlement = $this->db
                ->order_by('id_settlement', 'ASC')
                ->limit(1)
                ->get('tb_konsinyasi_settlement')
                ->row_array();
        }

        if (!$settlement) {
            $this->db
                ->where('no_faktur', $faktur['no_faktur'])
                ->where_in('status', ['DI_KIOS', 'PENDING']);
            if (!empty($selected_kd_barang)) {
                $this->db->where('kd_barang', $selected_kd_barang);
            }
            $settlement = $this->db
                ->order_by('id_settlement', 'ASC')
                ->limit(1)
                ->get('tb_konsinyasi_settlement')
                ->row_array();
        }

        if (!$settlement) {
            return;
        }

        $hrg_jual = (float)$settlement['hrg_jual'] > 0 ? (float)$settlement['hrg_jual'] : (float)($firstItem['hrg_satuan'] ?? 0);
        $qty_net_existing = (float)$settlement['qty_net'];

        if ($qty_konsinyasi_input !== null && $qty_konsinyasi_input > 0) {
            $qty_dibeli = min($qty_konsinyasi_input, $qty_net_existing);
        } else {
            $qty_dibeli = ($hrg_jual > 0) ? round($total_nominal / $hrg_jual, 3) : $qty_net_existing;
        }
        if ($qty_dibeli <= 0) {
            $qty_dibeli = $qty_net_existing;
        }

        $userName = $this->session->userdata('nm_karyawan')
            ?: $this->session->userdata('nama')
            ?: $this->session->userdata('username')
            ?: 'Keuangan';

        // Hitung urutan pembayaran ke berapa untuk faktur konsinyasi ini
        $paymentIndex = 1;
        if (!empty($id_pembayaran)) {
            $allPayments = $this->db
                ->select('id_pembayaran')
                ->where('id_faktur', (int)$id_faktur)
                ->where('status !=', 'CANCELLED')
                ->order_by('id_pembayaran', 'ASC')
                ->get('tbkeu_pembayaran_faktur')
                ->result_array();

            foreach ($allPayments as $idx => $p) {
                if ((int)$p['id_pembayaran'] === (int)$id_pembayaran) {
                    $paymentIndex = $idx + 1;
                    break;
                }
            }
        }
        $noFakturKonsinyasi = $faktur['no_faktur'] . '-' . $paymentIndex;

        if ($qty_dibeli < $qty_net_existing) {
            // Lunas sebagian (misal 50 pcs dari 120 pcs):
            // Buat record baru untuk sisa barang yang masih berada di kios (status DI_KIOS)
            $qty_sisa = $qty_net_existing - $qty_dibeli;
            $sisa_data = $settlement;
            unset($sisa_data['id_settlement']);
            $sisa_data['no_settlement']  = 'KONS-SET-' . date('ymd') . '-' . sprintf('%04d', rand(100, 9999));
            $sisa_data['no_faktur']      = $faktur['no_faktur'];
            $sisa_data['id_pembayaran']  = null;
            $sisa_data['qty_terjual']    = $qty_sisa;
            $sisa_data['qty_net']        = $qty_sisa;
            $sisa_data['subtotal_jual']  = round($qty_sisa * $hrg_jual, 2);
            $sisa_data['status']         = 'DI_KIOS';
            $sisa_data['catatan']        = 'Sisa titipan di kios setelah pelunasan ' . $qty_dibeli . ' ' . $settlement['satuan'];
            $sisa_data['created_at']     = date('Y-m-d H:i:s');
            $this->db->insert('tb_konsinyasi_settlement', $sisa_data);
        }

        // Settlement menyimpan identitas lot fisik yang dipilih oleh kios.
        // Alokasi HPP FIFO disimpan terpisah di tb_konsinyasi_cogs_allocation
        // dan tidak boleh menimpa lot, LPB asal, atau expired settlement ini.
        $this->db->where('id_settlement', $settlement['id_settlement'])->update('tb_konsinyasi_settlement', [
            'no_faktur'     => $noFakturKonsinyasi,
            'id_pembayaran' => !empty($id_pembayaran) ? (int)$id_pembayaran : null,
            'qty_terjual'   => $qty_dibeli,
            'qty_net'       => $qty_dibeli,
            'subtotal_jual' => round($qty_dibeli * $hrg_jual, 2),
            'status'        => 'LAKU',
            'settled_at'    => date('Y-m-d H:i:s'),
            'settled_by'    => $userName,
            'catatan'       => 'Barang laku dibeli kios ' . $qty_dibeli . ' ' . $settlement['satuan'] . ' via pelunasan faktur ' . $noFakturKonsinyasi . ' di Keuangan (Menunggu tagihan supplier)'
        ]);

        // Terbitkan entitas faktur konsinyasi baru secara mandiri ke tb_konsinyasi_faktur
        if (!empty($id_pembayaran)) {
            $this->load->model('M_Konsinyasi');
            $this->M_Konsinyasi->terbitkan_faktur_konsinyasi($id_faktur, $id_pembayaran, [
                'id_settlement' => (int) $settlement['id_settlement']
            ]);
        }
    }

    public function accounting_journal_schema_ready()
    {
        return $this->db->table_exists('tbkeu_jurnal')
            && $this->db->table_exists('tbkeu_jurnal_detail');
    }

    public function accounting_sales_journal_rows($search = '', $limit = 100)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            COALESCE(NULLIF(j.source_no, ''), NULLIF(j.source_id, ''), j.nomor_jurnal) AS no_faktur,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(NULLIF(kf.no_so, ''), NULLIF(f.no_so, ''), '') AS no_so,
            COALESCE(NULLIF(kf.nama_customer, ''), NULLIF(f.customer_name, ''), '') AS pelanggan,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tb_konsinyasi_faktur kf', 'j.source_type = "FAKTUR_PENJUALAN_KONSINYASI" AND (kf.no_faktur_konsinyasi = j.source_no OR CONCAT(kf.no_faktur_induk, "-", kf.id_pembayaran) = j.source_id)', 'left');
        $this->db->join('tbso_faktur_penjualan f', 'j.source_module = "SALES" AND ((j.source_type = "FAKTUR_PENJUALAN_KONSINYASI" AND f.no_faktur = kf.no_faktur_induk) OR (j.source_type != "FAKTUR_PENJUALAN_KONSINYASI" AND (f.no_faktur = j.source_id OR f.no_faktur = j.source_no)))', 'left');
        $this->db->where('j.source_module', 'SALES');
        $this->db->group_start();
        $this->db->where('j.source_type', 'FAKTUR_PENJUALAN');
        $this->db->or_where('j.source_type', 'FAKTUR_PENJUALAN_KONSINYASI');
        $this->db->group_end();
        $this->db->group_start();
        $this->db->where('j.posting_event', 'SALES_INVOICE');
        $this->db->or_where('j.posting_event IS NULL');
        $this->db->or_where('j.posting_event', '');
        $this->db->group_end();
        
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('j.source_no', $search);
            $this->db->or_like('j.nomor_jurnal', $search);
            $this->db->or_like('kf.no_so', $search);
            $this->db->or_like('kf.nama_customer', $search);
            $this->db->or_like('f.no_so', $search);
            $this->db->or_like('f.customer_name', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $this->db->limit((int)$limit > 0 ? (int)$limit : 100);

        return $this->db->get()->result();
    }

    public function accounting_payment_journal_rows($search = '', $limit = 100)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            j.source_id AS id_pembayaran,
            COALESCE(NULLIF(j.keterangan, ''), CONCAT('Penerimaan dari ', COALESCE(f.customer_name, ''))) AS keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(f.customer_name, '') AS pelanggan
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_pembayaran_faktur p', 'j.source_module = "KEUANGAN" AND CAST(j.source_id AS UNSIGNED) = p.id_pembayaran', 'left');
        $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = p.id_faktur', 'left');
        $this->db->where('j.source_module', 'KEUANGAN');
        $this->db->where('j.source_type', 'PEMBAYARAN_FAKTUR');
        $this->db->where('j.status', 'POSTED');
        if ($this->db->field_exists('status', 'tbkeu_pembayaran_faktur')) {
            $this->db->where("COALESCE(p.status, 'POSTED') = 'POSTED'");
        }
        
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('j.source_no', $search);
            $this->db->or_like('j.nomor_jurnal', $search);
            $this->db->or_like('f.customer_name', $search);
            $this->db->or_like('j.keterangan', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $this->db->limit((int)$limit > 0 ? (int)$limit : 100);

        return $this->db->get()->result();
    }

    public function accounting_retur_journal_rows($search = '', $limit = 100)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            j.source_id AS id_retur,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(r.no_spr, '') AS no_so,
            COALESCE(r.nama_customer, '') AS pelanggan,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbrp_retur_penjualan_header r', 'j.source_module = "SALES" AND r.no_retur = j.source_no', 'left');
        $this->db->where('j.source_module', 'SALES');
        $this->db->where('j.source_type', 'RETUR_PENJUALAN');
        
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('j.source_no', $search);
            $this->db->or_like('j.nomor_jurnal', $search);
            $this->db->or_like('r.no_spr', $search);
            $this->db->or_like('r.nama_customer', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $this->db->limit((int)$limit > 0 ? (int)$limit : 100);

        return $this->db->get()->result();
    }

    public function accounting_purchase_return_journal_rows($search = '', $limit = 100)
    {
        if (!$this->accounting_journal_schema_ready() || !$this->db->table_exists('tb_retur_pembelian') || !$this->db->table_exists('tb_lpb')) {
            return [];
        }

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            j.source_id AS id_retur,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(r.no_retur_pembelian, j.source_no, '') AS no_retur,
            COALESCE(l.nomor_lpb, '') AS nomor_lpb,
            COALESCE(s.nama_suplier, '') AS supplier,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tb_retur_pembelian r', 'r.id_retur_pembelian = CAST(j.source_id AS UNSIGNED)', 'left', false);
        $this->db->join('tb_lpb l', 'l.id_lpb = r.id_lpb', 'left');
        $this->db->join('tbpo_suplier s', 's.kd_suplier = r.kd_supplier', 'left');
        $this->db->where('j.source_module', 'LOGISTIK');
        $this->db->where('j.source_type', 'RETUR_PEMBELIAN');
        $this->db->where('j.posting_event', 'PURCHASE_RETURN');
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('j.source_no', $search);
            $this->db->or_like('j.nomor_jurnal', $search);
            $this->db->or_like('r.no_retur_pembelian', $search);
            $this->db->or_like('l.nomor_lpb', $search);
            $this->db->or_like('s.nama_suplier', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $this->db->limit((int)$limit > 0 ? (int)$limit : 100);

        return $this->db->get()->result();
    }

    public function accounting_supplier_payment_journal_rows($search = '', $limit = 100)
    {
        if (!$this->accounting_journal_schema_ready() || !$this->db->table_exists('tbkeu_pembayaran')) {
            return [];
        }

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            j.source_id AS id_pembayaran,
            j.source_type,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(p.nomor_pembayaran, j.source_no, '') AS nomor_pembayaran,
            COALESCE(s.nama_suplier, '') AS supplier,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_pembayaran p', 'p.id_jurnal = j.id_jurnal', 'left');
        $this->db->join('tbpo_suplier s', 's.id_suplier = p.id_supplier', 'left');
        $this->db->where('j.source_module', 'KEUANGAN');
        $this->db->where_in('j.source_type', ['SUPPLIER_PAYMENT', 'SUPPLIER_RETURN_DEDUCTION']);
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('j.source_no', $search);
            $this->db->or_like('j.nomor_jurnal', $search);
            $this->db->or_like('p.nomor_pembayaran', $search);
            $this->db->or_like('s.nama_suplier', $search);
            $this->db->or_like('j.keterangan', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $this->db->limit((int)$limit > 0 ? (int)$limit : 100);

        return $this->db->get()->result();
    }

    public function accounting_journal_detail_with_accounts($idJurnal)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return null;
        }

        $this->db->select("
            j.*,
            jj.kode_jenis_jurnal,
            COALESCE(NULLIF(tu.nama_lngkp, ''), NULLIF(k.nm_karyawan, ''), IF(j.created_by IS NULL, '', CONCAT('User #', j.created_by))) AS created_by_name,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
        $this->db->join('tb_users tu', 'tu.id = j.created_by', 'left');
        $this->db->join('tb_karyawan k', 'k.id = j.created_by', 'left');
        $this->db->where('j.id_jurnal', (int)$idJurnal);
        $journal = $this->db->get()->row();
        if (!$journal) {
            return null;
        }

        $this->db->select("
            d.*,
            a.kode_akun,
            COALESCE(NULLIF(a.nama_akun, ''), d.keterangan, '') AS nama_akun,
            a.kode_akun AS kode_rekening_display
        ", false);
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->db->where('d.id_jurnal', (int)$idJurnal);
        $this->db->order_by('d.nomor_baris', 'ASC');

        return [
            'journal' => $journal,
            'details' => $this->db->get()->result(),
        ];
    }

    public function accounting_sales_journal_detail($idJurnal)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return null;
        }

        $this->db->select("
            j.*,
            COALESCE(f.no_so, '') AS no_so,
            COALESCE(f.customer_name, '') AS pelanggan,
            COALESCE(
                NULLIF(tu.nama_lngkp, ''),
                NULLIF(k.nm_karyawan, ''), 
                NULLIF(p.create_by, ''), 
                NULLIF(f.create_by, ''), 
                CASE WHEN j.created_by = 0 THEN '' ELSE CONCAT('User #', j.created_by) END,
                'system'
            ) AS created_by_name,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbso_faktur_penjualan f', '(j.source_module = "SALES" AND f.no_faktur = j.source_id) OR (j.source_module = "KEUANGAN" AND f.no_faktur = j.source_no)', 'left');
        $this->db->join('tb_users tu', 'j.created_by = tu.id', 'left');
        $this->db->join('tb_karyawan k', 'j.created_by = k.id', 'left');
        $this->db->join('tbkeu_pembayaran_faktur p', 'j.source_module = "KEUANGAN" AND CAST(j.source_id AS UNSIGNED) = p.id_pembayaran', 'left');
        $this->db->where('j.id_jurnal', (int)$idJurnal);
        $journal = $this->db->get()->row();
        if (!$journal) {
            return null;
        }

        $journal_ids = [(int)$idJurnal];
        $ref_no = !empty($journal->source_id) ? $journal->source_id : (!empty($journal->source_no) ? $journal->source_no : '');
        if ($ref_no !== '') {
            $this->db->select('id_jurnal');
            $this->db->from('tbkeu_jurnal');
            $this->db->where('source_module', 'SALES');
            $this->db->group_start();
            $this->db->where('source_id', $ref_no);
            $this->db->or_where('source_no', $ref_no);
            $this->db->group_end();
            $related = $this->db->get()->result_array();
            if (!empty($related)) {
                $journal_ids = array_unique(array_merge($journal_ids, array_map('intval', array_column($related, 'id_jurnal'))));
            }
        }

        $this->db->select("d.*, a.kode_akun, a.nama_akun, a.kode_akun AS kode_rekening_display", false);
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->db->where_in('d.id_jurnal', $journal_ids);
        $this->db->order_by('d.id_jurnal', 'ASC');
        $this->db->order_by('d.nomor_baris', 'ASC');

        $details = $this->db->get()->result();

        $tot_debit = 0;
        $tot_kredit = 0;
        foreach ($details as $d) {
            $tot_debit += (float)$d->debit;
            $tot_kredit += (float)$d->kredit;
        }
        $journal->total_debit = $tot_debit;
        $journal->total_kredit = $tot_kredit;

        return [
            'journal' => $journal,
            'details' => $details,
        ];
    }

    /**
     * Proses Jurnal Retur Penjualan
     */
    public function post_jurnal_retur_penjualan($id_retur)
    {
        if (!$this->accounting_journal_schema_ready()) {
            return false;
        }

        // Query header retur
        $retur = $this->db->get_where('tbrp_retur_penjualan_header', ['id_retur' => $id_retur])->row_array();
        if (!$retur) {
            return false;
        }

        // Retur replace, service, dan konsinyasi tidak membentuk jurnal akuntansi.
        $tipe_retur = strtolower(trim($retur['tipe_retur'] ?? 'biasa'));
        if (in_array($tipe_retur, ['replace', 'service', 'konsinyasi'], true)) {
            // Hapus jurnal lama jika sebelumnya pernah terposting
            $existing_journals = $this->db->get_where('tbkeu_jurnal', [
                'source_module' => 'SALES',
                'source_type'   => 'RETUR_PENJUALAN',
                'source_id'     => $id_retur
            ])->result_array();

            foreach ($existing_journals as $ej) {
                $this->db->delete('tbkeu_jurnal_detail', ['id_jurnal' => $ej['id_jurnal']]);
                $this->db->delete('tbkeu_jurnal', ['id_jurnal' => $ej['id_jurnal']]);
            }

            if (!empty($retur['no_retur'])) {
                $no_journals = $this->db->get_where('tbkeu_jurnal', [
                    'source_module' => 'SALES',
                    'source_type'   => 'RETUR_PENJUALAN',
                    'source_no'     => trim($retur['no_retur'])
                ])->result_array();
                foreach ($no_journals as $nj) {
                    $this->db->delete('tbkeu_jurnal_detail', ['id_jurnal' => $nj['id_jurnal']]);
                    $this->db->delete('tbkeu_jurnal', ['id_jurnal' => $nj['id_jurnal']]);
                }
            }

            return false;
        }

        // Query detail retur
        $details = $this->db->get_where('tbrp_retur_penjualan_detail', ['id_retur' => $id_retur])->result_array();
        if (empty($details)) {
            return false;
        }

        $no_retur = trim($retur['no_retur']);
        $tanggal = $retur['tanggal_retur'] ?: date('Y-m-d');

        // Check if journal already exists for this return
        $existing_journal = $this->db->get_where('tbkeu_jurnal', [
            'source_module' => 'SALES',
            'source_type'   => 'RETUR_PENJUALAN',
            'source_id'     => $id_retur
        ])->row_array();

        if ($existing_journal) {
            $id_jurnal = $existing_journal['id_jurnal'];
            $nomor_jurnal = $existing_journal['nomor_jurnal'];
            // Hapus detail lama sebelum re-insert
            $this->db->delete('tbkeu_jurnal_detail', ['id_jurnal' => $id_jurnal]);
        } else {
            $nomor_jurnal = $this->generate_no_jurnal('retur_penjualan', 'RJP');
            $userId = (int)($this->session->userdata('id_karyawan') 
                ?: $this->session->userdata('id') 
                ?: $this->session->userdata('id_user') 
                ?: 0);
            if ($userId <= 0) {
                $userId = null;
            }

            $jurnal_data = [
                'nomor_jurnal' => $nomor_jurnal,
                'tanggal_transaksi' => $tanggal,
                'keterangan' => 'Retur Penjualan ' . $no_retur . ' (Customer: ' . $retur['nama_customer'] . ')',
                'status' => 'POSTED',
                'source_module' => 'SALES',
                'source_type' => 'RETUR_PENJUALAN',
                'source_id' => $id_retur,
                'source_no' => $no_retur,
                'total_debit' => 0,
                'total_kredit' => 0,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'posted_by' => $userId,
                'posted_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('tbkeu_jurnal', $jurnal_data);
            $id_jurnal = $this->db->insert_id();
        }

        // Account IDs resolver
        // 1. Kredit (Piutang Usaha atau Hutang Non Dagang jika Refund/Biasa)
        $tipe_retur = strtolower(trim($retur['tipe_retur'] ?? 'biasa'));
        $is_refund = ($tipe_retur === 'biasa' || $tipe_retur === 'refund');
        
        if ($is_refund) {
            $akun_kredit_row = $this->db->get_where('tbkeu_akun', ['kode_akun' => '21017'])->row_array();
            if (!$akun_kredit_row) {
                $this->db->like('nama_akun', 'Retur Penjualan yg blm dipot');
                $akun_kredit_row = $this->db->get('tbkeu_akun')->row_array();
            }
            $id_akun_kredit = $akun_kredit_row ? $akun_kredit_row['id_akun'] : 279;
            $keterangan_kredit = 'Hutang Retur ' . $no_retur;
        } else {
            $akun_kredit_row = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13099'])->row_array();
            if (!$akun_kredit_row) {
                $this->db->like('nama_akun', 'Piutang Usaha');
                $akun_kredit_row = $this->db->get('tbkeu_akun')->row_array();
            }
            $id_akun_kredit = $akun_kredit_row ? $akun_kredit_row['id_akun'] : 205; // fallback to 205 Q Piutang Dagang
            $keterangan_kredit = 'Potongan Piutang Retur ' . $no_retur;
        }

        // 2. Q PPN K (Debit for taxable)
        $akun_ppn = $this->db->get_where('tbkeu_akun', ['kode_akun' => '21024'])->row_array()
            ?: $this->db->like('nama_akun', 'PPN Keluaran')->get('tbkeu_akun')->row_array();
        $id_akun_ppn = $akun_ppn ? $akun_ppn['id_akun'] : 280;

        // Fetch all accounts for quick mapping
        $akun_all = $this->db->get('tbkeu_akun')->result_array();
        $akun_map = [];
        foreach ($akun_all as $a) {
            $akun_map[$a['kode_akun']] = (int)$a['id_akun'];
        }

        $line_num = 1;
        $total_debit = 0;
        $total_kredit = 0;
        $grouped_retur = [];
        $total_ppn = 0;
        $grouped_stock = [];

        foreach ($details as $d) {
            $item_val = (float)$d['qty_retur'] * (float)$d['harga_satuan'];
            if ($item_val <= 0) continue;

            $kd_barang_item = !empty($d['kd_barang']) ? trim($d['kd_barang']) : '';
            $no_faktur_item = !empty($d['no_faktur']) ? trim($d['no_faktur']) : '';

            // Prioritaskan kd_barang dari detail retur atau faktur asal
            if (empty($kd_barang_item) && !empty($no_faktur_item)) {
                $fd_row = $this->db->get_where('tbso_faktur_detail', [
                    'TRIM(no_faktur)' => $no_faktur_item,
                    'nama_barang'     => $d['nama_barang']
                ])->row_array();
                if ($fd_row && !empty($fd_row['kd_barang'])) {
                    $kd_barang_item = trim($fd_row['kd_barang']);
                }
            }

            $this->db->select('b.kode_barang, b.nama_barang, b.kelompok_dagang, 
                               COALESCE(ba.kode_akun_retur_penjualan, b.kode_akun_retur_penjualan) AS kode_akun_retur_penjualan,
                               COALESCE(ba.kode_akun_persediaan, b.kode_akun_persediaan) AS kode_akun_persediaan,
                               COALESCE(ba.kode_akun_harga_pokok, b.kode_akun_harga_pokok) AS kode_akun_harga_pokok,
                               fd.hrg_pokok, g.DESKRIPSI');
            $this->db->from('tbpo_barang b');
            $this->db->join('tbpo_barang_akun ba', 'ba.kode_barang = b.kode_barang', 'left');
            if (!empty($no_faktur_item)) {
                $this->db->join('tbso_faktur_detail fd', 'fd.kd_barang = b.kode_barang AND TRIM(fd.no_faktur) = ' . $this->db->escape($no_faktur_item), 'left');
            } else {
                $this->db->join('tbso_faktur_detail fd', '1=0', 'left');
            }
            $this->db->join('tbkeu_kelompok_dagang g', 'b.kelompok_dagang = g.NOINDEX', 'left');

            if (!empty($kd_barang_item)) {
                $this->db->where('b.kode_barang', $kd_barang_item);
            } else {
                $this->db->where('b.nama_barang', $d['nama_barang']);
            }
            $prod = $this->db->get()->row_array();

            $kode_akun_retur = $prod ? trim($prod['kode_akun_retur_penjualan'] ?? '') : '';
            $desc = $prod ? strtoupper(trim($prod['DESKRIPSI'] ?? '')) : '';
            $prefix = $prod ? strtoupper(substr($prod['kode_barang'], 0, 1)) : '';

            $is_bkp = false;
            if ($prod && (int)($prod['kelompok_dagang'] ?? 0) === 2) {
                $is_bkp = true;
            } elseif ($kode_akun_retur === '41014') {
                $is_bkp = true;
            } elseif (stripos($desc, 'BKPS') !== false) {
                $is_bkp = false;
            } elseif (stripos($desc, 'BKP') !== false) {
                $is_bkp = true;
            } elseif ($prefix === 'Q' && stripos($desc, 'BKPS') === false) {
                $is_bkp = true;
            } else {
                if (strpos(strtolower($d['nama_barang']), 'jasa') !== false) {
                    $is_bkp = false;
                }
            }

            // Tax calculation (DPP + PPN 11% untuk BKP)
            $dpp = $item_val;
            $ppn = 0;
            if ($is_bkp) {
                $dpp = round($item_val / 1.11, 5);
                $ppn = round($item_val - $dpp, 5);
            }

            // Resolve ID Akun Retur Penjualan langsung dari Master Barang
            $id_akun_retur = 0;
            if (!empty($kode_akun_retur)) {
                if (isset($akun_map[$kode_akun_retur])) {
                    $id_akun_retur = $akun_map[$kode_akun_retur];
                } else {
                    $aRow = $this->db->get_where('tbkeu_akun', ['kode_akun' => $kode_akun_retur])->row_array();
                    if ($aRow) {
                        $id_akun_retur = (int)$aRow['id_akun'];
                    }
                }
            }

            // Fallback jika belum tersetting di master barang
            if ($id_akun_retur <= 0) {
                $prefix = $prod ? strtoupper(substr($prod['kode_barang'], 0, 1)) : '';
                if ($prefix === 'Q') {
                    $id_akun_retur = $is_bkp ? ($akun_map['41014'] ?? 310) : ($akun_map['41015'] ?? 311);
                } elseif ($prefix === 'A') {
                    $id_akun_retur = $akun_map['41034'] ?? 316;
                } else {
                    $id_akun_retur = 310;
                }
            }

            if (!isset($grouped_retur[$id_akun_retur])) {
                $grouped_retur[$id_akun_retur] = 0;
            }
            $grouped_retur[$id_akun_retur] += $dpp;
            $total_ppn += $ppn;

            // Stock/HPP reversal calculation
            $cost_unit = ($prod && !empty($prod['hrg_pokok'])) ? (float)$prod['hrg_pokok'] : 0;
            
            // Fallback 1: Cek dari faktur penjualan terakhir jika join awal kosong
            if ($cost_unit <= 0 && !empty($kd_barang_item)) {
                $last_fd = $this->db->select('hrg_pokok')
                    ->where('kd_barang', $kd_barang_item)
                    ->where('hrg_pokok >', 0)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('tbso_faktur_detail')
                    ->row_array();
                if ($last_fd && (float)$last_fd['hrg_pokok'] > 0) {
                    $cost_unit = (float)$last_fd['hrg_pokok'];
                }
            }

            // Fallback 2: Cek dari SO detail terakhir
            if ($cost_unit <= 0 && !empty($kd_barang_item)) {
                $last_so = $this->db->select('hrg_pokok')
                    ->where('kd_barang', $kd_barang_item)
                    ->where('hrg_pokok >', 0)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('tbso_sales_order_detail')
                    ->row_array();
                if ($last_so && (float)$last_so['hrg_pokok'] > 0) {
                    $cost_unit = (float)$last_so['hrg_pokok'];
                }
            }

            // Fallback 3: Master barang
            if ($cost_unit <= 0) {
                $fallback_prod = null;
                if ($this->db->table_exists('tb_master_barang')) {
                    $fallback_prod = $this->db->get_where('tb_master_barang', ['nm_barang' => $d['nama_barang']])->row_array();
                }
                if (!$fallback_prod && $this->db->table_exists('tbpo_barang')) {
                    $fallback_prod = $this->db->get_where('tbpo_barang', ['nama_barang' => $d['nama_barang']])->row_array();
                }
                $cost_unit = $fallback_prod ? (float)($fallback_prod['hpp'] ?? $fallback_prod['harga_pokok'] ?? $fallback_prod['hrg_pokok'] ?? 0) : 0;
            }

            $cost_total = round((float)$d['qty_retur'] * $cost_unit, 5);

            // Ambil akun persediaan dan HPP dari Master Barang
            $kode_persediaan = $prod && !empty($prod['kode_akun_persediaan']) ? trim($prod['kode_akun_persediaan']) : '';
            $kode_hpp = $prod && !empty($prod['kode_akun_harga_pokok']) ? trim($prod['kode_akun_harga_pokok']) : '';

            if (empty($kode_persediaan)) {
                $prefix = $prod ? strtoupper(substr($prod['kode_barang'], 0, 1)) : '';
                if ($prefix === 'Q') {
                    $kode_persediaan = $is_bkp ? '14010' : '14011';
                } elseif ($prefix === 'A') {
                    $kode_persediaan = '14031';
                } else {
                    $kode_persediaan = '14010';
                }
            }
            if (empty($kode_hpp)) {
                $prefix = $prod ? strtoupper(substr($prod['kode_barang'], 0, 1)) : '';
                if ($prefix === 'Q') {
                    $kode_hpp = $is_bkp ? '51010' : '51011';
                } elseif ($prefix === 'A') {
                    $kode_hpp = '51031';
                } else {
                    $kode_hpp = '51010';
                }
            }

            $id_persediaan = isset($akun_map[$kode_persediaan]) ? $akun_map[$kode_persediaan] : 0;
            $id_hpp = isset($akun_map[$kode_hpp]) ? $akun_map[$kode_hpp] : 0;

            if ($cost_total > 0 && $id_persediaan > 0 && $id_hpp > 0) {
                $key = $id_persediaan . '-' . $id_hpp;
                if (!isset($grouped_stock[$key])) {
                    $grouped_stock[$key] = 0.0;
                }
                $grouped_stock[$key] += $cost_total;
            }
        }

        // Insert Grouped Retur Lines (Debit)
        foreach ($grouped_retur as $id_akun => $amount) {
            if ($amount <= 0) continue;
            
            $akun_info = $this->db->get_where('tbkeu_akun', ['id_akun' => $id_akun])->row_array();
            $nama_akun = $akun_info ? $akun_info['nama_akun'] : 'Retur Penjualan';

            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal,
                'nomor_baris' => $line_num++,
                'id_akun' => $id_akun,
                'keterangan' => 'Retur Penjualan (' . $nama_akun . ') - ' . $no_retur,
                'debit' => $amount,
                'kredit' => 0
            ]);
            $total_debit += $amount;
        }

        // Insert PPN (Debit)
        if ($total_ppn > 0) {
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal,
                'nomor_baris' => $line_num++,
                'id_akun' => $id_akun_ppn,
                'keterangan' => 'PPN Retur ' . $no_retur,
                'debit' => $total_ppn,
                'kredit' => 0
            ]);
            $total_debit += $total_ppn;
        }

        // Kredit: Sesuai tipe retur (Piutang Usaha atau Hutang Non Dagang)
        if ($total_debit > 0 && $id_akun_kredit) {
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal,
                'nomor_baris' => $line_num++,
                'id_akun' => $id_akun_kredit,
                'keterangan' => $keterangan_kredit,
                'debit' => 0,
                'kredit' => $total_debit
            ]);
            $total_kredit += $total_debit;
        }

        // Insert Stock Reversal Lines (Debit: Persediaan, Kredit: HPP)
        foreach ($grouped_stock as $key => $cost_amount) {
            if ($cost_amount <= 0) continue;
            list($id_persediaan, $id_hpp) = explode('-', $key);

            $persediaan_info = $this->db->get_where('tbkeu_akun', ['id_akun' => $id_persediaan])->row_array();
            $nama_persediaan = $persediaan_info ? $persediaan_info['nama_akun'] : 'Persediaan';

            $hpp_info = $this->db->get_where('tbkeu_akun', ['id_akun' => $id_hpp])->row_array();
            $nama_hpp = $hpp_info ? $hpp_info['nama_akun'] : 'HPP';

            // Debit: Persediaan
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal,
                'nomor_baris' => $line_num++,
                'id_akun' => $id_persediaan,
                'keterangan' => 'Reversal Persediaan Retur (' . $nama_persediaan . ') - ' . $no_retur,
                'debit' => $cost_amount,
                'kredit' => 0
            ]);
            $total_debit += $cost_amount;

            // Kredit: HPP
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal,
                'nomor_baris' => $line_num++,
                'id_akun' => $id_hpp,
                'keterangan' => 'Reversal HPP Retur (' . $nama_hpp . ') - ' . $no_retur,
                'debit' => 0,
                'kredit' => $cost_amount
            ]);
            $total_kredit += $cost_amount;
        }

        // Adjust totals in header to match exact rounding
        $this->db->where('id_jurnal', $id_jurnal)->update('tbkeu_jurnal', [
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit
        ]);

        return true;
    }

    public function accounting_sales_journal_report($start_date = '', $end_date = '', $keteranganJurnal = '', $nomorJurnal = '')
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        // Fetch headers (faktur reguler/Z induk & realisasi konsinyasi yang akan digabung ke faktur induk)
        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_type,
            j.posting_event,
            j.source_no AS referensi,
            COALESCE(NULLIF(j.source_no, ''), NULLIF(j.source_id, ''), j.nomor_jurnal) AS no_faktur,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(f.no_so, '') AS no_so,
            COALESCE(f.customer_name, '') AS pelanggan,
            COALESCE(f.tanggal_faktur, j.tanggal_transaksi) AS tanggal_faktur_induk,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join(
            'tbso_faktur_penjualan f',
            "j.source_module = 'SALES' AND f.no_faktur = REGEXP_REPLACE(COALESCE(NULLIF(j.source_no, ''), j.source_id), '-[0-9]+$', '')",
            'left',
            false
        );
        $this->db->where('j.source_module', 'SALES');
        // Kecualikan faktur pecahan H dan faktur dibatalkan
        $this->db->where("UPPER(LEFT(TRIM(COALESCE(j.source_no, '')), 1)) !=", 'H');
        $this->db->group_start();
            // Faktur induk reguler/Z (termasuk Z yang berstatus VOID karena dipecah ke faktur H)
            $this->db->group_start();
                $this->db->where('j.source_type', 'FAKTUR_PENJUALAN');
                $this->db->where("COALESCE(j.source_no, '') NOT REGEXP '-[0-9]+$'", null, false);
                $this->db->where("COALESCE(f.status, '') NOT IN ('cancelled', 'batal', 'draft')", null, false);
            $this->db->group_end();
            // Faktur konsinyasi/test market aktif (digabung ke nomor faktur induknya)
            $this->db->or_group_start();
                $this->db->where('j.source_type', 'FAKTUR_PENJUALAN_KONSINYASI');
                $this->db->where('j.status', 'POSTED');
                $this->db->where('j.reversed_at IS NULL', null, false);
            $this->db->group_end();
        $this->db->group_end();
        
        if ($start_date !== '' && $end_date !== '') {
            $this->db->where('j.tanggal_transaksi >=', $start_date);
            $this->db->where('j.tanggal_transaksi <=', $end_date);
        }

        $keteranganJurnal = trim((string) $keteranganJurnal);
        if ($keteranganJurnal !== '') {
            $this->db->group_start();
            $this->db->like('j.keterangan', $keteranganJurnal);
            $this->db->or_like('f.customer_name', $keteranganJurnal);
            $this->db->group_end();
        }

        $nomorJurnal = trim((string) $nomorJurnal);
        if ($nomorJurnal !== '') {
            $this->db->group_start();
            $this->db->like('j.nomor_jurnal', $nomorJurnal);
            $this->db->or_like('j.source_no', $nomorJurnal);
            $this->db->or_like('j.source_id', $nomorJurnal);
            $this->db->group_end();
        }

        $this->db->order_by('j.tanggal_transaksi', 'ASC');
        $this->db->order_by('j.id_jurnal', 'ASC');
        $headers = $this->db->get()->result_array();

        if (empty($headers)) {
            return [];
        }

        // Extract IDs for fetching details
        $journal_ids = array_column($headers, 'id_jurnal');

        // Fetch details
        $this->db->select("
            d.id_jurnal,
            d.nomor_baris,
            a.kode_akun,
            a.kode_akun AS kode_rekening_display,
            a.nama_akun,
            d.keterangan,
            d.debit,
            d.kredit,
            COALESCE(NULLIF(d.id_departemen, 0), '') AS cost_center,
            '' AS project_no
        ", false);
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun');
        $this->db->where_in('d.id_jurnal', $journal_ids);
        $this->db->order_by('d.id_jurnal', 'ASC');
        $this->db->order_by('d.nomor_baris', 'ASC');
        $details = $this->db->get()->result_array();

        // Group details by id_jurnal
        $details_grouped = [];
        foreach ($details as $d) {
            $details_grouped[$d['id_jurnal']][] = $d;
        }

        // Gabungkan jurnal konsinyasi/test market (-1, -2, dst.) ke nomor faktur induknya
        $finalHeaders = [];
        $groupedParentMap = [];

        foreach ($headers as $h) {
            $rowDetails = isset($details_grouped[$h['id_jurnal']]) ? $details_grouped[$h['id_jurnal']] : [];
            if (($h['source_type'] ?? '') !== 'FAKTUR_PENJUALAN_KONSINYASI') {
                $h['details'] = $rowDetails;
                $finalHeaders[] = $h;
                continue;
            }

            $parentNo = preg_replace('/-[0-9]+$/', '', trim((string) ($h['no_faktur'] ?? '')));
            $eventKey = strtoupper(trim((string) ($h['posting_event'] ?? 'SALES_INVOICE')));
            $groupKey = $parentNo . '||' . $eventKey;

            if (!isset($groupedParentMap[$groupKey])) {
                $pelanggan = trim((string) ($h['pelanggan'] ?? ''));
                $ket = ($eventKey === 'GOODS_ISSUE')
                    ? 'Penyesuaian persediaan, untuk ' . $parentNo
                    : ('Penjualan, ' . ($pelanggan !== '' ? $pelanggan : $parentNo));

                $groupedParentMap[$groupKey] = [
                    'id_jurnal'         => $h['id_jurnal'],
                    'nomor_jurnal'      => $h['nomor_jurnal'],
                    'tanggal_transaksi' => $h['tanggal_faktur_induk'] ?: $h['tanggal_transaksi'],
                    'referensi'         => $parentNo,
                    'no_faktur'         => $parentNo,
                    'keterangan'        => $ket,
                    'nilai'             => 0,
                    'status'            => $h['status'],
                    'no_so'             => $h['no_so'],
                    'pelanggan'         => '',
                    'kurs'              => 'IDR',
                    '_acc_map'          => [],
                    'details'           => []
                ];
            }

            foreach ($rowDetails as $d) {
                $accKey = ($d['kode_akun'] ?? '') . '||' . ((float) ($d['debit'] ?? 0) > 0 ? 'D' : 'K');
                if (!isset($groupedParentMap[$groupKey]['_acc_map'][$accKey])) {
                    $groupedParentMap[$groupKey]['_acc_map'][$accKey] = [
                        'kode_akun'             => $d['kode_akun'],
                        'kode_rekening_display' => $d['kode_rekening_display'],
                        'nama_akun'             => $d['nama_akun'],
                        'keterangan'            => $d['keterangan'],
                        'debit'                 => 0,
                        'kredit'                => 0,
                        'cost_center'           => $d['cost_center'],
                        'project_no'            => $d['project_no'],
                    ];
                }
                $groupedParentMap[$groupKey]['_acc_map'][$accKey]['debit'] += (float) ($d['debit'] ?? 0);
                $groupedParentMap[$groupKey]['_acc_map'][$accKey]['kredit'] += (float) ($d['kredit'] ?? 0);
            }
        }

        foreach ($groupedParentMap as $item) {
            $item['details'] = array_values($item['_acc_map']);
            unset($item['_acc_map']);
            $finalHeaders[] = $item;
        }

        usort($finalHeaders, function ($a, $b) {
            $cmpDate = strcmp((string) ($a['tanggal_transaksi'] ?? ''), (string) ($b['tanggal_transaksi'] ?? ''));
            if ($cmpDate !== 0) {
                return $cmpDate;
            }
            return ((int) ($a['id_jurnal'] ?? 0)) <=> ((int) ($b['id_jurnal'] ?? 0));
        });

        return $finalHeaders;
    }

    /**
     * Laporan Jurnal Potongan Penjualan berdasarkan jurnal aktif yang memiliki akun potongan penjualan.
     */
    public function accounting_sales_discount_journal_report($start_date = '', $end_date = '', $keteranganJurnal = '', $nomorJurnal = '')
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            COALESCE(NULLIF(j.source_no, ''), NULLIF(j.source_id, ''), j.nomor_jurnal) AS no_faktur,
            j.keterangan,
            j.status,
            COALESCE(f.customer_name, '') AS pelanggan,
            d.nomor_baris,
            a.kode_akun,
            a.kode_akun AS kode_rekening_display,
            a.nama_akun,
            d.debit,
            d.kredit,
            COALESCE(NULLIF(d.id_departemen, 0), '') AS cost_center,
            '' AS project_no
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_jurnal_detail d', 'd.id_jurnal = j.id_jurnal', 'inner');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
        $this->db->join('tbso_faktur_penjualan f', 'f.no_faktur = j.source_id OR f.no_faktur = j.source_no', 'left');
        $this->db->where('j.status', 'POSTED');
        $this->db->where('j.reversed_at IS NULL', null, false);
        $this->db->where('d.debit >', 0);
        $this->db->group_start();
        $this->db->where_in('a.kode_akun', ['41040', '41097', '410-97']);
        $this->db->or_like('a.nama_akun', 'Potongan Penjualan');
        $this->db->group_end();

        if ($start_date !== '' && $end_date !== '') {
            $this->db->where('j.tanggal_transaksi >=', $start_date);
            $this->db->where('j.tanggal_transaksi <=', $end_date);
        }

        $keteranganJurnal = trim((string) $keteranganJurnal);
        if ($keteranganJurnal !== '') {
            $this->db->group_start();
            $this->db->like('j.keterangan', $keteranganJurnal);
            $this->db->or_like('f.customer_name', $keteranganJurnal);
            $this->db->group_end();
        }

        $nomorJurnal = trim((string) $nomorJurnal);
        if ($nomorJurnal !== '') {
            $this->db->group_start();
            $this->db->like('j.nomor_jurnal', $nomorJurnal);
            $this->db->or_like('j.source_no', $nomorJurnal);
            $this->db->or_like('j.source_id', $nomorJurnal);
            $this->db->group_end();
        }

        $this->db->order_by('j.tanggal_transaksi', 'ASC');
        $this->db->order_by('j.id_jurnal', 'ASC');
        $rows = $this->db->get()->result_array();

        if (empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $amount = (float) ($row['debit'] ?? 0);
            $pelanggan = trim((string) ($row['pelanggan'] ?? ''));
            $ket = 'Potongan Penjualan';
            if ($pelanggan !== '') {
                $ket .= ', ' . $pelanggan;
            } elseif (!empty($row['keterangan'])) {
                $ket = $row['keterangan'];
            }

            $result[] = [
                'id_jurnal'         => $row['id_jurnal'],
                'nomor_jurnal'      => $row['nomor_jurnal'],
                'tanggal_transaksi' => $row['tanggal_transaksi'],
                'referensi'         => $row['referensi'],
                'no_faktur'         => $row['no_faktur'],
                'keterangan'        => $ket,
                'pelanggan'         => '',
                'details'           => [
                    [
                        'kode_akun'             => $row['kode_akun'],
                        'kode_rekening_display' => $row['kode_rekening_display'],
                        'nama_akun'             => $row['nama_akun'],
                        'debit'                 => $amount,
                        'kredit'                => 0,
                        'cost_center'           => $row['cost_center'] ?? '',
                        'project_no'            => $row['project_no'] ?? '',
                    ],
                    [
                        'kode_akun'             => '13099',
                        'kode_rekening_display' => '130-99',
                        'nama_akun'             => 'Piutang Usaha',
                        'debit'                 => 0,
                        'kredit'                => $amount,
                        'cost_center'           => $row['cost_center'] ?? '',
                        'project_no'            => $row['project_no'] ?? '',
                    ],
                ],
            ];
        }

        return $result;
    }

    /**
     * Laporan Penjualan - eFaktur format Zahir Accounting.
     */
    public function accounting_sales_efaktur_report($start_date = '', $end_date = '', $no_ref = '', $pelanggan = '', $npwp = '')
    {
        $start_date = trim((string) $start_date);
        $end_date   = trim((string) $end_date);
        $no_ref     = trim((string) $no_ref);
        $pelanggan  = trim((string) $pelanggan);
        $npwp       = trim((string) $npwp);

        $whereParts = ["u.total_bruto > 0"];
        $params     = [];

        if ($start_date !== '' && $end_date !== '') {
            $whereParts[] = 'u.tanggal_faktur >= ?';
            $whereParts[] = 'u.tanggal_faktur <= ?';
            $params[]     = $start_date;
            $params[]     = $end_date;
        }
        if ($no_ref !== '') {
            $whereParts[] = 'u.no_faktur LIKE ?';
            $params[]     = '%' . $no_ref . '%';
        }
        if ($pelanggan !== '') {
            $whereParts[] = 'u.pelanggan LIKE ?';
            $params[]     = '%' . $pelanggan . '%';
        }
        if ($npwp !== '') {
            $whereParts[] = 'u.npwp LIKE ?';
            $params[]     = '%' . $npwp . '%';
        }

        $whereSql = implode(' AND ', $whereParts);

        $sql = "
            SELECT
                u.tanggal_faktur,
                u.no_faktur,
                u.pelanggan,
                u.nomor_seri_faktur,
                u.npwp,
                ROUND(u.total_bruto / 1.11, 2) AS jumlah_dpp,
                ROUND(u.total_bruto - (u.total_bruto / 1.11), 2) AS jumlah_ppn,
                u.sudah_efaktur
            FROM (
                SELECT
                    f.tanggal_faktur,
                    f.no_faktur,
                    COALESCE(NULLIF(TRIM(c.nama_kios), ''), NULLIF(TRIM(c.nama_customer), ''), f.customer_name, '-') AS pelanggan,
                    '' AS nomor_seri_faktur,
                    COALESCE(
                        (
                            SELECT NULLIF(TRIM(ca.npwp), '')
                            FROM tb_customer_acak ca
                            WHERE (ca.kd_customer_induk = f.kd_customer OR ca.kd_customer = f.kd_customer)
                              AND COALESCE(TRIM(ca.npwp), '') <> ''
                            LIMIT 1
                        ),
                        '000000000000000'
                    ) AS npwp,
                    COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0) AS total_bruto,
                    '' AS sudah_efaktur
                FROM tbso_faktur_penjualan f
                LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
                WHERE f.status <> 'cancelled'
                  AND (f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)

                UNION ALL

                SELECT
                    fh.tanggal_faktur,
                    fh.no_faktur,
                    COALESCE(NULLIF(TRIM(fh.nama_toko), ''), NULLIF(TRIM(fh.customer_name), ''), '-') AS pelanggan,
                    '' AS nomor_seri_faktur,
                    COALESCE(
                        (
                            SELECT NULLIF(TRIM(ca.npwp), '')
                            FROM tb_customer_acak ca
                            WHERE (ca.kd_customer_induk = fh.kd_customer OR ca.kd_customer = fh.kd_customer)
                              AND COALESCE(TRIM(ca.npwp), '') <> ''
                            LIMIT 1
                        ),
                        '000000000000000'
                    ) AS npwp,
                    COALESCE(fh.grand_total, 0) AS total_bruto,
                    '' AS sudah_efaktur
                FROM tbso_faktur_h_import fh
                WHERE (fh.parent_no_faktur IS NULL OR fh.parent_no_faktur = '')
                  AND COALESCE(fh.status, '') <> 'cancelled'
            ) u
            WHERE {$whereSql}
            ORDER BY u.tanggal_faktur ASC, u.no_faktur ASC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Laporan Penjualan format Zahir Accounting:
     * - 'rincian' (Penjualan - Rincian)
     * - 'negatif-rangkuman' (Penjualan Bernilai Negatif - Rangkuman)
     * - 'negatif-sederhana' (Penjualan Bernilai Negatif - Sederhana)
     * - 'per-mata-uang-rangkuman' (Penjualan Per Mata Uang - Rangkuman)
     */
    public function accounting_sales_zahir_report($type, $start_date = '', $end_date = '', $no_ref = '', $pelanggan = '')
    {
        $start_date = trim((string) $start_date);
        $end_date   = trim((string) $end_date);
        $no_ref     = trim((string) $no_ref);
        $pelanggan  = trim((string) $pelanggan);

        if ($type === 'rincian') {
            $this->db->select("
                f.id_faktur,
                f.no_faktur,
                f.no_so,
                f.tanggal_faktur,
                f.kd_customer,
                COALESCE(NULLIF(TRIM(c.nama_kios), ''), NULLIF(TRIM(c.nama_customer), ''), f.customer_name, '-') AS pelanggan,
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), f.gudang_id, '-') AS nama_gudang,
                COALESCE(f.salesman, '-') AS salesman,
                COALESCE(f.create_by, '-') AS create_by,
                COALESCE(f.catatan, CONCAT('Penjualan, ', COALESCE(f.customer_name, ''))) AS catatan,
                f.status,
                COALESCE((SELECT SUM(pf.jumlah_pembayaran) FROM tbkeu_pembayaran_faktur pf WHERE pf.id_faktur = f.id_faktur AND COALESCE(pf.status, '') NOT IN ('cancelled', 'unposted')), 0) AS total_pembayaran
            ", false);
            $this->db->from('tbso_faktur_penjualan f');
            $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = f.gudang_id', 'left');
            $this->db->where('f.status <>', 'cancelled');
            $this->db->where('(f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)', null, false);

            if ($start_date !== '' && $end_date !== '') {
                $this->db->where('f.tanggal_faktur >=', $start_date);
                $this->db->where('f.tanggal_faktur <=', $end_date);
            }
            if ($no_ref !== '') {
                $this->db->like('f.no_faktur', $no_ref);
            }
            if ($pelanggan !== '') {
                $this->db->group_start();
                $this->db->like('f.customer_name', $pelanggan);
                $this->db->or_like('c.nama_kios', $pelanggan);
                $this->db->group_end();
            }

            $this->db->order_by('f.tanggal_faktur', 'ASC');
            $this->db->order_by('f.id_faktur', 'ASC');
            $headers = $this->db->get()->result_array();

            if (empty($headers)) {
                return [];
            }

            $fakturIds = array_column($headers, 'id_faktur');
            $detailRows = $this->db
                ->select("id_faktur, kd_barang, nama_barang, qty, satuan, hrg_satuan, disc, pajak, total_harga")
                ->from('tbso_faktur_detail')
                ->where_in('id_faktur', $fakturIds)
                ->order_by('id', 'ASC')
                ->get()
                ->result_array();

            $detailsByFaktur = [];
            foreach ($detailRows as $d) {
                $totalHrg = (float) ($d['total_harga'] ?? 0);
                $subTotalDpp = round($totalHrg / 1.11, 2);
                $detailsByFaktur[$d['id_faktur']][] = [
                    'kd_barang'   => $d['kd_barang'],
                    'nama_barang' => $d['nama_barang'],
                    'diterima'    => (float) ($d['qty'] ?? 0),
                    'dipesan'     => 0.0,
                    'satuan'      => $d['satuan'] ?? 'Pcs',
                    'harga_satuan'=> (float) ($d['hrg_satuan'] ?? 0),
                    'diskon'      => (float) ($d['disc'] ?? 0),
                    'sub_total'   => $subTotalDpp,
                    'pajak_pct'   => 11,
                    'total_harga' => $totalHrg,
                ];
            }

            foreach ($headers as &$h) {
                $items = $detailsByFaktur[$h['id_faktur']] ?? [];
                $grandTotal = array_sum(array_column($items, 'total_harga'));
                $pembayaran = (float) ($h['total_pembayaran'] ?? 0);
                $h['items']      = $items;
                $h['total']      = $grandTotal;
                $h['pembayaran'] = $pembayaran;
                $h['saldo']      = max(0, $grandTotal - $pembayaran);
            }
            unset($h);

            return $headers;
        }

        if ($type === 'negatif-rangkuman' || $type === 'negatif-sederhana') {
            $this->db->select("
                r.tanggal_retur AS tanggal,
                r.no_retur AS reference,
                COALESCE(NULLIF(r.no_faktur_potong, ''), r.no_spr, '') AS no_pesanan,
                COALESCE(NULLIF(TRIM(c.nama_kios), ''), r.nama_customer, '-') AS pelanggan,
                'IDR' AS mata_uang,
                r.total_nilai_retur,
                r.sisa_saldo_retur
            ", false);
            $this->db->from('tbrp_retur_penjualan_header r');
            $this->db->join('tb_customer c', 'c.kd_customer = r.kd_customer', 'left');
            $this->db->where('r.status_retur', 'selesai');
            $this->db->where('r.total_nilai_retur >', 0);

            if ($start_date !== '' && $end_date !== '') {
                $this->db->where('r.tanggal_retur >=', $start_date);
                $this->db->where('r.tanggal_retur <=', $end_date);
            }
            if ($no_ref !== '') {
                $this->db->like('r.no_retur', $no_ref);
            }
            if ($pelanggan !== '') {
                $this->db->group_start();
                $this->db->like('r.nama_customer', $pelanggan);
                $this->db->or_like('c.nama_kios', $pelanggan);
                $this->db->group_end();
            }

            $this->db->order_by('r.tanggal_retur', 'ASC');
            $rows = $this->db->get()->result_array();

            $result = [];
            foreach ($rows as $row) {
                $bruto = (float) ($row['total_nilai_retur'] ?? 0);
                $dpp   = round($bruto / 1.11, 2);
                $ppn   = round($bruto - $dpp, 2);
                $sisa  = (float) ($row['sisa_saldo_retur'] ?? 0);
                $bayar = max(0, $bruto - $sisa);

                $result[] = [
                    'tanggal'         => $row['tanggal'],
                    'reference'       => $row['reference'],
                    'no_pesanan'      => $row['no_pesanan'],
                    'pelanggan'       => $row['pelanggan'],
                    'mata_uang'       => 'IDR',
                    'sub_total'       => -$dpp,
                    'diskon'          => 0.0,
                    'pajak'           => -$ppn,
                    'total_penjualan' => -$bruto,
                    'pembayaran'      => -$bayar,
                    'saldo'           => -$sisa,
                ];
            }
            return $result;
        }

        // per-salesman-faktur, per-kelompok-pelanggan-faktur, sederhana, per-mata-uang-sederhana
        if (in_array($type, ['per-salesman-faktur', 'per-kelompok-pelanggan-faktur', 'sederhana', 'per-mata-uang-sederhana'], true)) {
            $whereParts = ['1=1'];
            $params     = [];

            if ($start_date !== '' && $end_date !== '') {
                $whereParts[] = 't.tanggal_faktur >= ?';
                $whereParts[] = 't.tanggal_faktur <= ?';
                $params[]     = $start_date;
                $params[]     = $end_date;
            }
            if ($no_ref !== '') {
                $whereParts[] = 't.no_faktur LIKE ?';
                $params[]     = '%' . $no_ref . '%';
            }
            if ($pelanggan !== '') {
                $whereParts[] = '(t.pelanggan_nama LIKE ? OR t.kd_customer LIKE ? OR t.salesman LIKE ?)';
                $like = '%' . $pelanggan . '%';
                array_push($params, $like, $like, $like);
            }

            $whereSql = implode(' AND ', $whereParts);
            $orderBy  = 't.tanggal_faktur ASC, t.no_faktur ASC';
            if ($type === 'per-salesman-faktur') {
                $orderBy = 't.salesman ASC, t.tanggal_faktur ASC, t.no_faktur ASC';
            } elseif ($type === 'per-kelompok-pelanggan-faktur') {
                $orderBy = 't.kelompok_pelanggan ASC, t.pelanggan_nama ASC, t.tanggal_faktur ASC, t.no_faktur ASC';
            }

            $sql = "
                SELECT * FROM (
                    SELECT
                        f.tanggal_faktur,
                        f.no_faktur,
                        COALESCE(f.no_so, '') AS order_no,
                        COALESCE(f.kd_customer, '') AS kd_customer,
                        COALESCE(NULLIF(TRIM(c.nama_kios), ''), NULLIF(TRIM(c.nama_customer), ''), f.customer_name, '-') AS pelanggan_nama,
                        COALESCE(NULLIF(TRIM(c.nama_sales), ''), 'Umum') AS kelompok_pelanggan,
                        COALESCE(NULLIF(TRIM(f.salesman), ''), NULLIF(TRIM(c.nama_sales), ''), 'Umum') AS salesman,
                        'IDR' AS mata_uang,
                        COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0) AS jumlah,
                        COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0) AS jumlah_idr
                    FROM tbso_faktur_penjualan f
                    LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
                    WHERE f.status <> 'cancelled'
                      AND (f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)

                    UNION ALL

                    SELECT
                        fh.tanggal_faktur,
                        fh.no_faktur,
                        COALESCE(fh.no_so, '') AS order_no,
                        COALESCE(fh.kd_customer, '') AS kd_customer,
                        COALESCE(NULLIF(TRIM(fh.nama_toko), ''), NULLIF(TRIM(fh.customer_name), ''), '-') AS pelanggan_nama,
                        COALESCE(NULLIF(TRIM(fh.salesman), ''), 'Umum') AS kelompok_pelanggan,
                        COALESCE(NULLIF(TRIM(fh.salesman), ''), 'Umum') AS salesman,
                        'IDR' AS mata_uang,
                        COALESCE(fh.grand_total, 0) AS jumlah,
                        COALESCE(fh.grand_total, 0) AS jumlah_idr
                    FROM tbso_faktur_h_import fh
                    WHERE (fh.parent_no_faktur IS NULL OR fh.parent_no_faktur = '')
                      AND COALESCE(fh.status, '') <> 'cancelled'
                ) t
                WHERE {$whereSql}
                ORDER BY {$orderBy}
            ";

            return $this->db->query($sql, $params)->result_array();
        }

        if ($type === 'per-kelompok-salesman') {
            $whereParts = ['1=1'];
            $params     = [];

            if ($start_date !== '' && $end_date !== '') {
                $whereParts[] = 't.tanggal_faktur >= ?';
                $whereParts[] = 't.tanggal_faktur <= ?';
                $params[]     = $start_date;
                $params[]     = $end_date;
            }
            if ($pelanggan !== '') {
                $whereParts[] = '(t.nama_salesman LIKE ? OR t.kelompok_salesman LIKE ?)';
                $like = '%' . $pelanggan . '%';
                array_push($params, $like, $like);
            }
            $whereSql = implode(' AND ', $whereParts);

            $sql = "
                SELECT
                    t.kelompok_salesman,
                    t.nama_salesman,
                    'IDR' AS mata_uang,
                    COALESCE(SUM(t.nilai), 0) AS nilai,
                    COALESCE(SUM(t.pembayaran), 0) AS pembayaran,
                    COALESCE(SUM(t.nilai - t.pembayaran), 0) AS saldo
                FROM (
                    SELECT
                        f.tanggal_faktur,
                        COALESCE(NULLIF(TRIM(c.nama_sales), ''), 'general') AS kelompok_salesman,
                        COALESCE(NULLIF(TRIM(f.salesman), ''), NULLIF(TRIM(c.nama_sales), ''), 'Others') AS nama_salesman,
                        COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0) AS nilai,
                        COALESCE((SELECT SUM(pf.jumlah_pembayaran) FROM tbkeu_pembayaran_faktur pf WHERE pf.id_faktur = f.id_faktur AND COALESCE(pf.status, '') NOT IN ('cancelled', 'unposted')), 0) AS pembayaran
                    FROM tbso_faktur_penjualan f
                    LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
                    WHERE f.status <> 'cancelled'
                      AND (f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)

                    UNION ALL

                    SELECT
                        fh.tanggal_faktur,
                        COALESCE(NULLIF(TRIM(fh.salesman), ''), 'general') AS kelompok_salesman,
                        COALESCE(NULLIF(TRIM(fh.salesman), ''), 'Others') AS nama_salesman,
                        COALESCE(fh.grand_total, 0) AS nilai,
                        COALESCE(fh.total_bayar, 0) AS pembayaran
                    FROM tbso_faktur_h_import fh
                    WHERE (fh.parent_no_faktur IS NULL OR fh.parent_no_faktur = '')
                      AND COALESCE(fh.status, '') <> 'cancelled'
                ) t
                WHERE {$whereSql}
                GROUP BY t.kelompok_salesman, t.nama_salesman
                ORDER BY t.kelompok_salesman ASC, t.nama_salesman ASC
            ";

            return $this->db->query($sql, $params)->result_array();
        }

        if ($type === 'retur-rangkuman' || $type === 'retur-sederhana') {
            $this->db->select("
                r.tanggal_retur AS tanggal,
                r.no_retur AS reference,
                COALESCE(NULLIF(r.no_faktur_potong, ''), r.no_spr, '') AS no_pesanan,
                r.kd_customer,
                COALESCE(NULLIF(TRIM(c.nama_kios), ''), r.nama_customer, '-') AS pelanggan,
                'IDR' AS mata_uang,
                r.total_nilai_retur,
                r.sisa_saldo_retur
            ", false);
            $this->db->from('tbrp_retur_penjualan_header r');
            $this->db->join('tb_customer c', 'c.kd_customer = r.kd_customer', 'left');
            $this->db->where_not_in('r.status_retur', ['draft', 'ditolak']);

            if ($start_date !== '' && $end_date !== '') {
                $this->db->where('r.tanggal_retur >=', $start_date);
                $this->db->where('r.tanggal_retur <=', $end_date);
            }
            if ($no_ref !== '') {
                $this->db->group_start();
                $this->db->like('r.no_retur', $no_ref);
                $this->db->or_like('r.no_spr', $no_ref);
                $this->db->group_end();
            }
            if ($pelanggan !== '') {
                $this->db->group_start();
                $this->db->like('r.nama_customer', $pelanggan);
                $this->db->or_like('c.nama_kios', $pelanggan);
                $this->db->group_end();
            }

            $this->db->order_by('r.tanggal_retur', 'ASC');
            $this->db->order_by('r.id_retur', 'ASC');
            $rows = $this->db->get()->result_array();

            $result = [];
            foreach ($rows as $row) {
                $bruto = (float) ($row['total_nilai_retur'] ?? 0);
                $dpp   = round($bruto / 1.11, 2);
                $ppn   = round($bruto - $dpp, 2);
                $sisa  = (float) ($row['sisa_saldo_retur'] ?? 0);
                $bayar = max(0, $bruto - $sisa);

                $result[] = [
                    'tanggal'     => $row['tanggal'],
                    'reference'   => $row['reference'],
                    'no_pesanan'  => $row['no_pesanan'],
                    'kd_customer' => $row['kd_customer'],
                    'pelanggan'   => $row['pelanggan'],
                    'mata_uang'   => 'IDR',
                    'sub_total'   => $dpp,
                    'diskon'      => 0.0,
                    'pajak'       => $ppn,
                    'total_retur' => $bruto,
                    'pembayaran'  => $bayar,
                    'saldo'       => $sisa,
                ];
            }
            return $result;
        }

        if ($type === 'retur-rincian') {
            $this->db->select("
                r.id_retur,
                r.no_retur,
                r.no_spr,
                r.tanggal_retur,
                r.kd_customer,
                COALESCE(NULLIF(TRIM(c.nama_kios), ''), r.nama_customer, '-') AS pelanggan,
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), '(B) Gdg. Induk') AS nama_gudang,
                COALESCE(r.nama_sales, '-') AS salesman,
                COALESCE(r.create_by_retur, '-') AS create_by,
                r.status_retur,
                r.total_nilai_retur,
                r.sisa_saldo_retur
            ", false);
            $this->db->from('tbrp_retur_penjualan_header r');
            $this->db->join('tb_customer c', 'c.kd_customer = r.kd_customer', 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = r.gudang_id', 'left');
            $this->db->where_not_in('r.status_retur', ['draft', 'ditolak']);

            if ($start_date !== '' && $end_date !== '') {
                $this->db->where('r.tanggal_retur >=', $start_date);
                $this->db->where('r.tanggal_retur <=', $end_date);
            }
            if ($no_ref !== '') {
                $this->db->group_start();
                $this->db->like('r.no_retur', $no_ref);
                $this->db->or_like('r.no_spr', $no_ref);
                $this->db->group_end();
            }
            if ($pelanggan !== '') {
                $this->db->group_start();
                $this->db->like('r.nama_customer', $pelanggan);
                $this->db->or_like('c.nama_kios', $pelanggan);
                $this->db->group_end();
            }

            $this->db->order_by('r.tanggal_retur', 'ASC');
            $this->db->order_by('r.id_retur', 'ASC');
            $headers = $this->db->get()->result_array();

            if (empty($headers)) {
                return [];
            }

            $returIds = array_column($headers, 'id_retur');
            $detailRows = $this->db
                ->select("id_retur, kd_barang, nama_barang, satuan, qty_retur, harga_satuan")
                ->from('tbrp_retur_penjualan_detail')
                ->where_in('id_retur', $returIds)
                ->order_by('no_urut', 'ASC')
                ->get()
                ->result_array();

            $detailsByRetur = [];
            foreach ($detailRows as $d) {
                $qty   = (float) ($d['qty_retur'] ?? 0);
                $harga = (float) ($d['harga_satuan'] ?? 0);
                $bruto = $qty * $harga;
                $subDpp = round($bruto / 1.11, 2);

                $detailsByRetur[$d['id_retur']][] = [
                    'kd_barang'    => $d['kd_barang'],
                    'nama_barang'  => $d['nama_barang'],
                    'kembali'      => $qty,
                    'telah_dipesan'=> 0.0,
                    'satuan'       => $d['satuan'] ?? 'Pcs',
                    'harga_satuan' => $harga,
                    'diskon'       => 0,
                    'sub_total'    => $subDpp,
                    'pajak_pct'    => 11,
                ];
            }

            foreach ($headers as &$h) {
                $tot  = (float) ($h['total_nilai_retur'] ?? 0);
                $sisa = (float) ($h['sisa_saldo_retur'] ?? 0);
                $h['items']      = $detailsByRetur[$h['id_retur']] ?? [];
                $h['catatan']    = 'Retur Penjualan, ' . $h['pelanggan'];
                $h['total']      = $tot;
                $h['pembayaran'] = max(0, $tot - $sisa);
                $h['saldo']      = $sisa;
            }
            unset($h);

            return $headers;
        }

        // per-mata-uang-rangkuman
        $whereDate = '';
        $params    = [];
        if ($start_date !== '' && $end_date !== '') {
            $whereDate = ' AND t.tanggal_faktur >= ? AND t.tanggal_faktur <= ?';
            $params    = [$start_date, $end_date];
        }

        $sql = "
            SELECT 'IDR' AS mata_uang, COALESCE(SUM(t.total_bruto), 0) AS jumlah, COALESCE(SUM(t.total_bruto), 0) AS jumlah_idr
            FROM (
                SELECT f.tanggal_faktur,
                       COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0) AS total_bruto
                FROM tbso_faktur_penjualan f
                WHERE f.status <> 'cancelled' AND (f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)
                UNION ALL
                SELECT fh.tanggal_faktur, COALESCE(fh.grand_total, 0) AS total_bruto
                FROM tbso_faktur_h_import fh
                WHERE (fh.parent_no_faktur IS NULL OR fh.parent_no_faktur = '') AND COALESCE(fh.status, '') <> 'cancelled'
            ) t
            WHERE 1=1 {$whereDate}
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Laporan Piutang Usaha format Zahir Accounting (6 varian).
     */
    public function accounting_receivable_zahir_report($type, $start_date = '', $end_date = '', $search = '')
    {
        $start_date = trim((string) $start_date);
        $end_date   = trim((string) $end_date);
        $search     = trim((string) $search);

        if ($type === 'pembayaran-pelanggan') {
            $this->db->select("
                pf.id_pembayaran,
                pf.tanggal_pembayaran,
                COALESCE(NULLIF(pf.no_bg, ''), CONCAT('PAY-', pf.id_pembayaran)) AS no_ref,
                pf.no_faktur,
                f.tanggal_faktur,
                f.kd_customer,
                COALESCE(NULLIF(TRIM(c.nama_kios), ''), f.customer_name, '-') AS nama_pelanggan,
                COALESCE(pf.jumlah_diskon, 0) AS diskon,
                COALESCE(pf.jumlah_pembayaran, 0) AS pembayaran
            ", false);
            $this->db->from('tbkeu_pembayaran_faktur pf');
            $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = pf.id_faktur', 'left');
            $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
            $this->db->where("COALESCE(pf.status, '') NOT IN ('cancelled', 'unposted')", null, false);

            if ($start_date !== '' && $end_date !== '') {
                $this->db->where('pf.tanggal_pembayaran >=', $start_date);
                $this->db->where('pf.tanggal_pembayaran <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('pf.no_faktur', $search);
                $this->db->or_like('f.customer_name', $search);
                $this->db->or_like('c.nama_kios', $search);
                $this->db->or_like('pf.no_bg', $search);
                $this->db->group_end();
            }
            $this->db->order_by('pf.tanggal_pembayaran', 'ASC');
            $this->db->order_by('pf.id_pembayaran', 'ASC');
            return $this->db->get()->result_array();
        }

        if ($type === 'mutasi-piutang') {
            $whereCust = '';
            $params    = [$start_date, $start_date, $start_date, $end_date, $start_date, $end_date];
            if ($search !== '') {
                $whereCust = ' AND (c.nama_kios LIKE ? OR c.nama_customer LIKE ? OR c.kd_customer LIKE ? OR c.nama_sales LIKE ?)';
                $like      = '%' . $search . '%';
                array_push($params, $like, $like, $like, $like);
            }

            $sql = "
                SELECT
                    COALESCE(NULLIF(TRIM(c.nama_sales), ''), 'Umum') AS kelompok,
                    c.kd_customer,
                    COALESCE(NULLIF(TRIM(c.nama_kios), ''), c.nama_customer, '-') AS nama_pelanggan,
                    'IDR' AS mata_uang,
                    (
                        COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_penjualan f INNER JOIN tbso_faktur_detail d ON d.id_faktur = f.id_faktur WHERE f.kd_customer = c.kd_customer AND f.status <> 'cancelled' AND (f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0) AND f.tanggal_faktur < ?), 0)
                        -
                        COALESCE((SELECT SUM(pf.jumlah_pembayaran + COALESCE(pf.jumlah_diskon, 0)) FROM tbkeu_pembayaran_faktur pf INNER JOIN tbso_faktur_penjualan f ON f.id_faktur = pf.id_faktur WHERE f.kd_customer = c.kd_customer AND COALESCE(pf.status, '') NOT IN ('cancelled', 'unposted') AND pf.tanggal_pembayaran < ?), 0)
                    ) AS saldo_awal,
                    COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_penjualan f INNER JOIN tbso_faktur_detail d ON d.id_faktur = f.id_faktur WHERE f.kd_customer = c.kd_customer AND f.status <> 'cancelled' AND (f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0) AND f.tanggal_faktur >= ? AND f.tanggal_faktur <= ?), 0) AS debet,
                    COALESCE((SELECT SUM(pf.jumlah_pembayaran + COALESCE(pf.jumlah_diskon, 0)) FROM tbkeu_pembayaran_faktur pf INNER JOIN tbso_faktur_penjualan f ON f.id_faktur = pf.id_faktur WHERE f.kd_customer = c.kd_customer AND COALESCE(pf.status, '') NOT IN ('cancelled', 'unposted') AND pf.tanggal_pembayaran >= ? AND pf.tanggal_pembayaran <= ?), 0) AS kredit
                FROM tb_customer c
                WHERE 1=1 {$whereCust}
                HAVING ABS(saldo_awal) > 0.001 OR debet > 0 OR kredit > 0
                ORDER BY kelompok ASC, nama_pelanggan ASC
            ";

            $rows = $this->db->query($sql, $params)->result_array();
            foreach ($rows as &$r) {
                $r['saldo_awal']  = (float) $r['saldo_awal'];
                $r['debet']       = (float) $r['debet'];
                $r['kredit']      = (float) $r['kredit'];
                $r['saldo_akhir'] = $r['saldo_awal'] + $r['debet'] - $r['kredit'];
            }
            unset($r);
            return $rows;
        }

        if ($type === 'piutang-per-mata-uang-rangkuman') {
            $asOf = $end_date !== '' ? $end_date : date('Y-m-d');
            $whereSearch = '';
            $params = [$asOf, $asOf, $asOf];
            if ($search !== '') {
                $whereSearch = ' AND (t.nama_pelanggan LIKE ? OR t.kd_customer LIKE ?)';
                $like = '%' . $search . '%';
                array_push($params, $like, $like);
            }

            $sql = "
                SELECT
                    t.kd_customer,
                    t.nama_pelanggan,
                    'IDR' AS mata_uang,
                    SUM(CASE WHEN t.umur_hari <= 30 THEN t.sisa ELSE 0 END) AS bucket_0_30,
                    SUM(CASE WHEN t.umur_hari > 30 AND t.umur_hari <= 60 THEN t.sisa ELSE 0 END) AS bucket_30_60,
                    SUM(CASE WHEN t.umur_hari > 60 AND t.umur_hari <= 90 THEN t.sisa ELSE 0 END) AS bucket_60_90,
                    SUM(CASE WHEN t.umur_hari > 90 THEN t.sisa ELSE 0 END) AS bucket_gt_90
                FROM (
                    SELECT
                        f.kd_customer,
                        COALESCE(NULLIF(TRIM(c.nama_kios), ''), f.customer_name, '-') AS nama_pelanggan,
                        DATEDIFF(?, COALESCE(f.tanggal_jatuh_tempo, f.tanggal_faktur)) AS umur_hari,
                        (
                            COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0)
                            -
                            COALESCE((SELECT SUM(pf.jumlah_pembayaran + COALESCE(pf.jumlah_diskon, 0)) FROM tbkeu_pembayaran_faktur pf WHERE pf.id_faktur = f.id_faktur AND COALESCE(pf.status, '') NOT IN ('cancelled', 'unposted') AND pf.tanggal_pembayaran <= ?), 0)
                        ) AS sisa
                    FROM tbso_faktur_penjualan f
                    LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
                    WHERE f.status <> 'cancelled'
                      AND (f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)
                      AND f.tanggal_faktur <= ?
                ) t
                WHERE ABS(t.sisa) > 0.001 {$whereSearch}
                GROUP BY t.kd_customer, t.nama_pelanggan
                ORDER BY t.nama_pelanggan ASC
            ";

            return $this->db->query($sql, $params)->result_array();
        }

        if (in_array($type, [
            'piutang-per-pelanggan-rincian',
            'piutang-per-salesman-rangkuman',
            'piutang-per-salesman-rincian',
            'piutang-per-mata-uang-rincian',
            'surat-tagihan-pelanggan',
        ], true)) {
            $asOf = $end_date !== '' ? $end_date : date('Y-m-d');
            $whereParts = ['1=1'];
            $params     = [$asOf, $asOf, $asOf];

            if ($type === 'surat-tagihan-pelanggan') {
                $whereParts[] = 't.sisa > 0.001';
            } else {
                $whereParts[] = 'ABS(t.sisa) > 0.001';
            }

            if ($search !== '') {
                $whereParts[] = '(t.nama_pelanggan LIKE ? OR t.kd_customer LIKE ? OR t.salesman LIKE ? OR t.no_faktur LIKE ?)';
                $like = '%' . $search . '%';
                array_push($params, $like, $like, $like, $like);
            }

            $whereSql = implode(' AND ', $whereParts);
            $orderBy  = ($type === 'piutang-per-salesman-rangkuman' || $type === 'piutang-per-salesman-rincian')
                ? 't.salesman ASC, t.nama_pelanggan ASC, t.tanggal_faktur ASC'
                : 't.nama_pelanggan ASC, t.tanggal_faktur ASC';

            $sql = "
                SELECT
                    t.*,
                    CASE WHEN t.umur_hari <= 30 THEN t.sisa ELSE 0 END AS bucket_0_30,
                    CASE WHEN t.umur_hari > 30 AND t.umur_hari <= 60 THEN t.sisa ELSE 0 END AS bucket_30_60,
                    CASE WHEN t.umur_hari > 60 AND t.umur_hari <= 90 THEN t.sisa ELSE 0 END AS bucket_60_90,
                    CASE WHEN t.umur_hari > 90 THEN t.sisa ELSE 0 END AS bucket_gt_90
                FROM (
                    SELECT
                        f.id_faktur,
                        f.no_faktur,
                        f.tanggal_faktur,
                        COALESCE(f.tanggal_jatuh_tempo, f.tanggal_faktur) AS jatuh_tempo,
                        f.kd_customer,
                        COALESCE(NULLIF(TRIM(c.nama_kios), ''), f.customer_name, '-') AS nama_pelanggan,
                        COALESCE(NULLIF(TRIM(f.salesman), ''), NULLIF(TRIM(c.nama_sales), ''), 'Umum') AS salesman,
                        'IDR' AS mata_uang,
                        DATEDIFF(?, COALESCE(f.tanggal_jatuh_tempo, f.tanggal_faktur)) AS umur_hari,
                        (
                            COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0)
                            -
                            COALESCE((SELECT SUM(pf.jumlah_pembayaran + COALESCE(pf.jumlah_diskon, 0)) FROM tbkeu_pembayaran_faktur pf WHERE pf.id_faktur = f.id_faktur AND COALESCE(pf.status, '') NOT IN ('cancelled', 'unposted') AND pf.tanggal_pembayaran <= ?), 0)
                        ) AS sisa
                    FROM tbso_faktur_penjualan f
                    LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
                    WHERE f.status <> 'cancelled'
                      AND (f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)
                      AND f.tanggal_faktur <= ?
                ) t
                WHERE {$whereSql}
                ORDER BY {$orderBy}
            ";

            return $this->db->query($sql, $params)->result_array();
        }

        // kartu-piutang-salesman, kartu-piutang-mata-uang-dasar, kartu-piutang-salesman-rangkuman
        $asOf = $end_date !== '' ? $end_date : date('Y-m-d');
        $this->db->select("
            f.id_faktur,
            f.no_faktur,
            f.tanggal_faktur,
            COALESCE(f.tanggal_jatuh_tempo, f.tanggal_faktur) AS jatuh_tempo,
            f.kd_customer,
            COALESCE(NULLIF(TRIM(c.nama_kios), ''), f.customer_name, '-') AS nama_pelanggan,
            COALESCE(NULLIF(TRIM(f.salesman), ''), NULLIF(TRIM(c.nama_sales), ''), 'Umum') AS salesman,
            COALESCE((SELECT SUM(d.total_harga) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0) AS nilai_faktur
        ", false);
        $this->db->from('tbso_faktur_penjualan f');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        $this->db->where('f.status <>', 'cancelled');
        $this->db->where('(f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)', null, false);
        if ($start_date !== '' && $end_date !== '') {
            $this->db->where('f.tanggal_faktur >=', $start_date);
            $this->db->where('f.tanggal_faktur <=', $end_date);
        } else {
            $this->db->where('f.tanggal_faktur <=', $asOf);
        }
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('f.no_faktur', $search);
            $this->db->or_like('f.customer_name', $search);
            $this->db->or_like('c.nama_kios', $search);
            $this->db->or_like('f.kd_customer', $search);
            $this->db->or_like('f.salesman', $search);
            $this->db->group_end();
        }
        if ($type === 'kartu-piutang-mata-uang-dasar') {
            $this->db->order_by('nama_pelanggan', 'ASC');
        } else {
            $this->db->order_by('salesman', 'ASC');
            $this->db->order_by('nama_pelanggan', 'ASC');
        }
        $this->db->order_by('f.tanggal_faktur', 'ASC');
        $fakturs = $this->db->get()->result_array();

        if (empty($fakturs)) {
            return [];
        }

        $fakturIds = array_column($fakturs, 'id_faktur');
        $payRows = $this->db
            ->select("id_faktur, id_pembayaran, tanggal_pembayaran, COALESCE(NULLIF(no_bg, ''), CONCAT('PAY-', id_pembayaran)) AS no_ref, jumlah_pembayaran, COALESCE(jumlah_diskon, 0) AS jumlah_diskon", false)
            ->from('tbkeu_pembayaran_faktur')
            ->where_in('id_faktur', $fakturIds)
            ->where("COALESCE(status, '') NOT IN ('cancelled', 'unposted')", null, false)
            ->order_by('tanggal_pembayaran', 'ASC')
            ->order_by('id_pembayaran', 'ASC')
            ->get()
            ->result_array();

        $payByFaktur = [];
        foreach ($payRows as $pr) {
            $payByFaktur[$pr['id_faktur']][] = $pr;
        }

        foreach ($fakturs as &$f) {
            $payments = $payByFaktur[$f['id_faktur']] ?? [];
            $totBayar = 0.0;
            $payList  = [];
            foreach ($payments as $p) {
                $amt  = (float) ($p['jumlah_pembayaran'] ?? 0);
                $disc = (float) ($p['jumlah_diskon'] ?? 0);
                $totBayar += $amt + $disc;
                if ($amt > 0) {
                    $payList[] = [
                        'tanggal' => $p['tanggal_pembayaran'],
                        'tp'      => 'CR',
                        'no_ref'  => $p['no_ref'],
                        'kredit'  => $amt,
                    ];
                }
                if ($disc > 0) {
                    $payList[] = [
                        'tanggal' => $p['tanggal_pembayaran'],
                        'tp'      => 'SD',
                        'no_ref'  => 'DISC-' . $p['id_pembayaran'],
                        'kredit'  => $disc,
                    ];
                }
            }
            $nilai = (float) ($f['nilai_faktur'] ?? 0);
            $f['debet']       = $nilai;
            $f['kredit']      = $totBayar;
            $f['saldo']       = $nilai - $totBayar;
            $f['pembayaran']  = $payList;
        }
        unset($f);

        return $fakturs;
    }

    /**
     * Memproses Jurnal Acak (Q) dan Jurnal (A) untuk Faktur Pecahan Z berdasarkan skema potongan
     * Skema Potongan:
     * - 0  : Tanpa Potongan
     * - 20 : Potongan 20% (Tax)
     * - 40 : Potongan 40% (20% Tax + 20% Fee Petugas)
     *
     * Faktur Induk (Z) tetap dicatat lunas tanpa potongan pada Jurnal Asli Z.
     * Potongan dialokasikan ke jurnal pecahan (Jurnal Acak Q & Jurnal A).
     */
    private function _post_jurnal_pecahan_faktur_z($id_pembayaran, $data_pembayaran, $id_akun_debit, $id_akun_kredit, $userId, $id_jenis_jurnal)
    {
        $no_faktur = $data_pembayaran['no_faktur'] ?? '';
        $id_faktur = (int)($data_pembayaran['id_faktur'] ?? 0);
        $jumlah = (float)($data_pembayaran['jumlah_pembayaran'] ?? 0);
        $metode = $data_pembayaran['metode_pembayaran'] ?? '';
        $tanggal = $data_pembayaran['tanggal_pembayaran'] ?? date('Y-m-d');

        if ($id_faktur <= 0 && !empty($no_faktur)) {
            $fRow = $this->db->select('id_faktur')->where('no_faktur', $no_faktur)->get('tbso_faktur_penjualan')->row();
            if ($fRow) {
                $id_faktur = (int)$fRow->id_faktur;
            }
        }

        if ($id_faktur <= 0 || !$this->db->table_exists('tbso_faktur_z_pecah')) {
            return false;
        }

        // Deteksi skema potongan otomatis dari faktur pecahan (tbso_faktur_z_pecah)
        $select_skema = $this->db->field_exists('skema_potongan', 'tbso_faktur_z_pecah') ? 'p.skema_potongan,' : '';

        $pecahans = $this->db
            ->select('p.id_pecah, p.no_faktur, ' . $select_skema . ' p.kd_customer, p.customer_name, p.tanggal_faktur, COALESCE(SUM(pd.total_harga), 0) AS total_pecahan')
            ->from('tbso_faktur_z_pecah p')
            ->join('tbso_faktur_z_pecah_detail pd', 'pd.id_pecah = p.id_pecah', 'left')
            ->where('p.parent_id_faktur', $id_faktur)
            ->where("p.status !=", 'cancelled')
            ->group_by('p.id_pecah')
            ->order_by('p.tanggal_faktur', 'ASC')
            ->order_by('p.id_pecah', 'ASC')
            ->get()
            ->result_array();

        if (empty($pecahans)) {
            return false;
        }

        // Ambil nama customer Faktur Z induk
        $customerIndukName = '';
        $fInduk = $this->db
            ->select('c.nama_customer')
            ->from('tbso_faktur_penjualan f')
            ->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left')
            ->where('f.id_faktur', $id_faktur)
            ->get()
            ->row();
        if ($fInduk && !empty($fInduk->nama_customer)) {
            $customerIndukName = trim($fInduk->nama_customer);
        }

        // Cari Akun Tambahan:
        // 1. Akun Q Kas (11010 / 110-10) untuk selisih potongan pada Jurnal Acak Q
        $akun_q_kas = $this->db->get_where('tbkeu_akun', ['kode_akun' => '11010'])->row_array();
        if (!$akun_q_kas) {
            $this->db->like('nama_akun', 'Q Kas');
            $akun_q_kas = $this->db->get('tbkeu_akun')->row_array();
        }
        $id_akun_q_kas = $akun_q_kas ? (int)$akun_q_kas['id_akun'] : null;

        // 2. Akun A Kas (11030 / 110-30) untuk Debit Jurnal A
        $akun_a_kas = $this->db->get_where('tbkeu_akun', ['kode_akun' => '11030'])->row_array();
        if (!$akun_a_kas) {
            $this->db->like('nama_akun', 'A Kas');
            $akun_a_kas = $this->db->get('tbkeu_akun')->row_array();
        }
        $id_akun_a_kas = $akun_a_kas ? (int)$akun_a_kas['id_akun'] : null;

        // 3. Akun A PND atas Pendapatan Pot Penj KIU (Tax) (13036 / 13040)
        $akun_tax = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13036'])->row_array();
        if (!$akun_tax) {
            $akun_tax = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13040'])->row_array();
        }
        if (!$akun_tax) {
            $this->db->like('nama_akun', 'Pot Penj KIU');
            $akun_tax = $this->db->get('tbkeu_akun')->row_array();
        }
        $id_akun_tax = $akun_tax ? (int)$akun_tax['id_akun'] : null;

        // 4. Akun A PND atas Pendapatan Pot Penj KIU (Fee Petugas) (13041)
        $akun_fee = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13041'])->row_array();
        if (!$akun_fee) {
            $this->db->like('nama_akun', 'Fee Petugas');
            $akun_fee = $this->db->get('tbkeu_akun')->row_array();
        }
        $id_akun_fee = $akun_fee ? (int)$akun_fee['id_akun'] : null;

        // ── Deteksi Skema Potongan Otomatis (0%, 20%, atau 40%) ──
        $skema_potongan_z = 20; // Default standar Karisma
        if (!empty($pecahans[0]['skema_potongan'])) {
            $skema_potongan_z = (int)$pecahans[0]['skema_potongan'];
        } else {
            // Deteksi cerdas dari rasio nilai total pecahan terhadap Faktur Z induk
            $tot_z_induk = (float)$this->db->select('SUM(total_harga) AS tot')->where('id_faktur', $id_faktur)->get('tbso_faktur_detail')->row()->tot;
            $tot_h_pecahan = 0.0;
            foreach ($pecahans as $p) {
                $tot_h_pecahan += (float)$p['total_pecahan'];
            }
            if ($tot_z_induk > 0 && $tot_h_pecahan > 0) {
                $ratio_h_z = $tot_h_pecahan / $tot_z_induk;
                if ($ratio_h_z <= 0.65) {
                    $skema_potongan_z = 40; // Potongan 40% (Netto ~60%)
                } elseif ($ratio_h_z <= 0.85) {
                    $skema_potongan_z = 20; // Potongan 20% (Netto ~80%)
                } else {
                    $skema_potongan_z = 0;  // Tanpa Potongan (Netto ~100%)
                }
            }
        }

        // Tentukan faktor pembagi untuk mendapatkan nilai bruto dasar
        $pembagi_bruto = ($skema_potongan_z === 40) ? 0.6 : (($skema_potongan_z === 20) ? 0.8 : 1.0);

        // ── Bersihkan Jurnal Pecahan (Q) & Jurnal A Lama Milik id_pembayaran ini jika pernah ada (Idempotence) ──
        $h_nos = array_column($pecahans, 'no_faktur');
        if (!empty($h_nos)) {
            $old_q = $this->db->select('id_jurnal')
                ->from('tbkeu_jurnal')
                ->where('source_module', 'KEUANGAN')
                ->where('source_type', 'PEMBAYARAN_FAKTUR')
                ->where('source_id', $id_pembayaran)
                ->where_in('source_no', $h_nos)
                ->get()->result_array();
            if (!empty($old_q)) {
                $oq_ids = array_column($old_q, 'id_jurnal');
                $this->db->where_in('id_jurnal', $oq_ids)->delete('tbkeu_jurnal_detail');
                $this->db->where_in('id_jurnal', $oq_ids)->delete('tbkeu_jurnal');
            }
        }

        // Bersihkan Jurnal A lama milik id_pembayaran ini jika pernah ada
        $old_a = $this->db->select('id_jurnal')
            ->from('tbkeu_jurnal')
            ->where('source_module', 'KEUANGAN')
            ->where('source_type', 'PEMBAYARAN_FAKTUR')
            ->where('source_id', $id_pembayaran)
            ->where('source_no', $no_faktur)
            ->like('keterangan', 'PND u/Pot')
            ->get()->result_array();
        if (!empty($old_a)) {
            $oa_ids = array_column($old_a, 'id_jurnal');
            $this->db->where_in('id_jurnal', $oa_ids)->delete('tbkeu_jurnal_detail');
            $this->db->where_in('id_jurnal', $oa_ids)->delete('tbkeu_jurnal');
        }

        // ── Ambil Total Pembayaran Bruto yang SUDAH DIBAYAR untuk Masing-Masing Faktur H dari Pembayaran Sebelumnya ──
        $existing_paid_h = [];
        if (!empty($h_nos)) {
            $prev_jurnals = $this->db
                ->select('source_no, COALESCE(SUM(total_debit), 0) AS total_sudah_bayar')
                ->from('tbkeu_jurnal')
                ->where('source_module', 'KEUANGAN')
                ->where('source_type', 'PEMBAYARAN_FAKTUR')
                ->where_in('source_no', $h_nos)
                ->where('source_id !=', $id_pembayaran)
                ->where('status', 'POSTED')
                ->group_by('source_no')
                ->get()
                ->result_array();
            foreach ($prev_jurnals as $pj) {
                $existing_paid_h[$pj['source_no']] = (float)$pj['total_sudah_bayar'];
            }
        }

        // ── 1. PEMBUATAN JURNAL ACAK (Q) DENGAN PRIORITAS FIFO (URUTAN PECAHAN LAMA LUNAS DULU) ──
        $sisa_dana_bayar = (float)$jumlah;
        $total_seluruh_potongan = 0.0;
        $count_pecahan = count($pecahans);

        foreach ($pecahans as $idx => $pec) {
            if ($sisa_dana_bayar <= 0.001) {
                // Dana pembayaran sudah habis diserap oleh pecahan-pecahan yang lebih awal.
                // Pecahan selanjutnya BELUM ADA PEMBAYARAN SAMA SEKALI.
                break;
            }

            $no_h = $pec['no_faktur'];
            $val_netto = (float)$pec['total_pecahan'];
            $val_bruto_target = ($val_netto > 0) ? round($val_netto / $pembagi_bruto, 2) : 0.0;

            $sudah_bayar_bruto = $existing_paid_h[$no_h] ?? 0.0;
            $sisa_bruto_h = max(0.0, round($val_bruto_target - $sudah_bayar_bruto, 2));

            if ($sisa_bruto_h <= 0.001) {
                // Pecahan ini sudah lunas dari pembayaran sebelumnya, lanjut ke pecahan berikutnya
                continue;
            }

            $is_last = ($idx === $count_pecahan - 1);
            if ($is_last) {
                // Pecahan terakhir menyerap seluruh sisa dana pembayaran yang tersedia
                $bruto_pecahan = round($sisa_dana_bayar, 2);
            } else {
                // Prioritaskan melunasi pecahan lama ini hingga target plafon brutonya terpenuhi
                $bruto_pecahan = min($sisa_dana_bayar, $sisa_bruto_h);
                $bruto_pecahan = round($bruto_pecahan, 2);
            }

            if ($bruto_pecahan <= 0.001) {
                continue;
            }

            $sisa_dana_bayar = max(0.0, round($sisa_dana_bayar - $bruto_pecahan, 2));

            if ($skema_potongan_z === 20) {
                $potongan_pecahan = round($bruto_pecahan * 0.20, 2);
                $netto_pecahan = $bruto_pecahan - $potongan_pecahan;
            } elseif ($skema_potongan_z === 40) {
                $potongan_pecahan = round($bruto_pecahan * 0.40, 2);
                $netto_pecahan = $bruto_pecahan - $potongan_pecahan;
            } else {
                $potongan_pecahan = 0.0;
                $netto_pecahan = $bruto_pecahan;
            }
            $total_seluruh_potongan += $potongan_pecahan;

            $no_jurnal_q = $this->generate_no_jurnal($metode);
            $nama_cust_q = !empty($pec['kontak_person']) && trim((string)$pec['kontak_person']) !== '-'
                ? trim((string)$pec['kontak_person'])
                : (!empty($pec['customer_name']) ? $pec['customer_name'] : $pec['kd_customer']);
            $ket_q = 'Penerimaan dari ' . $nama_cust_q;

            $jurnal_q_data = [
                'nomor_jurnal'      => $no_jurnal_q,
                'id_jenis_jurnal'   => $id_jenis_jurnal,
                'tanggal_transaksi' => $tanggal,
                'keterangan'        => $ket_q,
                'status'            => 'POSTED',
                'source_module'     => 'KEUANGAN',
                'source_type'       => 'PEMBAYARAN_FAKTUR',
                'source_id'         => $id_pembayaran,
                'source_no'         => $pec['no_faktur'],
                'total_debit'       => $bruto_pecahan,
                'total_kredit'      => $bruto_pecahan,
                'created_by'        => $userId,
                'created_at'        => date('Y-m-d H:i:s'),
                'posted_by'         => $userId,
                'posted_at'         => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tbkeu_jurnal', $jurnal_q_data);
            $id_jurnal_q = $this->db->insert_id();

            $line_q = 1;
            // Baris Debit: Kas / Bank yang dipilih (Q Mandiri, Q Kas Ayat Silang, dsb)
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal'   => $id_jurnal_q,
                'nomor_baris' => $line_q++,
                'id_akun'     => $id_akun_debit,
                'keterangan'  => $ket_q,
                'debit'       => $bruto_pecahan,
                'kredit'      => 0
            ]);

            // Baris Kredit: Piutang Usaha (Nilai Netto setelah potongan)
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal'   => $id_jurnal_q,
                'nomor_baris' => $line_q++,
                'id_akun'     => $id_akun_kredit,
                'keterangan'  => $ket_q,
                'debit'       => 0,
                'kredit'      => $netto_pecahan
            ]);

            // Baris Kredit: Q Kas (Selisih potongan pada jurnal acak)
            if ($potongan_pecahan > 0 && $id_akun_q_kas) {
                $this->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal'   => $id_jurnal_q,
                    'nomor_baris' => $line_q++,
                    'id_akun'     => $id_akun_q_kas,
                    'keterangan'  => $ket_q,
                    'debit'       => 0,
                    'kredit'      => $potongan_pecahan
                ]);
            }
        }

        // ── 2. PEMBUATAN JURNAL (A) DENGAN KODE KM (KAS MASUK) ────────
        if (($skema_potongan_z === 20 || $skema_potongan_z === 40) && $total_seluruh_potongan > 0 && $id_akun_a_kas) {
            $jenis_km = $this->db->get_where('tbkeu_jenis_jurnal', ['kode_jenis_jurnal' => 'KM'])->row();
            if (!$jenis_km) {
                $this->db->insert('tbkeu_jenis_jurnal', [
                    'kode_jenis_jurnal' => 'KM',
                    'nama_jenis_jurnal' => 'Kas Masuk',
                    'is_active'         => 1
                ]);
                $id_jenis_km = $this->db->insert_id();
            } else {
                $id_jenis_km = (int)$jenis_km->id_jenis_jurnal;
            }

            $no_jurnal_a = $this->generate_no_jurnal('A Kas', 'KM');
            $label_suffix = ($skema_potongan_z === 40) ? '(Tax & Fee Petugas)' : '(Tax)';
            $nama_cust_z = !empty($customerIndukName) ? $customerIndukName : 'Customer';
            $ket_a = 'PND u/Pot ' . $nama_cust_z . ' F.' . $no_faktur . ' ' . $label_suffix;

            $jurnal_a_data = [
                'nomor_jurnal'      => $no_jurnal_a,
                'id_jenis_jurnal'   => $id_jenis_km,
                'tanggal_transaksi' => $tanggal,
                'keterangan'        => $ket_a,
                'status'            => 'POSTED',
                'source_module'     => 'KEUANGAN',
                'source_type'       => 'PEMBAYARAN_FAKTUR',
                'source_id'         => $id_pembayaran,
                'source_no'         => $no_faktur,
                'total_debit'       => $total_seluruh_potongan,
                'total_kredit'      => $total_seluruh_potongan,
                'created_by'        => $userId,
                'created_at'        => date('Y-m-d H:i:s'),
                'posted_by'         => $userId,
                'posted_at'         => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tbkeu_jurnal', $jurnal_a_data);
            $id_jurnal_a = $this->db->insert_id();

            $line_a = 1;
            // Baris Debit: A Kas (11030) sebesar Total Potongan
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal'   => $id_jurnal_a,
                'nomor_baris' => $line_a++,
                'id_akun'     => $id_akun_a_kas,
                'keterangan'  => $ket_a,
                'debit'       => $total_seluruh_potongan,
                'kredit'      => 0
            ]);

            if ($skema_potongan_z === 20) {
                // Baris Kredit: A PND atas Pendapatan Pot Penj KIU (Tax) (13036)
                if ($id_akun_tax) {
                    $this->db->insert('tbkeu_jurnal_detail', [
                        'id_jurnal'   => $id_jurnal_a,
                        'nomor_baris' => $line_a++,
                        'id_akun'     => $id_akun_tax,
                        'keterangan'  => $ket_a,
                        'debit'       => 0,
                        'kredit'      => $total_seluruh_potongan
                    ]);
                }
            } elseif ($skema_potongan_z === 40) {
                // Baris Kredit 1: 20% Tax
                $tax_val = round($total_seluruh_potongan / 2, 2);
                $fee_val = $total_seluruh_potongan - $tax_val;

                if ($id_akun_tax) {
                    $this->db->insert('tbkeu_jurnal_detail', [
                        'id_jurnal'   => $id_jurnal_a,
                        'nomor_baris' => $line_a++,
                        'id_akun'     => $id_akun_tax,
                        'keterangan'  => 'PND u/Pot ' . $nama_cust_z . ' F.' . $no_faktur . ' (Tax)',
                        'debit'       => 0,
                        'kredit'      => $tax_val
                    ]);
                }
                // Baris Kredit 2: 20% Fee Petugas
                if ($id_akun_fee) {
                    $this->db->insert('tbkeu_jurnal_detail', [
                        'id_jurnal'   => $id_jurnal_a,
                        'nomor_baris' => $line_a++,
                        'id_akun'     => $id_akun_fee,
                        'keterangan'  => 'PND u/Pot ' . $nama_cust_z . ' F.' . $no_faktur . ' (Fee Petugas)',
                        'debit'       => 0,
                        'kredit'      => $fee_val
                    ]);
                }
            }
        }

        return true;
    }

/**
     * Membuat Jurnal Penjualan (SJ) dan Jurnal HPP (Goods Issue) untuk Faktur Pecahan Kode H.
     * Jurnal ini yang diakui resmi di laporan keuangan (Neraca & Laba Rugi) menggantikan Faktur Z.
     *
     * @param int $id_pecah
     * @param int|null $userId
     * @return array [id_jurnal_penjualan, nomor_jurnal_penjualan, id_jurnal_hpp, nomor_jurnal_hpp]
     */
    public function post_jurnal_penjualan_faktur_h($id_pecah, $userId = null)
    {
        $res = [
            'id_jurnal_penjualan'    => null,
            'nomor_jurnal_penjualan' => null,
            'id_jurnal_hpp'          => null,
            'nomor_jurnal_hpp'       => null,
        ];

        if (!$this->db->table_exists('tbkeu_jurnal') || !$this->db->table_exists('tbkeu_jurnal_detail')
            || !$this->db->table_exists('tbso_faktur_z_pecah') || !$this->db->table_exists('tbso_faktur_z_pecah_detail')) {
            return $res;
        }

        $id_pecah = (int)$id_pecah;
        if ($id_pecah <= 0) {
            return $res;
        }

        $faktur = $this->db->get_where('tbso_faktur_z_pecah', ['id_pecah' => $id_pecah])->row_array();
        if (!$faktur || strtolower((string)$faktur['status']) === 'cancelled') {
            return $res;
        }

        $items = $this->db->select('d.*, b.kelompok_dagang, b.kode_akun_penjualan, b.kode_akun_harga_pokok, b.kode_akun_persediaan')
            ->from('tbso_faktur_z_pecah_detail d')
            ->join('tbpo_barang b', 'b.kode_barang = d.kd_barang', 'left')
            ->where('d.id_pecah', $id_pecah)
            ->get()->result_array();

        if (empty($items)) {
            return $res;
        }

        if ($userId === null) {
            $userId = (int)($this->session->userdata('id_karyawan')
                ?: $this->session->userdata('id')
                ?: $this->session->userdata('id_user')
                ?: 0);
            if ($userId <= 0) {
                $userId = null;
            }
        }

        // Hapus jurnal penjualan lama jika sudah ada agar idempotent
        $this->delete_jurnal_penjualan_faktur_h($id_pecah);

        // Ambil nama customer penerima pecahan (kontak acak)
        $customerName = trim((string)($faktur['customer_name'] ?? ''));
        if ($this->db->table_exists('tb_customer_acak') && !empty($faktur['kd_customer'])) {
            $ca = $this->db->get_where('tb_customer_acak', ['kd_customer' => $faktur['kd_customer']])->row_array();
            if ($ca && !empty($ca['kontak_person']) && $ca['kontak_person'] !== '-') {
                $customerName = trim($ca['kontak_person']);
            }
        }
        if ($customerName === '') {
            $customerName = 'Pelanggan';
        }

        // Mapping akun-akun yang digunakan
        $kodes = [];
        foreach ($items as $it) {
            if (!empty($it['kode_akun_penjualan'])) $kodes[] = $it['kode_akun_penjualan'];
            if (!empty($it['kode_akun_harga_pokok'])) $kodes[] = $it['kode_akun_harga_pokok'];
            if (!empty($it['kode_akun_persediaan'])) $kodes[] = $it['kode_akun_persediaan'];
        }
        $akunMap = [];
        if (!empty($kodes)) {
            $akuns = $this->db->where_in('kode_akun', array_unique($kodes))->get('tbkeu_akun')->result_array();
            foreach ($akuns as $a) {
                $akunMap[$a['kode_akun']] = (int)$a['id_akun'];
            }
        }

        // 1. Akun Piutang Usaha (13099)
        $akun_piutang = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13099'])->row();
        if (!$akun_piutang) {
            $this->db->like('nama_akun', 'Piutang Usaha');
            $akun_piutang = $this->db->get('tbkeu_akun')->row();
        }
        $id_akun_piutang = $akun_piutang ? (int)$akun_piutang->id_akun : 196;

        // 2. Akun PPN Keluaran (21024)
        $akun_ppn = $this->db->get_where('tbkeu_akun', ['kode_akun' => '21024'])->row();
        if (!$akun_ppn) {
            $akun_ppn = $this->db->like('nama_akun', 'PPN K')->get('tbkeu_akun')->row();
        }
        $id_akun_ppn = $akun_ppn ? (int)$akun_ppn->id_akun : 280;

        // 3. Fallback Akun Penjualan BKP (41011)
        $akun_penjualan_def = $this->db->get_where('tbkeu_akun', ['kode_akun' => '41011'])->row();
        $id_akun_penj_def = $akun_penjualan_def ? (int)$akun_penjualan_def->id_akun : 307;

        // 4. Fallback Akun HPP (51010)
        $akun_hpp_def = $this->db->get_where('tbkeu_akun', ['kode_akun' => '51010'])->row();
        $id_akun_hpp_def = $akun_hpp_def ? (int)$akun_hpp_def->id_akun : 337;

        // 5. Fallback Akun Persediaan (14010)
        $akun_inv_def = $this->db->get_where('tbkeu_akun', ['kode_akun' => '14010'])->row();
        $id_akun_inv_def = $akun_inv_def ? (int)$akun_inv_def->id_akun : 225;

        // Akumulasi nominal penjualan, pajak, dan HPP
        $total_piutang = 0.0;
        $total_dpp     = 0.0;
        $total_pajak   = 0.0;
        $total_cogs    = 0.0;
        $groupRev      = [];
        $groupCogs     = [];
        $groupInv      = [];

        foreach ($items as $item) {
            $subtotal   = (float)($item['total_harga'] ?? $item['subtotal_after_disc'] ?? 0);
            $pajakRate  = (float)($item['pajak'] ?? 0);
            $hrg_pokok  = (float)($item['hrg_pokok'] ?? 0);
            $qty        = (float)($item['qty'] ?? 0);

            $divFactor  = ($pajakRate > 0) ? 1 + ($pajakRate / 100) : 1.0;
            $dpp_line   = round($subtotal / $divFactor, 2);
            $tax_line   = round($subtotal - $dpp_line, 2);
            $cogs_line  = round($qty * $hrg_pokok, 2);

            $total_piutang += $subtotal;
            $total_dpp     += $dpp_line;
            $total_pajak   += $tax_line;
            $total_cogs    += $cogs_line;

            // Group revenue akun
            $id_rev = !empty($item['kode_akun_penjualan']) && isset($akunMap[$item['kode_akun_penjualan']])
                ? $akunMap[$item['kode_akun_penjualan']] : $id_akun_penj_def;
            if (!isset($groupRev[$id_rev])) $groupRev[$id_rev] = 0.0;
            $groupRev[$id_rev] += $dpp_line;

            // Group COGS akun
            $id_cogs = !empty($item['kode_akun_harga_pokok']) && isset($akunMap[$item['kode_akun_harga_pokok']])
                ? $akunMap[$item['kode_akun_harga_pokok']] : $id_akun_hpp_def;
            if (!isset($groupCogs[$id_cogs])) $groupCogs[$id_cogs] = 0.0;
            $groupCogs[$id_cogs] += $cogs_line;

            // Group Persediaan akun
            $id_inv = !empty($item['kode_akun_persediaan']) && isset($akunMap[$item['kode_akun_persediaan']])
                ? $akunMap[$item['kode_akun_persediaan']] : $id_akun_inv_def;
            if (!isset($groupInv[$id_inv])) $groupInv[$id_inv] = 0.0;
            $groupInv[$id_inv] += $cogs_line;
        }

        if ($total_piutang <= 0) {
            return $res;
        }

        // Tentukan Jenis Jurnal SJ (Penjualan)
        $jenis_sj = $this->db->get_where('tbkeu_jenis_jurnal', ['kode_jenis_jurnal' => 'SJ'])->row();
        $id_jenis_sj = $jenis_sj ? (int)$jenis_sj->id_jenis_jurnal : 1;

        $tgl_transaksi = !empty($faktur['tanggal_faktur']) ? $faktur['tanggal_faktur'] : date('Y-m-d');
        $no_faktur_h   = trim((string)$faktur['no_faktur']);

        // =========================================================================
        // A. BUAT JURNAL PENJUALAN (SJ)
        // =========================================================================
        $nomor_jurnal_sj = $this->generate_no_jurnal('penjualan', 'SJ');
        $ket_sj          = 'Penjualan, ' . $customerName . ' (F. ' . $no_faktur_h . ')';

        $jurnal_sj_header = [
            'nomor_jurnal'      => $nomor_jurnal_sj,
            'id_jenis_jurnal'   => $id_jenis_sj,
            'tanggal_transaksi' => $tgl_transaksi,
            'keterangan'        => $ket_sj,
            'source_module'     => 'SALES',
            'source_type'       => 'FAKTUR_PECAH_H',
            'source_id'         => (string)$id_pecah,
            'source_no'         => $no_faktur_h,
            'posting_event'     => 'SALES_INVOICE',
            'status'            => 'POSTED',
            'total_debit'       => $total_piutang,
            'total_kredit'      => $total_piutang,
            'idempotency_key'   => 'SALES_INVOICE_FH_' . $id_pecah,
            'created_by'        => $userId,
            'created_at'        => date('Y-m-d H:i:s'),
            'posted_by'         => $userId,
            'posted_at'         => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tbkeu_jurnal', $jurnal_sj_header);
        $id_jurnal_sj = (int)$this->db->insert_id();

        $line_sj = 1;
        // Baris 1 Debit: Piutang Usaha
        $this->db->insert('tbkeu_jurnal_detail', [
            'id_jurnal'   => $id_jurnal_sj,
            'nomor_baris' => $line_sj++,
            'id_akun'     => $id_akun_piutang,
            'keterangan'  => $ket_sj . ' - Piutang',
            'debit'       => $total_piutang,
            'kredit'      => 0
        ]);

        // Baris Kredit: Pendapatan Penjualan
        foreach ($groupRev as $id_akun_rev => $amt_rev) {
            if ($amt_rev > 0) {
                $this->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal'   => $id_jurnal_sj,
                    'nomor_baris' => $line_sj++,
                    'id_akun'     => $id_akun_rev,
                    'keterangan'  => $ket_sj . ' - Pendapatan',
                    'debit'       => 0,
                    'kredit'      => $amt_rev
                ]);
            }
        }

        // Baris Kredit: PPN Keluaran jika ada
        if ($total_pajak > 0 && $id_akun_ppn) {
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal'   => $id_jurnal_sj,
                'nomor_baris' => $line_sj++,
                'id_akun'     => $id_akun_ppn,
                'keterangan'  => $ket_sj . ' - PPN Keluaran',
                'debit'       => 0,
                'kredit'      => $total_pajak
            ]);
        }

        $res['id_jurnal_penjualan']    = $id_jurnal_sj;
        $res['nomor_jurnal_penjualan'] = $nomor_jurnal_sj;

        // =========================================================================
        // B. BUAT JURNAL HPP & PERSEDIAAN (GOODS ISSUE)
        // =========================================================================
        $id_jurnal_hpp    = null;
        $nomor_jurnal_hpp = null;

        if ($total_cogs > 0) {
            $nomor_jurnal_hpp = $this->generate_no_jurnal('penyesuaian', 'SJ');
            $ket_hpp          = 'Penyesuaian persediaan, untuk ' . $nomor_jurnal_sj . ' (' . $no_faktur_h . ')';

            $jurnal_hpp_header = [
                'nomor_jurnal'      => $nomor_jurnal_hpp,
                'id_jenis_jurnal'   => $id_jenis_sj,
                'tanggal_transaksi' => $tgl_transaksi,
                'keterangan'        => $ket_hpp,
                'source_module'     => 'SALES',
                'source_type'       => 'FAKTUR_PECAH_H',
                'source_id'         => (string)$id_pecah,
                'source_no'         => $no_faktur_h,
                'posting_event'     => 'GOODS_ISSUE',
                'status'            => 'POSTED',
                'total_debit'       => $total_cogs,
                'total_kredit'      => $total_cogs,
                'idempotency_key'   => 'GOODS_ISSUE_FH_' . $id_pecah,
                'created_by'        => $userId,
                'created_at'        => date('Y-m-d H:i:s'),
                'posted_by'         => $userId,
                'posted_at'         => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('tbkeu_jurnal', $jurnal_hpp_header);
            $id_jurnal_hpp = (int)$this->db->insert_id();

            $line_hpp = 1;
            // Baris Debit: HPP
            foreach ($groupCogs as $id_akun_cogs => $amt_cogs) {
                if ($amt_cogs > 0) {
                    $this->db->insert('tbkeu_jurnal_detail', [
                        'id_jurnal'   => $id_jurnal_hpp,
                        'nomor_baris' => $line_hpp++,
                        'id_akun'     => $id_akun_cogs,
                        'keterangan'  => $ket_hpp . ' - HPP',
                        'debit'       => $amt_cogs,
                        'kredit'      => 0
                    ]);
                }
            }

            // Baris Kredit: Persediaan
            foreach ($groupInv as $id_akun_inv => $amt_inv) {
                if ($amt_inv > 0) {
                    $this->db->insert('tbkeu_jurnal_detail', [
                        'id_jurnal'   => $id_jurnal_hpp,
                        'nomor_baris' => $line_hpp++,
                        'id_akun'     => $id_akun_inv,
                        'keterangan'  => $ket_hpp . ' - Persediaan',
                        'debit'       => 0,
                        'kredit'      => $amt_inv
                    ]);
                }
            }

            $res['id_jurnal_hpp']    = $id_jurnal_hpp;
            $res['nomor_jurnal_hpp'] = $nomor_jurnal_hpp;
        }

        // Update tbso_faktur_z_pecah
        $this->db->where('id_pecah', $id_pecah)->update('tbso_faktur_z_pecah', [
            'id_jurnal_penjualan'    => $id_jurnal_sj,
            'nomor_jurnal_penjualan' => $nomor_jurnal_sj,
            'id_jurnal_hpp'          => $id_jurnal_hpp,
            'nomor_jurnal_hpp'       => $nomor_jurnal_hpp,
        ]);

        return $res;
    }

    /**
     * Menghapus Jurnal Penjualan dan HPP milik Faktur Pecahan H (saat di-unpost / di-reset).
     *
     * @param int $id_pecah
     * @return bool
     */
    public function delete_jurnal_penjualan_faktur_h($id_pecah)
    {
        if (!$this->db->table_exists('tbkeu_jurnal') || !$this->db->table_exists('tbkeu_jurnal_detail')) {
            return false;
        }

        $id_pecah = (int)$id_pecah;
        if ($id_pecah <= 0) {
            return false;
        }

        $jurnal_ids = [];
        $rows = $this->db->select('id_jurnal')
            ->from('tbkeu_jurnal')
            ->where('source_module', 'SALES')
            ->where('source_type', 'FAKTUR_PECAH_H')
            ->where('source_id', (string)$id_pecah)
            ->get()->result_array();

        foreach ($rows as $r) {
            $jurnal_ids[] = (int)$r['id_jurnal'];
        }

        $faktur = $this->db->select('id_jurnal_penjualan, id_jurnal_hpp, no_faktur')
            ->where('id_pecah', $id_pecah)
            ->get('tbso_faktur_z_pecah')
            ->row_array();

        if ($faktur) {
            if (!empty($faktur['id_jurnal_penjualan'])) $jurnal_ids[] = (int)$faktur['id_jurnal_penjualan'];
            if (!empty($faktur['id_jurnal_hpp'])) $jurnal_ids[] = (int)$faktur['id_jurnal_hpp'];
            if (!empty($faktur['no_faktur'])) {
                $rowsNo = $this->db->select('id_jurnal')
                    ->from('tbkeu_jurnal')
                    ->where('source_module', 'SALES')
                    ->where('source_type', 'FAKTUR_PECAH_H')
                    ->where('source_no', $faktur['no_faktur'])
                    ->get()->result_array();
                foreach ($rowsNo as $rn) {
                    $jurnal_ids[] = (int)$rn['id_jurnal'];
                }
            }
        }

        $jurnal_ids = array_values(array_unique(array_filter($jurnal_ids)));
        if (!empty($jurnal_ids)) {
            $this->db->where_in('id_jurnal', $jurnal_ids)->delete('tbkeu_jurnal_detail');
            $this->db->where_in('id_jurnal', $jurnal_ids)->delete('tbkeu_jurnal');
        }

        $this->db->where('id_pecah', $id_pecah)->update('tbso_faktur_z_pecah', [
            'id_jurnal_penjualan'    => null,
            'nomor_jurnal_penjualan' => null,
            'id_jurnal_hpp'          => null,
            'nomor_jurnal_hpp'       => null,
        ]);

        return true;
    }

    /**
     * Mengubah status Jurnal Penjualan Faktur Z Induk menjadi VOID (dikecualikan dari laporan keuangan)
     * karena Faktur Z tersebut telah dipecah menjadi Faktur H.
     *
     * @param string $no_faktur_z
     * @param int|null $userId
     * @return bool
     */
    public function void_jurnal_faktur_z_induk($no_faktur_z, $userId = null)
    {
        if (!$this->db->table_exists('tbkeu_jurnal')) {
            return false;
        }

        $no_faktur_z = trim((string)$no_faktur_z);
        if ($no_faktur_z === '') {
            return false;
        }

        if ($userId === null) {
            $userId = (int)($this->session->userdata('id_karyawan')
                ?: $this->session->userdata('id')
                ?: $this->session->userdata('id_user')
                ?: 0);
            if ($userId <= 0) $userId = null;
        }

        $this->db->where('source_module', 'SALES')
            ->where('source_no', $no_faktur_z)
            ->where('status', 'POSTED')
            ->update('tbkeu_jurnal', [
                'status'      => 'VOID',
                'voided_by'   => $userId,
                'voided_at'   => date('Y-m-d H:i:s'),
                'void_reason' => 'Digantikan oleh Faktur Pecahan H (Faktur Z telah dipecah)'
            ]);

        return true;
    }

    /**
     * Memulihkan status Jurnal Penjualan Faktur Z Induk menjadi POSTED
     * ketika seluruh Faktur Pecahan H di-unpost / di-reset kembali.
     *
     * @param string $no_faktur_z
     * @return bool
     */
    public function restore_jurnal_faktur_z_induk($no_faktur_z)
    {
        if (!$this->db->table_exists('tbkeu_jurnal')) {
            return false;
        }

        $no_faktur_z = trim((string)$no_faktur_z);
        if ($no_faktur_z === '') {
            return false;
        }

        $this->db->where('source_module', 'SALES')
            ->where('source_no', $no_faktur_z)
            ->where('status', 'VOID')
            ->where('void_reason', 'Digantikan oleh Faktur Pecahan H (Faktur Z telah dipecah)')
            ->update('tbkeu_jurnal', [
                'status'      => 'POSTED',
                'voided_by'   => null,
                'voided_at'   => null,
                'void_reason' => null
            ]);

        return true;
    }

    /**
     * Membuat Jurnal Pembayaran Acak (Q) dan Jurnal Kas Masuk (A) untuk Pelunasan Faktur Pecahan Kode H dari Sistem (tbso_faktur_z_pecah)
     */
    public function post_jurnal_pelunasan_faktur_z_pecah($faktur, $paymentData = [])
    {
        $result = [
            'id_jurnal_q'    => null,
            'nomor_jurnal_q' => null,
            'id_jurnal_a'    => null,
            'nomor_jurnal_a' => null,
        ];

        if (!$this->db->table_exists('tbkeu_jurnal') || !$this->db->table_exists('tbkeu_jurnal_detail')) {
            return $result;
        }

        $id_pecah         = (int)($faktur['id_pecah'] ?? 0);
        $no_faktur_h      = trim((string)($faktur['no_faktur'] ?? ''));
        $parent_no_faktur = trim((string)($paymentData['parent_no_faktur'] ?? $faktur['parent_no_faktur'] ?? ''));
        $tanggal          = !empty($paymentData['tanggal_pembayaran']) ? $paymentData['tanggal_pembayaran'] : ($faktur['tanggal_faktur'] ?? date('Y-m-d'));
        $metode           = trim((string)($paymentData['metode_pembayaran'] ?? 'Q Mandiri'));
        $skema_raw        = isset($paymentData['skema_potongan']) ? (int)$paymentData['skema_potongan'] : (int)($faktur['skema_potongan'] ?? 20);
        $skema_potongan   = ($skema_raw === 40) ? 40 : (($skema_raw === 0) ? 0 : 20);

        $netto_pecahan    = round((float)($paymentData['jumlah_bayar'] ?? $faktur['grand_total'] ?? 0), 2);
        if ($netto_pecahan <= 0) {
            return $result;
        }

        $pembagi_bruto = ($skema_potongan === 40) ? 0.6 : (($skema_potongan === 20) ? 0.8 : 1.0);
        $bruto_pecahan = isset($paymentData['nominal_bruto']) && (float)$paymentData['nominal_bruto'] > 0
            ? round((float)$paymentData['nominal_bruto'], 2)
            : (($pembagi_bruto > 0 && $pembagi_bruto < 1.0) ? round($netto_pecahan / $pembagi_bruto, 2) : $netto_pecahan);
        $potongan_pecahan = isset($paymentData['nominal_potongan']) && (float)$paymentData['nominal_potongan'] >= 0
            ? round((float)$paymentData['nominal_potongan'], 2)
            : max(0, round($bruto_pecahan - $netto_pecahan, 2));

        $userId = (int)($this->session->userdata('id_karyawan')
            ?: $this->session->userdata('id')
            ?: $this->session->userdata('id_user')
            ?: 0);
        if ($userId <= 0) {
            $userId = null;
        }

        // Nama untuk Keterangan Jurnal Acak (Q) harus menggunakan Kontak Person
        $kontak_q = trim((string)($faktur['kontak_person'] ?? ''));
        if ($kontak_q === '-' || strtolower($kontak_q) === 'null') {
            $kontak_q = '';
        }

        if ($kontak_q !== '') {
            $nama_cust_q = $kontak_q;
        } elseif (!empty($faktur['customer_name']) && trim((string)$faktur['customer_name']) !== '-') {
            $nama_cust_q = trim((string)$faktur['customer_name']);
        } elseif (!empty($faktur['nama_toko'])) {
            $nama_cust_q = trim((string)$faktur['nama_toko']);
        } else {
            $nama_cust_q = 'Pelanggan';
        }

        // Cari Akun-akun untuk Jurnal Acak (Q) dan Jurnal (A)
        // 1. Akun Piutang Usaha (13099)
        $akun_piutang = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13099'])->row();
        if (!$akun_piutang) {
            $this->db->like('nama_akun', 'Piutang Usaha');
            $akun_piutang = $this->db->get('tbkeu_akun')->row();
        }
        $id_akun_piutang = $akun_piutang ? (int)$akun_piutang->id_akun : 196;

        // 2. Akun Kas / Bank sesuai metode pembayaran
        $akun_kas = null;
        if (!empty($metode)) {
            $this->db->where('LOWER(nama_akun)', strtolower($metode));
            $akun_kas = $this->db->get('tbkeu_akun')->row();

            if (!$akun_kas) {
                $this->db->where('kode_akun', $metode);
                $akun_kas = $this->db->get('tbkeu_akun')->row();
            }
            if (!$akun_kas) {
                $this->db->like('nama_akun', $metode);
                $akun_kas = $this->db->get('tbkeu_akun')->row();
            }
        }
        if (!$akun_kas) {
            $akun_kas = $this->db->get_where('tbkeu_akun', ['kode_akun' => '12014'])->row()
                ?: $this->db->get_where('tbkeu_akun', ['kode_akun' => '11010'])->row();
        }
        $id_akun_kas = $akun_kas ? (int)$akun_kas->id_akun : 187;

        // Tentukan prefix jurnal Q (KM, MR, BC, BR)
        $prefix_jurnal_q = 'MR';
        $nama_akun_kas_lower = strtolower($akun_kas ? $akun_kas->nama_akun : $metode);
        if (strpos($nama_akun_kas_lower, 'mandiri') !== false) {
            $prefix_jurnal_q = 'MR';
        } elseif (strpos($nama_akun_kas_lower, 'bca') !== false) {
            $prefix_jurnal_q = 'BC';
        } elseif (strpos($nama_akun_kas_lower, 'bri') !== false) {
            $prefix_jurnal_q = 'BR';
        } elseif (strpos($nama_akun_kas_lower, 'kas') !== false) {
            $prefix_jurnal_q = 'KM';
        }
        $jenis_q = $this->db->get_where('tbkeu_jenis_jurnal', ['kode_jenis_jurnal' => $prefix_jurnal_q])->row();
        $id_jenis_jurnal_q = $jenis_q ? (int)$jenis_q->id_jenis_jurnal : 3;

        // 3. Akun Q Kas (11010) untuk Kredit Potongan pada Jurnal Q
        $akun_q_kas = $this->db->get_where('tbkeu_akun', ['kode_akun' => '11010'])->row();
        $id_akun_q_kas = $akun_q_kas ? (int)$akun_q_kas->id_akun : 185;

        // 4. Akun A Kas (11030) untuk Debit pada Jurnal A
        $akun_a_kas = $this->db->get_where('tbkeu_akun', ['kode_akun' => '11030'])->row();
        $id_akun_a_kas = $akun_a_kas ? (int)$akun_a_kas->id_akun : 186;

        // 5. Akun A PND atas Pendapatan Pot Penj KIU (Tax) (13036)
        $akun_tax = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13036'])->row();
        if (!$akun_tax) {
            $akun_tax = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13040'])->row();
        }
        if (!$akun_tax) {
            $this->db->like('nama_akun', 'Pot Penj KIU');
            $akun_tax = $this->db->get('tbkeu_akun')->row();
        }
        $id_akun_tax = $akun_tax ? (int)$akun_tax->id_akun : null;

        // 6. Akun A PND atas Pendapatan Pot Penj KIU (Fee Petugas) (13041)
        $akun_fee = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13041'])->row();
        if (!$akun_fee) {
            $this->db->like('nama_akun', 'Fee Petugas');
            $akun_fee = $this->db->get('tbkeu_akun')->row();
        }
        $id_akun_fee = $akun_fee ? (int)$akun_fee->id_akun : $id_akun_tax;

        // =========================================================================
        // A. BUAT JURNAL PEMBAYARAN ACAK (Q)
        // =========================================================================
        $no_jurnal_q = $this->generate_no_jurnal('pembayaran', $prefix_jurnal_q);
        $ket_q       = 'Penerimaan dari ' . $nama_cust_q;

        $jurnal_q_header = [
            'nomor_jurnal'      => $no_jurnal_q,
            'id_jenis_jurnal'   => $id_jenis_jurnal_q,
            'tanggal_transaksi' => $tanggal,
            'keterangan'        => $ket_q,
            'status'            => 'POSTED',
            'source_module'     => 'KEUANGAN',
            'source_type'       => 'PEMBAYARAN_FAKTUR_PECAH',
            'source_id'         => (string)$id_pecah,
            'source_no'         => $no_faktur_h,
            'idempotency_key'   => 'PAYMENT_FZ_PECAH_Q_' . ($id_pecah > 0 ? $id_pecah : $no_faktur_h),
            'total_debit'       => $bruto_pecahan,
            'total_kredit'      => $bruto_pecahan,
            'created_by'        => $userId,
            'created_at'        => date('Y-m-d H:i:s'),
            'posted_by'         => $userId,
            'posted_at'         => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbkeu_jurnal', $jurnal_q_header);
        $id_jurnal_q = (int)$this->db->insert_id();

        $line_q = 1;
        // Baris 1 (Debit): Kas/Bank yang dipilih (sebesar Bruto)
        $this->db->insert('tbkeu_jurnal_detail', [
            'id_jurnal'   => $id_jurnal_q,
            'nomor_baris' => $line_q++,
            'id_akun'     => $id_akun_kas,
            'keterangan'  => $ket_q,
            'debit'       => $bruto_pecahan,
            'kredit'      => 0
        ]);

        // Baris 2 (Kredit): Piutang Usaha 13099 (sebesar Netto Faktur H)
        $this->db->insert('tbkeu_jurnal_detail', [
            'id_jurnal'   => $id_jurnal_q,
            'nomor_baris' => $line_q++,
            'id_akun'     => $id_akun_piutang,
            'keterangan'  => $ket_q,
            'debit'       => 0,
            'kredit'      => $netto_pecahan
        ]);

        // Baris 3 (Kredit): Q Kas 11010 (sebesar Potongan jika > 0)
        if ($potongan_pecahan > 0 && $id_akun_q_kas) {
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal'   => $id_jurnal_q,
                'nomor_baris' => $line_q++,
                'id_akun'     => $id_akun_q_kas,
                'keterangan'  => $ket_q,
                'debit'       => 0,
                'kredit'      => $potongan_pecahan
            ]);
        }

        $result['id_jurnal_q']    = $id_jurnal_q;
        $result['nomor_jurnal_q'] = $no_jurnal_q;

        // =========================================================================
        // B. BUAT JURNAL KAS MASUK (A) — Jika terdapat Potongan (Skema 20% atau 40%)
        // =========================================================================
        if ($potongan_pecahan > 0 && $id_akun_a_kas && ($id_akun_tax || $id_akun_fee)) {
            $jenis_km = $this->db->get_where('tbkeu_jenis_jurnal', ['kode_jenis_jurnal' => 'KM'])->row();
            $id_jenis_km = $jenis_km ? (int)$jenis_km->id_jenis_jurnal : $id_jenis_jurnal_q;

            $no_jurnal_a = $this->generate_no_jurnal('pembayaran', 'KM');

            // Ambil info nama toko induk / customer untuk keterangan Jurnal A
            $ref_faktur_a = $parent_no_faktur !== '' ? $parent_no_faktur : $no_faktur_h;
            $nama_cust_a  = $nama_cust_q;
            if ($parent_no_faktur !== '' && $this->db->table_exists('tbso_faktur_penjualan')) {
                $parentRow = $this->db->select('customer_name, kd_customer')
                    ->where('no_faktur', $parent_no_faktur)
                    ->get('tbso_faktur_penjualan')
                    ->row_array();
                if ($parentRow && !empty($parentRow['customer_name'])) {
                    $nama_cust_a = trim($parentRow['customer_name']);
                    if (!empty($parentRow['kd_customer']) && $this->db->table_exists('tb_customer')) {
                        $cRow = $this->db->get_where('tb_customer', ['kd_customer' => $parentRow['kd_customer']])->row_array();
                        if ($cRow && !empty($cRow['nama_kios']) && stripos($nama_cust_a, $cRow['nama_kios']) === false) {
                            $nama_cust_a .= ' (' . trim($cRow['nama_kios']) . ')';
                        }
                    }
                }
            }

            $ket_a = ($skema_potongan === 40)
                ? ('PND u/Pot ' . $nama_cust_a . ' F.' . $ref_faktur_a . ' (Tax & Fee Petugas)')
                : ('PND u/Pot ' . $nama_cust_a . ' F.' . $ref_faktur_a . ' (Tax)');

            $jurnal_a_header = [
                'nomor_jurnal'      => $no_jurnal_a,
                'id_jenis_jurnal'   => $id_jenis_km,
                'tanggal_transaksi' => $tanggal,
                'keterangan'        => $ket_a,
                'status'            => 'POSTED',
                'source_module'     => 'KEUANGAN',
                'source_type'       => 'PEMBAYARAN_FAKTUR_PECAH',
                'source_id'         => (string)$id_pecah,
                'source_no'         => $no_faktur_h,
                'idempotency_key'   => 'PAYMENT_FZ_PECAH_A_' . ($id_pecah > 0 ? $id_pecah : $no_faktur_h),
                'total_debit'       => $potongan_pecahan,
                'total_kredit'      => $potongan_pecahan,
                'created_by'        => $userId,
                'created_at'        => date('Y-m-d H:i:s'),
                'posted_by'         => $userId,
                'posted_at'         => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tbkeu_jurnal', $jurnal_a_header);
            $id_jurnal_a = (int)$this->db->insert_id();

            $line_a = 1;
            // Baris Debit: A Kas (11030) sebesar Potongan
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal'   => $id_jurnal_a,
                'nomor_baris' => $line_a++,
                'id_akun'     => $id_akun_a_kas,
                'keterangan'  => $ket_a,
                'debit'       => $potongan_pecahan,
                'kredit'      => 0
            ]);

            if ($skema_potongan === 20) {
                // Baris Kredit: A PND atas Pendapatan Pot Penj KIU (Tax) (13036)
                if ($id_akun_tax) {
                    $this->db->insert('tbkeu_jurnal_detail', [
                        'id_jurnal'   => $id_jurnal_a,
                        'nomor_baris' => $line_a++,
                        'id_akun'     => $id_akun_tax,
                        'keterangan'  => $ket_a,
                        'debit'       => 0,
                        'kredit'      => $potongan_pecahan
                    ]);
                }
            } elseif ($skema_potongan === 40) {
                $tax_val = round($potongan_pecahan / 2, 2);
                $fee_val = round($potongan_pecahan - $tax_val, 2);

                // Baris Kredit 1: 20% Tax (13036)
                if ($id_akun_tax) {
                    $this->db->insert('tbkeu_jurnal_detail', [
                        'id_jurnal'   => $id_jurnal_a,
                        'nomor_baris' => $line_a++,
                        'id_akun'     => $id_akun_tax,
                        'keterangan'  => 'PND u/Pot ' . $nama_cust_a . ' F.' . $ref_faktur_a . ' (Tax)',
                        'debit'       => 0,
                        'kredit'      => $tax_val
                    ]);
                }
                // Baris Kredit 2: 20% Fee Petugas (13041)
                if ($id_akun_fee) {
                    $this->db->insert('tbkeu_jurnal_detail', [
                        'id_jurnal'   => $id_jurnal_a,
                        'nomor_baris' => $line_a++,
                        'id_akun'     => $id_akun_fee,
                        'keterangan'  => 'PND u/Pot ' . $nama_cust_a . ' F.' . $ref_faktur_a . ' (Fee Petugas)',
                        'debit'       => 0,
                        'kredit'      => $fee_val
                    ]);
                }
            }

            $result['id_jurnal_a']    = $id_jurnal_a;
            $result['nomor_jurnal_a'] = $no_jurnal_a;
        }

        return $result;
    }


    // =========================================================================
    // MODUL LAPORAN ARUS KAS (ZAHIR ACCOUNTING STYLE)
    // =========================================================================

    /**
     * Menghitung data Laporan Arus Kas (Metode Langsung / Kontra Akun Kas & Bank)
     * untuk periode tertentu.
     *
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return array
     */
    public function get_laporan_arus_kas($startDate, $endDate)
    {
        // 1. Ambil seluruh akun Kas & Bank (110xx dan 120xx)
        $cashAccounts = $this->db->select('id_akun, kode_akun, nama_akun')
            ->from('tbkeu_akun')
            ->group_start()
                ->like('kode_akun', '110', 'after')
                ->or_like('kode_akun', '120', 'after')
            ->group_end()
            ->get()->result_array();

        $cashIdList = array_column($cashAccounts, 'id_akun');
        if (empty($cashIdList)) {
            return [
                'top_items'          => [],
                'operating'          => [],
                'investing'          => [],
                'financing'          => [],
                'total_operating'    => 0,
                'total_investing'    => 0,
                'total_financing'    => 0,
                'total_keluar_masuk' => 0,
                'saldo_awal'         => 0,
                'saldo_akhir'        => 0,
            ];
        }

        // 2. Hitung Saldo Awal (semua jurnal kas posted sebelum tanggal mulai)
        $saldoAwalRow = $this->db->select('COALESCE(SUM(d.debit - d.kredit), 0) AS saldo_awal', false)
            ->from('tbkeu_jurnal_detail d')
            ->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal')
            ->where('j.status', 'POSTED')
            ->where('j.tanggal_transaksi <', $startDate)
            ->where_in('d.id_akun', $cashIdList)
            ->get()->row_array();
        $saldoAwal = (float)($saldoAwalRow['saldo_awal'] ?? 0);

        // 3. Cari seluruh id_jurnal POSTED yang melibatkan kas dalam periode ini
        $jurnalKasRows = $this->db->select('j.id_jurnal')
            ->from('tbkeu_jurnal j')
            ->join('tbkeu_jurnal_detail d', 'd.id_jurnal = j.id_jurnal')
            ->where('j.status', 'POSTED')
            ->where('j.tanggal_transaksi >=', $startDate)
            ->where('j.tanggal_transaksi <=', $endDate)
            ->where_in('d.id_akun', $cashIdList)
            ->group_by('j.id_jurnal')
            ->get()->result_array();

        if (empty($jurnalKasRows)) {
            return [
                'top_items'          => [],
                'operating'          => [],
                'investing'          => [],
                'financing'          => [],
                'total_operating'    => 0,
                'total_investing'    => 0,
                'total_financing'    => 0,
                'total_keluar_masuk' => 0,
                'saldo_awal'         => $saldoAwal,
                'saldo_akhir'        => $saldoAwal,
            ];
        }

        $jurnalIds = array_column($jurnalKasRows, 'id_jurnal');

        // 4. Ambil detail baris NON-KAS dari jurnal-jurnal tersebut
        $lines = [];
        $chunks = array_chunk($jurnalIds, 500);
        foreach ($chunks as $chunk) {
            $chunkLines = $this->db->select('d.id_jurnal, d.id_akun, d.debit, d.kredit, a.kode_akun, a.nama_akun')
                ->from('tbkeu_jurnal_detail d')
                ->join('tbkeu_akun a', 'a.id_akun = d.id_akun')
                ->where_in('d.id_jurnal', $chunk)
                ->where_not_in('d.id_akun', $cashIdList)
                ->get()->result_array();
            if (!empty($chunkLines)) {
                $lines = array_merge($lines, $chunkLines);
            }
        }

        $categories = [];
        $totalKeluarMasuk = 0.0;

        foreach ($lines as $line) {
            $kodeAkun = trim((string)$line['kode_akun']);
            $namaAkun = trim((string)$line['nama_akun']);
            // Efek kas: kredit - debit
            $cashAmount = (float)$line['kredit'] - (float)$line['debit'];
            $totalKeluarMasuk += $cashAmount;

            $map = $this->map_account_to_cash_flow_category($kodeAkun);
            $group = $map['group'];
            $catName = $map['category'];
            $order = $map['order'];

            if (!isset($categories[$group])) {
                $categories[$group] = [];
            }
            if (!isset($categories[$group][$catName])) {
                $categories[$group][$catName] = [
                    'name'     => $catName,
                    'order'    => $order,
                    'total'    => 0.0,
                    'accounts' => []
                ];
            }

            $categories[$group][$catName]['total'] += $cashAmount;

            $formattedKode = $this->format_kode_akun_zahir($kodeAkun);
            if (!isset($categories[$group][$catName]['accounts'][$formattedKode])) {
                $categories[$group][$catName]['accounts'][$formattedKode] = [
                    'kode_akun' => $formattedKode,
                    'nama_akun' => $namaAkun,
                    'total'     => 0.0
                ];
            }
            $categories[$group][$catName]['accounts'][$formattedKode]['total'] += $cashAmount;
        }

        // Urutkan kategori dan akun di dalamnya
        foreach ($categories as $grp => &$catList) {
            uasort($catList, function($a, $b) {
                return $a['order'] <=> $b['order'];
            });
            foreach ($catList as &$cItem) {
                ksort($cItem['accounts']);
            }
        }
        unset($catList, $cItem);

        // Subtotal per grup aktivitas
        $totalOperating = 0.0;
        if (!empty($categories['OPERATING'])) {
            foreach ($categories['OPERATING'] as $c) {
                $totalOperating += $c['total'];
            }
        }

        $totalInvesting = 0.0;
        if (!empty($categories['INVESTING'])) {
            foreach ($categories['INVESTING'] as $c) {
                $totalInvesting += $c['total'];
            }
        }

        $totalFinancing = 0.0;
        if (!empty($categories['FINANCING'])) {
            foreach ($categories['FINANCING'] as $c) {
                $totalFinancing += $c['total'];
            }
        }

        $saldoAkhir = $saldoAwal + $totalKeluarMasuk;

        return [
            'top_items'          => $categories['TOP'] ?? [],
            'operating'          => $categories['OPERATING'] ?? [],
            'investing'          => $categories['INVESTING'] ?? [],
            'financing'          => $categories['FINANCING'] ?? [],
            'total_operating'    => $totalOperating,
            'total_investing'    => $totalInvesting,
            'total_financing'    => $totalFinancing,
            'total_keluar_masuk' => $totalKeluarMasuk,
            'saldo_awal'         => $saldoAwal,
            'saldo_akhir'        => $saldoAkhir,
        ];
    }

    /**
     * Memetakan kode akun ke kategori Arus Kas sesuai standar Zahir Accounting Karisma.
     */
    public function map_account_to_cash_flow_category($kode_akun)
    {
        $c = preg_replace('/[^0-9]/', '', (string)$kode_akun);
        $prefix3 = substr($c, 0, 3);
        $prefix2 = substr($c, 0, 2);
        $prefix1 = substr($c, 0, 1);
        $num = (int)$c;

        if ($prefix3 === '150' || $prefix2 === '15') {
            return ['group' => 'TOP', 'category' => 'Biaya Dibayar Dimuka', 'order' => 1];
        }
        if ($num >= 66000 && $num <= 69999) {
            return ['group' => 'TOP', 'category' => 'Biaya Adm & Umum', 'order' => 2];
        }
        if ($prefix2 === '13') {
            return ['group' => 'OPERATING', 'category' => 'Piutang Usaha', 'order' => 3];
        }
        if ($prefix2 === '14') {
            return ['group' => 'OPERATING', 'category' => 'Persediaan', 'order' => 4];
        }
        if ($prefix2 === '21') {
            return ['group' => 'OPERATING', 'category' => 'Utang Usaha', 'order' => 5];
        }
        if ($prefix1 === '5') {
            return ['group' => 'OPERATING', 'category' => 'Beban atas Pendapatan', 'order' => 6];
        }
        if ($num >= 61000 && $num <= 65999) {
            return ['group' => 'OPERATING', 'category' => 'Beban Pemasaran Dan Penjualan', 'order' => 7];
        }
        if ($prefix1 === '4') {
            return ['group' => 'OPERATING', 'category' => 'Pendapatan Usaha', 'order' => 8];
        }
        if ($prefix1 === '8') {
            return ['group' => 'OPERATING', 'category' => 'Pendapatan Luar Usaha', 'order' => 9];
        }
        if ($num >= 91000 && $num <= 98999) {
            return ['group' => 'OPERATING', 'category' => 'Beban Luar Usaha', 'order' => 10];
        }
        if ($num >= 99000 && $num <= 99999) {
            return ['group' => 'OPERATING', 'category' => 'Beban Pajak', 'order' => 11];
        }
        if ($prefix2 === '16') {
            return ['group' => 'INVESTING', 'category' => 'Investasi Jangka Panjang', 'order' => 12];
        }
        if ($prefix2 === '17' || $prefix2 === '18') {
            return ['group' => 'INVESTING', 'category' => 'Harta Tetap Berwujud', 'order' => 13];
        }
        if ($prefix1 === '3') {
            return ['group' => 'FINANCING', 'category' => 'Modal', 'order' => 14];
        }
        if ($prefix1 === '2') {
            return ['group' => 'FINANCING', 'category' => 'Kewajiban Jangka Panjang', 'order' => 15];
        }

        return ['group' => 'OPERATING', 'category' => 'Lain-Lain', 'order' => 99];
    }

    /**
     * Memformat kode akun gaya Zahir (misal 15012 menjadi 150-12).
     */
    public function format_kode_akun_zahir($kode)
    {
        $kode = trim((string)$kode);
        if (strpos($kode, '-') !== false) return $kode;
        if (strlen($kode) >= 5) {
            return substr($kode, 0, 3) . '-' . substr($kode, 3);
        }
        return $kode;
    }

    // =========================================================================
    // MODUL LAPORAN LABA RUGI (ZAHIR ACCOUNTING STYLE)
    // =========================================================================

    /**
     * Menghitung data Laporan Laba Rugi bergaya Zahir Accounting.
     *
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate   'YYYY-MM-DD'
     * @param bool   $calcYtd   Hitung juga Year-to-Date (awal tahun fiskal s/d $endDate)
     * @return array
     */
    public function get_laporan_laba_rugi($startDate, $endDate, $calcYtd = false)
    {
        $yearStart = date('Y-01-01', strtotime($endDate));

        // 1. Ambil data transaksi mutasi jurnal POSTED pada rentang tanggal
        $sql = "SELECT 
                    a.id_akun,
                    a.kode_akun,
                    a.nama_akun,
                    a.saldo_normal,
                    COALESCE(SUM(CASE WHEN j.tanggal_transaksi >= " . $this->db->escape($startDate) . " AND j.tanggal_transaksi <= " . $this->db->escape($endDate) . " THEN d.debit ELSE 0 END), 0) AS cur_debit,
                    COALESCE(SUM(CASE WHEN j.tanggal_transaksi >= " . $this->db->escape($startDate) . " AND j.tanggal_transaksi <= " . $this->db->escape($endDate) . " THEN d.kredit ELSE 0 END), 0) AS cur_kredit,
                    COALESCE(SUM(CASE WHEN j.tanggal_transaksi >= " . $this->db->escape($yearStart) . " AND j.tanggal_transaksi <= " . $this->db->escape($endDate) . " THEN d.debit ELSE 0 END), 0) AS ytd_debit,
                    COALESCE(SUM(CASE WHEN j.tanggal_transaksi >= " . $this->db->escape($yearStart) . " AND j.tanggal_transaksi <= " . $this->db->escape($endDate) . " THEN d.kredit ELSE 0 END), 0) AS ytd_kredit
                FROM tbkeu_akun a
                JOIN tbkeu_jurnal_detail d ON d.id_akun = a.id_akun
                JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal AND j.status = 'POSTED'
                WHERE (a.kode_akun >= '40000' AND a.kode_akun <= '99999')
                  AND j.tanggal_transaksi >= " . ($calcYtd ? $this->db->escape($yearStart) : $this->db->escape($startDate)) . "
                  AND j.tanggal_transaksi <= " . $this->db->escape($endDate) . "
                GROUP BY a.id_akun
                ORDER BY a.kode_akun ASC";

        $rows = $this->db->query($sql)->result_array();

        // Siapkan kerangka pos laporan sesuai Zahir
        $groups = [
            'pendapatan_usaha'          => [],
            'pendapatan_lain'           => [],
            'beban_atas_pendapatan'     => [],
            'beban_pemasaran_penjualan' => [],
            'biaya_adm_umum'            => [],
            'beban_non_operasional'     => [],
            'pendapatan_luar_usaha'     => [],
            'beban_luar_usaha'          => [],
            'beban_pajak'               => [],
        ];

        $totals = [
            'pendapatan_usaha'          => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
            'pendapatan_lain'           => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
            'beban_atas_pendapatan'     => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
            'beban_pemasaran_penjualan' => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
            'biaya_adm_umum'            => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
            'beban_non_operasional'     => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
            'pendapatan_luar_usaha'     => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
            'beban_luar_usaha'          => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
            'beban_pajak'               => ['cur' => 0.0, 'ytd' => 0.0, 'budget' => 0.0],
        ];

        foreach ($rows as $r) {
            $kode = trim($r['kode_akun']);
            $num  = (int)preg_replace('/\D/', '', $kode);
            $p2   = substr($kode, 0, 2);
            $p3   = substr($kode, 0, 3);

            $isRevenue = in_array(substr($kode, 0, 1), ['4', '8'], true);
            $curAmount = $isRevenue 
                ? ((float)$r['cur_kredit'] - (float)$r['cur_debit']) 
                : ((float)$r['cur_debit'] - (float)$r['cur_kredit']);
            $ytdAmount = $isRevenue 
                ? ((float)$r['ytd_kredit'] - (float)$r['ytd_debit']) 
                : ((float)$r['ytd_debit'] - (float)$r['ytd_kredit']);

            // Abaikan jika kedua saldo 0
            if (abs($curAmount) < 0.0001 && abs($ytdAmount) < 0.0001) {
                continue;
            }

            // Tentukan target sub-group Zahir
            $targetGroup = null;
            if ($p2 === '41') {
                $targetGroup = 'pendapatan_usaha';
            } elseif ($p2 === '49') {
                $targetGroup = 'pendapatan_lain';
            } elseif (substr($kode, 0, 1) === '5') {
                $targetGroup = 'beban_atas_pendapatan';
            } elseif ($num >= 61000 && $num < 66000) {
                $targetGroup = 'beban_pemasaran_penjualan';
            } elseif ($num >= 66000 && $num < 70000) {
                $targetGroup = 'biaya_adm_umum';
            } elseif (substr($kode, 0, 1) === '7') {
                $targetGroup = 'beban_non_operasional';
            } elseif (substr($kode, 0, 1) === '8') {
                $targetGroup = 'pendapatan_luar_usaha';
            } elseif ($num >= 99000) {
                $targetGroup = 'beban_pajak';
            } elseif (substr($kode, 0, 1) === '9') {
                $targetGroup = 'beban_luar_usaha';
            }

            if ($targetGroup && isset($groups[$targetGroup])) {
                $item = [
                    'id_akun'      => $r['id_akun'],
                    'kode_akun'    => $kode,
                    'kode_tampil'  => $this->format_kode_akun_zahir($kode),
                    'nama_akun'    => $r['nama_akun'],
                    'saldo'        => $curAmount,
                    'ytd'          => $ytdAmount,
                    'budget'       => 0.0, // Default 0.00 jika belum ada master anggaran
                ];
                $groups[$targetGroup][] = $item;
                $totals[$targetGroup]['cur']    += $curAmount;
                $totals[$targetGroup]['ytd']    += $ytdAmount;
                $totals[$targetGroup]['budget'] += 0.0;
            }
        }

        // Kalkulasi Summary & Laba Rugi Bersih
        $calcSummary = function($key) use ($totals) {
            $totalPendapatanUsaha = $totals['pendapatan_usaha'][$key];
            $totalPendapatanLain  = $totals['pendapatan_lain'][$key];
            $totalPendapatan      = $totalPendapatanUsaha + $totalPendapatanLain;

            $totalBebanPokok      = $totals['beban_atas_pendapatan'][$key];
            $labaRugiKotor        = $totalPendapatan - $totalBebanPokok;

            $totalBebanPemasaran  = $totals['beban_pemasaran_penjualan'][$key];
            $totalBiayaAdmUmum    = $totals['biaya_adm_umum'][$key];
            $totalBebanOperasional = $totalBebanPemasaran + $totalBiayaAdmUmum;

            $labaRugiOperasi1     = $labaRugiKotor - $totalBebanOperasional;
            $totalBebanNonOps     = $totals['beban_non_operasional'][$key];
            $labaRugiOperasi      = $labaRugiOperasi1 - $totalBebanNonOps;

            $totalPendapatanLuarUsaha = $totals['pendapatan_luar_usaha'][$key];
            $totalPendapatanLainAll   = $totalPendapatanLuarUsaha;

            $totalBebanLuarUsaha  = $totals['beban_luar_usaha'][$key];
            $totalBebanPajak      = $totals['beban_pajak'][$key];
            $totalBebanLainAll    = $totalBebanLuarUsaha + $totalBebanPajak;

            $labaRugiBersih       = $labaRugiOperasi + $totalPendapatanLainAll - $totalBebanLainAll;

            return [
                'total_pendapatan_usaha'    => $totalPendapatanUsaha,
                'total_pendapatan_lain'     => $totalPendapatanLain,
                'total_pendapatan'          => $totalPendapatan,
                'total_beban_pokok'         => $totalBebanPokok,
                'laba_rugi_kotor'           => $labaRugiKotor,
                'total_beban_pemasaran'     => $totalBebanPemasaran,
                'total_biaya_adm_umum'      => $totalBiayaAdmUmum,
                'total_beban_operasional'   => $totalBebanOperasional,
                'laba_rugi_operasi'         => $labaRugiOperasi,
                'total_beban_non_ops'       => $totalBebanNonOps,
                'total_pendapatan_luar'     => $totalPendapatanLuarUsaha,
                'total_pendapatan_lain_all' => $totalPendapatanLainAll,
                'total_beban_luar'          => $totalBebanLuarUsaha,
                'total_beban_pajak'         => $totalBebanPajak,
                'total_beban_lain_all'      => $totalBebanLainAll,
                'laba_rugi_bersih'          => $labaRugiBersih,
            ];
        };

        return [
            'groups'       => $groups,
            'totals'       => $totals,
            'summary_cur'  => $calcSummary('cur'),
            'summary_ytd'  => $calcSummary('ytd'),
            'summary_bud'  => $calcSummary('budget'),
        ];
    }

    /**
     * Menggabungkan dua set data Laba Rugi untuk komparasi 2 Periode.
     *
     * @param string $p1Start
     * @param string $p1End
     * @param string $p2Start
     * @param string $p2End
     * @return array
     */
    public function get_laporan_laba_rugi_perbandingan($p1Start, $p1End, $p2Start, $p2End)
    {
        $data1 = $this->get_laporan_laba_rugi($p1Start, $p1End, false);
        $data2 = $this->get_laporan_laba_rugi($p2Start, $p2End, false);

        $mergedGroups = [];
        $groupKeys = [
            'pendapatan_usaha',
            'pendapatan_lain',
            'beban_atas_pendapatan',
            'beban_pemasaran_penjualan',
            'biaya_adm_umum',
            'beban_non_operasional',
            'pendapatan_luar_usaha',
            'beban_luar_usaha',
            'beban_pajak',
        ];

        foreach ($groupKeys as $gKey) {
            $items1 = [];
            foreach ($data1['groups'][$gKey] as $it) {
                $items1[$it['kode_akun']] = $it;
            }
            $items2 = [];
            foreach ($data2['groups'][$gKey] as $it) {
                $items2[$it['kode_akun']] = $it;
            }

            $allCodes = array_unique(array_merge(array_keys($items1), array_keys($items2)));
            sort($allCodes);

            $mergedList = [];
            foreach ($allCodes as $code) {
                $row1 = $items1[$code] ?? null;
                $row2 = $items2[$code] ?? null;
                $nama = $row1 ? $row1['nama_akun'] : ($row2 ? $row2['nama_akun'] : '');
                $tampil = $this->format_kode_akun_zahir($code);

                $mergedList[] = [
                    'kode_akun'    => $code,
                    'kode_tampil'  => $tampil,
                    'nama_akun'    => $nama,
                    'saldo_p1'     => $row1 ? $row1['saldo'] : 0.0,
                    'budget_p1'    => $row1 ? $row1['budget'] : 0.0,
                    'saldo_p2'     => $row2 ? $row2['saldo'] : 0.0,
                    'budget_p2'    => $row2 ? $row2['budget'] : 0.0,
                ];
            }
            $mergedGroups[$gKey] = $mergedList;
        }

        return [
            'p1_start'     => $p1Start,
            'p1_end'       => $p1End,
            'p2_start'     => $p2Start,
            'p2_end'       => $p2End,
            'data1'        => $data1,
            'data2'        => $data2,
            'groups'       => $mergedGroups,
            'summary_p1'   => $data1['summary_cur'],
            'summary_p2'   => $data2['summary_cur'],
        ];
    }

    // =========================================================================
    // MODUL LAPORAN NERACA & NERACA SALDO (ZAHIR ACCOUNTING STYLE)
    // =========================================================================

    /**
     * Helper untuk memetakan kode akun ke sub-klasifikasi neraca.
     */
    public function map_account_to_neraca_section($kodeAkun)
    {
        $clean = preg_replace('/\D/', '', (string)$kodeAkun);
        $prefix3 = substr($clean, 0, 3);
        $prefix5 = substr($clean, 0, 5);

        // HARTA
        if ($prefix3 === '110') return 'kas';
        if ($prefix3 === '120' || $prefix3 === '121') return 'bank';
        if ($prefix5 === '13022' || $prefix3 === '139') return 'piutang_lain';
        if ($prefix3 === '130' || $prefix3 === '131') return 'piutang_usaha';
        if ($prefix3 === '140') return 'persediaan_barang';
        if ($prefix3 === '149') return 'persediaan_lain';
        if ($prefix3 === '150') return 'biaya_dibayar_dimuka';
        if ($prefix3 === '151') return 'uang_muka_dibayar';
        if ($prefix3 === '152') return 'pajak_dibayar_dimuka';
        if ($prefix3 === '153') return 'biaya_dibayar_dimuka_lain';
        if ($prefix3 === '160') return 'investasi_jangka_panjang';
        if ($prefix3 === '170') return 'harta_tetap_berwujud';
        if ($prefix3 === '171') return 'akumulasi_penyusutan';
        if ($prefix3 === '180') return 'harta_tetap_tidak_berwujud';
        if ($prefix3 === '190') return 'harta_lainnya';

        // KEWAJIBAN
        if ($prefix3 === '210' || $prefix3 === '211') return 'utang_usaha';
        if ($prefix3 === '219') return 'utang_lain';
        if ($prefix3 === '221') return 'uang_muka_diterima';
        if ($prefix3 === '230') return 'utang_pajak';
        if ($prefix3 === '250') return 'utang_jangka_panjang';

        // MODAL
        if ($prefix3 === '310') return 'modal_ekuitas';
        if ($prefix3 === '320') return 'laba_ekuitas';

        return null;
    }

    /**
     * Menghitung data Laporan Neraca Standar Zahir pada bulan dan tahun tertentu.
     *
     * @param int $month 1-12
     * @param int $year 4-digit year
     * @return array
     */
    public function get_laporan_neraca($month, $year)
    {
        $month = (int)$month;
        $year  = (int)$year;
        $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
        $startDate   = "{$year}-{$monthPadded}-01";
        $cutoffDate  = date('Y-m-t', strtotime($startDate));
        $fiscalStart = "{$year}-01-01";

        // Definisi Pos Neraca Zahir
        $sectionsDef = [
            'harta' => [
                'kas'                       => ['title' => 'Kas', 'total_title' => 'Total Kas'],
                'bank'                      => ['title' => 'Bank', 'total_title' => 'Total Bank'],
                'piutang_usaha'             => ['title' => 'Piutang Usaha', 'total_title' => 'Total Piutang Usaha'],
                'piutang_lain'              => ['title' => 'Piutang Lain', 'total_title' => 'Total Piutang Lain'],
                'persediaan_barang'         => ['title' => 'Persediaan Barang', 'total_title' => 'Total Persediaan Barang'],
                'persediaan_lain'           => ['title' => 'Persediaan Lain', 'total_title' => 'Total Persediaan Lain', 'force_total' => true],
                'biaya_dibayar_dimuka'      => ['title' => 'Biaya Dibayar Dimuka', 'total_title' => 'Total Biaya Dibayar Dimuka'],
                'uang_muka_dibayar'         => ['title' => 'Uang Muka Dibayar', 'total_title' => 'Total Uang Muka Dibayar', 'force_total' => true],
                'pajak_dibayar_dimuka'      => ['title' => 'Pajak Dibayar Dimuka', 'total_title' => 'Total Pajak Dibayar Dimuka', 'force_total' => true],
                'biaya_dibayar_dimuka_lain' => ['title' => 'Biaya Dibayar Dimuka', 'total_title' => 'Total Biaya Dibayar Dimuka', 'force_total' => true],
                'investasi_jangka_panjang'  => ['title' => 'Investasi Jangka Panjang', 'total_title' => 'Total Investasi Jangka Panjang'],
                'harta_tetap_berwujud'      => ['title' => 'Harta Tetap Berwujud', 'total_title' => 'Total Harta Tetap Berwujud'],
                'akumulasi_penyusutan'      => ['title' => 'Akumulasi Penyusutan Harta Tetap', 'total_title' => 'Total Akumulasi Penyusutan Harta Tetap', 'force_total' => true],
                'harta_tetap_tidak_berwujud'=> ['title' => 'Harta Tetap Tidak Berwujud', 'total_title' => 'Total Harta Tetap Tidak Berwujud', 'force_total' => true],
            ],
            'kewajiban' => [
                'utang_usaha'               => ['title' => 'Utang Usaha', 'total_title' => 'Total Utang Usaha'],
                'utang_lain'                => ['title' => 'Utang Lain', 'total_title' => 'Total Utang Lain', 'force_total' => true],
                'uang_muka_diterima'        => ['title' => 'Uang Muka Diterima', 'total_title' => 'Total Uang Muka Diterima', 'force_total' => true],
                'utang_pajak'               => ['title' => 'Utang Pajak', 'total_title' => 'Total Utang Pajak', 'force_total' => true],
                'utang_jangka_panjang'      => ['title' => 'Utang Jangka Panjang', 'total_title' => 'Total Utang Jangka Panjang', 'force_total' => true],
            ],
            'modal' => [
                'modal_ekuitas'             => ['title' => 'Modal', 'total_title' => 'Total Modal'],
                'laba_ekuitas'              => ['title' => 'Laba', 'total_title' => 'Total Laba'],
            ]
        ];

        // 1. Ambil mutasi kumulatif transaksi posted sampai dengan cutoff date
        $sql = "SELECT 
                    a.id_akun,
                    a.kode_akun,
                    a.nama_akun,
                    a.saldo_normal,
                    COALESCE(SUM(d.debit), 0) AS total_debit,
                    COALESCE(SUM(d.kredit), 0) AS total_kredit
                FROM tbkeu_akun a
                LEFT JOIN tbkeu_jurnal_detail d ON d.id_akun = a.id_akun
                LEFT JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal AND j.status = 'POSTED' AND j.tanggal_transaksi <= " . $this->db->escape($cutoffDate) . "
                WHERE a.kode_akun < '40000'
                GROUP BY a.id_akun
                ORDER BY a.kode_akun ASC";

        $rows = $this->db->query($sql)->result_array();

        // 2. Hitung Laba Bersih Tahun Berjalan dari Awal Tahun Fiskal s/d Cutoff Date
        $sqlLaba = "SELECT 
                        COALESCE(SUM(CASE WHEN a.kode_akun >= '40000' AND a.kode_akun < '50000' THEN (d.kredit - d.debit)
                                          WHEN a.kode_akun >= '80000' AND a.kode_akun < '90000' THEN (d.kredit - d.debit)
                                          WHEN a.kode_akun >= '50000' AND a.kode_akun < '80000' THEN (d.debit - d.kredit)
                                          WHEN a.kode_akun >= '90000' THEN (d.debit - d.kredit)
                                          ELSE 0 END), 0) as net_income
                    FROM tbkeu_akun a
                    JOIN tbkeu_jurnal_detail d ON d.id_akun = a.id_akun
                    JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal AND j.status = 'POSTED'
                    WHERE a.kode_akun >= '40000'
                      AND j.tanggal_transaksi >= " . $this->db->escape($fiscalStart) . "
                      AND j.tanggal_transaksi <= " . $this->db->escape($cutoffDate);
        $rowLaba = $this->db->query($sqlLaba)->row_array();
        $labaTahunBerjalan = (float)($rowLaba['net_income'] ?? 0.0);

        // Inisialisasi struktur hasil
        $sections = [];
        $totals   = [];

        foreach ($sectionsDef as $catKey => $catItems) {
            $sections[$catKey] = [];
            $totals[$catKey] = 0.0;
            foreach ($catItems as $sKey => $sConf) {
                $sections[$catKey][$sKey] = [
                    'title'       => $sConf['title'],
                    'total_title' => $sConf['total_title'],
                    'force_total' => !empty($sConf['force_total']),
                    'items'       => [],
                    'total'       => 0.0,
                ];
            }
        }

        $foundLabaBerjalan = false;

        foreach ($rows as $r) {
            $kode = trim($r['kode_akun']);
            $nama = trim($r['nama_akun']);
            $secKey = $this->map_account_to_neraca_section($kode);
            if (!$secKey) continue;

            $firstChar = substr($kode, 0, 1);
            // Penghitungan saldo sesuai karakteristik pos
            if ($firstChar === '1') {
                $saldo = (float)$r['total_debit'] - (float)$r['total_kredit'];
            } else {
                $saldo = (float)$r['total_kredit'] - (float)$r['total_debit'];
            }

            // Cek apakah akun adalah 32097 / Laba Tahun Berjalan
            $cleanCode = preg_replace('/\D/', '', $kode);
            if ($cleanCode === '32097' || stripos($nama, 'Laba Tahun Berjalan') !== false) {
                $saldo = $labaTahunBerjalan;
                $foundLabaBerjalan = true;
            }

            // Jika saldo bernilai 0 dan tidak ada mutasi sama sekali, abaikan agar rapi
            if (abs($saldo) < 0.0001 && abs((float)$r['total_debit']) < 0.0001 && abs((float)$r['total_kredit']) < 0.0001) {
                // Kecuali akun penting di modal jika memang diperlukan
                if ($cleanCode !== '32098' && $cleanCode !== '32097') {
                    continue;
                }
            }

            $cat = ($firstChar === '1') ? 'harta' : (($firstChar === '2') ? 'kewajiban' : 'modal');

            $sections[$cat][$secKey]['items'][] = [
                'id_akun'     => $r['id_akun'],
                'kode_akun'   => $kode,
                'kode_tampil' => $this->format_kode_akun_zahir($kode),
                'nama_akun'   => $nama,
                'saldo'       => $saldo,
                'budget'      => 0.0,
            ];
            $sections[$cat][$secKey]['total'] += $saldo;
        }

        // Jika akun Laba Tahun Berjalan belum masuk dari baris di atas, tambahkan secara otomatis
        if (!$foundLabaBerjalan) {
            $sections['modal']['laba_ekuitas']['items'][] = [
                'id_akun'     => 0,
                'kode_akun'   => '32097',
                'kode_tampil' => '320-97',
                'nama_akun'   => 'Laba Tahun Berjalan',
                'saldo'       => $labaTahunBerjalan,
                'budget'      => 0.0,
            ];
            $sections['modal']['laba_ekuitas']['total'] += $labaTahunBerjalan;
        }

        // Hitung total tiap kategori
        foreach ($sections['harta'] as $s) {
            $totals['harta'] += $s['total'];
        }
        foreach ($sections['kewajiban'] as $s) {
            $totals['kewajiban'] += $s['total'];
        }

        $totalModal310 = $sections['modal']['modal_ekuitas']['total'];
        $totalLaba320  = $sections['modal']['laba_ekuitas']['total'];
        $totals['modal'] = $totalModal310 + $totalLaba320;
        $totals['kewajiban_dan_modal'] = $totals['kewajiban'] + $totals['modal'];

        return [
            'month'                 => $month,
            'year'                  => $year,
            'period_label'          => date('F Y', strtotime($startDate)),
            'cutoff_date'           => $cutoffDate,
            'sections'              => $sections,
            'totals'                => $totals,
            'total_modal_310'       => $totalModal310,
            'total_laba_320'        => $totalLaba320,
            'laba_tahun_berjalan'   => $labaTahunBerjalan,
        ];
    }

    /**
     * Menghitung Laporan Neraca Saldo (Trial Balance) 7 Kolom
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public function get_laporan_neraca_saldo($month, $year)
    {
        $month = (int)$month;
        $year  = (int)$year;
        $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
        $startDate   = "{$year}-{$monthPadded}-01";
        $endDate     = date('Y-m-t', strtotime($startDate));
        $fiscalStart = "{$year}-01-01";

        // Query seluruh akun beserta:
        // 1. Mutasi sebelum startDate (untuk saldo awal)
        // 2. Mutasi antara startDate dan endDate (untuk transaksi bulan ini)
        $sql = "SELECT 
                    a.id_akun,
                    a.kode_akun,
                    a.nama_akun,
                    a.saldo_normal,
                    -- Saldo Awal (sebelum bulan ini)
                    COALESCE(SUM(CASE 
                        WHEN j.status = 'POSTED' AND j.tanggal_transaksi < " . $this->db->escape($startDate) . " 
                             AND (a.kode_akun < '40000' OR j.tanggal_transaksi >= " . $this->db->escape($fiscalStart) . ")
                        THEN d.debit ELSE 0 END), 0) AS init_debit,
                    COALESCE(SUM(CASE 
                        WHEN j.status = 'POSTED' AND j.tanggal_transaksi < " . $this->db->escape($startDate) . " 
                             AND (a.kode_akun < '40000' OR j.tanggal_transaksi >= " . $this->db->escape($fiscalStart) . ")
                        THEN d.kredit ELSE 0 END), 0) AS init_kredit,
                    -- Transaksi Bulan Ini
                    COALESCE(SUM(CASE 
                        WHEN j.status = 'POSTED' AND j.tanggal_transaksi >= " . $this->db->escape($startDate) . " AND j.tanggal_transaksi <= " . $this->db->escape($endDate) . "
                        THEN d.debit ELSE 0 END), 0) AS cur_debit,
                    COALESCE(SUM(CASE 
                        WHEN j.status = 'POSTED' AND j.tanggal_transaksi >= " . $this->db->escape($startDate) . " AND j.tanggal_transaksi <= " . $this->db->escape($endDate) . "
                        THEN d.kredit ELSE 0 END), 0) AS cur_kredit
                FROM tbkeu_akun a
                LEFT JOIN tbkeu_jurnal_detail d ON d.id_akun = a.id_akun
                LEFT JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
                GROUP BY a.id_akun
                ORDER BY a.kode_akun ASC";

        $rows = $this->db->query($sql)->result_array();

        $items = [];
        $grandTotal = [
            'awal_debet'    => 0.0,
            'awal_kredit'   => 0.0,
            'mutasi_debet'  => 0.0,
            'mutasi_kredit' => 0.0,
            'akhir_debet'   => 0.0,
            'akhir_kredit'  => 0.0,
        ];

        foreach ($rows as $r) {
            $kode = trim($r['kode_akun']);
            $nama = trim($r['nama_akun']);

            $initNet = (float)$r['init_debit'] - (float)$r['init_kredit'];
            $curDeb  = (float)$r['cur_debit'];
            $curKre  = (float)$r['cur_kredit'];

            // Saldo Awal Debet/Kredit
            $awalDeb = ($initNet > 0.0001) ? $initNet : 0.0;
            $awalKre = ($initNet < -0.0001) ? abs($initNet) : 0.0;

            // Saldo Akhir
            $finalNet = ($initNet + $curDeb - $curKre);
            $akhirDeb = ($finalNet > 0.0001) ? $finalNet : 0.0;
            $akhirKre = ($finalNet < -0.0001) ? abs($finalNet) : 0.0;

            // Jika semua 0, hanya tampilkan jika ada transaksi atau merupakan akun master
            $hasActivity = (abs($awalDeb) > 0.0001 || abs($awalKre) > 0.0001 || abs($curDeb) > 0.0001 || abs($curKre) > 0.0001 || abs($akhirDeb) > 0.0001 || abs($akhirKre) > 0.0001);

            $item = [
                'id_akun'       => $r['id_akun'],
                'kode_akun'     => $kode,
                'kode_tampil'   => $this->format_kode_akun_zahir($kode),
                'nama_akun'     => $nama,
                'awal_debet'    => $awalDeb,
                'awal_kredit'   => $awalKre,
                'mutasi_debet'  => $curDeb,
                'mutasi_kredit' => $curKre,
                'akhir_debet'   => $akhirDeb,
                'akhir_kredit'  => $akhirKre,
                'has_activity'  => $hasActivity,
            ];

            $items[] = $item;

            $grandTotal['awal_debet']    += $awalDeb;
            $grandTotal['awal_kredit']   += $awalKre;
            $grandTotal['mutasi_debet']  += $curDeb;
            $grandTotal['mutasi_kredit'] += $curKre;
            $grandTotal['akhir_debet']   += $akhirDeb;
            $grandTotal['akhir_kredit']  += $akhirKre;
        }

        return [
            'month'        => $month,
            'year'         => $year,
            'period_label' => date('F Y', strtotime($startDate)),
            'items'        => $items,
            'grand_total'  => $grandTotal,
        ];
    }

    /**
     * Menghitung Laporan Neraca 12 Bulan dalam 1 Tahun Fiskal
     *
     * @param int $year
     * @return array
     */
    public function get_laporan_neraca_12_bulan($year)
    {
        $year = (int)$year;
        $monthlyData = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = $this->get_laporan_neraca($m, $year);
        }

        // Konsolidasikan daftar pos & akun dari ke-12 bulan
        $firstMonth = $monthlyData[1];
        $sections = $firstMonth['sections'];

        foreach ($sections as $catKey => &$catSections) {
            foreach ($catSections as $sKey => &$section) {
                $accountMap = [];
                for ($m = 1; $m <= 12; $m++) {
                    $mSection = $monthlyData[$m]['sections'][$catKey][$sKey] ?? null;
                    if ($mSection && !empty($mSection['items'])) {
                        foreach ($mSection['items'] as $item) {
                            $code = $item['kode_akun'];
                            if (!isset($accountMap[$code])) {
                                $accountMap[$code] = [
                                    'kode_akun'   => $code,
                                    'kode_tampil' => $item['kode_tampil'],
                                    'nama_akun'   => $item['nama_akun'],
                                    'monthly'     => array_fill(1, 12, 0.0),
                                ];
                            }
                            $accountMap[$code]['monthly'][$m] = $item['saldo'];
                        }
                    }
                }
                ksort($accountMap);
                $section['consolidated_items'] = array_values($accountMap);

                // Hitung total bulanan untuk seksi ini
                $section['monthly_totals'] = [];
                for ($m = 1; $m <= 12; $m++) {
                    $section['monthly_totals'][$m] = $monthlyData[$m]['sections'][$catKey][$sKey]['total'] ?? 0.0;
                }
            }
        }

        // Totals per bulan untuk Harta, Kewajiban, Modal, dll
        $monthlyTotals = [
            'harta'                => [],
            'kewajiban'            => [],
            'modal'                => [],
            'kewajiban_dan_modal'  => [],
        ];

        for ($m = 1; $m <= 12; $m++) {
            $monthlyTotals['harta'][$m]               = $monthlyData[$m]['totals']['harta'];
            $monthlyTotals['kewajiban'][$m]           = $monthlyData[$m]['totals']['kewajiban'];
            $monthlyTotals['modal'][$m]               = $monthlyData[$m]['totals']['modal'];
            $monthlyTotals['kewajiban_dan_modal'][$m] = $monthlyData[$m]['totals']['kewajiban_dan_modal'];
        }

        return [
            'year'           => $year,
            'sections'       => $sections,
            'monthly_totals' => $monthlyTotals,
            'monthly_data'   => $monthlyData,
        ];
    }

    /**
     * Menggabungkan 2 Periode Neraca untuk Komparasi (2 Kolom & 4 Kolom)
     */
    public function get_laporan_neraca_perbandingan($p1Month, $p1Year, $p2Month, $p2Year)
    {
        $data1 = $this->get_laporan_neraca($p1Month, $p1Year);
        $data2 = $this->get_laporan_neraca($p2Month, $p2Year);

        $mergedSections = [];
        $sections = $data1['sections'];

        foreach ($sections as $catKey => $catSections) {
            $mergedSections[$catKey] = [];
            foreach ($catSections as $sKey => $section1) {
                $items1 = [];
                foreach ($section1['items'] as $it) {
                    $items1[$it['kode_akun']] = $it;
                }

                $items2 = [];
                $section2 = $data2['sections'][$catKey][$sKey] ?? null;
                if ($section2 && !empty($section2['items'])) {
                    foreach ($section2['items'] as $it) {
                        $items2[$it['kode_akun']] = $it;
                    }
                }

                $allCodes = array_unique(array_merge(array_keys($items1), array_keys($items2)));
                sort($allCodes);

                $mergedList = [];
                foreach ($allCodes as $code) {
                    $row1 = $items1[$code] ?? null;
                    $row2 = $items2[$code] ?? null;
                    $nama = $row1 ? $row1['nama_akun'] : ($row2 ? $row2['nama_akun'] : '');
                    $tampil = $this->format_kode_akun_zahir($code);

                    $mergedList[] = [
                        'kode_akun'   => $code,
                        'kode_tampil' => $tampil,
                        'nama_akun'   => $nama,
                        'saldo_p1'    => $row1 ? $row1['saldo'] : 0.0,
                        'budget_p1'   => $row1 ? $row1['budget'] : 0.0,
                        'saldo_p2'    => $row2 ? $row2['saldo'] : 0.0,
                        'budget_p2'   => $row2 ? $row2['budget'] : 0.0,
                    ];
                }

                $mergedSections[$catKey][$sKey] = [
                    'title'       => $section1['title'],
                    'total_title' => $section1['total_title'],
                    'force_total' => $section1['force_total'],
                    'items'       => $mergedList,
                    'total_p1'    => $section1['total'],
                    'total_p2'    => $section2 ? $section2['total'] : 0.0,
                    'budget_p1'   => 0.0,
                    'budget_p2'   => 0.0,
                ];
            }
        }

        return [
            'p1_month'     => $p1Month,
            'p1_year'      => $p1Year,
            'p1_label'     => $data1['period_label'],
            'p2_month'     => $p2Month,
            'p2_year'      => $p2Year,
            'p2_label'     => $data2['period_label'],
            'data1'        => $data1,
            'data2'        => $data2,
            'sections'     => $mergedSections,
            'totals_p1'    => $data1['totals'],
            'totals_p2'    => $data2['totals'],
        ];
    }

    /**
     * Ambil daftar akun aktif untuk opsi filter dropdown Buku Besar.
     */
    public function zahir_coa_options()
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        return $this->db
            ->select('id_akun, kode_akun, nama_akun')
            ->from('tbkeu_akun')
            ->where('is_active', 1)
            ->order_by('kode_akun', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Laporan Buku Besar format Zahir (Standar, Mutasi, Mata Uang Sebenarnya, Mutasi Mata Uang Sebenarnya).
     */
    public function zahir_ledger_report($start_date = '', $end_date = '', $id_akun = '', $search = '')
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $start_date = trim((string) $start_date);
        $end_date   = trim((string) $end_date);
        $id_akun    = trim((string) $id_akun);
        $search     = trim((string) $search);

        // Ambil daftar akun yang sesuai filter
        $this->db->select('a.id_akun, a.kode_akun, a.nama_akun, a.saldo_normal');
        $this->db->from('tbkeu_akun a');
        $this->db->where('a.is_active', 1);
        if ($id_akun !== '') {
            $this->db->where('a.id_akun', (int) $id_akun);
        }
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('a.kode_akun', $search);
            $this->db->or_like('a.nama_akun', $search);
            $this->db->group_end();
        }
        $this->db->order_by('a.kode_akun', 'ASC');
        $accounts = $this->db->get()->result_array();

        if (empty($accounts)) {
            return [];
        }

        $accountIds = array_map(function ($row) {
            return (int) $row['id_akun'];
        }, $accounts);

        // Hitung saldo awal sebelum tanggal mulai
        $openingMap = [];
        if ($start_date !== '') {
            $openingRows = $this->db
                ->select('d.id_akun, COALESCE(SUM(d.debit), 0) AS total_debit, COALESCE(SUM(d.kredit), 0) AS total_kredit', false)
                ->from('tbkeu_jurnal_detail d')
                ->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner')
                ->where_in('d.id_akun', $accountIds)
                ->where('j.status', 'POSTED')
                ->where('j.reversed_at IS NULL', null, false)
                ->where('j.tanggal_transaksi <', $start_date)
                ->group_by('d.id_akun')
                ->get()
                ->result_array();

            foreach ($openingRows as $orow) {
                $openingMap[(int) $orow['id_akun']] = [
                    'debit'  => (float) $orow['total_debit'],
                    'kredit' => (float) $orow['total_kredit'],
                ];
            }
        }

        // Ambil seluruh transaksi detail pada rentang tanggal
        $this->db->select("
            d.id_akun,
            j.id_jurnal,
            j.tanggal_transaksi,
            COALESCE(NULLIF(jj.kode_jenis_jurnal, ''), 'GJ') AS tp,
            COALESCE(NULLIF(j.source_no, ''), j.nomor_jurnal) AS no_ref,
            j.nomor_jurnal,
            COALESCE(NULLIF(d.keterangan, ''), j.keterangan, '') AS keterangan,
            COALESCE(NULLIF(d.id_departemen, 0), '') AS no_dept,
            '' AS no_proyek,
            d.debit,
            d.kredit
        ", false);
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
        $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
        $this->db->where_in('d.id_akun', $accountIds);
        $this->db->where('j.status', 'POSTED');
        $this->db->where('j.reversed_at IS NULL', null, false);
        if ($start_date !== '' && $end_date !== '') {
            $this->db->where('j.tanggal_transaksi >=', $start_date);
            $this->db->where('j.tanggal_transaksi <=', $end_date);
        }
        $this->db->order_by('j.tanggal_transaksi', 'ASC');
        $this->db->order_by('j.id_jurnal', 'ASC');
        $this->db->order_by('d.nomor_baris', 'ASC');
        $detailRows = $this->db->get()->result_array();

        $detailsByAccount = [];
        foreach ($detailRows as $drow) {
            $detailsByAccount[(int) $drow['id_akun']][] = $drow;
        }

        $result = [];
        foreach ($accounts as $acc) {
            $aid = (int) $acc['id_akun'];
            $isCreditNormal = strtoupper((string) ($acc['saldo_normal'] ?? 'DEBIT')) === 'KREDIT';

            $openDeb = $openingMap[$aid]['debit'] ?? 0.0;
            $openKre = $openingMap[$aid]['kredit'] ?? 0.0;
            $openingBalance = $isCreditNormal ? ($openKre - $openDeb) : ($openDeb - $openKre);

            $txRows = $detailsByAccount[$aid] ?? [];

            // Tampilkan hanya akun yang memiliki saldo awal atau mutasi di periode ini (kecuali filter akun spesifik)
            if ($id_akun === '' && abs($openingBalance) < 0.0001 && empty($txRows)) {
                continue;
            }

            $running = $openingBalance;
            $totalDebit = 0.0;
            $totalKredit = 0.0;
            $formattedRows = [];

            foreach ($txRows as $tx) {
                $deb = (float) ($tx['debit'] ?? 0);
                $kre = (float) ($tx['kredit'] ?? 0);
                $totalDebit += $deb;
                $totalKredit += $kre;
                $running += $isCreditNormal ? ($kre - $deb) : ($deb - $kre);

                $formattedRows[] = [
                    'tanggal_transaksi' => $tx['tanggal_transaksi'],
                    'tp'                => $tx['tp'],
                    'no_ref'            => $tx['no_ref'],
                    'nomor_jurnal'      => $tx['nomor_jurnal'],
                    'keterangan'        => $tx['keterangan'],
                    'no_dept'           => $tx['no_dept'],
                    'no_proyek'         => $tx['no_proyek'],
                    'debit'             => $deb,
                    'kredit'            => $kre,
                    'saldo'             => $running,
                ];
            }

            $result[] = [
                'id_akun'         => $aid,
                'kode_akun'       => $acc['kode_akun'],
                'nama_akun'       => $acc['nama_akun'],
                'saldo_normal'    => $acc['saldo_normal'],
                'opening_balance' => $openingBalance,
                'total_debit'     => $totalDebit,
                'total_kredit'    => $totalKredit,
                'ending_balance'  => $running,
                'rows'            => $formattedRows,
            ];
        }

        return $result;
    }

    /**
     * Laporan Daftar Jurnal & Jejak Audit format Zahir.
     * Mode: 'all', 'general', 'audit_summary', 'audit_all', 'audit_detail'.
     */
    public function zahir_journal_list_report($start_date = '', $end_date = '', $mode = 'all', $keterangan = '', $nomor = '')
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $isAudit = strpos((string) $mode, 'audit') === 0;

        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            COALESCE(NULLIF(jj.kode_jenis_jurnal, ''), 'GJ') AS tp,
            COALESCE(NULLIF(j.source_no, ''), j.nomor_jurnal) AS no_ref,
            j.keterangan,
            j.status,
            j.total_debit,
            j.total_kredit,
            j.created_at,
            j.updated_at,
            j.reversed_at,
            COALESCE(NULLIF(u_create.nama_lngkp, ''), NULLIF(u_create.username, ''), 'System') AS created_by_name,
            COALESCE(NULLIF(u_upd.nama_lngkp, ''), NULLIF(u_upd.username, ''), '') AS updated_by_name,
            COALESCE(NULLIF(u_rev.nama_lngkp, ''), NULLIF(u_rev.username, ''), '') AS reversed_by_name
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
        $this->db->join('tb_users u_create', 'u_create.id = j.created_by', 'left');
        $this->db->join('tb_users u_upd', 'u_upd.id = j.updated_by', 'left');
        $this->db->join('tb_users u_rev', 'u_rev.id = j.reversed_by', 'left');

        if (!$isAudit) {
            $this->db->where('j.status', 'POSTED');
            $this->db->where('j.reversed_at IS NULL', null, false);
        }

        if ($mode === 'general') {
            $this->db->group_start();
            $this->db->where_in('jj.kode_jenis_jurnal', ['JU', 'GJ']);
            $this->db->or_where('j.source_module', 'ACCOUNTING');
            $this->db->group_end();
        }

        $start_date = trim((string) $start_date);
        $end_date   = trim((string) $end_date);
        if ($start_date !== '' && $end_date !== '') {
            $this->db->where('j.tanggal_transaksi >=', $start_date);
            $this->db->where('j.tanggal_transaksi <=', $end_date);
        }

        $keterangan = trim((string) $keterangan);
        if ($keterangan !== '') {
            $this->db->like('j.keterangan', $keterangan);
        }

        $nomor = trim((string) $nomor);
        if ($nomor !== '') {
            $this->db->group_start();
            $this->db->like('j.nomor_jurnal', $nomor);
            $this->db->or_like('j.source_no', $nomor);
            $this->db->or_like('j.source_id', $nomor);
            $this->db->group_end();
        }

        $this->db->order_by('j.tanggal_transaksi', 'ASC');
        $this->db->order_by('j.id_jurnal', 'ASC');
        $headers = $this->db->get()->result_array();

        if (empty($headers)) {
            return [];
        }

        // Untuk rangkuman audit tidak perlu memuat baris detail akun
        if ($mode === 'audit_summary') {
            return $headers;
        }

        $journalIds = array_column($headers, 'id_jurnal');
        $detailRows = $this->db
            ->select("
                d.id_jurnal,
                d.nomor_baris,
                a.kode_akun,
                a.nama_akun,
                COALESCE(NULLIF(d.keterangan, ''), '') AS keterangan,
                d.debit,
                d.kredit,
                COALESCE(NULLIF(d.id_departemen, 0), '') AS no_dept,
                '' AS no_proyek
            ", false)
            ->from('tbkeu_jurnal_detail d')
            ->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner')
            ->where_in('d.id_jurnal', $journalIds)
            ->order_by('d.id_jurnal', 'ASC')
            ->order_by('d.nomor_baris', 'ASC')
            ->get()
            ->result_array();

        $detailsByJournal = [];
        foreach ($detailRows as $drow) {
            $detailsByJournal[$drow['id_jurnal']][] = $drow;
        }

        foreach ($headers as &$hdr) {
            $hdr['details'] = $detailsByJournal[$hdr['id_jurnal']] ?? [];
        }
        unset($hdr);

        return $headers;
    }

    /**
     * Laporan Daftar Rekening (Chart of Accounts) format Zahir.
     */
    public function zahir_coa_list_report($search = '')
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $search = trim((string) $search);

        $this->db->select("
            a.id_akun,
            a.kode_akun,
            a.nama_akun,
            a.nama_akun AS nama_alias,
            COALESCE(k.kode_klasifikasi, '') AS kode_klasifikasi,
            COALESCE(k.nama_klasifikasi, 'Lainnya') AS nama_klasifikasi,
            COALESCE(k.alias_klasifikasi, '') AS alias_klasifikasi,
            COALESCE(k.urutan, 999) AS urutan
        ", false);
        $this->db->from('tbkeu_akun a');
        $this->db->join('tbkeu_klasifikasi_akun k', 'k.id_klasifikasi = a.id_klasifikasi', 'left');
        $this->db->where('a.is_active', 1);

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('a.kode_akun', $search);
            $this->db->or_like('a.nama_akun', $search);
            $this->db->or_like('k.nama_klasifikasi', $search);
            $this->db->group_end();
        }

        $this->db->order_by('urutan', 'ASC');
        $this->db->order_by('a.kode_akun', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Data Laporan Pembelian format Zahir Accounting.
     * Mendukung: jurnal-pembelian, jurnal-diskon, penerimaan-barang-harian, rincian, efaktur.
     */
    public function accounting_purchase_zahir_report($type, $start_date, $end_date, $search = '')
    {
        $type       = trim((string) $type);
        $start_date = trim((string) $start_date);
        $end_date   = trim((string) $end_date);
        $search     = trim((string) $search);

        // 1. Jurnal Pembelian & Jurnal Diskon Pembelian
        if ($type === 'jurnal-pembelian' || $type === 'jurnal-diskon') {
            if (!$this->accounting_journal_schema_ready()) {
                return [];
            }

            $this->db->select("
                j.id_jurnal,
                j.nomor_jurnal,
                j.tanggal_transaksi,
                j.keterangan,
                j.source_no,
                j.total_debit,
                j.total_kredit,
                jj.kode_jenis_jurnal
            ", false);
            $this->db->from('tbkeu_jurnal j');
            $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
            $this->db->where('j.status', 'POSTED');

            if ($type === 'jurnal-pembelian') {
                $this->db->where('jj.kode_jenis_jurnal', 'PJ');
            } else {
                $this->db->group_start();
                $this->db->where('jj.kode_jenis_jurnal', 'PD');
                $this->db->or_like('j.keterangan', 'Potongan Pembelian');
                $this->db->or_where("EXISTS (SELECT 1 FROM tbkeu_jurnal_detail jd2 JOIN tbkeu_akun a2 ON a2.id_akun = jd2.id_akun WHERE jd2.id_jurnal = j.id_jurnal AND (a2.kode_akun = '51097' OR a2.nama_akun LIKE '%Potongan Pembelian%'))", null, false);
                $this->db->group_end();
            }

            if ($start_date !== '') {
                $this->db->where('j.tanggal_transaksi >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('j.tanggal_transaksi <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('j.nomor_jurnal', $search);
                $this->db->or_like('j.source_no', $search);
                $this->db->or_like('j.keterangan', $search);
                $this->db->group_end();
            }

            $this->db->order_by('j.tanggal_transaksi', 'ASC');
            $this->db->order_by('j.id_jurnal', 'ASC');
            $headers = $this->db->get()->result_array();

            if (empty($headers)) {
                return [];
            }

            $journalIds = array_column($headers, 'id_jurnal');
            $detailRows = $this->db
                ->select("
                    d.id_jurnal,
                    d.nomor_baris,
                    a.kode_akun,
                    a.nama_akun,
                    d.keterangan,
                    d.debit,
                    d.kredit,
                    COALESCE(NULLIF(TRIM(d.nomor_dokumen), ''), '') AS nomor_dokumen,
                    '-' AS departemen,
                    '-' AS proyek
                ", false)
                ->from('tbkeu_jurnal_detail d')
                ->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left')
                ->where_in('d.id_jurnal', $journalIds)
                ->order_by('d.id_jurnal', 'ASC')
                ->order_by('d.nomor_baris', 'ASC')
                ->get()
                ->result_array();

            $detailsByJournal = [];
            foreach ($detailRows as $drow) {
                $detailsByJournal[$drow['id_jurnal']][] = $drow;
            }

            foreach ($headers as &$hdr) {
                $hdr['details'] = $detailsByJournal[$hdr['id_jurnal']] ?? [];
            }
            unset($hdr);

            return $headers;
        }

        // 2. Penerimaan Barang (Harian) - Format Laporan Pak Supri
        if ($type === 'penerimaan-barang-harian') {
            $this->db->select("
                l.id_lpb,
                DATE(COALESCE(l.tgl_sj, l.input_at)) AS tanggal_lpb,
                l.nomor_lpb,
                p.tgl_transaksi AS tanggal_po,
                COALESCE(NULLIF(TRIM(l.no_po), ''), NULLIF(TRIM(p.no_po), ''), '-') AS no_po,
                COALESCE(NULLIF(TRIM(l.nosj), ''), '-') AS nosj,
                COALESCE(NULLIF(TRIM(l.kd_suplier), ''), NULLIF(TRIM(p.kd_suplier), ''), '-') AS kd_suplier,
                COALESCE(NULLIF(TRIM(l.nama_suplier), ''), NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_suplier,
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), '-') AS nama_gudang,
                ld.id_detail_lpb,
                ld.kd_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), ld.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(lb.no_lot), ''), NULLIF(TRIM(ld.no_lot), ''), '-') AS no_lot,
                COALESCE(lb.expired_date, ld.expired_date) AS expired_date,
                COALESCE(lb.qty, ld.qty_diterima, 0) AS qty,
                COALESCE(ld.harga_satuan, 0) AS harga_satuan
            ", false);
            $this->db->from('tb_lpb l');
            $this->db->join('tb_lpb_detail ld', 'ld.id_lpb = l.id_lpb', 'inner');
            $this->db->join('tb_lpb_batch lb', 'lb.id_detail_lpb = ld.id_detail_lpb', 'left');
            $this->db->join('tbpo_po p', 'p.kd_po = l.kd_po', 'left');
            $this->db->join('tbpo_suplier s', "s.kd_suplier = COALESCE(NULLIF(l.kd_suplier, ''), p.kd_suplier)", 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = l.gudang_id', 'left');
            $this->db->join('tbpo_barang b', 'b.kode_barang = ld.kd_barang', 'left');

            if ($start_date !== '') {
                $this->db->where('DATE(COALESCE(l.tgl_sj, l.input_at)) >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('DATE(COALESCE(l.tgl_sj, l.input_at)) <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.nomor_lpb', $search);
                $this->db->or_like('l.no_po', $search);
                $this->db->or_like('p.no_po', $search);
                $this->db->or_like('l.nosj', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('l.nama_suplier', $search);
                $this->db->or_like('ld.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('lb.no_lot', $search);
                $this->db->or_like('ld.no_lot', $search);
                $this->db->group_end();
            }

            $this->db->order_by('tanggal_lpb', 'ASC');
            $this->db->order_by('l.id_lpb', 'ASC');
            $this->db->order_by('ld.id_detail_lpb', 'ASC');
            $this->db->order_by('lb.id_batch', 'ASC');
            $rows = $this->db->get()->result_array();

            if (empty($rows)) {
                return [];
            }

            // Ambil data penyesuaian harga jika ada untuk LPB terkait
            $lpbIds = array_values(array_unique(array_column($rows, 'id_lpb')));
            $adjByLpb = [];
            if (!empty($lpbIds)) {
                $adjRows = $this->db
                    ->select("
                        pa.id_lpb_salah,
                        pa.no_adjustment,
                        pa.tanggal_adjustment,
                        pad.kd_barang,
                        COALESCE(NULLIF(TRIM(pad.nama_barang), ''), pad.kd_barang) AS nama_barang,
                        pad.no_lot_adjustment,
                        pad.expired_date_adjustment,
                        pad.qty,
                        pad.harga_benar
                    ", false)
                    ->from('tb_lpb_price_adjustment pa')
                    ->join('tb_lpb_price_adjustment_detail pad', 'pad.id_adjustment = pa.id_adjustment', 'inner')
                    ->where_in('pa.id_lpb_salah', $lpbIds)
                    ->order_by('pa.tanggal_adjustment', 'ASC')
                    ->get()
                    ->result_array();

                foreach ($adjRows as $ar) {
                    $adjByLpb[$ar['id_lpb_salah']][] = $ar;
                }
            }

            // Kelompokkan berdasarkan id_lpb agar di view bisa dirender dengan baris Total --> per LPB
            $grouped = [];
            foreach ($rows as $r) {
                $idLpb = $r['id_lpb'];
                if (!isset($grouped[$idLpb])) {
                    $grouped[$idLpb] = [
                        'id_lpb'       => $idLpb,
                        'tanggal_lpb'  => $r['tanggal_lpb'],
                        'nomor_lpb'    => $r['nomor_lpb'],
                        'tanggal_po'   => $r['tanggal_po'],
                        'no_po'        => $r['no_po'],
                        'nosj'         => $r['nosj'],
                        'kd_suplier'   => $r['kd_suplier'],
                        'nama_suplier' => $r['nama_suplier'],
                        'nama_gudang'  => $r['nama_gudang'],
                        'items'        => [],
                        'adjustments'  => $adjByLpb[$idLpb] ?? [],
                    ];
                }
                $grouped[$idLpb]['items'][] = [
                    'kd_barang'    => $r['kd_barang'],
                    'nama_barang'  => $r['nama_barang'],
                    'no_lot'       => $r['no_lot'],
                    'expired_date' => $r['expired_date'],
                    'qty'          => (float) $r['qty'],
                    'harga_satuan' => (float) $r['harga_satuan'],
                ];
            }

            return array_values($grouped);
        }

        // 3. Pembelian - Rincian
        if ($type === 'rincian') {
            $this->db->select("
                l.id_lpb,
                DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) AS tanggal,
                l.nomor_lpb AS no_referensi,
                COALESCE(NULLIF(TRIM(l.kd_suplier), ''), NULLIF(TRIM(p.kd_suplier), ''), '-') AS kd_suplier,
                COALESCE(NULLIF(TRIM(l.nama_suplier), ''), NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_suplier,
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), 'Head Quarter') AS nama_gudang,
                IF(l.status_lpb = 1, 'Posting', 'Draft') AS status_dokumen,
                COALESCE(NULLIF(TRIM(l.keterangan), ''), '') AS catatan,
                COALESCE(NULLIF(TRIM(p.kd_user), ''), '-') AS bag_pembelian,
                COALESCE(NULLIF(TRIM(l.checker_name), ''), '-') AS input_oleh,
                l.kd_po
            ", false);
            $this->db->from('tb_lpb l');
            $this->db->join('tbpo_po p', 'p.kd_po = l.kd_po', 'left');
            $this->db->join('tbpo_suplier s', "s.kd_suplier = COALESCE(NULLIF(l.kd_suplier, ''), p.kd_suplier)", 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = l.gudang_id', 'left');

            if ($start_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.nomor_lpb', $search);
                $this->db->or_like('l.no_invoice', $search);
                $this->db->or_like('l.no_po', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('l.nama_suplier', $search);
                $this->db->or_like('l.keterangan', $search);
                $this->db->group_end();
            }

            $this->db->order_by('tanggal', 'ASC');
            $this->db->order_by('l.id_lpb', 'ASC');
            $headers = $this->db->get()->result_array();

            if (empty($headers)) {
                return [];
            }

            $lpbIds = array_column($headers, 'id_lpb');
            $detailRows = $this->db
                ->select("
                    ld.id_lpb,
                    ld.kd_barang,
                    COALESCE(NULLIF(TRIM(b.nama_barang), ''), ld.kd_barang) AS nama_barang,
                    COALESCE(ld.qty_diterima, 0) AS diterima,
                    COALESCE(MAX(dp.qty), ld.qty_diterima, 0) AS dipesan,
                    COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS satuan,
                    COALESCE(ld.harga_satuan, 0) AS harga_satuan,
                    COALESCE(ld.sales_disc, 0) AS diskon,
                    COALESCE(NULLIF(ld.dpp, 0), ld.total_harga, 0) AS subtotal,
                    (COALESCE(ld.ppn_11, 0) + COALESCE(ld.ppn_12, 0)) AS nilai_ppn
                ", false)
                ->from('tb_lpb_detail ld')
                ->join('tb_lpb l2', 'l2.id_lpb = ld.id_lpb', 'inner')
                ->join('tbpo_barang b', 'b.kode_barang = ld.kd_barang', 'left')
                ->join('tbpo_detail_po dp', 'dp.kd_po = l2.kd_po AND dp.kd_barang = ld.kd_barang', 'left')
                ->where_in('ld.id_lpb', $lpbIds)
                ->group_by('ld.id_detail_lpb')
                ->order_by('ld.id_lpb', 'ASC')
                ->order_by('ld.id_detail_lpb', 'ASC')
                ->get()
                ->result_array();

            $detailsByLpb = [];
            foreach ($detailRows as $dr) {
                $sub = (float) $dr['subtotal'];
                $ppn = (float) $dr['nilai_ppn'];
                $dr['pajak_persen'] = ($sub > 0 && $ppn > 0) ? round(($ppn / $sub) * 100, 1) : 0;
                $detailsByLpb[$dr['id_lpb']][] = $dr;
            }

            foreach ($headers as &$hdr) {
                $items        = $detailsByLpb[$hdr['id_lpb']] ?? [];
                $totalDpp     = 0.0;
                $totalPpn     = 0.0;
                $totalDiskon  = 0.0;
                foreach ($items as $it) {
                    $totalDpp    += (float) $it['subtotal'];
                    $totalPpn    += (float) $it['nilai_ppn'];
                    $totalDiskon += (float) $it['diskon'];
                }
                $grandTotal          = $totalDpp + $totalPpn;
                $hdr['items']        = $items;
                $hdr['biaya_kirim']  = 0.0;
                $hdr['diskon_akhir'] = $totalDiskon;
                $hdr['total']        = $grandTotal;
                $hdr['pembayaran']   = 0.0;
                $hdr['saldo']        = $grandTotal;
            }
            unset($hdr);

            return $headers;
        }

        // 4. Pembelian - eFaktur
        if ($type === 'efaktur') {
            $this->db->select("
                l.id_lpb,
                COALESCE(fp.tgl_fp, l.tanggal_faktur_pajak, l.tanggal_invoice, l.tgl_sj, DATE(l.input_at)) AS tanggal,
                l.nomor_lpb AS no_ref,
                COALESCE(NULLIF(TRIM(l.nama_suplier), ''), NULLIF(TRIM(s.nama_suplier), ''), '-') AS suplier,
                COALESCE(NULLIF(TRIM(fp.no_seri_fp), ''), NULLIF(TRIM(l.kode_faktur_pajak), ''), '') AS nomor_seri_faktur,
                '-' AS npwp,
                COALESCE(SUM(COALESCE(NULLIF(ld.dpp, 0), ld.total_harga, 0)), 0) AS jumlah_dpp,
                COALESCE(SUM(COALESCE(ld.ppn_11, 0) + COALESCE(ld.ppn_12, 0)), 0) AS jumlah_ppn,
                IF(COALESCE(fp.lapor_spt_masa, '') <> '' OR l.status_lpb = 1, 'Ya', 'Tidak') AS sudah
            ", false);
            $this->db->from('tb_lpb l');
            $this->db->join('tblpb_faktur_pajak fp', 'fp.id_lpb = l.id_lpb', 'left');
            $this->db->join('tb_lpb_detail ld', 'ld.id_lpb = l.id_lpb', 'left');
            $this->db->join('tbpo_po p', 'p.kd_po = l.kd_po', 'left');
            $this->db->join('tbpo_suplier s', "s.kd_suplier = COALESCE(NULLIF(l.kd_suplier, ''), p.kd_suplier)", 'left');
            $this->db->where("COALESCE(NULLIF(TRIM(fp.no_seri_fp), ''), NULLIF(TRIM(l.kode_faktur_pajak), '')) IS NOT NULL", null, false);

            if ($start_date !== '') {
                $this->db->where('COALESCE(fp.tgl_fp, l.tanggal_faktur_pajak, l.tanggal_invoice, l.tgl_sj, DATE(l.input_at)) >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('COALESCE(fp.tgl_fp, l.tanggal_faktur_pajak, l.tanggal_invoice, l.tgl_sj, DATE(l.input_at)) <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.nomor_lpb', $search);
                $this->db->or_like('fp.no_seri_fp', $search);
                $this->db->or_like('l.kode_faktur_pajak', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('l.nama_suplier', $search);
                $this->db->group_end();
            }

            $this->db->group_by('l.id_lpb');
            $this->db->order_by('tanggal', 'ASC');
            $this->db->order_by('l.id_lpb', 'ASC');
            return $this->db->get()->result_array();
        }

        // 5. Pesanan Pembelian - Rincian
        if ($type === 'pesanan-rincian') {
            $this->db->select("
                p.id_po,
                p.kd_po,
                p.no_po AS no_referensi,
                p.tgl_transaksi AS tanggal,
                p.kd_suplier,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), p.kd_suplier, '-') AS nama_suplier,
                COALESCE(NULLIF(TRIM(p.gdg_pengiriman), ''), 'Head Quarter') AS nama_gudang,
                COALESCE(NULLIF(TRIM(p.status), ''), 'Open') AS status_dokumen,
                COALESCE(p.total_harga_diskon, p.total_harga, 0) AS total_po,
                COALESCE(p.tax, 0) AS tax_persen,
                COALESCE(p.hrg_pajak, 0) AS hrg_pajak
            ", false);
            $this->db->from('tbpo_po p');
            $this->db->join('tbpo_suplier s', 's.kd_suplier = p.kd_suplier', 'left');

            if ($start_date !== '') {
                $this->db->where('p.tgl_transaksi >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('p.tgl_transaksi <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.no_po', $search);
                $this->db->or_like('p.kd_po', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('p.kd_suplier', $search);
                $this->db->group_end();
            }

            $this->db->order_by('p.tgl_transaksi', 'ASC');
            $this->db->order_by('p.id_po', 'ASC');
            $headers = $this->db->get()->result_array();

            if (empty($headers)) {
                return [];
            }

            $kdPos = array_column($headers, 'kd_po');
            $detailRows = $this->db
                ->select("
                    dp.kd_po,
                    dp.kd_barang,
                    COALESCE(NULLIF(TRIM(dp.nama_barang), ''), NULLIF(TRIM(b.nama_barang), ''), dp.kd_barang) AS nama_barang,
                    COALESCE(dp.qty, 0) AS dipesan,
                    COALESCE((
                        SELECT SUM(ld.qty_diterima)
                        FROM tb_lpb_detail ld
                        INNER JOIN tb_lpb l ON l.id_lpb = ld.id_lpb
                        WHERE l.kd_po = dp.kd_po AND ld.kd_barang = dp.kd_barang
                    ), 0) AS diterima,
                    COALESCE(NULLIF(TRIM(dp.satuan), ''), NULLIF(TRIM(b.satuan), ''), 'Pcs') AS satuan,
                    COALESCE(dp.harga_satuan_exclude, dp.hrg_satuan, 0) AS harga_satuan,
                    COALESCE(dp.nominal_diskon, 0) AS diskon_persen,
                    COALESCE(dp.total_harga_setelah_diskon, dp.hrg_total_diskon, dp.hrg_total, 0) AS subtotal,
                    dp.keterangan_harga_ppn,
                    dp.kd_user
                ", false)
                ->from('tbpo_detail_po dp')
                ->join('tbpo_barang b', 'b.kode_barang = dp.kd_barang', 'left')
                ->where_in('dp.kd_po', $kdPos)
                ->order_by('dp.id_det_po', 'ASC')
                ->get()
                ->result_array();

            $detailsByPo = [];
            $userByPo    = [];
            foreach ($detailRows as $dr) {
                $detailsByPo[$dr['kd_po']][] = $dr;
                if (!isset($userByPo[$dr['kd_po']]) && !empty($dr['kd_user'])) {
                    $userByPo[$dr['kd_po']] = $dr['kd_user'];
                }
            }

            foreach ($headers as &$hdr) {
                $items = $detailsByPo[$hdr['kd_po']] ?? [];
                $taxP  = (float) $hdr['tax_persen'];
                $sumSub = 0.0;
                foreach ($items as &$it) {
                    $sumSub += (float) $it['subtotal'];
                    $ketPpn = strtoupper(trim((string) ($it['keterangan_harga_ppn'] ?? '')));
                    $it['pajak_persen'] = ($ketPpn !== 'NON PPN' && $ketPpn !== 'NON-PPN' && $taxP > 0) ? $taxP : ($ketPpn === 'PPN' ? 11 : 0);
                }
                unset($it);

                $hdr['catatan']       = 'Pembelian, ' . $hdr['nama_suplier'];
                $hdr['bag_pembelian'] = $userByPo[$hdr['kd_po']] ?? '-';
                $hdr['input_oleh']    = $userByPo[$hdr['kd_po']] ?? '-';
                $hdr['biaya_kirim']   = 0.0;
                $hdr['diskon_akhir']  = 0.0;
                $hdr['total']         = ((float) $hdr['total_po'] > 0) ? (float) $hdr['total_po'] : $sumSub;
                $hdr['items']         = $items;
            }
            unset($hdr);

            return $headers;
        }

        // 6. Pembelian - Sederhana
        if ($type === 'sederhana') {
            $this->db->select("
                DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) AS tanggal,
                l.nomor_lpb AS no_ref,
                COALESCE(NULLIF(TRIM(l.no_po), ''), NULLIF(TRIM(p.no_po), ''), '') AS no_pesanan,
                CONCAT(
                    COALESCE(NULLIF(TRIM(l.nama_suplier), ''), NULLIF(TRIM(s.nama_suplier), ''), '-'),
                    ' (',
                    COALESCE(NULLIF(TRIM(l.kd_suplier), ''), NULLIF(TRIM(p.kd_suplier), ''), '-'),
                    ')'
                ) AS nama_pemasok,
                'IDR' AS mata_uang,
                COALESCE(SUM(COALESCE(NULLIF(ld.dpp, 0), ld.total_harga, 0) + COALESCE(ld.ppn_11, 0) + COALESCE(ld.ppn_12, 0)), 0) AS jumlah
            ", false);
            $this->db->from('tb_lpb l');
            $this->db->join('tb_lpb_detail ld', 'ld.id_lpb = l.id_lpb', 'left');
            $this->db->join('tbpo_po p', 'p.kd_po = l.kd_po', 'left');
            $this->db->join('tbpo_suplier s', "s.kd_suplier = COALESCE(NULLIF(l.kd_suplier, ''), p.kd_suplier)", 'left');

            if ($start_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.nomor_lpb', $search);
                $this->db->or_like('l.no_po', $search);
                $this->db->or_like('p.no_po', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('l.nama_suplier', $search);
                $this->db->group_end();
            }

            $this->db->group_by('l.id_lpb');
            $this->db->order_by('tanggal', 'ASC');
            $this->db->order_by('l.id_lpb', 'ASC');
            return $this->db->get()->result_array();
        }

        // 7. Pembelian Bernilai Negatif - Rangkuman & 8. Pengiriman Pembelian - Rangkuman
        if ($type === 'negatif-rangkuman' || $type === 'pengiriman-rangkuman') {
            $this->db->select("
                DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) AS tanggal,
                l.nomor_lpb AS no_referensi,
                COALESCE(NULLIF(TRIM(l.no_po), ''), NULLIF(TRIM(p.no_po), ''), '') AS no_pesanan,
                COALESCE(NULLIF(TRIM(l.nama_suplier), ''), NULLIF(TRIM(s.nama_suplier), ''), '-') AS pemasok,
                'IDR' AS mata_uang,
                COALESCE(SUM(COALESCE(NULLIF(ld.dpp, 0), ld.total_harga, 0)), 0) AS sub_total,
                COALESCE(SUM(COALESCE(ld.sales_disc, 0)), 0) AS diskon,
                COALESCE(SUM(COALESCE(ld.ppn_11, 0) + COALESCE(ld.ppn_12, 0)), 0) AS pajak,
                COALESCE(SUM(COALESCE(NULLIF(ld.dpp, 0), ld.total_harga, 0) + COALESCE(ld.ppn_11, 0) + COALESCE(ld.ppn_12, 0)), 0) AS total_pembelian,
                0 AS pembayaran,
                COALESCE(SUM(COALESCE(NULLIF(ld.dpp, 0), ld.total_harga, 0) + COALESCE(ld.ppn_11, 0) + COALESCE(ld.ppn_12, 0)), 0) AS saldo
            ", false);
            $this->db->from('tb_lpb l');
            $this->db->join('tb_lpb_detail ld', 'ld.id_lpb = l.id_lpb', 'left');
            $this->db->join('tbpo_po p', 'p.kd_po = l.kd_po', 'left');
            $this->db->join('tbpo_suplier s', "s.kd_suplier = COALESCE(NULLIF(l.kd_suplier, ''), p.kd_suplier)", 'left');

            if ($type === 'pengiriman-rangkuman') {
                // Pengiriman Pembelian = penerimaan barang yang belum difakturkan (draft/belum ada nomor invoice)
                $this->db->where("COALESCE(NULLIF(TRIM(l.no_invoice), ''), '-') =", '-');
            }

            if ($start_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.nomor_lpb', $search);
                $this->db->or_like('l.no_po', $search);
                $this->db->or_like('p.no_po', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('l.nama_suplier', $search);
                $this->db->group_end();
            }

            $this->db->group_by('l.id_lpb');
            if ($type === 'negatif-rangkuman') {
                $this->db->having('total_pembelian <', 0);
            }
            $this->db->order_by('tanggal', 'ASC');
            $this->db->order_by('l.id_lpb', 'ASC');
            return $this->db->get()->result_array();
        }

        // 9. Purchase Return & 10. Retur Pembelian - Rincian
        if ($type === 'purchase-return' || $type === 'retur-rincian') {
            $this->db->select("
                r.id_retur_pembelian,
                r.tanggal_retur AS tanggal,
                r.no_retur_pembelian AS no_faktur,
                COALESCE(NULLIF(TRIM(r.no_po), ''), '') AS no_po,
                r.kd_supplier AS kd_suplier,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), r.kd_supplier, '-') AS nama_suplier,
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), 'Head Quarter') AS nama_gudang,
                COALESCE(NULLIF(TRIM(r.status), ''), 'POSTED') AS status_dokumen,
                COALESCE(NULLIF(TRIM(r.alasan_retur), ''), '') AS alasan_retur,
                COALESCE(NULLIF(TRIM(r.purchasing_verified_by), ''), NULLIF(TRIM(r.created_by), ''), '-') AS bag_pembelian,
                COALESCE(NULLIF(TRIM(r.created_by), ''), '-') AS input_oleh,
                COALESCE(r.total_dpp, 0) AS total_dpp,
                COALESCE(r.total_ppn, 0) AS total_ppn,
                COALESCE(r.grand_total, 0) AS grand_total
            ", false);
            $this->db->from('tb_retur_pembelian r');
            $this->db->join('tbpo_suplier s', 's.kd_suplier = r.kd_supplier', 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = r.gudang_id', 'left');
            $this->db->where("COALESCE(r.status, '') NOT IN ('CANCELLED', 'VOID', 'REJECTED')", null, false);

            if ($start_date !== '') {
                $this->db->where('r.tanggal_retur >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('r.tanggal_retur <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('r.no_retur_pembelian', $search);
                $this->db->or_like('r.no_po', $search);
                $this->db->or_like('r.kd_supplier', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->group_end();
            }

            $this->db->order_by('r.tanggal_retur', 'ASC');
            $this->db->order_by('r.id_retur_pembelian', 'ASC');
            $headers = $this->db->get()->result_array();

            if (empty($headers)) {
                return [];
            }

            $retIds = array_column($headers, 'id_retur_pembelian');
            $detailRows = $this->db
                ->select("
                    rd.id_retur_pembelian,
                    rd.kd_barang,
                    COALESCE(NULLIF(TRIM(b.nama_barang), ''), rd.kd_barang) AS nama_barang,
                    COALESCE(rd.qty_retur, 0) AS qty,
                    0 AS on_order,
                    COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                    COALESCE(rd.harga_satuan, 0) AS harga,
                    COALESCE(rd.dpp, 0) AS dpp,
                    COALESCE(rd.ppn, 0) AS ppn,
                    COALESCE(rd.total, 0) AS total
                ", false)
                ->from('tb_retur_pembelian_detail rd')
                ->join('tbpo_barang b', 'b.kode_barang = rd.kd_barang', 'left')
                ->where_in('rd.id_retur_pembelian', $retIds)
                ->order_by('rd.id_detail_retur_pembelian', 'ASC')
                ->get()
                ->result_array();

            $detailsByRet = [];
            foreach ($detailRows as $dr) {
                $dpp = (float) $dr['dpp'];
                $ppn = (float) $dr['ppn'];
                $dr['vat_persen'] = ($dpp > 0 && $ppn > 0) ? round(($ppn / $dpp) * 100, 0) : 0;
                $detailsByRet[$dr['id_retur_pembelian']][] = $dr;
            }

            foreach ($headers as &$hdr) {
                $items = $detailsByRet[$hdr['id_retur_pembelian']] ?? [];
                $sumTot = 0.0;
                foreach ($items as $it) {
                    $sumTot += (float) $it['total'];
                }
                $gt = ((float) $hdr['grand_total'] > 0) ? (float) $hdr['grand_total'] : $sumTot;
                $hdr['catatan']      = 'Retur Pembelian, ' . $hdr['nama_suplier'];
                $hdr['biaya_kirim']  = 0.0;
                $hdr['diskon_akhir'] = 0.0;
                $hdr['total']        = $gt;
                $hdr['pembayaran']   = $gt;
                $hdr['saldo']        = 0.0;
                $hdr['items']        = $items;
            }
            unset($hdr);

            return $headers;
        }

        // 11. Retur Pembelian - Sederhana
        if ($type === 'retur-sederhana') {
            $this->db->select("
                r.tanggal_retur AS tanggal,
                r.no_retur_pembelian AS no_pesanan,
                COALESCE(NULLIF(TRIM(r.no_po), ''), '') AS no_permintaan,
                CONCAT(
                    COALESCE(NULLIF(TRIM(s.nama_suplier), ''), r.kd_supplier, '-'),
                    ' (',
                    COALESCE(r.kd_supplier, '-'),
                    ')'
                ) AS nama_pemasok,
                'IDR' AS mata_uang,
                COALESCE(r.grand_total, 0) AS jumlah
            ", false);
            $this->db->from('tb_retur_pembelian r');
            $this->db->join('tbpo_suplier s', 's.kd_suplier = r.kd_supplier', 'left');
            $this->db->where("COALESCE(r.status, '') NOT IN ('CANCELLED', 'VOID', 'REJECTED')", null, false);

            if ($start_date !== '') {
                $this->db->where('r.tanggal_retur >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('r.tanggal_retur <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('r.no_retur_pembelian', $search);
                $this->db->or_like('r.no_po', $search);
                $this->db->or_like('r.kd_supplier', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->group_end();
            }

            $this->db->order_by('r.tanggal_retur', 'ASC');
            $this->db->order_by('r.id_retur_pembelian', 'ASC');
            return $this->db->get()->result_array();
        }

        // 12. Pesanan Pembelian - Sederhana
        if ($type === 'pesanan-sederhana') {
            $this->db->select("
                p.tgl_transaksi AS tanggal,
                p.no_po AS no_pesanan,
                '' AS no_permintaan,
                CONCAT(
                    COALESCE(NULLIF(TRIM(s.nama_suplier), ''), p.kd_suplier, '-'),
                    ' (',
                    COALESCE(p.kd_suplier, '-'),
                    ')'
                ) AS nama_pemasok,
                'IDR' AS mata_uang,
                COALESCE(NULLIF(p.total_harga_diskon, 0), p.total_harga, 0) AS jumlah,
                p.status
            ", false);
            $this->db->from('tbpo_po p');
            $this->db->join('tbpo_suplier s', 's.kd_suplier = p.kd_suplier', 'left');

            if ($start_date !== '') {
                $this->db->where('p.tgl_transaksi >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('p.tgl_transaksi <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.no_po', $search);
                $this->db->or_like('p.kd_po', $search);
                $this->db->or_like('p.kd_suplier', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->group_end();
            }

            $this->db->order_by('p.tgl_transaksi', 'ASC');
            $this->db->order_by('p.id_po', 'ASC');
            return $this->db->get()->result_array();
        }

        // 13. Pesanan Pembelian - Status Pengiriman
        if ($type === 'pesanan-status-pengiriman') {
            $this->db->select("
                p.id_po,
                p.kd_po,
                p.tgl_transaksi AS tanggal,
                p.no_po AS no_pesanan,
                CONCAT(
                    COALESCE(NULLIF(TRIM(s.nama_suplier), ''), p.kd_suplier, '-'),
                    ' (',
                    COALESCE(p.kd_suplier, '-'),
                    ')'
                ) AS pemasok,
                COALESCE(NULLIF(TRIM(p.status), ''), 'Open') AS status_po,
                COALESCE(NULLIF(p.total_harga_diskon, 0), p.total_harga, 0) AS total
            ", false);
            $this->db->from('tbpo_po p');
            $this->db->join('tbpo_suplier s', 's.kd_suplier = p.kd_suplier', 'left');

            if ($start_date !== '') {
                $this->db->where('p.tgl_transaksi >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('p.tgl_transaksi <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.no_po', $search);
                $this->db->or_like('p.kd_po', $search);
                $this->db->or_like('p.kd_suplier', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->group_end();
            }

            $this->db->order_by('p.tgl_transaksi', 'ASC');
            $this->db->order_by('p.id_po', 'ASC');
            $headers = $this->db->get()->result_array();

            if (empty($headers)) {
                return [];
            }

            $kdPos = array_column($headers, 'kd_po');
            $detailRows = $this->db
                ->select("
                    dp.kd_po,
                    dp.kd_barang,
                    COALESCE(NULLIF(TRIM(dp.nama_barang), ''), NULLIF(TRIM(b.nama_barang), ''), dp.kd_barang) AS nama_barang,
                    COALESCE(NULLIF(TRIM(dp.satuan), ''), NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                    COALESCE(dp.qty, 0) AS pesanan,
                    COALESCE((
                        SELECT SUM(ld.qty_diterima)
                        FROM tb_lpb_detail ld
                        INNER JOIN tb_lpb l ON l.id_lpb = ld.id_lpb
                        WHERE l.kd_po = dp.kd_po AND ld.kd_barang = dp.kd_barang
                    ), 0) AS telah_tiba,
                    dp.kd_user
                ", false)
                ->from('tbpo_detail_po dp')
                ->join('tbpo_barang b', 'b.kode_barang = dp.kd_barang', 'left')
                ->where_in('dp.kd_po', $kdPos)
                ->order_by('dp.id_det_po', 'ASC')
                ->get()
                ->result_array();

            $detailsByPo = [];
            $userByPo    = [];
            foreach ($detailRows as $dr) {
                $pesanan = (float) $dr['pesanan'];
                $tiba    = (float) $dr['telah_tiba'];
                $dr['sisa_pesanan'] = max(0, $pesanan - $tiba);
                $detailsByPo[$dr['kd_po']][] = $dr;
                if (!isset($userByPo[$dr['kd_po']]) && !empty($dr['kd_user'])) {
                    $userByPo[$dr['kd_po']] = $dr['kd_user'];
                }
            }

            foreach ($headers as &$hdr) {
                $hdr['bag_pembelian'] = $userByPo[$hdr['kd_po']] ?? '-';
                $st = strtoupper(trim((string) $hdr['status_po']));
                $hdr['status_code'] = (strpos($st, 'CLOSE') !== false || $st === 'C' || $st === 'SELESAI') ? 'C' : '';
                $hdr['items'] = $detailsByPo[$hdr['kd_po']] ?? [];
            }
            unset($hdr);

            return $headers;
        }

        return [];
    }

    /**
     * Data Laporan Hutang Usaha format Zahir Accounting.
     * Mendukung: rincian-mata-uang-dasar, kartu-hutang-mata-uang-dasar,
     * mutasi-hutang-mata-uang-dasar, pembayaran-hutang-mata-uang-dasar, rangkuman-hutang-mata-uang-dasar.
     */
    public function accounting_payable_zahir_report($type, $start_date, $end_date, $search = '')
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        $type       = trim((string) $type);
        $start_date = trim((string) $start_date);
        $end_date   = trim((string) $end_date);
        $search     = trim((string) $search);
        $asOf       = ($end_date !== '') ? $end_date : date('Y-m-d');

        // 1. Rincian Hutang & Rangkuman Hutang (Aging Hutang per Faktur / per Pemasok)
        if ($type === 'rincian-mata-uang-dasar' || $type === 'rangkuman-hutang-mata-uang-dasar') {
            $this->db->select("
                COALESCE(d.id_supplier, 0) AS id_supplier,
                COALESCE(NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_suplier,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), 'Pemasok Umum') AS nama_suplier,
                COALESCE(NULLIF(TRIM(d.nomor_dokumen), ''), NULLIF(TRIM(j.source_no), ''), j.nomor_jurnal) AS no_referensi,
                MIN(j.tanggal_transaksi) AS tanggal,
                COALESCE(MAX(d.tanggal_jatuh_tempo), MIN(j.tanggal_transaksi)) AS jatuh_tempo,
                MAX(COALESCE(jj.kode_jenis_jurnal, 'PJ')) AS tp,
                SUM(d.kredit - d.debit) AS saldo
            ", false);
            $this->db->from('tbkeu_jurnal_detail d');
            $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
            $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
            $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
            $this->db->join('tbpo_suplier s', 's.id_suplier = d.id_supplier', 'left');
            $this->db->where('a.kode_akun', '21098');
            $this->db->where('j.status', 'POSTED');
            $this->db->where('j.tanggal_transaksi <=', $asOf);

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('s.nama_suplier', $search);
                $this->db->or_like('s.kd_suplier', $search);
                $this->db->or_like('d.nomor_dokumen', $search);
                $this->db->or_like('j.source_no', $search);
                $this->db->group_end();
            }

            $this->db->group_by([
                'COALESCE(d.id_supplier, 0)',
                "COALESCE(NULLIF(TRIM(s.kd_suplier), ''), '-')",
                "COALESCE(NULLIF(TRIM(s.nama_suplier), ''), 'Pemasok Umum')",
                "COALESCE(NULLIF(TRIM(d.nomor_dokumen), ''), NULLIF(TRIM(j.source_no), ''), j.nomor_jurnal)",
            ]);
            $this->db->having('ABS(SUM(d.kredit - d.debit)) >', 0.0001);
            $this->db->order_by('nama_suplier', 'ASC');
            $this->db->order_by('tanggal', 'ASC');
            $rows = $this->db->get()->result_array();

            $asOfTs = strtotime($asOf);
            $bySupplier = [];

            foreach ($rows as $r) {
                $saldo = (float) $r['saldo'];
                $jtTs  = strtotime($r['jatuh_tempo'] ?: $r['tanggal']);
                $days  = (int) floor(($asOfTs - $jtTs) / 86400);

                $b0 = 0.0; $b30 = 0.0; $b60 = 0.0; $b90 = 0.0;
                if ($days <= 30) {
                    $b0 = $saldo;
                } elseif ($days <= 60) {
                    $b30 = $saldo;
                } elseif ($days <= 90) {
                    $b60 = $saldo;
                } else {
                    $b90 = $saldo;
                }

                $supKey = $r['id_supplier'] . '|' . $r['kd_suplier'];
                if (!isset($bySupplier[$supKey])) {
                    $bySupplier[$supKey] = [
                        'id_supplier'  => $r['id_supplier'],
                        'kd_suplier'   => $r['kd_suplier'],
                        'nama_suplier' => trim($r['nama_suplier']),
                        'b_0_30'       => 0.0,
                        'b_30_60'      => 0.0,
                        'b_60_90'      => 0.0,
                        'b_gt_90'      => 0.0,
                        'total_saldo'  => 0.0,
                        'invoices'     => [],
                    ];
                }

                $bySupplier[$supKey]['b_0_30']      += $b0;
                $bySupplier[$supKey]['b_30_60']     += $b30;
                $bySupplier[$supKey]['b_60_90']     += $b60;
                $bySupplier[$supKey]['b_gt_90']     += $b90;
                $bySupplier[$supKey]['total_saldo'] += $saldo;
                $bySupplier[$supKey]['invoices'][]   = [
                    'tanggal'      => $r['tanggal'],
                    'jatuh_tempo'  => $r['jatuh_tempo'],
                    'tp'           => $r['tp'],
                    'no_referensi' => $r['no_referensi'],
                    'mata_uang'    => 'IDR',
                    'b_0_30'       => $b0,
                    'b_30_60'      => $b30,
                    'b_60_90'      => $b60,
                    'b_gt_90'      => $b90,
                    'saldo'        => $saldo,
                ];
            }

            return array_values($bySupplier);
        }

        // 2. Kartu Hutang - Mata Uang Dasar
        if ($type === 'kartu-hutang-mata-uang-dasar') {
            $this->db->select("
                COALESCE(d.id_supplier, 0) AS id_supplier,
                COALESCE(NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_suplier,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), 'Pemasok Umum') AS nama_suplier,
                COALESCE(NULLIF(TRIM(d.nomor_dokumen), ''), NULLIF(TRIM(j.source_no), ''), j.nomor_jurnal) AS no_faktur,
                j.tanggal_transaksi AS tanggal,
                COALESCE(jj.kode_jenis_jurnal, 'PJ') AS tp,
                COALESCE(NULLIF(TRIM(j.source_no), ''), j.nomor_jurnal) AS no_referensi,
                COALESCE(d.tanggal_jatuh_tempo, j.tanggal_transaksi) AS jatuh_tempo,
                d.debit,
                d.kredit
            ", false);
            $this->db->from('tbkeu_jurnal_detail d');
            $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
            $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
            $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
            $this->db->join('tbpo_suplier s', 's.id_suplier = d.id_supplier', 'left');
            $this->db->where('a.kode_akun', '21098');
            $this->db->where('j.status', 'POSTED');
            if ($start_date !== '') {
                $this->db->where('j.tanggal_transaksi >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('j.tanggal_transaksi <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('s.nama_suplier', $search);
                $this->db->or_like('s.kd_suplier', $search);
                $this->db->or_like('d.nomor_dokumen', $search);
                $this->db->or_like('j.source_no', $search);
                $this->db->group_end();
            }
            $this->db->order_by('nama_suplier', 'ASC');
            $this->db->order_by('no_faktur', 'ASC');
            $this->db->order_by('j.tanggal_transaksi', 'ASC');
            $this->db->order_by('j.id_jurnal', 'ASC');
            $rows = $this->db->get()->result_array();

            $bySupplier = [];
            foreach ($rows as $r) {
                $supKey = $r['id_supplier'] . '|' . $r['kd_suplier'];
                $fakKey = $r['no_faktur'];
                if (!isset($bySupplier[$supKey])) {
                    $bySupplier[$supKey] = [
                        'id_supplier'  => $r['id_supplier'],
                        'kd_suplier'   => $r['kd_suplier'],
                        'nama_suplier' => trim($r['nama_suplier']),
                        'total_debit'  => 0.0,
                        'total_kredit' => 0.0,
                        'total_saldo'  => 0.0,
                        'invoices'     => [],
                    ];
                }
                if (!isset($bySupplier[$supKey]['invoices'][$fakKey])) {
                    $bySupplier[$supKey]['invoices'][$fakKey] = [
                        'no_faktur'    => $fakKey,
                        'total_debit'  => 0.0,
                        'total_kredit' => 0.0,
                        'saldo'        => 0.0,
                        'lines'        => [],
                    ];
                }
                $deb = (float) $r['debit'];
                $kre = (float) $r['kredit'];
                $bySupplier[$supKey]['invoices'][$fakKey]['lines'][] = [
                    'tanggal'      => $r['tanggal'],
                    'tp'           => $r['tp'],
                    'no_referensi' => $r['no_referensi'],
                    'jatuh_tempo'  => $r['jatuh_tempo'],
                    'mata_uang'    => 'IDR',
                    'debit'        => $deb,
                    'kredit'       => $kre,
                ];
                $bySupplier[$supKey]['invoices'][$fakKey]['total_debit']  += $deb;
                $bySupplier[$supKey]['invoices'][$fakKey]['total_kredit'] += $kre;
                $bySupplier[$supKey]['invoices'][$fakKey]['saldo']        += ($kre - $deb);
                $bySupplier[$supKey]['total_debit']  += $deb;
                $bySupplier[$supKey]['total_kredit'] += $kre;
                $bySupplier[$supKey]['total_saldo']  += ($kre - $deb);
            }

            foreach ($bySupplier as &$sup) {
                $sup['invoices'] = array_values($sup['invoices']);
            }
            unset($sup);

            return array_values($bySupplier);
        }

        // 3. Mutasi Hutang - Mata Uang Dasar
        if ($type === 'mutasi-hutang-mata-uang-dasar') {
            $sEsc = $this->db->escape($start_date !== '' ? $start_date : '1900-01-01');
            $eEsc = $this->db->escape($end_date !== '' ? $end_date : date('Y-m-d'));

            $this->db->select("
                COALESCE(d.id_supplier, 0) AS id_supplier,
                COALESCE(NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_suplier,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), 'Pemasok Umum') AS nama_suplier,
                SUM(CASE WHEN j.tanggal_transaksi < $sEsc THEN (d.kredit - d.debit) ELSE 0 END) AS saldo_awal,
                SUM(CASE WHEN j.tanggal_transaksi BETWEEN $sEsc AND $eEsc THEN d.debit ELSE 0 END) AS debit,
                SUM(CASE WHEN j.tanggal_transaksi BETWEEN $sEsc AND $eEsc THEN d.kredit ELSE 0 END) AS kredit
            ", false);
            $this->db->from('tbkeu_jurnal_detail d');
            $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
            $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
            $this->db->join('tbpo_suplier s', 's.id_suplier = d.id_supplier', 'left');
            $this->db->where('a.kode_akun', '21098');
            $this->db->where('j.status', 'POSTED');
            $this->db->where('j.tanggal_transaksi <=', ($end_date !== '' ? $end_date : date('Y-m-d')));

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('s.nama_suplier', $search);
                $this->db->or_like('s.kd_suplier', $search);
                $this->db->group_end();
            }

            $this->db->group_by([
                'COALESCE(d.id_supplier, 0)',
                "COALESCE(NULLIF(TRIM(s.kd_suplier), ''), '-')",
                "COALESCE(NULLIF(TRIM(s.nama_suplier), ''), 'Pemasok Umum')",
            ]);
            $this->db->order_by('nama_suplier', 'ASC');
            $rows = $this->db->get()->result_array();

            foreach ($rows as &$r) {
                $r['nama_suplier'] = trim($r['nama_suplier']);
                $r['saldo_awal']   = (float) $r['saldo_awal'];
                $r['debit']        = (float) $r['debit'];
                $r['kredit']       = (float) $r['kredit'];
                $r['saldo_akhir']  = $r['saldo_awal'] - $r['debit'] + $r['kredit'];
            }
            unset($r);

            return $rows;
        }

        // 4. Pembayaran Hutang - Mata Uang Dasar
        if ($type === 'pembayaran-hutang-mata-uang-dasar') {
            $this->db->select("
                p.id_pembayaran,
                p.tanggal_pembayaran AS tanggal,
                p.nomor_pembayaran AS no_referensi,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), 'Pemasok Umum') AS nama_pemasok,
                COALESCE(p.amount, 0) AS total_pembayaran,
                pa.invoice_no AS no_faktur,
                p.tanggal_pembayaran AS tanggal_faktur,
                COALESCE(pa.amount_allocated, p.amount, 0) AS nilai_alokasi
            ", false);
            $this->db->from('tbkeu_pembayaran p');
            $this->db->join('tbpo_suplier s', 's.id_suplier = p.id_supplier', 'left');
            $this->db->join('tbkeu_pembayaran_alokasi pa', 'pa.id_pembayaran = p.id_pembayaran', 'left');
            $this->db->where('p.payment_type', 'SUPPLIER_PAYMENT');
            $this->db->where('p.status', 'POSTED');

            if ($start_date !== '') {
                $this->db->where('p.tanggal_pembayaran >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('p.tanggal_pembayaran <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.nomor_pembayaran', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('pa.invoice_no', $search);
                $this->db->group_end();
            }

            $this->db->order_by('p.tanggal_pembayaran', 'ASC');
            $this->db->order_by('p.id_pembayaran', 'ASC');
            $this->db->order_by('pa.nomor_baris', 'ASC');
            $rows = $this->db->get()->result_array();

            $grouped = [];
            foreach ($rows as $r) {
                $idPay = $r['id_pembayaran'];
                if (!isset($grouped[$idPay])) {
                    $grouped[$idPay] = [
                        'id_pembayaran'    => $idPay,
                        'tanggal'          => $r['tanggal'],
                        'no_referensi'     => $r['no_referensi'],
                        'nama_pemasok'     => trim($r['nama_pemasok']),
                        'total_pembayaran' => (float) $r['total_pembayaran'],
                        'items'            => [],
                    ];
                }
                if (!empty($r['no_faktur'])) {
                    $grouped[$idPay]['items'][] = [
                        'no_faktur'      => $r['no_faktur'],
                        'tanggal_faktur' => $r['tanggal_faktur'],
                        'diskon'         => 0.0,
                        'pembayaran'     => (float) $r['nilai_alokasi'],
                    ];
                }
            }

            return array_values($grouped);
        }

        return [];
    }

    /**
     * Data Laporan Persediaan Barang format Zahir Accounting.
     */
    public function accounting_inventory_zahir_report($type, $start_date, $end_date, $search = '')
    {
        $type       = trim((string) $type);
        $start_date = trim((string) $start_date);
        $end_date   = trim((string) $end_date);
        $search     = trim((string) $search);

        // 1. Jurnal Persediaan
        if ($type === 'jurnal-persediaan') {
            if (!$this->accounting_journal_schema_ready()) {
                return [];
            }

            $this->db->select("
                j.id_jurnal,
                j.nomor_jurnal,
                j.tanggal_transaksi,
                j.keterangan,
                j.source_no
            ", false);
            $this->db->from('tbkeu_jurnal j');
            $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
            $this->db->where('j.status', 'POSTED');
            $this->db->group_start();
            $this->db->where_in('jj.kode_jenis_jurnal', ['IJ', 'MR']);
            $this->db->or_where("EXISTS (SELECT 1 FROM tbkeu_jurnal_detail jd2 JOIN tbkeu_akun a2 ON a2.id_akun = jd2.id_akun WHERE jd2.id_jurnal = j.id_jurnal AND a2.kode_akun LIKE '140%')", null, false);
            $this->db->group_end();

            if ($start_date !== '') {
                $this->db->where('j.tanggal_transaksi >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('j.tanggal_transaksi <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('j.nomor_jurnal', $search);
                $this->db->or_like('j.source_no', $search);
                $this->db->or_like('j.keterangan', $search);
                $this->db->group_end();
            }

            $this->db->order_by('j.tanggal_transaksi', 'ASC');
            $this->db->order_by('j.id_jurnal', 'ASC');
            $headers = $this->db->get()->result_array();

            if (empty($headers)) {
                return [];
            }

            $journalIds = array_column($headers, 'id_jurnal');
            $detailRows = $this->db
                ->select("
                    d.id_jurnal,
                    a.kode_akun,
                    a.nama_akun,
                    d.debit,
                    d.kredit,
                    COALESCE(NULLIF(TRIM(d.nomor_dokumen), ''), '') AS nomor_dokumen,
                    '-' AS departemen,
                    '-' AS proyek
                ", false)
                ->from('tbkeu_jurnal_detail d')
                ->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left')
                ->where_in('d.id_jurnal', $journalIds)
                ->order_by('d.id_jurnal', 'ASC')
                ->order_by('d.nomor_baris', 'ASC')
                ->get()
                ->result_array();

            $byJ = [];
            foreach ($detailRows as $dr) {
                $byJ[$dr['id_jurnal']][] = $dr;
            }
            foreach ($headers as &$hdr) {
                $hdr['details'] = $byJ[$hdr['id_jurnal']] ?? [];
            }
            unset($hdr);

            return $headers;
        }

        // 2. Barang Dibeli
        if ($type === 'barang-dibeli') {
            $this->db->select("
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                ld.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), ld.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                SUM(COALESCE(ld.qty_diterima, 0)) AS jumlah,
                SUM(COALESCE(NULLIF(ld.dpp, 0), ld.total_harga, 0)) AS nilai
            ", false);
            $this->db->from('tb_lpb_detail ld');
            $this->db->join('tb_lpb l', 'l.id_lpb = ld.id_lpb', 'inner');
            $this->db->join('tbpo_barang b', 'b.kode_barang = ld.kd_barang', 'left');

            if ($start_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('ld.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('b.kelompok_dagang', $search);
                $this->db->group_end();
            }

            $this->db->group_by(['kelompok', 'ld.kd_barang', 'nama_barang', 'unit']);
            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('ld.kd_barang', 'ASC');
            return $this->db->get()->result_array();
        }

        // 3. Barang Terjual
        if ($type === 'barang-terjual') {
            $this->db->select("
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                fd.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(fd.nama_barang), ''), NULLIF(TRIM(b.nama_barang), ''), fd.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(fd.satuan), ''), NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                SUM(COALESCE(fd.qty, 0)) AS jumlah,
                SUM(COALESCE(fd.total_harga, 0)) AS nilai
            ", false);
            $this->db->from('tbso_faktur_detail fd');
            $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = fd.id_faktur', 'inner');
            $this->db->join('tbpo_barang b', 'b.kode_barang = fd.kd_barang', 'left');
            $this->db->where('f.status <>', 'cancelled');
            $this->db->where('(f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)', null, false);

            if ($start_date !== '') {
                $this->db->where('f.tanggal_faktur >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('f.tanggal_faktur <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('fd.kd_barang', $search);
                $this->db->or_like('fd.nama_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->group_end();
            }

            $this->db->group_by(['kelompok', 'fd.kd_barang', 'nama_barang', 'unit']);
            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('fd.kd_barang', 'ASC');
            return $this->db->get()->result_array();
        }

        // 4. Daftar Barang - Umum & Daftar Harga Jual Barang
        if ($type === 'daftar-barang-umum' || $type === 'daftar-harga-jual') {
            $priceCol = ($type === 'daftar-harga-jual')
                ? "COALESCE((SELECT fd.harga_satuan FROM tbso_faktur_detail fd WHERE fd.kd_barang = b.kode_barang AND fd.harga_satuan > 0 ORDER BY fd.id_faktur_detail DESC LIMIT 1), 0)"
                : "COALESCE((SELECT ld.harga_satuan FROM tb_lpb_detail ld WHERE ld.kd_barang = b.kode_barang AND ld.harga_satuan > 0 ORDER BY ld.id_detail_lpb DESC LIMIT 1), 0)";

            $this->db->select("
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                b.kode_barang,
                b.nama_barang,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                COALESCE((SELECT SUM(sb.qty_on_hand) FROM tberp_stock_batch sb WHERE sb.kd_barang = b.kode_barang), 0) AS tersedia,
                COALESCE((SELECT SUM(sb.qty_reserved) FROM tberp_stock_batch sb WHERE sb.kd_barang = b.kode_barang), 0) AS dipesan,
                $priceCol AS harga_satuan
            ", false);
            $this->db->from('tbpo_barang b');
            $this->db->where('b.is_active', 'T');

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('b.kode_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('b.kelompok_dagang', $search);
                $this->db->group_end();
            }

            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('b.kode_barang', 'ASC');
            $this->db->limit(1500);
            $rows = $this->db->get()->result_array();

            foreach ($rows as &$r) {
                $r['total_nilai'] = (float) $r['tersedia'] * (float) $r['harga_satuan'];
            }
            unset($r);
            return $rows;
        }

        // 5. Analisa Barang Per Gudang, Daftar Barang Per Gudang, & Formulir Stok Opname
        if ($type === 'analisa-per-gudang' || $type === 'daftar-barang-per-gudang' || $type === 'formulir-stok-opname') {
            $this->db->select("
                COALESCE(g.id_gudang, 0) AS id_gudang,
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), CONCAT('Gudang ', sb.gudang_id)) AS nama_gudang,
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                sb.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), sb.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                SUM(COALESCE(sb.qty_on_hand, 0)) AS tersedia,
                SUM(COALESCE(sb.qty_reserved, 0)) AS pesanan_pelanggan,
                COALESCE((SELECT SUM(dp.qty) FROM tbpo_detail_po dp INNER JOIN tbpo_po p ON p.kd_po = dp.kd_po WHERE dp.kd_barang = sb.kd_barang AND COALESCE(p.status, '') NOT IN ('Closed','CLOSED','Cancelled','CANCELLED')), 0) AS dipesan_ke_pemasok,
                COALESCE((SELECT ld.harga_satuan FROM tb_lpb_detail ld WHERE ld.kd_barang = sb.kd_barang AND ld.harga_satuan > 0 ORDER BY ld.id_detail_lpb DESC LIMIT 1), 0) AS harga_satuan
            ", false);
            $this->db->from('tberp_stock_batch sb');
            $this->db->join('tb_gudang g', 'g.id_gudang = sb.gudang_id', 'left');
            $this->db->join('tbpo_barang b', 'b.kode_barang = sb.kd_barang', 'left');

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('sb.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('g.nama_gudang', $search);
                $this->db->group_end();
            }

            $this->db->group_by(['id_gudang', 'nama_gudang', 'kelompok', 'sb.kd_barang', 'nama_barang', 'unit']);
            $this->db->order_by('nama_gudang', 'ASC');
            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('sb.kd_barang', 'ASC');
            $rows = $this->db->get()->result_array();

            foreach ($rows as &$r) {
                $r['total_nilai'] = (float) $r['tersedia'] * (float) $r['harga_satuan'];
            }
            unset($r);
            return $rows;
        }

        // 6. Kartu Stok - Umum & Kartu Stok Per Pemasok Per Gudang
        if ($type === 'kartu-stok-umum' || $type === 'kartu-stok-per-pemasok-per-gudang') {
            $sDate = $start_date !== '' ? $start_date : date('Y-m-01');
            $eDate = $end_date !== '' ? $end_date : date('Y-m-d');

            $this->db->select("
                l.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), l.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                COALESCE(l.gudang_id, 0) AS gudang_id,
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), CONCAT('Gudang ', l.gudang_id)) AS nama_gudang,
                DATE(l.created_at) AS tanggal,
                l.tipe,
                COALESCE(NULLIF(TRIM(l.ref_no), ''), '-') AS no_referensi,
                COALESCE(NULLIF(TRIM(l.ref_type), ''), l.tipe) AS ref_type,
                l.qty,
                COALESCE((SELECT ld.harga_satuan FROM tb_lpb_detail ld WHERE ld.kd_barang = l.kd_barang AND ld.harga_satuan > 0 ORDER BY ld.id_detail_lpb DESC LIMIT 1), 0) AS harga_pokok
            ", false);
            $this->db->from('tberp_stock_ledger l');
            $this->db->join('tbpo_barang b', 'b.kode_barang = l.kd_barang', 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = l.gudang_id', 'left');
            $this->db->where('DATE(l.created_at) <=', $eDate);
            $this->db->where_not_in('l.tipe', ['RESERVE', 'RELEASE']);

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('l.ref_no', $search);
                $this->db->or_like('g.nama_gudang', $search);
                $this->db->group_end();
            }

            if ($type === 'kartu-stok-per-pemasok-per-gudang') {
                $this->db->order_by('nama_gudang', 'ASC');
            }
            $this->db->order_by('l.kd_barang', 'ASC');
            $this->db->order_by('l.created_at', 'ASC');
            $this->db->order_by('l.id', 'ASC');
            $rows = $this->db->get()->result_array();

            $inTypes  = ['SALDO_AWAL', 'IN', 'RJUAL', 'ADJIN'];
            $grouped  = [];

            foreach ($rows as $r) {
                $key = ($type === 'kartu-stok-per-pemasok-per-gudang')
                    ? ($r['nama_gudang'] . '||' . $r['kode_barang'])
                    : $r['kode_barang'];

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'nama_gudang'   => $r['nama_gudang'],
                        'kode_barang'   => $r['kode_barang'],
                        'nama_barang'   => $r['nama_barang'],
                        'kelompok'      => $r['kelompok'],
                        'unit'          => $r['unit'],
                        'harga_pokok'   => (float) $r['harga_pokok'],
                        'opening_qty'   => 0.0,
                        'total_masuk'   => 0.0,
                        'total_keluar'  => 0.0,
                        'saldo_qty'     => 0.0,
                        'lines'         => [],
                    ];
                }

                $qty   = abs((float) $r['qty']);
                $isIn  = in_array($r['tipe'], $inTypes, true) || ($r['tipe'] === 'MUTASI' && (float) $r['qty'] > 0);
                $delta = $isIn ? $qty : -$qty;

                if ($r['tanggal'] < $sDate) {
                    $grouped[$key]['opening_qty'] += $delta;
                    $grouped[$key]['saldo_qty']   += $delta;
                } else {
                    $grouped[$key]['saldo_qty'] += $delta;
                    if ($isIn) {
                        $grouped[$key]['total_masuk'] += $qty;
                    } else {
                        $grouped[$key]['total_keluar'] += $qty;
                    }
                    $grouped[$key]['lines'][] = [
                        'tanggal'      => $r['tanggal'],
                        'tp'           => $r['ref_type'],
                        'no_referensi' => $r['no_referensi'],
                        'masuk'        => $isIn ? $qty : 0.0,
                        'keluar'       => $isIn ? 0.0 : $qty,
                        'harga_pokok'  => (float) $r['harga_pokok'],
                        'nilai'        => $qty * (float) $r['harga_pokok'],
                        'saldo'        => $grouped[$key]['saldo_qty'],
                        'saldo_nilai'  => $grouped[$key]['saldo_qty'] * (float) $r['harga_pokok'],
                    ];
                }
            }

            return array_values($grouped);
        }

        // 7. Transaksi Barang - Semua Transaksi & Transaksi - Pemakaian / Penyesuaian Barang
        if ($type === 'transaksi-semua' || $type === 'transaksi-pemakaian-penyesuaian') {
            $sDate = $start_date !== '' ? $start_date : date('Y-m-01');
            $eDate = $end_date !== '' ? $end_date : date('Y-m-d');

            $this->db->select("
                DATE(l.created_at) AS tanggal,
                COALESCE(NULLIF(TRIM(l.ref_no), ''), CONCAT('TRX-', l.id)) AS no_referensi,
                COALESCE(NULLIF(TRIM(l.ref_type), ''), l.tipe) AS ref_type,
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), CONCAT('Gudang ', l.gudang_id)) AS nama_gudang,
                l.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), l.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                l.tipe,
                l.qty,
                COALESCE((SELECT ld.harga_satuan FROM tb_lpb_detail ld WHERE ld.kd_barang = l.kd_barang AND ld.harga_satuan > 0 ORDER BY ld.id_detail_lpb DESC LIMIT 1), 0) AS harga_satuan
            ", false);
            $this->db->from('tberp_stock_ledger l');
            $this->db->join('tbpo_barang b', 'b.kode_barang = l.kd_barang', 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = l.gudang_id', 'left');
            $this->db->where('DATE(l.created_at) >=', $sDate);
            $this->db->where('DATE(l.created_at) <=', $eDate);
            $this->db->where_not_in('l.tipe', ['RESERVE', 'RELEASE']);

            if ($type === 'transaksi-pemakaian-penyesuaian') {
                $this->db->where_in('l.tipe', ['SALDO_AWAL', 'ADJIN', 'ADJOUT', 'MUTASI']);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.ref_no', $search);
                $this->db->or_like('l.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('g.nama_gudang', $search);
                $this->db->group_end();
            }

            $this->db->order_by('tanggal', 'ASC');
            $this->db->order_by('l.id', 'ASC');
            $rows = $this->db->get()->result_array();

            $inTypes = ['SALDO_AWAL', 'IN', 'RJUAL', 'ADJIN'];
            $grouped = [];
            foreach ($rows as $r) {
                $key = $r['tanggal'] . '||' . $r['no_referensi'];
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'tanggal'      => $r['tanggal'],
                        'no_referensi' => $r['no_referensi'],
                        'dari_gudang'  => $r['nama_gudang'],
                        'ke_gudang'    => $r['nama_gudang'],
                        'catatan'      => $r['ref_type'] . ' (' . $r['tipe'] . ')',
                        'status'       => 'Posting',
                        'input_oleh'   => '-',
                        'total'        => 0.0,
                        'items'        => [],
                    ];
                }
                $qty   = abs((float) $r['qty']);
                $isIn  = in_array($r['tipe'], $inTypes, true) || ($r['tipe'] === 'MUTASI' && (float) $r['qty'] > 0);
                $signedQty = $isIn ? $qty : -$qty;
                $harga = (float) $r['harga_satuan'];
                $sub   = $signedQty * $harga;

                $grouped[$key]['total'] += $sub;
                $grouped[$key]['items'][] = [
                    'kode_barang'  => $r['kode_barang'],
                    'nama_barang'  => $r['nama_barang'],
                    'jumlah'       => $signedQty,
                    'unit'         => $r['unit'],
                    'harga_satuan' => $harga,
                    'sub_total'    => $sub,
                ];
            }

            return array_values($grouped);
        }

        // 8. Mutasi Barang Per Gudang & Mutasi Barang - Umum
        if ($type === 'mutasi-per-gudang' || $type === 'mutasi-umum') {
            $sDate = $start_date !== '' ? $start_date : date('Y-m-01');
            $eDate = $end_date !== '' ? $end_date : date('Y-m-d');

            $this->db->select("
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), CONCAT('Gudang ', l.gudang_id)) AS nama_gudang,
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                l.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), l.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                DATE(l.created_at) AS tanggal,
                l.tipe,
                l.qty,
                COALESCE((SELECT ld.harga_satuan FROM tb_lpb_detail ld WHERE ld.kd_barang = l.kd_barang AND ld.harga_satuan > 0 ORDER BY ld.id_detail_lpb DESC LIMIT 1), 0) AS harga_pokok
            ", false);
            $this->db->from('tberp_stock_ledger l');
            $this->db->join('tbpo_barang b', 'b.kode_barang = l.kd_barang', 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = l.gudang_id', 'left');
            $this->db->where('DATE(l.created_at) <=', $eDate);
            $this->db->where_not_in('l.tipe', ['RESERVE', 'RELEASE']);

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('g.nama_gudang', $search);
                $this->db->group_end();
            }

            if ($type === 'mutasi-per-gudang') {
                $this->db->order_by('nama_gudang', 'ASC');
            }
            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('l.kd_barang', 'ASC');
            $rows = $this->db->get()->result_array();

            $inTypes = ['SALDO_AWAL', 'IN', 'RJUAL', 'ADJIN'];
            $grouped = [];
            foreach ($rows as $r) {
                $key = ($type === 'mutasi-per-gudang')
                    ? ($r['nama_gudang'] . '||' . $r['kelompok'] . '||' . $r['kode_barang'])
                    : ($r['kelompok'] . '||' . $r['kode_barang']);

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'nama_gudang' => $r['nama_gudang'],
                        'kelompok'    => $r['kelompok'],
                        'kode_barang' => $r['kode_barang'],
                        'nama_barang' => $r['nama_barang'],
                        'unit'        => $r['unit'],
                        'harga_pokok' => (float) $r['harga_pokok'],
                        'stok_awal'   => 0.0,
                        'stok_masuk'  => 0.0,
                        'stok_keluar' => 0.0,
                    ];
                }

                $qty   = abs((float) $r['qty']);
                $isIn  = in_array($r['tipe'], $inTypes, true) || ($r['tipe'] === 'MUTASI' && (float) $r['qty'] > 0);
                $delta = $isIn ? $qty : -$qty;

                if ($r['tanggal'] < $sDate) {
                    $grouped[$key]['stok_awal'] += $delta;
                } else {
                    if ($isIn) {
                        $grouped[$key]['stok_masuk'] += $qty;
                    } else {
                        $grouped[$key]['stok_keluar'] += $qty;
                    }
                }
            }

            foreach ($grouped as &$g) {
                $g['stok_akhir']  = $g['stok_awal'] + $g['stok_masuk'] - $g['stok_keluar'];
                $g['saldo_awal']  = $g['stok_awal'] * $g['harga_pokok'];
                $g['nilai_masuk'] = $g['stok_masuk'] * $g['harga_pokok'];
                $g['nilai_keluar'] = $g['stok_keluar'] * $g['harga_pokok'];
                $g['saldo_akhir'] = $g['stok_akhir'] * $g['harga_pokok'];
            }
            unset($g);

            return array_values($grouped);
        }

        // 9. Nomor Lot Barang - Mutasi
        if ($type === 'lot-mutasi') {
            $sDate = $start_date !== '' ? $start_date : date('Y-m-01');
            $eDate = $end_date !== '' ? $end_date : date('Y-m-d');

            $this->db->select("
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), CONCAT('Gudang ', l.gudang_id)) AS nama_gudang,
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                l.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), l.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                COALESCE(NULLIF(TRIM(l.no_lot), ''), '0') AS no_lot,
                l.expired_date,
                DATE(l.created_at) AS tanggal,
                COALESCE(NULLIF(TRIM(l.ref_no), ''), '-') AS no_ref,
                l.tipe,
                l.qty,
                COALESCE(NULLIF(TRIM(l.ref_type), ''), l.tipe) AS catatan
            ", false);
            $this->db->from('tberp_stock_ledger l');
            $this->db->join('tbpo_barang b', 'b.kode_barang = l.kd_barang', 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = l.gudang_id', 'left');
            $this->db->where('DATE(l.created_at) >=', $sDate);
            $this->db->where('DATE(l.created_at) <=', $eDate);
            $this->db->where_not_in('l.tipe', ['RESERVE', 'RELEASE']);

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('l.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('l.no_lot', $search);
                $this->db->or_like('l.ref_no', $search);
                $this->db->or_like('g.nama_gudang', $search);
                $this->db->group_end();
            }

            $this->db->order_by('nama_gudang', 'ASC');
            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('l.kd_barang', 'ASC');
            $this->db->order_by('no_lot', 'ASC');
            $this->db->order_by('l.created_at', 'ASC');
            $rows = $this->db->get()->result_array();

            $inTypes = ['SALDO_AWAL', 'IN', 'RJUAL', 'ADJIN'];
            $running = [];
            foreach ($rows as &$r) {
                $qty  = abs((float) $r['qty']);
                $isIn = in_array($r['tipe'], $inTypes, true) || ($r['tipe'] === 'MUTASI' && (float) $r['qty'] > 0);
                $runKey = $r['nama_gudang'] . '||' . $r['kode_barang'] . '||' . $r['no_lot'];
                if (!isset($running[$runKey])) {
                    $running[$runKey] = 0.0;
                }
                $running[$runKey] += ($isIn ? $qty : -$qty);
                $r['masuk']  = $isIn ? $qty : 0.0;
                $r['keluar'] = $isIn ? 0.0 : $qty;
                $r['saldo']  = $running[$runKey];
            }
            unset($r);

            return $rows;
        }

        // 10. Nomor Lot Barang - Tersedia
        if ($type === 'lot-tersedia') {
            $this->db->select("
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), CONCAT('Gudang ', sb.gudang_id)) AS nama_gudang,
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                sb.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), sb.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                COALESCE(NULLIF(TRIM(sb.no_lot), ''), '0') AS no_lot,
                COALESCE(sb.qty_on_hand, 0) AS jumlah,
                DATE(sb.created_at) AS tgl_produksi,
                sb.expired_date AS tgl_kadaluarsa
            ", false);
            $this->db->from('tberp_stock_batch sb');
            $this->db->join('tbpo_barang b', 'b.kode_barang = sb.kd_barang', 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = sb.gudang_id', 'left');

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('sb.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('sb.no_lot', $search);
                $this->db->or_like('g.nama_gudang', $search);
                $this->db->group_end();
            }

            $this->db->order_by('nama_gudang', 'ASC');
            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('sb.kd_barang', 'ASC');
            $this->db->order_by('no_lot', 'ASC');
            return $this->db->get()->result_array();
        }

        // 11. Analisa Barang - Umum
        if ($type === 'analisa-umum') {
            $this->db->select("
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                b.kode_barang,
                b.nama_barang,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                COALESCE((SELECT SUM(sb.qty_on_hand) FROM tberp_stock_batch sb WHERE sb.kd_barang = b.kode_barang), 0) AS tersedia,
                COALESCE((SELECT SUM(dp.qty) FROM tbpo_detail_po dp INNER JOIN tbpo_po p ON p.kd_po = dp.kd_po WHERE dp.kd_barang = b.kode_barang AND COALESCE(p.status, '') NOT IN ('Closed','CLOSED','Cancelled','CANCELLED')), 0) AS dipesan_ke_pemasok,
                COALESCE((SELECT SUM(sb.qty_reserved) FROM tberp_stock_batch sb WHERE sb.kd_barang = b.kode_barang), 0) AS pesanan_pelanggan
            ", false);
            $this->db->from('tbpo_barang b');
            $this->db->where('b.is_active', 'T');
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('b.kode_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('b.kelompok_dagang', $search);
                $this->db->group_end();
            }
            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('b.kode_barang', 'ASC');
            $this->db->limit(1500);
            return $this->db->get()->result_array();
        }

        // 12. Barang Paling Laris (Top 10 / Per Kelompok / Margin Keuntungan), Perputaran Cepat & Laba/Rugi Barang Total
        if (in_array($type, [
            'laris-top10',
            'laris-per-kelompok',
            'laris-keuntungan',
            'laba-rugi-total',
            'perputaran-cepat-per-kelompok',
            'perputaran-cepat-semua',
        ], true)) {
            $this->db->select("
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                fd.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(fd.nama_barang), ''), NULLIF(TRIM(b.nama_barang), ''), fd.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(fd.satuan), ''), NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                SUM(COALESCE(fd.qty, 0)) AS terjual,
                SUM(COALESCE(fd.total_harga, 0)) AS nilai_penjualan,
                COALESCE((SELECT ld.harga_satuan FROM tb_lpb_detail ld WHERE ld.kd_barang = fd.kd_barang AND ld.harga_satuan > 0 ORDER BY ld.id_detail_lpb DESC LIMIT 1), 0) AS hpp_unit
            ", false);
            $this->db->from('tbso_faktur_detail fd');
            $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = fd.id_faktur', 'inner');
            $this->db->join('tbpo_barang b', 'b.kode_barang = fd.kd_barang', 'left');
            $this->db->where('f.status <>', 'cancelled');
            $this->db->where('(f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)', null, false);

            if ($start_date !== '') {
                $this->db->where('f.tanggal_faktur >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('f.tanggal_faktur <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('fd.kd_barang', $search);
                $this->db->or_like('fd.nama_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->group_end();
            }

            $this->db->group_by(['kelompok', 'fd.kd_barang', 'nama_barang', 'unit']);
            if ($type === 'perputaran-cepat-semua') {
                $this->db->order_by('nilai_penjualan', 'DESC');
            } elseif ($type === 'perputaran-cepat-per-kelompok') {
                $this->db->order_by('kelompok', 'ASC');
                $this->db->order_by('nilai_penjualan', 'DESC');
            } elseif ($type === 'laba-rugi-total') {
                $this->db->order_by('kelompok', 'ASC');
                $this->db->order_by('nama_barang', 'ASC');
            } else {
                $this->db->order_by('kelompok', 'ASC');
                $this->db->order_by('terjual', 'DESC');
            }
            $rows = $this->db->get()->result_array();

            foreach ($rows as &$r) {
                $terjual = (float) $r['terjual'];
                $penj    = (float) $r['nilai_penjualan'];
                $hpp     = $terjual * (float) $r['hpp_unit'];
                $laba    = $penj - $hpp;
                $r['harga_pokok'] = $hpp;
                $r['laba_rugi']   = $laba;
                $r['persen']      = ($penj > 0) ? round(($laba / $penj) * 100, 2) : 0.0;
            }
            unset($r);

            if ($type === 'laris-top10') {
                $byGrp = [];
                $out   = [];
                foreach ($rows as $r) {
                    $g = $r['kelompok'];
                    if (!isset($byGrp[$g])) $byGrp[$g] = 0;
                    if ($byGrp[$g] < 10) {
                        $byGrp[$g]++;
                        $out[] = $r;
                    }
                }
                return $out;
            }
            if ($type === 'laris-keuntungan') {
                usort($rows, function ($a, $b) {
                    if ($a['kelompok'] !== $b['kelompok']) {
                        return strcmp($a['kelompok'], $b['kelompok']);
                    }
                    return $b['persen'] <=> $a['persen'];
                });
            }
            return $rows;
        }

        // 13. Laba / Rugi Barang - Rincian
        if ($type === 'laba-rugi-rincian') {
            $this->db->select("
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                fd.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(fd.nama_barang), ''), NULLIF(TRIM(b.nama_barang), ''), fd.kd_barang) AS nama_barang,
                f.tanggal_faktur AS tanggal,
                'SJ' AS tp,
                f.no_faktur AS no_referensi,
                COALESCE(NULLIF(TRIM(fd.satuan), ''), NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                COALESCE(fd.qty, 0) AS terjual,
                COALESCE(fd.total_harga, 0) AS nilai_penjualan,
                COALESCE((SELECT ld.harga_satuan FROM tb_lpb_detail ld WHERE ld.kd_barang = fd.kd_barang AND ld.harga_satuan > 0 ORDER BY ld.id_detail_lpb DESC LIMIT 1), 0) AS hpp_unit
            ", false);
            $this->db->from('tbso_faktur_detail fd');
            $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = fd.id_faktur', 'inner');
            $this->db->join('tbpo_barang b', 'b.kode_barang = fd.kd_barang', 'left');
            $this->db->where('f.status <>', 'cancelled');
            $this->db->where('(f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)', null, false);

            if ($start_date !== '') {
                $this->db->where('f.tanggal_faktur >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('f.tanggal_faktur <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('fd.kd_barang', $search);
                $this->db->or_like('fd.nama_barang', $search);
                $this->db->or_like('f.no_faktur', $search);
                $this->db->group_end();
            }

            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('fd.kd_barang', 'ASC');
            $this->db->order_by('f.tanggal_faktur', 'ASC');
            $rows = $this->db->get()->result_array();

            foreach ($rows as &$r) {
                $t = (float) $r['terjual'];
                $p = (float) $r['nilai_penjualan'];
                $h = $t * (float) $r['hpp_unit'];
                $l = $p - $h;
                $r['harga_pokok'] = $h;
                $r['laba_rugi']   = $l;
                $r['persen']      = ($p > 0) ? round(($l / $p) * 100, 2) : 0.0;
            }
            unset($r);
            return $rows;
        }

        // 14. Penjualan Per Barang / Per Pelanggan / Per Salesman (Faktur & Total)
        if (in_array($type, [
            'penjualan-barang-pelanggan-faktur',
            'penjualan-pelanggan-barang-total',
            'penjualan-pelanggan-barang-faktur',
            'penjualan-salesman-barang-total',
            'penjualan-barang-pelanggan-total',
            'penjualan-barang-salesman-total',
            'penjualan-salesman-pelanggan-barang-faktur',
            'penjualan-salesman-barang-non-retur',
            'penjualan-salesman-pelanggan',
        ], true)) {
            $this->db->select("
                '(B) Gdg. Induk' AS nama_gudang,
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                fd.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(fd.nama_barang), ''), NULLIF(TRIM(b.nama_barang), ''), fd.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(f.salesman), ''), NULLIF(TRIM(c.nama_sales), ''), 'Others') AS salesman,
                COALESCE(NULLIF(TRIM(c.nama_kios), ''), f.customer_name, '-') AS pelanggan,
                f.tanggal_faktur AS tanggal,
                f.no_faktur AS no_referensi,
                COALESCE(fd.qty, 0) AS jumlah,
                COALESCE(NULLIF(TRIM(fd.satuan), ''), NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                COALESCE(fd.hrg_satuan, 0) AS harga_satuan,
                COALESCE(fd.disc, 0) AS disc_persen,
                COALESCE(fd.total_harga, 0) AS total
            ", false);
            $this->db->from('tbso_faktur_detail fd');
            $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = fd.id_faktur', 'inner');
            $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
            $this->db->join('tbpo_barang b', 'b.kode_barang = fd.kd_barang', 'left');
            $this->db->where('f.status <>', 'cancelled');
            $this->db->where('(f.parent_id_faktur IS NULL OR f.parent_id_faktur = 0)', null, false);
            if ($type === 'penjualan-salesman-barang-non-retur') {
                $this->db->where('COALESCE(fd.qty, 0) >', 0, false);
            }

            if ($start_date !== '') {
                $this->db->where('f.tanggal_faktur >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('f.tanggal_faktur <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('fd.kd_barang', $search);
                $this->db->or_like('fd.nama_barang', $search);
                $this->db->or_like('f.no_faktur', $search);
                $this->db->or_like('f.customer_name', $search);
                $this->db->or_like('f.salesman', $search);
                $this->db->group_end();
            }

            $this->db->order_by('kelompok', 'ASC');
            $this->db->order_by('nama_barang', 'ASC');
            $this->db->order_by('salesman', 'ASC');
            $this->db->order_by('pelanggan', 'ASC');
            $this->db->order_by('f.tanggal_faktur', 'ASC');
            return $this->db->get()->result_array();
        }

        // 15. Analisa Pembelian Per Barang / Per Pemasok (Faktur & Total)
        if (in_array($type, [
            'pembelian-barang-pemasok-faktur',
            'pembelian-pemasok-barang-invoice',
            'pembelian-pemasok-barang-total',
            'pembelian-barang-pemasok-total',
        ], true)) {
            $this->db->select("
                COALESCE(NULLIF(TRIM(g.nama_gudang), ''), '(B) Gdg. Induk') AS nama_gudang,
                COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), 'Barang Dagangan') AS kelompok,
                ld.kd_barang AS kode_barang,
                COALESCE(NULLIF(TRIM(b.nama_barang), ''), ld.kd_barang) AS nama_barang,
                COALESCE(NULLIF(TRIM(l.nama_suplier), ''), NULLIF(TRIM(s.nama_suplier), ''), '-') AS pemasok,
                DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) AS tanggal,
                l.nomor_lpb AS no_referensi,
                COALESCE(ld.qty_diterima, 0) AS jumlah,
                COALESCE(NULLIF(TRIM(b.satuan), ''), 'Pcs') AS unit,
                COALESCE(ld.harga_satuan, 0) AS harga_satuan,
                0 AS disc_persen,
                COALESCE(NULLIF(ld.dpp, 0), ld.total_harga, 0) AS total
            ", false);
            $this->db->from('tb_lpb_detail ld');
            $this->db->join('tb_lpb l', 'l.id_lpb = ld.id_lpb', 'inner');
            $this->db->join('tbpo_po p', 'p.kd_po = l.kd_po', 'left');
            $this->db->join('tbpo_suplier s', "s.kd_suplier = COALESCE(NULLIF(l.kd_suplier, ''), p.kd_suplier)", 'left');
            $this->db->join('tb_gudang g', 'g.id_gudang = l.gudang_id', 'left');
            $this->db->join('tbpo_barang b', 'b.kode_barang = ld.kd_barang', 'left');

            if ($start_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) >=', $start_date);
            }
            if ($end_date !== '') {
                $this->db->where('DATE(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) <=', $end_date);
            }
            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('ld.kd_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('l.nomor_lpb', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('l.nama_suplier', $search);
                $this->db->group_end();
            }

            if ($type === 'pembelian-pemasok-barang-invoice' || $type === 'pembelian-pemasok-barang-total') {
                $this->db->order_by('pemasok', 'ASC');
                $this->db->order_by('kelompok', 'ASC');
                $this->db->order_by('nama_barang', 'ASC');
            } else {
                $this->db->order_by('nama_gudang', 'ASC');
                $this->db->order_by('kelompok', 'ASC');
                $this->db->order_by('nama_barang', 'ASC');
                $this->db->order_by('pemasok', 'ASC');
            }
            $this->db->order_by('tanggal', 'ASC');
            return $this->db->get()->result_array();
        }

        return [];
    }
}
