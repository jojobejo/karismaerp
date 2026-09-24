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
        $this->load->model('M_FakturLog');
        $this->load->model('M_Checker');
        $this->load->library(['form_validation', 'session', 'pagination']);
        $this->load->helper(['url', 'form']);
        $this->config->load('plafon_api', false, true);
        $this->_initApprovalTable();
    }

    private function _initApprovalTable()
    {
        if (!$this->db->table_exists('tbso_approval_harga')) {
            $this->db->query("
                CREATE TABLE `tbso_approval_harga` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_so` INT NOT NULL,
                    `id_so_detail` INT NOT NULL,
                    `harga_lama` DECIMAL(15,2) NOT NULL,
                    `harga_baru` DECIMAL(15,2) NOT NULL,
                    `requested_by` VARCHAR(100) NOT NULL,
                    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
                    `approved_by` VARCHAR(100) DEFAULT NULL,
                    `created_at` DATETIME NOT NULL,
                    `updated_at` DATETIME NOT NULL,
                    INDEX (`id_so`),
                    INDEX (`id_so_detail`),
                    INDEX (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
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
             ?? $this->session->userdata('nama_user')
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
                'nm_karyawan' => $row['nm_karyawan'] ?? ($nama ?? 'system'),
                'wilayah'     => $row['wilayah']     ?? ($wil ?? ''),
                'username'    => $row['username']    ?? ($usn ?? 'system'),
            ];
        }
        return ['nm_karyawan' => $nama ?? 'system', 'wilayah' => $wil ?? '', 'username' => $usn ?? 'system'];
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
        if (empty($customers)) {
            return [];
        }

        $this->load->model('M_pembayaran');

        $kdCustomers1000 = [];
        foreach ($customers as $c) {
            if (isset($c['plafon_aktif']) && (float)$c['plafon_aktif'] == 1000 && !empty($c['kd_customer'])) {
                $kdCustomers1000[] = $c['kd_customer'];
            }
        }

        $unpaidKdMap = [];
        if (!empty($kdCustomers1000)) {
            $unpaidKdMap = $this->M_pembayaran->get_unpaid_customer_kd_map($kdCustomers1000);
        }

        // Ambil rekapitulasi customer yang memiliki piutang (faktur belum lunas)
        $unpaidSummaryList = $this->M_pembayaran->get_customers_with_unpaid_faktur();
        $unpaidSummaryMap  = [];
        foreach ($unpaidSummaryList as $row) {
            $kdCust = $row['kd_customer'] ?? '';
            if ($kdCust !== '') {
                $unpaidSummaryMap[$kdCust] = [
                    'total_piutang' => (float)($row['sisa_tagihan'] ?? 0),
                    'total_faktur'  => (int)($row['total_faktur'] ?? 0),
                ];
            }
        }

        foreach ($customers as &$customer) {
            $customer['plafon_aktif']      = $customer['plafon_aktif']      ?? null;
            $kd = $customer['kd_customer'] ?? '';

            if (isset($unpaidSummaryMap[$kd])) {
                $customer['has_piutang']          = true;
                $customer['total_piutang']        = $unpaidSummaryMap[$kd]['total_piutang'];
                $customer['total_faktur_piutang'] = $unpaidSummaryMap[$kd]['total_faktur'];
                $customer['piutang']              = $unpaidSummaryMap[$kd]['total_piutang'];
            } else {
                $customer['has_piutang']          = false;
                $customer['total_piutang']        = 0;
                $customer['total_faktur_piutang'] = 0;
                $customer['piutang']              = 0;
            }

            $customer['plafon_status']     = null;
            $customer['plafon_updated_at'] = $customer['plafon_updated_at'] ?? null;
            $customer['has_unpaid_1000']   = isset($unpaidKdMap[$kd]);
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

    public function ajax_customer_piutang()
    {
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $kd_customer = trim((string)($this->input->post('kd_customer') ?: $this->input->get('kd_customer')));
        if ($kd_customer === '') {
            echo json_encode([
                'status'        => 'error',
                'message'       => 'Kode customer tidak valid.',
                'has_piutang'   => false,
                'total_piutang' => 0,
                'total_faktur'  => 0,
                'faktur_list'   => [],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->load->model('M_pembayaran');
        $fakturs = $this->M_pembayaran->get_unpaid_faktur_by_customer($kd_customer);

        $total_piutang = 0;
        $faktur_list   = [];
        foreach ($fakturs as $f) {
            $sisa = (float)($f['sisa_tagihan'] ?? 0);
            if ($sisa > 0) {
                $total_piutang += $sisa;
                $faktur_list[] = [
                    'no_faktur'           => $f['no_faktur'] ?? '-',
                    'tanggal_faktur'      => $f['tanggal_faktur'] ?? '-',
                    'tanggal_jatuh_tempo' => $f['tanggal_jatuh_tempo'] ?? '-',
                    'total_tagihan'       => (float)($f['total_tagihan'] ?? 0),
                    'total_pembayaran'    => (float)($f['total_pembayaran'] ?? 0),
                    'sisa_tagihan'        => $sisa,
                    'status_overdue'      => $f['status_overdue'] ?? '-',
                    'hari_overdue'        => (int)($f['hari_overdue'] ?? 0),
                    'sisa_hari'           => (int)($f['sisa_hari'] ?? 0),
                ];
            }
        }

        echo json_encode([
            'status'        => 'ok',
            'kd_customer'   => $kd_customer,
            'has_piutang'   => $total_piutang > 0,
            'total_piutang' => $total_piutang,
            'total_faktur'  => count($faktur_list),
            'faktur_list'   => $faktur_list,
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
        return in_array(strtoupper((string)$this->session->userdata('jobdesk')), ['ADMINSC', 'SC', 'SALESCOUNTER', 'ADMIN', 'MNGSC', 'MANAGER SC', 'MANAGERSC'], true);
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

    private function _ensureFakturStatusEnum()
    {
        $column = $this->db->query("SHOW COLUMNS FROM tbso_faktur_penjualan LIKE 'status'")->row_array();
        $type = strtolower((string)($column['Type'] ?? ''));
        if (strpos($type, "'selesai'") !== false && strpos($type, "'draft'") !== false && strpos($type, "'proses_do'") !== false) {
            return;
        }

        $this->db->query("
            ALTER TABLE tbso_faktur_penjualan
            MODIFY COLUMN status ENUM('draft','confirmed','proses_do','selesai','selesai_do','cancelled')
            NOT NULL DEFAULT 'confirmed' COMMENT 'draft | confirmed | proses_do | selesai | cancelled'
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
        $pending = $this->M_SalesOrder->get_pending_cancel_requests();
        $data['pending_cancels'] = !empty($pending) ? array_unique(array_column($pending, 'id_so')) : [];
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
        $this->_ensureFakturStatusEnum();
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
            
            $tgl_transaksi = substr((string)($row['tanggal_transaksi'] ?? date('Y-m-d')), 0, 10);
            $group_key = $rute . '||' . $tgl_transaksi;

            if (!isset($route_summary[$group_key])) {
                $route_summary[$group_key] = [
                    'kd_rute'                    => $rute,
                    'tgl_transaksi'              => $tgl_transaksi,
                    'total_so'                   => 0,
                    'total_sudah_faktur'         => 0,
                    'total_belum_faktur'         => 0,
                    'total_qty_siap_faktur'      => 0,
                    'total_qty_tidak_terkirim'   => 0,
                    'total_item_ditolak'         => 0,
                    'latest_update_at'           => '',
                ];
            }

            $route_summary[$group_key]['total_so']++;
            if ((int)($row['jumlah_faktur'] ?? 0) > 0) {
                $route_summary[$group_key]['total_sudah_faktur']++;
            } else {
                $route_summary[$group_key]['total_belum_faktur']++;
            }
            $route_summary[$group_key]['total_qty_siap_faktur']    += (float)($row['total_qty_siap_faktur'] ?? 0);
            $route_summary[$group_key]['total_qty_tidak_terkirim'] += (float)($row['total_qty_tidak_terkirim'] ?? 0);
            $route_summary[$group_key]['total_item_ditolak']       += (int)($row['jumlah_item_ditolak'] ?? 0);
            $updated_at = (string)($row['update_at'] ?? $row['create_at'] ?? '');
            if ($updated_at > $route_summary[$group_key]['latest_update_at']) {
                $route_summary[$group_key]['latest_update_at'] = $updated_at;
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

        $this->_ensureFakturStatusEnum();
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

        // Enforce approval check for non-manager SC users (e.g. ADMINSC)
        $jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
        $can_bypass = in_array($jobdesk, ['MNGSC', 'MANAGER SC', 'MANAGERSC', 'ADMIN'], true);

        if (!$can_bypass) {
            $approved_request = $this->db->get_where('tbso_approval_harga', [
                'id_so_detail' => $id_so_detail,
                'harga_baru' => $harga,
                'status' => 'approved'
            ])->row_array();

            if (!$approved_request) {
                echo json_encode([
                    'msg' => 'error',
                    'message' => 'Perubahan harga memerlukan persetujuan Manager SC. Silakan ajukan permintaan persetujuan.',
                    'require_approval' => true
                ]);
                exit;
            }
        }

        $old_detail = $this->db->get_where('tbso_sales_order_detail', ['id' => $id_so_detail])->row_array();
        $updated = $this->M_SalesOrder->update_so_detail_harga($id_so, $id_so_detail, $harga, $this->_getUsername());
        if ($updated) {
            $this->M_FakturLog->log(
                $so['no_so'] ?? '',
                null,
                null,
                'UPDATE_HARGA',
                'Admin SC mengubah harga item ' . ($old_detail['nama_barang'] ?? '') . ' pada SO ' . ($so['no_so'] ?? '') . ' menjadi Rp ' . number_format($harga, 0, ',', '.'),
                $this->_getUsername()
            );
        }
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
                $cp     = strtolower(trim((string)($post['cara_pembayaran'] ?? '')));
                if ($plafon !== null && (float)$plafon == 1000) {
                    if (!in_array($cp, ['cash', 'transfer'], true)) {
                        $this->session->set_flashdata('error', 'Customer dengan plafon 1.000 harus menggunakan pembayaran Cash atau Transfer.');
                        redirect('sales_order/create');
                        return;
                    }

                    $this->load->model('M_pembayaran');
                    $unpaid_invoices = $this->M_pembayaran->get_unpaid_faktur_by_customer($customer['kd_customer']);
                    if (!empty($unpaid_invoices)) {
                        $this->session->set_flashdata('error', 'Customer dengan plafon 1.000 tidak dapat membuat SO baru karena masih memiliki nota (Faktur) yang belum lunas.');
                        redirect('sales_order/create');
                        return;
                    }
                }

                $grand_total = 0;
                foreach ($details as $d) {
                    $grand_total += (float)$d['total_harga'];
                }
                // Pembayaran Cash dapat dilakukan oleh semua customer berapapun jumlah transaksinya tanpa memperdulikan plafon
                if ($cp !== 'cash' && $plafon !== null && (float)$plafon != 1000 && $grand_total > $plafon) {
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
            // Deduct plafon customer
            if (!empty($post['customer_id'])) {
                $customer_check = $this->db->get_where('tb_customer', ['kd_customer' => $post['customer_id']])->row_array();
                if ($customer_check && isset($customer_check['plafon_aktif']) && (float)$customer_check['plafon_aktif'] != 1000) {
                    $so_total = 0;
                    foreach ($details as $d) {
                        $so_total += (float)$d['total_harga'];
                    }
                    if ($so_total > 0) {
                        $this->db->set('plafon_aktif', 'plafon_aktif - ' . $so_total, FALSE);
                        $this->db->where('kd_customer', $post['customer_id']);
                        $this->db->update('tb_customer');
                    }
                }
            }

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
                $cp     = strtolower(trim((string)($post['cara_pembayaran'] ?? '')));
                if ($plafon !== null && (float)$plafon == 1000) {
                    if (!in_array($cp, ['cash', 'transfer'], true)) {
                        $this->session->set_flashdata('error', 'Customer dengan plafon 1.000 harus menggunakan pembayaran Cash atau Transfer.');
                        redirect('sales_order/edit/' . $id_so);
                        return;
                    }

                    $this->load->model('M_pembayaran');
                    $unpaid_invoices = $this->M_pembayaran->get_unpaid_faktur_by_customer($customer['kd_customer']);
                    if (!empty($unpaid_invoices)) {
                        $this->session->set_flashdata('error', 'Customer dengan plafon 1.000 tidak dapat melakukan pemesanan karena masih memiliki nota (Faktur) yang belum lunas.');
                        redirect('sales_order/edit/' . $id_so);
                        return;
                    }
                }

                $grand_total = 0;
                foreach ($details as $d) {
                    $grand_total += (float)$d['total_harga'];
                }
                // Pembayaran Cash dapat dilakukan oleh semua customer berapapun jumlah transaksinya tanpa memperdulikan plafon
                if ($cp !== 'cash' && $plafon !== null && (float)$plafon != 1000 && $grand_total > $plafon) {
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

        // Calculate old total for plafon refund
        $old_total = 0;
        $old_details = $this->M_SalesOrder->get_so_detail($id_so);
        foreach ($old_details as $d) {
            $old_total += (float)$d['total_harga'];
        }
        $old_customer = $so['kd_customer'];

        $result = $this->M_SalesOrder->update_so($id_so, $header, $details);

        if ($result) {
            // Plafon adjustments
            // 1. Refund old total to old customer
            if ($old_total > 0 && !empty($old_customer)) {
                $old_cust_check = $this->db->get_where('tb_customer', ['kd_customer' => $old_customer])->row_array();
                if ($old_cust_check && isset($old_cust_check['plafon_aktif']) && (float)$old_cust_check['plafon_aktif'] != 1000) {
                    $this->db->set('plafon_aktif', 'plafon_aktif + ' . $old_total, FALSE);
                    $this->db->where('kd_customer', $old_customer);
                    $this->db->update('tb_customer');
                }
            }
            
            // 2. Deduct new total from new customer
            if (!empty($post['customer_id'])) {
                $new_total = 0;
                foreach ($details as $d) {
                    $new_total += (float)$d['total_harga'];
                }
                if ($new_total > 0) {
                    $new_cust_check = $this->db->get_where('tb_customer', ['kd_customer' => $post['customer_id']])->row_array();
                    if ($new_cust_check && isset($new_cust_check['plafon_aktif']) && (float)$new_cust_check['plafon_aktif'] != 1000) {
                        $this->db->set('plafon_aktif', 'plafon_aktif - ' . $new_total, FALSE);
                        $this->db->where('kd_customer', $post['customer_id']);
                        $this->db->update('tb_customer');
                    }
                }
            }

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

        // Restore plafon
        $customer_check = $this->db->get_where('tb_customer', ['kd_customer' => $so['kd_customer']])->row_array();
        if ($customer_check && isset($customer_check['plafon_aktif']) && (float)$customer_check['plafon_aktif'] != 1000) {
            $details = $this->M_SalesOrder->get_so_detail($id_so);
            $so_total = 0;
            foreach ($details as $d) {
                $so_total += (float)$d['total_harga'];
            }
            if ($so_total > 0) {
                $this->db->set('plafon_aktif', 'plafon_aktif + ' . $so_total, FALSE);
                $this->db->where('kd_customer', $so['kd_customer']);
                $this->db->update('tb_customer');
            }
        }

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

        $kelompok_filter = strtolower(trim($this->input->get('kelompok_filter', true) ?? 'bkp'));
        if (!in_array($kelompok_filter, ['promosi', 'dagangan', 'bkps', 'bkp'], true)) {
            $kelompok_filter = 'bkp';
        }

        $tax_mode = ($kelompok_filter === 'bkp') ? 'pajak' : 'non_pajak';
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

        $items_outstanding = array_filter($items_outstanding, function($d) use ($kelompok_filter) {
            $kd_barang = trim((string)($d['kd_barang'] ?? ''));
            $first_char = strtoupper(substr($kd_barang, 0, 1));
            $desc = strtoupper(trim((string)($d['kelompok_dagang_deskripsi'] ?? '')));
            $is_bkps = (strpos($desc, 'BKPS') !== false);

            if ($kelompok_filter === 'promosi') {
                return $first_char === 'Z';
            } elseif ($kelompok_filter === 'dagangan') {
                return $first_char === 'A';
            } elseif ($kelompok_filter === 'bkps') {
                return $first_char === 'Q' && $is_bkps;
            } elseif ($kelompok_filter === 'bkp') {
                return $first_char === 'Q' && !$is_bkps;
            }
            return false;
        });

        if (empty($items_outstanding)) {
            $message = !empty($selected_items)
                ? 'Item yang dipilih tidak valid, sudah difakturkan seluruhnya, atau tidak sesuai jenis faktur ' . ($tax_mode === 'pajak' ? 'Pajak (kode Q)' : 'Non Pajak (kode bukan Q)') . '.'
                : 'Tidak ada barang ' . ($tax_mode === 'pajak' ? 'Pajak (kode Q)' : 'Non Pajak (kode bukan Q)') . ' dengan kategori kelompok dagang tersebut yang siap difakturkan.';
            $this->session->set_flashdata('error', $message);
            redirect('sales_order/admin_sc/pilih_barang/' . $id_so);
            return;
        }

        $data['page_title']        = 'KARISMA - Buat Faktur Penjualan dari SO ' . $so['no_so'];
        $data['so']                = $so;
        $is_faktur_z               = !empty($so['is_faktur_z']);
        $data['is_faktur_z']       = $is_faktur_z;
        $data['details']           = array_map(function($item) use ($tax_rate) {
            $item['pajak'] = $tax_rate;
            // Faktur Z Induk nominal aslinya tetap utuh (potongan 20% baru diterapkan saat dipecah menjadi Faktur H)
            return $item;
        }, array_values($items_outstanding));
        $faktur_prefix             = $is_faktur_z ? 'Z' : $this->_getFakturUserPrefix();
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

        $details = $this->M_SalesOrder->get_so_detail($id_so);
        $details_map = [];
        foreach ($details as $d) {
            $details_map[(int)$d['id_so_detail']] = $d;
        }

        $first_category = null;
        foreach ($faktur_items as $item) {
            $so_det = $details_map[(int)$item['id_so_detail']] ?? null;
            if (!$so_det) {
                $this->session->set_flashdata('error', 'Item tidak valid.');
                redirect('sales_order/admin_sc/pilih_barang/' . $id_so);
                return;
            }
            $kd = trim((string)($so_det['kd_barang'] ?? ''));
            $c = strtoupper(substr($kd, 0, 1));
            $desc = strtoupper(trim((string)($so_det['kelompok_dagang_deskripsi'] ?? '')));
            $is_bkps = (strpos($desc, 'BKPS') !== false);
            
            $cat = 'other';
            if ($c === 'Z') {
                $cat = 'promosi';
            } elseif ($c === 'A') {
                $cat = 'dagangan';
            } elseif ($c === 'Q') {
                $cat = $is_bkps ? 'bkps' : 'bkp';
            }
            
            if ($first_category === null) {
                $first_category = $cat;
            } elseif ($first_category !== $cat) {
                $this->session->set_flashdata('error', 'Tidak boleh menggabungkan barang dengan kategori yang berbeda dalam satu faktur.');
                redirect('sales_order/admin_sc/pilih_barang/' . $id_so);
                return;
            }
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
            $tax_mode = $this->input->post('tax_mode', true) ?: 'non_pajak';
            $item_params = [];
            if (!empty($post['id_so_detail']) && is_array($post['id_so_detail'])) {
                foreach ($post['id_so_detail'] as $i => $id_so_detail) {
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
                    if ($qty > 0) {
                        $item_params[] = 'item[]=' . $id_so_detail;
                    }
                }
            }
            $query_str = '?tax_mode=' . rawurlencode($tax_mode);
            if (!empty($item_params)) {
                $query_str .= '&' . implode('&', $item_params);
            }
            redirect('sales_order/admin_sc/form_faktur/' . $id_so . $query_str);
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

            $this->M_FakturLog->log(
                $so['no_so'] ?? '',
                $no_faktur,
                is_array($result) ? ($result['id_faktur'] ?? null) : null,
                'BUAT_FAKTUR',
                'Admin SC membuat Faktur Penjualan ' . $no_faktur . ' dari SO ' . ($so['no_so'] ?? '') . ' (' . count($faktur_items) . ' item).',
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

        // Resolve numeric id_faktur (supports lookup by no_faktur string from buku besar drilldown)
        $numeric_id_faktur = $faktur['id_faktur'];

        $details = $this->M_SalesOrder->get_faktur_detail($numeric_id_faktur);
        $so      = $this->M_SalesOrder->get_so($faktur['id_so']);
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $child_fakturs = [];
        if (!empty($faktur['is_split_parent'])) {
            $child_fakturs = $this->db->get_where('tbso_faktur_z_pecah', ['parent_id_faktur' => $numeric_id_faktur])->result_array();
        }

        $parent_faktur = null;
        if (!empty($faktur['parent_id_faktur'])) {
            $parent_faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $faktur['parent_id_faktur']])->row_array();
        }

        $has_remaining_split_qty = false;
        if (!empty($so['is_faktur_z']) && empty($faktur['parent_id_faktur']) && !in_array($faktur['status'], ['cancelled', 'draft'], true)) {
            $child_details = $this->db->select('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date, SUM(fd.qty) as qty_allocated')
                ->from('tbso_faktur_z_pecah_detail fd')
                ->where('fd.parent_id_faktur', $numeric_id_faktur)
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

    // ================================================================
    // PECAH FAKTUR Z — MODUL UTAMA LIST FAKTUR Z
    // ================================================================
    public function pecah_faktur()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $this->_ensureFakturStatusEnum();
        $this->_ensureFakturPaymentInfoColumns();

        $filter = [
            'date1'         => $this->input->post('date1') ?: $this->input->get('date1', true),
            'date2'         => $this->input->post('date2') ?: $this->input->get('date2', true),
            'status_pecah'  => $this->input->post('status_pecah') ?: $this->input->get('status_pecah', true) ?: 'all',
            'status_faktur' => $this->input->post('status_faktur') ?: $this->input->get('status_faktur', true) ?: 'all',
            'search'        => $this->input->post('search') ?: $this->input->get('search', true),
        ];

        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        $fakturs = $this->M_SalesOrder->get_faktur_z_list($filter);
        $fakturs_h = $this->M_SalesOrder->get_all_faktur_pecah_h($filter);

        // Ringkasan statistik untuk widget
        $stat = [
            'total'          => count($fakturs),
            'belum_dipecah'  => 0,
            'sudah_dipecah'  => 0,
            'turunan'        => count($fakturs_h),
            'total_nilai'    => 0,
        ];

        // Pisahkan Faktur Z belum dipecah vs sudah dipecah
        $fakturs_belum_dipecah = [];
        $fakturs_sudah_dipecah = [];

        // Kelompokkan Faktur Z berdasarkan Nama Kios
        $kios_grouped = [];
        foreach ($fakturs as $f) {
            $stat['total_nilai'] += (float)($f['grand_total'] ?? 0);
            if ($f['tipe_faktur'] === 'belum_dipecah') {
                $stat['belum_dipecah']++;
                $fakturs_belum_dipecah[] = $f;
            } else {
                $stat['sudah_dipecah']++;
                $fakturs_sudah_dipecah[] = $f;
            }

            // Penentuan nama kios
            $kios_name = !empty($f['nama_kios']) ? trim($f['nama_kios']) : (!empty($f['display_customer_name']) ? trim($f['display_customer_name']) : (!empty($f['customer_name']) ? trim($f['customer_name']) : 'Tanpa Kios'));
            if (!isset($kios_grouped[$kios_name])) {
                $kios_grouped[$kios_name] = [
                    'nama_kios'          => $kios_name,
                    'kd_customer'        => $f['kd_customer'] ?? '-',
                    'nama_customer'      => $f['display_customer_name'] ?? $f['customer_name'] ?? '-',
                    'kd_rute'            => $f['customer_kd_rute'] ?? $f['so_kd_rute'] ?? '',
                    'total_faktur'       => 0,
                    'total_nilai'        => 0,
                    'total_qty'          => 0,
                    'total_barang'       => 0,
                    'belum_dipecah'      => 0,
                    'sudah_dipecah'      => 0,
                    'total_nilai_belum'  => 0,
                    'total_qty_belum'    => 0,
                    'total_barang_belum' => 0,
                    'fakturs'            => []
                ];
            }

            $kios_grouped[$kios_name]['total_faktur']++;
            $kios_grouped[$kios_name]['total_nilai'] += (float)($f['grand_total'] ?? 0);
            $kios_grouped[$kios_name]['total_qty']   += (float)($f['total_qty'] ?? 0);
            $kios_grouped[$kios_name]['total_barang']+= (int)($f['total_barang'] ?? 0);
            if ($f['tipe_faktur'] === 'belum_dipecah') {
                $kios_grouped[$kios_name]['belum_dipecah']++;
                $kios_grouped[$kios_name]['total_nilai_belum']  += (float)($f['grand_total'] ?? 0);
                $kios_grouped[$kios_name]['total_qty_belum']    += (float)($f['total_qty'] ?? 0);
                $kios_grouped[$kios_name]['total_barang_belum'] += (int)($f['total_barang'] ?? 0);
            } else {
                $kios_grouped[$kios_name]['sudah_dipecah']++;
            }
            $kios_grouped[$kios_name]['fakturs'][] = $f;
        }

        // Filter kios yang masih memiliki Faktur Z belum dipecah
        $kios_grouped_belum = [];
        foreach ($kios_grouped as $kn => $kg) {
            if ($kg['belum_dipecah'] > 0) {
                $kios_grouped_belum[$kn] = $kg;
            }
        }

        // Urutkan grup kios berdasarkan nama kios secara alfabetis
        ksort($kios_grouped, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($kios_grouped_belum, SORT_NATURAL | SORT_FLAG_CASE);

        $data['page_title']            = 'KARISMA - Modul Pecah Faktur Z';
        $data['fakturs']               = $fakturs;
        $data['fakturs_belum_dipecah'] = $fakturs_belum_dipecah;
        $data['fakturs_sudah_dipecah'] = $fakturs_sudah_dipecah;
        $data['fakturs_h']             = $fakturs_h;
        $data['kios_grouped']          = $kios_grouped;
        $data['kios_grouped_belum']    = $kios_grouped_belum;
        $data['stat']                  = $stat;
        $data['filter']                = $filter;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pecah_faktur_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function pecah_faktur_kios($kd_customer = null)
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        if (empty($kd_customer)) {
            redirect('sales_order/pecah_faktur');
            return;
        }

        $kd_customer = rawurldecode($kd_customer);
        $this->_ensureFakturStatusEnum();
        $this->_ensureFakturPaymentInfoColumns();

        // Ambil info customer / kios dari tb_customer
        $customer = $this->db->get_where('tb_customer', ['kd_customer' => $kd_customer])->row_array();
        if (!$customer) {
            $faktur_sample = $this->db->get_where('tbso_faktur_penjualan', ['kd_customer' => $kd_customer])->row_array();
            if ($faktur_sample) {
                $customer = [
                    'kd_customer'   => $kd_customer,
                    'nama_kios'     => $faktur_sample['customer_name'] ?? 'Kios ' . $kd_customer,
                    'nama_customer' => $faktur_sample['customer_name'] ?? '-',
                    'kd_rute'       => '-',
                    'alamat_kios'   => '-',
                    'regional'      => '-'
                ];
            } else {
                $this->session->set_flashdata('error', 'Kios dengan kode customer ' . htmlspecialchars($kd_customer) . ' tidak ditemukan.');
                redirect('sales_order/pecah_faktur');
                return;
            }
        }

        $filter = [
            'date1'         => $this->input->post('date1') ?: $this->input->get('date1', true),
            'date2'         => $this->input->post('date2') ?: $this->input->get('date2', true),
            'status_pecah'  => $this->input->post('status_pecah') ?: $this->input->get('status_pecah', true) ?: 'all',
            'status_faktur' => $this->input->post('status_faktur') ?: $this->input->get('status_faktur', true) ?: 'all',
            'search'        => $this->input->post('search') ?: $this->input->get('search', true),
        ];

        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        // Ambil data faktur Z
        $all_fakturs = $this->M_SalesOrder->get_faktur_z_list($filter);
        $kios_fakturs = [];
        $stat = [
            'total'          => 0,
            'belum_dipecah'  => 0,
            'sudah_dipecah'  => 0,
            'total_nilai'    => 0,
            'total_qty'      => 0,
            'total_barang'   => 0
        ];

        $kios_fakturs_belum = [];
        $kios_fakturs_sudah = [];

        foreach ($all_fakturs as $f) {
            if ($f['kd_customer'] === $kd_customer) {
                $kios_fakturs[] = $f;
                $stat['total']++;
                $stat['total_nilai']  += (float)($f['grand_total'] ?? 0);
                $stat['total_qty']    += (float)($f['total_qty'] ?? 0);
                $stat['total_barang'] += (int)($f['total_barang'] ?? 0);
                if ($f['tipe_faktur'] === 'belum_dipecah') {
                    $stat['belum_dipecah']++;
                    $kios_fakturs_belum[] = $f;
                } else {
                    $stat['sudah_dipecah']++;
                    $kios_fakturs_sudah[] = $f;
                }
            }
        }

        $nama_kios = !empty($customer['nama_kios']) ? $customer['nama_kios'] : (!empty($customer['nama_customer']) ? $customer['nama_customer'] : 'Kios ' . $kd_customer);
        $data['page_title']         = 'Faktur Z - ' . $nama_kios;
        $data['customer']           = $customer;
        $data['nama_kios']          = $nama_kios;
        $data['kd_customer']        = $kd_customer;
        $data['kios_fakturs']       = $kios_fakturs;
        $data['kios_fakturs_belum'] = $kios_fakturs_belum;
        $data['kios_fakturs_sudah'] = $kios_fakturs_sudah;
        $data['stat']               = $stat;
        $data['filter']             = $filter;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/pecah_faktur_kios_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Memproses pemecahan otomatis seluruh Faktur Z pada kios yang dipilih (Auto-Split per Kios).
     * Maksimal nilai per faktur pecahan H dibatasi (default Rp 25.000.000).
     */
    public function auto_split_kios($kd_customer = null)
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        if (empty($kd_customer)) {
            $this->session->set_flashdata('error', 'Kode customer kios tidak valid.');
            redirect('sales_order/pecah_faktur');
            return;
        }

        $max_nominal = $this->input->post('max_nominal');
        if (!empty($max_nominal)) {
            $clean_max = preg_replace('/[^0-9]/', '', (string)$max_nominal);
            $max_nominal = (float)$clean_max;
        }
        if (empty($max_nominal) || $max_nominal <= 0) {
            $max_nominal = 25000000;
        }

        $username = $this->_getUsername();
        $result = $this->M_SalesOrder->auto_split_faktur_kios($kd_customer, $max_nominal, $username);

        if (!empty($result['success'])) {
            $this->session->set_flashdata('success', 
                '<strong>Berhasil Melakukan Pemecahan Otomatis!</strong><br>' .
                $result['total_parent_processed'] . ' Faktur Z berhasil diproses menjadi ' . 
                $result['total_created'] . ' Faktur Pecahan (Kode H). Maksimal plafon: Rp ' . 
                number_format($max_nominal, 0, ',', '.') . ' per faktur.'
            );
        } else {
            $msg = !empty($result['message']) ? $result['message'] : 'Gagal memproses pemecahan otomatis Faktur Z.';
            $this->session->set_flashdata('error', $msg);
        }

        redirect('sales_order/pecah_faktur_kios/' . $kd_customer);
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
            redirect('sales_order/pecah_faktur');
            return;
        }
        if (!empty($faktur['parent_id_faktur'])) {
            $this->session->set_flashdata('error', 'Faktur turunan tidak dapat dipecah lagi.');
            redirect('sales_order/pecah_faktur');
            return;
        }
        if (in_array($faktur['status'], ['cancelled', 'draft'], true)) {
            $this->session->set_flashdata('error', 'Faktur dengan status Draft atau Cancelled tidak dapat dipecah.');
            redirect('sales_order/pecah_faktur');
            return;
        }

        // Calculate remaining quantities dari tbso_faktur_z_pecah_detail
        $child_details = $this->db->select('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date, SUM(fd.qty) as qty_allocated')
            ->from('tbso_faktur_z_pecah_detail fd')
            ->where('fd.parent_id_faktur', $id_faktur)
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
            redirect('sales_order/pecah_faktur');
            return;
        }

        $cust_induk = $this->db->get_where('tb_customer', ['kd_customer' => $faktur['kd_customer']])->row_array();
        $nama_kios = !empty($cust_induk['nama_kios']) ? $cust_induk['nama_kios'] : $faktur['customer_name'];
        $customers_acak = $this->M_SalesOrder->get_customers_acak_by_kios($nama_kios, $faktur['kd_customer']);

        $data['page_title']       = 'Pecah Faktur Z - ' . $faktur['no_faktur'];
        $data['faktur']           = $faktur;
        $data['so']               = $so;
        $data['details']          = $details;
        $data['customers']        = !empty($customers_acak) ? $customers_acak : $this->M_SalesOrder->get_customers();
        $data['is_customer_acak'] = !empty($customers_acak);
        $data['kios_induk_nama']  = $nama_kios;
        $data['back_url']         = base_url('sales_order/pecah_faktur');

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

        // Calculate remaining quantities dari tbso_faktur_z_pecah_detail
        $child_details = $this->db->select('fd.id_so_detail, fd.kd_barang, fd.no_lot, fd.expired_date, SUM(fd.qty) as qty_allocated')
            ->from('tbso_faktur_z_pecah_detail fd')
            ->where('fd.parent_id_faktur', $id_faktur)
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
        $parent_details_by_id = [];
        $total_remaining = 0;
        foreach ($details as $d) {
            $parent_details_by_id[$d['id']] = $d;
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

        $cust_induk = $this->db->get_where('tb_customer', ['kd_customer' => $faktur['kd_customer']])->row_array();
        $nama_kios = !empty($cust_induk['nama_kios']) ? $cust_induk['nama_kios'] : $faktur['customer_name'];
        $allowed_acak = $this->M_SalesOrder->get_customers_acak_by_kios($nama_kios, $faktur['kd_customer']);
        $allowed_acak_map = [];
        foreach ($allowed_acak as $ca) {
            $allowed_acak_map[$ca['kd_customer']] = $ca;
        }

        foreach ($splits as $idx => $s) {
            $kd_cust = trim((string)($s['kd_customer'] ?? ''));
            if ($kd_cust === '') {
                $validation_errors[] = "Customer Penerima #" . $idx . " belum dipilih.";
                continue;
            }

            $customer_display_name = '';
            if (!empty($allowed_acak_map)) {
                if (!isset($allowed_acak_map[$kd_cust])) {
                    $validation_errors[] = "Customer Penerima #" . $idx . " (" . htmlspecialchars($kd_cust) . ") bukan kontak person milik kios " . htmlspecialchars($nama_kios) . ". Pastikan customer tidak tertukar!";
                    continue;
                }
                $customer_display_name = $allowed_acak_map[$kd_cust]['kontak_person'];
            } else {
                $cust = $this->db->get_where('tb_customer', ['kd_customer' => $kd_cust])->row_array();
                if (!$cust) {
                    $validation_errors[] = "Customer Penerima #" . $idx . " tidak valid.";
                    continue;
                }
                $customer_display_name = $cust['nama_customer'];
            }

            $items = $s['items'] ?? [];
            $has_qty = false;
            $slot_nominal = 0.0;
            foreach ($items as $itemId => $qty) {
                $qty = (float)$qty;
                if ($qty < 0) {
                    $validation_errors[] = "Kuantitas untuk customer " . htmlspecialchars($customer_display_name) . " tidak boleh negatif.";
                }
                if ($qty > 0) {
                    $has_qty = true;
                    if (!isset($allocated_qtys[$itemId])) {
                        $allocated_qtys[$itemId] = 0.0;
                    }
                    $allocated_qtys[$itemId] += $qty;

                    // Hitung estimasi nominal transaksi pecahan (diskon 20% dari harga induk)
                    if (isset($parent_details_by_id[$itemId])) {
                        $pd_item = $parent_details_by_id[$itemId];
                        $hrg_pecah = round((float)$pd_item['hrg_satuan'] * 0.8, 2);
                        $disc_rate = (float)($pd_item['disc'] ?? 0);
                        $slot_nominal += round($qty * $hrg_pecah * (1 - ($disc_rate / 100)), 2);
                    }
                }
            }

            if (!$has_qty) {
                $validation_errors[] = "Harap masukkan kuantitas barang minimal 1 item untuk customer " . htmlspecialchars($customer_display_name) . ".";
            } else {
                // Cek validasi batas limit 250 Juta per kontak person
                $limit_check = $this->M_SalesOrder->check_kontak_person_limit($kd_cust, $slot_nominal);
                if (!$limit_check['is_allowed']) {
                    $validation_errors[] = $limit_check['message'];
                }
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
            $this->M_FakturLog->log(
                $so['no_so'] ?? '',
                $faktur['no_faktur'] ?? '',
                $id_faktur,
                'SPLIT_FAKTUR',
                'Admin SC memecah Faktur Z ' . ($faktur['no_faktur'] ?? '') . ' menjadi ' . count($splits) . ' faktur turunan.',
                $username
            );
            $this->session->set_flashdata('success', 'Faktur Z <b>' . $faktur['no_faktur'] . '</b> berhasil dipecah menjadi faktur turunan.');
            redirect('sales_order/pecah_faktur');
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses pemecahan: ' . (is_string($result) ? $result : 'Database error'));
            redirect('sales_order/split_faktur/' . $id_faktur);
        }
    }

    public function split_faktur_batch()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $this->_ensureFakturPaymentInfoColumns();
        $this->M_SalesOrder->ensure_faktur_z_pecah_tables();

        $raw_ids = $this->input->post('id_faktur') ?: $this->input->get('id_faktur');
        $id_fakturs = [];
        if (is_array($raw_ids)) {
            $id_fakturs = array_filter(array_map('intval', $raw_ids));
        } elseif (is_string($raw_ids) && trim($raw_ids) !== '') {
            $id_fakturs = array_filter(array_map('intval', explode(',', $raw_ids)));
        }

        if (empty($id_fakturs)) {
            $this->session->set_flashdata('warning', 'Pilih minimal 1 Faktur Z untuk dipecah.');
            redirect('sales_order/pecah_faktur');
            return;
        }

        $jumlah_pecah = (int)($this->input->post('jumlah_pecah') ?: $this->input->get('jumlah_pecah') ?: 2);
        if ($jumlah_pecah < 1) $jumlah_pecah = 1;
        if ($jumlah_pecah > 50) $jumlah_pecah = 50;

        // Ambil data Faktur Z
        $parent_fakturs = [];
        $invalid_reasons = [];

        foreach ($id_fakturs as $fid) {
            $f = $this->M_SalesOrder->get_faktur($fid);
            if (!$f) {
                $invalid_reasons[] = "Faktur #$fid tidak ditemukan.";
                continue;
            }
            if (!empty($f['parent_id_faktur'])) {
                $invalid_reasons[] = "Faktur " . $f['no_faktur'] . " adalah faktur turunan dan tidak dapat dipecah lagi.";
                continue;
            }
            if (in_array($f['status'], ['cancelled', 'draft'], true)) {
                $invalid_reasons[] = "Faktur " . $f['no_faktur'] . " berstatus " . $f['status'] . " sehingga tidak dapat dipecah.";
                continue;
            }
            $parent_fakturs[$fid] = $f;
        }

        if (empty($parent_fakturs)) {
            $this->session->set_flashdata('error', 'Tidak ada Faktur Z valid yang dapat diproses.<br>' . implode('<br>', $invalid_reasons));
            redirect('sales_order/pecah_faktur');
            return;
        }

        // Ambil alokasi yang sudah tersimpan di tbso_faktur_z_pecah_detail untuk parent-parent ini
        $child_allocations = $this->db->select('parent_id_faktur, id_so_detail, kd_barang, no_lot, expired_date, SUM(qty) as qty_allocated')
            ->from('tbso_faktur_z_pecah_detail')
            ->where_in('parent_id_faktur', array_keys($parent_fakturs))
            ->group_by('parent_id_faktur, id_so_detail, kd_barang, no_lot, expired_date')
            ->get()
            ->result_array();

        $allocated_map = [];
        foreach ($child_allocations as $ca) {
            $pid = (int)$ca['parent_id_faktur'];
            $key = implode('|', [
                $ca['id_so_detail'],
                $ca['kd_barang'],
                (string)$ca['no_lot'],
                $ca['expired_date']
            ]);
            $allocated_map[$pid][$key] = (float)$ca['qty_allocated'];
        }

        // Ambil detail item dari masing-masing Faktur Z
        $pool_items = [];
        $total_pool_qty = 0;

        foreach ($parent_fakturs as $pid => $pf) {
            $details = $this->M_SalesOrder->get_faktur_detail($pid);
            foreach ($details as $d) {
                $key = implode('|', [
                    $d['id_so_detail'],
                    $d['kd_barang'],
                    (string)$d['no_lot'],
                    $d['expired_date']
                ]);
                $allocated = $allocated_map[$pid][$key] ?? 0.0;
                $remaining = max(0.0, (float)$d['qty'] - $allocated);

                // Sertakan item jika masih ada sisa stok yang bisa dipecah
                if ($remaining > 0.0001) {
                    $d['parent_id_faktur'] = $pid;
                    $d['parent_no_faktur'] = $pf['no_faktur'];
                    $d['remaining_qty']     = $remaining;
                    $pool_items[$d['id']]  = $d;
                    $total_pool_qty += $remaining;
                }
            }
        }

        if ($total_pool_qty <= 0) {
            $this->session->set_flashdata('warning', 'Semua barang pada Faktur Z yang dipilih sudah sepenuhnya dipecah.');
            redirect('sales_order/pecah_faktur');
            return;
        }

        // Kumpulkan data kios induk dari Faktur Z yang dipilih
        $kios_induk_map = [];
        $kios_names = [];
        foreach ($parent_fakturs as $pf) {
            $kd = $pf['kd_customer'];
            $c_db = $this->db->get_where('tb_customer', ['kd_customer' => $kd])->row_array();
            $toko_name = !empty($c_db['nama_kios']) ? $c_db['nama_kios'] : $pf['customer_name'];
            $kios_induk_map[$kd] = $toko_name;
            $kios_names[$toko_name] = true;
        }

        // Ambil customer acak yang terikat pada kios-kios induk ini
        $customers_acak = [];
        foreach ($kios_induk_map as $kd => $toko) {
            $res = $this->M_SalesOrder->get_customers_acak_by_kios($toko, $kd);
            foreach ($res as $ca) {
                $customers_acak[$ca['kd_customer']] = $ca;
            }
        }

        if (!empty($customers_acak)) {
            $customers = array_values($customers_acak);
            $is_customer_acak = true;
        } else {
            $customers = $this->M_SalesOrder->get_customers();
            $is_customer_acak = false;
        }

        // Cek apakah mode auto-split aktif (misal dari tombol Pecahkan Semua Kios)
        $should_auto_prefill = $this->input->post('auto_split') || $this->input->get('auto_split') || $this->input->post('max_nominal') || $this->input->get('max_nominal');
        $pre_allocated_splits = [];
        $is_auto_prefilled = false;
        $max_plafon = (float)($this->input->post('max_nominal') ?: $this->input->get('max_nominal') ?: 25000000);
        if ($max_plafon <= 0) $max_plafon = 25000000;

        if ($should_auto_prefill) {
            $contact_idx = 0;
            // Generate bucket per Faktur Z induk secara TERISOLASI
            foreach ($parent_fakturs as $pid => $pf) {
                // Kumpulkan item milik faktur induk ini
                $pf_items = [];
                foreach ($pool_items as $d_id => $it) {
                    if ((int)$it['parent_id_faktur'] === (int)$pid) {
                        $pf_items[$d_id] = $it;
                    }
                }
                if (empty($pf_items)) continue;

                $current_bucket = [
                    'items'            => [],
                    'total_nominal'    => 0.0,
                    'total_qty'        => 0.0,
                    'parent_no_faktur' => $pf['no_faktur']
                ];

                foreach ($pf_items as $d_id => $it) {
                    $qty_remaining = (float)$it['remaining_qty'];
                    $hrg_satuan_pecah = round((float)$it['hrg_satuan'] * 0.8, 2);
                    $disc = (float)($it['disc'] ?? 0);
                    $effective_unit_price = round($hrg_satuan_pecah * (1 - ($disc / 100)), 2);

                    while ($qty_remaining > 0.0001) {
                        $remaining_budget = $max_plafon - $current_bucket['total_nominal'];
                        if ($remaining_budget <= 0.01 && !empty($current_bucket['items'])) {
                            $pre_allocated_splits[] = $current_bucket;
                            $current_bucket = [
                                'items'            => [],
                                'total_nominal'    => 0.0,
                                'total_qty'        => 0.0,
                                'parent_no_faktur' => $pf['no_faktur']
                            ];
                            $remaining_budget = $max_plafon;
                        }

                        $fit_qty = ($effective_unit_price <= 0.01) ? $qty_remaining : floor($remaining_budget / $effective_unit_price);

                        if ($fit_qty >= $qty_remaining) {
                            $alloc_qty = $qty_remaining;
                            $subtotal = round($alloc_qty * $effective_unit_price, 2);
                            $current_bucket['items'][$d_id] = [
                                'qty'         => $alloc_qty,
                                'hrg_satuan'  => $hrg_satuan_pecah,
                                'total_harga' => $subtotal
                            ];
                            $current_bucket['total_nominal'] += $subtotal;
                            $current_bucket['total_qty']     += $alloc_qty;
                            $qty_remaining = 0;
                        } elseif ($fit_qty > 0) {
                            $alloc_qty = $fit_qty;
                            $subtotal = round($alloc_qty * $effective_unit_price, 2);
                            $current_bucket['items'][$d_id] = [
                                'qty'         => $alloc_qty,
                                'hrg_satuan'  => $hrg_satuan_pecah,
                                'total_harga' => $subtotal
                            ];
                            $current_bucket['total_nominal'] += $subtotal;
                            $current_bucket['total_qty']     += $alloc_qty;
                            $qty_remaining -= $alloc_qty;

                            $pre_allocated_splits[] = $current_bucket;
                            $current_bucket = [
                                'items'            => [],
                                'total_nominal'    => 0.0,
                                'total_qty'        => 0.0,
                                'parent_no_faktur' => $pf['no_faktur']
                            ];
                        } else {
                            if (!empty($current_bucket['items'])) {
                                $pre_allocated_splits[] = $current_bucket;
                                $current_bucket = [
                                    'items'            => [],
                                    'total_nominal'    => 0.0,
                                    'total_qty'        => 0.0,
                                    'parent_no_faktur' => $pf['no_faktur']
                                ];
                            } else {
                                $alloc_qty = min(1.0, $qty_remaining);
                                $subtotal = round($alloc_qty * $effective_unit_price, 2);
                                $current_bucket['items'][$d_id] = [
                                    'qty'         => $alloc_qty,
                                    'hrg_satuan'  => $hrg_satuan_pecah,
                                    'total_harga' => $subtotal
                                ];
                                $current_bucket['total_nominal'] += $subtotal;
                                $current_bucket['total_qty']     += $alloc_qty;
                                $qty_remaining -= $alloc_qty;

                                $pre_allocated_splits[] = $current_bucket;
                                $current_bucket = [
                                    'items'            => [],
                                    'total_nominal'    => 0.0,
                                    'total_qty'        => 0.0,
                                    'parent_no_faktur' => $pf['no_faktur']
                                ];
                            }
                        }
                    }
                }

                if (!empty($current_bucket['items'])) {
                    $pre_allocated_splits[] = $current_bucket;
                }
            }

            if (!empty($pre_allocated_splits)) {
                $is_auto_prefilled = true;
                $jumlah_pecah = count($pre_allocated_splits);
                // Prioritaskan kontak person yang belum mencapai limit 250 juta untuk rotasi auto-fill
                $available_customers = array_values(array_filter($customers, function($c) {
                    return empty($c['is_limit_reached']) && (float)($c['total_nominal_pecah'] ?? 0) < M_SalesOrder::LIMIT_KONTAK_PERSON_FAKTUR_H;
                }));
                if (empty($available_customers)) {
                    $available_customers = $customers;
                }

                foreach ($pre_allocated_splits as $b_idx => &$bkt) {
                    if (!empty($available_customers)) {
                        $cust_obj = $available_customers[$contact_idx % count($available_customers)];
                        $contact_idx++;
                        $bkt['kd_customer'] = $cust_obj['kd_customer'];
                    }
                }
                unset($bkt);
            }
        }

        $kd_kios = $this->input->post('kd_kios') ?: $this->input->get('kd_kios');
        $back_url = !empty($kd_kios) ? base_url('sales_order/pecah_faktur_kios/' . $kd_kios) : base_url('sales_order/pecah_faktur');

        $data['page_title']           = 'KARISMA - Pecah Faktur Z Sekaligus (' . count($parent_fakturs) . ' Faktur)';
        $data['parent_fakturs']       = $parent_fakturs;
        $data['pool_items']           = $pool_items;
        $data['total_pool_qty']       = $total_pool_qty;
        $data['jumlah_pecah']         = $jumlah_pecah;
        $data['customers']            = $customers;
        $data['is_customer_acak']     = $is_customer_acak;
        $data['kios_induk_names']     = implode(', ', array_keys($kios_names));
        $data['back_url']             = $back_url;
        $data['pre_allocated_splits'] = $pre_allocated_splits;
        $data['is_auto_prefilled']     = $is_auto_prefilled;
        $data['max_plafon']           = $max_plafon;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_split_batch_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan_split_faktur_batch()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $this->_ensureFakturPaymentInfoColumns();
        $this->M_SalesOrder->ensure_faktur_z_pecah_tables();

        $post = $this->input->post(null, true);
        $parent_ids = $post['parent_ids'] ?? [];
        $splits     = $post['splits'] ?? [];

        if (empty($parent_ids) || empty($splits)) {
            $this->session->set_flashdata('error', 'Data pemecahan faktur tidak lengkap.');
            redirect('sales_order/pecah_faktur');
            return;
        }

        // Ambil data parent faktur
        $parent_fakturs = [];
        foreach ($parent_ids as $pid) {
            $f = $this->M_SalesOrder->get_faktur($pid);
            if ($f && empty($f['parent_id_faktur'])) {
                $parent_fakturs[$pid] = $f;
            }
        }

        if (empty($parent_fakturs)) {
            $this->session->set_flashdata('error', 'Faktur Z sumber tidak valid.');
            redirect('sales_order/pecah_faktur');
            return;
        }

        // Ambil data item detail
        $parent_details = [];
        $allocated_map = [];

        $child_allocations = $this->db->select('parent_id_faktur, id_so_detail, kd_barang, no_lot, expired_date, SUM(qty) as qty_allocated')
            ->from('tbso_faktur_z_pecah_detail')
            ->where_in('parent_id_faktur', array_keys($parent_fakturs))
            ->group_by('parent_id_faktur, id_so_detail, kd_barang, no_lot, expired_date')
            ->get()
            ->result_array();

        foreach ($child_allocations as $ca) {
            $pid = (int)$ca['parent_id_faktur'];
            $key = implode('|', [
                $ca['id_so_detail'],
                $ca['kd_barang'],
                (string)$ca['no_lot'],
                $ca['expired_date']
            ]);
            $allocated_map[$pid][$key] = (float)$ca['qty_allocated'];
        }

        foreach ($parent_fakturs as $pid => $pf) {
            $details = $this->M_SalesOrder->get_faktur_detail($pid);
            foreach ($details as $d) {
                $key = implode('|', [
                    $d['id_so_detail'],
                    $d['kd_barang'],
                    (string)$d['no_lot'],
                    $d['expired_date']
                ]);
                $allocated = $allocated_map[$pid][$key] ?? 0.0;
                $remaining = max(0.0, (float)$d['qty'] - $allocated);
                $d['parent_no_faktur'] = $pf['no_faktur'];
                $d['remaining_qty']    = $remaining;
                $parent_details[$d['id']] = $d;
            }
        }

        $total_requested_per_item = [];
        $batch_allocated_nominal_per_cust = [];
        $validation_errors = [];

        // Kumpulkan customer acak yang valid untuk parent faktur terpilih
        $allowed_acak_map = [];
        $allowed_kios_names = [];
        foreach ($parent_fakturs as $pf) {
            $kd = $pf['kd_customer'];
            $c_db = $this->db->get_where('tb_customer', ['kd_customer' => $kd])->row_array();
            $toko_name = !empty($c_db['nama_kios']) ? $c_db['nama_kios'] : $pf['customer_name'];
            $allowed_kios_names[$toko_name] = true;
            $res = $this->M_SalesOrder->get_customers_acak_by_kios($toko_name, $kd);
            foreach ($res as $ca) {
                $allowed_acak_map[$ca['kd_customer']] = $ca;
            }
        }

        foreach ($splits as $s_idx => $s) {
            $kd_cust = trim((string)($s['kd_customer'] ?? ''));
            $items = $s['items'] ?? [];

            $has_item = false;
            $slot_nominal = 0.0;
            foreach ($items as $detail_id => $it) {
                $qty = (float)($it['qty'] ?? 0);
                if ($qty > 0.0001) {
                    $has_item = true;
                    if (!isset($total_requested_per_item[$detail_id])) {
                        $total_requested_per_item[$detail_id] = 0.0;
                    }
                    $total_requested_per_item[$detail_id] += $qty;

                    if (isset($parent_details[$detail_id])) {
                        $pd = $parent_details[$detail_id];
                        $hrg_satuan_pecah = round((float)$pd['hrg_satuan'] * 0.8, 2);
                        $disc = (float)($pd['disc'] ?? 0);
                        $effective_unit_price = round($hrg_satuan_pecah * (1 - ($disc / 100)), 2);
                        $slot_nominal += round($qty * $effective_unit_price, 2);
                    }
                }
            }

            if ($has_item) {
                if (empty($kd_cust)) {
                    $validation_errors[] = "Slot Pecahan #" . ($s_idx + 1) . " memiliki alokasi barang namun belum memilih Customer.";
                } elseif (!empty($allowed_acak_map) && !isset($allowed_acak_map[$kd_cust])) {
                    $validation_errors[] = "Slot Pecahan #" . ($s_idx + 1) . ": Customer Penerima (" . htmlspecialchars($kd_cust) . ") bukan kontak person milik kios (" . implode(', ', array_keys($allowed_kios_names)) . "). Pastikan customer tidak tertukar!";
                } else {
                    if (!isset($batch_allocated_nominal_per_cust[$kd_cust])) {
                        $batch_allocated_nominal_per_cust[$kd_cust] = 0.0;
                    }
                    $batch_allocated_nominal_per_cust[$kd_cust] += $slot_nominal;
                }
            }
        }

        // Cek validasi batas maksimal limit 250 Juta per kontak person
        foreach ($batch_allocated_nominal_per_cust as $kd_c => $nom_added) {
            $limit_check = $this->M_SalesOrder->check_kontak_person_limit($kd_c, $nom_added);
            if (!$limit_check['is_allowed']) {
                $validation_errors[] = $limit_check['message'];
            }
        }

        foreach ($total_requested_per_item as $detail_id => $req_qty) {
            if (isset($parent_details[$detail_id])) {
                $max_available = (float)$parent_details[$detail_id]['remaining_qty'];
                if ($req_qty > ($max_available + 0.001)) {
                    $validation_errors[] = "Total alokasi untuk <b>" . htmlspecialchars($parent_details[$detail_id]['nama_barang']) . "</b> (" . number_format($req_qty) . ") melebihi sisa stok yang tersedia (" . number_format($max_available) . ").";
                }
            }
        }

        if (!empty($validation_errors)) {
            $this->session->set_flashdata('error', 'Validasi gagal:<br>&bull; ' . implode('<br>&bull; ', $validation_errors));
            redirect('sales_order/split_faktur_batch?id_faktur=' . implode(',', array_keys($parent_fakturs)) . '&jumlah_pecah=' . count($splits));
            return;
        }

        $username = $this->_getUsername();
        $result = $this->M_SalesOrder->proses_split_faktur_batch($parent_fakturs, $parent_details, $splits, $username);

        if (!empty($result['success'])) {
            $this->session->set_flashdata('success', 'Berhasil membuat <b>' . $result['total_created'] . ' Faktur Pecahan (Kode H)</b> dari Faktur Z yang dipilih.');
            redirect('sales_order/pecah_faktur');
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses pemecahan massal. Pastikan minimal 1 pecahan memiliki alokasi barang dan customer.');
            redirect('sales_order/split_faktur_batch?id_faktur=' . implode(',', array_keys($parent_fakturs)) . '&jumlah_pecah=' . count($splits));
        }
    }

    public function detail_faktur_pecah($id_pecah)
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $faktur = $this->M_SalesOrder->get_faktur_pecah_h($id_pecah);
        if (!$faktur) show_404();

        $details = $this->M_SalesOrder->get_faktur_pecah_h_detail($faktur['id_pecah']);
        $parent_faktur = $this->M_SalesOrder->get_faktur($faktur['parent_id_faktur']);

        $data['page_title']    = 'KARISMA - Faktur Pecahan ' . $faktur['no_faktur'];
        $data['faktur']        = $faktur;
        $data['details']       = $details;
        $data['parent_faktur'] = $parent_faktur;
        $data['back_url']      = base_url('sales_order/pecah_faktur');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_pecah_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Membatalkan / menghapus 1 faktur pecahan (Kode H) dan mengembalikan kuantitas barang ke faktur induk.
     */
    public function hapus_faktur_pecah($id_pecah = null)
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        if (empty($id_pecah)) {
            redirect('sales_order/pecah_faktur');
            return;
        }

        $faktur_pecah = $this->M_SalesOrder->get_faktur_pecah_h($id_pecah);
        if (!$faktur_pecah) {
            $this->session->set_flashdata('error', 'Faktur pecahan tidak ditemukan.');
            redirect('sales_order/pecah_faktur');
            return;
        }

        $parent_id = (int)$faktur_pecah['parent_id_faktur'];
        $no_pecah  = $faktur_pecah['no_faktur'];

        $this->db->delete('tbso_faktur_z_pecah_detail', ['id_pecah' => $id_pecah]);
        $this->db->delete('tbso_faktur_z_pecah', ['id_pecah' => $id_pecah]);

        $remaining_child = $this->db->get_where('tbso_faktur_z_pecah', ['parent_id_faktur' => $parent_id])->num_rows();
        if ($remaining_child === 0) {
            $this->db->where('id_faktur', $parent_id)->update('tbso_faktur_penjualan', ['is_split_parent' => 0]);
        }

        $this->session->set_flashdata('success', "Faktur pecahan <strong>" . htmlspecialchars($no_pecah) . "</strong> berhasil dibatalkan/dihapus.");
        if (!empty($faktur_pecah['kd_customer'])) {
            redirect('sales_order/pecah_faktur_kios/' . $faktur_pecah['kd_customer']);
        } else {
            redirect('sales_order/pecah_faktur');
        }
    }

    /**
     * Mereset seluruh pemecahan pada Faktur Z induk sehingga kembali menjadi belum dipecah.
     */
    public function reset_pecah_faktur_induk($id_faktur = null)
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        if (empty($id_faktur)) {
            redirect('sales_order/pecah_faktur');
            return;
        }

        $faktur = $this->M_SalesOrder->get_faktur($id_faktur);
        if (!$faktur) {
            $this->session->set_flashdata('error', 'Faktur Z tidak ditemukan.');
            redirect('sales_order/pecah_faktur');
            return;
        }

        $children = $this->db->get_where('tbso_faktur_z_pecah', ['parent_id_faktur' => $id_faktur])->result_array();
        foreach ($children as $c) {
            $this->db->delete('tbso_faktur_z_pecah_detail', ['id_pecah' => $c['id_pecah']]);
            $this->db->delete('tbso_faktur_z_pecah', ['id_pecah' => $c['id_pecah']]);
        }

        $this->db->where('id_faktur', $id_faktur)->update('tbso_faktur_penjualan', ['is_split_parent' => 0]);

        $this->session->set_flashdata('success', "Seluruh pecahan untuk Faktur Z <strong>" . htmlspecialchars($faktur['no_faktur']) . "</strong> berhasil di-reset. Faktur Z kembali berstatus Belum Dipecah.");
        if (!empty($faktur['kd_customer'])) {
            redirect('sales_order/pecah_faktur_kios/' . $faktur['kd_customer']);
        } else {
            redirect('sales_order/pecah_faktur');
        }
    }

    /**
     * Export Faktur Z Pecahan (Kode H) ke format Excel (.xlsx)
     * Mengikuti format dan rumus persis sesuai template format export.xlsx
     */
    public function export_faktur_pecah()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        // Pastikan PhpSpreadsheet tersedia
        if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            if (file_exists(FCPATH . 'vendor/autoload.php')) {
                require_once FCPATH . 'vendor/autoload.php';
            } elseif (file_exists(APPPATH . 'libraries/PhpSpreadsheet.php')) {
                require_once APPPATH . 'libraries/PhpSpreadsheet.php';
            }
        }

        $kd_customer = trim((string)($this->input->get('kd_customer') ?? ''));
        $id_pecah    = $this->input->get('id_pecah');
        $id_faktur   = $this->input->get('id_faktur');
        $date1       = $this->input->get('date1');
        $date2       = $this->input->get('date2');

        // Query data rincian faktur pecahan
        $this->db->select('
            d.id AS id_detail,
            d.id_pecah,
            d.no_faktur AS no_faktur_h,
            d.parent_no_faktur AS no_faktur_z,
            d.parent_id_faktur,
            d.kd_barang,
            d.nama_barang,
            d.qty,
            d.hrg_satuan,
            d.pajak,
            d.disc,
            d.total_harga,
            p.tanggal_faktur,
            p.salesman,
            p.kd_customer,
            p.customer_name,
            p.catatan AS ket_faktur,
            ca.kd_customer AS ca_kd_customer,
            ca.nama_toko AS ca_nama_toko,
            ca.kontak_person AS ca_kontak_person,
            ca.alamat AS ca_alamat,
            ca.kota AS ca_kota,
            ca.nik AS ca_nik,
            ca.npwp AS ca_npwp,
            cust.nama_customer AS master_nama_customer,
            cust.alamat_kios AS master_alamat,
            cust.regional AS master_kota,
            b.kelompok_barang,
            b.kategori_barang,
            fd.hrg_satuan AS hrg_satuan_asli
        ');
        $this->db->from('tbso_faktur_z_pecah_detail d');
        $this->db->join('tbso_faktur_z_pecah p', 'p.id_pecah = d.id_pecah', 'inner');
        $this->db->join('tbso_faktur_penjualan fz', 'fz.id_faktur = p.parent_id_faktur', 'left');
        $this->db->join('tb_customer_acak ca', 'ca.kd_customer = p.kd_customer', 'left');
        $this->db->join('tb_customer cust', 'cust.kd_customer = fz.kd_customer', 'left');
        $this->db->join('tbpo_barang b', 'b.kode_barang = d.kd_barang', 'left');
        $this->db->join('tbso_faktur_detail fd', 'fd.id = d.id_faktur_detail_parent', 'left');
        $this->db->where('p.status !=', 'cancelled');

        if (!empty($kd_customer)) {
            $this->db->group_start();
            $this->db->where('fz.kd_customer', $kd_customer);
            $this->db->or_where('p.kd_customer', $kd_customer);
            $this->db->or_where('ca.kd_customer', $kd_customer);
            $this->db->or_where('ca.kd_customer_induk', $kd_customer);
            $this->db->group_end();
        }
        if (!empty($id_pecah)) {
            $this->db->where('p.id_pecah', $id_pecah);
        }
        if (!empty($id_faktur)) {
            $this->db->where('p.parent_id_faktur', $id_faktur);
        }
        if (!empty($date1)) {
            $this->db->where('p.tanggal_faktur >=', $date1);
        }
        if (!empty($date2)) {
            $this->db->where('p.tanggal_faktur <=', $date2);
        }

        $this->db->order_by('p.tanggal_faktur', 'ASC');
        $this->db->order_by('p.id_pecah', 'ASC');
        $this->db->order_by('d.id', 'ASC');

        $rows = $this->db->get()->result_array();

        if (empty($rows)) {
            $this->session->set_flashdata('error', 'Tidak ada data Faktur Pecahan (Kode H) yang ditemukan untuk di-export.');
            $back_url = !empty($kd_customer) 
                ? base_url('sales_order/pecah_faktur_kios/' . $kd_customer) 
                : ($this->input->server('HTTP_REFERER') ?: base_url('sales_order/pecah_faktur'));
            redirect($back_url);
            return;
        }

        // Buat Spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Penjualan Harian');

        // Font default
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

        // Header Dokumen Baris 1 - 4
        $sheet->setCellValue('A1', 'Laporan Penjualan Harian');
        $sheet->setCellValue('A2', 'PT. Karisma Indoagro Universal');

        $minDate = $rows[0]['tanggal_faktur'];
        $maxDate = end($rows)['tanggal_faktur'];
        $tglAwal = !empty($date1) ? date('d-M-Y', strtotime($date1)) : (!empty($minDate) ? date('d-M-Y', strtotime($minDate)) : date('01-M-Y'));
        $tglAkhir = !empty($date2) ? date('d-M-Y', strtotime($date2)) : (!empty($maxDate) ? date('d-M-Y', strtotime($maxDate)) : date('t-M-Y'));
        $periodeText = "Periode {$tglAwal} - {$tglAkhir}";
        $sheet->setCellValue('A3', $periodeText);

        $sheet->setCellValue('A4', '( Untuk Nota T tidak lapor pajak, ganti nama acak langsung di Nota I, penjualan diinfo ) Acc bu Diana 06/11/2022');

        // Styling Baris Judul
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(9);

        // Header Kolom di Baris 7 (A7 - AB7)
        $headers = [
            'A' => 'No.',
            'B' => 'Tanggal',
            'C' => 'F.Acak',
            'D' => 'Faktur Z',
            'E' => 'Sales',
            'F' => 'Kode',
            'G' => 'Nama Customer',
            'H' => 'Kontak Person',
            'I' => 'Alamat',
            'J' => 'Kota',
            'K' => 'NIK',
            'L' => 'NPWP',
            'M' => 'Jenis',
            'N' => 'Kode',
            'O' => 'Nama Barang',
            'P' => 'Qty',
            'Q' => 'Harga Jual',
            'R' => 'Harga Inc PPN',
            'S' => 'Harga Normal',
            'T' => 'Disc',
            'U' => 'Jumlah Harga Jual',
            'V' => 'DPP Nilai Lain',
            'W' => 'Pajak %',
            'X' => 'Pajak ( Rp )',
            'Y' => 'Penj Icn PPN',
            'Z' => 'Per Faktur',
            'AA' => '',
            'AB' => 'keterangan'
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . '7', $title);
        }

        // Style Header Baris 7
        $headerRange = 'A7:AB7';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2']
            ]
        ]);
        $sheet->getRowDimension(7)->setRowHeight(24);

        // Grouping items per Faktur H untuk perhitungan grand total Per Faktur (Kolom Z)
        $fakturGrouped = [];
        foreach ($rows as $item) {
            $fakturGrouped[$item['no_faktur_h']][] = $item;
        }

        $rowNum = 8;
        $no = 1;

        foreach ($fakturGrouped as $noFakturH => $items) {
            // Hitung total include PPN untuk faktur pecahan ini
            $fakturTotalNetto = 0;
            foreach ($items as $it) {
                $q = (float)$it['qty'];
                $h = (float)$it['hrg_satuan'];
                $d = (float)$it['disc'];
                $fakturTotalNetto += ($q * $h) - ($q * $h * $d);
            }

            foreach ($items as $item) {
                $r = $rowNum;

                $tglFaktur = !empty($item['tanggal_faktur']) ? date('d-M-y', strtotime($item['tanggal_faktur'])) : '';
                $sales     = !empty($item['salesman']) ? $item['salesman'] : 'Sales';
                $kdCust    = !empty($item['ca_kd_customer']) ? $item['ca_kd_customer'] : $item['kd_customer'];
                $namaCust  = !empty($item['ca_nama_toko']) ? $item['ca_nama_toko'] : $item['customer_name'];
                $kontak    = !empty($item['ca_kontak_person']) ? $item['ca_kontak_person'] : '-';
                $alamat    = !empty($item['ca_alamat']) ? $item['ca_alamat'] : (!empty($item['master_alamat']) ? $item['master_alamat'] : '-');
                $kota      = !empty($item['ca_kota']) ? $item['ca_kota'] : (!empty($item['master_kota']) ? $item['master_kota'] : 'Jember');
                $nik       = !empty($item['ca_nik']) ? (string)$item['ca_nik'] : '';
                $npwp      = !empty($item['ca_npwp']) ? (string)$item['ca_npwp'] : '000000000000000';
                $jenis     = !empty($item['kelompok_barang']) ? $item['kelompok_barang'] : (!empty($item['kategori_barang']) ? $item['kategori_barang'] : 'Umum');

                $qty       = (float)$item['qty'];
                $hrgNormal = ((float)$item['hrg_satuan_asli'] > 0) ? (float)$item['hrg_satuan_asli'] : round((float)$item['hrg_satuan'] / 0.8, 2);
                $disc      = (float)$item['disc'];
                $pajakPct  = (float)$item['pajak'];

                // Isi Cell Data
                $sheet->setCellValue('A' . $r, $no++);
                $sheet->setCellValue('B' . $r, $tglFaktur);
                $sheet->setCellValue('C' . $r, $item['no_faktur_h']);
                $sheet->setCellValue('D' . $r, $item['no_faktur_z']);
                $sheet->setCellValue('E' . $r, $sales);
                $sheet->setCellValue('F' . $r, $kdCust);
                $sheet->setCellValue('G' . $r, $namaCust);
                $sheet->setCellValue('H' . $r, $kontak);
                $sheet->setCellValue('I' . $r, $alamat);
                $sheet->setCellValue('J' . $r, $kota);
                $sheet->setCellValueExplicit('K' . $r, $nik, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('L' . $r, $npwp, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('M' . $r, $jenis);
                $sheet->setCellValue('N' . $r, $item['kd_barang']);
                $sheet->setCellValue('O' . $r, $item['nama_barang']);
                $sheet->setCellValue('P' . $r, $qty);

                // Rumus Formula Persis Template format export.xlsx
                $sheet->setCellValue('Q' . $r, "=IF(W{$r}=0,R{$r},R{$r}/1.11)");
                $sheet->setCellValue('R' . $r, "=+S{$r}*0.8");
                $sheet->setCellValue('S' . $r, $hrgNormal);
                $sheet->setCellValue('T' . $r, $disc);
                $sheet->setCellValue('U' . $r, "=(P{$r}*Q{$r})-(P{$r}*Q{$r}*T{$r})");
                $sheet->setCellValue('V' . $r, "=+U{$r}*0.916666666666667");
                $sheet->setCellValue('W' . $r, $pajakPct);
                $sheet->setCellValue('X' . $r, "=+V{$r}*W{$r}/100");
                $sheet->setCellValue('Y' . $r, "=(P{$r}*R{$r})-(P{$r}*R{$r}*T{$r})");
                $sheet->setCellValue('Z' . $r, $fakturTotalNetto);
                $sheet->setCellValue('AA' . $r, "=Y{$r}/0.8");
                $sheet->setCellValue('AB' . $r, $item['ket_faktur'] ?? '');

                // Alignment Data
                $sheet->getStyle('A' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('K' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('L' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('N' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('W' . $r)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                // Number Formats
                $sheet->getStyle('P' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('Q' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('R' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('S' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('T' . $r)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle('U' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('V' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('W' . $r)->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('X' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('Y' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('Z' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('AA' . $r)->getNumberFormat()->setFormatCode('#,##0.00');

                // Border per baris data
                $sheet->getStyle("A{$r}:AB{$r}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $rowNum++;
            }
        }

        // Set Auto Column Width untuk seluruh kolom A s/d AB
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);

        // Header Download Browser
        $slug = !empty($kd_customer) ? '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $kd_customer) : '';
        $filename = 'Export_Faktur_Pecahan' . $slug . '_' . date('Ymd_His') . '.xlsx';

        // Bersihkan output buffer jika ada
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function customer_acak()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $this->M_SalesOrder->ensure_customer_acak_table();

        $selected_toko = trim((string)($this->input->get('nama_toko') ?? ''));
        $search = trim((string)($this->input->get('q') ?? ''));

        $filter = [];
        if (!empty($selected_toko)) {
            $filter['nama_toko'] = $selected_toko;
        }
        if (!empty($search)) {
            $filter['search'] = $search;
        }

        $customers_acak   = $this->M_SalesOrder->get_all_customers_acak($filter);
        $unique_tokos     = $this->M_SalesOrder->get_unique_tokos_customer_acak();

        $data['page_title']       = 'Master Customer Acak (Pecah Faktur)';
        $data['customers_acak']   = $customers_acak;
        $data['unique_tokos']     = $unique_tokos;
        $data['selected_toko']    = $selected_toko;
        $data['search']           = $search;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/customer_acak_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Simpan penambahan kontak Customer Acak baru
     */
    public function simpan_customer_acak()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $this->M_SalesOrder->ensure_customer_acak_table();

        $kd_customer       = strtoupper(trim((string)$this->input->post('kd_customer')));
        $nama_toko         = trim((string)$this->input->post('nama_toko'));
        $kd_customer_induk = strtoupper(trim((string)$this->input->post('kd_customer_induk')));
        $kontak_person     = trim((string)$this->input->post('kontak_person'));
        $alamat            = trim((string)$this->input->post('alamat'));
        $kota              = trim((string)$this->input->post('kota'));
        $nik               = trim((string)$this->input->post('nik'));
        $npwp              = trim((string)$this->input->post('npwp'));

        if (empty($kd_customer) || empty($kontak_person) || empty($nama_toko)) {
            $this->session->set_flashdata('error', 'Kode Customer Acak, Nama Kios / Toko Induk, dan Kontak Person wajib diisi.');
            redirect('sales_order/customer_acak');
            return;
        }

        if (empty($npwp)) {
            $npwp = '000000000000000';
        }

        // Cek duplikasi kode customer acak
        $existing = $this->db->get_where('tb_customer_acak', ['kd_customer' => $kd_customer])->row_array();
        if ($existing) {
            $this->session->set_flashdata('error', 'Kode Customer Acak <b>' . htmlspecialchars($kd_customer) . '</b> sudah digunakan oleh ' . htmlspecialchars($existing['kontak_person']) . ' (' . htmlspecialchars($existing['nama_toko']) . '). Silakan gunakan kode acak lain.');
            redirect('sales_order/customer_acak');
            return;
        }

        $insert_data = [
            'kd_customer'       => $kd_customer,
            'nama_toko'         => $nama_toko,
            'kd_customer_induk' => !empty($kd_customer_induk) ? $kd_customer_induk : null,
            'kontak_person'     => $kontak_person,
            'alamat'            => $alamat,
            'kota'              => $kota,
            'nik'               => $nik,
            'npwp'              => $npwp,
            'created_at'        => date('Y-m-d H:i:s')
        ];

        $insert = $this->db->insert('tb_customer_acak', $insert_data);
        if ($insert) {
            $this->session->set_flashdata('success', 'Kontak Customer Acak baru <b>' . htmlspecialchars($kontak_person) . ' (' . htmlspecialchars($kd_customer) . ')</b> berhasil ditambahkan untuk toko ' . htmlspecialchars($nama_toko) . '.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan data customer acak.');
        }

        redirect('sales_order/customer_acak?nama_toko=' . rawurlencode($nama_toko));
    }

    /**
     * Update data kontak Customer Acak
     */
    public function update_customer_acak()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $this->M_SalesOrder->ensure_customer_acak_table();

        $id = (int)$this->input->post('id');
        if (!$id) {
            $this->session->set_flashdata('error', 'ID Customer Acak tidak valid.');
            redirect('sales_order/customer_acak');
            return;
        }

        $kd_customer       = strtoupper(trim((string)$this->input->post('kd_customer')));
        $nama_toko         = trim((string)$this->input->post('nama_toko'));
        $kd_customer_induk = strtoupper(trim((string)$this->input->post('kd_customer_induk')));
        $kontak_person     = trim((string)$this->input->post('kontak_person'));
        $alamat            = trim((string)$this->input->post('alamat'));
        $kota              = trim((string)$this->input->post('kota'));
        $nik               = trim((string)$this->input->post('nik'));
        $npwp              = trim((string)$this->input->post('npwp'));

        if (empty($kd_customer) || empty($kontak_person) || empty($nama_toko)) {
            $this->session->set_flashdata('error', 'Kode Customer Acak, Nama Kios / Toko Induk, dan Kontak Person wajib diisi.');
            redirect('sales_order/customer_acak');
            return;
        }

        // Cek duplikasi kode dengan ID lain
        $duplicate = $this->db->where('kd_customer', $kd_customer)->where('id !=', $id)->get('tb_customer_acak')->row_array();
        if ($duplicate) {
            $this->session->set_flashdata('error', 'Kode Customer Acak <b>' . htmlspecialchars($kd_customer) . '</b> sudah digunakan oleh kontak lain.');
            redirect('sales_order/customer_acak');
            return;
        }

        $update_data = [
            'kd_customer'       => $kd_customer,
            'nama_toko'         => $nama_toko,
            'kd_customer_induk' => !empty($kd_customer_induk) ? $kd_customer_induk : null,
            'kontak_person'     => $kontak_person,
            'alamat'            => $alamat,
            'kota'              => $kota,
            'nik'               => $nik,
            'npwp'              => !empty($npwp) ? $npwp : '000000000000000',
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $id)->update('tb_customer_acak', $update_data);
        $this->session->set_flashdata('success', 'Data Customer Acak <b>' . htmlspecialchars($kontak_person) . ' (' . htmlspecialchars($kd_customer) . ')</b> berhasil diperbarui.');
        redirect('sales_order/customer_acak?nama_toko=' . rawurlencode($nama_toko));
    }

    /**
     * Hapus data kontak Customer Acak
     */
    public function hapus_customer_acak($id = null)
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $this->M_SalesOrder->ensure_customer_acak_table();

        $id = (int)$id;
        $row = $this->db->get_where('tb_customer_acak', ['id' => $id])->row_array();
        if ($row) {
            $this->db->delete('tb_customer_acak', ['id' => $id]);
            $this->session->set_flashdata('success', 'Kontak Customer Acak <b>' . htmlspecialchars($row['kontak_person']) . ' (' . htmlspecialchars($row['kd_customer']) . ')</b> berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Data Customer Acak tidak ditemukan.');
        }
        redirect('sales_order/customer_acak');
    }

    /**
     * AJAX Helper untuk mengusulkan kode acak berikutnya (misal: AGRO04AC)
     */
    public function get_next_kode_acak()
    {
        if (!$this->session->userdata('logged_in')) {
            echo json_encode(['status' => false, 'message' => 'Unauthorized']);
            return;
        }

        $kd_induk  = strtoupper(trim((string)$this->input->get('kd_induk')));
        $nama_toko = trim((string)$this->input->get('nama_toko'));

        // Cari prefix dari kode induk atau nama toko
        $prefix = '';
        if (!empty($kd_induk)) {
            // Ambil huruf depan dari kd_induk (misal AGRO dari AGRO76)
            preg_match('/^[A-Z]+/', $kd_induk, $matches);
            $prefix = !empty($matches[0]) ? substr($matches[0], 0, 4) : substr($kd_induk, 0, 4);
        } elseif (!empty($nama_toko)) {
            $clean = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($nama_toko));
            $prefix = substr($clean, 0, 4);
        }

        if (empty($prefix)) {
            $prefix = 'CUST';
        }
        $prefix = strtoupper(str_pad($prefix, 4, 'X'));

        // Cari kode acak tertinggi yang berawalan prefix dan berakhiran AC
        $existing_codes = $this->db->select('kd_customer')
            ->from('tb_customer_acak')
            ->like('kd_customer', $prefix, 'after')
            ->get()
            ->result_array();

        $max_num = 0;
        foreach ($existing_codes as $ec) {
            $code = strtoupper(trim($ec['kd_customer']));
            // Pola: PREFIX + 2 digit angka + AC, contoh AGRO01AC
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{2})AC$/i', $code, $m)) {
                $num = (int)$m[1];
                if ($num > $max_num) {
                    $max_num = $num;
                }
            }
        }

        $next_num = $max_num + 1;
        $suggested_code = sprintf('%s%02dAC', $prefix, $next_num);

        echo json_encode([
            'status' => true,
            'suggested_code' => $suggested_code
        ]);
    }

    /**
     * AJAX Search Kios dari tb_customer untuk Select2
     */
    public function ajax_search_kios()
    {
        if (!$this->session->userdata('logged_in')) {
            echo json_encode(['results' => []]);
            return;
        }

        $q = trim((string)$this->input->get('q'));
        $this->db->select('kd_customer, nama_kios, nama_customer, regional, alamat_kios');
        $this->db->from('tb_customer');
        if (!empty($q)) {
            $this->db->group_start();
            $this->db->like('nama_kios', $q);
            $this->db->or_like('kd_customer', $q);
            $this->db->or_like('nama_customer', $q);
            $this->db->group_end();
        }
        $this->db->order_by('nama_kios', 'ASC');
        $this->db->limit(30);
        $rows = $this->db->get()->result_array();

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'id'          => $r['nama_kios'],
                'text'        => $r['nama_kios'] . ' (' . $r['kd_customer'] . ') - ' . ($r['regional'] ?: '-'),
                'nama_kios'   => $r['nama_kios'],
                'kd_customer' => $r['kd_customer'],
                'kota'        => $r['regional'] ?? '',
                'alamat'      => $r['alamat_kios'] ?? ''
            ];
        }

        echo json_encode(['results' => $results]);
    }

    public function sync_customer_acak()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }

        $file_path = FCPATH . 'cust acak.xlsx';
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File cust acak.xlsx tidak ditemukan di root aplikasi.');
            redirect('sales_order/customer_acak');
            return;
        }

        try {
            require_once FCPATH . 'vendor/autoload.php';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($file_path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $customers_db = $this->M_SalesOrder->get_customers();

            $total_processed = 0;
            $now = date('Y-m-d H:i:s');

            $this->db->trans_start();
            foreach ($rows as $idx => $r) {
                if ($idx == 0) continue;

                $kd_customer = trim((string)($r[1] ?? ''));
                $nama_toko   = trim((string)($r[2] ?? ''));
                $kontak      = trim((string)($r[3] ?? ''));
                $alamat      = trim((string)($r[4] ?? ''));
                $kota        = trim((string)($r[5] ?? ''));
                $nik         = trim((string)($r[6] ?? ''));
                $npwp        = trim((string)($r[7] ?? ''));

                if (empty($kd_customer) || empty($nama_toko)) continue;

                $kd_induk = null;
                foreach ($customers_db as $c) {
                    if (!empty($c['nama_kios']) && strcasecmp(trim($c['nama_kios']), $nama_toko) === 0) {
                        $kd_induk = $c['kd_customer'];
                        break;
                    }
                }
                if (!$kd_induk) {
                    foreach ($customers_db as $c) {
                        if (!empty($c['nama_customer']) && strcasecmp(trim($c['nama_customer']), $nama_toko) === 0) {
                            $kd_induk = $c['kd_customer'];
                            break;
                        }
                    }
                }

                $existing = $this->db->get_where('tb_customer_acak', ['kd_customer' => $kd_customer])->row_array();
                if ($existing) {
                    $this->db->where('kd_customer', $kd_customer)->update('tb_customer_acak', [
                        'nama_toko'         => $nama_toko,
                        'kd_customer_induk' => $kd_induk ?: $existing['kd_customer_induk'],
                        'kontak_person'     => $kontak,
                        'alamat'            => $alamat,
                        'kota'              => $kota,
                        'nik'               => $nik,
                        'npwp'              => $npwp,
                        'updated_at'        => $now
                    ]);
                } else {
                    $this->db->insert('tb_customer_acak', [
                        'kd_customer'       => $kd_customer,
                        'nama_toko'         => $nama_toko,
                        'kd_customer_induk' => $kd_induk,
                        'kontak_person'     => $kontak,
                        'alamat'            => $alamat,
                        'kota'              => $kota,
                        'nik'               => $nik,
                        'npwp'              => $npwp,
                        'created_at'        => $now
                    ]);
                }
                $total_processed++;
            }
            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata('success', 'Berhasil menyinkronkan <b>' . $total_processed . ' kontak customer acak</b> dari file Excel.');
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan data customer acak.');
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        redirect('sales_order/customer_acak');
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
        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }
        $value = trim((string)$value);
        if ($value === '') return 0.0;

        $value = preg_replace('/[^\d,.\-]/', '', $value);
        if ($value === '' || $value === '-') return 0.0;

        // Jika ada titik DAN koma (misal: "1.250.000,50" atau "1,250,000.50")
        if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                // Format ID: 1.250.000,50 -> 1250000.50
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                // Format EN: 1,250,000.50 -> 1250000.50
                $value = str_replace(',', '', $value);
            }
        } elseif (strpos($value, '.') !== false) {
            // Hanya ada titik (misal: "95.000", "1.250.000", atau "95.5")
            if (substr_count($value, '.') > 1) {
                // Ribuan multiple: "1.250.000" -> 1250000
                $value = str_replace('.', '', $value);
            } else {
                // 1 titik: cek apakah ribuan ("95.000") atau desimal ("95.5")
                $parts = explode('.', $value);
                if (isset($parts[1]) && strlen($parts[1]) === 3) {
                    // Tepat 3 digit di belakang titik -> ribuan (95.000, 5.000, 100.500)
                    $value = $parts[0] . $parts[1];
                } else {
                    // Desimal pecahan (misal 95.5, 95.25)
                }
            }
        } elseif (strpos($value, ',') !== false) {
            // Hanya ada koma (misal: "95,5" atau "1,250,000" atau "95,000")
            if (substr_count($value, ',') > 1) {
                $value = str_replace(',', '', $value);
            } else {
                $parts = explode(',', $value);
                if (isset($parts[1]) && strlen($parts[1]) === 3 && (int)$parts[1] === 0) {
                    // Format EN ribuan: 95,000 -> 95000
                    $value = $parts[0] . $parts[1];
                } else {
                    // Desimal format ID: 95,5 -> 95.5
                    $value = str_replace(',', '.', $value);
                }
            }
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
            $hrg_pk      = $this->_parse_number_input($post['hrg_pokok'][$i]   ?? 0);
            $is_ubah_harga = $hrg > 0 && $hrg_pk > 0 && abs($hrg - $hrg_pk) > 0.001;
            $harga_approval_by = strtolower(trim((string)($post['harga_approval_by'][$i] ?? '')));
            if (!$is_ubah_harga || !in_array($harga_approval_by, $allowed_harga_approval, true)) {
                $harga_approval_by = '';
            }
            $qty_box     = $this->_parse_number_input($post['qty_box'][$i]      ?? 0);
            $qty_satuan  = $this->_parse_number_input($post['qty_satuan'][$i]   ?? 0);
            $isi_per_box = max(1, (int)($post['isi_per_box'][$i] ?? 1));
            $pajak       = $this->_parse_number_input($post['pajak'][$i]        ?? 0);
            $disc        = $this->_parse_number_input($post['disc'][$i]         ?? 0);
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
                'berat_gram'           => $this->_parse_number_input($post['berat_gram'][$i]  ?? 0),
                'kubikasi_m3'          => $this->_parse_number_input($post['kubikasi_m3'][$i] ?? 0),
                'create_by'            => $this->_getUsername(),
            ];
        }
        return $details;
    }

    private function _validate_harga_approval(array $details)
    {
        return [];
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

            $hrg         = $this->_parse_number_input($post['hrg_satuan'][$i]  ?? 0);
            $hrg_pk      = $this->_parse_number_input($post['hrg_pokok'][$i]   ?? 0);
            $isi_per_box = max(1, (int)($post['isi_per_box'][$i] ?? 1));
            if (isset($post['qty_box_input'][$i]) || isset($post['qty_pcs_input'][$i])) {
                $qty_box_input = $this->_parse_number_input($post['qty_box_input'][$i] ?? 0);
                $qty_pcs_input = $this->_parse_number_input($post['qty_pcs_input'][$i] ?? 0);
                $qty = ($qty_box_input * $isi_per_box) + $qty_pcs_input;
            } else {
                $qty_input   = isset($post['qty_input'][$i])
                    ? $this->_parse_number_input($post['qty_input'][$i])
                    : $this->_parse_number_input($post['qty_faktur'][$i] ?? 0);
                $qty_mode    = strtolower(trim($post['qty_mode'][$i] ?? 'pcs'));
                $qty         = $qty_mode === 'box' ? ($qty_input * $isi_per_box) : $qty_input;
            }
            if ($qty <= 0) continue; // lewati item dengan qty 0

            $pajak       = $this->_parse_number_input($post['pajak'][$i]        ?? 0);
            $disc        = $this->_parse_number_input($post['disc'][$i]         ?? 0);

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
                   x.note_faktur, c.kode_barang AS kd_system, c.nama_barang AS nm_barang,
                   x.no_lot, x.nominal_p, x.jtempo, x.tgl_exp, x.satuan,
                   x.status, x.kd_do, x.qty,
                   (c.panjang * c.lebar * c.tinggi) AS dimensi,
                   FLOOR(x.qty / NULLIF((c.panjang * c.lebar * c.tinggi), 0)) AS qty_box,
                   (x.qty % NULLIF((c.panjang * c.lebar * c.tinggi), 0)) AS qty_pcs
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
            JOIN tbpo_barang c ON c.kode_barang = x.kd_barang
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
            JOIN tbpo_barang m ON m.kode_barang = a.kd_barang
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

        $faktur_info = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        $this->M_FakturLog->log(
            $faktur_info['no_so'] ?? null,
            $faktur_info['no_faktur'] ?? null,
            $id_faktur,
            'REPOST_ITEM',
            'Admin SC merepost ' . count($id_fd_list) . ' item faktur kembali ke SO.',
            $repost_by
        );

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

        // Cek faktur
        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$faktur) {
            echo json_encode(['status' => false, 'message' => 'Faktur tidak ditemukan']);
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

    public function get_faktur_detail_info_json()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id_faktur = (int)$this->input->get('id_faktur');
        if ($id_faktur <= 0) {
            echo json_encode(['status' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$faktur) {
            echo json_encode(['status' => false, 'message' => 'Faktur tidak ditemukan']);
            exit;
        }

        $this->load->model('M_SalesOrder');
        $items = $this->M_SalesOrder->get_faktur_detail($id_faktur);

        foreach ($items as &$item) {
            if (!empty($item['expired_date']) && $item['expired_date'] !== '0000-00-00') {
                $item['expired_date'] = date('d/m/Y', strtotime($item['expired_date']));
            } else {
                $item['expired_date'] = null;
            }
        }

        echo json_encode([
            'status' => true,
            'faktur' => $faktur,
            'items' => $items
        ]);
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

        $this->M_FakturLog->log(
            $so['no_so'] ?? '',
            null,
            null,
            'KEMBALIKAN_SO',
            'Admin SC mengembalikan SO ' . ($so['no_so'] ?? '') . ' ke Sales. Status baru: ' . $result['new_status'],
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

    public function get_faktur_activity_log_json()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->_canAccessAdminSc()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak']);
            exit;
        }

        $id_faktur = $this->input->get('id_faktur', true);
        $no_faktur = $this->input->get('no_faktur', true);
        $no_so     = $this->input->get('no_so', true);
        $keyword   = $this->input->get('keyword', true);
        $limit     = (int)($this->input->get('limit', true) ?: 100);

        $logs = $this->M_FakturLog->get_filtered([
            'id_faktur' => $id_faktur,
            'no_faktur' => $no_faktur,
            'no_so'     => $no_so,
            'keyword'   => $keyword,
        ], $limit);

        foreach ($logs as &$log) {
            $log['formatted_date'] = !empty($log['created_at']) ? date('d/m/Y H:i:s', strtotime($log['created_at'])) : '-';
        }
        unset($log);

        echo json_encode(['status' => true, 'data' => $logs]);
        exit;
    }

    public function admin_sc_activity_log()
    {
        if (!$this->_canAccessAdminSc()) {
            $this->_denyAdminScAccess();
            return;
        }

        $per_page = 25;
        $page     = max(1, (int)($this->input->get('page') ?? 1));
        $offset   = ($page - 1) * $per_page;

        $filter = [
            'no_so'     => $this->input->get('no_so',   true) ?? '',
            'no_faktur' => $this->input->get('no_faktur', true) ?? '',
            'aksi'      => $this->input->get('aksi',    true) ?? '',
            'tanggal'   => $this->input->get('tanggal', true) ?? '',
            'keyword'   => $this->input->get('keyword', true) ?? '',
        ];

        $data['page_title'] = 'KARISMA - Activity Log Admin SC';
        $data['logs']       = $this->M_FakturLog->get_filtered($filter, $per_page, $offset);
        $data['total']      = $this->M_FakturLog->count_filtered($filter);
        $data['filter']     = $filter;
        $data['per_page']   = $per_page;
        $data['page']       = $page;
        $data['total_pages']= ceil($data['total'] / $per_page);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/admin_sc_activity_log.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_sc_minta_approval_harga($id_so)
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post') {
            echo json_encode(['msg' => 'error', 'message' => 'Method tidak valid.']);
            exit;
        }

        if (!$this->_canAccessAdminSc()) {
            echo json_encode(['msg' => 'error', 'message' => 'Anda tidak memiliki akses.']);
            exit;
        }

        $id_so_detail = (int)$this->input->post('id_so_detail', true);
        $harga_baru = (float)$this->input->post('harga', true);

        if ($id_so_detail <= 0 || $harga_baru <= 0) {
            echo json_encode(['msg' => 'error', 'message' => 'Parameter tidak valid.']);
            exit;
        }

        $old_detail = $this->db->get_where('tbso_sales_order_detail', ['id' => $id_so_detail])->row_array();
        if (!$old_detail) {
            echo json_encode(['msg' => 'error', 'message' => 'Item SO tidak ditemukan.']);
            exit;
        }

        $harga_lama = (float)$old_detail['hrg_satuan'];
        if (abs($harga_lama - $harga_baru) < 0.01) {
            echo json_encode(['msg' => 'error', 'message' => 'Harga baru sama dengan harga saat ini.']);
            exit;
        }

        // Hapus request pending sebelumnya untuk item ini agar tidak menumpuk
        $this->db->where([
            'id_so_detail' => $id_so_detail,
            'status' => 'pending'
        ])->delete('tbso_approval_harga');

        $data_request = [
            'id_so' => $id_so,
            'id_so_detail' => $id_so_detail,
            'harga_lama' => $harga_lama,
            'harga_baru' => $harga_baru,
            'requested_by' => $this->_getUsername(),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $inserted = $this->db->insert('tbso_approval_harga', $data_request);

        echo json_encode([
            'msg' => $inserted ? 'success' : 'error',
            'message' => $inserted ? 'Permintaan persetujuan harga berhasil dikirim ke Manager SC.' : 'Gagal mengirim permintaan.',
        ]);
        exit;
    }

    public function admin_sc_get_approval_status($id_so)
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $approvals = $this->db->get_where('tbso_approval_harga', ['id_so' => $id_so])->result_array();
        echo json_encode([
            'msg' => 'success',
            'data' => $approvals
        ]);
        exit;
    }

    public function admin_sc_approve_harga()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
        if (!in_array($jobdesk, ['MNGSC', 'MANAGER SC', 'MANAGERSC', 'ADMIN'], true)) {
            echo json_encode(['msg' => 'error', 'message' => 'Hanya Manager SC yang dapat memberikan persetujuan.']);
            exit;
        }

        $id_approval = (int)$this->input->post('id', true);
        if ($id_approval <= 0) {
            echo json_encode(['msg' => 'error', 'message' => 'ID tidak valid.']);
            exit;
        }

        $approval = $this->db->get_where('tbso_approval_harga', ['id' => $id_approval])->row_array();
        if (!$approval) {
            echo json_encode(['msg' => 'error', 'message' => 'Data permintaan tidak ditemukan.']);
            exit;
        }

        $this->db->trans_begin();

        $this->db->where('id', $id_approval)->update('tbso_approval_harga', [
            'status' => 'approved',
            'approved_by' => $this->_getUsername(),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->M_SalesOrder->update_so_detail_harga(
            $approval['id_so'],
            $approval['id_so_detail'],
            $approval['harga_baru'],
            $this->_getUsername()
        );

        $so = $this->M_SalesOrder->get_so($approval['id_so']);
        $old_detail = $this->db->get_where('tbso_sales_order_detail', ['id' => $approval['id_so_detail']])->row_array();

        $this->M_FakturLog->log(
            $so['no_so'] ?? '',
            null,
            null,
            'UPDATE_HARGA',
            'Manager SC menyetujui perubahan harga item ' . ($old_detail['nama_barang'] ?? '') . ' menjadi Rp ' . number_format($approval['harga_baru'], 0, ',', '.'),
            $this->_getUsername()
        );

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['msg' => 'error', 'message' => 'Gagal memproses persetujuan.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['msg' => 'success', 'message' => 'Permintaan persetujuan harga disetujui.']);
        }
        exit;
    }

    public function admin_sc_reject_harga()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
        if (!in_array($jobdesk, ['MNGSC', 'MANAGER SC', 'MANAGERSC', 'ADMIN'], true)) {
            echo json_encode(['msg' => 'error', 'message' => 'Hanya Manager SC yang dapat menolak permintaan.']);
            exit;
        }

        $id_approval = (int)$this->input->post('id', true);
        if ($id_approval <= 0) {
            echo json_encode(['msg' => 'error', 'message' => 'ID tidak valid.']);
            exit;
        }

        $approval = $this->db->get_where('tbso_approval_harga', ['id' => $id_approval])->row_array();
        if (!$approval) {
            echo json_encode(['msg' => 'error', 'message' => 'Data permintaan tidak ditemukan.']);
            exit;
        }

        $updated = $this->db->where('id', $id_approval)->update('tbso_approval_harga', [
            'status' => 'rejected',
            'approved_by' => $this->_getUsername(),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode([
            'msg' => $updated ? 'success' : 'error',
            'message' => $updated ? 'Permintaan persetujuan harga ditolak.' : 'Gagal memproses penolakan.',
        ]);
        exit;
    }

    public function admin_sc_get_pending_approvals()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $this->db->select('a.*, so.no_so, customer.nama_customer AS nm_customer, d.nama_barang');
        $this->db->from('tbso_approval_harga a');
        $this->db->join('tbso_sales_order so', 'so.id_so = a.id_so', 'left');
        $this->db->join('tbso_sales_order_detail d', 'd.id = a.id_so_detail', 'left');
        $this->db->join('tb_customer customer', 'customer.kd_customer = so.kd_customer', 'left');
        $this->db->where('a.status', 'pending');
        $this->db->order_by('a.created_at', 'DESC');
        $pending = $this->db->get()->result_array();

        echo json_encode([
            'msg' => 'success',
            'data' => $pending
        ]);
        exit;
    }

    public function get_partial_items($id_so)
    {
        while (ob_get_level()) ob_end_clean();
        $items = $this->db->select('id as id_so_detail, kd_barang, nama_barang, qty, COALESCE(qty_faktur, 0) as qty_faktur')
                          ->from('tbso_sales_order_detail')
                          ->where('id_so', $id_so)
                          ->where('(qty - COALESCE(qty_faktur, 0)) >', 0.001)
                          ->get()->result_array();
        echo json_encode(['msg' => 'success', 'data' => $items]);
    }

    public function request_cancel_partial()
    {
        while (ob_get_level()) ob_end_clean();
        $id_so = $this->input->post('id_so');
        $items = $this->input->post('items');
        
        if (empty($id_so) || empty($items)) {
            echo json_encode(['msg' => 'error', 'message' => 'Data tidak valid.']);
            return;
        }

        $inserted = $this->M_SalesOrder->submit_cancel_partial_request($id_so, $items, $this->_getUsername());
        
        echo json_encode([
            'msg' => $inserted ? 'success' : 'error',
            'message' => $inserted ? 'Permintaan pembatalan berhasil diajukan ke Manager SC.' : 'Gagal mengajukan permintaan.'
        ]);
    }

    public function admin_sc_get_pending_cancel_requests()
    {
        while (ob_get_level()) ob_end_clean();
        $requests = $this->M_SalesOrder->get_pending_cancel_requests();
        echo json_encode(['msg' => 'success', 'data' => $requests]);
    }

    public function admin_sc_approve_cancel_partial()
    {
        while (ob_get_level()) ob_end_clean();
        $jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
        if (!in_array($jobdesk, ['MNGSC', 'MANAGER SC', 'MANAGERSC', 'ADMIN'], true)) {
            echo json_encode(['msg' => 'error', 'message' => 'Akses ditolak.']);
            return;
        }

        $request_ids = $this->input->post('request_ids');
        if (empty($request_ids)) {
            echo json_encode(['msg' => 'error', 'message' => 'Tidak ada data yang dipilih.']);
            return;
        }

        $first_req = $this->M_SalesOrder->get_request_by_id($request_ids[0]);
        $id_so = $first_req ? $first_req['id_so'] : null;

        $approved = $this->M_SalesOrder->approve_cancel_partial($request_ids, $this->_getUsername());

        if ($approved && $id_so) {
            $this->M_SalesOrder->check_and_update_completed_so($id_so);
            $so = $this->M_SalesOrder->get_so($id_so);
            $this->M_ActivityLog->log(
                $so['no_so'] ?? '', null, 'CANCEL_PARTIAL',
                'Manager SC menyetujui pembatalan sisa barang partial.',
                $this->_getUsername()
            );
        }

        echo json_encode([
            'msg' => $approved ? 'success' : 'error',
            'message' => $approved ? 'Pembatalan disetujui.' : 'Gagal menyetujui.'
        ]);
    }

    public function admin_sc_reject_cancel_partial()
    {
        while (ob_get_level()) ob_end_clean();
        $jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
        if (!in_array($jobdesk, ['MNGSC', 'MANAGER SC', 'MANAGERSC', 'ADMIN'], true)) {
            echo json_encode(['msg' => 'error', 'message' => 'Akses ditolak.']);
            return;
        }

        $request_ids = $this->input->post('request_ids');
        if (empty($request_ids)) {
            echo json_encode(['msg' => 'error', 'message' => 'Tidak ada data yang dipilih.']);
            return;
        }

        $rejected = $this->M_SalesOrder->reject_cancel_partial($request_ids, $this->_getUsername());
        echo json_encode([
            'msg' => $rejected ? 'success' : 'error',
            'message' => $rejected ? 'Pembatalan ditolak.' : 'Gagal menolak.'
        ]);
    }
}
