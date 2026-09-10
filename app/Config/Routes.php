<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'PublicController::index');
$routes->get('profil', 'PublicController::profil');
$routes->get('layanan', 'PublicController::layanan');
$routes->get('kesehatan', 'PublicController::kesehatan');
$routes->get('posyandu', 'PublicController::posyandu');
$routes->get('posbindu', 'PublicController::posbindu');
$routes->get('edukasi-kesehatan', 'PublicController::edukasiKesehatan');
$routes->get('edukasi-kesehatan/(:segment)', 'PublicController::edukasiKesehatanTopik/$1');
$routes->get('keuangan', 'PublicController::keuangan');
$routes->get('layanan-online', 'PublicController::layananOnline');
$routes->post('layanan-online', 'PublicController::submitLayananOnline');
$routes->get('layanan-online/surat/(:segment)', 'PublicController::cetakSurat/$1');
$routes->get('kegiatan', 'PublicController::kegiatan');
$routes->get('pengurus', 'PublicController::pengurus');
$routes->get('aspirasi', 'PublicController::aspirasi');
$routes->post('aspirasi', 'PublicController::submitAspirasi');

$routes->get('admin/login', 'Admin\AuthController::login');
$routes->post('admin/login', 'Admin\AuthController::attemptLogin');
$routes->get('admin/logout', 'Admin\AuthController::logout');

$routes->group('admin', ['filter' => 'adminauth'], static function ($routes) {
    $routes->get('/', 'Admin\PanelController::dashboard');
    $routes->get('dashboard', 'Admin\PanelController::dashboard');
    $routes->match(['GET', 'POST'], 'profil', 'Admin\PanelController::profil');
    $routes->match(['GET', 'POST'], 'program', 'Admin\PanelController::program');
    $routes->match(['GET', 'POST'], 'kegiatan', 'Admin\PanelController::kegiatan');
    $routes->match(['GET', 'POST'], 'layanan', 'Admin\PanelController::layanan');
    $routes->match(['GET', 'POST'], 'edukasi', 'Admin\PanelController::edukasi');
    $routes->post('edukasi/delete/(:num)', 'Admin\PanelController::deleteEdukasi/$1');
    $routes->match(['GET', 'POST'], 'kesehatan-jadwal', 'Admin\PanelController::kesehatanJadwal');
    $routes->match(['GET', 'POST'], 'kesehatan-data', 'Admin\PanelController::kesehatanData');
    $routes->match(['GET', 'POST'], 'pengajuan-surat', 'Admin\PanelController::pengajuanSurat');
    $routes->match(['GET', 'POST'], 'pengurus', 'Admin\PanelController::pengurus');
    $routes->post('pengurus/struktur-gambar', 'Admin\PanelController::uploadPengurusStructureImage');
    $routes->post('pengurus/struktur-gambar/delete', 'Admin\PanelController::deletePengurusStructureImage');
    $routes->post('pengurus/struktur-penjelasan', 'Admin\PanelController::savePengurusStructureDescription');
    $routes->match(['GET', 'POST'], 'warga', 'Admin\PanelController::warga');
    $routes->match(['GET', 'POST'], 'keuangan', 'Admin\PanelController::keuangan');
    $routes->match(['GET', 'POST'], 'aspirasi', 'Admin\PanelController::aspirasi');
    $routes->match(['GET', 'POST'], 'akun', 'Admin\PanelController::akun');
    $routes->match(['GET', 'POST'], 'import', 'Admin\ImportController::index');
    $routes->get('import/template/(:segment)', 'Admin\ImportController::template/$1');
});
