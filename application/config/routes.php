<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

// Sistem Routes
$route['default_controller'] = 'Auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//Auth Login
$route['process']                                   = 'Auth/process';
$route['logout']                                    = 'Auth/logout';
$route['dashboard']                                 = 'Dashboard';

//DAILY STOCK AHMAD & PENDINGPO
$route['keuangan']                                  = 'keuangan/C_Keuangan';
$route['pendingpo']                                 = 'keuangan/C_Keuangan/pendingpo';
$route['insertmodule']                              = 'keuangan/C_Keuangan/insertmodule';
$route['insermodule_lot']                           = 'keuangan/C_Keuangan/insermodule_lot';
$route['insertmodule_pnd']                          = 'keuangan/C_Keuangan/insertmodule_pnd';
$route['csv_import']                                = 'keuangan/C_Keuangan/import';
$route['csv_import_lot']                            = 'keuangan/C_Keuangan/csv_import_lot';
$route['daily_stock_lot']                           = 'keuangan/C_Keuangan/daily_stock_lot';
$route['csv_import_po_pnd']                         = 'keuangan/C_Keuangan/csv_import_po_pnd';
$route['get_data_a/(:any)']                         = 'keuangan/C_Keuangan/get_stock_a/$1';
$route['detail_lot/(:any)']                         = 'keuangan/C_Keuangan/detail_lot/$1';
$route['gudang/(:any)']                             = 'keuangan/C_Keuangan/gudang/$1';
$route['get_data_global']                           = 'keuangan/C_Keuangan/get_data_global';
$route['list_stock_minimum/(:any)']                 = 'keuangan/C_Keuangan/list_stock_minimum/$1';
$route['truncateitm/(:any)/(:any)']                 = 'keuangan/C_Keuangan/trsuncateitm/$1/$2';
$route['deletedata/(:any)']                         = 'keuangan/C_Keuangan/deletedata/$1';
$route['pagination']                                = 'keuangan/C_Coba1';
$route['gudang/(:any)/suplier/(:any)']              = 'keuangan/C_Keuangan/stock_suplier/$1/$2';

// LOGISTIK ICS
$route['ics']                                       = 'logistik/C_Ics';
$route['ics/master_barang']                         = 'logistik/C_Ics/master_barang';
$route['ics/save_mbarang']                          = 'logistik/C_Ics/add_master_barang';
$route['ics/get_detail_mbarang']                    = 'logistik/C_Ics/get_detail_mbarang';
$route['ics/by_expdate']                            = 'logistik/C_Ics/ics_by_expdate';
$route['ics/by_allbarang']                          = 'logistik/C_Ics/ics_by_allbarang';
$route['ics/updateinline']                          = 'logistik/C_Ics/update_inline';
$route['ics/icsdo']                                 = 'logistik/C_Ics/ics_do';
$route['ics/icsdo/dohistori']                       = 'logistik/C_Ics/history_ics_do';
$route['ics/icspo']                                 = 'logistik/C_Ics/ics_po';
$route['ics/diffrent']                              = 'logistik/C_Ics/ics_diffrent';
$route['ics/sv_opname']                             = 'logistik/C_Ics/save_opname_ics';
$route['ics/get_detail_barang']                     = 'logistik/C_Ics/get_detail_barang';
$route['ics/ics_stock_controller/(:any)']           = 'logistik/C_Ics/ics_stock_controller/$1';
$route['ics/get_detail_by_exp_date']                = 'logistik/C_Ics/get_detail_by_exp_date';
$route['ics/stock_by_kodebr/(:any)']                = 'logistik/C_Ics/stock_by_kodebr/$1';
$route['ics/get_detail_by_exp']                     = 'logistik/C_Ics/get_detail_by_exp';
$route['ics/by_allbarang_ics/(:any)']               = 'logistik/C_Ics/by_allbarang_ics/$1';
$route['ics/detail_allbarang/(:any)']               = 'logistik/C_Ics/ics_detail_allbarang/$1';
$route['ics/by_expdate_ics/(:any)']                 = 'logistik/C_Ics/by_expdate_ics/$1';
$route['ics/ics_diffrent']                          = 'logistik/C_Ics/ics_diffrent';
$route['ics/import_csv']                            = 'logistik/C_Ics/import_csv_po';
$route['ics/sc_do_by_date_range']                   = 'logistik/C_Ics/sc_do_by_date_range';
$route['ics/kalkulatorics']                         = 'logistik/C_Ics/kalkulator_operan';

$route['data_lpb_zahir']                            = 'logistik/C_Ics/data_lpb_zahir';
$route['get_lpb']                                   = 'logistik/C_Ics/get_lpb';

