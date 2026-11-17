<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pricelist extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Pricelist");
        $this->load->model("M_Katalog");
        $this->load->helper('form');
    }

    function index()
    {
        $id = $this->input->get('id');
        $kd = $this->input->get('kd');

        $data['title'] = $this->M_Pricelist->getInfo($id);
        $data['harga'] = $this->M_Pricelist->getDetail($id)->result();
        $data['hargas'] = $this->M_Pricelist->getHarga($id)->result();
        $data['price'] = $this->M_Pricelist->getPricelist($id)->result();
        $data['prices'] = $this->M_Pricelist->getPricelists($id)->result();
        $data['dprice'] = $this->M_Pricelist->getDtPeicelist($id)->result();
        $data['sprice'] = $this->M_Pricelist->getSpPricelist($id)->result();
        $data['checkH1'] = $this->M_Pricelist->checkPricelist1($kd)->result();
        $data['detHarga'] = $this->M_Pricelist->getAllHarga();
        $data['pricelist_data'] = $this->M_Pricelist->getPricelistNew($id);

        $this->load->view("partial/katalog/header");
        $this->load->view("content/pricelist/dashboard", $data);
        $this->load->view("partial/katalog/footer");
    }

    public function updatePrice()
    {
        $idprice = $this->input->post("id_isi");
        $kdbarang = $this->input->post('kd_isi');
        $r1      = $this->input->post('r1_isi');
        $r2      = $this->input->post('r2_isi');
        $r3      = $this->input->post('r3_isi');
        $r4      = $this->input->post('r4_isi');

        $data = array(
            'id'  => $idprice,
            'harga_r1'  => $r1,
            'harga_r2'  => $r2,
            'harga_program'  => $r3,
            'harga_online'  => $r4
        );
        $this->M_Pricelist->editPrice($data, $idprice);
        redirect('pricelist?id=' . $kdbarang);
    }

    public function insertQty()
    {
        $kd_barang = $this->input->post("kdbarang");
        $nmbarang = $this->input->post("nm_barang");
        $hargaR1 = $this->input->post("harga_isi1");
        $hargaR2 = $this->input->post("harga_isi2");
        $hargaR3 = $this->input->post("harga_isi3");
        $hargaR4 = $this->input->post("harga_isi4");

        $data = array(
            'kode_barang' => $kd_barang,
            'nama_barang' => $nmbarang,
            'harga_r1' => $hargaR1,
            'harga_r2' => $hargaR2,
            'harga_program' => $hargaR3,
            'harga_online' => $hargaR4,
        );

        $this->M_Pricelist->insertHarga($data);
        redirect('pricelist?id=' . $kd_barang);
    }

    public function checkdata($post_qty, $post_kdbar)
    {
        return $this->M_Pricelist->checkQty($post_qty, $post_kdbar);
    }

    public function insertQtyPrice()
    {
        $kodebarang = $this->input->post('kdbarang');
        $namabarang = $this->input->post('nm_barang');
        $hargaQty1  = $this->input->post('hr_1');
        $hargaQty2  = $this->input->post('hr_2');
        $hargaQty3  = $this->input->post('hr_3');
        $hargaQty4  = $this->input->post('hr_4');
        $ketQty1    = $this->input->post('ket_1');
        $ketQty2    = $this->input->post('ket_2');
        $ketQty3    = $this->input->post('ket_3');
        $ketQty4    = $this->input->post('ket_4');

        $data = array(
            'kode_barang'   => $kodebarang,
            'nama_barang'   => $namabarang,
            'qty_1'         => $hargaQty1,
            'qty_2'         => $hargaQty2,
            'qty_3'         => $hargaQty3,
            'qty_4'         => $hargaQty4,
            'ket1'          => $ketQty1,
            'ket2'          => $ketQty2,
            'ket3'          => $ketQty3,
            'ket4'          => $ketQty4
        );

        $this->M_Pricelist->insertQtyPrice($data);
        redirect('pricelist?id=' . $kodebarang);
    }

    public function editSpecialPrice()
    {
        $id = $this->input->get('id');

        $idsprice = $this->input->post("id_isi");
        $kd_barang = $this->input->post("kode_barang_isi");
        $hargaR1 = $this->input->post("harga_isi1");
        $hargaR2 = $this->input->post("harga_isi2");
        $hargaR3 = $this->input->post("harga_isi3");
        $hargaR4 = $this->input->post("harga_isi4");

        $data = array(
            'id_barang' => $idsprice,
            'harga_r1' => $hargaR1,
            'harga_r2' => $hargaR2,
            'harga_program' => $hargaR3,
            'harga_online' => $hargaR4,
        );

        $this->M_Pricelist->updateSpecial($data, $idsprice);
        redirect('pricelist?id=' . $kd_barang);
    }

    public function editHargaPrice()
    {
        $idharga = $this->input->post('idharga');
        $kodebarang = $this->input->post('kdbarang');
        $namabarang = $this->input->post('nm_barang');
        $hargaQty1  = $this->input->post('hr_1');
        $hargaQty2  = $this->input->post('hr_2');
        $hargaQty3  = $this->input->post('hr_3');
        $hargaQty4  = $this->input->post('hr_4');
        $ketQty1    = $this->input->post('ket_1');
        $ketQty2    = $this->input->post('ket_2');
        $ketQty3    = $this->input->post('ket_3');
        $ketQty4    = $this->input->post('ket_4');

        $data = array(
            'kode_barang'   => $kodebarang,
            'nama_barang'   => $namabarang,
            'qty_1'         => $hargaQty1,
            'qty_2'         => $hargaQty2,
            'qty_3'         => $hargaQty3,
            'qty_4'         => $hargaQty4,
            'ket1'          => $ketQty1,
            'ket2'          => $ketQty2,
            'ket3'          => $ketQty3,
            'ket4'          => $ketQty4
        );

        $this->M_Pricelist->editQtyPrice($data, $idharga);
        redirect('pricelist?id=' . $kodebarang);
    }

    public function hapusHargaEcer($id, $kode)
    {
        $this->M_Pricelist->deleteHargaEcer($id);
        redirect('pricelist?id=' . $kode);
    }
    public function hapusQty($id, $kode)
    {
        $this->M_Pricelist->deleteHargaQty($id);
        redirect('pricelist?id=' . $kode);
    }

    public function addPromoImg1()
    {

        // // Tambahkan validasi akses
        // $hak = $this->session->userdata('hak_akses');
        // if ($hak != '1' && $hak != '2') {
        //     redirect('pricelist');
        //     return;
        // }

        $id_barang = $this->input->post("id_bar");
        $kd_barang = $this->input->post("kode_barang_isi");
        $nama_bar = $this->input->post("nama_barang_isi");
        $prdk_fks = $this->input->post("produk_fks_isi");
        $nama_sup = $this->input->post("nama_suplier_isi");
        $ktg_prodk = $this->input->post("katagori_isi");

        if (!empty($_FILES['gambar_1'])) {
            $config['upload_path'] = './images/kontrak/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
            $config['max_size'] = '10000';
            $config['max_width'] = '6000';
            $config['max_height'] = '6000';
            $config['overwrite'] = TRUE;
            $config['file_name'] = 'PROMO-1-' . date('Y') . date('m') . date('U') . '_' . $_FILES['gambar_1']['name'];
            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            // Tambahkan ini agar MIME type dikenali dengan benar
            $config['mime_types'] = array(
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp'
            );

            if (!$this->upload->do_upload('gambar_1')) {
                $error = array('error' => $this->upload->display_errors());
                print_r($error);
                die;
            } else {
                if ($this->upload->do_upload('gambar_1')) {
                    $image_data1 = $this->upload->data();
                    $full_path1 = $config['file_name'];
                    $data["gbr_promo1"] = $full_path1;
                }
            }
        }

        $data = array(
            'id_barang' => $id_barang,
            'kode_barang' => $kd_barang,
            'nama_barang' => $nama_bar,
            'nama_suplier' => $nama_sup,
            'produk_fokus' => $prdk_fks,
            'bahan_aktif' => $ktg_prodk,
            'gbr_promo1' => $image_data1['file_name']
        );

        $this->M_Pricelist->addPromo1($data, $id_barang);
        redirect('pricelist?id=' . $kd_barang);
    }

    public function addPromoImg2()
    {

        // $hak = $this->session->userdata('hak_akses');
        // if ($hak != '1' && $hak != '2') {
        //     redirect('pricelist');
        //     return;
        // }


        $id_barang = $this->input->post("id_bar");
        $kd_barang = $this->input->post("kode_barang_isi");
        $nama_bar = $this->input->post("nama_barang_isi");
        $prdk_fks = $this->input->post("produk_fks_isi");
        $nama_sup = $this->input->post("nama_suplier_isi");
        $ktg_prodk = $this->input->post("katagori_isi");

        if (!empty($_FILES['gambar_1'])) {
            $config['upload_path'] = './images/kontrak/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
            $config['max_size'] = '10000';
            $config['max_width'] = '6000';
            $config['max_height'] = '6000';
            $config['overwrite'] = TRUE;
            $config['file_name'] = 'PROMO-2-' . date('Y') . date('m') . date('U') . '_' . $_FILES['gambar_1']['name'];
            $this->load->library('upload', $config);
            $this->upload->initialize($config);;

            if (!$this->upload->do_upload('gambar_1')) {
                $error = array('error' => $this->upload->display_errors());
                print_r($error);
                die;
            } else {
                if ($this->upload->do_upload('gambar_1')) {
                    $image_data1 = $this->upload->data();
                    $full_path1 = $config['file_name'];
                    $data["gbr_promo2"] = $full_path1;
                }
            }
        }

        $data = array(
            'id_barang' => $id_barang,
            'kode_barang' => $kd_barang,
            'nama_barang' => $nama_bar,
            'nama_suplier' => $nama_sup,
            'produk_fokus' => $prdk_fks,
            'bahan_aktif' => $ktg_prodk,
            'gbr_promo2' => $image_data1['file_name']
        );

        $this->M_Pricelist->addPromo1($data, $id_barang);
        redirect('pricelist?id=' . $kd_barang);
    }

    public function gambarproduk_br()
    {

        $hak = $this->session->userdata('hak_akses');
        if ($hak != '1' && $hak != '2' && $hak != '4') {
            redirect('pricelist');
            return;
        }

        $id_barang = $this->input->post("id_bar");
        $kd_barang = $this->input->post("kode_barang_isi");
        $nama_bar  = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->input->post("nama_barang_isi"));

        if (!empty($_FILES['gambar_1']['name'])) {

            $config['upload_path']   = './images/produk/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size']      = 10000;
            $config['max_width']     = 6000;
            $config['max_height']    = 6000;
            $config['overwrite']     = TRUE;
            $config['file_name']     = $nama_bar . "_" . time();

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('gambar_1')) {
                echo $this->upload->display_errors();
                exit;
            }

            $image_data = $this->upload->data();
            $file_name  = $image_data['file_name'];

            $data = [
                'gbr_produk' => $file_name
            ];

            $this->M_Pricelist->addGbrproduk($data, $id_barang);
        }

        redirect('pricelist?id=' . $kd_barang);
    }

    public function addPromoImg3()
    {

        // $hak = $this->session->userdata('hak_akses');
        // if ($hak != '1' && $hak != '2') {
        //     redirect('pricelist');
        //     return;
        // }

        $id_barang = $this->input->post("id_bar");
        $kd_barang = $this->input->post("kode_barang_isi");
        $nama_bar = $this->input->post("nama_barang_isi");
        $prdk_fks = $this->input->post("produk_fks_isi");
        $nama_sup = $this->input->post("nama_suplier_isi");
        $ktg_prodk = $this->input->post("katagori_isi");

        if (!empty($_FILES['gambar_1'])) {
            $config['upload_path'] = './images/kontrak/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
            $config['max_size'] = '10000';
            $config['max_width'] = '6000';
            $config['max_height'] = '6000';
            $config['overwrite'] = TRUE;
            $config['file_name'] = 'PROMO-3-' . date('Y') . date('m') . date('U') . '_' . $_FILES['gambar_1']['name'];
            $this->load->library('upload', $config);
            $this->upload->initialize($config);;

            if (!$this->upload->do_upload('gambar_1')) {
                $error = array('error' => $this->upload->display_errors());
                print_r($error);
                die;
            } else {
                if ($this->upload->do_upload('gambar_1')) {
                    $image_data1 = $this->upload->data();
                    $full_path1 = $config['file_name'];
                    $data["gbr_promo3"] = $full_path1;
                }
            }
        }

        $data = array(
            'id_barang' => $id_barang,
            'kode_barang' => $kd_barang,
            'nama_barang' => $nama_bar,
            'nama_suplier' => $nama_sup,
            'produk_fokus' => $prdk_fks,
            'bahan_aktif' => $ktg_prodk,
            'gbr_promo3' => $image_data1['file_name']
        );

        $this->M_Pricelist->addPromo1($data, $id_barang);
        redirect('pricelist?id=' . $kd_barang);
    }

    public function convertRtoQty()
    {
        $idR        = $this->input->post('id_harga');
        $nm_barang  = $this->input->post('nm_barang');
        $kd_barang  = $this->input->post('kdbarang');
        $hargaQty1  = $this->input->post('hr_1');
        $hargaQty2  = $this->input->post('hr_2');
        $hargaQty3  = $this->input->post('hr_3');
        $hargaQty4  = $this->input->post('hr_4');
        $ketQty1    = $this->input->post('ket_1');
        $ketQty2    = $this->input->post('ket_2');
        $ketQty3    = $this->input->post('ket_3');
        $ketQty4    = $this->input->post('ket_4');

        $dataInsert = array(
            'kode_barang'   => $kd_barang,
            'nama_barang'   => $nm_barang,
            'qty_1'         => $hargaQty1,
            'qty_2'         => $hargaQty2,
            'qty_3'         => $hargaQty3,
            'qty_4'         => $hargaQty4,
            'ket1'          => $ketQty1,
            'ket2'          => $ketQty2,
            'ket3'          => $ketQty3,
            'ket4'          => $ketQty4
        );
        $this->M_Pricelist->insertConvertQty($dataInsert);
        $this->M_Pricelist->deleteConvertQty($idR);
        redirect('pricelist?id=' . $kd_barang);
    }

    public function convertToNett()
    {
        $idNett     = $this->input->post('idbarang');
        $nm_barang  = $this->input->post('nm_barang');
        $kd_barang  = $this->input->post('kdbarang');
        $hr_1       = $this->input->post('harga_isi1');
        $hr_2       = $this->input->post('harga_isi2');
        $hr_3       = $this->input->post('harga_isi3');
        $hr_4       = $this->input->post('harga_isi4');

        $dataInsert = array(
            'kode_barang'   => $kd_barang,
            'nama_barang'   => $nm_barang,
            'harga_r1'      => $hr_1,
            'harga_r2'      => $hr_2,
            'harga_program' => $hr_3,
            'harga_online'  => $hr_4
        );
        $this->M_Pricelist->insertConvertNett($dataInsert);
        $this->M_Pricelist->deleteConvertNett($idNett);
        redirect('pricelist?id=' . $kd_barang);
    }

    public function add()
    {
        $kode_barang = $this->input->post('id_barang');
        $satuan = $this->input->post('satuan');
        $r1 = $this->input->post('r1');
        $r2 = $this->input->post('r2');
        $umum = $this->input->post('umum');
        
        // Ambil data barang berdasarkan kode_barang
        $this->db->where('kode_barang', $kode_barang);
        $query = $this->db->get('tb_barangv2');
        $barang_info = $query->row_array();
        
        if (!$barang_info) {
            $this->session->set_flashdata('error', 'Data barang tidak ditemukan');
            redirect('pricelist?id=' . $kode_barang);
            return;
        }
        
        // Cek apakah sudah ada data pricelist untuk barang ini
        $this->db->where('kode_barang', $kode_barang);
        $existing = $this->db->get('tb_pricelist')->row_array();
        
        if ($existing) {
            // Update data yang sudah ada - cari slot kosong
            $update_data = array();
            
            if (empty($existing['ket1'])) {
                // Slot 1 kosong
                $update_data = array(
                    'qty_1_r1' => $r1 ? $r1 : 0,
                    'qty_1_r2' => $r2 ? $r2 : 0,
                    'qty_1_umum' => $umum ? $umum : 0,
                    'ket1' => $satuan
                );
            } elseif (empty($existing['ket2'])) {
                // Slot 2 kosong
                $update_data = array(
                    'qty_2_r1' => $r1 ? $r1 : 0,
                    'qty_2_r2' => $r2 ? $r2 : 0,
                    'qty_2_umum' => $umum ? $umum : 0,
                    'ket2' => $satuan
                );
            } elseif (empty($existing['ket3'])) {
                // Slot 3 kosong
                $update_data = array(
                    'qty_3_r1' => $r1 ? $r1 : 0,
                    'qty_3_r2' => $r2 ? $r2 : 0,
                    'qty_3_umum' => $umum ? $umum : 0,
                    'ket3' => $satuan
                );
            } elseif (empty($existing['ket4'])) {
                // Slot 4 kosong
                $update_data = array(
                    'qty_4_r1' => $r1 ? $r1 : 0,
                    'qty_4_r2' => $r2 ? $r2 : 0,
                    'qty_4_umum' => $umum ? $umum : 0,
                    'ket4' => $satuan
                );
            } else {
                // Semua slot penuh
                $this->session->set_flashdata('error', 'Pricelist sudah penuh (maksimal 4 satuan)');
                redirect('pricelist?id=' . $kode_barang);
                return;
            }
            
            // Update record yang ada
            $this->db->where('id_pricelist', $existing['id_pricelist']);
            $this->db->update('tb_pricelist', $update_data);
            
        } else {
            // Insert data baru jika belum ada
            $data = array(
                'kode_barang' => $barang_info['kode_barang'],
                'nama_barang' => $barang_info['nama_barang'],
                'qty_1_r1' => $r1 ? $r1 : 0,
                'qty_1_r2' => $r2 ? $r2 : 0,
                'qty_1_umum' => $umum ? $umum : 0,
                'ket1' => $satuan,
                'qty_2_r1' => 0,
                'qty_2_r2' => 0,
                'qty_2_umum' => 0,
                'ket2' => '',
                'qty_3_r1' => 0,
                'qty_3_r2' => 0,
                'qty_3_umum' => 0,
                'ket3' => '',
                'qty_4_r1' => 0,
                'qty_4_r2' => 0,
                'qty_4_umum' => 0,
                'ket4' => ''
            );
            
            $this->db->insert('tb_pricelist', $data);
        }
        
        $this->session->set_flashdata('success', 'Pricelist berhasil ditambahkan');
        redirect('pricelist?id=' . $barang_info['kode_barang']);
    }

    public function delete_item()
    {
        $id = $this->input->post('id_pricelist');
        $qty_slot = $this->input->post('qty_slot');
        $kode_barang = $this->input->post('kode_barang');
        
        // Kosongkan slot yang dipilih
        $data = array();
        
        if ($qty_slot == 'qty_1') {
            $data = array(
                'qty_1_r1' => 0,
                'qty_1_r2' => 0,
                'qty_1_umum' => 0,
                'ket1' => ''
            );
        } elseif ($qty_slot == 'qty_2') {
            $data = array(
                'qty_2_r1' => 0,
                'qty_2_r2' => 0,
                'qty_2_umum' => 0,
                'ket2' => ''
            );
        } elseif ($qty_slot == 'qty_3') {
            $data = array(
                'qty_3_r1' => 0,
                'qty_3_r2' => 0,
                'qty_3_umum' => 0,
                'ket3' => ''
            );
        } elseif ($qty_slot == 'qty_4') {
            $data = array(
                'qty_4_r1' => 0,
                'qty_4_r2' => 0,
                'qty_4_umum' => 0,
                'ket4' => ''
            );
        }
        
        $this->db->where('id_pricelist', $id);
        $this->db->update('tb_pricelist', $data);
        
        // Cek apakah semua slot kosong, jika ya hapus record
        $this->db->where('id_pricelist', $id);
        $check = $this->db->get('tb_pricelist')->row_array();
        
        if (empty($check['ket1']) && empty($check['ket2']) && empty($check['ket3']) && empty($check['ket4'])) {
            $this->db->where('id_pricelist', $id);
            $this->db->delete('tb_pricelist');
        }
        
        $this->session->set_flashdata('success', 'Item pricelist berhasil dihapus');
        redirect('pricelist?id=' . $kode_barang);
    }

    public function edit()
    {
        $id = $this->input->post('id_pricelist');
        $kode_barang = $this->input->post('kode_barang');
        $qty_slot = $this->input->post('qty_slot'); // qty_1, qty_2, qty_3, atau qty_4
        $satuan = $this->input->post('satuan');
        $r1 = $this->input->post('r1');
        $r2 = $this->input->post('r2');
        $umum = $this->input->post('umum');
        
        // Tentukan field mana yang akan diupdate berdasarkan slot
        $data = array();
        
        if ($qty_slot == 'qty_1') {
            $data = array(
                'qty_1_r1' => $r1 ? $r1 : 0,
                'qty_1_r2' => $r2 ? $r2 : 0,
                'qty_1_umum' => $umum ? $umum : 0,
                'ket1' => $satuan
            );
        } elseif ($qty_slot == 'qty_2') {
            $data = array(
                'qty_2_r1' => $r1 ? $r1 : 0,
                'qty_2_r2' => $r2 ? $r2 : 0,
                'qty_2_umum' => $umum ? $umum : 0,
                'ket2' => $satuan
            );
        } elseif ($qty_slot == 'qty_3') {
            $data = array(
                'qty_3_r1' => $r1 ? $r1 : 0,
                'qty_3_r2' => $r2 ? $r2 : 0,
                'qty_3_umum' => $umum ? $umum : 0,
                'ket3' => $satuan
            );
        } elseif ($qty_slot == 'qty_4') {
            $data = array(
                'qty_4_r1' => $r1 ? $r1 : 0,
                'qty_4_r2' => $r2 ? $r2 : 0,
                'qty_4_umum' => $umum ? $umum : 0,
                'ket4' => $satuan
            );
        }
        
        $this->M_Pricelist->updatePricelist($data, $id);
        $this->session->set_flashdata('success', 'Pricelist berhasil diupdate');
        redirect('pricelist?id=' . $kode_barang);
    }
}
