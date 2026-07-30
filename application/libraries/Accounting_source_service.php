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

        $customerName = '';
        if (!empty($header->kd_customer)) {
            $cust = $this->CI->db->select('nama_customer')->where('kd_customer', $header->kd_customer)->get('tb_customer')->row();
            if ($cust) {
                $customerName = trim($cust->nama_customer);
            }
        }

        $base = [
            'journal_type' => 'SJ',
            'tanggal_transaksi' => $header->tanggal_faktur,
            'source_module' => 'SALES',
            'source_type' => 'FAKTUR_PENJUALAN',
            'source_id' => $noFaktur,
            'source_no' => $noFaktur,
            'scope_type' => 'WAREHOUSE',
            'scope_key' => trim((string)$header->gudang_id) !== '' ? (string)$header->gudang_id : '*',
            'tanggal_jatuh_tempo' => $header->tanggal_jatuh_tempo,
            'keterangan' => 'Penjualan, ' . $customerName,
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

                $issue = $this->CI->accounting_service->post_auto('GOODS_ISSUE', array_merge($base, [
                    'keterangan' => 'Penyesuaian persediaan, untuk [NOMOR_JURNAL]',
                    'idempotency_key' => 'GOODS_ISSUE-FAKTUR-' . $noFaktur,
                    'amount' => '0.0000',
                    'tax' => '0.0000',
                    'cogs' => $standard_cogs,
                    'cogs_lines' => $cogs_lines,
                    'inventory_lines' => $inventory_lines
                ]), $userId);
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
