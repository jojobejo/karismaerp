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
$route['process']   = 'Auth/process';
$route['logout']    = 'Auth/logout';

//Dashboard
$route['dashboard'] = 'Dashboard';
$route['konfirm_tamu'] = 'Dashboard/konfirm_tamu';

//inventaris
$route['inventaris']                            = 'inventaris/C_Inventaris';
$route['addinventaris']                         = 'inventaris/C_Inventaris/addinventaris';
$route['editinventaris']                        = 'inventaris/C_Inventaris/editinventaris';
$route['hapusinventaris1']                      = 'inventaris/C_Inventaris/hapusinventaris';
$route['changeowner']                           = 'inventaris/C_Inventaris/changeowner';
$route['selectowner']                           = 'inventaris/C_Inventaris/selectowner';

//LOGISTIK - TRUK SETTING
$route['truckoprational']                       = 'logistik/C_Logistik';
$route['opplat']                                = 'logistik/C_Logistik/op_plat';
$route['addplat']                               = 'logistik/C_Logistik/addplat';
$route['editplat']                              = 'logistik/C_Logistik/editplat';
$route['hapusplat']                             = 'logistik/C_Logistik/hapusplat';
$route['driverop']                              = 'logistik/C_Logistik/driverop';
$route['tambahdriver']                          = 'logistik/C_Logistik/addriver';
$route['editdriver']                            = 'logistik/C_Logistik/editdriver';
$route['hapusdriver']                           = 'logistik/C_Logistik/hapusdriver';
$route['activedrver/(:any)']                    = 'logistik/C_Logistik/activedrver/$1';
$route['nonactivedriver/(:any)']                = 'logistik/C_Logistik/nonactivedriver/$1';
$route['tambahpenggunadriver']                  = 'logistik/C_Logistik/tambahpenggunadriver';
$route['editnorut']                             = 'logistik/C_Logistik/editnorut';
$route['tambahhelper']                          = 'logistik/C_Logistik/tambahhelper';
$route['edithelper']                            = 'logistik/C_Logistik/edithelper';
$route['hapushelper']                           = 'logistik/C_Logistik/hapushelper';
$route['nonactivehelper/(:any)']                = 'logistik/C_Logistik/nonactivehelper/$1';
$route['activehelper/(:any)']                   = 'logistik/C_Logistik/activehelper/$1';

$route['tambahhelper']                          = 'logistik/C_Logistik/tambahhelper';

//LOGISTIK DELIVERI ORDER
$route['deliveriorder']                         = 'logistik/C_Logistik/deleveriorder';
$route['tambahorderdriver']                     = 'logistik/C_Logistik/tambahorderdriver';
$route['editorderdeliver/(:any)']               = 'logistik/C_Logistik/editdeliv/$1';
$route['addorderdeliv']                         = 'logistik/C_Logistik/addorderdeliv';
$route['select2driver']                         = 'logistik/C_Logistik/select2driver';
$route['detail_deliveri/(:any)']                = 'logistik/C_Logistik/det_deliveri/$1';
$route['detail_deliveri/(:any)/(:any)']         = 'logistik/C_Logistik/det_driver/$1/$2';
$route['add_pending_driver']                    = 'logistik/C_Logistik/add_pending_driver';
$route['driver_pending']                        = 'logistik/C_Logistik/driver_pending';
$route['det_pnd_driver/(:any)/(:any)']          = 'logistik/C_Logistik/det_pnd_driver/$1/$2';
$route['get_kd_truk_order']                     = 'logistik/C_Logistik/select_kd_truk';
$route['tracking_driver']                       = 'logistik/C_Logistik/tracking_driver';
$route['export_excel']                          = 'logistik/C_Logistik/export_tracking_driver';
$route['det_driver_tracking/(:any)']            = 'logistik/C_Logistik/detail_tracking_driver/$1';
$route['editdetaildriver']                      = 'logistik/C_Logistik/editdetaildriver';
$route['hapus_detail_order']                    = 'logistik/C_Logistik/hapus_detail_order';
$route['export_excel_lap_distribusi']           = 'logistik/C_Logistik/export_lap_distribusi/';
$route['exportrekaplaporandriver']              = 'logistik/C_Logistik/export_rekap_laporan_driver/';
$route['tmp_logistik_distribusi']               = 'logistik/C_Logistik/tmp_lap_distribusi/';
$route['edit_laporan_tmp_dis']                  = 'logistik/C_Logistik/edit_laporan_tmp_dis/';
$route['insert_laporan_tmp_dis']                = 'logistik/C_Logistik/insert_laporan_tmp_dis/';

