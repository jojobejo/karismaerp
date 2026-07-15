<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 */
class C_Stocknonkomersil extends CI_Controller

{

    function __construct()
    {
        parent::__construct();
        $this->load->model('stock/M_Stocknonkomersil');
        $this->load->model('PO/M_Postatus');
        $this->load->model('PO/M_Purchase');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $lokasi = trim((string)$this->input->get('lokasi', true));

        $data['title'] = 'List Stock Non Komersil';
        $data['selected_lokasi'] = $lokasi;
        $data['lokasi_option'] = $this->M_Stocknonkomersil->get_master_lokasi();
        $data['lokasi_option_modal'] = $data['lokasi_option'];
        $data['stocknk'] = array();

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/stock/nonkomersil/body.php', $data);
        $this->load->view('partial/footer');
        $this->load->view('content/stock/nonkomersil/datatables');
    }

    public function list_stock_non_komersil_po()
    {
        $data['title'] = 'list stock tersedia';
        $data['stocknk'] = $this->M_Stocknonkomersil->getallbarang()->result();
        $data['satuan'] = $this->M_Stocknonkomersil->getSatuan();

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content\stock\nonkomersil\list_stock_po_nonkomersil.php', $data);
        $this->load->view('partial/footer');
        $this->load->view('content/stock/nonkomersil/datatables');
    }

    public function tmp_add_barang_komersil()
    {
        $kdbarang   = $this->input->post('kd_isi');
        $namabarang = $this->input->post('nama_isi');
        $descbarang  = $this->input->post('desc_isi');
        $ketbarang  = $this->input->post('ket_isi');
        $qtybarang  = $this->input->post('qty_isi');
        $hrgsatuan  = $this->input->post('hrg_isi');
        $kduser     = $this->session->userdata('kode');
        $totalharga = $qtybarang * $hrgsatuan;

        $dataBarang = array(
            'nama_barang'   => $namabarang,
            'deskripsi'     => $descbarang,
            'keterangan'    => $ketbarang,
            'qty'           => $qtybarang,
            'hrg_satuan'    => $hrgsatuan,
            'total_harga'   => $totalharga,
            'kd_barang'     => $kdbarang,
            'kd_user'       => $kduser,
            'gbr_produk'    => 'Karisma.png'
        );
        $kdgenerate = array(
            'kd_barang' => $kdbarang
        );

        $this->M_Purchase->generatekd($kdgenerate);
        $this->M_Purchase->input_tmp_nk($dataBarang);

        redirect('pononkomersil');
    }
    public function detailtransaksi($kdbarang)
    {
        $data['title']      = 'Detail Stock Barang';
        $data['kdgenerate'] = $this->M_Stocknonkomersil->getgeneratekd();
        $data['item']       = $this->M_Stocknonkomersil->get_data_item($kdbarang)->result();
        $data['stock']      = $this->M_Stocknonkomersil->get_detail_transaksi_itm($kdbarang)->result();
        $data['note']       = $this->M_Stocknonkomersil->get_note($kdbarang);
        $data['trash']      = $this->M_Stocknonkomersil->get_data_trash($kdbarang)->result();

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/stock/nonkomersil/detail_itm_tr.php', $data);
        $this->load->view('partial/footer');
        $this->load->view('content/stock/nonkomersil/datatables');
    }

