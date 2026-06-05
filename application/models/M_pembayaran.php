<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pembayaran extends CI_Model
{
    private $payment_table = 'tbso_pembayaran_faktur';

    public function __construct()
    {
        parent::__construct();
        $this->_ensure_payment_table();
    }

    private function _ensure_payment_table()
    {
        if ($this->db->table_exists($this->payment_table)) {
            return;
        }

        $this->load->dbforge();
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
                'constraint' => 30,
                'null'       => true,
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
                COALESCE(SUM(jumlah_pembayaran), 0) AS total_pembayaran
            FROM {$this->payment_table}
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
            {$tanggal_selesai_do} AS tanggal_selesai_do,
            f.status,
            COALESCE(ft.total_tagihan, 0) AS total_tagihan,
            COALESCE(fp.total_pembayaran, 0) AS total_pembayaran,
            GREATEST(COALESCE(ft.total_tagihan, 0) - COALESCE(fp.total_pembayaran, 0), 0) AS sisa_tagihan,
            CASE
                WHEN COALESCE(fp.total_pembayaran, 0) >= COALESCE(ft.total_tagihan, 0)
                    AND COALESCE(ft.total_tagihan, 0) > 0 THEN 'lunas'
                WHEN COALESCE(fp.total_pembayaran, 0) > 0 THEN 'sebagian'
                ELSE 'belum_lunas'
            END AS status_pembayaran,
            DATEDIFF(CURDATE(), DATE({$tanggal_selesai_do})) AS hari_selesai_do,
            CASE
                WHEN DATEDIFF(CURDATE(), DATE({$tanggal_selesai_do})) >= 90 THEN 'Overdue 90'
                WHEN DATEDIFF(CURDATE(), DATE({$tanggal_selesai_do})) >= 60 THEN 'Overdue 60'
                WHEN DATEDIFF(CURDATE(), DATE({$tanggal_selesai_do})) >= 30 THEN 'Overdue 30'
                ELSE 'Belum overdue'
            END AS status_overdue
        ", false);
        $this->db->from('tbso_faktur_penjualan f');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        $this->db->join('(' . $this->_total_tagihan_sql() . ') ft', 'ft.id_faktur = f.id_faktur', 'left');
        $this->db->join('(' . $this->_total_pembayaran_sql() . ') fp', 'fp.id_faktur = f.id_faktur', 'left');
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

    public function insert_payment($data)
    {
        return $this->db->insert($this->payment_table, $data);
    }
}
