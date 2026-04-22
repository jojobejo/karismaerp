<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_SalesOrder extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_SalesOrder');
        $this->load->model('M_ActivityLog');
        $this->load->library(['form_validation', 'session', 'pagination']);
        $this->load->helper(['url', 'form']);
    }

    // ================================================================
    // HELPER — Ambil data user yang sedang login dari session / DB.
    // ================================================================
    private function _getCurrentUser()
    {
        $id   = $this->session->userdata('id_karyawan')
             ?? $this->session->userdata('id')
             ?? $this->session->userdata('user_id')
             ?? $this->session->userdata('karyawan_id')
             ?? null;

        $usn  = $this->session->userdata('username')
             ?? $this->session->userdata('user_name')
             ?? $this->session->userdata('login')
             ?? null;

        $nama = $this->session->userdata('nm_karyawan')
             ?? $this->session->userdata('nama')
             ?? $this->session->userdata('name')
             ?? $this->session->userdata('nama_user')
             ?? null;

        $wil  = $this->session->userdata('wilayah')
             ?? $this->session->userdata('wilayah_id')
             ?? $this->session->userdata('gudang_id')
             ?? $this->session->userdata('id_wilayah')
             ?? null;

        if (!empty($nama) && !empty($wil)) {
            return [
                'nm_karyawan' => $nama,
                'wilayah'     => $wil,
                'username'    => $usn ?? $nama,
            ];
        }

        $row = null;
        if (!empty($id)) {
            $row = $this->db->get_where('tb_karyawan', ['id' => $id])->row_array();
        }
        if (!$row && !empty($usn)) {
            $row = $this->db->get_where('tb_karyawan', ['username' => $usn])->row_array();
        }

        if ($row) {
            return [
                'nm_karyawan' => $row['nm_karyawan'] ?? 'system',
                'wilayah'     => $row['wilayah']     ?? '',
                'username'    => $row['username']    ?? 'system',
            ];
        }

        return ['nm_karyawan' => 'system', 'wilayah' => '', 'username' => 'system'];
    }

    private function _getUsername()
    {
        return $this->_getCurrentUser()['nm_karyawan'];
    }

    private function _getGudangId($post = [])
    {
        if (!empty($post['gudang_id'])) return $post['gudang_id'];
        $wil = $this->_getCurrentUser()['wilayah'];
        if (!empty($wil)) return $wil;
        return '';
    }

    // ================================================================
    // HELPER — Decode id_so dari URI segment (:any)
    //
    // Dengan route $route['sales_order/detail/(:any)'] = '.../$1',
    // CodeIgniter meneruskan seluruh sisa URI sebagai satu string
    // dengan '/' di antaranya. Kita tinggal rawurldecode saja.
    //
    // Contoh URI : sales_order/detail/SO%2F202604%2F0003
    //   → $encoded : SO%2F202604%2F0003  → decode → SO/202604/0003  ✓
    //
    // Contoh URI : sales_order/detail/SO/202604/0003
    //   → $encoded : SO/202604/0003      → decode → SO/202604/0003  ✓
    // ================================================================
    private function _decodeId($encoded)
    {
        return rawurldecode((string)$encoded);
    }

    // ================================================================
    // HELPER — redirect ke detail, encode '/' → %2F agar 1 segment
    // ================================================================
    private function _redirectDetail($id_so)
    {
        // rawurlencode mengubah '/' → '%2F'
        // Hasilnya: sales_order/detail/SO%2F202604%2F0003
        // Route (:any) menangkap seluruh string itu sebagai $1
        redirect('sales_order/detail/' . rawurlencode($id_so));
    }

    // ================================================================
    // LIST
    // ================================================================
    public function index()
    {
        $filter = [
            'date1'       => $this->input->post('date1'),
            'date2'       => $this->input->post('date2'),
            'status'      => $this->input->post('status'),
            'customer_id' => $this->input->post('customer_id'),
        ];

        $data['page_title'] = 'KARISMA - Sales Order';
        $data['so_list']    = $this->M_SalesOrder->get_all_so($filter);
        $data['customers']  = $this->M_SalesOrder->get_customers();
        $data['filter']     = $filter;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FORM CREATE
    // ================================================================
    public function create()
    {
        $data['page_title']     = 'KARISMA - Buat Sales Order';
        $data['no_so']          = '';   // kosong, diisi manual user
        $data['no_faktur']      = $this->M_SalesOrder->generate_no_faktur();
        $data['customers']      = $this->M_SalesOrder->get_customers();
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
    // STORE (POST)
    // ================================================================
    public function store()
    {
        if ($this->input->method() !== 'post') show_404();

        $post      = $this->input->post(null, true);
        $details   = $this->_parse_detail_post($post);
        $gudang_id = $this->_getGudangId($post);

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal 1 item barang harus diisi.');
            redirect('sales_order/create');
            return;
        }

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

        $no_so    = $post['no_so'];
        $no_faktur = $post['no_faktur'];

        $header = [
            'no_so'             => $no_so,
            'no_faktur'         => $no_faktur,
            'tanggal_transaksi' => $post['tanggal'],
            'customer_id'       => $post['customer_id'],
            'customer_name'     => $post['customer_name'],
            'gudang_id'         => $gudang_id,
            'batas_tonase'      => $tk['batas_tonase'],
            'batas_kubikasi'    => $tk['batas_kubikasi'],
            'total_tonase'      => $tk['total_tonase'],
            'total_kubikasi'    => $tk['total_kubikasi'],
            'is_nego'           => $is_nego,
            'status'            => $is_nego ? 'waiting_approval' : 'draft',
            'catatan'           => $post['catatan'] ?? null,
            'create_by'         => $this->_getUsername(),
        ];

        $id_so = $this->M_SalesOrder->simpan_so($header, $details); // return int

        if ($id_so) {
            $aksi = $is_nego ? 'CREATE_NEGO' : 'CREATE';
            $detail_str = [];
            foreach ($details as $d) {
                $detail_str[] = $d['nama_barang']
                    .' | Box: '.$d['qty_box']
                    .' | Ecer: '.$d['qty_satuan'].' pcs'
                    .' | Total: '.$d['qty'].' pcs';
            }
            $ket           = 'SO baru dibuat. Customer: '.$post['customer_name'].'. Total item: '.count($details);
            $detail_produk = implode("\n", $detail_str);
            $this->M_ActivityLog->log($no_so, $no_faktur, $aksi, $ket, $this->_getUsername(), $detail_produk);

            if ($is_nego) {
                $this->M_SalesOrder->simpan_request_approval_nego($no_so, $this->_getUsername(), $no_faktur);
                $this->session->set_flashdata('warning', 'SO berhasil disimpan. Menunggu approval harga nego.');
            } elseif (!empty($tk['warnings'])) {
                $this->session->set_flashdata('warning', '<b>Peringatan:</b> ' . implode('<br>', $tk['warnings']));
            } else {
                $this->session->set_flashdata('success', 'Sales Order <b>' . $no_so . '</b> berhasil dibuat.');
            }
            redirect('sales_order/detail/' . $id_so); // id_so = integer, aman di URL
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan SO.');
            redirect('sales_order/create');
        }
    }

    // ================================================================
    // DETAIL
    // Route: $route['sales_order/detail/(:any)'] = 'C_SalesOrder/detail/$1';
    // $encoded = seluruh sisa path setelah "detail/", mis: SO%2F202604%2F0003
    // ================================================================
    public function detail($id_so)
    {
        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();

        $data['page_title'] = 'KARISMA - Detail SO ' . $id_so;
        $data['so']         = $so;
        $data['details'] = $this->M_SalesOrder->get_so_detail($so['no_so']);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FORM EDIT
    // Route: $route['sales_order/edit/(:any)'] = 'C_SalesOrder/edit/$1';
    // ================================================================
    public function edit($id_so)
    {
        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || $so['status'] !== 'draft') {
            $this->session->set_flashdata('error', 'SO tidak dapat diedit.');
            redirect('sales_order');
            return;
        }

        $data['page_title']     = 'KARISMA - Edit SO ' . $id_so;
        $data['no_so']          = $so['no_so'] ?? '';
        $data['no_faktur']      = $so['no_faktur'] ?? '';
        $data['so']             = $so;
        $data['details']        = $this->M_SalesOrder->get_so_detail($so['no_so']);
        $data['customers']      = $this->M_SalesOrder->get_customers();
        $data['gudang_id']      = $so['gudang_id'];
        $data['batas_tonase']   = M_SalesOrder::BATAS_TONASE;
        $data['batas_kubikasi'] = M_SalesOrder::BATAS_KUBIKASI;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // UPDATE (POST)
    // Route: $route['sales_order/update/(:any)'] = 'C_SalesOrder/update/$1';
    // ================================================================
    public function update($id_so)
    {
        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();

        $post      = $this->input->post(null, true);
        $details   = $this->_parse_detail_post($post);
        $gudang_id = $this->_getGudangId($post);

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal 1 item barang harus diisi.');
            redirect('sales_order/edit/' . rawurlencode($id_so));
            return;
        }

        $stock_errors = $this->M_SalesOrder->validasi_stok($details, $gudang_id, $id_so);
        if (!empty($stock_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $stock_errors));
            redirect('sales_order/edit/' . rawurlencode($id_so));
            return;
        }

        $tk      = $this->M_SalesOrder->validasi_tonase_kubikasi($details);
        $is_nego = 0;
        foreach ($details as $d) {
            if (!empty($d['is_nego'])) { $is_nego = 1; break; }
        }

        $header = [
            'no_so'             => $post['no_so']     ?? ($so['no_so']     ?? ''),
            'no_faktur'         => $post['no_faktur'] ?? ($so['no_faktur'] ?? ''),
            'tanggal_transaksi' => $post['tanggal'],
            'customer_id'       => $post['customer_id'],
            'customer_name'     => $post['customer_name'],
            'gudang_id'         => $gudang_id,
            'batas_tonase'      => $tk['batas_tonase'],
            'batas_kubikasi'    => $tk['batas_kubikasi'],
            'total_tonase'      => $tk['total_tonase'],
            'total_kubikasi'    => $tk['total_kubikasi'],
            'is_nego'           => $is_nego,
            'status'            => $is_nego ? 'waiting_approval' : 'draft',
            'catatan'           => $post['catatan'] ?? null,
            'update_by'         => $this->_getUsername(),
        ];

        $result = $this->M_SalesOrder->update_so($id_so, $header, $details);

        if ($result) {
            // Activity log
            $so_data   = $this->M_SalesOrder->get_so($id_so);
            $no_so_log = $so_data['no_so']    ?? '';
            $no_fak    = $so_data['no_faktur'] ?? '';
            $detail_str = [];
            foreach ($details as $d) {
                $detail_str[] = $d['nama_barang']
                    .' | Box: '.$d['qty_box']
                    .' | Ecer: '.$d['qty_satuan'].' pcs'
                    .' | Total: '.$d['qty'].' pcs';
            }
            $ket           = 'SO diupdate. Customer: '.($post['customer_name']??'').'. Total item: '.count($details);
            $detail_produk = implode("\n", $detail_str);
            $this->M_ActivityLog->log($no_so_log, $no_fak, 'UPDATE', $ket, $this->_getUsername(), $detail_produk);

            if ($is_nego) {
                $this->M_SalesOrder->simpan_request_approval_nego(
                    $post['no_so'] ?? '',
                    $this->_getUsername(),
                    $post['no_faktur'] ?? ''
                );
            }
            if (!empty($tk['warnings'])) {
                $this->session->set_flashdata('warning', implode('<br>', $tk['warnings']));
            } else {
                $this->session->set_flashdata('success', 'Sales Order <b>' . $id_so . '</b> berhasil diupdate.');
            }
            $this->_redirectDetail($id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal update SO.');
            redirect('sales_order/edit/' . rawurlencode($id_so));
        }
    }

    // ================================================================
    // CANCEL
    // Route: $route['sales_order/cancel/(:any)'] = 'C_SalesOrder/cancel/$1';
    // ================================================================
    public function cancel($id_so)
    {
        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();

        if (!$so || in_array($so['status'], ['completed', 'cancelled'])) {
            $this->session->set_flashdata('error', 'SO tidak dapat dibatalkan.');
            $this->_redirectDetail($id_so);
            return;
        }
        $this->M_SalesOrder->update_status($id_so, 'cancelled', $this->_getUsername());

        // Activity log
        $so_data = $this->M_SalesOrder->get_so($id_so);
        $this->M_ActivityLog->log(
            $so_data['no_so']    ?? '',
            $so_data['no_faktur'] ?? '',
            'CANCEL',
            'SO dibatalkan.',
            $this->_getUsername()
        );

        $this->session->set_flashdata('success', 'Sales Order <b>' . $id_so . '</b> berhasil dibatalkan.');
        redirect('sales_order');
    }

    // ================================================================
    // APPROVAL NEGO
    // ================================================================
    public function approval()
    {
        $data['page_title'] = 'KARISMA - Approval Harga Nego';
        $data['list']       = $this->M_SalesOrder->get_pending_approval();
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function approve()
    {
        $status_approval = $this->input->post('status', true);
        $this->M_SalesOrder->proses_approval_nego(
            $this->input->post('id',   true),
            $status_approval,
            $this->input->post('note', true),
            $this->_getUsername()
        );

        // Activity log
        $id_approval = $this->input->post('id', true);
        $approval    = $this->db->get_where('tbso_approval_nego', ['id' => $id_approval])->row_array();
        $aksi_appr   = ($status_approval === 'approved') ? 'APPROVE' : 'REJECT';
        $ket_appr    = 'Harga nego '.($status_approval==='approved'?'disetujui':'ditolak').'. Note: '.$this->input->post('note', true);
        $this->M_ActivityLog->log(
            $approval['no_so']    ?? '',
            $approval['no_faktur'] ?? '',
            $aksi_appr,
            $ket_appr,
            $this->_getUsername()
        );

        $msg = ($status_approval === 'approved') ? 'disetujui' : 'ditolak';
        $this->session->set_flashdata('success', "Harga nego berhasil <b>{$msg}</b>.");
        redirect('sales_order/approval');
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
            'no_so'    => $this->input->get('no_so',    true) ?? '',
            'aksi'     => $this->input->get('aksi',     true) ?? '',
            'tanggal'  => $this->input->get('tanggal',  true) ?? '',
            'keyword'  => $this->input->get('keyword',  true) ?? '',
        ];

        $data['page_title'] = 'KARISMA - Activity Log SO';
        $data['logs']       = $this->M_ActivityLog->get_filtered($filter, $per_page, $offset);
        $data['total']      = $this->M_ActivityLog->count_filtered($filter);
        $data['filter']     = $filter;
        $data['per_page']   = $per_page;
        $data['page']       = $page;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_activity_log.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // AJAX — activity log per SO (untuk tab di halaman detail)
    public function activity_log_so($id_so)
    {
        $so      = $this->M_SalesOrder->get_so($id_so);
        $no_so   = $so['no_so'] ?? '';
        $logs    = $this->M_ActivityLog->get_by_no_so($no_so);

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
            $stock = $this->M_SalesOrder->get_available_stock_with_dimensi(null, $kd_barang);

            foreach ($stock as &$row) {
                $row['available_stock'] = (float)($row['available_stock'] ?? 0);
                $row['available_box']   = (int)($row['available_box']    ?? 0);
                $row['available_ecer']  = (int)($row['available_ecer']   ?? 0);
                $row['berat_gram']      = (float)($row['berat_gram']     ?? 0);
                $row['kubikasi_m3']     = (float)($row['kubikasi_m3']    ?? 0);
                $row['hpp']             = (float)($row['hpp']            ?? 0);
                $row['isi_per_box']     = (int)($row['isi_per_box']      ?? 1);
                $row['p']               = (float)($row['p']              ?? 0);
                $row['l']               = (float)($row['l']              ?? 0);
                $row['t']               = (float)($row['t']              ?? 0);
                foreach (['kode_barang','nama_barang','satuan','exp_date','no_lot','gudang'] as $f) {
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
    // PRIVATE — Parse POST detail item
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

            $qty_kecil   = ($qty_box * $isi_per_box) + $qty_satuan;
            $total_tax   = $hrg * $qty_kecil * (1 + $pajak / 100);
            $is_nego     = ($hrg > 0 && $hrg < $hrg_pk) ? 1 : 0;

            $kd_po = $this->M_SalesOrder->get_kd_po(
                $kd,
                $post['expired_date'][$i] ?? '',
                $post['no_lot'][$i]       ?? ''
            );

            $details[] = [
                'produk_id'    => $post['produk_id'][$i]    ?? '',
                'kd_barang'    => $kd,
                'nama_barang'  => $post['nama_barang'][$i]  ?? '',
                'qty'          => $qty_kecil,
                'qty_box'      => $qty_box,
                'qty_satuan'   => $qty_satuan,
                'isi_per_box'  => $isi_per_box,
                'satuan'       => $post['satuan'][$i]        ?? '',
                'expired_date' => $post['expired_date'][$i]  ?? '',
                'no_lot'       => $post['no_lot'][$i]        ?? null,
                'kd_po'        => $kd_po,
                'pajak'        => $pajak,
                'hrg_satuan'   => $hrg,
                'hrg_pokok'    => $hrg_pk,
                'total_harga'  => $total_tax,
                'berat_gram'   => (float)($post['berat_gram'][$i]  ?? 0),
                'kubikasi_m3'  => (float)($post['kubikasi_m3'][$i] ?? 0),
                'kode_akun'    => $post['kode_akun'][$i]     ?? null,
                'is_nego'      => $is_nego,
                'create_by'    => $this->_getUsername(),
            ];
        }
        return $details;
    }
}