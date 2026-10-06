<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_DeliveryTrip extends CI_Model
{
    const STATUS_LOADING = 'PROSES_LOADING';
    const STATUS_INVOICING = 'PROSES_FAKTUR';
    const STATUS_WAITING_ADDITIONAL = 'MENUNGGU_TAMBAHAN';
    const STATUS_ADDITIONAL = 'PROSES_TAMBAHAN';
    const STATUS_CLOSED = 'DITUTUP';

    public function get($id_trip)
    {
        return $this->db->where('id_trip', (int)$id_trip)->get('tb_delivery_trip')->row_array();
    }

    public function get_active_by_route($kd_rute)
    {
        return $this->db
            ->where('kd_rute', strtoupper(trim((string)$kd_rute)))
            ->where_in('status', ['DRAFT', 'VERIFIKASI', 'SIAP_LOADING', self::STATUS_LOADING, self::STATUS_INVOICING, self::STATUS_WAITING_ADDITIONAL, self::STATUS_ADDITIONAL])
            ->order_by('id_trip', 'DESC')
            ->limit(1)
            ->get('tb_delivery_trip')
            ->row_array();
    }

    public function get_or_create($kd_rute, $tgl_pengiriman, $created_by)
    {
        $active = $this->get_active_by_route($kd_rute);
        if ($active) return $active;

        $kd_rute = strtoupper(trim((string)$kd_rute));
        $date = $tgl_pengiriman ?: date('Y-m-d');
        $this->db->insert('tb_delivery_trip', [
            'kode_trip' => 'TRIP/' . $kd_rute . '/' . date('dmy', strtotime($date)) . '/' . str_pad((string)$this->next_daily_number($date), 3, '0', STR_PAD_LEFT),
            'kd_rute' => $kd_rute,
            'tgl_pengiriman' => $date,
            'kapasitas_tonase' => 7,
            'kapasitas_kubikasi' => 9,
            'status' => 'SIAP_LOADING',
            'created_by' => $created_by ?: 'system',
        ]);
        return $this->get($this->db->insert_id());
    }

    private function next_daily_number($date)
    {
        $row = $this->db->select('COUNT(*) total')->where('tgl_pengiriman', $date)->get('tb_delivery_trip')->row_array();
        return ((int)($row['total'] ?? 0)) + 1;
    }

    public function attach_so($id_trip, $id_so, $is_additional = false)
    {
        $trip = $this->get($id_trip);
        if (!$trip || in_array($trip['status'], [self::STATUS_CLOSED, 'BERANGKAT', 'SELESAI'], true)) return false;

        $this->db->where('id_so', (int)$id_so)->update('tbso_sales_order', [
            'id_trip' => (int)$id_trip,
            'is_additional_load' => $is_additional ? 1 : 0,
        ]);
        if ($is_additional) {
            $source = $this->db->select('loading_tgl_pengiriman,loading_jenis_pengiriman,loading_driver,loading_nolambung')
                ->where('id_trip', (int)$id_trip)->where('is_additional_load', 0)
                ->where('loading_tgl_pengiriman IS NOT NULL', null, false)
                ->order_by('id_so', 'ASC')->limit(1)->get('tbso_sales_order')->row_array();
            if ($source) {
                $this->db->where('id_so', (int)$id_so)->update('tbso_sales_order', $source);
                $this->db->where('id_trip', (int)$id_trip)->update('tb_delivery_trip', [
                    'tgl_pengiriman' => $source['loading_tgl_pengiriman'],
                    'driver' => $source['loading_driver'],
                    'nolambung' => $source['loading_nolambung'],
                ]);
            }
        }
        return $this->db->affected_rows() >= 0;
    }

    public function sync_faktur($id_so, $id_faktur)
    {
        $so = $this->db->select('id_trip,is_additional_load')->where('id_so', (int)$id_so)->get('tbso_sales_order')->row_array();
        if (!$so || empty($so['id_trip'])) return true;
        return $this->db->where('id_faktur', (int)$id_faktur)->update('tbso_faktur_penjualan', [
            'id_trip' => (int)$so['id_trip'],
            'is_additional_load' => !empty($so['is_additional_load']) ? 1 : 0,
        ]);
    }

    public function open_additional($id_trip, $by, $deadline = null)
    {
        $trip = $this->get($id_trip);
        if (!$trip || in_array($trip['status'], [self::STATUS_CLOSED, 'BERANGKAT', 'SELESAI'], true)) return false;
        $this->db->trans_begin();
        $this->db->where('id_trip', (int)$id_trip)->update('tb_delivery_trip', [
            'status' => self::STATUS_WAITING_ADDITIONAL,
            'additional_load_deadline' => $deadline ?: date('Y-m-d H:i:s', strtotime('+2 hours')),
            'opened_additional_by' => $by,
            'opened_additional_at' => date('Y-m-d H:i:s'),
        ]);
        // Status 6: DO belum diberangkatkan karena masih menunggu muatan tambahan.
        $this->db->where('id_trip', (int)$id_trip)->where('status', 5)->update('tb_do', ['status' => 6]);
        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    public function close($id_trip, $by)
    {
        $this->db->trans_begin();
        $this->db->where('id_trip', (int)$id_trip)->update('tb_delivery_trip', [
            'status' => self::STATUS_CLOSED,
            'closed_by' => $by,
            'closed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('id_trip', (int)$id_trip)->where('status', 6)->update('tb_do', ['status' => 5]);
        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    public function set_status($id_trip, $status)
    {
        return $this->db->where('id_trip', (int)$id_trip)->update('tb_delivery_trip', ['status' => $status]);
    }

    public function capacity_summary($id_trip)
    {
        $trip = $this->get($id_trip);
        if (!$trip) return null;

        $row = $this->db->query("\n            SELECT\n                COALESCE(SUM(CASE WHEN sod.checker_loaded = 1 THEN sod.qty * sod.berat_gram / 1000000 ELSE 0 END), 0) loaded_tonase,\n                COALESCE(SUM(CASE WHEN sod.checker_loaded = 1 THEN sod.qty * sod.kubikasi_m3 ELSE 0 END), 0) loaded_kubikasi,\n                COALESCE(SUM(CASE WHEN sod.checker_loaded = 0 THEN sod.qty * sod.berat_gram / 1000000 ELSE 0 END), 0) reserved_tonase,\n                COALESCE(SUM(CASE WHEN sod.checker_loaded = 0 THEN sod.qty * sod.kubikasi_m3 ELSE 0 END), 0) reserved_kubikasi\n            FROM tbso_sales_order_detail sod\n            JOIN tbso_sales_order so ON so.id_so = sod.id_so\n            WHERE so.id_trip = ?\n        ", [(int)$id_trip])->row_array();

        $trip['loaded_tonase'] = (float)$row['loaded_tonase'];
        $trip['loaded_kubikasi'] = (float)$row['loaded_kubikasi'];
        $trip['reserved_tonase'] = (float)$row['reserved_tonase'];
        $trip['reserved_kubikasi'] = (float)$row['reserved_kubikasi'];
        $trip['remaining_tonase'] = max(0, (float)$trip['kapasitas_tonase'] - $trip['loaded_tonase'] - $trip['reserved_tonase']);
        $trip['remaining_kubikasi'] = max(0, (float)$trip['kapasitas_kubikasi'] - $trip['loaded_kubikasi'] - $trip['reserved_kubikasi']);
        return $trip;
    }

    public function validate_capacity($id_trip)
    {
        $summary = $this->capacity_summary($id_trip);
        if (!$summary) return ['valid' => false, 'message' => 'Trip tidak ditemukan.'];
        $used_tonase = $summary['loaded_tonase'] + $summary['reserved_tonase'];
        $used_kubikasi = $summary['loaded_kubikasi'] + $summary['reserved_kubikasi'];
        if ($used_tonase > (float)$summary['kapasitas_tonase'] + 0.000001 || $used_kubikasi > (float)$summary['kapasitas_kubikasi'] + 0.000001) {
            return ['valid' => false, 'message' => 'Muatan melebihi kapasitas trip.'];
        }
        return ['valid' => true, 'summary' => $summary];
    }
}
