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
    // LIST SALES ORDER
    // ================================================================
    public function index()
    {
        $date1       = $this->input->post('date1');
        $date2       = $this->input->post('date2');
        $status      = $this->input->post('status');
        $customer_id = $this->input->post('customer_id');

        $filter = [
            'date1'       => $date1,
            'date2'       => $date2,
            'status'      => $status,
            'customer_id' => $customer_id,
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
    // FORM BUAT SO BARU
    // ================================================================
    public function create()
    {
        $data['page_title'] = 'KARISMA - Buat Sales Order';
        $data['no_so']      = $this->M_SalesOrder->generate_no_so();
        $data['customers']  = $this->M_SalesOrder->get_customers();
        $data['gudang_id']  = $this->session->userdata('gudang_id') ?? '';
        $data['so']         = null;   // null = mode tambah
        $data['details']    = [];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // SIMPAN SO BARU (POST)
    // ================================================================
    public function store()
    {
        if ($this->input->method() !== 'post') show_404();

        $post    = $this->input->post(null, true);
        $details = $this->_parse_detail_post($post);

        // Validasi: minimal 1 item
        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal 1 item barang harus diisi.');
            redirect('sales_order/create');
            return;
        }

        // Validasi stok (server-side)
        $stock_errors = $this->M_SalesOrder->validasi_stok($details, $post['gudang_id']);
        if (!empty($stock_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $stock_errors));
            redirect('sales_order/create');
            return;
        }

        // Hitung & validasi tonase/kubikasi
        $tk = $this->M_SalesOrder->validasi_tonase_kubikasi(
            $details,
            (float)($post['batas_tonase']   ?? 0),
            (float)($post['batas_kubikasi'] ?? 0)
        );

        // Deteksi harga nego
        $is_nego = 0;
        foreach ($details as $d) {
            if (!empty($d['is_nego'])) { $is_nego = 1; break; }
        }

        $id_so = $post['id_so'];

        $header = [
            'id_so'             => $id_so,
            'tanggal_transaksi' => $post['tanggal'],
            'customer_id'       => $post['customer_id'],
            'customer_name'     => $post['customer_name'],
            'gudang_id'         => $post['gudang_id'],
            'batas_tonase'      => $post['batas_tonase']   ?: null,
            'batas_kubikasi'    => $post['batas_kubikasi'] ?: null,
            'total_tonase'      => $tk['total_tonase'],
            'total_kubikasi'    => $tk['total_kubikasi'],
            'is_nego'           => $is_nego,
            'status'            => $is_nego ? 'waiting_approval' : 'draft',
            'catatan'           => $post['catatan'] ?? null,
            'create_by'         => $this->session->userdata('username'),
        ];

        $result = $this->M_SalesOrder->simpan_so($header, $details);

        if ($result) {
            if ($is_nego) {
                $this->M_SalesOrder->simpan_request_approval_nego(
                    $id_so,
                    $this->session->userdata('username')
                );
                $this->session->set_flashdata('warning',
                    'SO berhasil disimpan. Menunggu approval harga nego.');
            } elseif (!empty($tk['warnings'])) {
                $this->session->set_flashdata('warning',
                    '<b>Peringatan:</b> ' . implode('<br>', $tk['warnings']));
            } else {
                $this->session->set_flashdata('success', 'Sales Order <b>' . $id_so . '</b> berhasil dibuat.');
            }
            redirect('sales_order/detail/' . $id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan SO. Silakan coba lagi.');
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

        $data['page_title'] = 'KARISMA - Detail SO ' . $id_so;
        $data['so']         = $so;
        $data['details']    = $this->M_SalesOrder->get_so_detail($id_so);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FORM EDIT SO
    // ================================================================
    public function edit($id_so)
    {
        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || $so['status'] !== 'draft') {
            $this->session->set_flashdata('error', 'SO tidak dapat diedit.');
            redirect('sales_order');
            return;
        }

        $data['page_title'] = 'KARISMA - Edit SO ' . $id_so;
        $data['no_so']      = $id_so;
        $data['so']         = $so;
        $data['details']    = $this->M_SalesOrder->get_so_detail($id_so);
        $data['customers']  = $this->M_SalesOrder->get_customers();
        $data['gudang_id']  = $so['gudang_id'];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // UPDATE SO (POST)
    // ================================================================
    public function update($id_so)
    {
        if ($this->input->method() !== 'post') show_404();

        $post    = $this->input->post(null, true);
        $details = $this->_parse_detail_post($post);

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal 1 item barang harus diisi.');
            redirect('sales_order/edit/' . $id_so);
            return;
        }

        $stock_errors = $this->M_SalesOrder->validasi_stok($details, $post['gudang_id'], $id_so);
        if (!empty($stock_errors)) {
            $this->session->set_flashdata('error', implode('<br>', $stock_errors));
            redirect('sales_order/edit/' . $id_so);
            return;
        }

        $tk = $this->M_SalesOrder->validasi_tonase_kubikasi(
            $details,
            (float)($post['batas_tonase']   ?? 0),
            (float)($post['batas_kubikasi'] ?? 0)
        );

        $is_nego = 0;
        foreach ($details as $d) {
            if (!empty($d['is_nego'])) { $is_nego = 1; break; }
        }

        $header = [
            'tanggal_transaksi' => $post['tanggal'],
            'customer_id'       => $post['customer_id'],
            'customer_name'     => $post['customer_name'],
            'gudang_id'         => $post['gudang_id'],
            'batas_tonase'      => $post['batas_tonase']   ?: null,
            'batas_kubikasi'    => $post['batas_kubikasi'] ?: null,
            'total_tonase'      => $tk['total_tonase'],
            'total_kubikasi'    => $tk['total_kubikasi'],
            'is_nego'           => $is_nego,
            'status'            => $is_nego ? 'waiting_approval' : 'draft',
            'catatan'           => $post['catatan'] ?? null,
            'update_by'         => $this->session->userdata('username'),
        ];

        $result = $this->M_SalesOrder->update_so($id_so, $header, $details);

        if ($result) {
            if ($is_nego) {
                $this->M_SalesOrder->simpan_request_approval_nego(
                    $id_so,
                    $this->session->userdata('username')
                );
            }
            if (!empty($tk['warnings'])) {
                $this->session->set_flashdata('warning', implode('<br>', $tk['warnings']));
            } else {
                $this->session->set_flashdata('success', 'Sales Order <b>' . $id_so . '</b> berhasil diupdate.');
            }
            redirect('sales_order/detail/' . $id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal update SO.');
            redirect('sales_order/edit/' . $id_so);
        }
    }

    // ================================================================
    // CANCEL SO (POST)
    // ================================================================
    public function cancel($id_so)
    {
        if ($this->input->method() !== 'post') show_404();

        $so = $this->M_SalesOrder->get_so($id_so);
        if (!$so || in_array($so['status'], ['completed', 'cancelled'])) {
            $this->session->set_flashdata('error', 'SO tidak dapat dibatalkan.');
            redirect('sales_order/detail/' . $id_so);
            return;
        }

        $this->M_SalesOrder->update_status($id_so, 'cancelled', $this->session->userdata('username'));
        $this->session->set_flashdata('success', 'Sales Order <b>' . $id_so . '</b> berhasil dibatalkan.');
        redirect('sales_order');
    }

    // ================================================================
    // HALAMAN APPROVAL NEGO (Manager/Admin)
    // ================================================================
    public function approval()
    {
        $data['page_title'] = 'KARISMA - Approval Harga Nego';
        $data['list']       = $this->M_SalesOrder->get_pending_approval();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // PROSES APPROVAL (POST)
    // ================================================================
    public function approve()
    {
        if ($this->input->method() !== 'post') show_404();

        $id     = $this->input->post('id',     true);
        $status = $this->input->post('status', true); // approved / rejected
        $note   = $this->input->post('note',   true);

        $this->M_SalesOrder->proses_approval_nego(
            $id,
            $status,
            $note,
            $this->session->userdata('username')
        );

        $msg = ($status === 'approved') ? 'disetujui' : 'ditolak';
        $this->session->set_flashdata('success', "Harga nego berhasil <b>{$msg}</b>.");
        redirect('sales_order/approval');
    }

    // ================================================================
    // AJAX — Ambil Data Stok (dipanggil dari modal picker)
    // ================================================================
    public function get_stock()
    {
        $gudang_id = $this->input->get('gudang_id', true);
        $kd_barang = $this->input->get('kd_barang', true) ?: null;

        $stock = $this->M_SalesOrder->get_available_stock($gudang_id, $kd_barang);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'data' => $stock]);
    }

    // ================================================================
    // AJAX — Ambil Detail Barang (harga pokok, tonase, kubikasi)
    // ================================================================
    public function get_barang()
    {
        $kd_barang = $this->input->get('kd_barang', true);
        $barang    = $this->M_SalesOrder->get_detail_barang($kd_barang);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'data' => $barang]);
    }

    // ================================================================
    // PRIVATE — Parse array item dari POST
    // ================================================================
    private function _parse_detail_post($post)
    {
        $details = [];
        if (empty($post['kd_barang']) || !is_array($post['kd_barang'])) return $details;

        foreach ($post['kd_barang'] as $i => $kd) {
            if (empty($kd)) continue;

            $hrg    = (float)($post['hrg_satuan'][$i] ?? 0);
            $hrg_pk = (float)($post['hrg_pokok'][$i]  ?? 0);
            $qty    = (float)($post['qty'][$i]         ?? 0);
            $pajak  = (float)($post['pajak'][$i]       ?? 0);

            $subtotal  = $hrg * $qty;
            $total_tax = $subtotal + ($subtotal * $pajak / 100);
            $is_nego   = ($hrg > 0 && $hrg < $hrg_pk) ? 1 : 0;

            $details[] = [
                'produk_id'       => $post['produk_id'][$i]       ?? '',
                'kd_barang'       => $kd,
                'nama_barang'     => $post['nama_barang'][$i]      ?? '',
                'qty'             => $qty,
                'satuan'          => $post['satuan'][$i]           ?? '',
                'expired_date'    => $post['expired_date'][$i]     ?? '',
                'no_lot'          => $post['no_lot'][$i]           ?? null,
                'pajak'           => $pajak,
                'hrg_satuan'      => $hrg,
                'hrg_pokok'       => $hrg_pk,
                'total_harga'     => $total_tax,
                'tonase_satuan'   => (float)($post['tonase_satuan'][$i]   ?? 0),
                'kubikasi_satuan' => (float)($post['kubikasi_satuan'][$i] ?? 0),
                'kode_akun'       => $post['kode_akun'][$i]        ?? null,
                'is_nego'         => $is_nego,
                'create_by'       => $this->session->userdata('username'),
            ];
        }
        return $details;
    }
}