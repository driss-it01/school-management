<?php
require_once 'config.php';
$user = new User();
$user->logout();
redirect('login.php');