<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Pricelist
 * 
 * Modul Manajemen & Monitoring Pricelist Barang terintegrasi LPB
 */
class C_Pricelist extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Pricelist');
        $this->load->library('session');
    }

    /**
     * Halaman Utama Pricelist Barang
     */
    public function index()
    {
        $data['page_title'] = 'ERP KARISMA - Pricelist Barang (Terintegrasi LPB)';
        
        // Ambil master kelompok dagang untuk filter dropdown
        $data['kelompok_dagang'] = $this->db->query("SELECT DISTINCT kelompok_dagang FROM tbpo_barang WHERE COALESCE(kelompok_dagang, '') != '' ORDER BY kelompok_dagang ASC")->result_array();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/pricelist/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Response Data JSON Datatable / AJAX
     */
    public function ajax_data()
    {
        $search = $this->input->post('search')['value'] ?? $this->input->get('search') ?? '';
        $kelompokDagang = $this->input->post('kelompok_dagang') ?? $this->input->get('kelompok_dagang') ?? '';
        $tier = $this->input->post('tier') ?? $this->input->get('tier') ?? 'REGULAR';
        $start = (int)($this->input->post('start') ?? 0);
        $length = (int)($this->input->post('length') ?? 50);

        if ($length <= 0) $length = 50;

        $result = $this->M_Pricelist->get_pricelist_rows($search, $kelompokDagang, $tier, $length, $start);

        echo json_encode([
            'draw' => (int)($this->input->post('draw') ?? 1),
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['total'],
            'data' => $result['rows']
        ]);
    }

    /**
     * Memperbarui Margin / Harga Jual via Modal AJAX
     */
    public function update_margin()
    {
        $idPricelist = (int)$this->input->post('id_pricelist');
        $marginPersen = (float)$this->input->post('margin_persen');
        $marginNominal = (float)$this->input->post('margin_nominal');
        $user = $this->session->userdata('username') ?? 'ADMIN';

        if ($idPricelist <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID Pricelist tidak valid.']);
            return;
        }

        $res = $this->M_Pricelist->update_margin($idPricelist, $marginPersen, $marginNominal, $user);
        echo json_encode($res);
    }

    /**
     * Merekalkulasi seluruh HPP Average dari LPB & memperbarui Pricelist
     */
    public function recalculate_all()
    {
        $user = $this->session->userdata('username') ?? 'SYSTEM';
        $res = $this->M_Pricelist->recalculate_all_items($user);
        echo json_encode($res);
    }

    /**
     * Ambil histori perubahan harga per barang (AJAX)
     */
    public function history()
    {
        $kdBarang = trim((string)$this->input->get('kd_barang'));
        if ($kdBarang === '') {
            echo json_encode(['success' => false, 'data' => []]);
            return;
        }

        $history = $this->db->where('kd_barang', $kdBarang)
            ->order_by('changed_at', 'DESC')
            ->limit(30)
            ->get('tb_pricelist_barang_history')
            ->result_array();

        echo json_encode(['success' => true, 'data' => $history]);
    }
}
