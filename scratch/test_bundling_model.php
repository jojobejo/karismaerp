<?php
require 'index.php';
$ci = &get_instance();
$ci->load->model('M_Bundling');
$ci->M_Bundling->ensure_bundling_schema();
$noReq = $ci->M_Bundling->generate_request_number();
$noAsm = $ci->M_Bundling->generate_assembly_number();
$noDsb = $ci->M_Bundling->generate_disassembly_number();
echo "Generated Numbers:\n";
echo "Req: $noReq\nAsm: $noAsm\nDsb: $noDsb\n";
echo "M_Bundling Model Loaded Successfully!\n";
