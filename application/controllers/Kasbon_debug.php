<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper debug sementara — akses via /karismaerp/kasbon_debug
 * Hapus file ini setelah debugging selesai.
 */
class Kasbon_debug extends CI_Controller
{
    public function index()
    {
        if (!$this->session->userdata('username')) {
            die('Silakan login terlebih dahulu.');
        }

        $this->load->model('M_Kasbon');

        $user = [
            'id'      => $this->session->userdata('id'),
            'nama'    => $this->session->userdata('nama'),
            'username'=> $this->session->userdata('username'),
            'jobdesk' => strtoupper((string)$this->session->userdata('jobdesk')),
            'lv'      => $this->session->userdata('lv'),
        ];

        echo '<pre>';
        echo "=== SESSION USER ===\n";
        print_r($user);

        echo "\n=== SEMUA DATA KASBON ===\n";
        $semua = $this->M_Kasbon->get_all_kasbon();
        foreach ($semua as $row) {
            echo "ID:{$row['id']} | No:{$row['no_kasbon']} | Status:{$row['status']} | approver_1:{$row['approver_1']} | approver_2:{$row['approver_2']}\n";
        }

        echo "\n=== KASBON UNTUK USER INI (get_kasbon_for_user) ===\n";
        $filtered = $this->M_Kasbon->get_kasbon_for_user($user);
        if (empty($filtered)) {
            echo "(Tidak ada hasil)\n";
        } else {
            foreach ($filtered as $row) {
                echo "ID:{$row['id']} | No:{$row['no_kasbon']} | Status:{$row['status']} | approver_1:{$row['approver_1']}\n";
            }
        }

        echo "\n=== TEST CAN_APPROVE (per baris) ===\n";
        foreach ($semua as $row) {
            $boleh = $this->M_Kasbon->can_approve($row, $user) ? 'YA' : 'TIDAK';
            echo "ID:{$row['id']} | Status:{$row['status']} | approver_1:{$row['approver_1']} | Can Approve: {$boleh}\n";
        }

        echo "\n=== LAST DB QUERY ===\n";
        echo $this->db->last_query();
        echo '</pre>';
    }
}
