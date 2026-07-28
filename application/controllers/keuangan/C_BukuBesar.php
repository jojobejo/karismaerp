<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_BukuBesar extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }
        $this->load->database();
        $this->load->helper(['url', 'form']);
        $this->load->library('Accounting_service');
    }

    public function jurnal_umum()
    {
        $data['page_title'] = 'Buku Besar - Jurnal Umum';
        
        // Fetch accounts for general journal entry dropdowns
        $data['accounts'] = $this->db->select('id_akun, kode_akun, nama_akun')
            ->where('tipe_akun', 'POSTING')
            ->where('is_active', 1)
            ->order_by('kode_akun', 'ASC')
            ->get('tbkeu_akun')
            ->result_array();
            
        // Generate automatic reference prefix
        $dateStr = date('dmy');
        $this->db->select('nomor_jurnal');
        $this->db->like('nomor_jurnal', 'GJ-' . $dateStr, 'after');
        $this->db->order_by('nomor_jurnal', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('tbkeu_jurnal');
        
        $next_num = 1;
        if ($query->num_rows() > 0) {
            $last_no = $query->row()->nomor_jurnal;
            $parts = explode('-', $last_no);
            if (isset($parts[1])) {
                $seq_part = substr($parts[1], 6);
                if (is_numeric($seq_part)) {
                    $next_num = (int)$seq_part + 1;
                }
            }
        }
        $data['next_ref'] = 'GJ-' . $dateStr . sprintf('%05d', $next_num);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/buku_besar_jurnal_umum.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function jurnal_umum_list()
    {
        $search = $this->input->post('search');
        
        $this->db->select('j.*');
        $this->db->from('tbkeu_jurnal j');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('j.nomor_jurnal', $search);
            $this->db->or_like('j.keterangan', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $rows = $this->db->get()->result_array();
        
        foreach ($rows as &$row) {
            $row['nilai_formatted'] = 'Rp ' . number_format($row['total_debit'], 2, ',', '.');
            $row['tanggal_formatted'] = date('d/m/Y', strtotime($row['tanggal_transaksi']));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    public function jurnal_umum_store()
    {
        $post = $this->input->post();
        
        $referensi = trim((string)($post['referensi'] ?? ''));
        $tanggal = trim((string)($post['tanggal'] ?? date('Y-m-d')));
        $keterangan = trim((string)($post['keterangan'] ?? ''));
        $postNow = isset($post['post_now']) && $post['post_now'] == 1;

        if (empty($referensi)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Nomor referensi wajib diisi.']));
        }

        $lines = [];
        $totalDebit = 0;
        $totalKredit = 0;
        
        if (!empty($post['lines']) && is_array($post['lines'])) {
            foreach ($post['lines'] as $line) {
                if (empty($line['id_akun'])) continue;
                
                $debit = isset($line['debit']) ? (float)$line['debit'] : 0;
                $kredit = isset($line['kredit']) ? (float)$line['kredit'] : 0;
                
                if ($debit == 0 && $kredit == 0) continue;
                
                $totalDebit += $debit;
                $totalKredit += $kredit;

                $lines[] = [
                    'id_akun' => (int)$line['id_akun'],
                    'keterangan' => trim((string)($line['keterangan'] ?? $keterangan)),
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'nomor_dokumen' => $referensi,
                ];
            }
        }

        if (empty($lines)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Detail jurnal (akun) minimal 1 dengan nilai debit/kredit.']));
        }

        if (abs($totalDebit - $totalKredit) > 0.0001) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Jurnal tidak balance! Total Debit (' . number_format($totalDebit, 2) . ') harus sama dengan Total Kredit (' . number_format($totalKredit, 2) . ').']));
        }

        $userId = $this->session->userdata('id') ?: null;

        $payload = [
            'nomor_jurnal' => $referensi,
            'tanggal_transaksi' => $tanggal,
            'keterangan' => $keterangan,
            'journal_type' => 'JU',
            'source_module' => 'ACCOUNTING',
            'source_type' => 'MANUAL',
            'posting_event' => 'MANUAL_JOURNAL',
            'lines' => $lines
        ];

        $this->db->trans_begin();
        
        // Simpan Jurnal
        $result = $this->accounting_service->create_manual_journal($payload, $userId);
        
        if ($result['success']) {
            $idJurnal = $result['data']['id_jurnal'];
            
            if ($postNow) {
                // Post Jurnal
                $postResult = $this->accounting_service->post_manual_journal($idJurnal, $userId);
                if (!$postResult['success']) {
                    $this->db->trans_rollback();
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['success' => false, 'message' => $postResult['message']]));
                }
            }
            
            $this->db->trans_commit();
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'message' => 'Transaksi Jurnal berhasil direkam.']));
        } else {
            $this->db->trans_rollback();
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => $result['message']]));
        }
    }

    public function jurnal_umum_delete()
    {
        $id = (int)$this->input->post('id_jurnal');
        
        $journal = $this->db->get_where('tbkeu_jurnal', ['id_jurnal' => $id])->row();
        if (!$journal) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Jurnal tidak ditemukan.']));
        }
        
        $this->db->trans_begin();
        $this->db->where('id_jurnal', $id)->delete('tbkeu_jurnal_detail');
        $this->db->where('id_jurnal', $id)->delete('tbkeu_jurnal');
        $this->db->where('id_jurnal', $id)->delete('tbkeu_jurnal_log');
        
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Gagal menghapus jurnal.']));
        }
        $this->db->trans_commit();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'message' => 'Jurnal berhasil dihapus.']));
    }

    public function jurnal_umum_detail()
    {
        $id = (int)$this->input->post('id_jurnal');
        
        $journal = $this->db->get_where('tbkeu_jurnal', ['id_jurnal' => $id])->row_array();
        if (!$journal) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Jurnal tidak ditemukan.']));
        }
        
        $this->db->select('d.*, a.kode_akun, a.nama_akun');
        $this->db->from('tbkeu_jurnal_detail d');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->db->where('d.id_jurnal', $id);
        $this->db->order_by('d.nomor_baris', 'ASC');
        $details = $this->db->get()->result_array();
        
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => [
                    'journal' => $journal,
                    'details' => $details
                ]
            ]));
    }
}
