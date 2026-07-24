<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {
    public function lpb() {
        $this->load->model('M_Logistik');
        $tmpRows = $this->M_Logistik->get_tmp_po_received_posting_rows('Q001/KIU/VII/2026A', 'SYNGE01');
        if (empty($tmpRows)) {
            echo "No tmp rows found\n";
            return;
        }

        $payload = [
            'no_po'       => 'Q001/KIU/VII/2026A',
            'kd_po'       => $tmpRows[0]['kd_po'],
            'kd_suplier'  => 'SYNGE01',
            'nosj'        => 'SJ-TEST-001',
            'tgl_sj'      => '2026-07-24',
            'no_invoice'  => '-',
            'jenis_lpb'   => 'LPB CP',
            'gudang_id'   => 'GDG01',
            'keterangan'  => 'Test',
            'dilakukan_oleh' => 'System',
            'checker_name' => 'System',
            'checker_by' => 'System'
        ];

        $this->db->trans_begin();
        $idLpb = $this->M_Logistik->create_lpb_from_tmp($payload, $tmpRows);

        if (!$idLpb || $this->db->trans_status() === FALSE) {
            echo "FAILED CREATE LPB!\n";
            print_r($this->db->error());
            $this->db->trans_rollback();
        } else {
            echo "SUCCESS CREATE LPB: $idLpb\n";
            $this->M_Logistik->update_pre_po_status_by_kd_po($payload['kd_po'], 2);
            $this->db->trans_commit();

            echo "CALLING ACCOUNTING...\n";
            $this->load->library('Accounting_source_service');
            $accountingResult = $this->accounting_source_service->post_goods_receipt($idLpb, 1);
            print_r($accountingResult);

            echo "CLEANUP...\n";
            $this->db->where('id_lpb', $idLpb)->delete('tb_lpb');
        }
    }
}
