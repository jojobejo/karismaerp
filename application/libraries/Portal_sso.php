<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Library Adapter SSO Portal KARISMA ERP (Authorization Code + PKCE S256)
 * Menangani pembuatan PKCE, pertukaran code, konfirmasi login-event,
 * pemeriksaan diagnostik check, dan pengelolaan katalog module klien.
 */
class Portal_sso
{
    /** @var CI_Controller */
    protected $CI;

    /** @var array */
    protected $settings = [];

    public function __construct(array $params = [])
    {
        $this->CI = &get_instance();
        $this->CI->config->load('portal_sso', true);
        $configured = $this->CI->config->item('portal_sso', 'portal_sso');
        if (!is_array($configured)) {
            $configured = [];
        }
        $this->settings = array_merge($configured, $params);
    }

    /**
     * Mengambil nilai konfigurasi SSO berdasarkan kunci.
     */
    public function getConfig($key = null, $default = null)
    {
        if ($key === null) {
            return $this->settings;
        }
        return array_key_exists($key, $this->settings) ? $this->settings[$key] : $default;
    }

    /**
     * Memastikan konfigurasi dasar SSO klien sudah lengkap dan memakai HTTPS.
     */
    public function isConfigured()
    {
        $portalBaseUrl = (string) $this->getConfig('portal_base_url', '');
        $clientId = (string) $this->getConfig('client_id', '');
        $clientSecret = (string) $this->getConfig('client_secret', '');
        $redirectUri = (string) $this->getConfig('redirect_uri', '');

        if ($portalBaseUrl === '' || $clientId === '' || $clientSecret === '' || $redirectUri === '') {
            return false;
        }

        return stripos($portalBaseUrl, 'https://') === 0
            && stripos($redirectUri, 'https://') === 0;
    }

    /**
     * Encode string biner ke format Base64URL tanpa padding sesuai RFC 7636.
     */
    public function base64UrlEncode($binaryData)
    {
        return rtrim(strtr(base64_encode((string) $binaryData), '+/', '-_'), '=');
    }

