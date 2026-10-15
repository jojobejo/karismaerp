<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_TestMarket extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }
        $this->load->model('M_TestMarket');
        $this->load->helper(['url', 'form']);
    }

    public function index()
    {
        $this->M_TestMarket->sync_all_invoices();

        $filters = [
            'keyword'     => trim((string)$this->input->get('q', true)),
            'kd_customer' => trim((string)$this->input->get('kd_customer', true)),
            'status'      => trim((string)$this->input->get('status', true)),
            'tab'         => $this->input->get('tab', true) === 'dibeli' ? 'dibeli' : 'kios',
        ];

        $perPage = 20;
        $totalItems = $this->M_TestMarket->count_grouped_items($filters);
        $totalRealizations = $this->M_TestMarket->count_realizations($filters);
        $itemPage = $this->normalize_page($this->input->get('page_kios'), $totalItems, $perPage);
        $realizationPage = $this->normalize_page($this->input->get('page_dibeli'), $totalRealizations, $perPage);

        $data = [
            'page_title'   => 'KARISMA - MONITORING TEST MARKET',
            'filters'      => $filters,
            'summary'      => $this->M_TestMarket->get_summary($filters),
            'items'        => $this->M_TestMarket->get_grouped_items($filters, $perPage, ($itemPage - 1) * $perPage),
            'realizations' => $this->M_TestMarket->get_recent_realizations($filters, $perPage, ($realizationPage - 1) * $perPage),
            'item_pagination' => $this->pagination_data($totalItems, $itemPage, $perPage),
            'realization_pagination' => $this->pagination_data($totalRealizations, $realizationPage, $perPage),
            'customers'    => $this->M_TestMarket->get_customer_options(),
            'warehouse_id' => $this->M_TestMarket->get_warehouse_id(),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/test_market_dashboard.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    private function normalize_page($requestedPage, $totalRows, $perPage)
    {
        $totalPages = max(1, (int)ceil((int)$totalRows / (int)$perPage));
        return min(max(1, (int)$requestedPage), $totalPages);
    }

    private function pagination_data($totalRows, $currentPage, $perPage)
    {
        $totalRows = (int)$totalRows;
        $totalPages = max(1, (int)ceil($totalRows / (int)$perPage));
        return [
            'total_rows'   => $totalRows,
            'current_page' => (int)$currentPage,
            'per_page'     => (int)$perPage,
            'total_pages'  => $totalPages,
            'from'         => $totalRows > 0 ? (((int)$currentPage - 1) * (int)$perPage) + 1 : 0,
            'to'           => min((int)$currentPage * (int)$perPage, $totalRows),
        ];
    }

    public function detail_barang($itemCode = '')
    {
        $itemCode = rawurldecode((string)$itemCode);
        if ($itemCode === '') {
            redirect('test-market');
        }

        $this->M_TestMarket->sync_all_invoices();

        $filters = [
            'keyword'     => '',
            'kd_customer' => trim((string)$this->input->get('kd_customer', true)),
            'status'      => trim((string)$this->input->get('status', true)),
            'kd_barang'   => $itemCode,
        ];
        $positions = $this->M_TestMarket->get_positions($filters);
        $realizations = $this->M_TestMarket->get_recent_realizations($filters, 500);

        $data = [
            'page_title' => 'KARISMA - DETAIL BARANG TEST MARKET',
            'filters'    => $filters,
            'positions'  => $positions,
            'summary'    => $this->summarize_positions($positions, $realizations),
            'kios_list'  => $this->group_positions_by_kiosk($positions),
            'realizations' => $realizations,
            'item_code'  => $itemCode,
            'item_name'  => !empty($positions) ? ($positions[0]['nama_barang'] ?: $itemCode) : $itemCode,
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/test_market_detail_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    private function group_positions_by_item(array $positions)
    {
        $items = [];

        foreach ($positions as $position) {
            $itemCode = (string)$position['kd_barang'];
            if (!isset($items[$itemCode])) {
                $items[$itemCode] = [
                    'kd_barang'   => $itemCode,
                    'nama_barang' => $position['nama_barang'] ?: $itemCode,
                    'satuan'      => $position['satuan'] ?? '',
                    'qty_titip'   => 0,
                    'qty_terjual' => 0,
                    'qty_sisa'    => 0,
                    'details'     => [],
                    'customers'   => [],
                    'invoices'    => [],
                ];
            }

            $items[$itemCode]['qty_titip'] += (float)$position['qty_titip'];
            $items[$itemCode]['qty_terjual'] += (float)$position['qty_terjual'];
            $items[$itemCode]['qty_sisa'] += (float)$position['qty_sisa'];
            $items[$itemCode]['details'][] = $position;
            $items[$itemCode]['customers'][(string)$position['kd_customer']] = true;
            $items[$itemCode]['invoices'][(string)$position['id_faktur']] = true;
        }

        foreach ($items as &$item) {
            $item['total_kios'] = count($item['customers']);
            $item['total_faktur'] = count($item['invoices']);
            unset($item['customers'], $item['invoices']);
        }
        unset($item);

        uasort($items, static function ($first, $second) {
            return strcasecmp($first['nama_barang'], $second['nama_barang']);
        });

        return array_values($items);
    }

    private function summarize_positions(array $positions, array $realizations)
    {
        $summary = [
            'qty_titip'    => 0,
            'qty_terjual'  => 0,
            'qty_sisa'     => 0,
            'total_kios'   => 0,
            'total_faktur' => 0,
            'nilai_laku'   => 0,
        ];
        $customers = [];
        $invoices = [];

        foreach ($positions as $position) {
            $summary['qty_titip'] += (float)$position['qty_titip'];
            $summary['qty_terjual'] += (float)$position['qty_terjual'];
            $summary['qty_sisa'] += (float)$position['qty_sisa'];
            $customers[(string)$position['kd_customer']] = true;
            $invoices[(string)$position['id_faktur']] = true;
        }
        foreach ($realizations as $realization) {
            $summary['nilai_laku'] += (float)$realization['subtotal'];
        }

        $summary['total_kios'] = count($customers);
        $summary['total_faktur'] = count($invoices);
        return $summary;
    }

    private function group_positions_by_kiosk(array $positions)
    {
        $kiosList = [];

        foreach ($positions as $position) {
            $customerCode = (string)$position['kd_customer'];
            if (!isset($kiosList[$customerCode])) {
                $kiosList[$customerCode] = [
                    'kd_customer'   => $customerCode,
                    'customer_name' => $position['customer_name'] ?: $customerCode,
                    'satuan'        => $position['satuan'] ?? '',
                    'qty_titip'     => 0,
                    'qty_terjual'   => 0,
                    'qty_sisa'      => 0,
                    'total_faktur'  => 0,
                    'invoices'      => [],
                ];
            }

            $kiosList[$customerCode]['qty_titip'] += (float)$position['qty_titip'];
            $kiosList[$customerCode]['qty_terjual'] += (float)$position['qty_terjual'];
            $kiosList[$customerCode]['qty_sisa'] += (float)$position['qty_sisa'];
            $kiosList[$customerCode]['invoices'][(string)$position['id_faktur']] = true;
        }

        foreach ($kiosList as &$kios) {
            $kios['total_faktur'] = count($kios['invoices']);
            unset($kios['invoices']);
        }
        unset($kios);

        uasort($kiosList, static function ($first, $second) {
            return strcasecmp($first['customer_name'], $second['customer_name']);
        });
        return array_values($kiosList);
    }
}
