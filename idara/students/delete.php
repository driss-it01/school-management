<?php
require_once '../../config.php';
$userObj = new User();
if (!$userObj->isLoggedIn() || $_SESSION['user_role'] !== 'idara') redirect('../../login.php');

$id = $_GET['id'] ?? null;
if ($id) {
    $studentObj = new Student();
    $studentObj->delete($id);
}
redirect('index.php');