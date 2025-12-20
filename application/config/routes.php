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
$route['process']                   = 'Auth/process';
$route['logout']                    = 'Auth/logout';
$route['dashboard']                 = 'Dashboard';

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

// LOGISTIK ICS & OPNAME
$route['final_result']                              = 'logistik/C_Logistik/final_result_opname';
$route['ics/(:any)']                                = 'logistik/C_Logistik/ics/$1';
$route['stockopname']                               = 'logistik/C_Logistik/stockopname';
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
$route['logistik']                                  = 'logistik/C_Logistik/delivery_order';
$route['logistikprepare']                           = 'logistik/C_Logistik/delivery_order';
$route['create_do']                                 = 'logistik/C_Logistik/create_do';
$route['insert_tmp/(:any)/(:any)']                  = 'logistik/C_Logistik/insert_tmp/$1/$2';
$route['revert_do/(:any)/(:any)']                   = 'logistik/C_Logistik/revert_do/$1/$2';
$route['cancel_fk/(:any)/(:any)']                   = 'logistik/C_Logistik/cancel_fk/$1/$2';
$route['detail_fk/(:any)']                          = 'logistik/C_Logistik/detail_fk/$1';
$route['insertfromdraft/(:any)/(:any)/(:any)']      = 'logistik/C_Logistik/insertfromdraft/$1/$2/$3';
$route['detail_do/(:any)']                          = 'logistik/C_Logistik/detail_do/$1';
$route['list_faktur/(:any)/(:any)']                 = 'logistik/C_Logistik/list_faktur_sortby_rute/$1/$2';
$route['acc_check/(:any)/(:any)/(:any)']            = 'logistik/C_Logistik/acc_check/$1/$2/$3';
$route['rekam_order_check']                         = 'logistik/C_Logistik/rekam_order_check';
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

//SCHEDULE DIREKTUR
$route['schedule_direktur']         = 'schedule/C_Schedule';
$route['act_schedule/(:any)']       = 'schedule/C_Schedule/act_schedule/$1';

// DEVELOPMENT 
$route['development']         = 'schedule/C_Development/dashboard_do';

// EXTRAVAGANZA - UNDIAN
$route['extravaganza']                  = 'extravaganza/C_Extravaganza';
$route['extravaganza_undian']           = 'extravaganza/C_Extravaganza/undian';
$route['extravaganza_savewin']          = 'extravaganza/C_Extravaganza/save_win';
// EXTRAVAGANZA - REGISTRASI
$route['extravaganza_registrasi']       = 'extravaganza/C_Extravaganza/registrasi_tamu';


// Sistem Routes
// $route['default_controller'] = 'Auth';
// $route['404_override'] = '';
// $route['translate_uri_dashes'] = FALSE;

// Auth Login
// $route['process']   = 'Auth/process';
// $route['logout']    = 'Auth/logout';

// Dashboard
// $route['dashboard'] = 'Dashboard';
// $route['konfirm_tamu'] = 'Dashboard/konfirm_tamu';

// inventaris
// $route['inventaris']        = 'inventaris/C_Inventaris';
// $route['addinventaris']     = 'inventaris/C_Inventaris/addinventaris';
// $route['editinventaris']    = 'inventaris/C_Inventaris/editinventaris';
// $route['hapusinventaris1']   = 'inventaris/C_Inventaris/hapusinventaris';
// $route['changeowner']       = 'inventaris/C_Inventaris/changeowner';
// $route['selectowner']       = 'inventaris/C_Inventaris/selectowner';

