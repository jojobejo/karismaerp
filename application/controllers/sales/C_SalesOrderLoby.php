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

        // 1. Format array paralel (dari form SO modern)
        if (!empty($post['kd_barang']) && is_array($post['kd_barang'])) {
            foreach ($post['kd_barang'] as $i => $kd) {
                if (empty($kd)) continue;

                $hrg         = $this->_parse_number_input($post['hrg_satuan'][$i] ?? 0);
                $hrg_pk      = $this->_parse_number_input($post['hrg_pokok'][$i]   ?? 0);
                $qty_box     = $this->_parse_number_input($post['qty_box'][$i]      ?? 0);
                $qty_satuan  = $this->_parse_number_input($post['qty_satuan'][$i]   ?? 0);
                $isi_per_box = max(1, (int)($post['isi_per_box'][$i] ?? 1));
                $pajak       = $this->_parse_number_input($post['pajak'][$i]        ?? 0);
                $disc        = $this->_parse_number_input($post['disc'][$i]         ?? 0);
                $qty_kecil   = ($qty_box * $isi_per_box) + $qty_satuan;

                if ($qty_kecil <= 0) continue;

                $subtotal_before_disc = $hrg * $qty_kecil;
                $subtotal_after_disc  = $subtotal_before_disc * (1 - $disc / 100);
                $total_tax            = $subtotal_after_disc  * (1 + $pajak / 100);

                $details[] = [
                    'produk_id'            => $kd,
                    'kd_barang'            => $kd,
                    'nama_barang'          => $post['nama_barang'][$i]  ?? '',
                    'qty'                  => $qty_kecil,
                    'qty_box'              => $qty_box,
                    'qty_satuan'           => $qty_satuan,
                    'isi_per_box'          => $isi_per_box,
                    'satuan'               => $post['satuan'][$i]        ?? 'PCS',
                    'expired_date'         => $post['expired_date'][$i]  ?? '',
                    'no_lot'               => $post['no_lot'][$i]        ?? null,
                    'pajak'                => $pajak,
                    'disc'                 => $disc,
                    'subtotal_before_disc' => $subtotal_before_disc,
                    'subtotal_after_disc'  => $subtotal_after_disc,
                    'hrg_satuan'           => $hrg,
                    'hrg_pokok'            => $hrg_pk,
                    'total_harga'          => $total_tax,
                    'berat_gram'           => $this->_parse_number_input($post['berat_gram'][$i]  ?? 0),
                    'kubikasi_m3'          => $this->_parse_number_input($post['kubikasi_m3'][$i] ?? 0),
                    'create_by'            => $this->_getUsername(),
                    'create_at'            => date('Y-m-d H:i:s'),
                ];
            }
            return $details;
        }

        // 2. Format items associative (fallback)
        if (!empty($post['items']) && is_array($post['items'])) {
            foreach ($post['items'] as $row) {
                $kd = trim((string)($row['kd_barang'] ?? ''));
                $qty = (float)($row['qty'] ?? 0);
                if (empty($kd) || $qty <= 0) continue;

                $hrg         = (float)($row['hrg_satuan'] ?? 0);
                $hrg_pk      = (float)($row['hrg_pokok'] ?? 0);
                $disc        = (float)($row['disc'] ?? 0);
                $pajak       = (float)($row['pajak'] ?? 0);
                $isi_per_box = max(1, (int)($row['isi_per_box'] ?? 1));
                $sub_b       = $qty * $hrg;
                $sub_a       = $sub_b * (1 - ($disc / 100));

                $details[] = [
                    'produk_id'            => $kd,
                    'kd_barang'            => $kd,
                    'nama_barang'          => $row['nama_barang'] ?? '',
                    'qty'                  => $qty,
                    'qty_box'              => floor($qty / $isi_per_box),
                    'qty_satuan'           => fmod($qty, $isi_per_box),
                    'isi_per_box'          => $isi_per_box,
                    'satuan'               => $row['satuan'] ?? 'PCS',
                    'expired_date'         => $row['expired_date'] ?? '',
                    'no_lot'               => $row['no_lot'] ?? null,
                    'hrg_satuan'           => $hrg,
                    'hrg_pokok'            => $hrg_pk,
                    'disc'                 => $disc,
                    'pajak'                => $pajak,
                    'subtotal_before_disc' => $sub_b,
                    'subtotal_after_disc'  => $sub_a,
                    'total_harga'          => $sub_a,
                    'berat_gram'           => (float)($row['berat_gram'] ?? 0),
                    'kubikasi_m3'          => (float)($row['kubikasi_m3'] ?? 0),
                    'create_by'            => $this->_getUsername(),
                    'create_at'            => date('Y-m-d H:i:s'),
                ];
            }
        }

        return $details;
    }

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

        $post    = $this->input->post(null, true);
        $details = $this->_parse_detail_post($post);

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal harus ada 1 item barang valid dengan Qty > 0.');
            redirect('sales_order_loby/create');
            return;
        }

        $total_tonase   = 0;
        $total_kubikasi = 0;
        foreach ($details as $d) {
            $total_tonase   += (float)$d['qty'] * ((float)($d['berat_gram'] ?? 0) / 1000000);
            $total_kubikasi += (float)$d['qty'] * (float)($d['kubikasi_m3'] ?? 0);
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
            'cara_pembayaran'   => 'cash',
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

        $post    = $this->input->post(null, true);
        $details = $this->_parse_detail_post($post);

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Daftar barang tidak boleh kosong.');
            redirect('sales_order_loby/edit/' . $id_so);
            return;
        }

        $total_tonase   = 0;
        $total_kubikasi = 0;
        foreach ($details as $d) {
            $total_tonase   += (float)$d['qty'] * ((float)($d['berat_gram'] ?? 0) / 1000000);
            $total_kubikasi += (float)$d['qty'] * (float)($d['kubikasi_m3'] ?? 0);
        }

        $header = [
            'tanggal_transaksi' => $tanggal,
            'kd_customer'       => $kd_customer,
            'customer_name'     => $customer_name,
            'gudang_id'         => $gudang_id,
            'total_tonase'      => round($total_tonase, 6),
            'total_kubikasi'    => round($total_kubikasi, 6),
            'catatan'           => $catatan,
            'cara_pembayaran'   => 'cash',
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

    public function delete($id_so)
    {
        $res = $this->M_SalesOrderLoby->delete_so($id_so, $this->_getUsername());
        if ($res['success']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('sales_order_loby');
    }

    public function unpost($id_so = null)
    {
        if (!$id_so) {
            $id_so = $this->input->post('id_so', true);
        }

        $res = $this->M_SalesOrderLoby->unpost_so($id_so, $this->_getUsername());

        if ($this->input->is_ajax_request()) {
            return $this->output->set_content_type('application/json')->set_output(json_encode($res));
        }

        if ($res['success']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
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
        $this->load->view('content/sales/so_loby_faktur_detail.php', $data);
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

    // ================================================================
    // DETAIL JURNAL AJAX (ZAHIR RIGHT-CLICK / POPUP)
    // ================================================================

    public function detail_jurnal_ajax($id_so)
    {
        $so = $this->M_SalesOrderLoby->get_so($id_so);
        if (!$so) {
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Data Sales Order Loby tidak ditemukan.'
            ]));
        }

        $fakturs = $this->M_SalesOrderLoby->get_faktur_by_so($id_so);
        $no_faktur = !empty($fakturs) ? $fakturs[0]['no_faktur'] : null;

        // Cari jurnal di tbkeu_jurnal
        $this->db->select('j.*');
        $this->db->from('tbkeu_jurnal j');
        $this->db->group_start();
        if ($no_faktur) {
            $this->db->where_in('j.idempotency_key', [
                'SALES_INVOICE-FAKTUR-' . $no_faktur,
                'GOODS_ISSUE-FAKTUR-' . $no_faktur
            ]);
            $this->db->or_where('j.source_no', $no_faktur);
            $this->db->or_where('j.source_id', $no_faktur);
        }
        $this->db->or_where('j.source_no', $so['no_so']);
        $this->db->or_where('j.source_id', $so['no_so']);
        $this->db->group_end();
        $this->db->order_by('j.id_jurnal', 'ASC');
        $journals = $this->db->get()->result_array();

        if (empty($journals)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Jurnal belum terposting atau transaksi SO Loby ini belum memiliki faktur penjualan.'
            ]));
        }

        $journal_ids = array_column($journals, 'id_jurnal');

        $this->db->select('d.*, a.kode_akun, a.nama_akun, j.nomor_jurnal, j.tanggal_transaksi, j.keterangan AS jurnal_keterangan, j.source_type');
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'left');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->db->where_in('d.id_jurnal', $journal_ids);
        $this->db->order_by('d.id_jurnal', 'ASC');
        $this->db->order_by('d.nomor_baris', 'ASC');
        $details = $this->db->get()->result_array();

        $main_header = $journals[0];

        // Format user
        $user_name = $so['create_by'] ?: ($main_header['created_by'] ?? '-');
        if (is_numeric($user_name)) {
            $karyawan = $this->db->select('nm_karyawan, username')->where('id', (int)$user_name)->get('tb_karyawan')->row_array();
            if ($karyawan) {
                $user_name = $karyawan['nm_karyawan'] ?: $karyawan['username'];
            }
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success' => true,
            'header'  => $main_header,
            'journals'=> $journals,
            'details' => $details,
            'user'    => $user_name
        ]));
    }
}
