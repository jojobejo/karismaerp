<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Konfigurasi Integrasi SSO Portal KARISMA ERP
 * Aplikasi Klien: Penilaian Lingkungan (penilaian_lingkungan)
 */
$readEnv = static function (array $keys, $default = '') {
    foreach ($keys as $key) {
        $value = getenv($key);
        if ($value !== false && trim((string) $value) !== '') {
            return trim((string) $value);
        }
        if (isset($_ENV[$key]) && trim((string) $_ENV[$key]) !== '') {
            return trim((string) $_ENV[$key]);
        }
        if (isset($_SERVER[$key]) && trim((string) $_SERVER[$key]) !== '') {
            return trim((string) $_SERVER[$key]);
        }
    }
    return $default;
};

$config['portal_sso'] = [
    // Base URL Portal SSO KARISMA ERP
    'portal_base_url' => rtrim(
        $readEnv(
            ['PORTAL_BASE_URL', 'KARISMAERP_SSO_PORTAL_BASE_URL'],
            'https://portal.karismaerp.com'
        ),
        '/'
    ),

    // Kode aplikasi (client_id) yang terdaftar di Portal
    'client_id' => $readEnv(
        ['PORTAL_CLIENT_ID', 'KARISMAERP_SSO_CLIENT_ID'],
        'penilaian_lingkungan'
    ),

    // Secret key klien SSO (disimpan di backend server, jangan pernah dikirim ke browser)
    'client_secret' => $readEnv(
        ['PORTAL_CLIENT_SECRET', 'KARISMAERP_SSO_CLIENT_SECRET'],
        'b0e5806df880c63a1410144f8f4bc9d5dac58a5d1f84723bbccfeac008124172'
    ),

    // URL peluncuran awal SSO klien yang terdaftar di Portal
    'launch_uri' => $readEnv(
        ['PORTAL_LAUNCH_URL', 'KARISMAERP_SSO_LAUNCH_URI'],
        'https://lingkungan.karismaerp.com/sso/login'
    ),

    // Callback HTTPS terdaftar di Portal (wajib sama persis saat authorize dan token exchange)
    'redirect_uri' => $readEnv(
        ['PORTAL_CALLBACK_URL', 'KARISMAERP_SSO_REDIRECT_URI'],
        'https://lingkungan.karismaerp.com/sso/callback'
    ),

    // Masa berlaku transaksi state/PKCE di session lokal (detik) - sesuai batas code Portal 120 detik
    'code_ttl_seconds' => 120,

    // Timeout koneksi HTTP backend-ke-backend (detik)
    'http_timeout_seconds' => 15,

    // Path CA bundle opsional untuk verifikasi sertifikat TLS cURL
    'ca_bundle' => $readEnv(
        ['PORTAL_CA_BUNDLE', 'KARISMAERP_SSO_CA_BUNDLE', 'CURL_CA_BUNDLE', 'SSL_CERT_FILE'],
        ''
    ),

    // Kunci session penyimpanan transaksi PKCE sementara
    'session_key' => 'portal_sso',
];
