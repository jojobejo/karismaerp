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

    public function post_sales_invoice($noFaktur, $kdDo = '', $userId = null)
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

        $totals = $this->CI->db->query(
            "SELECT COUNT(*) AS line_count,
                    COALESCE(SUM(subtotal_after_disc), 0) AS amount,
                    COALESCE(SUM(total_harga - subtotal_after_disc), 0) AS tax,
                    COALESCE(SUM(qty * hrg_pokok), 0) AS cogs
             FROM tbso_faktur_detail
             WHERE id_faktur = ? AND no_faktur = ?",
            [(int)$header->id_faktur, $noFaktur]
        )->row();
        if (!$totals || (int)$totals->line_count < 1 || bccomp((string)$totals->amount, '0', 4) <= 0) {
            return $this->record_failure('SALES_INVOICE', [
                'source_module' => 'SALES',
                'source_type' => 'FAKTUR_PENJUALAN',
                'source_id' => $noFaktur,
                'source_no' => $noFaktur,
            ], 'Detail atau nilai faktur final belum valid.', ['SOURCE_AMOUNT_INVALID']);
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

        $invoice = $this->CI->accounting_service->post_auto('SALES_INVOICE', $base + [
            'idempotency_key' => 'SALES_INVOICE-FAKTUR-' . $noFaktur,
            'amount' => $totals->amount,
            'tax' => $totals->tax,
            'cogs' => '0.0000',
        ], $userId);
        if (!$invoice['success']) {
            return $invoice;
        }

        $issue = $this->CI->accounting_service->post_auto('GOODS_ISSUE', $base + [
            'idempotency_key' => 'GOODS_ISSUE-FAKTUR-' . $noFaktur,
            'amount' => '0.0000',
            'tax' => '0.0000',
            'cogs' => $totals->cogs,
        ], $userId);

        return [
            'success' => $issue['success'],
            'message' => $issue['success'] ? 'Faktur dan pengeluaran persediaan berhasil diposting.' : $issue['message'],
            'data' => ['sales_invoice' => $invoice['data'], 'goods_issue' => $issue['data']],
            'errors' => $issue['errors'],
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
