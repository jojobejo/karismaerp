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

//inventaris
$route['inventaris']        = 'inventaris/C_Inventaris';
$route['addinventaris']     = 'inventaris/C_Inventaris/addinventaris';
$route['editinventaris']    = 'inventaris/C_Inventaris/editinventaris';
$route['hapusinventaris']   = 'inventaris/C_Inventaris/hapusinventaris';
$route['changeowner']       = 'inventaris/C_Inventaris/changeowner';
$route['selectowner']       = 'inventaris/C_Inventaris/selectowner';

//LOGISTIK - TRUK SETTING
$route['truckoprational']   = 'logistik/C_Logistik';
$route['opplat']            = 'logistik/C_Logistik/op_plat';
$route['addplat']           = 'logistik/C_Logistik/addplat';
$route['editplat']          = 'logistik/C_Logistik/editplat';
$route['hapusplat']         = 'logistik/C_Logistik/hapusplat';
$route['driverop']          = 'logistik/C_Logistik/driverop';
$route['tambahdriver']      = 'logistik/C_Logistik/addriver';
$route['editdriver']        = 'logistik/C_Logistik/editdriver';
$route['hapusdriver']        = 'logistik/C_Logistik/hapusdriver';
$route['activedrver/(:any)'] = 'logistik/C_Logistik/activedrver/$1';
$route['nonactivedriver/(:any)'] = 'logistik/C_Logistik/nonactivedriver/$1';
$route['tambahpenggunadriver'] = 'logistik/C_Logistik/tambahpenggunadriver';
//LOGISTIK DELIVERI ORDER
$route['deliveriorder']     = 'logistik/C_Logistik/deleveriorder';
$route['tambahorderdriver']     = 'logistik/C_Logistik/tambahorderdriver';
$route['addorderdeliv']     = 'logistik/C_Logistik/addorderdeliv';
$route['select2driver']     = 'logistik/C_Logistik/select2driver';
$route['detail_deliveri/(:any)'] = 'logistik/C_Logistik/det_deliveri/$1';
$route['detail_deliveri/(:any)/(:any)'] = 'logistik/C_Logistik/det_driver/$1/$2';
$route['add_pending_driver']     = 'logistik/C_Logistik/add_pending_driver';

//UserAccount
$route['userAdmin'] = 'User/Admin';
$route['addUser']   = 'User/Admin/addUser';
