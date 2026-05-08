<?php
require_once __DIR__ . '/../config.php';
$userObj = new User();
if (!$userObj->isLoggedIn()) {
    redirect('login.php');
}

function requireRole($role) {
    if ($_SESSION['user_role'] !== $role) {
        redirect('login.php');
    }
}