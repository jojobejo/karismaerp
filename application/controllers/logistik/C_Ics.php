<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Ics extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Ics');
        $this->load->model('M_Logistik');
        $this->load->model('M_Keuangan');
        $this->load->helper('stock_helper');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        $data['page_title']     = 'KARISMA - LOGISTIK';
        $data['kdgenerate']     = $this->M_Keuangan->generate_update();
        $data['list_faktur']    = $this->M_Logistik->get_data_penjualan_zahir();
        $data['updated']        = $this->M_Logistik->get_updated_data_preparation();
        $data['listdo']         = $this->M_Logistik->getdo();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_by_expdate()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['barang_ics']         = $this->M_Ics->list_barang_ics_expdate();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_by_expdate.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_by_allbarang()
    {
        $data['page_title']         = 'KARISMA - ICS';
        $data['barang_ics']         = $this->M_Ics->list_barang_ics_allbarang();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_by_allbarang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_diffrent()
    {
        $data['page_title']         = 'KARISMA - ICS';
        $data['barang_ics']         = $this->M_Ics->list_barang_ics_diffrent();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_show_diff.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function stock_by_kodebr($kd)
    {
        $kdbarang = $this->M_Ics->getnmbarang($kd);
        $data_barang = $this->db
            ->select('a.nama_barang, a.exp_date')
            ->from('tb_saldo_awal a')
            ->join('tb_master_barang b', 'b.nm_barang = a.nama_barang')
            ->where('a.nama_barang', $kdbarang)
            ->get()
            ->row();

        if (!$data_barang) {
            show_404();
        }

        $nama_barang = $data_barang->nama_barang;
        $exp_date    = $data_barang->exp_date;

        $query = $this->db->query("SELECT
            a.id,
            b.kd_system,
            a.nama_barang,
            a.exp_date as exp_date,
            (b.p*b.l*b.t) AS dimensi,
            SUM(a.qty) AS qty_awal,
            COALESCE(pending.qty_pending, 0) AS DO,
            COALESCE(purchase.qty_po, 0) AS PO,
            COALESCE(opname.qty_opname, 0) AS ICS,
            (SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
            ((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0))- COALESCE(opname.qty_opname, 0) AS selisih,
            IF(((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0), 1, 0) AS status
            FROM tb_saldo_awal a
            JOIN tb_master_barang b ON b.nm_barang = a.nama_barang
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_pending
                FROM tb_ics_do
                GROUP BY nama_barang, exp_date
            ) pending ON pending.nama_barang = a.nama_barang AND pending.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_po
                FROM tb_ics_po
                GROUP BY nama_barang, exp_date
            ) purchase ON purchase.nama_barang = a.nama_barang AND purchase.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_opname
                FROM tb_ics_opname
                GROUP BY nama_barang, exp_date
            ) opname ON opname.nama_barang = a.nama_barang AND opname.exp_date = a.exp_date
            WHERE a.nama_barang = ? AND a.exp_date = ?
            GROUP BY a.nama_barang, a.exp_date", array($nama_barang, $exp_date));

        $data['detail_stok']        = $query->result();
        $data['page_title']         = 'KARISMA - ICS';
        $data['get_barang']         = $this->M_Ics->get_detail_barang($kd);
        $data['list_stock_by_exp']  = $this->M_Ics->tracking_br_diffrent_by_expdate($kdbarang);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/stock_by_kodebr.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function get_detail_by_exp()
    {
        $nama_barang = $this->input->post('nama_barang');
        $exp_date = $this->input->post('exp_date');

        $data_do = $this->db->select('kd_faktur, tgl_transaksi, qty')
            ->from('tb_ics_do')
            ->where('nama_barang', $nama_barang)
            ->where('exp_date', $exp_date)
            ->get()->result();

        $data_po = $this->db->select('kd_faktur_lpb, tgl_transaksi, qty')
            ->from('tb_ics_po')
            ->where('nama_barang', $nama_barang)
            ->where('exp_date', $exp_date)
            ->get()->result();

        $data_log = $this->db->select('inputer, tgl_input, qty')
            ->from('tb_log_ics')
            ->where('nama_barang', $nama_barang)
            ->where('exp_date', $exp_date)
            ->get()->result();

        echo json_encode([
            'data_do' => $data_do,
            'data_po' => $data_po,
            'data_log' => $data_log
        ]);
    }

    public function get_detail_by_exp_date()
    {
        $exp_date = $this->input->post('exp_date');
        $nama_barang = $this->input->post('nama_barang');
        $data = $this->M_Ics->get_exp_detail($nama_barang, $exp_date);
        echo json_encode($data);
    }

    public function master_barang()
    {
        $data['page_title']         = 'KARISMA - MASTER BARANG';
        $data['mbarang']            = $this->M_Ics->get_master_barang_ics();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/master_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function get_detail_mbarang()
    {
        $id = $this->input->post('id');
        $this->db->select('i.id,i.nm_barang,i.bhn_aktif,i.satuan,i.p,i.l,i.t,i.berat,i.kubikasi,i.qty_min,i.status');
        $this->db->from('tb_mbarang i');
        $this->db->where('i.id', $id);
        $query = $this->db->get()->row();

        echo json_encode($query);
    }

    // public function add_master_barang()
    // {
    //     $nmbarang       = $this->post('');
    //     $bhn_aktif      = $this->post('');
    //     $satuan         = $this->post('');
    //     $dimensi        = $this->post('');
    //     $tonase         = $this->post('');
    //     $kubikasi       = $this->post('');
    //     $qty_min        = $this->post('');

    //     $svbarang = array(
    //         'kd_system'     =>
    //         'nm_barang'     =>
    //         'bhn_aktif'     =>
    //         'satuan'        =>
    //         'p'             =>
    //         'l'             =>
    //         't'             =>
    //         'berat'         =>
    //         'kubikasi'      =>
    //         'qty_min'       =>
    //         'status'        =>
    //     );

    //     $this->M_Ics->insert_mbarang_ics($svbarang);
    //     redirect('ics/master_barang');
    // }

    public function update_inline()
    {
        $nama_barang = $this->input->post('nama_barang');
        $exp_date = $this->input->post('exp_date');
        $field = $this->input->post('field');
        $value = (int) $this->input->post('value');
        $datenow = date('d/m/Y');

        $barang = $this->db->get_where('tb_mbarang', ['nm_barang' => $nama_barang])->row();
        if (!$barang) {
            echo json_encode(['status' => 'failed', 'message' => 'Barang tidak ditemukan']);
            return;
        }

        $unit_size = $barang->p * $barang->l * $barang->t;

        $current = $this->db->get_where('tb_ics_opname', [
            'nama_barang' => $nama_barang,
            'exp_date' => $exp_date,
            'DATE(input_at)' => $datenow
        ])->row();

        if ($field == 'opname_box') {
            $new_qty = $value * $unit_size;

            if ($current) {
                $sisa_pcs = $current->qty % $unit_size;
                $this->db->set('qty', $new_qty + $sisa_pcs);
                $this->db->set('qty_box', $value);
                $this->db->where('id', $current->id);
                $this->db->update('tb_ics_opname');
            } else {
                $this->db->insert('tb_ics_opname', [
                    'nama_barang' => $nama_barang,
                    'exp_date' => $exp_date,
                    'qty' => $new_qty,
                    'qty_box' => $value,
                    'qty_pcs' => 0,
                    'inputer' => $this->session->userdata('nama'),
                    'tim' => '0',
                    'wilayah' => '-',
                    'input_at' => $datenow
                ]);
            }
        } elseif ($field == 'opname_pcs') {
            $new_qty = $value;

            if ($current) {
                $box_qty = floor($current->qty / $unit_size);
                $this->db->set('qty', ($box_qty * $unit_size) + $new_qty);
                $this->db->set('qty_pcs', $value);
                $this->db->where('id', $current->id);
                $this->db->update('tb_ics_opname');
            } else {
                $this->db->insert('tb_ics_opname', [
                    'nama_barang' => $nama_barang,
                    'exp_date' => $exp_date,
                    'qty' => $new_qty,
                    'qty_box' => 0,
                    'qty_pcs' => $value,
                    'inputer' => $this->session->userdata('nama'),
                    'tim' => '0',
                    'wilayah' => '-',
                    'input_at' => $datenow
                ]);
            }
        }
        echo json_encode(['status' => 'success']);
    }

    public function get_data()
    {
        $data = $this->M_Ics->list_barang_ics();
        echo json_encode($data);
    }

    public function inline_update()
    {
        $id    = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');

        if ($field === 'qty')  $value = (int)$value;

        $ok = $this->Stok_model->update_cell($id, $field, $value);
        echo json_encode(['success' => $ok]);
    }

    public function ics_do()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $tgl                        = date('d/m/Y');
        $data['tanggal_now']        = date('d/m/Y');
        $data['ics_do']             = $this->M_Ics->list_do_today($tgl);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/icsdo.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ics_po()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $tgl                        = date('d/m/Y');
        $data['tanggal_now']        = date('d/m/Y');
        $data['ics_po']             = $this->M_Ics->list_po_today($tgl);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/icspo.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan_opname()
    {
        $this->load->database();
        $today = date('d/m/Y');
        $this->db->set('input_at', $today);
        $this->db->update('tb_ics');
        $data_ics = $this->db->get('tb_ics')->result_array();
        if (empty($data_ics)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tb_ics kosong.']);
            return;
        }
        $insert_batch = [];
        foreach ($data_ics as $row) {
            $insert_batch[] = [
                'nama_barang' => $row['nama_barang'],
                'exp_date'    => $row['exp_date'],
                'qty'         => $row['qty'],
                'qty_box'     => $row['qty_box'],
                'qty_pcs'     => $row['qty_pcs'],
                'input_at'    => $today
            ];
        }

        $this->db->insert_batch('tb_ics_opname', $insert_batch);
        echo json_encode(['status' => 'success']);
    }

    public function ics_stock_controller($idbarang)
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['nmbarang']           = $this->M_Ics->get_br_name($idbarang);
        $data_barang = $this->db
            ->select('a.nama_barang, a.exp_date')
            ->from('tb_saldo_awal a')
            ->join('tb_mbarang b', 'b.nm_barang = a.nama_barang')
            ->where('a.id', $idbarang)
            ->get()
            ->row();

        if (!$data_barang) {
            show_404();
        }

        $nama_barang = $data_barang->nama_barang;
        $exp_date    = $data_barang->exp_date;

        $query = $this->db->query("SELECT
            a.id,
            a.nama_barang,
            a.exp_date as exp_date,
            (b.p*b.l*b.t) AS dimensi,
            SUM(a.qty) AS qty_awal,
            COALESCE(pending.qty_pending, 0) AS DO,
            COALESCE(purchase.qty_po, 0) AS PO,
            COALESCE(opname.qty_opname, 0) AS ICS,
            (SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
            COALESCE(opname.qty_opname, 0) - ((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) AS selisih,
            IF(((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0), 1, 0) AS status
            FROM tb_saldo_awal a
            JOIN tb_mbarang b ON b.nm_barang = a.nama_barang
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_pending
                FROM tb_ics_do
                GROUP BY nama_barang, exp_date
            ) pending ON pending.nama_barang = a.nama_barang AND pending.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_po
                FROM tb_ics_po
                GROUP BY nama_barang, exp_date
            ) purchase ON purchase.nama_barang = a.nama_barang AND purchase.exp_date = a.exp_date
            LEFT JOIN (
                SELECT nama_barang, exp_date, SUM(qty) AS qty_opname
                FROM tb_ics_opname
                GROUP BY nama_barang, exp_date
            ) opname ON opname.nama_barang = a.nama_barang AND opname.exp_date = a.exp_date
            WHERE a.nama_barang = ? AND a.exp_date = ?
            GROUP BY a.nama_barang, a.exp_date", array($nama_barang, $exp_date));

        $data['detail_stok']        = $query->result();
        $data['detail_allbarang']   = $this->M_Ics->ics_get_all_qty_barang($nama_barang);
        $data['input_log']          = $this->M_Ics->ics_log_input($nama_barang, $exp_date);
        $data['data_do']            = $this->M_Ics->get_do_by_expdate($nama_barang, $exp_date);
        $data['data_po']            = $this->M_Ics->get_po_by_expdate($nama_barang, $exp_date);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_stock_controller.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
    }

    public function get_detail_barang()
    {
        $id = $this->input->post('id');
        $this->db->select('i.id, i.nama_barang, i.exp_date, (m.p * m.l * m.t) AS dimensi');
        $this->db->from('tb_ics i');
        $this->db->join('tb_mbarang m', 'm.nm_barang = i.nama_barang', 'left');
        $this->db->where('i.id', $id);
        $query = $this->db->get()->row();

        echo json_encode($query);
    }

    public function ics_detail_allbarang($nmbarang)
    {
        $data['ics_detail_all'] = $this->M_Ics->get_detail_ics_allbarang($nmbarang);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics_detail_allbarang.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/ics/ajaxics.php', $data);
    }

    public function simpan_input_opname()
    {
        $dimensi   = (int) $this->input->post('dimensi');
        $qty_box   = (int) $this->input->post('qty_box');
        $qty_pcs   = (int) $this->input->post('qty_pcs');
        $qty_total = ($qty_box * $dimensi) + $qty_pcs;
        $today = date('d/m/Y');

        $data = [
            'nama_barang' => $this->input->post('nama_barang'),
            'exp_date'    => $this->input->post('exp_date'),
            'qty'         => $qty_total,
            'qty_box'     => $qty_box,
            'qty_pcs'     => $qty_pcs,
            'dimensi'     => $dimensi,
            'input_at'    => $today,
            'create_at'   => date('Y-m-d H:i:s')
        ];

        if ($this->db->insert('tb_ics_opname', $data)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insert gagal']);
        }
    }

    public function save_opname_ics()
    {
        $action      = $this->input->post('action');
        $id          = $this->input->post('id');
        $kdbarang    = $this->input->post('kdbarang');
        $nama_barang = $this->input->post('nama_barang');
        $exp_date    = $this->input->post('exp_date');
        $dimensi     = $this->input->post('dimensi');
        $qty_box     = $this->input->post('qty_box');
        $qty_pcs     = $this->input->post('qty_pcs');
        $keterangan  = $this->input->post('keterangan_isi');
        $qty_total   = ($qty_box * $dimensi) + $qty_pcs;
        $expawal     = date('d/m/Y', strtotime($exp_date));

        $data = [
            'nama_barang' => $nama_barang,
            'exp_date'    => $exp_date,
            'qty_box'     => $qty_box,
            'qty_pcs'     => $qty_pcs,
            'qty'         => $qty_total,
            'inputer'     => $this->session->userdata('nama'),
            'input_at'    => date('d/m/Y'),
            'create_at'   => date('Y-m-d H:i:s')
        ];

        $dataawal = [
            'kd_system'   => $kdbarang,
            'nama_barang' => $nama_barang,
            'exp_date'    => $expawal,
            'qty_box'     => $qty_box,
            'qty_pcs'     => $qty_pcs,
            'qty'         => $qty_total,
            'inputer'     => $this->session->userdata('nama'),
            'input_at'    => date('d/m/Y'),
            'create_at'   => date('Y-m-d H:i:s')
        ];

        $data_awal = [
            'nama_barang' => $nama_barang,
            'exp_date'    => $expawal,
            'qty'         => $qty_total,
            'qty_box'     => $qty_box,
            'qty_pcs'     => $qty_pcs,
            'input_at'    => date('d/m/Y'),
            'create_at'   => date('Y-m-d H:i:s')
        ];

        $logics = [
            'nama_barang'   => $nama_barang,
            'qty'           => $qty_total,
            'qty_box'       => $qty_box,
            'qty_pcs'       => $qty_pcs,
            'no_lot'        => '-',
            'exp_date'      => $exp_date,
            'keterangan'    => $keterangan,
            'inputer'       => $this->session->userdata('nama'),
            'tgl_input'     => date('d/m/Y'),
            'create_at'     => date('Y-m-d H:i:s')
        ];

        $logicsawal = [
            'nama_barang'   => $nama_barang,
            'qty'           => $qty_total,
            'qty_box'       => $qty_box,
            'qty_pcs'       => $qty_pcs,
            'no_lot'        => '-',
            'exp_date'      => $expawal,
            'keterangan'    => $keterangan,
            'inputer'       => $this->session->userdata('nama'),
            'tgl_input'     => date('d/m/Y'),
            'create_at'     => date('Y-m-d H:i:s')
        ];

        switch ($action) {
            case 'dashboard':
                $this->db->insert('tb_ics_opname', $data);
                $this->db->insert('tb_log_ics', $logics);
                $this->session->set_flashdata('success', 'Data opname berhasil disimpan.');
                redirect('ics');
                break;
            case 'formdetail':
                $this->db->insert('tb_ics_opname', $data);
                $this->db->insert('tb_log_ics', $logics);
                $this->session->set_flashdata('success', 'Data opname berhasil disimpan.');
                redirect('ics/ics_stock_controller/' . $id);
                break;
            case 'formbyexp':
                $this->db->update('tb_ics_opname', $data, ['id' => $id]);
                $this->db->insert('tb_log_ics', $logics);
                $this->session->set_flashdata('success', 'Data opname berhasil diperbarui.');
                redirect('ics/stock_by_kodebr/' . $kdbarang);
                break;
            case 'new_expired':
                $this->db->insert('tb_ics_opname', $dataawal);
                $this->db->insert('tb_log_ics', $logicsawal);
                $this->db->insert('tb_saldo_awal', $data_awal);
                $this->session->set_flashdata('success', 'Data opname berhasil diperbarui.');
                redirect('ics/stock_by_kodebr/' . $kdbarang);
                break;
            case 'diffrent':
                $this->db->insert('tb_ics_opname', $data);
                $this->db->insert('tb_log_ics', $logics);
                $this->session->set_flashdata('success', 'Data opname berhasil disimpan.');
                redirect('ics/ics_diffrent');
                break;
        }
    }

    public function import_csv_po()
    {
        $this->load->library('upload');
        $this->load->helper('file');

        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['file_name']     = 'import_po_' . time();
        $config['overwrite']     = true;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_csv')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('ics/icspo');
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        $handle = fopen($filePath, "r");
        $header = fgetcsv($handle);

        $data_import = [];
        $now = date('d/m/Y');

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data_import[] = [
                'tgl_transaksi'   => $row[0],
                'kd_faktur_lpb'   => $row[1],
                'nama_barang'     => $row[2],
                'exp_date'        => $row[3],
                'qty'             => $row[4],
                'input_at'        => $now,
                'lpb_status'      => '1',
            ];
        }
        fclose($handle);

        if (!empty($data_import)) {
            $this->db->insert_batch('tb_ics_po', $data_import);
            $this->session->set_flashdata('success', 'Import berhasil!');
        } else {
            $this->session->set_flashdata('error', 'File kosong atau format tidak sesuai.');
        }

        redirect('ics/icspo');
    }
}
