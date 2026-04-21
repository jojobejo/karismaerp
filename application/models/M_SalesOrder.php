<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_SalesOrder.php
 *
 * GUDANG: v_available_stock.gudang = tb_karyawan.wilayah (integer)
 *         tbso_stock_reservation.gudang_id harus sama nilainya (integer).
 *
 * FORMAT exp_date di view: DD/MM/YYYY (text/varchar)
 *   Form mengirim YYYY-MM-DD → dikonversi DD/MM/YYYY saat query/simpan.
 *
 * STOK BERKURANG:
 *   v_available_stock JOIN tbso_stock_reservation ON:
 *     kd_barang = kd_barang
 *     exp_date  = exp_date   ← harus sama format (DD/MM/YYYY)
 *     gudang    = gudang_id  ← harus sama nilai (integer wilayah)
 *     status    = 'active'
 */
class M_SalesOrder extends CI_Model
{
    const BATAS_TONASE   = 6;
    const BATAS_KUBIKASI = 9;

    // ----------------------------------------------------------------
    // HELPER tanggal
    // ----------------------------------------------------------------
    private function _normalizeDate($raw)
    {
        $raw = trim((string)$raw);
        if (!$raw) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $raw, $m))
            return $m[3].'-'.$m[2].'-'.$m[1];
        return $raw;
    }

    // YYYY-MM-DD → DD/MM/YYYY (format kolom exp_date di view & reservasi)
    private function _toViewDate($ymd)
    {
        $ymd = $this->_normalizeDate($ymd);
        if (!$ymd) return '';
        $p = explode('-', $ymd);
        return count($p) === 3 ? $p[2].'/'.$p[1].'/'.$p[0] : $ymd;
    }

    // ----------------------------------------------------------------
    // GENERATE NOMOR SO
    // ----------------------------------------------------------------
    public function generate_no_faktur()
    {
        $prefix = 'INV' . date('dmY');

        $this->db->like('no_faktur', $prefix, 'after');
        $this->db->order_by('no_faktur', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get('tbso_sales_order')->row();

        if ($row) {
            $last = (int) substr($row->no_faktur, -4);
            return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    // ----------------------------------------------------------------
    // CUSTOMER
    // ----------------------------------------------------------------
    public function get_customers()
    {
        return $this->db->order_by('nama_customer', 'ASC')
                        ->get('tb_customer')
                        ->result_array();
    }

    public function get_customer($id)
    {
        return $this->db->get_where('tb_customer', ['id' => $id])->row_array();
    }

    // ----------------------------------------------------------------
    // STOK
    // ----------------------------------------------------------------
    public function get_available_stock($gudang_id = null, $kd_barang = null)
    {
        $this->db->where('available_stock >', 0);
        if (!empty($gudang_id)) $this->db->where('gudang', $gudang_id);
        if ($kd_barang)         $this->db->where('kode_barang', $kd_barang);
        return $this->db->get('v_available_stock')->result_array();
    }

    /**
     * Cek stok satu item.
     * exp_date input (YYYY-MM-DD) dikonversi ke DD/MM/YYYY untuk cocokkan kolom view.
     * gudang_id kosong → SUM semua gudang (fallback saat session belum set).
     */
    public function cek_stock($kd_barang, $exp_date, $gudang_id)
    {
        $ddmmyyyy = $this->_toViewDate($exp_date);

        if (!empty($gudang_id)) {
            $sql = "SELECT * FROM v_available_stock
                    WHERE kode_barang = ? AND exp_date = ? AND gudang = ?
                    LIMIT 1";
            return $this->db->query($sql, [$kd_barang, $ddmmyyyy, $gudang_id])->row_array();
        } else {
            $sql = "SELECT kode_barang, nama_barang, exp_date,
                           SUM(available_stock) AS available_stock
                    FROM v_available_stock
                    WHERE kode_barang = ? AND exp_date = ?
                    GROUP BY kode_barang, nama_barang, exp_date
                    LIMIT 1";
            return $this->db->query($sql, [$kd_barang, $ddmmyyyy])->row_array();
        }
    }

    // ----------------------------------------------------------------
    // MASTER BARANG
    // ----------------------------------------------------------------
    public function get_detail_barang($kd_barang)
    {
        $row = $this->db->get_where('tb_master_barang_all', ['kd_barang' => $kd_barang])->row_array();
        if (!$row) return null;
        return $this->_normalize_barang($row);
    }

    private function _get_master_bulk(array $kd_list)
    {
        if (empty($kd_list)) return [];
        $rows = $this->db->where_in('kd_barang', array_unique($kd_list))
                         ->get('tb_master_barang_all')
                         ->result_array();
        $map = [];
        foreach ($rows as $r) { $map[$r['kd_barang']] = $this->_normalize_barang($r); }
        return $map;
    }

    private function _normalize_barang(array $row)
    {
        $berat = 0;
        foreach (['berat','berat_gram','weight','berat_satuan','gr'] as $c) {
            if (array_key_exists($c,$row) && $row[$c]!==null && $row[$c]!=='') { $berat=(float)$row[$c]; break; }
        }
        $kubikasi = 0;
        foreach (['kubikasi','kubikasi_m3','volume','kubik','cbm'] as $c) {
            if (array_key_exists($c,$row) && $row[$c]!==null && $row[$c]!=='') { $kubikasi=(float)$row[$c]; break; }
        }
        $hpp = 0;
        foreach (['hpp','harga_pokok','cost','cogs','h_pokok'] as $c) {
            if (array_key_exists($c,$row) && $row[$c]!==null && $row[$c]!=='') { $hpp=(float)$row[$c]; break; }
        }

        $p = (int)($row['p'] ?? $row['panjang'] ?? $row['length'] ?? 0);
        $l = (int)($row['l'] ?? $row['lebar']   ?? $row['width']  ?? 0);
        $t = (int)($row['t'] ?? $row['tinggi']  ?? $row['height'] ?? 0);

        $isi = 0;
        foreach (['isi_box','qty_isi','isi','isi_per_box','qty_per_box','jumlah_isi'] as $c) {
            if (array_key_exists($c,$row) && (int)$row[$c]>0) { $isi=(int)$row[$c]; break; }
        }
        if ($isi < 1 && $p > 0 && $l > 0 && $t > 0) $isi = $p * $l * $t;
        if ($isi < 1) $isi = 1;

        $satuan = '';
        foreach (['satuan','unit','uom','satuan_kecil'] as $c) {
            if (!empty($row[$c])) { $satuan = (string)$row[$c]; break; }
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

    // ----------------------------------------------------------------
    // STOK + DIMENSI — AJAX /get_stock
    // ----------------------------------------------------------------
    public function get_available_stock_with_dimensi($gudang_id = null, $kd_barang = null)
    {
        $this->db->where('available_stock >', 0);
        if ($kd_barang)         $this->db->where('kode_barang', $kd_barang);
        $stocks = $this->db->get('v_available_stock')->result_array();

        if (empty($stocks)) return [];

        $kd_list = array_column($stocks, 'kode_barang');
        $master  = $this->_get_master_bulk($kd_list);

        foreach ($stocks as &$row) {
            $kd = $row['kode_barang'];
            $m  = isset($master[$kd]) ? $master[$kd] : [];

            $row['berat_gram']  = $m['berat_gram']  ?? 0;
            $row['kubikasi_m3'] = $m['kubikasi_m3'] ?? 0;
            $row['hpp']         = $m['hpp']         ?? 0;
            $row['p']           = $m['p']           ?? 0;
            $row['l']           = $m['l']           ?? 0;
            $row['t']           = $m['t']           ?? 0;
            $row['isi_per_box'] = $m['isi_per_box'] ?? 1;
            $row['satuan']      = $m['satuan'] ?? ($row['satuan'] ?? '');

            // Konversi exp_date DD/MM/YYYY → YYYY-MM-DD untuk form/JS
            $row['exp_date'] = $this->_normalizeDate($row['exp_date'] ?? '');
            $row['gudang'] = $row['gudang'] ?? '';

            $av  = (float)($row['available_stock'] ?? 0);
            $isi = max(1, (int)$row['isi_per_box']);

            $row['available_box']  = (int)floor($av / $isi);
            $row['available_ecer'] = (int)fmod($av, $isi);
        }
        unset($row);

        return $stocks;
    }

    // ----------------------------------------------------------------
    // LIST SO
    // ----------------------------------------------------------------
    public function get_all_so($filter = [])
    {
        $this->db->select('so.*, c.nama_customer');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.id = so.customer_id', 'left');
        if (!empty($filter['status']))       $this->db->where('so.status', $filter['status']);
        if (!empty($filter['date1']))        $this->db->where('so.tanggal_transaksi >=', $filter['date1']);
        if (!empty($filter['date2']))        $this->db->where('so.tanggal_transaksi <=', $filter['date2']);
        if (!empty($filter['customer_id'])) $this->db->where('so.customer_id', $filter['customer_id']);
        $this->db->order_by('so.create_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_so($id_so)
    {
        $this->db->select('so.*, c.nama_customer');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.id = so.customer_id', 'left');
        $this->db->where('so.id_so', $id_so);
        return $this->db->get()->row_array();
    }

    public function get_so_detail($no_so)
    {
        $rows = $this->db->get_where('tbso_sales_order_detail', ['no_so' => $no_so])->result_array();

        // Mapping nama kolom DB → nama yang dipakai JS di so_form
        foreach ($rows as &$row) {
            $row['berat_gram']  = $row['tonase_satuan']   ?? 0;
            $row['kubikasi_m3'] = $row['kubikasi_satuan'] ?? 0;
            $row['hrg_pokok']   = $row['hrg_pokok']       ?? 0;
            $row['gudang']      = $row['gudang_id']        ?? '';

            if (!isset($row['qty_box'])) {
                $isi = max(1, (int)($row['isi_per_box'] ?? 1));
                $row['qty_box']    = floor((float)$row['qty'] / $isi);
                $row['qty_satuan'] = fmod((float)$row['qty'], $isi);
            }
        }
        unset($row);

        return $rows;
    }

    // ----------------------------------------------------------------
    // SIMPAN SO
    //
    // KUNCI STOK BERKURANG:
    //   tbso_stock_reservation.exp_date  → DD/MM/YYYY (sama dengan view)
    //   tbso_stock_reservation.gudang_id → integer wilayah (sama dengan view.gudang)
    //   Kedua field ini harus identik dengan yang ada di v_available_stock
    //   agar JOIN di view bisa mengurangi available_stock.
    // ----------------------------------------------------------------
    public function simpan_so($header, $details)
    {
        $this->db->trans_start();
        $this->db->insert('tbso_sales_order', $header);
        $id_so = $this->db->insert_id();
        $no_so = $header['no_so'];

        foreach ($details as $d) {
            $d['no_so']    = $no_so;
            $d['no_faktur'] = $header['no_faktur'];
            $this->db->insert('tbso_sales_order_detail', $d);
            $id_detail = $this->db->insert_id();

            $exp_ddmmyyyy = $this->_toViewDate($d['expired_date']);

            $this->db->insert('tbso_stock_reservation', [
                'no_so'        => $no_so,
                'no_faktur'    => $header['no_faktur'],
                'id_so_detail' => $id_detail,
                'kd_barang'    => $d['kd_barang'],
                'exp_date'     => $this->_normalizeDate($d['expired_date']),
                'no_lot'       => $d['no_lot'],
                'gudang_id'    => $header['gudang_id'],
                'qty_reserved' => $d['qty'],
                'status'       => 'active',
            ]);
        }

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['jumlah_item' => count($details)]);
        $this->db->trans_complete();

        return $this->db->trans_status() ? $id_so : false;
    }

    // ----------------------------------------------------------------
    // UPDATE SO
    // ----------------------------------------------------------------
    public function update_so($id_so, $header, $details)
    {
        $this->db->trans_start();
        $no_so = $header['no_so'];
        $no_faktur = $header['no_faktur'];

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', $header);

        $this->db->delete('tbso_sales_order_detail', ['no_so' => $no_so]);

        $this->db->where('no_so', $no_so);
        $this->db->update('tbso_stock_reservation', ['status' => 'released']);

        foreach ($details as $d) {
            $d['no_so']    = $no_so;
            $d['no_faktur'] = $no_faktur;
            $this->db->insert('tbso_sales_order_detail', $d);
            $id_detail = $this->db->insert_id();
            $exp_ddmmyyyy = $this->_toViewDate($d['expired_date']);

            $this->db->insert('tbso_stock_reservation', [
                'no_so'        => $no_so,
                'no_faktur'    => $no_faktur,
                'id_so_detail' => $id_detail,
                'kd_barang'    => $d['kd_barang'],
                'exp_date'     => $this->_normalizeDate($d['expired_date']),
                'no_lot'       => $d['no_lot'],
                'gudang_id'    => $header['gudang_id'],
                'qty_reserved' => $d['qty'],
                'status'       => 'active',
            ]);
        }

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['jumlah_item' => count($details)]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ----------------------------------------------------------------
    // VALIDASI STOK
    // ----------------------------------------------------------------
    public function validasi_stok($details, $gudang_id, $exclude_no_so = null)
    {
        $errors = [];
        foreach ($details as $d) {
            $stock     = $this->cek_stock($d['kd_barang'], $d['expired_date'], $gudang_id);
            $available = $stock ? (float)$stock['available_stock'] : 0;

            if ($exclude_no_so) {
                $exp_ddmmyyyy = $this->_toViewDate($d['expired_date']);
                $this->db->select('SUM(qty_reserved) as qty');
                $this->db->where('no_so', $exclude_no_so); // ← no_so
                $this->db->where('kd_barang', $d['kd_barang']);
                $this->db->where('exp_date', $this->_normalizeDate($d['expired_date']));
                $this->db->where('status', 'active');
                $res       = $this->db->get('tbso_stock_reservation')->row_array();
                $available += $res ? (float)$res['qty'] : 0;
            }

            $available = round($available, 3);
            $diminta   = round((float)$d['qty'], 3);

            if ($diminta > $available) {
                $isi      = max(1, (int)($d['isi_per_box'] ?? 1));
                $av_box   = (int)floor($available / $isi);
                $av_ecer  = (int)fmod($available, $isi);
                $req_box  = (int)($d['qty_box']    ?? 0);
                $req_ecer = (int)($d['qty_satuan'] ?? 0);

                $errors[] = "Stok tidak cukup: <b>{$d['nama_barang']}</b> "
                        . "(Exp: {$d['expired_date']}) — "
                        . "Diminta: {$req_box} box + {$req_ecer} pcs = {$diminta} pcs, "
                        . "Tersedia: {$av_box} box + {$av_ecer} pcs = {$available} pcs";
            }
        }
        return $errors;
    }
    // ----------------------------------------------------------------
    // VALIDASI TONASE + KUBIKASI
    // ----------------------------------------------------------------
    public function validasi_tonase_kubikasi($details,
        $batas_tonase   = self::BATAS_TONASE,
        $batas_kubikasi = self::BATAS_KUBIKASI)
    {
        $batas_tonase   = ($batas_tonase   > 0) ? (float)$batas_tonase   : self::BATAS_TONASE;
        $batas_kubikasi = ($batas_kubikasi > 0) ? (float)$batas_kubikasi : self::BATAS_KUBIKASI;

        $total_tonase = 0; $total_kubikasi = 0;
        foreach ($details as $d) {
            $qty = (float)($d['qty'] ?? 0);
            $total_tonase   += $qty * ((float)($d['berat_gram']  ?? 0) / 1000000);
            $total_kubikasi += $qty *  (float)($d['kubikasi_m3'] ?? 0);
        }

        $total_tonase   = round($total_tonase,   6);
        $total_kubikasi = round($total_kubikasi, 6);
        $warnings = [];

        if ($total_tonase > $batas_tonase && $total_kubikasi <= $batas_kubikasi)
            $warnings[] = "Tonase melebihi batas (".round($total_tonase,3)." ton &gt; {$batas_tonase} ton).";
        elseif ($total_kubikasi > $batas_kubikasi && $total_tonase <= $batas_tonase)
            $warnings[] = "Kubikasi melebihi batas (".round($total_kubikasi,4)." m³ &gt; {$batas_kubikasi} m³).";
        elseif ($total_tonase > $batas_tonase && $total_kubikasi > $batas_kubikasi)
            $warnings[] = "Tonase (".round($total_tonase,3)." ton) DAN kubikasi (".round($total_kubikasi,4)." m³) melebihi batas!";

        return [
            'total_tonase'   => $total_tonase,
            'total_kubikasi' => $total_kubikasi,
            'batas_tonase'   => $batas_tonase,
            'batas_kubikasi' => $batas_kubikasi,
            'warnings'       => $warnings,
        ];
    }

    // ----------------------------------------------------------------
    // APPROVAL NEGO
    // ----------------------------------------------------------------
    public function simpan_request_approval_nego($no_so, $req_by, $no_faktur = '')
    {
        $ada = $this->db->get_where('tbso_approval_nego', ['no_so'=>$no_so,'status'=>'pending'])->row_array();
        if (!$ada) $this->db->insert('tbso_approval_nego', [
            'no_so'     => $no_so,
            'no_faktur' => $no_faktur,
            'status'    => 'pending',
            'req_by'    => $req_by,
        ]);
        $this->db->where('no_so', $no_so);
        $this->db->update('tbso_sales_order', ['status' => 'waiting_approval']);
    }

    public function get_pending_approval()
    {
        $this->db->select('an.*, so.customer_name, so.tanggal_transaksi, so.total_tonase, so.total_kubikasi');
        $this->db->from('tbso_approval_nego an');
        $this->db->join('tbso_sales_order so', 'so.no_so = an.no_so', 'left');
        $this->db->where('an.status', 'pending');
        $this->db->order_by('an.req_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function proses_approval_nego($id, $status, $note, $act_by)
    {
        $this->db->where('id', $id);
        $this->db->update('tbso_approval_nego', ['status'=>$status,'note'=>$note,'act_by'=>$act_by,'act_at'=>date('Y-m-d H:i:s')]);
        $row = $this->db->get_where('tbso_approval_nego', ['id'=>$id])->row_array();
        if ($row) {
            $this->db->where('no_so', $row['no_so']);
            $this->db->update('tbso_sales_order', ['status' => ($status==='approved')?'approved':'draft']);
        }
    }

    // ----------------------------------------------------------------
    // UPDATE STATUS SO
    // ----------------------------------------------------------------
    public function update_status($id_so, $status, $update_by)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['status' => $status, 'update_by' => $update_by]);

        if ($status === 'cancelled' && $so) {
            $this->db->where('no_so', $so['no_so']);
            $this->db->update('tbso_stock_reservation', ['status' => 'released']);
        }
    }

    // ----------------------------------------------------------------
    // PARTIAL DELIVERY
    // ----------------------------------------------------------------
    public function update_qty_delivered($id_so_detail, $qty_delivered)
    {
        $this->db->where('id', $id_so_detail);
        $this->db->update('tbso_sales_order_detail', ['qty_delivered' => $qty_delivered]);
        $detail = $this->db->get_where('tbso_sales_order_detail', ['id'=>$id_so_detail])->row_array();
        if ($detail) {
            $all = $this->db->get_where('tbso_sales_order_detail', ['id_so'=>$detail['id_so']])->result_array();
            $all_done = true; $any_done = false;
            foreach ($all as $item) {
                if ($item['qty_delivered'] >= $item['qty']) $any_done = true;
                else $all_done = false;
            }
            if ($all_done) {
                $this->update_status($detail['id_so'], 'completed', 'system');
                $this->db->where('id_so', $detail['id_so']);
                $this->db->update('tbso_stock_reservation', ['status' => 'released']);
            } elseif ($any_done) {
                $this->update_status($detail['id_so'], 'partial_delivered', 'system');
            }
        }
    }
}