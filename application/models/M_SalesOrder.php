<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_SalesOrder.php
 *
 * Konversi:
 *   Tonase (ton)   = qty_kecil × berat_gram / 1.000.000
 *   Kubikasi (m³)  = qty_kecil × kubikasi_m3
 *   isi_per_box    = (p×l×t cm³) / (kubikasi_m3 × 1.000.000)
 *   qty_kecil      = qty_box × isi_per_box
 *
 * Batas default: 6 ton, 9 m³
 */
class M_SalesOrder extends CI_Model
{
    const BATAS_TONASE   = 6;
    const BATAS_KUBIKASI = 9;

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
    // STOK (dari view — tidak diubah strukturnya)
    // ----------------------------------------------------------------
    public function get_available_stock($gudang_id = null, $kd_barang = null)
    {
        $this->db->where('available_stock >', 0);
        if ($gudang_id) $this->db->where('gudang', $gudang_id);
        if ($kd_barang) $this->db->where('kode_barang', $kd_barang);
        return $this->db->get('v_available_stock')->result_array();
    }

    public function cek_stock($kd_barang, $exp_date, $gudang_id)
    {
        $sql = "SELECT * FROM v_available_stock
                WHERE kode_barang = ?
                AND DATE(exp_date) = DATE(?)
                AND gudang = ?
                LIMIT 1";
        return $this->db->query($sql, [$kd_barang, $exp_date, $gudang_id])->row_array();
    }

    // ----------------------------------------------------------------
    // MASTER BARANG — normalisasi kolom fleksibel
    // Mendukung berbagai nama kolom yang mungkin ada di tabel Anda.
    // ----------------------------------------------------------------

    /**
     * Kembalikan satu baris master barang yang sudah dinormalisasi.
     */
    public function get_detail_barang($kd_barang)
    {
        $row = $this->db->get_where('tb_master_barang_all', ['kd_barang' => $kd_barang])->row_array();
        if (!$row) return null;
        return $this->_normalize_barang($row);
    }

    /**
     * Bulk fetch master barang berdasarkan daftar kd_barang.
     * Mengembalikan array berindeks kd_barang.
     */
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
     * Normalisasi satu baris master barang.
     * Coba beberapa nama kolom alternatif agar tidak error
     * meski nama kolom berbeda-beda di tiap instalasi.
     */
    private function _normalize_barang(array $row)
    {
        // ---- berat (gram per satuan kecil) ----
        $berat = 0;
        foreach (['berat', 'berat_gram', 'weight', 'berat_satuan', 'gr'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $berat = (float)$row[$c]; break;
            }
        }

        // ---- kubikasi (m³ per satuan kecil) ----
        $kubikasi = 0;
        foreach (['kubikasi', 'kubikasi_m3', 'volume', 'kubik', 'cbm'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $kubikasi = (float)$row[$c]; break;
            }
        }

        // ---- HPP ----
        $hpp = 0;
        foreach (['hpp', 'harga_pokok', 'cost', 'cogs', 'h_pokok'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $hpp = (float)$row[$c]; break;
            }
        }

        // ---- dimensi box (cm) ----
        $p = (float)($row['p'] ?? $row['panjang'] ?? $row['length'] ?? 0);
        $l = (float)($row['l'] ?? $row['lebar']   ?? $row['width']  ?? 0);
        $t = (float)($row['t'] ?? $row['tinggi']  ?? $row['height'] ?? 0);

        // ---- isi per box ----
        $isi = 0;
        foreach (['isi_box', 'qty_isi', 'isi', 'isi_per_box', 'qty_per_box', 'jumlah_isi'] as $c) {
            if (array_key_exists($c, $row) && (int)$row[$c] > 0) {
                $isi = (int)$row[$c]; break;
            }
        }
        // Hitung dari dimensi jika kolom isi tidak ada
        if ($isi < 1 && $p > 0 && $l > 0 && $t > 0 && $kubikasi > 0) {
            $vol_box   = $p * $l * $t;              // cm³
            $vol_kecil = $kubikasi * 1000000;        // m³ → cm³
            $isi = (int)round($vol_box / $vol_kecil);
        }
        if ($isi < 1) $isi = 1;

        // Tulis kembali nilai yang sudah dinormalisasi
        $row['berat_gram']  = $berat;
        $row['kubikasi_m3'] = $kubikasi;
        $row['hpp']         = $hpp;
        $row['p']           = $p;
        $row['l']           = $l;
        $row['t']           = $t;
        $row['isi_per_box'] = $isi;

        return $row;
    }

