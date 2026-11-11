<?php
/* ==============================================================================
   FILE: dashboard.php
   PURPOSE: Main Dashboard page (matching Image 2).

   CSS & JAVASCRIPT ASSET SOURCES / DOCUMENTATION NOTE:
   - "css/bootstrap.min.css":
       Bootstrap v3.3.7 CSS framework stored locally in the "css" directory.
       Provides responsive grid, typography, and base utility classes without external CDN.
   - "css/dashboard.css":
       Custom dashboard stylesheet stored locally in the "css" directory.
       Provides styling for the periwinkle sidebar (#687fd9), active pill outline,
       cyan toolbar buttons (#24b8d9), and clean card rows with horizontal dividers.
   - "js/jquery-1.10.2.min.js":
       Local jQuery library located in the "js" directory.
       Enables DOM selection and interactive event handling.
   - "js/bootstrap.min.js":
       Bootstrap v3.3.7 JavaScript located in the "js" directory.
       Provides Bootstrap component support.
   - "js/dashboard.js":
       Custom dashboard scripts located in the "js" directory.
       Handles sidebar toggle (open/close) and menu accordion expand/collapse.
   - "sidebar.php":
       Modular PHP component included into this dashboard layout.
   ============================================================================== */

// Start session to access logged-in user information
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is validated; if not logged in, optionally redirect or set demo defaults
if (!isset($_SESSION["validated"]) || $_SESSION["validated"] !== true) {
    // If not logged in, you can enable redirection:
    // header("Location: login.php");
    // exit;
}

// User details for dashboard display (matching Image 2 defaults)
$userName       = !empty($_SESSION['susdusd']) ? $_SESSION['susdusd'] : '2000';
$userEmail      = !empty($_SESSION['dashboard_email']) ? $_SESSION['dashboard_email'] : '';
$passwordExpiry = !empty($_SESSION['dashboard_password_expiry']) ? $_SESSION['dashboard_password_expiry'] : '';
$companyNews    = !empty($_SESSION['company_news']) ? $_SESSION['company_news'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Dashboard - <?= htmlspecialchars($_SESSION['scomnam'] ?? 'Ravi Breeder Farm') ?></title>

    <!-- 1. Bootstrap v3.3.7 CSS (Local file from css/ folder) -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

    <!-- 2. Custom Dashboard Stylesheet (Local file from css/ folder) -->
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

    <!-- Main Content Area (Matching Image 2) -->
    <main class="main-content">

        <!-- Top Action Buttons (Cyan Toolbar) -->
        <div class="toolbar-actions">
            <button type="button" class="btn-cyan" id="sidebarToggleBtn" title="Toggle Sidebar">
                <span class="icon-bars"></span>
                <span>Toggle Sidebar</span>
            </button>
            <button type="button" class="btn-cyan">
                <span>Inq All Docs</span>
            </button>
            <button type="button" class="btn-cyan">
                <span>-------</span>
            </button>
            <button type="button" class="btn-cyan">
                <span>-------</span>
            </button>
        </div>

        <!-- Center Information Card (Matching Image 2 layout & lines) -->
        <div class="dashboard-card">

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

