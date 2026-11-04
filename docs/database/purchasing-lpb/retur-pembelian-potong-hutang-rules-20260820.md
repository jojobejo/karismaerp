# Database Retur Pembelian Potong Hutang

Tanggal: 20 Agustus 2026

## Status Perubahan Schema

Tidak ada perubahan struktur tabel dan tidak ada migration SQL baru yang wajib dijalankan.

Pengembangan memakai tabel accounting dan pembayaran supplier yang sudah tersedia:

- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`
- `tbkeu_akun`
- `tbkeu_pembayaran`
- `tbkeu_pembayaran_alokasi`
- `tb_retur_pembelian`
- `tbpo_suplier`

## Akun Yang Wajib Tersedia

Rules baru membutuhkan akun berikut sebagai akun aktif dan bertipe `POSTING`:

| Kode Akun | Fungsi |
| --- | --- |
| `13013` | Piutang non dagang retur pembelian yang belum dipotong |
| `13017` | PPN Masukan / PPN M Ymh Diterima untuk retur BKP |
| `14010` | Persediaan BKP |
| `14011` | Persediaan BKPS |

## Penyimpanan Jurnal Retur

Jurnal retur pembelian tetap disimpan di:

- Header: `tbkeu_jurnal`
- Detail: `tbkeu_jurnal_detail`

Identitas jurnal:

- `source_module = LOGISTIK`
- `source_type = RETUR_PEMBELIAN`
- `posting_event = PURCHASE_RETURN`
- `source_id = id_retur_pembelian`
- `source_no = no_retur_pembelian`

Kode rule dicatat pada keterangan baris jurnal:

- `RBELI-PH-BKP` untuk retur pembelian BKP.
- `RBELI-PH-BKPS` untuk retur pembelian BKPS.

## Penyimpanan Potong Hutang Retur

Potong hutang retur disimpan sebagai transaksi pembayaran supplier standar:

- Header pembayaran: `tbkeu_pembayaran`
- Detail alokasi: `tbkeu_pembayaran_alokasi`
- Header jurnal: `tbkeu_jurnal`
- Detail jurnal: `tbkeu_jurnal_detail`

Identitas transaksi:

- `payment_type = SUPPLIER_PAYMENT`
- `source_module = KEUANGAN`
- `source_type = SUPPLIER_RETURN_DEDUCTION`
- `posting_event = SUPPLIER_PAYMENT`

Alokasi hutang memakai:

- `invoice_source_type = LPB_FINAL`
- `invoice_no = nomor dokumen hutang terbuka`

Alokasi retur memakai:

- `invoice_source_type = RETUR_PEMBELIAN_CREDIT`
- `invoice_no = nomor retur pembelian`

## Query Audit Singkat

Saldo retur pembelian yang belum dipotong:

```sql
SELECT
  d.id_supplier,
  d.nomor_dokumen AS no_retur_pembelian,
  SUM(d.debit) AS total_retur,
  SUM(d.kredit) AS total_dipotong,
  SUM(d.debit - d.kredit) AS sisa_retur
FROM tbkeu_jurnal_detail d
JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
JOIN tbkeu_akun a ON a.id_akun = d.id_akun
WHERE j.status = 'POSTED'
  AND j.reversed_at IS NULL
  AND a.kode_akun = '13013'
GROUP BY d.id_supplier, d.nomor_dokumen
HAVING sisa_retur > 0;
```

Jurnal potong hutang retur:

```sql
SELECT
  j.nomor_jurnal,
  j.tanggal_transaksi,
  j.source_no,
  a.kode_akun,
  a.nama_akun,
  d.nomor_dokumen,
  d.debit,
  d.kredit
FROM tbkeu_jurnal j
JOIN tbkeu_jurnal_detail d ON d.id_jurnal = j.id_jurnal
JOIN tbkeu_akun a ON a.id_akun = d.id_akun
WHERE j.source_type = 'SUPPLIER_RETURN_DEDUCTION'
ORDER BY j.tanggal_transaksi DESC, j.id_jurnal DESC, d.nomor_baris ASC;
```
