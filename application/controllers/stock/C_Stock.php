<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Stock extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Stock');
        $this->load->library('session');
        $this->load->helper(['url', 'login_auth']);
        is_logged_in();
    }

    private function _filters()
    {
        return [
            'kd_barang' => $this->input->get('kd_barang', true),
            'gudang_id' => $this->input->get('gudang_id', true),
            'expired_from' => $this->input->get('expired_from', true),
            'expired_to' => $this->input->get('expired_to', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to' => $this->input->get('date_to', true),
            'tipe' => $this->input->get('tipe', true),
            'kd_suplier' => $this->input->get('kd_suplier', true),
            'status_alert' => $this->input->get('status_alert', true),
            'search' => $this->input->get('search', true),
            'include_zero' => $this->input->get('include_zero', true) === '1',
            'limit' => $this->input->get('limit', true),
            'page' => $this->input->get('page', true),
            'per_page' => $this->input->get('per_page', true),
        ];
    }

    private function _json($payload, $statusCode = 200)
    {
        if (ob_get_level()) ob_end_clean();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    public function index()
    {
        $data['page_title'] = 'KARISMA - Stock';
        $data['gudang_summary'] = $this->M_Stock->get_gudang_summary();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stock/stock_control.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function summary()
    {
        try {
            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_summary($this->_filters()),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function gudangs()
    {
        try {
            $filters = $this->_filters();
            unset($filters['gudang_id']);

            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_gudang_summary($filters),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function available()
    {
        try {
            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_available_for_sales($this->_filters()),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function items()
    {
        try {
            $filters = $this->_filters();
            if (empty($filters['per_page'])) {
                $filters['per_page'] = 15;
            }

            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_item_rows($filters),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function detail($kdBarang = '')
    {
        $kdBarang = rawurldecode((string)$kdBarang);
        if ($kdBarang === '') {
            $kdBarang = (string)$this->input->get('kd_barang', true);
        }

        if ($kdBarang === '') {
            show_404();
        }

        $gudangId = (string)$this->input->get('gudang_id', true);
        $detail = $this->M_Stock->get_stock_item_detail($kdBarang, $gudangId);
        if (empty($detail['item']) && empty($detail['ledger_history'])) {
            show_404();
        }

        $data['page_title'] = 'KARISMA - Detail Stock';
        $data['kd_barang'] = $kdBarang;
        $data['gudang_id'] = $gudangId;
        $data['detail'] = $detail;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stock/stock_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function batches()
    {
        try {
            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_batch_rows($this->_filters()),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function ledger()
    {
        try {
            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_ledger_rows($this->_filters()),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function reconciliation()
    {
        try {
            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_reconciliation($this->_filters()),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function sync()
    {
        try {
            $dryRun = $this->input->method() !== 'post' || $this->input->post('apply', true) !== '1';
            $filters = $this->_filters();
            if ($this->input->post('kd_barang', true)) $filters['kd_barang'] = $this->input->post('kd_barang', true);
            if ($this->input->post('gudang_id', true)) $filters['gudang_id'] = $this->input->post('gudang_id', true);

            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->sync_batch_from_ledger($filters, $dryRun),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function buffer()
    {
        $data['page_title'] = 'KARISMA - Buffer Stock Control';
        $data['gudang_summary'] = $this->M_Stock->get_gudang_summary();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stock/stock_buffer.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function buffer_summary()
    {
        try {
            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_buffer_summary($this->_filters()),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function buffer_items()
    {
        try {
            $filters = $this->_filters();
            if (empty($filters['per_page'])) {
                $filters['per_page'] = 15;
            }

            $this->_json([
                'status' => 'ok',
                'data' => $this->M_Stock->get_buffer_rows($filters),
            ]);
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function buffer_update_min()
    {
        try {
            $kdBarang = $this->input->post('kd_barang', true);
            $stockMinimum = $this->input->post('stock_minimum', true);

            if (empty($kdBarang)) {
                $this->_json(['status' => 'error', 'message' => 'Kode barang wajib diisi.'], 400);
                return;
            }

            $success = $this->M_Stock->update_stock_minimum($kdBarang, $stockMinimum);
            if ($success) {
                $this->_json([
                    'status' => 'ok',
                    'message' => 'Berhasil memperbarui target minimum buffer stock barang.',
                ]);
            } else {
                $this->_json(['status' => 'error', 'message' => 'Gagal mengedit minimum stock.'], 500);
            }
        } catch (Exception $e) {
            $this->_json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function buffer_export()
    {
        try {
            $filters = $this->_filters();
            $filters['per_page'] = 999999;
            $filters['page'] = 1;

            $result = $this->M_Stock->get_buffer_rows($filters);
            $rows = $result['rows'];

            $filename = 'buffer_stock_report_' . date('Ymd_His') . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Kode Barang',
                'Nama Barang',
                'Supplier',
                'Satuan',
                'Isi per Box',
                'Min Buffer (Pcs)',
                'Stok Fisik (Pcs)',
                'Reserved (Pcs)',
                'Stok Available (Pcs)',
                'Defisit (Pcs)',
                'Rekomendasi Reorder (Box)',
                'Status Alert'
            ]);

            foreach ($rows as $r) {
                fputcsv($output, [
                    $r['kd_barang'],
                    $r['nama_barang'],
                    $r['nama_suplier'],
                    $r['satuan'],
                    $r['isi_per_box'],
                    $r['stock_minimum'],
                    $r['qty_on_hand'],
                    $r['qty_reserved'],
                    $r['qty_available'],
                    $r['defisit'],
                    $r['reorder_box'],
                    $r['status_alert']
                ]);
            }

            fclose($output);
            exit;
        } catch (Exception $e) {
            show_error($e->getMessage(), 500);
        }
    }
}

