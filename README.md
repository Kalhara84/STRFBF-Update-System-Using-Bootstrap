# System Architecture & Code Study Guide: CSS, JavaScript & PHP Integration

This document explains every CSS class and JavaScript interaction used in each PHP file of the strbf_pasan project, in simple English, so you can study how PHP, HTML, CSS and JavaScript connect together. Sections 2-5 cover one PHP file each, in the order they are used.

---

## Table of Contents
1. [Project Overview & File Architecture](#1-project-overview--file-architecture)
2. [sidebar.php - CSS Classes, JavaScript & Logic](#2-sidebarphp---css-classes-javascript--logic)
3. [dashboard.php - CSS Classes, JavaScript & Logic](#3-dashboardphp---css-classes-javascript--logic)
4. [login.php - CSS Classes, JavaScript & Logic](#4-loginphp---css-classes-javascript--logic)
5. [logout.php - CSS Classes, JavaScript & Logic](#5-logoutphp---css-classes-javascript--logic)
6. [CSS Files Reference](#6-css-files-reference)
7. [JavaScript Files Reference](#7-javascript-files-reference)

---

## 1. Project Overview & File Architecture

The system consists of these local files:

```text
strbf_pasan/
├── css/
│   ├── bootstrap.min.css     <-- Bootstrap v3.3.7 base CSS framework
│   ├── custom.css            <-- Styles for login.php
│   ├── dashboard.css         <-- Styles for dashboard.php & sidebar.php
│   └── preloader.css         <-- Empty .preloader rule (login.php, not used now)
├── js/
│   ├── jquery-1.10.2.min.js  <-- jQuery library (DOM, events, animation)
│   ├── jquery.validate.min.js<-- jQuery Validation plugin (login form)
│   ├── bootstrap.min.js      <-- Bootstrap v3.3.7 JavaScript
│   ├── html5shiv.min.js      <-- Only for Internet Explorer older than 9
│   ├── dashboard.js          <-- Sidebar toggle & menu accordion
│   ├── login.js              <-- Form validation rules for login.php
│   └── script.js             <-- Empty placeholder (login.php)
├── img/
│   ├── lgf-Logo.png          <-- Company logo
│   ├── login-back2.jpg       <-- Background image for login.php
│   └── login-side.jpg        <-- Right-side picture for login.php
├── sidebar.php               <-- Navigation sidebar component (included by dashboard.php)
├── dashboard.php             <-- Main dashboard page
├── login.php                 <-- User authentication page
└── logout.php                <-- Session termination script
```

**How the pages connect:**

1. User opens login.php and types user name + password (js/login.js checks the fields in the browser).
2. login.php checks the database (table usdmast), saves user data in the session, and redirects to dashboard.php.
3. dashboard.php shows the card and includes sidebar.php. js/dashboard.js makes the sidebar toggle and the menus open/close.
4. The red Logout button in the sidebar opens logout.php, which clears the session and redirects back to login.php.

---

## 2. sidebar.php - CSS Classes, JavaScript & Logic

### Role of `sidebar.php`

sidebar.php is a reusable PHP component. It does not make a full web page. It only prints the left navigation bar (logo, company name, menu, logout button). dashboard.php pulls it in with <?php include 'sidebar.php'; ?>, so the sidebar code is written once and can be reused on any page.

### Files it uses

| Type | File | Why it is needed |
|---|---|---|
| CSS | `css/dashboard.css` | Does ALL the styling of the sidebar (every class starting with sidebar-). It is loaded by dashboard.php, not by sidebar.php itself. |
| CSS | `css/bootstrap.min.css` | Gives the Glyphicon arrow icons (glyphicon, glyphicon-chevron-down, glyphicon-chevron-right). Also loaded by dashboard.php. |
| JS | `js/dashboard.js` | Makes the menu open/close and the sidebar hide/show. Loaded at the bottom of dashboard.php. |
| JS | `js/jquery-1.10.2.min.js` | jQuery. dashboard.js needs it, so it must be loaded BEFORE dashboard.js. |
| Image | `img/lgf-Logo.png` | Company logo shown at the top of the sidebar. |

### PHP logic

- Lines 14-16: $companyName - uses the company name from the session ($_SESSION['scomnam']). If it is empty, or still the default 'Ravi Breeder Farm', it shows 'Ramico International (Pvt) Ltd' instead.
- Line 17: $loggedUser - the user code from $_SESSION['susdusd']. If empty it shows '2000'.
- htmlspecialchars() is used when printing both values. It turns characters like < and > into safe text so nobody can inject HTML into the page.
- The Logout button is a normal link: <a href="logout.php">. It runs logout.php (see Section 5).

### CSS classes used

Every class below is used in sidebar.php. 'Source' tells you which CSS file contains the rule. Search for the class name with a dot in front (for example .sidebar-subtitle) to find it.

| CSS class | Source file | Used on | What it does |
|---|---|---|---|
| `sidebar` | `css/dashboard.css` | <aside> | The whole left panel. Width 270px, dark navy background (#0e1428), a thin right border. display:flex with flex-direction:column and justify-content:space-between: menu goes at the top and the footer (logout) is pushed to the bottom. It has a 0.28s transition on margin-left so hiding it looks smooth. |
| `collapsed` | `css/dashboard.css` | added by JS to <aside> | Written as .sidebar.collapsed. Sets margin-left:-270px, which slides the sidebar out of the screen. It is not in the PHP file: js/dashboard.js adds and removes it. |
| `sidebar-header` | `css/dashboard.css` | <div> at the top | Top block with padding and text-align:center, so logo, name and user are centered. |
| `sidebar-logo-wrap` | `css/dashboard.css` | <div> around the logo | Centers the logo and adds 14px space under it. |
| `sidebar-logo-img` | `css/dashboard.css` | <img> | Logo size 76x76px, round (border-radius:50%), light background, blue glow border, shadow. On hover it grows a little (transform:scale(1.05)). |
| `sidebar-title` | `css/dashboard.css` | <h1> | Company name. 17px, bold, white, centered. |
| `sidebar-subtitle` | `css/dashboard.css` | <p> under the title | The 'Logged in : 2000' line. 13px, regular weight, white at 65% opacity (soft grey), centered, no margin. Because it uses a faded white it looks less important than the title. |
| `sidebar-section-title` | `css/dashboard.css` | <div> above the menu | Small heading 'Dashboard Sidebar'. 12px, bold, UPPERCASE (text-transform), letter-spacing 1.2px, 50% white. |
| `sidebar-nav` | `css/dashboard.css` | <ul> | The main menu list. list-style:none removes bullets; margin 0; horizontal padding 14px. |
| `sidebar-nav-item` | `css/dashboard.css` | <li> | One top-level menu entry. Only adds 5px bottom margin. |
| `has-submenu` | `No CSS rule (marker class)` | <li> | No style at all. It is a label that tells JavaScript 'this item has a submenu'. dashboard.js selects .sidebar-nav-item.has-submenu to attach the click behaviour. |
| `active` | `css/dashboard.css` | <li> | Used as .sidebar-nav-item.active > .sidebar-nav-link. Gives the open menu a lighter navy background (#1a2444). JS adds/removes it when a submenu opens/closes. |
| `sidebar-nav-link` | `css/dashboard.css` | <a> | The clickable menu title. display:flex with justify-content:space-between puts the text on the left and the arrow on the right. Padding 10px 14px, rounded 6px, hover/focus background #151e3a. |
| `sidebar-chevron` | `css/dashboard.css` | <span> | The small arrow at the right. Font size 11px, 75% white. NOTE: it has a transition on 'transform' but nothing rotates it. The arrow direction changes because JS swaps the Glyphicon class (right <-> down). |
| `glyphicon` | `css/bootstrap.min.css` | <span> | Bootstrap base class. Required for every Bootstrap icon (sets the icon font). |
| `glyphicon-chevron-down` | `css/bootstrap.min.css` | <span> | Shows a 'v' arrow (menu is open). |
| `glyphicon-chevron-right` | `css/bootstrap.min.css` | <span> | Shows a '>' arrow (menu is closed). |
| `sidebar-submenu` | `css/dashboard.css` | <ul> inside <li> | The hidden/visible child list. No bullets, 18px left indent. The PHP file also has inline style="display:block" or "display:none" to set the starting state. jQuery slideToggle changes this inline style. |
| `sidebar-submenu-item` | `css/dashboard.css` | <li> | One child entry; 3px vertical margin. |
| `sidebar-submenu-link` | `css/dashboard.css` | <a> | Child link. 13px, 68% white. On hover/focus becomes full white with a soft blue background rgba(79,111,230,0.15). |
| `sidebar-footer` | `css/dashboard.css` | <div> at the bottom | Bottom area with padding and a top divider line (border-top). Stays at the bottom because .sidebar uses space-between. |
| `sidebar-logout-btn` | `css/dashboard.css` | <a href="logout.php"> | Red Logout button. Red gradient (#e53935 to #c62828), white bold text, rounded, shadow. Hover: lighter red + moves up 1px. Active (pressed): moves down 1px. !important is used on color/text-decoration to beat Bootstrap's link styles. |
| `sidebar-logout-icon` | `css/dashboard.css` | <svg> | Sets the exit icon to 16x16px. The drawing itself is inline SVG (path/polyline/line) using stroke="currentColor", so the icon takes the text colour (white). |

### JavaScript used

sidebar.php has no <script> tag. All behaviour comes from js/dashboard.js, loaded by dashboard.php. The script runs inside $(document).ready(...), which waits until the HTML is fully loaded.

#### Accordion menu (open/close submenu)

File: `js/dashboard.js`

```javascript
$(".sidebar-nav-item.has-submenu > .sidebar-nav-link").on("click", function (e) {
    e.preventDefault();
    var $parent  = $(this).closest(".sidebar-nav-item");
    var $submenu = $parent.find(".sidebar-submenu");
    var $chevron = $(this).find(".sidebar-chevron");
    $submenu.slideToggle(200, function () {
        if ($submenu.is(":visible")) {
            $parent.addClass("active");
            if ($chevron.hasClass("glyphicon-chevron-right")) {
                $chevron.removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
            }
        } else {
            $parent.removeClass("active");
            if ($chevron.hasClass("glyphicon-chevron-down") &&
                $parent.find(".sidebar-nav-link span:contains('Master')").length) {
                $chevron.removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
            }
        }
    });
});
```

How it works:

1. The selector '.sidebar-nav-item.has-submenu > .sidebar-nav-link' means: the <a class="sidebar-nav-link"> that is a direct child of an <li> having both classes.
2. e.preventDefault() stops the link from doing its normal action (the href is javascript:void(0) anyway).
3. closest() finds the parent <li>; find() finds the submenu <ul> and the arrow <span> inside it.
4. slideToggle(200) opens or closes the submenu with a 200 millisecond slide. When it finishes, the callback function runs.
5. If the submenu is now visible: add class 'active' to the <li> and make sure the arrow is 'down'.
6. If it is now hidden: remove 'active'. The arrow is turned back to 'right' ONLY if the menu text contains the word 'Master' (see Things to know).

#### Hide / show the whole sidebar

File: `js/dashboard.js`

```javascript
function toggleSidebar() {
    $(".sidebar").toggleClass("collapsed");
}
$("#sidebarToggleBtn").on("click", function (e) {
    e.preventDefault();
    toggleSidebar();
});
```

How it works:

1. The button with id sidebarToggleBtn is in dashboard.php (not here).
2. Each click toggles the class 'collapsed' on <aside class="sidebar">. CSS .sidebar.collapsed then moves it off-screen.

#### Auto-hide on phones

File: `js/dashboard.js`

```javascript
if ($(window).width() < 768) {
    $(".sidebar").addClass("collapsed");
}
```

How it works:

1. If the browser window is narrower than 768px when the page loads, the sidebar starts hidden. The user can open it with the toggle button.

### Things to know (bugs, quirks, tips)

- (2) Accounts Related has no 'has-submenu' class, so JavaScript ignores it. It is just a normal link (href="#").
- (15) Admin has 'has-submenu' but no submenu and no arrow. Clicking it runs the JS, which calls preventDefault and then finds nothing to slide, so nothing visible happens.
- The arrow bug: when 'Operations' is closed, its arrow stays 'down' because the code only switches back to 'right' when the menu text contains 'Master'. 'Master Files' works correctly; 'Operations' does not. If you want it fixed for every menu, remove the whole '&& $parent.find(...:contains(Master)).length' part of the condition.
- Comment in dashboard.css mentions 'TEA EXPORT SYSTEM' but the PHP prints 'Dashboard Sidebar'. The comment is just old; the text comes from the PHP file.
- All sub-links use href="#" - they are placeholders, no real pages yet.

---

## 3. dashboard.php - CSS Classes, JavaScript & Logic

### Role of `dashboard.php`

dashboard.php is the main page a user sees after a successful login. login.php redirects here with header('Location: dashboard.php'). It shows the sidebar (included from sidebar.php), a button to hide the sidebar, and one information card (User Name, Email, Password Expiry, Company News).

### Files it uses

| Type | File | Why it is needed |
|---|---|---|
| CSS | `css/bootstrap.min.css` | Bootstrap 3.3.7 base styles and Glyphicon icons. Loaded first in <head>. |
| CSS | `css/dashboard.css` | All custom dashboard and sidebar styling. Loaded after Bootstrap so it can override it. |
| PHP include | `sidebar.php` | Printed inside .dashboard-wrapper, to the left of the main content. |
| JS | `js/html5shiv.min.js` | Only for Internet Explorer older than 9 (inside <!--[if lt IE 9]>). Modern browsers ignore it. |
| JS | `js/jquery-1.10.2.min.js` | jQuery. Loaded at the end of <body>, first. |
| JS | `js/bootstrap.min.js` | Bootstrap's JavaScript. Needs jQuery, so it comes second. (This page does not currently use any Bootstrap component that needs it.) |
| JS | `js/dashboard.js` | Our own script. Must be last because it uses jQuery. |
| Image | `img/lgf-Logo.png` | Logo in the card header (the same file is used in the sidebar). |

### PHP logic

- Lines 26-28: session_start() only if the session is not already started.
- $companyName, $userName: same fallback rules as sidebar.php (default company name and user '2000').
- $userEmail comes from $_SESSION['dashboard_email'] (saved by login.php). If empty it falls back to a hard-coded sample address.
- $passwordExpiry comes from $_SESSION['dashboard_password_expiry'] (login date + 30 days, calculated in login.php). Fallback is the hard-coded date 2026-12-11.
- $companyNews comes from $_SESSION['company_news']; if empty it shows '-'.
- Every value is printed with <?= htmlspecialchars(...) ?> (short echo tag + safe escaping).
- The <title> also uses the company name: 'Dashboard - <company>'.

### CSS classes used

Classes used directly in dashboard.php. (The sidebar classes are explained in Section 2.) All rules are in css/dashboard.css.

| CSS class | Source file | Used on | What it does |
|---|---|---|---|
| `dashboard-wrapper` | `css/dashboard.css` | <div> around everything | Outer box. display:flex puts sidebar and main content side by side. min-height:100vh = at least the full screen height. overflow-x:hidden stops sideways scrolling while the sidebar slides. |
| `main-content` | `css/dashboard.css` | <main> | Right side area. flex:1 means 'take all remaining width'. Padding 24px 36px 40px 36px, navy background, vertical scroll if needed. It is also a flex column. |
| `toolbar-actions` | `css/dashboard.css` | <div> holding the button | Just adds 24px bottom margin under the toggle button. |
| `btn-toggle-sidebar` | `css/dashboard.css` | <button id="sidebarToggleBtn"> | Square 42x42px blue (#4f6fe6) button with rounded corners and a glow shadow. inline-flex centers the icon. Hover/focus: lighter blue and moves up 1px. Pressed: moves down 1px. The id is what JavaScript uses. |
| `toggle-icon` | `css/dashboard.css` | <svg> inside the button | Icon size 20x20px (the 3 lines 'hamburger' icon drawn with SVG <line> tags). |
| `dashboard-card` | `css/dashboard.css` | <div> | The big center card. Background #131a2e, border, 12px rounded corners, deep shadow, padding 38px 46px 44px. Width 100%. (Currently broken by a CSS typo - see Things to know.) |
| `card-header-area` | `css/dashboard.css` | <div> | Top part of the card: centered, with a divider line (border-bottom) under it. |
| `card-logo-badge` | `css/dashboard.css` | <div> | Round 52x52px badge holding the logo. overflow:hidden keeps the image inside the circle. The rule '.card-logo-badge img' sets the image to 32x32px. |
| `card-main-title` | `css/dashboard.css` | <h2> | Heading 'Service Provider Name'. 26px, bold, white. |
| `card-table-box` | `css/dashboard.css` | <div> | Rounded bordered box that holds the rows. overflow:hidden keeps the corners round. |
| `info-row` | `css/dashboard.css` | <div> (x4) | One row = one label + one value. display:flex puts them on one line. Bottom border between rows (the last row has none: .info-row:last-child). Hover changes the background. |
| `info-label` | `css/dashboard.css` | <div> | Left column text (User Name, Email ...). Fixed width 280px, bold (600), white. |
| `info-value` | `css/dashboard.css` | <div> | Right column value. flex:1 = fills the rest of the row. Regular weight, nearly white. |

**Extra:** @media (max-width: 768px) at the bottom of dashboard.css changes the layout on phones: less padding, the sidebar becomes position:fixed (floats over the content with a shadow), and each .info-row switches to a column (label above value).

### JavaScript used

dashboard.php has no inline JavaScript. It loads js/dashboard.js at the bottom of the page. The parts that matter for this page:

#### Toggle Sidebar button

File: `js/dashboard.js`

```javascript
function toggleSidebar() {
    $(".sidebar").toggleClass("collapsed");
}
$("#sidebarToggleBtn").on("click", function (e) {
    e.preventDefault();
    toggleSidebar();
});
```

How it works:

1. #sidebarToggleBtn (the blue button in this file) is clicked.
2. $('.sidebar').toggleClass('collapsed') adds the class if missing, removes it if present.
3. CSS .sidebar.collapsed{margin-left:-270px} then hides/shows the sidebar with the 0.28s animation.

#### Start hidden on small screens

File: `js/dashboard.js`

```javascript
if ($(window).width() < 768) {
    $(".sidebar").addClass("collapsed");
}
```

How it works:

1. Runs once when the page loads. Windows narrower than 768px start with the sidebar collapsed.

#### Submenu accordion

File: `js/dashboard.js`

```javascript
(same code as in Section 2)
```

How it works:

1. It works on the sidebar menu that dashboard.php includes. See Section 2 for the full explanation.

### Things to know (bugs, quirks, tips)

- CSS typo: in css/dashboard.css, right after the .toggle-icon rule there is a leftover line 'vertical-align: middle; }'. The browser reads it as the start of a broken rule that swallows the next selector (.dashboard-card), so the card styles are dropped. We tested it: background stays transparent and padding is 0. Fix: delete the stray 'vertical-align: middle; }' lines, or move 'vertical-align: middle;' inside the .toggle-icon braces.
- dashboard.php does not check $_SESSION['validated']. Anyone who types /dashboard.php in the browser can open it without logging in (it only shows default values). A simple guard is: if (empty($_SESSION['validated'])) { header('Location: login.php'); exit; }
- The fallback email and date are hard-coded sample values.

---

## 4. login.php - CSS Classes, JavaScript & Logic

### Role of `login.php`

login.php is the sign-in page. The top part is PHP (session, database connection, checking the user name and password). The bottom part is the HTML form. When the form is posted to the same page and everything is correct, the user is redirected to dashboard.php.

### Files it uses

| Type | File | Why it is needed |
|---|---|---|
| CSS | `css/bootstrap.min.css` | Bootstrap 3.3.7: grid (row, col-sm-6), form-control, btn, hidden, bg-danger, glyphicon, etc. |
| CSS | `css/custom.css` | The whole login page design (all classes starting with login- and brand-). Loaded with ?v=2 so browsers fetch a fresh copy after changes (cache busting). |
| CSS | `css/preloader.css` | Preloader style. The rule is empty; the file exists only to avoid a 404 error. |
| JS | `js/html5shiv.min.js` | Only for Internet Explorer older than 9. |
| JS | `js/jquery-1.10.2.min.js` | jQuery (loaded in <head>). |
| JS | `js/script.js` | Common script file. It only has a comment - it is empty. |
| JS | `js/jquery.validate.min.js` | jQuery Validation plugin. Adds the .validate() function used by login.js. |
| JS | `js/login.js` | Our validation rules. Loaded at the end of <body> so the form already exists. |
| Image | `img/login-back2.jpg` | Page background (set in css/custom.css .login-body). |
| Image | `img/login-side.jpg` | Right-side picture (set in css/custom.css .login-right). |

### PHP logic

- Lines 20-26: hide notices/warnings and start the session.
- Lines 47-166: create session variables with empty defaults, then set paths and database connection values (database, host, port, user, password).
- Line 144: connect to MySQL with mysqli.
- Line 170: if(isset($_POST['submit'])) - runs only when the SIGN IN button was pressed (the button has name="submit").
- Lines 175-203: read usdusd (user) and usdpwd (password); SELECT the user from table usdmast; if no row -> 'Invalid User Code'; if password differs -> 'Invalid Password'. Messages are stored in the $error array.
- Lines 213-283: if no error, copy user details from the database row into $_SESSION variables (susdusd, susdnam, susdadmin ...).
- Lines 287-315: password age check. Admin users and user '2000' never expire. Others expire after 30 days -> 'Password Expired; Please Contact Administrator'.
- Lines 317-324: if still valid: $_SESSION['validated']=true, session_regenerate_id(true) (new session ID for safety), and saves dashboard_email and dashboard_password_expiry for dashboard.php.
- Lines 327-336: Remember Me - saves user name and password into cookies 'rememberusd' and 'rememberpwd'.
- Lines 341-369: writes a LOGIN record to table logmast_login (except user 2000) and redirects: header('Location: dashboard.php'); exit;
- Lines 373-382: when the page is just opened (not posted), it fills the form from the cookies.
- Lines 391-396: on the local machine (127.0.0.1) the form is pre-filled with a test user and password.
- Inside the form: <?= $te_usdusd ?> and <?= $te_usdpwd ?> print the values into the inputs; the loop 'foreach($error as $error) echo $error;' prints the error messages above the fields.

### CSS classes used

Classes used in login.php. 'Bootstrap' classes come from css/bootstrap.min.css; 'login-...' classes are written by us in css/custom.css.

| CSS class | Source file | Used on | What it does |
|---|---|---|---|
| `login-body` | `css/custom.css` | <body> | Page background image (img/login-back2.jpg, cover, fixed) with a light blue fallback colour. display:flex + align-items/justify-content:center puts the card in the middle of the screen. min-height:100vh. |
| `preloader` | `css/preloader.css` | <div> | Empty rule - no visible effect. A leftover for a loading animation (the JS for it is commented out in login.php). |
| `login-card` | `css/custom.css` | <div> | The white card holding both halves. max-width 860px, soft blue shadow, overflow:hidden keeps the corners clean. |
| `row` | `css/bootstrap.min.css` | <div> | Bootstrap grid row (together with login-row). |
| `login-row` | `css/custom.css` | <div> | Makes the row display:flex, min-height 440px, margin 0. It also hides Bootstrap's :before/:after clearfix pieces (display:none) because flex does not need them. '.login-row > [class*=col-]' removes padding from the two columns. |
| `col-sm-6` | `css/bootstrap.min.css` | <div> x2 | Bootstrap grid: each half takes 50% of the width from 768px screens upward. On smaller screens they stack. |
| `login-left` | `css/custom.css` | <div> | Left half. Blue gradient background (#0f52b6 to #0d469c), white text, padding 42px (with !important to beat the column padding reset), flex column centered vertically. |
| `login-brand` | `css/custom.css` | <div> | Row with the badge and 'Login' title (display:flex, 36px space below). |
| `brand-badge` | `css/custom.css` | <span> | Circle (44x44px, white 2px border, border-radius 50%) with the text 'RBF' inside. No image is used - it is pure CSS. |
| `brand-title` | `css/custom.css` | <span> | The word 'Login'. 24px, normal weight, white. |
| `login-form` | `css/custom.css` | <form> | Parent class. Many rules use it as a prefix (.login-form .form-group, .login-form .form-control, .login-form .btn-login) so the styles apply ONLY inside this form. |
| `form-group` | `css/bootstrap.min.css (+ css/custom.css)` | <div> | Bootstrap wrapper for a field. custom.css makes the gap 22px and position:relative. |
| `form-control` | `css/bootstrap.min.css (+ css/custom.css)` | <input> | Bootstrap input style, changed by custom.css to the 'underline' style: transparent background, only a bottom border, white text, 44px height. On focus the bottom border becomes 2px solid white. Also styles the placeholder colour (three vendor-prefixed rules) and stops the yellow Chrome autofill colour (:-webkit-autofill). |
| `input-lg` | `css/bootstrap.min.css` | <input> | Bootstrap 'large' input size. |
| `hidden` | `css/bootstrap.min.css` | <small> | Bootstrap utility: display:none !important. The two <small class="hidden"> messages (User Name Required / Password Required) are always hidden and no JS uses them - they are unused leftovers. |
| `login-options` | `css/custom.css` | <div> | Row holding 'Remember Me' on the left and 'Forgot Password..?' on the right (display:flex; justify-content:space-between). |
| `login-remember` | `css/custom.css` | <label> | Remember Me text: 13px, white, pointer cursor, user-select:none so the text is not highlighted on double click. Its checkbox rule adds 7px right margin. |
| `login-forgot` | `css/custom.css` | <a> | Forgot Password link: 13px, 85% white, no underline; underline appears on hover. The link goes to usdforgot.php (that file is not in this project). |
| `btn` | `css/bootstrap.min.css` | <input type=submit> | Bootstrap base button class. |
| `btn-block` | `css/bootstrap.min.css` | <input type=submit> | Makes the button 100% wide. |
| `btn-lg` | `css/bootstrap.min.css` | <input type=submit> | Large button size. |
| `btn-login` | `css/custom.css` | <input type=submit> | Our look for SIGN IN: white background, blue text (#0f52b6), bold, UPPERCASE, letter-spacing 1.5px. Hover/focus: very light blue. Pressed: scale(0.99). |
| `bg-warning / bg-danger` | `css/bootstrap.min.css (+ css/custom.css)` | <p> printed by PHP | PHP builds these in the $error array: <p class='bg-warning'> for 'Invalid User Code' and <p class='bg-danger'> for 'Invalid Password' / 'Password Expired'. custom.css re-colours them: danger = pink background + dark red text; warning = pale yellow + orange text. |
| `glyphicon glyphicon-remove-circle` | `css/bootstrap.min.css` | <i> in error message | Bootstrap icon: a circle with an X, shown before the error text. |
| `red` | `Not defined anywhere` | <i> in error message | Class name with no CSS rule, so it does nothing. |
| `label.error` | `css/custom.css` | <label> created by JS | Not written in the PHP. The jQuery Validation plugin creates <label class="error"> after an invalid input. Styled by '#login .form-group label.error': light pink (#ffcdd2), 12px, display:block. |
| `login-right` | `css/custom.css` | <div> | Right half. Background picture img/login-side.jpg (centered at bottom, cover), white text, padding 48px 40px, position:relative. |
| `hidden-xs` | `css/bootstrap.min.css` | <div> | Bootstrap: hides this whole right half on phones (screens under 768px). Together with the media query at the end of custom.css the card becomes a single column. |
| `login-tip` | `css/custom.css` | <div> | Text block on the picture (max-width 320px, z-index 2 so it stays above the image). Its child rules: '.login-tip small' = small uppercase 'PRO TIP #1'; '.login-tip h2' = 30px heading; '.login-tip p' = 13px paragraph. |

### JavaScript used

login.php has a <script> block in <head>, but all its lines are commented out (old preloader code), so it does nothing. The real behaviour is in js/login.js, loaded at the bottom.

#### Form validation (js/login.js)

File: `js/login.js`

```javascript
$("#login").validate({
    rules: {
        usdusd: "required",
        usdpwd: { required: true, minlength: 1 },
        agree: "required"
    },
    messages: {
        usdusd: "Please enter User Code",
        usdpwd: {
            required: "Please provide a password",
            minlength: "Your password must be at least 1 characters long"
        }
    },
    submitHandler: function (form) { form.submit(); }
});
```

How it works:

1. The file wraps the code in a self-calling function (function($,W,D){...})(jQuery, window, document). It creates an object JQUERY4U with a method setupFormValidation, and calls it when the document is ready.
2. $('#login') selects the <form id="login">. .validate({...}) comes from js/jquery.validate.min.js.
3. rules: keys are the 'name' attributes of the inputs. usdusd (user name) is required; usdpwd (password) is required with minlength 1. 'agree' is listed but there is no such field, so it has no effect.
4. messages: the text shown under the field when a rule fails ('Please enter User Code', 'Please provide a password').
5. The plugin adds <label class="error"> after the invalid input - this is what the #login .form-group label.error rule colours.
6. submitHandler runs only when all fields are valid and calls form.submit(), which posts the form to login.php (PHP then checks the database).
7. This is only a browser-side check (to save a trip to the server). PHP still checks everything again.

#### script.js

File: `js/script.js`

```javascript
/* empty - only a comment */
```

How it works:

1. A placeholder for extra scripts. Nothing runs from it.

#### Autocomplete trick (no JS)

File: `login.php`

```html
<input type="text" id="fake_user" style="display:none">
<input type="password" id="fake_pass" style="display:none">
```

How it works:

1. Two hidden dummy inputs placed before the real fields so the browser fills the dummy fields instead of the real ones. The real username input also has autocomplete="off".

### Things to know (bugs, quirks, tips)

- Security: the SQL query puts $_POST['usdusd'] directly into the text ('... WHERE usdusd = '$te_usdusd''). This allows SQL injection. Use prepared statements (mysqli_prepare / bind_param).
- Passwords are compared as plain text and the Remember Me cookie stores the password itself. For real systems use password_hash() / password_verify() and never store passwords in cookies.
- The database user/password are written inside the file and the user is root. Move them to a separate config file outside the web folder.
- Pre-filling the test user for 127.0.0.1 is handy on your own PC; remove it before putting the site on a server.
- error_reporting(...) hides notices. While you are learning, remove it so you can see mistakes.
- Possible bug: line 346 uses date("Y-%m-%d") (extra % sign) for the login log record, so the saved date looks like 2026-%11-11. Use date('Y-m-d'). The other date() calls (lines 40 and 294) are correct.
- The pink error text from the validation plugin only appears when JavaScript runs; PHP errors appear in the .bg-warning / .bg-danger boxes.

---

## 5. logout.php - CSS Classes, JavaScript & Logic

### Role of `logout.php`

logout.php has no design. It prints nothing. It ends the user's session and sends the browser back to login.php. It is opened when the user clicks the red Logout button in the sidebar (sidebar.php: <a href="logout.php" class="sidebar-logout-btn">).

### Files it uses

| Type | File | Why it is needed |
|---|---|---|
| CSS | `None` | This file outputs no HTML, so no CSS is used. |
| JS | `None` | No JavaScript is used. Everything happens on the server and ends with a redirect. |

### PHP logic

- Lines 15-17: start the session if it is not started yet, so we can access it.
- Line 20: $_SESSION = array(); - empties all session variables (user name, validated flag, etc.).
- Lines 23-34: if sessions use cookies, send the session cookie again with an expiry time in the past (time() - 42000). The browser then deletes it. session_get_cookie_params() gives the same path/domain/secure/httponly values so the correct cookie is removed.
- Line 37: session_destroy() - deletes the session data stored on the server.
- Lines 40-41: header('Location: login.php'); exit; - redirect to the login page and stop the script. exit is important so nothing else runs.
- Order matters: header() and setcookie() must run before any output is sent (no echo or HTML before them).

### CSS classes used

None. logout.php does not output any HTML, so there are no CSS classes in it. The only styled thing connected to it is the button that links here:

| CSS class | Source file | Used on | What it does |
|---|---|---|---|
| `sidebar-logout-btn` | `css/dashboard.css` | <a> in sidebar.php | The red button that opens logout.php (explained in Section 2). |
| `sidebar-logout-icon` | `css/dashboard.css` | <svg> in sidebar.php | The 16x16 exit icon inside that button. |

### JavaScript used

None. There is no JavaScript. The redirect is done by PHP using header('Location: ...').

### Things to know (bugs, quirks, tips)

- Flow: sidebar Logout click -> logout.php (clears session) -> login.php (shows the form again).
- The remember-me cookies set by login.php (rememberusd, rememberpwd) are NOT removed by logout.php, so the login form will be filled again next time.
- Logout is a normal link (GET request). Anyone could trigger it by loading that URL; this is acceptable for a simple project.

---

## 6. CSS Files Reference

1. **`css/bootstrap.min.css`** - Bootstrap v3.3.7 (local). Grid (.row, .col-sm-6), utilities (.hidden, .hidden-xs), buttons (.btn, .btn-block, .btn-lg), forms (.form-group, .form-control, .input-lg), alerts (.bg-danger, .bg-warning) and Glyphicon icons. Used by login.php, dashboard.php and sidebar.php.
2. **`css/custom.css`** - Login page design only: wavy background, two-column card, underline inputs, RBF badge, white SIGN IN button, validation message colours. Used by login.php.
3. **`css/dashboard.css`** - Dashboard and sidebar design only: midnight navy theme (#0b1020), sidebar (#0e1428), card (#131a2e), blue toggle button (#4f6fe6), red logout button (#e53935). Used by dashboard.php and sidebar.php. Contains the stray-brace typo described in Section 3.
4. **`css/preloader.css`** - One empty rule (.preloader). Kept so the browser does not get a 404 error.

---

## 7. JavaScript Files Reference

1. **`js/jquery-1.10.2.min.js`** - jQuery. Gives $(...) selectors, .on('click'), .toggleClass(), .slideToggle(). Must load before every other script.
2. **`js/jquery.validate.min.js`** - jQuery Validation plugin. Provides $('form').validate(). Used by login.js.
3. **`js/bootstrap.min.js`** - Bootstrap JavaScript (needs jQuery). Loaded in dashboard.php but no Bootstrap component on the page needs it right now.
4. **`js/html5shiv.min.js`** - Lets old Internet Explorer (below 9) understand HTML5 tags such as <aside> and <main>. Loaded only inside <!--[if lt IE 9]>.
5. **`js/dashboard.js`** - Our dashboard script: (1) button #sidebarToggleBtn toggles .collapsed on .sidebar, (2) clicking a .has-submenu menu slides its submenu and swaps the arrow, (3) sidebar starts collapsed on screens narrower than 768px.
6. **`js/login.js`** - Our login script: validates usdusd and usdpwd with jQuery Validation, shows <label class="error"> messages, then submits the form.
7. **`js/script.js`** - Empty placeholder (only a comment).