    public function trash_transaksi($key, $id)
    {
        $action = $key;

        switch ($action) {
                // 1 = delete transaksi
                // 2 = redo transaksi
            case '1':
                $item_tr = $this->M_Stocknonkomersil->get_data_itemtr($id)->result();
                if ($item_tr) {
                    foreach ($item_tr as $t) {
                        $trashdata = array(
                            'kd_akun'           => $t->kd_akun,
                            'kd_po_nk'          => $t->kd_po_nk,
                            'kd_barang'         => $t->kd_barang,
                            'kd_barangsys'      => $t->kd_barangsys,
                            'keterangan'        => $t->keterangan,
                            'kat_barang'        => $t->kat_barang,
                            'tr_qty'            => $t->tr_qty,
                            'satuan'            => $t->satuan,
                            'inputer'           => $t->inputer,
                            'req_by'            => $t->req_by,
                            'tgl_transaksi'     => $t->tgl_transaksi,
                            'create_at'         => $t->create_at,
                            'last_updated_by'   => $this->session->userdata('kode')
                        );
                        $this->M_Stocknonkomersil->insert_trash_bin_tr($trashdata);
                        $this->M_Stocknonkomersil->deldettransaksi($id);
                        redirect('detailtransaksi/' . $t->kd_barang);
                    }
                }
                break;
            case '2':
                $item_trs = $this->M_Stocknonkomersil->get_data_trashid($id)->result();
                if ($item_trs) {
                    foreach ($item_trs as $t) {
                        $trashdata = array(
                            'kd_akun'           => $t->kd_akun,
                            'kd_po_nk'          => $t->kd_po_nk,
                            'kd_barang'         => $t->kd_barang,
                            'kd_barangsys'      => $t->kd_barangsys,
                            'keterangan'        => $t->keterangan,
                            'kat_barang'        => $t->kat_barang,
                            'tr_qty'            => $t->tr_qty,
                            'satuan'            => $t->satuan,
                            'inputer'           => $t->inputer,
                            'req_by'            => $t->req_by,
                            'tgl_transaksi'     => $t->tgl_transaksi,
                            'create_at'         => $t->create_at,
                            'last_updated_by'   => $this->session->userdata('kode')
                        );
                        $this->M_Stocknonkomersil->insttransaksi($trashdata);
                        $this->M_Stocknonkomersil->delete_trash($id);
                        redirect('detailtransaksi/' . $t->kd_barang);
                    }
                }
                break;
        }
    }

