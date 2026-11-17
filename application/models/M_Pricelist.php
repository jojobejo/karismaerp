<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 
 */

class M_Pricelist extends CI_Model
{
    public $id_brg;
    public $nama_brg;
    public $kode_brg;
    public $supp_brg;
    public $sp_price1;
    public $sp_price2;
    public $sp_price3;


    public function getInfo($id_brg)
    {
        $this->db->select('*');
        $this->db->from('tb_barangv2');
        $this->db->where('kode_barang', $id_brg);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row;
        }
    }

    public function getAllHarga()
    {
        $this->db->select('*');
        $this->db->from('tb_pricelist_r');
        return  $this->db->get()->result();
    }

    public function getDetail($id_brg)
    {
        $this->db->select('*');
        $this->db->FROM('tb_pricelist_r');
        $this->db->where('tb_pricelist_r.kode_barang', $id_brg);
        $query = $this->db->get();
        return $query;
    }

    public function getHarga($kd_barang)
    {
        $this->db->select('*');
        $this->db->select('count(id) as total_item');
        $this->db->from('tb_pricelist_r a');
        $this->db->where('a.kode_barang', $kd_barang);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query;
    }

    public function getPricelist($kode_brg)
    {
        $this->db->select('*');
        $this->db->from('tb_pricelist a');
        $this->db->where('a.kode_barang', $kode_brg);
        $query = $this->db->get();
        return $query;
    }
    public function getPricelists($kode_brg)
    {
        $this->db->select('*');
        $this->db->select('count(id_pricelist) as total_item');
        $this->db->from('tb_pricelist');
        $this->db->where('kode_barang', $kode_brg);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query;
    }
    public function getDtPeicelist($id)
    {
        $this->db->select('*');
        $this->db->from('tb_pricelist');
        $this->db->where('id_pricelist', $id);
        $query = $this->db->get();
        return $query;
    }

    public function getSpPricelist($id_brg)
    {
        $this->db->select('*');
        $this->db->from('tb_special_price');
        $this->db->where('kode_barang', $id_brg);
        $query = $this->db->get();
        return $query;
    }

    public function insertQtyPrice($data)
    {
        return $this->db->insert('tb_pricelist', $data);
    }

    public function insertPrice($data)
    {
        return $this->db->insert('tb_price', $data);
    }

    public function deleteHargaEcer($data)
    {
        return $this->db->delete('tb_pricelist_r', array('id' => $data));
    }
    public function deleteHargaQty($data)
    {
        return $this->db->delete('tb_pricelist', array('id_pricelist' => $data));
    }

    public function insertHarga($data)
    {
        return $this->db->insert('tb_pricelist_r', $data);
    }

    public function checkPricelist1($kd)
    {
        return $this->db->query("SELECT
        COUNT(a.id) as total_item FROM tb_pricelist_r a WHERE kode_barang = '$kd'
        ");
    }

    public function checkQty($post_qty, $post_kdbar)
    {
        $this->db->where('qty_jual', $post_qty);
        $this->db->where('kode_barang', $post_kdbar);

        $query = $this->db->get('tb_barang');

        $count_row = $query->num_rows();
        if ($count_row > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function editPrice($data, $idprice)
    {
        $this->db->where('id', $idprice);
        return $this->db->update('tb_pricelist_r', $data);
    }

    public function editQtyPrice($data, $idprice)
    {
        $this->db->where('id_pricelist', $idprice);
        return $this->db->update('tb_pricelist', $data);
    }

    public function insertSprice($data)
    {
        return $this->db->insert('tb_special_price', $data);
    }

    public function updateSpecial($data, $idsprice)
    {
        $this->db->where('id_barang', $idsprice);
        return $this->db->update('tb_barang', $data);
    }
    public function addPromo1($data, $id_barang)
    {
        $this->db->where('id_barang', $id_barang);
        return $this->db->update('tb_barangv2', $data);
    }
    public function addGbrproduk($data, $id_barang)
    {
        $this->db->where('id_barang', $id_barang);
        return $this->db->update('tb_barangv2', $data);
    }
    public function insertConvertQty($data)
    {
        return $this->db->insert('tb_pricelist', $data);
    }
    public function deleteConvertQty($id)
    {
        return $this->db->delete('tb_pricelist_r', array('id' => $id));
    }
    public function insertConvertNett($data)
    {
        return $this->db->insert('tb_pricelist_r', $data);
    }
    public function deleteConvertNett($id)
    {
        return $this->db->delete('tb_pricelist', array('id_pricelist' => $id));
    }
    public function insertPricelistNew($data)
    {
        return $this->db->insert('tb_pricelist', $data);
    }
    //model pricelist
    public function getPricelistNew($kode_barang)
    {
        $this->db->where('kode_barang', $kode_barang);
        $this->db->order_by('id_pricelist', 'ASC');
        $query = $this->db->get('tb_pricelist');
        
        $result = array();
        foreach ($query->result_array() as $row) {
            // Tambahkan semua qty yang ada datanya dengan informasi slot
            if (!empty($row['ket1'])) {
                $result[] = array(
                    'id' => $row['id_pricelist'],
                    'slot' => 'qty_1',
                    'satuan' => $row['ket1'],
                    'r1' => $row['qty_1_r1'],
                    'r2' => $row['qty_1_r2'],
                    'umum' => $row['qty_1_umum']
                );
            }
            if (!empty($row['ket2'])) {
                $result[] = array(
                    'id' => $row['id_pricelist'],
                    'slot' => 'qty_2',
                    'satuan' => $row['ket2'],
                    'r1' => $row['qty_2_r1'],
                    'r2' => $row['qty_2_r2'],
                    'umum' => $row['qty_2_umum']
                );
            }
            if (!empty($row['ket3'])) {
                $result[] = array(
                    'id' => $row['id_pricelist'],
                    'slot' => 'qty_3',
                    'satuan' => $row['ket3'],
                    'r1' => $row['qty_3_r1'],
                    'r2' => $row['qty_3_r2'],
                    'umum' => $row['qty_3_umum']
                );
            }
            if (!empty($row['ket4'])) {
                $result[] = array(
                    'id' => $row['id_pricelist'],
                    'slot' => 'qty_4',
                    'satuan' => $row['ket4'],
                    'r1' => $row['qty_4_r1'],
                    'r2' => $row['qty_4_r2'],
                    'umum' => $row['qty_4_umum']
                );
            }
        }
        
        return $result;
    }

    public function getPricelistById($id)
    {
        $this->db->where('id_pricelist', $id);
        $query = $this->db->get('tb_pricelist');
        return $query->row_array();
    }

    public function deletePricelist($id)
    {
        $this->db->where('id_pricelist', $id);
        return $this->db->delete('tb_pricelist');
    }

    public function updatePricelist($data, $id)
    {
        $this->db->where('id_pricelist', $id);
        return $this->db->update('tb_pricelist', $data);
    }
}
