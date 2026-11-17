<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pricelist extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Pricelist");
    }

    function index()
    {
        $id = $this->input->get('id');
        $data['title'] = $this->M_Pricelist->getInfo($id);
        $data['price'] = $this->M_Pricelist->getPricelist($id)->result();
        $data['sprice'] = $this->M_Pricelist->getDetail($id)->result();
        $data['dropdown'] = $this->M_Pricelist->getDropdown($id);
    }



    public function updatePrice()
    {
        $idprice    = $this->input->post("id_price_isi");
        $post_kdbar = $this->input->post('kdbarang_isi');
        $post_qty   = $this->input->post('qtyisi');
        $hrg        = $this->input->post('hrgisi');

        $data = array(
            'id_price'      => $idprice,
            'kode_barang'   => $post_kdbar,
            'qty'           => $post_qty,
            'hrg'           => $hrg
        );
        $this->m_Pricelist->editPrice($data, $idprice);
        redirect('admin/katalog');
    }

    public function insertQty()
    {
        $idprice    = $this->input->post("id_price_isi");
        $post_kdbar = $this->input->post("kdbarangisi");
        $post_qty   = $this->input->post("qtyisi");
        $hrg        = $this->input->post("hrgisi");

        $data = array(
            'id_price'      => $idprice,
            'kode_barang'   => $post_kdbar,
            'qty'           => $post_qty,
            'harga'         => $hrg
        );

        if ($this->M_Pricelist->checkQty($post_qty, $post_kdbar)) {
            $error = "silahkan isi kuantitas yang berbeda";
            $this->session->set_flashdata('message_error', $error);
            redirect('admin/katalog');
        } else {
            $this->M_Pricelist->insertPrice($data);
            $success = "data telah tersimpan";
            $this->session->set_flashdata('message_success', $success);
            redirect('admin/katalog');
        }
    }

    public function checkdata($post_qty, $post_kdbar)
    {
        return $this->M_Pricelist->checkQty($post_qty, $post_kdbar);
    }
    
}
