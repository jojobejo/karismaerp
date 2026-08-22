<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model Penyesuaian Barang (Penyesuaian Persediaan)
 * Mengelola CRUD transaksi penyesuaian barang gudang
 * Referensi: Zahir Accounting > Persediaan > Penyesuaian Persediaan
 */
class M_PenyesuaianBarang extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Generate nomor referensi otomatis
     * Format: APBddmmyy-XX (contoh: APB140826-01)
     */
    public function generate_ref_no()
    {
        $dateStr = date('dmy'); // DDMMYY
        $prefix = 'APB' . $dateStr;

        $this->db->select('no_referensi');
        $this->db->like('no_referensi', $prefix, 'after');
        $this->db->order_by('no_referensi', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('tbkeu_penyesuaian_barang');

        $next_num = 1;
        if ($query->num_rows() > 0) {
            $last_no = $query->row()->no_referensi;
            // Format: APBddmmyy-XX
            $parts = explode('-', $last_no);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $next_num = (int)$parts[1] + 1;
            }
        }

        return $prefix . '-' . sprintf('%02d', $next_num);
    }

    /**
     * Ambil semua data penyesuaian barang dengan filter
     */
    public function get_all($filters = [], $limit = 250)
    {
        $this->db->select('pb.*, gd.nama_gudang as gudang_dari, gk.nama_gudang as gudang_ke');
        $this->db->from('tbkeu_penyesuaian_barang pb');
        $this->db->join('tb_gudang gd', 'gd.id_gudang = pb.id_gudang_dari', 'left');
        $this->db->join('tb_gudang gk', 'gk.id_gudang = pb.id_gudang_ke', 'left');

        if (!empty($filters['date_from'])) {
            $this->db->where('pb.tanggal >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('pb.tanggal <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('pb.no_referensi', $filters['search']);
            $this->db->or_like('pb.keterangan', $filters['search']);
            $this->db->group_end();
        }
        if (!empty($filters['status']) && $filters['status'] !== 'Semua') {
            if (strcasecmp($filters['status'], 'POSTED') === 0) {
                $this->db->where('pb.status', 'POSTED');
            } elseif (strcasecmp($filters['status'], 'UNPOSTED') === 0 || strcasecmp($filters['status'], 'DRAFT') === 0) {
                $this->db->where('pb.status !=', 'POSTED');
            } else {
                $this->db->where('pb.status', $filters['status']);
            }
        }

        $this->db->order_by('pb.tanggal', 'DESC');
        $this->db->order_by('pb.id_penyesuaian', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    /**
     * Ambil data penyesuaian barang by ID (header + detail)
     */
    public function get_by_id($id)
    {
        $this->db->select('pb.*, gd.nama_gudang as gudang_dari, gk.nama_gudang as gudang_ke');
        $this->db->from('tbkeu_penyesuaian_barang pb');
        $this->db->join('tb_gudang gd', 'gd.id_gudang = pb.id_gudang_dari', 'left');
        $this->db->join('tb_gudang gk', 'gk.id_gudang = pb.id_gudang_ke', 'left');
        $this->db->where('pb.id_penyesuaian', $id);
        $header = $this->db->get()->row_array();

        if ($header) {
            $this->db->select('d.*, a.kode_akun, a.nama_akun');
            $this->db->from('tbkeu_penyesuaian_barang_detail d');
            $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
            $this->db->where('d.id_penyesuaian', $id);
            $this->db->order_by('d.nomor_baris', 'ASC');
            $header['details'] = $this->db->get()->result_array();
        }

        return $header;
    }

    /**
     * Simpan (insert/update) header + detail penyesuaian barang
     */
    public function save($data, $details)
    {
        $this->db->trans_start();

        $id = isset($data['id_penyesuaian']) ? (int)$data['id_penyesuaian'] : 0;

        if ($id > 0) {
            // Jika mengedit data existing, kembalikan stok & jurnal lama terlebih dahulu
            $existing = $this->get_by_id($id);
            if ($existing) {
                $this->reverse_stock_and_journal($existing);
            }
            $this->db->where('id_penyesuaian', $id)->update('tbkeu_penyesuaian_barang', $data);
            $this->db->where('id_penyesuaian', $id)->delete('tbkeu_penyesuaian_barang_detail');
        } else {
            $this->db->insert('tbkeu_penyesuaian_barang', $data);
            $id = $this->db->insert_id();
        }

        $baris = 1;
        foreach ($details as $detail) {
            $lotDataJson = null;
            if (!empty($detail['lots']) && is_array($detail['lots'])) {
                $lotDataJson = json_encode($detail['lots']);
            } elseif (!empty($detail['lot_data'])) {
                $lotDataJson = is_string($detail['lot_data']) ? $detail['lot_data'] : json_encode($detail['lot_data']);
            }

            $no_lot = !empty($detail['no_lot']) ? trim($detail['no_lot']) : '';
            $expired_date = !empty($detail['expired_date']) && $detail['expired_date'] !== '0000-00-00' ? $detail['expired_date'] : null;

            // Jika ada multi-lot, ambil no_lot pertama sebagai representasi utama
            if (empty($no_lot) && !empty($detail['lots']) && is_array($detail['lots']) && count($detail['lots']) > 0) {
                $no_lot = trim($detail['lots'][0]['no_lot'] ?? '');
                $expired_date = !empty($detail['lots'][0]['expired_date']) ? $detail['lots'][0]['expired_date'] : null;
            }

            $this->db->insert('tbkeu_penyesuaian_barang_detail', [
                'id_penyesuaian' => $id,
                'kd_barang'      => $detail['kd_barang'],
                'nm_barang'      => $detail['nm_barang'] ?? '',
                'jumlah'         => $detail['jumlah'],
                'satuan'         => $detail['satuan'] ?? '',
                'id_akun'        => $detail['id_akun'] ?? null,
                'no_lot'         => $no_lot,
                'expired_date'   => $expired_date,
                'lot_data'       => $lotDataJson,
                'nomor_baris'    => $baris++
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id : false;
    }

    /**
     * Posting transaksi penyesuaian ke jurnal akuntansi
     * Tipe jurnal: IJ (Inventory Journal)
     */
    public function post_to_journal($id_penyesuaian, $userId = null)
    {
        $this->load->library('Accounting_service');
        $data = $this->get_by_id($id_penyesuaian);

        if (!$data || $data['status'] === 'POSTED') {
            return false;
        }

        $this->db->trans_start();

        // Siapkan baris jurnal dari detail (Agregasi / Pengelompokan per Akun seperti di Zahir)
        $grouped_debits = [];
        $grouped_kredits = [];
        $total_nilai_transaksi = 0;

        $gudang_id = $data['id_gudang_dari'] ?: ($data['id_gudang_ke'] ?: null);

        foreach ($data['details'] as $detail) {
            $jumlah = (float)$detail['jumlah'];
            if ($jumlah == 0 || empty($detail['id_akun'])) continue;

            $kd_barang = $detail['kd_barang'];
            $hpp = $this->get_item_hpp($kd_barang, $gudang_id);
            $nominal = round(abs($jumlah) * $hpp, 2);
            if ($nominal <= 0) $nominal = round(abs($jumlah) * 1, 2);

            $total_nilai_transaksi += $nominal;

            // 1. Akun yang dipilih di Form (Akun Beban / HPP / Selisih Persediaan)
            $id_akun_form = (int)$detail['id_akun'];

            // 2. Akun Persediaan barang dari master mapping
            $akun_persediaan = $this->get_item_inventory_account($kd_barang);
            $id_akun_persediaan = (int)$akun_persediaan['id_akun'];

            // Jika akun form sama dengan akun persediaan, fallback akun form ke HPP
            if ($id_akun_form === $id_akun_persediaan) {
                $akun_hpp = $this->get_item_cogs_account($kd_barang);
                if (!empty($akun_hpp['id_akun'])) {
                    $id_akun_form = (int)$akun_hpp['id_akun'];
                }
            }

            if ($jumlah < 0) {
                // Barang KELUAR: DEBIT Akun Beban/HPP, KREDIT Akun Persediaan
                if (!isset($grouped_debits[$id_akun_form])) $grouped_debits[$id_akun_form] = 0;
                $grouped_debits[$id_akun_form] += $nominal;

                if (!isset($grouped_kredits[$id_akun_persediaan])) $grouped_kredits[$id_akun_persediaan] = 0;
                $grouped_kredits[$id_akun_persediaan] += $nominal;
            } else {
                // Barang MASUK: DEBIT Akun Persediaan, KREDIT Akun Beban/HPP
                if (!isset($grouped_debits[$id_akun_persediaan])) $grouped_debits[$id_akun_persediaan] = 0;
                $grouped_debits[$id_akun_persediaan] += $nominal;

                if (!isset($grouped_kredits[$id_akun_form])) $grouped_kredits[$id_akun_form] = 0;
                $grouped_kredits[$id_akun_form] += $nominal;
            }
        }

        // Susun baris jurnal hasil konsolidasi per akun (Semua Debit di awal, lalu Kredit)
        $lines = [];
        $total_debit = 0;
        $total_kredit = 0;

        foreach ($grouped_debits as $id_akun => $val) {
            if ($val > 0) {
                $lines[] = [
                    'id_akun'        => $id_akun,
                    'keterangan'     => $data['keterangan'] ?: 'Penyesuaian Persediaan',
                    'debit'          => $val,
                    'kredit'         => 0,
                    'nomor_dokumen'  => $data['no_referensi']
                ];
                $total_debit += $val;
            }
        }

        foreach ($grouped_kredits as $id_akun => $val) {
            if ($val > 0) {
                $lines[] = [
                    'id_akun'        => $id_akun,
                    'keterangan'     => $data['keterangan'] ?: 'Penyesuaian Persediaan',
                    'debit'          => 0,
                    'kredit'         => $val,
                    'nomor_dokumen'  => $data['no_referensi']
                ];
                $total_kredit += $val;
            }
        }

        if (empty($lines) || $total_debit <= 0 || $total_kredit <= 0) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Tidak ada detail jurnal yang valid atau nilai total nol.'];
        }

        // Insert & Post jurnal via Accounting_service
        $payload = [
            'nomor_jurnal'       => $data['no_referensi'],
            'tanggal_transaksi'  => $data['tanggal'],
            'keterangan'         => $data['keterangan'] ?: 'Penyesuaian Persediaan',
            'journal_type'       => 'IJ',
            'source_module'      => 'PERSEDIAAN',
            'source_type'        => 'PENYESUAIAN_BARANG',
            'source_id'          => (string)$id_penyesuaian,
            'source_no'          => $data['no_referensi'],
            'idempotency_key'    => 'STOCK_ADJUSTMENT-' . $id_penyesuaian . '-' . $data['no_referensi'],
            'lines'              => $lines
        ];

        $journal_res = $this->accounting_service->post_auto('STOCK_ADJUSTMENT', $payload, $userId);
        if (!$journal_res['success']) {
            $this->db->trans_rollback();
            return $journal_res;
        }

        $id_jurnal = $journal_res['data']['id_jurnal'];

        // Update stok di stock_ledger & stock_batch
        $this->update_stock($data);

        // Update status ke POSTED dan perbarui total_nilai transaksi dengan nominal rupiah
        $this->db->where('id_penyesuaian', $id_penyesuaian)->update('tbkeu_penyesuaian_barang', [
            'status'      => 'POSTED',
            'id_jurnal'   => $id_jurnal,
            'total_nilai' => $total_nilai_transaksi,
            'updated_at'  => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();
        return ['success' => $this->db->trans_status(), 'message' => 'Jurnal berhasil diposting.'];
    }

    /**
     * Ambil HPP barang dari perhitungan Kartu Stok Gudang (Moving Average)
     * Mengikuti kalkulasi laporan kartu stok gudang (IN: LPB & Retur, OUT: Faktur)
     */
    public function get_item_hpp($kd_barang, $id_gudang = null)
    {
        $kd_barang = trim((string)$kd_barang);
        if ($kd_barang === '') {
            return 0.0;
        }

        // 1. Ambil transaksi IN dari Penerimaan Barang (LPB)
        $in_query = "SELECT l.tgl_sj AS tanggal, ld.qty_diterima AS qty, ld.harga_satuan AS harga, 'IN' AS type
                     FROM tb_lpb l
                     JOIN tb_lpb_detail ld ON l.id_lpb = ld.id_lpb
                     WHERE ld.kd_barang = ? AND l.status_lpb = 1";
        $params_in = [$kd_barang];
        if (!empty($id_gudang)) {
            $in_query .= " AND l.gudang_id = ?";
            $params_in[] = $id_gudang;
        }
        $in_tx = $this->db->query($in_query, $params_in)->result_array();

        // 2. Ambil transaksi IN dari Retur Penjualan
        $retur_query = "SELECT r.tanggal_retur AS tanggal, rd.qty_retur AS qty, rd.harga_satuan AS harga, 'IN' AS type
                        FROM tbrp_retur_penjualan_header r
                        JOIN tbrp_retur_penjualan_detail rd ON r.id_retur = rd.id_retur
                        JOIN tbpo_barang b ON (TRIM(LOWER(rd.nama_barang)) = TRIM(LOWER(b.nama_barang)))
                        WHERE b.kode_barang = ? AND r.status_retur NOT IN ('ditolak', 'batal')";
        $params_retur = [$kd_barang];
        if (!empty($id_gudang)) {
            $retur_query .= " AND r.gudang_id = ?";
            $params_retur[] = $id_gudang;
        }
        $retur_tx = $this->db->query($retur_query, $params_retur)->result_array();

        $all_in = array_merge($in_tx, $retur_tx);

        // 3. Ambil transaksi OUT dari Faktur Penjualan
        $out_query = "SELECT f.tanggal_faktur AS tanggal, fd.qty, fd.hrg_satuan AS harga, 'OUT' AS type
                      FROM tbso_faktur_penjualan f
                      JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                      WHERE fd.kd_barang = ? AND f.status NOT IN ('draft', 'cancelled')";
        $params_out = [$kd_barang];
        if (!empty($id_gudang)) {
            $out_query .= " AND f.gudang_id = ?";
            $params_out[] = $id_gudang;
        }
        $out_tx = $this->db->query($out_query, $params_out)->result_array();

        $txs = array_merge($all_in, $out_tx);

        if (!empty($txs)) {
            usort($txs, function($a, $b) {
                $t1 = strtotime($a['tanggal'] ?? '1970-01-01');
                $t2 = strtotime($b['tanggal'] ?? '1970-01-01');
                if ($t1 === $t2) {
                    return (($a['type'] ?? 'IN') === 'IN') ? -1 : 1;
                }
                return $t1 < $t2 ? -1 : 1;
            });

            $qty_saldo = 0.0;
            $nilai_saldo = 0.0;
            $average_hpp = 0.0;

            foreach ($txs as $tx) {
                $qty = (float)$tx['qty'];
                $harga = (float)$tx['harga'];

                if ($tx['type'] === 'IN') {
                    $qty_saldo += $qty;
                    $nilai_saldo += ($qty * $harga);
                    if ($qty_saldo > 0) {
                        $average_hpp = $nilai_saldo / $qty_saldo;
                    }
                } else {
                    $qty_saldo -= $qty;
                    $nilai_saldo -= ($qty * $average_hpp);
                }
            }

            if ($average_hpp > 0) {
                return (float)$average_hpp;
            }
        }

        // Fallback jika belum ada pergerakan di kartu stok:
        // Cek harga pembelian terakhir di LPB detail
        $last_lpb = $this->db->select('harga_satuan')
            ->from('tb_lpb_detail')
            ->where('kd_barang', $kd_barang)
            ->where('harga_satuan >', 0)
            ->order_by('id_detail', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
        if ($last_lpb && (float)$last_lpb['harga_satuan'] > 0) {
            return (float)$last_lpb['harga_satuan'];
        }

        // Cek tb_barang_hpp_average
        if ($this->db->table_exists('tb_barang_hpp_average')) {
            $hppRow = $this->db->select('hpp_avg_dpp')->where('kd_barang', $kd_barang)->limit(1)->get('tb_barang_hpp_average')->row_array();
            if ($hppRow && (float)$hppRow['hpp_avg_dpp'] > 0) {
                return (float)$hppRow['hpp_avg_dpp'];
            }
        }

        // Cek tbso_sales_order_detail
        if ($this->db->table_exists('tbso_sales_order_detail')) {
            $soStmt = $this->db->select('hrg_pokok')->where('kd_barang', $kd_barang)->where('hrg_pokok >', 0)->order_by('id', 'DESC')->limit(1)->get('tbso_sales_order_detail')->row_array();
            if ($soStmt && (float)$soStmt['hrg_pokok'] > 0) {
                return (float)$soStmt['hrg_pokok'];
            }
        }

        return 0.0;
    }

    /**
     * Ambil akun persediaan barang
     */
    public function get_item_inventory_account($kd_barang)
    {
        $res = $this->db->select('a.id_akun, a.kode_akun, a.nama_akun')
            ->from('tbpo_barang_akun ba')
            ->join('tbkeu_akun a', 'a.kode_akun = ba.kode_akun_persediaan')
            ->where('ba.kode_barang', $kd_barang)
            ->limit(1)
            ->get()
            ->row_array();

        if ($res && !empty($res['id_akun'])) {
            return $res;
        }

        // Fallback akun persediaan 14010 (Persediaan # 1)
        $fallback = $this->db->where('kode_akun', '14010')->limit(1)->get('tbkeu_akun')->row_array();
        if ($fallback) {
            return $fallback;
        }

        return ['id_akun' => 102, 'kode_akun' => '14010', 'nama_akun' => 'Persediaan # 1'];
    }

    /**
     * Ambil akun HPP barang
     */
    public function get_item_cogs_account($kd_barang)
    {
        $res = $this->db->select('a.id_akun, a.kode_akun, a.nama_akun')
            ->from('tbpo_barang_akun ba')
            ->join('tbkeu_akun a', 'a.kode_akun = ba.kode_akun_harga_pokok')
            ->where('ba.kode_barang', $kd_barang)
            ->limit(1)
            ->get()
            ->row_array();

        if ($res && !empty($res['id_akun'])) {
            return $res;
        }

        $fallback = $this->db->where('kode_akun', '51010')->limit(1)->get('tbkeu_akun')->row_array();
        if ($fallback) {
            return $fallback;
        }

        return ['id_akun' => 160, 'kode_akun' => '51010', 'nama_akun' => 'Harga Pokok Penjualan # 1'];
    }

    /**
     * Update stok di tberp_stock_ledger dan tberp_stock_batch
     * Jumlah positif = ADJIN (masuk), negatif = ADJOUT (keluar)
     */
    private function update_stock($data)
    {
        $gudang_id = $data['id_gudang_dari'] ?: ($data['id_gudang_ke'] ?: null);

        foreach ($data['details'] as $detail) {
            $jumlah = (float)$detail['jumlah'];
            if ($jumlah == 0) continue;

            $lots = [];
            if (!empty($detail['lot_data'])) {
                $decoded = is_string($detail['lot_data']) ? json_decode($detail['lot_data'], true) : $detail['lot_data'];
                if (is_array($decoded) && count($decoded) > 0) {
                    $lots = $decoded;
                }
            }

            if (empty($lots)) {
                // Single lot fallback
                $lots[] = [
                    'no_lot'       => $detail['no_lot'] ?? '',
                    'expired_date' => !empty($detail['expired_date']) && $detail['expired_date'] !== '0000-00-00' ? $detail['expired_date'] : null,
                    'jumlah'       => $jumlah
                ];
            }

            foreach ($lots as $lotItem) {
                $lotQty = isset($lotItem['jumlah']) ? (float)$lotItem['jumlah'] : $jumlah;
                if ($lotQty == 0) continue;

                $lotTipe = $lotQty > 0 ? 'ADJIN' : 'ADJOUT';
                $no_lot = trim((string)($lotItem['no_lot'] ?? ''));
                $expired_date = !empty($lotItem['expired_date']) && $lotItem['expired_date'] !== '0000-00-00' ? $lotItem['expired_date'] : null;

                // Insert ke tberp_stock_ledger
                $this->db->insert('tberp_stock_ledger', [
                    'kd_barang'    => $detail['kd_barang'],
                    'gudang_id'    => $gudang_id,
                    'no_lot'       => $no_lot,
                    'expired_date' => $expired_date,
                    'qty'          => abs($lotQty),
                    'tipe'         => $lotTipe,
                    'ref_no'       => $data['no_referensi'],
                    'ref_type'     => 'PENYESUAIAN',
                    'created_at'   => date('Y-m-d H:i:s')
                ]);

                // Update qty_on_hand di stock_batch
                $this->apply_batch_qty_change($detail['kd_barang'], $gudang_id, $no_lot, $expired_date, $lotQty);
            }
        }
    }

    /**
     * Membalikkan perubahan stok dan menghapus jurnal yang berhubungan dengan transaksi penyesuaian
     */
    private function reverse_stock_and_journal($data)
    {
        if (empty($data)) {
            return;
        }

        $refNo = $data['no_referensi'] ?? '';
        $idJurnal = !empty($data['id_jurnal']) ? (int)$data['id_jurnal'] : 0;
        $gudangId = $data['id_gudang_dari'] ?: ($data['id_gudang_ke'] ?: null);

        // 1. Hapus Jurnal terkait jika ada
        if ($idJurnal > 0) {
            $this->db->where('id_jurnal', $idJurnal)->delete('tbkeu_jurnal_log');
            $this->db->where('id_jurnal', $idJurnal)->delete('tbkeu_jurnal_detail');
            $this->db->where('id_jurnal', $idJurnal)->delete('tbkeu_jurnal');
        }
        if (!empty($refNo)) {
            $jurnalByRef = $this->db->select('id_jurnal')
                ->where('source_module', 'PERSEDIAAN')
                ->where('source_type', 'PENYESUAIAN_BARANG')
                ->where('source_no', $refNo)
                ->get('tbkeu_jurnal')
                ->result_array();
            foreach ($jurnalByRef as $jRow) {
                $jId = (int)$jRow['id_jurnal'];
                $this->db->where('id_jurnal', $jId)->delete('tbkeu_jurnal_log');
                $this->db->where('id_jurnal', $jId)->delete('tbkeu_jurnal_detail');
                $this->db->where('id_jurnal', $jId)->delete('tbkeu_jurnal');
            }
        }

        // 2. Reverse Stok
        // Periksa apakah ada catatan di tberp_stock_ledger untuk transaksi ini
        $ledgerEntries = [];
        if (!empty($refNo)) {
            $ledgerEntries = $this->db->where('ref_no', $refNo)
                ->where('ref_type', 'PENYESUAIAN')
                ->get('tberp_stock_ledger')
                ->result_array();
        }

        if (!empty($ledgerEntries)) {
            // Reversal berdasarkan apa yang pernah dicatat di stock_ledger
            foreach ($ledgerEntries as $ledger) {
                $kdBarang = $ledger['kd_barang'];
                $gId = $ledger['gudang_id'] ?: $gudangId;
                $noLot = trim((string)($ledger['no_lot'] ?? ''));
                $expDate = !empty($ledger['expired_date']) && $ledger['expired_date'] !== '0000-00-00' ? $ledger['expired_date'] : null;
                $qty = (float)$ledger['qty'];
                $tipe = $ledger['tipe']; // ADJIN (+qty) atau ADJOUT (-qty)

                // Jika dulu ADJIN (menambah), sekarang kurangi (-qty)
                // Jika dulu ADJOUT (mengurangi), sekarang tambah (+qty)
                $qtyAdjustment = ($tipe === 'ADJIN') ? -$qty : $qty;

                $this->apply_batch_qty_change($kdBarang, $gId, $noLot, $expDate, $qtyAdjustment);
            }

            // Hapus ledger entries transaksi ini
            $this->db->where('ref_no', $refNo)
                ->where('ref_type', 'PENYESUAIAN')
                ->delete('tberp_stock_ledger');
        } elseif (($data['status'] ?? '') === 'POSTED' && !empty($data['details'])) {
            // Fallback jika ledger tidak ditemukan tapi status POSTED
            foreach ($data['details'] as $detail) {
                $jumlah = (float)$detail['jumlah'];
                if ($jumlah == 0) continue;

                $lots = [];
                if (!empty($detail['lot_data'])) {
                    $decoded = is_string($detail['lot_data']) ? json_decode($detail['lot_data'], true) : $detail['lot_data'];
                    if (is_array($decoded) && count($decoded) > 0) $lots = $decoded;
                }
                if (empty($lots)) {
                    $lots[] = [
                        'no_lot'       => $detail['no_lot'] ?? '',
                        'expired_date' => !empty($detail['expired_date']) && $detail['expired_date'] !== '0000-00-00' ? $detail['expired_date'] : null,
                        'jumlah'       => $jumlah
                    ];
                }

                foreach ($lots as $lotItem) {
                    $lotQty = isset($lotItem['jumlah']) ? (float)$lotItem['jumlah'] : $jumlah;
                    $noLot = trim((string)($lotItem['no_lot'] ?? ''));
                    $expDate = !empty($lotItem['expired_date']) && $lotItem['expired_date'] !== '0000-00-00' ? $lotItem['expired_date'] : null;

                    // Mengembalikan stok: dikurangi dari jumlah yang pernah ditambah/dikurang
                    $qtyAdjustment = -$lotQty;
                    $this->apply_batch_qty_change($detail['kd_barang'], $gudangId, $noLot, $expDate, $qtyAdjustment);
                }
            }
        }
    }

    /**
     * Terapkan perubahan qty pada stock batch secara cerdas (mencocokkan lot & fallback jika lot kosong)
     */
    private function apply_batch_qty_change($kdBarang, $gudangId, $noLot, $expDate, $qtyDiff)
    {
        if ($qtyDiff == 0 || empty($kdBarang)) {
            return;
        }

        $batchQuery = $this->db->where('kd_barang', $kdBarang);
        if (!empty($gudangId)) {
            $batchQuery->where('gudang_id', $gudangId);
        }

        if ($noLot !== '') {
            $batchQuery->where('no_lot', $noLot);
        }
        if ($expDate) {
            $batchQuery->where('expired_date', $expDate);
        }

        $batch = $batchQuery->order_by('id', 'DESC')->limit(1)->get('tberp_stock_batch')->row_array();

        // Jika tidak ditemukan dan no_lot kosong, coba cari batch apapun di gudang tersebut
        if (!$batch && $noLot === '') {
            $fallbackQuery = $this->db->where('kd_barang', $kdBarang);
            if (!empty($gudangId)) {
                $fallbackQuery->where('gudang_id', $gudangId);
            }
            $batch = $fallbackQuery->order_by('qty_on_hand', 'DESC')->limit(1)->get('tberp_stock_batch')->row_array();
        }

        if ($batch) {
            $newQty = (float)$batch['qty_on_hand'] + $qtyDiff;
            $this->db->where('id', $batch['id'])->update('tberp_stock_batch', [
                'qty_on_hand' => $newQty,
                'update_at'   => date('Y-m-d H:i:s')
            ]);
        } else {
            // Buat batch baru jika belum ada
            $this->db->insert('tberp_stock_batch', [
                'kd_barang'    => $kdBarang,
                'gudang_id'    => (string)($gudangId ?: '2'),
                'no_lot'       => $noLot,
                'expired_date' => $expDate ?: '1000-01-01',
                'qty_on_hand'  => $qtyDiff,
                'qty_reserved' => 0,
                'created_at'   => date('Y-m-d H:i:s'),
                'update_at'    => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Hapus transaksi penyesuaian barang
     * Reverse jurnal dan stok (baik POSTED maupun jika ada ledger)
     */
    public function delete($id)
    {
        $data = $this->get_by_id($id);
        if (!$data) return false;

        $this->db->trans_start();

        // Reverse stok dan jurnal transaksi ini
        $this->reverse_stock_and_journal($data);

        // Hapus header (detail akan cascade)
        $this->db->where('id_penyesuaian', $id)->delete('tbkeu_penyesuaian_barang');

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Unpost transaksi (kembalikan status ke DRAFT, reverse jurnal & stok)
     */
    public function unpost($id)
    {
        $data = $this->get_by_id($id);
        if (!$data || $data['status'] !== 'POSTED') return false;

        $this->db->trans_start();

        // Reverse stok dan jurnal transaksi ini
        $this->reverse_stock_and_journal($data);

        // Update status ke DRAFT
        $this->db->where('id_penyesuaian', $id)->update('tbkeu_penyesuaian_barang', [
            'status'     => 'DRAFT',
            'id_jurnal'  => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Lookup lot barang persediaan untuk modal popup Data Lot Barang
     * Menampilkan daftar lot ID, Tanggal Expired, dan Qty Tersedia
     */
    public function lookup_lot_barang($kd_barang, $gudang_id = null, $search = '')
    {
        if (empty($kd_barang)) {
            return [];
        }

        // 1. Ambil lot dari tberp_stock_batch
        $this->db->select("
            sb.no_lot,
            sb.expired_date,
            SUM(CASE WHEN sb.gudang_id = " . (!empty($gudang_id) ? $this->db->escape($gudang_id) : "sb.gudang_id") . " 
                     THEN (COALESCE(sb.qty_on_hand, 0) - COALESCE(sb.qty_reserved, 0)) 
                     ELSE 0 
                END) AS qty_tersedia,
            SUM(COALESCE(sb.qty_on_hand, 0)) AS qty_total
        ", false);
        $this->db->from('tberp_stock_batch sb');
        $this->db->where('sb.kd_barang', $kd_barang);
        $this->db->where('sb.no_lot IS NOT NULL');
        $this->db->where('sb.no_lot !=', '');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('sb.no_lot', $search);
            $this->db->or_like('sb.expired_date', $search);
            $this->db->group_end();
        }

        $this->db->group_by(['sb.no_lot', 'sb.expired_date']);
        $this->db->order_by('qty_tersedia', 'DESC');
        $this->db->order_by('sb.expired_date', 'ASC');
        $this->db->order_by('sb.no_lot', 'ASC');

        $rows = $this->db->get()->result_array();

        // 2. Fallback: jika di stock_batch tidak ditemukan lot, cek dari tb_lpb_batch
        if (empty($rows)) {
            $this->db->select("
                lb.no_lot,
                lb.expired_date,
                0 AS qty_tersedia,
                0 AS qty_total
            ");
            $this->db->from('tb_lpb_batch lb');
            $this->db->join('tb_lpb_detail ld', 'ld.id_detail = lb.id_detail_lpb', 'inner');
            $this->db->where('ld.kd_barang', $kd_barang);
            $this->db->where('lb.no_lot IS NOT NULL');
            $this->db->where('lb.no_lot !=', '');

            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('lb.no_lot', $search);
                $this->db->or_like('lb.expired_date', $search);
                $this->db->group_end();
            }

            $this->db->group_by(['lb.no_lot', 'lb.expired_date']);
            $this->db->order_by('lb.expired_date', 'ASC');
            $this->db->limit(100);
            $rows = $this->db->get()->result_array();
        }

        foreach ($rows as &$r) {
            $r['expired_date_formatted'] = (!empty($r['expired_date']) && $r['expired_date'] !== '0000-00-00' && $r['expired_date'] !== '1000-01-01')
                ? date('d/m/Y', strtotime($r['expired_date']))
                : '-';
            $qtyFloat = (float)$r['qty_tersedia'];
            $r['tersedia_formatted'] = (floor($qtyFloat) == $qtyFloat) ? (string)$qtyFloat : (string)round($qtyFloat, 3);
        }

        return $rows;
    }

    /**
     * Lookup barang persediaan untuk modal popup
     * Menampilkan master persediaan barang aktif beserta data stok (tersedia, dipesan, total),
     * kelompok barang/dagang, nama gudang, dan default akun persediaan.
     */
    public function lookup_barang($search = '', $gudang_id = null)
    {
        $gudangFilter = '';
        if (!empty($gudang_id)) {
            $gudangFilter = "WHERE sb.gudang_id = " . $this->db->escape($gudang_id);
        }

        $stockSubquery = "(
            SELECT 
                sb.kd_barang,
                " . (!empty($gudang_id) ? "MIN(sb.gudang_id) as gudang_id," : "NULL as gudang_id,") . "
                SUM(COALESCE(sb.qty_on_hand - sb.qty_reserved, 0)) AS qty_tersedia,
                SUM(COALESCE(sb.qty_reserved, 0)) AS qty_dipesan,
                SUM(COALESCE(sb.qty_on_hand, 0)) AS qty_total
            FROM tberp_stock_batch sb
            {$gudangFilter}
            GROUP BY sb.kd_barang
        )";

        $this->db->select("
            pb.kode_barang AS kd_barang,
            pb.kode_barang AS kode,
            pb.nama_barang AS nama_barang,
            pb.nama_barang AS deskripsi,
            pb.satuan AS satuan,
            COALESCE(stk.qty_tersedia, 0) AS tersedia,
            COALESCE(stk.qty_dipesan, 0) AS dipesan,
            COALESCE(stk.qty_total, 0) AS total,
            COALESCE(kd.DESKRIPSI, pb.kelompok_barang, '-') AS kelompok,
            " . (!empty($gudang_id) ? "COALESCE(g.nama_gudang, '-') AS nama_gudang," : "'-' AS nama_gudang,") . "
            COALESCE(a.id_akun, a2.id_akun) AS id_akun,
            COALESCE(a.kode_akun, a2.kode_akun) AS kode_akun,
            COALESCE(a.nama_akun, a2.nama_akun) AS nama_akun
        ", false);

        $this->db->from('tbpo_barang pb');
        $this->db->join("{$stockSubquery} stk", 'stk.kd_barang = pb.kode_barang', 'left');
        $this->db->join('tbkeu_kelompok_dagang kd', 'CAST(kd.NOINDEX AS CHAR) = pb.kelompok_dagang', 'left');
        if (!empty($gudang_id)) {
            $this->db->join('tb_gudang g', 'g.id_gudang = stk.gudang_id', 'left');
        }
        $this->db->join('tbkeu_akun a', 'a.kode_akun = pb.kode_akun_persediaan', 'left');
        $this->db->join('tbpo_barang_akun ba', 'ba.kode_barang = pb.kode_barang', 'left');
        $this->db->join('tbkeu_akun a2', 'a2.kode_akun = ba.kode_akun_persediaan', 'left');

        $this->db->where("(pb.is_active = 'T' OR pb.is_active = '1' OR pb.is_active IS NULL)", null, false);
        $this->db->where('pb.kode_barang IS NOT NULL');
        $this->db->where('pb.kode_barang !=', '');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('pb.kode_barang', $search);
            $this->db->or_like('pb.nama_barang', $search);
            $this->db->or_like('kd.DESKRIPSI', $search);
            $this->db->or_like('pb.kelompok_barang', $search);
            $this->db->group_end();
        }

        $this->db->order_by('pb.kode_barang', 'ASC');
        $this->db->limit(300);

        return $this->db->get()->result_array();
    }

    /**
     * Lookup daftar gudang aktif
     */
    public function lookup_gudang()
    {
        return $this->db->select('id_gudang, nama_gudang')
            ->where('is_active', 1)
            ->order_by('nama_gudang', 'ASC')
            ->get('tb_gudang')
            ->result_array();
    }
}
