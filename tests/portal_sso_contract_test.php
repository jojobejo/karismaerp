<?php
// Jalankan dengan PHP CLI; seluruh data uji berada di memori, tanpa database aplikasi.
define('BASEPATH', __DIR__);
class CI_Model { public $db; public $load; public $portalAccess; public $clientAccounts; }
require __DIR__ . '/../application/models/M_PortalSso.php';
require __DIR__ . '/../application/models/M_PortalSsoClientAccounts.php';
require __DIR__ . '/../application/models/M_PortalAccess.php';
require __DIR__ . '/../application/models/M_PortalSsoDiagnostics.php';

function verify($condition, $label) {
    if (!$condition) throw new RuntimeException('GAGAL: ' . $label);
    echo 'LULUS: ' . $label . PHP_EOL;
}
class TestResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function row_array() { return $this->rows[0] ?? null; }
    public function result_array() { return $this->rows; }
}
class TestDb {
    public $tables = [];
    private $filters = [];
    private $table;
    private $snapshot;
    private $affected = 0;
    public function table_exists($table) { return isset($this->tables[$table]); }
    public function select($fields) { return $this; }
    public function from($table) { $this->table = $table; return $this; }
    public function join($table, $condition, $type) {
        verify(strpos($condition, 'c.app_code = a.app_code') !== false, 'Join assignment terikat aplikasi');
        return $this;
    }
    public function limit($count) { return $this; }
    public function where($key, $value = null, $escape = true) {
        if (is_array($key)) foreach ($key as $k => $v) $this->filters[$k] = $v;
        else $this->filters[$key] = $value;
        return $this;
    }
    private function matches($row) {
        foreach ($this->filters as $key => $value) {
            $key = preg_replace('/^a\./', '', $key);
            if ($key === 'used_at IS NULL') { if ($row['used_at'] !== null) return false; }
            elseif ($key === 'expires_at >=') { if ($row['expires_at'] < $value) return false; }
            elseif (!array_key_exists($key, $row) || $row[$key] != $value) return false;
        }
        return true;
    }
    public function get($table = null) {
        $table = $table ?: $this->table;
        $rows = array_values(array_filter($this->tables[$table] ?? [], function ($row) { return $this->matches($row); }));
        $this->filters = [];
        return new TestResult($rows);
    }
    public function get_where($table, $where) { return $this->where($where)->get($table); }
    public function count_all_results($table) { return count($this->get($table)->result_array()); }
    public function trans_begin() { $this->snapshot = $this->tables; }
    public function trans_rollback() { $this->tables = $this->snapshot; }
    public function trans_commit() { return true; }
    public function trans_status() { return true; }
    public function affected_rows() { return $this->affected; }
    public function update($table, $data) {
        $this->affected = 0;
        foreach ($this->tables[$table] as &$row) if ($this->matches($row)) { $row = array_merge($row, $data); $this->affected++; }
        unset($row);
        $this->filters = [];
        return true;
    }
}
class TestLoader { public function model($name, $alias) {} }
class TestAccess extends M_PortalAccess {
    public function apps() { return ['test' => []]; }
}

