<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Ics extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Ics');
        $this->load->helper('stock_helper');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $tgl                        = date('d/m/Y');
        $data['tanggal_now']        = date('d/m/Y');
        $data['barang_ics']         = $this->M_Ics->list_barang_ics($tgl);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/ics/ics.php', $data);
        $this->load->view('partial/main/footer.php');
    }

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
            b.kode_barang,
            a.exp_date as exp_date,
            (b.p*b.l*b.t) AS dimensi,
            SUM(a.qty) AS qty_awal,
            COALESCE(pending.qty_pending, 0) AS DO,
            COALESCE(purchase.qty_po, 0) AS PO,
            COALESCE(opname.qty_opname, 0) AS ICS,
            (SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0) AS qty_all,
            COALESCE(opname.qty_opname, 0) - ((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) AS selisih,
            IF(((SUM(a.qty) - COALESCE(pending.qty_pending, 0)) + COALESCE(purchase.qty_po, 0)) = COALESCE(opname.qty_opname, 0), 1, 0) AS status
            FROM tb_ics a
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
        $data['input_log']          = $this->M_Ics->ics_log_input($nama_barang, $exp_date);

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
        $id          = $this->input->post('id');
        $nama_barang = $this->input->post('nama_barang');
        $exp_date    = $this->input->post('exp_date');
        $dimensi     = $this->input->post('dimensi');
        $qty_box     = $this->input->post('qty_box');
        $qty_pcs     = $this->input->post('qty_pcs');
        $qty_total   = ($qty_box * $dimensi) + $qty_pcs;

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

        $logics = [
            'nama_barang'   => $nama_barang,
            'qty'           => $qty_total,
            'qty_box'       => $qty_box,
            'qty_pcs'       => $qty_pcs,
            'no_lot'        => '-',
            'exp_date'      => $exp_date,
            'keterangan'    => 'ICS UPDATE',
            'inputer'       => $this->session->userdata('nama'),
            'tgl_input'     => date('d/m/Y'),
            'create_at'     => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tb_ics_opname', $data);
        $this->db->insert('tb_log_ics', $logics);

        $this->session->set_flashdata('success', 'Data opname berhasil disimpan.');
        redirect('ics/ics_stock_controller/' . $id);
    }
}
