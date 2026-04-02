<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Check Sheet Inspection System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Check Sheet Inspection System">
    <meta name="keywords" content="checksheet, inspection, admin, bootstrap">
    <meta name="author" content="">

    <!-- Favicon -->
    <link rel="icon"
          href="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/assets/images/favicon.ico"
          type="image/x-icon">

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">

    <!-- Required Framework -->
    <link rel="stylesheet" type="text/css"
          href="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/bower_components/bootstrap/dist/css/bootstrap.min.css">

    <!-- feather icons -->
    <link rel="stylesheet" type="text/css"
          href="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/assets/icon/feather/css/feather.css">

    <!-- Template style -->
    <link rel="stylesheet" type="text/css"
          href="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/assets/css/style.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/assets/css/jquery.mCustomScrollbar.css">
</head>

<body>
<!-- Pre-loader start -->
<div class="theme-loader">
    <div class="ball-scale">
        <div class="contain">
            <?php for ($i = 0; $i < 10; $i++): ?>
                <div class="ring">
                    <div class="frame"></div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
<!-- Pre-loader end -->

<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">

        <!-- TOP NAVBAR -->
        <nav class="navbar header-navbar pcoded-header">
            <div class="navbar-wrapper">

                <div class="navbar-logo">
                    <a class="mobile-menu" id="mobile-collapse" href="#!">
                        <i class="feather icon-menu"></i>
                    </a>

                    <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php'); ?>">
                        <span class="text-uppercase text-white font-weight-bold">
                            Check Sheet
                        </span>
                    </a>

                    <a class="mobile-options">
                        <i class="feather icon-more-horizontal"></i>
                    </a>
                </div>

                <div class="navbar-container">
                    <ul class="nav-left">
                        <li>
                            <a href="#!" onclick="javascript:toggleFullScreen()">
                                <i class="feather icon-maximize full-screen"></i>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav-right">
                        <?php if ($user): ?>
                            <li class="user-profile header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        <span><?php echo htmlspecialchars($user['username']); ?>
                                            (<?php echo htmlspecialchars($user['role']); ?>)</span>
                                        <i class="feather icon-chevron-down"></i>
                                    </div>
                                    <ul class="show-notification profile-notification dropdown-menu"
                                        data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li>
                                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_form'); ?>">
                                                <i class="feather icon-check-square"></i> Check Entry
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=logout'); ?>">
                                                <i class="feather icon-log-out"></i> Logout
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </nav>
        <!-- /TOP NAVBAR -->

        <!-- MAIN CONTAINER & SIDEBAR -->
        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">

                <!-- SIDEBAR -->
                <nav class="pcoded-navbar">
                    <div class="pcoded-inner-navbar main-menu">

                        <div class="pcoded-navigatio-lavel">Navigation</div>
                        <ul class="pcoded-item pcoded-left-item">
                            <li class="<?php echo ($current_page === 'dashboard' ? 'active' : ''); ?>">
                                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php'); ?>">
                                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                                    <span class="pcoded-mtext">Dashboard / Results</span>
                                </a>
                            </li>

                            <li class="<?php echo ($current_page === 'checks_form' ? 'active' : ''); ?>">
                                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_form'); ?>">
                                    <span class="pcoded-micon"><i class="feather icon-clipboard"></i></span>
                                    <span class="pcoded-mtext">Check Entry</span>
                                </a>
                            </li>

                            <li class="<?php echo ($current_page === 'checks_list' ? 'active' : ''); ?>">
                                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_list'); ?>">
                                    <span class="pcoded-micon"><i class="feather icon-list"></i></span>
                                    <span class="pcoded-mtext">Results</span>
                                </a>
                            </li>

                            <?php if (is_admin()): ?>
                                <li class="pcoded-hasmenu <?php echo in_array($current_page, ['areas', 'check_sheets', 'users']) ? 'active pcoded-trigger' : ''; ?>">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-settings"></i></span>
                                        <span class="pcoded-mtext">Master Data</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="<?php echo ($current_page === 'areas' ? 'active' : ''); ?>">
                                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=areas'); ?>">
                                                <span class="pcoded-mtext">Areas</span>
                                            </a>
                                        </li>
                                        <li class="<?php echo ($current_page === 'check_sheets' ? 'active' : ''); ?>">
                                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets'); ?>">
                                                <span class="pcoded-mtext">Check Sheets &amp; Items</span>
                                            </a>
                                        </li>
                                        <li class="<?php echo ($current_page === 'users' ? 'active' : ''); ?>">
                                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=users'); ?>">
                                                <span class="pcoded-mtext">Users</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>

                    </div>
                </nav>
                <!-- /SIDEBAR -->

                <!-- MAIN CONTENT WRAPPER – modules go inside page-wrapper -->
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                <!-- modules will render inside page-wrapper -->
