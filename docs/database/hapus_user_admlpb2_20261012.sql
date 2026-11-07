-- Menghapus akun autentikasi ADMLPB2 setelah akses retur dialihkan ke ADMLPB.
-- Log login dan log akses historis tetap dipertahankan untuk audit.

START TRANSACTION;

DELETE FROM tb_karyawan
WHERE LOWER(username) = 'admlpb2';

DELETE FROM tb_users
WHERE LOWER(username) = 'admlpb2';

COMMIT;
