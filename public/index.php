<?php
require_once __DIR__ . '/../src/config.php';

if (!empty($_SESSION['user'])) {
    header('Location: dashboard_ma.php');
    exit;
}

header('Location: login.php');
exit;
