-- ============================================================================
-- Migrasi: Penambahan Gudang Test Market (Transit) untuk Alur SO Test Market
-- ============================================================================

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`)
SELECT 'Gudang Test Market', 'INDUK', 1, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` IN ('Gudang Test Market', 'Gdg. Test Market')
);
