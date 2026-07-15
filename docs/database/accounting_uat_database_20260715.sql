-- KARISMA ERP - UAT Database Accounting (READ ONLY)
-- Expected: seluruh test berstatus PASS. Script ini tidak mengubah data.

SET NAMES utf8mb4;
SET @db := DATABASE();
SET @cutover_date := '2026-07-15';

SELECT 'DB-01 tabel inti lengkap' AS test_case,
       IF(COUNT(*) = 13, 'PASS', CONCAT('FAIL: ', COUNT(*), '/13 tabel')) AS result
FROM information_schema.tables
WHERE table_schema = @db AND table_name IN (
  'tbkeu_akun','tbkeu_periode_fiskal','tbkeu_periode_fiskal_log','tbkeu_jenis_jurnal',
  'tbkeu_jurnal','tbkeu_jurnal_detail','tbkeu_jurnal_log','tbkeu_mapping_akun',
  'tbkeu_posting_exception','tbkeu_nomor_dokumen','tbkeu_saldo_awal_akun',
  'tbkeu_pembayaran','tbkeu_pembayaran_alokasi'
);

SELECT 'DB-02 jurnal header balance' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' jurnal')) AS result
FROM tbkeu_jurnal
WHERE total_debit <> total_kredit OR total_debit < 0 OR total_kredit < 0;

SELECT 'DB-03 header sama dengan detail' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' jurnal')) AS result
FROM (
  SELECT j.id_jurnal
  FROM tbkeu_jurnal j
  LEFT JOIN tbkeu_jurnal_detail d ON d.id_jurnal = j.id_jurnal
  GROUP BY j.id_jurnal, j.total_debit, j.total_kredit
  HAVING j.total_debit <> COALESCE(SUM(d.debit),0)
      OR j.total_kredit <> COALESCE(SUM(d.kredit),0)
) x;

SELECT 'DB-04 detail satu sisi dan nonnegatif' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' baris')) AS result
FROM tbkeu_jurnal_detail
WHERE debit < 0 OR kredit < 0 OR (debit > 0 AND kredit > 0) OR (debit = 0 AND kredit = 0);

SELECT 'DB-05 nomor dan idempotency unik' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' duplikat')) AS result
FROM (
  SELECT nomor_jurnal FROM tbkeu_jurnal GROUP BY nomor_jurnal HAVING COUNT(*) > 1
  UNION ALL
  SELECT idempotency_key FROM tbkeu_jurnal WHERE idempotency_key IS NOT NULL
  GROUP BY idempotency_key HAVING COUNT(*) > 1
) x;

SELECT 'DB-06 referential orphan' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' orphan')) AS result
FROM tbkeu_jurnal_detail d
LEFT JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
LEFT JOIN tbkeu_akun a ON a.id_akun = d.id_akun
WHERE j.id_jurnal IS NULL OR a.id_akun IS NULL;

SELECT 'DB-07 periode tidak overlap' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' pasangan')) AS result
FROM tbkeu_periode_fiskal a
JOIN tbkeu_periode_fiskal b ON a.id_periode < b.id_periode
 AND a.tanggal_mulai <= b.tanggal_selesai
 AND a.tanggal_selesai >= b.tanggal_mulai;

SELECT 'DB-08 posted berada dalam periode' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' jurnal')) AS result
FROM tbkeu_jurnal j
LEFT JOIN tbkeu_periode_fiskal p ON p.id_periode = j.id_periode
WHERE j.status = 'POSTED'
  AND (p.id_periode IS NULL OR j.tanggal_transaksi < p.tanggal_mulai OR j.tanggal_transaksi > p.tanggal_selesai);

SELECT 'DB-09 reversal lengkap dan balance neto' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' reversal')) AS result
FROM (
  SELECT o.id_jurnal
  FROM tbkeu_jurnal o
  LEFT JOIN tbkeu_jurnal r ON r.reversal_of_journal_id = o.id_jurnal AND r.status = 'POSTED'
  WHERE o.reversed_at IS NOT NULL
  GROUP BY o.id_jurnal, o.total_debit, o.total_kredit
  HAVING COUNT(r.id_jurnal) <> 1
     OR COALESCE(SUM(r.total_debit),0) <> o.total_kredit
     OR COALESCE(SUM(r.total_kredit),0) <> o.total_debit
) x;

