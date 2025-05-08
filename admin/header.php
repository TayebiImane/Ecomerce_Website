<?php
ob_start();
session_start();
include("../includes/db.php");
include("inc/functions.php");
include("inc/CSRF_Protect.php");
$csrf = new CSRF_Protect();
$error_message = '';
$success_message = '';
$error_message1 = '';
$success_message1 = '';
if(!isset($_SESSION['user'])) {
    header('location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Panel</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/datepicker3.css">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/select2.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.css">
    <link rel="stylesheet" href="css/jquery.fancybox.css">
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <link rel="stylesheet" href="css/_all-skins.min.css">
    <link rel="stylesheet" href="css/on-off-switch.css"/>
    <link rel="stylesheet" href="css/summernote.css">
    <link rel="stylesheet" href="style.css">
    <!-- À mettre dans <head> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Custom color scheme */
        :root {
            --primary-color:rgb(3, 115, 243);
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        .skin-blue .main-header .navbar {
            background-color: var(--primary-color);
        }
        
        .skin-blue .main-header .logo {
            background-color: var(--primary-color);
            color: #fff;
            border-bottom: 0 solid transparent;
        }
        
        .main-header .logo img {
            max-height: 40px;
            margin-top: -5px;
        }
        
        .main-sidebar {
            background-color: var(--primary-color) !important;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-menu > li > a {
            padding: 12px 15px;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu > li:hover > a, 
        .sidebar-menu > li.active > a {
            background-color: rgba(255,255,255,0.1);
            border-left-color: var(--secondary-color);
        }
        
        .sidebar-menu .fa {
            width: 25px;
            text-align: center;
        }
        
        .navbar-custom-menu .user-menu > a {
            padding: 15px;
            color: var(--light-color);
        }
        
        .navbar-custom-menu .user-menu > a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .user-footer {
            padding: 10px 15px;
        }
        
        .user-footer .btn {
            margin: 5px 0;
            width: 100%;
            text-align: left;
            border-radius: 3px;
        }
        
        .content-wrapper {
            background-color: #f5f7fa;
        }
        
        /* Improve sidebar toggle button */
        .sidebar-toggle {
            color: #fff !important;
            padding: 15px !important;
        }
        
        /* Better active state indication */
        .sidebar-menu > li.active {
            background-color: rgba(255,255,255,0.05);
        }
        
        /* Admin title styling */
        .admin-title {
            float: left;
            line-height: 50px;
            color: #fff;
            padding-left: 15px;
            font-size: 18px;
            font-weight: 300;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body class="hold-transition fixed skin-blue sidebar-mini">
    <div class="wrapper">
        <header class="main-header">
		<a class="navbar-brand" href="index.php">
		<img src="../logo.png" alt="logo" style="width: 150px; height: auto; max-height: 100px; margin-top: -20px;">
            </a>
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-bars"></i>
                </a>
                <span class="admin-title">Espace administrateur</span>
                <!-- Top Bar ... User Information .. Login/Log out Area -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-user-circle"></i>
                                <span class="hidden-xs"><?php echo $_SESSION['USER']['NOM_USER']; ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="profile-edit.php" class="btn btn-default btn-flat">
                                            <i class="fa fa-user"></i> Edit Profile
                                        </a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="logout.php" class="btn btn-default btn-flat">
                                            <i class="fa fa-sign-out"></i> Log out
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <?php $cur_page = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1); ?>
        <!-- Side Bar to Manage Shop Activities -->
        <aside class="main-sidebar">
            <section class="sidebar">
                <ul class="sidebar-menu">
                    <li class="treeview <?php if(($cur_page == 'product.php') || ($cur_page == 'product-add.php') || ($cur_page == 'product-edit.php')) {echo 'active';} ?>" style="margin-top:50px;">
                        <a href="product.php">
                            <i class="fa fa-shopping-bag"></i> 
                            <span>Produits</span>
                        </a>
                    </li>
                    <li class="treeview <?php if(($cur_page == 'commandes.php') || ($cur_page == 'order.php')) {echo 'active';} ?>">
                        <a href="order.php">
                            <i class="fa fa-sticky-note"></i> 
                            <span>Les commandes</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>
        <div class="content-wrapper">
            <!-- Main content will go here -->