// HRD 
$route['hrd_lap_distribusi']                    = 'hrd/C_Hrd/lap_distribusi';
$route['get_server_lap_dis']                    = 'hrd/C_Hrd/get_lap_distribusi';
$route['add_lap_distribusi_hrd']                = 'hrd/C_Hrd/input_lap_distribusi';
$route['edit_lap_distribusi_hrd/(:any)']        = 'hrd/C_Hrd/v_edit_lap_distribusi/$1';
$route['edit_lap_distribusi_hrd']               = 'hrd/C_Hrd/edit_lap_distribusi';
$route['hapus_lap_distribusi_hrd/(:any)']       = 'hrd/C_Hrd/v_hapus_lap_distribusi_hrd/$1';
$route['hapus_lap_distribusi_hrd']              = 'hrd/C_Hrd/hapus_lap_distribusi_hrd';
$route['hrd_lap_tamu']                          = 'hrd/C_Hrd/lap_tamu';
$route['hrd_add_tamu']                          = 'hrd/C_Hrd/hrd_add_tamu';
$route['tambah_lap_tamu']                       = 'hrd/C_Hrd/tambah_lap_tamu';
$route['hapus_lap_tamu_hrd']                    = 'hrd/C_Hrd/hapus_lap_tamu_hrd';
$route['edit_lap_tamu_hrd']                     = 'hrd/C_Hrd/edit_lap_tamu';
$route['konfirm_buku_tamu']                     = 'hrd/C_Hrd/konfirm_buku_tamu';
$route['hrd_lap_Karyawan_KM']                   = 'hrd/C_Hrd/lap_karykm';
$route['edit_lap_Karyawan_KM']                  = 'hrd/C_Hrd/edit_lap_karykm';
$route['tambah_lap_karykm']                     = 'hrd/C_Hrd/tambah_lap_karykm';
$route['hapus_lap_karykm']                      = 'hrd/C_Hrd/hapus_lap_karykm';
$route['hrd_lap_expedisi']                      = 'hrd/C_Hrd/lap_expedisi';
$route['edit_lap_expedisi']                     = 'hrd/C_Hrd/edit_lap_expedisi';
$route['tambah_lap_expedisi']                   = 'hrd/C_Hrd/tambah_lap_expedisi';
$route['hapus_lap_expedisi']                    = 'hrd/C_Hrd/hapus_lap_expedisi';
$route['hrd_lap_issue']                         = 'hrd/C_Hrd/lap_issue';
$route['edit_lap_issue']                        = 'hrd/C_Hrd/edit_lap_issue';
$route['update_status_issue/(:any)']            = 'hrd/C_Hrd/update_status_issue/$1';
$route['tambah_lap_issue']                      = 'hrd/C_Hrd/tambah_lap_issue';
$route['hapus_lap_issue']                       = 'hrd/C_Hrd/hapus_lap_issue';
$route['search_lap_distribusi']                 = 'hrd/C_Hrd/search_lap_distribusi';
$route['v_cari_lap_distribusi']                 = 'hrd/C_Hrd/v_cari_lap_distribusi';
$route['hrd_data_truk']                         = 'hrd/C_Hrd/hrd_data_truk';
$route['hrd_all_karyawan']                      = 'hrd/C_Hrd/hrd_all_karyawan';
$route['updatekmsekarang']                      = 'hrd/C_Hrd/update_km_now_service_truk';
$route['updatekmsebelum']                       = 'hrd/C_Hrd/update_km_past_service_truk';
$route['edit_karyawan']                         = 'hrd/C_Hrd/edit_karyawan';
$route['add_karyawan']                          = 'hrd/C_Hrd/add_karyawan';
$route['export_laporan_issue']                  = 'hrd/C_Hrd/export_laporan_issue';
$route['ex_lap_kar']                            = 'hrd/C_Hrd/export_laporan_karyawan';
$route['export_hrd_lap_distribusi']             = 'hrd/C_Hrd/export_hrd_lap_distribusi';