SELECT 'DB-10 tidak ada status reversal legacy' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' jurnal')) AS result
FROM tbkeu_jurnal WHERE status = 'REVERSED';

SELECT 'DB-11 mapping event wajib lengkap' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' role')) AS result
FROM (
  SELECT 'SALES_INVOICE' event_name, 'ACCOUNT_RECEIVABLE' role_name, 'DEBIT' side_name UNION ALL
  SELECT 'SALES_INVOICE','SALES_REVENUE','KREDIT' UNION ALL
  SELECT 'GOODS_ISSUE','COGS','DEBIT' UNION ALL
  SELECT 'GOODS_ISSUE','INVENTORY','KREDIT' UNION ALL
  SELECT 'GOODS_RECEIPT','INVENTORY','DEBIT' UNION ALL
  SELECT 'GOODS_RECEIPT','GRNI','KREDIT' UNION ALL
  SELECT 'CUSTOMER_PAYMENT','CASH_BANK','DEBIT' UNION ALL
  SELECT 'CUSTOMER_PAYMENT','ACCOUNT_RECEIVABLE','KREDIT' UNION ALL
  SELECT 'SUPPLIER_PAYMENT','ACCOUNT_PAYABLE','DEBIT' UNION ALL
  SELECT 'SUPPLIER_PAYMENT','CASH_BANK','KREDIT'
) req
LEFT JOIN tbkeu_mapping_akun m ON m.posting_event=req.event_name
 AND m.account_role=req.role_name AND m.entry_side=req.side_name AND m.is_active=1
WHERE m.id_mapping IS NULL;

SELECT 'DB-12 mapping menuju akun posting aktif' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' mapping')) AS result
FROM tbkeu_mapping_akun m
LEFT JOIN tbkeu_akun a ON a.id_akun=m.id_akun
WHERE m.is_active=1 AND (a.id_akun IS NULL OR a.is_active<>1 OR a.tipe_akun<>'POSTING');

SELECT 'DB-13 sales invoice rekonsiliasi sumber' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' faktur')) AS result
FROM (
  SELECT f.no_faktur,
         COALESCE(SUM(fd.total_harga),0) source_total,
         COALESCE(MAX(j.total_debit),0) journal_total
  FROM tbso_faktur_penjualan f
  JOIN tbso_faktur_detail fd ON fd.id_faktur=f.id_faktur
  JOIN tbkeu_jurnal j ON j.source_module='SALES' AND j.source_type='FAKTUR_PENJUALAN'
   AND j.source_id=f.no_faktur AND j.posting_event='SALES_INVOICE' AND j.status='POSTED'
  WHERE f.status<>'cancelled'
  GROUP BY f.no_faktur
  HAVING source_total <> journal_total
) x;

SELECT 'DB-14 goods issue rekonsiliasi HPP' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' faktur')) AS result
FROM (
  SELECT f.no_faktur,
         COALESCE(SUM(fd.qty*fd.hrg_pokok),0) source_cogs,
         COALESCE(MAX(j.total_debit),0) journal_cogs
  FROM tbso_faktur_penjualan f
  JOIN tbso_faktur_detail fd ON fd.id_faktur=f.id_faktur
  JOIN tbkeu_jurnal j ON j.source_module='SALES' AND j.source_type='FAKTUR_PENJUALAN'
   AND j.source_id=f.no_faktur AND j.posting_event='GOODS_ISSUE' AND j.status='POSTED'
  WHERE f.status<>'cancelled'
  GROUP BY f.no_faktur
  HAVING source_cogs <> journal_cogs
) x;

SELECT 'DB-15 pembayaran dan alokasi konsisten' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' pembayaran')) AS result
FROM (
  SELECT p.id_pembayaran
  FROM tbkeu_pembayaran p
  LEFT JOIN tbkeu_pembayaran_alokasi a ON a.id_pembayaran=p.id_pembayaran
  GROUP BY p.id_pembayaran,p.amount,p.allocated_amount,p.unapplied_amount
  HAVING p.amount <> p.allocated_amount+p.unapplied_amount
      OR p.allocated_amount <> COALESCE(SUM(a.amount_allocated),0)
) x;

