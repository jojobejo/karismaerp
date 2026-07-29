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

        $jurnal_data = [
            'nomor_jurnal' => $nomor_jurnal,
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
        $akun_debit = $this->db->get_where('tbkeu_akun', ['nama_akun' => $metode])->row_array();
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
                $this->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal' => $id_jurnal,
                    'nomor_baris' => $baris++,
                    'id_akun' => $id_akun_debit,
                    'keterangan' => 'Penerimaan ' . $metode,
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
                'keterangan' => 'Piutang Usaha Faktur ' . $no_faktur,
                'debit' => 0,
                'kredit' => $total_piutang
            ]);
        }

        return true;
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
            j.source_id AS no_faktur,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(f.no_so, '') AS no_so,
            COALESCE(f.customer_name, '') AS pelanggan,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbso_faktur_penjualan f', 'j.source_module = "SALES" AND f.no_faktur = j.source_id', 'left');
        $this->db->where('j.source_module', 'SALES');
        $this->db->where('j.source_type', 'FAKTUR_PENJUALAN');
        $this->db->group_start();
        $this->db->where('j.posting_event', 'SALES_INVOICE');
        $this->db->or_where('j.posting_event IS NULL');
        $this->db->or_where('j.posting_event', '');
        $this->db->group_end();
        
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('j.source_no', $search);
            $this->db->or_like('j.nomor_jurnal', $search);
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
            CONCAT('Penerimaan dari ', COALESCE(f.customer_name, '')) AS keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(f.customer_name, '') AS pelanggan
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_pembayaran_faktur p', 'j.source_module = "KEUANGAN" AND CAST(j.source_id AS UNSIGNED) = p.id_pembayaran', 'left');
        $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = p.id_faktur', 'left');
        $this->db->where('j.source_module', 'KEUANGAN');
        $this->db->where('j.source_type', 'PEMBAYARAN_FAKTUR');
        
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
                NULLIF(u.nama_user, ''), 
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
        $this->db->join('tb_user u', 'j.created_by = u.id', 'left');
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

        $this->db->select("d.*, a.kode_akun, a.nama_akun, COALESCE(ref.kode_rekening_display, a.kode_akun) AS kode_rekening_display", false);
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->db->join('tbkeu_akun_karismaerp_ref ref', 'ref.id_akun = a.id_akun', 'left');
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

        // Query detail retur
        $details = $this->db->get_where('tbrp_retur_penjualan_detail', ['id_retur' => $id_retur])->result_array();
        if (empty($details)) {
            return false;
        }

        $no_retur = $retur['no_retur'];
        $tanggal = $retur['tanggal_retur'] ?: date('Y-m-d');
        $nomor_jurnal = $this->generate_no_jurnal('retur_penjualan', 'RJP');

        // Calculate total value
        $total_value = 0;
        foreach ($details as $d) {
            $total_value += (float)$d['qty_retur'] * (float)$d['harga_satuan'];
        }

        if ($total_value <= 0) {
            return false;
        }

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
            'total_debit' => $total_value,
            'total_kredit' => $total_value,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tbkeu_jurnal', $jurnal_data);
        $id_jurnal = $this->db->insert_id();

        // Account IDs resolver
        // 1. Piutang Usaha (Kredit)
        $akun_piutang = $this->db->get_where('tbkeu_akun', ['kode_akun' => '13099'])->row_array();
        if (!$akun_piutang) {
            $this->db->like('nama_akun', 'Piutang Usaha');
            $akun_piutang = $this->db->get('tbkeu_akun')->row_array();
        }
        $id_akun_piutang = $akun_piutang ? $akun_piutang['id_akun'] : 205; // fallback to 205 Q Piutang Dagang

        // 2. Q PPN K (Debit for taxable)
        $akun_ppn = $this->db->get_where('tbkeu_akun', ['kode_akun' => '21024'])->row_array()
            ?: $this->db->like('nama_akun', 'PPN Keluaran')->get('tbkeu_akun')->row_array();
        $id_akun_ppn = $akun_ppn ? $akun_ppn['id_akun'] : 280;

        // Fetch all accounts for quick mapping
        $akun_all = $this->db->get('tbkeu_akun')->result_array();
        $akun_map = [];
        foreach ($akun_all as $a) {
            $akun_map[$a['kode_akun']] = $a['id_akun'];
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

            $this->db->select('b.kode_barang, b.kode_akun_retur_penjualan, b.kode_akun_persediaan, b.kode_akun_harga_pokok, fd.hrg_pokok, g.DESKRIPSI');
            $this->db->from('tbpo_barang b');
            $this->db->join('tbso_faktur_detail fd', 'fd.kd_barang = b.kode_barang AND fd.no_faktur = ' . $this->db->escape($d['no_faktur']), 'left');
            $this->db->join('tbkeu_kelompok_dagang g', 'b.kelompok_dagang = g.NOINDEX', 'left');
            $this->db->where('b.nama_barang', $d['nama_barang']);
            $prod = $this->db->get()->row_array();

            $kode_akun_retur = $prod ? trim($prod['kode_akun_retur_penjualan']) : '';
            $desc = $prod ? strtoupper(trim($prod['DESKRIPSI'])) : '';

            $is_bkp = false;
            if (stripos($desc, 'BKPS') !== false) {
                $is_bkp = false;
            } elseif (stripos($desc, 'BKP') !== false) {
                $is_bkp = true;
            } else {
                if (strpos(strtolower($d['nama_barang']), 'jasa') !== false) {
                    $is_bkp = false;
                }
            }

            // Tax calculation
            $dpp = $item_val;
            $ppn = 0;
            if ($is_bkp) {
                $dpp = round($item_val / 1.11, 2);
                $ppn = round($item_val - $dpp, 2);
            }

            // Resolve ID Akun
            $id_akun_retur = isset($akun_map[$kode_akun_retur]) ? $akun_map[$kode_akun_retur] : 310; // fallback

            if (!isset($grouped_retur[$id_akun_retur])) {
                $grouped_retur[$id_akun_retur] = 0;
            }
            $grouped_retur[$id_akun_retur] += $dpp;
            $total_ppn += $ppn;

            // Stock/HPP reversal calculation
            $cost_unit = $prod && (float)$prod['hrg_pokok'] > 0 ? (float)$prod['hrg_pokok'] : 0;
            if ($cost_unit <= 0) {
                $fallback_prod = $this->db->get_where('tbpo_barang', ['nama_barang' => $d['nama_barang']])->row_array();
                $cost_unit = $fallback_prod ? (float)$fallback_prod['harga_pokok'] : 0;
            }
            $cost_total = round((float)$d['qty_retur'] * $cost_unit, 2);

            $prefix = $prod ? strtoupper(substr($prod['kode_barang'], 0, 1)) : '';
            $kode_persediaan = '';
            $kode_hpp = '';

            if ($prefix === 'Q') {
                if ($is_bkp) {
                    $kode_persediaan = '14010';
                    $kode_hpp = '51010';
                } else {
                    $kode_persediaan = '14011';
                    $kode_hpp = '51011';
                }
            } elseif ($prefix === 'Z') {
                $kode_persediaan = '14011';
                $kode_hpp = '51011';
            } elseif ($prefix === 'A') {
                $kode_persediaan = '14031';
                $kode_hpp = '51031';
            } else {
                $kode_persediaan = $prod && !empty($prod['kode_akun_persediaan']) ? $prod['kode_akun_persediaan'] : '14010';
                $kode_hpp = $prod && !empty($prod['kode_akun_harga_pokok']) ? $prod['kode_akun_harga_pokok'] : '51010';
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

        // Kredit: Piutang Usaha
        if ($total_debit > 0) {
            $this->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal,
                'nomor_baris' => $line_num++,
                'id_akun' => $id_akun_piutang,
                'keterangan' => 'Potongan Piutang Retur ' . $no_retur,
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
            j.source_id AS no_faktur,
            j.keterangan,
            j.total_debit AS nilai,
            j.status,
            COALESCE(f.no_so, '') AS no_so,
            COALESCE(f.customer_name, '') AS pelanggan,
            'IDR' AS kurs
        ", false);
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbso_faktur_penjualan f', 'j.source_module = "SALES" AND f.no_faktur = j.source_id', 'left');
        $this->db->where('j.source_module', 'SALES');
        $this->db->where('j.source_type', 'FAKTUR_PENJUALAN');
        
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
}
