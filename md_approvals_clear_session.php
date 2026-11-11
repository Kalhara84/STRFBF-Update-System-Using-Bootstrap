<?php
require_once dirname(__FILE__) . '/md_approvals_bootstrap.php';

// Clear the saved approval IDs from session
unset($_SESSION['saved_approval_ids']);
echo "Session cleared";
?>
