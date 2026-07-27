<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'PublicController::index');
$routes->get('profil', 'PublicController::profil');
$routes->get('layanan', 'PublicController::layanan');
$routes->get('kesehatan', 'PublicController::kesehatan');
$routes->get('edukasi-kesehatan', 'PublicController::edukasiKesehatan');
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
    $routes->match(['get', 'post'], 'profil', 'Admin\PanelController::profil');
    $routes->match(['get', 'post'], 'program', 'Admin\PanelController::program');
    $routes->match(['get', 'post'], 'kegiatan', 'Admin\PanelController::kegiatan');
    $routes->match(['get', 'post'], 'layanan', 'Admin\PanelController::layanan');
    $routes->match(['get', 'post'], 'pengajuan-surat', 'Admin\PanelController::pengajuanSurat');
    $routes->match(['get', 'post'], 'pengurus', 'Admin\PanelController::pengurus');
    $routes->post('pengurus/struktur-gambar', 'Admin\PanelController::uploadPengurusStructureImage');
    $routes->post('pengurus/struktur-gambar/delete', 'Admin\PanelController::deletePengurusStructureImage');
    $routes->post('pengurus/struktur-penjelasan', 'Admin\PanelController::savePengurusStructureDescription');
    $routes->match(['get', 'post'], 'warga', 'Admin\PanelController::warga');
    $routes->match(['get', 'post'], 'keuangan', 'Admin\PanelController::keuangan');
    $routes->match(['get', 'post'], 'aspirasi', 'Admin\PanelController::aspirasi');
    $routes->match(['get', 'post'], 'akun', 'Admin\PanelController::akun');
    $routes->match(['get', 'post'], 'import', 'Admin\ImportController::index');
    $routes->get('import/template/(:segment)', 'Admin\ImportController::template/$1');
});
