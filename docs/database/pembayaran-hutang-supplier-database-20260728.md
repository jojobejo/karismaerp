# Development Database Pembayaran Hutang Supplier

Tanggal: 2026-07-28  
Scope: struktur database, query, dan jurnal untuk module pembayaran hutang supplier.

## Tabel Yang Dipakai

Tidak ada tabel wajib baru pada fase ini. Module memakai tabel accounting dan source LPB/retur yang sudah ada.

Tabel accounting:

- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`
- `tbkeu_akun`
- `tbkeu_pembayaran`
- `tbkeu_pembayaran_alokasi`
- `tbkeu_mapping_akun`

Tabel source operasional:

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_suplier`
- `tb_retur_pembelian`
- `tb_retur_pembelian_detail`

## Tabel Payment

### `tbkeu_pembayaran`

Untuk pembayaran supplier:

- `payment_type = SUPPLIER_PAYMENT`
- `source_module = KEUANGAN`
- `source_type = SUPPLIER_PAYMENT`
- `source_id = nomor_pembayaran`
- `source_no = nomor_pembayaran`
- `id_supplier` diisi dari supplier outstanding
- `amount = total pembayaran`
- `allocated_amount = total alokasi`
- `unapplied_amount = 0` untuk MVP
- `status = POSTED`
- `id_jurnal` terhubung ke jurnal supplier payment

### `tbkeu_pembayaran_alokasi`

Untuk pembayaran supplier:

- `invoice_source_module = LOGISTIK`
- `invoice_source_type = LPB_FINAL`
- `invoice_source_id = id LPB bila terbaca`
- `invoice_no = nomor_dokumen` dari jurnal hutang
- `amount_allocated = nilai alokasi`

## Query Outstanding

Outstanding supplier dihitung dari jurnal, bukan dari total LPB mentah.

```sql
SELECT
    d.id_supplier,
    d.nomor_dokumen,
    COALESCE(SUM(d.kredit), 0) AS total_hutang,
    COALESCE(SUM(d.debit), 0) AS total_pengurang,
    COALESCE(SUM(d.kredit - d.debit), 0) AS outstanding
FROM tbkeu_jurnal_detail d
INNER JOIN tbkeu_jurnal j
    ON j.id_jurnal = d.id_jurnal
   AND j.status = 'POSTED'
   AND j.reversed_at IS NULL
INNER JOIN tbkeu_akun a ON a.id_akun = d.id_akun
WHERE (a.tipe_kontrol = 'HUTANG' OR a.kode_akun = '21098')
  AND COALESCE(d.nomor_dokumen, '') <> ''
GROUP BY d.id_supplier, d.nomor_dokumen
HAVING outstanding > 0;
```

Catatan akun `21098`:

- data LPB/retur lokal historis memakai `21098` sebagai Hutang Usaha;
- pada konsep awal ditemukan `21098` belum bertipe kontrol `HUTANG`;
- untuk menjaga data existing tetap terbaca tanpa mengubah jurnal posted, module membaca `21098` sebagai akun hutang historis.

## Jurnal Supplier Payment

Event: `SUPPLIER_PAYMENT`

Jurnal standar:

| Debit | Kredit |
| --- | --- |
| Hutang Usaha per dokumen |  |
|  | Kas/Bank yang dipilih user |

Detail debit hutang dipecah per `nomor_dokumen` agar outstanding dokumen turun benar.

Contoh:

| Baris | Akun | Debit | Kredit | Nomor Dokumen |
| --- | --- | ---: | ---: | --- |
| 1 | Bank/Kas | 0 | 10.000.000 | BYS-202607-00001 |
| 2 | Hutang Usaha | 6.000.000 | 0 | LPB-001 |
| 3 | Hutang Usaha | 4.000.000 | 0 | LPB-002 |

## Void Payment

Void payment:

1. hanya bisa untuk `tbkeu_pembayaran.status = POSTED`;
2. wajib alasan;
3. memanggil `Accounting_service::reversal_journal()`;
4. mengubah payment menjadi `VOID`;
5. tidak menghapus `tbkeu_pembayaran` atau `tbkeu_pembayaran_alokasi`.

## Perubahan Schema

Tidak ada migration wajib baru.

Opsional fase lanjutan:

- `tbkeu_pembayaran_attachment` untuk bukti transfer;
- `tbkeu_pembayaran_log` untuk audit before/after;
- master metode pembayaran jika Finance ingin mapping metode ke akun bank lebih terkendali.

## Data Lokal Saat Pengujian Query

Query outstanding lokal membaca supplier terbuka, antara lain:

- `PT.Sinar General Industries`;
- `PT.Sari Kresna Kimia`;
- `PT.Agriculture Constraction Indonesia`.

Nilai berasal dari jurnal LPB/retur/payment, bukan dari tabel LPB saja.