    /**
     * Membuat transaksi Authorization Code + PKCE S256 baru beserta URL authorize Portal.
     */
    public function createAuthorizationRequest($now = null)
    {
        $timestamp = is_int($now) ? $now : time();
        $ttl = max(30, min(120, (int) $this->getConfig('code_ttl_seconds', 120)));

        $state = $this->base64UrlEncode(random_bytes(32));
        $codeVerifier = $this->base64UrlEncode(random_bytes(32));
        $codeChallenge = $this->base64UrlEncode(hash('sha256', $codeVerifier, true));

        $query = http_build_query([
            'client_id' => (string) $this->getConfig('client_id'),
            'redirect_uri' => (string) $this->getConfig('redirect_uri'),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        $authorizeUrl = rtrim((string) $this->getConfig('portal_base_url'), '/') . '/sso/authorize?' . $query;

        return [
            'state' => $state,
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            'issued_at' => $timestamp,
            'expires_at' => $timestamp + $ttl,
            'authorize_url' => $authorizeUrl,
        ];
    }

    /**
     * Memvalidasi state callback terhadap transaksi SSO yang disimpan di session.
     */
    public function validateCallbackTransaction($transaction, $callbackState, $now = null)
    {
        if (!is_array($transaction)) {
            return ['valid' => false, 'error' => 'Transaksi SSO tidak ditemukan atau sudah digunakan.'];
        }

        $timestamp = is_int($now) ? $now : time();
        $expectedState = isset($transaction['state']) ? (string) $transaction['state'] : '';
        $codeVerifier = isset($transaction['code_verifier']) ? (string) $transaction['code_verifier'] : '';
        $expiresAt = isset($transaction['expires_at']) ? (int) $transaction['expires_at'] : 0;

        if ($expectedState === '' || strlen($expectedState) < 16 || strlen($expectedState) > 512) {
            return ['valid' => false, 'error' => 'State transaksi SSO pada session tidak valid.'];
        }

        if (!preg_match('/^[A-Za-z0-9._~-]{43,128}$/', $codeVerifier)) {
            return ['valid' => false, 'error' => 'Verifier PKCE transaksi SSO tidak valid.'];
        }

        if ($expiresAt < $timestamp) {
            return ['valid' => false, 'error' => 'Sesi otorisasi SSO telah kedaluwarsa (melebihi 120 detik). Silakan ulangi login.'];
        }

        $callbackState = (string) $callbackState;
        if ($callbackState === '' || !hash_equals($expectedState, $callbackState)) {
            return ['valid' => false, 'error' => 'Validasi state SSO tidak cocok. Permintaan ditolak.'];
        }

        return [
            'valid' => true,
            'state' => $expectedState,
            'code_verifier' => $codeVerifier,
        ];
    }

    /**
     * Menukar authorization code ke endpoint POST /api/sso/token dan memvalidasi klaim identitas.
     */
    public function exchangeAuthorizationCode($authorizationCode, $codeVerifier, $expectedState)
    {
        $authorizationCode = trim((string) $authorizationCode);
        $codeVerifier = trim((string) $codeVerifier);
        $expectedState = (string) $expectedState;

        if ($authorizationCode === '' || !preg_match('/^[A-Za-z0-9._~-]{43,128}$/', $codeVerifier)) {
            return [
                'success' => false,
                'http_code' => 400,
                'error_code' => 'invalid_request',
                'message' => 'Parameter authorization code atau verifier tidak valid.',
            ];
        }

        $endpoint = rtrim((string) $this->getConfig('portal_base_url'), '/') . '/api/sso/token';
        $response = $this->sendFormPost($endpoint, [
            'client_id' => (string) $this->getConfig('client_id'),
            'client_secret' => (string) $this->getConfig('client_secret'),
            'code' => $authorizationCode,
            'redirect_uri' => (string) $this->getConfig('redirect_uri'),
            'code_verifier' => $codeVerifier,
        ]);

        if (!$response['ok']) {
            $errorCode = isset($response['json']['error']) ? (string) $response['json']['error'] : 'token_exchange_failed';
            return [
                'success' => false,
                'http_code' => $response['http_code'],
                'error_code' => $errorCode,
                'message' => $this->translatePortalError($errorCode, $response['http_code']),
            ];
        }

        $validation = $this->validateTokenClaims($response['json'], $expectedState);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'http_code' => 400,
                'error_code' => 'invalid_claims',
                'message' => $validation['error'],
            ];
        }

        return [
            'success' => true,
            'http_code' => 200,
            'claims' => $validation['claims'],
        ];
    }

