<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Bufferstockglobal extends CI_Model

{

    var $column_order = array('nama_suplier', 'nm_barang', 'satuan', 'qty', 'qty_box', 'qty_pcs');
    var $column_search = array('nama_suplier', 'nm_barang');

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _get_datatables_query()
    {
        $this->db->select([
            'b.nama_suplier AS nmsuplier',
            'c.nm_barang AS nmbarang',
            'c.satuan AS satuan',
            'a.qty AS qty',
            '(c.p * c.l * c.t) AS dimensi',
            'FLOOR((a.qty / (c.p * c.l * c.t))) AS qty_box',
            '(a.qty - FLOOR(a.qty / (c.p * c.l * c.t)) * (c.p * c.l * c.t)) AS qty_pcs'
        ]);
        $this->db->from('tb_dailystock_global a');
        $this->db->join('tb_suplier b', 'b.kd_suplier = a.kd_suplier');
        $this->db->join('tb_master_barang c', 'c.kode_barang = a.kd_barang');
        $this->db->where('a.qty >', 0);
        $this->db->group_by('c.nm_barang');

        $i = 0;

        foreach ($this->column_search as $item) {
            if ($_POST['search']['value']) {

                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all()
    {
        $this->db->select([
            'b.nama_suplier AS nmsuplier',
            'c.nm_barang AS nmbarang',
            'c.satuan AS satuan',
            'a.qty AS qty',
            '(c.p * c.l * c.t) AS dimensi',
            'FLOOR((a.qty / (c.p * c.l * c.t))) AS qty_box',
            '(a.qty - FLOOR(a.qty / (c.p * c.l * c.t)) * (c.p * c.l * c.t)) AS qty_pcs'
        ]);
        $this->db->from('tb_dailystock_global a');
        $this->db->join('tb_suplier b', 'b.kd_suplier = a.kd_suplier');
        $this->db->join('tb_master_barang c', 'c.kode_barang = a.kd_barang');
        $this->db->where('a.qty >', 0);
        $this->db->group_by('c.nm_barang');
        return $this->db->count_all_results();
    }
}
