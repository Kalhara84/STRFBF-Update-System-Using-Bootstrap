<?php
/* ==============================================================================
   FILE: sidebar.php
   PURPOSE: Reusable Sidebar navigation component (Dark Navy Midnight Theme).

   CSS & ASSET SOURCES NOTE:
   - Styling: "css/dashboard.css" (.sidebar, .sidebar-header, .sidebar-nav, etc.)
   - Base Framework: "css/bootstrap5.min.css" (Local Bootstrap v5.3.3)
   - Logo Asset: "img/lgf-Logo.png" (Displayed above company title)
   - JavaScript Behavior: "js/dashboard.js" (Handles submenu expand/collapse)
   - Red Logout Button: Styled via .sidebar-logout-btn in css/dashboard.css
   ============================================================================== */

$companyName = !empty($_SESSION['scomnam']) && $_SESSION['scomnam'] !== 'Ravi Breeder Farm'
    ? $_SESSION['scomnam']
    : 'Ramico International (Pvt) Ltd';
$loggedUser = !empty($_SESSION['susdusd']) ? $_SESSION['susdusd'] : '2000';
?>

<!-- SIDEBAR COMPONENT (Dark Navy Midnight Theme) -->
<aside class="sidebar" id="sidebar">

    <div>
        <!-- Top Header: Logo above Company Name & User -->
        <div class="sidebar-header">
            <!-- Logo displayed above text (User Request 2) -->
            <div class="sidebar-logo-wrap">
                <img src="img/lgf-Logo.png" alt="Logo" class="sidebar-logo-img">
            </div>
            <h1 class="sidebar-title">
                <?= htmlspecialchars($companyName) ?>
            </h1>
            <p class="sidebar-subtitle">Logged in : <?= htmlspecialchars($loggedUser) ?></p>
        </div>

        <!-- Section Title -->
        <div class="sidebar-section-title">Dashboard Sidebar</div>

        <!-- Navigation Menu -->
        <ul class="sidebar-nav">

            <!-- (1) Operations (Active/Expanded with submenu) -->
            <li class="sidebar-nav-item has-submenu active">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(1) Operations</span>
                    <span class="sidebar-chevron" aria-hidden="true"></span>
                </a>
                <ul class="sidebar-submenu" style="display: block;">
                    <li class="sidebar-submenu-item">
                        <a href="dpmm.php" class="sidebar-submenu-link">(1) Enter Payment Request</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">Reports</a>
                    </li>
                </ul>
            </li>

            <!-- (2) Accounts Related -->
            <li class="sidebar-nav-item">
                <a href="#" class="sidebar-nav-link">
                    <span>(2) Accounts Related</span>
                </a>
            </li>

            <!-- (3) Master Files -->
            <li class="sidebar-nav-item has-submenu">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(3) Master Files</span>
                    <span class="sidebar-chevron" aria-hidden="true"></span>
                </a>
                <ul class="sidebar-submenu" style="display: none;">
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(1) Item Master Maintenance</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(2) Locations</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(3) Customers</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(4) Suppliers</a>
                    </li>
                </ul>
            </li>

            <!-- (15) Admin -->
            <li class="sidebar-nav-item has-submenu">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(15) Admin</span>
                </a>
            </li>

        </ul>
    </div>

    <!-- Sidebar Footer: Distinct Red Logout Button (User Request 4) -->
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-logout-btn" title="Sign Out">
            <svg class="sidebar-logout-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Logout</span>
        </a>
    </div>

</aside>