<?php 
ob_start();
session_start();
include 'includes/db.php';
unset($_SESSION['user']);
header("location: login.php"); 
?>