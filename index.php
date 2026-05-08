<?php
require_once 'config.php';
$user = new User();
if (!$user->isLoggedIn()) redirect('login.php');

$current = $user->getCurrentUser();
if ($current['role'] === 'idara') {
    redirect('idara/dashboard.php');
} else {
    redirect('prof/dashboard.php');
}