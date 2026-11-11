<?php
if (!isset($_SESSION)) {
    session_start();
}
if (empty($_SESSION['validated']) || empty($_SESSION['susdusd'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Please sign in again.');
}
require_once dirname(__FILE__) . '/db.php';
