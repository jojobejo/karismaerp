# Database Module Jurnal - Analisa Zahir to ERP

Tanggal: 2026-07-15

## Sumber Analisa

- Excel Zahir: `C:/Users/bram/Documents/data_akun_zahir_to_erp.xlsx`
- Dump SQL: `C:/Users/bram/Downloads/kiucoid_karismaerp_local (10).sql`
- Database lokal aktif: `kiucoid_karismaerp_local`

## Struktur Database Accounting Saat Ini

Tabel accounting yang ditemukan pada database lokal aktif:

- `tbkeu_akun`
- `tbkeu_dummy_source`
- `tbkeu_jenis_jurnal`
- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`
- `tbkeu_jurnal_log`
- `tbkeu_klasifikasi_akun`
- `tbkeu_mapping_akun`
- `tbkeu_nomor_dokumen`
- `tbkeu_pembayaran`
- `tbkeu_pembayaran_alokasi`
- `tbkeu_pembayaran_faktur`
- `tbkeu_periode_fiskal`
- `tbkeu_periode_fiskal_log`
- `tbkeu_posting_exception`
- `tbkeu_saldo_awal_akun`
- `tbkeu_saldo_normal`
- `tbkeu_tipe_kontrol`

Row penting saat scan:

| Tabel | Row |
| --- | ---: |
| `tbkeu_klasifikasi_akun` | 9 |
| `tbkeu_akun` | 22 |
| `tbkeu_jurnal` | 2 |
| `tbkeu_jurnal_detail` | 10 |
| `tbkeu_mapping_akun` | 30 |
| `tbkeu_saldo_awal_akun` | 0 |

Integrity check lokal:

| Check | Issue |
| --- | ---: |
| Duplicate `kode_akun` | 0 |
| Orphan `parent_id` | 0 |
| Akun `HEADER` dengan `allow_manual_journal = 1` | 0 |
| Jurnal header tidak balance | 0 |
| Total header tidak sama dengan detail | 0 |
| Jurnal `POSTED` tanpa detail | 0 |

## Struktur Zahir dari Excel

| Sheet | Kolom utama | Row data |
| --- | --- | ---: |
| `KELOMPOK` | `KELOMPOK`, `NAMAKELOMPOK`, `KETERANGAN`, `ISAUTO`, `SIMBOL` | 23 |
| `KLASIFIKASI` | `NOKLASIFIKASI`, `NAMAKLASIFIKASI`, `ALIASKLASIFIKASI`, `NAMAKLASTEMP` | 9 |
| `SUB CLASS` | `NOSUBKLASIFIKASI`, `NOKLASIFIKASI`, `ALIASSUBKLASIFIKASI`, `NAMASUBKLASIFIKASI`, `CASHFLOWID` | 36 |
| `KIRAAN` | `KODE`, `NOSUBKLASIFIKASI`, `NAMA`, `ALIASNAMA`, `TEMPNAMA` | 501 |
| `AKUN LABA RUGI` | `Klasifikasi`, `Kode Rekening`, `Nama Rekening`, `BERTAMBAH`, `BERKURANG` | 267 |
| `AKUN NERACA` | `Klasifikasi`, `Kode Rekening`, `Nama Rekening`, `BERTAMBAH`, `BERKURANG` | 230 |

## Mapping Data Zahir ke ERP

| Data Zahir | Target ERP Saat Ini | Status |
| --- | --- | --- |
| `KLASIFIKASI` | `tbkeu_klasifikasi_akun` | Sudah kompatibel secara konsep. |
| `SUB CLASS` | `tbkeu_sub_klasifikasi_akun` | Menjadi master pendukung, bukan akun COA. |
| `KIRAAN` | `tbkeu_akun` | Perlu import 501 akun. |
| `KELOMPOK` | `tbkeu_jenis_jurnal` | Perlu mapping/seed tambahan dari simbol Zahir. |
| `AKUN LABA RUGI` | Belum ada rule table eksplisit | Perlu tabel rule laporan. |
| `AKUN NERACA` | Belum ada rule table eksplisit | Perlu tabel rule laporan. |

## Rekomendasi Struktur Tambahan

### 1. Batch Import

```sql
CREATE TABLE tbkeu_karismaerp_import_batch (
  id_batch BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  file_name VARCHAR(255) NOT NULL,
  file_hash VARCHAR(64) DEFAULT NULL,
  status ENUM('DRAFT','VALIDATED','APPLIED','FAILED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  total_kelompok INT UNSIGNED NOT NULL DEFAULT 0,
  total_klasifikasi INT UNSIGNED NOT NULL DEFAULT 0,
  total_sub_class INT UNSIGNED NOT NULL DEFAULT 0,
  total_kiraan INT UNSIGNED NOT NULL DEFAULT 0,
  total_rule_laba_rugi INT UNSIGNED NOT NULL DEFAULT 0,
  total_rule_neraca INT UNSIGNED NOT NULL DEFAULT 0,
  validation_message TEXT DEFAULT NULL,
  created_by BIGINT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  applied_by BIGINT DEFAULT NULL,
  applied_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id_batch)
);
```

### 2. Staging Import

Gunakan staging agar raw Excel bisa diaudit ulang sebelum apply:

- `tbkeu_karismaerp_stg_kelompok`
- `tbkeu_karismaerp_stg_klasifikasi`
- `tbkeu_karismaerp_stg_sub_class`
- `tbkeu_karismaerp_stg_kiraan`
- `tbkeu_karismaerp_stg_report_rule`

Kolom minimal staging:

- `id_batch`
- `sheet_name`
- `row_number`
- kolom raw sesuai Excel
- `normalized_code`
- `validation_status`
- `validation_message`

### 3. Master Sub Klasifikasi

```sql
CREATE TABLE tbkeu_sub_klasifikasi_akun (
  id_sub_klasifikasi BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_sub_klasifikasi VARCHAR(30) NOT NULL,
  id_klasifikasi TINYINT UNSIGNED NOT NULL,
  nama_sub_klasifikasi VARCHAR(150) NOT NULL,
  alias_sub_klasifikasi VARCHAR(150) DEFAULT NULL,
  cashflow_id VARCHAR(30) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_sub_klasifikasi),
  UNIQUE KEY uk_tbkeu_sub_klasifikasi_kode (kode_sub_klasifikasi),
  KEY idx_tbkeu_sub_klasifikasi_klasifikasi (id_klasifikasi),
  CONSTRAINT fk_tbkeu_sub_klasifikasi_klasifikasi
    FOREIGN KEY (id_klasifikasi) REFERENCES tbkeu_klasifikasi_akun (id_klasifikasi)
    ON UPDATE CASCADE
);
```

### 4. Referensi Zahir Pada Akun

Opsi aman tanpa mengganggu `tbkeu_akun` existing adalah tabel relasi:

```sql
CREATE TABLE tbkeu_akun_karismaerp_ref (
  id_ref BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_akun BIGINT UNSIGNED NOT NULL,
  id_sub_klasifikasi BIGINT UNSIGNED DEFAULT NULL,
  kode_kiraan VARCHAR(30) NOT NULL,
  kode_rekening_display VARCHAR(30) DEFAULT NULL,
  nama_karismaerp VARCHAR(150) DEFAULT NULL,
  alias_karismaerp VARCHAR(150) DEFAULT NULL,
  temp_nama_karismaerp VARCHAR(150) DEFAULT NULL,
  source_batch_id BIGINT UNSIGNED DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_ref),
  UNIQUE KEY uk_tbkeu_akun_karismaerp_kiraan (kode_kiraan),
  KEY idx_tbkeu_akun_karismaerp_akun (id_akun),
  KEY idx_tbkeu_akun_karismaerp_sub (id_sub_klasifikasi),
  CONSTRAINT fk_tbkeu_akun_karismaerp_akun
    FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun)
    ON UPDATE CASCADE,
  CONSTRAINT fk_tbkeu_akun_karismaerp_sub
    FOREIGN KEY (id_sub_klasifikasi) REFERENCES tbkeu_sub_klasifikasi_akun (id_sub_klasifikasi)
    ON DELETE SET NULL ON UPDATE CASCADE
);
```

### 5. Rule Laporan

```sql
CREATE TABLE tbkeu_report_rule_akun (
  id_rule BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  statement_type ENUM('LABA_RUGI','NERACA') NOT NULL,
  id_akun BIGINT UNSIGNED NOT NULL,
  kode_rekening_source VARCHAR(30) NOT NULL,
  kode_rekening_normalized VARCHAR(30) NOT NULL,
  nama_rekening_source VARCHAR(150) DEFAULT NULL,
  klasifikasi_source VARCHAR(100) DEFAULT NULL,
  group_source VARCHAR(150) DEFAULT NULL,
  bertambah_side ENUM('DEBIT','KREDIT') NOT NULL,
  berkurang_side ENUM('DEBIT','KREDIT') NOT NULL,
  urutan INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  source_batch_id BIGINT UNSIGNED DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_rule),
  UNIQUE KEY uk_tbkeu_report_rule_statement_akun (statement_type, id_akun),
  KEY idx_tbkeu_report_rule_code (kode_rekening_normalized),
  KEY idx_tbkeu_report_rule_statement_order (statement_type, urutan),
  CONSTRAINT fk_tbkeu_report_rule_akun
    FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun)
    ON UPDATE CASCADE
);
```

## Aturan Normalisasi Kode

Gunakan `KIRAAN.KODE` sebagai canonical account code.

Contoh:

| Sumber | Raw | Normalized |
| --- | --- | --- |
| `KIRAAN.KODE` | `41010` | `41010` |
| `AKUN LABA RUGI.Kode Rekening` | `410-10` | `41010` |
| `AKUN NERACA.Kode Rekening` | `110-10` | `11010` |

Rule:

```text
normalized_code = uppercase(trim(kode_rekening))
normalized_code = replace(normalized_code, '-', '')
normalized_code = replace(normalized_code, ' ', '')
```

## Formula Laporan

Report lama hanya memakai klasifikasi dan saldo normal. Setelah rule Zahir masuk, formula harus memakai `bertambah_side`.

```text
Jika bertambah_side = DEBIT:
nilai = SUM(debit) - SUM(kredit)