$route['truncate_laporan_distribusi']           = 'hrd/C_Hrd/truncate_laporan_distribusi';
$route['lap_tamu_serverside']                   = 'hrd/C_Hrd/lap_tamu_serverside';
$route['get_tamu_by_id/(:any)']                 = 'hrd/C_Hrd/get_tamu_by_id/$1';
$route['export_data_tamu_all']                  = 'hrd/C_Hrd/export_data_tamu_all';
$route['truncate_all_data_tamu']                = 'hrd/C_Hrd/truncate_all_data_tamu';
$route['lap_karykm_serverside']                 = 'hrd/C_Hrd/lap_karykm_serverside';
$route['get_karykm_by_id/(:any)']               = 'hrd/C_Hrd/get_karykm_by_id/$1';
$route['hapus_karykm']                          = 'hrd/C_Hrd/hapus_karykm';
$route['lap_expedisi_serverside']               = 'hrd/C_Hrd/lap_expedisi_serverside';
$route['get_expedisi_by_id/(:any)']             = 'hrd/C_Hrd/get_expedisi_by_id/$1';
$route['export_file_laporan_expedisis']         = 'hrd/C_Hrd/export_data_hrd_lap_expedisi';

$route['hrd_lap_paket_pos']                     = 'hrd/C_Hrd/hrd_lap_penerimaan_pos';
$route['lap_penerimaan_pos_serverside']         = 'hrd/C_Hrd/lap_penerimaan_pos_serverside';
$route['tambah_penerimaan_paket']               = 'hrd/C_Hrd/tambah_penerimaan_paket';
$route['konfirmasi_penerimaan_paket']           = 'hrd/C_Hrd/konfirmasi_penerimaan_paket';
$route['get_paket_by_id/(:any)']                = 'hrd/C_Hrd/get_paket_by_id/$1';
$route['edit_penerimaan_paket']                 = 'hrd/C_Hrd/edit_penerimaan_paket';
$route['hapus_penerimaan_paket']                = 'hrd/C_Hrd/hapus_penerimaan_paket';

$route['hrd_chelklist_kendaraan']               = 'hrd/C_Hrd/checklist_kendaraan';
$route['store_checklist_kendaraan']             = 'hrd/C_Hrd/store_checklist_kendaraan';
$route['all_laporan_chelist_kendaraan']         = 'hrd/C_Hrd/all_laporan_chelist_kendaraan';
$route['ajax_checklist_kendaraan']              = 'hrd/C_Hrd/ajax_checklist_kendaraan';
$route['export_data_checklist_kendaraan']       = 'hrd/C_Hrd/export_laporan_checklist_kendaraan';
$route['detail_checklist_kendaraan/(:any)']     = 'hrd/C_Hrd/detail_checklist/$1';

//KPI
$route['dashboardkpi']                          = 'kpi/C_Kpi';
$route['dashboardkpiwhat']                      = 'kpi/C_Kpi/what';
$route['add_kpi_baru']                          = 'kpi/C_Kpi/addkpi';
$route['detail_kpi/(:any)/(:any)']              = 'kpi/C_Kpi/detail_kpi/$1/$2';

//UserAccount
$route['userAdmin']                             = 'User/Admin';
$route['addUser']                               = 'User/Admin/addUser';

//IndeksNilaiKepuasanPelanggan
$route['kepuasan_pelanggan']                    = 'Pelanggan/C_Pelanggan';
$route['nilai_ipkp/(:any)']                     = 'Pelanggan/C_Pelanggan/input_nilai/$1';
$route['ratingreview']                          = 'Pelanggan/C_Pelanggan/rating_review';

