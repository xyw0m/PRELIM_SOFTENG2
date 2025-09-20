<?php
session_start();
require_once '../src/Auth.php';
$auth = new Auth();
$auth->logout();
header("Location: index.php");
exit();
