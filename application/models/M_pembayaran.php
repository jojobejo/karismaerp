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
                'tanggal_bg_cair' => [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'metode_pembayaran',
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
            'metode_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
                        WHEN LOWER(COALESCE(metode_pembayaran, '')) = 'bg'
                            AND COALESCE(status_bg, 'pending') <> 'cair' THEN 0
                        ELSE jumlah_pembayaran
                    END
                ), 0) AS total_pembayaran,
                COALESCE(SUM(
                    CASE
                        WHEN LOWER(COALESCE(metode_pembayaran, '')) = 'bg'
                            AND COALESCE(status_bg, 'pending') <> 'cair' THEN jumlah_pembayaran
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
            CASE
                WHEN DATEDIFF(CURDATE(), DATE(f.tanggal_faktur)) <= 30 THEN 'Overdue 30'
                WHEN DATEDIFF(CURDATE(), DATE(f.tanggal_faktur)) <= 60 THEN 'Overdue 60'
                ELSE 'Overdue 90'
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

    public function get_faktur_summary($id_faktur)
    {
        $this->_select_invoice_summary();
        $this->db->where('f.id_faktur', $id_faktur);

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
            ->where("LOWER(COALESCE(metode_pembayaran, '')) = 'bg'", null, false)
            ->where('status_bg <>', 'cair')
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

        $is_pending_bg = (strtolower($data['metode_pembayaran'] ?? '') === 'bg' && ($data['status_bg'] ?? '') === 'pending');

        if (!$is_pending_bg && $this->db->table_exists('tbkeu_jurnal') && $this->db->table_exists('tbkeu_jurnal_detail')) {
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

    public function mark_bg_cair($id_pembayaran, $user)
    {
        $this->db->where('id_pembayaran', (int)$id_pembayaran);
        $success = $this->db->update($this->payment_table, [
            'status_bg'  => 'cair',
            'bg_cair_by' => $user,
            'bg_cair_at' => date('Y-m-d H:i:s'),
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
}
