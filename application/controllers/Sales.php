<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Sales extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Katalog");
        $this->load->library('form_validation');
    }

    function index()
    {
        if ($this->session->userdata('status') != "login") {
            redirect('login');
        }

        $data['tot_gbr'] = $this->M_Katalog->get_total()->result();
        $data['statistik'] = $this->M_Katalog->getStatistik();

        $this->load->view("partial/sales/header");
        $this->load->view("content/sales/katalog/dashboard", $data);
        $this->load->view("partial/sales/footer");
    }

    function getBarang()
    {
        $list = $this->M_Katalog->get_datatables();
        $data = array();

        foreach ($list as $field) {
            $row = array();
            $row[] = $field->kode_barang;
            $row[] = $field->produk_fokus;
            $row[] = $field->nama_barang;
            $row[] = $field->nama_suplier;
            $row[] = $field->kelompok;
            $row[] = $field->bahan_aktif;

            $imagePath = "images/produk/" . $field->gbr_produk;
            if (!file_exists($imagePath)) $imagePath = "images/Karisma.png";
            $row[] = '<img src="' . $imagePath . '" style="width:80px; height:80px; object-fit:cover;">';

            $shopee_badge    = $field->shopee
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> Shopee</span>'
                : '<span class="badge badge-secondary mb-1">Shopee -</span>';
            $tokopedia_badge = $field->tokopedia
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> Tokopedia</span>'
                : '<span class="badge badge-secondary mb-1">Tokopedia -</span>';
            $kiushop_badge   = $field->kiushop
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> KiuShop</span>'
                : '<span class="badge badge-secondary mb-1">KiuShop -</span>';
            $row[] = $shopee_badge . '<br>' . $tokopedia_badge . '<br>' . $kiushop_badge;

            $row[] = '
                <div class="d-flex justify-content-center flex-wrap action-group">
                    <a href="' . base_url('pricelist?id=' . $field->kode_barang) . '" 
                    class="btn btn-primary btn-sm btn-icon mr-1 mb-1" target="_blank" title="Lihat Pricelist">
                        <i class="fa fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-warning btn-sm btn-icon btn-edit-bahan mr-1 mb-1"
                        data-id="' . $field->id_barang . '"
                        data-kode="' . htmlspecialchars($field->kode_barang, ENT_QUOTES, 'UTF-8') . '"
                        data-nama="' . htmlspecialchars($field->nama_barang, ENT_QUOTES, 'UTF-8') . '"
                        data-kelompok="' . htmlspecialchars($field->kelompok, ENT_QUOTES, 'UTF-8') . '"
                        data-bahan="' . htmlspecialchars($field->bahan_aktif, ENT_QUOTES, 'UTF-8') . '"
                        title="Edit Bahan Aktif">
                        <i class="fa fa-pencil-alt"></i>
                    </button>
                    <button type="button" class="btn btn-info btn-sm btn-icon btn-edit-gambar mb-1"
                        data-id="' . $field->id_barang . '"
                        data-kode="' . htmlspecialchars($field->kode_barang, ENT_QUOTES, 'UTF-8') . '"
                        data-nama="' . htmlspecialchars($field->nama_barang, ENT_QUOTES, 'UTF-8') . '"
                        data-image="' . htmlspecialchars($field->gbr_produk, ENT_QUOTES, 'UTF-8') . '"
                        title="Edit Gambar Produk">
                        <i class="fa fa-image"></i>
                    </button>
                </div>
            ';

            $data[] = $row;
        }

        $output = array(
            "draw"            => $_POST['draw'],
            "recordsTotal"    => $this->M_Katalog->count_all(),
            "recordsFiltered" => $this->M_Katalog->count_filtered(),
            "data"            => $data,
        );
        echo json_encode($output);
    }

    public function getBarangAjax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $page = max(1, (int) $this->input->get('page'));
        $per_page = (int) $this->input->get('per_page');
        $per_page = $per_page > 0 ? min($per_page, 48) : 12;
        $filters = array(
            'search' => (string) $this->input->get('search', true),
            'filter_fokus' => (string) $this->input->get('filter_fokus', true),
            'filter_online' => (string) $this->input->get('filter_online', true),
            'sort' => (string) $this->input->get('sort', true)
        );

        $total = $this->M_Katalog->count_catalog_ajax($filters);
        $total_pages = max(1, (int) ceil($total / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $items = $this->M_Katalog->get_catalog_ajax($filters, $per_page, $offset);
        $mapped = array();

        foreach ($items as $item) {
            $gambar = ($item->gbr_produk && $item->gbr_produk !== '-' && file_exists(FCPATH . 'images/produk/' . $item->gbr_produk))
                ? base_url('images/produk/' . $item->gbr_produk)
                : base_url('images/Karisma.png');

            $mapped[] = array(
                'id_barang' => (int) $item->id_barang,
                'kode_barang' => (string) $item->kode_barang,
                'nama_barang' => (string) $item->nama_barang,
                'nama_suplier' => (string) $item->nama_suplier,
                'produk_fokus' => (string) $item->produk_fokus,
                'kelompok' => (string) $item->kelompok,
                'bahan_aktif' => (string) $item->bahan_aktif,
                'gbr_produk' => (string) $item->gbr_produk,
                'image_url' => $gambar,
                'shopee' => (int) $item->shopee,
                'tokopedia' => (int) $item->tokopedia,
                'kiushop' => (int) $item->kiushop,
                'pricelist_url' => base_url('pricelist?id=' . $item->kode_barang)
            );
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => true,
                'items' => $mapped,
                'meta' => array(
                    'page' => $page,
                    'per_page' => $per_page,
                    'total_items' => $total,
                    'total_pages' => $total_pages,
                    'from' => $total > 0 ? ($offset + 1) : 0,
                    'to' => min($offset + $per_page, $total)
                )
            )));
    }

    public function updateBahanAktif()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id_barang = (int) $this->input->post('id_barang');
        $bahan_aktif = trim((string) $this->input->post('bahan_aktif'));
        $kelompok = trim((string) $this->input->post('kelompok'));

        if (!$id_barang || $bahan_aktif === '') {
            return $this->jsonResponse(false, 'Data belum lengkap. Bahan aktif wajib diisi.');
        }

        $barang = $this->M_Katalog->getById($id_barang);
        if (!$barang) {
            return $this->jsonResponse(false, 'Data produk tidak ditemukan.');
        }

        $data = array(
            'bahan_aktif' => $bahan_aktif,
            'kelompok' => $kelompok !== '' ? $kelompok : $barang->kelompok
        );

        $saved = $this->M_Katalog->updateBahanAktif($id_barang, $data);
        if (!$saved) {
            return $this->jsonResponse(false, 'Perubahan bahan aktif gagal disimpan.');
        }

        return $this->jsonResponse(true, 'Bahan aktif berhasil diperbarui.', array(
            'id_barang' => $id_barang,
            'bahan_aktif' => $data['bahan_aktif'],
            'kelompok' => $data['kelompok']
        ));
    }

    public function updateGambarProduk()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id_barang = (int) $this->input->post('id_barang');
        if (!$id_barang) {
            return $this->jsonResponse(false, 'ID barang tidak valid.');
        }

        $barang = $this->M_Katalog->getById($id_barang);
        if (!$barang) {
            return $this->jsonResponse(false, 'Data produk tidak ditemukan.');
        }

        if (empty($_FILES['gambar_produk']['name'])) {
            return $this->jsonResponse(false, 'Silakan pilih gambar produk terlebih dahulu.');
        }

        $upload_path = FCPATH . 'images/produk/';
        if (!is_dir($upload_path) && !@mkdir($upload_path, 0775, true)) {
            return $this->jsonResponse(false, 'Folder upload gambar produk tidak tersedia di server.');
        }

        if (!is_writable($upload_path)) {
            return $this->jsonResponse(false, 'Folder upload gambar produk tidak memiliki izin tulis di server.');
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
        $config['max_size'] = 10000;
        $config['max_width'] = 6000;
        $config['max_height'] = 6000;
        $config['encrypt_name'] = true;

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('gambar_produk')) {
            return $this->jsonResponse(false, strip_tags($this->upload->display_errors()));
        }

        $upload_data = $this->upload->data();
        $file_name = $upload_data['file_name'];

        $saved = $this->M_Katalog->updateGambarProduk($id_barang, $file_name);
        if (!$saved) {
            @unlink(FCPATH . 'images/produk/' . $file_name);
            return $this->jsonResponse(false, 'Gambar produk gagal disimpan ke database.');
        }

        $gambar_lama = $barang->gbr_produk;
        $path_gambar_lama = FCPATH . 'images/produk/' . $gambar_lama;
        if ($gambar_lama && $gambar_lama !== '-' && $gambar_lama !== $file_name && file_exists($path_gambar_lama)) {
            @unlink($path_gambar_lama);
        }

        return $this->jsonResponse(true, 'Gambar produk berhasil diperbarui.', array(
            'id_barang' => $id_barang,
            'image_url' => base_url('images/produk/' . $file_name),
            'file_name' => $file_name
        ));
    }

    private function jsonResponse($status, $message, $data = array())
    {
        $response = array_merge(array(
            'status' => $status,
            'message' => $message
        ), $data);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
