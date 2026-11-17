<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 
 */

class M_Katalog extends CI_Model
{

    var $table = 'tb_barangv2'; //nama tabel dari database
    var $column_order =
    array('id_barang', 'kode_barang', 'nama_barang', 'nama_suplier', 'produk_fokus', 'kelompok', 'bahan_aktif', 'gbr_produk', 'gbr_promo1', 'gbr_promo2', 'gbr_promo3');
    var $column_search = array('produk_fokus', 'nama_barang', 'nama_suplier', 'bahan_aktif', 'kelompok');
    var $order = array('produk_fokus' => 'desc'); // default order 


    public function getAll()
    {
        $this->db->from('tb_barangv2');
        $this->db->group_by('kode_barang');
        $this->db->order_by("produk_fokus desc");
        $query = $this->db->get();
        return $query->result();
    }

    public function get_total()
    {
        return $this->db->query("SELECT 
        COUNT(a.id_barang)AS t_barang,
        SUM(CASE WHEN a.gbr_produk = '-' THEN 1 ELSE 0 END) AS t_non_gbr,
        SUM(CASE WHEN a.gbr_produk != '-' THEN 1 ELSE 0 END) AS t_upl_gbr
        FROM tb_barangv2 a
        ");
    }

    public function adddata($data)
    {
        return $this->db->insert('tb_barangv2', $data);
    }

    function editdataKat($data, $id_barang)
    {
        $this->db->where('id_barang', $id_barang);
        return $this->db->update('tb_barangv2', $data);
    }

    function barangdel($id_barang)
    {
        $this->db->where('id_barang', $id_barang);
        return $this->db->delete("tb_barangv2");
    }

    public function get_datatables()
    {
        $this->datatables_query();
        if (isset($_POST['start']) && $_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    private function datatables_query()
    {
        $this->db->from('tb_barangv2');
        $columns = array('kode_barang','produk_fokus','nama_barang','nama_suplier','kelompok','bahan_aktif','gbr_produk','shopee','tokopedia','kiushop');

        // Search global
        if (!empty($_POST['search']['value'])) {
            $this->db->group_start();
            foreach ($columns as $col) {
                $this->db->or_like($col, $_POST['search']['value']);
            }
            $this->db->group_end();
        }

        // Filter produk fokus
        if (!empty($_POST['filter_fokus'])) {
            if ($_POST['filter_fokus'] == 'kosong') {
                $this->db->where('(produk_fokus IS NULL OR produk_fokus = "")', null, false);
            } else {
                $this->db->where('produk_fokus', $_POST['filter_fokus']);
            }
        }

        // Filter online shop ← tambahan baru
        if (!empty($_POST['filter_online'])) {
            if ($_POST['filter_online'] == 'shopee') {
                $this->db->where('shopee', 1);
            } elseif ($_POST['filter_online'] == 'tokopedia') {
                $this->db->where('tokopedia', 1);
            } elseif ($_POST['filter_online'] == 'kiushop') {
                $this->db->where('kiushop', 1);
            } elseif ($_POST['filter_online'] == 'kosong') {
                $this->db->where('shopee', 0);
                $this->db->where('tokopedia', 0);
                $this->db->where('kiushop', 0);
            }
        }

        // Order
        if (isset($_POST['order'])) {
            $this->db->order_by($columns[$_POST['order'][0]['column']], $_POST['order'][0]['dir']);
        } else {
            $this->db->order_by('id_barang', 'DESC');
        }
    }

    public function count_all()
    {
        $this->db->from('tb_barangv2');
        return $this->db->count_all_results();
    }

    public function count_filtered()
    {
        $this->datatables_query();
        return $this->db->count_all_results();
    }

    // Statistik untuk info cards
    public function getStatistik()
    {
        $stat = array();

        $stat['total']     = $this->db->count_all('tb_barangv2');
        
        $this->db->where('shopee', 1);
        $stat['shopee']    = $this->db->count_all_results('tb_barangv2');
        
        $this->db->where('tokopedia', 1);
        $stat['tokopedia'] = $this->db->count_all_results('tb_barangv2');
        
        $this->db->where('kiushop', 1);
        $stat['kiushop']   = $this->db->count_all_results('tb_barangv2');

        return $stat;
    }
    
    public function updateOnlineShop($data, $id_barang)
    {
        $this->db->where('id_barang', $id_barang);
        return $this->db->update('tb_barangv2', $data);
    }

    public function getById($id_barang)
    {
        return $this->db->get_where('tb_barangv2', array('id_barang' => $id_barang))->row();
    }

    public function updateBahanAktif($id_barang, $data)
    {
        $this->db->where('id_barang', $id_barang);
        return $this->db->update('tb_barangv2', $data);
    }

    public function updateGambarProduk($id_barang, $file_name)
    {
        $this->db->where('id_barang', $id_barang);
        return $this->db->update('tb_barangv2', array('gbr_produk' => $file_name));
    }

    private function build_catalog_ajax_query($filters = array())
    {
        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';
        $filter_fokus = isset($filters['filter_fokus']) ? trim((string) $filters['filter_fokus']) : '';
        $filter_online = isset($filters['filter_online']) ? trim((string) $filters['filter_online']) : '';
        $sort = isset($filters['sort']) ? trim((string) $filters['sort']) : 'fokus';

        $this->db->from('tb_barangv2');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('kode_barang', $search);
            $this->db->or_like('nama_barang', $search);
            $this->db->or_like('nama_suplier', $search);
            $this->db->or_like('kelompok', $search);
            $this->db->or_like('bahan_aktif', $search);
            $this->db->group_end();
        }

        if ($filter_fokus !== '') {
            if ($filter_fokus === 'kosong') {
                $this->db->where('(produk_fokus IS NULL OR produk_fokus = "")', null, false);
            } else {
                $this->db->where('produk_fokus', $filter_fokus);
            }
        }

        if ($filter_online !== '') {
            if ($filter_online === 'shopee') {
                $this->db->where('shopee', 1);
            } elseif ($filter_online === 'tokopedia') {
                $this->db->where('tokopedia', 1);
            } elseif ($filter_online === 'kiushop') {
                $this->db->where('kiushop', 1);
            } elseif ($filter_online === 'kosong') {
                $this->db->where('shopee', 0);
                $this->db->where('tokopedia', 0);
                $this->db->where('kiushop', 0);
            }
        }

        switch ($sort) {
            case 'nama_asc':
                $this->db->order_by('nama_barang', 'ASC');
                break;
            case 'nama_desc':
                $this->db->order_by('nama_barang', 'DESC');
                break;
            case 'supplier':
                $this->db->order_by('nama_suplier', 'ASC');
                $this->db->order_by('nama_barang', 'ASC');
                break;
            case 'terbaru':
                $this->db->order_by('id_barang', 'DESC');
                break;
            case 'fokus':
            default:
                $this->db->order_by('produk_fokus', 'DESC');
                $this->db->order_by('id_barang', 'DESC');
                break;
        }
    }

    public function get_catalog_ajax($filters = array(), $limit = 12, $offset = 0)
    {
        $this->build_catalog_ajax_query($filters);
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result();
    }

    public function count_catalog_ajax($filters = array())
    {
        $this->build_catalog_ajax_query($filters);
        return $this->db->count_all_results();
    }
}