function fixture() {
    $db = new TestDb();
    $verifier = str_repeat('v', 43);
    $db->tables = [
        'tb_portal_sso_client' => [['app_code' => 'test', 'is_enabled' => 1, 'redirect_uri' => 'https://client.test/callback', 'launch_uri' => 'https://client.test/start', 'client_secret_hash' => hash('sha256', 'test-secret')]],
        'tb_portal_sso_code' => [['id' => 1, 'app_code' => 'test', 'code_hash' => hash('sha256', 'test-code'), 'redirect_uri' => 'https://client.test/callback', 'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='), 'used_at' => null, 'expires_at' => date('Y-m-d H:i:s', time() + 120), 'auth_source' => 'tb_users', 'user_id' => 7, 'state' => 'test-state-123456789']],
        'tb_users' => [['id' => 7, 'status' => 1, 'username' => 'portal-user']],
        'tb_portal_app_access' => [['app_code' => 'test', 'auth_source' => 'tb_users', 'user_id' => 7, 'is_allowed' => 1]],
        'tb_portal_sso_client_account' => [], 'tb_portal_sso_client_account_assignment' => [],
        'tb_portal_sso_client_account_assignment a' => [['app_code' => 'test', 'auth_source' => 'tb_users', 'user_id' => 7, 'account_id' => 9, 'username' => 'local-assigned']],
        'tb_portal_sso_login_history' => [],
    ];
    $sso = new M_PortalSso(); $sso->db = $db; $sso->load = new TestLoader();
    $sso->portalAccess = new TestAccess(); $sso->portalAccess->db = $db;
    $sso->clientAccounts = new M_PortalSsoClientAccounts(); $sso->clientAccounts->db = $db;
    return $sso;
}
function exchange($sso, $secret = 'test-secret', $code = 'test-code', $callback = 'https://client.test/callback', $verifier = null) {
    return $sso->exchange_code('test', $secret, $code, $callback, $verifier ?? str_repeat('v', 43));
}
$sso = fixture();
$claims = exchange($sso);
verify($claims && $claims['client_account_username'] === 'local-assigned' && $claims['sub'] === 'tb_users:7', 'Token membawa akun yang ditetapkan dan subject stabil');
verify(!isset($claims['password']) && !isset($claims['client_secret']), 'Klaim tidak memuat password atau secret');
verify(exchange($sso) === false, 'Code sekali pakai menolak replay');
foreach (['secret', 'code', 'callback', 'verifier'] as $field) {
    $args = ['test-secret', 'test-code', 'https://client.test/callback', str_repeat('v', 43)];
    $args[array_search($field, ['secret', 'code', 'callback', 'verifier'])] = 'salah';
    verify(exchange(fixture(), ...$args) === false, $field . ' salah ditolak');
}
foreach (['expired', 'disabled', 'inactive_user', 'access_revoked', 'access_missing', 'assignment_missing', 'assignment_duplicate', 'assignment_empty', 'assignment_table_missing'] as $case) {
    $sso = fixture();
    if ($case === 'expired') $sso->db->tables['tb_portal_sso_code'][0]['expires_at'] = date('Y-m-d H:i:s', time() - 1);
    if ($case === 'disabled') $sso->db->tables['tb_portal_sso_client'][0]['is_enabled'] = 0;
    if ($case === 'inactive_user') $sso->db->tables['tb_users'][0]['status'] = 0;
    if ($case === 'access_revoked') $sso->db->tables['tb_portal_app_access'][0]['is_allowed'] = 0;
    if ($case === 'access_missing') $sso->db->tables['tb_portal_app_access'] = [];
    if ($case === 'assignment_missing') $sso->db->tables['tb_portal_sso_client_account_assignment a'] = [];
    if ($case === 'assignment_duplicate') $sso->db->tables['tb_portal_sso_client_account_assignment a'][] = $sso->db->tables['tb_portal_sso_client_account_assignment a'][0];
    if ($case === 'assignment_empty') $sso->db->tables['tb_portal_sso_client_account_assignment a'][0]['username'] = '';
    if ($case === 'assignment_table_missing') unset($sso->db->tables['tb_portal_sso_client_account_assignment']);
    verify(exchange($sso) === false, $case . ' ditolak');
}
$sso = fixture();
$diagnostics = new M_PortalSsoDiagnostics();
$diagnostics->db = $sso->db; $diagnostics->load = $sso->load;
$diagnostics->portalSso = $sso; $diagnostics->portalAccess = $sso->portalAccess; $diagnostics->clientAccounts = $sso->clientAccounts;
$snapshot = $sso->db->tables;
$report = $diagnostics->inspect('test', 'tb_users:7', 'https://client.test/callback');
verify($report['portal_ready'] && !$report['client_session_verified'], 'Diagnosis tidak mengklaim session klien terverifikasi');
verify($snapshot === $sso->db->tables, 'Diagnosis tidak mengubah data');
verify(!$diagnostics->inspect('test', 'tb_users:7', 'https://wrong.test/callback')['portal_ready'], 'Diagnosis mendeteksi callback berbeda');
verify(!$diagnostics->inspect('test', 'invalid:7', 'https://client.test/callback')['portal_ready'], 'Diagnosis menolak sumber identitas tidak valid');
class CI_Controller {}
require __DIR__ . '/../application/controllers/Sso.php';
class TestInput {
    public $verb = 'POST'; public $fields = [];
    public function method($upper = false) { return $this->verb; }
    public function post($key, $clean = false) { return $this->fields[$key] ?? null; }
}
class TestOutput {
    public $status; public $body; public $headers = [];
    public function set_header($value) { $this->headers[] = $value; return $this; }
    public function set_status_header($value) { $this->status = $value; return $this; }
    public function set_content_type($type, $charset) { return $this; }
    public function set_output($value) { $this->body = json_decode($value, true); return $this; }
}
class TestSsoController extends Sso {
    public $input; public $output; public $portalSso; public $load; public $diagnostics;
    public function __construct() {}
}
$controller = new TestSsoController();
$controller->input = new TestInput(); $controller->output = new TestOutput();
$controller->portalSso = $sso; $controller->load = new TestLoader(); $controller->diagnostics = $diagnostics;
$controller->input->verb = 'GET'; $controller->check();
verify($controller->output->status === 405, 'API check menolak GET');
$controller->input->verb = 'POST'; $controller->check();
verify($controller->output->status === 401, 'API check menolak tanpa kredensial');
$controller->input->fields = ['client_id' => 'test', 'client_secret' => 'test-secret', 'sub' => 'tb_users:7', 'redirect_uri' => 'https://client.test/callback'];
$controller->check();
verify($controller->output->status === 200 && $controller->output->body['data']['portal_ready'], 'API check menerima kredensial dan parameter valid');
verify(in_array('Cache-Control: no-store', $controller->output->headers, true), 'Respons API tidak disimpan cache');
$controller->input->fields['client_id'] = 'other'; $controller->check();
verify($controller->output->status === 401, 'Secret aplikasi tidak dapat dipakai untuk aplikasi lain');
$controller->input->fields['client_id'] = 'test'; $controller->input->fields['sub'] = 'invalid'; $controller->check();
verify($controller->output->status === 400, 'API check menolak sub tidak valid');
echo 'Seluruh pengujian kontrak selesai.' . PHP_EOL;
