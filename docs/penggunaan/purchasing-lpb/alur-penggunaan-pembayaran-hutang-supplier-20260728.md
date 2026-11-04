# Alur Penggunaan Module Pembayaran Hutang Supplier

Tanggal: 2026-07-28

## Alur Ringkas

```text
LPB POST / GOODS_RECEIPT
        |
        v
Retur pembelian POTONG_HUTANG jika ada
        |
        v
Keuangan buka pembayaran supplier
        |
        v
Pilih supplier dan dokumen hutang
        |
        v
Isi akun kas/bank, nominal, dan alokasi
        |
        v
Posting SUPPLIER_PAYMENT
        |
        v
Hutang turun, kas/bank berkurang, histori payment terbentuk
```

## Alur Detail

1. Logistik/Purchasing menyelesaikan LPB.
2. LPB final menghasilkan jurnal `GOODS_RECEIPT`.
3. Jika ada barang dikembalikan ke supplier, buat retur pembelian sampai `POSTED`.
4. Keuangan membuka `keuangan/pembayaran-supplier`.
5. Keuangan memilih supplier dengan sisa hutang.
6. Keuangan memilih dokumen yang akan dibayar.
7. Keuangan memilih akun kas/bank.
8. Keuangan mengisi nominal pembayaran.
9. Sistem menghitung total alokasi.
10. Sistem memvalidasi:
    - supplier valid;
    - dokumen masih outstanding;
    - alokasi tidak melebihi outstanding;
    - total pembayaran sama dengan total alokasi;
    - akun kas/bank valid.
11. Sistem membuat payment dan jurnal.
12. Jika payment salah, Keuangan melakukan void dengan alasan.

## Alur Jurnal

### LPB / Goods Receipt

```text
Dr Persediaan
Dr PPN Masukan jika ada
    Cr Hutang Usaha
```

### Retur Pembelian Potong Hutang

```text
Dr Hutang Usaha
    Cr Persediaan
    Cr PPN Masukan jika ada
```

### Pembayaran Supplier

```text
Dr Hutang Usaha
    Cr Kas/Bank
```

## Status Yang Terlibat

| Entity | Status | Makna |
| --- | --- | --- |
| `tb_lpb.status_lpb` | `1` | LPB POST |
| `tbkeu_jurnal.status` | `POSTED` | Jurnal aktif untuk laporan |
| `tbkeu_jurnal.reversed_at` | `NULL` | Jurnal belum dibalik |
| `tbkeu_pembayaran.status` | `POSTED` | Payment aktif |
| `tbkeu_pembayaran.status` | `VOID` | Payment dibatalkan via reversal |

## Kondisi Gagal Yang Umum

| Kondisi | Penyebab | Tindakan |
| --- | --- | --- |
| Supplier tidak muncul | Tidak ada outstanding jurnal hutang | Cek jurnal LPB/retur |
| Dokumen tidak bisa dibayar | Outstanding dokumen nol | Cek apakah sudah dibayar/void/reversal |
| Posting ditolak | Alokasi melebihi outstanding | Turunkan nominal alokasi |
| Posting ditolak | Total payment tidak sama dengan total alokasi | Samakan nilai payment dan alokasi |
| Akun kas/bank kosong | COA kas/bank belum aktif | Aktifkan akun posting bertipe `KAS` atau `BANK` |

