<?php
session_start();
if (empty($_SESSION['validated'])) {
    header('Location: login.php');
    exit;
}
$paymentPages = array(
    array('add', 'Add Payment Request', 'dpmenter_bs.php?cmdopt=add'),
    array('amend', 'Amend Payment Request', 'dpmenter_bs.php?cmdopt=ame'),
    array('approvals', 'MD Approvals', 'md_approvals.php'),
    array('print', 'Print', 'dpmprt_pp.php'),
    array('inquiry', 'Payment Inquiry', 'dp_inq02.php'),
    array('documents', 'All Documents', 'inhinqtrn.php'),
    array('list', 'Payment List', 'dplist_01.php')
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Direct Payments ? Add Payment Request</title>
    <link rel="stylesheet" href="css/bootstrap5.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/payments.css">
</head>
<body>
<div class="dashboard-wrapper">
    <?php include dirname(__FILE__) . '/sidebar.php'; ?>
    <main class="main-content payment-main">
        <div class="toolbar-actions payment-toolbar">
            <button type="button" class="btn-toggle-sidebar" id="sidebarToggleBtn" title="Toggle Sidebar" aria-label="Toggle Sidebar" aria-controls="sidebar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <path d="M3 6h18M3 12h18M3 18h18"></path>
                </svg>
            </button>
            <div class="payment-heading">
                <p>Operations / Direct Payments</p>
                <h1 id="paymentTitle" aria-live="polite">Add Payment Request</h1>
            </div>
            <a class="payment-home" href="dashboard.php">Dashboard</a>
        </div>
        <section class="payment-workspace" aria-label="Direct payments workspace">
            <nav class="payment-tabs" aria-label="Payment sections">
                <?php foreach ($paymentPages as $index => $page) { ?>
                <a class="payment-tab<?php echo $index === 0 ? ' active' : ''; ?>"
                   href="<?php echo htmlspecialchars($page[2], ENT_QUOTES, 'UTF-8'); ?>"
                   target="paymentFrame" data-section="<?php echo $page[0]; ?>"
                   <?php echo $index === 0 ? 'aria-current="page"' : ''; ?>><?php echo $page[1]; ?></a>
                <?php } ?>
            </nav>
            <div class="payment-frame-wrap">
                <p id="paymentLoading" class="payment-loading" role="status" hidden>Loading payment page?</p>
                <iframe id="paymentFrame" data-theme-version="<?php echo filemtime(dirname(__FILE__) . '/css/payment-content.css'); ?>" name="paymentFrame" title="Add Payment Request" src="dpmenter_bs.php?cmdopt=add"></iframe>
            </div>
        </section>
    </main>
</div>
<script src="jquery/jquery-1.10.2.min.js"></script>
<script src="js/dashboard.js"></script>
<script src="js/ent_dashboard_dpmm.js?v=<?php echo filemtime(dirname(__FILE__) . '/js/ent_dashboard_dpmm.js'); ?>"></script>
</body>
</html>
