<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_SalesOrder extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_SalesOrder');
        $this->load->model('M_Logistik');
        $this->load->model('M_ActivityLog');
        $this->load->model('M_Checker');
        $this->load->library(['form_validation', 'session', 'pagination']);
        $this->load->helper(['url', 'form']);
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

    private function _isRestrictedSalesUser()
    {
        return strtoupper((string)$this->session->userdata('jobdesk')) === 'SC';
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
            ->where_in('jobdesk', ['SC', 'SALES', 'SALESCOUNTER', 'SALESONLINE'])
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
        $filter = [
            'date1'       => $this->input->post('date1'),
            'date2'       => $this->input->post('date2'),
            'status'      => $this->input->post('status'),
            'customer_id' => $this->input->post('customer_id'),
        ];
        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        $data['page_title'] = 'KARISMA - Sales Order';
        $data['so_list']    = $this->M_SalesOrder->get_all_so($filter);
        $data['customers']  = $this->M_SalesOrder->get_customers();
        $data['filter']     = $filter;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FORM CREATE SO
    // ================================================================
    public function create()
    {
        $data['page_title']     = 'KARISMA - Buat Sales Order';
        $data['no_so']          = $this->M_SalesOrder->generate_no_so();
        $data['customers']      = $this->M_SalesOrder->get_customers();
        $data['tax_list']       = $this->M_SalesOrder->get_tax_list();
        $data['approver_list']  = $this->M_SalesOrder->get_approver_list();
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

        $post      = $this->input->post(null, true);
        $details   = $this->_parse_detail_post($post);
        $gudang_id = $this->_getGudangId($post);

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal 1 item barang harus diisi.');
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
        $is_nego = 0;
        foreach ($details as $d) {
            if (!empty($d['is_nego'])) { $is_nego = 1; break; }
        }

        $no_so = $post['no_so'] ?? $this->M_SalesOrder->generate_no_so();

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

            if ($is_nego) {
                $approve_by = '';
                foreach ($details as $d) {
                    if (!empty($d['is_nego']) && !empty($d['approve_by'])) {
                        $approve_by = $d['approve_by']; break;
                    }
                }
                $this->M_SalesOrder->simpan_request_approval(
                    $no_so,
                    'Harga item berbeda dari HPP. Dibuat oleh: ' . $this->_getUsername(),
                    $this->_getUsername(),
                    $approve_by
                );
                $this->session->set_flashdata('warning',
                    'SO berhasil disimpan dengan status <b>Draft</b>. Menunggu approval dari <b>' . $approve_by . '</b>.');
            } elseif (!empty($tk['warnings'])) {
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
        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $details  = $this->M_SalesOrder->get_so_detail($id_so);
        $fakturs  = $this->M_SalesOrder->get_faktur_by_so($id_so);

        // Hitung ringkasan qty per baris
        $total_order       = 0;
        $total_faktur      = 0;
        $total_outstanding = 0;
        foreach ($details as $d) {
            $total_order       += (float)$d['qty'];
            $total_faktur      += (float)$d['qty_faktur'];
            $total_outstanding += (float)($d['qty'] - $d['qty_faktur']);
        }

        $data['page_title']        = 'KARISMA - Detail SO ' . ($so['no_so'] ?? $id_so);
        $data['so']                = $so;
        $data['details']           = $details;
        $data['fakturs']           = $fakturs;
        $data['total_order']       = $total_order;
        $data['total_faktur']      = $total_faktur;
        $data['total_outstanding'] = $total_outstanding;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FORM EDIT SO (hanya saat Draft)
    // ================================================================
    public function edit($id_so)
    {
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
        $data['details']        = $this->M_SalesOrder->get_so_detail($id_so);
        $data['customers']      = $this->M_SalesOrder->get_customers();
        $data['tax_list']       = $this->M_SalesOrder->get_tax_list();
        $data['approver_list']  = $this->M_SalesOrder->get_approver_list();
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

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $post      = $this->input->post(null, true);
        $details   = $this->_parse_detail_post($post);
        $gudang_id = $this->_getGudangId($post);

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal 1 item barang harus diisi.');
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
        $is_nego = 0;
        foreach ($details as $d) {
            if (!empty($d['is_nego'])) { $is_nego = 1; break; }
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

            if ($is_nego) {
                $approve_by = $post['approve_by'] ?? '';
                $this->M_SalesOrder->simpan_request_approval(
                    $so_fresh['no_so'] ?? '',
                    'Harga item berbeda dari HPP. Diupdate oleh: ' . $this->_getUsername(),
                    $this->_getUsername(),
                    $approve_by
                );
            }

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

        $so = $this->M_SalesOrder->get_so($id_so);
        if ($so && !$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }
        if (!$so || $so['status'] !== 'open') {
            $this->session->set_flashdata('error', 'Faktur hanya dapat dibuat dari SO yang berstatus Open.');
            redirect('sales_order/detail/' . $id_so);
            return;
        }

        $details = $this->M_SalesOrder->get_so_detail($id_so);
        $selected_items = $this->input->get('item', true);
        if (!is_array($selected_items)) {
            $selected_items = $selected_items !== null && $selected_items !== '' ? [$selected_items] : [];
        }
        $selected_items = array_filter(array_map('intval', $selected_items));
        $tax_mode = strtolower(trim($this->input->get('tax_mode', true) ?? 'non_pajak'));
        $tax_rate = $tax_mode === 'pajak' ? 11 : 0;

        // Filter hanya item yang masih ada outstanding
        $items_outstanding = array_filter($details, function($d) {
            return ((float)$d['qty'] - (float)$d['qty_faktur']) > 0;
        });

        if (!empty($selected_items)) {
            $items_outstanding = array_filter($items_outstanding, function($d) use ($selected_items) {
                return in_array((int)$d['id_so_detail'], $selected_items, true);
            });
        }

        if (empty($items_outstanding)) {
            $message = !empty($selected_items)
                ? 'Item yang dipilih tidak valid atau sudah difakturkan seluruhnya.'
                : 'Semua item pada SO ini sudah difakturkan seluruhnya.';
            $this->session->set_flashdata('error', $message);
            redirect('sales_order/detail/' . $id_so);
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

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || $so['status'] !== 'open') {
            $this->session->set_flashdata('error', 'SO tidak valid atau tidak berstatus Open.');
            redirect('sales_order/detail/' . $id_so);
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
            redirect('sales_order/form_faktur/' . $id_so);
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
            redirect('sales_order/form_faktur/' . $id_so);
            return;
        }

        $faktur_prefix = !empty($so['is_faktur_z']) ? 'Z' : $this->_getFakturUserPrefix();
        $expected_prefix = $faktur_prefix . 'INV' . date('dmy');
        $posted_no_faktur = trim((string)($post['no_faktur'] ?? ''));
        $no_faktur = $posted_no_faktur;

        if (strpos($posted_no_faktur, $expected_prefix) !== 0 || $this->M_SalesOrder->is_no_faktur_used($posted_no_faktur)) {
            $no_faktur = $this->M_SalesOrder->generate_no_faktur($faktur_prefix);
        }
        $cara_pembayaran = strtolower(trim($post['cara_pembayaran'] ?? 'cash'));
        if (!in_array($cara_pembayaran, ['cash', 'transfer', 'tempo'], true)) {
            $cara_pembayaran = 'cash';
        }
        $jtempo = (int)($post['jtempo'] ?? 0);
        if (!in_array($jtempo, [0, 30, 60, 90], true)) {
            $jtempo = 0;
        }

        $faktur_header = [
            'no_faktur'             => $no_faktur,
            'tanggal_faktur'        => $post['tanggal_faktur'],
            'tanggal_jatuh_tempo'   => $post['tanggal_jatuh_tempo'] ?? null,
            'salesman'              => trim($post['salesman'] ?? ''),
            'cara_pembayaran'       => $cara_pembayaran,
            'jtempo'                => $jtempo,
            'tempo'                 => $jtempo,
            'catatan'               => $post['catatan'] ?? null,
            'create_by'             => $this->_getUsername(),
        ];

        $result = $this->M_SalesOrder->buat_faktur($id_so, $faktur_header, $faktur_items);

        if (is_array($result) && isset($result['errors'])) {
            $this->session->set_flashdata('error', implode('<br>', $result['errors']));
            redirect('sales_order/form_faktur/' . $id_so);
            return;
        }

        if ($result) {
            $so_fresh = $this->M_SalesOrder->get_so($id_so);

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
            if (($so_fresh['status'] ?? '') === 'completed') {
                $this->session->set_flashdata('success',
                    'Faktur <b>' . $no_faktur . '</b> berhasil dibuat. '
                    . 'Seluruh item pada SO <b>' . $so['no_so'] . '</b> sudah terpenuhi. Status SO: <b>Completed</b>.');
            } else {
                $this->session->set_flashdata('success',
                    'Faktur <b>' . $no_faktur . '</b> berhasil dibuat. SO masih berstatus <b>Open</b> — masih ada outstanding.');
            }

            redirect('sales_order/detail/' . $id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan Faktur Penjualan.');
            redirect('sales_order/form_faktur/' . $id_so);
        }
    }

    // ================================================================
    // DETAIL FAKTUR
    // ================================================================
    public function detail_faktur($id_faktur)
    {
        $faktur  = $this->M_SalesOrder->get_faktur($id_faktur);
        if (!$faktur) show_404();

        $details = $this->M_SalesOrder->get_faktur_detail($id_faktur);
        $so      = $this->M_SalesOrder->get_so($faktur['id_so']);
        if (!$this->_canAccessSo($so)) {
            $this->_denySoAccess();
            return;
        }

        $data['page_title'] = 'KARISMA - Faktur ' . $faktur['no_faktur'];
        $data['faktur']     = $faktur;
        $data['details']    = $details;
        $data['so']         = $so;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FAKTUR PER RUTE - confirmed dan belum masuk Delivery Order
    // ================================================================
    public function faktur_rute()
    {
        $this->_ensureSoRouteColumn();

        $selected_rute = trim((string)($this->input->get('rute', true) ?? ''));

        $routes = $this->M_SalesOrder->get_pending_faktur_rute_summary();
        if ($selected_rute === '' && !empty($routes)) {
            $selected_rute = $routes[0]['kd_rute'];
        }

        $fakturs = $this->M_SalesOrder->get_pending_faktur_by_rute($selected_rute);

        $total_tonase = 0;
        $total_kubikasi = 0;
        foreach ($fakturs as $f) {
            $total_tonase += (float)($f['total_tonase'] ?? 0);
            $total_kubikasi += (float)($f['total_kubikasi'] ?? 0);
        }

        $data['page_title']       = 'KARISMA - Faktur per Rute';
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
    // SO PER RUTE - hanya menampilkan daftar Sales Order berdasarkan rute
    // ================================================================
    public function so_rute()
    {
        $this->_ensureSoRouteColumn();

        $selected_rute = trim((string)($this->input->get('rute', true) ?? ''));
        $filter = [];
        if ($this->_isRestrictedSalesUser()) {
            $filter['create_by'] = $this->_getUsername();
        }

        $routes = $this->M_SalesOrder->get_so_rute_summary($filter);
        if ($selected_rute === '' && !empty($routes)) {
            $selected_rute = $routes[0]['kd_rute'];
        }

        $sales_orders = $this->M_SalesOrder->get_so_by_rute($selected_rute, $filter);

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
            if (!$so || ($so['status'] ?? '') !== 'open') {
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
                $message .= ' <b>' . $skipped . ' SO</b> dilewati karena tidak valid atau tidak berstatus Open.';
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Tidak ada SO yang berhasil dipindahkan.');
        }

        redirect('sales_order/so_rute?rute=' . rawurlencode($target_rute));
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
            'no_so'   => $this->input->get('no_so',   true) ?? '',
            'aksi'    => $this->input->get('aksi',    true) ?? '',
            'tanggal' => $this->input->get('tanggal', true) ?? '',
            'keyword' => $this->input->get('keyword', true) ?? '',
        ];

        $data['page_title'] = 'KARISMA - Activity Log SO';
        $data['logs']       = $this->_hydrate_missing_log_detail_produk(
            $this->M_ActivityLog->get_filtered($filter, $per_page, $offset)
        );
        $data['total']      = $this->M_ActivityLog->count_filtered($filter);
        $data['filter']     = $filter;
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

            $stock = $this->M_SalesOrder->get_available_stock_with_dimensi($gudang_id, $kd_barang);

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
    private function _parse_detail_post($post)
    {
        $details = [];
        if (empty($post['kd_barang']) || !is_array($post['kd_barang'])) return $details;

        foreach ($post['kd_barang'] as $i => $kd) {
            if (empty($kd)) continue;

            $hrg         = (float)($post['hrg_satuan'][$i]  ?? 0);
            $hrg_pk      = (float)($post['hrg_pokok'][$i]   ?? 0);
            $qty_box     = (float)($post['qty_box'][$i]      ?? 0);
            $qty_satuan  = (float)($post['qty_satuan'][$i]   ?? 0);
            $isi_per_box = max(1, (int)($post['isi_per_box'][$i] ?? 1));
            $pajak       = (float)($post['pajak'][$i]        ?? 0);
            $disc        = (float)($post['disc'][$i]         ?? 0);
            $qty_kecil   = ($qty_box * $isi_per_box) + $qty_satuan;

            $subtotal_before_disc = $hrg * $qty_kecil;
            $subtotal_after_disc  = $subtotal_before_disc * (1 - $disc / 100);
            $total_tax            = $subtotal_after_disc  * (1 + $pajak / 100);
            $is_nego              = ($hrg > 0 && $hrg < $hrg_pk) ? 1 : 0;

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
                'ref_no'               => null,
                'pajak'                => $pajak,
                'disc'                 => $disc,
                'subtotal_before_disc' => $subtotal_before_disc,
                'subtotal_after_disc'  => $subtotal_after_disc,
                'hrg_satuan'           => $hrg,
                'hrg_pokok'            => $hrg_pk,
                'total_harga'          => $total_tax,
                'berat_gram'           => (float)($post['berat_gram'][$i]  ?? 0),
                'kubikasi_m3'          => (float)($post['kubikasi_m3'][$i] ?? 0),
                'is_nego'              => $is_nego,
                'approve_by'           => trim($post['approve_by'][$i] ?? ''),
                'create_by'            => $this->_getUsername(),
            ];
        }
        return $details;
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
            $total_harga          = $subtotal_after_disc   * (1 + $pajak / 100);

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
            'message' => 'DO ' . $created['kd_do'] . ' dibuat Siap Loading dari '
                . $created['total_faktur'] . ' faktur rute ' . $kd_rute . '.'
        ]);
        exit;
    }
}
