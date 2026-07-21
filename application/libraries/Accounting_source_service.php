<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Adapter transaksi sumber ke jurnal. Seluruh nominal dibaca ulang dari tabel
 * final; controller tidak boleh mengirim nominal buatan sendiri ke accounting.
 */
class Accounting_source_service
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        $this->CI->load->library('Accounting_service');
    }

    public function post_sales_invoice($noFaktur, $kdDo = '', $userId = null, $postGoodsIssue = true)
    {
        $noFaktur = trim((string)$noFaktur);
        if ($noFaktur === '') {
            return $this->fail('Nomor faktur kosong.', ['SOURCE_ID_REQUIRED']);
        }

        $header = $this->CI->db
            ->where('no_faktur', $noFaktur)
            ->get('tbso_faktur_penjualan')
            ->row();
        if (!$header || strtolower((string)$header->status) === 'cancelled') {
            return $this->record_failure('SALES_INVOICE', [
                'source_module' => 'SALES',
                'source_type' => 'FAKTUR_PENJUALAN',
                'source_id' => $noFaktur,
                'source_no' => $noFaktur,
            ], 'Faktur final tidak ditemukan atau sudah dibatalkan.', ['SOURCE_NOT_POSTABLE']);
        }

        $items = $this->CI->db->query(
            "SELECT d.*, b.kelompok_dagang, b.kode_akun_penjualan, b.kode_akun_harga_pokok, b.kode_akun_persediaan
             FROM tbso_faktur_detail d
             LEFT JOIN tbpo_barang b ON d.kd_barang = b.kode_barang
             WHERE d.id_faktur = ? AND d.no_faktur = ?",
            [(int)$header->id_faktur, $noFaktur]
        )->result();

        if (empty($items)) {
            return $this->record_failure('SALES_INVOICE', [
                'source_module' => 'SALES',
                'source_type' => 'FAKTUR_PENJUALAN',
                'source_id' => $noFaktur,
                'source_no' => $noFaktur,
            ], 'Detail atau nilai faktur final belum valid.', ['SOURCE_AMOUNT_INVALID']);
        }

        $kodes = [];
        foreach ($items as $item) {
            if (!empty($item->kode_akun_penjualan)) $kodes[] = $item->kode_akun_penjualan;
            if (!empty($item->kode_akun_harga_pokok)) $kodes[] = $item->kode_akun_harga_pokok;
            if (!empty($item->kode_akun_persediaan)) $kodes[] = $item->kode_akun_persediaan;
        }
        $akunMap = [];
        if (!empty($kodes)) {
            $akuns = $this->CI->db->where_in('kode_akun', array_unique($kodes))->get('tbkeu_akun')->result();
            foreach ($akuns as $a) {
                $akunMap[$a->kode_akun] = $a->id_akun;
            }
        }

        $normal_amount = 0.0;
        $normal_cogs = 0.0;
        $promo_value = 0.0;
        $taxRate = 0.0;

        $is_bkps = false;
        $groupRev = [];
        $groupCogs = [];
        $groupInv = [];
        $groupPromoCogs = [];
        $groupPromoInv = [];

        foreach ($items as $item) {
            $kelompok = (int)($item->kelompok_dagang ?? 0);
            
            $subtotal = (float)$item->subtotal_after_disc;
            $cogs = (float)$item->qty * (float)$item->hrg_pokok;
            
            $normal_amount += $subtotal;
            $normal_cogs += $cogs;
            if ((float)$item->pajak > $taxRate) {
                $taxRate = (float)$item->pajak;
            }

            if ($kelompok === 5) {
                $promo_value += $cogs;
                
                $id_cogs = isset($akunMap[$item->kode_akun_harga_pokok]) ? $akunMap[$item->kode_akun_harga_pokok] : 0;
                if (!isset($groupPromoCogs[$id_cogs])) $groupPromoCogs[$id_cogs] = 0.0;
                $groupPromoCogs[$id_cogs] += $cogs;
                
                $id_inv = isset($akunMap[$item->kode_akun_persediaan]) ? $akunMap[$item->kode_akun_persediaan] : 0;
                if (!isset($groupPromoInv[$id_inv])) $groupPromoInv[$id_inv] = 0.0;
                $groupPromoInv[$id_inv] += $cogs;
            }

            $id_rev = isset($akunMap[$item->kode_akun_penjualan]) ? $akunMap[$item->kode_akun_penjualan] : 0;
            if (!isset($groupRev[$id_rev])) $groupRev[$id_rev] = 0.0;
            $groupRev[$id_rev] += $subtotal;

            if ($kelompok !== 5) {
                $id_cogs = isset($akunMap[$item->kode_akun_harga_pokok]) ? $akunMap[$item->kode_akun_harga_pokok] : 0;
                if (!isset($groupCogs[$id_cogs])) $groupCogs[$id_cogs] = 0.0;
                $groupCogs[$id_cogs] += $cogs;
                
                $id_inv = isset($akunMap[$item->kode_akun_persediaan]) ? $akunMap[$item->kode_akun_persediaan] : 0;
                if (!isset($groupInv[$id_inv])) $groupInv[$id_inv] = 0.0;
                $groupInv[$id_inv] += $cogs;
            }

            if ($kelompok > 0) {
                $group = $this->CI->db->get_where('tbkeu_kelompok_dagang', ['NOINDEX' => $kelompok])->row();
                if ($group) {
                    $desc = strtoupper(trim($group->DESKRIPSI));
                    if (strpos($desc, 'BKPS') !== false) {
                        $is_bkps = true;
                    }
                }
            }
        }

        $base = [
            'tanggal_transaksi' => $header->tanggal_faktur,
            'source_module' => 'SALES',
            'source_type' => 'FAKTUR_PENJUALAN',
            'source_id' => $noFaktur,
            'source_no' => $noFaktur,
            'scope_type' => 'WAREHOUSE',
            'scope_key' => trim((string)$header->gudang_id) !== '' ? (string)$header->gudang_id : '*',
            'tanggal_jatuh_tempo' => $header->tanggal_jatuh_tempo,
            'keterangan' => 'Faktur penjualan ' . $noFaktur . ($kdDo !== '' ? ' / DO ' . $kdDo : ''),
        ];

        $is_pajak = $this->CI->db->query(
            "SELECT COUNT(*) AS count FROM tbso_faktur_detail WHERE id_faktur = ? AND kd_barang LIKE 'Q%'",
            [(int)$header->id_faktur]
        )->row()->count > 0;

        $is_promosi = $this->CI->db->query(
            "SELECT COUNT(*) AS count FROM tbso_faktur_detail WHERE id_faktur = ? AND kd_barang LIKE 'Z%'",
            [(int)$header->id_faktur]
        )->row()->count > 0;

        $is_dagangan = $this->CI->db->query(
            "SELECT COUNT(*) AS count FROM tbso_faktur_detail WHERE id_faktur = ? AND kd_barang LIKE 'A%'",
            [(int)$header->id_faktur]
        )->row()->count > 0;

        $invoice_data = null;
        if ($normal_amount > 0) {
            $amount = 0.0;
            $tax = 0.0;
            $divFactor = ($taxRate > 0) ? 1 + ($taxRate / 100) : 1;
            $sales_revenue_lines = [];
            
            foreach ($groupRev as $id_akun => $val) {
                $line_amt = round($val / $divFactor, 4);
                $amount += $line_amt;
                if ($id_akun > 0) {
                    $sales_revenue_lines[] = ['amount' => $line_amt, 'id_akun' => $id_akun];
                }
            }
            $tax = round($normal_amount - $amount, 4);

            $invoice = $this->CI->accounting_service->post_auto('SALES_INVOICE', $base + [
                'idempotency_key' => 'SALES_INVOICE-FAKTUR-' . $noFaktur,
                'amount' => $amount,
                'tax' => $tax,
                'cogs' => '0.0000',
                'is_pajak' => $is_pajak,
                'is_bkps' => $is_bkps,
                'is_promosi' => $is_promosi,
                'is_dagangan' => $is_dagangan,
                'sales_revenue_lines' => $sales_revenue_lines
            ], $userId);
            if (!$invoice['success']) {
                return $invoice;
            }
            $invoice_data = $invoice['data'];
        }

        $issue_data = null;
        if ($postGoodsIssue && $normal_cogs > 0) {
            $standard_cogs = $normal_cogs - $promo_value;
            if ($standard_cogs > 0) {
                $cogs_lines = [];
                $inventory_lines = [];
                foreach ($groupCogs as $id_akun => $val) {
                    if ($id_akun > 0) $cogs_lines[] = ['amount' => $val, 'id_akun' => $id_akun];
                }
                foreach ($groupInv as $id_akun => $val) {
                    if ($id_akun > 0) $inventory_lines[] = ['amount' => $val, 'id_akun' => $id_akun];
                }

                $issue = $this->CI->accounting_service->post_auto('GOODS_ISSUE', $base + [
                    'idempotency_key' => 'GOODS_ISSUE-FAKTUR-' . $noFaktur,
                    'amount' => '0.0000',
                    'tax' => '0.0000',
                    'cogs' => $standard_cogs,
                    'cogs_lines' => $cogs_lines,
                    'inventory_lines' => $inventory_lines
                ], $userId);
                if ($issue['success']) {
                    $issue_data = $issue['data'];
                }
            }
        }

        // Post Journal for Promotional Items
        if ($promo_value > 0) {
            $this->CI->load->model('M_Journal');
            $nomor_jurnal_prm = $this->CI->M_Journal->generate_no_jurnal('promosi', 'PRM');
            
            $jurnal_prm_data = [
                'nomor_jurnal' => $nomor_jurnal_prm,
                'tanggal_transaksi' => $header->tanggal_faktur,
                'keterangan' => 'Promosi Penjualan Faktur ' . $noFaktur,
                'status' => 'POSTED',
                'source_module' => 'SALES',
                'source_type' => 'PROMOSI_PENJUALAN',
                'source_id' => $header->id_faktur,
                'source_no' => $noFaktur,
                'total_debit' => $promo_value,
                'total_kredit' => $promo_value,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'posted_by' => $userId,
                'posted_at' => date('Y-m-d H:i:s')
            ];
            
            $this->CI->db->insert('tbkeu_jurnal', $jurnal_prm_data);
            $id_jurnal_prm = $this->CI->db->insert_id();

            $baris = 1;
            
            // Debit: Biaya Promosi
            foreach ($groupPromoCogs as $id_akun => $val) {
                $akun_biaya = ($id_akun > 0) ? $id_akun : 360; // fallback to 360
                $this->CI->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal' => $id_jurnal_prm,
                    'nomor_baris' => $baris++,
                    'id_akun' => $akun_biaya,
                    'keterangan' => 'Biaya Promosi Faktur ' . $noFaktur,
                    'debit' => $val,
                    'kredit' => 0
                ]);
            }
            
            // Kredit: Persediaan Barang Promosi
            foreach ($groupPromoInv as $id_akun => $val) {
                $akun_persediaan = ($id_akun > 0) ? $id_akun : 226; // fallback to 226
                $this->CI->db->insert('tbkeu_jurnal_detail', [
                    'id_jurnal' => $id_jurnal_prm,
                    'nomor_baris' => $baris++,
                    'id_akun' => $akun_persediaan,
                    'keterangan' => 'Persediaan Barang Promosi Faktur ' . $noFaktur,
                    'debit' => 0,
                    'kredit' => $val
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'Faktur penjualan berhasil diposting.',
            'data' => ['sales_invoice' => $invoice_data, 'goods_issue' => $issue_data],
            'errors' => [],
        ];
    }

    public function post_goods_receipt($idLpb, $userId = null)
    {
        $idLpb = (int)$idLpb;
        $header = $this->CI->db->where('id_lpb', $idLpb)->get('tb_lpb')->row();
        if (!$header) {
            return $this->fail('LPB final tidak ditemukan.', ['SOURCE_NOT_FOUND']);
        }

        $totals = $this->CI->db->query(
            "SELECT COUNT(*) AS line_count,
                    SUM(CASE WHEN d.harga_verified_at IS NULL THEN 1 ELSE 0 END) AS unverified_count,
                    SUM(
                        CASE
                            WHEN COALESCE(
                                NULLIF(d.harga_satuan, 0),
                                NULLIF(pp.harga_satuan_kecil_exclude, 0),
                                NULLIF(pp.harga_satuan_exclude, 0)
                            ) IS NULL THEN 1
                            ELSE 0
                        END
                    ) AS unresolved_count,
                    COALESCE(
                        SUM(
                            COALESCE(
                                NULLIF(d.total_harga, 0),
                                d.qty_diterima * COALESCE(
                                    NULLIF(d.harga_satuan, 0),
                                    NULLIF(pp.harga_satuan_kecil_exclude, 0),
                                    NULLIF(pp.harga_satuan_exclude, 0),
                                    0
                                )
                            )
                        ),
                        0
                    ) AS amount
             FROM tb_lpb h
             INNER JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
             LEFT JOIN tbpo_detail_po pp
               ON pp.no_po = h.no_po
              AND pp.kd_po = h.kd_po
              AND pp.kd_barang = d.kd_barang
             WHERE h.id_lpb = ?",
            [$idLpb]
        )->row();

        $payload = [
            'tanggal_transaksi' => $header->tgl_sj ?: date('Y-m-d'),
            'keterangan' => 'LPB ' . $idLpb . ' / PO ' . $header->no_po,
            'source_module' => 'LOGISTIK',
            'source_type' => 'LPB_FINAL',
            'source_id' => (string)$idLpb,
            'source_no' => trim((string)$header->no_invoice) !== '' ? $header->no_invoice : $header->no_po,
            'idempotency_key' => 'GOODS_RECEIPT-LPB-' . $idLpb,
            'scope_type' => 'WAREHOUSE',
            'scope_key' => (string)$header->gudang_id,
            'amount' => $totals ? $totals->amount : '0.0000',
            'tax' => '0.0000',
            'cogs' => '0.0000',
        ];

        if (!$totals || (int)$totals->line_count < 1 || (int)$totals->unresolved_count > 0 || bccomp((string)$totals->amount, '0', 4) <= 0) {
            return $this->record_failure(
                'GOODS_RECEIPT',
                $payload,
                'Harga perolehan LPB belum tersedia untuk seluruh detail. Lengkapi harga dari PO/detail LPB sebelum retry.',
                ['LPB_COST_UNRESOLVED']
            );
        }

        if ((int)$totals->unverified_count > 0) {
            return $this->record_failure(
                'GOODS_RECEIPT',
                $payload,
                'Harga detail LPB belum diverifikasi seluruhnya. Verifikasi harga LPB, lalu retry posting.',
                ['LPB_PRICE_UNVERIFIED']
            );
        }

        return $this->CI->accounting_service->post_auto('GOODS_RECEIPT', $payload, $userId);
    }

    private function record_failure($event, $payload, $message, $errors)
    {
        $this->CI->accounting_service->capture_posting_exception($event, $payload, $message, $errors);
        return $this->fail($message, $errors);
    }

    private function fail($message, $errors)
    {
        return ['success' => false, 'message' => $message, 'data' => null, 'errors' => $errors];
    }
}
