<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Api', 'preDo');

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
    }

    /**
     * GET /api/predo
     * ?limit=50&offset=0
     */

    public function index()
    {
        $limit  = (int) $this->input->get('limit') ?: 100;
        $offset = (int) $this->input->get('offset') ?: 0;

        $data = $this->preDo->get_all($limit, $offset);

        echo json_encode([
            'status' => true,
            'total'  => count($data),
            'data'   => $data
        ]);
    }

    /**
     * GET /api/predo/faktur/{kode_faktur}
     */

    public function faktur($kode_faktur)
    {
        if (!$kode_faktur) {
            $this->_bad_request('Kode faktur wajib diisi');
            return;
        }

        $data = $this->preDo->get_by_kode_faktur($kode_faktur);

        echo json_encode([
            'status' => $data ? true : false,
            'data'   => $data
        ]);
    }

    /**
     * GET /api/predo/kdupdate/{kdupdate}
     */
    public function kdupdate($kdupdate)
    {
        if (!$kdupdate) {
            $this->_bad_request('kdupdate wajib diisi');
            return;
        }

        $data = $this->preDo->get_by_kdupdate($kdupdate);

        echo json_encode([
            'status' => true,
            'total'  => count($data),
            'data'   => $data
        ]);
    }

    private function _bad_request($message)
    {
        http_response_code(400);
        echo json_encode([
            'status'  => false,
            'message' => $message
        ]);
    }
}