    // ----------------------------------------------------------------
    // STOK + DIMENSI — untuk endpoint AJAX /get_stock
    //
    // Dua query terpisah (aman, tidak perlu kolom di view):
    //   1. Ambil semua stok dari v_available_stock
    //   2. Ambil master barang (bulk IN query)
    //   3. Gabungkan di PHP
    // ----------------------------------------------------------------
    public function get_available_stock_with_dimensi($gudang_id = null, $kd_barang = null)
    {
        // -- Query 1: stok --
        $this->db->where('available_stock >', 0);
        if ($gudang_id) $this->db->where('gudang', $gudang_id);
        if ($kd_barang) $this->db->where('kode_barang', $kd_barang);
        $stocks = $this->db->get('v_available_stock')->result_array();

        if (empty($stocks)) return [];

        // -- Query 2: master barang (bulk) --
        $kd_list = array_column($stocks, 'kode_barang');
        $master  = $this->_get_master_bulk($kd_list);

        // -- Gabungkan --
        foreach ($stocks as &$row) {
            $kd  = $row['kode_barang'];
            $m   = isset($master[$kd]) ? $master[$kd] : [];

            $row['berat_gram']  = isset($m['berat_gram'])  ? $m['berat_gram']  : 0;
            $row['kubikasi_m3'] = isset($m['kubikasi_m3']) ? $m['kubikasi_m3'] : 0;
            $row['hpp']         = isset($m['hpp'])         ? $m['hpp']         : 0;
            $row['p']           = isset($m['p'])           ? $m['p']           : 0;
            $row['l']           = isset($m['l'])           ? $m['l']           : 0;
            $row['t']           = isset($m['t'])           ? $m['t']           : 0;
            $row['isi_per_box'] = isset($m['isi_per_box']) ? $m['isi_per_box'] : 1;

            $av  = (float)($row['available_stock'] ?? 0);
            $isi = max(1, (int)$row['isi_per_box']);
            $row['available_box'] = (int)floor($av / $isi);
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
                'exp_date'     => $d['expired_date'],
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
                'exp_date'     => $d['expired_date'],
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
                $this->db->where('DATE(exp_date)', date('Y-m-d', strtotime($d['expired_date'])));
                $this->db->where('status', 'active');
                $res       = $this->db->get('tbso_stock_reservation')->row_array();
                $available += $res ? (float)$res['qty'] : 0;
            }
            $available  = round($available, 3);
            $diminta    = round((float)$d['qty'], 3);
            if ($diminta > $available) {
                $isi      = max(1, (int)($d['isi_per_box'] ?? 1));
                $av_box   = (int)floor($available / $isi);
                $req_box  = (int)ceil($diminta   / $isi);
                $errors[] = "Stok tidak cukup: <b>{$d['nama_barang']}</b> "
                          . "(Exp: {$d['expired_date']}) — "
                          . "Diminta: {$req_box} box ({$diminta} pcs), "
                          . "Tersedia: {$av_box} box ({$available} pcs)";
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

        $total_tonase   = 0;
        $total_kubikasi = 0;

        foreach ($details as $d) {
            $qty         = (float)($d['qty']         ?? 0); // satuan kecil
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

        if ($over_ton && !$over_kub) {
            $warnings[] = "Tonase melebihi batas (" . round($total_tonase, 3)
                        . " ton &gt; {$batas_tonase} ton), kubikasi masih aman.";
        } elseif ($over_kub && !$over_ton) {
            $warnings[] = "Kubikasi melebihi batas (" . round($total_kubikasi, 4)
                        . " m³ &gt; {$batas_kubikasi} m³), tonase masih aman.";
        } elseif ($over_ton && $over_kub) {
            $warnings[] = "Tonase (" . round($total_tonase, 3) . " ton) DAN "
                        . "kubikasi (" . round($total_kubikasi, 4) . " m³) melebihi batas!";
        }

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
        $ada = $this->db->get_where('tbso_approval_nego', [
            'id_so'  => $id_so,
            'status' => 'pending',
        ])->row_array();
        if (!$ada) {
            $this->db->insert('tbso_approval_nego', [
                'id_so'  => $id_so,
                'status' => 'pending',
                'req_by' => $req_by,
            ]);
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
            'status' => $status,
            'note'   => $note,
            'act_by' => $act_by,
            'act_at' => date('Y-m-d H:i:s'),
        ]);
        $row = $this->db->get_where('tbso_approval_nego', ['id' => $id])->row_array();
        if ($row) {
            $new_status = ($status === 'approved') ? 'approved' : 'draft';
            $this->db->where('id_so', $row['id_so']);
            $this->db->update('tbso_sales_order', ['status' => $new_status]);
        }
    }

    // ----------------------------------------------------------------
    // UPDATE STATUS SO
    // ----------------------------------------------------------------
    public function update_status($id_so, $status, $update_by)
    {
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['status' => $status, 'update_by' => $update_by]);
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
            $all      = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $detail['id_so']])->result_array();
            $all_done = true;
            $any_done = false;
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