Jika bertambah_side = KREDIT:
nilai = SUM(kredit) - SUM(debit)
```

Filter:

- Laba rugi: `j.status = 'POSTED'` dan tanggal dalam periode.
- Neraca: `j.status = 'POSTED'` dan tanggal `<= tanggal_akhir`.

## Urutan Migration yang Disarankan

1. Buat tabel batch dan staging.
2. Buat `tbkeu_sub_klasifikasi_akun`.
3. Buat `tbkeu_akun_karismaerp_ref`.
4. Buat `tbkeu_report_rule_akun`.
5. Import Excel ke staging.
6. Jalankan validation query.
7. Apply klasifikasi dan sub klasifikasi sebagai master pendukung.
8. Apply akun `KIRAAN` sebagai akun `POSTING`.
9. Apply rule laporan.
10. Review dan update `tbkeu_mapping_akun`.
11. Jalankan UAT jurnal dan laporan.

## Query Audit Setelah Import

```sql
-- Rule laporan yang tidak punya akun canonical.
SELECT r.*
FROM tbkeu_karismaerp_stg_report_rule r
LEFT JOIN tbkeu_karismaerp_stg_kiraan k
  ON k.id_batch = r.id_batch
 AND k.kode = r.normalized_code
WHERE r.id_batch = ?
  AND k.kode IS NULL;

