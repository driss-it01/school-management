<?php
require_once __DIR__ . '/../config.php';
$currentUser = (new User())->getCurrentUser();
if (!$currentUser) {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Gestion Scolaire' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="d-flex" id="wrapper">
    <?php
    if ($_SESSION['user_role'] === 'idara') {
        include __DIR__ . '/sidebar-idara.php';
    } else {
        include __DIR__ . '/sidebar-prof.php';
    }
    ?>
    <div id="page-content-wrapper" class="w-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
            <span class="navbar-brand">Lycée Technique - Maroc</span>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3"><?= htmlspecialchars($currentUser['full_name']) ?></span>
                <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>
            </div>
        </nav>
        <div class="container-fluid p-4">