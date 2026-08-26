<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_SalesOrderLoby
 * Khusus menangani transaksi Sales Order Loby (Walk-in Customer / Direct Cash Sales).
 *
 * Alur Loby:
 *  SO Loby (CASH) -> Faktur Langsung (status: selesai_do) -> Print Faktur -> Terintegrasi Keuangan (/keuangan/pembayaran)
 *  Tanpa melalui proses Loading SO, Rute, dan Delivery Order (DO).
 */
class M_SalesOrderLoby extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_ensure_columns();
    }

    /**
     * Memastikan kolom pembeda so_source tersedia di tabel SO dan Faktur.
     */
    private function _ensure_columns()
    {
        if ($this->db->table_exists('tbso_sales_order')) {
            if (!$this->db->field_exists('so_source', 'tbso_sales_order')) {
                $this->db->query("ALTER TABLE `tbso_sales_order` ADD COLUMN `so_source` VARCHAR(20) NOT NULL DEFAULT 'SALES' AFTER `status`");
            }
        }

        if ($this->db->table_exists('tbso_faktur_penjualan')) {
            if (!$this->db->field_exists('so_source', 'tbso_faktur_penjualan')) {
                $this->db->query("ALTER TABLE `tbso_faktur_penjualan` ADD COLUMN `so_source` VARCHAR(20) NOT NULL DEFAULT 'SALES' AFTER `status`");
            }
            if (!$this->db->field_exists('tanggal_selesai_do', 'tbso_faktur_penjualan')) {
                $this->db->query("ALTER TABLE `tbso_faktur_penjualan` ADD COLUMN `tanggal_selesai_do` DATE NULL DEFAULT NULL AFTER `tanggal_faktur`");
            }
        }
    }

    // ================================================================
    // HELPER TANGGAL & STOK
    // ================================================================

    private function _normalizeDate($raw)
    {
        $raw = trim((string)$raw);
        if (!$raw) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $raw, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        return $raw;
    }

    private function _stockQtyColumn()
    {
        return $this->db->field_exists('qty_on_hand', 'tberp_stock_batch') ? 'qty_on_hand' : 'qty';
    }

    private function _prepareSalesOrderDetailData(array $detail)
    {
        if (!$this->db->field_exists('harga_approval_by', 'tbso_sales_order_detail')) {
            unset($detail['harga_approval_by']);
        }
        return $detail;
    }

    private function _stockBatchIdForMovement($kd_barang, $exp_date, $no_lot, $gudang_id, $qty, $mode)
    {
        $qty_col = $this->_stockQtyColumn();
        $exp_normalized = $this->_normalizeDate($exp_date);

        $this->db->select('id');
        $this->db->from('tberp_stock_batch');
        $this->db->where('kd_barang', $kd_barang);
        $this->db->where('gudang_id', $gudang_id);
        if (!empty($no_lot)) {
            $this->db->where('no_lot', $no_lot);
        }
        if (!empty($exp_normalized)) {
            $this->db->where('expired_date', $exp_normalized);
        }

        if ($mode === 'reserve') {
            $this->db->where('(' . $qty_col . ' - COALESCE(qty_reserved, 0)) >=', (float)$qty, false);
        } elseif ($mode === 'invoice') {
            $this->db->where($qty_col . ' >=', (float)$qty);
            $this->db->where('qty_reserved >=', (float)$qty);
        } elseif ($mode === 'release') {
            $this->db->where('qty_reserved >', 0);
        }

        $this->db->order_by('expired_date', 'ASC');
        $this->db->order_by('id', 'ASC');
        $this->db->limit(1);

        $row = $this->db->get()->row_array();
        return $row ? (int)$row['id'] : 0;
    }

    // ================================================================
    // GENERATOR NOMOR DOKUMEN
    // ================================================================

    /**
     * Generate Nomor SO Loby — format: SO-LBY/dmy/XXXX
     */
    public function generate_no_so()
    {
        $prefix = 'SO-LBY/' . date('dmy') . '/';

        $row = $this->db
            ->like('no_so', $prefix, 'after')
            ->order_by('no_so', 'DESC')
            ->limit(1)
            ->get('tbso_sales_order')
            ->row();

        if ($row) {
            $last = (int)substr($row->no_so, -4);
            return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    /**
     * Generate Nomor Faktur Penjualan Loby
     */
    public function generate_no_faktur($user_prefix = '')
    {
        $user_prefix = preg_replace('/[^A-Z]/', '', strtoupper((string)$user_prefix));
        if ($user_prefix === '') {
            $user_prefix = 'LBY';
        }
        $prefix = $user_prefix . 'INV' . date('dmy');

        $row = $this->db->query("
            SELECT nomor
            FROM (
                SELECT no_faktur AS nomor
                FROM tbso_faktur_penjualan
                WHERE no_faktur LIKE ?
                UNION
                SELECT kd_faktur AS nomor
                FROM tb_detail_do
                WHERE kd_faktur LIKE ?
                UNION
                SELECT kd_faktur AS nomor
                FROM tb_tmp_detaildo
                WHERE kd_faktur LIKE ?
            ) faktur_terpakai
            ORDER BY nomor DESC
            LIMIT 1
        ", [$prefix . '%', $prefix . '%', $prefix . '%'])->row();

        if ($row) {
            $last = (int)substr($row->nomor, -4);
            return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    // ================================================================
    // MASTER DATA LOOKUP
    // ================================================================

    public function get_customers()
    {
        return $this->db
            ->order_by('nama_customer', 'ASC')
            ->get('tb_customer')
            ->result_array();
    }

    public function get_customer($id_or_kd)
    {
        $this->db->where('id', $id_or_kd);
        $this->db->or_where('kd_customer', $id_or_kd);
        return $this->db->get('tb_customer')->row_array();
    }

    public function get_gudang_list()
    {
        return $this->db
            ->where('is_active', 1)
            ->where_in('tipe', ['INDUK'])
            ->order_by('nama_gudang', 'ASC')
            ->get('tb_gudang')
            ->result_array();
    }

    public function get_tax_list()
    {
        return $this->db->order_by('nm_tax', 'ASC')
            ->get('tb_set_tax')
            ->result_array();
    }

    public function get_detail_barang($kd_barang)
    {
        $row = $this->db->get_where('tbpo_barang', ['kode_barang' => $kd_barang])->row_array();
        if (!$row) return null;
        return $this->_normalize_barang($row);
    }

    private function _normalize_barang(array $row)
    {
        $berat = 0;
        foreach (['berat', 'berat_gram', 'weight', 'berat_satuan', 'gr'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $berat = (float)$row[$c];
                break;
            }
        }
        $kubikasi = 0;
        foreach (['kubikasi', 'kubikasi_m3', 'volume', 'kubik', 'cbm'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $kubikasi = (float)$row[$c];
                break;
            }
        }
        $hpp = 0;
        foreach (['hpp', 'harga_pokok', 'cost', 'cogs', 'h_pokok'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $hpp = (float)$row[$c];
                break;
            }
        }

        $p = (int)($row['p'] ?? $row['panjang'] ?? $row['length'] ?? 0);
        $l = (int)($row['l'] ?? $row['lebar']   ?? $row['width']  ?? 0);
        $t = (int)($row['t'] ?? $row['tinggi']  ?? $row['height'] ?? 0);

        $isi = 0;
        foreach (['isi_box', 'qty_isi', 'isi', 'isi_per_box', 'qty_per_box', 'jumlah_isi'] as $c) {
            if (array_key_exists($c, $row) && (int)$row[$c] > 0) {
                $isi = (int)$row[$c];
                break;
            }
        }
        if ($isi < 1 && $p > 0 && $l > 0 && $t > 0) $isi = $p * $l * $t;
        if ($isi < 1) $isi = 1;

        $satuan = '';
        foreach (['satuan', 'unit', 'uom', 'satuan_kecil'] as $c) {
            if (!empty($row[$c])) {
                $satuan = (string)$row[$c];
                break;
            }
        }

        $row['berat_gram']  = $berat;
        $row['kubikasi_m3'] = $kubikasi;
        $row['hpp']         = $hpp;
        $row['p']           = $p;
        $row['l']           = $l;
        $row['t']           = $t;
        $row['isi_per_box'] = $isi;
        $row['satuan']      = $satuan;

        return $row;
    }

    private function _get_master_bulk(array $kd_list)
    {
        if (empty($kd_list)) return [];
        $unique_kds = array_values(array_unique(array_filter($kd_list)));
        if (empty($unique_kds)) return [];

        $rows = $this->db->where_in('kode_barang', $unique_kds)
            ->get('tbpo_barang')
            ->result_array();

        $hpp_map = [];
        $lpb_rows = $this->db->query("
            SELECT d.kd_barang, d.harga_satuan
            FROM tb_lpb_detail d
            JOIN (
                SELECT kd_barang, MAX(id_detail_lpb) AS max_id
                FROM tb_lpb_detail
                WHERE kd_barang IN ? AND COALESCE(harga_satuan, 0) > 0
                GROUP BY kd_barang
            ) latest ON d.id_detail_lpb = latest.max_id
        ", [$unique_kds])->result_array();

        foreach ($lpb_rows as $lr) {
            $hpp_map[$lr['kd_barang']] = (float)$lr['harga_satuan'];
        }

        $map = [];
        foreach ($rows as $r) {
            $kd = $r['kode_barang'];
            $normalized = $this->_normalize_barang($r);
            if (isset($hpp_map[$kd]) && $hpp_map[$kd] > 0) {
                $normalized['hpp'] = $hpp_map[$kd];
            }
            $map[$kd] = $normalized;
        }
        return $map;
    }

    public function get_available_stock_with_dimensi($gudang_id = null, $kd_barang = null, $exclude_id_so = null)
    {
        $gudang_id_str = !empty($gudang_id) ? (string)$gudang_id : null;
        $qty_col = $this->_stockQtyColumn();
        $exclude_so = $exclude_id_so
            ? $this->db->get_where('tbso_sales_order', ['id_so' => $exclude_id_so])->row_array()
            : null;

        $this->db->select('sb.id AS stock_batch_id, sb.kd_barang, sb.gudang_id, sb.no_lot, sb.expired_date,
                           sb.' . $qty_col . ' AS qty_on_hand,
                           COALESCE(sb.qty_reserved, 0) AS qty_reserved,
                           (sb.' . $qty_col . ' - COALESCE(sb.qty_reserved, 0)) AS available_stock,
                           mb.nama_barang AS nama_barang', false);
        $this->db->from('tberp_stock_batch sb');
        $this->db->join('tbpo_barang mb', 'mb.kode_barang = sb.kd_barang', 'left');

        if (!empty($kd_barang)) {
            $this->db->where('sb.kd_barang', $kd_barang);
        }
        if (!empty($gudang_id_str)) {
            $this->db->where(
                "CAST(sb.gudang_id AS CHAR) = CAST('" . $this->db->escape_str($gudang_id_str) . "' AS CHAR)"
            );
        }

        $this->db->order_by('sb.kd_barang', 'ASC');
        $this->db->order_by('sb.expired_date', 'ASC');
        $this->db->order_by('sb.no_lot', 'ASC');
        $this->db->order_by('sb.id', 'ASC');

        $stocks = $this->db->get()->result_array();

        if (empty($stocks)) return [];

        $master = $this->_get_master_bulk(array_column($stocks, 'kd_barang'));

        foreach ($stocks as &$row) {
            $kd = $row['kd_barang'];
            $m  = $master[$kd] ?? [];

            if (empty($row['nama_barang']) && !empty($m['nama_barang'])) {
                $row['nama_barang'] = $m['nama_barang'];
            }

            $row['berat_gram']  = $m['berat_gram']  ?? 0;
            $row['kubikasi_m3'] = $m['kubikasi_m3'] ?? 0;
            $row['hpp']         = $m['hpp']         ?? 0;
            $row['p']           = $m['p']           ?? 0;
            $row['l']           = $m['l']           ?? 0;
            $row['t']           = $m['t']           ?? 0;
            $row['isi_per_box'] = $m['isi_per_box'] ?? 1;
            $row['satuan']      = $m['satuan']      ?? ($row['satuan'] ?? '');

            if (isset($row['expired_date'])) {
                $row['exp_date'] = $this->_normalizeDate($row['expired_date']);
                $row['gudang']   = (string)($row['gudang_id'] ?? '');
            } else {
                $row['exp_date'] = $this->_normalizeDate($row['exp_date'] ?? '');
                $row['gudang']   = (string)($row['gudang'] ?? '');
            }

            $row['gudang_id'] = (string)($row['gudang_id'] ?? $row['gudang'] ?? '');

            $own_reserved = 0;
            if ($exclude_so && (string)($exclude_so['gudang_id'] ?? '') === (string)($row['gudang_id'] ?? '')) {
                $own_reserved = $this->_reserved_qty_for_so_batch(
                    $exclude_id_so,
                    $row['kd_barang'],
                    $row['exp_date'] ?? $row['expired_date'] ?? '',
                    $row['no_lot'] ?? null
                );
            }

            $av  = (float)($row['available_stock'] ?? 0) + $own_reserved;
            if ($av <= 0) {
                $row = null;
                continue;
            }

            $row['own_reserved_stock'] = $own_reserved;
            $row['available_stock']    = $av;
            $isi = max(1, (int)$row['isi_per_box']);

            $row['available_box']  = (int)floor($av / $isi);
            $row['available_ecer'] = (int)fmod($av, $isi);
        }
        unset($row);

        return array_values(array_filter($stocks));
    }

    private function _reserved_qty_for_so_batch($id_so, $kd_barang, $expired_date, $no_lot = null)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        if (!$so) return 0;

        $exp_normalized = $this->_normalizeDate($expired_date);

        $this->db->select('SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS reserved_qty');
        $this->db->from('tbso_sales_order_detail');
        $this->db->where('id_so', $id_so);
        $this->db->where('kd_barang', $kd_barang);
        if (!empty($exp_normalized)) {
            $this->db->where('expired_date', $exp_normalized);
        }
        if (!empty($no_lot)) {
            $this->db->where('no_lot', $no_lot);
        }
        $row = $this->db->get()->row_array();
        return (float)($row['reserved_qty'] ?? 0);
    }

    // ================================================================
    // RESERVASI & PENGURANGAN STOK
    // ================================================================

    private function _reservasi_stok($no_so, $id_so_detail, $kd_barang, $exp_date, $no_lot, $gudang_id, $qty)
    {
        $qty_col = $this->_stockQtyColumn();
        $exp_normalized = $this->_normalizeDate($exp_date);

        $stock_batch_id = $this->_stockBatchIdForMovement(
            $kd_barang,
            $exp_normalized,
            $no_lot,
            $gudang_id,
            $qty,
            'reserve'
        );

        if ($stock_batch_id <= 0) {
            return false;
        }

        $this->db->where('id', $stock_batch_id);
        $this->db->set('qty_reserved', 'COALESCE(qty_reserved, 0) + ' . (float)$qty, false);
        $this->db->set('update_at', date('Y-m-d H:i:s'));
        $this->db->update('tberp_stock_batch');

        if ($this->db->affected_rows() < 1) {
            return false;
        }

        $this->db->insert('tberp_stock_ledger', [
            'kd_barang'    => $kd_barang,
            'gudang_id'    => $gudang_id,
            'no_lot'       => $no_lot ?: null,
            'expired_date' => $exp_normalized,
            'qty'          => (float)$qty,
            'tipe'         => 'RESERVE',
            'ref_no'       => $no_so,
            'ref_type'     => 'SALES_ORDER_LOBY',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    private function _kurangi_reserved_batch($no_so, $kd_barang, $exp_date, $no_lot, $gudang_id, $qty)
    {
        $exp_normalized = $this->_normalizeDate($exp_date);
        $stock_batch_id = $this->_stockBatchIdForMovement(
            $kd_barang,
            $exp_normalized,
            $no_lot,
            $gudang_id,
            $qty,
            'release'
        );

        if ($stock_batch_id <= 0) {
            return false;
        }

        $this->db->where('id', $stock_batch_id);
        $this->db->set('qty_reserved', 'GREATEST(COALESCE(qty_reserved, 0) - ' . (float)$qty . ', 0)', false);
        $this->db->set('update_at', date('Y-m-d H:i:s'));
        $this->db->update('tberp_stock_batch');

        $this->db->insert('tberp_stock_ledger', [
            'kd_barang'    => $kd_barang,
            'gudang_id'    => $gudang_id,
            'no_lot'       => $no_lot ?: null,
            'expired_date' => $exp_normalized,
            'qty'          => (float)$qty,
            'tipe'         => 'RELEASE',
            'ref_no'       => $no_so,
            'ref_type'     => 'SALES_ORDER_LOBY_CANCEL',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    // ================================================================
    // LIST SALES ORDER LOBY
    // ================================================================

    public function get_all_so_loby($filter = [])
    {
        $this->db->select('
            so.*,
            c.nama_customer,
            c.nama_kios,
            c.regional,
            COUNT(sd.id) AS jumlah_item,
            COALESCE(SUM(sd.qty), 0) AS total_qty_order,
            COALESCE(SUM(sd.qty_faktur), 0) AS total_qty_faktur,
            COALESCE(SUM(sd.total_harga), 0) AS grand_total_so,
            fp.id_faktur,
            fp.no_faktur,
            fp.status AS status_faktur,
            fp.tanggal_faktur,
            CASE
                WHEN fp.id_faktur IS NOT NULL THEN 1
                ELSE 0
            END AS is_invoiced
        ');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.kd_customer = so.kd_customer', 'left');
        $this->db->join('tbso_sales_order_detail sd', 'sd.id_so = so.id_so', 'left');
        $this->db->join('tbso_faktur_penjualan fp', 'fp.id_so = so.id_so', 'left');
        
        // Filter khusus LOBY
        $this->db->where('so.so_source', 'LOBY');

        if (!empty($filter['status'])) {
            if ($filter['status'] === 'invoiced') {
                $this->db->where('fp.id_faktur IS NOT NULL');
            } elseif ($filter['status'] === 'un-invoiced') {
                $this->db->where('fp.id_faktur IS NULL');
                $this->db->where('so.status !=', 'cancelled');
            } else {
                $this->db->where('so.status', $filter['status']);
            }
        }

        if (!empty($filter['date_start'])) {
            $this->db->where('so.tanggal_transaksi >=', $filter['date_start']);
        }
        if (!empty($filter['date_end'])) {
            $this->db->where('so.tanggal_transaksi <=', $filter['date_end']);
        }
        if (!empty($filter['kd_customer'])) {
            $this->db->where('so.kd_customer', $filter['kd_customer']);
        }
        if (!empty($filter['keyword'])) {
            $kw = $filter['keyword'];
            $this->db->group_start();
            $this->db->like('so.no_so', $kw);
            $this->db->or_like('so.customer_name', $kw);
            $this->db->or_like('c.nama_customer', $kw);
            $this->db->or_like('fp.no_faktur', $kw);
            $this->db->group_end();
        }

        $this->db->group_by('so.id_so, fp.id_faktur');
        $this->db->order_by('so.tanggal_transaksi', 'DESC');
        $this->db->order_by('so.id_so', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_so($id_so)
    {
        $this->db->select('so.*, c.nama_customer, c.nama_kios, c.alamat_kios, c.regional');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.kd_customer = so.kd_customer', 'left');
        $this->db->where('so.id_so', $id_so);
        $this->db->where('so.so_source', 'LOBY');
        return $this->db->get()->row_array();
    }

    public function get_so_detail($id_so)
    {
        $rows = $this->db
            ->select('d.id AS id_so_detail, d.*, b.kelompok_dagang')
            ->from('tbso_sales_order_detail d')
            ->join('tbpo_barang b', 'd.kd_barang = b.kode_barang', 'left')
            ->where('d.id_so', $id_so)
            ->get()
            ->result_array();

        foreach ($rows as &$row) {
            $row['berat_gram']      = (float)($row['berat_gram']       ?? 0);
            $row['kubikasi_m3']     = (float)($row['kubikasi_m3']      ?? 0);
            $row['hrg_pokok']       = (float)($row['hrg_pokok']        ?? 0);
            $row['disc']            = (float)($row['disc']             ?? 0);
            $row['qty_faktur']      = (float)($row['qty_faktur']       ?? 0);
            $row['qty_outstanding'] = max(0, (float)$row['qty'] - (float)$row['qty_faktur']);

            if (!isset($row['qty_box']) || $row['qty_box'] === null) {
                $isi               = max(1, (int)($row['isi_per_box'] ?? 1));
                $row['qty_box']    = floor((float)$row['qty'] / $isi);
                $row['qty_satuan'] = fmod((float)$row['qty'], $isi);
            }
        }
        unset($row);

        return $rows;
    }

    // ================================================================
    // SIMPAN / UPDATE / CANCEL SO LOBY
    // ================================================================

    public function simpan_so($header, $details)
    {
        $this->db->trans_start();

        $so_data = [
            'no_so'             => $header['no_so'],
            'tanggal_transaksi' => $header['tanggal_transaksi'],
            'kd_customer'       => $header['kd_customer'],
            'customer_name'     => $header['customer_name'],
            'gudang_id'         => $header['gudang_id'],
            'batas_tonase'      => $header['batas_tonase'] ?? 7,
            'batas_kubikasi'    => $header['batas_kubikasi'] ?? 9,
            'total_tonase'      => $header['total_tonase'] ?? 0,
            'total_kubikasi'    => $header['total_kubikasi'] ?? 0,
            'status'            => 'open', // SO Loby langsung Open (siap faktur)
            'so_source'         => 'LOBY', // Penanda Transaksi LOBY
            'catatan'           => $header['catatan'] ?? 'Penjualan Loby (Direct Cash)',
            'cara_pembayaran'   => 'cash', // Pembayaran Hanya CASH
            'create_by'         => $header['create_by'],
            'create_at'         => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('tbso_sales_order', $so_data);
        $id_so = $this->db->insert_id();
        $no_so = $header['no_so'];

        foreach ($details as $d) {
            $d['id_so']          = $id_so;
            $d['no_so']          = $no_so;
            $d['qty_faktur']     = 0;
            $d['qty_siap_faktur'] = $d['qty'];
            $d = $this->_prepareSalesOrderDetailData($d);
            $this->db->insert('tbso_sales_order_detail', $d);
            $id_detail = $this->db->insert_id();

            $reserved = $this->_reservasi_stok(
                $no_so,
                $id_detail,
                $d['kd_barang'],
                $d['expired_date'],
                $d['no_lot'] ?? null,
                $header['gudang_id'],
                $d['qty']
            );

            if (!$reserved) {
                $this->db->trans_rollback();
                return false;
            }
        }

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['jumlah_item' => count($details)]);

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id_so : false;
    }

    public function update_so($id_so, $header, $details)
    {
        $so = $this->get_so($id_so);
        if (!$so || in_array($so['status'], ['completed', 'cancelled'], true)) {
            return false;
        }

        // Cek apakah sudah ada faktur
        $fakturs = $this->get_faktur_by_so($id_so);
        if (!empty($fakturs)) {
            return false;
        }

        $this->db->trans_start();

        $no_so    = $so['no_so'];
        $gudang_id = $header['gudang_id'];

        $so_update = [
            'tanggal_transaksi' => $header['tanggal_transaksi'],
            'kd_customer'       => $header['kd_customer'],
            'customer_name'     => $header['customer_name'],
            'gudang_id'         => $gudang_id,
            'total_tonase'      => $header['total_tonase'] ?? 0,
            'total_kubikasi'    => $header['total_kubikasi'] ?? 0,
            'catatan'           => $header['catatan'] ?? null,
            'cara_pembayaran'   => 'cash',
            'update_by'         => $header['update_by'],
            'update_at'         => date('Y-m-d H:i:s'),
        ];
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', $so_update);

        // Lepas reservasi lama
        $old_details = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
        foreach ($old_details as $old) {
            $outstanding = (float)$old['qty'] - (float)($old['qty_faktur'] ?? 0);
            if ($outstanding <= 0) continue;

            $this->_kurangi_reserved_batch(
                $no_so,
                $old['kd_barang'],
                $old['expired_date'],
                $old['no_lot'] ?? null,
                $so['gudang_id'],
                $outstanding
            );
        }

        // Hapus detail lama dan re-insert detail baru
        $this->db->delete('tbso_sales_order_detail', ['id_so' => $id_so]);

        foreach ($details as $d) {
            $d['id_so']          = $id_so;
            $d['no_so']          = $no_so;
            $d['qty_faktur']     = 0;
            $d['qty_siap_faktur'] = $d['qty'];
            $d = $this->_prepareSalesOrderDetailData($d);
            $this->db->insert('tbso_sales_order_detail', $d);
            $id_detail = $this->db->insert_id();

            $reserved = $this->_reservasi_stok(
                $no_so,
                $id_detail,
                $d['kd_barang'],
                $d['expired_date'],
                $d['no_lot'] ?? null,
                $gudang_id,
                $d['qty']
            );

            if (!$reserved) {
                $this->db->trans_rollback();
                return false;
            }
        }

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['jumlah_item' => count($details)]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function cancel_so($id_so, $cancel_by)
    {
        $so = $this->get_so($id_so);
        if (!$so || in_array($so['status'], ['completed', 'cancelled'], true)) {
            return false;
        }

        $fakturs = $this->get_faktur_by_so($id_so);
        if (!empty($fakturs)) {
            return false;
        }

        $this->db->trans_start();

        $details = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
        foreach ($details as $d) {
            $outstanding = (float)$d['qty'] - (float)($d['qty_faktur'] ?? 0);
            if ($outstanding <= 0) continue;

            $this->_kurangi_reserved_batch(
                $so['no_so'],
                $d['kd_barang'],
                $d['expired_date'],
                $d['no_lot'] ?? null,
                $so['gudang_id'],
                $outstanding
            );
        }

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', [
            'status'    => 'cancelled',
            'update_by' => $cancel_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ================================================================
    // PROSES FAKTUR PENJUALAN LOBY (INSTAN & TERINTEGRASI)
    // ================================================================

    public function get_faktur_by_so($id_so)
    {
        return $this->db
            ->where('id_so', $id_so)
            ->order_by('create_at', 'ASC')
            ->get('tbso_faktur_penjualan')
            ->result_array();
    }

    public function get_faktur($id_faktur)
    {
        $this->db->select('f.*, c.nama_customer, c.nama_kios, c.alamat_kios, c.regional, c.kd_rute AS customer_kd_rute');
        $this->db->from('tbso_faktur_penjualan f');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        if (is_numeric($id_faktur)) {
            $this->db->where('f.id_faktur', (int)$id_faktur);
        } else {
            $this->db->where('f.no_faktur', $id_faktur);
        }
        return $this->db->get()->row_array();
    }

    public function get_faktur_detail($id_faktur)
    {
        return $this->db
            ->select('fd.*, mb.nama_barang AS master_nama_barang')
            ->from('tbso_faktur_detail fd')
            ->join('tbpo_barang mb', 'mb.kode_barang = fd.kd_barang', 'left')
            ->where('fd.id_faktur', $id_faktur)
            ->get()
            ->result_array();
    }

    /**
     * Memproses Faktur Penjualan langsung untuk SO Loby.
     * Alur:
     * 1. Insert header `tbso_faktur_penjualan` dengan status = 'selesai_do', so_source = 'LOBY', cara_pembayaran = 'cash'.
     * 2. Insert detail `tbso_faktur_detail`.
     * 3. Pengurangan stok fisik OUT pada `tberp_stock_batch` & `tberp_stock_ledger`.
     * 4. Posting jurnal akuntansi penjualan otomatis.
     * 5. Update SO status menjadi 'completed'.
     */
    public function buat_faktur_loby($id_so, $faktur_header, $faktur_items)
    {
        $so = $this->get_so($id_so);
        if (!$so || $so['status'] === 'completed' || $so['status'] === 'cancelled') {
            return ['errors' => ['Sales Order Loby tidak valid atau sudah selesai/dibatalkan.']];
        }

        // Cek apakah sudah pernah difakturkan
        $existing_fakturs = $this->get_faktur_by_so($id_so);
        if (!empty($existing_fakturs)) {
            return ['errors' => ['Sales Order Loby ini sudah memiliki Faktur Penjualan.']];
        }

        $this->db->trans_start();

        $no_faktur = $faktur_header['no_faktur'];
        $gudang_id = $so['gudang_id'];
        $tgl_faktur = $faktur_header['tanggal_faktur'] ?? date('Y-m-d');

        // 1. Insert Header Faktur
        $fh = [
            'no_faktur'          => $no_faktur,
            'id_so'              => $id_so,
            'no_so'              => $so['no_so'],
            'kd_customer'        => $so['kd_customer'],
            'customer_name'      => $so['customer_name'],
            'gudang_id'          => $gudang_id,
            'tanggal_faktur'     => $tgl_faktur,
            'tanggal_selesai_do' => $tgl_faktur, // Langsung selesai untuk LOBY
            'catatan'            => $faktur_header['catatan'] ?? 'Penjualan Langsung Loby',
            'status'             => 'selesai_do', // Siap masuk pembayaran keuangan
            'so_source'          => 'LOBY',
            'cara_pembayaran'    => 'cash',
            'create_by'          => $faktur_header['create_by'],
            'create_at'          => date('Y-m-d H:i:s'),
        ];

        // Hitung total tonase & kubikasi
        $total_tonase   = 0;
        $total_kubikasi = 0;
        foreach ($faktur_items as $item) {
            $total_tonase   += (float)$item['qty'] * ((float)($item['berat_gram'] ?? 0) / 1000000);
            $total_kubikasi += (float)$item['qty'] * (float)($item['kubikasi_m3'] ?? 0);
        }
        $fh['total_tonase']   = round($total_tonase, 6);
        $fh['total_kubikasi'] = round($total_kubikasi, 6);

        $this->db->insert('tbso_faktur_penjualan', $fh);
        $id_faktur = $this->db->insert_id();

        // 2. Insert Detail Faktur & Pengurangan Stok Fisik
        $qty_col = $this->_stockQtyColumn();

        foreach ($faktur_items as $item) {
            $qty_item = (float)$item['qty'];

            $fd = [
                'id_faktur'           => $id_faktur,
                'no_faktur'           => $no_faktur,
                'id_so'               => $id_so,
                'id_so_detail'        => $item['id_so_detail'],
                'kd_barang'           => $item['kd_barang'],
                'nama_barang'         => $item['nama_barang']    ?? '',
                'no_lot'              => $item['no_lot']         ?? null,
                'expired_date'        => $this->_normalizeDate($item['expired_date'] ?? ''),
                'qty'                 => $qty_item,
                'qty_box'             => $item['qty_box']        ?? 0,
                'qty_satuan'          => $item['qty_satuan']     ?? 0,
                'isi_per_box'         => $item['isi_per_box']    ?? 1,
                'satuan'              => $item['satuan']         ?? '',
                'hrg_satuan'          => $item['hrg_satuan']     ?? 0,
                'hrg_pokok'           => $item['hrg_pokok']      ?? 0,
                'disc'                => $item['disc']           ?? 0,
                'pajak'               => $item['pajak']          ?? 0,
                'subtotal_before_disc' => $item['subtotal_before_disc'] ?? 0,
                'subtotal_after_disc'  => $item['subtotal_after_disc']  ?? 0,
                'total_harga'         => $item['total_harga']    ?? 0,
                'berat_gram'          => $item['berat_gram']     ?? 0,
                'kubikasi_m3'         => $item['kubikasi_m3']    ?? 0,
                'gudang_id'           => $gudang_id,
                'create_by'           => $faktur_header['create_by'],
            ];
            $this->db->insert('tbso_faktur_detail', $fd);

            $exp_normalized = $this->_normalizeDate($item['expired_date'] ?? '');

            // Kurangi stok fisik dan reserved di tberp_stock_batch
            $stock_batch_id = $this->_stockBatchIdForMovement(
                $item['kd_barang'],
                $exp_normalized,
                $item['no_lot'] ?? null,
                $gudang_id,
                $qty_item,
                'invoice'
            );

            if ($stock_batch_id <= 0) {
                $this->db->trans_rollback();
                return ['errors' => ['Stok batch untuk barang ' . $item['kd_barang'] . ' tidak mencukupi saat faktur dibuat.']];
            }

            $this->db->where('id', $stock_batch_id);
            $this->db->set($qty_col, $qty_col . ' - ' . $qty_item, false);
            $this->db->set('qty_reserved', 'qty_reserved - ' . $qty_item, false);
            $this->db->set('update_at', date('Y-m-d H:i:s'));
            $this->db->update('tberp_stock_batch');

            // Log ledger OUT
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $item['kd_barang'],
                'gudang_id'    => $gudang_id,
                'no_lot'       => $item['no_lot'] ?? null,
                'expired_date' => $exp_normalized,
                'qty'          => $qty_item,
                'tipe'         => 'OUT',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'FAKTUR PENJUALAN LOBY',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            // Update qty_faktur di detail SO
            $this->db->where('id', $item['id_so_detail']);
            $this->db->set('qty_faktur', (float)$qty_item);
            $this->db->update('tbso_sales_order_detail');
        }

        // 3. Rekam ringkasan jurnal faktur di tbso_faktur_jurnal
        $total_nilai_pesanan = 0;
        foreach ($faktur_items as $item) {
            $total_nilai_pesanan += (float)($item['subtotal_after_disc'] ?? 0);
        }
        $tax_rate = (float)($faktur_items[0]['pajak'] ?? 0);
        $div_factor = 1 + ($tax_rate / 100);
        
        $jurnal_piutang = round($total_nilai_pesanan);
        $jurnal_penjualan = round($jurnal_piutang / $div_factor);
        $jurnal_ppn_keluar = $jurnal_piutang - $jurnal_penjualan;

        $fj = [
            'id_faktur'      => $id_faktur,
            'no_faktur'      => $no_faktur,
            'piutang_dagang' => $jurnal_piutang,
            'penjualan'      => $jurnal_penjualan,
            'ppn_keluar'     => $jurnal_ppn_keluar,
            'created_at'     => date('Y-m-d H:i:s')
        ];
        if ($this->db->table_exists('tbso_faktur_jurnal')) {
            $this->db->insert('tbso_faktur_jurnal', $fj);
        }

        // 4. Posting jurnal accounting otomatis
        if ($this->db->table_exists('tbkeu_jurnal') && $this->db->table_exists('tbkeu_jurnal_detail')) {
            $this->load->library('Accounting_source_service');
            $journal = $this->accounting_source_service->post_sales_invoice(
                $no_faktur,
                '',
                (int)($faktur_header['created_by_id'] ?? 0) ?: null,
                true
            );

            if (empty($journal['success'])) {
                $this->db->trans_rollback();
                return [
                    'errors' => [
                        'Faktur Loby batal disimpan karena posting jurnal otomatis gagal: '
                        . ($journal['message'] ?? 'Posting jurnal gagal.')
                    ],
                ];
            }
        }

        // 5. Update status SO Loby menjadi 'completed'
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', [
            'status'    => 'completed',
            'update_by' => $faktur_header['create_by'],
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();
        if (!$this->db->trans_status()) {
            return false;
        }

        return [
            'id_faktur' => $id_faktur,
            'no_faktur' => $no_faktur,
        ];
    }
}
