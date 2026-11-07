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
            COALESCE(NULLIF(j.source_id, ''), j.source_no) AS no_faktur,
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
            COALESCE(NULLIF(k.nm_karyawan, ''), IF(j.created_by IS NULL, '', CONCAT('User #', j.created_by))) AS created_by_name,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
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

    public function accounting_sales_journal_report($start_date = '', $end_date = '')
    {
        if (!$this->accounting_journal_schema_ready()) {
            return [];
        }

        // Fetch headers
        $this->db->select("
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.source_no AS referensi,
            COALESCE(NULLIF(j.source_id, ''), j.source_no) AS no_faktur,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(f.no_so, '') AS no_so,
            COALESCE(f.customer_name, '') AS pelanggan,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbso_faktur_penjualan f', 'j.source_module = "SALES" AND (f.no_faktur = j.source_id OR f.no_faktur = j.source_no)', 'left');
        $this->db->where('j.source_module', 'SALES');
        $this->db->group_start();
        $this->db->where('j.source_type', 'FAKTUR_PENJUALAN');
        $this->db->or_where('j.source_type', 'FAKTUR_PENJUALAN_KONSINYASI');
        $this->db->group_end();
        
        if ($start_date !== '' && $end_date !== '') {
            $this->db->where('j.tanggal_transaksi >=', $start_date);
            $this->db->where('j.tanggal_transaksi <=', $end_date);
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
            COALESCE(a.kode_akun_display, a.kode_akun) AS kode_rekening_display,
            a.nama_akun,
            d.keterangan,
            d.debit,
            d.kredit,
            d.cost_center,
            d.project_no
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

        // Merge headers with details
        foreach ($headers as &$h) {
            $h['details'] = isset($details_grouped[$h['id_jurnal']]) ? $details_grouped[$h['id_jurnal']] : [];
        }

        return $headers;
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

}