    public function revisitr($akun, $kdpo, $kdbr)
    {
        $data['title']      = 'Revisi Qty Persedian';
        if ($akun == '11511') {
            $data['stockdet']   = $this->M_Stocknonkomersil->get_detail_br_rev_buy($kdpo, $kdbr)->result();
        } else {
            $data['stockdet']   = $this->M_Stocknonkomersil->get_detail_br_rev_req($kdpo, $kdbr)->result();
        }


        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/stock/nonkomersil/revisitr', $data);
        $this->load->view('partial/footer');
        $this->load->view('content/stock/nonkomersil/datatables');
    }
    public function adjustmenqty()
    {
        date_default_timezone_set("Asia/Jakarta");
        $adjustmentkd   = $this->input->post('adjustmentkd');
        $kdbrsistem     = $this->input->post('kdbrsistem');
        $kdbarang       = $this->input->post('kdbarang');
        $katbarang      = $this->input->post('katbarang');
        $kdakun         = $this->input->post('kdakun');
        $satuanid       = $this->input->post('satuanid');
        $adjqty         = $this->input->post('adjqty');
        $ket            = $this->input->post('ket_isi');
        $now            = date('Y-m-d H:i:s');
        $now1           = date('Y-m-d');

        $generatekd         = array(
            'kd_barang'     => $adjustmentkd
        );

        $insrtadjustment    = array(
            'kd_akun'           => $kdakun,
            'kd_po_nk'          => $adjustmentkd,
            'kd_barang'         => $kdbrsistem,
            'kd_barangsys'      => $kdbarang,
            'keterangan'        => $ket,
            'kat_barang'        => $katbarang,
            'tr_qty'            => $adjqty,
            'satuan'            => $satuanid,
            'inputer'           => $this->session->userdata('kode'),
            'req_by'            => '-',
            'tgl_transaksi'     => $now1,
            'create_at'         => $now,
            'last_updated_by'   => $this->session->userdata('kode'),
            'update_at'         => $now
        );

        $this->M_Stocknonkomersil->generatekd($generatekd);
        $this->M_Stocknonkomersil->insttransaksi($insrtadjustment);

        if ($kdakun == '11513') {
            $inputnote = array(
                'kd_po'         => $kdbrsistem,
                'isi_note'      => 'ADJUSTMENT PENAMBAHAN QTY - ' . $ket,
                'kd_user'       => $this->session->userdata('kode'),
                'nama_user'     => $this->session->userdata('nama_user'),
                'note_for'      => '3',
                'update_status' => '3',
                'create_at'     => $now
            );
            $this->M_Stocknonkomersil->insrt_note($inputnote);
            redirect('detailtransaksi/' . $kdbrsistem);
        } else if ($kdakun == '11514') {
            $inputnote = array(
                'kd_po'         => $kdbrsistem,
                'isi_note'      => 'ADJUSTMENT PENGURANGAN QTY - ' . $ket,
                'kd_user'       => $this->session->userdata('kode'),
                'nama_user'     => $this->session->userdata('nama_user'),
                'note_for'      => '3',
                'update_status' => '3',
                'create_at'     => $now
            );
            $this->M_Stocknonkomersil->insrt_note($inputnote);
            redirect('detailtransaksi/' . $kdbrsistem);
        }
    }
    public function nkrestok()
    {
        $data['title']      = 'List Stock Non Komersil';
        $data['vstock']     = $this->M_Stocknonkomersil->v_stockzero()->result();
        $data['draftpo']    = $this->M_Stocknonkomersil->draftpo()->result();

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/stock/nonkomersil/listnostock.php', $data);
        $this->load->view('partial/footer');
        $this->load->view('content/stock/nonkomersil/datatables');
    }
    public function indraftrestock()
    {
        $kduser     = $this->session->userdata('kode');
        $kdbsys     = $this->input->post('kdbys');
        $kdbr       = $this->input->post('kdbr');
        $katbarang  = $this->input->post('katbr');
        $nmbarang   = $this->input->post('nm_barang');
        $descnk     = $this->input->post('descnk_isi');
        $ket        = $this->input->post('ket_isi');
        $qty        = $this->input->post('qty_isi');
        $satuan     = $this->input->post('satqty');
        $hrgsatuan  = $this->input->post('hrgsat');

        $hrgtot     = $qty * $hrgsatuan;

        $inputdt    = array(
            'jnis_po'       => '3',
            'nama_barang'   => $nmbarang,
            'deskripsi'     => $descnk,
            'keterangan'    => $ket,
            'qty'           => $qty,
            'satuan'        => $satuan,
            'hrg_satuan'    => $hrgsatuan,
            'total_harga'   => $hrgtot,
            'kd_bsys'       => $kdbsys,
            'kd_barang'     => $kdbr,
            'kat_barang'    => $katbarang,
            'kd_user'       => $kduser
        );
        $this->M_Stocknonkomersil->inputtmprestock($inputdt);
        redirect('nkrestok');
    }

    public function filterqtybytgl()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $kdbarang = $this->input->post('kdbarang');

        $data['title']      = 'Detail Stock Barang By Tanggal';
        $data['start_date'] = $start_date;
        $data['end_date']   = $end_date;

