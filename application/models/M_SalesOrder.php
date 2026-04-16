<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_SalesOrder extends CI_Model
{
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
    // STOK — Available Stock = Stock On Hand - Stock Reserved
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
        // Gunakan query manual agar tipe data tidak ambigu
        $sql = "SELECT * FROM v_available_stock 
                WHERE kode_barang = ? 
                AND DATE(exp_date) = DATE(?) 
                AND gudang = ?
                LIMIT 1";
        
        $query = $this->db->query($sql, [
            $kd_barang,
            $exp_date,
            $gudang_id
        ]);
        
        return $query->row_array();
    }

    // ----------------------------------------------------------------
    // MASTER BARANG
    // ----------------------------------------------------------------
    public function get_harga_pokok($kd_barang)
    {
        $row = $this->db->get_where('tb_master_barang', ['kd_barang' => $kd_barang])->row_array();
        return $row ? (float)$row['hrg_pokok'] : 0;
    }

    public function get_detail_barang($kd_barang)
    {
        return $this->db->get_where('tb_master_barang', ['kd_barang' => $kd_barang])->row_array();
    }

    // ----------------------------------------------------------------
    // LIST SALES ORDER
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
    // SIMPAN SO (HEADER + DETAIL + RESERVASI STOK) — Transaction
    // ----------------------------------------------------------------
    public function simpan_so($header, $details)
    {
        $this->db->trans_start();

        // 1. Insert header
        $this->db->insert('tbso_sales_order', $header);

        // 2. Insert detail & reservasi stok per item
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

        // 3. Update jumlah_item
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

        // Hapus detail & reservasi lama
        $this->db->delete('tbso_sales_order_detail', ['id_so' => $id_so]);
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_stock_reservation', ['status' => 'released']);

        // Insert ulang
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
    // VALIDASI STOK (server-side)
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

            // Gunakan ROUND agar tidak ada floating point mismatch
            $available = round($available, 3);
            $diminta   = round((float)$d['qty'], 3);

            if ($diminta > $available) {
                $errors[] = "Stok tidak cukup: <b>{$d['nama_barang']}</b> "
                        . "(Exp: {$d['expired_date']}) — "
                        . "Diminta: {$diminta}, Tersedia: {$available}";
            }
        }
        return $errors;
    }

    // ----------------------------------------------------------------
    // VALIDASI TONASE & KUBIKASI
    // ----------------------------------------------------------------
    public function validasi_tonase_kubikasi($details, $batas_tonase, $batas_kubikasi)
    {
        $total_tonase   = 0;
        $total_kubikasi = 0;
        $warnings       = [];

        foreach ($details as $d) {
            $total_tonase   += (float)$d['tonase_satuan']   * (float)$d['qty'];
            $total_kubikasi += (float)$d['kubikasi_satuan'] * (float)$d['qty'];
        }

        if ($batas_tonase > 0 && $batas_kubikasi > 0) {
            $over_ton = $total_tonase   > $batas_tonase;
            $over_kub = $total_kubikasi > $batas_kubikasi;

            if ($over_ton && !$over_kub) {
                $warnings[] = "Tonase melebihi batas ({$total_tonase} kg &gt; {$batas_tonase} kg), kubikasi masih aman.";
            } elseif ($over_kub && !$over_ton) {
                $warnings[] = "Kubikasi melebihi batas ({$total_kubikasi} m³ &gt; {$batas_kubikasi} m³), tonase masih aman.";
            } elseif ($over_ton && $over_kub) {
                $warnings[] = "Tonase ({$total_tonase} kg) DAN kubikasi ({$total_kubikasi} m³) melebihi batas!";
            }
        }

        return [
            'total_tonase'   => $total_tonase,
            'total_kubikasi' => $total_kubikasi,
            'warnings'       => $warnings,
        ];
    }

    // ----------------------------------------------------------------
    // APPROVAL NEGO
    // ----------------------------------------------------------------
    public function simpan_request_approval_nego($id_so, $req_by)
    {
        // Cek apakah sudah ada request pending
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
        $this->db->update('tbso_sales_order', [
            'status'    => $status,
            'update_by' => $update_by,
        ]);

        // Jika cancelled → release semua reservasi
        if ($status === 'cancelled') {
            $this->db->where('id_so', $id_so);
            $this->db->update('tbso_stock_reservation', ['status' => 'released']);
        }
    }

    // ----------------------------------------------------------------
    // PARTIAL DELIVERY: update qty_delivered per item
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