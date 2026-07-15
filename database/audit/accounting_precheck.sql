/*
FASE 0 - Accounting precheck read-only SQL
Database target: kiucoid_karismaerp_local

Rules:
- SELECT only.
- Do not run INSERT, UPDATE, DELETE, ALTER, DROP, TRUNCATE, CREATE, or migration commands.
- This file is safe for audit/precheck. It does not fix data.
*/

USE `kiucoid_karismaerp_local`;

/* 00. Confirm source tables exist */
SELECT
    TABLE_NAME,
    ENGINE,
    TABLE_COLLATION,
    TABLE_ROWS
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'tbso_sales_order',
      'tbso_sales_order_detail',
      'tb_customer',
      'tb_pre_do',
      'tb_do',
      'tb_detail_do',
      'tbkeu_pembayaran_faktur',
      'tb_pre_po',
      'tb_pre_po_invoice_adjustment',
      'tb_pre_po_diskon_history',
      'tb_po_received',
      'tb_tmp_po_received',
      'tb_lpb',
      'tb_lpb_detail',
      'tb_lpb_batch',
      'tb_suplier',
      'tbpo_suplier',
      'tbpo_po',
      'tbpo_detail_po',
      'tberp_stock_ledger',
      'tberp_stock_batch',
      'tb_mutasi',
      'tb_detail_mutasi',
      'tb_master_barang_all',
      'tb_gudang',
      'tb_gudang_wilayah',
      'stockopname_master_item',
      'stockopname_opname',
      'stockopname_pending',
      'stockopname_master_manual_item',
      'tbpo_transaksi',
      'tbpo_transaksi_tmp',
      'tbpo_transaksi_trashbin',
      'tbpo_akun_tr'
  )
ORDER BY TABLE_NAME;

/* 01. Confirm excluded PO transaction tables are isolated from accounting code by naming inventory only */
SELECT
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME,
    UPDATE_TIME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('tbpo_transaksi', 'tbpo_transaksi_tmp', 'tbpo_transaksi_trashbin', 'tbpo_akun_tr')
ORDER BY TABLE_NAME;

/* 02. Column type audit for key fields */
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_KEY,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'tbso_sales_order',
      'tbso_sales_order_detail',
      'tb_customer',
      'tb_pre_do',
      'tb_do',
      'tb_detail_do',
      'tbkeu_pembayaran_faktur',
      'tb_pre_po',
      'tb_pre_po_invoice_adjustment',
      'tb_po_received',
      'tb_tmp_po_received',
      'tb_lpb',
      'tb_lpb_detail',
      'tb_lpb_batch',
      'tb_suplier',
      'tbpo_suplier',
      'tbpo_po',
      'tbpo_detail_po',
      'tberp_stock_ledger',
      'tberp_stock_batch',
      'tb_mutasi',
      'tb_detail_mutasi',
      'tb_master_barang_all',
      'tb_gudang'
  )
  AND (
      COLUMN_NAME LIKE '%id%'
      OR COLUMN_NAME LIKE '%kd%'
      OR COLUMN_NAME LIKE '%kode%'
      OR COLUMN_NAME LIKE '%no%'
      OR COLUMN_NAME LIKE '%status%'
      OR COLUMN_NAME LIKE '%tgl%'
      OR COLUMN_NAME LIKE '%tanggal%'
      OR COLUMN_NAME LIKE '%date%'
      OR COLUMN_NAME LIKE '%customer%'
      OR COLUMN_NAME LIKE '%suplier%'
      OR COLUMN_NAME LIKE '%supplier%'
      OR COLUMN_NAME LIKE '%barang%'
      OR COLUMN_NAME LIKE '%gudang%'
      OR COLUMN_NAME LIKE '%qty%'
      OR COLUMN_NAME LIKE '%harga%'
      OR COLUMN_NAME LIKE '%hrg%'
      OR COLUMN_NAME LIKE '%hpp%'
      OR COLUMN_NAME LIKE '%nominal%'
      OR COLUMN_NAME LIKE '%total%'
      OR COLUMN_NAME LIKE '%tax%'
      OR COLUMN_NAME LIKE '%pajak%'
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;

/* 03. Existing indexes and constraints */
SELECT
    TABLE_NAME,
    INDEX_NAME,
    NON_UNIQUE,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ', ') AS columns_in_index
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'tbso_sales_order',
      'tbso_sales_order_detail',
      'tb_customer',
      'tb_pre_do',
      'tb_do',
      'tb_detail_do',
      'tbkeu_pembayaran_faktur',
      'tb_pre_po',
      'tb_lpb',
      'tb_lpb_detail',
      'tb_lpb_batch',
      'tb_suplier',
      'tbpo_suplier',
      'tbpo_po',
      'tbpo_detail_po',
      'tberp_stock_ledger',
      'tberp_stock_batch',
      'tb_mutasi',
      'tb_detail_mutasi',
      'tb_master_barang_all',
      'tb_gudang'
  )
GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE
ORDER BY TABLE_NAME, INDEX_NAME;

/* 04. Duplicate kode barang */
SELECT
    kd_barang,
    COUNT(*) AS total_rows,
    COUNT(DISTINCT nama_barang) AS distinct_names
FROM tb_master_barang_all
GROUP BY kd_barang
HAVING COUNT(*) > 1
ORDER BY total_rows DESC, kd_barang;

/* 05. Duplicate customer */
SELECT
    kd_customer,
    COUNT(*) AS total_rows,
    COUNT(DISTINCT nama_customer) AS distinct_names
FROM tb_customer
GROUP BY kd_customer
HAVING COUNT(*) > 1
ORDER BY total_rows DESC, kd_customer;

/* 06. Duplicate supplier ERP */
SELECT
    kd_suplier,
    COUNT(*) AS total_rows,
    COUNT(DISTINCT nama_suplier) AS distinct_names
FROM tb_suplier
GROUP BY kd_suplier
HAVING COUNT(*) > 1
ORDER BY total_rows DESC, kd_suplier;

/* 07. Duplicate supplier PO */
SELECT
    kd_suplier,
    COUNT(*) AS total_rows,
    COUNT(DISTINCT nama_suplier) AS distinct_names
FROM tbpo_suplier
GROUP BY kd_suplier
HAVING COUNT(*) > 1
ORDER BY total_rows DESC, kd_suplier;

/* 08. Supplier mismatch between tb_suplier and tbpo_suplier */
SELECT
    COALESCE(s.kd_suplier, ps.kd_suplier) AS kd_suplier,
    s.nama_suplier AS nama_suplier_erp,
    ps.nama_suplier AS nama_suplier_po,
    CASE
        WHEN s.kd_suplier IS NULL THEN 'ONLY_IN_TBPO_SUPLIER'
        WHEN ps.kd_suplier IS NULL THEN 'ONLY_IN_TB_SUPLIER'
        WHEN TRIM(COALESCE(s.nama_suplier, '')) <> TRIM(COALESCE(ps.nama_suplier, '')) THEN 'NAME_MISMATCH'
        ELSE 'MATCH'
    END AS audit_status
FROM tb_suplier s
LEFT JOIN tbpo_suplier ps ON ps.kd_suplier = s.kd_suplier
UNION
SELECT
    COALESCE(s.kd_suplier, ps.kd_suplier) AS kd_suplier,
    s.nama_suplier AS nama_suplier_erp,
    ps.nama_suplier AS nama_suplier_po,
    CASE
        WHEN s.kd_suplier IS NULL THEN 'ONLY_IN_TBPO_SUPLIER'
        WHEN ps.kd_suplier IS NULL THEN 'ONLY_IN_TB_SUPLIER'
        WHEN TRIM(COALESCE(s.nama_suplier, '')) <> TRIM(COALESCE(ps.nama_suplier, '')) THEN 'NAME_MISMATCH'
        ELSE 'MATCH'
    END AS audit_status
FROM tbpo_suplier ps
LEFT JOIN tb_suplier s ON s.kd_suplier = ps.kd_suplier
HAVING audit_status <> 'MATCH'
ORDER BY kd_suplier;

/* 09. Duplicate nomor faktur from sales sources */
SELECT source_table, no_faktur, total_rows
FROM (
    SELECT 'tbso_sales_order' AS source_table, no_so AS no_faktur, COUNT(*) AS total_rows
    FROM tbso_sales_order
    WHERE no_so IS NOT NULL AND no_so <> ''
    GROUP BY no_so
    HAVING COUNT(*) > 1
    UNION ALL
    SELECT 'tb_pre_do' AS source_table, kd_faktur AS no_faktur, COUNT(*) AS total_rows
    FROM tb_pre_do
    WHERE kd_faktur IS NOT NULL AND kd_faktur <> ''
    GROUP BY kd_faktur
    HAVING COUNT(*) > 1
    UNION ALL
    SELECT 'tb_detail_do' AS source_table, kd_faktur AS no_faktur, COUNT(*) AS total_rows
    FROM tb_detail_do
    WHERE kd_faktur IS NOT NULL AND kd_faktur <> ''
    GROUP BY kd_faktur
    HAVING COUNT(*) > 1
) x
ORDER BY source_table, total_rows DESC, no_faktur;

