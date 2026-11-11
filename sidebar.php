<?php
/* ==============================================================================
   FILE: sidebar.php
   PURPOSE: Reusable Sidebar navigation component (matching the new Dark Navy reference image).

   CSS & ASSET SOURCES NOTE:
   - Styling: "css/dashboard.css" (.sidebar, .sidebar-header, .sidebar-nav, etc.)
   - Base Framework: "css/bootstrap.min.css" (Local Bootstrap v3.3.7 grid & base classes)
   - JavaScript Behavior: "js/dashboard.js" (Handles submenu expand/collapse and sidebar toggle)
   - Dynamic Values:
       * Company Name: $_SESSION['scomnam'] (Defaults to "Ramico International (Pvt) Ltd")
       * Logged-in User: $_SESSION['susdusd'] (Defaults to "2000")
   ============================================================================== */

$companyName = !empty($_SESSION['scomnam']) && $_SESSION['scomnam'] !== 'Ravi Breeder Farm' 
    ? $_SESSION['scomnam'] 
    : 'Ramico International (Pvt) Ltd';
$loggedUser  = !empty($_SESSION['susdusd']) ? $_SESSION['susdusd'] : '2000';
?>

<!-- SIDEBAR COMPONENT (Dark Navy Midnight Theme matching image) -->
<aside class="sidebar" id="sidebar">

    <div>
        <!-- Top Header: Company Name & Logged In User -->
        <div class="sidebar-header">
            <h1 class="sidebar-title">
                <?= htmlspecialchars($companyName) ?>
            </h1>
            <p class="sidebar-subtitle">Logged in : <?= htmlspecialchars($loggedUser) ?></p>
        </div>

        <!-- Section Title matching image -->
        <div class="sidebar-section-title">TEA EXPORT SYSTEM</div>

        <!-- Navigation Menu -->
        <ul class="sidebar-nav">

            <!-- (1) Operations (Active/Expanded with submenu matching image) -->
            <li class="sidebar-nav-item has-submenu active">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(1) Operations</span>
                    <span class="sidebar-chevron glyphicon glyphicon-chevron-down"></span>
                </a>
                <ul class="sidebar-submenu" style="display: block;">
                    <li class="sidebar-submenu-item">
                        <a href="#" class="sidebar-submenu-link">Tea Purchases/Grn</a>
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

            <!-- (3) Master Files (with right chevron matching image) -->
            <li class="sidebar-nav-item has-submenu">
                <a href="javascript:void(0);" class="sidebar-nav-link">
                    <span>(3) Master Files</span>
                    <span class="sidebar-chevron glyphicon glyphicon-chevron-right"></span>
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

    <!-- Sidebar Footer: Logout link matching image -->
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-footer-link">Logout</a>
    </div>

</aside>