-- Akun KIRAAN yang tidak punya rule laporan.
SELECT k.*
FROM tbkeu_karismaerp_stg_kiraan k
LEFT JOIN tbkeu_karismaerp_stg_report_rule r
  ON r.id_batch = k.id_batch
 AND r.normalized_code = k.kode
WHERE k.id_batch = ?
  AND r.normalized_code IS NULL;

-- Akun report yang dipakai jurnal tetapi rule tidak aktif.
SELECT a.kode_akun, a.nama_akun, COUNT(d.id_jurnal_detail) AS journal_rows
FROM tbkeu_akun a
JOIN tbkeu_jurnal_detail d ON d.id_akun = a.id_akun
LEFT JOIN tbkeu_report_rule_akun r ON r.id_akun = a.id_akun AND r.is_active = 1
WHERE r.id_rule IS NULL
GROUP BY a.id_akun;
```

## Status Perubahan Database Pada Task Ini

Migration master Karismaerp sudah dibuat dan database lokal aktif `kiucoid_karismaerp_local` sudah dibersihkan dari akun 3 digit sesuai rule terakhir. Dokumen ini tetap menjadi catatan database terpisah dari dokumentasi development aplikasi.

## Update 2026-07-15 - File Migration Dibuat

Migration SQL untuk data Excel Zahir sudah dibuat pada:

`docs/database/accounting_karismaerp_master_migration_20260715.sql`

Tabel baru yang dibuat oleh migration:

- `tbkeu_karismaerp_import_batch`
- `tbkeu_karismaerp_kelompok_jurnal`
- `tbkeu_sub_klasifikasi_akun`
- `tbkeu_akun_karismaerp_ref`
- `tbkeu_report_rule_akun`

Tabel existing yang di-seed/update:

- `tbkeu_jenis_jurnal`
- `tbkeu_klasifikasi_akun`
- `tbkeu_akun`

Data yang dimigrasikan dari Excel:

| Sumber Excel | Target | Row |
| --- | --- | ---: |
| `KELOMPOK` | `tbkeu_karismaerp_kelompok_jurnal` dan `tbkeu_jenis_jurnal` | 23 |
| `KLASIFIKASI` | `tbkeu_klasifikasi_akun` | 9 |
| `SUB CLASS` | `tbkeu_sub_klasifikasi_akun` sebagai master pendukung | 36 |
| `KIRAAN` | akun posting level 2 `tbkeu_akun` dan `tbkeu_akun_karismaerp_ref` | 501 |
| `AKUN LABA RUGI` | `tbkeu_report_rule_akun` | 267 |
| `AKUN NERACA` | `tbkeu_report_rule_akun` | 230 |

Catatan desain database:

- Semua tabel baru memakai prefix `tbkeu_`.
- `tbkeu_akun_karismaerp_ref.id_akun` dan `tbkeu_report_rule_akun.id_akun` di-resolve setelah seed akun dibuat.
- Unique rule laporan memakai `statement_type + kode_rekening_normalized`, supaya rerun migration tetap idempotent.
- Kode rekening report seperti `410-10` disimpan sebagai source, sedangkan `41010` menjadi kode normalized.
- Kode sub klasifikasi 3 digit tidak dibuat sebagai akun COA; sub klasifikasi hanya disimpan pada `tbkeu_sub_klasifikasi_akun`.
- Akun posting dari `KIRAAN` menjadi child langsung dari header klasifikasi 4 digit, contoh `11010` menjadi child `1000`.

Validasi sudah dilakukan pada database sementara `codex_karismaerp_migration_test` dengan schema hasil `mysqldump --no-data` dari database lokal. Migration berhasil dijalankan dan rerun tanpa menggandakan data.

## Update 2026-07-15 - Rule Hapus Akun COA 3 Digit

Masalah yang ditemukan pada UI daftar akun:

- akun sub klasifikasi 3 digit seperti `110 Kas` tampil sebagai akun;
- akun posting turunannya seperti `11010 Q Kas` juga tampil;
- akibatnya user melihat data ganda, padahal `110` hanya grouping/sub klasifikasi.

Rule final:

- kode akun 3 digit dihapus dari `tbkeu_akun`;
- kode 3 digit tetap hidup di `tbkeu_sub_klasifikasi_akun.kode_sub_klasifikasi`;
- `tbkeu_sub_klasifikasi_akun.id_akun_header` diset `NULL`;
- akun posting tidak diarahkan ke parent 3 digit;
- akun posting diarahkan langsung ke header klasifikasi 4 digit.

Query cleanup yang dijalankan pada database lokal:

```sql
SET @akun_3_digit_dipakai_jurnal := (
  SELECT COUNT(*)
  FROM tbkeu_jurnal_detail d
  JOIN tbkeu_akun a ON a.id_akun = d.id_akun
  WHERE a.kode_akun REGEXP '^[0-9]{3}$'
);

