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
            "SELECT d.*, b.kelompok_dagang
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

        $normal_amount = 0.0;
        $normal_cogs = 0.0;
        $promo_value = 0.0;
        $taxRate = 0.0;

        $is_bkps = false;
        foreach ($items as $item) {
            $kelompok = (int)($item->kelompok_dagang ?? 0);
            if ($kelompok === 5) {
                // Promotional item
                $promo_value += (float)$item->qty * (float)$item->hrg_pokok;
            } else {
                // Normal item
                $normal_amount += (float)$item->subtotal_after_disc;
                $normal_cogs += (float)$item->qty * (float)$item->hrg_pokok;
                if ((float)$item->pajak > $taxRate) {
                    $taxRate = (float)$item->pajak;
                }
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

        $invoice_data = null;
        if ($normal_amount > 0) {
            $amount = 0.0;
            $tax = 0.0;
            if ($taxRate > 0) {
                $divFactor = 1 + ($taxRate / 100);
                $amount = round($normal_amount / $divFactor, 4);
                $tax = round($normal_amount - $amount, 4);
            } else {
                $amount = $normal_amount;
                $tax = 0.0000;
            }

            $invoice = $this->CI->accounting_service->post_auto('SALES_INVOICE', $base + [
                'idempotency_key' => 'SALES_INVOICE-FAKTUR-' . $noFaktur,
                'amount' => $amount,
                'tax' => $tax,
                'cogs' => '0.0000',
                'is_pajak' => $is_pajak,
                'is_bkps' => $is_bkps,
            ], $userId);
            if (!$invoice['success']) {
                return $invoice;
            }
            $invoice_data = $invoice['data'];
        }

        $issue_data = null;
        if ($postGoodsIssue && $normal_cogs > 0) {
            $issue = $this->CI->accounting_service->post_auto('GOODS_ISSUE', $base + [
                'idempotency_key' => 'GOODS_ISSUE-FAKTUR-' . $noFaktur,
                'amount' => '0.0000',
                'tax' => '0.0000',
                'cogs' => $normal_cogs,
            ], $userId);
            if ($issue['success']) {
                $issue_data = $issue['data'];
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

            // Resolve A Biaya Promosi (61036) dynamically
            $akun_biaya = $this->CI->db->get_where('tbkeu_akun', ['kode_akun' => '61036'])->row_array();
            $id_akun_biaya = $akun_biaya ? $akun_biaya['id_akun'] : 360;

            // Resolve A Persediaan Barang Promosi (14031) dynamically
            $akun_persediaan = $this->CI->db->get_where('tbkeu_akun', ['kode_akun' => '14031'])->row_array();
            $id_akun_persediaan = $akun_persediaan ? $akun_persediaan['id_akun'] : 226;
            
            // Debit: A Biaya Promosi
            $this->CI->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal_prm,
                'nomor_baris' => 1,
                'id_akun' => $id_akun_biaya,
                'keterangan' => 'Biaya Promosi Faktur ' . $noFaktur,
                'debit' => $promo_value,
                'kredit' => 0
            ]);
            
            // Kredit: A Persediaan Barang Promosi
            $this->CI->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $id_jurnal_prm,
                'nomor_baris' => 2,
                'id_akun' => $id_akun_persediaan,
                'keterangan' => 'Persediaan Barang Promosi Faktur ' . $noFaktur,
                'debit' => 0,
                'kredit' => $promo_value
            ]);
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