// LOGISTIK - TRUK SETTING
// $route['truckoprational']   = 'logistik/C_Logistik';
// $route['opplat']            = 'logistik/C_Logistik/op_plat';
// $route['addplat']           = 'logistik/C_Logistik/addplat';
// $route['editplat']          = 'logistik/C_Logistik/editplat';
// $route['hapusplat']         = 'logistik/C_Logistik/hapusplat';
// $route['driverop']          = 'logistik/C_Logistik/driverop';
// $route['tambahdriver']      = 'logistik/C_Logistik/addriver';
// $route['editdriver']        = 'logistik/C_Logistik/editdriver';
// $route['hapusdriver']        = 'logistik/C_Logistik/hapusdriver';
// $route['activedrver/(:any)'] = 'logistik/C_Logistik/activedrver/$1';
// $route['nonactivedriver/(:any)'] = 'logistik/C_Logistik/nonactivedriver/$1';
// $route['tambahpenggunadriver'] = 'logistik/C_Logistik/tambahpenggunadriver';
// $route['editnorut'] = 'logistik/C_Logistik/editnorut';
// $route['tambahhelper'] = 'logistik/C_Logistik/tambahhelper';
// $route['edithelper'] = 'logistik/C_Logistik/edithelper';
// $route['hapushelper'] = 'logistik/C_Logistik/hapushelper';
// $route['nonactivehelper/(:any)'] = 'logistik/C_Logistik/nonactivehelper/$1';
// $route['activehelper/(:any)'] = 'logistik/C_Logistik/activehelper/$1';

// $route['tambahhelper'] = 'logistik/C_Logistik/tambahhelper';

// LOGISTIK DELIVERI ORDER
// $route['deliveriorder']     = 'logistik/C_Logistik/deleveriorder';
// $route['tambahorderdriver']     = 'logistik/C_Logistik/tambahorderdriver';
// $route['editorderdeliver/(:any)']     = 'logistik/C_Logistik/editdeliv/$1';
// $route['addorderdeliv']     = 'logistik/C_Logistik/addorderdeliv';
// $route['select2driver']     = 'logistik/C_Logistik/select2driver';
// $route['detail_deliveri/(:any)'] = 'logistik/C_Logistik/det_deliveri/$1';
// $route['detail_deliveri/(:any)/(:any)'] = 'logistik/C_Logistik/det_driver/$1/$2';
// $route['add_pending_driver']     = 'logistik/C_Logistik/add_pending_driver';
// $route['driver_pending']    = 'logistik/C_Logistik/driver_pending';
// $route['det_pnd_driver/(:any)/(:any)'] = 'logistik/C_Logistik/det_pnd_driver/$1/$2';
// $route['get_kd_truk_order'] = 'logistik/C_Logistik/select_kd_truk';
// $route['tracking_driver'] = 'logistik/C_Logistik/tracking_driver';
// $route['export_excel'] = 'logistik/C_Logistik/export_tracking_driver';
// $route['det_driver_tracking/(:any)'] = 'logistik/C_Logistik/detail_tracking_driver/$1';
// $route['editdetaildriver'] = 'logistik/C_Logistik/editdetaildriver';
// $route['hapus_detail_order'] = 'logistik/C_Logistik/hapus_detail_order';
// $route['export_excel_lap_distribusi'] = 'logistik/C_Logistik/export_lap_distribusi/';
// $route['exportrekaplaporandriver'] = 'logistik/C_Logistik/export_rekap_laporan_driver/';
// $route['tmp_logistik_distribusi'] = 'logistik/C_Logistik/tmp_lap_distribusi/';
// $route['edit_laporan_tmp_dis'] = 'logistik/C_Logistik/edit_laporan_tmp_dis/';
// $route['insert_laporan_tmp_dis'] = 'logistik/C_Logistik/insert_laporan_tmp_dis/';

