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

        if (!$postGoodsIssue) {
            return [
                'success' => true,
                'message' => 'Jurnal faktur penjualan berhasil diposting.',
                'data' => ['sales_invoice' => $invoice['data'], 'goods_issue' => null],
                'errors' => [],
            ];
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
        $this->CI->db->select("h.*, p.kd_suplier, COALESCE(s.id_suplier, 0) AS id_supplier_source, COALESCE(s.nama_suplier, '') AS nama_suplier", false);
        $this->CI->db->from('tb_lpb h');
        $this->CI->db->join('tbpo_po p', 'p.kd_po = h.kd_po AND p.no_po = h.no_po', 'left');
        $this->CI->db->join('tbpo_suplier s', 's.kd_suplier = p.kd_suplier', 'left');
        $this->CI->db->where('h.id_lpb', $idLpb);
        $header = $this->CI->db->get()->row();
        if (!$header) {
            return $this->fail('LPB final tidak ditemukan.', ['SOURCE_NOT_FOUND']);
        }

        $nomorLpb = trim((string)($header->nomor_lpb ?? ''));
        $sourceNo = $nomorLpb !== '' ? $nomorLpb : trim((string)$header->no_po);
        $payload = [
            'tanggal_transaksi' => $header->tgl_sj ?: date('Y-m-d'),
            'journal_type' => 'PJ',
            'keterangan' => 'Pembelian, ' . (trim((string)$header->nama_suplier) !== '' ? $header->nama_suplier : 'Supplier'),
            'source_module' => 'LOGISTIK',
            'source_type' => 'LPB_FINAL',
            'source_id' => (string)$idLpb,
            'source_no' => $sourceNo,
            'idempotency_key' => 'GOODS_RECEIPT-LPB-' . $idLpb,
            'scope_type' => 'WAREHOUSE',
            'scope_key' => (string)$header->gudang_id,
            'id_supplier' => (int)($header->id_supplier_source ?? 0),
            'id_gudang' => (int)$header->gudang_id,
        ];

        if ($nomorLpb === '') {
            return $this->record_failure(
                'GOODS_RECEIPT',
                $payload,
                'Nomor LPB belum tersedia. Jurnal pembelian hanya terbit setelah nomor LPB final terbentuk.',
                ['LPB_NUMBER_REQUIRED']
            );
        }

        $totals = $this->CI->db->query(
            "SELECT COUNT(*) AS line_count,
                    SUM(CASE WHEN COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '') NOT IN ('2', '3') THEN 1 ELSE 0 END) AS unsupported_count,
                    SUM(CASE
                        WHEN COALESCE(NULLIF(d.total_harga, 0), 0) <= 0
                         AND COALESCE(
                            NULLIF(d.harga_satuan, 0),
                            NULLIF(pp.harga_satuan_kecil_exclude, 0),
                            NULLIF(pp.harga_satuan_exclude, 0)
                         ) IS NULL
                        THEN 1 ELSE 0
                    END) AS unresolved_count,
                    COALESCE(SUM(CASE WHEN COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '') = '2' THEN
                        COALESCE(
                            NULLIF(d.total_harga, 0),
                            d.qty_diterima * COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0)
                        )
                    ELSE 0 END), 0) AS amount_bkp,
                    COALESCE(SUM(CASE WHEN COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '') = '3' THEN
                        COALESCE(
                            NULLIF(d.total_harga, 0),
                            d.qty_diterima * COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0)
                        )
                    ELSE 0 END), 0) AS amount_bkps
             FROM tb_lpb h
             INNER JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
             LEFT JOIN tbpo_detail_po pp
               ON pp.no_po = h.no_po
              AND pp.kd_po = h.kd_po
              AND pp.kd_barang = d.kd_barang
             LEFT JOIN tbpo_barang b ON b.kode_barang = d.kd_barang
             WHERE h.id_lpb = ?",
            [$idLpb]
        )->row();

        if (!$totals || (int)$totals->line_count < 1 || (int)$totals->unresolved_count > 0) {
            return $this->record_failure(
                'GOODS_RECEIPT',
                $payload,
                'Harga perolehan LPB belum tersedia untuk seluruh detail. Lengkapi harga dari PO/detail LPB sebelum retry.',
                ['LPB_COST_UNRESOLVED']
            );
        }

        if ((int)$totals->unsupported_count > 0) {
            return $this->record_failure(
                'GOODS_RECEIPT',
                $payload,
                'Kelompok dagang LPB belum masuk rule hardcode pembelian. Saat ini hanya kelompok dagang 2 BKP dan 3 BKPS.',
                ['LPB_PURCHASE_GROUP_UNSUPPORTED']
            );
        }

        $amountBkp = $this->money($totals->amount_bkp ?? 0);
        $amountBkps = $this->money($totals->amount_bkps ?? 0);
        $vatBkp = $this->money(bcdiv(bcmul($amountBkp, '11', 4), '100', 4));
        $totalPayable = $this->money(bcadd(bcadd($amountBkp, $vatBkp, 4), $amountBkps, 4));

        if (bccomp($totalPayable, '0', 4) <= 0) {
            return $this->record_failure(
                'GOODS_RECEIPT',
                $payload,
                'Nilai LPB pembelian tidak valid untuk diposting.',
                ['LPB_AMOUNT_INVALID']
            );
        }

        $lines = [];
        if (bccomp($amountBkp, '0', 4) === 1) {
            $lines[] = $this->purchase_line_by_code('14010', 'Persediaan # 1', $amountBkp, '0.0000', $payload);
            $lines[] = $this->purchase_line_by_code('13017', 'Q PPN M Ymh Diterima', $vatBkp, '0.0000', $payload);
        }
        if (bccomp($amountBkps, '0', 4) === 1) {
            $lines[] = $this->purchase_line_by_code('14011', 'Q Persediaan Brg Dagangan BKPS', $amountBkps, '0.0000', $payload);
        }
        $lines[] = $this->purchase_line_by_code('21098', 'Hutang Usaha', '0.0000', $totalPayable, $payload);

        foreach ($lines as $line) {
            if (empty($line['id_akun'])) {
                return $this->record_failure(
                    'GOODS_RECEIPT',
                    $payload,
                    'Akun hardcode jurnal pembelian LPB belum valid di Chart of Accounts.',
                    ['LPB_PURCHASE_ACCOUNT_NOT_FOUND']
                );
            }
        }

        $payload['amount'] = bcadd($amountBkp, $amountBkps, 4);
        $payload['tax'] = $vatBkp;
        $payload['cogs'] = '0.0000';
        $payload['lines'] = $lines;

        return $this->CI->accounting_service->post_auto('GOODS_RECEIPT', $payload, $userId);
    }

    private function purchase_line_by_code($kodeAkun, $label, $debit, $kredit, $payload)
    {
        return [
            'id_akun' => $this->account_id_by_code($kodeAkun),
            'keterangan' => $label,
            'debit' => $this->money($debit),
            'kredit' => $this->money($kredit),
            'id_customer' => 0,
            'id_supplier' => (int)($payload['id_supplier'] ?? 0),
            'id_barang' => 0,
            'id_gudang' => (int)($payload['id_gudang'] ?? 0),
            'id_departemen' => 0,
            'tanggal_jatuh_tempo' => '',
            'nomor_dokumen' => trim((string)($payload['source_no'] ?? '')),
        ];
    }

    private function account_id_by_code($kodeAkun)
    {
        $row = $this->CI->db
            ->select('id_akun')
            ->where('kode_akun', trim((string)$kodeAkun))
            ->get('tbkeu_akun')
            ->row();

        return $row ? (int)$row->id_akun : 0;
    }

    private function money($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '0.0000';
        }

        $value = str_replace(' ', '', $value);
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        if (!preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            return '0.0000';
        }

        return bcadd($value, '0', 4);
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
