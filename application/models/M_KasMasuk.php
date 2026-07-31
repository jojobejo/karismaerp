<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_KasMasuk extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function generate_ref_no()
    {
        $dateStr = date('dmy'); // DDMMYY
        $prefix = 'AKM' . $dateStr;
        
        $this->db->select('no_referensi');
        $this->db->like('no_referensi', $prefix, 'after');
        $this->db->order_by('no_referensi', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('tbkeu_kas_masuk');

        $next_num = 1;
        if ($query->num_rows() > 0) {
            $last_no = $query->row()->no_referensi;
            // Format: AKMddmmyy-XX
            $parts = explode('-', $last_no);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $next_num = (int)$parts[1] + 1;
            }
        }

        return $prefix . '-' . sprintf('%02d', $next_num);
    }

    public function get_all($filters = [], $limit = 250)
    {
        $this->db->select('km.*, a.nama_akun as nama_akun_kas, a.kode_akun as kode_akun_kas');
        $this->db->from('tbkeu_kas_masuk km');
        $this->db->join('tbkeu_akun a', 'a.id_akun = km.id_akun_kas', 'left');

        if (!empty($filters['date_from'])) {
            $this->db->where('km.tanggal >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('km.tanggal <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('km.no_referensi', $filters['search']);
            $this->db->or_like('km.diterima_dari', $filters['search']);
            $this->db->or_like('km.memo', $filters['search']);
            $this->db->group_end();
        }
        if (!empty($filters['status']) && $filters['status'] !== 'Semua') {
            $this->db->where('km.status', $filters['status']);
        }

        $this->db->order_by('km.tanggal', 'DESC');
        $this->db->order_by('km.id_kas_masuk', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->select('km.*, a.nama_akun as nama_akun_kas, a.kode_akun as kode_akun_kas');
        $this->db->from('tbkeu_kas_masuk km');
        $this->db->join('tbkeu_akun a', 'a.id_akun = km.id_akun_kas', 'left');
        $this->db->where('km.id_kas_masuk', $id);
        $header = $this->db->get()->row_array();

        if ($header) {
            $this->db->select('d.*, a.kode_akun, a.nama_akun');
            $this->db->from('tbkeu_kas_masuk_detail d');
            $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
            $this->db->where('d.id_kas_masuk', $id);
            $this->db->order_by('d.nomor_baris', 'ASC');
            $header['details'] = $this->db->get()->result_array();
        }

        return $header;
    }

    public function save($data, $details)
    {
        $this->db->trans_start();

        $id = isset($data['id_kas_masuk']) ? (int)$data['id_kas_masuk'] : 0;

        if ($id > 0) {
            $this->db->where('id_kas_masuk', $id)->update('tbkeu_kas_masuk', $data);
            $this->db->where('id_kas_masuk', $id)->delete('tbkeu_kas_masuk_detail');
        } else {
            $this->db->insert('tbkeu_kas_masuk', $data);
            $id = $this->db->insert_id();
        }

        $baris = 1;
        foreach ($details as $detail) {
            $this->db->insert('tbkeu_kas_masuk_detail', [
                'id_kas_masuk' => $id,
                'id_akun' => $detail['id_akun'],
                'nilai' => $detail['nilai'],
                'nomor_baris' => $baris++
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id : false;
    }

    public function post_to_journal($id_kas_masuk, $userId = null)
    {
        $this->load->library('Accounting_service');
        $data = $this->get_by_id($id_kas_masuk);

        if (!$data || $data['status'] === 'POSTED') {
            return false;
        }

        $this->db->trans_start();

        // Prepare Jurnal Lines
        $lines = [];
        
        // Debit Side: the source cash/bank account receiving the funds
        $lines[] = [
            'id_akun' => $data['id_akun_kas'],
            'keterangan' => $data['memo'] ?: ('Kas Masuk via ' . $data['nama_akun_kas']),
            'debit' => $data['total_amount'],
            'kredit' => 0,
            'nomor_dokumen' => $data['no_referensi']
        ];

        // Credit Side: the allocated detail accounts
        foreach ($data['details'] as $detail) {
            $lines[] = [
                'id_akun' => $detail['id_akun'],
                'keterangan' => $data['memo'] ?: 'Alokasi Pendapatan/Penerimaan Kas Masuk',
                'debit' => 0,
                'kredit' => $detail['nilai'],
                'nomor_dokumen' => $data['no_referensi']
            ];
        }

        // Insert Journal using Accounting_service
        $payload = [
            'nomor_jurnal' => $data['no_referensi'],
            'tanggal_transaksi' => $data['tanggal'],
            'keterangan' => $data['memo'] ?: ('Penerimaan dari ' . $data['diterima_dari']),
            'journal_type' => 'CR', // Cash Receipt
            'source_module' => 'KEUANGAN',
            'source_type' => 'KAS_MASUK',
            'source_id' => $id_kas_masuk,
            'source_no' => $data['no_referensi'],
            'lines' => $lines
        ];

        $journal_res = $this->accounting_service->create_manual_journal($payload, $userId);
        if (!$journal_res['success']) {
            $this->db->trans_rollback();
            return $journal_res;
        }

        $id_jurnal = $journal_res['data']['id_jurnal'];
        $post_res = $this->accounting_service->post_manual_journal($id_jurnal, $userId);
        if (!$post_res['success']) {
            $this->db->trans_rollback();
            return $post_res;
        }

        // Update status to POSTED
        $this->db->where('id_kas_masuk', $id_kas_masuk)->update('tbkeu_kas_masuk', [
            'status' => 'POSTED',
            'id_jurnal' => $id_jurnal,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();
        return ['success' => $this->db->trans_status(), 'message' => 'Jurnal berhasil diposting.'];
    }

    public function delete($id)
    {
        $data = $this->get_by_id($id);
        if (!$data) return false;

        $this->db->trans_start();

        // If it was posted, reverse/delete the journal
        if ($data['status'] === 'POSTED' && !empty($data['id_jurnal'])) {
            $this->load->library('Accounting_service');
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal_detail');
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal');
            $this->db->where('id_jurnal', $data['id_jurnal'])->delete('tbkeu_jurnal_log');
        }

        $this->db->where('id_kas_masuk', $id)->delete('tbkeu_kas_masuk');

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function terbilang($nilai)
    {
        $nilai = abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[$nilai];
        } else if ($nilai < 20) {
            $temp = $this->terbilang($nilai - 10) . " belas";
        } else if ($nilai < 100) {
            $temp = $this->terbilang((int)($nilai / 10)) . " puluh" . $this->terbilang($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . $this->terbilang($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = $this->terbilang((int)($nilai / 100)) . " ratus" . $this->terbilang($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . $this->terbilang($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = $this->terbilang((int)($nilai / 1000)) . " ribu" . $this->terbilang($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = $this->terbilang((int)($nilai / 1000000)) . " juta" . $this->terbilang($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = $this->terbilang((int)($nilai / 1000000000)) . " milyar" . $this->terbilang(fmod($nilai, 1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = $this->terbilang((int)($nilai / 1000000000000)) . " trilyun" . $this->terbilang(fmod($nilai, 1000000000000));
        }
        
        $spelled = trim($temp);
        return empty($spelled) ? 'Nol' : ucwords($spelled) . ' Rupiah';
    }
}
