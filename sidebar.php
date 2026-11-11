<?php
/* ==============================================================================
   FILE: sidebar.php
   PURPOSE: Reusable Sidebar navigation component (matching Image 2).

   CSS & ASSET SOURCES NOTE:
   - Styling: "css/dashboard.css" (.sidebar, .sidebar-header, .sidebar-nav, etc.)
   - Base Icons / Font: "css/bootstrap.min.css" (Glyphicons & Bootstrap layout)
   - JavaScript Behavior: "js/dashboard.js" (Handles submenu accordion & toggle)
   - Dynamic Session Values:
       * Company Name: $_SESSION['scomnam'] (Defaults to "Ravi Breeder Farm")
       * Logged-in User: $_SESSION['susdusd'] (Defaults to "2000")
   ============================================================================== */

$companyName = !empty($_SESSION['scomnam']) ? $_SESSION['scomnam'] : 'Ravi Breeder Farm';
$loggedUser  = !empty($_SESSION['susdusd']) ? $_SESSION['susdusd'] : '2000';
?>

<!-- SIDEBAR COMPONENT (Matching Image 2) -->
<aside class="sidebar" id="sidebar">

    <div>
        <!-- Top Header: Back Arrow, Company Name & Logged In User -->
        <div class="sidebar-header">
            <a href="javascript:void(0);" class="sidebar-back-btn" id="sidebarBackBtn" title="Collapse Sidebar">&larr;</a>
            <h1 class="sidebar-title"><?= htmlspecialchars($companyName) ?></h1>
            <p class="sidebar-subtitle">Logged in : <?= htmlspecialchars($loggedUser) ?></p>
        </div>

        <!-- Section Heading -->
        <div class="sidebar-section-title">Breeder System</div>

        <!-- Navigation Menu -->
        <ul class="sidebar-nav">

            <!-- (1) Master Files (Active pill outline with expanded submenu matching Image 2) -->
            <li class="sidebar-nav-item has-submenu active">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(1)Master Files</span>
                    <span class="glyphicon glyphicon-chevron-up sidebar-chevron"></span>
                </a>
                <ul class="sidebar-submenu" style="display: block;">
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(1)Item Master Maintenance</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(2)Locations</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(3)Customers</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(4)Suppliers</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(5)Rep Master</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(6)Item Rate File (Supplier Wise)</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(7)Item Selling Price Rate File</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">---</a>
                    </li>
                </ul>
            </li>

            <!-- (2) Data Entry -->
            <li class="sidebar-nav-item has-submenu">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(2)Data Entry</span>
                    <span class="glyphicon glyphicon-chevron-down sidebar-chevron"></span>
                </a>
                <ul class="sidebar-submenu" style="display: none;">
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(1)Daily Data Entry</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(2)Stock Adjustment</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(3)Batch Transfer</a>
                    </li>
                </ul>
            </li>

            <!-- (3) Reports -->
            <li class="sidebar-nav-item has-submenu">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(3)Reports</span>
                    <span class="glyphicon glyphicon-chevron-down sidebar-chevron"></span>
                </a>
                <ul class="sidebar-submenu" style="display: none;">
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(1)Stock Summary Report</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(2)Sales & Issue Report</a>
                    </li>
                </ul>
            </li>

            <!-- (4) Admin -->
            <li class="sidebar-nav-item has-submenu">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(4)Admin</span>
                    <span class="glyphicon glyphicon-chevron-down sidebar-chevron"></span>
                </a>
                <ul class="sidebar-submenu" style="display: none;">
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(1)User Master Maintenance</a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">(2)System Configuration</a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>

    <!-- Footer Links: Change Password & Logout -->
    <div class="sidebar-footer">
        <a href="usdchgpwd.php" class="sidebar-footer-link">Change Password</a>
        <a href="logout.php" class="sidebar-footer-link">Logout</a>
    </div>

</aside>
