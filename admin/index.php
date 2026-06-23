<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin.php';

$page = trim((string) ($_GET['page'] ?? 'dashboard'));
$page = trim($page, '/');
if ($page === '') {
    $page = 'dashboard';
}

$meta = adminPageMeta($page);

if ($page === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        adminHandleLoginPost();
    }

    redirect('login.php');
}

if ($page === 'logout') {
    logoutAdmin();
    flash('info', 'Signed Out', 'Session cleared.');
    redirect('login.php');
}

requireAdminRole(['admin', 'store_manager']);
adminHandlePost($page, []);

if ($page === 'reports' && isset($_GET['export']) && $_GET['export'] === 'csv') {
    adminCsvReportRows(adminStoreFilter(adminData()['reportRows']));
}

$adminData = adminData();


$pageTitle = $meta['title'];
$activeSection = $meta['section'];
$currentPage = $page;

adminView($meta['template'], array_merge($adminData, [
    'pageTitle' => $pageTitle,
    'activeSection' => $activeSection,
    'currentPage' => $currentPage,
]));






