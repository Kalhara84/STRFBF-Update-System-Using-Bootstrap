<?php
/* ==============================================================================
   FILE: dashboard.php
   PURPOSE: Main Dashboard page (matching the new Dark Navy reference image).

   CSS & JAVASCRIPT ASSET SOURCES / DOCUMENTATION NOTE:
   - "css/bootstrap.min.css":
       Bootstrap v3.3.7 CSS framework stored locally in the "css" directory.
       Provides base responsive grid, layout resets, and typography.
   - "css/dashboard.css":
       Custom dark stylesheet located in the "css" directory.
       Provides the dark midnight blue palette (#0b1020, #0e1428, #131a2e),
       royal blue button (#4f6fe6), and table rows matching the user's reference image.
   - "js/jquery-1.10.2.min.js":
       Local jQuery library located in the "js" directory.
   - "js/bootstrap.min.js":
       Bootstrap v3.3.7 JavaScript located in the "js" directory.
   - "js/dashboard.js":
       Interactive script handling the "Toggle Sidebar" button and submenu collapse/expand.
   - "sidebar.php":
       Sidebar navigation component included on the left.
   ============================================================================== */

// Start session to access logged-in user information (PHP 5.3+ compatible)
if (!isset($_SESSION)) {
    session_start();
}

// User details for dashboard display (matching reference image defaults)
$companyName    = !empty($_SESSION['scomnam']) && $_SESSION['scomnam'] !== 'Ravi Breeder Farm'
    ? $_SESSION['scomnam'] 
    : 'Ramico International (Pvt) Ltd';
$userName       = !empty($_SESSION['susdusd']) ? $_SESSION['susdusd'] : '2000';
$userEmail      = !empty($_SESSION['dashboard_email']) ? $_SESSION['dashboard_email'] : 'user@example.com';
$passwordExpiry = !empty($_SESSION['dashboard_password_expiry']) ? $_SESSION['dashboard_password_expiry'] : '2026-12-31';
$companyNews    = !empty($_SESSION['company_news']) ? $_SESSION['company_news'] : '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Dashboard - <?= htmlspecialchars($companyName) ?></title>

    <!-- 1. Bootstrap v3.3.7 CSS (Local file from css/ folder) -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

    <!-- 2. Custom Dark Dashboard Stylesheet (Local file from css/ folder) -->
    <link rel="stylesheet" type="text/css" href="css/dashboard.css">

    <!-- HTML5 shim for backward compatibility with IE8 -->
    <!--[if lt IE 9]>
        <script src="js/html5shiv.min.js"></script>
    <![endif]-->
</head>
<body>

<div class="dashboard-wrapper">

    <!-- Modular Sidebar included from sidebar.php -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content Area (Matching reference image) -->
    <main class="main-content">

        <!-- Top Action Button ("Toggle Sidebar" button in image) -->
        <div class="toolbar-actions">
            <button type="button" class="btn-toggle-sidebar" id="sidebarToggleBtn" title="Toggle Sidebar">
                Toggle Sidebar
            </button>
        </div>

        <!-- Center Information Card (Dark Navy Slate Container) -->
        <div class="dashboard-card">

            <!-- Card Header: Logo Badge & Title -->
            <div class="card-header-area">
                <div class="card-logo-badge">Logo</div>
                <h2 class="card-main-title">Service Provider Name</h2>
            </div>

            <!-- Inner Table Box with Divider Lines -->
            <div class="card-table-box">

                <!-- Row 1: User Name -->
                <div class="info-row">
                    <div class="info-label">User Name</div>
                    <div class="info-value"><?= htmlspecialchars($userName) ?></div>
                </div>

                <!-- Row 2: Email -->
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($userEmail) ?></div>
                </div>

                <!-- Row 3: Password Expiry -->
                <div class="info-row">
                    <div class="info-label">Password Expiry</div>
                    <div class="info-value"><?= htmlspecialchars($passwordExpiry) ?></div>
                </div>

                <!-- Row 4: Company News -->
                <div class="info-row">
                    <div class="info-label">Company News</div>
                    <div class="info-value"><?= htmlspecialchars($companyNews) ?></div>
                </div>

            </div>

        </div>

    </main>

</div>

<!-- 1. jQuery v1.10.2 (Local file from js/ folder) -->
<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>

<!-- 2. Bootstrap v3.3.7 JS (Local file from js/ folder) -->
<script type="text/javascript" src="js/bootstrap.min.js"></script>

<!-- 3. Dashboard Interactive Script (Local file from js/ folder) -->
<script type="text/javascript" src="js/dashboard.js"></script>

</body>
</html>
