<?php
// views/content/sales/retur/logistik_list.php
$role_config = [
    'icon'         => 'truck-loading',
    'color'        => 'success',
    'action_base'  => 'retur_penjualan/logistik/proses/',
    'action_label' => 'Proses Retur',
];
$this->load->view('content/sales/retur/_spr_queue_list_base', array_merge(get_defined_vars(), ['role_config' => $role_config]));
