<?php
// views/content/sales/retur/koor_sc_list.php
$role_config = [
    'icon'         => 'clipboard-check',
    'color'        => 'warning',
    'action_base'  => 'retur_penjualan/koor_sc/verifikasi/',
    'action_label' => 'Verifikasi',
];
$this->load->view('content/sales/retur/_spr_queue_list_base', array_merge(get_defined_vars(), ['role_config' => $role_config]));
