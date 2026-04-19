<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_SalesOrder extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_SalesOrder');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);
    }

    // ================================================================
    // HELPER — username dari session, fallback 'system'
    // ================================================================
    private function _getUsername()
    {
        $keys = ['username', 'user_name', 'nama', 'nama_user', 'user',
                 'login_name', 'email', 'id_user', 'userid'];
        foreach ($keys as $key) {
            $val = $this->session->userdata($key);
            if (!empty($val)) return $val;
        }
        return 'system';
    }

    // ================================================================
    // HELPER — gudang_id dari POST → session → ''
    // ================================================================
    private function _getGudangId($post = [])
    {
        if (!empty($post['gudang_id'])) return $post['gudang_id'];
        // Coba berbagai key session yang mungkin menyimpan gudang/wilayah
        $keys = ['gudang_id', 'id_gudang', 'wilayah_id', 'id_wilayah', 'gudang'];
        foreach ($keys as $key) {
            $val = $this->session->userdata($key);
            if (!empty($val)) return $val;
        }
        return '';
    }

    // ================================================================
    // HELPER — redirect aman dengan id_so yang mengandung '/'
    // ================================================================
    private function _redirectDetail($id_so)
    {
        // Encode setiap segment agar '/' dalam nomor SO tidak memutus URL
        $encoded = implode('/', array_map('rawurlencode', explode('/', $id_so)));
        redirect('sales_order/detail/' . $encoded);
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
        $data['no_so']          = $this->M_SalesOrder->generate_no_so();
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

        $id_so  = $post['id_so'];
        $header = [
            'id_so'             => $id_so,
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

        $result = $this->M_SalesOrder->simpan_so($header, $details);

        if ($result) {
            if ($is_nego) {
                $this->M_SalesOrder->simpan_request_approval_nego($id_so, $this->_getUsername());
                $this->session->set_flashdata('warning', 'SO berhasil disimpan. Menunggu approval harga nego.');
            } elseif (!empty($tk['warnings'])) {
                $this->session->set_flashdata('warning', '<b>Peringatan:</b> ' . implode('<br>', $tk['warnings']));
            } else {
                $this->session->set_flashdata('success', 'Sales Order <b>' . $id_so . '</b> berhasil dibuat.');
            }
            $this->_redirectDetail($id_so);  // ← FIX: encode '/' dalam nomor SO
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan SO. Silakan coba lagi.');
            redirect('sales_order/create');
        }
    }

    // ================================================================
    // DETAIL
    // Terima id_so dari URL yang mungkin ter-encode
    // ================================================================
    public function detail($seg1, $seg2 = null, $seg3 = null)
    {
        // Rekonstruksi id_so dari segment URL
        // URL: sales_order/detail/SO/202604/0001
        $parts = array_filter([$seg1, $seg2, $seg3]);
        $id_so = implode('/', array_map('rawurldecode', $parts));

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so) show_404();

        $data['page_title'] = 'KARISMA - Detail SO ' . $id_so;
        $data['so']         = $so;
        $data['details']    = $this->M_SalesOrder->get_so_detail($id_so);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FORM EDIT
    // ================================================================
    public function edit($seg1, $seg2 = null, $seg3 = null)
    {
        $parts = array_filter([$seg1, $seg2, $seg3]);
        $id_so = implode('/', array_map('rawurldecode', $parts));

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || $so['status'] !== 'draft') {
            $this->session->set_flashdata('error', 'SO tidak dapat diedit.');
            redirect('sales_order');
            return;
        }

        $data['page_title']     = 'KARISMA - Edit SO ' . $id_so;
        $data['no_so']          = $id_so;
        $data['so']             = $so;
        $data['details']        = $this->M_SalesOrder->get_so_detail($id_so);
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
    // ================================================================
    public function update($seg1, $seg2 = null, $seg3 = null)
    {
        if ($this->input->method() !== 'post') show_404();

        $parts     = array_filter([$seg1, $seg2, $seg3]);
        $id_so     = implode('/', array_map('rawurldecode', $parts));
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
            if ($is_nego) {
                $this->M_SalesOrder->simpan_request_approval_nego($id_so, $this->_getUsername());
            }
            if (!empty($tk['warnings'])) {
                $this->session->set_flashdata('warning', implode('<br>', $tk['warnings']));
            } else {
                $this->session->set_flashdata('success', 'Sales Order <b>' . $id_so . '</b> berhasil diupdate.');
            }
            $this->_redirectDetail($id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal update SO.');
            redirect('sales_order/edit/' . $id_so);
        }
    }

    // ================================================================
    // CANCEL
    // ================================================================
    public function cancel($seg1, $seg2 = null, $seg3 = null)
    {
        if ($this->input->method() !== 'post') show_404();
        $parts = array_filter([$seg1, $seg2, $seg3]);
        $id_so = implode('/', array_map('rawurldecode', $parts));

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || in_array($so['status'], ['completed', 'cancelled'])) {
            $this->session->set_flashdata('error', 'SO tidak dapat dibatalkan.');
            $this->_redirectDetail($id_so);
            return;
        }
        $this->M_SalesOrder->update_status($id_so, 'cancelled', $this->_getUsername());
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
        if ($this->input->method() !== 'post') show_404();
        $this->M_SalesOrder->proses_approval_nego(
            $this->input->post('id',     true),
            $this->input->post('status', true),
            $this->input->post('note',   true),
            $this->_getUsername()
        );
        $msg = ($this->input->post('status') === 'approved') ? 'disetujui' : 'ditolak';
        $this->session->set_flashdata('success', "Harga nego berhasil <b>{$msg}</b>.");
        redirect('sales_order/approval');
    }

    // ================================================================
    // AJAX — get_stock
    // gudang dari GET param → session → '' (model handle empty)
    // ================================================================
    public function get_stock()
    {
        if (ob_get_level()) ob_end_clean();

        try {
            $gudang_id = $this->input->get('gudang_id', true);
            if (empty($gudang_id)) {
                $gudang_id = $this->_getGudangId();
            }
            $kd_barang = $this->input->get('kd_barang', true) ?: null;

            $stock = $this->M_SalesOrder->get_available_stock_with_dimensi($gudang_id, $kd_barang);

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
    //
    // HARGA: hrg_satuan = harga per BOX (input user)
    //        total_harga = hrg_satuan × qty_box  (+ eceran × hrg/pcs)
    //        Subtotal BUKAN per pcs — sesuai harga yang diinput per box.
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

            // Total qty dalam satuan kecil (untuk stok & tonase)
            $qty_kecil = ($qty_box * $isi_per_box) + $qty_satuan;

            // Harga per pcs = hrg_satuan / isi_per_box
            $hrg_per_pcs = $isi_per_box > 0 ? $hrg / $isi_per_box : 0;

            // Subtotal: box × hrg/box + eceran × hrg/pcs
            $subtotal_before = ($hrg * $qty_box) + ($hrg_per_pcs * $qty_satuan);
            $total_tax       = $subtotal_before * (1 + $pajak / 100);
            $is_nego         = ($hrg > 0 && $hrg < $hrg_pk) ? 1 : 0;

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