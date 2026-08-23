<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_Transaksi (Admin Transaction Hub)
 * Mengelola transaksi ERP yang memiliki dampak jurnal otomatis:
 * 1. Penjualan (Faktur Penjualan)
 * 2. Pembelian (LPB)
 * 3. Pembayaran Customer (Pelunasan Piutang)
 * 4. Pembayaran Supplier (Pelunasan Hutang)
 * 5. Retur Penjualan
 * 6. Retur Pembelian
 * Beserta fungsi Audit, Repost, Edit, Delete, dan Auto-Sync Jurnal Akuntansi.
 * Dioptimalkan untuk performa tinggi (push-down date filters & correlated sub-sums).
 */
class M_Transaksi extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('Accounting_service');
        $this->load->library('Accounting_source_service');
        $this->load->model('M_Journal');
    }

    // =========================================================================
    // 1. QUERY & LIST TRANSAKSI OTOMATIS JURNAL (HIGH PERFORMANCE)
    // =========================================================================

    /**
     * Mengambil daftar transaksi gabungan atau per jenis transaksi
     */
    public function get_transactions($category = 'all', $filters = [], $limit = 50, $offset = 0)
    {
        $category = strtolower(trim((string)$category));
        $today = date('Y-m-d');
        $date_from = !empty($filters['date_from']) ? $filters['date_from'] : $today;
        $date_to   = !empty($filters['date_to']) ? $filters['date_to'] : $today;
        $search    = trim((string)($filters['search'] ?? ''));
        $status    = trim((string)($filters['status'] ?? ''));
        $limit     = max(1, (int)$limit);
        $offset    = max(0, (int)$offset);

        $subqueries = [];
        $queryParams = [];

        // 1. PENJUALAN (FAKTUR PENJUALAN)
        if ($category === 'all' || $category === 'penjualan' || $category === 'faktur_penjualan') {
            if ($this->db->table_exists('tbso_faktur_penjualan')) {
                $subqueries[] = "
                    SELECT 
                        'penjualan' AS trans_category,
                        'Penjualan (Faktur)' AS trans_category_label,
                        f.id_faktur AS id_transaksi,
                        f.no_faktur AS no_dokumen,
                        COALESCE(f.no_so, '') AS no_referensi,
                        f.tanggal_faktur AS tanggal_transaksi,
                        COALESCE(NULLIF(f.customer_name, ''), c.nama_customer, 'Customer') AS nama_entitas,
                        COALESCE(f.status, 'posted') AS status_transaksi,
                        COALESCE((SELECT SUM(d.subtotal_after_disc) FROM tbso_faktur_detail d WHERE d.id_faktur = f.id_faktur), 0) AS total_nominal,
                        COALESCE(NULLIF(f.catatan, ''), CONCAT('Faktur Penjualan: ', f.no_faktur)) AS keterangan,
                        j.id_jurnal,
                        j.nomor_jurnal,
                        j.status AS status_jurnal,
                        j.total_debit AS journal_debit,
                        j.total_kredit AS journal_kredit,
                        f.create_at AS created_at
                    FROM tbso_faktur_penjualan f
                    LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
                    LEFT JOIN tbkeu_jurnal j ON (
                        (j.source_module = 'SALES' AND j.source_type = 'FAKTUR_PENJUALAN' AND (j.source_id = f.no_faktur OR j.source_no = f.no_faktur))
                        OR j.idempotency_key = CONCAT('SALES_INVOICE-FAKTUR-', f.no_faktur)
                    )
                    WHERE f.tanggal_faktur >= ? AND f.tanggal_faktur <= ?
                ";
                $queryParams[] = $date_from;
                $queryParams[] = $date_to;
            }
        }

        // 2. PEMBELIAN (LPB / LAPORAN PENERIMAAN BARANG)
        if ($category === 'all' || $category === 'pembelian' || $category === 'lpb') {
            if ($this->db->table_exists('tb_lpb')) {
                $subqueries[] = "
                    SELECT 
                        'pembelian' AS trans_category,
                        'Pembelian (LPB)' AS trans_category_label,
                        l.id_lpb AS id_transaksi,
                        COALESCE(NULLIF(l.nomor_lpb, ''), l.no_po, CONCAT('LPB #', l.id_lpb)) AS no_dokumen,
                        COALESCE(l.no_po, l.nosj, '') AS no_referensi,
                        COALESCE(l.tgl_sj, DATE(l.input_at)) AS tanggal_transaksi,
                        COALESCE(s.nama_suplier, 'Supplier') AS nama_entitas,
                        CASE WHEN l.status_lpb = 1 THEN 'selesai' ELSE 'draft' END AS status_transaksi,
                        COALESCE((SELECT SUM(ld.total_harga) FROM tb_lpb_detail ld WHERE ld.id_lpb = l.id_lpb), 0) AS total_nominal,
                        COALESCE(NULLIF(l.keterangan, ''), CONCAT('Penerimaan Barang PO ', COALESCE(l.no_po, ''))) AS keterangan,
                        j.id_jurnal,
                        j.nomor_jurnal,
                        j.status AS status_jurnal,
                        j.total_debit AS journal_debit,
                        j.total_kredit AS journal_kredit,
                        l.input_at AS created_at
                    FROM tb_lpb l
                    LEFT JOIN tbpo_po p ON (p.kd_po = l.kd_po AND p.no_po = l.no_po)
                    LEFT JOIN tbpo_suplier s ON s.kd_suplier = p.kd_suplier
                    LEFT JOIN tbkeu_jurnal j ON (
                        (j.source_module = 'LOGISTIK' AND j.source_type = 'LPB_FINAL' AND j.source_id = CAST(l.id_lpb AS CHAR))
                        OR j.idempotency_key = CONCAT('GOODS_RECEIPT-LPB-', l.id_lpb)
                    )
                    WHERE COALESCE(l.tgl_sj, DATE(l.input_at)) >= ? AND COALESCE(l.tgl_sj, DATE(l.input_at)) <= ?
                ";
                $queryParams[] = $date_from;
                $queryParams[] = $date_to;
            }
        }

        // 3. PEMBAYARAN CUSTOMER (PELUNASAN PIUTANG FAKTUR)
        if ($category === 'all' || $category === 'pembayaran_customer' || $category === 'piutang') {
            if ($this->db->table_exists('tbkeu_pembayaran_faktur')) {
                $subqueries[] = "
                    SELECT 
                        'pembayaran_customer' AS trans_category,
                        'Pembayaran Customer' AS trans_category_label,
                        pf.id_pembayaran AS id_transaksi,
                        CONCAT('BYR-', pf.id_pembayaran) AS no_dokumen,
                        COALESCE(pf.no_faktur, '') AS no_referensi,
                        pf.tanggal_pembayaran AS tanggal_transaksi,
                        COALESCE(NULLIF(fp.customer_name, ''), 'Customer') AS nama_entitas,
                        COALESCE(pf.status_kasir, 'valid') AS status_transaksi,
                        (COALESCE(pf.jumlah_pembayaran, 0) + COALESCE(pf.jumlah_diskon, 0)) AS total_nominal,
                        CONCAT('Pembayaran Faktur ', COALESCE(pf.no_faktur, ''), ' via ', COALESCE(pf.metode_pembayaran, 'Kas/Bank')) AS keterangan,
                        j.id_jurnal,
                        j.nomor_jurnal,
                        j.status AS status_jurnal,
                        j.total_debit AS journal_debit,
                        j.total_kredit AS journal_kredit,
                        pf.create_at AS created_at
                    FROM tbkeu_pembayaran_faktur pf
                    LEFT JOIN tbso_faktur_penjualan fp ON (fp.id_faktur = pf.id_faktur OR fp.no_faktur = pf.no_faktur)
                    LEFT JOIN tbkeu_jurnal j ON (
                        j.source_module = 'KEUANGAN' AND j.source_type = 'PEMBAYARAN_FAKTUR' AND j.source_id = CAST(pf.id_pembayaran AS CHAR)
                    )
                    WHERE pf.tanggal_pembayaran >= ? AND pf.tanggal_pembayaran <= ?
                ";
                $queryParams[] = $date_from;
                $queryParams[] = $date_to;
            }
        }

        // 4. PEMBAYARAN SUPPLIER (PELUNASAN HUTANG SUPPLIER)
        if ($category === 'all' || $category === 'pembayaran_supplier' || $category === 'hutang') {
            if ($this->db->table_exists('tbkeu_pembayaran')) {
                $subqueries[] = "
                    SELECT 
                        'pembayaran_supplier' AS trans_category,
                        'Pembayaran Supplier' AS trans_category_label,
                        ps.id_pembayaran AS id_transaksi,
                        COALESCE(NULLIF(ps.nomor_pembayaran, ''), CONCAT('PBY-', ps.id_pembayaran)) AS no_dokumen,
                        COALESCE(ps.source_no, '') AS no_referensi,
                        ps.tanggal_pembayaran AS tanggal_transaksi,
                        COALESCE(s.nama_suplier, 'Supplier') AS nama_entitas,
                        COALESCE(ps.status, 'POSTED') AS status_transaksi,
                        COALESCE(ps.amount, 0) AS total_nominal,
                        COALESCE(NULLIF(ps.keterangan, ''), CONCAT('Pembayaran Hutang Supplier #', ps.id_pembayaran)) AS keterangan,
                        j.id_jurnal,
                        j.nomor_jurnal,
                        j.status AS status_jurnal,
                        j.total_debit AS journal_debit,
                        j.total_kredit AS journal_kredit,
                        ps.created_at AS created_at
                    FROM tbkeu_pembayaran ps
                    LEFT JOIN tbpo_suplier s ON s.id_suplier = ps.id_supplier
                    LEFT JOIN tbkeu_jurnal j ON (j.id_jurnal = ps.id_jurnal OR (j.source_module = 'KEUANGAN' AND j.source_id = CAST(ps.id_pembayaran AS CHAR)))
                    WHERE ps.payment_type = 'SUPPLIER_PAYMENT' 
                      AND ps.tanggal_pembayaran >= ? AND ps.tanggal_pembayaran <= ?
                ";
                $queryParams[] = $date_from;
                $queryParams[] = $date_to;
            }
        }

        // 5. RETUR PENJUALAN
        if ($category === 'all' || $category === 'retur_penjualan') {
            if ($this->db->table_exists('tbrp_retur_penjualan_header')) {
                $subqueries[] = "
                    SELECT 
                        'retur_penjualan' AS trans_category,
                        'Retur Penjualan' AS trans_category_label,
                        rp.id_retur AS id_transaksi,
                        rp.no_retur AS no_dokumen,
                        COALESCE(rp.no_spr, '') AS no_referensi,
                        rp.tanggal_retur AS tanggal_transaksi,
                        COALESCE(NULLIF(rp.nama_customer, ''), 'Customer') AS nama_entitas,
                        COALESCE(rp.status_retur, 'selesai') AS status_transaksi,
                        COALESCE((SELECT SUM(rpd.qty_retur * rpd.harga_satuan) FROM tbrp_retur_penjualan_detail rpd WHERE rpd.id_retur = rp.id_retur), 0) AS total_nominal,
                        CONCAT('Retur Penjualan: ', rp.no_retur, ' (SPR: ', COALESCE(rp.no_spr, ''), ')') AS keterangan,
                        j.id_jurnal,
                        j.nomor_jurnal,
                        j.status AS status_jurnal,
                        j.total_debit AS journal_debit,
                        j.total_kredit AS journal_kredit,
                        rp.create_at_retur AS created_at
                    FROM tbrp_retur_penjualan_header rp
                    LEFT JOIN tbkeu_jurnal j ON (
                        j.source_module = 'SALES' AND j.source_type = 'RETUR_PENJUALAN' AND (j.source_no = rp.no_retur OR j.source_id = CAST(rp.id_retur AS CHAR))
                    )
                    WHERE rp.tanggal_retur >= ? AND rp.tanggal_retur <= ?
                ";
                $queryParams[] = $date_from;
                $queryParams[] = $date_to;
            }
        }

        // 6. RETUR PEMBELIAN
        if ($category === 'all' || $category === 'retur_pembelian') {
            if ($this->db->table_exists('tb_retur_pembelian')) {
                $subqueries[] = "
                    SELECT 
                        'retur_pembelian' AS trans_category,
                        'Retur Pembelian' AS trans_category_label,
                        rb.id_retur_pembelian AS id_transaksi,
                        rb.no_retur_pembelian AS no_dokumen,
                        COALESCE(l.nomor_lpb, rb.no_po, '') AS no_referensi,
                        rb.tanggal_retur AS tanggal_transaksi,
                        COALESCE(s.nama_suplier, 'Supplier') AS nama_entitas,
                        COALESCE(rb.status, 'POSTED') AS status_transaksi,
                        COALESCE(rb.grand_total, (SELECT SUM(rbd.total) FROM tb_retur_pembelian_detail rbd WHERE rbd.id_retur_pembelian = rb.id_retur_pembelian), 0) AS total_nominal,
                        COALESCE(NULLIF(rb.alasan_retur, ''), CONCAT('Retur Pembelian: ', rb.no_retur_pembelian)) AS keterangan,
                        j.id_jurnal,
                        j.nomor_jurnal,
                        j.status AS status_jurnal,
                        j.total_debit AS journal_debit,
                        j.total_kredit AS journal_kredit,
                        rb.created_at AS created_at
                    FROM tb_retur_pembelian rb
                    LEFT JOIN tb_lpb l ON l.id_lpb = rb.id_lpb
                    LEFT JOIN tbpo_suplier s ON s.kd_suplier = rb.kd_supplier
                    LEFT JOIN tbkeu_jurnal j ON (
                        j.id_jurnal = rb.id_jurnal 
                        OR (j.source_module = 'LOGISTIK' AND j.source_type = 'RETUR_PEMBELIAN' AND j.source_id = CAST(rb.id_retur_pembelian AS CHAR))
                    )
                    WHERE rb.tanggal_retur >= ? AND rb.tanggal_retur <= ?
                ";
                $queryParams[] = $date_from;
                $queryParams[] = $date_to;
            }
        }

        if (empty($subqueries)) {
            return [
                'data' => [],
                'total' => 0,
                'summary' => [
                    'total_count' => 0,
                    'total_nominal' => 0,
                    'total_posted' => 0,
                    'total_unposted' => 0,
                ]
            ];
        }

        $unionSql = implode("\n UNION ALL \n", $subqueries);

        // Filter Outer Wrapping Query
        $where = [];
        $outerParams = [];

        if ($status !== '' && $status !== 'all') {
            if ($status === 'POSTED') {
                $where[] = "(u.status_jurnal = 'POSTED' OR u.status_transaksi = 'POSTED' OR u.status_transaksi = 'selesai' OR u.status_transaksi = 'done')";
            } elseif ($status === 'UNPOSTED') {
                $where[] = "(u.id_jurnal IS NULL OR u.status_jurnal != 'POSTED')";
            } elseif ($status === 'CANCELLED') {
                $where[] = "(u.status_transaksi = 'cancelled' OR u.status_transaksi = 'ditolak' OR u.status_jurnal = 'VOID')";
            } else {
                $where[] = "u.status_transaksi = ?";
                $outerParams[] = $status;
            }
        }

        if ($search !== '') {
            $where[] = "(u.no_dokumen LIKE ? OR u.no_referensi LIKE ? OR u.nama_entitas LIKE ? OR u.keterangan LIKE ? OR u.nomor_jurnal LIKE ?)";
            $like = '%' . $search . '%';
            $outerParams[] = $like;
            $outerParams[] = $like;
            $outerParams[] = $like;
            $outerParams[] = $like;
            $outerParams[] = $like;
        }

        $whereClause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

        // Total Count & Summary
        $summarySql = "
            SELECT 
                COUNT(*) AS total_count,
                COALESCE(SUM(u.total_nominal), 0) AS total_nominal,
                COALESCE(SUM(CASE WHEN u.id_jurnal IS NOT NULL AND u.status_jurnal = 'POSTED' THEN 1 ELSE 0 END), 0) AS total_posted,
                COALESCE(SUM(CASE WHEN u.id_jurnal IS NULL OR u.status_jurnal != 'POSTED' THEN 1 ELSE 0 END), 0) AS total_unposted
            FROM ({$unionSql}) u
            {$whereClause}
        ";

        $summaryParams = array_merge($queryParams, $outerParams);
        $summary = $this->db->query($summarySql, $summaryParams)->row_array();

        // Paginated Query with embedded LIMIT & OFFSET
        $dataSql = "
            SELECT u.*
            FROM ({$unionSql}) u
            {$whereClause}
            ORDER BY u.tanggal_transaksi DESC, u.created_at DESC, u.id_transaksi DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $rows = $this->db->query($dataSql, $summaryParams)->result_array();

        return [
            'data' => $rows,
            'total' => (int)($summary['total_count'] ?? 0),
            'summary' => [
                'total_count'    => (int)($summary['total_count'] ?? 0),
                'total_nominal'  => (float)($summary['total_nominal'] ?? 0),
                'total_posted'   => (int)($summary['total_posted'] ?? 0),
                'total_unposted' => (int)($summary['total_unposted'] ?? 0),
            ]
        ];
    }

    // =========================================================================
    // 2. DETAIL TRANSAKSI & JURNAL BREAKDOWN
    // =========================================================================

    /**
     * Mengambil detail lengkap suatu transaksi beserta rincian item & jurnal akuntansinya
     */
    public function get_transaction_detail($category, $idTransaksi)
    {
        $category = strtolower(trim((string)$category));
        $result = [
            'category' => $category,
            'header' => null,
            'items' => [],
            'journal' => null,
            'journal_lines' => [],
        ];

        switch ($category) {
            case 'penjualan':
            case 'faktur_penjualan':
                $header = $this->db->select('f.*, c.nama_customer, c.alamat_kios AS alamat')
                    ->from('tbso_faktur_penjualan f')
                    ->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left')
                    ->where('f.id_faktur', (int)$idTransaksi)
                    ->or_where('f.no_faktur', $idTransaksi)
                    ->get()->row_array();
                if ($header) {
                    $header['no_dokumen'] = $header['no_faktur'];
                    $header['no_referensi'] = $header['no_so'] ?? '';
                    $header['tanggal_transaksi'] = $header['tanggal_faktur'];
                    $header['nama_entitas'] = $header['nama_customer'] ?: ($header['customer_name'] ?: 'Customer');
                    $header['status_transaksi'] = $header['status'] ?: 'POSTED';
                    $header['keterangan'] = $header['catatan'] ?: ('Faktur Penjualan: ' . $header['no_faktur']);

                    $result['header'] = $header;
                    $result['items'] = $this->db->select('d.*, d.hrg_satuan AS harga_satuan, d.disc AS diskon_persen, d.subtotal_after_disc AS subtotal')
                        ->where('d.id_faktur', (int)$header['id_faktur'])
                        ->get('tbso_faktur_detail d')
                        ->result_array();

                    // Journal
                    $result['journal'] = $this->db->where('source_module', 'SALES')
                        ->group_start()
                        ->where('source_id', $header['no_faktur'])
                        ->or_where('source_no', $header['no_faktur'])
                        ->or_where('idempotency_key', 'SALES_INVOICE-FAKTUR-' . $header['no_faktur'])
                        ->group_end()
                        ->order_by('id_jurnal', 'DESC')
                        ->get('tbkeu_jurnal')->row_array();
                }
                break;

            case 'pembelian':
            case 'lpb':
                $header = $this->db->select('l.*, s.nama_suplier, s.alamat_suplier AS alamat, p.tgl_transaksi AS tgl_po')
                    ->from('tb_lpb l')
                    ->join('tbpo_po p', 'p.kd_po = l.kd_po AND p.no_po = l.no_po', 'left')
                    ->join('tbpo_suplier s', 's.kd_suplier = p.kd_suplier', 'left')
                    ->where('l.id_lpb', (int)$idTransaksi)
                    ->get()->row_array();
                if ($header) {
                    $header['no_dokumen'] = !empty($header['nomor_lpb']) ? $header['nomor_lpb'] : (!empty($header['no_po']) ? $header['no_po'] : ('LPB #' . $header['id_lpb']));
                    $header['no_referensi'] = !empty($header['no_po']) ? $header['no_po'] : ($header['nosj'] ?? '');
                    $header['tanggal_transaksi'] = !empty($header['tgl_sj']) ? $header['tgl_sj'] : date('Y-m-d', strtotime($header['input_at']));
                    $header['nama_entitas'] = $header['nama_suplier'] ?: 'Supplier';
                    $header['status_transaksi'] = ($header['status_lpb'] == 1) ? 'selesai' : 'draft';
                    $header['keterangan'] = $header['keterangan'] ?: ('Penerimaan Barang PO ' . ($header['no_po'] ?? ''));

                    $result['header'] = $header;
                    $result['items'] = $this->db->select('d.*, COALESCE(mb.nm_barang, d.kd_barang) AS nama_barang, mb.satuan AS satuan_master, d.qty_diterima AS qty, d.total_harga AS subtotal')
                        ->from('tb_lpb_detail d')
                        ->join('tb_master_barang mb', 'mb.kode_barang = d.kd_barang OR mb.kd_system = d.kd_barang', 'left')
                        ->where('d.id_lpb', (int)$header['id_lpb'])
                        ->get()->result_array();

                    $result['journal'] = $this->db->where('source_module', 'LOGISTIK')
                        ->where('source_type', 'LPB_FINAL')
                        ->where('source_id', (string)$header['id_lpb'])
                        ->order_by('id_jurnal', 'DESC')
                        ->get('tbkeu_jurnal')->row_array();
                }
                break;

            case 'pembayaran_customer':
                $header = $this->db->select('pf.*, f.customer_name, f.no_so, f.tanggal_faktur')
                    ->from('tbkeu_pembayaran_faktur pf')
                    ->join('tbso_faktur_penjualan f', 'f.id_faktur = pf.id_faktur OR f.no_faktur = pf.no_faktur', 'left')
                    ->where('pf.id_pembayaran', (int)$idTransaksi)
                    ->get()->row_array();
                if ($header) {
                    $header['no_dokumen'] = 'BYR-' . $header['id_pembayaran'];
                    $header['no_referensi'] = $header['no_faktur'] ?? '';
                    $header['tanggal_transaksi'] = $header['tanggal_pembayaran'];
                    $header['nama_entitas'] = $header['customer_name'] ?: 'Customer';
                    $header['total_nominal'] = (float)$header['jumlah_pembayaran'] + (float)$header['jumlah_diskon'];
                    $header['status_transaksi'] = $header['status_kasir'] ?: 'POSTED';
                    $header['keterangan'] = 'Pembayaran Faktur ' . ($header['no_faktur'] ?? '') . ' via ' . ($header['metode_pembayaran'] ?? 'Kas/Bank');

                    $result['header'] = $header;
                    $result['items'] = [$header];
                    $result['journal'] = $this->db->where('source_module', 'KEUANGAN')
                        ->where('source_type', 'PEMBAYARAN_FAKTUR')
                        ->where('source_id', (string)$header['id_pembayaran'])
                        ->order_by('id_jurnal', 'DESC')
                        ->get('tbkeu_jurnal')->row_array();
                }
                break;

            case 'pembayaran_supplier':
                $header = $this->db->select('ps.*, s.nama_suplier')
                    ->from('tbkeu_pembayaran ps')
                    ->join('tbpo_suplier s', 's.id_suplier = ps.id_supplier', 'left')
                    ->where('ps.id_pembayaran', (int)$idTransaksi)
                    ->get()->row_array();
                if ($header) {
                    $header['no_dokumen'] = !empty($header['nomor_pembayaran']) ? $header['nomor_pembayaran'] : ('PBY-' . $header['id_pembayaran']);
                    $header['no_referensi'] = $header['source_no'] ?? '';
                    $header['tanggal_transaksi'] = $header['tanggal_pembayaran'];
                    $header['nama_entitas'] = $header['nama_suplier'] ?: 'Supplier';
                    $header['total_nominal'] = (float)$header['amount'];
                    $header['status_transaksi'] = $header['status'] ?: 'POSTED';
                    $header['keterangan'] = $header['keterangan'] ?: ('Pembayaran Hutang Supplier #' . $header['id_pembayaran']);

                    $result['header'] = $header;
                    if ($this->db->table_exists('tbkeu_pembayaran_alokasi')) {
                        $result['items'] = $this->db->where('id_pembayaran', (int)$header['id_pembayaran'])->get('tbkeu_pembayaran_alokasi')->result_array();
                    } else {
                        $result['items'] = [$header];
                    }
                    if (!empty($header['id_jurnal'])) {
                        $result['journal'] = $this->db->where('id_jurnal', (int)$header['id_jurnal'])->get('tbkeu_jurnal')->row_array();
                    }
                }
                break;

            case 'retur_penjualan':
                $header = $this->db->select('rp.*')
                    ->from('tbrp_retur_penjualan_header rp')
                    ->where('rp.id_retur', (int)$idTransaksi)
                    ->or_where('rp.no_retur', $idTransaksi)
                    ->get()->row_array();
                if ($header) {
                    $header['no_dokumen'] = $header['no_retur'];
                    $header['no_referensi'] = $header['no_spr'] ?? '';
                    $header['tanggal_transaksi'] = $header['tanggal_retur'];
                    $header['nama_entitas'] = $header['nama_customer'] ?: 'Customer';
                    $header['status_transaksi'] = $header['status_retur'] ?: 'selesai';
                    $header['keterangan'] = 'Retur Penjualan: ' . $header['no_retur'] . ' (SPR: ' . ($header['no_spr'] ?? '') . ')';

                    $result['header'] = $header;
                    $result['items'] = $this->db->select('d.*, d.qty_retur AS qty, (d.qty_retur * d.harga_satuan) AS subtotal, COALESCE(mb.kode_barang, mb.kd_system, "") AS kd_barang_master')
                        ->from('tbrp_retur_penjualan_detail d')
                        ->join('tb_master_barang mb', 'mb.nm_barang = d.nama_barang', 'left')
                        ->where('d.id_retur', (int)$header['id_retur'])
                        ->get()->result_array();

                    $result['journal'] = $this->db->where('source_module', 'SALES')
                        ->where('source_type', 'RETUR_PENJUALAN')
                        ->group_start()
                        ->where('source_id', (string)$header['id_retur'])
                        ->or_where('source_no', $header['no_retur'])
                        ->group_end()
                        ->order_by('id_jurnal', 'DESC')
                        ->get('tbkeu_jurnal')->row_array();
                }
                break;

            case 'retur_pembelian':
                $header = $this->db->select('rb.*, s.nama_suplier, l.nomor_lpb')
                    ->from('tb_retur_pembelian rb')
                    ->join('tbpo_suplier s', 's.kd_suplier = rb.kd_supplier', 'left')
                    ->join('tb_lpb l', 'l.id_lpb = rb.id_lpb', 'left')
                    ->where('rb.id_retur_pembelian', (int)$idTransaksi)
                    ->get()->row_array();
                if ($header) {
                    $header['no_dokumen'] = $header['no_retur_pembelian'];
                    $header['no_referensi'] = !empty($header['nomor_lpb']) ? $header['nomor_lpb'] : ($header['no_po'] ?? '');
                    $header['tanggal_transaksi'] = $header['tanggal_retur'];
                    $header['nama_entitas'] = $header['nama_suplier'] ?: 'Supplier';
                    $header['status_transaksi'] = $header['status'] ?: 'POSTED';
                    $header['keterangan'] = $header['alasan_retur'] ?: ('Retur Pembelian: ' . $header['no_retur_pembelian']);

                    $result['header'] = $header;
                    $result['items'] = $this->db->select('d.*, COALESCE(mb.nm_barang, d.kd_barang) AS nama_barang, d.qty_retur AS qty, d.total AS subtotal')
                        ->from('tb_retur_pembelian_detail d')
                        ->join('tb_master_barang mb', 'mb.kode_barang = d.kd_barang OR mb.kd_system = d.kd_barang', 'left')
                        ->where('d.id_retur_pembelian', (int)$header['id_retur_pembelian'])
                        ->get()->result_array();

                    if (!empty($header['id_jurnal'])) {
                        $result['journal'] = $this->db->where('id_jurnal', (int)$header['id_jurnal'])->get('tbkeu_jurnal')->row_array();
                    } else {
                        $result['journal'] = $this->db->where('source_module', 'LOGISTIK')
                            ->where('source_type', 'RETUR_PEMBELIAN')
                            ->where('source_id', (string)$header['id_retur_pembelian'])
                            ->order_by('id_jurnal', 'DESC')
                            ->get('tbkeu_jurnal')->row_array();
                    }
                }
                break;
        }

        // Ambil journal detail lines jika ada ID Jurnal
        if (!empty($result['journal']['id_jurnal'])) {
            $idJurnal = (int)$result['journal']['id_jurnal'];
            $result['journal_lines'] = $this->db->select('d.*, a.kode_akun, a.nama_akun')
                ->from('tbkeu_jurnal_detail d')
                ->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left')
                ->where('d.id_jurnal', $idJurnal)
                ->order_by('d.nomor_baris', 'ASC')
                ->get()->result_array();
        }

        return $result;
    }

    /**
     * Mengambil daftar riwayat Activity Log (Audit Trail)
     */
    public function get_activity_logs($filters = [], $limit = 50, $offset = 0)
    {
        if (!$this->db->table_exists('tbso_faktur_log')) {
            return ['data' => [], 'total' => 0];
        }

        $date_from = !empty($filters['date_from']) ? $filters['date_from'] : '';
        $date_to   = !empty($filters['date_to']) ? $filters['date_to'] : '';
        $search    = trim((string)($filters['search'] ?? ''));
        $limit     = max(1, (int)$limit);
        $offset    = max(0, (int)$offset);

        $this->db->select('log.*, fp.customer_name as fp_customer_name, c.nama_customer as master_customer_name');
        $this->db->from('tbso_faktur_log log');
        $this->db->join('tbso_faktur_penjualan fp', 'fp.id_faktur = log.id_faktur OR fp.no_faktur = log.no_faktur', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = fp.kd_customer', 'left');

        if (!empty($date_from)) {
            $this->db->where('DATE(log.created_at) >=', $date_from);
        }
        if (!empty($date_to)) {
            $this->db->where('DATE(log.created_at) <=', $date_to);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('log.no_faktur', $search);
            $this->db->or_like('log.no_so', $search);
            $this->db->or_like('log.dilakukan_oleh', $search);
            $this->db->or_like('log.keterangan', $search);
            $this->db->or_like('fp.customer_name', $search);
            $this->db->or_like('c.nama_customer', $search);
            $this->db->group_end();
        }

        $cloneDb = clone $this->db;
        $total = $cloneDb->count_all_results('', false);

        $this->db->order_by('log.id', 'DESC');
        $this->db->limit($limit, $offset);
        $rows = $this->db->get()->result_array();

        return [
            'data' => $rows,
            'total' => (int)$total
        ];
    }

    // =========================================================================
    // 3. EDIT TRANSAKSI & SINKRONISASI JURNAL
    // =========================================================================

    /**
     * Memperbarui transaksi dan otomatis sinkronisasi perhitungan jurnal akuntansi
     */
    public function update_transaction_with_journal_sync($category, $idTransaksi, $postData, $userId = null)
    {
        $category = strtolower(trim((string)$category));
        $this->db->trans_begin();

        try {
            switch ($category) {
                case 'penjualan':
                case 'faktur_penjualan':
                    $res = $this->_update_faktur_penjualan((int)$idTransaksi, $postData, $userId);
                    break;

                case 'pembelian':
                case 'lpb':
                    $res = $this->_update_lpb((int)$idTransaksi, $postData, $userId);
                    break;

                case 'pembayaran_customer':
                    $res = $this->_update_pembayaran_customer((int)$idTransaksi, $postData, $userId);
                    break;

                case 'pembayaran_supplier':
                    $res = $this->_update_pembayaran_supplier((int)$idTransaksi, $postData, $userId);
                    break;

                case 'retur_penjualan':
                    $res = $this->_update_retur_penjualan((int)$idTransaksi, $postData, $userId);
                    break;

                case 'retur_pembelian':
                    $res = $this->_update_retur_pembelian((int)$idTransaksi, $postData, $userId);
                    break;

                default:
                    throw new Exception('Kategori transaksi tidak dikenali: ' . $category);
            }

            if (!$res['success']) {
                $this->db->trans_rollback();
                return $res;
            }

            $this->db->trans_commit();
            return ['success' => true, 'message' => $res['message'] ?? 'Transaksi dan jurnal berhasil diperbarui & disinkronkan.'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // 4. REPOST TRANSAKSI & JURNAL
    // =========================================================================

    /**
     * Memposting ulang transaksi dan meregenerasi jurnal akuntansi yang bersih
     */
    public function repost_transaction_with_journal_sync($category, $idTransaksi, $userId = null)
    {
        $category = strtolower(trim((string)$category));
        $this->db->trans_begin();

        try {
            switch ($category) {
                case 'penjualan':
                case 'faktur_penjualan':
                    $faktur = $this->db->where('id_faktur', (int)$idTransaksi)->or_where('no_faktur', $idTransaksi)->get('tbso_faktur_penjualan')->row_array();
                    if (!$faktur) throw new Exception('Faktur penjualan tidak ditemukan.');

                    // Hapus jurnal lama
                    $this->_delete_old_journals('SALES', 'FAKTUR_PENJUALAN', $faktur['no_faktur'], 'SALES_INVOICE-FAKTUR-' . $faktur['no_faktur']);

                    // Repost jurnal via Accounting_source_service
                    $res = $this->accounting_source_service->post_sales_invoice($faktur['no_faktur'], '', $userId, true);
                    if (!$res['success']) throw new Exception('Gagal posting jurnal faktur: ' . ($res['message'] ?? ''));

                    // Pastikan status faktur posted
                    $this->db->where('id_faktur', (int)$faktur['id_faktur'])->update('tbso_faktur_penjualan', [
                        'status' => 'posted',
                        'update_by' => $userId,
                        'update_at' => date('Y-m-d H:i:s'),
                    ]);

                    // Catat ke tbso_faktur_log
                    if ($this->db->table_exists('tbso_faktur_log')) {
                        $user_nama = $this->session->userdata('nama') ?: ($this->session->userdata('username') ?: 'Admin');
                        $user_username = $this->session->userdata('username') ?: 'admin';
                        $this->db->insert('tbso_faktur_log', [
                            'no_so'          => $faktur['no_so'],
                            'no_faktur'      => $faktur['no_faktur'],
                            'id_faktur'      => (int)$faktur['id_faktur'],
                            'aksi'           => 'REPOST_FAKTUR',
                            'keterangan'     => 'Posting Ulang (Repost) Faktur Penjualan & Regenerasi Jurnal Akuntansi',
                            'dilakukan_oleh' => $user_nama . ' (' . $user_username . ')',
                            'ip_address'     => $this->input->ip_address(),
                            'created_at'     => date('Y-m-d H:i:s')
                        ]);
                    }
                    break;

                case 'pembelian':
                case 'lpb':
                    $lpb = $this->db->where('id_lpb', (int)$idTransaksi)->get('tb_lpb')->row_array();
                    if (!$lpb) throw new Exception('LPB pembelian tidak ditemukan.');

                    $this->_delete_old_journals('LOGISTIK', 'LPB_FINAL', (string)$lpb['id_lpb'], 'GOODS_RECEIPT-LPB-' . $lpb['id_lpb']);
                    $res = $this->accounting_source_service->post_goods_receipt((int)$lpb['id_lpb'], $userId);
                    if (!$res['success']) throw new Exception('Gagal posting jurnal LPB: ' . ($res['message'] ?? ''));
                    break;

                case 'pembayaran_customer':
                    $pf = $this->db->where('id_pembayaran', (int)$idTransaksi)->get('tbkeu_pembayaran_faktur')->row_array();
                    if (!$pf) throw new Exception('Pembayaran customer tidak ditemukan.');

                    $this->_delete_old_journals('KEUANGAN', 'PEMBAYARAN_FAKTUR', (string)$pf['id_pembayaran']);
                    $this->M_Journal->post_jurnal_pembayaran((int)$pf['id_pembayaran'], $pf);
                    break;

                case 'pembayaran_supplier':
                    $ps = $this->db->where('id_pembayaran', (int)$idTransaksi)->get('tbkeu_pembayaran')->row_array();
                    if (!$ps) throw new Exception('Pembayaran supplier tidak ditemukan.');

                    if (!empty($ps['id_jurnal'])) {
                        $this->_delete_journal_by_id((int)$ps['id_jurnal']);
                    }
                    $this->_repost_pembayaran_supplier_journal($ps, $userId);
                    break;

                case 'retur_penjualan':
                    $rp = $this->db->where('id_retur', (int)$idTransaksi)->or_where('no_retur', $idTransaksi)->get('tbrp_retur_penjualan_header')->row_array();
                    if (!$rp) throw new Exception('Retur penjualan tidak ditemukan.');

                    $this->_delete_old_journals('SALES', 'RETUR_PENJUALAN', (string)$rp['id_retur'], '', $rp['no_retur']);
                    $this->M_Journal->post_jurnal_retur_penjualan((int)$rp['id_retur']);
                    break;

                case 'retur_pembelian':
                    $rb = $this->db->where('id_retur_pembelian', (int)$idTransaksi)->get('tb_retur_pembelian')->row_array();
                    if (!$rb) throw new Exception('Retur pembelian tidak ditemukan.');

                    if (!empty($rb['id_jurnal'])) {
                        $this->_delete_journal_by_id((int)$rb['id_jurnal']);
                    }
                    $this->_delete_old_journals('LOGISTIK', 'RETUR_PEMBELIAN', (string)$rb['id_retur_pembelian']);
                    
                    $this->load->model('M_ReturPembelian');
                    $repostRes = $this->M_ReturPembelian->post_retur_pembelian((int)$rb['id_retur_pembelian'], $userId);
                    if (!$repostRes['success']) throw new Exception('Gagal posting jurnal retur pembelian: ' . ($repostRes['message'] ?? ''));
                    break;

                default:
                    throw new Exception('Kategori transaksi tidak dikenali: ' . $category);
            }

            $this->db->trans_commit();
            return ['success' => true, 'message' => 'Transaksi berhasil di-repost dan jurnal akuntansi kembali aktif.'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // 5. DELETE TRANSAKSI & PEMBERSIHAN JURNAL
    // =========================================================================

    /**
     * Menghapus transaksi dan membersihkan seluruh jurnal akuntansi terkait
     */
    public function delete_transaction_with_journal_sync($category, $idTransaksi, $userId = null, $reason = '')
    {
        $category = strtolower(trim((string)$category));
        $this->db->trans_begin();

        try {
            switch ($category) {
                case 'penjualan':
                case 'faktur_penjualan':
                    $faktur = $this->db->where('id_faktur', (int)$idTransaksi)->or_where('no_faktur', $idTransaksi)->get('tbso_faktur_penjualan')->row_array();
                    if (!$faktur) throw new Exception('Faktur penjualan tidak ditemukan.');

                    // Hapus jurnal terkait
                    $this->_delete_old_journals('SALES', 'FAKTUR_PENJUALAN', $faktur['no_faktur'], 'SALES_INVOICE-FAKTUR-' . $faktur['no_faktur']);
                    if ($this->db->table_exists('tbso_faktur_jurnal')) {
                        $this->db->delete('tbso_faktur_jurnal', ['id_faktur' => (int)$faktur['id_faktur']]);
                    }

                    // Catat log hapus ke tbso_faktur_log
                    if ($this->db->table_exists('tbso_faktur_log')) {
                        $user_nama = $this->session->userdata('nama') ?: ($this->session->userdata('username') ?: 'Admin');
                        $user_username = $this->session->userdata('username') ?: 'admin';
                        $this->db->insert('tbso_faktur_log', [
                            'no_so'          => $faktur['no_so'],
                            'no_faktur'      => $faktur['no_faktur'],
                            'id_faktur'      => (int)$faktur['id_faktur'],
                            'aksi'           => 'DELETE_FAKTUR',
                            'keterangan'     => 'Hapus Faktur Penjualan & Pembersihan Jurnal via Modul Transaksi. Alasan: ' . ($reason ?: '-'),
                            'dilakukan_oleh' => $user_nama . ' (' . $user_username . ')',
                            'ip_address'     => $this->input->ip_address(),
                            'created_at'     => date('Y-m-d H:i:s')
                        ]);
                    }

                    // Hapus detail & header faktur
                    $this->db->delete('tbso_faktur_detail', ['id_faktur' => (int)$faktur['id_faktur']]);
                    $this->db->delete('tbso_faktur_penjualan', ['id_faktur' => (int)$faktur['id_faktur']]);
                    break;

                case 'pembelian':
                case 'lpb':
                    $lpb = $this->db->where('id_lpb', (int)$idTransaksi)->get('tb_lpb')->row_array();
                    if (!$lpb) throw new Exception('LPB pembelian tidak ditemukan.');

                    $this->_delete_old_journals('LOGISTIK', 'LPB_FINAL', (string)$lpb['id_lpb'], 'GOODS_RECEIPT-LPB-' . $lpb['id_lpb']);
                    $this->db->delete('tb_lpb_detail', ['id_lpb' => (int)$lpb['id_lpb']]);
                    $this->db->delete('tb_lpb', ['id_lpb' => (int)$lpb['id_lpb']]);
                    break;

                case 'pembayaran_customer':
                    $pf = $this->db->where('id_pembayaran', (int)$idTransaksi)->get('tbkeu_pembayaran_faktur')->row_array();
                    if (!$pf) throw new Exception('Pembayaran customer tidak ditemukan.');

                    $this->_delete_old_journals('KEUANGAN', 'PEMBAYARAN_FAKTUR', (string)$pf['id_pembayaran']);
                    $this->db->delete('tbkeu_pembayaran_faktur', ['id_pembayaran' => (int)$pf['id_pembayaran']]);
                    break;

                case 'pembayaran_supplier':
                    $ps = $this->db->where('id_pembayaran', (int)$idTransaksi)->get('tbkeu_pembayaran')->row_array();
                    if (!$ps) throw new Exception('Pembayaran supplier tidak ditemukan.');

                    if (!empty($ps['id_jurnal'])) {
                        $this->_delete_journal_by_id((int)$ps['id_jurnal']);
                    }
                    $this->_delete_old_journals('KEUANGAN', 'SUPPLIER_PAYMENT', (string)$ps['id_pembayaran']);
                    if ($this->db->table_exists('tbkeu_pembayaran_alokasi')) {
                        $this->db->delete('tbkeu_pembayaran_alokasi', ['id_pembayaran' => (int)$ps['id_pembayaran']]);
                    }
                    $this->db->delete('tbkeu_pembayaran', ['id_pembayaran' => (int)$ps['id_pembayaran']]);
                    break;

                case 'retur_penjualan':
                    $rp = $this->db->where('id_retur', (int)$idTransaksi)->or_where('no_retur', $idTransaksi)->get('tbrp_retur_penjualan_header')->row_array();
                    if (!$rp) throw new Exception('Retur penjualan tidak ditemukan.');

                    $this->_delete_old_journals('SALES', 'RETUR_PENJUALAN', (string)$rp['id_retur'], '', $rp['no_retur']);
                    $this->db->delete('tbrp_retur_penjualan_detail', ['id_retur' => (int)$rp['id_retur']]);
                    $this->db->delete('tbrp_retur_penjualan_header', ['id_retur' => (int)$rp['id_retur']]);
                    break;

                case 'retur_pembelian':
                    $rb = $this->db->where('id_retur_pembelian', (int)$idTransaksi)->get('tb_retur_pembelian')->row_array();
                    if (!$rb) throw new Exception('Retur pembelian tidak ditemukan.');

                    if (!empty($rb['id_jurnal'])) {
                        $this->_delete_journal_by_id((int)$rb['id_jurnal']);
                    }
                    $this->_delete_old_journals('LOGISTIK', 'RETUR_PEMBELIAN', (string)$rb['id_retur_pembelian']);
                    $this->db->delete('tb_retur_pembelian_detail', ['id_retur_pembelian' => (int)$rb['id_retur_pembelian']]);
                    $this->db->delete('tb_retur_pembelian', ['id_retur_pembelian' => (int)$rb['id_retur_pembelian']]);
                    break;

                default:
                    throw new Exception('Kategori transaksi tidak dikenali: ' . $category);
            }

            $this->db->trans_commit();
            return ['success' => true, 'message' => 'Transaksi dan seluruh jurnal terkait berhasil dihapus bersih.'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // PRIVATE INTERNAL HANDLERS
    // =========================================================================

    private function _update_faktur_penjualan($idFaktur, $postData, $userId)
    {
        $faktur = $this->db->where('id_faktur', $idFaktur)->get('tbso_faktur_penjualan')->row_array();
        if (!$faktur) throw new Exception('Faktur penjualan tidak ditemukan.');

        // Update items
        if (!empty($postData['items']) && is_array($postData['items'])) {
            $total_tonase = 0;
            $total_kubikasi = 0;

            foreach ($postData['items'] as $item) {
                $idDetail = (int)($item['id_faktur_detail'] ?? $item['id'] ?? 0);
                $qty = (float)($item['qty'] ?? 0);
                $harga = (float)($item['hrg_satuan'] ?? $item['harga_satuan'] ?? 0);
                $diskonPersen = (float)($item['disc'] ?? $item['diskon_persen'] ?? 0);
                $diskonRp = (float)($item['diskon_rp'] ?? 0);

                // Cek apakah ada input langsung subtotal / total_harga
                if (isset($item['total_harga']) && $item['total_harga'] !== '' && is_numeric(str_replace([',', ' '], '', $item['total_harga']))) {
                    $subtotalAfterDisc = (float)str_replace([',', ' '], '', $item['total_harga']);
                    $subtotal = ($diskonRp > 0) ? ($subtotalAfterDisc + $diskonRp) : (($diskonPersen > 0 && $diskonPersen < 100) ? round($subtotalAfterDisc / (1 - ($diskonPersen / 100)), 2) : $subtotalAfterDisc);
                    if ($qty > 0 && $harga <= 0) {
                        $harga = round($subtotal / $qty, 2);
                    }
                } else {
                    $subtotal = round($qty * $harga, 2);
                    if ($diskonPersen > 0) {
                        $diskonRp = round($subtotal * ($diskonPersen / 100), 2);
                    }
                    $subtotalAfterDisc = max(0, $subtotal - $diskonRp);
                }

                $currentDetail = $this->db->where('id', $idDetail)->get('tbso_faktur_detail')->row_array();
                if (!$currentDetail) {
                    $currentDetail = $this->db->where('id_faktur', $idFaktur)->get('tbso_faktur_detail')->row_array();
                }
                $berat = (float)($currentDetail['berat_gram'] ?? 0);
                $kubikasi = (float)($currentDetail['kubikasi_m3'] ?? 0);

                $total_tonase += ($qty * $berat / 1000000);
                $total_kubikasi += ($qty * $kubikasi);

                $this->db->where('id', $idDetail)->update('tbso_faktur_detail', [
                    'qty' => $qty,
                    'hrg_satuan' => $harga,
                    'disc' => $diskonPersen,
                    'subtotal_before_disc' => $subtotal,
                    'subtotal_after_disc' => $subtotalAfterDisc,
                    'total_harga' => $subtotalAfterDisc,
                ]);
            }

            $this->db->where('id_faktur', $idFaktur)->update('tbso_faktur_penjualan', [
                'total_tonase' => round($total_tonase, 6),
                'total_kubikasi' => round($total_kubikasi, 6),
                'tanggal_faktur' => !empty($postData['tanggal_transaksi']) ? $postData['tanggal_transaksi'] : $faktur['tanggal_faktur'],
                'catatan' => isset($postData['keterangan']) ? $postData['keterangan'] : $faktur['catatan'],
                'update_by' => $userId,
                'update_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Hapus jurnal lama dan regenerasi via accounting source service
        $this->_delete_old_journals('SALES', 'FAKTUR_PENJUALAN', $faktur['no_faktur'], 'SALES_INVOICE-FAKTUR-' . $faktur['no_faktur']);
        $postRes = $this->accounting_source_service->post_sales_invoice($faktur['no_faktur'], '', $userId, true);
        if (!$postRes['success']) {
            throw new Exception('Gagal sinkronisasi jurnal faktur penjualan: ' . ($postRes['message'] ?? ''));
        }

        // Sinkronisasi tabel tbso_faktur_jurnal jika ada
        if ($this->db->table_exists('tbso_faktur_jurnal')) {
            $sum_detail = $this->db->query(
                "SELECT COALESCE(SUM(total_harga), 0) AS grand_total,
                        COALESCE(MAX(pajak), 0) AS tax_rate
                 FROM tbso_faktur_detail
                 WHERE id_faktur = ?",
                [(int)$idFaktur]
            )->row_array();

            $grand_total = (float)($sum_detail['grand_total'] ?? 0);
            $tax_rate    = (float)($sum_detail['tax_rate'] ?? 0);
            $div_factor  = ($tax_rate > 0) ? (1 + ($tax_rate / 100)) : 1;

            $jurnal_piutang    = round($grand_total, 2);
            $jurnal_penjualan  = round($jurnal_piutang / $div_factor, 2);
            $jurnal_ppn_keluar = round($jurnal_piutang - $jurnal_penjualan, 2);

            $existing_fj = $this->db->where('id_faktur', $idFaktur)
                                    ->or_where('no_faktur', $faktur['no_faktur'])
                                    ->get('tbso_faktur_jurnal')
                                    ->row_array();
            if ($existing_fj) {
                $this->db->where('id', $existing_fj['id']);
                $this->db->update('tbso_faktur_jurnal', [
                    'piutang_dagang' => $jurnal_piutang,
                    'penjualan'      => $jurnal_penjualan,
                    'ppn_keluar'     => $jurnal_ppn_keluar,
                ]);
            } else {
                $this->db->insert('tbso_faktur_jurnal', [
                    'id_faktur'      => $idFaktur,
                    'no_faktur'      => $faktur['no_faktur'],
                    'piutang_dagang' => $jurnal_piutang,
                    'penjualan'      => $jurnal_penjualan,
                    'ppn_keluar'     => $jurnal_ppn_keluar,
                    'created_at'     => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Catat ke tbso_faktur_log
        if ($this->db->table_exists('tbso_faktur_log')) {
            $user_nama = $this->session->userdata('nama') ?: ($this->session->userdata('username') ?: 'Admin');
            $user_username = $this->session->userdata('username') ?: 'admin';
            $this->db->insert('tbso_faktur_log', [
                'no_so'          => $faktur['no_so'],
                'no_faktur'      => $faktur['no_faktur'],
                'id_faktur'      => (int)$faktur['id_faktur'],
                'aksi'           => 'EDIT_FAKTUR',
                'keterangan'     => 'Revisi Qty & Nilai Faktur via Modul Transaksi. Catatan: ' . ($postData['keterangan'] ?? '-'),
                'detail_produk'  => !empty($postData['items']) ? json_encode($postData['items']) : null,
                'dilakukan_oleh' => $user_nama . ' (' . $user_username . ')',
                'ip_address'     => $this->input->ip_address(),
                'created_at'     => date('Y-m-d H:i:s')
            ]);
        }

        return ['success' => true, 'message' => 'Faktur penjualan dan jurnal akuntansi berhasil diperbarui.'];
    }

    private function _update_lpb($idLpb, $postData, $userId)
    {
        $lpb = $this->db->where('id_lpb', $idLpb)->get('tb_lpb')->row_array();
        if (!$lpb) throw new Exception('LPB tidak ditemukan.');

        if (!empty($postData['items']) && is_array($postData['items'])) {
            foreach ($postData['items'] as $item) {
                $idDetail = (int)($item['id_detail_lpb'] ?? $item['id_lpb_detail'] ?? 0);
                $qty = (float)($item['qty_diterima'] ?? 0);
                $harga = (float)($item['harga_satuan'] ?? 0);

                if (isset($item['total_harga']) && $item['total_harga'] !== '' && is_numeric(str_replace([',', ' '], '', $item['total_harga']))) {
                    $total = (float)str_replace([',', ' '], '', $item['total_harga']);
                    if ($qty > 0 && $harga <= 0) {
                        $harga = round($total / $qty, 2);
                    }
                } else {
                    $total = round($qty * $harga, 2);
                }

                $this->db->where('id_detail_lpb', $idDetail)->update('tb_lpb_detail', [
                    'qty_diterima' => $qty,
                    'harga_satuan' => $harga,
                    'total_harga' => $total,
                ]);
            }
        }

        if (!empty($postData['tanggal_transaksi'])) {
            $this->db->where('id_lpb', $idLpb)->update('tb_lpb', [
                'tgl_sj' => $postData['tanggal_transaksi'],
                'keterangan' => $postData['keterangan'] ?? $lpb['keterangan'],
            ]);
        }

        // Sinkronisasi Jurnal LPB
        $this->_delete_old_journals('LOGISTIK', 'LPB_FINAL', (string)$idLpb, 'GOODS_RECEIPT-LPB-' . $idLpb);
        $postRes = $this->accounting_source_service->post_goods_receipt($idLpb, $userId);
        if (!$postRes['success']) {
            throw new Exception('Gagal sinkronisasi jurnal LPB: ' . ($postRes['message'] ?? ''));
        }

        return ['success' => true, 'message' => 'LPB pembelian dan jurnal akuntansi berhasil diperbarui.'];
    }

    private function _update_pembayaran_customer($idPembayaran, $postData, $userId)
    {
        $pf = $this->db->where('id_pembayaran', $idPembayaran)->get('tbkeu_pembayaran_faktur')->row_array();
        if (!$pf) throw new Exception('Pembayaran customer tidak ditemukan.');

        $jumlah = (float)($postData['total_nominal'] ?? $pf['jumlah_pembayaran']);
        $diskon = (float)($postData['jumlah_diskon'] ?? $pf['jumlah_diskon']);
        $metode = !empty($postData['metode_pembayaran']) ? $postData['metode_pembayaran'] : $pf['metode_pembayaran'];
        $tanggal = !empty($postData['tanggal_transaksi']) ? $postData['tanggal_transaksi'] : $pf['tanggal_pembayaran'];

        $updateData = [
            'jumlah_pembayaran' => $jumlah,
            'jumlah_diskon' => $diskon,
            'metode_pembayaran' => $metode,
            'tanggal_pembayaran' => $tanggal,
        ];
        $this->db->where('id_pembayaran', $idPembayaran)->update('tbkeu_pembayaran_faktur', $updateData);

        // Sinkronisasi Jurnal
        $this->_delete_old_journals('KEUANGAN', 'PEMBAYARAN_FAKTUR', (string)$idPembayaran);
        $newPf = array_merge($pf, $updateData);
        $this->M_Journal->post_jurnal_pembayaran($idPembayaran, $newPf);

        return ['success' => true, 'message' => 'Pembayaran customer dan jurnal berhasil diperbarui.'];
    }

    private function _update_pembayaran_supplier($idPembayaran, $postData, $userId)
    {
        $ps = $this->db->where('id_pembayaran', $idPembayaran)->get('tbkeu_pembayaran')->row_array();
        if (!$ps) throw new Exception('Pembayaran supplier tidak ditemukan.');

        $amount = (float)($postData['total_nominal'] ?? $ps['amount']);
        $tanggal = !empty($postData['tanggal_transaksi']) ? $postData['tanggal_transaksi'] : $ps['tanggal_pembayaran'];
        $keterangan = isset($postData['keterangan']) ? $postData['keterangan'] : $ps['keterangan'];

        $this->db->where('id_pembayaran', $idPembayaran)->update('tbkeu_pembayaran', [
            'amount' => $amount,
            'tanggal_pembayaran' => $tanggal,
            'keterangan' => $keterangan,
        ]);

        if (!empty($ps['id_jurnal'])) {
            $this->_delete_journal_by_id((int)$ps['id_jurnal']);
        }
        $this->_repost_pembayaran_supplier_journal(array_merge($ps, [
            'amount' => $amount,
            'tanggal_pembayaran' => $tanggal,
            'keterangan' => $keterangan,
        ]), $userId);

        return ['success' => true, 'message' => 'Pembayaran supplier dan jurnal berhasil diperbarui.'];
    }

    private function _update_retur_penjualan($idRetur, $postData, $userId)
    {
        $rp = $this->db->where('id_retur', $idRetur)->get('tbrp_retur_penjualan_header')->row_array();
        if (!$rp) throw new Exception('Retur penjualan tidak ditemukan.');

        if (!empty($postData['items']) && is_array($postData['items'])) {
            foreach ($postData['items'] as $item) {
                $idDetail = (int)($item['id_retur_detail'] ?? 0);
                $qty = (float)($item['qty_retur'] ?? 0);
                $harga = (float)($item['harga_satuan'] ?? 0);

                if (isset($item['subtotal']) && $item['subtotal'] !== '' && is_numeric(str_replace([',', ' '], '', $item['subtotal']))) {
                    $subtotal = (float)str_replace([',', ' '], '', $item['subtotal']);
                    if ($qty > 0 && ($harga <= 0 || !isset($item['harga_satuan']))) {
                        $harga = round($subtotal / $qty, 2);
                    }
                }

                $this->db->where('id_retur_detail', $idDetail)->update('tbrp_retur_penjualan_detail', [
                    'qty_retur' => $qty,
                    'harga_satuan' => $harga,
                ]);
            }
        }

        if (!empty($postData['tanggal_transaksi'])) {
            $this->db->where('id_retur', $idRetur)->update('tbrp_retur_penjualan_header', ['tanggal_retur' => $postData['tanggal_transaksi']]);
        }

        // Sinkronisasi Jurnal Retur Penjualan
        $this->_delete_old_journals('SALES', 'RETUR_PENJUALAN', (string)$idRetur, '', $rp['no_retur']);
        $this->M_Journal->post_jurnal_retur_penjualan($idRetur);

        return ['success' => true, 'message' => 'Retur penjualan dan jurnal berhasil diperbarui.'];
    }

    private function _update_retur_pembelian($idRetur, $postData, $userId)
    {
        $rb = $this->db->where('id_retur_pembelian', $idRetur)->get('tb_retur_pembelian')->row_array();
        if (!$rb) throw new Exception('Retur pembelian tidak ditemukan.');

        $grandTotal = 0;
        if (!empty($postData['items']) && is_array($postData['items'])) {
            foreach ($postData['items'] as $item) {
                $idDetail = (int)($item['id_detail_retur_pembelian'] ?? $item['id_retur_pembelian_detail'] ?? 0);
                $qty = (float)($item['qty_retur'] ?? 0);
                $harga = (float)($item['harga_satuan'] ?? 0);

                if (isset($item['total']) && $item['total'] !== '' && is_numeric(str_replace([',', ' '], '', $item['total']))) {
                    $subtotal = (float)str_replace([',', ' '], '', $item['total']);
                    if ($qty > 0 && ($harga <= 0 || !isset($item['harga_satuan']))) {
                        $harga = round($subtotal / $qty, 2);
                    }
                } else {
                    $subtotal = round($qty * $harga, 2);
                }
                $grandTotal += $subtotal;

                $this->db->where('id_detail_retur_pembelian', $idDetail)->update('tb_retur_pembelian_detail', [
                    'qty_retur' => $qty,
                    'harga_satuan' => $harga,
                    'total' => $subtotal,
                    'dpp' => $subtotal,
                ]);
            }
        }

        $this->db->where('id_retur_pembelian', $idRetur)->update('tb_retur_pembelian', [
            'grand_total' => $grandTotal,
            'tanggal_retur' => !empty($postData['tanggal_transaksi']) ? $postData['tanggal_transaksi'] : $rb['tanggal_retur'],
        ]);

        if (!empty($rb['id_jurnal'])) {
            $this->_delete_journal_by_id((int)$rb['id_jurnal']);
        }
        $this->_delete_old_journals('LOGISTIK', 'RETUR_PEMBELIAN', (string)$idRetur);

        $this->load->model('M_ReturPembelian');
        $this->M_ReturPembelian->post_retur_pembelian($idRetur, $userId);

        return ['success' => true, 'message' => 'Retur pembelian dan jurnal berhasil diperbarui.'];
    }

    private function _repost_pembayaran_supplier_journal($ps, $userId)
    {
        $idPembayaran = (int)$ps['id_pembayaran'];
        $amount = (float)$ps['amount'];
        
        // Cari akun kas/bank dan akun hutang
        $akunKas = $this->db->like('nama_akun', 'KAS', 'after')->or_like('nama_akun', 'BANK', 'after')->get('tbkeu_akun')->row_array();
        $idAkunKas = $akunKas ? (int)$akunKas['id_akun'] : 1;

        $akunHutang = $this->db->get_where('tbkeu_akun', ['kode_akun' => '21098'])->row_array();
        if (!$akunHutang) {
            $akunHutang = $this->db->like('nama_akun', 'HUTANG', 'both')->get('tbkeu_akun')->row_array();
        }
        $idAkunHutang = $akunHutang ? (int)$akunHutang['id_akun'] : 39;

        $nomorJurnal = $this->M_Journal->generate_no_jurnal('Kas', 'KK');

        $this->db->insert('tbkeu_jurnal', [
            'nomor_jurnal' => $nomorJurnal,
            'tanggal_transaksi' => $ps['tanggal_pembayaran'] ?: date('Y-m-d'),
            'keterangan' => 'Pembayaran Hutang Supplier: ' . ($ps['keterangan'] ?? ''),
            'source_module' => 'KEUANGAN',
            'source_type' => 'SUPPLIER_PAYMENT',
            'source_id' => (string)$idPembayaran,
            'source_no' => $ps['nomor_pembayaran'] ?? ('PBY-' . $idPembayaran),
            'status' => 'POSTED',
            'total_debit' => $amount,
            'total_kredit' => $amount,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);
        $idJurnal = $this->db->insert_id();

        // Line 1 Debit Hutang Usaha
        $this->db->insert('tbkeu_jurnal_detail', [
            'id_jurnal' => $idJurnal,
            'nomor_baris' => 1,
            'id_akun' => $idAkunHutang,
            'id_supplier' => (int)($ps['id_supplier'] ?? 0),
            'keterangan' => 'Pelunasan Hutang Supplier',
            'debit' => $amount,
            'kredit' => 0,
        ]);

        // Line 2 Kredit Kas/Bank
        $this->db->insert('tbkeu_jurnal_detail', [
            'id_jurnal' => $idJurnal,
            'nomor_baris' => 2,
            'id_akun' => $idAkunKas,
            'keterangan' => 'Pengeluaran Kas/Bank untuk Supplier',
            'debit' => 0,
            'kredit' => $amount,
        ]);

        $this->db->where('id_pembayaran', $idPembayaran)->update('tbkeu_pembayaran', [
            'id_jurnal' => $idJurnal,
        ]);
    }

    private function _delete_old_journals($module, $sourceType = '', $sourceId = '', $idempotencyKey = '', $sourceNo = '')
    {
        $this->db->select('id_jurnal')->from('tbkeu_jurnal');

        $this->db->group_start();
        if ($module !== '') {
            $this->db->where('source_module', $module);
        }
        if ($sourceType !== '') {
            $this->db->where('source_type', $sourceType);
        }
        if ($sourceId !== '') {
            $this->db->group_start();
            $this->db->where('source_id', (string)$sourceId);
            if ($sourceNo !== '') {
                $this->db->or_where('source_no', (string)$sourceNo);
            }
            $this->db->group_end();
        }
        if ($idempotencyKey !== '') {
            $this->db->or_where('idempotency_key', $idempotencyKey);
        }
        $this->db->group_end();

        $journals = $this->db->get()->result_array();
        if (!empty($journals)) {
            $ids = array_column($journals, 'id_jurnal');
            $this->db->where_in('id_jurnal', $ids)->delete('tbkeu_jurnal_detail');
            if ($this->db->table_exists('tbkeu_jurnal_log')) {
                $this->db->where_in('id_jurnal', $ids)->delete('tbkeu_jurnal_log');
            }
            $this->db->where_in('id_jurnal', $ids)->delete('tbkeu_jurnal');
        }
    }

    private function _delete_journal_by_id($idJurnal)
    {
        $idJurnal = (int)$idJurnal;
        if ($idJurnal <= 0) return;

        $this->db->delete('tbkeu_jurnal_detail', ['id_jurnal' => $idJurnal]);
        if ($this->db->table_exists('tbkeu_jurnal_log')) {
            $this->db->delete('tbkeu_jurnal_log', ['id_jurnal' => $idJurnal]);
        }
        $this->db->delete('tbkeu_jurnal', ['id_jurnal' => $idJurnal]);
    }
}
