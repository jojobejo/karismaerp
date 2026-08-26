<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_SalesOrderLoby
 * Modul Sales Order Khusus Loby (Direct Cash Sales / Walk-in Customer).
 *
 * Alur:
 * Buat SO Loby (CASH) -> Faktur Langsung (selesai_do) -> Print Faktur -> Terintegrasi Keuangan (/keuangan/pembayaran)
 */
class C_SalesOrderLoby extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_SalesOrderLoby');
        $this->load->model('M_Stock');
        $this->load->model('M_Logistik');
        $this->load->model('M_ActivityLog');
        $this->load->library(['form_validation', 'session', 'pagination']);
        $this->load->helper(['url', 'form']);
    }

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
            return ['nm_karyawan' => $nama, 'wilayah' => $wil, 'username' => $usn ?? $nama, 'id' => $id];
        }

        $row = null;
        if (!empty($id))  $row = $this->db->get_where('tb_karyawan', ['id'       => $id])->row_array();
        if (!$row && !empty($usn)) $row = $this->db->get_where('tb_karyawan', ['username' => $usn])->row_array();

        if ($row) {
            return [
                'id'          => $row['id']          ?? $id,
                'nm_karyawan' => $row['nm_karyawan'] ?? ($nama ?? 'system'),
                'wilayah'     => $row['wilayah']     ?? ($wil ?? ''),
                'username'    => $row['username']    ?? ($usn ?? 'system'),
            ];
        }
        return ['id' => $id, 'nm_karyawan' => $nama ?? 'system', 'wilayah' => $wil ?? '', 'username' => $usn ?? 'system'];
    }

    private function _getUsername()
    {
        return $this->_getCurrentUser()['nm_karyawan'];
    }

    // ================================================================
    // LIST SALES ORDER LOBY
    // ================================================================

    public function index()
    {
        $filter = [
            'date_start'  => $this->input->get('date_start', true),
            'date_end'    => $this->input->get('date_end', true),
            'status'      => $this->input->get('status', true),
            'kd_customer' => $this->input->get('kd_customer', true),
            'keyword'     => $this->input->get('keyword', true),
        ];

        $data['page_title'] = 'Sales Order Loby';
        $data['filter']     = $filter;
        $data['so_list']    = $this->M_SalesOrderLoby->get_all_so_loby($filter);
        $data['customers']  = $this->M_SalesOrderLoby->get_customers();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_loby_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // FORM BUAT SO LOBY BARU
    // ================================================================

    public function create()
    {
        $data['page_title']     = 'Buat Sales Order Loby';
        $data['no_so']          = $this->M_SalesOrderLoby->generate_no_so();
        $data['customers']      = $this->M_SalesOrderLoby->get_customers();
        $data['gudang_list']    = $this->M_SalesOrderLoby->get_gudang_list();
        $data['taxes']          = $this->M_SalesOrderLoby->get_tax_list();
        $data['batas_tonase']   = 7;
        $data['batas_kubikasi'] = 9;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_loby_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // SIMPAN SO LOBY
    // ================================================================

    public function store()
    {
        $no_so         = trim((string)$this->input->post('no_so', true));
        $tanggal       = trim((string)$this->input->post('tanggal', true));
        $kd_customer   = trim((string)$this->input->post('customer_id', true));
        $customer_name = trim((string)$this->input->post('customer_name', true));
        $gudang_id     = trim((string)$this->input->post('gudang_id', true));
        $catatan       = trim((string)$this->input->post('catatan', true));
        $cara_bayar    = 'cash'; // Wajib CASH untuk LOBY

        if (empty($no_so) || empty($tanggal) || empty($kd_customer) || empty($gudang_id)) {
            $this->session->set_flashdata('error', 'Lengkapi seluruh data wajib (No SO, Tanggal, Customer, Gudang).');
            redirect('sales_order_loby/create');
            return;
        }

        $items = $this->input->post('items', true);
        if (empty($items) || !is_array($items)) {
            $this->session->set_flashdata('error', 'Daftar barang tidak boleh kosong.');
            redirect('sales_order_loby/create');
            return;
        }

        $details = [];
        $total_tonase = 0;
        $total_kubikasi = 0;

        foreach ($items as $idx => $row) {
            $kd_barang = trim((string)($row['kd_barang'] ?? ''));
            $qty       = (float)($row['qty'] ?? 0);
            if (empty($kd_barang) || $qty <= 0) {
                continue;
            }

            $exp_date = trim((string)($row['expired_date'] ?? ''));
            $no_lot   = trim((string)($row['no_lot'] ?? ''));

            $hrg_satuan          = (float)($row['hrg_satuan'] ?? 0);
            $hrg_pokok           = (float)($row['hrg_pokok'] ?? 0);
            $disc                = (float)($row['disc'] ?? 0);
            $pajak               = (float)($row['pajak'] ?? 0);
            $subtotal_before     = $qty * $hrg_satuan;
            $subtotal_after      = $subtotal_before * (1 - ($disc / 100));
            $total_harga         = $subtotal_after;
            $berat_gram          = (float)($row['berat_gram'] ?? 0);
            $kubikasi_m3         = (float)($row['kubikasi_m3'] ?? 0);
            $isi_per_box         = max(1, (int)($row['isi_per_box'] ?? 1));

            $total_tonase   += $qty * ($berat_gram / 1000000);
            $total_kubikasi += $qty * $kubikasi_m3;

            $details[] = [
                'produk_id'            => $kd_barang,
                'kd_barang'            => $kd_barang,
                'nama_barang'          => $row['nama_barang'] ?? '',
                'qty'                  => $qty,
                'qty_box'              => floor($qty / $isi_per_box),
                'qty_satuan'           => fmod($qty, $isi_per_box),
                'isi_per_box'          => $isi_per_box,
                'satuan'               => $row['satuan'] ?? 'PCS',
                'expired_date'         => $exp_date,
                'no_lot'               => $no_lot ?: null,
                'hrg_satuan'           => $hrg_satuan,
                'hrg_pokok'            => $hrg_pokok,
                'disc'                 => $disc,
                'pajak'                => $pajak,
                'subtotal_before_disc' => $subtotal_before,
                'subtotal_after_disc'  => $subtotal_after,
                'total_harga'          => $total_harga,
                'berat_gram'           => $berat_gram,
                'kubikasi_m3'          => $kubikasi_m3,
                'create_by'            => $this->_getUsername(),
                'create_at'            => date('Y-m-d H:i:s'),
            ];
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal harus ada 1 barang valid dengan Qty > 0.');
            redirect('sales_order_loby/create');
            return;
        }

        $header = [
            'no_so'             => $no_so,
            'tanggal_transaksi' => $tanggal,
            'kd_customer'       => $kd_customer,
            'customer_name'     => $customer_name,
            'gudang_id'         => $gudang_id,
            'batas_tonase'      => 7,
            'batas_kubikasi'    => 9,
            'total_tonase'      => round($total_tonase, 6),
            'total_kubikasi'    => round($total_kubikasi, 6),
            'catatan'           => $catatan,
            'create_by'         => $this->_getUsername(),
        ];

        $id_so = $this->M_SalesOrderLoby->simpan_so($header, $details);

        if ($id_so) {
            $this->session->set_flashdata('success', 'Sales Order Loby (' . $no_so . ') berhasil disimpan. Anda dapat langsung memproses Faktur Penjualan.');
            redirect('sales_order_loby/detail/' . $id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan SO Loby. Periksa kecukupan stok atau batch barang.');
            redirect('sales_order_loby/create');
        }
    }

    // ================================================================
    // DETAIL SO LOBY
    // ================================================================

    public function detail($id_so)
    {
        $so = $this->M_SalesOrderLoby->get_so($id_so);
        if (!$so) {
            show_404();
            return;
        }

        $data['page_title'] = 'Detail Sales Order Loby - ' . $so['no_so'];
        $data['so']         = $so;
        $data['details']    = $this->M_SalesOrderLoby->get_so_detail($id_so);
        $data['fakturs']    = $this->M_SalesOrderLoby->get_faktur_by_so($id_so);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_loby_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // EDIT & UPDATE SO LOBY
    // ================================================================

    public function edit($id_so)
    {
        $so = $this->M_SalesOrderLoby->get_so($id_so);
        if (!$so) {
            show_404();
            return;
        }

        if ($so['status'] === 'completed') {
            $this->session->set_flashdata('error', 'SO Loby yang sudah difakturkan tidak dapat diedit.');
            redirect('sales_order_loby/detail/' . $id_so);
            return;
        }

        $data['page_title']     = 'Edit Sales Order Loby - ' . $so['no_so'];
        $data['so']             = $so;
        $data['details']        = $this->M_SalesOrderLoby->get_so_detail($id_so);
        $data['customers']      = $this->M_SalesOrderLoby->get_customers();
        $data['gudang_list']    = $this->M_SalesOrderLoby->get_gudang_list();
        $data['taxes']          = $this->M_SalesOrderLoby->get_tax_list();
        $data['batas_tonase']   = 7;
        $data['batas_kubikasi'] = 9;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_loby_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id_so)
    {
        $so = $this->M_SalesOrderLoby->get_so($id_so);
        if (!$so || $so['status'] === 'completed') {
            $this->session->set_flashdata('error', 'SO Loby tidak dapat diubah.');
            redirect('sales_order_loby');
            return;
        }

        $tanggal       = trim((string)$this->input->post('tanggal', true));
        $kd_customer   = trim((string)$this->input->post('customer_id', true));
        $customer_name = trim((string)$this->input->post('customer_name', true));
        $gudang_id     = trim((string)$this->input->post('gudang_id', true));
        $catatan       = trim((string)$this->input->post('catatan', true));

        $items = $this->input->post('items', true);
        if (empty($items) || !is_array($items)) {
            $this->session->set_flashdata('error', 'Daftar barang tidak boleh kosong.');
            redirect('sales_order_loby/edit/' . $id_so);
            return;
        }

        $details = [];
        $total_tonase = 0;
        $total_kubikasi = 0;

        foreach ($items as $idx => $row) {
            $kd_barang = trim((string)($row['kd_barang'] ?? ''));
            $qty       = (float)($row['qty'] ?? 0);
            if (empty($kd_barang) || $qty <= 0) {
                continue;
            }

            $exp_date = trim((string)($row['expired_date'] ?? ''));
            $no_lot   = trim((string)($row['no_lot'] ?? ''));

            $hrg_satuan          = (float)($row['hrg_satuan'] ?? 0);
            $hrg_pokok           = (float)($row['hrg_pokok'] ?? 0);
            $disc                = (float)($row['disc'] ?? 0);
            $pajak               = (float)($row['pajak'] ?? 0);
            $subtotal_before     = $qty * $hrg_satuan;
            $subtotal_after      = $subtotal_before * (1 - ($disc / 100));
            $total_harga         = $subtotal_after;
            $berat_gram          = (float)($row['berat_gram'] ?? 0);
            $kubikasi_m3         = (float)($row['kubikasi_m3'] ?? 0);
            $isi_per_box         = max(1, (int)($row['isi_per_box'] ?? 1));

            $total_tonase   += $qty * ($berat_gram / 1000000);
            $total_kubikasi += $qty * $kubikasi_m3;

            $details[] = [
                'produk_id'            => $kd_barang,
                'kd_barang'            => $kd_barang,
                'nama_barang'          => $row['nama_barang'] ?? '',
                'qty'                  => $qty,
                'qty_box'              => floor($qty / $isi_per_box),
                'qty_satuan'           => fmod($qty, $isi_per_box),
                'isi_per_box'          => $isi_per_box,
                'satuan'               => $row['satuan'] ?? 'PCS',
                'expired_date'         => $exp_date,
                'no_lot'               => $no_lot ?: null,
                'hrg_satuan'           => $hrg_satuan,
                'hrg_pokok'            => $hrg_pokok,
                'disc'                 => $disc,
                'pajak'                => $pajak,
                'subtotal_before_disc' => $subtotal_before,
                'subtotal_after_disc'  => $subtotal_after,
                'total_harga'          => $total_harga,
                'berat_gram'           => $berat_gram,
                'kubikasi_m3'          => $kubikasi_m3,
                'create_by'            => $this->_getUsername(),
                'create_at'            => date('Y-m-d H:i:s'),
            ];
        }

        $header = [
            'tanggal_transaksi' => $tanggal,
            'kd_customer'       => $kd_customer,
            'customer_name'     => $customer_name,
            'gudang_id'         => $gudang_id,
            'total_tonase'      => round($total_tonase, 6),
            'total_kubikasi'    => round($total_kubikasi, 6),
            'catatan'           => $catatan,
            'update_by'         => $this->_getUsername(),
        ];

        $res = $this->M_SalesOrderLoby->update_so($id_so, $header, $details);

        if ($res) {
            $this->session->set_flashdata('success', 'Sales Order Loby berhasil diperbarui.');
            redirect('sales_order_loby/detail/' . $id_so);
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui SO Loby.');
            redirect('sales_order_loby/edit/' . $id_so);
        }
    }

    public function cancel($id_so)
    {
        $res = $this->M_SalesOrderLoby->cancel_so($id_so, $this->_getUsername());
        if ($res) {
            $this->session->set_flashdata('success', 'Sales Order Loby berhasil dibatalkan dan reservasi stok dilepas.');
        } else {
            $this->session->set_flashdata('error', 'Gagal membatalkan SO Loby.');
        }
        redirect('sales_order_loby');
    }

    // ================================================================
    // PROSES FAKTUR PENJUALAN LOBY
    // ================================================================

    public function form_faktur($id_so)
    {
        $so = $this->M_SalesOrderLoby->get_so($id_so);
        if (!$so) {
            show_404();
            return;
        }

        if ($so['status'] === 'completed') {
            $this->session->set_flashdata('info', 'SO Loby ini sudah difakturkan.');
            redirect('sales_order_loby/detail/' . $id_so);
            return;
        }

        $data['page_title'] = 'Proses Faktur Penjualan Loby - ' . $so['no_so'];
        $data['so']         = $so;
        $data['details']    = $this->M_SalesOrderLoby->get_so_detail($id_so);
        $data['no_faktur']  = $this->M_SalesOrderLoby->generate_no_faktur('LBY');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/so_loby_faktur_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan_faktur($id_so)
    {
        $so = $this->M_SalesOrderLoby->get_so($id_so);
        if (!$so || $so['status'] === 'completed') {
            $this->session->set_flashdata('error', 'SO Loby tidak dapat difakturkan.');
            redirect('sales_order_loby');
            return;
        }

        $no_faktur  = trim((string)$this->input->post('no_faktur', true));
        $tgl_faktur = trim((string)$this->input->post('tanggal_faktur', true)) ?: date('Y-m-d');
        $catatan    = trim((string)$this->input->post('catatan', true)) ?: 'Penjualan Langsung Loby';

        if (empty($no_faktur)) {
            $no_faktur = $this->M_SalesOrderLoby->generate_no_faktur('LBY');
        }

        $so_details = $this->M_SalesOrderLoby->get_so_detail($id_so);
        if (empty($so_details)) {
            $this->session->set_flashdata('error', 'Item SO Loby tidak ditemukan.');
            redirect('sales_order_loby/detail/' . $id_so);
            return;
        }

        $faktur_header = [
            'no_faktur'      => $no_faktur,
            'tanggal_faktur' => $tgl_faktur,
            'catatan'        => $catatan,
            'create_by'      => $this->_getUsername(),
            'created_by_id'  => $this->_getCurrentUser()['id'] ?? null,
        ];

        $faktur_items = [];
        foreach ($so_details as $sd) {
            $faktur_items[] = [
                'id_so_detail'         => $sd['id_so_detail'],
                'kd_barang'            => $sd['kd_barang'],
                'nama_barang'          => $sd['nama_barang'],
                'no_lot'               => $sd['no_lot'],
                'expired_date'         => $sd['expired_date'],
                'qty'                  => $sd['qty'],
                'qty_box'              => $sd['qty_box'],
                'qty_satuan'           => $sd['qty_satuan'],
                'isi_per_box'          => $sd['isi_per_box'],
                'satuan'               => $sd['satuan'],
                'hrg_satuan'           => $sd['hrg_satuan'],
                'hrg_pokok'            => $sd['hrg_pokok'],
                'disc'                 => $sd['disc'],
                'pajak'                => $sd['pajak'],
                'subtotal_before_disc' => $sd['subtotal_before_disc'],
                'subtotal_after_disc'  => $sd['subtotal_after_disc'],
                'total_harga'          => $sd['total_harga'],
                'berat_gram'           => $sd['berat_gram'],
                'kubikasi_m3'          => $sd['kubikasi_m3'],
            ];
        }

        $res = $this->M_SalesOrderLoby->buat_faktur_loby($id_so, $faktur_header, $faktur_items);

        if (!empty($res['errors'])) {
            $this->session->set_flashdata('error', implode('<br>', $res['errors']));
            redirect('sales_order_loby/form_faktur/' . $id_so);
            return;
        }

        if ($res && !empty($res['id_faktur'])) {
            $this->session->set_flashdata('success', 'Faktur Penjualan Loby (' . $no_faktur . ') berhasil dibuat, stok fisik telah berkurang, jurnal terbentuk, dan transaksi telah masuk ke modul Keuangan.');
            redirect('sales_order_loby/detail_faktur/' . $res['id_faktur']);
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses Faktur Penjualan Loby.');
            redirect('sales_order_loby/form_faktur/' . $id_so);
        }
    }

    // ================================================================
    // DETAIL & PRINT FAKTUR PENJUALAN LOBY
    // ================================================================

    public function detail_faktur($id_faktur)
    {
        $faktur = $this->M_SalesOrderLoby->get_faktur($id_faktur);
        if (!$faktur) {
            show_404();
            return;
        }

        $data['page_title'] = 'Detail Faktur Penjualan Loby - ' . $faktur['no_faktur'];
        $data['faktur']     = $faktur;
        $data['details']    = $this->M_SalesOrderLoby->get_faktur_detail($id_faktur);
        $data['so']         = $this->M_SalesOrderLoby->get_so($faktur['id_so']);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function print_faktur($id_faktur)
    {
        $faktur = $this->M_SalesOrderLoby->get_faktur($id_faktur);
        if (!$faktur) {
            show_404();
            return;
        }

        $data['page_title'] = 'Cetak Faktur Penjualan - ' . $faktur['no_faktur'];
        $data['faktur']     = $faktur;
        $data['details']    = $this->M_SalesOrderLoby->get_faktur_detail($id_faktur);
        $data['so']         = $this->M_SalesOrderLoby->get_so($faktur['id_so']);

        $this->load->view('content/sales/so_loby_print_faktur.php', $data);
    }

    // ================================================================
    // AJAX LOOKUP STOK & BARANG
    // ================================================================

    public function get_stock()
    {
        $gudang_id = $this->input->get('gudang_id', true);
        $exclude_id_so = $this->input->get('exclude_id_so', true);

        $stocks = $this->M_SalesOrderLoby->get_available_stock_with_dimensi($gudang_id, null, $exclude_id_so);

        echo json_encode([
            'status' => 'success',
            'data'   => $stocks,
        ]);
    }

    public function get_barang()
    {
        $kd_barang = $this->input->get('kd_barang', true);
        $barang = $this->M_SalesOrderLoby->get_detail_barang($kd_barang);

        echo json_encode([
            'status' => $barang ? 'success' : 'error',
            'data'   => $barang,
        ]);
    }
}