// LOGISTIK & OPNAME
$route['final_result']                              = 'logistik/C_Logistik/final_result_opname';
$route['ics/(:any)']                                = 'logistik/C_Logistik/ics/$1';
$route['stockopname']                               = 'logistik/C_Logistik/stockopname';
$route['gudang']                                    = 'logistik/C_Logistik/module_gudang';
$route['detailbarang/(:any)']                       = 'logistik/C_Logistik/detailbarang/$1';
$route['forminput/(:any)/(:any)']                   = 'logistik/C_Logistik/forminput/$1/$2';
$route['insertopname']                              = 'logistik/C_Logistik/insertopname';
$route['stkopname_tracking']                        = 'logistik/C_Logistik/stkopname_tracking';
$route['admstocktracking']                          = 'logistik/C_Logistik/admstocktracking';
$route['searchbarang']                              = 'logistik/C_Logistik/searchbarang';
$route['search_get_exp_date']                       = 'logistik/C_Logistik/search_get_exp_date';
$route['save_opname']                               = 'logistik/C_Logistik/save_opname';
$route['save_edit_opname']                          = 'logistik/C_Logistik/save_edit_opname';
$route['request_opname']                            = 'logistik/C_Logistik/request_opname';
$route['cek_req_user_opname/(:any)/(:any)']         = 'logistik/C_Logistik/cek_req_user_opname/$1/$2';
$route['req_opname_acc/(:any)']                     = 'logistik/C_Logistik/req_opname_acc/$1';
$route['trackingtim/(:any)']                        = 'logistik/C_Logistik/admtrackingtim/$1';
$route['compare_opname']                            = 'logistik/C_Logistik/compare_opname';
$route['compare_wilayah/(:any)']                    = 'logistik/C_Logistik/compare_wilayah/$1';
$route['opname_datapending']                        = 'logistik/C_Logistik/opname_datapending';
$route['request_opname_admin']                      = 'logistik/C_Logistik/request_opname_admin';
$route['detailtrack/(:any)/(:any)']                 = 'logistik/C_Logistik/detail_tracking_input/$1/$2';
$route['export_compare_allbarang']                  = 'logistik/C_Logistik/export_compare_allbarang';
$route['usropname_input']                           = 'logistik/C_Logistik/usropname_input';
$route['delete_opname/(:any)']                      = 'logistik/C_Logistik/delete_opname/$1';
$route['data_final_input_opname']                   = 'logistik/C_Logistik/data_final_input_opname';



//LOGISTIK - DO
$route['data_preview_do']                           = 'logistik/C_Logistik/preview_csv';
$route['pre_do/insert_csv']                         = 'logistik/C_Logistik/insert_csv';
$route['logistik']                                  = 'logistik/C_Logistik/delivery_order';
$route['logistikprepare']                           = 'logistik/C_Logistik/delivery_order';
$route['create_do']                                 = 'logistik/C_Logistik/create_do';
$route['faktur_on_site']                            = 'logistik/C_Logistik/faktur_on_site';
$route['edited_rute_do']                            = 'logistik/C_Logistik/edited_rute_do';
$route['insert_tmp/(:any)/(:any)']                  = 'logistik/C_Logistik/insert_tmp/$1/$2';
$route['revert_do/(:any)/(:any)']                   = 'logistik/C_Logistik/revert_do/$1/$2';
$route['cancel_fk/(:any)/(:any)']                   = 'logistik/C_Logistik/cancel_fk/$1/$2';
$route['detail_fk/(:any)']                          = 'logistik/C_Logistik/detail_fk/$1';
$route['insertfromdraft/(:any)/(:any)']             = 'logistik/C_Logistik/insertfromdraft/$1/$2';
$route['detail_do/(:any)']                          = 'logistik/C_Logistik/detail_do/$1';

// $route['list_faktur/(:any)/(:any)']              = 'logistik/C_Logistik/list_faktur_sortby_rute/$1/$2';

$route['list_faktur/(:any)']                        = 'logistik/C_Logistik/list_faktur_sortby_rute/$1';
$route['acc_check/(:any)/(:any)/(:any)']            = 'logistik/C_Logistik/acc_check/$1/$2/$3';
$route['rekam_order_check']                         = 'logistik/C_Logistik/rekam_order_check';
$route['do/repost_status']                          = 'logistik/C_Logistik/repost_status';
$route['do/delete_ics_do']                          = 'logistik/C_Logistik/delete_ics_do';
$route['print_do/(:any)']                           = 'logistik/C_Logistik/print_do/$1';
$route['print_regis/(:any)']                        = 'logistik/C_Logistik/print_regis/$1';
$route['print_checker/(:any)']                      = 'logistik/C_Logistik/print_checker/$1';
$route['pnd_br_detpo/(:any)/(:any)/(:any)']         = 'logistik/C_Logistik/pnd_br_detpo/$1/$2/$3';
$route['get_barang']                                = 'logistik/C_Logistik/get_barang';
$route['update_barang']                             = 'logistik/C_Logistik/update_barang';
$route['rekam_do']                                  = 'logistik/C_Logistik/rekam_do';
$route['truncatelog/(:any)/(:any)']                 = 'logistik/C_Logistik/truncatelog/$1/$2';
$route['get_tmp_do']                                = 'logistik/C_Logistik/get_tmp_do';
$route['get_tmpdonorut']                            = 'logistik/C_Logistik/get_tmpdonorut';
$route['update_norut']                              = 'logistik/C_Logistik/update_norut';
$route['save_do']                                   = 'logistik/C_Logistik/save_do';
$route['custupdate']                                = 'logistik/C_Logistik/custupdate';

//SCHEDULE DIREKTUR
$route['schedule_direktur']                         = 'schedule/C_Schedule';
$route['act_schedule/(:any)']                       = 'schedule/C_Schedule/act_schedule/$1';

// DEVELOPMENT 
$route['development']                               = 'schedule/C_Development/dashboard_do';

// EXTRAVAGANZA - UNDIAN
$route['extravaganza']                              = 'extravaganza/C_Extravaganza';
$route['extravaganza_undian']                       = 'extravaganza/C_Extravaganza/undian';
$route['extravaganza_savewin']                      = 'extravaganza/C_Extravaganza/save_win';

// EXTRAVAGANZA - REGISTRASI
$route['extravaganza_registrasi']                   = 'extravaganza/C_Extravaganza/registrasi_tamu';

// USER REPORT
$route['user_report']                               = 'karyawan_report/C_Report';

// PRICELIST - ONLINE
$route['pricelist_online']                          = 'keuangan/C_Keuangan/pricelist_online';


// SALES TONASE KUBIKASI
$route['sales_report']                              = 'sales/C_Sales/dashboard_sales';