//RequestDesign
$route['requestdesign']                         = 'requestdesign/C_requestdesign';
$route['addinventaris']                         = 'inventaris/C_Inventaris/addinventaris';
$route['editinventaris']                        = 'inventaris/C_Inventaris/editinventaris';
$route['hapusinventaris1']                      = 'inventaris/C_Inventaris/hapusinventaris';
$route['changeowner']                           = 'inventaris/C_Inventaris/changeowner';
$route['selectowner']                           = 'inventaris/C_Inventaris/selectowner';
=======
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

// STOCK ONLINE GUDANG
$route['stock']                                     = 'logistik/C_Logistik/stock_control';

// MASTER BARANG
$route['master_barang']                             = 'keuangan/C_Keuangan/master_barang';
$route['master_barang/list']                        = 'keuangan/C_Keuangan/master_barang_list';
$route['master_barang/detail']                      = 'keuangan/C_Keuangan/master_barang_detail';
$route['master_barang/store']                       = 'keuangan/C_Keuangan/master_barang_store';
$route['master_barang/update']                      = 'keuangan/C_Keuangan/master_barang_update';
$route['master_barang/delete']                      = 'keuangan/C_Keuangan/master_barang_delete';
$route['master_customer']                           = 'keuangan/C_Keuangan/master_customer';
$route['master_customer/list']                      = 'keuangan/C_Keuangan/master_customer_list';
$route['master_customer/detail']                    = 'keuangan/C_Keuangan/master_customer_detail';
$route['master_customer/store']                     = 'keuangan/C_Keuangan/master_customer_store';
$route['master_customer/update']                    = 'keuangan/C_Keuangan/master_customer_update';
$route['master_customer/delete']                    = 'keuangan/C_Keuangan/master_customer_delete';

// LOGISTIK ICS
$route['ics']                                       = 'logistik/C_Ics';

$route['ics/master_barang']                         = 'logistik/C_Ics/master_barang';
$route['ics/save_mbarang']                          = 'logistik/C_Ics/add_master_barang';
$route['ics/save_edit_mbarang']                     = 'logistik/C_Ics/edit_master_barang';
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
$route['ics/detail_barang/(:any)']                  = 'logistik/C_Ics/view_detail_master_barang/$1';

$route['ics/gudang']                                = 'logistik/C_Ics/master_gudang';
$route['ics/gudang_list']                           = 'logistik/C_Ics/gudang_list';
$route['ics/gudang_save']                           = 'logistik/C_Ics/gudang_save';
$route['ics/get_wilayah_by_gudang']                 = 'logistik/C_Ics/get_wilayah_by_gudang';
$route['ics/update_gudang']                         = 'logistik/C_Ics/update_gudang';
$route['ics/update_wilayah']                        = 'logistik/C_Ics/update_wilayah';
$route['ics/detail_wilayah/(:any)']                 = 'logistik/C_Ics/detail_wilayah/$1';
$route['data_lpb_zahir']                            = 'logistik/C_Ics/data_lpb_zahir';
$route['get_lpb']                                   = 'logistik/C_Ics/get_lpb';
$route['ics/barangpic']                             = 'logistik/C_Ics/pic_barang';
$route['ics/barangpic/(:any)']                      = 'logistik/C_Ics/pic_barang/$1';
$route['ics/update_pic_lokasi']                     = 'logistik/C_Ics/update_pic_lokasi';
$route['ics/barangpergudang']                       = 'logistik/C_Ics/barangpergudang';
$route['ics/ajax_barang_pergudang']                 = 'logistik/C_Ics/ajax_barang_pergudang';