        $data['item']       = $this->M_Stocknonkomersil->get_item_bytgl($start_date, $end_date, $kdbarang)->result();
        $data['note']       = $this->M_Stocknonkomersil->get_note($kdbarang);
        $data['stock']      = $this->M_Stocknonkomersil->get_detail_transaksi_itm_date($start_date, $end_date, $kdbarang)->result();

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/stock/nonkomersil/stock_detailitm', $data);
        $this->load->view('partial/footer');
        $this->load->view('content/stock/nonkomersil/datatables');
    }
    public function tr_allstock()
    {
        $data['title']      = 'List Transaksi Allstock';

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/stock/nonkomersil/histori_stock_all_ponk', $data);
        $this->load->view('partial/footer');
    }

    public function fetch_tracking_data()
    {
        $tgl1 = $this->input->get('tanggal_1');
        $tgl2 = $this->input->get('tanggal_2');

        $data = $this->M_Stocknonkomersil->get_filtered_data($tgl1, $tgl2);
        echo json_encode($data);
    }

    public function master_lokasi()
    {
        $data['title'] = 'Master Lokasi';
        $data['lokasi'] = $this->M_Stocknonkomersil->get_master_lokasi();

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/stock/nonkomersil/master_lokasi', $data);
        $this->load->view('partial/footer');
        $this->load->view('content/stock/nonkomersil/datatables');
    }

    public function ajax_stocknonkomersil()
    {
        $lokasi = trim((string)$this->input->get('lokasi', true));
        $status_stock = trim((string)$this->input->get('status_stock', true));
        $draw = $this->input->get('draw');

        if ($draw !== null) {
            $order = $this->input->get('order');
            $search = $this->input->get('search');
            $length = (int)$this->input->get('length');

            $params = [
                'lokasi' => $lokasi,
                'status_stock' => $status_stock,
                'search' => is_array($search) && isset($search['value']) ? trim((string)$search['value']) : '',
                'start' => max(0, (int)$this->input->get('start')),
                'length' => ($length > 0 && $length <= 100) ? $length : 10,
                'order_column' => is_array($order) && isset($order[0]['column']) ? (int)$order[0]['column'] : 0,
                'order_dir' => is_array($order) && isset($order[0]['dir']) ? (string)$order[0]['dir'] : 'asc'
            ];

            $result = $this->M_Stocknonkomersil->get_stock_datatable($params);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => (int)$draw,
                    'recordsTotal' => $result['records_total'],
                    'recordsFiltered' => $result['records_filtered'],
                    'data' => $result['data']
                ]));
            return;
        }

        $stock = $this->M_Stocknonkomersil->v_stock($lokasi, $status_stock);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $stock
            ]));
    }

    public function update_minimum_stock()
    {
        $level = (string)$this->session->userdata('lv');
        if ($level !== '1' && $level !== '2') {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false]));
            return;
        }

        $kode_barang = trim((string)$this->input->post('kode_barang', true));
        $minimum_stock = (float)$this->input->post('minimum_stock');

        $updated = false;
        if ($kode_barang !== '' && $minimum_stock >= 0) {
            $updated = $this->M_Stocknonkomersil->update_minimum_stock($kode_barang, $minimum_stock);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => (bool)$updated
            ]));
    }

    public function update_lokasi_barang()
    {
        $kode_barang = trim((string)$this->input->post('kode_barang', true));
        $id_lokasi = (int)$this->input->post('id_lokasi');

        $updated = false;
        if ($kode_barang !== '' && $id_lokasi > 0) {
            $updated = $this->M_Stocknonkomersil->update_lokasi_barang($kode_barang, $id_lokasi);
        }

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => (bool)$updated
                ]));
            return;
        }

        redirect('stocknonkomersil');
    }

    public function add_master_lokasi()
    {
        $nama_lokasi = trim($this->input->post('nama_lokasi'));
        if ($nama_lokasi !== '') {
            $this->M_Stocknonkomersil->add_master_lokasi([
                'nama_lokasi' => $nama_lokasi
            ]);
        }

        redirect('master_lokasi');
    }

    public function edit_master_lokasi()
    {
        $id_lokasi = $this->input->post('id_lokasi');
        $nama_lokasi = trim($this->input->post('nama_lokasi'));

        if ($id_lokasi && $nama_lokasi !== '') {
            $this->M_Stocknonkomersil->update_master_lokasi($id_lokasi, [
                'nama_lokasi' => $nama_lokasi
            ]);
        }

        redirect('master_lokasi');
    }

    public function hapus_master_lokasi($id)
    {
        $this->M_Stocknonkomersil->delete_master_lokasi($id);
        redirect('master_lokasi');
    }
}
