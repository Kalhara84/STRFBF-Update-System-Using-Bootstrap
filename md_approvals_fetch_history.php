<?php
require_once dirname(__FILE__) . '/md_approvals_bootstrap.php';


if(!isset($_POST['id'])) {
    die("<div class='alert alert-danger'>No item ID provided</div>");
}

if(!isset($_POST['modopt'])) {
    die("<div class='alert alert-danger'>No mode specified</div>");
}

$te_id = intval($_POST['id']);
$modopt = $_POST['modopt'];

if($te_id <= 0) {
    die("<div class='alert alert-danger'>Invalid ID</div>");
}

// Purchase Order History
if($modopt == 'poh') {
    $sql = "SELECT inhddt,supnam,stkdes,inlcpr,inlqty FROM inltran
               left join inhtran on inlinhsno=inhsno
               left join supmast on inhsupsno=supsno
               left join stkmast on inlstksno=stksno
               WHERE inhtyp='21' and inlstksno='$te_id'
               order by inhddt desc limit 5";

    $res = mysqli_query($conn, $sql);

    if(!$res) {
        die("<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>");
    }

    if(mysqli_num_rows($res) == 0) {
        die("<div class='alert alert-info'>No purchase history found for this item</div>");
    }

    echo "<table class='table table-striped'>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Supplier</th>";
    echo "<th>Item</th>";
    echo "<th>Qty</th>";
    echo "<th>Price</th>";
    echo "</tr>";

    while($r = mysqli_fetch_assoc($res)){
        echo "<tr>";
        echo "<td>{$r['inhddt']}</td>";
        echo "<td>{$r['supnam']}</td>";
        echo "<td>{$r['stkdes']}</td>";
        echo "<td>{$r['inlqty']}</td>";
        echo "<td>{$r['inlcpr']}</td>";
        echo "</tr>";
    }

    echo "</table>";
    mysqli_free_result($res);
}
elseif($modopt == 'dph') {
    $sql = "SELECT inhddt,inldes, gendes, inlval
            FROM inltran
            left join inhtran on inlinhsno=inhsno
            LEFT JOIN g_genmast ON inlgensno=gensno
            WHERE inhtyp='30' and inlinhsno='$te_id'
            order by inhddt desc limit 5";

    $res = mysqli_query($conn, $sql);

    // print_r($sql);

    if(!$res) {
        die("<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>");
    }

    if(mysqli_num_rows($res) == 0) {
        die("<div class='alert alert-info'>No direct payment history found for this item</div>");
    }

    echo "<table class='table table-striped'>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Remarks</th>";
    echo "<th>Account</th>";
    echo "<th>Value</th>";
    echo "</tr>";

    while($r = mysqli_fetch_assoc($res)){
        echo "<tr>";
        echo "<td>{$r['inhddt']}</td>";
        echo "<td>{$r['inldes']}</td>";
        echo "<td>{$r['gendes']}</td>";
        echo "<td>{$r['inlval']}</td>";
        echo "</tr>";
    }

    echo "</table>";
    mysqli_free_result($res);

}
else {
    die("<div class='alert alert-danger'>Unknown mode: " . htmlspecialchars($modopt) . "</div>");
}