/* 10. Duplicate nomor LPB/invoice */
SELECT
    no_invoice,
    COUNT(*) AS total_rows
FROM tb_lpb
WHERE no_invoice IS NOT NULL AND no_invoice <> ''
GROUP BY no_invoice
HAVING COUNT(*) > 1
ORDER BY total_rows DESC, no_invoice;

/* 11. Orphan Sales Order detail */
SELECT
    d.id,
    d.id_so,
    d.no_so,
    d.kd_barang
FROM tbso_sales_order_detail d
LEFT JOIN tbso_sales_order h
    ON h.id_so = d.id_so OR h.no_so = d.no_so
WHERE h.id_so IS NULL
LIMIT 200;

/* 12. Orphan faktur/DO detail */
SELECT
    d.id,
    d.kd_do,
    d.kd_faktur,
    d.kd_barang
FROM tb_detail_do d
LEFT JOIN tb_do h ON h.kd_do = d.kd_do
WHERE h.id IS NULL
LIMIT 200;

/* 13. Orphan LPB detail */
SELECT
    d.id_detail_lpb,
    d.id_lpb,
    d.kd_barang
FROM tb_lpb_detail d
LEFT JOIN tb_lpb h ON h.id_lpb = d.id_lpb
WHERE h.id_lpb IS NULL
LIMIT 200;

/* 14. Orphan LPB batch */
SELECT
    b.id_batch,
    b.id_detail_lpb,
    b.no_lot,
    b.expired_date
FROM tb_lpb_batch b
LEFT JOIN tb_lpb_detail d ON d.id_detail_lpb = b.id_detail_lpb
WHERE d.id_detail_lpb IS NULL
LIMIT 200;

/* 15. Orphan pembayaran faktur */
SELECT
    p.id_pembayaran,
    p.id_faktur,
    p.no_faktur,
    p.tanggal_pembayaran
FROM tbkeu_pembayaran_faktur p
LEFT JOIN tb_pre_do f ON f.kd_faktur = p.no_faktur
LEFT JOIN tbso_sales_order so ON so.no_so = p.no_faktur
WHERE f.id IS NULL
  AND so.id_so IS NULL
LIMIT 200;

/* 16. Faktur/DO without valid customer */
SELECT
    d.kd_faktur,
    d.kd_customer,
    COUNT(*) AS total_rows
FROM tb_detail_do d
LEFT JOIN tb_customer c ON c.kd_customer = d.kd_customer
WHERE c.id IS NULL
GROUP BY d.kd_faktur, d.kd_customer
ORDER BY total_rows DESC
LIMIT 200;

/* 17. PO without valid supplier in tb_suplier */
SELECT
    p.kd_po,
    p.no_po,
    p.kd_suplier,
    COUNT(*) AS total_rows
FROM tb_pre_po p
LEFT JOIN tb_suplier s ON s.kd_suplier = p.kd_suplier
WHERE s.id IS NULL
GROUP BY p.kd_po, p.no_po, p.kd_suplier
ORDER BY total_rows DESC
LIMIT 200;

/* 18. Faktur without detail */
SELECT
    h.kd_faktur,
    COUNT(d.id) AS detail_rows
FROM (
    SELECT DISTINCT kd_faktur FROM tb_pre_do
) h
LEFT JOIN tb_detail_do d ON d.kd_faktur = h.kd_faktur
GROUP BY h.kd_faktur
HAVING COUNT(d.id) = 0
ORDER BY h.kd_faktur
LIMIT 200;

/* 19. LPB without detail */
SELECT
    h.id_lpb,
    h.kd_po,
    h.no_po,
    h.no_invoice
FROM tb_lpb h
LEFT JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
GROUP BY h.id_lpb, h.kd_po, h.no_po, h.no_invoice
HAVING COUNT(d.id_detail_lpb) = 0
ORDER BY h.id_lpb DESC
LIMIT 200;

/* 20. HPP zero or missing on master barang */
SELECT
    id,
    kd_barang,
    nama_barang,
    hpp
FROM tb_master_barang_all
WHERE hpp IS NULL OR hpp <= 0
ORDER BY kd_barang
LIMIT 500;

/* 21. HPP zero or missing on SO detail */
SELECT
    id,
    no_so,
    kd_barang,
    nama_barang,
    qty,
    hrg_pokok
