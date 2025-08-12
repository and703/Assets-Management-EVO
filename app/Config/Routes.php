<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/',                           'Dashboard::index');
$routes->get ('assets/data',                'AssetController::data');            // JSON data for DataTables
$routes->get ('assets/',                    'AssetController::index');           // list
$routes->get ('assets/create',              'AssetController::create');          // form
$routes->post('assets',                     'AssetController::store');           // save new
$routes->get ('assets/(:num)',              'AssetController::show/$1');         // detail
$routes->get ('assets/(:num)/edit',         'AssetController::edit/$1');         // edit form
$routes->put ('assets/(:num)',              'AssetController::update/$1');       // update
$routes->delete('assets/(:num)',            'AssetController::destroy/$1');      // delete
$routes->post('/import',                    'AssetController::importExcel');     // import from Excel
// routes->post('api/rfid',                 'Api\RfidController::log');          // apply auth filter if you have one
$routes->get('assets/(:num)/audits',        'AssetAuditController::index/$1');
$routes->get('assets/(:num)/audits/data',   'AssetAuditController::data/$1');
$routes->get ('logs',                       'ApiLogController::index');          // list
$routes->get ('logs/data',                  'ApiLogController::data');            // JSON data for DataTables
$routes->get ('logs/(:num)',                'ApiLogController::detail/$1');      // detail
$routes->get ('/import',                    'AssetController::showImportForm');  // import form
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
