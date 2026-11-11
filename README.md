# System Architecture & Code Study Guide: CSS, JavaScript & PHP Integration

This comprehensive documentation explains every CSS class and JavaScript interaction used across each PHP file in the `strbf_pasan` project. It is written in simple, clear English specifically to help you study and understand how PHP, HTML, CSS, and JavaScript connect together.

---

## Table of Contents
1. [Project Overview & File Architecture](#1-project-overview--file-architecture)
2. [sidebar.php – CSS Classes & JavaScript Explanations](#2-sidebarphp--css-classes--javascript-explanations)
3. [dashboard.php – CSS Classes & JavaScript Explanations](#3-dashboardphp--css-classes--javascript-explanations)
4. [login.php – CSS Classes & JavaScript Explanations](#4-loginphp--css-classes--javascript-explanations)
5. [logout.php – Logic & Connection Flow](#5-logoutphp--logic--connection-flow)
6. [CSS Files Reference (Where Each Class Comes From)](#6-css-files-reference)
7. [JavaScript Files Reference (What Each Script Does)](#7-javascript-files-reference)

---

## 1. Project Overview & File Architecture

The system consists of the following local files:

```text
strbf_pasan/
├── css/
│   ├── bootstrap.min.css     <-- Bootstrap v3.3.7 base CSS framework
│   ├── custom.css            <-- Styles specifically for login.php
│   └── dashboard.css         <-- Styles for dashboard.php & sidebar.php
├── js/
│   ├── jquery-1.10.2.min.js  <-- jQuery library for DOM manipulation & events
│   ├── bootstrap.min.js      <-- Bootstrap v3.3.7 JavaScript
│   ├── dashboard.js          <-- Custom JS for sidebar toggle & menu accordion
│   └── login.js              <-- Form validation logic for login.php
├── img/
│   ├── lgf-Logo.png          <-- Company circular logo
│   ├── login-back2.jpg       <-- Wavy 3D background image for login.php
│   └── login-side.jpg        <-- Laptop chart illustration for login.php
├── sidebar.php               <-- Modular navigation sidebar component
├── dashboard.php             <-- Main dashboard page (includes sidebar.php)
├── login.php                 <-- User authentication page
└── logout.php                <-- Session termination script
```

---

## 2. sidebar.php – CSS Classes & JavaScript Explanations

### Role of `sidebar.php`
`sidebar.php` is a modular PHP component included directly inside `dashboard.php` using `<?php include 'sidebar.php'; ?>`. It renders the vertical navigation bar on the left side of the screen.

### CSS Classes Used in `sidebar.php`

| CSS Class Name | Source File | What It Does / Styling Purpose |
| :--- | :--- | :--- |
| `sidebar` | `css/dashboard.css` | Styles the main left sidebar container: sets fixed width of `270px`, deep dark slate background (`#0e1428`), vertical flex layout, border divider on the right (`1px solid #1a223e`), and smooth slide transition (`0.28s`). |
| `collapsed` | `css/dashboard.css` | Added dynamically by `js/dashboard.js` when the user clicks the toggle button. It sets `margin-left: -270px;`, sliding the sidebar smoothly off-screen. |
| `sidebar-header` | `css/dashboard.css` | The top container block inside the sidebar. Sets padding and `text-align: center;` so the logo, company name, and username are centered. |
| `sidebar-logo-wrap` | `css/dashboard.css` | Wrapper `<div>` for the logo image. Sets auto margins to horizontally center the logo above the text. |
| `sidebar-logo-img` | `css/dashboard.css` | Styles the company logo image: size of `76px x 76px`, circular shape (`border-radius: 50%`), glowing border (`2px solid rgba(79, 111, 230, 0.45)`), and smooth hover scale effect. |
| `sidebar-title` | `css/dashboard.css` | Styles the main company heading ("Ramico International (Pvt) Ltd"): white bold text (`font-size: 17px; font-weight: 700; text-align: center;`). |
| `sidebar-subtitle` | `css/dashboard.css` | Styles the logged-in user label ("Logged in : 2000"): muted pale blue-gray text (`font-size: 13px; color: rgba(255, 255, 255, 0.65); text-align: center;`). |
| `sidebar-section-title` | `css/dashboard.css` | Uppercase category divider heading ("TEA EXPORT SYSTEM"): small uppercase font (`font-size: 12px; letter-spacing: 1.2px; text-transform: uppercase; color: rgba(255, 255, 255, 0.5);`). |
| `sidebar-nav` | `css/dashboard.css` | The parent `<ul>` navigation list. Clears default browser list styling (removes bullet points, removes default browser margins). |
| `sidebar-nav-item` | `css/dashboard.css` | Each menu item wrapper `<li>`. Adds small vertical spacing (`margin-bottom: 5px;`) between menu items. |
| `has-submenu` | `css/dashboard.css` | A marker class indicating that this menu item contains a collapsible sub-list (`.sidebar-submenu`). Used by JavaScript to detect click events. |
| `active` | `css/dashboard.css` | Highlight state for open or active menu items. Applies a lighter navy background (`background-color: #1a2444;`) to the active parent item. |
| `sidebar-nav-link` | `css/dashboard.css` | Clickable anchor link (`<a>`) for main menu headers. Flexbox layout to push chevron arrow to the right, rounded corners (`border-radius: 6px`), hover background highlight (`#151e3a`). |
| `sidebar-chevron` | `css/dashboard.css` | Small arrow icon container on the right side of menu links. Controls icon sizing (`11px`) and rotation animation. |
| `glyphicon` | `css/bootstrap.min.css` | Base Bootstrap font icon class required for all Glyphicon icons. |
| `glyphicon-chevron-down` | `css/bootstrap.min.css` | Displays a down-facing arrow icon `v` indicating an open or expandable menu. |
| `glyphicon-chevron-right` | `css/bootstrap.min.css` | Displays a right-facing arrow icon `>` indicating a collapsed menu. |
| `sidebar-submenu` | `css/dashboard.css` | The nested child `<ul>` list holding sub-items like "Tea Purchases/Grn" and "Reports". Can be hidden (`display: none;`) or shown (`display: block;`). |
| `sidebar-submenu-item` | `css/dashboard.css` | Child wrapper `<li>` inside a submenu. |
| `sidebar-submenu-link` | `css/dashboard.css` | Clickable child link (`<a>`). Muted text color that brightens to pure white with an indigo background on hover (`rgba(79, 111, 230, 0.15)`). |
| `sidebar-footer` | `css/dashboard.css` | Bottom container pinned to the bottom of the sidebar. Has a top divider line (`border-top: 1px solid #1a223e;`) separating navigation from the logout area. |
| `sidebar-logout-btn` | `css/dashboard.css` | Styles the prominent red Logout button: vibrant red gradient (`linear-gradient(135deg, #e53935 0%, #c62828 100%)`), white bold text, shadow, rounded corners, and hover lift effect. |
| `sidebar-logout-icon` | `css/dashboard.css` | Sizing (`16px x 16px`) for the SVG logout door/exit icon. |

### JavaScript Interactions in `sidebar.php`
- **File:** `js/dashboard.js`
- **Code:**
  ```javascript
  $(".sidebar-nav-item.has-submenu > .sidebar-nav-link").on("click", function (e) {
      e.preventDefault();
      var $parent = $(this).closest(".sidebar-nav-item");
      var $submenu = $parent.find(".sidebar-submenu");
      var $chevron = $(this).find(".sidebar-chevron");

      $submenu.slideToggle(200, function () {
          if ($submenu.is(":visible")) {
              $parent.addClass("active");
              $chevron.removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
          } else {
              $parent.removeClass("active");
              $chevron.removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
          }
      });
  });
  ```
- **How It Works:**
  1. User clicks any menu header with `.has-submenu` (e.g. `(1) Operations` or `(3) Master Files`).
  2. jQuery intercepts the click event using `.on("click", ...)`.
  3. `.slideToggle(200)` animates the child submenu opening or closing over 200 milliseconds.
  4. If the submenu opens: it adds `.active` to the parent and changes the chevron arrow to point downward (`glyphicon-chevron-down`).
  5. If the submenu closes: it removes `.active` and changes the chevron arrow to point rightward (`glyphicon-chevron-right`).

---

## 3. dashboard.php – CSS Classes & JavaScript Explanations

### Role of `dashboard.php`
`dashboard.php` is the primary authenticated user landing page. It checks the PHP session, includes `sidebar.php`, displays the icon-based sidebar toggle button, and renders the central information card.

### CSS Classes Used in `dashboard.php`

| CSS Class Name | Source File | What It Does / Styling Purpose |
| :--- | :--- | :--- |
| `dashboard-wrapper` | `css/dashboard.css` | Top-level flexbox container (`display: flex; min-height: 100vh; background-color: #0b1020;`). Organizes the sidebar and main content side-by-side. |
| `main-content` | `css/dashboard.css` | The right-side main body area. Takes up all remaining screen width (`flex: 1;`), with deep navy background (`#0b1020`) and generous padding (`24px 36px`). |
| `toolbar-actions` | `css/dashboard.css` | Header container for action buttons at the top of the main content area (`margin-bottom: 24px;`). |
| `btn-toggle-sidebar` | `css/dashboard.css` | Modern icon-only square button (`42px x 42px`) with royal indigo background (`#4f6fe6`), rounded corners (`8px`), soft drop shadow, and hover lift effect. |
| `toggle-icon` | `css/dashboard.css` | Sizing (`20px x 20px`) and alignment for the SVG hamburger menu icon inside the toggle button. |
| `dashboard-card` | `css/dashboard.css` | The main dark slate card container (`background-color: #131a2e; border: 1px solid #1e2848; border-radius: 12px; box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45);`). Expanded to `width: 100%` so no empty space is wasted. |
| `card-header-area` | `css/dashboard.css` | Top centered section inside the card with bottom border line (`border-bottom: 1px solid #1c2646;`). Contains the circular logo and card title. |
| `card-logo-badge` | `css/dashboard.css` | Circular frame (`52px x 52px; border-radius: 50%; background-color: #1b2444; border: 1px solid #2a3867;`) displaying the centered company logo image. |
| `card-main-title` | `css/dashboard.css` | Large bold white heading ("Service Provider Name", `font-size: 26px; font-weight: 700; color: #ffffff; text-align: center;`). |
| `card-table-box` | `css/dashboard.css` | Inner table container (`background-color: #10162a; border: 1px solid #1f2a4a; border-radius: 8px;`). Wraps all data rows cleanly. |
| `info-row` | `css/dashboard.css` | Each row inside the table box. Uses flexbox (`display: flex; align-items: center;`), horizontal divider line (`border-bottom: 1px solid #1f2a4a;`), and hover highlight (`background-color: #141c35;`). |
| `info-label` | `css/dashboard.css` | The left column label (e.g. "User Name", "Email"): white bold text with fixed width (`width: 280px; font-weight: 600; color: #ffffff;`). |
| `info-value` | `css/dashboard.css` | The right column value (e.g. "2000", "rumeshdezoysa@gmail.com"): flexible width (`flex: 1;`), bright text (`color: rgba(255, 255, 255, 0.95);`). |

### JavaScript Interactions in `dashboard.php`
- **File:** `js/dashboard.js`
- **Code:**
  ```javascript
  function toggleSidebar() {
      $(".sidebar").toggleClass("collapsed");
  }

  $("#sidebarToggleBtn").on("click", function (e) {
      e.preventDefault();
      toggleSidebar();
  });
  ```
- **How It Works:**
  1. The user clicks the royal blue icon button (`#sidebarToggleBtn`).
  2. jQuery intercepts the click event.
  3. `$(".sidebar").toggleClass("collapsed")` toggles the `.collapsed` CSS class on `<aside class="sidebar">`.
  4. If `.collapsed` is added: the sidebar animates off-screen to the left (`margin-left: -270px;`).
  5. If `.collapsed` is removed: the sidebar animates back into full view (`margin-left: 0;`).

---

## 4. login.php – CSS Classes & JavaScript Explanations

### Role of `login.php`
`login.php` handles user authentication (verifying User Code `usdusd` and Password `usdpwd` against database table `usdmast`). It renders a centered 2-column card matching Image 1.

### CSS Classes Used in `login.php`

| CSS Class Name | Source File | What It Does / Styling Purpose |
| :--- | :--- | :--- |
| `login-body` | `css/custom.css` | Full-height page wrapper (`min-height: 100vh; display: flex; align-items: center; justify-content: center;`). Displays fluid 3D wavy background wallpaper (`img/login-back2.jpg`). |
| `login-card` | `css/custom.css` | Centered floating card (`max-width: 860px; background: #ffffff; border-radius: 4px; box-shadow: 0 20px 45px rgba(16, 44, 92, 0.28);`). |
| `row` | `css/bootstrap.min.css` | Standard Bootstrap grid row with negative margins. |
| `login-row` | `css/custom.css` | Custom flex row (`display: flex; min-height: 440px;`) making both left and right sides equal height. |
| `col-sm-6` | `css/bootstrap.min.css` | Bootstrap 6-column grid class giving each side exactly 50% width on tablet/desktop screens. |
| `login-left` | `css/custom.css` | The solid royal blue left panel (`background: linear-gradient(145deg, #0f52b6, #0d469c); color: #ffffff; padding: 42px;`). |
| `login-brand` | `css/custom.css` | Brand container holding the circular RBF badge and "Login" heading. |
| `brand-badge` | `css/custom.css` | Circular white outline badge (`width: 44px; height: 44px; border: 2px solid #ffffff; border-radius: 50%; font-weight: 700;`) with text "RBF". |
| `brand-title` | `css/custom.css` | Heading text "Login" (`font-size: 24px; color: #ffffff;`). |
| `login-form` | `css/custom.css` | Form wrapper controlling internal vertical spacing. |
| `form-group` | `css/bootstrap.min.css` | Bootstrap field container applying vertical spacing (`margin-bottom: 22px;`). |
| `form-control` | `css/bootstrap.min.css` & `css/custom.css` | Underline text input style: transparent background, no box border, only a white bottom line (`border-bottom: 1px solid rgba(255,255,255,0.55); color: #ffffff;`). |
| `input-lg` | `css/bootstrap.min.css` | Bootstrap class setting comfortable input height (`44px`). |
| `login-options` | `css/custom.css` | Flexbox row with `justify-content: space-between;` placing "Remember Me" on the left and "Forgot Password...?" on the right. |
| `login-remember` | `css/custom.css` | Label styling for the checkbox (`font-size: 13px; color: #ffffff; cursor: pointer;`). |
| `login-forgot` | `css/custom.css` | Anchor link styling for the password reset link (`color: rgba(255, 255, 255, 0.85); font-size: 13px;`). |
| `btn` | `css/bootstrap.min.css` | Bootstrap base button class. |
| `btn-block` | `css/bootstrap.min.css` | Bootstrap class making the button span 100% full width. |
| `btn-lg` | `css/bootstrap.min.css` | Bootstrap large button padding. |
| `btn-login` | `css/custom.css` | Custom styling for the "SIGN IN" button: pure white background (`#ffffff`), bold royal blue text (`#0f52b6`), uppercase lettering with letter-spacing. |
| `login-right` | `css/custom.css` | Right panel displaying background image `img/login-side.jpg` (laptop bar chart illustration) and pro-tip text. |
| `hidden-xs` | `css/bootstrap.min.css` | Bootstrap responsive helper class that automatically hides the right image panel on small mobile phones (`< 768px`). |
| `login-tip` | `css/custom.css` | Text container for "PRO TIP #1", "Speed up data entry", and instructional text. |
| `bg-danger` | `css/bootstrap.min.css` & `css/custom.css` | Light red alert box used by PHP to display invalid credentials or error messages. |
| `bg-warning` | `css/bootstrap.min.css` & `css/custom.css` | Light yellow alert box used by PHP to display warning messages. |

### JavaScript Interactions in `login.php`
- **File:** `js/login.js` (uses `js/jquery.validate.min.js`)
- **Code:**
  ```javascript
  $("#login").validate({
      rules: {
          usdusd: { required: true },
          usdpwd: { required: true }
      },
      messages: {
          usdusd: "User Name Required",
          usdpwd: "Password Required"
      }
  });
  ```
- **How It Works:**
  1. When the user clicks "SIGN IN", jQuery Validate inspects both input fields.
  2. If either `usdusd` or `usdpwd` is blank, submission is prevented and a `<label class="error">` message is shown below the input.
  3. When both fields are filled, the form submits to the PHP backend (`login.php`) via POST.

---

## 5. logout.php – Logic & Connection Flow

### Role of `logout.php`
`logout.php` contains no HTML, CSS, or JS. It is a dedicated server-side endpoint that securely clears the authenticated user session.

### Execution Flow:
1. `if (!isset($_SESSION)) { session_start(); }`: Resumes the user's active session.
2. `$_SESSION = array();`: Clears all in-memory session variables (such as `susdusd`, `validated`, `dashboard_email`).
3. `setcookie(session_name(), '', time() - 42000, ...)`: Expires and deletes the session cookie stored in the user's browser.
4. `session_destroy()`: Destroys the session file stored on the server.
5. `header("Location: login.php"); exit;`: Instantly sends HTTP redirect back to `login.php`.

---

## 6. CSS Files Reference

1. **`css/bootstrap.min.css`** (Bootstrap v3.3.7 - Local)
   - Provides grid system (`.row`, `.col-sm-6`), responsive utilities (`.hidden-xs`), button base styles (`.btn`, `.btn-block`), form groups (`.form-group`), and Glyphicon icons (`.glyphicon`, `.glyphicon-chevron-down`).
2. **`css/custom.css`** (Custom Login Stylesheet - Local)
   - Specifically styles `login.php`: wavy fluid background, centered 2-column card, underline text inputs, RBF badge, and white SIGN IN button.
3. **`css/dashboard.css`** (Custom Dashboard Stylesheet - Local)
   - Specifically styles `dashboard.php` and `sidebar.php`: Midnight Navy theme (`#0b1020`), Dark Slate sidebar (`#0e1428`), expanded table card (`#131a2e`), icon-only toggle button (`#4f6fe6`), and vibrant red logout button (`#e53935`).

---

## 7. JavaScript Files Reference

1. **`js/jquery-1.10.2.min.js`** (jQuery Library - Local)
   - Fast JavaScript library providing DOM element selection, event listeners (`.on("click")`), and animation methods (`.slideToggle()`).
2. **`js/bootstrap.min.js`** (Bootstrap JavaScript - Local)
   - Powers Bootstrap components and responsive collapse features.
3. **`js/dashboard.js`** (Custom Dashboard Logic - Local)
   - Binds the click event to `#sidebarToggleBtn` to add/remove `.collapsed` on the sidebar.
   - Binds click events to `.sidebar-nav-item.has-submenu > .sidebar-nav-link` to slide open/close submenus and rotate chevron icons.
4. **`js/login.js`** (Custom Form Validation - Local)
   - Uses `jquery.validate.min.js` to validate required fields on `login.php` before form submission.