UPDATE tbkeu_akun child
JOIN tbkeu_akun old_parent
  ON old_parent.id_akun = child.parent_id
 AND old_parent.kode_akun REGEXP '^[0-9]{3}$'
JOIN tbkeu_akun top_parent
  ON top_parent.kode_akun = CAST((child.id_klasifikasi * 1000) AS CHAR)
SET child.parent_id = top_parent.id_akun,
    child.level_akun = 2
WHERE @akun_3_digit_dipakai_jurnal = 0;

UPDATE tbkeu_sub_klasifikasi_akun s
JOIN tbkeu_akun a
  ON a.id_akun = s.id_akun_header
 AND a.kode_akun REGEXP '^[0-9]{3}$'
SET s.id_akun_header = NULL
WHERE @akun_3_digit_dipakai_jurnal = 0;

DELETE a
FROM tbkeu_akun a
WHERE @akun_3_digit_dipakai_jurnal = 0
  AND a.kode_akun REGEXP '^[0-9]{3}$';
```

Hasil validasi setelah cleanup:

| Validasi | Hasil |
| --- | ---: |
| Akun 3 digit dipakai jurnal | 0 |
| Total akun `tbkeu_akun` | 510 |
| Akun kode 3 digit tersisa | 0 |
| `tbkeu_akun_karismaerp_ref` | 501 |
| `tbkeu_report_rule_akun` | 497 |
| Referensi akun tanpa `tbkeu_akun` | 0 |
| Rule laporan tanpa `tbkeu_akun` | 0 |
| Akun posting import tanpa parent | 0 |

Contoh hasil parent:

| Kode akun | Nama akun | Parent | Level |
| --- | --- | --- | ---: |
| `11010` | `Q Kas` | `1000 Harta` | 2 |
| `11011` | `Q Kas (PBGM)` | `1000 Harta` | 2 |
| `11030` | `A Kas` | `1000 Harta` | 2 |