$route['ics/retur']                                 = 'logistik/C_Ics/dash_retur';
$route['ics/retur/penjualan']                       = 'logistik/C_Ics/retur_penjualan';
$route['ics/retur/pembelian']                       = 'logistik/C_Ics/retur_pembelian';
$route['ics/retur/faktur_select2']                  = 'logistik/C_Ics/ajax_retur_faktur_select2';
$route['ics/retur/barang_select2']                  = 'logistik/C_Ics/ajax_retur_barang_select2';
$route['ics/retur/lot_select2']                     = 'logistik/C_Ics/ajax_retur_lot_select2';
$route['ics/retur/exp_select2']                     = 'logistik/C_Ics/ajax_retur_exp_select2';
$route['ics/retur/add_detail']                      = 'logistik/C_Ics/ajax_retur_add_detail';
$route['ics/retur/list_detail']                     = 'logistik/C_Ics/ajax_retur_list_detail';
$route['ics/retur/delete_detail']                   = 'logistik/C_Ics/ajax_retur_delete_detail';
$route['ics/retur/rekam_penjualan']                 = 'logistik/C_Ics/ajax_retur_rekam_penjualan';
$route['ics/retur/rekam_pembelian']                 = 'logistik/C_Ics/ajax_retur_rekam_pembelian';
$route['ics/retur/detail_retur']                    = 'logistik/C_Ics/detail_retur';
$route['ics/retur/detail_retur/(:any)']             = 'logistik/C_Ics/detail_retur/$1';
$route['ics/retur/pembelian/faktur_select2']        = 'logistik/C_Ics/ajax_retur_pembelian_faktur_select2';
$route['ics/retur/pembelian/barang_select2']        = 'logistik/C_Ics/ajax_retur_pembelian_barang_select2';
$route['ics/retur/pembelian/exp_select2']           = 'logistik/C_Ics/ajax_retur_pembelian_exp_select2';
$route['ics/retur/pembelian/add_detail']            = 'logistik/C_Ics/ajax_retur_pembelian_add_detail';
$route['ics/retur/pembelian/list_detail']           = 'logistik/C_Ics/ajax_retur_pembelian_list_detail';
$route['ics/retur/pembelian/delete_detail']         = 'logistik/C_Ics/ajax_retur_pembelian_delete_detail';

// MUTASI BARANG GUDANG
$route['ics/mutasi_barang']                         = 'logistik/C_Ics/mutasi_barang';
$route['ics/mutasi_barang/input']                   = 'logistik/C_Ics/input_mutasi_barang';

$route['ics/ajax_barang_select2']                   = 'logistik/C_Ics/ajax_barang_select2';
$route['ics/ajax_expired_by_barang']                = 'logistik/C_Ics/ajax_expired_by_barang';

$route['ics/ajax_barang_by_gudang']                 = 'logistik/C_Ics/ajax_barang_by_gudang';
$route['ics/ajax_exp_by_gudang_barang']             = 'logistik/C_Ics/ajax_exp_by_gudang_barang';
$route['ics/ajax_get_qty_gudang']                   = 'logistik/C_Ics/ajax_get_qty_gudang';
$route['ics/ajax_add_tmp_mutasi']                   = 'logistik/C_Ics/ajax_add_tmp_mutasi';
$route['ics/ajax_list_tmp_mutasi']                  = 'logistik/C_Ics/ajax_list_tmp_mutasi';
$route['ics/ajax_update_tmp_mutasi']                = 'logistik/C_Ics/ajax_update_tmp_mutasi';
$route['ics/ajax_delete_tmp_mutasi']                = 'logistik/C_Ics/ajax_delete_tmp_mutasi';
$route['ics/ajax_rekam_mutasi']                     = 'logistik/C_Ics/ajax_rekam_mutasi';

$route['ics/ajax_detail_mutasi']                    = 'logistik/C_Ics/ajax_detail_mutasi';
$route['ics/ajax_rollback_mutasi']                  = 'logistik/C_Ics/ajax_rollback_mutasi';
$route['ics/ajax_delete_mutasi']                    = 'logistik/C_Ics/ajax_delete_mutasi';
$route['ics/ajax_unpost_mutasi']                    = 'logistik/C_Ics/ajax_unpost_mutasi';
$route['ics/ajax_filter_mutasi']                    = 'logistik/C_Ics/ajax_filter_mutasi';


$route['logistik/stock']                            = 'logistik/C_Ics/saldo_stock';
$route['logistik/lpb']                              = 'logistik/C_Ics/create_lpb';