FROM tbso_sales_order_detail
WHERE hrg_pokok IS NULL OR hrg_pokok <= 0
ORDER BY id DESC
LIMIT 500;

/* 22. Harga jual zero on sales sources */
SELECT
    'tbso_sales_order_detail' AS source_table,
    id AS source_id,
    no_so AS doc_no,
    kd_barang,
    hrg_satuan AS harga,
    total_harga AS total
FROM tbso_sales_order_detail
WHERE COALESCE(hrg_satuan, 0) <= 0 OR COALESCE(total_harga, 0) <= 0
UNION ALL
SELECT
    'tb_detail_do' AS source_table,
    id AS source_id,
    kd_faktur AS doc_no,
    kd_barang,
    nominal_p AS harga,
    nominal_p * qty AS total
FROM tb_detail_do
WHERE COALESCE(nominal_p, 0) <= 0
LIMIT 500;

/* 23. Negative nominal audit */
SELECT 'tb_pre_do' AS source_table, id AS source_id, kd_faktur AS doc_no, kd_barang, nominal_p AS nominal
FROM tb_pre_do
WHERE nominal_p < 0
UNION ALL
SELECT 'tb_detail_do' AS source_table, id AS source_id, kd_faktur AS doc_no, kd_barang, nominal_p AS nominal
FROM tb_detail_do
WHERE nominal_p < 0
UNION ALL
SELECT 'tb_pre_po' AS source_table, id_pre_po AS source_id, kd_po AS doc_no, kd_barang, harga_total AS nominal
FROM tb_pre_po
WHERE harga_total < 0
UNION ALL
SELECT 'tb_pre_po_invoice_adjustment' AS source_table, id AS source_id, kd_po AS doc_no, kd_barang, grand_total AS nominal
FROM tb_pre_po_invoice_adjustment
WHERE grand_total < 0
LIMIT 500;

/* 24. Date columns still stored as text/varchar */
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (COLUMN_NAME LIKE '%tgl%' OR COLUMN_NAME LIKE '%tanggal%' OR COLUMN_NAME LIKE '%date%' OR COLUMN_NAME LIKE '%at%')
  AND DATA_TYPE IN ('text', 'varchar', 'char', 'longtext', 'mediumtext', 'tinytext')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

/* 25. Dates that cannot be converted with common formats */
SELECT 'tb_pre_do.tgl_inputer' AS source_column, id AS source_id, tgl_inputer AS raw_value
FROM tb_pre_do
WHERE tgl_inputer IS NOT NULL
  AND tgl_inputer <> ''
  AND STR_TO_DATE(tgl_inputer, '%d/%m/%Y') IS NULL
  AND STR_TO_DATE(tgl_inputer, '%Y-%m-%d') IS NULL
LIMIT 200;

SELECT 'tb_detail_do.tgl_transaksi' AS source_column, id AS source_id, tgl_transaksi AS raw_value
FROM tb_detail_do
WHERE tgl_transaksi IS NOT NULL
  AND tgl_transaksi <> ''
  AND STR_TO_DATE(tgl_transaksi, '%d/%m/%Y') IS NULL
  AND STR_TO_DATE(tgl_transaksi, '%Y-%m-%d') IS NULL
LIMIT 200;

SELECT 'tb_pre_po.tgl_transaksi' AS source_column, id_pre_po AS source_id, tgl_transaksi AS raw_value
FROM tb_pre_po
WHERE tgl_transaksi IS NOT NULL
  AND tgl_transaksi <> ''
  AND STR_TO_DATE(tgl_transaksi, '%d/%m/%Y') IS NULL
  AND STR_TO_DATE(tgl_transaksi, '%Y-%m-%d') IS NULL
LIMIT 200;

SELECT 'tb_mutasi.tgl_transaksi' AS source_column, id AS source_id, tgl_transaksi AS raw_value
FROM tb_mutasi
WHERE tgl_transaksi IS NOT NULL
  AND tgl_transaksi <> ''
  AND STR_TO_DATE(tgl_transaksi, '%d/%m/%Y') IS NULL
  AND STR_TO_DATE(tgl_transaksi, '%Y-%m-%d') IS NULL
LIMIT 200;

/* 26. Gudang id type mismatch */
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME IN ('gudang_id', 'id_gudang', 'gudang_asal', 'gudang_mutasi', 'gdg_asal', 'gdg_mutasi')
ORDER BY TABLE_NAME, COLUMN_NAME;

