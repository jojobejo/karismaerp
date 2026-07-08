<?php
// views/content/sales/retur/admin_stock_list.php
$role_config = [
    'icon'         => 'boxes',
    'color'        => 'info',
    'action_base'  => 'retur_penjualan/admin_stock/cek/',
    'action_label' => 'Cek Fisik',
];
$this->load->view('content/sales/retur/_spr_queue_list_base', array_merge(get_defined_vars(), ['role_config' => $role_config]));