//  HRD 
// $route['hrd_lap_distribusi'] = 'hrd/C_Hrd/lap_distribusi';
// $route['get_server_lap_dis'] = 'hrd/C_Hrd/get_lap_distribusi';
// $route['add_lap_distribusi_hrd'] = 'hrd/C_Hrd/input_lap_distribusi';
// $route['edit_lap_distribusi_hrd/(:any)'] = 'hrd/C_Hrd/v_edit_lap_distribusi/$1';
// $route['edit_lap_distribusi_hrd'] = 'hrd/C_Hrd/edit_lap_distribusi';
// $route['hapus_lap_distribusi_hrd/(:any)'] = 'hrd/C_Hrd/v_hapus_lap_distribusi_hrd/$1';
// $route['hapus_lap_distribusi_hrd'] = 'hrd/C_Hrd/hapus_lap_distribusi_hrd';
// $route['hrd_lap_tamu'] = 'hrd/C_Hrd/lap_tamu';
// $route['hrd_add_tamu'] = 'hrd/C_Hrd/hrd_add_tamu';
// $route['tambah_lap_tamu'] = 'hrd/C_Hrd/tambah_lap_tamu';
// $route['hapus_lap_tamu_hrd'] = 'hrd/C_Hrd/hapus_lap_tamu_hrd';
// $route['edit_lap_tamu_hrd'] = 'hrd/C_Hrd/edit_lap_tamu';
// $route['konfirm_buku_tamu'] = 'hrd/C_Hrd/konfirm_buku_tamu';
// $route['hrd_lap_Karyawan_KM'] = 'hrd/C_Hrd/lap_karykm';
// $route['edit_lap_Karyawan_KM'] = 'hrd/C_Hrd/edit_lap_karykm';
// $route['tambah_lap_karykm'] = 'hrd/C_Hrd/tambah_lap_karykm';
// $route['hapus_lap_karykm'] = 'hrd/C_Hrd/hapus_lap_karykm';
// $route['hrd_lap_expedisi'] = 'hrd/C_Hrd/lap_expedisi';
// $route['edit_lap_expedisi'] = 'hrd/C_Hrd/edit_lap_expedisi';
// $route['tambah_lap_expedisi'] = 'hrd/C_Hrd/tambah_lap_expedisi';
// $route['hapus_lap_expedisi'] = 'hrd/C_Hrd/hapus_lap_expedisi';
// $route['hrd_lap_issue'] = 'hrd/C_Hrd/lap_issue';
// $route['edit_lap_issue'] = 'hrd/C_Hrd/edit_lap_issue';
// $route['tambah_lap_issue'] = 'hrd/C_Hrd/tambah_lap_issue';
// $route['hapus_lap_issue'] = 'hrd/C_Hrd/hapus_lap_issue';
// $route['search_lap_distribusi'] = 'hrd/C_Hrd/search_lap_distribusi';
// $route['v_cari_lap_distribusi'] = 'hrd/C_Hrd/v_cari_lap_distribusi';
// $route['hrd_data_truk'] = 'hrd/C_Hrd/hrd_data_truk';
// $route['hrd_all_karyawan'] = 'hrd/C_Hrd/hrd_all_karyawan';
// $route['updatekmsekarang'] = 'hrd/C_Hrd/update_km_now_service_truk';
// $route['updatekmsebelum'] = 'hrd/C_Hrd/update_km_past_service_truk';
// $route['edit_karyawan'] = 'hrd/C_Hrd/edit_karyawan';
// $route['add_karyawan'] = 'hrd/C_Hrd/add_karyawan';
// $route['export_laporan_issue'] = 'hrd/C_Hrd/export_laporan_issue';
// $route['ex_lap_kar'] = 'hrd/C_Hrd/export_laporan_karyawan';

// KPI
// $route['dashboardkpi'] = 'kpi/C_Kpi';
// $route['indikator_kpi'] = 'kpi/C_Kpi/indikator_kpi';

// UserAccount
// $route['userAdmin'] = 'User/Admin';
// $route['addUser']   = 'User/Admin/addUser';

// IndeksNilaiKepuasanPelanggan
// $route['kepuasan_pelanggan'] = 'Pelanggan/C_Pelanggan';
// $route['nilai_ipkp/(:any)'] = 'Pelanggan/C_Pelanggan/input_nilai/$1';
// $route['ratingreview'] = 'Pelanggan/C_Pelanggan/rating_review';

// RequestDesign
// $route['requestdesign']        = 'requestdesign/C_requestdesign';
// $route['addinventaris']     = 'inventaris/C_Inventaris/addinventaris';
// $route['editinventaris']    = 'inventaris/C_Inventaris/editinventaris';
// $route['hapusinventaris1']   = 'inventaris/C_Inventaris/hapusinventaris';
// $route['changeowner']       = 'inventaris/C_Inventaris/changeowner';
// $route['selectowner']       = 'inventaris/C_Inventaris/selectowner';

// scedule_direktur
// $route['schedule_direktur']         = 'schedule/C_Schedule';
// $route['act_schedule/(:any)']       = 'schedule/C_Schedule/act_schedule/$1';
