<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class M_Hrd extends CI_Model
{
    public function get_all_laporan()
    {
        return $this->db->query("SELECT a.*
        FROM tb_lap_distribusi a
        ");
    }
    public function getalltamulb()
    {
        return $this->db->query("SELECT a.*
        FROM tb_tamu_lby a
        ");
    }
    public function get_lap_id($id)
    {
        return $this->db->query("SELECT a.*
        FROM tb_lap_distribusi a
        WHERE id = $id
        ");
    }
    public function addlapdistribusihrd($data)
    {
        return $this->db->insert('tb_lap_distribusi', $data);
    }
    public function editlapdistribusihrd($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_lap_distribusi', $data);
    }
    public function hapus_lap_distribusi_hrd($id)
    {
        return $this->db->delete('tb_lap_distribusi', array("id" => $id));
    }

    public function konfirmtamulb($data)
    {
        return $this->db->insert('tb_tamu', $data);
    }

    public function get_all_tamu_lb()
    {
        return $this->db->query("SELECT a.*
        FROM tb_tamu_lby a
        ");
    }

    public function hapus_lap_tamu_lby($id)
    {
        return $this->db->delete('tb_tamu_lby', array("id" => $id));
    }
    public function addlaptamuhrd($data)
    {
        return $this->db->insert('tb_tamu_lby', $data);
    }
    public function editlaptamu($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_tamu', $data);
    }
    public function hapus_lap_tamu_lb($id)
    {
        return $this->db->delete('tb_tamu_lby', array("id" => $id));
    }
    public function hapus_lap_tamu_hrd($id)
    {
        return $this->db->delete('tb_tamu', array("id" => $id));
    }
    public function get_all_tamu()
    {
        return $this->db->query("SELECT a.*
        FROM tb_tamu a
        ");
    }

    //karyawan keluar masuk 

    public function get_all_laporan_karykm()
    {
        return $this->db->query("SELECT a.*
        FROM tb_karyawan_keluarmasuk a
        ");
    }
    public function addlapkarykm($data)
    {
        return $this->db->insert('tb_karyawan_keluarmasuk', $data);
    }
    public function editlapkarykm($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_karyawan_keluarmasuk', $data);
    }
    public function hapuslapkarykm($id)
    {
        return $this->db->delete('tb_karyawan_keluarmasuk', array("id" => $id));
    }

    //laporan Expedisi

    public function get_all_laporan_expedisi()
    {
        return $this->db->query("SELECT a.*
         FROM tb_expedisi a
         ");
    }
    public function addlapexpedisi($data)
    {
        return $this->db->insert('tb_expedisi', $data);
    }
    public function editlapexpedisi($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_expedisi', $data);
    }
    public function hapuslapexpedisi($id)
    {
        return $this->db->delete('tb_expedisi', array("id" => $id));
    }

    //issue

    public function get_all_laporan_issue()
    {
        return $this->db->query("SELECT a.*
        FROM tb_issue a
        ");
    }
    public function export_lap_issue()
    {
        return $this->db->get('tb_issue')->result();
    }
    public function export_lap_km_karyawan()
    {
        return $this->db->get('tb_karyawan_keluarmasuk')->result();
    }
    public function addlapissue($data)
    {
        return $this->db->insert('tb_issue', $data);
    }
    public function editlapissue($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_issue', $data);
    }
    public function hapuslapissue($id)
    {
        return $this->db->delete('tb_issue', array("id" => $id));
    }
    public function cari_lap_distribusi($v1, $v2)
    {
        return $this->db->query("SELECT a.*
        FROM tb_lap_distribusi a
        WHERE $v1 LIKE '$v2'
        ");
    }
    public function get_all_truk_service_histori()
    {
        return $this->db->query("SELECT a.*
        FROM tb_service_truk a
        ");
    }
    public function update_km_service($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_service_truk', $data);
    }
    public function get_all_karyawan()
    {
        return $this->db->query("SELECT a.*
        FROM tb_user a WHERE id > '4'
        ");
    }
    public function update_karyawan($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_user', $data);
    }
    public function add_karyawan($data)
    {
        return $this->db->insert('tb_user', $data);
    }



    var $table = 'tb_lap_distribusi'; //nama tabel dari database
    var $column_order = array('tglkeluar', 'tglmasuk', 'nopol', 'nolambung', 'namadriver', 'namahelper', 'tujuan', 'jamkeluar', 'kmkeluar', 'jammasuk', 'kmmasuk', 'keterangan', 'id'); //field yang ada di table user
    var $column_search = array('nopol', 'namadriver', 'namahelper', 'tujuan'); //field yang diizin untuk pencarian 
    var $order = array('id' => 'asc'); // default order 

    private function _get_datatables_query()
    {

        $this->db->from($this->table);

        $i = 0;

        foreach ($this->column_search as $item) // looping awal
        {
            if ($_POST['search']['value']) // jika datatable mengirimkan pencarian dengan metode POST
            {

                if ($i === 0) // looping awal
                {
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
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
    
    public function insertchedule($data)
    {
        return $this->db->insert('tb_schedule_dirut', $data);
    }
    public function getdataschedule()
    {
        return $this->db->query("SELECT a.*
        FROM tb_schedule_dirut a
        ");
    }
    public function editchedule($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_schedule_dirut', $data);
    }
    public function reschedule($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_schedule_dirut', $data);
    }
    public function deleteschedule($id)
    {
        return $this->db->delete('tb_schedule_dirut', array("id" => $id));
    }
}
