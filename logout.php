<?php
/* ==============================================================================
   FILE: logout.php
   PURPOSE: Clears the current user session and redirects back to login.php.

   FLOW & LOGIC NOTE:
   - Starts or resumes the current session.
   - Resets all session data in $_SESSION.
   - Clears the session cookie in the client's browser.
   - Destroys the session data stored on the server.
   - Redirects cleanly to login.php.
   ============================================================================== */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// If session cookie exists, destroy it by setting an expiry date in the past
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session data on server
session_destroy();

// Redirect back to the login page
header("Location: login.php");
exit;

