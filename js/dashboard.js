/* ==============================================================================
   FILE: js/dashboard.js
   PURPOSE: Interactive behavior for the Dashboard (matching Image 2).

   SCRIPTS & ASSET SOURCES NOTE:
   - Dependency 1: "js/jquery-1.10.2.min.js" (Local jQuery library)
   - Dependency 2: "js/bootstrap.min.js" (Local Bootstrap v3.3.7 JS library)
   - Behavior provided by this script:
       1. Sidebar Toggle:
          - Toggling the sidebar open/closed via the "Toggle Sidebar" cyan button.
          - Toggling the sidebar closed/open via the top-left arrow "←".
       2. Menu Accordion:
          - Expanding and collapsing submenus (e.g. Master Files, Data Entry, Reports, Admin).
          - Updating the chevron icon (up/down) when an accordion section opens or closes.
          - Highlighting the active menu header with the dark pill border matching Image 2.
   ============================================================================== */

$(document).ready(function () {
    "use strict";

    // 1. Sidebar Toggle functionality
    function toggleSidebar() {
        $(".sidebar").toggleClass("collapsed");
    }

    $("#sidebarToggleBtn").on("click", function (e) {
        e.preventDefault();
        toggleSidebar();
    });

    $("#sidebarBackBtn").on("click", function (e) {
        e.preventDefault();
        toggleSidebar();
    });

    // 2. Sidebar Accordion Submenu Toggle
    $(".sidebar-nav-item.has-submenu > .sidebar-nav-link").on("click", function (e) {
        e.preventDefault();

        var $parent = $(this).closest(".sidebar-nav-item");
        var $submenu = $parent.find(".sidebar-submenu");
        var $chevron = $(this).find(".sidebar-chevron");

        // Toggle submenu visibility with smooth slide
        $submenu.slideToggle(200, function () {
            if ($submenu.is(":visible")) {
                $parent.addClass("active");
                $chevron.removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
            } else {
                $parent.removeClass("active");
                $chevron.removeClass("glyphicon-chevron-up").addClass("glyphicon-chevron-down");
            }
        });
    });

    // 3. Optional: Responsive auto-collapse on small screens
    if ($(window).width() < 768) {
        $(".sidebar").addClass("collapsed");
    }
});

