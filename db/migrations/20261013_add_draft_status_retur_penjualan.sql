-- Menambahkan status draft untuk mendukung unpost retur penjualan dari Admin Transaksi.
ALTER TABLE `tbrp_retur_penjualan_header`
MODIFY COLUMN `status_retur` ENUM(
    'draft',
    'menunggu_verifikasi',
    'retur_menunggu_kadepub',
    'retur_menunggu_mngacc',
    'retur_menunggu_mngsc',
    'retur_menunggu_mngse',
    'retur_menunggu_kadepsc',
    'retur_menunggu_dirop',
    'retur_menunggu_dirut',
    'menunggu_collection',
    'menunggu_kasir',
    'selesai',
    'ditolak'
) NOT NULL DEFAULT 'menunggu_verifikasi';