    /**
     * Memvalidasi struktur klaim yang dikembalikan oleh POST /api/sso/token.
     */
    public function validateTokenClaims($payload, $expectedState)
    {
        if (!is_array($payload) || empty($payload['success']) || !isset($payload['data']) || !is_array($payload['data'])) {
            return ['valid' => false, 'error' => 'Respons klaim identitas dari Portal tidak valid.'];
        }

        $data = $payload['data'];
        $expectedAppCode = (string) $this->getConfig('client_id');
        $returnedAppCode = isset($data['app_code']) ? (string) $data['app_code'] : '';
        if ($returnedAppCode === '' || !hash_equals($expectedAppCode, $returnedAppCode)) {
            return ['valid' => false, 'error' => 'Klaim app_code tidak sesuai dengan aplikasi ini.'];
        }

        $returnedState = isset($data['state']) ? (string) $data['state'] : '';
        if ($returnedState === '' || !hash_equals((string) $expectedState, $returnedState)) {
            return ['valid' => false, 'error' => 'Klaim state pada respons token tidak cocok.'];
        }

        $user = isset($data['user']) && is_array($data['user']) ? $data['user'] : [];
        $sub = isset($user['sub']) ? trim((string) $user['sub']) : '';
        if (!preg_match('/^(tb_karyawan|tb_users):([1-9][0-9]*)$/D', $sub, $matches)) {
            return ['valid' => false, 'error' => 'Format klaim subject (sub) Portal tidak sah.'];
        }

        $subSource = $matches[1];
        $subUserId = (int) $matches[2];
        $authSource = isset($user['auth_source']) ? trim((string) $user['auth_source']) : '';
        $userId = isset($user['user_id']) ? (int) $user['user_id'] : 0;

        if ($authSource !== $subSource || $userId !== $subUserId) {
            return ['valid' => false, 'error' => 'Klaim sumber identitas dan user_id Portal tidak konsisten dengan sub.'];
        }

        $clientAccountUsername = isset($data['client_account_username'])
            ? trim((string) $data['client_account_username'])
            : '';
        if ($clientAccountUsername === '' || strlen($clientAccountUsername) > 190) {
            return ['valid' => false, 'error' => 'Akun kerja aplikasi klien belum ditetapkan oleh administrator Portal.'];
        }

        return [
            'valid' => true,
            'claims' => [
                'app_code' => $returnedAppCode,
                'state' => $returnedState,
                'client_account_username' => $clientAccountUsername,
                'user' => [
                    'sub' => $sub,
                    'user_id' => $subUserId,
                    'auth_source' => $subSource,
                    'username' => isset($user['username']) ? trim((string) $user['username']) : '',
                    'name' => isset($user['name']) ? trim((string) $user['name']) : '',
                ],
            ],
        ];
    }

    /**
     * Mengirim konfirmasi keberhasilan validasi akun lokal ke POST /api/sso/login-event.
     */
    public function recordLoginEvent($sub, $accountUsername, $sourceIp = null, $userAgent = null, $eventId = null)
    {
        $sub = trim((string) $sub);
        $accountUsername = trim((string) $accountUsername);
        $eventId = $eventId !== null ? strtolower(trim((string) $eventId)) : bin2hex(random_bytes(32));

        if (!preg_match('/^(tb_karyawan|tb_users):([1-9][0-9]*)$/D', $sub)
            || $accountUsername === ''
            || !preg_match('/^[a-f0-9]{64}$/', $eventId)
        ) {
            return [
                'success' => false,
                'http_code' => 400,
                'error_code' => 'invalid_request',
                'message' => 'Parameter konfirmasi login-event tidak valid.',
            ];
        }

        $payload = [
            'client_id' => (string) $this->getConfig('client_id'),
            'client_secret' => (string) $this->getConfig('client_secret'),
            'sub' => $sub,
            'account_username' => $accountUsername,
            'event_id' => $eventId,
        ];

        $ipString = trim((string) $sourceIp);
        if ($ipString !== '' && filter_var($ipString, FILTER_VALIDATE_IP) !== false) {
            $payload['source_ip'] = substr($ipString, 0, 45);
        }

        $uaString = trim((string) $userAgent);
        if ($uaString !== '') {
            $payload['user_agent'] = substr($uaString, 0, 255);
        }

        $endpoint = rtrim((string) $this->getConfig('portal_base_url'), '/') . '/api/sso/login-event';
        $response = $this->sendFormPost($endpoint, $payload);

        if (!$response['ok'] || empty($response['json']['success'])) {
            $errorCode = isset($response['json']['error']) ? (string) $response['json']['error'] : 'login_event_failed';
            return [
                'success' => false,
                'http_code' => $response['http_code'],
                'error_code' => $errorCode,
                'message' => $this->translatePortalError($errorCode, $response['http_code']),
                'event_id' => $eventId,
            ];
        }

        return [
            'success' => true,
            'http_code' => 200,
            'event_id' => $eventId,
        ];
    }

