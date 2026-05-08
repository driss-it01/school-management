<?php
require_once '../../config.php';
$userObj = new User();
if (!$userObj->isLoggedIn() || $_SESSION['user_role'] !== 'idara') redirect('../../login.php');

$id = $_GET['id'] ?? null;
if ($id) {
    $userObj->deleteProf($id);
}
redirect('index.php');