SELECT 'DB-16 qty on-hand stock sama dengan ledger' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' batch')) AS result
FROM (
  SELECT b.id
  FROM tberp_stock_batch b
  LEFT JOIN (
    SELECT kd_barang,gudang_id,no_lot,expired_date,
           SUM(CASE
                 WHEN tipe IN ('SALDO_AWAL','IN','RBELI','RJUAL','ADJIN') THEN qty
                 WHEN tipe IN ('OUT','ADJOUT') THEN -qty
                 ELSE 0
               END) qty_ledger
    FROM tberp_stock_ledger
    GROUP BY kd_barang,gudang_id,no_lot,expired_date
  ) l ON l.kd_barang=b.kd_barang AND l.gudang_id=b.gudang_id
     AND l.no_lot=b.no_lot AND l.expired_date <=> b.expired_date
  WHERE b.qty_on_hand <> COALESCE(l.qty_ledger,0)
) x;

SELECT 'DB-16B qty reserved stock (domain stok)' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('REVIEW: ', COUNT(*), ' batch; rekonsiliasi modul stok')) AS result
FROM (
  SELECT b.id
  FROM tberp_stock_batch b
  LEFT JOIN (
    SELECT kd_barang,gudang_id,no_lot,expired_date,
           SUM(CASE WHEN tipe='RESERVE' THEN qty WHEN tipe='RELEASE' THEN -qty ELSE 0 END) qty_reserved
    FROM tberp_stock_ledger
    GROUP BY kd_barang,gudang_id,no_lot,expired_date
  ) l ON l.kd_barang=b.kd_barang AND l.gudang_id=b.gudang_id
     AND l.no_lot=b.no_lot AND l.expired_date <=> b.expired_date
  WHERE b.qty_reserved <> COALESCE(l.qty_reserved,0)
) x;

SELECT 'DB-17 exception terbuka' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('REVIEW: ', COUNT(*), ' exception OPEN')) AS result
FROM tbkeu_posting_exception WHERE status='OPEN';

SELECT 'DB-18 faktur sejak cutover sudah diposting' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' faktur')) AS result
FROM tbso_faktur_penjualan f
LEFT JOIN tbkeu_jurnal js ON js.source_module='SALES' AND js.source_type='FAKTUR_PENJUALAN'
 AND js.source_id=f.no_faktur AND js.posting_event='SALES_INVOICE' AND js.status='POSTED'
LEFT JOIN tbkeu_jurnal jg ON jg.source_module='SALES' AND jg.source_type='FAKTUR_PENJUALAN'
 AND jg.source_id=f.no_faktur AND jg.posting_event='GOODS_ISSUE' AND jg.status='POSTED'
WHERE f.tanggal_faktur >= @cutover_date AND f.status<>'cancelled'
  AND (js.id_jurnal IS NULL OR jg.id_jurnal IS NULL);

SELECT 'DB-19 LPB sejak cutover posted atau exception' AS test_case,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FAIL: ', COUNT(*), ' LPB')) AS result
FROM tb_lpb l
LEFT JOIN tbkeu_jurnal j ON j.source_module='LOGISTIK' AND j.source_type='LPB_FINAL'
 AND j.source_id=CAST(l.id_lpb AS CHAR) AND j.posting_event='GOODS_RECEIPT' AND j.status='POSTED'
LEFT JOIN tbkeu_posting_exception e ON e.source_module='LOGISTIK' AND e.source_type='LPB_FINAL'
 AND e.source_id=CAST(l.id_lpb AS CHAR) AND e.posting_event='GOODS_RECEIPT' AND e.status='OPEN'
WHERE COALESCE(l.input_at,l.tgl_sj) >= @cutover_date
  AND j.id_jurnal IS NULL AND e.id_exception IS NULL;

SELECT 'DB-20 compatibility Sales/DO lengkap' AS test_case,
       IF(
         (SELECT COUNT(*) FROM information_schema.tables
          WHERE table_schema=@db AND table_name IN ('tbso_so_approval','tbso_stock_reservation'))=2
         AND (SELECT COUNT(*) FROM tbso_sales_order WHERE no_faktur IS NULL)=0
         AND (SELECT COUNT(*) FROM tbso_sales_order_detail WHERE no_faktur IS NULL)=0,
         'PASS', 'FAIL: migration sales/logistics belum lengkap'
       ) AS result;
