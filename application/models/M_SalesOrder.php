<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_SalesOrder.php
 *
 * ISI PER BOX = p × l × t (INT kolom master barang)
 *
 * GUDANG: v_available_stock.gudang = wilayah_id dari tb_saldo_awal
 *   → integer, bukan string nama gudang.
 *
 * FORMAT exp_date di view: DD/MM/YYYY (varchar/text)
 *   → dikonversi ke YYYY-MM-DD untuk form/JS, dan balik ke DD/MM/YYYY
 *     saat query ke view.
 *
 * STOK berkurang: v_available_stock sudah JOIN ke tbso_stock_reservation.
 *   Agar stok berkurang, gudang_id di reservasi harus cocok dengan
 *   kolom gudang_id di tbso_stock_reservation yang di-JOIN ke view.
 */
class M_SalesOrder extends CI_Model
{
    const BATAS_TONASE   = 6;
    const BATAS_KUBIKASI = 9;

    // ----------------------------------------------------------------
    // HELPER: YYYY-MM-DD ↔ DD/MM/YYYY
    // ----------------------------------------------------------------
    private function _normalizeDate($raw)
    {
        $raw = trim((string)$raw);
        if (!$raw) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $raw, $m))
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        return $raw;
    }

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
    public function generate_no_so()
    {
        $prefix = 'SO/' . date('Ym') . '/';
        $this->db->like('id_so', $prefix, 'after');
        $this->db->order_by('id_so', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get('tbso_sales_order')->row();
        if ($row) {
            $last = (int) substr($row->id_so, -4);
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
     * - exp_date dikonversi ke DD/MM/YYYY (format kolom di view)
     * - gudang kosong → SUM semua gudang (validasi tidak gagal saat session belum set)
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
            // Gudang tidak diketahui → ambil total semua gudang
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
        foreach ($rows as $r) {
            $map[$r['kd_barang']] = $this->_normalize_barang($r);
        }
        return $map;
    }

    /**
     * Normalisasi master barang.
     * - satuan   : dari kolom 'satuan' di tb_master_barang_all
     * - isi_per_box: kolom eksplisit → p × l × t → fallback 1
     */
    private function _normalize_barang(array $row)
    {
        // berat (gram per satuan kecil)
        $berat = 0;
        foreach (['berat','berat_gram','weight','berat_satuan','gr'] as $c) {
            if (array_key_exists($c,$row) && $row[$c]!==null && $row[$c]!=='') { $berat=(float)$row[$c]; break; }
        }

        // kubikasi (m³ per satuan kecil)
        $kubikasi = 0;
        foreach (['kubikasi','kubikasi_m3','volume','kubik','cbm'] as $c) {
            if (array_key_exists($c,$row) && $row[$c]!==null && $row[$c]!=='') { $kubikasi=(float)$row[$c]; break; }
        }

        // HPP
        $hpp = 0;
        foreach (['hpp','harga_pokok','cost','cogs','h_pokok'] as $c) {
            if (array_key_exists($c,$row) && $row[$c]!==null && $row[$c]!=='') { $hpp=(float)$row[$c]; break; }
        }

        // Dimensi INT
        $p = (int)($row['p'] ?? $row['panjang'] ?? $row['length'] ?? 0);
        $l = (int)($row['l'] ?? $row['lebar']   ?? $row['width']  ?? 0);
        $t = (int)($row['t'] ?? $row['tinggi']  ?? $row['height'] ?? 0);

        // isi_per_box: kolom eksplisit → p×l×t → 1
        $isi = 0;
        foreach (['isi_box','qty_isi','isi','isi_per_box','qty_per_box','jumlah_isi'] as $c) {
            if (array_key_exists($c,$row) && (int)$row[$c]>0) { $isi=(int)$row[$c]; break; }
        }
        if ($isi < 1 && $p > 0 && $l > 0 && $t > 0) $isi = $p * $l * $t;
        if ($isi < 1) $isi = 1;

        // Satuan dari kolom 'satuan' di tb_master_barang_all
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
        $row['satuan']      = $satuan;  // ← pastikan satuan ikut

        return $row;
    }

    // ----------------------------------------------------------------
    // STOK + DIMENSI — endpoint AJAX /get_stock
    //
    // exp_date dikonversi DD/MM/YYYY → YYYY-MM-DD untuk form/JS.
    // satuan diambil dari master barang (JOIN di PHP, bukan di SQL view).
    // gudang di v_available_stock = wilayah_id (integer).
    // ----------------------------------------------------------------
    public function get_available_stock_with_dimensi($gudang_id = null, $kd_barang = null)
    {
        $this->db->where('available_stock >', 0);
        if (!empty($gudang_id)) $this->db->where('gudang', $gudang_id);
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

            // Satuan dari master (view tidak punya kolom satuan)
            $row['satuan'] = $m['satuan'] ?? ($row['satuan'] ?? '');

            // Konversi exp_date DD/MM/YYYY → YYYY-MM-DD
            $row['exp_date'] = $this->_normalizeDate($row['exp_date'] ?? '');

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
        $this->db->select('so.*, c.nama_customer, c.alamat as customer_alamat, c.telepon as customer_tlp');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.id = so.customer_id', 'left');
        $this->db->where('so.id_so', $id_so);
        return $this->db->get()->row_array();
    }

    public function get_so_detail($id_so)
    {
        return $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
    }

    // ----------------------------------------------------------------
    // SIMPAN SO
    //
    // STOK BERKURANG: v_available_stock JOIN ke tbso_stock_reservation
    //   dengan kondisi: kd_barang + exp_date + gudang_id + status='active'.
    //   Agar stok berkurang, exp_date di reservasi harus sama formatnya
    //   dengan exp_date di view (DD/MM/YYYY).
    //   → Kita simpan dalam format asli form (YYYY-MM-DD) dan biarkan
    //     view query menggunakan DATE() jika kolom reservasi bertipe DATE.
    //     Cek definisi kolom exp_date di tbso_stock_reservation Anda.
    // ----------------------------------------------------------------
    public function simpan_so($header, $details)
    {
        $this->db->trans_start();
        $this->db->insert('tbso_sales_order', $header);
        foreach ($details as $d) {
            $d['id_so'] = $header['id_so'];
            $this->db->insert('tbso_sales_order_detail', $d);
            $id_detail = $this->db->insert_id();
            $this->db->insert('tbso_stock_reservation', [
                'id_so'        => $header['id_so'],
                'id_so_detail' => $id_detail,
                'kd_barang'    => $d['kd_barang'],
                // Simpan exp_date dalam format DD/MM/YYYY agar cocok dengan view
                'exp_date'     => $this->_toViewDate($d['expired_date']),
                'no_lot'       => $d['no_lot'],
                'gudang_id'    => $header['gudang_id'],
                'qty_reserved' => $d['qty'],
                'status'       => 'active',
            ]);
        }
        $this->db->where('id_so', $header['id_so']);
        $this->db->update('tbso_sales_order', ['jumlah_item' => count($details)]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ----------------------------------------------------------------
    // UPDATE SO
    // ----------------------------------------------------------------
    public function update_so($id_so, $header, $details)
    {
        $this->db->trans_start();
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', $header);
        $this->db->delete('tbso_sales_order_detail', ['id_so' => $id_so]);
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_stock_reservation', ['status' => 'released']);
        foreach ($details as $d) {
            $d['id_so'] = $id_so;
            $this->db->insert('tbso_sales_order_detail', $d);
            $id_detail = $this->db->insert_id();
            $this->db->insert('tbso_stock_reservation', [
                'id_so'        => $id_so,
                'id_so_detail' => $id_detail,
                'kd_barang'    => $d['kd_barang'],
                'exp_date'     => $this->_toViewDate($d['expired_date']),
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
    public function validasi_stok($details, $gudang_id, $exclude_so = null)
    {
        $errors = [];
        foreach ($details as $d) {
            $stock     = $this->cek_stock($d['kd_barang'], $d['expired_date'], $gudang_id);
            $available = $stock ? (float)$stock['available_stock'] : 0;

            if ($exclude_so) {
                $this->db->select('SUM(qty_reserved) as qty');
                $this->db->where('id_so', $exclude_so);
                $this->db->where('kd_barang', $d['kd_barang']);
                // exp_date di reservasi disimpan DD/MM/YYYY
                $this->db->where('exp_date', $this->_toViewDate($d['expired_date']));
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
            $qty         = (float)($d['qty']         ?? 0);
            $berat_gram  = (float)($d['berat_gram']  ?? 0);
            $kubikasi_m3 = (float)($d['kubikasi_m3'] ?? 0);
            $total_tonase   += $qty * ($berat_gram / 1000000);
            $total_kubikasi += $qty * $kubikasi_m3;
        }

        $total_tonase   = round($total_tonase,   6);
        $total_kubikasi = round($total_kubikasi, 6);
        $warnings       = [];
        $over_ton       = $total_tonase   > $batas_tonase;
        $over_kub       = $total_kubikasi > $batas_kubikasi;

        if ($over_ton && !$over_kub)
            $warnings[] = "Tonase melebihi batas (".round($total_tonase,3)." ton &gt; {$batas_tonase} ton).";
        elseif ($over_kub && !$over_ton)
            $warnings[] = "Kubikasi melebihi batas (".round($total_kubikasi,4)." m³ &gt; {$batas_kubikasi} m³).";
        elseif ($over_ton && $over_kub)
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
    public function simpan_request_approval_nego($id_so, $req_by)
    {
        $ada = $this->db->get_where('tbso_approval_nego', ['id_so'=>$id_so,'status'=>'pending'])->row_array();
        if (!$ada) {
            $this->db->insert('tbso_approval_nego', ['id_so'=>$id_so,'status'=>'pending','req_by'=>$req_by]);
        }
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['status' => 'waiting_approval']);
    }

    public function get_pending_approval()
    {
        $this->db->select('an.*, so.customer_name, so.tanggal_transaksi, so.total_tonase, so.total_kubikasi');
        $this->db->from('tbso_approval_nego an');
        $this->db->join('tbso_sales_order so', 'so.id_so = an.id_so', 'left');
        $this->db->where('an.status', 'pending');
        $this->db->order_by('an.req_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function proses_approval_nego($id, $status, $note, $act_by)
    {
        $this->db->where('id', $id);
        $this->db->update('tbso_approval_nego', [
            'status'=>$status,'note'=>$note,'act_by'=>$act_by,'act_at'=>date('Y-m-d H:i:s')
        ]);
        $row = $this->db->get_where('tbso_approval_nego', ['id'=>$id])->row_array();
        if ($row) {
            $this->db->where('id_so', $row['id_so']);
            $this->db->update('tbso_sales_order', ['status' => ($status==='approved') ? 'approved' : 'draft']);
        }
    }

    // ----------------------------------------------------------------
    // UPDATE STATUS SO
    // ----------------------------------------------------------------
    public function update_status($id_so, $status, $update_by)
    {
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['status'=>$status,'update_by'=>$update_by]);
        if ($status === 'cancelled') {
            $this->db->where('id_so', $id_so);
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
        $detail = $this->db->get_where('tbso_sales_order_detail', ['id' => $id_so_detail])->row_array();
        if ($detail) {
            $all      = $this->db->get_where('tbso_sales_order_detail', ['id_so'=>$detail['id_so']])->result_array();
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