    /**
     * Memeriksa kesiapan konfigurasi dan prasyarat SSO klien ke POST /api/sso/check tanpa membuat session.
     */
    public function checkClientPrerequisites($sub)
    {
        $sub = trim((string) $sub);
        if (!preg_match('/^(tb_karyawan|tb_users):([1-9][0-9]*)$/D', $sub)) {
            return [
                'success' => false,
                'http_code' => 400,
                'error_code' => 'invalid_request',
                'message' => 'Format subject (sub) tidak valid untuk pemeriksaan SSO.',
            ];
        }

        $endpoint = rtrim((string) $this->getConfig('portal_base_url'), '/') . '/api/sso/check';
        $response = $this->sendFormPost($endpoint, [
            'client_id' => (string) $this->getConfig('client_id'),
            'client_secret' => (string) $this->getConfig('client_secret'),
            'sub' => $sub,
            'redirect_uri' => (string) $this->getConfig('redirect_uri'),
        ]);

        if (!$response['ok'] || empty($response['json']['success'])) {
            $errorCode = isset($response['json']['error']) ? (string) $response['json']['error'] : 'check_failed';
            return [
                'success' => false,
                'http_code' => $response['http_code'],
                'error_code' => $errorCode,
                'message' => $this->translatePortalError($errorCode, $response['http_code']),
            ];
        }

        return [
            'success' => true,
            'http_code' => $response['http_code'],
            'data' => isset($response['json']['data']) ? $response['json']['data'] : [],
        ];
    }

    /**
     * Mengambil daftar katalog module aplikasi klien dari Portal (GET /api/client/modules).
     */
    public function listModules()
    {
        return $this->sendModuleCatalogRequest('GET', '/api/client/modules');
    }

    /**
     * Mengambil detail satu module aplikasi klien dari Portal (GET /api/client/modules/{module_code}).
     */
    public function getModule($moduleCode)
    {
        $moduleCode = rawurlencode(trim((string) $moduleCode));
        return $this->sendModuleCatalogRequest('GET', '/api/client/modules/' . $moduleCode);
    }

    /**
     * Menambahkan module baru ke katalog aplikasi klien di Portal (POST /api/client/modules).
     */
    public function createModule(array $modulePayload)
    {
        return $this->sendModuleCatalogRequest('POST', '/api/client/modules', $modulePayload);
    }

    /**
     * Memperbarui module pada katalog aplikasi klien di Portal (PUT /api/client/modules/{module_code}).
     */
    public function updateModule($moduleCode, array $modulePayload)
    {
        $moduleCode = rawurlencode(trim((string) $moduleCode));
        return $this->sendModuleCatalogRequest('PUT', '/api/client/modules/' . $moduleCode, $modulePayload);
    }

    /**
     * Menghapus module dari katalog aplikasi klien di Portal (DELETE /api/client/modules/{module_code}).
     */
    public function deleteModule($moduleCode)
    {
        $moduleCode = rawurlencode(trim((string) $moduleCode));
        return $this->sendModuleCatalogRequest('DELETE', '/api/client/modules/' . $moduleCode);
    }

    /**
     * Menerjemahkan kode error Portal menjadi pesan yang aman untuk pengguna.
     */
    public function translatePortalError($errorCode, $httpCode = 0)
    {
        $map = [
            'invalid_request' => 'Permintaan autentikasi SSO tidak valid. Silakan ulangi login dari Portal.',
            'invalid_grant' => 'Kode otorisasi SSO tidak valid, sudah digunakan, atau telah kedaluwarsa.',
            'invalid_client' => 'Konfigurasi kredensial klien SSO tidak valid atau aplikasi sedang dinonaktifkan.',
            'account_not_assigned' => 'Akun kerja lokal untuk aplikasi Penilaian Lingkungan belum ditetapkan oleh administrator Portal.',
            'access_denied' => 'Anda tidak memiliki izin akses untuk masuk ke aplikasi Penilaian Lingkungan.',
            'login_event_rejected' => 'Konfirmasi histori login SSO ditolak oleh Portal karena penetapan akun tidak cocok.',
            'method_not_allowed' => 'Metode permintaan HTTP tidak diizinkan oleh endpoint SSO.',
            'temporarily_unavailable' => 'Layanan SSO Portal sedang tidak tersedia sementara. Silakan coba beberapa saat lagi.',
        ];

        $key = strtolower(trim((string) $errorCode));
        if (isset($map[$key])) {
            return $map[$key];
        }

        if ((int) $httpCode === 403) {
            return 'Akses ke aplikasi ditolak oleh Portal SSO.';
        }
        if ((int) $httpCode >= 500 || (int) $httpCode === 0) {
            return 'Gagal menghubungi layanan SSO Portal melalui HTTPS. Periksa koneksi jaringan/sertifikat server.';
        }

        return 'Autentikasi SSO gagal diproses. Silakan coba kembali.';
    }

