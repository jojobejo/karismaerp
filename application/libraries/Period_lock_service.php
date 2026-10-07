<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Period_lock_service
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function validate_date($transactionDate)
    {
        $date = date('Y-m-d', strtotime((string)$transactionDate));
        if (!$transactionDate || $date === '1970-01-01') {
            return ['success'=>false,'message'=>'Tanggal transaksi tidak valid.','errors'=>['INVALID_TRANSACTION_DATE']];
        }
        $period = $this->CI->db->where('tanggal_mulai <=',$date)->where('tanggal_selesai >=',$date)->where('is_active',1)->get('tbkeu_periode_fiskal')->row();
        if (!$period) return ['success'=>false,'message'=>'Periode fiskal untuk tanggal transaksi belum tersedia.','errors'=>['PERIOD_NOT_FOUND']];
        if ($period->status !== 'OPEN') return ['success'=>false,'message'=>'Periode akuntansi sudah ditutup. Transaksi tidak dapat dibuat atau diubah.','errors'=>['PERIOD_CLOSED']];
        if ($this->CI->db->table_exists('tbkeu_reopen_request')) {
            $reopen = $this->CI->db->where('id_periode',(int)$period->id_periode)->where('status','APPROVED')->order_by('id_reopen','DESC')->get('tbkeu_reopen_request')->row();
            if ($reopen && (!empty($reopen->reopen_until) && strtotime($reopen->reopen_until) < time())) {
                return ['success'=>false,'message'=>'Batas waktu buka buku telah berakhir. Periode harus ditutup ulang.','errors'=>['REOPEN_EXPIRED']];
            }
        }
        return ['success'=>true,'message'=>'Periode dapat menerima transaksi.','data'=>['id_periode'=>(int)$period->id_periode]];
    }
}
