/* ==============================================================================
   FILE: js/dashboard.js
   PURPOSE: Interactive behavior for the Dark Navy Dashboard.

   SCRIPTS & ASSET SOURCES NOTE:
   - Dependency 1: "js/jquery-1.10.2.min.js" (Local jQuery library)
   - Dependency 2: "js/bootstrap.min.js" (Local Bootstrap v3.3.7 JS library)
   - Capabilities:
       1. "Toggle Sidebar" button functionality to show/hide the sidebar smoothly.
       2. Sidebar accordion menu toggle for items with submenus (e.g. Operations, Master Files).
       3. Responsive mobile handling.
   ============================================================================== */

$(document).ready(function () {
    "use strict";

    // 1. Sidebar Toggle Button
    function toggleSidebar() {
        $(".sidebar").toggleClass("collapsed");
    }

    $("#sidebarToggleBtn").on("click", function (e) {
        e.preventDefault();
        toggleSidebar();
    });

    // 2. Accordion Submenu Toggle for Sidebar items
    $(".sidebar-nav-item.has-submenu > .sidebar-nav-link").on("click", function (e) {
        e.preventDefault();

        var $parent = $(this).closest(".sidebar-nav-item");
        var $submenu = $parent.find(".sidebar-submenu");
        var $chevron = $(this).find(".sidebar-chevron");

        // Slide toggle submenu
        $submenu.slideToggle(200, function () {
            if ($submenu.is(":visible")) {
                $parent.addClass("active");
                if ($chevron.hasClass("glyphicon-chevron-right")) {
                    $chevron.removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
                }
            } else {
                $parent.removeClass("active");
                if ($chevron.hasClass("glyphicon-chevron-down") && $parent.find(".sidebar-nav-link span:contains('Master')").length) {
                    $chevron.removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
                }
            }
        });
    });

    // 3. Auto-collapse on small mobile screens
    if ($(window).width() < 768) {
        $(".sidebar").addClass("collapsed");
    }
});