/* 27. Stock ledger references without matching master barang */
SELECT
    l.id,
    l.kd_barang,
    l.gudang_id,
    l.tipe,
    l.ref_no,
    l.ref_type
FROM tberp_stock_ledger l
LEFT JOIN tb_master_barang_all b ON b.kd_barang = l.kd_barang
WHERE b.id IS NULL
LIMIT 200;

/* 28. Stock batch references without matching master barang or gudang */
SELECT
    sb.id,
    sb.kd_barang,
    sb.gudang_id,
    sb.no_lot,
    sb.expired_date,
    CASE WHEN b.id IS NULL THEN 'BARANG_NOT_FOUND' ELSE 'BARANG_OK' END AS barang_status,
    CASE WHEN g.id_gudang IS NULL THEN 'GUDANG_NOT_FOUND' ELSE 'GUDANG_OK' END AS gudang_status
FROM tberp_stock_batch sb
LEFT JOIN tb_master_barang_all b ON b.kd_barang = sb.kd_barang
LEFT JOIN tb_gudang g ON CAST(g.id_gudang AS CHAR) = CAST(sb.gudang_id AS CHAR)
WHERE b.id IS NULL OR g.id_gudang IS NULL
LIMIT 200;

/* 29. LPB value candidates with zero/missing price */
SELECT
    h.id_lpb,
    h.kd_po,
    h.no_po,
    d.id_detail_lpb,
    d.kd_barang,
    d.qty_diterima,
    pp.hrg_satuan AS pre_po_hrg_satuan,
    pp.harga_total AS pre_po_harga_total,
    adj.harga_satuan AS adj_harga_satuan,
    adj.grand_total AS adj_grand_total
FROM tb_lpb h
JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
LEFT JOIN tb_pre_po pp
    ON pp.kd_po = h.kd_po
   AND pp.kd_barang = d.kd_barang
LEFT JOIN tb_pre_po_invoice_adjustment adj
    ON adj.kd_po = h.kd_po
   AND adj.kd_barang = d.kd_barang
WHERE COALESCE(adj.harga_satuan, pp.hrg_satuan, 0) <= 0
ORDER BY h.id_lpb DESC
LIMIT 500;

/* 30. Payment table amount capability check */
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'tbkeu_pembayaran_faktur'
ORDER BY ORDINAL_POSITION;

/* 31. Payment exceeds invoice value candidate.
   This query currently reports inability to validate because tbkeu_pembayaran_faktur has no amount column.
*/
SELECT
    'PAYMENT_AMOUNT_COLUMN_MISSING' AS audit_code,
    'tbkeu_pembayaran_faktur has no amount/nominal column, so overpayment cannot be audited from this table.' AS message;

/* 32. Current source status distribution */
SELECT 'tbso_sales_order.status' AS source_status, status, COUNT(*) AS total_rows
FROM tbso_sales_order
GROUP BY status
UNION ALL
SELECT 'tb_pre_do.data_sts' AS source_status, CAST(data_sts AS CHAR) AS status, COUNT(*) AS total_rows
FROM tb_pre_do
GROUP BY data_sts
UNION ALL
SELECT 'tb_do.status' AS source_status, CAST(status AS CHAR) AS status, COUNT(*) AS total_rows
FROM tb_do
GROUP BY status
UNION ALL
SELECT 'tb_detail_do.status' AS source_status, CAST(status AS CHAR) AS status, COUNT(*) AS total_rows
FROM tb_detail_do
GROUP BY status
UNION ALL
SELECT 'tb_pre_po.status' AS source_status, CAST(status AS CHAR) AS status, COUNT(*) AS total_rows
FROM tb_pre_po
GROUP BY status
UNION ALL
SELECT 'tb_mutasi.status' AS source_status, status, COUNT(*) AS total_rows
FROM tb_mutasi
GROUP BY status
ORDER BY source_status, status;

/* 33. Tables that look like retur source tables */
SELECT
    TABLE_NAME,
    TABLE_ROWS
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE '%retur%'
ORDER BY TABLE_NAME;

/* 34. Tables that look like stock adjustment source tables */
SELECT
    TABLE_NAME,
    TABLE_ROWS
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND (
      TABLE_NAME LIKE '%adjust%'
      OR TABLE_NAME LIKE '%opname%'
      OR TABLE_NAME LIKE '%stock_hold%'
  )
ORDER BY TABLE_NAME;

/* 35. Accounting tables currently present */
SELECT
    TABLE_NAME,
    ENGINE,
    TABLE_ROWS
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE 'tbkeu_%'
ORDER BY TABLE_NAME;
