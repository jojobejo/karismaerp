# Perbaikan Pembulatan Total Jurnal Pembelian LPB

## Masalah

Jurnal LPB `2600001` menghasilkan total Rp540.000.000,23, sedangkan total bruto PO/LPB adalah Rp540.000.000,00. Selisih muncul karena DPP berasal dari akumulasi harga exclude per unit dengan presisi empat desimal, kemudian PPN 11% dihitung kembali dari akumulasi tersebut.

## Perbaikan

`Accounting_source_service::post_goods_receipt()` sekarang menjadikan total harga bruto PO sebagai nilai tagihan otoritatif untuk barang BKP. Nilai jurnal disimpan dengan presisi empat desimal. Pembulatan dua desimal hanya dilakukan pada tampilan sehingga:

- DPP database: `486486486.4864`; tampilan: Rp486.486.486,49
- PPN database: `53513513.5135`; tampilan: Rp53.513.513,51
- Hutang Usaha database: `539999999.9999`; tampilan: Rp540.000.000,00

Nilai debit dan kredit tetap seimbang hingga empat desimal. Selisih truncation `0,0001` tidak dibuang dan tidak menyebabkan jurnal tidak balance. Jurnal yang telah terbentuk sebelum perubahan perlu direkam ulang atau dikoreksi secara terarah.

## Validasi

1. Buka `ics/data_lpb` dan pastikan total LPB Rp540.000.000,00.
2. Rekam ulang jurnal pembelian LPB terkait.
3. Buka `jurnal/pembelian`.
4. Pastikan total debit dan kredit sama-sama Rp540.000.000,00.
