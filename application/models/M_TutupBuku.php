<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_TutupBuku extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Mengambil daftar periode fiskal dengan metadata closing dan validasi
     */
    public function get_fiscal_periods_with_closing_status()
    {
        $this->db->select("
            p.*,
            (SELECT COUNT(*) FROM tbkeu_jurnal j WHERE j.id_periode = p.id_periode AND j.status = 'POSTED') as total_jurnal_posted,
            (SELECT COUNT(*) FROM tbkeu_jurnal j WHERE j.id_periode = p.id_periode AND j.status = 'DRAFT') as total_jurnal_draft,
            c.id_closing,
            c.closing_version,
            c.tipe_closing as last_closing_type,
            c.status as closing_status,
            c.checksum,
            c.total_laba_bersih_periode,
            c.created_at as closing_executed_at,
            vr.id_validation_run,
            vr.status_overall as last_val_status,
            vr.blocking_count as last_val_blocking,
            vr.warning_count as last_val_warning,
            vr.run_at as last_val_run_at
        ");
        $this->db->from('tbkeu_periode_fiskal p');
        $this->db->join(
            '(SELECT * FROM tbkeu_closing_period c1 WHERE c1.id_closing = (
                SELECT MAX(c2.id_closing) FROM tbkeu_closing_period c2 WHERE c2.id_periode = c1.id_periode
            )) c',
            'c.id_periode = p.id_periode',
            'left'
        );
        $this->db->join(
            '(SELECT * FROM tbkeu_closing_validation_run vr1 WHERE vr1.id_validation_run = (
                SELECT MAX(vr2.id_validation_run) FROM tbkeu_closing_validation_run vr2 WHERE vr2.id_periode = vr1.id_periode
            )) vr',
            'vr.id_periode = p.id_periode',
            'left'
        );
        $this->db->order_by('p.tanggal_mulai', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Mengambil detail satu periode fiskal
     */
    public function get_period_by_id($idPeriode)
    {
        return $this->db->where('id_periode', (int)$idPeriode)->get('tbkeu_periode_fiskal')->row();
    }

    /**
     * Mengambil riwayat run validasi untuk periode
     */
    public function get_validation_run_history($idPeriode, $limit = 10)
    {
        $this->db->select('vr.*, COALESCE(u.nama_lngkp, u.username) as run_by_name');
        $this->db->from('tbkeu_closing_validation_run vr');
        $this->db->join('tb_users u', 'u.id = vr.run_by', 'left');
        $this->db->where('vr.id_periode', (int)$idPeriode);
        $this->db->order_by('vr.id_validation_run', 'DESC');
        $this->db->limit((int)$limit);
        return $this->db->get()->result();
    }

    /**
     * Mengambil log detail dari suatu run validasi
     */
    public function get_validation_logs_by_run($idValidationRun)
    {
        return $this->db
            ->where('id_validation_run', (int)$idValidationRun)
            ->order_by('tingkat_keparahan', 'ASC')
            ->order_by('status', 'DESC')
            ->get('tbkeu_closing_validation_log')
            ->result();
    }

    /**
     * Mengambil ringkasan widget dashboard tutup buku
     */
    public function get_closing_dashboard_metrics()
    {
        $totalPeriods = $this->db->count_all_results('tbkeu_periode_fiskal');
        $openPeriods = $this->db->where('status', 'OPEN')->count_all_results('tbkeu_periode_fiskal');
        $closedPeriods = $this->db->where('status', 'CLOSED')->count_all_results('tbkeu_periode_fiskal');
        $pendingReopen = $this->db->where('status_approval', 'PENDING')->count_all_results('tbkeu_reopen_request');

        return [
            'total_periods' => $totalPeriods,
            'open_periods' => $openPeriods,
            'closed_periods' => $closedPeriods,
            'pending_reopen' => $pendingReopen,
        ];
    }
}
