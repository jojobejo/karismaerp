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
            
        // Generate nomor referensi otomatis berdasarkan tanggal hari ini
        $data['next_ref'] = $this->_generate_next_ref(date('Y-m-d'));

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/jurnal_umum.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function jurnal_umum_list()
    {
        $search = $this->input->get_post('search');
        $date_from = $this->input->get_post('date_from');
        $date_to = $this->input->get_post('date_to');
        $status = $this->input->get_post('status');
        
        $this->db->select('j.*');
        $this->db->from('tbkeu_jurnal j');
        $this->db->group_start();
        $this->db->where('j.source_type', 'MANUAL');
        $this->db->or_where('j.posting_event', 'MANUAL_JOURNAL');
        $this->db->group_end();

        if (!empty($date_from)) {
            $this->db->where('j.tanggal_transaksi >=', $date_from);
        }
        if (!empty($date_to)) {
            $this->db->where('j.tanggal_transaksi <=', $date_to);
        }
        if (!empty($status) && $status !== 'Semua') {
            if (strcasecmp($status, 'POSTED') === 0) {
                $this->db->where('j.status', 'POSTED');
            } elseif (strcasecmp($status, 'UNPOSTED') === 0 || strcasecmp($status, 'DRAFT') === 0) {
                $this->db->where('j.status !=', 'POSTED');
            } else {
                $this->db->where('j.status', $status);
            }
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('j.nomor_jurnal', $search);
            $this->db->or_like('j.keterangan', $search);
            $this->db->group_end();
        }
        $this->db->order_by('j.tanggal_transaksi', 'DESC');
        $this->db->order_by('j.id_jurnal', 'DESC');
        $rows = $this->db->get()->result_array();

        foreach ($rows as $k => $row) {
            $rows[$k]['nilai_formatted'] = 'Rp ' . number_format($row['total_debit'], 2, ',', '.');
            $rows[$k]['tanggal_formatted'] = date('d/m/Y', strtotime($row['tanggal_transaksi']));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    public function get_next_ref()
    {
        $tanggal = $this->input->get_post('tanggal');
        $ref = $this->_generate_next_ref($tanggal);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'next_ref' => $ref]));
    }

    /**
     * Generate nomor referensi jurnal umum berikutnya berdasarkan tanggal
     * Format: GJ-dmyXXXXX (5 digit urut, misal: GJ-11092600051)
     */
    private function _generate_next_ref($tanggal = null)
    {
        $time = !empty($tanggal) ? strtotime($tanggal) : false;
        $dateStr = $time ? date('dmy', $time) : date('dmy');
        $prefix = 'GJ-' . $dateStr;

        $query = $this->db->select('nomor_jurnal')
            ->like('nomor_jurnal', $prefix, 'after')
            ->get('tbkeu_jurnal');

        $max_seq = 0;
        foreach ($query->result() as $row) {
            $no = $row->nomor_jurnal;
            if (strpos($no, $prefix) === 0) {
                $seq_part = substr($no, strlen($prefix));
                if (is_numeric($seq_part)) {
                    $seq = (int)$seq_part;
                    if ($seq > $max_seq) {
                        $max_seq = $seq;
                    }
                }
            }
        }

        $next_num = $max_seq + 1;
        return $prefix . sprintf('%05d', $next_num);
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

        $existing = $this->db->get_where('tbkeu_jurnal', ['nomor_jurnal' => $referensi])->row();
        if ($existing) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Nomor referensi / jurnal ' . $referensi . ' sudah terdaftar. Silakan gunakan nomor referensi lain.']));
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
            'source_id' => $referensi,
            'source_no' => $referensi,
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
        // Hapus child records terlebih dahulu untuk menghindari error foreign key constraint
        $this->db->where('id_jurnal', $id)->delete('tbkeu_jurnal_log');
        $this->db->where('id_jurnal', $id)->delete('tbkeu_jurnal_detail');
        $this->db->where('reversal_of_journal_id', $id)->update('tbkeu_jurnal', ['reversal_of_journal_id' => null]);
        $this->db->where('id_jurnal', $id)->delete('tbkeu_jurnal');
        
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
        
        if ($id <= 0 && $this->input->post('id_pembayaran')) {
            $id_pembayaran = (int)$this->input->post('id_pembayaran');
            $journal_by_pay = $this->db->where('source_module', 'KEUANGAN')
                ->group_start()
                    ->where('source_id', (string)$id_pembayaran)
                    ->or_where('source_id', $id_pembayaran)
                ->group_end()
                ->order_by('id_jurnal', 'DESC')
                ->get('tbkeu_jurnal')
                ->row_array();
            if ($journal_by_pay) {
                $id = (int)$journal_by_pay['id_jurnal'];
            }
        }
        
        $journal = $this->db->get_where('tbkeu_jurnal', ['id_jurnal' => $id])->row_array();
        if (!$journal) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Jurnal tidak ditemukan. Transaksi pembayaran ini belum tercatat pada jurnal umum.']));
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

    public function index()
    {
        $data['page_title'] = 'Buku Besar';
        
        // Fetch departments
        $data['departments'] = $this->db->select('id, nama_departemen')
            ->order_by('nama_departemen', 'ASC')
            ->get('tb_departemen')
            ->result_array();

        // Get min and max account codes for default values
        $min_acc = $this->db->select('kode_akun')->where('tipe_akun', 'POSTING')->where('is_active', 1)->order_by('kode_akun', 'ASC')->limit(1)->get('tbkeu_akun')->row();
        $max_acc = $this->db->select('kode_akun')->where('tipe_akun', 'POSTING')->where('is_active', 1)->order_by('kode_akun', 'DESC')->limit(1)->get('tbkeu_akun')->row();
        
        $data['min_account'] = $min_acc ? $min_acc->kode_akun : '';
        $data['max_account'] = $max_acc ? $max_acc->kode_akun : '';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/buku_besar.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function get_accounts_lookup()
    {
        $search = $this->input->get('search', true);
        
        $this->db->select('id_akun, kode_akun, nama_akun');
        $this->db->from('tbkeu_akun');
        $this->db->where('tipe_akun', 'POSTING');
        $this->db->where('is_active', 1);
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('kode_akun', $search);
            $this->db->or_like('nama_akun', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('kode_akun', 'ASC');
        $rows = $this->db->get()->result_array();
        
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $rows]));
    }

    public function get_ledger_data()
    {
        $filter_type = $this->input->post('filter_type', true) ?: 'standar';
        $date_from = $this->input->post('date_from', true);
        $date_to = $this->input->post('date_to', true);
        $account_from = $this->input->post('account_from', true);
        $account_to = $this->input->post('account_to', true);
        $dept_from = $this->input->post('dept_from', true);
        $dept_to = $this->input->post('dept_to', true);
        
        $search_keterangan = $this->input->post('search_keterangan', true);
        $search_nomor = $this->input->post('search_nomor', true);

        if (empty($date_from)) $date_from = date('Y-m-01');
        if (empty($date_to)) $date_to = date('Y-m-d');

        // Menentukan akun mana yang akan dimuat
        $this->db->reset_query();
        if ($filter_type === 'pencarian') {
            // Temukan id_akun unik yang sesuai dengan kata kunci keterangan atau nomor jurnal
            $this->db->select('DISTINCT(jd.id_akun) as id_akun');
            $this->db->from('tbkeu_jurnal_detail jd');
            $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = jd.id_jurnal', 'inner');
            $this->db->where('j.status', 'POSTED');
            if (!empty($search_keterangan)) {
                $this->db->group_start();
                $this->db->like('jd.keterangan', $search_keterangan);
                $this->db->or_like('j.keterangan', $search_keterangan);
                $this->db->group_end();
            }
            if (!empty($search_nomor)) {
                $this->db->like('j.nomor_jurnal', $search_nomor);
            }
            $matching_ids_query = $this->db->get()->result_array();
            $matching_ids = array_column($matching_ids_query, 'id_akun');

            if (empty($matching_ids)) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => true, 'data' => [], 'filters' => $_POST]));
            }

            // Ambil hanya akun yang cocok
            $this->db->reset_query();
            $this->db->select('id_akun, kode_akun, nama_akun, saldo_normal');
            $this->db->from('tbkeu_akun');
            $this->db->where('tipe_akun', 'POSTING');
            $this->db->where('is_active', 1);
            $this->db->where_in('id_akun', $matching_ids);
        } else {
            $this->db->select('id_akun, kode_akun, nama_akun, saldo_normal');
            $this->db->from('tbkeu_akun');
            $this->db->where('tipe_akun', 'POSTING');
            $this->db->where('is_active', 1);
            if (!empty($account_from)) {
                $this->db->where('kode_akun >=', $account_from);
            }
            if (!empty($account_to)) {
                $this->db->where('kode_akun <=', $account_to);
            }
        }

        $this->db->order_by('kode_akun', 'ASC');
        $accounts = $this->db->get()->result_array();

        $result_data = [];

        foreach ($accounts as $acc) {
            $id_akun = $acc['id_akun'];
            $saldo_normal = strtoupper(trim((string)$acc['saldo_normal']));

            // 1. Hitung Saldo Awal
            $calc_sa = ($filter_type !== 'pencarian' || !empty($date_from));
            
            $saldo_awal = 0.0;
            if ($calc_sa) {
                $this->db->reset_query();
                $this->db->select('SUM(debit) as sa_debit, SUM(kredit) as sa_kredit, MAX(tanggal_saldo) as sa_date');
                $this->db->from('tbkeu_saldo_awal_akun');
                $this->db->where('id_akun', $id_akun);
                $this->db->where('tanggal_saldo <', $date_from);
                $sa_query = $this->db->get()->row();
                
                $opening_debit = $sa_query ? (float)$sa_query->sa_debit : 0.0;
                $opening_kredit = $sa_query ? (float)$sa_query->sa_kredit : 0.0;
                $sa_date = ($sa_query && !empty($sa_query->sa_date)) ? $sa_query->sa_date : '1970-01-01';

                // Jumlahkan transaksi sebelum tanggal awal
                $this->db->reset_query();
                $this->db->select('SUM(jd.debit) as j_debit, SUM(jd.kredit) as j_kredit');
                $this->db->from('tbkeu_jurnal_detail jd');
                $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = jd.id_jurnal', 'inner');
                $this->db->where('jd.id_akun', $id_akun);
                $this->db->where('j.status', 'POSTED');
                $this->db->where('j.tanggal_transaksi >=', $sa_date);
                $this->db->where('j.tanggal_transaksi <', $date_from);

                $apply_dept = false;
                if ($dept_from !== '' && $dept_from !== null && (int)$dept_from !== 0) {
                    $apply_dept = true;
                }
                if ($dept_to !== '' && $dept_to !== null && (int)$dept_to !== 999999999) {
                    $apply_dept = true;
                }

                if ($apply_dept) {
                    $this->db->group_start();
                    $this->db->where('jd.id_departemen >=', (int)$dept_from);
                    $this->db->where('jd.id_departemen <=', (int)$dept_to);
                    $this->db->group_end();
                }

                $j_query = $this->db->get()->row();
                $tot_debit = $opening_debit + ($j_query ? (float)$j_query->j_debit : 0.0);
                $tot_kredit = $opening_kredit + ($j_query ? (float)$j_query->j_kredit : 0.0);

                if ($saldo_normal === 'KREDIT') {
                    $saldo_awal = $tot_kredit - $tot_debit;
                } else {
                    $saldo_awal = $tot_debit - $tot_kredit;
                }
            }

            // 2. Query mutasi transaksi
            $this->db->reset_query();
            $this->db->select("
                j.tanggal_transaksi as tanggal,
                COALESCE(jj.kode_jenis_jurnal, '') as tp,
                j.nomor_jurnal as no_referensi,
                COALESCE(NULLIF(jd.keterangan, ''), j.keterangan, '') as catatan,
                COALESCE(d.nama_departemen, '') as departemen,
                COALESCE(jd.debit, 0) as debit,
                COALESCE(jd.kredit, 0) as kredit,
                jd.id_jurnal_detail,
                j.source_module,
                j.source_type,
                j.source_id,
                j.source_no,
                j.id_jurnal
            ", false);
            $this->db->from('tbkeu_jurnal_detail jd');
            $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = jd.id_jurnal', 'inner');
            $this->db->join('tb_departemen d', 'd.id = jd.id_departemen', 'left');
            $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
            $this->db->where('jd.id_akun', $id_akun);
            $this->db->where('j.status', 'POSTED');

            if ($filter_type === 'pencarian') {
                if (!empty($search_keterangan)) {
                    $this->db->group_start();
                    $this->db->like('jd.keterangan', $search_keterangan);
                    $this->db->or_like('j.keterangan', $search_keterangan);
                    $this->db->group_end();
                }
                if (!empty($search_nomor)) {
                    $this->db->like('j.nomor_jurnal', $search_nomor);
                }
            } else {
                $this->db->where('j.tanggal_transaksi >=', $date_from);
                $this->db->where('j.tanggal_transaksi <=', $date_to);

                $apply_dept = false;
                if ($dept_from !== '' && $dept_from !== null && (int)$dept_from !== 0) {
                    $apply_dept = true;
                }
                if ($dept_to !== '' && $dept_to !== null && (int)$dept_to !== 999999999) {
                    $apply_dept = true;
                }

                if ($apply_dept) {
                    $this->db->group_start();
                    $this->db->where('jd.id_departemen >=', (int)$dept_from);
                    $this->db->where('jd.id_departemen <=', (int)$dept_to);
                    $this->db->group_end();
                }
            }

            $this->db->order_by('j.tanggal_transaksi', 'ASC');
            $this->db->order_by('j.id_jurnal', 'ASC');
            $this->db->order_by('jd.id_jurnal_detail', 'ASC');
            $mutations = $this->db->get()->result_array();

            // Hitung running balance tanpa reference leak
            $running_balance = $saldo_awal;
            $total_debit = 0.0;
            $total_kredit = 0.0;

            foreach ($mutations as $idx => $m) {
                $m['debit'] = (float)$m['debit'];
                $m['kredit'] = (float)$m['kredit'];
                $total_debit += $m['debit'];
                $total_kredit += $m['kredit'];

                if ($saldo_normal === 'KREDIT') {
                    $running_balance += ($m['kredit'] - $m['debit']);
                } else {
                    $running_balance += ($m['debit'] - $m['kredit']);
                }
                $m['saldo'] = $running_balance;
                $m['tanggal_formatted'] = date('d/m/Y', strtotime($m['tanggal']));
                $m['debit_formatted'] = number_format($m['debit'], 2, '.', ',');
                $m['kredit_formatted'] = number_format($m['kredit'], 2, '.', ',');
                $mutations[$idx] = $m;
            }

            $saldo_akhir = $running_balance;
            $mutasi = $saldo_normal === 'KREDIT' ? ($total_kredit - $total_debit) : ($total_debit - $total_kredit);

            $result_data[] = [
                'account' => $acc,
                'saldo_awal' => $saldo_awal,
                'saldo_akhir' => $saldo_akhir,
                'total_debit' => $total_debit,
                'total_kredit' => $total_kredit,
                'mutasi' => $mutasi,
                'mutations' => $mutations
            ];
        }

        $is_combined = (count($accounts) > 1);

        if ($is_combined) {
            $all_mutations = [];
            $grand_debit = 0.0;
            $grand_kredit = 0.0;

            foreach ($result_data as $accData) {
                foreach ($accData['mutations'] as $m) {
                    $all_mutations[] = $m;
                    $grand_debit += $m['debit'];
                    $grand_kredit += $m['kredit'];
                }
            }

            // Urutkan daftar transaksi secara kronologis
            usort($all_mutations, function($a, $b) {
                $dateA = strtotime($a['tanggal']);
                $dateB = strtotime($b['tanggal']);
                if ($dateA === $dateB) {
                    return (int)$a['id_jurnal_detail'] - (int)$b['id_jurnal_detail'];
                }
                return $dateA - $dateB;
            });

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'is_combined' => true,
                    'total_debit' => $grand_debit,
                    'total_kredit' => $grand_kredit,
                    'mutasi' => $grand_debit - $grand_kredit,
                    'data' => $all_mutations,
                    'filters' => [
                        'filter_type' => $filter_type,
                        'date_from' => $date_from,
                        'date_to' => $date_to,
                        'account_from' => $account_from,
                        'account_to' => $account_to,
                        'dept_from' => $dept_from,
                        'dept_to' => $dept_to,
                        'search_keterangan' => $search_keterangan,
                        'search_nomor' => $search_nomor
                    ]
                ]));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'is_combined' => false,
                'data' => $result_data,
                'filters' => [
                    'filter_type' => $filter_type,
                    'date_from' => $date_from,
                    'date_to' => $date_to,
                    'account_from' => $account_from,
                    'account_to' => $account_to,
                    'dept_from' => $dept_from,
                    'dept_to' => $dept_to,
                    'search_keterangan' => $search_keterangan,
                    'search_nomor' => $search_nomor
                ]
            ]));
    }
}

