<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Organisasi extends CI_Controller
{
    private $entities = ['departemen', 'jobdesk', 'jabatan'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('master/M_Organisasi', 'model');
        $this->load->library('permission');
        $this->permission->guard('master/user-management');
    }

    private function respond($status, $message, $data = [])
    {
        while (ob_get_level()) ob_end_clean();
        $this->output->set_content_type('application/json')->set_output(json_encode(['status' => (bool)$status, 'message' => $message, 'data' => $data]));
    }

    private function validEntity($entity)
    {
        if (!in_array($entity, $this->entities, true)) { show_404(); return false; }
        return true;
    }

    public function index()
    {
        $data['page_title'] = 'KARISMA ERP - Setting Master Kantor';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/master/organisasi/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function list($entity = '')
    {
        if (!$this->validEntity($entity)) return;
        $this->output->set_content_type('application/json')->set_output(json_encode($this->model->datatable($entity, $this->input->post(null, true) ?: [])));
    }

    public function detail($entity = '', $id = 0)
    {
        if (!$this->validEntity($entity)) return;
        $row = $this->model->find($entity, $id);
        $this->respond((bool)$row, $row ? 'Data ditemukan.' : 'Data tidak ditemukan.', $row ?: []);
    }

    public function save($entity = '', $id = 0)
    {
        if (!$this->validEntity($entity)) return;
        $input = $this->input->post(null, true) ?: [];
        $nameField = 'nama_' . $entity;
        $name = trim((string)($input[$nameField] ?? ''));
        if ($name === '') return $this->respond(false, 'Nama ' . $entity . ' wajib diisi.');
        if ($this->model->nameExists($entity, $name, (int)$id ?: null)) {
            return $this->respond(false, 'Nama ' . $entity . ' sudah digunakan.');
        }
        $ok = $this->model->save($entity, $input, (int)$id ?: null);
        $this->respond((bool)$ok, $ok ? 'Data berhasil disimpan.' : 'Nama wajib diisi atau data gagal disimpan.', ['id' => is_numeric($ok) ? $ok : (int)$id]);
    }

    public function delete($entity = '', $id = 0)
    {
        if (!$this->validEntity($entity)) return;
        $ok = $this->model->delete($entity, $id);
        $this->respond($ok, $ok ? 'Data berhasil dihapus.' : 'Data gagal dihapus.');
    }
}
