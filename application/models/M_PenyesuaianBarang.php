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
            $this->db->where('pb.status', $filters['status']);
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

        foreach ($data['details'] as $detail) {
            $jumlah = (float)$detail['jumlah'];
            if ($jumlah == 0 || empty($detail['id_akun'])) continue;

            $kd_barang = $detail['kd_barang'];
            $hpp = $this->get_item_hpp($kd_barang);
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
     * Ambil HPP barang dari master
     */
    public function get_item_hpp($kd_barang)
    {
        $stmt = $this->db->select('hpp')->where('kd_barang', $kd_barang)->limit(1)->get('tb_master_barang_all')->row_array();
        if ($stmt && (float)$stmt['hpp'] > 0) {
            return (float)$stmt['hpp'];
        }

        $soStmt = $this->db->select('hrg_pokok')->where('kd_barang', $kd_barang)->where('hrg_pokok >', 0)->order_by('id', 'DESC')->limit(1)->get('tbso_sales_order_detail')->row_array();
        if ($soStmt && (float)$soStmt['hrg_pokok'] > 0) {
            return (float)$soStmt['hrg_pokok'];
        }

        return 20000.0;
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

            $tipe = $jumlah > 0 ? 'ADJIN' : 'ADJOUT';

            // Cek apakah ada rincian multi-lot
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
                $no_lot = trim($lotItem['no_lot'] ?? '');
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
                $batchQuery = $this->db->where('kd_barang', $detail['kd_barang'])
                    ->where('gudang_id', $gudang_id);
                
                if ($no_lot !== '') {
                    $batchQuery->where('no_lot', $no_lot);
                }
                if ($expired_date) {
                    $batchQuery->where('expired_date', $expired_date);
                }

                $batch = $batchQuery->order_by('id', 'DESC')->limit(1)->get('tberp_stock_batch')->row_array();

                if ($batch) {
                    $new_qty = (float)$batch['qty_on_hand'] + $lotQty;
                    $this->db->where('id', $batch['id'])->update('tberp_stock_batch', [
                        'qty_on_hand' => $new_qty,
                        'update_at'   => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // Jika batch belum ada dan penyesuaian menambah stok, buat batch baru
                    $this->db->insert('tberp_stock_batch', [
                        'kd_barang'    => $detail['kd_barang'],
                        'gudang_id'    => (string)$gudang_id,
                        'no_lot'       => $no_lot,
                        'expired_date' => $expired_date ?: '1000-01-01',
                        'qty_on_hand'  => $lotQty,
                        'qty_reserved' => 0,
                        'created_at'   => date('Y-m-d H:i:s'),
                        'update_at'    => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    /**
     * Hapus transaksi penyesuaian barang
     * Jika POSTED, reverse jurnal dan stok
     */
    public function delete($id)
    {
        $data = $this->get_by_id($id);
        if (!$data) return false;

        $this->db->trans_start();

        // Jika POSTED, reverse jurnal
        if ($data['status'] === 'POSTED' && !empty($data['id_jurnal'])) {
            $this->load->library('Accounting_service');
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal_log');
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal_detail');
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal');

            // Reverse stok: hapus ledger entries
            $this->db->where('ref_no', $data['no_referensi'])
                ->where('ref_type', 'PENYESUAIAN')
                ->delete('tberp_stock_ledger');

            // Reverse qty di stock_batch
            $gudang_id = $data['id_gudang_dari'] ?: ($data['id_gudang_ke'] ?: null);
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
                    $no_lot = trim($lotItem['no_lot'] ?? '');
                    $expired_date = !empty($lotItem['expired_date']) && $lotItem['expired_date'] !== '0000-00-00' ? $lotItem['expired_date'] : null;

                    $batchQuery = $this->db->where('kd_barang', $detail['kd_barang'])
                        ->where('gudang_id', $gudang_id);
                    if ($no_lot !== '') {
                        $batchQuery->where('no_lot', $no_lot);
                    }
                    if ($expired_date) {
                        $batchQuery->where('expired_date', $expired_date);
                    }

                    $batch = $batchQuery->order_by('id', 'DESC')->limit(1)->get('tberp_stock_batch')->row_array();

                    if ($batch) {
                        $new_qty = (float)$batch['qty_on_hand'] - $lotQty;
                        $this->db->where('id', $batch['id'])->update('tberp_stock_batch', [
                            'qty_on_hand' => $new_qty,
                            'update_at'   => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }

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

        // Reverse jurnal
        if (!empty($data['id_jurnal'])) {
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal_log');
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal_detail');
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal');
        }

        // Reverse stok
        $this->db->where('ref_no', $data['no_referensi'])
            ->where('ref_type', 'PENYESUAIAN')
            ->delete('tberp_stock_ledger');

        $gudang_id = $data['id_gudang_dari'] ?: ($data['id_gudang_ke'] ?: null);
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
                $no_lot = trim($lotItem['no_lot'] ?? '');
                $expired_date = !empty($lotItem['expired_date']) && $lotItem['expired_date'] !== '0000-00-00' ? $lotItem['expired_date'] : null;

                $batchQuery = $this->db->where('kd_barang', $detail['kd_barang'])
                    ->where('gudang_id', $gudang_id);
                if ($no_lot !== '') {
                    $batchQuery->where('no_lot', $no_lot);
                }
                if ($expired_date) {
                    $batchQuery->where('expired_date', $expired_date);
                }

                $batch = $batchQuery->order_by('id', 'DESC')->limit(1)->get('tberp_stock_batch')->row_array();

                if ($batch) {
                    $new_qty = (float)$batch['qty_on_hand'] - $lotQty;
                    $this->db->where('id', $batch['id'])->update('tberp_stock_batch', [
                        'qty_on_hand' => $new_qty,
                        'update_at'   => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

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
            $this->db->where('ld.kode_barang', $kd_barang);
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
