<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Laporan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session']);
        $this->load->helper(['url']);
        $this->_ensure_access();
    }

    private function _ensure_access()
    {
        if (!$this->session->userdata('logged_in')) {
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
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/laporan_penjualan_piutang/jurnal_penjualan_report.php', $data);
        $this->load->view('partial/main/footergdg.php');
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

    public function kartu_stock_gudang_data()
    {
        $kd_barang = $this->input->post('kd_barang', true);
        $kelompok_barang = $this->input->post('kelompok_barang', true); // will hold kelompok_dagang ID
        $id_gudang = $this->input->post('id_gudang', true);
        $start_date = $this->input->post('start_date', true);
        $end_date = $this->input->post('end_date', true);

        // Fetch target products
        $this->db->select('kode_barang, nama_barang, satuan, kelompok_dagang');
        $this->db->from('tbpo_barang');
        if ($kd_barang && $kd_barang !== 'all') {
            $this->db->where('kode_barang', $kd_barang);
        }
        if ($kelompok_barang && $kelompok_barang !== 'all') {
            $this->db->where('kelompok_dagang', $kelompok_barang);
        }
        $products = $this->db->get()->result_array();

        $all_products_data = [];
        
        foreach ($products as $prod) {
            // Fetch IN transactions
            $in_query = "SELECT l.tgl_sj AS tanggal, l.nomor_lpb AS referensi, ld.qty_diterima AS qty, ld.harga_satuan AS harga, l.gudang_id, 'IN' AS type, b.satuan
                         FROM tb_lpb l
                         JOIN tb_lpb_detail ld ON l.id_lpb = ld.id_lpb
                         JOIN tbpo_barang b ON ld.kd_barang = b.kode_barang
                         WHERE ld.kd_barang = ? AND l.status_lpb = 1";
            $params_in = [$prod['kode_barang']];
            if ($id_gudang && $id_gudang !== 'all') {
                $in_query .= " AND l.gudang_id = ?";
                $params_in[] = $id_gudang;
            }
            $in_tx = $this->db->query($in_query, $params_in)->result_array();

            // Fetch OUT transactions
            $out_query = "SELECT f.tanggal_faktur AS tanggal, f.no_faktur AS referensi, fd.qty, fd.hrg_satuan AS harga, f.gudang_id, 'OUT' AS type, b.satuan
                          FROM tbso_faktur_penjualan f
                          JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                          JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                          WHERE fd.kd_barang = ? AND f.status = 'confirmed'";
            $params_out = [$prod['kode_barang']];
            if ($id_gudang && $id_gudang !== 'all') {
                $out_query .= " AND f.gudang_id = ?";
                $params_out[] = $id_gudang;
            }
            $out_tx = $this->db->query($out_query, $params_out)->result_array();

            // Merge and sort
            $txs = array_merge($in_tx, $out_tx);
            usort($txs, function($a, $b) {
                $t1 = strtotime($a['tanggal']);
                $t2 = strtotime($b['tanggal']);
                if ($t1 === $t2) {
                    // IN transactions come first on the same day
                    return ($a['type'] === 'IN') ? -1 : 1;
                }
                return $t1 < $t2 ? -1 : 1;
            });

            // Calculate running values
            $qty_saldo = 0.0;
            $nilai_saldo = 0.0;
            $average_hpp = 0.0;

            $opening_balance = [
                'tanggal' => '',
                'referensi' => 'O/B Opening Balance',
                'unit' => !empty($txs) ? $txs[0]['satuan'] : $prod['satuan'],
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
            
            // Subtotal metrics
            $sub_masuk_qty = 0.0;
            $sub_masuk_nilai = 0.0;
            $sub_keluar_qty = 0.0;
            $sub_keluar_nilai = 0.0;

            foreach ($txs as $tx) {
                $qty = (float)$tx['qty'];
                $harga = (float)$tx['harga'];
                $tx_date = $tx['tanggal'];

                if ($tx['type'] === 'IN') {
                    $qty_saldo += $qty;
                    $nilai_saldo += ($qty * $harga);
                    $average_hpp = $qty_saldo > 0 ? ($nilai_saldo / $qty_saldo) : 0.0;
                } else {
                    $qty_saldo -= $qty;
                    $nilai_saldo -= ($qty * $average_hpp);
                }

                if (strtotime($tx_date) < strtotime($start_date)) {
                    // Update opening balance
                    $opening_balance['saldo_qty'] = $qty_saldo;
                    $opening_balance['saldo_harga'] = $average_hpp;
                    $opening_balance['saldo_nilai'] = $nilai_saldo;
                } else if (strtotime($tx_date) <= strtotime($end_date)) {
                    // Transaction falls inside filter period
                    $row = [
                        'tanggal' => $tx['tanggal'],
                        'referensi' => ($tx['type'] === 'IN' ? 'PJ ' : 'SJ ') . $tx['referensi'],
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
                        $row['masuk_qty'] = $qty;
                        $row['masuk_harga'] = $harga;
                        $row['masuk_nilai'] = $qty * $harga;
                        
                        $sub_masuk_qty += $qty;
                        $sub_masuk_nilai += ($qty * $harga);
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

            // Prep opening balance unit
            if (!empty($report_rows)) {
                $opening_balance['unit'] = $report_rows[0]['unit'];
            }

            // Only include product if there are transactions in period, or if the opening balance is non-zero
            if (!empty($report_rows) || $opening_balance['saldo_qty'] != 0) {
                $grp_desc = 'Tanpa Kelompok';
                if (!empty($prod['kelompok_dagang'])) {
                    $grp_row = $this->db->get_where('tbkeu_kelompok_dagang', ['NOINDEX' => $prod['kelompok_dagang']])->row_array();
                    if ($grp_row) {
                        $grp_desc = $grp_row['DESKRIPSI'];
                    }
                }

                $all_products_data[] = [
                    'product_code' => $prod['kode_barang'],
                    'product_name' => $prod['nama_barang'],
                    'kelompok_barang' => $grp_desc,
                    'opening_balance' => $opening_balance,
                    'rows' => $report_rows,
                    'sub_masuk_qty' => $sub_masuk_qty,
                    'sub_masuk_harga' => $sub_masuk_qty > 0 ? ($sub_masuk_nilai / $sub_masuk_qty) : 0.0,
                    'sub_masuk_nilai' => $sub_masuk_nilai,
                    'sub_keluar_qty' => $sub_keluar_qty,
                    'sub_keluar_harga' => $sub_keluar_qty > 0 ? ($sub_keluar_nilai / $sub_keluar_qty) : 0.0,
                    'sub_keluar_nilai' => $sub_keluar_nilai,
                    'final_saldo_qty' => $qty_saldo,
                    'final_saldo_harga' => $average_hpp,
                    'final_saldo_nilai' => $nilai_saldo
                ];
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $all_products_data
            ]));
    }
}

