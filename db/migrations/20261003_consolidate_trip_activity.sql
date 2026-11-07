-- Konsolidasikan aktivitas ganda yang sempat terbentuk untuk trip yang sama.
UPDATE tb_loading_lk utama
JOIN (SELECT id_trip, MIN(id) id_utama, MAX(id) id_terbaru FROM tb_loading_lk WHERE id_trip IS NOT NULL AND is_archived = 0 GROUP BY id_trip HAVING COUNT(*) > 1) g
  ON g.id_utama = utama.id
JOIN tb_loading_lk terbaru ON terbaru.id = g.id_terbaru
SET utama.status = terbaru.status,
    utama.waktu_mulai = IF(terbaru.status = 'SIAP_LOADING', NULL, utama.waktu_mulai),
    utama.waktu_selesai = IF(terbaru.status = 'SIAP_LOADING', NULL, utama.waktu_selesai),
    utama.progres = IF(terbaru.status = 'SIAP_LOADING', 0, utama.progres);
UPDATE tb_loading_lk l
JOIN (SELECT id_trip, MIN(id) id_utama FROM tb_loading_lk WHERE id_trip IS NOT NULL AND is_archived = 0 GROUP BY id_trip HAVING COUNT(*) > 1) g
  ON g.id_trip = l.id_trip AND l.id <> g.id_utama
SET l.is_archived = 1, l.archived_at = NOW(), l.archived_by = 'migration-consolidate-trip';

UPDATE tb_loading_kk utama
JOIN (SELECT id_trip, MIN(id) id_utama, MAX(id) id_terbaru FROM tb_loading_kk WHERE id_trip IS NOT NULL AND is_archived = 0 GROUP BY id_trip HAVING COUNT(*) > 1) g
  ON g.id_utama = utama.id
JOIN tb_loading_kk terbaru ON terbaru.id = g.id_terbaru
SET utama.status = terbaru.status,
    utama.waktu_mulai = IF(terbaru.status = 'SIAP_LOADING', NULL, utama.waktu_mulai),
    utama.waktu_selesai = IF(terbaru.status = 'SIAP_LOADING', NULL, utama.waktu_selesai),
    utama.progres = IF(terbaru.status = 'SIAP_LOADING', 0, utama.progres);
UPDATE tb_loading_kk l
JOIN (SELECT id_trip, MIN(id) id_utama FROM tb_loading_kk WHERE id_trip IS NOT NULL AND is_archived = 0 GROUP BY id_trip HAVING COUNT(*) > 1) g
  ON g.id_trip = l.id_trip AND l.id <> g.id_utama
SET l.is_archived = 1, l.archived_at = NOW(), l.archived_by = 'migration-consolidate-trip';

-- SO tambahan mewarisi plan pengiriman SO awal dalam trip yang sama.
UPDATE tbso_sales_order tambahan
JOIN tbso_sales_order awal ON awal.id_trip = tambahan.id_trip AND awal.is_additional_load = 0
SET tambahan.loading_tgl_pengiriman = awal.loading_tgl_pengiriman,
    tambahan.loading_jenis_pengiriman = awal.loading_jenis_pengiriman,
    tambahan.loading_driver = awal.loading_driver,
    tambahan.loading_nolambung = awal.loading_nolambung
WHERE tambahan.is_additional_load = 1 AND awal.loading_tgl_pengiriman IS NOT NULL;
