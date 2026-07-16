<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_SalesOrder extends CI_Controller
{
    private $plafon_fetch_completed = true;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_SalesOrder');
        $this->load->model('M_Stock');
        $this->load->model('M_Logistik');
        $this->load->model('M_ActivityLog');
        $this->load->model('M_Checker');
        $this->load->library(['form_validation', 'session', 'pagination']);
        $this->load->helper(['url', 'form']);
        $this->config->load('plafon_api', false, true);
    }

    // ================================================================
    // HELPER — user session
    // ================================================================
    private function _getCurrentUser()
    {
        $id   = $this->session->userdata('id_karyawan')
             ?? $this->session->userdata('id')
             ?? $this->session->userdata('user_id')
             ?? null;

        $usn  = $this->session->userdata('username')
             ?? $this->session->userdata('user_name')
             ?? $this->session->userdata('login')
             ?? null;

        $nama = $this->session->userdata('nm_karyawan')
             ?? $this->session->userdata('nama')
             ?? $this->session->userdata('name')
             ?? null;

        $wil  = $this->session->userdata('wilayah')
             ?? $this->session->userdata('wilayah_id')
             ?? $this->session->userdata('gudang_id')
             ?? null;

        if (!empty($nama) && !empty($wil)) {
            return ['nm_karyawan' => $nama, 'wilayah' => $wil, 'username' => $usn ?? $nama];
        }

        $row = null;
        if (!empty($id))  $row = $this->db->get_where('tb_karyawan', ['id'       => $id])->row_array();
        if (!$row && !empty($usn)) $row = $this->db->get_where('tb_karyawan', ['username' => $usn])->row_array();

        if ($row) {
            return [
                'nm_karyawan' => $row['nm_karyawan'] ?? 'system',
                'wilayah'     => $row['wilayah']     ?? '',
                'username'    => $row['username']    ?? 'system',
            ];
        }
        return ['nm_karyawan' => 'system', 'wilayah' => '', 'username' => 'system'];
    }

    private function _getUsername()  { return $this->_getCurrentUser()['nm_karyawan']; }

    private function _getCustomersForCurrentSales()
    {
        if (!$this->_isRestrictedSalesUser()) {
            return $this->_attachPlafonToCustomers($this->M_SalesOrder->get_customers());
        }

        return $this->_attachPlafonToCustomers($this->M_SalesOrder->get_customers($this->_getUsername()));
    }

    private function _attachPlafonToCustomers(array $customers)
    {
        foreach ($customers as &$customer) {
            $customer['plafon_aktif']      = $customer['plafon_aktif']      ?? null;
            $customer['piutang']           = null;
            $customer['plafon_status']     = null;
            $customer['plafon_updated_at'] = $customer['plafon_updated_at'] ?? null;
        }
        unset($customer);
        return $customers;
    }

    private function _getPlafonCustomerMap($force_refresh = false)
    {
        $this->plafon_fetch_completed = true;

        $base_url = rtrim((string)$this->config->item('plafon_api_base_url'), '/');
        $api_key  = (string)$this->config->item('plafon_api_key');

        if ($base_url === '' || $api_key === '' || !function_exists('curl_init')) {
            $this->plafon_fetch_completed = false;
            return [];
        }

        $timeout   = 30;
        $max_pages = max(1, (int)($this->config->item('plafon_api_max_pages') ?: 100));

        // ── Langkah 1: fetch halaman pertama untuk tahu total halaman ──
        $first_url = $base_url . '/api/customers?per_page=100&page=1';
        $ch = curl_init($first_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: Bearer ' . $api_key,
            ],
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => $timeout,
        ]);
        $body      = curl_exec($ch);
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $curl_err !== '' || $http_code < 200 || $http_code >= 300) {
            $this->plafon_fetch_completed = false;
            return [];
        }

        $first_payload = json_decode($body, true);
        if (!is_array($first_payload) || empty($first_payload['data'])) {
            $this->plafon_fetch_completed = false;
            return [];
        }

        $last_page = (int)($first_payload['meta']['last_page'] ?? 1);
        $last_page = min($last_page, $max_pages);

        // Kumpulkan data halaman pertama
        $map = [];
        foreach ($first_payload['data'] as $row) {
            $kode = strtoupper(trim((string)($row['kode_customer'] ?? '')));
            if ($kode === '') continue;
            $map[$kode] = ['plafon_aktif' => $row['plafon_aktif'] ?? null];
        }

        // ── Langkah 2: fetch halaman 2 dst secara paralel ──
        if ($last_page > 1) {
            $mh      = curl_multi_init();
            $handles = [];

            for ($page = 2; $page <= $last_page; $page++) {
                $url = $base_url . '/api/customers?per_page=100&page=' . $page;
                $ch  = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => [
                        'Accept: application/json',
                        'Authorization: Bearer ' . $api_key,
                    ],
                    CURLOPT_CONNECTTIMEOUT => 15,
                    CURLOPT_TIMEOUT        => $timeout,
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$page] = $ch;
            }

            // Jalankan semua request paralel
            $running = null;
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh);
            } while ($running > 0);

            // Ambil hasil semua halaman
            foreach ($handles as $page => $ch) {
                $page_body = curl_multi_getcontent($ch);
                $page_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);

                if (!$page_body || $page_code < 200 || $page_code >= 300) continue;

                $page_payload = json_decode($page_body, true);
                if (!is_array($page_payload) || empty($page_payload['data'])) continue;

                foreach ($page_payload['data'] as $row) {
                    $kode = strtoupper(trim((string)($row['kode_customer'] ?? '')));
                    if ($kode === '') continue;
                    $map[$kode] = ['plafon_aktif' => $row['plafon_aktif'] ?? null];
                }
            }

            curl_multi_close($mh);
        }

        $this->plafon_fetch_completed = true;

        // ── Langkah 3: simpan ke DB dalam satu batch ──
        if (!empty($map)) {
            $this->_ensurePlafonColumns();
            $this->_savePlafonBatch($map);
        }

        return $map;
    }

    private function _savePlafonBatch(array $map)
    {
        if (empty($map)) return;

        $now        = date('Y-m-d H:i:s');
        $chunk_size = 200; // proses per 200 customer
        $chunks     = array_chunk($map, $chunk_size, true);

        foreach ($chunks as $chunk) {
            $case_plafon = '';
            $kode_list   = [];

            foreach ($chunk as $kode => $plafon) {
                $kode_esc     = $this->db->escape(strtoupper($kode));
                $plafon_val   = $plafon['plafon_aktif'] !== null
                    ? (float)$plafon['plafon_aktif']
                    : 'NULL';
                $case_plafon .= " WHEN UPPER(TRIM(kd_customer)) = {$kode_esc} THEN {$plafon_val}";
                $kode_list[]  = $kode_esc;
            }

            $in_clause = implode(',', $kode_list);

            $this->db->query("
                UPDATE tb_customer
                SET
                    plafon_aktif      = CASE {$case_plafon} ELSE plafon_aktif END,
                    plafon_updated_at = '{$now}'
                WHERE UPPER(TRIM(kd_customer)) IN ({$in_clause})
            ");
        }
    }

    public function refresh_plafon_customers()
    {
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $map = $this->_getPlafonCustomerMap(true);

        if (empty($map) || !$this->plafon_fetch_completed) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Gagal mengambil data plafon customer dari API.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'status'     => 'ok',
            'message'    => 'Data plafon customer berhasil diperbarui ke database.',
            'count'      => count($map),
            'updated_at' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function _validateCustomerForCurrentSales($kd_customer, $redirect_url)
    {
        if (!$this->_isRestrictedSalesUser()) {
            return true;
        }

        if ($this->M_SalesOrder->is_customer_for_sales($kd_customer, $this->_getUsername())) {
            return true;
        }

        $this->session->set_flashdata('error', 'Customer tidak sesuai dengan nama sales Anda.');
        redirect($redirect_url);
        return false;
    }

    private function _isRestrictedSalesUser()
    {
        return strtoupper((string)$this->session->userdata('jobdesk')) === 'SC';
    }

    private function _canAccessAdminSc()
    {
        return in_array(strtoupper((string)$this->session->userdata('jobdesk')), ['ADMINSC', 'SC', 'SALESCOUNTER', 'ADMIN'], true);
    }

    private function _isAdminScOnlyUser()
    {
        return in_array(strtoupper((string)$this->session->userdata('jobdesk')), ['ADMINSC', 'SALESCOUNTER'], true);
    }

    private function _denyAdminScAccess()
    {
        $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman Admin SC.');
        redirect('sales_order');
    }

    private function _canAccessSo($so)
    {
        if (!$this->_isRestrictedSalesUser()) return true;
        if (empty($so)) return false;

        return (string)($so['create_by'] ?? '') === (string)$this->_getUsername();
    }

    private function _denySoAccess()
    {
        $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk membuka Sales Order milik SC lain.');
        redirect('sales_order');
    }

    private function _getFakturUserPrefix()
    {
        $this->_ensureFakturPrefixColumn();

        $current_id = $this->session->userdata('id_karyawan')
            ?? $this->session->userdata('id')
            ?? $this->session->userdata('user_id')
            ?? null;
        $current_username = $this->session->userdata('username')
            ?? $this->session->userdata('user_name')
            ?? $this->session->userdata('login')
            ?? null;
        $current_name = $this->session->userdata('nm_karyawan')
            ?? $this->session->userdata('nama')
            ?? $this->session->userdata('name')
            ?? null;

        $users = $this->db
            ->select('id, username, nm_karyawan, faktur_prefix')
            ->where_in('jobdesk', ['ADMINSC', 'SC', 'SALES', 'SALESCOUNTER', 'SALESONLINE'])
            ->order_by('id', 'ASC')
            ->get('tb_karyawan')
            ->result_array();
        $users = $this->_seedMissingFakturPrefixes($users);

        $index = $this->_findUserPrefixIndex($users, $current_id, $current_username, $current_name);
        $current_user = $index === null ? null : $users[$index];

        if ($index === null) {
            $users = $this->db
                ->select('id, username, nm_karyawan, faktur_prefix')
                ->order_by('id', 'ASC')
                ->get('tb_karyawan')
                ->result_array();
            $users = $this->_seedMissingFakturPrefixes($users);
            $index = $this->_findUserPrefixIndex($users, $current_id, $current_username, $current_name);
            $current_user = $index === null ? null : $users[$index];
        }

        if (!$current_user) {
            return 'X';
        }

        $saved_prefix = $this->_normalizeFakturPrefix($current_user['faktur_prefix'] ?? '');
        if ($saved_prefix !== '') {
            return $saved_prefix;
        }

        $legacy_prefix = $this->_numberToFakturPrefix($index + 1);
        $prefix = $this->_getAvailableFakturPrefix($legacy_prefix, $current_user['id']);

        $this->db
            ->where('id', $current_user['id'])
            ->update('tb_karyawan', ['faktur_prefix' => $prefix]);

        return $prefix;
    }

    private function _seedMissingFakturPrefixes(array $users)
    {
        foreach ($users as $index => &$user) {
            $saved_prefix = $this->_normalizeFakturPrefix($user['faktur_prefix'] ?? '');
            if ($saved_prefix !== '') {
                $user['faktur_prefix'] = $saved_prefix;
                continue;
            }

            $legacy_prefix = $this->_numberToFakturPrefix($index + 1);
            $prefix = $this->_getAvailableFakturPrefix($legacy_prefix, $user['id']);
            $this->db
                ->where('id', $user['id'])
                ->update('tb_karyawan', ['faktur_prefix' => $prefix]);

            $user['faktur_prefix'] = $prefix;
        }
        unset($user);

        return $users;
    }

    private function _ensureFakturPrefixColumn()
    {
        if ($this->db->field_exists('faktur_prefix', 'tb_karyawan')) {
            return;
        }

        $this->load->dbforge();
        $this->dbforge->add_column('tb_karyawan', [
            'faktur_prefix' => [
                'type'       => 'VARCHAR',
                'constraint' => 4,
                'null'       => true,
                'after'      => 'username',
            ],
        ]);
    }

    private function _ensurePlafonColumns()
    {
        if ($this->db->field_exists('plafon_aktif', 'tb_customer')) {
            return;
        }

        $this->load->dbforge();
        $this->dbforge->add_column('tb_customer', [
            'plafon_aktif' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => true,
                'after'      => 'nama_kios',
            ],
        ]);
        $this->dbforge->add_column('tb_customer', [
            'plafon_updated_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'plafon_aktif',
            ],
        ]);
    }

    private function _ensureSoFakturZColumn()
    {
        if ($this->db->field_exists('is_faktur_z', 'tbso_sales_order')) {
            return;
        }

        $this->load->dbforge();
        $this->dbforge->add_column('tbso_sales_order', [
            'is_faktur_z' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'catatan',
            ],
        ]);
    }

    private function _ensureSoRouteColumn()
    {
        if ($this->db->field_exists('kd_rute', 'tbso_sales_order')) {
            return;
        }

        $this->load->dbforge();
        $this->dbforge->add_column('tbso_sales_order', [
            'kd_rute' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'kd_customer',
            ],
        ]);
    }

    private function _ensureSoCaraPembayaranColumn()
    {
        if ($this->db->field_exists('cara_pembayaran', 'tbso_sales_order')) {
            return;
        }

        $this->load->dbforge();
        $this->dbforge->add_column('tbso_sales_order', [
            'cara_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => 'cash',
                'after'      => 'catatan',
            ],
        ]);
    }

    private function _ensureFakturPaymentInfoColumns()
    {
        $this->load->dbforge();

        foreach ([
            'tanggal_jatuh_tempo' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'tanggal_faktur',
            ],
            'salesman' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'tanggal_jatuh_tempo',
            ],
            'cara_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'salesman',
            ],
            'jtempo' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'cara_pembayaran',
            ],
            'tempo' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'jtempo',
            ],
            'parent_id_faktur' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'tempo',
            ],
            'is_split_parent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'parent_id_faktur',
            ],
        ] as $field => $definition) {
            if (!$this->db->field_exists($field, 'tbso_faktur_penjualan')) {
                $this->dbforge->add_column('tbso_faktur_penjualan', [$field => $definition]);
            }
        }
    }

    private function _autoCreateDoFromFinishedFakturRoute(array $so, $create_by)
    {
        $kd_rute = trim((string)($so['kd_rute'] ?? ''));
        if ($kd_rute === '') {
            $kd_rute = trim((string)($so['customer_kd_rute'] ?? ''));
        }
        return $this->M_Logistik->check_and_auto_create_do($kd_rute, $create_by);
    }

    private function _ensureSoSedangVerifikasiStatus()
    {
        $column = $this->db->query("SHOW COLUMNS FROM tbso_sales_order LIKE 'status'")->row_array();
        $type = strtolower((string)($column['Type'] ?? ''));
        if (strpos($type, "'sedang_verifikasi'") !== false && strpos($type, "'siap_faktur'") !== false && strpos($type, "'partial'") !== false) {
            return;
        }

        $this->db->query("
            ALTER TABLE tbso_sales_order
            MODIFY COLUMN status ENUM('draft','open','sedang_verifikasi','siap_faktur','partial','completed','cancelled')
            NOT NULL DEFAULT 'draft'
        ");
    }

    private function _ensureSoLoadingVerificationColumns()
    {
        $this->load->dbforge();

        if (!$this->db->field_exists('qty_siap_faktur', 'tbso_sales_order_detail')) {
            $this->dbforge->add_column('tbso_sales_order_detail', [
                'qty_siap_faktur' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,3',
                    'null'       => true,
                    'after'      => 'qty_faktur',
                ],
            ]);
        }
        if (!$this->db->field_exists('qty_tidak_terkirim', 'tbso_sales_order_detail')) {
            $this->dbforge->add_column('tbso_sales_order_detail', [
                'qty_tidak_terkirim' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,3',
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'qty_siap_faktur',
                ],
            ]);
        }
        foreach ([
            'verifikasi_loading_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending', 'null' => false],
            'verifikasi_loading_note' => ['type' => 'TEXT', 'null' => true],
            'verifikasi_loading_by' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'verifikasi_loading_at' => ['type' => 'DATETIME', 'null' => true],
            'checker_loaded' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false],
        ] as $field => $definition) {
            if (!$this->db->field_exists($field, 'tbso_sales_order_detail')) {
                $this->dbforge->add_column('tbso_sales_order_detail', [$field => $definition]);
            }
        }
    }

    private function _normalizeFakturPrefix($prefix)
    {
        return preg_replace('/[^A-Z]/', '', strtoupper((string)$prefix));
    }

    private function _getAvailableFakturPrefix($preferred_prefix, $current_user_id)
    {
        $preferred_prefix = $this->_normalizeFakturPrefix($preferred_prefix);
        if ($preferred_prefix === '') {
            $preferred_prefix = 'X';
        }

        if (!$this->_isFakturPrefixUsedByOtherUser($preferred_prefix, $current_user_id)) {
            return $preferred_prefix;
        }

        $number = 1;
        do {
            $candidate = $this->_numberToFakturPrefix($number++);
        } while ($this->_isFakturPrefixUsedByOtherUser($candidate, $current_user_id));

        return $candidate;
    }

    private function _isFakturPrefixUsedByOtherUser($prefix, $current_user_id)
    {
        $prefix = $this->_normalizeFakturPrefix($prefix);
        if ($prefix === '') {
            return false;
        }

        return $this->db
            ->where('faktur_prefix', $prefix)
            ->where('id !=', $current_user_id)
            ->count_all_results('tb_karyawan') > 0;
    }

    private function _findUserPrefixIndex(array $users, $current_id, $current_username, $current_name)
    {
        foreach ($users as $index => $user) {
            if ($current_id !== null && (string)($user['id'] ?? '') === (string)$current_id) {
                return $index;
            }
            if ($current_username !== null && (string)($user['username'] ?? '') === (string)$current_username) {
                return $index;
            }
            if ($current_name !== null && (string)($user['nm_karyawan'] ?? '') === (string)$current_name) {
                return $index;
            }
        }

        return null;
    }

    private function _numberToFakturPrefix($number)
    {
        $number = max(1, (int)$number);
        $prefix = '';

        while ($number > 0) {
            $number--;
            $prefix = chr(65 + ($number % 26)) . $prefix;
            $number = (int)floor($number / 26);
        }

        return $prefix;
    }

    private function _getGudangId($post = [])
    {
        if (!empty($post['gudang_id'])) return $post['gudang_id'];
        $wil = $this->_getCurrentUser()['wilayah'];
        return !empty($wil) ? $wil : '';
    }

    // ================================================================
    // LIST SO
    // ================================================================
    public function index()
    {
        if ($this->_isAdminScOnlyUser()) {
            redirect('sales_order/admin_sc');
            return;
        }

        $this->_ensureSoSedangVerifikasiStatus();
        $this->_ensureSoLoadingVerificationColumns();

        $show_completed = (string)($this->input->get('selesai', true) ?? $this->input->post('selesai', true) ?? '') === '1';

        $filter = [
            'date1'       => $this->input->post('date1') ?: $this->input->get('date1', true),
            'date2'       => $this->input->post('date2') ?: $this->input->get('date2', true),
            'status'      => $show_completed ? 'completed' : ($this->input->post('status') ?: $this->input->get('status', true)),
            'customer_id' => $this->input->post('customer_id') ?: $this->input->get('customer_id', true),
        ];
        if (!$show_completed && empty($filter['status'])) {
            $filter['exclude_status'] = ['completed'];
        }
        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        $data['page_title'] = 'KARISMA - Sales Order';
        $data['so_list']    = $this->M_SalesOrder->get_all_so($filter);
        $data['customers']  = $this->M_SalesOrder->get_customers();
        $data['filter']     = array_diff_key($filter, ['exclude_status' => true]);
        $data['show_completed'] = $show_completed;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_sc()
    {
        if (!$this->_canAccessAdminSc()) {
            $this->_denyAdminScAccess();
            return;
        }

        $this->_ensureSoFakturZColumn();
        $this->_ensureSoSedangVerifikasiStatus();
        $this->_ensureSoLoadingVerificationColumns();
        $this->M_Logistik->sync_faktur_selesai_do_for_on_delivery();

        $selected_rute = trim((string)(
            $this->input->get('rute', true)
            ?: $this->input->post('rute', true)
            ?: ''
        ));
        $filter = [
            'date1'       => $this->input->post('date1') ?: $this->input->get('date1', true),
            'date2'       => $this->input->post('date2') ?: $this->input->get('date2', true),
            'customer_id' => $this->input->post('customer_id') ?: $this->input->get('customer_id', true),
        ];
        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        $summary_filter = $filter;
        $all_ready_so = $this->M_SalesOrder->get_admin_sc_ready_so($summary_filter);
        $route_summary = [];
        foreach ($all_ready_so as $row) {
            $rute = trim((string)($row['kd_rute'] ?: ($row['customer_kd_rute'] ?? '')));
            if ($rute === '') $rute = '-';
            if (!isset($route_summary[$rute])) {
                $route_summary[$rute] = [
                    'kd_rute'                    => $rute,
                    'total_so'                   => 0,
                    'total_sudah_faktur'         => 0,
                    'total_belum_faktur'         => 0,
                    'total_qty_siap_faktur'      => 0,
                    'total_qty_tidak_terkirim'   => 0,
                    'total_item_ditolak'         => 0,
                    'latest_update_at'           => '',
                ];
            }

            $route_summary[$rute]['total_so']++;
            if ((int)($row['jumlah_faktur'] ?? 0) > 0) {
                $route_summary[$rute]['total_sudah_faktur']++;
            } else {
                $route_summary[$rute]['total_belum_faktur']++;
            }
            $route_summary[$rute]['total_qty_siap_faktur']    += (float)($row['total_qty_siap_faktur'] ?? 0);
            $route_summary[$rute]['total_qty_tidak_terkirim'] += (float)($row['total_qty_tidak_terkirim'] ?? 0);
            $route_summary[$rute]['total_item_ditolak']       += (int)($row['jumlah_item_ditolak'] ?? 0);
            $updated_at = (string)($row['update_at'] ?? $row['create_at'] ?? '');
            if ($updated_at > $route_summary[$rute]['latest_update_at']) {
                $route_summary[$rute]['latest_update_at'] = $updated_at;
            }
        }
        uasort($route_summary, function($a, $b) {
            return strcmp((string)$b['latest_update_at'], (string)$a['latest_update_at']);
        });

        $so_filter = $filter;
        if ($selected_rute !== '') {
            $so_filter['kd_rute'] = $selected_rute;
        }

        $data['page_title'] = 'KARISMA - Admin SC - SO Siap Faktur';
        $data['so_list']    = $selected_rute !== ''
            ? $this->M_SalesOrder->get_admin_sc_ready_so($so_filter)
            : [];
        $data['route_summary'] = array_values($route_summary);
        $data['selected_rute'] = $selected_rute;
        $data['customers']  = $this->M_SalesOrder->get_customers();
        $data['filter']     = $filter;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/admin_sc_so_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_sc_faktur()
    {
        if (!$this->_canAccessAdminSc()) {
            $this->_denyAdminScAccess();
            return;
        }

        $this->_ensureFakturPaymentInfoColumns();
        $this->M_Logistik->sync_faktur_selesai_do_for_on_delivery();

        $selected_rute = trim((string)(
            $this->input->get('rute', true)
            ?: $this->input->post('rute', true)
            ?: ''
        ));
        $filter = [
            'date1'       => $this->input->post('date1') ?: $this->input->get('date1', true),
            'date2'       => $this->input->post('date2') ?: $this->input->get('date2', true),
            'customer_id' => $this->input->post('customer_id') ?: $this->input->get('customer_id', true),
        ];
        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        $all_fakturs = $this->M_SalesOrder->get_admin_sc_faktur_selesai($filter);
        $route_summary = [];
        foreach ($all_fakturs as $faktur) {
            $rute = trim((string)(($faktur['so_kd_rute'] ?? '') ?: ($faktur['customer_kd_rute'] ?? '')));
            if ($rute === '') $rute = '-';
            $tgl_faktur = substr((string)($faktur['tanggal_faktur'] ?? $faktur['create_at'] ?? date('Y-m-d')), 0, 10);
            $group_key  = $rute . '||' . $tgl_faktur;

            if (!isset($route_summary[$group_key])) {
                $route_summary[$group_key] = [
                    'kd_rute'          => $rute,
                    'tgl_faktur'       => $tgl_faktur,
                    'total_faktur'     => 0,
                    'total_qty'        => 0,
                    'total_pajak'      => 0,
                    'grand_total'      => 0,
                    'total_item_ditolak' => 0,
                    'latest_faktur_at' => '',
                ];
            }
            $route_summary[$group_key]['total_faktur']++;
            $route_summary[$group_key]['total_qty']   += (float)($faktur['total_qty']   ?? 0);
            $route_summary[$group_key]['total_pajak'] += (float)($faktur['total_pajak'] ?? 0);
            $route_summary[$group_key]['grand_total'] += (float)($faktur['grand_total'] ?? 0);
            // Accumulate max ditolak per SO per route group (avoid double-counting same SO's items)
            $route_summary[$group_key]['total_item_ditolak'] = max(
                (int)$route_summary[$group_key]['total_item_ditolak'],
                (int)($faktur['jumlah_item_ditolak'] ?? 0)
            );
            $latest = (string)($faktur['tanggal_faktur'] ?? $faktur['create_at'] ?? '');
            if ($latest > $route_summary[$group_key]['latest_faktur_at']) {
                $route_summary[$group_key]['latest_faktur_at'] = $latest;
            }
        }
        uasort($route_summary, function($a, $b) {
            return strcmp((string)$b['latest_faktur_at'], (string)$a['latest_faktur_at']);
        });

        $faktur_filter = $filter;
        if ($selected_rute !== '') {
            $faktur_filter['kd_rute'] = $selected_rute;
        }
        $fakturs = $selected_rute !== ''
            ? $this->M_SalesOrder->get_admin_sc_faktur_selesai($faktur_filter)
            : [];

        $total_nilai = 0;
        $total_pajak = 0;
        $grand_total = 0;
        $total_qty = 0;
        foreach ($fakturs as $faktur) {
            $total_nilai += (float)($faktur['total_nilai_faktur'] ?? 0);
            $total_pajak += (float)($faktur['total_pajak'] ?? 0);
            $grand_total += (float)($faktur['grand_total'] ?? 0);
            $total_qty += (float)($faktur['total_qty'] ?? 0);
        }

        $data['page_title']  = 'KARISMA - Admin SC - Faktur Selesai';
        $data['fakturs']     = $fakturs;
        $data['route_summary'] = array_values($route_summary);
        $data['selected_rute'] = $selected_rute;
        $data['customers']   = $this->M_SalesOrder->get_customers();
        $data['filter']      = $filter;
        $data['total_nilai'] = $total_nilai;
        $data['total_pajak'] = $total_pajak;
        $data['grand_total'] = $grand_total;
        $data['total_qty']   = $total_qty;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/admin_sc_faktur_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_sc_print_faktur_rute()
    {
        if (!$this->_canAccessAdminSc()) {
            $this->_denyAdminScAccess();
            return;
        }

        $this->_ensureFakturPaymentInfoColumns();

        $selected_rute = trim((string)$this->input->get('rute', true));
        if ($selected_rute === '') {
            $this->session->set_flashdata('error', 'Pilih rute terlebih dahulu untuk cetak semua faktur.');
            redirect('sales_order/admin_sc/faktur');
            return;
        }

        $filter = [
            'date1'       => $this->input->get('date1', true),
            'date2'       => $this->input->get('date2', true),
            'customer_id' => $this->input->get('customer_id', true),
            'kd_rute'     => $selected_rute,
        ];
        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        $fakturs = $this->M_SalesOrder->get_admin_sc_faktur_selesai($filter);
        if (empty($fakturs)) {
            $this->session->set_flashdata('warning', 'Tidak ada faktur selesai pada rute ini.');
            redirect('sales_order/admin_sc/faktur?rute=' . rawurlencode($selected_rute));
            return;
        }

        $print_items = [];
        foreach ($fakturs as $faktur) {
            $so = $this->M_SalesOrder->get_so($faktur['id_so']);
            if (!$this->_canAccessSo($so)) {
                continue;
            }
            $print_items[] = [
                'faktur'  => $faktur,
                'so'      => $so,
                'details' => $this->M_SalesOrder->get_faktur_detail($faktur['id_faktur']),
            ];
        }

        if (empty($print_items)) {
            $this->session->set_flashdata('error', 'Tidak ada faktur yang dapat dicetak.');
            redirect('sales_order/admin_sc/faktur?rute=' . rawurlencode($selected_rute));
            return;
        }

        $data['page_title']    = 'KARISMA - Cetak Faktur Rute ' . $selected_rute;
        $data['selected_rute'] = $selected_rute;
        $data['print_items']   = $print_items;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/admin_sc_faktur_print_all.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_sc_pilih_barang($id_so)
    {
        if (!$this->_canAccessAdminSc()) {
            $this->_denyAdminScAccess();
            return;
        }

        $this->_ensureSoFakturZColumn();
        $this->_ensureSoSedangVerifikasiStatus();
        $this->_ensureSoLoadingVerificationColumns();

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || !in_array(($so['status'] ?? ''), ['siap_faktur', 'partial'], true)) {
            $this->session->set_flashdata('error', 'SO belum siap difakturkan atau harus diverifikasi ulang oleh logistik.');
            redirect('sales_order/admin_sc');
            return;
        }
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $details = array_values(array_filter($this->M_SalesOrder->get_so_detail($id_so), function($item) {
            return (float)($item['qty_available_faktur'] ?? 0) > 0;
        }));

        if (empty($details)) {
            $this->session->set_flashdata('warning', 'Tidak ada barang terverifikasi yang masih bisa difakturkan.');
            redirect('sales_order/admin_sc');
            return;
        }

        $data['page_title'] = 'KARISMA - Pilih Barang Faktur ' . $so['no_so'];
        $data['so']         = $so;
        $data['details']    = $details;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/admin_sc_pilih_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_sc_form_faktur($id_so)
    {
        if (!$this->_canAccessAdminSc()) {
            $this->_denyAdminScAccess();
            return;
        }

        return $this->form_faktur($id_so);
    }

    public function admin_sc_update_harga_faktur($id_so)
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post') {
            echo json_encode(['msg' => 'error', 'message' => 'Method tidak valid.']);
            exit;
        }

        if (!$this->_canAccessAdminSc()) {
            echo json_encode(['msg' => 'error', 'message' => 'Anda tidak memiliki akses Admin SC.']);
            exit;
        }

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || !in_array(($so['status'] ?? ''), ['siap_faktur', 'partial'], true)) {
            echo json_encode(['msg' => 'error', 'message' => 'SO tidak valid atau belum siap difakturkan.']);
            exit;
        }
        if (!$this->_canAccessSo($so)) {
            echo json_encode(['msg' => 'error', 'message' => 'Anda tidak memiliki akses untuk mengubah SO ini.']);
            exit;
        }

        $id_so_detail = (int)$this->input->post('id_so_detail', true);
        $harga = (float)$this->input->post('harga', true);

        if ($id_so_detail <= 0 || $harga <= 0) {
            echo json_encode(['msg' => 'error', 'message' => 'Harga satuan tidak valid.']);
            exit;
        }

        $updated = $this->M_SalesOrder->update_so_detail_harga($id_so, $id_so_detail, $harga, $this->_getUsername());
        echo json_encode([
            'msg' => $updated ? 'success' : 'error',
            'message' => $updated ? 'Harga SO berhasil diperbarui.' : 'Gagal memperbarui harga SO.',
            'harga' => $harga,
        ]);
        exit;
    }

    // ================================================================
    // FORM CREATE SO
    // ================================================================
    public function create()
    {
        $this->_ensureSoCaraPembayaranColumn();

        $data['page_title']     = 'KARISMA - Buat Sales Order';
        $data['no_so']          = $this->M_SalesOrder->generate_no_so();
        $data['customers']      = $this->_getCustomersForCurrentSales();
        $data['tax_list']       = $this->M_SalesOrder->get_tax_list();
        $data['gudang_list']    = $this->M_SalesOrder->get_gudang_list();
        $data['gudang_id']      = $this->_getGudangId();
        $data['so']             = null;
        $data['details']        = [];
        $data['batas_tonase']   = M_SalesOrder::BATAS_TONASE;
        $data['batas_kubikasi'] = M_SalesOrder::BATAS_KUBIKASI;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // STORE SO (POST)
    // ================================================================
    public function store()
    {
        if ($this->input->method() !== 'post') show_404();
        $this->_ensureSoFakturZColumn();
        $this->_ensureSoCaraPembayaranColumn();

        $post      = $this->input->post(null, true);
        $details   = $this->_parse_detail_post($post);
        $gudang_id = $this->_getGudangId($post);

        if (!$this->_validateCustomerForCurrentSales($post['customer_id'] ?? '', 'sales_order/create')) {
            return;
        }

        if (!empty($post['customer_id'])) {
            $customer = $this->db->get_where('tb_customer', ['kd_customer' => $post['customer_id']])->row_array();
            if ($customer) {
                $plafon = isset($customer['plafon_aktif']) ? (float)$customer['plafon_aktif'] : null;
                if ($plafon !== null && (float)$plafon == 1000) {
                    $this->session->set_flashdata('error', 'Customer dengan plafon 1.000 tidak dapat dipilih untuk membuat SO.');
                    redirect('sales_order/create');
                    return;
                }

                $grand_total = 0;
                foreach ($details as $d) {
                    $grand_total += (float)$d['total_harga'];
                }
                if ($plafon !== null && (float)$plafon != 1000 && $grand_total > $plafon) {
                    $this->session->set_flashdata(
                        'error',
                        'Grand total SO (Rp ' . number_format($grand_total, 0, ',', '.') . ') melebihi plafon customer (Rp ' . number_format($plafon, 0, ',', '.') . ').'
                    );
                    redirect('sales_order/create');
                    return;
                }
            }
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal 1 item barang harus diisi.');
            redirect('sales_order/create');
            return;
        }

        $approval_errors = $this->_validate_harga_approval($details);
        if (!empty($approval_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $approval_errors));
            redirect('sales_order/create');
            return;
        }

        // Validasi stok
        $stock_errors = $this->M_SalesOrder->validasi_stok($details, $gudang_id);
        if (!empty($stock_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $stock_errors));
            redirect('sales_order/create');
            return;
        }

        $tk      = $this->M_SalesOrder->validasi_tonase_kubikasi($details);
        if ((float)$tk['total_tonase'] > (float)$tk['batas_tonase']) {
            $this->session->set_flashdata(
                'error',
                'Tonase melebihi batas maksimal ' . $tk['batas_tonase'] . ' ton. '
                . 'Total tonase: ' . round($tk['total_tonase'], 3) . ' ton.'
            );
            redirect('sales_order/create');
            return;
        }

        $no_so = $post['no_so'] ?? $this->M_SalesOrder->generate_no_so();

        $cara_pembayaran_so = strtolower(trim($post['cara_pembayaran'] ?? 'cash'));
        if (!in_array($cara_pembayaran_so, ['cash', 'transfer', 'bg', 'tempo'], true)) {
            $cara_pembayaran_so = 'cash';
        }

        $header = [
            'no_so'             => $no_so,
            'tanggal_transaksi' => $post['tanggal'],
            'kd_customer'       => $post['customer_id'],
            'customer_name'     => $post['customer_name'],
            'gudang_id'         => $gudang_id,
            'batas_tonase'      => $tk['batas_tonase'],
            'batas_kubikasi'    => $tk['batas_kubikasi'],
            'total_tonase'      => $tk['total_tonase'],
            'total_kubikasi'    => $tk['total_kubikasi'],
            'catatan'           => $post['catatan'] ?? null,
            'cara_pembayaran'   => $cara_pembayaran_so,
            'is_faktur_z'       => !empty($post['is_faktur_z']) ? 1 : 0,
            'create_by'         => $this->_getUsername(),
        ];

        $id_so = $this->M_SalesOrder->simpan_so($header, $details);

        if ($id_so) {
            // Activity log
            $detail_str = $this->_format_detail_produk_log($details);

            $this->M_ActivityLog->log(
                $no_so, '', 'CREATE_SO',
                'SO baru dibuat. Customer: ' . $post['customer_name'] . '. Total item: ' . count($details),
                $this->_getUsername(),
                implode("\n", $detail_str)
            );

            if (!empty($tk['warnings'])) {
                $this->session->set_flashdata('warning',
                    'SO berhasil disimpan. <b>Peringatan:</b> ' . implode('<br>', $tk['warnings']));
            } else {
                $this->session->set_flashdata('success',
                    'Sales Order <b>' . $no_so . '</b> berhasil dibuat dengan status <b>Draft</b>.');
            }

            redirect('sales_order/detail/' . $id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan SO.');
            redirect('sales_order/create');
        }
    }

    // ================================================================
    // DETAIL SO
    // ================================================================
    public function detail($id_so)
    {
        if ($this->_isAdminScOnlyUser()) {
            $this->session->set_flashdata('warning', 'Admin SC tidak menggunakan halaman Detail SO. Silakan gunakan halaman Admin SC atau Faktur Selesai.');
            redirect('sales_order/admin_sc');
            return;
        }

        $this->_ensureSoSedangVerifikasiStatus();
        $this->_ensureSoLoadingVerificationColumns();
        $this->_ensureFakturPaymentInfoColumns();

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $details  = $this->M_SalesOrder->get_so_detail($id_so);
        $fakturs  = $this->M_SalesOrder->get_faktur_by_so($id_so);
        $faktur_details = [];
        foreach ($fakturs as $faktur) {
            $faktur_details[(int)$faktur['id_faktur']] = $this->M_SalesOrder->get_faktur_detail($faktur['id_faktur']);
        }

        // Hitung ringkasan qty per baris
        $total_order       = 0;
        $total_faktur      = 0;
        $total_outstanding = 0;
        $total_available_faktur = 0;
        foreach ($details as $d) {
            $total_order       += (float)$d['qty'];
            $total_faktur      += (float)$d['qty_faktur'];
            $total_outstanding += (float)($d['qty'] - $d['qty_faktur']);
            $total_available_faktur += (float)($d['qty_available_faktur'] ?? 0);
        }

        $data['page_title']        = 'KARISMA - Detail SO ' . ($so['no_so'] ?? $id_so);
        $data['so']                = $so;
        $data['details']           = $details;
        $data['fakturs']           = $fakturs;
        $data['faktur_details']    = $faktur_details;
        $data['total_order']       = $total_order;
        $data['total_faktur']      = $total_faktur;
        $data['total_outstanding'] = $total_outstanding;
        $data['total_available_faktur'] = $total_available_faktur;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FORM EDIT SO (hanya saat Draft)
    // ================================================================
    public function edit($id_so)
    {
        $this->_ensureSoCaraPembayaranColumn();

        $so = $this->M_SalesOrder->get_so($id_so);
        if ($so && !$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }
        if (!$so || $so['status'] !== 'draft') {
            $this->session->set_flashdata('error', 'SO tidak dapat diedit. Hanya SO berstatus Draft yang dapat diedit.');
            redirect('sales_order');
            return;
        }

        $data['page_title']     = 'KARISMA - Edit SO ' . $so['no_so'];
        $data['no_so']          = $so['no_so'] ?? '';
        $data['so']             = $so;
        $details                = $this->M_SalesOrder->get_so_detail($id_so);
        $stock_rows             = [];
        $kd_list                = array_values(array_unique(array_filter(array_column($details, 'kd_barang'))));
        foreach ($kd_list as $kd_barang) {
            $stock_rows = array_merge(
                $stock_rows,
                $this->M_SalesOrder->get_available_stock_with_dimensi($so['gudang_id'], $kd_barang, $id_so)
            );
        }
        $stock_map              = [];
        foreach ($stock_rows as $stock) {
            $stock_map[implode('|', [
                (string)($stock['kd_barang'] ?? ''),
                (string)($stock['exp_date'] ?? $stock['expired_date'] ?? ''),
                (string)($stock['no_lot'] ?? ''),
            ])] = $stock;
        }
        foreach ($details as &$detail) {
            $key = implode('|', [
                (string)($detail['kd_barang'] ?? ''),
                (string)($detail['expired_date'] ?? ''),
                (string)($detail['no_lot'] ?? ''),
            ]);
            if (!isset($stock_map[$key])) {
                $detail['available_stock'] = (float)($detail['qty'] ?? 0);
                continue;
            }

            $stock = $stock_map[$key];
            $detail['available_stock'] = (float)($stock['available_stock'] ?? 0);
            foreach (['nama_barang', 'satuan'] as $field) {
                if (empty($detail[$field]) && isset($stock[$field])) {
                    $detail[$field] = $stock[$field];
                }
            }
            foreach (['berat_gram', 'kubikasi_m3', 'isi_per_box'] as $field) {
                if ((float)($detail[$field] ?? 0) <= 0 && isset($stock[$field])) {
                    $detail[$field] = $stock[$field];
                }
            }
            if ((float)($detail['hrg_pokok'] ?? 0) <= 0 && isset($stock['hpp'])) {
                $detail['hrg_pokok'] = $stock['hpp'];
            }
        }
        unset($detail);
        $data['details']        = $details;
        $data['customers']      = $this->_getCustomersForCurrentSales();
        $data['tax_list']       = $this->M_SalesOrder->get_tax_list();
        $data['gudang_list']    = $this->M_SalesOrder->get_gudang_list();
        $data['gudang_id']      = $so['gudang_id'];
        $data['batas_tonase']   = M_SalesOrder::BATAS_TONASE;
        $data['batas_kubikasi'] = M_SalesOrder::BATAS_KUBIKASI;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // UPDATE SO (POST)
    // ================================================================
    public function update($id_so)
    {
        $this->_ensureSoFakturZColumn();
        $this->_ensureSoCaraPembayaranColumn();

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $post      = $this->input->post(null, true);
        $details   = $this->_parse_detail_post($post);
        $gudang_id = $this->_getGudangId($post);

        if (!$this->_validateCustomerForCurrentSales($post['customer_id'] ?? '', 'sales_order/edit/' . $id_so)) {
            return;
        }

        if (!empty($post['customer_id'])) {
            $customer = $this->db->get_where('tb_customer', ['kd_customer' => $post['customer_id']])->row_array();
            if ($customer) {
                $plafon = isset($customer['plafon_aktif']) ? (float)$customer['plafon_aktif'] : null;
                if ($plafon !== null && (float)$plafon == 1000) {
                    $this->session->set_flashdata('error', 'Customer dengan plafon 1.000 tidak dapat dipilih untuk membuat SO.');
                    redirect('sales_order/edit/' . $id_so);
                    return;
                }

                $grand_total = 0;
                foreach ($details as $d) {
                    $grand_total += (float)$d['total_harga'];
                }
                if ($plafon !== null && (float)$plafon != 1000 && $grand_total > $plafon) {
                    $this->session->set_flashdata(
                        'error',
                        'Grand total SO (Rp ' . number_format($grand_total, 0, ',', '.') . ') melebihi plafon customer (Rp ' . number_format($plafon, 0, ',', '.') . ').'
                    );
                    redirect('sales_order/edit/' . $id_so);
                    return;
                }
            }
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal 1 item barang harus diisi.');
            redirect('sales_order/edit/' . $id_so);
            return;
        }

        $approval_errors = $this->_validate_harga_approval($details);
        if (!empty($approval_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $approval_errors));
            redirect('sales_order/edit/' . $id_so);
            return;
        }

        $stock_errors = $this->M_SalesOrder->validasi_stok($details, $gudang_id, $id_so);
        if (!empty($stock_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $stock_errors));
            redirect('sales_order/edit/' . $id_so);
            return;
        }

        $tk      = $this->M_SalesOrder->validasi_tonase_kubikasi($details);
        if ((float)$tk['total_tonase'] > (float)$tk['batas_tonase']) {
            $this->session->set_flashdata(
                'error',
                'Tonase melebihi batas maksimal ' . $tk['batas_tonase'] . ' ton. '
                . 'Total tonase: ' . round($tk['total_tonase'], 3) . ' ton.'
            );
            redirect('sales_order/edit/' . $id_so);
            return;
        }

        $cara_pembayaran_so = strtolower(trim($post['cara_pembayaran'] ?? 'cash'));
        if (!in_array($cara_pembayaran_so, ['cash', 'transfer', 'bg', 'tempo', 'bonus'], true)) {
            $cara_pembayaran_so = 'cash';
        }

        $header = [
            'tanggal_transaksi' => $post['tanggal'],
            'kd_customer'       => $post['customer_id'],
            'customer_name'     => $post['customer_name'],
            'gudang_id'         => $gudang_id,
            'batas_tonase'      => $tk['batas_tonase'],
            'batas_kubikasi'    => $tk['batas_kubikasi'],
            'total_tonase'      => $tk['total_tonase'],
            'total_kubikasi'    => $tk['total_kubikasi'],
            'catatan'           => $post['catatan'] ?? null,
            'cara_pembayaran'   => $cara_pembayaran_so,
            'is_faktur_z'       => !empty($post['is_faktur_z']) ? 1 : 0,
            'update_by'         => $this->_getUsername(),
        ];

        $result = $this->M_SalesOrder->update_so($id_so, $header, $details);

        if ($result) {
            $so_fresh = $this->M_SalesOrder->get_so($id_so);
            $detail_str = $this->_format_detail_produk_log($details);

            $this->M_ActivityLog->log(
                $so_fresh['no_so'] ?? '', '', 'UPDATE_SO',
                'SO diupdate. Customer: ' . ($post['customer_name'] ?? '') . '. Total item: ' . count($details),
                $this->_getUsername(),
                implode("\n", $detail_str)
            );

            if (!empty($tk['warnings'])) {
                $this->session->set_flashdata('warning', implode('<br>', $tk['warnings']));
            } else {
                $this->session->set_flashdata('success', 'Sales Order <b>' . ($so_fresh['no_so'] ?? $id_so) . '</b> berhasil diupdate.');
            }
            redirect('sales_order/detail/' . $id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal update SO. Pastikan SO masih berstatus Draft.');
            redirect('sales_order/edit/' . $id_so);
        }
    }

    // ================================================================
    // REKAM SO — Draft → Open
    // ================================================================
    public function rekam($id_so)
    {
        $so = $this->M_SalesOrder->get_so($id_so);
        if ($so && !$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }
        if (!$so || $so['status'] !== 'draft') {
            $this->session->set_flashdata('error', 'SO tidak dapat direkam. Hanya SO berstatus Draft yang dapat direkam.');
            redirect('sales_order/detail/' . $id_so);
            return;
        }

        $details = $this->M_SalesOrder->get_so_detail($id_so);
        $detail_str = $this->_format_detail_produk_log($details);

        $result = $this->M_SalesOrder->rekam_so($id_so, $this->_getUsername());

        if ($result) {
            $this->M_ActivityLog->log(
                $so['no_so'] ?? '', '', 'REKAM_SO',
                'SO direkam. Status berubah dari Draft menjadi Open. SO siap dibuatkan Faktur Penjualan.',
                $this->_getUsername(),
                implode("\n", $detail_str)
            );
            $this->session->set_flashdata('success',
                'SO <b>' . htmlspecialchars($so['no_so']) . '</b> berhasil direkam. Status: <b>Open</b>. '
                . 'Faktur Penjualan dapat dibuat sekarang.');
        } else {
            $this->session->set_flashdata('error', 'Gagal merekam SO.');
        }

        redirect('sales_order/detail/' . $id_so);
    }

    // ================================================================
    // CANCEL SO
    // ================================================================
    public function cancel($id_so)
    {
        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        if (in_array($so['status'], ['completed', 'cancelled'])) {
            $this->session->set_flashdata('error', 'SO tidak dapat dibatalkan.');
            redirect('sales_order/detail/' . $id_so);
            return;
        }

        // Cek apakah sudah ada faktur yang dibuat
        $fakturs = $this->M_SalesOrder->get_faktur_by_so($id_so);
        if (!empty($fakturs)) {
            $this->session->set_flashdata('error',
                'SO tidak dapat dibatalkan karena sudah memiliki <b>' . count($fakturs) . ' Faktur Penjualan</b>. '
                . 'Batalkan semua faktur terlebih dahulu.');
            redirect('sales_order/detail/' . $id_so);
            return;
        }

        $this->M_SalesOrder->update_status($id_so, 'cancelled', $this->_getUsername());

        $this->M_ActivityLog->log(
            $so['no_so'] ?? '', '', 'CANCEL_SO',
            'SO dibatalkan.',
            $this->_getUsername()
        );

        $this->session->set_flashdata('success', 'Sales Order <b>' . htmlspecialchars($so['no_so']) . '</b> berhasil dibatalkan.');
        redirect('sales_order');
    }

    // ================================================================
    // FAKTUR PENJUALAN — Form buat faktur dari SO
    // ================================================================
    public function form_faktur($id_so)
    {
        $this->_ensureSoFakturZColumn();
        $this->_ensureSoSedangVerifikasiStatus();
        $this->_ensureSoLoadingVerificationColumns();
        $this->_ensureFakturPaymentInfoColumns();

        $so = $this->M_SalesOrder->get_so($id_so);
        if ($so && !$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }
        if (!$so || !in_array(($so['status'] ?? ''), ['siap_faktur', 'partial'], true)) {
            $this->session->set_flashdata('error', 'Faktur hanya dapat dibuat dari SO yang sudah melewati verifikasi logistik.');
            redirect('sales_order/admin_sc');
            return;
        }

        $details = $this->M_SalesOrder->get_so_detail($id_so);
        $selected_items = $this->input->get('item', true);
        if (!is_array($selected_items)) {
            $selected_items = $selected_items !== null && $selected_items !== '' ? [$selected_items] : [];
        }
        $selected_items = array_filter(array_map('intval', $selected_items));
        $tax_mode = strtolower(trim($this->input->get('tax_mode', true) ?? 'non_pajak'));
        if (!in_array($tax_mode, ['pajak', 'non_pajak'], true)) {
            $tax_mode = 'non_pajak';
        }
        $tax_rate = ($tax_mode === 'pajak') ? 11 : 0;

        // Filter hanya item yang sudah lolos verifikasi barang dan belum difakturkan.
        $items_outstanding = array_filter($details, function($d) {
            return (float)($d['qty_available_faktur'] ?? 0) > 0;
        });

        if (!empty($selected_items)) {
            $items_outstanding = array_filter($items_outstanding, function($d) use ($selected_items) {
                return in_array((int)$d['id_so_detail'], $selected_items, true);
            });
        }

        $items_outstanding = array_filter($items_outstanding, function($d) use ($tax_mode) {
            $is_pajak_item = strtoupper(substr(trim((string)($d['kd_barang'] ?? '')), 0, 1)) === 'Q';
            return $tax_mode === 'pajak' ? $is_pajak_item : !$is_pajak_item;
        });

        if (empty($items_outstanding)) {
            $message = !empty($selected_items)
                ? 'Item yang dipilih tidak valid, sudah difakturkan seluruhnya, atau tidak sesuai jenis faktur ' . ($tax_mode === 'pajak' ? 'Pajak (kode Q)' : 'Non Pajak (kode bukan Q)') . '.'
                : 'Tidak ada barang ' . ($tax_mode === 'pajak' ? 'Pajak (kode Q)' : 'Non Pajak (kode bukan Q)') . ' yang siap difakturkan.';
            $this->session->set_flashdata('error', $message);
            redirect('sales_order/admin_sc/pilih_barang/' . $id_so);
            return;
        }

        $data['page_title']        = 'KARISMA - Buat Faktur Penjualan dari SO ' . $so['no_so'];
        $data['so']                = $so;
        $data['details']           = array_map(function($item) use ($tax_rate) {
            $item['pajak'] = $tax_rate;
            return $item;
        }, array_values($items_outstanding));
        $faktur_prefix             = !empty($so['is_faktur_z']) ? 'Z' : $this->_getFakturUserPrefix();
        $data['no_faktur']         = $this->M_SalesOrder->generate_no_faktur($faktur_prefix);
        $data['tax_list']          = $this->M_SalesOrder->get_tax_list();
        $data['tax_mode']          = $tax_rate > 0 ? 'pajak' : 'non_pajak';
        $data['tax_rate']          = $tax_rate;
        $data['batas_tonase']      = M_SalesOrder::BATAS_TONASE;
        $data['batas_kubikasi']    = M_SalesOrder::BATAS_KUBIKASI;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // SIMPAN FAKTUR (POST)
    // ================================================================
    public function simpan_faktur($id_so)
    {
        if ($this->input->method() !== 'post') show_404();
        $this->_ensureSoFakturZColumn();
        $this->_ensureSoSedangVerifikasiStatus();
        $this->_ensureSoLoadingVerificationColumns();
        $this->_ensureFakturPaymentInfoColumns();

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || !in_array(($so['status'] ?? ''), ['siap_faktur', 'partial'], true)) {
            $this->session->set_flashdata('error', 'SO tidak valid atau harus diverifikasi ulang oleh logistik sebelum difakturkan.');
            redirect($this->_isAdminScOnlyUser() ? 'sales_order/admin_sc' : 'sales_order/detail/' . $id_so);
            return;
        }
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $post         = $this->input->post(null, true);
        $faktur_items = $this->_parse_faktur_items($post);

        if (empty($faktur_items)) {
            $this->session->set_flashdata('error', 'Minimal 1 item harus dimasukkan ke faktur.');
            redirect('sales_order/admin_sc/form_faktur/' . $id_so);
            return;
        }

        // Validasi stok untuk qty yang akan difakturkan
        $gudang_id    = $so['gudang_id'];
        $stock_errors = [];
        foreach ($faktur_items as $item) {
            $stock = $this->M_SalesOrder->cek_stock(
                $item['kd_barang'],
                $item['expired_date'],
                $gudang_id,
                $item['no_lot'] ?? null
            );
            $available = $stock ? (float)$stock['available_stock'] : 0;
            if ((float)$item['qty'] > $available + (float)($stock['qty_reserved'] ?? 0)) {
                $stock_errors[] = "Stok fisik tidak mencukupi untuk <b>{$item['nama_barang']}</b>.";
            }
        }
        if (!empty($stock_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $stock_errors));
            redirect('sales_order/admin_sc/form_faktur/' . $id_so);
            return;
        }

        $faktur_prefix = !empty($so['is_faktur_z']) ? 'Z' : $this->_getFakturUserPrefix();
        $expected_prefix = $faktur_prefix . 'INV' . date('dmy');
        $posted_no_faktur = trim((string)($post['no_faktur'] ?? ''));
        $no_faktur = $posted_no_faktur;

        if (strpos($posted_no_faktur, $expected_prefix) !== 0 || $this->M_SalesOrder->is_no_faktur_used($posted_no_faktur)) {
            $no_faktur = $this->M_SalesOrder->generate_no_faktur($faktur_prefix);
        }
        $cara_pembayaran = strtolower(trim((string)($so['cara_pembayaran'] ?? 'cash')));
        if (!in_array($cara_pembayaran, ['cash', 'transfer', 'tempo', 'bg'], true)) {
            $cara_pembayaran = 'cash';
        }
        $jtempo = (int)($post['jtempo'] ?? 0);
        if (!in_array($jtempo, [0, 30, 60, 90], true)) {
            $jtempo = 0;
        }

        $salesman = trim((string)($so['create_by'] ?? ''));

        $faktur_header = [
            'no_faktur'             => $no_faktur,
            'tanggal_faktur'        => $post['tanggal_faktur'],
            'tanggal_jatuh_tempo'   => $post['tanggal_jatuh_tempo'] ?? null,
            'salesman'              => $salesman,
            'cara_pembayaran'       => $cara_pembayaran,
            'jtempo'                => $jtempo,
            'tempo'                 => $jtempo,
            'catatan'               => $post['catatan'] ?? null,
            'create_by'             => $this->_getUsername(),
            'created_by_id'         => (int)($this->session->userdata('id_karyawan') ?: $this->session->userdata('id') ?: 0),
        ];

        $result = $this->M_SalesOrder->buat_faktur($id_so, $faktur_header, $faktur_items);

        if (is_array($result) && isset($result['errors'])) {
            $this->session->set_flashdata('error', implode('<br>', $result['errors']));
            redirect('sales_order/admin_sc/form_faktur/' . $id_so);
            return;
        }

        if ($result) {
            $so_fresh = $this->M_SalesOrder->get_so($id_so);
            $auto_do = $this->_autoCreateDoFromFinishedFakturRoute($so_fresh ?: $so, $this->_getUsername());

            $detail_str = array_map(function($item) {
                return $item['nama_barang'] . ' | Qty: ' . $item['qty'] . ' pcs';
            }, $faktur_items);

            $this->M_ActivityLog->log(
                $so['no_so'] ?? '', $no_faktur, 'BUAT_FAKTUR',
                'Faktur Penjualan ' . $no_faktur . ' dibuat dari SO ' . $so['no_so'] . '. Item: ' . count($faktur_items),
                $this->_getUsername(),
                implode("\n", $detail_str)
            );

            // Cek apakah SO sudah completed
            $auto_do_message = '';
            if (!empty($auto_do['kd_do'])) {
                $auto_do_message = ' DO <b>' . htmlspecialchars($auto_do['kd_do']) . '</b> otomatis dibuat berisi <b>'
                    . (int)$auto_do['total_faktur'] . '</b> faktur rute terkait.';
            }

            $journal_message = '';
            if (is_array($result) && !empty($result['journal']['sales_invoice']['nomor_jurnal'])) {
                $journal_message = ' Jurnal <b>' . htmlspecialchars($result['journal']['sales_invoice']['nomor_jurnal']) . '</b> otomatis dibuat.';
            }

            if (($so_fresh['status'] ?? '') === 'completed') {
                $this->session->set_flashdata('success',
                    'Faktur <b>' . $no_faktur . '</b> berhasil dibuat. '
                    . 'Seluruh item pada SO <b>' . $so['no_so'] . '</b> sudah terpenuhi. Status SO: <b>Completed</b>.'
                    . $auto_do_message . $journal_message);
            } else {
                $this->session->set_flashdata('success',
                    'Faktur <b>' . $no_faktur . '</b> berhasil dibuat. SO masih memiliki barang yang belum terkirim.'
                    . $auto_do_message . $journal_message);
            }

            if ($this->_isAdminScOnlyUser()) {
                $redirect_rute = trim((string)(($so_fresh['kd_rute'] ?? '') ?: ($so_fresh['customer_kd_rute'] ?? '') ?: ($so['kd_rute'] ?? '') ?: ($so['customer_kd_rute'] ?? '')));
                redirect('sales_order/admin_sc' . ($redirect_rute !== '' ? '?rute=' . rawurlencode($redirect_rute) : ''));
            }
            redirect('sales_order/detail/' . $id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan Faktur Penjualan.');
            redirect('sales_order/admin_sc/form_faktur/' . $id_so);
        }
    }

    // ================================================================
    // DETAIL FAKTUR
    // ================================================================
    public function detail_faktur($id_faktur)
    {
        $this->_ensureFakturPaymentInfoColumns();

        $faktur  = $this->M_SalesOrder->get_faktur($id_faktur);
        if (!$faktur) show_404();

        $details = $this->M_SalesOrder->get_faktur_detail($id_faktur);
        $so      = $this->M_SalesOrder->get_so($faktur['id_so']);
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $child_fakturs = [];
        if (!empty($faktur['is_split_parent'])) {
            $child_fakturs = $this->db->get_where('tbso_faktur_penjualan', ['parent_id_faktur' => $id_faktur])->result_array();
        }

        $parent_faktur = null;
        if (!empty($faktur['parent_id_faktur'])) {
            $parent_faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $faktur['parent_id_faktur']])->row_array();
        }

        $has_remaining_split_qty = false;
        if (!empty($so['is_faktur_z']) && empty($faktur['parent_id_faktur']) && !in_array($faktur['status'], ['cancelled', 'draft'], true)) {
            $child_details = $this->db->select('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date, SUM(fd.qty) as qty_allocated')
                ->from('tbso_faktur_detail fd')
                ->join('tbso_faktur_penjualan fp', 'fp.id_faktur = fd.id_faktur')
                ->where('fp.parent_id_faktur', $id_faktur)
                ->where('fp.status !=', 'cancelled')
                ->group_by('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date')
                ->get()
                ->result_array();

            $allocated_map = [];
            foreach ($child_details as $cd) {
                $key = implode('|', [
                    $cd['id_so_detail'],
                    $cd['kd_barang'],
                    (string)$cd['no_lot'],
                    $cd['expired_date']
                ]);
                $allocated_map[$key] = (float)$cd['qty_allocated'];
            }

            $total_remaining = 0;
            foreach ($details as $d) {
                $key = implode('|', [
                    $d['id_so_detail'],
                    $d['kd_barang'],
                    (string)$d['no_lot'],
                    $d['expired_date']
                ]);
                $allocated = $allocated_map[$key] ?? 0.0;
                $total_remaining += max(0.0, (float)$d['qty'] - $allocated);
            }
            if ($total_remaining > 0.001) {
                $has_remaining_split_qty = true;
            }
        }

        $data['page_title']    = 'KARISMA - Faktur ' . $faktur['no_faktur'];
        $data['faktur']        = $faktur;
        $data['details']       = $details;
        $data['so']            = $so;
        $data['child_fakturs'] = $child_fakturs;
        $data['parent_faktur'] = $parent_faktur;
        $data['has_remaining_split_qty'] = $has_remaining_split_qty;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function split_faktur($id_faktur)
    {
        $this->_ensureFakturPaymentInfoColumns();

        $faktur = $this->M_SalesOrder->get_faktur($id_faktur);
        if (!$faktur) show_404();

        $so = $this->M_SalesOrder->get_so($faktur['id_so']);
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        if (empty($so['is_faktur_z'])) {
            $this->session->set_flashdata('error', 'Hanya Faktur Z yang dapat dipecah.');
            redirect('sales_order/detail_faktur/' . $id_faktur);
            return;
        }
        if (!empty($faktur['parent_id_faktur'])) {
            $this->session->set_flashdata('error', 'Faktur turunan tidak dapat dipecah lagi.');
            redirect('sales_order/detail_faktur/' . $id_faktur);
            return;
        }
        if (in_array($faktur['status'], ['cancelled', 'draft'], true)) {
            $this->session->set_flashdata('error', 'Faktur dengan status Draft atau Cancelled tidak dapat dipecah.');
            redirect('sales_order/detail_faktur/' . $id_faktur);
            return;
        }

        // Calculate remaining quantities
        $child_details = $this->db->select('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date, SUM(fd.qty) as qty_allocated')
            ->from('tbso_faktur_detail fd')
            ->join('tbso_faktur_penjualan fp', 'fp.id_faktur = fd.id_faktur')
            ->where('fp.parent_id_faktur', $id_faktur)
            ->where('fp.status !=', 'cancelled')
            ->group_by('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date')
            ->get()
            ->result_array();

        $allocated_map = [];
        foreach ($child_details as $cd) {
            $key = implode('|', [
                $cd['id_so_detail'],
                $cd['kd_barang'],
                (string)$cd['no_lot'],
                $cd['expired_date']
            ]);
            $allocated_map[$key] = (float)$cd['qty_allocated'];
        }

        $details = $this->M_SalesOrder->get_faktur_detail($id_faktur);
        $total_remaining = 0;
        foreach ($details as &$d) {
            $key = implode('|', [
                $d['id_so_detail'],
                $d['kd_barang'],
                (string)$d['no_lot'],
                $d['expired_date']
            ]);
            $allocated = $allocated_map[$key] ?? 0.0;
            $remaining = max(0.0, (float)$d['qty'] - $allocated);
            $d['qty'] = $remaining;
            $total_remaining += $remaining;
        }
        unset($d);

        if ($total_remaining <= 0) {
            $this->session->set_flashdata('error', 'Faktur induk ini sudah sepenuhnya dipecah.');
            redirect('sales_order/detail_faktur/' . $id_faktur);
            return;
        }

        $data['page_title'] = 'Pecah Faktur Z - ' . $faktur['no_faktur'];
        $data['faktur']     = $faktur;
        $data['so']         = $so;
        $data['details']    = $details;
        $data['customers']  = $this->M_SalesOrder->get_customers();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_split_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan_split_faktur($id_faktur)
    {
        $this->_ensureFakturPaymentInfoColumns();

        $faktur = $this->M_SalesOrder->get_faktur($id_faktur);
        if (!$faktur) show_404();

        $so = $this->M_SalesOrder->get_so($faktur['id_so']);
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        if (empty($so['is_faktur_z']) || !empty($faktur['parent_id_faktur']) || in_array($faktur['status'], ['cancelled', 'draft'], true)) {
            $this->session->set_flashdata('error', 'Proses pemecahan faktur tidak valid.');
            redirect('sales_order/detail_faktur/' . $id_faktur);
            return;
        }

        $post = $this->input->post(null, true);
        $splits = $post['splits'] ?? [];

        if (empty($splits)) {
            $this->session->set_flashdata('error', 'Harap tambahkan minimal 1 customer penerima.');
            redirect('sales_order/split_faktur/' . $id_faktur);
            return;
        }

        // Calculate remaining quantities
        $child_details = $this->db->select('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date, SUM(fd.qty) as qty_allocated')
            ->from('tbso_faktur_detail fd')
            ->join('tbso_faktur_penjualan fp', 'fp.id_faktur = fd.id_faktur')
            ->where('fp.parent_id_faktur', $id_faktur)
            ->where('fp.status !=', 'cancelled')
            ->group_by('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date')
            ->get()
            ->result_array();

        $allocated_map = [];
        foreach ($child_details as $cd) {
            $key = implode('|', [
                $cd['id_so_detail'],
                $cd['kd_barang'],
                (string)$cd['no_lot'],
                $cd['expired_date']
            ]);
            $allocated_map[$key] = (float)$cd['qty_allocated'];
        }

        $details = $this->M_SalesOrder->get_faktur_detail($id_faktur);
        $parent_qtys = [];
        $total_remaining = 0;
        foreach ($details as $d) {
            $key = implode('|', [
                $d['id_so_detail'],
                $d['kd_barang'],
                (string)$d['no_lot'],
                $d['expired_date']
            ]);
            $allocated = $allocated_map[$key] ?? 0.0;
            $remaining = max(0.0, (float)$d['qty'] - $allocated);
            $parent_qtys[$d['id']] = [
                'qty' => $remaining,
                'nama' => $d['nama_barang']
            ];
            $total_remaining += $remaining;
        }

        if ($total_remaining <= 0) {
            $this->session->set_flashdata('error', 'Faktur induk ini sudah sepenuhnya dipecah.');
            redirect('sales_order/detail_faktur/' . $id_faktur);
            return;
        }

        $allocated_qtys = [];
        $validation_errors = [];

        foreach ($splits as $idx => $s) {
            $kd_cust = trim((string)($s['kd_customer'] ?? ''));
            if ($kd_cust === '') {
                $validation_errors[] = "Customer Penerima #" . $idx . " belum dipilih.";
                continue;
            }

            $cust = $this->db->get_where('tb_customer', ['kd_customer' => $kd_cust])->row_array();
            if (!$cust) {
                $validation_errors[] = "Customer Penerima #" . $idx . " tidak valid.";
                continue;
            }

            $items = $s['items'] ?? [];
            $has_qty = false;
            foreach ($items as $itemId => $qty) {
                $qty = (float)$qty;
                if ($qty < 0) {
                    $validation_errors[] = "Kuantitas untuk customer " . htmlspecialchars($cust['nama_customer']) . " tidak boleh negatif.";
                }
                if ($qty > 0) {
                    $has_qty = true;
                    if (!isset($allocated_qtys[$itemId])) {
                        $allocated_qtys[$itemId] = 0.0;
                    }
                    $allocated_qtys[$itemId] += $qty;
                }
            }

            if (!$has_qty) {
                $validation_errors[] = "Harap masukkan kuantitas barang minimal 1 item untuk customer " . htmlspecialchars($cust['nama_customer']) . ".";
            }
        }

        foreach ($parent_qtys as $itemId => $data) {
            $allocated = $allocated_qtys[$itemId] ?? 0.0;
            if ($allocated > $data['qty']) {
                $validation_errors[] = "Total alokasi untuk barang <b>" . htmlspecialchars($data['nama']) . "</b> (" . $allocated . " pcs) melebihi kuantitas induk (" . $data['qty'] . " pcs).";
            }
        }

        if (!empty($validation_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $validation_errors));
            redirect('sales_order/split_faktur/' . $id_faktur);
            return;
        }

        $username = $this->_getUsername();
        $result = $this->M_SalesOrder->proses_split_faktur($faktur, $details, $splits, $username);

        if ($result === true) {
            $this->session->set_flashdata('success', 'Faktur Z <b>' . $faktur['no_faktur'] . '</b> berhasil dipecah menjadi faktur turunan.');
            redirect('sales_order/detail_faktur/' . $id_faktur);
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses pemecahan: ' . (is_string($result) ? $result : 'Database error'));
            redirect('sales_order/split_faktur/' . $id_faktur);
        }
    }

    // ================================================================
    // FAKTUR PER RUTE - faktur selesai DO dalam pengiriman hari ini
    // ================================================================
    public function faktur_rute()
    {
        $this->_ensureSoRouteColumn();
        $this->M_Logistik->sync_faktur_selesai_do_for_on_delivery();

        $selected_rute = trim((string)($this->input->get('rute', true) ?? ''));

        $routes = $this->M_SalesOrder->get_today_delivery_faktur_rute_summary();
        if ($selected_rute === '' && !empty($routes)) {
            $selected_rute = $routes[0]['kd_rute'];
        }

        $fakturs = $this->M_SalesOrder->get_today_delivery_faktur_by_rute($selected_rute);

        $total_tonase = 0;
        $total_kubikasi = 0;
        foreach ($fakturs as $f) {
            $total_tonase += (float)($f['total_tonase'] ?? 0);
            $total_kubikasi += (float)($f['total_kubikasi'] ?? 0);
        }

        $data['page_title']       = 'KARISMA - Faktur per Rute';
        $data['today']            = date('Y-m-d');
        $data['routes']           = $routes;
        $data['selected_rute']    = $selected_rute;
        $data['fakturs']          = $fakturs;
        $data['batas_tonase']     = M_SalesOrder::BATAS_TONASE;
        $data['batas_kubikasi']   = M_SalesOrder::BATAS_KUBIKASI;
        $data['total_tonase']     = round($total_tonase, 3);
        $data['total_kubikasi']   = round($total_kubikasi, 4);
        $data['sisa_tonase']      = round(M_SalesOrder::BATAS_TONASE - $total_tonase, 3);
        $data['sisa_kubikasi']    = round(M_SalesOrder::BATAS_KUBIKASI - $total_kubikasi, 4);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_rute.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // SO RUTE - daftar semua SO open untuk penentuan rute, lalu detail per rute
    // ================================================================
    public function so_rute()
    {
        $this->_ensureSoRouteColumn();
        $this->_ensureSoSedangVerifikasiStatus();
        $this->_ensureSoLoadingVerificationColumns();

        $selected_rute = trim((string)($this->input->get('rute', true) ?? ''));
        $selected_customer_rute = trim((string)($this->input->get('customer_rute', true) ?? ''));
        $filter = [];
        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }
        $base_filter = $filter;
        if ($selected_rute === '' && $selected_customer_rute !== '') {
            $filter['customer_kd_rute'] = $selected_customer_rute;
        }

        $routes = $this->M_SalesOrder->get_so_rute_summary($filter);
        $sales_orders = $selected_rute !== ''
            ? $this->M_SalesOrder->get_so_by_rute($selected_rute, $filter)
            : $this->M_SalesOrder->get_open_so_for_routing($filter);

        $total_tonase = 0;
        $total_kubikasi = 0;
        $total_qty_order = 0;
        $total_qty_faktur = 0;
        $total_qty_outstanding = 0;
        foreach ($sales_orders as $so) {
            $total_tonase += (float)($so['total_tonase'] ?? 0);
            $total_kubikasi += (float)($so['total_kubikasi'] ?? 0);
            $total_qty_order += (float)($so['total_qty_order'] ?? 0);
            $total_qty_faktur += (float)($so['total_qty_faktur'] ?? 0);
            $total_qty_outstanding += (float)($so['total_qty_outstanding'] ?? 0);
        }

        $data['page_title']            = 'KARISMA - SO per Rute';
        $data['routes']                = $routes;
        $data['selected_rute']         = $selected_rute;
        $data['selected_customer_rute'] = $selected_customer_rute;
        $data['customer_route_options'] = $this->M_SalesOrder->get_open_so_customer_route_options($base_filter);
        $data['is_all_so_mode']        = ($selected_rute === '');
        $data['all_so_count']          = $this->M_SalesOrder->count_open_so_for_routing($base_filter);
        $data['sales_orders']          = $sales_orders;
        $data['batas_tonase']          = M_SalesOrder::BATAS_TONASE;
        $data['batas_kubikasi']        = M_SalesOrder::BATAS_KUBIKASI;
        $data['total_tonase']          = round($total_tonase, 3);
        $data['total_kubikasi']        = round($total_kubikasi, 4);
        $data['sisa_tonase']           = round(M_SalesOrder::BATAS_TONASE - $total_tonase, 3);
        $data['sisa_kubikasi']         = round(M_SalesOrder::BATAS_KUBIKASI - $total_kubikasi, 4);
        $data['total_qty_order']       = round($total_qty_order, 2);
        $data['total_qty_faktur']      = round($total_qty_faktur, 2);
        $data['total_qty_outstanding'] = round($total_qty_outstanding, 2);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_rute.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function bulk_update_so_rute()
    {
        if ($this->input->method() !== 'post') show_404();
        $this->_ensureSoRouteColumn();

        $target_rute = trim((string)$this->input->post('kd_rute', true));
        $current_rute = trim((string)$this->input->post('current_rute', true));
        $ids = $this->input->post('id_so', true);
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('intval', $ids)))) : [];
        $redirect_rute = $target_rute !== '' ? $target_rute : $current_rute;

        if (empty($ids)) {
            $this->session->set_flashdata('error', 'Pilih minimal satu SO yang akan dipindahkan.');
            redirect('sales_order/so_rute' . ($redirect_rute !== '' ? '?rute=' . rawurlencode($redirect_rute) : ''));
            return;
        }
        if ($target_rute === '' || !$this->M_SalesOrder->rute_exists($target_rute)) {
            $this->session->set_flashdata('error', 'Rute tujuan tidak valid.');
            redirect('sales_order/so_rute' . ($current_rute !== '' ? '?rute=' . rawurlencode($current_rute) : ''));
            return;
        }

        $moved = 0;
        $skipped = 0;
        foreach ($ids as $id_so) {
            $so = $this->M_SalesOrder->get_so($id_so);
            if (!$so || !in_array(($so['status'] ?? ''), ['open', 'partial'], true)) {
                $skipped++;
                continue;
            }
            if (!$this->_canAccessSo($so)) {
                $this->_denySoAccess();
                return;
            }

            $old_rute = $so['kd_rute'] ?? $so['customer_kd_rute'] ?? '';
            if ($this->M_SalesOrder->update_so_rute($id_so, $target_rute, $this->_getUsername())) {
                $moved++;
                $this->M_ActivityLog->log(
                    $so['no_so'] ?? '', '', 'UPDATE_SO_RUTE',
                    'Rute SO diubah dari ' . ($old_rute ?: '-') . ' ke ' . $target_rute . ' melalui bulk update.',
                    $this->_getUsername()
                );
            } else {
                $skipped++;
            }
        }

        if ($moved > 0) {
            $message = '<b>' . $moved . ' SO</b> berhasil dipindahkan ke rute <b>' . htmlspecialchars($target_rute) . '</b>.';
            if ($skipped > 0) {
                $message .= ' <b>' . $skipped . ' SO</b> dilewati karena tidak valid atau bukan status Open/Partial.';
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Tidak ada SO yang berhasil dipindahkan.');
        }

        redirect('sales_order/so_rute?rute=' . rawurlencode($target_rute));
    }

    public function reset_so_rute()
    {
        if ($this->input->method() !== 'post') show_404();
        $this->_ensureSoRouteColumn();

        $id_so = (int)$this->input->post('id_so', true);
        $current_rute = trim((string)$this->input->post('current_rute', true));
        $redirect = 'sales_order/so_rute' . ($current_rute !== '' ? '?rute=' . rawurlencode($current_rute) : '');

        if ($id_so <= 0) {
            $this->session->set_flashdata('error', 'SO tidak valid.');
            redirect($redirect);
            return;
        }

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || !in_array(($so['status'] ?? ''), ['open', 'partial'], true)) {
            $this->session->set_flashdata('error', 'SO tidak ditemukan atau bukan status Open/Partial.');
            redirect($redirect);
            return;
        }
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $old_rute = trim((string)($so['kd_rute'] ?? ''));
        if ($old_rute === '') {
            $this->session->set_flashdata('warning', 'SO tersebut belum memiliki rute loading.');
            redirect($redirect);
            return;
        }

        if ($this->M_SalesOrder->clear_so_rute($id_so, $this->_getUsername())) {
            $this->M_ActivityLog->log(
                $so['no_so'] ?? '', '', 'RESET_SO_RUTE',
                'Rute SO dikosongkan dari ' . $old_rute . ' agar kembali ke Semua SO Open/Partial.',
                $this->_getUsername()
            );
            $this->session->set_flashdata('success', 'SO <b>' . htmlspecialchars($so['no_so'] ?? '') . '</b> dikembalikan ke Semua SO Open/Partial.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengembalikan SO ke Semua SO Open/Partial.');
        }

        redirect($redirect);
    }

    public function confirm_so_rute_loading()
    {
        if ($this->input->method() !== 'post') show_404();
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $this->_ensureSoRouteColumn();
        $this->_ensureSoSedangVerifikasiStatus();
        $this->_ensureSoLoadingVerificationColumns();

        $kd_rute = trim((string)$this->input->post('kd_rute', true));
        $note = trim((string)$this->input->post('note', true));
        if ($kd_rute === '' || !$this->M_SalesOrder->rute_exists($kd_rute)) {
            echo json_encode(['msg' => 'error', 'message' => 'Rute tidak valid.']);
            exit;
        }

        $filter = [];
        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        $sales_orders = $this->M_SalesOrder->get_so_by_rute($kd_rute, $filter);
        if (empty($sales_orders)) {
            echo json_encode(['msg' => 'error', 'message' => 'Tidak ada SO Open pada rute ini.']);
            exit;
        }

        $total_tonase_loading = 0;
        $total_kubikasi_loading = 0;
        foreach ($sales_orders as $so) {
            if (in_array(($so['status'] ?? ''), ['open', 'partial'], true)) {
                $total_tonase_loading += (float)($so['total_tonase'] ?? 0);
                $total_kubikasi_loading += (float)($so['total_kubikasi'] ?? 0);
            }
        }
        if ($total_tonase_loading > M_SalesOrder::BATAS_TONASE) {
            echo json_encode([
                'msg' => 'error',
                'message' => 'Tonase rute melebihi batas maksimal ' . M_SalesOrder::BATAS_TONASE
                    . ' ton. Total: ' . round($total_tonase_loading, 3) . ' ton.'
            ]);
            exit;
        }
        if ($total_kubikasi_loading > M_SalesOrder::BATAS_KUBIKASI) {
            echo json_encode([
                'msg' => 'error',
                'message' => 'Kubikasi rute melebihi batas maksimal ' . M_SalesOrder::BATAS_KUBIKASI
                    . ' m3. Total: ' . round($total_kubikasi_loading, 4) . ' m3.'
            ]);
            exit;
        }

        $confirm_by = $this->_getUsername();
        $updated = 0;
        foreach ($sales_orders as $so) {
            if (!in_array(($so['status'] ?? ''), ['open', 'partial'], true)) {
                continue;
            }

            if ($this->M_SalesOrder->update_status($so['id_so'], 'sedang_verifikasi', $confirm_by)) {
                $updated++;
                $description = 'SO dikonfirmasi siap loading oleh Sales. Status berubah menjadi Verifikasi untuk rute ' . $kd_rute . '.';
                if ($note !== '') {
                    $description .= ' Catatan: ' . $note;
                }
                $this->M_ActivityLog->log(
                    $so['no_so'] ?? '', '', 'SO_SIAP_LOADING',
                    $description,
                    $confirm_by
                );
            }
        }

        if ($updated <= 0) {
            echo json_encode(['msg' => 'error', 'message' => 'Tidak ada SO yang berhasil dikonfirmasi.']);
            exit;
        }

        $this->db->insert('tb_log_confirm_sales', [
            'kd_do'      => $kd_rute,
            'action'     => 'siap',
            'note'       => $note,
            'confirm_by' => $confirm_by,
            'confirm_at' => date('Y-m-d H:i:s'),
        ]);

        echo json_encode([
            'msg'     => 'success',
            'message' => $updated . ' SO rute ' . $kd_rute . ' berubah menjadi Verifikasi.'
        ]);
        exit;
    }

    // ================================================================
    // ACTIVITY LOG
    // ================================================================
    public function activity_log()
    {
        $per_page = 20;
        $page     = (int)($this->input->get('page') ?? 1);
        $offset   = ($page - 1) * $per_page;

        $filter = [
            'no_so'        => $this->input->get('no_so',   true) ?? '',
            'aksi'         => $this->input->get('aksi',    true) ?? '',
            'tanggal'      => $this->input->get('tanggal', true) ?? '',
            'keyword'      => $this->input->get('keyword', true) ?? '',
            'exclude_aksi' => ['BUAT_FAKTUR'],
        ];

        $data['page_title'] = 'KARISMA - Activity Log SO';
        $data['logs']       = $this->_hydrate_missing_log_detail_produk(
            $this->M_ActivityLog->get_filtered($filter, $per_page, $offset)
        );
        $data['total']      = $this->M_ActivityLog->count_filtered($filter);
        $data['filter']     = array_diff_key($filter, ['exclude_aksi' => true]);
        $data['per_page']   = $per_page;
        $data['page']       = $page;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_activity_log.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function activity_log_so($id_so)
    {
        $so   = $this->M_SalesOrder->get_so($id_so);
        if (!$this->_canAccessSo($so)) {
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $logs = $this->_hydrate_missing_log_detail_produk(
            $this->M_ActivityLog->get_by_no_so($so['no_so'] ?? '')
        );
        $logs = array_values(array_filter($logs, function($log) {
            return strtoupper((string)($log['aksi'] ?? '')) !== 'BUAT_FAKTUR';
        }));

        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok', 'data' => $logs], JSON_UNESCAPED_UNICODE);
    }

    // ================================================================
    // AJAX — get_stock
    // ================================================================
    public function get_stock()
    {
        if (ob_get_level()) ob_end_clean();
        try {
            $kd_barang = $this->input->get('kd_barang', true) ?: null;
            $gudang_id = $this->input->get('gudang_id', true);
            $gudang_id = ($gudang_id !== null && $gudang_id !== '') ? (string)$gudang_id : null;
            $exclude_id_so = (int)($this->input->get('exclude_id_so', true) ?: 0);
            if ($exclude_id_so > 0) {
                $exclude_so = $this->M_SalesOrder->get_so($exclude_id_so);
                if (!$exclude_so || !$this->_canAccessSo($exclude_so)) {
                    $exclude_id_so = 0;
                }
            }

            $stock = $this->M_SalesOrder->get_available_stock_with_dimensi(
                $gudang_id,
                $kd_barang,
                $exclude_id_so ?: null
            );

            foreach ($stock as &$row) {
                $row['available_stock'] = (float)($row['available_stock'] ?? 0);
                $row['available_box']   = (int)($row['available_box']    ?? 0);
                $row['available_ecer']  = (int)($row['available_ecer']   ?? 0);
                $row['berat_gram']      = (float)($row['berat_gram']     ?? 0);
                $row['kubikasi_m3']     = (float)($row['kubikasi_m3']    ?? 0);
                $row['hpp']             = (float)($row['hpp']            ?? 0);
                $row['isi_per_box']     = (int)($row['isi_per_box']      ?? 1);
                $row['gudang_id']       = (string)($row['gudang_id']     ?? '');
                $row['gudang']          = (string)($row['gudang']        ?? $row['gudang_id']);
                $row['stock_key']       = implode('|', [
                    $row['stock_batch_id'] ?? $row['id'] ?? '',
                    $row['kd_barang'] ?? '',
                    $row['gudang_id'] ?? '',
                    $row['no_lot'] ?? '',
                    $row['exp_date'] ?? $row['expired_date'] ?? '',
                ]);

                foreach (['kd_barang','nama_barang','satuan','exp_date','no_lot','gudang','gudang_id','stock_key'] as $f) {
                    if (isset($row[$f])) {
                        $row[$f] = mb_convert_encoding((string)$row[$f], 'UTF-8', 'UTF-8');
                    }
                }
            }
            unset($row);

            $json = json_encode(['status' => 'ok', 'data' => $stock], JSON_UNESCAPED_UNICODE);
            if ($json === false) throw new Exception('json_encode failed: ' . json_last_error_msg());

            header('Content-Type: application/json; charset=utf-8');
            echo $json;
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ================================================================
    // AJAX — get_barang
    // ================================================================
    public function get_barang()
    {
        if (ob_get_level()) ob_end_clean();
        $kd_barang = $this->input->get('kd_barang', true);
        $barang    = $this->M_SalesOrder->get_detail_barang($kd_barang);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok', 'data' => $barang], JSON_UNESCAPED_UNICODE);
    }

    // ================================================================
    // PRIVATE — parse POST detail SO
    // ================================================================
    private function _parse_number_input($value)
    {
        $value = trim((string)$value);
        if ($value === '') return 0;

        $value = preg_replace('/[^\d,.\-]/', '', $value);
        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace('.', '', $value);
        }

        return (float)$value;
    }

    private function _parse_detail_post($post)
    {
        $details = [];
        if (empty($post['kd_barang']) || !is_array($post['kd_barang'])) return $details;
        $allowed_harga_approval = ['direksi', 'manager sc', 'kadep keu & sc'];

        foreach ($post['kd_barang'] as $i => $kd) {
            if (empty($kd)) continue;

            $hrg         = $this->_parse_number_input($post['hrg_satuan'][$i] ?? 0);
            $hrg_pk      = (float)($post['hrg_pokok'][$i]   ?? 0);
            $is_ubah_harga = $hrg > 0 && $hrg_pk > 0 && abs($hrg - $hrg_pk) > 0.001;
            $harga_approval_by = strtolower(trim((string)($post['harga_approval_by'][$i] ?? '')));
            if (!$is_ubah_harga || !in_array($harga_approval_by, $allowed_harga_approval, true)) {
                $harga_approval_by = '';
            }
            $qty_box     = (float)($post['qty_box'][$i]      ?? 0);
            $qty_satuan  = (float)($post['qty_satuan'][$i]   ?? 0);
            $isi_per_box = max(1, (int)($post['isi_per_box'][$i] ?? 1));
            $pajak       = (float)($post['pajak'][$i]        ?? 0);
            $disc        = (float)($post['disc'][$i]         ?? 0);
            $qty_kecil   = ($qty_box * $isi_per_box) + $qty_satuan;

            $subtotal_before_disc = $hrg * $qty_kecil;
            $subtotal_after_disc  = $subtotal_before_disc * (1 - $disc / 100);
            $total_tax            = $subtotal_after_disc  * (1 + $pajak / 100);
            $details[] = [
                'kd_barang'            => $kd,
                'nama_barang'          => $post['nama_barang'][$i]  ?? '',
                'qty'                  => $qty_kecil,
                'qty_box'              => $qty_box,
                'qty_satuan'           => $qty_satuan,
                'isi_per_box'          => $isi_per_box,
                'satuan'               => $post['satuan'][$i]        ?? '',
                'expired_date'         => $post['expired_date'][$i]  ?? '',
                'no_lot'               => $post['no_lot'][$i]        ?? null,
                'pajak'                => $pajak,
                'disc'                 => $disc,
                'subtotal_before_disc' => $subtotal_before_disc,
                'subtotal_after_disc'  => $subtotal_after_disc,
                'hrg_satuan'           => $hrg,
                'hrg_pokok'            => $hrg_pk,
                'harga_approval_by'     => $harga_approval_by,
                'total_harga'          => $total_tax,
                'berat_gram'           => (float)($post['berat_gram'][$i]  ?? 0),
                'kubikasi_m3'          => (float)($post['kubikasi_m3'][$i] ?? 0),
                'create_by'            => $this->_getUsername(),
            ];
        }
        return $details;
    }

    private function _validate_harga_approval(array $details)
    {
        $errors = [];
        foreach ($details as $d) {
            $hrg = (float)($d['hrg_satuan'] ?? 0);
            $hpp = (float)($d['hrg_pokok'] ?? 0);
            $is_ubah_harga = $hrg > 0 && $hpp > 0 && abs($hrg - $hpp) > 0.001;
            if ($is_ubah_harga && empty($d['harga_approval_by'])) {
                $errors[] = ($d['nama_barang'] ?? 'Barang') . ': pilih approval perubahan harga.';
            }
        }
        return $errors;
    }

    private function _format_detail_produk_log(array $details)
    {
        return array_map(function($d) {
            $nama = $d['nama_barang'] ?? '-';
            $box = $d['qty_box'] ?? 0;
            $ecer = $d['qty_satuan'] ?? 0;
            $total = $d['qty'] ?? 0;

            return $nama
                . ' | Box: ' . $box
                . ' | Ecer: ' . $ecer . ' pcs'
                . ' | Total: ' . $total . ' pcs';
        }, $details);
    }

    private function _hydrate_missing_log_detail_produk(array $logs)
    {
        $cache = [];
        foreach ($logs as &$log) {
            if (!empty($log['detail_produk']) || empty($log['no_so'])) {
                continue;
            }

            $aksi = strtoupper((string)($log['aksi'] ?? ''));
            if (!in_array($aksi, ['CREATE_SO', 'UPDATE_SO', 'REKAM_SO'], true)) {
                continue;
            }

            $no_so = (string)$log['no_so'];
            if (!array_key_exists($no_so, $cache)) {
                $so = $this->db
                    ->select('id_so')
                    ->get_where('tbso_sales_order', ['no_so' => $no_so])
                    ->row_array();

                $cache[$no_so] = '';
                if ($so) {
                    $detail_str = $this->_format_detail_produk_log(
                        $this->M_SalesOrder->get_so_detail($so['id_so'])
                    );
                    $cache[$no_so] = implode("\n", $detail_str);
                }
            }

            $log['detail_produk'] = $cache[$no_so];
        }
        unset($log);

        return $logs;
    }

    // ================================================================
    // PRIVATE — parse POST item faktur
    // ================================================================
    private function _parse_faktur_items($post)
    {
        $items = [];
        if (empty($post['id_so_detail']) || !is_array($post['id_so_detail'])) return $items;

        foreach ($post['id_so_detail'] as $i => $id_so_detail) {
            if (empty($id_so_detail)) continue;

            $hrg         = (float)($post['hrg_satuan'][$i]  ?? 0);
            $hrg_pk      = (float)($post['hrg_pokok'][$i]   ?? 0);
            $isi_per_box = max(1, (int)($post['isi_per_box'][$i] ?? 1));
            if (isset($post['qty_box_input'][$i]) || isset($post['qty_pcs_input'][$i])) {
                $qty_box_input = (float)($post['qty_box_input'][$i] ?? 0);
                $qty_pcs_input = (float)($post['qty_pcs_input'][$i] ?? 0);
                $qty = ($qty_box_input * $isi_per_box) + $qty_pcs_input;
            } else {
                $qty_input   = isset($post['qty_input'][$i])
                    ? (float)$post['qty_input'][$i]
                    : (float)($post['qty_faktur'][$i] ?? 0);
                $qty_mode    = strtolower(trim($post['qty_mode'][$i] ?? 'pcs'));
                $qty         = $qty_mode === 'box' ? ($qty_input * $isi_per_box) : $qty_input;
            }
            if ($qty <= 0) continue; // lewati item dengan qty 0

            $pajak       = (float)($post['pajak'][$i]        ?? 0);
            $disc        = (float)($post['disc'][$i]         ?? 0);

            $subtotal_before_disc = $hrg * $qty;
            $subtotal_after_disc  = $subtotal_before_disc * (1 - $disc / 100);
            $total_harga          = $subtotal_after_disc;

            $items[] = [
                'id_so_detail'         => $id_so_detail,
                'kd_barang'            => $post['kd_barang'][$i]      ?? '',
                'nama_barang'          => $post['nama_barang'][$i]     ?? '',
                'no_lot'               => $post['no_lot'][$i]          ?? null,
                'expired_date'         => $post['expired_date'][$i]    ?? '',
                'qty'                  => $qty,
                'qty_box'              => floor($qty / $isi_per_box),
                'qty_satuan'           => fmod($qty, $isi_per_box),
                'isi_per_box'          => $isi_per_box,
                'satuan'               => $post['satuan'][$i]          ?? '',
                'hrg_satuan'           => $hrg,
                'hrg_pokok'            => $hrg_pk,
                'disc'                 => $disc,
                'pajak'                => $pajak,
                'subtotal_before_disc' => $subtotal_before_disc,
                'subtotal_after_disc'  => $subtotal_after_disc,
                'total_harga'          => $total_harga,
                'berat_gram'           => (float)($post['berat_gram'][$i]  ?? 0),
                'kubikasi_m3'          => (float)($post['kubikasi_m3'][$i] ?? 0),
            ];
        }
        return $items;
    }

    // ================================================================
    // LIST DO (tidak berubah)
    // ================================================================
    public function list_do()
    {
        $data['page_title'] = 'KARISMA - SALES - LIST DO';
        $data['listdo']     = $this->M_Logistik->get_do_for_sales();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/list_do.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_do($kd_do)
    {
        $query = $this->db->query("
            SELECT x.norut, d.nama_customer AS nama_kios, d.telp1, d.telp2,
                   x.kd_rute, d.regional, x.id, x.kd_faktur, x.tgl_transaksi,
                   x.note_faktur, c.kd_barang AS kd_system, c.nama_barang AS nm_barang,
                   x.no_lot, x.nominal_p, x.jtempo, x.tgl_exp, x.satuan,
                   x.status, x.kd_do, x.qty,
                   (c.p * c.l * c.t) AS dimensi,
                   FLOOR(x.qty / (c.p * c.l * c.t)) AS qty_box,
                   (x.qty % (c.p * c.l * c.t)) AS qty_pcs
            FROM (
                SELECT a.id, a.norut, a.kd_do, a.kd_customer, a.kd_rute,
                       a.kd_faktur, a.tgl_transaksi, a.kd_barang, a.no_lot,
                       a.tgl_exp, a.nominal_p, a.jtempo, a.note_faktur,
                       a.satuan, a.status, SUM(a.qty) AS qty
                FROM tb_detail_do a
                JOIN tb_do b ON b.kd_do = a.kd_do
                WHERE b.kd_do = ?
                GROUP BY a.id, a.norut, a.kd_do, a.kd_customer, a.kd_rute,
                         a.kd_faktur, a.tgl_transaksi, a.kd_barang, a.no_lot,
                         a.tgl_exp, a.nominal_p, a.jtempo, a.satuan, a.status
            ) x
            JOIN tb_master_barang_all c ON c.kd_barang = x.kd_barang
            JOIN tb_customer d ON d.kd_customer = x.kd_customer
            ORDER BY d.nama_customer ASC, x.kd_faktur ASC, c.nama_barang ASC
        ", [$kd_do]);

        $query1 = $this->db->query("
            SELECT b.id, b.kd_do, b.regional, b.nolambung, b.driver, b.status,
                   lcs.action AS sales_confirm_status,
                   lcs.confirm_by AS sales_confirm_by,
                   lcs.confirm_at AS sales_confirm_at,
                   lcs.note AS sales_confirm_note,
                   COUNT(DISTINCT a.kd_barang) AS total_barang,
                   ROUND(SUM(a.qty * m.berat)/1000000, 2) AS total_tonase_faktur,
                   ROUND(SUM(a.qty * m.kubikasi), 2) AS total_kubikasi,
                   COUNT(DISTINCT a.kd_customer) AS totalfaktur
            FROM tb_detail_do a
            JOIN tb_do b ON b.kd_do = a.kd_do
            LEFT JOIN tb_log_confirm_sales lcs
                ON lcs.id = (
                    SELECT l2.id
                    FROM tb_log_confirm_sales l2
                    WHERE l2.kd_do = b.kd_do
                    ORDER BY l2.confirm_at DESC, l2.id DESC
                    LIMIT 1
                )
            JOIN tb_master_barang_all m ON m.kd_barang = a.kd_barang
            WHERE b.kd_do = ?
            GROUP BY b.id, b.kd_do, b.regional, b.nolambung, b.driver, b.status,
                     lcs.action, lcs.confirm_by, lcs.confirm_at, lcs.note
        ", [$kd_do]);

        $query2      = $this->db->where('kd_do', $kd_do)->get('tb_do');
        $log_confirm = $this->M_Logistik->get_log_confirm_sales($kd_do);

        $data['page_title']  = 'KARISMA - SALES - DETAIL DO';
        $data['kdo']         = $query1->result();
        $data['dostatus']    = $query2->result();
        $data['data_list']   = $query->result();
        $data['log_confirm'] = $log_confirm;
        $data['kd_do']       = $kd_do;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/detail_do_sales.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function confirm_loading()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $kd_do      = $this->input->post('kd_do');
        $action     = $this->input->post('action');
        $note       = $this->input->post('note') ?? '';
        $confirm_by = $this->session->userdata('nama');

        if (!$kd_do || !in_array($action, ['siap', 'belum_siap'])) {
            echo json_encode(['msg' => 'error', 'message' => 'Data tidak valid']);
            exit;
        }

        $do = $this->db->where('kd_do', $kd_do)->where('status', 2)->get('tb_do')->row();
        if (!$do) {
            echo json_encode(['msg' => 'error', 'message' => 'DO tidak ditemukan atau sudah dikonfirmasi']);
            exit;
        }

        $this->M_Logistik->update_sales_confirm($kd_do, $action, $confirm_by, $note);

        if ($action === 'siap') {
            $this->M_Logistik->insertlog_do([
                'kd_do'      => $kd_do,
                'tgl_input'  => date('d/m/Y'),
                'keterangan' => 'SALES CONFIRM - SIAP LOADING oleh ' . $confirm_by,
                'inputer'    => $confirm_by,
            ]);
            $this->M_Checker->sync_do_activity($kd_do, 'siap_loading', $confirm_by);
        }

        $msg = ($action === 'siap')
            ? 'Konfirmasi Siap Loading berhasil. DO sekarang On Delivery.'
            : 'DO ditandai Belum Siap Loading.';

        echo json_encode(['msg' => 'success', 'message' => $msg, 'action' => $action]);
        exit;
    }

    public function confirm_rute_loading()
    {
        $this->_ensureSoRouteColumn();

        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $kd_rute = trim((string)$this->input->post('kd_rute', true));
        $note = trim((string)$this->input->post('note', true));
        if ($kd_rute === '' || strtoupper($kd_rute) === 'TANPA_RUTE') {
            echo json_encode(['msg' => 'error', 'message' => 'Rute tidak valid.']);
            exit;
        }

        if (!$this->M_Logistik->get_rute_do($kd_rute)) {
            echo json_encode([
                'msg'     => 'error',
                'message' => 'Rute belum terdaftar sebagai rute LK atau KK.'
            ]);
            exit;
        }

        $confirm_by = $this->_getUsername();
        $created = $this->M_Logistik->create_ready_do_from_faktur_rute($kd_rute, $note, $confirm_by);

        if (!$created) {
            echo json_encode([
                'msg'     => 'error',
                'message' => 'Tidak ada faktur confirmed yang dapat diproses untuk rute ini.'
            ]);
            exit;
        }

        $this->M_Logistik->insertlog_do([
            'kd_do'      => $created['kd_do'],
            'tgl_input'  => date('d/m/Y'),
            'keterangan' => 'SALES SIAP LOADING RUTE ' . $kd_rute . ' oleh ' . $confirm_by,
            'inputer'    => $confirm_by,
        ]);

        $this->M_Checker->sync_route_activity($kd_rute, 'siap_loading', $confirm_by);

        echo json_encode([
            'msg'     => 'success',
            'message' => 'DO ' . $created['kd_do'] . ' dibuat sebagai Proses DO dari '
                . $created['total_faktur'] . ' faktur rute ' . $kd_rute . '.'
        ]);
        exit;
    }

    public function repost_faktur_item()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post') {
            echo json_encode(['status' => false, 'message' => 'Method tidak valid']);
            exit;
        }

        $id_faktur  = (int)$this->input->post('id_faktur');
        $id_fd_list = $this->input->post('id_fd_list'); // array of int
        $repost_by  = $this->session->userdata('username') ?? 'system';

        if ($id_faktur <= 0 || empty($id_fd_list)) {
            echo json_encode(['status' => false, 'message' => 'Data tidak valid']);
            exit;
        }

        $id_fd_list = array_map('intval', (array)$id_fd_list);

        $this->load->model('M_SalesOrder');
        $result = $this->M_SalesOrder->repost_item_faktur($id_faktur, $id_fd_list, $repost_by);

        if (!empty($result['errors'])) {
            echo json_encode(['status' => false, 'message' => implode('<br>', $result['errors'])]);
            exit;
        }

        echo json_encode([
            'status'  => true,
            'message' => 'Item berhasil direpost ke SO.',
            'faktur_cancelled' => ($result['sisa_detail'] === 0),
        ]);
        exit;
    }

    public function get_faktur_detail_json()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id_faktur = (int)$this->input->get('id_faktur');
        if ($id_faktur <= 0) {
            echo json_encode(['status' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        // Cek faktur belum masuk DO
        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$faktur) {
            echo json_encode(['status' => false, 'message' => 'Faktur tidak ditemukan']);
            exit;
        }
        $in_do = $this->db->get_where('tb_detail_do', ['kd_faktur' => $faktur['no_faktur']])->num_rows();
        if ($in_do > 0) {
            echo json_encode(['status' => false, 'message' => 'Faktur sudah masuk DO, tidak bisa direpost']);
            exit;
        }

        $this->load->model('M_SalesOrder');
        $items = $this->M_SalesOrder->get_faktur_detail($id_faktur);

        // Format expired_date untuk tampilan
        foreach ($items as &$item) {
            if (!empty($item['expired_date']) && $item['expired_date'] !== '0000-00-00') {
                $item['expired_date'] = date('d/m/Y', strtotime($item['expired_date']));
            } else {
                $item['expired_date'] = null;
            }
        }

        echo json_encode(['status' => true, 'items' => $items]);
        exit;
    }

    public function kembalikan_so_ke_sales()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post') {
            echo json_encode(['status' => false, 'message' => 'Method tidak valid']);
            exit;
        }

        if (!$this->_canAccessAdminSc()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak']);
            exit;
        }

        $id_so     = (int)$this->input->post('id_so');
        $update_by = $this->_getUsername();

        if ($id_so <= 0) {
            echo json_encode(['status' => false, 'message' => 'ID SO tidak valid']);
            exit;
        }

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || !$this->_canAccessSo($so)) {
            echo json_encode(['status' => false, 'message' => 'SO tidak ditemukan atau akses ditolak']);
            exit;
        }

        $result = $this->M_SalesOrder->kembalikan_so_ke_sales($id_so, $update_by);

        if (!empty($result['errors'])) {
            echo json_encode(['status' => false, 'message' => implode('<br>', $result['errors'])]);
            exit;
        }

        $this->M_ActivityLog->log(
            $so['no_so'] ?? '', '', 'KEMBALIKAN_SO_KE_SALES',
            'SO dikembalikan ke Sales oleh Admin SC. Status baru: ' . $result['new_status'],
            $update_by
        );

        $message = 'SO berhasil dikembalikan ke Sales dengan status <b>' . $result['new_status'] . '</b>.';
        
        // Jika DO otomatis dibuat karena matching barang tidak terfaktur = tidak dimuat
        if (!empty($result['do_created'])) {
            $message .= '<br><br>🎉 <b>DO ' . htmlspecialchars($result['do_created']) . '</b> berhasil dibuat otomatis karena barang yang tidak terfaktur cocok dengan barang yang tidak dimuat (checker loading).';
        }

        echo json_encode([
            'status'     => true,
            'message'    => $message,
            'new_status' => $result['new_status'],
            'do_created' => $result['do_created'] ?? null,
        ]);
        exit;
    }
}
