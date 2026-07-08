<?php
// views/content/sales/retur/kadep_sc_list.php
$role_config = [
    'icon'         => 'user-tie',
    'color'        => 'primary',
    'action_base'  => 'retur_penjualan/kadep_sc/approve/',
    'action_label' => 'Tinjau & Setujui',
];
$this->load->view('content/sales/retur/_spr_queue_list_base', array_merge(get_defined_vars(), ['role_config' => $role_config]));
