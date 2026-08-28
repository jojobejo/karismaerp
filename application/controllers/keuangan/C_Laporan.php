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
            $in_query = "SELECT l.tgl_sj AS tanggal, l.nomor_lpb AS referensi, ld.qty_diterima AS qty, ld.harga_satuan AS harga, l.gudang_id, 'IN' AS type, 'PJ ' AS ref_prefix, b.satuan
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

            // Fetch IN transactions from Retur Penjualan
            $retur_query = "SELECT r.tanggal_retur AS tanggal, r.no_retur AS referensi, rd.qty_retur AS qty, 0 AS harga, r.gudang_id, 'IN' AS type, 'RJ ' AS ref_prefix, b.satuan
                            FROM tbrp_retur_penjualan_header r
                            JOIN tbrp_retur_penjualan_detail rd ON r.id_retur = rd.id_retur
                            JOIN tbpo_barang b ON (
                                (rd.kd_barang IS NOT NULL AND rd.kd_barang != '' AND rd.kd_barang = b.kode_barang) OR
                                (TRIM(LOWER(rd.nama_barang)) = TRIM(LOWER(b.nama_barang)))
                            )
                            WHERE b.kode_barang = ? AND r.status_retur NOT IN ('ditolak', 'batal')";
            $params_retur = [$prod['kode_barang']];
            if ($id_gudang && $id_gudang !== 'all') {
                $retur_query .= " AND r.gudang_id = ?";
                $params_retur[] = $id_gudang;
            }
            $retur_tx = $this->db->query($retur_query, $params_retur)->result_array();

            // Merge all IN transactions (LPB & Retur Penjualan)
            $in_tx = array_merge($in_tx, $retur_tx);

            // Fetch OUT transactions from sales invoices (tbso_faktur_detail)
            $out_query = "SELECT f.tanggal_faktur AS tanggal, f.no_faktur AS referensi, fd.qty, fd.hrg_satuan AS harga, f.gudang_id, 'OUT' AS type, 'SJ ' AS ref_prefix, b.satuan
                          FROM tbso_faktur_penjualan f
                          JOIN tbso_faktur_detail fd ON f.id_faktur = fd.id_faktur
                          JOIN tbpo_barang b ON fd.kd_barang = b.kode_barang
                          WHERE fd.kd_barang = ? AND f.status NOT IN ('draft', 'cancelled')";
            $params_out = [$prod['kode_barang']];
            if ($id_gudang && $id_gudang !== 'all') {
                $out_query .= " AND f.gudang_id = ?";
                $params_out[] = $id_gudang;
            }
            $out_tx = $this->db->query($out_query, $params_out)->result_array();

            // Fetch OUT transactions from Retur Pembelian (Retur ke Supplier)
            if ($this->db->table_exists('tb_retur_pembelian') && $this->db->table_exists('tb_retur_pembelian_detail')) {
                $retur_beli_query = "SELECT rb.tanggal_retur AS tanggal, rb.no_retur_pembelian AS referensi, rbd.qty_retur AS qty, rbd.harga_satuan AS harga, rb.gudang_id, 'OUT' AS type, 'RB ' AS ref_prefix, b.satuan
                                     FROM tb_retur_pembelian rb
                                     JOIN tb_retur_pembelian_detail rbd ON rb.id_retur_pembelian = rbd.id_retur_pembelian
                                     JOIN tbpo_barang b ON rbd.kd_barang = b.kode_barang
                                     WHERE rbd.kd_barang = ? AND rb.status NOT IN ('DRAFT', 'REJECTED', 'BATAL')";
                $params_retur_beli = [$prod['kode_barang']];
                if ($id_gudang && $id_gudang !== 'all') {
                    $retur_beli_query .= " AND rb.gudang_id = ?";
                    $params_retur_beli[] = $id_gudang;
                }
                $retur_beli_tx = $this->db->query($retur_beli_query, $params_retur_beli)->result_array();
                $out_tx = array_merge($out_tx, $retur_beli_tx);
            }

            // Fetch transactions from Penyesuaian Persediaan / Penyesuaian Barang (tbkeu_penyesuaian_barang)
            $adj_query = "SELECT pb.tanggal, pb.no_referensi AS referensi, 
                                 ABS(pbd.jumlah) AS qty, 
                                 0 AS harga, 
                                 pb.id_gudang_dari AS gudang_id, 
                                 IF(pbd.jumlah > 0, 'IN', 'OUT') AS type, 
                                 'PB ' AS ref_prefix, 
                                 b.satuan,
                                 pb.id_gudang_ke
                          FROM tbkeu_penyesuaian_barang pb
                          JOIN tbkeu_penyesuaian_barang_detail pbd ON pb.id_penyesuaian = pbd.id_penyesuaian
                          JOIN tbpo_barang b ON pbd.kd_barang = b.kode_barang
                          WHERE pbd.kd_barang = ? AND pb.status NOT IN ('BATAL', 'CANCEL')";
            $params_adj = [$prod['kode_barang']];
            $adj_tx = $this->db->query($adj_query, $params_adj)->result_array();

            $adj_in_tx = [];
            $adj_out_tx = [];

            foreach ($adj_tx as $atx) {
                $gudang_dari = !empty($atx['gudang_id']) ? (int)$atx['gudang_id'] : 0;
                $gudang_ke   = !empty($atx['id_gudang_ke']) ? (int)$atx['id_gudang_ke'] : 0;

                // 1. Gudang Asal
                if (!$id_gudang || $id_gudang === 'all' || (string)$gudang_dari === (string)$id_gudang) {
                    if ($atx['type'] === 'IN') {
                        $adj_in_tx[] = $atx;
                    } else {
                        $adj_out_tx[] = $atx;
                    }
                }

                // 2. Gudang Tujuan (HANYA jika transfer ke gudang berbeda dan barang keluar dari gudang asal)
                if ($gudang_ke > 0 && $gudang_ke !== $gudang_dari && $atx['type'] === 'OUT') {
                    if (!$id_gudang || $id_gudang === 'all' || (string)$gudang_ke === (string)$id_gudang) {
                        $in_transfer = $atx;
                        $in_transfer['gudang_id'] = $gudang_ke;
                        $in_transfer['type'] = 'IN';
                        $in_transfer['ref_prefix'] = 'TF ';
                        $adj_in_tx[] = $in_transfer;
                    }
                }
            }

            // Gabungkan semua transaksi IN & OUT
            $in_tx = array_merge($in_tx, $adj_in_tx);
            $out_tx = array_merge($out_tx, $adj_out_tx);

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
                    $in_harga = ($harga > 0) ? $harga : $average_hpp;
                    $qty_saldo += $qty;
                    $nilai_saldo += ($qty * $in_harga);
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
}

