-- KARISMA ERP - Align Dummy Journal Data With Agro Distributor Context
-- Tanggal: 2026-07-13
-- Scope: memperbarui jurnal dummy testing lama agar route `jurnal` memakai konteks PT distributor agrobisnis.
-- File ini hanya menyentuh jurnal dummy dengan source_module/source_type CLI_TEST SALES_INVOICE.

SET NAMES utf8mb4;

SET @sales_source_no := 'INV-AGRO-20260713-001';
SET @reversal_source_no := 'REV-INV-AGRO-20260713-001';
SET @sales_note := 'Faktur penjualan Herbisida GulmaClean 1L 48 botol ke Koperasi Tani Subur Jaya';
SET @reversal_note := 'Reversal faktur penjualan Herbisida GulmaClean 1L karena koreksi dummy UAT accounting';
SET @sales_amount := 8880000.0000;
SET @sales_tax := 976800.0000;
SET @sales_cogs := 6144000.0000;
SET @sales_total := @sales_amount + @sales_tax;
SET @journal_total := @sales_total + @sales_cogs;

UPDATE tbkeu_jurnal
SET
  source_module = 'SALES_DISTRIBUTOR_AGRO',
  source_type = 'SALES_INVOICE',
  source_id = @sales_source_no,
  source_no = @sales_source_no,
  posting_event = 'SALES_INVOICE',
  keterangan = @sales_note,
  total_debit = @journal_total,
  total_kredit = @journal_total,
  updated_at = CURRENT_TIMESTAMP
WHERE id_jurnal = 1
  AND (
    source_module = 'CLI_TEST'
    OR source_no LIKE 'CLI-SALES-%'
    OR keterangan = 'CLI dummy sales invoice'
  );

UPDATE tbkeu_jurnal_detail jd
JOIN tbkeu_akun a ON a.id_akun = jd.id_akun
SET
  jd.keterangan = CASE a.kode_akun
    WHEN '1300' THEN CONCAT(@sales_note, ' - Piutang outlet/koperasi tani')
    WHEN '4100' THEN CONCAT(@sales_note, ' - Pendapatan penjualan obat tanaman')
    WHEN '2300' THEN CONCAT(@sales_note, ' - PPN keluaran')
    WHEN '5100' THEN CONCAT(@sales_note, ' - Harga pokok obat tanaman')
    WHEN '1400' THEN CONCAT(@sales_note, ' - Persediaan obat tanaman')
    ELSE jd.keterangan
  END,
  jd.debit = CASE a.kode_akun
    WHEN '1300' THEN @sales_total
    WHEN '5100' THEN @sales_cogs
    ELSE 0.0000
  END,
  jd.kredit = CASE a.kode_akun
    WHEN '4100' THEN @sales_amount
    WHEN '2300' THEN @sales_tax
    WHEN '1400' THEN @sales_cogs
    ELSE 0.0000
  END,
  jd.nomor_dokumen = @sales_source_no,
  jd.updated_at = CURRENT_TIMESTAMP
WHERE jd.id_jurnal = 1;

UPDATE tbkeu_jurnal
SET
  source_module = 'ACCOUNTING',
  source_type = 'REVERSAL',
  source_id = '1',
  source_no = @reversal_source_no,
  posting_event = 'REVERSAL',
  keterangan = @reversal_note,
  total_debit = @journal_total,
  total_kredit = @journal_total,
  updated_at = CURRENT_TIMESTAMP
WHERE id_jurnal = 2
  AND reversal_of_journal_id = 1;

UPDATE tbkeu_jurnal_detail jd
JOIN tbkeu_akun a ON a.id_akun = jd.id_akun
SET
  jd.keterangan = CASE a.kode_akun
    WHEN '1300' THEN CONCAT(@reversal_note, ' - Piutang outlet/koperasi tani')
    WHEN '4100' THEN CONCAT(@reversal_note, ' - Pendapatan penjualan obat tanaman')
    WHEN '2300' THEN CONCAT(@reversal_note, ' - PPN keluaran')
    WHEN '5100' THEN CONCAT(@reversal_note, ' - Harga pokok obat tanaman')
    WHEN '1400' THEN CONCAT(@reversal_note, ' - Persediaan obat tanaman')
    ELSE jd.keterangan
  END,
  jd.debit = CASE a.kode_akun
    WHEN '4100' THEN @sales_amount
    WHEN '2300' THEN @sales_tax
    WHEN '1400' THEN @sales_cogs
    ELSE 0.0000
  END,
  jd.kredit = CASE a.kode_akun
    WHEN '1300' THEN @sales_total
    WHEN '5100' THEN @sales_cogs
    ELSE 0.0000
  END,
  jd.nomor_dokumen = @reversal_source_no,
  jd.updated_at = CURRENT_TIMESTAMP
WHERE jd.id_jurnal = 2;

SELECT id_jurnal, nomor_jurnal, source_no, status, total_debit, total_kredit, keterangan
FROM tbkeu_jurnal
WHERE id_jurnal IN (1, 2)
ORDER BY id_jurnal;
