<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Laporan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url']);
        $this->_ensure_access();
    }

    private function _ensure_access()
    {
        if (!$this->session->userdata('logged_in')) {
            if ($this->input->is_ajax_request() || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
                    ]));
                $this->output->_display();
                exit;
            }
            redirect('Auth');
        }
    }

    // ====================================================
    // LANDING — Laporan utama (pilih kategori)
    // ====================================================
    public function index()
    {
        $data['page_title'] = 'KARISMA - LAPORAN';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Keuangan
    // ====================================================
    public function keuangan()
    {
        $data['page_title'] = 'KARISMA - LAPORAN KEUANGAN';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Penjualan & Piutang
    // ====================================================
    public function penjualan()
    {
        $data['page_title'] = 'KARISMA - LAPORAN PENJUALAN & PIUTANG';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Pembelian & Hutang
    // ====================================================
    public function pembelian()
    {
        $data['page_title'] = 'KARISMA - LAPORAN PEMBELIAN & HUTANG';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_pembelian_hutang/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Barang / Inventori
    // ====================================================
    public function barang()
    {
        $data['page_title'] = 'KARISMA - LAPORAN BARANG';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_barang/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Lainnya
    // ====================================================
    public function lainnya()
    {
        $data['page_title'] = 'KARISMA - LAPORAN LAINNYA';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_lainnya/index.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Halaman Report Spesifik
    // ====================================================
    public function jurnal_penjualan_report()
    {
        $data['page_title'] = 'KARISMA - LAPORAN JURNAL PENJUALAN';
        $data['page_heading'] = 'Jurnal - Penjualan';
        $data['report_title'] = 'Jurnal Penjualan';
        $data['report_endpoint'] = base_url('jurnal/sales-report-data');
        $data['empty_message'] = 'Data jurnal penjualan tidak ditemukan pada periode ini.';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/jurnal_penjualan_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Halaman Laporan Jurnal - Potongan Penjualan.
     */
    public function jurnal_potongan_report()
    {
        $data['page_title'] = 'KARISMA - LAPORAN JURNAL POTONGAN PENJUALAN';
        $data['page_heading'] = 'Jurnal - Potongan Penjualan';
        $data['report_title'] = 'Jurnal Potongan Penjualan';
        $data['report_endpoint'] = base_url('jurnal/sales-discount-report-data');
        $data['empty_message'] = 'Data jurnal potongan penjualan tidak ditemukan pada periode ini.';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/jurnal_penjualan_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Halaman Laporan Penjualan - eFaktur.
     */
    public function efaktur_penjualan_report()
    {
        $data['page_title']      = 'KARISMA - PENJUALAN EFAKTUR';
        $this->load->model('M_Journal');
        $data['filter_options'] = $this->M_Journal->salesReportFilterOptions('efaktur');
        $data['report_endpoint'] = base_url('laporan/penjualan/efaktur-data');
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/efaktur_penjualan_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Endpoint JSON data untuk Laporan Penjualan - eFaktur.
     */
    public function efaktur_penjualan_data()
    {
        $this->load->model('M_Journal');

        $startDate = trim((string) $this->input->get('start_date', true));
        $endDate   = trim((string) $this->input->get('end_date', true));
        $noRef     = trim((string) $this->input->get('no_ref', true));
        $pelanggan = trim((string) $this->input->get('pelanggan', true));
        $npwp      = trim((string) $this->input->get('npwp', true));

        if ($startDate === '') {
            $startDate = date('Y-m-01');
        }
        if ($endDate === '') {
            $endDate = date('Y-m-t');
        }

        $rows = $this->M_Journal->accounting_sales_efaktur_report($startDate, $endDate, $noRef, $pelanggan, $npwp, $this->salesReportFilters());

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'rows'       => $rows,
            ]));
    }

    private function _zahir_penjualan_meta($jenis)
    {
        $map = [
            'rincian' => [
                'slug'  => 'rincian',
                'title' => 'Penjualan - Rincian',
            ],
            'negatif-rangkuman' => [
                'slug'  => 'negatif-rangkuman',
                'title' => 'Penjualan Bernilai Negatif - Rangkuman',
            ],
            'negatif-sederhana' => [
                'slug'  => 'negatif-sederhana',
                'title' => 'Penjualan Bernilai Negatif - Sederhana',
            ],
            'per-mata-uang-rangkuman' => [
                'slug'  => 'per-mata-uang-rangkuman',
                'title' => 'Penjualan Per Mata Uang - Rangkuman',
            ],
            'per-salesman-faktur' => [
                'slug'  => 'per-salesman-faktur',
                'title' => 'Penjualan Per Salesman Per Faktur',
            ],
            'per-kelompok-pelanggan-faktur' => [
                'slug'  => 'per-kelompok-pelanggan-faktur',
                'title' => 'Penjualan Per Kelompok Pelanggan Per Faktur',
            ],
            'sederhana' => [
                'slug'  => 'sederhana',
                'title' => 'Penjualan - Sederhana',
            ],
            'per-mata-uang-sederhana' => [
                'slug'  => 'per-mata-uang-sederhana',
                'title' => 'Penjualan Per Mata Uang - Sederhana',
            ],
            'per-kelompok-salesman' => [
                'slug'  => 'per-kelompok-salesman',
                'title' => 'Penjualan Per Kelompok Salesman',
            ],
            'retur-rangkuman' => [
                'slug'  => 'retur-rangkuman',
                'title' => 'Retur Penjualan - Rangkuman',
            ],
            'retur-sederhana' => [
                'slug'  => 'retur-sederhana',
                'title' => 'Retur Penjualan Sederhana',
            ],
            'retur-rincian' => [
                'slug'  => 'retur-rincian',
                'title' => 'Retur Penjualan - Rincian',
            ],
        ];
        return $map[$jenis] ?? $map['rincian'];
    }

    /**
     * Halaman Laporan Penjualan format Zahir (Rincian, Negatif Rangkuman/Sederhana, Per Mata Uang Rangkuman).
     */
    public function penjualan_zahir_report($jenis = 'rincian')
    {
        $meta = $this->_zahir_penjualan_meta($jenis);
        $data['page_title']      = 'KARISMA - ' . strtoupper($meta['title']);
        $data['report_meta']     = $meta;
        $this->load->model('M_Journal');
        $data['filter_options'] = $this->M_Journal->salesReportFilterOptions($meta['slug']);
        $data['report_endpoint'] = base_url('laporan/penjualan/zahir-data');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/penjualan_zahir_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Endpoint JSON data untuk laporan penjualan format Zahir.
     */
    public function penjualan_zahir_data()
    {
        $this->load->model('M_Journal');

        $jenis     = trim((string) $this->input->get('jenis', true));
        $startDate = trim((string) $this->input->get('start_date', true));
        $endDate   = trim((string) $this->input->get('end_date', true));
        $noRef     = trim((string) $this->input->get('no_ref', true));
        $pelanggan = trim((string) $this->input->get('pelanggan', true));

        if ($startDate === '') {
            $startDate = date('Y-m-01');
        }
        if ($endDate === '') {
            $endDate = date('Y-m-t');
        }

        $meta = $this->_zahir_penjualan_meta($jenis);
        $rows = $this->M_Journal->accounting_sales_zahir_report($meta['slug'], $startDate, $endDate, $noRef, $pelanggan, $this->salesReportFilters());

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'meta'       => $meta,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'rows'       => $rows,
            ]));
    }

    public function uang_muka_penjualan_report()
    {
        $this->load->model('M_Journal');
        $selected = array_intersect_key($this->salesReportFilters(), array_flip(['customer', 'status']));
        $showReport = $this->input->get('start_date') !== null || $this->input->get('end_date') !== null;
        $dateError = false;
        foreach (['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-t')] as $key => $default) {
            $value = $this->input->get($key);
            $date = is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)
                ? DateTime::createFromFormat('!Y-m-d', $value) : false;
            $valid = $date && $date->format('Y-m-d') === $value;
            $dateError = $dateError || ($showReport && !$valid);
            $selected[$key] = $valid ? $value : $default;
        }
        $dateError = $dateError || $selected['start_date'] > $selected['end_date'];
        $customers = $this->M_Journal->salesAdvanceCustomers();
        $data = [
            'page_title' => 'KARISMA - UANG MUKA',
            'selected' => $selected,
            'filter_options' => ['customer' => $customers, 'status' => ['DRAFT' => 'Draft', 'POSTED' => 'Posted']],
            'rows' => $showReport && !$dateError ? $this->M_Journal->salesAdvanceReport($selected) : [],
            'show_report' => $showReport,
            'date_error' => $dateError,
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/uang_muka_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    private function salesReportFilters()
    {
        $filters = [];
        foreach (['customer', 'customer_class', 'salesman', 'salesman_class', 'status', 'export_status', 'serial_status'] as $key) {
            $value = $this->input->get($key);
            $filters[$key] = is_string($value) ? trim($value) : '';
        }

        return $filters;
    }

    private function _zahir_piutang_meta($jenis)
    {
        $map = [
            'kartu-piutang-salesman' => [
                'slug'  => 'kartu-piutang-salesman',
                'title' => 'Kartu Piutang Per Salesman',
            ],
            'kartu-piutang-mata-uang-dasar' => [
                'slug'  => 'kartu-piutang-mata-uang-dasar',
                'title' => 'Kartu Piutang Mata Uang Dasar',
            ],
            'kartu-piutang-salesman-rangkuman' => [
                'slug'  => 'kartu-piutang-salesman-rangkuman',
                'title' => 'Kartu Piutang Per Salesman - Rangkuman',
            ],
            'mutasi-piutang' => [
                'slug'  => 'mutasi-piutang',
                'title' => 'Mutasi Piutang - Mata Uang Dasar',
            ],
            'pembayaran-pelanggan' => [
                'slug'  => 'pembayaran-pelanggan',
                'title' => 'Pembayaran Pelanggan - Mata Uang Dasar',
            ],
            'piutang-per-mata-uang-rangkuman' => [
                'slug'  => 'piutang-per-mata-uang-rangkuman',
                'title' => 'Piutang Usaha Per Mata Uang - Rangkuman',
            ],
            'piutang-per-pelanggan-rincian' => [
                'slug'  => 'piutang-per-pelanggan-rincian',
                'title' => 'Piutang Usaha Per Pelanggan - Rincian',
            ],
            'piutang-per-salesman-rangkuman' => [
                'slug'  => 'piutang-per-salesman-rangkuman',
                'title' => 'Piutang Usaha Per Salesman - Rangkuman',
            ],
            'piutang-per-salesman-rincian' => [
                'slug'  => 'piutang-per-salesman-rincian',
                'title' => 'Piutang Usaha Per Salesman - Rincian',
            ],
            'piutang-per-mata-uang-rincian' => [
                'slug'  => 'piutang-per-mata-uang-rincian',
                'title' => 'Piutang Usaha Per Mata Uang - Rincian',
            ],
            'surat-tagihan-pelanggan' => [
                'slug'  => 'surat-tagihan-pelanggan',
                'title' => 'Surat Tagihan Pelanggan',
            ],
        ];
        return $map[$jenis] ?? $map['kartu-piutang-salesman'];
    }

    /**
     * Halaman Laporan Piutang Usaha format Zahir Accounting.
     */
    public function piutang_zahir_report($jenis = 'kartu-piutang-salesman')
    {
        $meta = $this->_zahir_piutang_meta($jenis);
        $data['page_title']      = 'KARISMA - ' . strtoupper($meta['title']);
        $data['report_meta']     = $meta;
        $data['report_endpoint'] = base_url('laporan/piutang/zahir-data');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/piutang_zahir_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Endpoint JSON data laporan Piutang Usaha format Zahir.
     */
    public function piutang_zahir_data()
    {
        $this->load->model('M_Journal');

        $jenis     = trim((string) $this->input->get('jenis', true));
        $startDate = trim((string) $this->input->get('start_date', true));
        $endDate   = trim((string) $this->input->get('end_date', true));
        $search    = trim((string) $this->input->get('search', true));

        if ($startDate === '') {
            $startDate = date('Y-m-01');
        }
        if ($endDate === '') {
            $endDate = date('Y-m-t');
        }

        $meta = $this->_zahir_piutang_meta($jenis);
        $rows = $this->M_Journal->accounting_receivable_zahir_report($meta['slug'], $startDate, $endDate, $search);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'meta'       => $meta,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'rows'       => $rows,
            ]));
    }

    /**
     * Metadata laporan Pembelian format Zahir Accounting.
     */
    private function _zahir_pembelian_meta($jenis)
    {
        $map = [
            'jurnal-pembelian' => [
                'slug'  => 'jurnal-pembelian',
                'title' => 'Jurnal - Pembelian',
            ],
            'jurnal-diskon' => [
                'slug'  => 'jurnal-diskon',
                'title' => 'Jurnal - Diskon Pembelian',
            ],
            'penerimaan-barang-harian' => [
                'slug'  => 'penerimaan-barang-harian',
                'title' => 'Penerimaan Barang (Harian)',
            ],
            'rincian' => [
                'slug'  => 'rincian',
                'title' => 'Pembelian - Rincian',
            ],
            'efaktur' => [
                'slug'  => 'efaktur',
                'title' => 'Pembelian - eFaktur',
            ],
            'pesanan-rincian' => [
                'slug'  => 'pesanan-rincian',
                'title' => 'Pesanan Pembelian - Rincian',
            ],
            'sederhana' => [
                'slug'  => 'sederhana',
                'title' => 'Pembelian - Sederhana',
            ],
            'negatif-rangkuman' => [
                'slug'  => 'negatif-rangkuman',
                'title' => 'Pembelian Bernilai Negatif - Rangkuman',
            ],
            'pengiriman-rangkuman' => [
                'slug'  => 'pengiriman-rangkuman',
                'title' => 'Pengiriman Pembelian - Rangkuman',
            ],
            'purchase-return' => [
                'slug'  => 'purchase-return',
                'title' => 'Purchase Return',
            ],
            'retur-rincian' => [
                'slug'  => 'retur-rincian',
                'title' => 'Retur Pembelian - Rincian',
            ],
            'retur-sederhana' => [
                'slug'  => 'retur-sederhana',
                'title' => 'Retur Pembelian - Sederhana',
            ],
            'pesanan-sederhana' => [
                'slug'  => 'pesanan-sederhana',
                'title' => 'Pesanan Pembelian - Sederhana',
            ],
            'pesanan-status-pengiriman' => [
                'slug'  => 'pesanan-status-pengiriman',
                'title' => 'Pesanan Pembelian - Status Pengiriman',
            ],
        ];
        return $map[$jenis] ?? $map['jurnal-pembelian'];
    }

    /**
     * Halaman Laporan Pembelian format Zahir Accounting.
     */
    public function pembelian_zahir_report($jenis = 'jurnal-pembelian')
    {
        $meta = $this->_zahir_pembelian_meta($jenis);
        $data['page_title']      = 'KARISMA - ' . strtoupper($meta['title']);
        $data['report_meta']     = $meta;
        $data['report_endpoint'] = base_url('laporan/pembelian/zahir-data');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_pembelian_hutang/pembelian_zahir_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Endpoint JSON data laporan Pembelian format Zahir.
     */
    public function pembelian_zahir_data()
    {
        $this->load->model('M_Journal');

        $jenis     = trim((string) $this->input->get('jenis', true));
        $startDate = trim((string) $this->input->get('start_date', true));
        $endDate   = trim((string) $this->input->get('end_date', true));
        $search    = trim((string) $this->input->get('search', true));

        if ($startDate === '') {
            $startDate = date('Y-m-01');
        }
        if ($endDate === '') {
            $endDate = date('Y-m-t');
        }

        $meta = $this->_zahir_pembelian_meta($jenis);
        $rows = $this->M_Journal->accounting_purchase_zahir_report($meta['slug'], $startDate, $endDate, $search);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'meta'       => $meta,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'rows'       => $rows,
            ]));
    }

    /**
     * Metadata laporan Hutang Usaha format Zahir Accounting.
     */
    private function _zahir_hutang_meta($jenis)
    {
        $map = [
            'rincian-mata-uang-dasar' => [
                'slug'  => 'rincian-mata-uang-dasar',
                'title' => 'Rincian Hutang - Mata Uang Dasar',
            ],
            'kartu-hutang-mata-uang-dasar' => [
                'slug'  => 'kartu-hutang-mata-uang-dasar',
                'title' => 'Kartu Hutang - Mata Uang Dasar',
            ],
            'mutasi-hutang-mata-uang-dasar' => [
                'slug'  => 'mutasi-hutang-mata-uang-dasar',
                'title' => 'Mutasi Hutang - Mata Uang Dasar',
            ],
            'pembayaran-hutang-mata-uang-dasar' => [
                'slug'  => 'pembayaran-hutang-mata-uang-dasar',
                'title' => 'Pembayaran Hutang - Mata Uang Dasar',
            ],
            'rangkuman-hutang-mata-uang-dasar' => [
                'slug'  => 'rangkuman-hutang-mata-uang-dasar',
                'title' => 'Rangkuman Hutang - Mata Uang Dasar',
            ],
        ];
        return $map[$jenis] ?? $map['rincian-mata-uang-dasar'];
    }

    /**
     * Halaman Laporan Hutang Usaha format Zahir Accounting.
     */
    public function hutang_zahir_report($jenis = 'rincian-mata-uang-dasar')
    {
        $meta = $this->_zahir_hutang_meta($jenis);
        $data['page_title']      = 'KARISMA - ' . strtoupper($meta['title']);
        $data['report_meta']     = $meta;
        $data['report_endpoint'] = base_url('laporan/hutang/zahir-data');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_pembelian_hutang/hutang_zahir_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Endpoint JSON data laporan Hutang Usaha format Zahir.
     */
    public function hutang_zahir_data()
    {
        $this->load->model('M_Journal');

        $jenis     = trim((string) $this->input->get('jenis', true));
        $startDate = trim((string) $this->input->get('start_date', true));
        $endDate   = trim((string) $this->input->get('end_date', true));
        $search    = trim((string) $this->input->get('search', true));

        if ($startDate === '') {
            $startDate = date('Y-m-01');
        }
        if ($endDate === '') {
            $endDate = date('Y-m-t');
        }

        $meta = $this->_zahir_hutang_meta($jenis);
        $rows = $this->M_Journal->accounting_payable_zahir_report($meta['slug'], $startDate, $endDate, $search);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'meta'       => $meta,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'rows'       => $rows,
            ]));
    }

    /**
     * Metadata laporan Persediaan Barang format Zahir Accounting.
     */
    private function _zahir_barang_meta($jenis)
    {
        $map = [
            'kartu-stok-umum' => [
                'slug'  => 'kartu-stok-umum',
                'title' => 'Kartu Stok - Umum',
            ],
            'analisa-per-gudang' => [
                'slug'  => 'analisa-per-gudang',
                'title' => 'Analisa Barang Per Gudang',
            ],
            'barang-dibeli' => [
                'slug'  => 'barang-dibeli',
                'title' => 'Barang Dibeli',
            ],
            'barang-terjual' => [
                'slug'  => 'barang-terjual',
                'title' => 'Barang Terjual',
            ],
            'daftar-barang-umum' => [
                'slug'  => 'daftar-barang-umum',
                'title' => 'Daftar Barang - Umum',
            ],
            'daftar-barang-per-gudang' => [
                'slug'  => 'daftar-barang-per-gudang',
                'title' => 'Daftar Barang Per Gudang',
            ],
            'daftar-harga-jual' => [
                'slug'  => 'daftar-harga-jual',
                'title' => 'Daftar Harga Jual Barang',
            ],
            'jurnal-persediaan' => [
                'slug'  => 'jurnal-persediaan',
                'title' => 'Jurnal Persediaan',
            ],
            'kartu-stok-per-pemasok-per-gudang' => [
                'slug'  => 'kartu-stok-per-pemasok-per-gudang',
                'title' => 'Kartu Stok Per Pemasok Per Gudang',
            ],
            'formulir-stok-opname' => [
                'slug'  => 'formulir-stok-opname',
                'title' => 'Formulir Stok Opname',
            ],
            'transaksi-semua' => [
                'slug'  => 'transaksi-semua',
                'title' => 'Transaksi Barang - Semua Transaksi',
            ],
            'transaksi-pemakaian-penyesuaian' => [
                'slug'  => 'transaksi-pemakaian-penyesuaian',
                'title' => 'Transaksi - Pemakaian / Penyesuaian Barang',
            ],
            'mutasi-per-gudang' => [
                'slug'  => 'mutasi-per-gudang',
                'title' => 'Mutasi Barang Per Gudang',
            ],
            'mutasi-umum' => [
                'slug'  => 'mutasi-umum',
                'title' => 'Mutasi Barang - Umum',
            ],
            'lot-mutasi' => [
                'slug'  => 'lot-mutasi',
                'title' => 'Nomor Lot Barang - Mutasi',
            ],
            'lot-tersedia' => [
                'slug'  => 'lot-tersedia',
                'title' => 'Nomor Lot Barang - Tersedia',
            ],
            'penjualan-barang-pelanggan-faktur' => [
                'slug'  => 'penjualan-barang-pelanggan-faktur',
                'title' => 'Penjualan Per Barang Per Pelanggan Per Faktur',
            ],
            'analisa-umum' => [
                'slug'  => 'analisa-umum',
                'title' => 'Analisa Barang - Umum',
            ],
            'laris-top10' => [
                'slug'  => 'laris-top10',
                'title' => 'Barang Paling Laris - (10 Teratas) Per Kelompok',
            ],
            'laris-per-kelompok' => [
                'slug'  => 'laris-per-kelompok',
                'title' => 'Barang Paling Laris - Per Kelompok',
            ],
            'laris-keuntungan' => [
                'slug'  => 'laris-keuntungan',
                'title' => 'Barang Paling Laris (Keuntungan)',
            ],
            'laba-rugi-rincian' => [
                'slug'  => 'laba-rugi-rincian',
                'title' => 'Laba / Rugi Barang - Rincian',
            ],
            'laba-rugi-total' => [
                'slug'  => 'laba-rugi-total',
                'title' => 'Laba / Rugi Barang - Total',
            ],
            'pembelian-barang-pemasok-faktur' => [
                'slug'  => 'pembelian-barang-pemasok-faktur',
                'title' => 'Pembelian Per Barang Per Pemasok Per Faktur',
            ],
            'pembelian-pemasok-barang-invoice' => [
                'slug'  => 'pembelian-pemasok-barang-invoice',
                'title' => 'Pembelian Per Pemasok Per Barang Per Invoice',
            ],
            'pembelian-pemasok-barang-total' => [
                'slug'  => 'pembelian-pemasok-barang-total',
                'title' => 'Pembelian Per Pemasok Per Barang Total',
            ],
            'pembelian-barang-pemasok-total' => [
                'slug'  => 'pembelian-barang-pemasok-total',
                'title' => 'Pembelian Per Barang Per Pemasok Total',
            ],
            'perputaran-cepat-per-kelompok' => [
                'slug'  => 'perputaran-cepat-per-kelompok',
                'title' => 'Perputaran Barang Paling Cepat Per Kelompok',
            ],
            'perputaran-cepat-semua' => [
                'slug'  => 'perputaran-cepat-semua',
                'title' => 'Perputaran Barang Paling Cepat - Semua Kelompok',
            ],
            'penjualan-pelanggan-barang-total' => [
                'slug'  => 'penjualan-pelanggan-barang-total',
                'title' => 'Penjualan Per Pelanggan Per Barang Total',
            ],
            'penjualan-pelanggan-barang-faktur' => [
                'slug'  => 'penjualan-pelanggan-barang-faktur',
                'title' => 'Penjualan Per Pelanggan Per Barang Per Faktur',
            ],
            'penjualan-salesman-barang-total' => [
                'slug'  => 'penjualan-salesman-barang-total',
                'title' => 'Penjualan Per Salesman Per Barang Total',
            ],
            'penjualan-barang-pelanggan-total' => [
                'slug'  => 'penjualan-barang-pelanggan-total',
                'title' => 'Penjualan Per Barang Per Pelanggan Total',
            ],
            'penjualan-barang-salesman-total' => [
                'slug'  => 'penjualan-barang-salesman-total',
                'title' => 'Penjualan Per Barang Per Salesman Total',
            ],
            'penjualan-salesman-pelanggan-barang-faktur' => [
                'slug'  => 'penjualan-salesman-pelanggan-barang-faktur',
                'title' => 'Penjualan Per Salesman Per Pelanggan Per Barang Per Faktur',
            ],
            'penjualan-salesman-barang-non-retur' => [
                'slug'  => 'penjualan-salesman-barang-non-retur',
                'title' => 'Penjualan Per Salesman Per Barang (Selain Retur)',
            ],
            'penjualan-salesman-pelanggan' => [
                'slug'  => 'penjualan-salesman-pelanggan',
                'title' => 'Penjualan Per Salesman Per Pelanggan',
            ],
        ];
        return $map[$jenis] ?? $map['kartu-stok-umum'];
    }

    /**
     * Halaman Laporan Persediaan Barang format Zahir Accounting.
     */
    public function barang_zahir_report($jenis = 'kartu-stok-umum')
    {
        $meta = $this->_zahir_barang_meta($jenis);
        $data['page_title']      = 'KARISMA - ' . strtoupper($meta['title']);
        $data['report_meta']     = $meta;
        $data['report_endpoint'] = base_url('laporan/barang/zahir-data');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_barang/barang_zahir_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Endpoint JSON data laporan Persediaan Barang format Zahir.
     */
    public function barang_zahir_data()
    {
        $this->load->model('M_Journal');

        $jenis     = trim((string) $this->input->get('jenis', true));
        $startDate = trim((string) $this->input->get('start_date', true));
        $endDate   = trim((string) $this->input->get('end_date', true));
        $search    = trim((string) $this->input->get('search', true));

        if ($startDate === '') {
            $startDate = date('Y-m-01');
        }
        if ($endDate === '') {
            $endDate = date('Y-m-t');
        }

        $meta = $this->_zahir_barang_meta($jenis);
        $rows = $this->M_Journal->accounting_inventory_zahir_report($meta['slug'], $startDate, $endDate, $search);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'meta'       => $meta,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'rows'       => $rows,
            ]));
    }

    public function kartu_stock_gudang_report()
    {
        $data['page_title'] = 'KARISMA - KARTU STOK PER GUDANG';
        
        $data['warehouses'] = $this->db->get_where('tb_gudang', ['is_active' => 1])->result_array();
        
        $data['groups'] = $this->db->select('NOINDEX as id, DESKRIPSI as kelompok')
                                   ->from('tbkeu_kelompok_dagang')
                                   ->where('NOINDEX >', 0)
                                   ->get()->result_array();
                                   
        $data['products'] = $this->db->select('kode_barang, nama_barang')
                                     ->from('tbpo_barang')
                                     ->where('is_active', 'T')
                                     ->get()->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_barang/kartu_stock_gudang.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Halaman Laporan Kartu Stok A (khusus barang dengan awalan kode selain Q dan C, seperti A, Z, X).
     */
    public function kartu_stock_a_report()
    {
        $data['page_title'] = 'KARISMA - KARTU STOK A';
        $data['report_title'] = 'Kartu Stok A';
        $data['report_data_url'] = base_url('laporan/barang/kartu-stock-a-data');

        $data['warehouses'] = $this->db->get_where('tb_gudang', ['is_active' => 1])->result_array();

        $data['groups'] = $this->db->select('NOINDEX as id, DESKRIPSI as kelompok')
                                   ->from('tbkeu_kelompok_dagang')
                                   ->where('NOINDEX >', 0)
                                   ->get()->result_array();

        $data['products'] = $this->db->select('kode_barang, nama_barang')
                                     ->from('tbpo_barang')
                                     ->where('is_active', 'T')
                                     ->where("UPPER(LEFT(TRIM(kode_barang), 1)) NOT IN ('Q', 'C')", null, false)
                                     ->order_by('kode_barang', 'ASC')
                                     ->get()->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_barang/kartu_stock_gudang.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Halaman Laporan Kartu Stok Pajak (khusus barang BKP & BKPS dengan filter penjualan pajak).
     */
    public function kartu_stock_pajak_report()
    {
        $data['page_title'] = 'KARISMA - KARTU STOK PAJAK';

        // Kecualikan gudang konsinyasi & gudang virtual test market di kios dari pilihan lokasi stok pajak
        $meta = $this->_getKartuStokMeta();
        $excluded_wh = $meta['excluded_pajak_wh_ids'];

        $this->db->from('tb_gudang')->where('is_active', 1);
        if (!empty($excluded_wh)) {
            $this->db->where_not_in('id_gudang', $excluded_wh);
        }
        $data['warehouses'] = $this->db->get()->result_array();

        // Kelompok barang pajak: BKP (2) dan BKPS (3)
        $data['groups'] = $this->db->select('NOINDEX as id, DESKRIPSI as kelompok')
                                   ->from('tbkeu_kelompok_dagang')
                                   ->where_in('NOINDEX', [2, 3])
                                   ->order_by('NOINDEX', 'ASC')
                                   ->get()->result_array();

        // Hanya produk aktif yang termasuk kelompok pajak (BKP & BKPS)
        $data['products'] = $this->db->select('kode_barang, nama_barang')
                                     ->from('tbpo_barang')
                                     ->where('is_active', 'T')
                                     ->where("COALESCE(NULLIF(TRIM(kelompok_dagang), ''), NULLIF(TRIM(kelompok_barang), ''), '') IN ('2', '3')", null, false)
                                     ->order_by('kode_barang', 'ASC')
                                     ->get()->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_barang/kartu_stock_pajak.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function kartu_stock_konsinyasi_report()
    {
        $data['page_title'] = 'KARISMA - KARTU STOK BARANG KONSINYASI (FIFO)';
        
        // Ambil 3 gudang konsinyasi aktif
        $data['warehouses'] = $this->db->select('id_gudang, nama_gudang')
            ->from('tb_gudang')
            ->where_in('id_gudang', [13, 14, 15])
            ->or_like('nama_gudang', 'Konsi')
            ->or_like('nama_gudang', 'Titipan di Kios')
            ->get()->result_array();
            
        // Ambil daftar supplier konsinyasi
        $data['suppliers'] = $this->db->select('kd_suplier, nama_suplier')
            ->from('tb_konsinyasi_settlement')
            ->where('kd_suplier IS NOT NULL', null, false)
            ->where('kd_suplier !=', '')
            ->group_by(['kd_suplier', 'nama_suplier'])
            ->order_by('nama_suplier', 'ASC')
            ->get()->result_array();

        // Ambil daftar barang konsinyasi
        $data['products'] = $this->db->query("
            SELECT DISTINCT b.kode_barang, b.nama_barang
            FROM tbpo_barang b
            WHERE b.kode_barang IN (
                SELECT DISTINCT kd_barang FROM tb_konsinyasi_settlement
                UNION
                SELECT DISTINCT ld.kd_barang FROM tb_lpb_detail ld JOIN tb_lpb l ON ld.id_lpb = l.id_lpb WHERE l.gudang_id IN (13, 14, 15)
            )
            ORDER BY b.nama_barang ASC
        ")->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_barang/kartu_stock_konsinyasi.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    // ====================================================
    // Laporan Jurnal Transaksi
    // ====================================================
    public function jurnal_transaksi_report()
    {
        $data['page_title'] = 'KARISMA - LAPORAN JURNAL TRANSAKSI';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/jurnal_transaksi.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function jurnal_transaksi_data()
    {
        $start_date = $this->input->post('start_date', true);
        $end_date   = $this->input->post('end_date', true);
        $this->db->select('j.tanggal_transaksi as tanggal, j.nomor_jurnal, a.kode_akun as kd_akun, a.nama_akun, d.debit, d.kredit, j.keterangan');
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_jurnal_detail d', 'j.id_jurnal = d.id_jurnal', 'left');
        $this->db->join('tbkeu_akun a', 'd.id_akun = a.id_akun', 'left');
        if ($start_date) {
            $this->db->where('j.tanggal_transaksi >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('j.tanggal_transaksi <=', $end_date);
        }
        $this->db->order_by('j.tanggal_transaksi', 'ASC');
        $data = $this->db->get()->result_array();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $data]));
    }


    /**
     * Mengambil metadata master gudang, kelompok barang, serta ID gudang khusus.
     */
    private function _getKartuStokMeta()
    {
        $wh_konsinyasi = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->group_start()
                ->where('id_gudang', 13)
                ->or_like('nama_gudang', 'Konsi')
            ->group_end()
            ->limit(1)->get()->row_array();
        $id_wh_konsinyasi = $wh_konsinyasi ? (int)$wh_konsinyasi['id_gudang'] : 13;

        $wh_titipan_kios = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Titipan di Kios')
            ->limit(1)->get()->row_array();
        $id_wh_titipan_kios = $wh_titipan_kios ? (int)$wh_titipan_kios['id_gudang'] : 14;

        $wh_konsinyasi_lunas = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Konsinyasi Lunas')
            ->limit(1)->get()->row_array();
        $id_wh_konsinyasi_lunas = $wh_konsinyasi_lunas ? (int)$wh_konsinyasi_lunas['id_gudang'] : 15;

        $wh_test_market = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->where('nama_gudang', 'Gdg. Test Market di Kios')
            ->limit(1)->get()->row_array();
        $id_wh_test_market = $wh_test_market ? (int)$wh_test_market['id_gudang'] : null;

        $wh_transit_tm = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->group_start()
                ->where('nama_gudang', 'Gudang Test Market')
                ->or_where('nama_gudang', 'Gdg. Test Market')
            ->group_end()
            ->limit(1)->get()->row_array();
        $id_wh_transit_tm = $wh_transit_tm ? (int)$wh_transit_tm['id_gudang'] : null;
        $id_wh_transit_tm_legacy = $wh_transit_tm ? (int)$wh_transit_tm['id_gudang'] : 17;

        $wh_induk = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Induk')
            ->limit(1)->get()->row_array();
        $id_wh_induk = $wh_induk ? (int)$wh_induk['id_gudang'] : 2;

        // Ambil master gudang aktif
        $warehouses_raw = $this->db->get_where('tb_gudang', ['is_active' => 1])->result_array();
        $warehouses_map = [];
        foreach ($warehouses_raw as $w) {
            $warehouses_map[(int)$w['id_gudang']] = $w['nama_gudang'];
        }

        // Master deskripsi kelompok barang
        $groups_raw = $this->db->get('tbkeu_kelompok_dagang')->result_array();
        $groups_map = [];
        foreach ($groups_raw as $g) {
            $groups_map[$g['NOINDEX']] = $g['DESKRIPSI'];
        }

        $excluded_pajak_wh_ids = array_values(array_unique(array_filter([
            $id_wh_konsinyasi,
            $id_wh_titipan_kios,
            $id_wh_konsinyasi_lunas,
            $id_wh_test_market,
            $id_wh_transit_tm
        ], function($id) {
            return $id !== null && (int)$id > 0;
        })));

        return [
            'id_wh_konsinyasi' => $id_wh_konsinyasi,
            'id_wh_titipan_kios' => $id_wh_titipan_kios,
            'id_wh_konsinyasi_lunas' => $id_wh_konsinyasi_lunas,
            'id_wh_test_market' => $id_wh_test_market,
            'id_wh_transit_tm' => $id_wh_transit_tm,
            'id_wh_transit_tm_legacy' => $id_wh_transit_tm_legacy,
            'id_wh_induk' => $id_wh_induk,
            'excluded_pajak_wh_ids' => $excluded_pajak_wh_ids,
            'warehouses_map' => $warehouses_map,
            'groups_map' => $groups_map
        ];
    }

    /**
     * Mengeksekusi daftar subquery mutasi dan membentuk kartu stok Moving Average per barang & gudang.
     */
    private function _buildMovingAverageStockCards(array $queries, array $warehouses_map, array $groups_map, $start_date, $end_date)
    {
        if (empty($queries)) {
            return [];
        }

        // Eksekusi satu batch UNION ALL query
        $union_sql = implode(" UNION ALL ", $queries) . " ORDER BY tanggal ASC";
        $all_tx = $this->db->query($union_sql)->result_array();

        // Kelompokkan hasil per [kd_barang][gudang_id]
        $grouped = [];
        $product_info = [];

        foreach ($all_tx as $tx) {
            $kd = $tx['kd_barang'];
            $wh = (int)$tx['gudang_id'];

            if (!isset($product_info[$kd])) {
                $product_info[$kd] = [
                    'nama_barang' => $tx['nama_barang'],
                    'satuan' => $tx['satuan'],
                    'kelompok_dagang' => $tx['kelompok_dagang']
                ];
            }

            $grouped[$kd][$wh][] = $tx;
        }

        $all_products_data = [];

        // Urutkan berdasarkan kode produk
        ksort($grouped);

        foreach ($grouped as $kd => $byWarehouse) {
            ksort($byWarehouse);

            $prod_meta = $product_info[$kd];
            $grp_desc = $groups_map[$prod_meta['kelompok_dagang']] ?? 'Tanpa Kelompok';

            foreach ($byWarehouse as $wh_id => $txs) {
                $wh_name = $warehouses_map[$wh_id] ?? ('Gudang ' . $wh_id);

                // Urutkan transaksi dalam gudang ini (IN selalu didahulukan sebelum OUT di tanggal yang sama)
                usort($txs, function($a, $b) {
                    $t1 = strtotime($a['tanggal']);
                    $t2 = strtotime($b['tanggal']);
                    if ($t1 === $t2) {
                        if ($a['type'] === $b['type']) {
                            return strcmp((string)($a['referensi'] ?? ''), (string)($b['referensi'] ?? ''));
                        }
                        return ($a['type'] === 'IN') ? -1 : 1;
                    }
                    return $t1 < $t2 ? -1 : 1;
                });

                $qty_saldo = 0.0;
                $nilai_saldo = 0.0;
                $average_hpp = 0.0;

                $opening_balance = [
                    'tanggal' => '',
                    'referensi' => 'O/B Opening Balance',
                    'unit' => !empty($txs) ? $txs[0]['satuan'] : $prod_meta['satuan'],
                    'masuk_qty' => 0.0,
                    'masuk_harga' => 0.0,
                    'masuk_nilai' => 0.0,
                    'keluar_qty' => 0.0,
                    'keluar_harga' => 0.0,
                    'keluar_nilai' => 0.0,
                    'saldo_qty' => 0.0,
                    'saldo_harga' => 0.0,
                    'saldo_nilai' => 0.0
                ];

                $report_rows = [];
                $sub_masuk_qty = 0.0;
                $sub_masuk_nilai = 0.0;
                $sub_keluar_qty = 0.0;
                $sub_keluar_nilai = 0.0;

                foreach ($txs as $tx) {
                    $qty = (float)$tx['qty'];
                    $harga = (float)$tx['harga'];
                    $tx_date = $tx['tanggal'];

                    if ($tx['type'] === 'IN') {
                        $in_harga = ($harga > 0) ? $harga : $average_hpp;
                        $qty_saldo += $qty;
                        $nilai_saldo += ($qty * $in_harga);
                        $average_hpp = $qty_saldo > 0 ? ($nilai_saldo / $qty_saldo) : 0.0;
                    } else {
                        $qty_saldo -= $qty;
                        $nilai_saldo -= ($qty * $average_hpp);
                    }

                    if (strtotime($tx_date) < strtotime($start_date)) {
                        // Saldo awal sebelum periode filter
                        $opening_balance['saldo_qty'] = $qty_saldo;
                        $opening_balance['saldo_harga'] = $average_hpp;
                        $opening_balance['saldo_nilai'] = $nilai_saldo;
                    } else if (strtotime($tx_date) <= strtotime($end_date)) {
                        // Masuk ke baris transaksi periode
                        $refPrefix = $tx['ref_prefix'] ?? ($tx['type'] === 'IN' ? 'PJ ' : 'SJ ');
                        $row = [
                            'tanggal' => $tx['tanggal'],
                            'referensi' => $refPrefix . $tx['referensi'],
                            'unit' => $tx['satuan'],
                            'masuk_qty' => 0.0,
                            'masuk_harga' => 0.0,
                            'masuk_nilai' => 0.0,
                            'keluar_qty' => 0.0,
                            'keluar_harga' => 0.0,
                            'keluar_nilai' => 0.0,
                            'saldo_qty' => $qty_saldo,
                            'saldo_harga' => $average_hpp,
                            'saldo_nilai' => $nilai_saldo
                        ];

                        if ($tx['type'] === 'IN') {
                            $in_harga = ($harga > 0) ? $harga : $average_hpp;
                            $row['masuk_qty'] = $qty;
                            $row['masuk_harga'] = $in_harga;
                            $row['masuk_nilai'] = $qty * $in_harga;
                            $sub_masuk_qty += $qty;
                            $sub_masuk_nilai += ($qty * $in_harga);
                        } else {
                            $row['keluar_qty'] = $qty;
                            $row['keluar_harga'] = $average_hpp;
                            $row['keluar_nilai'] = $qty * $average_hpp;
                            $sub_keluar_qty += $qty;
                            $sub_keluar_nilai += ($qty * $average_hpp);
                        }
                        $report_rows[] = $row;
                    }
                }

                if (!empty($report_rows)) {
                    $opening_balance['unit'] = $report_rows[0]['unit'];
                }

                // Hanya sertakan kartu stok gudang ini jika ada mutasi dalam periode atau saldo awal/akhir != 0
                if (!empty($report_rows) || $opening_balance['saldo_qty'] != 0 || $qty_saldo != 0) {
                    $all_products_data[] = [
                        'id_gudang'         => $wh_id,
                        'warehouse_name'    => $wh_name,
                        'product_code'      => $kd,
                        'product_name'      => $prod_meta['nama_barang'],
                        'kelompok_barang'   => $grp_desc,
                        'opening_balance'   => $opening_balance,
                        'rows'              => $report_rows,
                        'sub_masuk_qty'     => $sub_masuk_qty,
                        'sub_masuk_harga'   => $sub_masuk_qty > 0 ? ($sub_masuk_nilai / $sub_masuk_qty) : 0.0,
                        'sub_masuk_nilai'   => $sub_masuk_nilai,
                        'sub_keluar_qty'    => $sub_keluar_qty,
                        'sub_keluar_harga'  => $sub_keluar_qty > 0 ? ($sub_keluar_nilai / $sub_keluar_qty) : 0.0,
                        'sub_keluar_nilai'  => $sub_keluar_nilai,
                        'final_saldo_qty'   => $qty_saldo,
                        'final_saldo_harga' => $average_hpp,
                        'final_saldo_nilai' => $nilai_saldo
                    ];
                }
            }
        }

        return $all_products_data;
    }

    public function kartu_stock_gudang_data()
    {
        return $this->_render_kartu_stock_gudang_data('');
    }

    /**
     * Endpoint data Kartu Stok A: hanya barang dengan awalan kode selain Q dan C (A, Z, X, dll).
     */
    public function kartu_stock_a_data()
    {
        return $this->_render_kartu_stock_gudang_data(" AND UPPER(LEFT(TRIM(b.kode_barang), 1)) NOT IN ('Q', 'C')");
    }

    private function _render_kartu_stock_gudang_data($extraProdFilter = '')
    {
        $kd_barang = $this->input->post('kd_barang', true);
        $kelompok_barang = $this->input->post('kelompok_barang', true);
        $id_gudang = $this->input->post('id_gudang', true);
        $start_date = $this->input->post('start_date', true);
        $end_date = $this->input->post('end_date', true);

        $meta = $this->_getKartuStokMeta();
        $id_wh_konsinyasi = $meta['id_wh_konsinyasi'];
        $id_wh_titipan_kios = $meta['id_wh_titipan_kios'];
        $id_wh_konsinyasi_lunas = $meta['id_wh_konsinyasi_lunas'];
        $id_wh_test_market = $meta['id_wh_test_market'];
        $id_wh_transit_tm = $meta['id_wh_transit_tm_legacy'];
        $id_wh_induk = $meta['id_wh_induk'];
        $warehouses_map = $meta['warehouses_map'];
        $groups_map = $meta['groups_map'];

        $wh_target_id = ($id_gudang && $id_gudang !== 'all') ? (int)$id_gudang : null;

        // Filter produk dan kelompok untuk klausul WHERE
        $prod_filter_clause = (string) $extraProdFilter;
        if ($kd_barang && $kd_barang !== 'all') {
            $prod_filter_clause .= " AND b.kode_barang = " . $this->db->escape($kd_barang);
        }
        if ($kelompok_barang && $kelompok_barang !== 'all') {
            $prod_filter_clause .= " AND b.kelompok_dagang = " . $this->db->escape($kelompok_barang);
        }

        // 1. Query LPB (Penerimaan Barang / Masuk)
        $sql_lpb = "SELECT 
                        ld.kd_barang,
                        l.gudang_id,
                        l.tgl_sj AS tanggal,
                        l.nomor_lpb AS referensi,
                        ld.qty_diterima AS qty,
                        ld.harga_satuan AS harga,
                        'IN' AS type,
                        'PJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        b.kelompok_dagang
                    FROM tb_lpb l
                    JOIN tb_lpb_detail ld ON l.id_lpb = ld.id_lpb
                    JOIN tbpo_barang b ON ld.kd_barang = b.kode_barang
                    WHERE l.status_lpb = 1 AND l.tgl_sj <= " . $this->db->escape($end_date) . "
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_lpb .= " AND l.gudang_id = {$wh_target_id}";
        }

        // 2. Query Retur Penjualan (Masuk)
        $sql_retur = "SELECT 
                        b.kode_barang AS kd_barang,
                        r.gudang_id,
                        r.tanggal_retur AS tanggal,
                        r.no_retur AS referensi,
                        rd.qty_retur AS qty,
                        COALESCE((
                            SELECT fd.hrg_pokok
                            FROM tbso_faktur_detail fd
                            WHERE fd.no_faktur = rd.no_faktur
                              AND fd.kd_barang = b.kode_barang
                              AND (fd.no_lot = rd.no_batch OR rd.no_batch IS NULL OR rd.no_batch = '')
                            ORDER BY fd.id DESC
                            LIMIT 1
                        ), 0) AS harga,
                        'IN' AS type,
                        'RJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        b.kelompok_dagang
                    FROM tbrp_retur_penjualan_header r
                    JOIN tbrp_retur_penjualan_detail rd ON r.id_retur = rd.id_retur
                    JOIN tbpo_barang b ON (
                        (rd.kd_barang IS NOT NULL AND rd.kd_barang != '' AND rd.kd_barang = b.kode_barang) OR
                        (TRIM(LOWER(rd.nama_barang)) = TRIM(LOWER(b.nama_barang)))
                    )
                    WHERE r.status_retur NOT IN ('ditolak', 'batal') AND r.tanggal_retur <= " . $this->db->escape($end_date) . "
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_retur .= " AND r.gudang_id = {$wh_target_id}";
        }

        // 3. Query Faktur Penjualan (Keluar)
        $sql_faktur = "SELECT 
                        fd.kd_barang,
                        f.gudang_id,
                        f.tanggal_faktur AS tanggal,
                        f.no_faktur AS referensi,
                        fd.qty,
                        fd.hrg_pokok AS harga,
                        'OUT' AS type,
                        'SJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        b.kelompok_dagang
                    FROM tbso_faktur_penjualan f
                    JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                    JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                    WHERE f.status NOT IN ('draft', 'cancelled') AND f.tanggal_faktur <= " . $this->db->escape($end_date) . "
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_faktur .= " AND f.gudang_id = {$wh_target_id}";
        }

        $queries = [
            "($sql_lpb)",
            "($sql_retur)",
            "($sql_faktur)"
        ];

        // 4. Arus khusus Test Market pada gudang virtual di kios.
        // Faktur T memindahkan posisi stok ke kios, sedangkan realisasi
        // pembayaran mengeluarkan qty yang benar-benar dibeli oleh kios.
        if ($id_wh_test_market
            && (!$wh_target_id || $wh_target_id === $id_wh_test_market)
            && $this->db->table_exists('tb_test_market_position')) {
            $sql_test_market_in = "SELECT
                                p.kd_barang,
                                {$id_wh_test_market} AS gudang_id,
                                f.tanggal_faktur AS tanggal,
                                CONCAT(p.no_faktur, ' (Titip ke ', COALESCE(p.customer_name, 'Kios'), ')') AS referensi,
                                p.qty_titip AS qty,
                                p.harga_pokok AS harga,
                                'IN' AS type,
                                'TM-TITIP ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tb_test_market_position p
                            JOIN tbso_faktur_penjualan f ON f.id_faktur = p.id_faktur
                            JOIN tbpo_barang b ON b.kode_barang = p.kd_barang
                            WHERE p.status != 'DIBATALKAN'
                              AND f.status NOT IN ('draft', 'cancelled')
                              AND f.tanggal_faktur <= " . $this->db->escape($end_date) . "
                              {$prod_filter_clause}";
            $queries[] = "($sql_test_market_in)";

            if ($this->db->table_exists('tb_test_market_realisasi')) {
                $sql_test_market_out = "SELECT
                                r.kd_barang,
                                {$id_wh_test_market} AS gudang_id,
                                r.tanggal_realisasi AS tanggal,
                                CONCAT(r.no_faktur, ' (Dibeli ', COALESCE(p.customer_name, 'Kios'), ')') AS referensi,
                                r.qty,
                                p.harga_pokok AS harga,
                                'OUT' AS type,
                                'TM-TERJUAL ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tb_test_market_realisasi r
                            JOIN tb_test_market_position p ON p.id_position = r.id_position
                            JOIN tbpo_barang b ON b.kode_barang = r.kd_barang
                            WHERE r.status = 'POSTED'
                              AND r.tanggal_realisasi <= " . $this->db->escape($end_date) . "
                              {$prod_filter_clause}";
                $queries[] = "($sql_test_market_out)";
            }
        }

        // 4b. Arus Mutasi SO Test Market (Dari Gudang Induk ke Gudang Test Market)
        if ($this->db->table_exists('tbso_sales_order') && $this->db->table_exists('tbso_sales_order_detail')) {
            // IN: Masuk ke Gudang Test Market saat SO dibuat
            if (!$wh_target_id || $wh_target_id == $id_wh_transit_tm) {
                $sql_tm_so_in = "SELECT 
                                    sd.kd_barang,
                                    {$id_wh_transit_tm} AS gudang_id,
                                    COALESCE(so.tanggal_transaksi, DATE(so.create_at)) AS tanggal,
                                    CONCAT(so.no_so, ' (Mutasi SO dari Gdg. Induk)') AS referensi,
                                    sd.qty,
                                    sd.hrg_pokok AS harga,
                                    'IN' AS type,
                                    'SO ' AS ref_prefix,
                                    b.satuan,
                                    b.nama_barang,
                                    b.kelompok_dagang
                                FROM tbso_sales_order so
                                JOIN tbso_sales_order_detail sd ON so.id_so = sd.id_so
                                JOIN tbpo_barang b ON sd.kd_barang = b.kode_barang
                                WHERE so.is_test_market = 1
                                  AND so.status NOT IN ('cancelled')
                                  AND COALESCE(so.tanggal_transaksi, DATE(so.create_at)) <= " . $this->db->escape($end_date) . "
                                  {$prod_filter_clause}";
                $queries[] = "($sql_tm_so_in)";
            }

            // OUT: Keluar dari Gudang Induk menuju Gudang Test Market saat SO dibuat
            if (!$wh_target_id || $wh_target_id == $id_wh_induk) {
                $sql_tm_so_out = "SELECT 
                                    sd.kd_barang,
                                    {$id_wh_induk} AS gudang_id,
                                    COALESCE(so.tanggal_transaksi, DATE(so.create_at)) AS tanggal,
                                    CONCAT(so.no_so, ' (Mutasi SO ke Gudang Test Market)') AS referensi,
                                    sd.qty,
                                    sd.hrg_pokok AS harga,
                                    'OUT' AS type,
                                    'SO ' AS ref_prefix,
                                    b.satuan,
                                    b.nama_barang,
                                    b.kelompok_dagang
                                FROM tbso_sales_order so
                                JOIN tbso_sales_order_detail sd ON so.id_so = sd.id_so
                                JOIN tbpo_barang b ON sd.kd_barang = b.kode_barang
                                WHERE so.is_test_market = 1
                                  AND so.status NOT IN ('cancelled')
                                  AND COALESCE(so.tanggal_transaksi, DATE(so.create_at)) <= " . $this->db->escape($end_date) . "
                                  {$prod_filter_clause}";
                $queries[] = "($sql_tm_so_out)";
            }
        }

        // 5. Query Retur Pembelian (Keluar) jika tabel ada
        if ($this->db->table_exists('tb_retur_pembelian') && $this->db->table_exists('tb_retur_pembelian_detail')) {
            $sql_retur_beli = "SELECT 
                                rbd.kd_barang,
                                rb.gudang_id,
                                rb.tanggal_retur AS tanggal,
                                rb.no_retur_pembelian AS referensi,
                                rbd.qty_retur AS qty,
                                rbd.harga_satuan AS harga,
                                'OUT' AS type,
                                'RB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tb_retur_pembelian rb
                            JOIN tb_retur_pembelian_detail rbd ON rb.id_retur_pembelian = rbd.id_retur_pembelian
                            JOIN tbpo_barang b ON rbd.kd_barang = b.kode_barang
                            WHERE rb.status NOT IN ('DRAFT', 'REJECTED', 'BATAL') AND rb.tanggal_retur <= " . $this->db->escape($end_date) . "
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_retur_beli .= " AND rb.gudang_id = {$wh_target_id}";
            }
            $queries[] = "($sql_retur_beli)";
        }

        // 6. Khusus Konsinyasi Titipan di Kios
        if (!$wh_target_id || $wh_target_id == $id_wh_titipan_kios) {
            // IN: Faktur Penjualan yang dititipkan ke Kios bersumber dari Gudang Konsinyasi
            $sql_kios_in = "SELECT 
                                fd.kd_barang,
                                {$id_wh_titipan_kios} AS gudang_id,
                                f.tanggal_faktur AS tanggal,
                                CONCAT(f.no_faktur, ' (', COALESCE(f.customer_name, 'Kios'), ')') AS referensi,
                                fd.qty,
                                fd.hrg_pokok AS harga,
                                'IN' AS type,
                                'TITIP-IN ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tbso_faktur_penjualan f
                            JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                            JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                            WHERE f.status NOT IN ('draft', 'cancelled') 
                              AND f.gudang_id = {$id_wh_konsinyasi}
                              AND f.tanggal_faktur <= " . $this->db->escape($end_date) . "
                              {$prod_filter_clause}";
            $queries[] = "($sql_kios_in)";

            // OUT: Barang konsinyasi diretur dari kios kembali ke gudang konsinyasi.
            $sql_kios_return_out = "SELECT
                                rd.kd_barang,
                                {$id_wh_titipan_kios} AS gudang_id,
                                r.tanggal_retur AS tanggal,
                                CONCAT(r.no_retur, ' / Kembali ke Gudang Konsinyasi') AS referensi,
                                rd.qty_retur AS qty,
                                COALESCE((
                                    SELECT fd.hrg_pokok
                                    FROM tbso_faktur_detail fd
                                    WHERE fd.no_faktur = rd.no_faktur
                                      AND fd.kd_barang = rd.kd_barang
                                      AND (fd.no_lot = rd.no_batch OR rd.no_batch IS NULL OR rd.no_batch = '')
                                    ORDER BY fd.id DESC
                                    LIMIT 1
                                ), 0) AS harga,
                                'OUT' AS type,
                                'RETUR-KONSINYASI ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tbrp_retur_penjualan_header r
                            JOIN tbrp_retur_penjualan_detail rd ON r.id_retur = rd.id_retur
                            JOIN tbpo_barang b ON rd.kd_barang = b.kode_barang
                            WHERE r.tipe_retur = 'konsinyasi'
                              AND r.status_retur NOT IN ('ditolak', 'batal')
                              AND r.tanggal_retur <= " . $this->db->escape($end_date) . "
                              {$prod_filter_clause}";
            $queries[] = "($sql_kios_return_out)";

            // OUT: Barang keluar dari Gudang Titipan di Kios ketika dilakukan pembayaran di Keuangan (keuangan/pembayaran/bayar)
            // Yaitu saat kios membeli/membayar barang konsinyasi yang laku (tercatat di pembayaran faktur & settlement)
            if ($this->db->table_exists('tb_konsinyasi_settlement')) {
                $sql_kios_out = "SELECT 
                                    s.kd_barang,
                                    {$id_wh_titipan_kios} AS gudang_id,
                                    COALESCE(p.tanggal_pembayaran, s.tanggal_settlement, DATE(s.created_at)) AS tanggal,
                                    CONCAT(COALESCE(p.no_faktur_konsinyasi, s.no_faktur, s.no_settlement), ' (', COALESCE(s.customer_name, 'Kios'), ')') AS referensi,
                                    s.qty_net AS qty,
                                    COALESCE(fd.hrg_pokok, s.hrg_jual, 0) AS harga,
                                    'OUT' AS type,
                                    'TITIP-OUT ' AS ref_prefix,
                                    b.satuan,
                                    b.nama_barang,
                                    b.kelompok_dagang
                                FROM tb_konsinyasi_settlement s
                                JOIN tbpo_barang b ON s.kd_barang = b.kode_barang
                                LEFT JOIN tbkeu_pembayaran_faktur p ON p.id_pembayaran = s.id_pembayaran
                                LEFT JOIN tbso_faktur_detail fd ON fd.id_faktur = s.id_faktur AND fd.kd_barang = s.kd_barang AND (fd.no_lot = s.no_lot OR s.no_lot IS NULL OR s.no_lot = '')
                                WHERE (s.id_pembayaran IS NOT NULL OR s.status IN ('LAKU', 'BILLED'))
                                  AND (p.status IS NULL OR p.status NOT IN ('CANCELLED', 'UNPOST', 'DRAFT'))
                                  AND COALESCE(p.tanggal_pembayaran, s.tanggal_settlement, DATE(s.created_at)) <= " . $this->db->escape($end_date) . "
                                  {$prod_filter_clause}";
                $queries[] = "($sql_kios_out)";
            }
        }

        // 7. Khusus Konsinyasi Lunas
        if ((!$wh_target_id || $wh_target_id == $id_wh_konsinyasi_lunas) && $this->db->table_exists('tb_konsinyasi_settlement')) {
            // IN: Penerimaan pembelian resmi dari Supplier (status BILLED)
            $sql_lunas_in = "SELECT 
                                s.kd_barang,
                                {$id_wh_konsinyasi_lunas} AS gudang_id,
                                COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) AS tanggal,
                                CONCAT(s.no_settlement, ' (', s.nama_suplier, ' - INV: ', COALESCE(s.no_invoice_supplier, '-'), ')') AS referensi,
                                s.qty_net AS qty,
                                s.hrg_beli_satuan AS harga,
                                'IN' AS type,
                                'LUNAS-IN ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tb_konsinyasi_settlement s
                            JOIN tbpo_barang b ON s.kd_barang = b.kode_barang
                            WHERE s.status = 'BILLED'
                              AND COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) <= " . $this->db->escape($end_date) . "
                              {$prod_filter_clause}";
            $queries[] = "($sql_lunas_in)";

            // OUT: Pengeluaran barang konsinyasi lunas karena telah dibeli/diserahkan ke Kios
            $sql_lunas_out = "SELECT 
                                s.kd_barang,
                                {$id_wh_konsinyasi_lunas} AS gudang_id,
                                COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) AS tanggal,
                                CONCAT(COALESCE(p.no_faktur_konsinyasi, s.no_faktur, s.no_settlement), ' (Penjualan ke ', COALESCE(s.customer_name, 'Kios'), ' - Ref: ', s.no_settlement, ')') AS referensi,
                                s.qty_net AS qty,
                                s.hrg_beli_satuan AS harga,
                                'OUT' AS type,
                                'LUNAS-OUT ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tb_konsinyasi_settlement s
                            JOIN tbpo_barang b ON s.kd_barang = b.kode_barang
                            LEFT JOIN tbkeu_pembayaran_faktur p ON p.id_pembayaran = s.id_pembayaran
                            WHERE s.status = 'BILLED'
                              AND COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) <= " . $this->db->escape($end_date) . "
                              {$prod_filter_clause}";
            $queries[] = "($sql_lunas_out)";
        }

        // 8. Query Penyesuaian Barang
        if ($this->db->table_exists('tbkeu_penyesuaian_barang') && $this->db->table_exists('tbkeu_penyesuaian_barang_detail')) {
            // A. Penyesuaian satu gudang / gudang tujuan
            $sql_adj_in = "SELECT 
                                pbd.kd_barang,
                                IF(pb.id_gudang_ke IS NOT NULL AND pb.id_gudang_ke > 0, pb.id_gudang_ke, pb.id_gudang_dari) AS gudang_id,
                                pb.tanggal,
                                pb.no_referensi AS referensi,
                                ABS(pbd.jumlah) AS qty,
                                0 AS harga,
                                'IN' AS type,
                                'PB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tbkeu_penyesuaian_barang pb
                            JOIN tbkeu_penyesuaian_barang_detail pbd ON pb.id_penyesuaian = pbd.id_penyesuaian
                            JOIN tbpo_barang b ON pbd.kd_barang = b.kode_barang
                            WHERE pb.status = 'POSTED' AND pbd.jumlah > 0 AND pb.tanggal <= " . $this->db->escape($end_date) . "
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_adj_in .= " AND IF(pb.id_gudang_ke IS NOT NULL AND pb.id_gudang_ke > 0, pb.id_gudang_ke, pb.id_gudang_dari) = {$wh_target_id}";
            }
            $queries[] = "($sql_adj_in)";

            $sql_adj_out = "SELECT 
                                pbd.kd_barang,
                                IF(pb.id_gudang_dari IS NOT NULL AND pb.id_gudang_dari > 0, pb.id_gudang_dari, pb.id_gudang_ke) AS gudang_id,
                                pb.tanggal,
                                pb.no_referensi AS referensi,
                                ABS(pbd.jumlah) AS qty,
                                0 AS harga,
                                'OUT' AS type,
                                'PB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                b.kelompok_dagang
                            FROM tbkeu_penyesuaian_barang pb
                            JOIN tbkeu_penyesuaian_barang_detail pbd ON pb.id_penyesuaian = pbd.id_penyesuaian
                            JOIN tbpo_barang b ON pbd.kd_barang = b.kode_barang
                            WHERE pb.status = 'POSTED' AND pbd.jumlah < 0 AND pb.tanggal <= " . $this->db->escape($end_date) . "
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_adj_out .= " AND IF(pb.id_gudang_dari IS NOT NULL AND pb.id_gudang_dari > 0, pb.id_gudang_dari, pb.id_gudang_ke) = {$wh_target_id}";
            }
            $queries[] = "($sql_adj_out)";
        }

        $all_products_data = $this->_buildMovingAverageStockCards($queries, $warehouses_map, $groups_map, $start_date, $end_date);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $all_products_data
            ]));
    }

    /**
     * Endpoint JSON Laporan Kartu Stok Pajak.
     * Aturan:
     * 1. Hanya barang pajak (kelompok BKP = '2' & BKPS = '3').
     * 2. Semua penjualan reguler masuk KECUALI konsinyasi, test market, dan faktur Z induk.
     * 3. Penjualan test market yang masuk hanya yang sudah lunas (realisasi POSTED & pembayaran valid).
     * 4. Penjualan faktur Z yang dipecah diambil dari stok Faktur H hasil pemecahan.
     */
    public function kartu_stock_pajak_data()
    {
        $kd_barang = $this->input->post('kd_barang', true);
        $kelompok_barang = $this->input->post('kelompok_barang', true);
        $id_gudang = $this->input->post('id_gudang', true);
        $start_date = $this->input->post('start_date', true);
        $end_date = $this->input->post('end_date', true);

        $meta = $this->_getKartuStokMeta();
        $id_wh_transit_tm = $meta['id_wh_transit_tm'];
        $id_wh_induk = $meta['id_wh_induk'];
        $excluded_wh = $meta['excluded_pajak_wh_ids'];
        $excluded_wh_sql = !empty($excluded_wh) ? implode(',', array_map('intval', $excluded_wh)) : '';
        $warehouses_map = $meta['warehouses_map'];
        $groups_map = $meta['groups_map'];

        $wh_target_id = ($id_gudang && $id_gudang !== 'all') ? (int)$id_gudang : null;
        $end_date_sql = $this->db->escape($end_date);

        // Kelompok barang pajak wajib BKP ('2') & BKPS ('3')
        $pajak_group_expr = "COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '')";
        $prod_filter_clause = " AND {$pajak_group_expr} IN ('2', '3')";
        if ($kd_barang && $kd_barang !== 'all') {
            $prod_filter_clause .= " AND b.kode_barang = " . $this->db->escape($kd_barang);
        }
        if ($kelompok_barang && $kelompok_barang !== 'all') {
            $prod_filter_clause .= " AND {$pajak_group_expr} = " . $this->db->escape((string)$kelompok_barang);
        }

        $queries = [];

        // 1. Query LPB (Penerimaan Barang / Masuk) - khusus barang kode Q & C, non konsinyasi & non virtual TM
        $lpb_where_extra = " AND UPPER(LEFT(TRIM(ld.kd_barang), 1)) IN ('Q', 'C')
                             AND UPPER(LEFT(TRIM(COALESCE(l.nomor_lpb, '')), 4)) != 'LPBK'
                             AND COALESCE(UPPER(l.jenis_lpb), '') NOT LIKE '%KONSINYASI%'";
        if ($excluded_wh_sql !== '') {
            $lpb_where_extra .= " AND l.gudang_id NOT IN ({$excluded_wh_sql})";
        }
        if ($this->db->field_exists('is_konsinyasi', 'tb_lpb')) {
            $lpb_where_extra .= " AND COALESCE(l.is_konsinyasi, 0) = 0";
        }
        $sql_lpb = "SELECT 
                        ld.kd_barang,
                        l.gudang_id,
                        l.tgl_sj AS tanggal,
                        l.nomor_lpb AS referensi,
                        ld.qty_diterima AS qty,
                        ld.harga_satuan AS harga,
                        'IN' AS type,
                        'PJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        {$pajak_group_expr} AS kelompok_dagang
                    FROM tb_lpb l
                    JOIN tb_lpb_detail ld ON l.id_lpb = ld.id_lpb
                    JOIN tbpo_barang b ON ld.kd_barang = b.kode_barang
                    WHERE l.status_lpb = 1 AND l.tgl_sj <= {$end_date_sql}
                    {$lpb_where_extra}
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_lpb .= " AND l.gudang_id = {$wh_target_id}";
        }
        $queries[] = "($sql_lpb)";

        // 2. Query Retur Penjualan (Masuk) - hanya tipe refund ('biasa'/'refund'), kecualikan replace/service/konsinyasi, faktur T/K, dan gudang konsinyasi/TM
        $retur_where_extra = " AND COALESCE(LOWER(TRIM(r.tipe_retur)), 'biasa') IN ('biasa', 'refund')
                               AND UPPER(LEFT(TRIM(COALESCE(rd.no_faktur, '')), 1)) NOT IN ('K', 'T')";
        if ($excluded_wh_sql !== '') {
            $retur_where_extra .= " AND r.gudang_id NOT IN ({$excluded_wh_sql})";
        }
        $sql_retur = "SELECT 
                        b.kode_barang AS kd_barang,
                        r.gudang_id,
                        r.tanggal_retur AS tanggal,
                        r.no_retur AS referensi,
                        rd.qty_retur AS qty,
                        COALESCE((
                            SELECT fd.hrg_pokok
                            FROM tbso_faktur_detail fd
                            WHERE fd.no_faktur = rd.no_faktur
                              AND fd.kd_barang = b.kode_barang
                              AND (fd.no_lot = rd.no_batch OR rd.no_batch IS NULL OR rd.no_batch = '')
                            ORDER BY fd.id DESC
                            LIMIT 1
                        ), 0) AS harga,
                        'IN' AS type,
                        'RJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        {$pajak_group_expr} AS kelompok_dagang
                    FROM tbrp_retur_penjualan_header r
                    JOIN tbrp_retur_penjualan_detail rd ON r.id_retur = rd.id_retur
                    JOIN tbpo_barang b ON (
                        (rd.kd_barang IS NOT NULL AND rd.kd_barang != '' AND rd.kd_barang = b.kode_barang) OR
                        (TRIM(LOWER(rd.nama_barang)) = TRIM(LOWER(b.nama_barang)))
                    )
                    WHERE r.status_retur NOT IN ('ditolak', 'batal') AND r.tanggal_retur <= {$end_date_sql}
                    {$retur_where_extra}
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_retur .= " AND r.gudang_id = {$wh_target_id}";
        }
        $queries[] = "($sql_retur)";

        // 3. Query Faktur Penjualan Reguler (Keluar)
        // Kecualikan konsinyasi (K & gudang konsinyasi), Test Market (T & is_test_market), dan Faktur Z (Z & is_split_parent)
        $faktur_where_extra = " AND UPPER(LEFT(TRIM(f.no_faktur), 1)) NOT IN ('K', 'T', 'Z')";
        if ($excluded_wh_sql !== '') {
            $faktur_where_extra .= " AND f.gudang_id NOT IN ({$excluded_wh_sql})";
        }
        if ($this->db->field_exists('is_test_market', 'tbso_faktur_penjualan')) {
            $faktur_where_extra .= " AND COALESCE(f.is_test_market, 0) = 0";
        }
        if ($this->db->field_exists('is_split_parent', 'tbso_faktur_penjualan')) {
            $faktur_where_extra .= " AND COALESCE(f.is_split_parent, 0) = 0";
        }
        if ($this->db->field_exists('is_konsinyasi', 'tbso_faktur_penjualan')) {
            $faktur_where_extra .= " AND COALESCE(f.is_konsinyasi, 0) = 0";
        }
        $sql_faktur = "SELECT 
                        fd.kd_barang,
                        f.gudang_id,
                        f.tanggal_faktur AS tanggal,
                        f.no_faktur AS referensi,
                        fd.qty,
                        fd.hrg_pokok AS harga,
                        'OUT' AS type,
                        'SJ ' AS ref_prefix,
                        b.satuan,
                        b.nama_barang,
                        {$pajak_group_expr} AS kelompok_dagang
                    FROM tbso_faktur_penjualan f
                    JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                    JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                    WHERE f.status NOT IN ('draft', 'cancelled') AND f.tanggal_faktur <= {$end_date_sql}
                    {$faktur_where_extra}
                    {$prod_filter_clause}";
        if ($wh_target_id) {
            $sql_faktur .= " AND f.gudang_id = {$wh_target_id}";
        }
        $queries[] = "($sql_faktur)";

        // 4. Query Penjualan Faktur H (hasil pemecahan Faktur Z yang masuk ke kartu stok pajak)
        if ($this->db->table_exists('tbso_faktur_z_pecah') && $this->db->table_exists('tbso_faktur_z_pecah_detail')) {
            $h_wh_expr = "CASE
                            WHEN pd.gudang_id REGEXP '^[0-9]+$' AND CAST(pd.gudang_id AS UNSIGNED) > 0 THEN CAST(pd.gudang_id AS UNSIGNED)
                            WHEN p.gudang_id REGEXP '^[0-9]+$' AND CAST(p.gudang_id AS UNSIGNED) > 0 THEN CAST(p.gudang_id AS UNSIGNED)
                            WHEN fp.gudang_id IS NOT NULL AND fp.gudang_id > 0 THEN CAST(fp.gudang_id AS UNSIGNED)
                            ELSE {$id_wh_induk}
                          END";
            $h_date_expr = "COALESCE(NULLIF(p.tanggal_faktur, '0000-00-00'), fp.tanggal_faktur)";

            $h_where_extra = "";
            if ($excluded_wh_sql !== '') {
                $h_where_extra .= " AND ({$h_wh_expr}) NOT IN ({$excluded_wh_sql})";
            }
            if ($wh_target_id) {
                $h_where_extra .= " AND ({$h_wh_expr}) = {$wh_target_id}";
            }

            $sql_faktur_h = "SELECT 
                                pd.kd_barang,
                                ({$h_wh_expr}) AS gudang_id,
                                ({$h_date_expr}) AS tanggal,
                                CONCAT(p.no_faktur, IF(COALESCE(p.parent_no_faktur, fp.no_faktur, '') != '', CONCAT(' (Ref Z: ', COALESCE(p.parent_no_faktur, fp.no_faktur), ')'), '')) AS referensi,
                                pd.qty,
                                COALESCE(NULLIF(pd.hrg_pokok, 0), fdp.hrg_pokok, 0) AS harga,
                                'OUT' AS type,
                                'SJ-H ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                {$pajak_group_expr} AS kelompok_dagang
                            FROM tbso_faktur_z_pecah p
                            JOIN tbso_faktur_z_pecah_detail pd ON p.id_pecah = pd.id_pecah
                            JOIN tbpo_barang b ON pd.kd_barang = b.kode_barang
                            LEFT JOIN tbso_faktur_penjualan fp ON fp.id_faktur = p.parent_id_faktur
                            LEFT JOIN tbso_faktur_detail fdp ON fdp.id = pd.id_faktur_detail_parent
                            WHERE LOWER(COALESCE(p.status, 'confirmed')) NOT IN ('draft', 'cancelled')
                              AND (fp.id_faktur IS NULL OR LOWER(COALESCE(fp.status, '')) NOT IN ('draft', 'cancelled'))
                              AND ({$h_date_expr}) <= {$end_date_sql}
                              {$h_where_extra}
                              {$prod_filter_clause}";
            $queries[] = "($sql_faktur_h)";
        }

        // 5. Query Penjualan Test Market yang sudah dibayar & terbit faktur realisasi (T...-1, T...-2, dst)
        // Diakui sebagai pembelian (IN) dan penjualan (OUT) sekaligus di kartu stok pajak
        if ($this->db->table_exists('tb_test_market_realisasi') && $this->db->table_exists('tb_test_market_position')) {
            $tm_wh_expr = ($id_wh_transit_tm && $id_wh_transit_tm > 0)
                ? "IF(p.gudang_asal_id REGEXP '^[0-9]+$' AND CAST(p.gudang_asal_id AS UNSIGNED) > 0 AND CAST(p.gudang_asal_id AS UNSIGNED) != {$id_wh_transit_tm}, CAST(p.gudang_asal_id AS UNSIGNED), {$id_wh_induk})"
                : "IF(p.gudang_asal_id REGEXP '^[0-9]+$' AND CAST(p.gudang_asal_id AS UNSIGNED) > 0, CAST(p.gudang_asal_id AS UNSIGNED), {$id_wh_induk})";

            $has_pay_table = $this->db->table_exists('tbkeu_pembayaran_faktur');
            $pay_join = $has_pay_table ? "LEFT JOIN tbkeu_pembayaran_faktur pay ON pay.id_pembayaran = r.id_pembayaran" : "";
            $tm_date_expr = $has_pay_table ? "COALESCE(NULLIF(pay.tanggal_pembayaran, '0000-00-00'), r.tanggal_realisasi)" : "r.tanggal_realisasi";
            $tm_ref_expr = $has_pay_table
                ? "COALESCE(NULLIF(TRIM(r.no_faktur_realisasi), ''), NULLIF(TRIM(pay.no_faktur_konsinyasi), ''), '')"
                : "COALESCE(NULLIF(TRIM(r.no_faktur_realisasi), ''), '')";

            $tm_where_extra = " AND ({$tm_ref_expr}) != ''";
            if ($has_pay_table) {
                $pay_valid = "COALESCE(UPPER(pay.status), '') NOT IN ('CANCELLED', 'UNPOST', 'DRAFT')";
                if ($this->db->field_exists('status_bg', 'tbkeu_pembayaran_faktur')) {
                    $pay_valid .= " AND COALESCE(LOWER(pay.status_bg), '') != 'pending'";
                }
                if ($this->db->field_exists('status_kasir', 'tbkeu_pembayaran_faktur')) {
                    $pay_valid .= " AND COALESCE(LOWER(pay.status_kasir), '') != 'pending_kasir'";
                }
                $tm_where_extra .= " AND (pay.id_pembayaran IS NULL OR ({$pay_valid}))";
            }
            if ($excluded_wh_sql !== '') {
                $tm_where_extra .= " AND ({$tm_wh_expr}) NOT IN ({$excluded_wh_sql})";
            }
            if ($wh_target_id) {
                $tm_where_extra .= " AND ({$tm_wh_expr}) = {$wh_target_id}";
            }

            // 5a. Test Market Lunas - Masuk (Pembelian)
            $sql_tm_beli = "SELECT 
                                r.kd_barang,
                                ({$tm_wh_expr}) AS gudang_id,
                                ({$tm_date_expr}) AS tanggal,
                                CONCAT({$tm_ref_expr}, ' (TM Beli - ', COALESCE(p.customer_name, 'Kios'), ')') AS referensi,
                                r.qty,
                                COALESCE(p.harga_pokok, 0) AS harga,
                                'IN' AS type,
                                'TM-BELI ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                {$pajak_group_expr} AS kelompok_dagang
                            FROM tb_test_market_realisasi r
                            JOIN tb_test_market_position p ON p.id_position = r.id_position
                            JOIN tbpo_barang b ON b.kode_barang = r.kd_barang
                            {$pay_join}
                            WHERE r.status = 'POSTED'
                              AND p.status != 'DIBATALKAN'
                              AND ({$tm_date_expr}) <= {$end_date_sql}
                              {$tm_where_extra}
                              {$prod_filter_clause}";
            $queries[] = "($sql_tm_beli)";

            // 5b. Test Market Lunas - Keluar (Penjualan)
            $sql_tm_jual = "SELECT 
                                r.kd_barang,
                                ({$tm_wh_expr}) AS gudang_id,
                                ({$tm_date_expr}) AS tanggal,
                                CONCAT({$tm_ref_expr}, ' (TM Jual - ', COALESCE(p.customer_name, 'Kios'), ')') AS referensi,
                                r.qty,
                                COALESCE(p.harga_pokok, 0) AS harga,
                                'OUT' AS type,
                                'TM-JUAL ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                {$pajak_group_expr} AS kelompok_dagang
                            FROM tb_test_market_realisasi r
                            JOIN tb_test_market_position p ON p.id_position = r.id_position
                            JOIN tbpo_barang b ON b.kode_barang = r.kd_barang
                            {$pay_join}
                            WHERE r.status = 'POSTED'
                              AND p.status != 'DIBATALKAN'
                              AND ({$tm_date_expr}) <= {$end_date_sql}
                              {$tm_where_extra}
                              {$prod_filter_clause}";
            $queries[] = "($sql_tm_jual)";
        }

        // 5c. Query Penjualan Konsinyasi yang sudah dilunasi ke supplier (status BILLED / masuk Gdg. Konsinyasi Lunas)
        // Diakui sebagai pembelian (IN) dan penjualan (OUT) sekaligus di kartu stok pajak
        if ($this->db->table_exists('tb_konsinyasi_settlement') && (!$wh_target_id || $wh_target_id == $id_wh_induk)) {
            $has_pay_table = $this->db->table_exists('tbkeu_pembayaran_faktur');
            $kons_pay_join = $has_pay_table ? "LEFT JOIN tbkeu_pembayaran_faktur pay ON pay.id_pembayaran = s.id_pembayaran" : "";
            $kons_date_expr = "COALESCE(NULLIF(s.tgl_invoice_supplier, '0000-00-00'), DATE(s.settled_at), s.tanggal_settlement, DATE(s.created_at))";
            $kons_ref_expr = $has_pay_table
                ? "COALESCE(NULLIF(TRIM(pay.no_faktur_konsinyasi), ''), NULLIF(TRIM(s.no_faktur), ''), s.no_settlement)"
                : "COALESCE(NULLIF(TRIM(s.no_faktur), ''), s.no_settlement)";

            $kons_where_extra = "";
            if ($has_pay_table) {
                $pay_valid = "COALESCE(UPPER(pay.status), '') NOT IN ('CANCELLED', 'UNPOST', 'DRAFT')";
                if ($this->db->field_exists('status_bg', 'tbkeu_pembayaran_faktur')) {
                    $pay_valid .= " AND COALESCE(LOWER(pay.status_bg), '') != 'pending'";
                }
                if ($this->db->field_exists('status_kasir', 'tbkeu_pembayaran_faktur')) {
                    $pay_valid .= " AND COALESCE(LOWER(pay.status_kasir), '') != 'pending_kasir'";
                }
                $kons_where_extra .= " AND (pay.id_pembayaran IS NULL OR ({$pay_valid}))";
            }

            // Konsinyasi Lunas - Masuk (Pembelian)
            $sql_kons_beli = "SELECT 
                                s.kd_barang,
                                {$id_wh_induk} AS gudang_id,
                                ({$kons_date_expr}) AS tanggal,
                                CONCAT({$kons_ref_expr}, ' (Konsinyasi Beli - ', COALESCE(s.nama_suplier, 'Supplier'), ')') AS referensi,
                                s.qty_net AS qty,
                                COALESCE(s.hrg_beli_satuan, 0) AS harga,
                                'IN' AS type,
                                'KONS-BELI ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                {$pajak_group_expr} AS kelompok_dagang
                            FROM tb_konsinyasi_settlement s
                            JOIN tbpo_barang b ON b.kode_barang = s.kd_barang
                            {$kons_pay_join}
                            WHERE s.status = 'BILLED'
                              AND s.qty_net > 0
                              AND ({$kons_date_expr}) <= {$end_date_sql}
                              {$kons_where_extra}
                              {$prod_filter_clause}";
            $queries[] = "($sql_kons_beli)";

            // Konsinyasi Lunas - Keluar (Penjualan)
            $sql_kons_jual = "SELECT 
                                s.kd_barang,
                                {$id_wh_induk} AS gudang_id,
                                ({$kons_date_expr}) AS tanggal,
                                CONCAT({$kons_ref_expr}, ' (Konsinyasi Jual - ', COALESCE(s.customer_name, 'Kios'), ')') AS referensi,
                                s.qty_net AS qty,
                                COALESCE(s.hrg_beli_satuan, 0) AS harga,
                                'OUT' AS type,
                                'KONS-JUAL ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                {$pajak_group_expr} AS kelompok_dagang
                            FROM tb_konsinyasi_settlement s
                            JOIN tbpo_barang b ON b.kode_barang = s.kd_barang
                            {$kons_pay_join}
                            WHERE s.status = 'BILLED'
                              AND s.qty_net > 0
                              AND ({$kons_date_expr}) <= {$end_date_sql}
                              {$kons_where_extra}
                              {$prod_filter_clause}";
            $queries[] = "($sql_kons_jual)";
        }

        // 6. Query Retur Pembelian (Keluar) - khusus Potong Hutang Usaha (POTONG_HUTANG), non konsinyasi & non virtual TM
        if ($this->db->table_exists('tb_retur_pembelian') && $this->db->table_exists('tb_retur_pembelian_detail')) {
            $rb_where_extra = "";
            if ($excluded_wh_sql !== '') {
                $rb_where_extra .= " AND rb.gudang_id NOT IN ({$excluded_wh_sql})";
            }
            if ($this->db->field_exists('is_konsinyasi', 'tb_retur_pembelian')) {
                $rb_where_extra .= " AND COALESCE(rb.is_konsinyasi, 0) = 0";
            }
            if ($this->db->field_exists('jenis_penyelesaian', 'tb_retur_pembelian')) {
                $rb_where_extra .= " AND UPPER(TRIM(COALESCE(rb.jenis_penyelesaian, ''))) = 'POTONG_HUTANG'";
            }
            $sql_retur_beli = "SELECT 
                                rbd.kd_barang,
                                rb.gudang_id,
                                rb.tanggal_retur AS tanggal,
                                rb.no_retur_pembelian AS referensi,
                                rbd.qty_retur AS qty,
                                rbd.harga_satuan AS harga,
                                'OUT' AS type,
                                'RB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                {$pajak_group_expr} AS kelompok_dagang
                            FROM tb_retur_pembelian rb
                            JOIN tb_retur_pembelian_detail rbd ON rb.id_retur_pembelian = rbd.id_retur_pembelian
                            JOIN tbpo_barang b ON rbd.kd_barang = b.kode_barang
                            WHERE rb.status NOT IN ('DRAFT', 'REJECTED', 'BATAL') AND rb.tanggal_retur <= {$end_date_sql}
                            {$rb_where_extra}
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_retur_beli .= " AND rb.gudang_id = {$wh_target_id}";
            }
            $queries[] = "($sql_retur_beli)";
        }

        // 7. Query Penyesuaian Barang - non konsinyasi & non virtual TM
        if ($this->db->table_exists('tbkeu_penyesuaian_barang') && $this->db->table_exists('tbkeu_penyesuaian_barang_detail')) {
            $adj_in_wh_expr = "IF(pb.id_gudang_ke IS NOT NULL AND pb.id_gudang_ke > 0, pb.id_gudang_ke, pb.id_gudang_dari)";
            $adj_out_wh_expr = "IF(pb.id_gudang_dari IS NOT NULL AND pb.id_gudang_dari > 0, pb.id_gudang_dari, pb.id_gudang_ke)";

            $adj_in_extra = ($excluded_wh_sql !== '') ? " AND {$adj_in_wh_expr} NOT IN ({$excluded_wh_sql})" : "";
            $adj_out_extra = ($excluded_wh_sql !== '') ? " AND {$adj_out_wh_expr} NOT IN ({$excluded_wh_sql})" : "";

            $sql_adj_in = "SELECT 
                                pbd.kd_barang,
                                {$adj_in_wh_expr} AS gudang_id,
                                pb.tanggal,
                                pb.no_referensi AS referensi,
                                ABS(pbd.jumlah) AS qty,
                                0 AS harga,
                                'IN' AS type,
                                'PB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                {$pajak_group_expr} AS kelompok_dagang
                            FROM tbkeu_penyesuaian_barang pb
                            JOIN tbkeu_penyesuaian_barang_detail pbd ON pb.id_penyesuaian = pbd.id_penyesuaian
                            JOIN tbpo_barang b ON pbd.kd_barang = b.kode_barang
                            WHERE pb.status = 'POSTED' AND pbd.jumlah > 0 AND pb.tanggal <= {$end_date_sql}
                            {$adj_in_extra}
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_adj_in .= " AND {$adj_in_wh_expr} = {$wh_target_id}";
            }
            $queries[] = "($sql_adj_in)";

            $sql_adj_out = "SELECT 
                                pbd.kd_barang,
                                {$adj_out_wh_expr} AS gudang_id,
                                pb.tanggal,
                                pb.no_referensi AS referensi,
                                ABS(pbd.jumlah) AS qty,
                                0 AS harga,
                                'OUT' AS type,
                                'PB ' AS ref_prefix,
                                b.satuan,
                                b.nama_barang,
                                {$pajak_group_expr} AS kelompok_dagang
                            FROM tbkeu_penyesuaian_barang pb
                            JOIN tbkeu_penyesuaian_barang_detail pbd ON pb.id_penyesuaian = pbd.id_penyesuaian
                            JOIN tbpo_barang b ON pbd.kd_barang = b.kode_barang
                            WHERE pb.status = 'POSTED' AND pbd.jumlah < 0 AND pb.tanggal <= {$end_date_sql}
                            {$adj_out_extra}
                            {$prod_filter_clause}";
            if ($wh_target_id) {
                $sql_adj_out .= " AND {$adj_out_wh_expr} = {$wh_target_id}";
            }
            $queries[] = "($sql_adj_out)";
        }

        $all_products_data = $this->_buildMovingAverageStockCards($queries, $warehouses_map, $groups_map, $start_date, $end_date);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $all_products_data
            ]));
    }

    /**
     * Menambahkan penerimaan ke antrean persediaan FIFO.
     *
     * Saldo negatif lama diselesaikan terlebih dahulu agar antrean tetap
     * menggambarkan stok fisik yang benar ketika ada transaksi terlambat.
     */
    private function addKonsinyasiFifoLayer(&$layers, $qty, $price, $layerId = null)
    {
        $remainingQty = (float)$qty;
        $epsilon = 0.000001;

        while ($remainingQty > $epsilon && !empty($layers) && $layers[0]['qty'] < -$epsilon) {
            $coveredQty = min($remainingQty, abs($layers[0]['qty']));
            $layers[0]['qty'] += $coveredQty;
            $remainingQty -= $coveredQty;

            if (abs($layers[0]['qty']) <= $epsilon) {
                array_shift($layers);
            }
        }

        if ($remainingQty > $epsilon) {
            $layers[] = [
                'layer_id' => $layerId,
                'qty'      => $remainingQty,
                'harga'    => (float)$price
            ];
        }
    }

    /**
     * Mengeluarkan stok dari lapisan yang masuk paling awal.
     */
    private function consumeKonsinyasiFifoLayers(&$layers, $qty, $fallbackPrice)
    {
        $remainingQty = (float)$qty;
        $epsilon = 0.000001;
        $segments = [];

        if (!empty($layers) && $layers[0]['qty'] < -$epsilon) {
            $price = (float)$fallbackPrice;
            $layers[0]['qty'] -= $remainingQty;

            return [[
                'layer_id' => $layers[0]['layer_id'] ?? null,
                'qty'      => $remainingQty,
                'harga'    => $price,
                'nilai'    => $remainingQty * $price
            ]];
        }

        while ($remainingQty > $epsilon && !empty($layers)) {
            if ($layers[0]['qty'] <= $epsilon) {
                array_shift($layers);
                continue;
            }

            $takenQty = min($remainingQty, $layers[0]['qty']);
            $segments[] = [
                'layer_id' => $layers[0]['layer_id'] ?? null,
                'qty'      => $takenQty,
                'harga'    => (float)$layers[0]['harga'],
                'nilai'    => $takenQty * (float)$layers[0]['harga']
            ];

            $layers[0]['qty'] -= $takenQty;
            $remainingQty -= $takenQty;

            if ($layers[0]['qty'] <= $epsilon) {
                array_shift($layers);
            }
        }

        // Tetap tampilkan kekurangan stok agar selisih data sumber mudah diaudit.
        if ($remainingQty > $epsilon) {
            $price = (float)$fallbackPrice;
            $shortageLayerId = uniqid('neg_', true);
            $segments[] = [
                'layer_id' => $shortageLayerId,
                'qty'      => $remainingQty,
                'harga'    => $price,
                'nilai'    => $remainingQty * $price
            ];
            $layers[] = [
                'layer_id' => $shortageLayerId,
                'qty'      => -$remainingQty,
                'harga'    => $price
            ];
        }

        return $segments;
    }

    /**
     * Membentuk ringkasan dan detail lapisan saldo untuk kebutuhan tampilan.
     */
    private function summarizeKonsinyasiFifoLayers($layers)
    {
        $summary = [
            'qty'    => 0.0,
            'nilai'  => 0.0,
            'harga'  => 0.0,
            'layers' => []
        ];

        foreach ($layers as $layer) {
            $qty = (float)$layer['qty'];
            $price = (float)$layer['harga'];
            $value = $qty * $price;

            if (abs($qty) <= 0.000001) {
                continue;
            }

            $summary['qty'] += $qty;
            $summary['nilai'] += $value;
            $summary['layers'][] = [
                'layer_id' => $layer['layer_id'] ?? null,
                'qty'      => $qty,
                'harga'    => $price,
                'nilai'    => $value
            ];
        }

        if (abs($summary['qty']) > 0.000001) {
            $summary['harga'] = $summary['nilai'] / $summary['qty'];
        }

        return $summary;
    }

    /**
     * Membentuk identitas detail transfer yang sama untuk sisi keluar Gudang
     * Konsinyasi dan sisi masuk Gudang Titipan di Kios.
     */
    private function getKonsinyasiTransferKey($transaction)
    {
        return implode('|', [
            (string)$transaction['kd_barang'],
            (string)$transaction['referensi'],
            (string)$transaction['no_lot'],
            number_format((float)$transaction['qty'], 6, '.', '')
        ]);
    }

    public function kartu_stock_konsinyasi_data()
    {
        $kd_barang  = $this->input->post('kd_barang', true);
        $kd_suplier = $this->input->post('kd_suplier', true);
        $id_gudang  = $this->input->post('id_gudang', true);
        $start_date = $this->input->post('start_date', true);
        $end_date   = $this->input->post('end_date', true);

        // Ambil ID 3 Gudang Konsinyasi
        $wh_konsinyasi = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->group_start()
                ->where('id_gudang', 13)
                ->or_like('nama_gudang', 'Konsi')
            ->group_end()
            ->limit(1)->get()->row_array();
        $id_wh_konsinyasi = $wh_konsinyasi ? (int)$wh_konsinyasi['id_gudang'] : 13;

        $wh_titipan_kios = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Titipan di Kios')
            ->limit(1)->get()->row_array();
        $id_wh_titipan_kios = $wh_titipan_kios ? (int)$wh_titipan_kios['id_gudang'] : 14;

        $wh_konsinyasi_lunas = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Konsinyasi Lunas')
            ->limit(1)->get()->row_array();
        $id_wh_konsinyasi_lunas = $wh_konsinyasi_lunas ? (int)$wh_konsinyasi_lunas['id_gudang'] : 15;

        // Ambil master gudang aktif
        $warehouses_raw = $this->db->get_where('tb_gudang', ['is_active' => 1])->result_array();
        $warehouses_map = [];
        foreach ($warehouses_raw as $w) {
            $warehouses_map[(int)$w['id_gudang']] = $w['nama_gudang'];
        }

        $wh_target_id = ($id_gudang && $id_gudang !== 'all') ? (int)$id_gudang : null;

        // Filter klausul
        $prod_filter_ld = ($kd_barang && $kd_barang !== 'all') ? " AND ld.kd_barang = " . $this->db->escape($kd_barang) : "";
        $prod_filter_fd = ($kd_barang && $kd_barang !== 'all') ? " AND fd.kd_barang = " . $this->db->escape($kd_barang) : "";
        $prod_filter_s  = ($kd_barang && $kd_barang !== 'all') ? " AND s.kd_barang = " . $this->db->escape($kd_barang) : "";
        
        $sup_filter_l = ($kd_suplier && $kd_suplier !== 'all') ? " AND l.kd_suplier = " . $this->db->escape($kd_suplier) : "";
        $sup_filter_s = ($kd_suplier && $kd_suplier !== 'all') ? " AND s.kd_suplier = " . $this->db->escape($kd_suplier) : "";

        $queries = [];

        // 1. LPB Penerimaan dari Supplier (Gudang 13 - IN)
        if (!$wh_target_id || $wh_target_id == $id_wh_konsinyasi || $wh_target_id == $id_wh_titipan_kios) {
            $sql_lpb = "SELECT 
                            ld.kd_barang,
                            b.nama_barang,
                            b.satuan,
                            {$id_wh_konsinyasi} AS gudang_id,
                            'Gdg. Konsinyasi' AS nama_gudang,
                            l.tgl_sj AS tanggal,
                            l.nomor_lpb AS referensi,
                            'LPB-IN ' AS ref_prefix,
                            ld.no_lot AS no_lot,
                            l.nomor_lpb AS nomor_lpb_asal,
                            COALESCE(sup.nama_suplier, l.kd_suplier, 'Supplier') AS partner_name,
                            ld.qty_diterima AS qty,
                            ld.harga_satuan AS harga_fifo,
                            'IN' AS type,
                            'TERIMA_LPB' AS flow_status
                        FROM tb_lpb l
                        JOIN tb_lpb_detail ld ON l.id_lpb = ld.id_lpb
                        JOIN tbpo_barang b ON ld.kd_barang = b.kode_barang
                        LEFT JOIN tbpo_suplier sup ON l.kd_suplier = sup.kd_suplier
                        WHERE l.status_lpb = 1 
                          AND (l.gudang_id = {$id_wh_konsinyasi} OR l.nomor_lpb LIKE 'LPBK%')
                          AND l.tgl_sj <= " . $this->db->escape($end_date) . "
                          {$prod_filter_ld} {$sup_filter_l}";
            $queries[] = "($sql_lpb)";
        }

        // 2. Faktur Penjualan Titip ke Kios (Gudang 13 - OUT)
        if (!$wh_target_id || $wh_target_id == $id_wh_konsinyasi || $wh_target_id == $id_wh_titipan_kios) {
            $sql_kios_out13 = "SELECT 
                            fd.kd_barang,
                            b.nama_barang,
                            b.satuan,
                            {$id_wh_konsinyasi} AS gudang_id,
                            'Gdg. Konsinyasi' AS nama_gudang,
                            f.tanggal_faktur AS tanggal,
                            f.no_faktur AS referensi,
                            'TITIP-OUT ' AS ref_prefix,
                            fd.no_lot AS no_lot,
                            COALESCE((SELECT s.nomor_lpb_asal FROM tb_konsinyasi_settlement s WHERE s.id_faktur = f.id_faktur AND s.kd_barang = fd.kd_barang LIMIT 1), '-') AS nomor_lpb_asal,
                            COALESCE(f.customer_name, 'Kios') AS partner_name,
                            fd.qty AS qty,
                            COALESCE((SELECT s.hrg_beli_satuan FROM tb_konsinyasi_settlement s WHERE s.id_faktur = f.id_faktur AND s.kd_barang = fd.kd_barang LIMIT 1), fd.hrg_pokok, 0) AS harga_fifo,
                            'OUT' AS type,
                            'TITIP_KE_KIOS' AS flow_status
                        FROM tbso_faktur_penjualan f
                        JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                        JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                        WHERE f.status NOT IN ('draft', 'cancelled')
                          AND f.gudang_id = {$id_wh_konsinyasi}
                          AND f.tanggal_faktur <= " . $this->db->escape($end_date) . "
                          {$prod_filter_fd}";
            if ($kd_suplier && $kd_suplier !== 'all') {
                $sql_kios_out13 .= " AND EXISTS (SELECT 1 FROM tb_konsinyasi_settlement st WHERE st.id_faktur = f.id_faktur AND st.kd_barang = fd.kd_barang AND st.kd_suplier = " . $this->db->escape($kd_suplier) . ")";
            }
            $queries[] = "($sql_kios_out13)";
        }

        // 3. Faktur Penjualan Titip ke Kios (Gudang 14 - IN)
        if (!$wh_target_id || $wh_target_id == $id_wh_titipan_kios) {
            $sql_kios_in14 = "SELECT 
                            fd.kd_barang,
                            b.nama_barang,
                            b.satuan,
                            {$id_wh_titipan_kios} AS gudang_id,
                            'Gdg. Titipan di Kios' AS nama_gudang,
                            f.tanggal_faktur AS tanggal,
                            f.no_faktur AS referensi,
                            'TITIP-IN ' AS ref_prefix,
                            fd.no_lot AS no_lot,
                            COALESCE((SELECT s.nomor_lpb_asal FROM tb_konsinyasi_settlement s WHERE s.id_faktur = f.id_faktur AND s.kd_barang = fd.kd_barang LIMIT 1), '-') AS nomor_lpb_asal,
                            COALESCE(f.customer_name, 'Kios') AS partner_name,
                            fd.qty AS qty,
                            COALESCE((SELECT s.hrg_beli_satuan FROM tb_konsinyasi_settlement s WHERE s.id_faktur = f.id_faktur AND s.kd_barang = fd.kd_barang LIMIT 1), fd.hrg_pokok, 0) AS harga_fifo,
                            'IN' AS type,
                            'TITIP_DI_KIOS' AS flow_status
                        FROM tbso_faktur_penjualan f
                        JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                        JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                        WHERE f.status NOT IN ('draft', 'cancelled')
                          AND f.gudang_id = {$id_wh_konsinyasi}
                          AND f.tanggal_faktur <= " . $this->db->escape($end_date) . "
                          {$prod_filter_fd}";
            if ($kd_suplier && $kd_suplier !== 'all') {
                $sql_kios_in14 .= " AND EXISTS (SELECT 1 FROM tb_konsinyasi_settlement st WHERE st.id_faktur = f.id_faktur AND st.kd_barang = fd.kd_barang AND st.kd_suplier = " . $this->db->escape($kd_suplier) . ")";
            }
            $queries[] = "($sql_kios_in14)";
        }

        // 4. Pembayaran Kios di Keuangan (Gudang 14 - OUT)
        if ((!$wh_target_id || $wh_target_id == $id_wh_titipan_kios) && $this->db->table_exists('tb_konsinyasi_settlement')) {
            $sql_kios_out14 = "SELECT 
                            s.kd_barang,
                            b.nama_barang,
                            b.satuan,
                            {$id_wh_titipan_kios} AS gudang_id,
                            'Gdg. Titipan di Kios' AS nama_gudang,
                            COALESCE(p.tanggal_pembayaran, s.tanggal_settlement, DATE(s.created_at)) AS tanggal,
                            CONCAT(COALESCE(p.no_faktur_konsinyasi, s.no_faktur, s.no_settlement)) AS referensi,
                            'LAKU-OUT ' AS ref_prefix,
                            s.no_lot AS no_lot,
                            COALESCE(s.nomor_lpb_asal, '-') AS nomor_lpb_asal,
                            COALESCE(s.customer_name, 'Kios') AS partner_name,
                            s.qty_net AS qty,
                            s.hrg_beli_satuan AS harga_fifo,
                            'OUT' AS type,
                            'DIBELI_KIOS' AS flow_status
                        FROM tb_konsinyasi_settlement s
                        JOIN tbpo_barang b ON s.kd_barang = b.kode_barang
                        LEFT JOIN tbkeu_pembayaran_faktur p ON p.id_pembayaran = s.id_pembayaran
                        WHERE (s.id_pembayaran IS NOT NULL OR s.status IN ('LAKU', 'BILLED'))
                          AND (p.status IS NULL OR p.status NOT IN ('CANCELLED', 'UNPOST', 'DRAFT'))
                          AND COALESCE(p.tanggal_pembayaran, s.tanggal_settlement, DATE(s.created_at)) <= " . $this->db->escape($end_date) . "
                          {$prod_filter_s} {$sup_filter_s}";
            $queries[] = "($sql_kios_out14)";
        }

        // 5 & 6. Pelunasan Konsinyasi (Gudang 15 - IN & OUT)
        if ((!$wh_target_id || $wh_target_id == $id_wh_konsinyasi_lunas) && $this->db->table_exists('tb_konsinyasi_settlement')) {
            $sql_lunas_in15 = "SELECT 
                            s.kd_barang,
                            b.nama_barang,
                            b.satuan,
                            {$id_wh_konsinyasi_lunas} AS gudang_id,
                            'Gdg. Konsinyasi Lunas' AS nama_gudang,
                            COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) AS tanggal,
                            s.no_settlement AS referensi,
                            'LUNAS-IN ' AS ref_prefix,
                            s.no_lot AS no_lot,
                            COALESCE(s.nomor_lpb_asal, '-') AS nomor_lpb_asal,
                            CONCAT(s.nama_suplier, ' (INV: ', COALESCE(s.no_invoice_supplier, '-'), ')') AS partner_name,
                            s.qty_net AS qty,
                            s.hrg_beli_satuan AS harga_fifo,
                            'IN' AS type,
                            'LUNAS_SUPPLIER' AS flow_status
                        FROM tb_konsinyasi_settlement s
                        JOIN tbpo_barang b ON s.kd_barang = b.kode_barang
                        WHERE s.status = 'BILLED'
                          AND COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) <= " . $this->db->escape($end_date) . "
                          {$prod_filter_s} {$sup_filter_s}";
            $queries[] = "($sql_lunas_in15)";

            $sql_lunas_out15 = "SELECT 
                            s.kd_barang,
                            b.nama_barang,
                            b.satuan,
                            {$id_wh_konsinyasi_lunas} AS gudang_id,
                            'Gdg. Konsinyasi Lunas' AS nama_gudang,
                            COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) AS tanggal,
                            CONCAT(COALESCE(p.no_faktur_konsinyasi, s.no_faktur, s.no_settlement)) AS referensi,
                            'LUNAS-OUT ' AS ref_prefix,
                            s.no_lot AS no_lot,
                            COALESCE(s.nomor_lpb_asal, '-') AS nomor_lpb_asal,
                            COALESCE(s.customer_name, 'Kios') AS partner_name,
                            s.qty_net AS qty,
                            s.hrg_beli_satuan AS harga_fifo,
                            'OUT' AS type,
                            'PENJUALAN_SELESAI' AS flow_status
                        FROM tb_konsinyasi_settlement s
                        JOIN tbpo_barang b ON s.kd_barang = b.kode_barang
                        LEFT JOIN tbkeu_pembayaran_faktur p ON p.id_pembayaran = s.id_pembayaran
                        WHERE s.status = 'BILLED'
                          AND COALESCE(s.tgl_invoice_supplier, DATE(s.settled_at), s.tanggal_settlement) <= " . $this->db->escape($end_date) . "
                          {$prod_filter_s} {$sup_filter_s}";
            $queries[] = "($sql_lunas_out15)";
        }

        if (empty($queries)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true, 'data' => []]));
        }

        $union_sql = implode(" UNION ALL ", $queries) . " ORDER BY tanggal ASC";
        $all_tx = $this->db->query($union_sql)->result_array();

        // Kelompokkan per [kd_barang][gudang_id]
        $grouped = [];
        $product_info = [];

        foreach ($all_tx as $tx_index => $tx) {
            $kd = $tx['kd_barang'];
            $wh = (int)$tx['gudang_id'];
            $tx['_sequence'] = $tx_index;

            if (!isset($product_info[$kd])) {
                $product_info[$kd] = [
                    'nama_barang' => $tx['nama_barang'],
                    'satuan'      => $tx['satuan']
                ];
            }

            $grouped[$kd][$wh][] = $tx;
        }

        $all_products_data = [];
        $titip_transfer_layers = [];
        ksort($grouped);

        foreach ($grouped as $kd => $byWarehouse) {
            ksort($byWarehouse);
            $prod_meta = $product_info[$kd];

            foreach ($byWarehouse as $wh_id => $txs) {
                $wh_name = $warehouses_map[$wh_id] ?? ('Gudang ' . $wh_id);

                usort($txs, function($a, $b) {
                    $t1 = strtotime($a['tanggal']);
                    $t2 = strtotime($b['tanggal']);
                    if ($t1 !== $t2) {
                        return $t1 < $t2 ? -1 : 1;
                    }

                    // Penerimaan pada tanggal yang sama harus membentuk lapisan sebelum pengeluaran.
                    if ($a['type'] !== $b['type']) {
                        return $a['type'] === 'IN' ? -1 : 1;
                    }

                    $referenceCompare = strnatcasecmp((string)$a['referensi'], (string)$b['referensi']);
                    if ($referenceCompare !== 0) {
                        return $referenceCompare;
                    }

                    return $a['_sequence'] <=> $b['_sequence'];
                });

                $fifo_layers = [];

                $opening_balance = [
                    'tanggal'     => '',
                    'referensi'   => 'O/B Opening Balance',
                    'unit'        => $prod_meta['satuan'],
                    'saldo_qty'   => 0.0,
                    'saldo_harga' => 0.0,
                    'saldo_nilai' => 0.0,
                    'saldo_layers'=> []
                ];

                $report_rows = [];
                $sub_masuk_qty = 0.0;
                $sub_masuk_nilai = 0.0;
                $sub_keluar_qty = 0.0;
                $sub_keluar_nilai = 0.0;
                $primary_supplier = '';

                foreach ($txs as $tx) {
                    $qty = (float)$tx['qty'];
                    $harga = (float)$tx['harga_fifo'];
                    $tx_date = $tx['tanggal'];
                    $movement_layers = [];

                    if (!empty($tx['partner_name']) && empty($primary_supplier)) {
                        $primary_supplier = $tx['partner_name'];
                    }

                    if ($tx['type'] === 'IN') {
                        $transfer_key = $this->getKonsinyasiTransferKey($tx);
                        $transferred_layers = null;

                        if ($tx['flow_status'] === 'TITIP_DI_KIOS' && !empty($titip_transfer_layers[$transfer_key])) {
                            $transferred_layers = array_shift($titip_transfer_layers[$transfer_key]);
                        }

                        if (!empty($transferred_layers)) {
                            foreach ($transferred_layers as $layer_index => $transferred_layer) {
                                $layer_qty = (float)$transferred_layer['qty'];
                                $layer_price = (float)$transferred_layer['harga'];
                                $layer_id = 'in_' . $wh_id . '_' . $tx['_sequence'] . '_' . $layer_index;
                                $this->addKonsinyasiFifoLayer($fifo_layers, $layer_qty, $layer_price, $layer_id);
                                $movement_layers[] = [
                                    'layer_id' => $layer_id,
                                    'qty'      => $layer_qty,
                                    'harga'    => $layer_price,
                                    'nilai'    => $layer_qty * $layer_price
                                ];
                            }
                        } else {
                            $layer_id = 'in_' . $wh_id . '_' . $tx['_sequence'] . '_0';
                            $this->addKonsinyasiFifoLayer($fifo_layers, $qty, $harga, $layer_id);
                            $movement_layers[] = [
                                'layer_id' => $layer_id,
                                'qty'      => $qty,
                                'harga'    => $harga,
                                'nilai'    => $qty * $harga
                            ];
                        }
                    } else {
                        $movement_layers = $this->consumeKonsinyasiFifoLayers($fifo_layers, $qty, $harga);

                        if ($tx['flow_status'] === 'TITIP_KE_KIOS') {
                            $transfer_key = $this->getKonsinyasiTransferKey($tx);
                            $titip_transfer_layers[$transfer_key][] = $movement_layers;
                        }
                    }

                    $saldo_summary = $this->summarizeKonsinyasiFifoLayers($fifo_layers);

                    if (strtotime($tx_date) < strtotime($start_date)) {
                        $opening_balance['saldo_qty'] = $saldo_summary['qty'];
                        $opening_balance['saldo_harga'] = $saldo_summary['harga'];
                        $opening_balance['saldo_nilai'] = $saldo_summary['nilai'];
                        $opening_balance['saldo_layers'] = $saldo_summary['layers'];
                    } else if (strtotime($tx_date) <= strtotime($end_date)) {
                        $refText = trim($tx['ref_prefix'] . $tx['referensi']);
                        if (!empty($tx['partner_name'])) {
                            $refText .= ' (' . $tx['partner_name'] . ')';
                        }
                        if (!empty($tx['no_lot']) && $tx['no_lot'] !== '-') {
                            $refText .= ' [Lot: ' . $tx['no_lot'] . ']';
                        }

                        $row = [
                            'tanggal'        => $tx['tanggal'],
                            'referensi'      => $refText,
                            'unit'           => $tx['satuan'],
                            'flow_status'    => $tx['flow_status'],
                            'masuk_qty'      => 0.0,
                            'masuk_harga'    => 0.0,
                            'masuk_nilai'    => 0.0,
                            'keluar_qty'     => 0.0,
                            'keluar_harga'   => 0.0,
                            'keluar_nilai'   => 0.0,
                            'saldo_qty'      => $saldo_summary['qty'],
                            'saldo_harga'    => $saldo_summary['harga'],
                            'saldo_nilai'    => $saldo_summary['nilai'],
                            'movement_layers'=> $movement_layers,
                            'saldo_layers'   => $saldo_summary['layers']
                        ];

                        if ($tx['type'] === 'IN') {
                            $fifo_in_value = array_sum(array_column($movement_layers, 'nilai'));
                            $row['masuk_qty'] = $qty;
                            $row['masuk_harga'] = $qty > 0 ? ($fifo_in_value / $qty) : 0.0;
                            $row['masuk_nilai'] = $fifo_in_value;
                            $sub_masuk_qty += $qty;
                            $sub_masuk_nilai += $fifo_in_value;
                        } else {
                            $fifo_out_value = array_sum(array_column($movement_layers, 'nilai'));
                            $row['keluar_qty'] = $qty;
                            $row['keluar_harga'] = $qty > 0 ? ($fifo_out_value / $qty) : 0.0;
                            $row['keluar_nilai'] = $fifo_out_value;
                            $sub_keluar_qty += $qty;
                            $sub_keluar_nilai += $fifo_out_value;
                        }

                        $report_rows[] = $row;
                    }
                }

                $final_summary = $this->summarizeKonsinyasiFifoLayers($fifo_layers);

                $should_show_warehouse = !$wh_target_id || $wh_id == $wh_target_id;

                if ($should_show_warehouse && (!empty($report_rows) || $opening_balance['saldo_qty'] != 0 || $final_summary['qty'] != 0)) {
                    $all_products_data[] = [
                        'id_gudang'         => $wh_id,
                        'warehouse_name'    => $wh_name,
                        'product_code'      => $kd,
                        'product_name'      => $prod_meta['nama_barang'],
                        'kelompok_barang'   => $primary_supplier ?: 'Supplier Konsinyasi',
                        'unit'              => $prod_meta['satuan'],
                        'opening_balance'   => $opening_balance,
                        'rows'              => $report_rows,
                        'sub_masuk_qty'     => $sub_masuk_qty,
                        'sub_masuk_harga'   => $sub_masuk_qty > 0 ? ($sub_masuk_nilai / $sub_masuk_qty) : 0.0,
                        'sub_masuk_nilai'   => $sub_masuk_nilai,
                        'sub_keluar_qty'    => $sub_keluar_qty,
                        'sub_keluar_harga'  => $sub_keluar_qty > 0 ? ($sub_keluar_nilai / $sub_keluar_qty) : 0.0,
                        'sub_keluar_nilai'  => $sub_keluar_nilai,
                        'final_saldo_qty'   => $final_summary['qty'],
                        'final_saldo_harga' => $final_summary['harga'],
                        'final_saldo_nilai' => $final_summary['nilai'],
                        'final_saldo_layers'=> $final_summary['layers']
                    ];
                }
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data'    => $all_products_data
            ]));
    }

    public function repost_retur_journal($id_retur = null)
    {
        $this->load->model('M_Journal');
        if ($id_retur) {
            $result = $this->M_Journal->post_jurnal_retur_penjualan((int)$id_retur);
            echo json_encode(['success' => (bool)$result, 'id_retur' => $id_retur]);
        } else {
            $all = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'selesai'])->result_array();
            $count = 0;
            foreach ($all as $r) {
                if ($this->M_Journal->post_jurnal_retur_penjualan((int)$r['id_retur'])) {
                    $count++;
                }
            }
            echo json_encode(['success' => true, 'processed' => $count]);
        }
    }

    // =========================================================================
    // LAPORAN ARUS KAS (ZAHIR ACCOUNTING STYLE)
    // =========================================================================

    /**
     * Halaman Laporan Arus Kas - Rangkuman
     */
    public function arus_kas_rangkuman()
    {
        $this->_ensure_access();

        $startDate = trim((string)$this->input->get('start_date', true));
        $endDate   = trim((string)$this->input->get('end_date', true));

        // Default jika belum ada filter: 1 bulan berjalan (awal s/d akhir bulan)
        if (empty($startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        if (empty($endDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-t');
        }

        $this->load->model('M_Journal');
        $cashFlowData = $this->M_Journal->get_laporan_arus_kas($startDate, $endDate);

        $data = [
            'page_title'   => 'KARISMA - Laporan Arus Kas (Rangkuman)',
            'company_name' => 'KARISMA 2025',
            'report_title' => 'Laporan Arus Kas',
            'report_type'  => 'rangkuman',
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'data'         => $cashFlowData,
            'print_time'   => date('H:i   d F, Y'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/arus_kas_rangkuman.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Halaman Laporan Arus Kas - Rincian
     */
    public function arus_kas_rincian()
    {
        $this->_ensure_access();

        $startDate = trim((string)$this->input->get('start_date', true));
        $endDate   = trim((string)$this->input->get('end_date', true));

        if (empty($startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        if (empty($endDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-t');
        }

        $this->load->model('M_Journal');
        $cashFlowData = $this->M_Journal->get_laporan_arus_kas($startDate, $endDate);

        $data = [
            'page_title'   => 'KARISMA - Laporan Arus Kas (Rincian)',
            'company_name' => 'KARISMA 2025',
            'report_title' => 'Laporan Arus Kas',
            'report_type'  => 'rincian',
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'data'         => $cashFlowData,
            'print_time'   => date('H:i   d F, Y'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/arus_kas_rincian.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Export Laporan Arus Kas ke format Excel
     */
    public function arus_kas_export($type = 'rangkuman')
    {
        $this->_ensure_access();

        $startDate = trim((string)$this->input->get('start_date', true));
        $endDate   = trim((string)$this->input->get('end_date', true));

        if (empty($startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        if (empty($endDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-t');
        }

        $this->load->model('M_Journal');
        $cashFlowData = $this->M_Journal->get_laporan_arus_kas($startDate, $endDate);

        $isRincian = ($type === 'rincian');
        $filename = 'Laporan_Arus_Kas_' . ($isRincian ? 'Rincian_' : 'Rangkuman_') . $startDate . '_sd_' . $endDate . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $data = [
            'company_name' => 'KARISMA 2025',
            'report_title' => 'Laporan Arus Kas',
            'report_type'  => $type,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'data'         => $cashFlowData,
            'is_rincian'   => $isRincian,
            'print_time'   => date('H:i   d F, Y'),
        ];

        $this->load->view('content/keuangan/laporan/laporan_keuangan/arus_kas_excel.php', $data);
    }

    // =========================================================================
    // LAPORAN LABA RUGI (ZAHIR ACCOUNTING STYLE)
    // =========================================================================

    /**
     * 1. Laba Rugi Standar (1 Kolom Saldo)
     */
    public function laba_rugi_standar()
    {
        $this->_ensure_access();

        $p1Month   = (int)$this->input->get('p1_month', true);
        $p1Year    = (int)$this->input->get('p1_year', true);
        $startDate = trim((string)$this->input->get('start_date', true));
        $endDate   = trim((string)$this->input->get('end_date', true));

        if ($p1Month >= 1 && $p1Month <= 12 && $p1Year >= 2000 && $p1Year <= 2100) {
            $startDate = sprintf('%04d-%02d-01', $p1Year, $p1Month);
            $endDate   = date('Y-m-t', strtotime($startDate));
        }

        if (empty($startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        if (empty($endDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-t');
        }

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_laba_rugi($startDate, $endDate, false);

        $data = [
            'page_title'       => 'KARISMA - Laporan Laba Rugi',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Laba Rugi',
            'report_mode'      => 'standar',
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'p1_month'         => (int)date('n', strtotime($startDate)),
            'p1_year'          => (int)date('Y', strtotime($startDate)),
            'period_label'     => date('F Y', strtotime($startDate)),
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/laba-rugi'),
            'export_url'       => base_url('laporan/keuangan/laba-rugi-export/standar?start_date=' . $startDate . '&end_date=' . $endDate),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/laba_rugi_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 2. Laba Rugi (Saldo & Year to Date)
     */
    public function laba_rugi_ytd()
    {
        $this->_ensure_access();

        $p1Month   = (int)$this->input->get('p1_month', true);
        $p1Year    = (int)$this->input->get('p1_year', true);
        $startDate = trim((string)$this->input->get('start_date', true));
        $endDate   = trim((string)$this->input->get('end_date', true));

        if ($p1Month >= 1 && $p1Month <= 12 && $p1Year >= 2000 && $p1Year <= 2100) {
            $startDate = sprintf('%04d-%02d-01', $p1Year, $p1Month);
            $endDate   = date('Y-m-t', strtotime($startDate));
        }

        if (empty($startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        if (empty($endDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-t');
        }

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_laba_rugi($startDate, $endDate, true);

        $data = [
            'page_title'       => 'KARISMA - Laporan Laba Rugi (Saldo & YTD)',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Laba Rugi',
            'report_mode'      => 'ytd',
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'p1_month'         => (int)date('n', strtotime($startDate)),
            'p1_year'          => (int)date('Y', strtotime($startDate)),
            'period_label'     => date('F Y', strtotime($startDate)),
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/laba-rugi-ytd'),
            'export_url'       => base_url('laporan/keuangan/laba-rugi-export/ytd?start_date=' . $startDate . '&end_date=' . $endDate),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/laba_rugi_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 3. Laba Rugi - Perbandingan 2 Kolom
     */
    public function laba_rugi_perbandingan_2()
    {
        $this->_ensure_access();

        $p1Month = (int)$this->input->get('p1_month', true);
        $p1Year  = (int)$this->input->get('p1_year', true);
        $p2Month = (int)$this->input->get('p2_month', true);
        $p2Year  = (int)$this->input->get('p2_year', true);
        $p1Start = trim((string)$this->input->get('p1_start', true));
        $p1End   = trim((string)$this->input->get('p1_end', true));
        $p2Start = trim((string)$this->input->get('p2_start', true));
        $p2End   = trim((string)$this->input->get('p2_end', true));

        if ($p1Month >= 1 && $p1Month <= 12 && $p1Year >= 2000 && $p1Year <= 2100) {
            $p1Start = sprintf('%04d-%02d-01', $p1Year, $p1Month);
            $p1End   = date('Y-m-t', strtotime($p1Start));
        }
        if ($p2Month >= 1 && $p2Month <= 12 && $p2Year >= 2000 && $p2Year <= 2100) {
            $p2Start = sprintf('%04d-%02d-01', $p2Year, $p2Month);
            $p2End   = date('Y-m-t', strtotime($p2Start));
        }

        // Default: Periode 1 = Bulan berjalan, Periode 2 = Bulan lalu
        if (empty($p1Start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $p1Start)) {
            $p1Start = date('Y-m-01');
        }
        if (empty($p1End) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $p1End)) {
            $p1End = date('Y-m-t');
        }
        if (empty($p2Start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $p2Start)) {
            $p2Start = date('Y-m-01', strtotime('-1 month', strtotime($p1Start)));
        }
        if (empty($p2End) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $p2End)) {
            $p2End = date('Y-m-t', strtotime('-1 month', strtotime($p1Start)));
        }

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_laba_rugi_perbandingan($p1Start, $p1End, $p2Start, $p2End);

        $data = [
            'page_title'       => 'KARISMA - Laba Rugi - Perbandingan 2 Kolom',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Laba Rugi - Perbandingan 2 Kolom',
            'report_mode'      => 'perbandingan_2',
            'p1_start'         => $p1Start,
            'p1_end'           => $p1End,
            'p2_start'         => $p2Start,
            'p2_end'           => $p2End,
            'p1_month'         => (int)date('n', strtotime($p1Start)),
            'p1_year'          => (int)date('Y', strtotime($p1Start)),
            'p2_month'         => (int)date('n', strtotime($p2Start)),
            'p2_year'          => (int)date('Y', strtotime($p2Start)),
            'p1_label'         => date('F Y', strtotime($p1Start)),
            'p2_label'         => date('F Y', strtotime($p2Start)),
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/laba-rugi-perbandingan-2'),
            'export_url'       => base_url("laporan/keuangan/laba-rugi-export/perbandingan_2?p1_start={$p1Start}&p1_end={$p1End}&p2_start={$p2Start}&p2_end={$p2End}"),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/laba_rugi_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 4. Laba Rugi - Anggaran vs Realisasi
     */
    public function laba_rugi_anggaran()
    {
        $this->_ensure_access();

        $p1Month   = (int)$this->input->get('p1_month', true);
        $p1Year    = (int)$this->input->get('p1_year', true);
        $startDate = trim((string)$this->input->get('start_date', true));
        $endDate   = trim((string)$this->input->get('end_date', true));

        if ($p1Month >= 1 && $p1Month <= 12 && $p1Year >= 2000 && $p1Year <= 2100) {
            $startDate = sprintf('%04d-%02d-01', $p1Year, $p1Month);
            $endDate   = date('Y-m-t', strtotime($startDate));
        }

        if (empty($startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        if (empty($endDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-t');
        }

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_laba_rugi($startDate, $endDate, false);

        $data = [
            'page_title'       => 'KARISMA - Laba Rugi - Anggaran vs Realisasi',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Laba Rugi - Anggaran vs Realisasi',
            'report_mode'      => 'anggaran',
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'p1_month'         => (int)date('n', strtotime($startDate)),
            'p1_year'          => (int)date('Y', strtotime($startDate)),
            'period_label'     => date('F Y', strtotime($startDate)),
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/laba-rugi-anggaran'),
            'export_url'       => base_url('laporan/keuangan/laba-rugi-export/anggaran?start_date=' . $startDate . '&end_date=' . $endDate),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/laba_rugi_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 5. Laba Rugi - Perbandingan 4 Kolom
     */
    public function laba_rugi_perbandingan_4()
    {
        $this->_ensure_access();

        $p1Month = (int)$this->input->get('p1_month', true);
        $p1Year  = (int)$this->input->get('p1_year', true);
        $p2Month = (int)$this->input->get('p2_month', true);
        $p2Year  = (int)$this->input->get('p2_year', true);
        $p1Start = trim((string)$this->input->get('p1_start', true));
        $p1End   = trim((string)$this->input->get('p1_end', true));
        $p2Start = trim((string)$this->input->get('p2_start', true));
        $p2End   = trim((string)$this->input->get('p2_end', true));

        if ($p1Month >= 1 && $p1Month <= 12 && $p1Year >= 2000 && $p1Year <= 2100) {
            $p1Start = sprintf('%04d-%02d-01', $p1Year, $p1Month);
            $p1End   = date('Y-m-t', strtotime($p1Start));
        }
        if ($p2Month >= 1 && $p2Month <= 12 && $p2Year >= 2000 && $p2Year <= 2100) {
            $p2Start = sprintf('%04d-%02d-01', $p2Year, $p2Month);
            $p2End   = date('Y-m-t', strtotime($p2Start));
        }

        if (empty($p1Start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $p1Start)) {
            $p1Start = date('Y-m-01');
        }
        if (empty($p1End) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $p1End)) {
            $p1End = date('Y-m-t');
        }
        if (empty($p2Start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $p2Start)) {
            $p2Start = date('Y-m-01', strtotime('-1 month', strtotime($p1Start)));
        }
        if (empty($p2End) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $p2End)) {
            $p2End = date('Y-m-t', strtotime('-1 month', strtotime($p1Start)));
        }

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_laba_rugi_perbandingan($p1Start, $p1End, $p2Start, $p2End);

        $data = [
            'page_title'       => 'KARISMA - Laba Rugi - Perbandingan 4 Kolom',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Laba Rugi - Perbandingan 4 Kolom',
            'report_mode'      => 'perbandingan_4',
            'p1_start'         => $p1Start,
            'p1_end'           => $p1End,
            'p2_start'         => $p2Start,
            'p2_end'           => $p2End,
            'p1_label'         => date('F Y', strtotime($p1Start)),
            'p2_label'         => date('F Y', strtotime($p2Start)),
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/laba-rugi-perbandingan-4'),
            'export_url'       => base_url("laporan/keuangan/laba-rugi-export/perbandingan_4?p1_start={$p1Start}&p1_end={$p1End}&p2_start={$p2Start}&p2_end={$p2End}"),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/laba_rugi_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Export Laporan Laba Rugi ke format Excel
     */
    public function laba_rugi_export($type = 'standar')
    {
        $this->_ensure_access();

        $this->load->model('M_Journal');

        $isComparison = in_array($type, ['perbandingan_2', 'perbandingan_4'], true);

        if ($isComparison) {
            $p1Start = trim((string)$this->input->get('p1_start', true));
            $p1End   = trim((string)$this->input->get('p1_end', true));
            $p2Start = trim((string)$this->input->get('p2_start', true));
            $p2End   = trim((string)$this->input->get('p2_end', true));

            if (empty($p1Start)) $p1Start = date('Y-m-01');
            if (empty($p1End))   $p1End   = date('Y-m-t');
            if (empty($p2Start)) $p2Start = date('Y-m-01', strtotime('-1 month', strtotime($p1Start)));
            if (empty($p2End))   $p2End   = date('Y-m-t', strtotime('-1 month', strtotime($p1Start)));

            $reportData = $this->M_Journal->get_laporan_laba_rugi_perbandingan($p1Start, $p1End, $p2Start, $p2End);
            $filename = 'Laporan_Laba_Rugi_' . ($type === 'perbandingan_2' ? 'Perbandingan_2_Kolom_' : 'Perbandingan_4_Kolom_') . $p1Start . '_vs_' . $p2Start . '.xls';

            $data = [
                'company_name' => 'KARISMA 2025',
                'report_title' => $type === 'perbandingan_2' ? 'Laba Rugi - Perbandingan 2 Kolom' : 'Laba Rugi - Perbandingan 4 Kolom',
                'report_mode'  => $type,
                'p1_start'     => $p1Start,
                'p1_end'       => $p1End,
                'p2_start'     => $p2Start,
                'p2_end'       => $p2End,
                'p1_label'     => date('F Y', strtotime($p1Start)),
                'p2_label'     => date('F Y', strtotime($p2Start)),
                'data'         => $reportData,
                'print_time'   => date('H:i   d F, Y'),
            ];
        } else {
            $startDate = trim((string)$this->input->get('start_date', true));
            $endDate   = trim((string)$this->input->get('end_date', true));

            if (empty($startDate)) $startDate = date('Y-m-01');
            if (empty($endDate))   $endDate   = date('Y-m-t');

            $calcYtd = ($type === 'ytd');
            $reportData = $this->M_Journal->get_laporan_laba_rugi($startDate, $endDate, $calcYtd);

            $titles = [
                'standar'  => 'Laba Rugi',
                'ytd'      => 'Laba Rugi (Saldo & YTD)',
                'anggaran' => 'Laba Rugi - Anggaran vs Realisasi',
            ];
            $title = $titles[$type] ?? 'Laba Rugi';
            $filename = 'Laporan_Laba_Rugi_' . ucfirst($type) . '_' . $startDate . '_sd_' . $endDate . '.xls';

            $data = [
                'company_name' => 'KARISMA 2025',
                'report_title' => $title,
                'report_mode'  => $type,
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'period_label' => date('F Y', strtotime($startDate)),
                'data'         => $reportData,
                'print_time'   => date('H:i   d F, Y'),
            ];
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $this->load->view('content/keuangan/laporan/laporan_keuangan/laba_rugi_excel.php', $data);
    }

    // =========================================================================
    // MODUL LAPORAN NERACA (ZAHIR ACCOUNTING STYLE)
    // =========================================================================

    /**
     * 1. Neraca (Standar)
     */
    public function neraca_standar()
    {
        $this->_ensure_access();

        $month = (int)($this->input->get('p1_month', true) ?: $this->input->get('month', true) ?: date('n'));
        $year  = (int)($this->input->get('p1_year', true) ?: $this->input->get('year', true) ?: date('Y'));

        if ($month < 1 || $month > 12) $month = (int)date('n');
        if ($year < 2000 || $year > 2100) $year = (int)date('Y');

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_neraca($month, $year);

        $data = [
            'page_title'       => 'KARISMA - Laporan Neraca',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Neraca',
            'report_mode'      => 'standar',
            'month'            => $month,
            'year'             => $year,
            'period_label'     => $reportData['period_label'],
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/neraca'),
            'export_url'       => base_url('laporan/keuangan/neraca-export/standar?month=' . $month . '&year=' . $year),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/neraca_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 2. Neraca Saldo (Trial Balance)
     */
    public function neraca_saldo()
    {
        $this->_ensure_access();

        $month = (int)($this->input->get('p1_month', true) ?: $this->input->get('month', true) ?: date('n'));
        $year  = (int)($this->input->get('p1_year', true) ?: $this->input->get('year', true) ?: date('Y'));

        if ($month < 1 || $month > 12) $month = (int)date('n');
        if ($year < 2000 || $year > 2100) $year = (int)date('Y');

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_neraca_saldo($month, $year);

        $data = [
            'page_title'       => 'KARISMA - Neraca Saldo',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Neraca Saldo',
            'report_mode'      => 'saldo',
            'month'            => $month,
            'year'             => $year,
            'period_label'     => $reportData['period_label'],
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/neraca-saldo'),
            'export_url'       => base_url('laporan/keuangan/neraca-export/saldo?month=' . $month . '&year=' . $year),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/neraca_saldo_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 3. Neraca - 12 Bulan
     */
    public function neraca_12_bulan()
    {
        $this->_ensure_access();

        $month = (int)($this->input->get('p1_month', true) ?: $this->input->get('month', true) ?: date('n'));
        $year  = (int)($this->input->get('p1_year', true) ?: $this->input->get('year', true) ?: date('Y'));

        if ($month < 1 || $month > 12) $month = (int)date('n');
        if ($year < 2000 || $year > 2100) $year = (int)date('Y');

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_neraca_12_bulan($year);

        $monthName = date('F', mktime(0, 0, 0, $month, 10));

        $data = [
            'page_title'       => 'KARISMA - Neraca - 12 Bulan',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Neraca - 12 Bulan',
            'report_mode'      => '12_bulan',
            'month'            => $month,
            'year'             => $year,
            'period_label'     => "{$monthName} {$year}",
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/neraca-12-bulan'),
            'export_url'       => base_url('laporan/keuangan/neraca-export/12_bulan?month=' . $month . '&year=' . $year),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/neraca_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 4. Neraca - Perbandingan 2 Kolom
     */
    public function neraca_perbandingan_2()
    {
        $this->_ensure_access();

        $p1Month = (int)($this->input->get('p1_month', true) ?: date('n'));
        $p1Year  = (int)($this->input->get('p1_year', true) ?: date('Y'));
        $p2Month = (int)($this->input->get('p2_month', true) ?: (($p1Month > 1) ? $p1Month - 1 : 12));
        $p2Year  = (int)($this->input->get('p2_year', true) ?: (($p1Month > 1) ? $p1Year : $p1Year - 1));

        if ($p1Month < 1 || $p1Month > 12) $p1Month = (int)date('n');
        if ($p2Month < 1 || $p2Month > 12) $p2Month = (int)date('n');

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_neraca_perbandingan($p1Month, $p1Year, $p2Month, $p2Year);

        $data = [
            'page_title'       => 'KARISMA - Neraca - Perbandingan 2 Kolom',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Neraca - Perbandingan 2 Kolom',
            'report_mode'      => 'perbandingan_2',
            'p1_month'         => $p1Month,
            'p1_year'          => $p1Year,
            'p2_month'         => $p2Month,
            'p2_year'          => $p2Year,
            'p1_label'         => $reportData['p1_label'],
            'p2_label'         => $reportData['p2_label'],
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/neraca-perbandingan-2'),
            'export_url'       => base_url('laporan/keuangan/neraca-export/perbandingan_2?p1_month=' . $p1Month . '&p1_year=' . $p1Year . '&p2_month=' . $p2Month . '&p2_year=' . $p2Year),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/neraca_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 5. Neraca - Anggaran vs Realisasi
     */
    public function neraca_anggaran()
    {
        $this->_ensure_access();

        $month = (int)($this->input->get('p1_month', true) ?: $this->input->get('month', true) ?: date('n'));
        $year  = (int)($this->input->get('p1_year', true) ?: $this->input->get('year', true) ?: date('Y'));

        if ($month < 1 || $month > 12) $month = (int)date('n');
        if ($year < 2000 || $year > 2100) $year = (int)date('Y');

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_neraca($month, $year);

        $data = [
            'page_title'       => 'KARISMA - Neraca - Anggaran vs Realisasi',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Neraca - Anggaran vs Realisasi',
            'report_mode'      => 'anggaran',
            'month'            => $month,
            'year'             => $year,
            'period_label'     => $reportData['period_label'],
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/neraca-anggaran'),
            'export_url'       => base_url('laporan/keuangan/neraca-export/anggaran?month=' . $month . '&year=' . $year),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/neraca_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * 6. Neraca - Perbandingan 4 Kolom
     */
    public function neraca_perbandingan_4()
    {
        $this->_ensure_access();

        $p1Month = (int)($this->input->get('p1_month', true) ?: date('n'));
        $p1Year  = (int)($this->input->get('p1_year', true) ?: date('Y'));
        $p2Month = (int)($this->input->get('p2_month', true) ?: (($p1Month > 1) ? $p1Month - 1 : 12));
        $p2Year  = (int)($this->input->get('p2_year', true) ?: (($p1Month > 1) ? $p1Year : $p1Year - 1));

        if ($p1Month < 1 || $p1Month > 12) $p1Month = (int)date('n');
        if ($p2Month < 1 || $p2Month > 12) $p2Month = (int)date('n');

        $this->load->model('M_Journal');
        $reportData = $this->M_Journal->get_laporan_neraca_perbandingan($p1Month, $p1Year, $p2Month, $p2Year);

        $data = [
            'page_title'       => 'KARISMA - Neraca - Perbandingan 4 kolom',
            'company_name'     => 'KARISMA 2025',
            'report_title'     => 'Neraca - Perbandingan 4 kolom',
            'report_mode'      => 'perbandingan_4',
            'p1_month'         => $p1Month,
            'p1_year'          => $p1Year,
            'p2_month'         => $p2Month,
            'p2_year'          => $p2Year,
            'p1_label'         => $reportData['p1_label'],
            'p2_label'         => $reportData['p2_label'],
            'data'             => $reportData,
            'print_time'       => date('H:i   d F, Y'),
            'filter_action'    => base_url('laporan/keuangan/neraca-perbandingan-4'),
            'export_url'       => base_url('laporan/keuangan/neraca-export/perbandingan_4?p1_month=' . $p1Month . '&p1_year=' . $p1Year . '&p2_month=' . $p2Month . '&p2_year=' . $p2Year),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/neraca_view.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Export Neraca ke format Excel
     */
    public function neraca_export($type = 'standar')
    {
        $this->_ensure_access();

        $this->load->model('M_Journal');

        $isComparison = in_array($type, ['perbandingan_2', 'perbandingan_4'], true);

        if ($isComparison) {
            $p1Month = (int)($this->input->get('p1_month', true) ?: date('n'));
            $p1Year  = (int)($this->input->get('p1_year', true) ?: date('Y'));
            $p2Month = (int)($this->input->get('p2_month', true) ?: (($p1Month > 1) ? $p1Month - 1 : 12));
            $p2Year  = (int)($this->input->get('p2_year', true) ?: (($p1Month > 1) ? $p1Year : $p1Year - 1));

            $reportData = $this->M_Journal->get_laporan_neraca_perbandingan($p1Month, $p1Year, $p2Month, $p2Year);
            $filename = 'Laporan_Neraca_' . ($type === 'perbandingan_2' ? 'Perbandingan_2_Kolom_' : 'Perbandingan_4_Kolom_') . "{$p1Year}_{$p1Month}_vs_{$p2Year}_{$p2Month}.xls";

            $data = [
                'company_name' => 'KARISMA 2025',
                'report_title' => $type === 'perbandingan_2' ? 'Neraca - Perbandingan 2 Kolom' : 'Neraca - Perbandingan 4 kolom',
                'report_mode'  => $type,
                'p1_month'     => $p1Month,
                'p1_year'      => $p1Year,
                'p2_month'     => $p2Month,
                'p2_year'      => $p2Year,
                'p1_label'     => $reportData['p1_label'],
                'p2_label'     => $reportData['p2_label'],
                'data'         => $reportData,
                'print_time'   => date('H:i   d F, Y'),
            ];
        } elseif ($type === 'saldo') {
            $month = (int)($this->input->get('month', true) ?: date('n'));
            $year  = (int)($this->input->get('year', true) ?: date('Y'));

            $reportData = $this->M_Journal->get_laporan_neraca_saldo($month, $year);
            $filename = "Laporan_Neraca_Saldo_{$year}_{$month}.xls";

            $data = [
                'company_name' => 'KARISMA 2025',
                'report_title' => 'Neraca Saldo',
                'report_mode'  => 'saldo',
                'month'        => $month,
                'year'         => $year,
                'period_label' => $reportData['period_label'],
                'data'         => $reportData,
                'print_time'   => date('H:i   d F, Y'),
            ];
        } elseif ($type === '12_bulan') {
            $month = (int)($this->input->get('month', true) ?: date('n'));
            $year  = (int)($this->input->get('year', true) ?: date('Y'));

            $reportData = $this->M_Journal->get_laporan_neraca_12_bulan($year);
            $filename = "Laporan_Neraca_12_Bulan_{$year}.xls";
            $monthName = date('F', mktime(0, 0, 0, $month, 10));

            $data = [
                'company_name' => 'KARISMA 2025',
                'report_title' => 'Neraca - 12 Bulan',
                'report_mode'  => '12_bulan',
                'month'        => $month,
                'year'         => $year,
                'period_label' => "{$monthName} {$year}",
                'data'         => $reportData,
                'print_time'   => date('H:i   d F, Y'),
            ];
        } else {
            $month = (int)($this->input->get('month', true) ?: date('n'));
            $year  = (int)($this->input->get('year', true) ?: date('Y'));

            $reportData = $this->M_Journal->get_laporan_neraca($month, $year);

            $titles = [
                'standar'  => 'Neraca',
                'anggaran' => 'Neraca - Anggaran vs Realisasi',
            ];
            $title = $titles[$type] ?? 'Neraca';
            $filename = "Laporan_Neraca_{$type}_{$year}_{$month}.xls";

            $data = [
                'company_name' => 'KARISMA 2025',
                'report_title' => $title,
                'report_mode'  => $type,
                'month'        => $month,
                'year'         => $year,
                'period_label' => $reportData['period_label'],
                'data'         => $reportData,
                'print_time'   => date('H:i   d F, Y'),
            ];
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $this->load->view('content/keuangan/laporan/laporan_keuangan/neraca_excel.php', $data);
    }

    // =========================================================================
    // LAPORAN LAINNYA — TAGPD (DAFTAR TAGIHAN PIUTANG)
    // =========================================================================

    /**
     * Mengambil parameter filter TAGPD dari request GET atau POST secara seragam.
     *
     * @return array
     */
    private function _getTagpdFilterParams()
    {
        $todayDate = trim((string)($this->input->get_post('today_date', true) ?: date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $todayDate)) {
            $todayDate = date('Y-m-d');
        }

        return [
            'today_date' => $todayDate,
            'filters'    => [
                'sales'        => trim((string)$this->input->get_post('sales', true)),
                'status_bayar' => trim((string)$this->input->get_post('status_bayar', true)),
                'keyword'      => trim((string)$this->input->get_post('keyword', true)),
            ],
        ];
    }

    /**
     * Halaman utama laporan TAGPD (Daftar Tagihan Piutang).
     */
    public function tagpd_report()
    {
        $this->load->model('M_LaporanTagpd');
        $params = $this->_getTagpdFilterParams();
        $report = $this->M_LaporanTagpd->get_tagpd_report($params['today_date'], $params['filters']);

        $data['page_title']     = 'KARISMA - TAGPD (DAFTAR TAGIHAN PIUTANG)';
        $data['today_date']     = $report['today_date'];
        $data['month_start']    = $report['month_start'];
        $data['sales_options']  = $report['sales_options'];
        $data['column_letters'] = $this->M_LaporanTagpd->get_column_letters();
        $data['initial_report'] = $report;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_lainnya/tagpd_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Endpoint AJAX JSON untuk memuat ulang data TAGPD sesuai filter.
     */
    public function tagpd_data()
    {
        $this->load->model('M_LaporanTagpd');
        $params = $this->_getTagpdFilterParams();
        $report = $this->M_LaporanTagpd->get_tagpd_report($params['today_date'], $params['filters']);
        $tableHtml = $this->load->view('content/keuangan/laporan/laporan_lainnya/_tagpd_table.php', [
            'rows'           => $report['rows'],
            'totals'         => $report['totals'],
            'column_letters' => $this->M_LaporanTagpd->get_column_letters(),
            'is_excel'       => false,
        ], true);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'data'       => $report,
                'table_html' => $tableHtml,
            ]));
    }

    /**
     * Export laporan TAGPD ke Excel (.xls) atau CSV (.csv).
     */
    public function tagpd_export()
    {
        $this->_ensure_access();
        $this->load->model('M_LaporanTagpd');

        $params = $this->_getTagpdFilterParams();
        $format = strtolower(trim((string)($this->input->get('format', true) ?: 'xls')));
        $report = $this->M_LaporanTagpd->get_tagpd_report($params['today_date'], $params['filters']);
        $columnLetters = $this->M_LaporanTagpd->get_column_letters();

        if ($format === 'csv') {
            $filename = 'TAGPD_Daftar_Tagihan_Piutang_' . $report['today_date'] . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $out = fopen('php://output', 'w');
            // Tulis BOM UTF-8 agar Excel membaca karakter dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            // Baris nomor kolom 1..42 (offset 4 kolom awal)
            $numRow = array_fill(0, 46, '');
            for ($i = 4; $i < 46; $i++) {
                $numRow[$i] = $i - 3;
            }
            fputcsv($out, $numRow);

            // Baris Jangka Waktu JT & Today
            $metaRow = array_fill(0, 46, '');
            $metaRow[0] = 'Jangka Waktu JT';
            $metaRow[12] = 'Today:';
            $metaRow[13] = $report['today_date'];
            fputcsv($out, $metaRow);

            // Baris Judul
            $titleRow = array_fill(0, 46, '');
            $titleRow[0] = 'Daftar Tagihan Piutang';
            fputcsv($out, $titleRow);

            // Baris Header Tingkat 1
            $h1 = [
                'No', 'A/Q', 'Tanggal  Faktur', 'Tanggal  Tax', 'No. Faktur', 'FP', 'Plafon', 'Sales', 'Collection',
                'Tgl Masuk Kios Baru', 'Metode', 'KODE', 'Nama Customer/Toko', 'Nama Customer',
                'TAX', '', 'Pengiriman', '', 'Wilayah1', 'SE', 'Jatuh Tempo', 'Tanggal JT', 'Bts JT (hr)',
                'Saldo Awal (Rp)', 'Saldo Awal/Faktur (Rp)', 'Jumlah/Faktur (Rp)',
                'Pelunasan', '', '', '', '', '', '',
                'Tanggal Pelunasan', 'Diselesaikan dg', 'Tgl Regt. (KU)', 'Saldo PD (Rp)', 'Saldo PD/Faktur',
                'Program Bonus OTP', 'Keterangan', 'PH/Bonus/ Faktur', 'Jumlah PH/Bonus/ Faktur',
                'Cocokan Saldo Tax/Non Tax', '', '', 'Bts JT (Hari)'
            ];
            fputcsv($out, $h1);

            // Baris Header Tingkat 2
            $h2 = array_fill(0, 46, '');
            $h2[10] = 'Bayar';
            $h2[14] = 'Alamat';
            $h2[15] = 'Kota';
            $h2[16] = 'Alamat';
            $h2[17] = 'Kota';
            $h2[26] = 'Retur, Pot, Bonus';
            $h2[27] = 'BG';
            $h2[28] = 'Tgl JT BG';
            $h2[29] = 'No. BG';
            $h2[30] = 'BG Cair';
            $h2[31] = 'Tgl BG Cair';
            $h2[32] = 'KU/Cash';
            $h2[34] = 'ket byr';
            $h2[37] = 'Rp.';
            fputcsv($out, $h2);

            // Baris Huruf Kolom A..BH
            fputcsv($out, $columnLetters);

            // Baris Data
            foreach ($report['rows'] as $r) {
                fputcsv($out, array_values($r));
            }

            fclose($out);
            return;
        }

        $filename = 'TAGPD_Daftar_Tagihan_Piutang_' . $report['today_date'] . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $data = [
            'today_date'     => $report['today_date'],
            'month_start'    => $report['month_start'],
            'rows'           => $report['rows'],
            'totals'         => $report['totals'],
            'column_letters' => $columnLetters,
        ];

        $this->load->view('content/keuangan/laporan/laporan_lainnya/tagpd_excel.php', $data);
    }

    /**
     * Metadata konfigurasi 10 laporan Buku Besar & Jurnal format Zahir Accounting.
     */
    private function _zahir_buku_besar_meta($jenis)
    {
        $map = [
            'buku-besar-standar' => [
                'slug'     => 'buku-besar-standar',
                'title'    => 'Buku Besar - Standar',
                'subtitle' => '',
                'category' => 'ledger',
                'variant'  => 'standar',
            ],
            'buku-besar-mutasi' => [
                'slug'     => 'buku-besar-mutasi',
                'title'    => 'Buku Besar - Mutasi',
                'subtitle' => '',
                'category' => 'ledger',
                'variant'  => 'mutasi',
            ],
            'buku-besar-mata-uang' => [
                'slug'     => 'buku-besar-mata-uang',
                'title'    => 'Buku Besar - Mata Uang Sebenarnya',
                'subtitle' => '',
                'category' => 'ledger',
                'variant'  => 'mata_uang',
            ],
            'buku-besar-mutasi-mata-uang' => [
                'slug'     => 'buku-besar-mutasi-mata-uang',
                'title'    => 'Buku Besar Mutasi - Mata Uang Sebenarnya',
                'subtitle' => '',
                'category' => 'ledger',
                'variant'  => 'mutasi_mata_uang',
            ],
            'daftar-jurnal' => [
                'slug'     => 'daftar-jurnal',
                'title'    => 'Daftar Jurnal',
                'subtitle' => 'Semua Transaksi',
                'category' => 'journal',
                'mode'     => 'all',
            ],
            'jurnal-umum' => [
                'slug'     => 'jurnal-umum',
                'title'    => 'Daftar Jurnal',
                'subtitle' => 'General Journal',
                'category' => 'journal',
                'mode'     => 'general',
            ],
            'jurnal-kas-keluar' => [
                'slug'     => 'jurnal-kas-keluar',
                'title'    => 'Jurnal Kas Keluar',
                'subtitle' => 'Write Checks',
                'category' => 'journal',
                'mode'     => 'cash_out',
            ],
            'jurnal-kas-masuk' => [
                'slug'     => 'jurnal-kas-masuk',
                'title'    => 'Jurnal Kas Masuk',
                'subtitle' => 'Cash Deposit',
                'category' => 'journal',
                'mode'     => 'cash_in',
            ],
            'daftar-rekening' => [
                'slug'     => 'daftar-rekening',
                'title'    => 'Daftar Rekening',
                'subtitle' => '',
                'category' => 'coa',
                'mode'     => 'coa',
            ],
            'jejak-audit-rangkuman' => [
                'slug'     => 'jejak-audit-rangkuman',
                'title'    => 'Jejak Audit - Rangkuman',
                'subtitle' => '',
                'category' => 'audit',
                'mode'     => 'audit_summary',
            ],
            'jejak-audit-semua' => [
                'slug'     => 'jejak-audit-semua',
                'title'    => 'Jejak Audit - Semua Transaksi',
                'subtitle' => '',
                'category' => 'audit',
                'mode'     => 'audit_all',
            ],
            'jejak-audit-rincian' => [
                'slug'     => 'jejak-audit-rincian',
                'title'    => 'Rincian Jejak Audit - Semua Transaksi',
                'subtitle' => '',
                'category' => 'audit',
                'mode'     => 'audit_detail',
            ],
        ];

        return $map[$jenis] ?? $map['buku-besar-standar'];
    }

    /**
     * Halaman Laporan Buku Besar & Jurnal format Zahir Accounting.
     */
    public function buku_besar_zahir_report($jenis = 'buku-besar-standar')
    {
        $this->load->model('M_Journal');
        $meta = $this->_zahir_buku_besar_meta($jenis);

        $data['page_title']           = 'KARISMA - ' . strtoupper($meta['title']);
        $data['report_meta']          = $meta;
        $data['report_endpoint']      = base_url('laporan/keuangan/buku-besar-data');
        $data['coa_options']          = $meta['category'] === 'ledger' ? $this->M_Journal->zahir_coa_options() : [];
        $data['journal_type_options'] = $meta['category'] === 'journal' ? $this->M_Journal->zahir_journal_type_options() : [];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_keuangan/buku_besar_zahir_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Endpoint JSON data untuk 10 laporan Buku Besar & Jurnal format Zahir.
     */
    public function buku_besar_zahir_data()
    {
        $this->load->model('M_Journal');

        $jenis      = trim((string) $this->input->get('jenis', true));
        $startDate  = trim((string) $this->input->get('start_date', true));
        $endDate    = trim((string) $this->input->get('end_date', true));
        $idAkun        = trim((string) $this->input->get('id_akun', true));
        $idJenisJurnal = trim((string) $this->input->get('id_jenis_jurnal', true));
        $keterangan    = trim((string) $this->input->get('keterangan', true));
        $nomor         = trim((string) $this->input->get('nomor', true));
        $search        = trim((string) $this->input->get('search', true));

        if ($startDate === '') {
            $startDate = date('Y-m-01');
        }
        if ($endDate === '') {
            $endDate = date('Y-m-t');
        }

        $meta = $this->_zahir_buku_besar_meta($jenis);
        $rows = [];

        if ($meta['category'] === 'ledger') {
            $rows = $this->M_Journal->zahir_ledger_report($startDate, $endDate, $idAkun, $search);
        } elseif ($meta['category'] === 'coa') {
            $rows = $this->M_Journal->zahir_coa_list_report($search);
        } else {
            $rows = $this->M_Journal->zahir_journal_list_report($startDate, $endDate, $meta['mode'], $keterangan, $nomor, $idJenisJurnal);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'meta'       => $meta,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'rows'       => $rows,
            ]));
    }

    // ====================================================
    // Laporan Transaksi Operasional (View & Export Excel)
    // ====================================================
    public function operasional_report()
    {
        $this->load->model('M_LaporanOperasional');
        $dateFrom = trim((string)($this->input->get('date_from', true) ?: date('Y-m-01')));
        $dateTo   = trim((string)($this->input->get('date_to', true) ?: date('Y-m-d')));

        $data['page_title']     = 'KARISMA - LAPORAN TRANSAKSI OPERASIONAL';
        $data['date_from']      = $dateFrom;
        $data['date_to']        = $dateTo;
        $data['initial_report'] = $this->M_LaporanOperasional->get_operasional_report($dateFrom, $dateTo);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_lainnya/operasional_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function operasional_data()
    {
        $this->load->model('M_LaporanOperasional');
        $dateFrom = trim((string)($this->input->get_post('date_from', true) ?: date('Y-m-01')));
        $dateTo   = trim((string)($this->input->get_post('date_to', true) ?: date('Y-m-d')));

        $report = $this->M_LaporanOperasional->get_operasional_report($dateFrom, $dateTo);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data'    => $report,
            ]));
    }

    public function operasional_export()
    {
        if (!file_exists(FCPATH . 'vendor/autoload.php')) {
            show_error('Library PhpSpreadsheet tidak ditemukan.', 500);
            return;
        }
        require_once FCPATH . 'vendor/autoload.php';
        $this->load->model('M_LaporanOperasional');

        $dateFrom = trim((string)($this->input->get('date_from', true) ?: date('Y-m-01')));
        $dateTo   = trim((string)($this->input->get('date_to', true) ?: date('Y-m-d')));
        $report   = $this->M_LaporanOperasional->get_operasional_report($dateFrom, $dateTo);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('KARISMA ERP')
            ->setTitle('Laporan Transaksi Operasional');

        // 1. Sheet Ringkasan
        $s1 = $spreadsheet->getActiveSheet();
        $s1->setTitle('Ringkasan');
        $ringkasanRows = array_map(function ($r) {
            return [$r['indikator'], $r['nilai']];
        }, $report['ringkasan']);
        $this->_writeOperasionalTableSheet(
            $s1,
            'TRANSAKSI OPERASIONAL - KARISMA ERP',
            'Periode: ' . $report['date_from'] . ' s/d ' . $report['date_to'],
            ['Indikator', 'Nilai'],
            $ringkasanRows
        );

        // 2. Sheet PO Detail
        $s2 = $spreadsheet->createSheet(1);
        $s2->setTitle('PO Detail');
        $poRows = array_map(function ($r) {
            return [
                $r['no_po'],
                $r['tgl_transaksi'],
                $r['nama_suplier'],
                $r['kd_barang'],
                $r['nama_barang'],
                (float)$r['qty'],
                $r['satuan'],
                (float)$r['harga_po'],
                $r['skema_ppn'],
                (float)$r['nilai_barang'],
            ];
        }, $report['po_detail']);
        $this->_writeOperasionalTableSheet(
            $s2,
            'PURCHASE ORDER (PO DETAIL)',
            'Periode: ' . $report['date_from'] . ' s/d ' . $report['date_to'],
            ['No. PO', 'Tanggal PO', 'Supplier', 'Kode Barang', 'Nama Barang', 'Qty', 'Satuan', 'Harga PO/Unit', 'Skema PPN', 'Nilai Barang'],
            $poRows,
            ['F' => '#,##0.##', 'H' => '#,##0.00', 'J' => '#,##0.00'],
            ['F', 'J']
        );

        // 3. Sheet LPB Barang Datang (termasuk detail barang, harga barang, dan tanggal realisasi PO)
        $s3 = $spreadsheet->createSheet(2);
        $s3->setTitle('LPB Barang Datang');
        $lpbRows = array_map(function ($r) {
            return [
                $r['tanggal_realisasi'],
                $r['no_po'],
                $r['nomor_lpb'],
                $r['nama_suplier'],
                $r['kd_barang'],
                $r['nama_barang'],
                $r['kategori_pajak'],
                (float)$r['qty_po'],
                (float)$r['qty_datang'],
                (float)$r['qty_sisa'],
                $r['satuan'],
                (float)$r['harga_inc_ppn'],
                (float)$r['harga_dpp'],
                (float)$r['nilai_datang'],
                (float)$r['nilai_datang_inc'],
                $r['status_realisasi'],
            ];
        }, $report['lpb_detail']);
        $this->_writeOperasionalTableSheet(
            $s3,
            'LPB BARANG DATANG & REALISASI PO',
            'Periode: ' . $report['date_from'] . ' s/d ' . $report['date_to'],
            ['Tgl Realisasi PO', 'No. PO', 'No. LPB', 'Supplier', 'Kode Barang', 'Detail Barang', 'Kategori', 'Qty PO', 'Qty Datang', 'Qty Sisa', 'Satuan', 'Harga Inc PPN', 'Harga DPP', 'Nilai Datang (DPP)', 'Nilai Datang Inc PPN', 'Status Realisasi'],
            $lpbRows,
            ['H' => '#,##0.##', 'I' => '#,##0.##', 'J' => '#,##0.##', 'L' => '#,##0.00', 'M' => '#,##0.00', 'N' => '#,##0.00', 'O' => '#,##0.00'],
            ['H', 'I', 'J', 'N', 'O']
        );

        // 4. Sheet Faktur Penjualan (HPP Moving Average sesuai jurnal)
        $s4 = $spreadsheet->createSheet(3);
        $s4->setTitle('Faktur Penjualan');
        $fakturRows = array_map(function ($r) {
            return [
                $r['tanggal_faktur'],
                $r['no_faktur'],
                $r['customer_name'],
                $r['cara_bayar_tempo'],
                $r['kd_barang'],
                $r['nama_barang'],
                (float)$r['qty'],
                $r['satuan'],
                (float)$r['nilai_faktur'],
                (float)$r['dpp'],
                (float)$r['hpp_unit'],
                (float)$r['hpp_total'],
                (float)$r['laba_kotor'],
            ];
        }, $report['faktur_penjualan']);
        $this->_writeOperasionalTableSheet(
            $s4,
            'FAKTUR PENJUALAN (HPP MOVING AVERAGE)',
            'Periode: ' . $report['date_from'] . ' s/d ' . $report['date_to'],
            ['Tgl Faktur', 'No. Faktur', 'Customer', 'Cara Bayar / Tempo', 'Kode Barang', 'Barang', 'Qty', 'Satuan', 'Nilai Faktur', 'DPP', 'HPP Satuan', 'HPP Total', 'Laba Kotor'],
            $fakturRows,
            ['G' => '#,##0.##', 'I' => '#,##0.00', 'J' => '#,##0.00', 'K' => '#,##0.00', 'L' => '#,##0.00', 'M' => '#,##0.00'],
            ['G', 'I', 'J', 'L', 'M']
        );

        // 5. Sheet Retur Pembelian (No. Jurnal di depan)
        $s5 = $spreadsheet->createSheet(4);
        $s5->setTitle('Retur Pembelian');
        $rbRows = array_map(function ($r) {
            return [
                $r['tanggal_retur'],
                $r['nomor_jurnal'],
                $r['no_retur_pembelian'],
                $r['nama_suplier'],
                $r['referensi'],
                $r['barang_lot'],
                (float)$r['qty_retur'],
                $r['satuan'],
                (float)$r['dpp'],
                (float)$r['ppn'],
                (float)$r['total_retur'],
            ];
        }, $report['retur_pembelian']);
        $this->_writeOperasionalTableSheet(
            $s5,
            'RETUR PEMBELIAN',
            'Periode: ' . $report['date_from'] . ' s/d ' . $report['date_to'],
            ['Tgl Retur', 'No. Jurnal', 'No. Retur', 'Supplier', 'Referensi PO / LPB', 'Barang dan Lot', 'Qty Retur', 'Satuan', 'DPP', 'PPN', 'Total Retur'],
            $rbRows,
            ['G' => '#,##0.##', 'I' => '#,##0.00', 'J' => '#,##0.00', 'K' => '#,##0.00'],
            ['G', 'I', 'J', 'K']
        );

        // 6. Sheet Retur Penjualan (No. Jurnal di depan)
        $s6 = $spreadsheet->createSheet(5);
        $s6->setTitle('Retur Penjualan');
        $rpRows = array_map(function ($r) {
            return [
                $r['tanggal_retur'],
                $r['nomor_jurnal'],
                $r['no_retur'],
                $r['nama_customer'],
                $r['faktur_asal'],
                $r['barang_lot'],
                $r['alasan'],
                (float)$r['qty_retur'],
                $r['satuan'],
                (float)$r['nilai_retur'],
                (float)$r['saldo_retur_aktif'],
                (float)$r['hpp_reversal'],
            ];
        }, $report['retur_penjualan']);
        $this->_writeOperasionalTableSheet(
            $s6,
            'RETUR PENJUALAN',
            'Periode: ' . $report['date_from'] . ' s/d ' . $report['date_to'],
            ['Tgl Retur', 'No. Jurnal', 'No. Retur', 'Customer', 'Faktur Asal', 'Barang dan Lot', 'Alasan', 'Qty Retur', 'Satuan', 'Nilai Retur', 'Saldo Retur Aktif', 'HPP Reversal'],
            $rpRows,
            ['H' => '#,##0.##', 'J' => '#,##0.00', 'K' => '#,##0.00', 'L' => '#,##0.00'],
            ['H', 'J', 'K', 'L']
        );

        // 7. Sheet Pembayaran Customer
        $s7 = $spreadsheet->createSheet(6);
        $s7->setTitle('Pembayaran Customer');
        $pcRows = array_map(function ($r) {
            return [
                $r['tanggal_pembayaran'],
                $r['nomor_transaksi'],
                $r['customer_kios'],
                $r['no_faktur'],
                $r['metode_rekening'],
                $r['rincian'],
                (float)$r['nilai'],
                $r['status_bayar'],
            ];
        }, $report['pembayaran_customer']);
        $this->_writeOperasionalTableSheet(
            $s7,
            'PEMBAYARAN CUSTOMER',
            'Periode: ' . $report['date_from'] . ' s/d ' . $report['date_to'],
            ['Tanggal', 'No. Transaksi / Jurnal', 'Customer / Kios', 'Faktur', 'Metode / Rekening', 'Rincian', 'Nilai', 'Status'],
            $pcRows,
            ['G' => '#,##0.00'],
            ['G']
        );

        // 8. Sheet Pembayaran Supplier (No. Jurnal di depan, No. Bukti Bayar di belakang)
        $s8 = $spreadsheet->createSheet(7);
        $s8->setTitle('Pembayaran Supplier');
        $psRows = array_map(function ($r) {
            return [
                $r['tanggal_pembayaran'],
                $r['nomor_jurnal'],
                $r['nama_suplier'],
                $r['no_lpb'],
                $r['rekening'],
                (float)$r['nilai'],
                $r['status_bayar'],
                $r['no_bukti_bayar'],
            ];
        }, $report['pembayaran_supplier']);
        $this->_writeOperasionalTableSheet(
            $s8,
            'PEMBAYARAN SUPPLIER',
            'Periode: ' . $report['date_from'] . ' s/d ' . $report['date_to'],
            ['Tanggal', 'No. Jurnal', 'Supplier', 'No. LPB', 'Rekening', 'Nilai', 'Status', 'No. Bukti Bayar'],
            $psRows,
            ['F' => '#,##0.00'],
            ['F']
        );

        // 9. Sheet Laba Rugi
        $s9 = $spreadsheet->createSheet(8);
        $s9->setTitle('Laba Rugi');
        $this->_writeOperasionalLabaRugiSheet($s9, $report['laba_rugi'], $report['date_from'], $report['date_to']);

        // 10. Sheet Neraca Saldo & Laba Kotor (sesuai format rekap akun & laba kotor)
        $s10 = $spreadsheet->createSheet(9);
        $s10->setTitle('Neraca Saldo & Laba Kotor');
        $this->_writeOperasionalNeracaSaldoSheet($s10, $report['mutasi_akun']);

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Operasional_' . $report['date_from'] . '_sd_' . $report['date_to'] . '_' . date('Ymd_His') . '.xlsx';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $prevErr = error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        error_reporting($prevErr);
        exit;
    }

    private function _writeOperasionalTableSheet($sheet, $title, $subtitle, array $headers, array $rows, array $numFormats = [], array $sumCols = [])
    {
        $colCount = count($headers);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet->setCellValue('A2', $subtitle);
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:' . $lastCol . '4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E5B5A'],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $rowNum = 5;
        $sums = array_fill_keys($sumCols, 0.0);

        if (empty($rows)) {
            $sheet->setCellValue('A' . $rowNum, 'Tidak ada data pada periode ini.');
            $sheet->mergeCells('A' . $rowNum . ':' . $lastCol . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $rowNum)->getFont()->setItalic(true);
            $rowNum++;
        } else {
            foreach ($rows as $r) {
                foreach ($r as $colIdx => $val) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                    if (is_float($val) || is_int($val)) {
                        $sheet->setCellValue($colLetter . $rowNum, $val);
                        if (isset($sums[$colLetter])) {
                            $sums[$colLetter] += (float)$val;
                        }
                    } else {
                        $sheet->setCellValueExplicit($colLetter . $rowNum, (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
                $rowNum++;
            }

            if (!empty($sumCols)) {
                $sheet->setCellValue('A' . $rowNum, 'TOTAL (' . count($rows) . ' baris)');
                foreach ($sums as $colLetter => $totalVal) {
                    $sheet->setCellValue($colLetter . $rowNum, $totalVal);
                }
                $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->getFont()->setBold(true);
                $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E2E8F0');
            }
        }

        foreach ($numFormats as $colLetter => $fmt) {
            $sheet->getStyle($colLetter . '5:' . $colLetter . $rowNum)->getNumberFormat()->setFormatCode($fmt);
        }

        $lastDataRow = empty($rows) ? 5 : ($emptyRow = !empty($sumCols) ? $rowNum : $rowNum - 1);
        $sheet->getStyle('A4:' . $lastCol . $lastDataRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        for ($c = 1; $c <= $colCount; $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        $sheet->freezePane('A5');
    }

    private function _writeOperasionalLabaRugiSheet($sheet, array $labaRugi, $dateFrom, $dateTo)
    {
        $s = $labaRugi['summary'];
        $sheet->setCellValue('A1', 'RINGKASAN LABA RUGI PENJUALAN');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet->setCellValue('A2', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        $sheet->fromArray(['Ringkasan Laba Rugi Penjualan', 'Nilai'], null, 'A4');
        $sheet->getStyle('A4:B4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E5B5A']],
        ]);

        $summaryItems = [
            ['Penjualan terfaktur sebelum retur (DPP)', (float)$s['penjualan_dpp']],
            ['Retur penjualan (DPP)', (float)$s['retur_dpp']],
            ['Penjualan bersih', (float)$s['penjualan_bersih']],
            ['HPP penjualan sebelum retur (Moving Average)', (float)$s['hpp_faktur']],
            ['Pengurang HPP atas barang yang diretur', (float)$s['hpp_retur']],
            ['HPP bersih', (float)$s['hpp_bersih']],
            ['Laba kotor setelah retur', (float)$s['laba_kotor']],
            ['Margin laba kotor (%)', round((float)$s['margin'], 2)],
        ];
        $r = 5;
        foreach ($summaryItems as $item) {
            $sheet->setCellValue('A' . $r, $item[0]);
            $sheet->setCellValue('B' . $r, $item[1]);
            $r++;
        }
        $sheet->getStyle('B5:B11')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('B12')->getNumberFormat()->setFormatCode('0.00"%"');
        $sheet->getStyle('A4:B12')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function _writeOperasionalNeracaSaldoSheet($sheet, array $ma)
    {
        $numFmt = '#,##0.00;(#,##0.00);"-"';
        $r = 1;
        foreach (($ma['neraca_saldo'] ?? []) as $item) {
            $sheet->setCellValueExplicit('A' . $r, (string)$item['kode_akun'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $r, $item['nama_akun']);
            if ($item['debit_saldo'] !== null) {
                $sheet->setCellValue('C' . $r, (float)$item['debit_saldo']);
            }
            if ($item['kredit_saldo'] !== null) {
                $sheet->setCellValue('D' . $r, (float)$item['kredit_saldo']);
            }
            $sheet->getStyle('A' . $r . ':D' . $r)->getFont()->setBold(true);
            $r++;
        }

        // Baris total balance Debit & Kredit
        $sheet->setCellValue('C' . $r, (float)($ma['total_debit'] ?? 0));
        $sheet->setCellValue('D' . $r, (float)($ma['total_kredit'] ?? 0));
        $sheet->getStyle('C' . $r . ':D' . $r)->getFont()->setBold(true);
        $sheet->getStyle('C' . $r . ':D' . $r)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $r += 2;
        $sheet->getStyle('A' . $r . ':D' . $r)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $r++;

        // Penjualan & Retur
        foreach (($ma['penjualan_accounts'] ?? []) as $p) {
            $sheet->setCellValue('B' . $r, $p['nama_akun']);
            $sheet->setCellValue('C' . $r, (float)$p['nilai']);
            $sheet->getStyle('B' . $r . ':C' . $r)->getFont()->setBold(true);
            $r++;
        }
        $sheet->setCellValue('D' . $r, (float)($ma['total_penjualan'] ?? 0));
        $sheet->getStyle('D' . $r)->getFont()->setBold(true);
        $sheet->getStyle('C' . ($r - 1))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $r++;

        // HPP
        foreach (($ma['hpp_accounts'] ?? []) as $h) {
            $sheet->setCellValue('B' . $r, $h['nama_akun']);
            $sheet->setCellValue('C' . $r, (float)$h['nilai']);
            $sheet->getStyle('B' . $r . ':C' . $r)->getFont()->setBold(true);
            $r++;
        }
        $sheet->setCellValue('D' . $r, (float)($ma['total_hpp'] ?? 0));
        $sheet->getStyle('D' . $r)->getFont()->setBold(true);
        $sheet->getStyle('C' . ($r - 1))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $r++;

        // Laba Kotor
        $sheet->setCellValue('B' . $r, 'LABA KOTOR');
        $sheet->setCellValue('D' . $r, (float)($ma['laba_kotor'] ?? 0));
        $sheet->getStyle('B' . $r . ':D' . $r)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
        ]);

        $sheet->getStyle('C1:D' . $r)->getNumberFormat()->setFormatCode($numFmt);
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
