<?php
require_once dirname(__FILE__) . '/md_approvals_bootstrap.php';



$te_usdusd=$_SESSION["susdusd"];
//echo "$te_usdusd";

$te_enttime = strftime('%H:%M:%S');
$te_entddt  = strftime ("%Y-%m-%d");
$te_ucdnew  = $te_usdusd;


if($te_usdusd!='ravi' and $te_usdusd!='2000')
{
 header('HTTP/1.1 403 Forbidden');
 exit('You do not have permission to save approvals.');
}

if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
    echo "No items selected";
    exit;
}

$ids = $_POST['ids'];
$success_count = 0;

// Initialize session array if not exists
if (!isset($_SESSION['saved_approval_ids'])) {
    $_SESSION['saved_approval_ids'] = array();
}

foreach ($ids as $val) {
    // Split the ID and Type (e.g., "123##purchase")
    $parts = explode("##", $val);
    $id = mysqli_real_escape_string($conn, $parts[0]);
    $type = $parts[1];

    $sql = "";
    if ($type == 'purchase' || $type == 'raw' || $type == 'vaccine' || $type == 'chemicals' || $type == 'chickboxes' || $type == 'service' || $type == 'fuel'   ) {

        $sql_inl = "select inlinhsno from inltran where inlsno='$id'";
        $result_inl = mysqli_query($conn,$sql_inl) or die("SQL Error :".mysqli_error($conn));
        $fields_inl    = mysqli_fetch_array($result_inl);
        $te_inlinhsno  = $fields_inl['inlinhsno'];

        $sql_  = "update inhtran set inhappyn='y',inhappucd='$te_ucdnew',inhappddt='$te_entddt',inhapptime='$te_enttime'
                         where inhsno='$te_inlinhsno'";
        $result_    = mysqli_query($conn,$sql_) or die("Could not update inh");

        $sql = "UPDATE inltran SET inlappyn='y',inlappucd='$te_ucdnew',inlappddt='$te_entddt',inlapptime='$te_enttime' WHERE inlsno='$id'";

    }
    elseif ($type == 'chq') {

        $sql = "UPDATE g_grlmast SET grhappyn='y' WHERE grhsno='$id'";

    }
    elseif ($type == 'estm' || $type == 'serv') {

        $sql_  = "update inhtran set inhappyn='y',inhappucd='$te_ucdnew',inhappddt='$te_entddt',inhapptime='$te_enttime'
                         where inhsno='$id'";
        $result_    = mysqli_query($conn,$sql_) or die("Could not update inh");


        $sql = "UPDATE inltran SET inlappyn='y',inlappucd='$te_ucdnew',inlappddt='$te_entddt',inlapptime='$te_enttime' WHERE inlinhsno='$id'";
        //echo "$sql<br>";
    }
    elseif ($type == 'dirpay') {

        $sql_  = "update inhtran set inhappyn='y',inhappucd='$te_ucdnew',inhappddt='$te_entddt',inhapptime='$te_enttime'
                         where inhsno='$id'";
        $result_    = mysqli_query($conn,$sql_) or die("Could not update inh");


        $sql = "UPDATE inltran SET inlappyn='y',inlappucd='$te_ucdnew',inlappddt='$te_entddt',inlapptime='$te_enttime' WHERE inlinhsno='$id'";
    }

    if ($sql != "" && mysqli_query($conn, $sql)) {
        $success_count++;
        // Add to session so it stays visible after reload
        if (!in_array($val, $_SESSION['saved_approval_ids'])) {
            $_SESSION['saved_approval_ids'][] = $val;
        }
    }
}

echo "$success_count items approved successfully.";
?>
