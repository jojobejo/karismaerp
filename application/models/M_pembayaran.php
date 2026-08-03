<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pembayaran extends CI_Model
{
    private $payment_table = 'tbkeu_pembayaran_faktur';

    public function __construct()
    {
        parent::__construct();
        $this->_rename_legacy_payment_table();
        $this->_ensure_payment_table();
        $this->db->query("ALTER TABLE tbkeu_pembayaran_faktur MODIFY COLUMN metode_pembayaran VARCHAR(100) NULL");
    }

    private function _rename_legacy_payment_table()
    {
        if (!$this->db->table_exists('tbkeu_pembayaran_faktur') && $this->db->table_exists('tbso_pembayaran_faktur')) {
            $this->db->query('RENAME TABLE tbso_pembayaran_faktur TO tbkeu_pembayaran_faktur');
        }
    }

    private function _ensure_payment_table()
    {
        $this->load->dbforge();

        if ($this->db->table_exists($this->payment_table)) {
            foreach ([
                'jumlah_diskon' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '16,2',
                    'default'    => 0,
                    'after'      => 'jumlah_pembayaran',
                ],
                'tanggal_bg_cair' => [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'metode_pembayaran',
                ],
                'no_bg' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'tanggal_bg_cair',
                ],
                'nama_bank' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'no_bg',
                ],
                'cara_pembayaran' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'nama_bank',
                ],
                'status_bg' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'not_bg',
                    'null'       => false,
                    'after'      => 'tanggal_bg_cair',
                ],
                'bg_cair_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'status_bg',
                ],
                'bg_cair_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'bg_cair_by',
                ],
                'status_kasir' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'valid',
                    'null'       => false,
                    'after'      => 'status_bg',
                ],
                'kasir_approved_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'status_kasir',
                ],
                'kasir_approved_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'kasir_approved_by',
                ],
            ] as $field => $definition) {
                if (!$this->db->field_exists($field, $this->payment_table)) {
                    $this->dbforge->add_column($this->payment_table, [$field => $definition]);
                }
            }
            $this->db
                ->where("LOWER(COALESCE(metode_pembayaran, '')) = 'bg'", null, false)
                ->where('status_bg', 'not_bg')
                ->update($this->payment_table, ['status_bg' => 'pending']);
            return;
        }

        $this->dbforge->add_field([
            'id_pembayaran' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_faktur' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'no_faktur' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'tanggal_pembayaran' => [
                'type' => 'DATE',
            ],
            'jumlah_pembayaran' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'default'    => 0,
            ],
            'jumlah_diskon' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'default'    => 0,
            ],
            'metode_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'cara_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tanggal_bg_cair' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status_bg' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'not_bg',
            ],
            'bg_cair_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bg_cair_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status_kasir' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'valid',
            ],
            'kasir_approved_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'kasir_approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'create_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'create_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key('id_pembayaran', true);
        $this->dbforge->add_key('id_faktur');
        $this->dbforge->add_key('no_faktur');
        $this->dbforge->create_table($this->payment_table, true);
    }

    private function _total_tagihan_sql()
    {
        return "
            SELECT
                id_faktur,
                COALESCE(SUM(total_harga), 0) AS total_tagihan
            FROM tbso_faktur_detail
            GROUP BY id_faktur
        ";
    }

    private function _total_pembayaran_sql()
    {
        return "
            SELECT
                id_faktur,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(status_bg, 'not_bg') = 'pending' THEN 0
                        WHEN COALESCE(status_kasir, 'valid') = 'pending_kasir' THEN 0
                        ELSE (jumlah_pembayaran + jumlah_diskon)
                    END
                ), 0) AS total_pembayaran,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(status_bg, 'not_bg') = 'pending' THEN (jumlah_pembayaran + jumlah_diskon)
                        ELSE 0
                    END
                ), 0) AS total_bg_pending
            FROM {$this->payment_table}
            GROUP BY id_faktur
        ";
    }

    private function _nama_barang_sql()
    {
        return "
            SELECT
                id_faktur,
                GROUP_CONCAT(DISTINCT nama_barang ORDER BY nama_barang ASC SEPARATOR ', ') AS nama_barang
            FROM tbso_faktur_detail
            GROUP BY id_faktur
        ";
    }

    private function _tanggal_selesai_do_expr()
    {
        if ($this->db->field_exists('tanggal_selesai_do', 'tbso_faktur_penjualan')) {
            return 'COALESCE(f.tanggal_selesai_do, f.update_at, f.tanggal_faktur)';
        }
        if ($this->db->field_exists('selesai_do_at', 'tbso_faktur_penjualan')) {
            return 'COALESCE(f.selesai_do_at, f.update_at, f.tanggal_faktur)';
        }
        if ($this->db->field_exists('update_at', 'tbso_faktur_penjualan')) {
            return 'COALESCE(f.update_at, f.tanggal_faktur)';
        }
        return 'f.tanggal_faktur';
    }

    private function _select_invoice_summary()
    {
        $tanggal_selesai_do = $this->_tanggal_selesai_do_expr();
        $cara_pembayaran_select = $this->db->field_exists('cara_pembayaran', 'tbso_faktur_penjualan')
            ? "COALESCE(f.cara_pembayaran, '') AS cara_pembayaran"
            : "'' AS cara_pembayaran";

        $this->db->select("
            f.id_faktur,
            f.no_faktur,
            f.no_so,
            f.kd_customer,
            COALESCE(NULLIF(f.customer_name, ''), c.nama_customer, '-') AS nama_customer,
            c.nama_kios,
            c.regional,
            c.kd_rute,
            f.tanggal_faktur,
            f.tanggal_jatuh_tempo,
            {$cara_pembayaran_select},
            {$tanggal_selesai_do} AS tanggal_selesai_do,
            f.status,
            COALESCE(ft.total_tagihan, 0) AS total_tagihan,
            COALESCE(fp.total_pembayaran, 0) AS total_pembayaran,
            COALESCE(fp.total_bg_pending, 0) AS total_bg_pending,
            GREATEST(COALESCE(ft.total_tagihan, 0) - COALESCE(fp.total_pembayaran, 0), 0) AS sisa_tagihan,
            CASE
                WHEN COALESCE(fp.total_pembayaran, 0) >= COALESCE(ft.total_tagihan, 0)
                    AND COALESCE(ft.total_tagihan, 0) > 0 THEN 'lunas'
                WHEN COALESCE(fp.total_pembayaran, 0) > 0 THEN 'sebagian'
                ELSE 'belum_lunas'
            END AS status_pembayaran,
            DATEDIFF(CURDATE(), DATE(f.tanggal_faktur)) AS hari_overdue,
            DATEDIFF(DATE(f.tanggal_jatuh_tempo), CURDATE()) AS sisa_hari,
            CASE
                WHEN COALESCE(f.jtempo, 0) = 30 THEN 'Overdue 30'
                WHEN COALESCE(f.jtempo, 0) = 60 THEN 'Overdue 60'
                WHEN COALESCE(f.jtempo, 0) = 90 THEN 'Overdue 90'
                ELSE 'Belum overdue'
            END AS status_overdue,
            COALESCE(fnb.nama_barang, '-') AS nama_barang
        ", false);
        $this->db->from('tbso_faktur_penjualan f');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        $this->db->join('(' . $this->_total_tagihan_sql() . ') ft', 'ft.id_faktur = f.id_faktur', 'left');
        $this->db->join('(' . $this->_total_pembayaran_sql() . ') fp', 'fp.id_faktur = f.id_faktur', 'left');
        $this->db->join('(' . $this->_nama_barang_sql() . ') fnb', 'fnb.id_faktur = f.id_faktur', 'left');
        $this->db->where('f.status', 'selesai_do');
    }

    public function get_customers_with_unpaid_faktur($keyword = '')
    {
        $invoice_summary_sql = $this->_invoice_summary_sql($keyword);

        $this->db->select("
            x.kd_customer,
            x.nama_customer,
            COUNT(*) AS total_faktur,
            SUM(x.total_tagihan) AS total_tagihan,
            SUM(x.total_pembayaran) AS total_pembayaran,
            SUM(x.total_bg_pending) AS total_bg_pending,
            SUM(x.sisa_tagihan) AS sisa_tagihan
        ", false);
        $this->db->from('(' . $invoice_summary_sql . ') x');
        $this->db->where('x.sisa_tagihan >', 0);
        $this->db->group_by('x.kd_customer, x.nama_customer');
        $this->db->order_by('x.nama_customer', 'ASC');

        return $this->db->get()->result_array();
    }

    private function _invoice_summary_sql($keyword = '')
    {
        $this->_select_invoice_summary();
        if ($keyword !== '') {
            $this->db->group_start();
            $this->db->like('f.customer_name', $keyword);
            $this->db->or_like('c.nama_customer', $keyword);
            $this->db->or_like('f.kd_customer', $keyword);
            $this->db->group_end();
        }

        return $this->db->get_compiled_select();
    }

    public function get_unpaid_faktur_by_customer($kd_customer)
    {
        $this->_select_invoice_summary();
        $this->db->where('f.kd_customer', $kd_customer);
        $this->db->having('sisa_tagihan >', 0);
        $this->db->order_by('tanggal_selesai_do', 'ASC');
        $this->db->order_by('f.tanggal_faktur', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_all_unpaid_fakturs()
    {
        $this->_select_invoice_summary();
        
        $this->db->group_start();
        $this->db->where('f.jtempo >', 0);
        $this->db->or_where('f.tempo >', 0);
        $this->db->group_end();

        $this->db->having('sisa_tagihan >', 0);
        $this->db->order_by('tanggal_selesai_do', 'ASC');
        $this->db->order_by('f.tanggal_faktur', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_faktur_summary($identifier)
    {
        $this->_select_invoice_summary();
        if (is_numeric($identifier) && (int)$identifier == $identifier) {
            $this->db->where('f.id_faktur', (int)$identifier);
        } else {
            $this->db->where('f.no_faktur', $identifier);
        }

        return $this->db->get()->row_array();
    }

    public function get_payment_history($id_faktur)
    {
        return $this->db
            ->where('id_faktur', $id_faktur)
            ->order_by('tanggal_pembayaran', 'DESC')
            ->order_by('id_pembayaran', 'DESC')
            ->get($this->payment_table)
            ->result_array();
    }

    public function get_pending_bg_payment($id_faktur)
    {
        return $this->db
            ->where('id_faktur', (int)$id_faktur)
            ->where('status_bg', 'pending')
            ->order_by('tanggal_pembayaran', 'ASC')
            ->order_by('id_pembayaran', 'ASC')
            ->limit(1)
            ->get($this->payment_table)
            ->row_array();
    }

    public function insert_payment($data)
    {
        $this->db->trans_start();
        $this->db->insert($this->payment_table, $data);
        $id_pembayaran = $this->db->insert_id();

        $is_pending_bg = (($data['status_bg'] ?? '') === 'pending');
        $is_pending_kasir = (($data['status_kasir'] ?? 'valid') === 'pending_kasir');

        if (!$is_pending_bg && !$is_pending_kasir && $this->db->table_exists('tbkeu_jurnal') && $this->db->table_exists('tbkeu_jurnal_detail')) {
            $this->load->model('M_Journal');
            $this->M_Journal->post_jurnal_pembayaran($id_pembayaran, $data);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_payment($id_pembayaran)
    {
        return $this->db
            ->where('id_pembayaran', (int)$id_pembayaran)
            ->get($this->payment_table)
            ->row_array();
    }

    public function mark_bg_cair($id_pembayaran, $user, $update_data = [])
    {
        $this->db->where('id_pembayaran', (int)$id_pembayaran);
        $payload = array_merge($update_data, [
            'status_bg'  => 'cair',
            'bg_cair_by' => $user,
            'bg_cair_at' => date('Y-m-d H:i:s'),
        ]);
        $success = $this->db->update($this->payment_table, $payload);

        if ($success) {
            $payment = $this->get_payment($id_pembayaran);
            if ($payment && $this->db->table_exists('tbkeu_jurnal') && $this->db->table_exists('tbkeu_jurnal_detail')) {
                $this->load->model('M_Journal');
                $this->M_Journal->post_jurnal_pembayaran($id_pembayaran, $payment);
            }
        }

        return $success;
    }

    /**
     * Get available return balance (saldo retur) for a customer
     */
    public function get_customer_saldo_retur($kd_customer)
    {
        // 1. Get total completed / active returns after Dirut approval (only for tipe 'biasa')
        $this->db->select('SUM(d.qty_retur * d.harga_satuan) AS total_retur');
        $this->db->from('tbrp_retur_penjualan_header h');
        $this->db->join('tbrp_retur_penjualan_detail d', 'd.id_retur = h.id_retur');
        $this->db->where('h.kd_customer', $kd_customer);
        $this->db->where_in('h.status_retur', ['menunggu_collection', 'menunggu_kasir', 'selesai']);
        $this->db->where('h.tipe_retur', 'biasa');
        $total_retur = (float)$this->db->get()->row()->total_retur;

        // 2. Get total return used in payments
        $this->db->select('SUM(p.jumlah_pembayaran) AS total_used');
        $this->db->from($this->payment_table . ' p');
        $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = p.id_faktur');
        $this->db->where('f.kd_customer', $kd_customer);
        $this->db->where('p.metode_pembayaran', 'retur');
        $total_used = (float)$this->db->get()->row()->total_used;

        return max(0.0, $total_retur - $total_used);
    }

    public function update_cara_pembayaran($id_faktur, $cara_pembayaran)
    {
        if ($this->db->field_exists('cara_pembayaran', 'tbso_faktur_penjualan')) {
            $this->db->where('id_faktur', (int)$id_faktur);
            return $this->db->update('tbso_faktur_penjualan', ['cara_pembayaran' => $cara_pembayaran]);
        }
        return false;
    }

    public function get_recent_payments($keyword = '', $limit = 50)
    {
        $this->db->select('p.*, c.nama_customer, c.kd_customer');
        $this->db->from($this->payment_table . ' p');
        $this->db->join('tbso_faktur_penjualan f', 'f.id_faktur = p.id_faktur', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        
        if ($keyword !== '') {
            $this->db->group_start();
            $this->db->like('p.no_faktur', $keyword);
            $this->db->or_like('c.nama_customer', $keyword);
            $this->db->or_like('p.metode_pembayaran', $keyword);
            $this->db->group_end();
        }
        
        $this->db->order_by('p.tanggal_pembayaran', 'DESC');
        $this->db->order_by('p.id_pembayaran', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result_array();
    }

    public function get_due_pending_payments()
    {
        return $this->db
            ->select('p.*, c.nama_customer, c.kd_customer')
            ->from($this->payment_table . ' p')
            ->join('tbso_faktur_penjualan f', 'f.id_faktur = p.id_faktur', 'left')
            ->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left')
            ->where('p.status_bg', 'pending')
            ->where('p.tanggal_bg_cair <=', date('Y-m-d'))
            ->order_by('p.tanggal_bg_cair', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_pending_kasir_payments()
    {
        return $this->db
            ->select('p.*, c.nama_customer, c.kd_customer')
            ->from($this->payment_table . ' p')
            ->join('tbso_faktur_penjualan f', 'f.id_faktur = p.id_faktur', 'left')
            ->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left')
            ->where('p.status_kasir', 'pending_kasir')
            ->order_by('p.tanggal_pembayaran', 'ASC')
            ->get()
            ->result_array();
    }

    public function approve_kasir_payment($id_pembayaran, $user)
    {
        $this->db->where('id_pembayaran', (int)$id_pembayaran);
        $success = $this->db->update($this->payment_table, [
            'status_kasir' => 'valid',
            'kasir_approved_by' => $user,
            'kasir_approved_at' => date('Y-m-d H:i:s'),
        ]);

        if ($success) {
            $payment = $this->get_payment($id_pembayaran);
            if ($payment && $this->db->table_exists('tbkeu_jurnal') && $this->db->table_exists('tbkeu_jurnal_detail')) {
                $this->load->model('M_Journal');
                $this->M_Journal->post_jurnal_pembayaran($id_pembayaran, $payment);
            }
        }
        return $success;
    }
}