    /**
     * Mengirim HTTP POST dengan Content-Type application/x-www-form-urlencoded melalui HTTPS cURL.
     */
    protected function sendFormPost($url, array $fields)
    {
        if (stripos((string) $url, 'https://') !== 0) {
            return [
                'ok' => false,
                'http_code' => 0,
                'json' => ['error' => 'insecure_endpoint'],
            ];
        }

        $timeout = max(5, (int) $this->getConfig('http_timeout_seconds', 15));
        $body = http_build_query($fields, '', '&', PHP_QUERY_RFC3986);

        $ch = curl_init($url);
        $curlOptions = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
                'Cache-Control: no-store',
            ],
        ];

        $caBundle = trim((string) $this->getConfig('ca_bundle', ''));
        if ($caBundle !== '' && is_file($caBundle)) {
            $curlOptions[CURLOPT_CAINFO] = $caBundle;
        }

        curl_setopt_array($ch, $curlOptions);
        $rawBody = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($rawBody === false || $curlErrno !== 0) {
            return [
                'ok' => false,
                'http_code' => 0,
                'json' => ['error' => 'temporarily_unavailable'],
            ];
        }

        $decoded = json_decode((string) $rawBody, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        return [
            'ok' => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'json' => $decoded,
        ];
    }

    /**
     * Mengirim request HTTPS ke endpoint Katalog Module Klien menggunakan HTTP Basic Auth.
     */
    protected function sendModuleCatalogRequest($method, $path, array $jsonBody = null)
    {
        $url = rtrim((string) $this->getConfig('portal_base_url'), '/') . '/' . ltrim((string) $path, '/');
        if (stripos($url, 'https://') !== 0) {
            return [
                'ok' => false,
                'http_code' => 0,
                'json' => ['error' => 'insecure_endpoint'],
            ];
        }

        $timeout = max(5, (int) $this->getConfig('http_timeout_seconds', 15));
        $clientId = (string) $this->getConfig('client_id');
        $clientSecret = (string) $this->getConfig('client_secret');
        $basicAuth = base64_encode($clientId . ':' . $clientSecret);

        $headers = [
            'Authorization: Basic ' . $basicAuth,
            'Accept: application/json',
            'Cache-Control: no-store',
        ];

        $ch = curl_init($url);
        $curlOptions = [
            CURLOPT_CUSTOMREQUEST => strtoupper((string) $method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if ($jsonBody !== null) {
            $encoded = json_encode($jsonBody, JSON_UNESCAPED_SLASHES);
            $curlOptions[CURLOPT_POSTFIELDS] = $encoded;
            $headers[] = 'Content-Type: application/json';
        }

        $curlOptions[CURLOPT_HTTPHEADER] = $headers;

        $caBundle = trim((string) $this->getConfig('ca_bundle', ''));
        if ($caBundle !== '' && is_file($caBundle)) {
            $curlOptions[CURLOPT_CAINFO] = $caBundle;
        }

        curl_setopt_array($ch, $curlOptions);
        $rawBody = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($rawBody === false || $curlErrno !== 0) {
            return [
                'ok' => false,
                'http_code' => 0,
                'json' => ['error' => 'temporarily_unavailable'],
            ];
        }

        $decoded = json_decode((string) $rawBody, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        return [
            'ok' => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'json' => $decoded,
        ];
    }
}