// LOGISTIK - DISTRIBUSI
$route['logistik/distibusi']                        = 'logistik/C_Distribusi';
$route['logistik/distibusi/get_ploting_rute']       = 'logistik/C_Distribusi/get_ploting_rute';
$route['logistik/distibusi/driver_rute_matrix']     = 'logistik/C_Distribusi/driver_rute_matrix';
$route['logistik/distibusi/driver_ready']           = 'logistik/C_Distribusi/driver_ready';
$route['logistik/distibusi/list_faktur_status']     = 'logistik/C_Distribusi/list_faktur_status';
$route['logistik/distibusi/ajax_list_faktur_status'] = 'logistik/C_Distribusi/ajax_list_faktur_status';
$route['logistik/distibusi/list_do_status_2']       = 'logistik/C_Distribusi/list_do_status_2';


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
$route['view_faktur_not_list']                      = 'logistik/C_Logistik/view_faktur_not_list';
$route['ajax_view_faktur_not_list']                 = 'logistik/C_Logistik/ajax_view_faktur_not_list';
$route['ajax_update_kd_barang_not_list']            = 'logistik/C_Logistik/ajax_update_kd_barang_not_list';
$route['faktur_on_site']                            = 'logistik/C_Logistik/faktur_on_site';
$route['edited_rute_do']                            = 'logistik/C_Logistik/edited_rute_do';
$route['insert_tmp/(:any)/(:any)']                  = 'logistik/C_Logistik/insert_tmp/$1/$2';
$route['revert_do/(:any)/(:any)']                   = 'logistik/C_Logistik/revert_do/$1/$2';
$route['cancel_fk/(:any)/(:any)']                   = 'logistik/C_Logistik/cancel_fk/$1/$2';
$route['detail_fk/(:any)']                          = 'logistik/C_Logistik/detail_fk/$1';
$route['insertfromdraft/(:any)/(:any)']             = 'logistik/C_Logistik/insertfromdraft/$1/$2';
$route['detail_do/(:any)']                          = 'logistik/C_Logistik/detail_do/$1';
$route['logistik/get_faktur']                       = 'logistik/C_Logistik/get_faktur';

$route['faktur_bintang']                            = 'logistik/C_Logistik/faktur_bintang';
$route['get_fktur_bintang']                         = 'logistik/C_Logistik/get_fktur_bintang';
$route['get_customer_bintang']                      = 'logistik/C_Logistik/get_customer_bintang';
$route['update_customer_faktur']                    = 'logistik/C_Logistik/update_customer_faktur';

$route['tonase_report']                             = 'logistik/C_Logistik/tonase_report';

//LOGISTIK - DO (FAKTUR PENDING)
$route['detail_fk_pnd/(:any)']                      = 'logistik/C_Logistik/detail_fk_pnd/$1';
$route['create_pending_do/(:any)']                  = 'logistik/C_Logistik/create_pending_do/$1';

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
$route['get_barang_pending_detail']                 = 'logistik/C_Logistik/get_barang_pending_detail';
$route['update_barang_pending']                     = 'logistik/C_Logistik/update_barang_pending';
$route['update_barang']                             = 'logistik/C_Logistik/update_barang';
$route['rekam_do']                                  = 'logistik/C_Logistik/rekam_do';
$route['truncatelog/(:any)/(:any)']                 = 'logistik/C_Logistik/truncatelog/$1/$2';
$route['get_tmp_do']                                = 'logistik/C_Logistik/get_tmp_do';
$route['get_tmpdonorut']                            = 'logistik/C_Logistik/get_tmpdonorut';
$route['update_norut']                              = 'logistik/C_Logistik/update_norut';
$route['save_do']                                   = 'logistik/C_Logistik/save_do';
$route['custupdate']                                = 'logistik/C_Logistik/custupdate';



// COBA API
$route['getdata_kiupo']                             = 'api/C_Api';

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

// EXPORT
$route['export-stock']                              = 'logistik/C_ExportStock/export';

// SALES KIU KATALOG
$route['kiu_katalog']                               = 'sales/C_ExportStock/export';

