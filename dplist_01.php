<?php 
$config_use_sessions = TRUE;
session_start();
   
if (!$_SESSION["validated"]) 
{ 
    header("Location: login.php");
    return;
}

$o = new HtmlFormsObjects;   

require_once dirname(__FILE__) . '/db.php';

// --- AJAX HANDLER FOR MODAL DATA ---
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'get_lines') {
    ob_clean();
    $inhsno = $_POST['inhsno'];
    
    $sql_lines = "SELECT inltran.inllno, g_genmast.gendes, inltran.inldes, inltran.inlamt 
                  FROM inltran 
                  LEFT JOIN g_genmast ON inltran.inlgensno = g_genmast.gensno 
                  WHERE inlinhsno='$inhsno' 
                  ORDER BY inllno ASC";
    $res_lines = mysqli_query(db_connection(), $sql_lines);
    
    $html = '';
    $total_amt = 0;
    
    if(mysqli_num_rows($res_lines) > 0) {
        while($row = mysqli_fetch_assoc($res_lines)) {
            $total_amt += $row['inlamt'];
            $disp_gendes = ($row['gendes'] != '') ? $row['gendes'] : '-';
            $disp_inldes = ($row['inldes'] != '') ? $row['inldes'] : '-';
            
            $html .= "<tr>";
            $html .= "<td class='text-center text-dark'>{$row['inllno']}</td>";
            $html .= "<td class='text-dark'>{$disp_gendes}</td>";
            $html .= "<td class='text-dark'>{$disp_inldes}</td>";
            $html .= "<td class='text-end fw-bold text-dark'>" . number_format($row['inlamt'], 2) . "</td>";
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='4' class='text-center py-3 text-dark'>No line items found.</td></tr>";
    }
    
    // Return HTML rows and the total amount separated by a delimiter
    echo $html . "|||" . number_format($total_amt, 2);
    exit;
}
// ------------------------------------

$h_typ = '30';

// Initialize Filter Variables
$f_locsno    = isset($_POST['f_locsno']) ? $_POST['f_locsno'] : '';
$f_dptyp     = isset($_POST['f_dptyp']) ? $_POST['f_dptyp'] : '';
$f_from_date = isset($_POST['f_from_date']) ? $_POST['f_from_date'] : '';
$f_to_date   = isset($_POST['f_to_date']) ? $_POST['f_to_date'] : '';

// Handle Search Execution
$result_list = false; // Default to false meaning "no search executed yet"
$record_count = 0;

if(isset($_POST['search'])) {
    $sql_list = "SELECT inhsno, inhdno, inhddt, inhsupnam, gen.gendes as bankacc, inhrem, inhval 
                 FROM inhtran
                 LEFT JOIN g_genmast gen ON inhgensno = gen.gensno
                 WHERE inhtyp='$h_typ' AND (inhcanucd='' OR inhcanucd IS NULL)";

    if ($f_locsno != '') {
        $sql_list .= " AND inhlocsno='$f_locsno'";
    }
    if ($f_dptyp != '') {
        $sql_list .= " AND inhdptypsno='$f_dptyp'";
    }
    if ($f_from_date != '') {
        $sql_list .= " AND inhddt >= '" . dtoy($f_from_date) . "'";
    }
    if ($f_to_date != '') {
        $sql_list .= " AND inhddt <= '" . dtoy($f_to_date) . "'";
    }

    $sql_list .= " ORDER BY inhsno DESC";
    $result_list = mysqli_query(db_connection(), $sql_list) or die("Query Error: " . mysqli_error(db_connection()));
    $record_count = mysqli_num_rows($result_list);
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Direct Payment List</title>
<link href="css/bootstrap5.min.css" rel="stylesheet">
<link href="css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="css/select2-bootstrap-5-theme.min.css" />

<script src="jquery/jquery-1.11.3.min.js"></script>
<script src="js/bootstrap5.bundle.min.js"></script>
<script src="js/select2.min.js"></script>
<script language="JavaScript" src="js/calendar2.js"></script>

<style>
    /* Full height layout without main window scrolling, Strict Black/White/Blue Theme */
    body {
        height: 100vh;
        overflow: hidden;
        background-color: #ffffff;
        color: #000000;
        font-size: 0.85rem;
    }
    .wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 8px 15px;
    }
    .flex-shrink-0 { flex-shrink: 0; }
    .flex-grow-1 { flex-grow: 1; min-height: 0; }
    
    /* Condensed Form Elements */
    .form-label { font-size: 0.75rem; margin-bottom: 0.1rem; color: #000000; }
    .select2-container .select2-selection--single { height: 30px !important; border-color: #000000; }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 28px !important; font-size: 0.8rem; padding-top: 0; color: #000000;}
    .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.8rem; height: 30px;}
    .input-group-sm > .form-control, .input-group-sm > .btn { height: 30px; font-size: 0.8rem; border-color: #000000; color: #000000;}
    
    /* Table Styling */
    .table-compact th, .table-compact td { padding: 0.3rem 0.5rem; font-size: 0.8rem; vertical-align: middle; border-bottom: 1px solid #000000; }
    .table-header-sticky { position: sticky; top: 0; z-index: 1; background-color: #ffffff; border-bottom: 2px solid #000000; }
    
    /* Modal Tweaks */
    .modal-content { border: 2px solid #000000; border-radius: 0; }
    .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }
</style>

<script>
function loadModalData(inhsno, inhdno) {
    $('#modalDocNo').text(inhdno);
    $('#modalTableBody').html("<tr><td colspan='4' class='text-center py-3 fw-bold text-primary'>Loading...</td></tr>");
    $('#modalTotalVal').text("0.00");
    
    $.post('dplist_01.php', { ajax_action: 'get_lines', inhsno: inhsno }, function(response) {
        var parts = response.split('|||');
        var tableHtml = parts[0];
        var totalVal = parts[1];
        
        $('#modalTableBody').html(tableHtml);
        $('#modalTotalVal').text(totalVal);
    });
}
</script>
</head>
<body>
<div class="wrapper">
    <h6 class="text-primary border-bottom border-dark pb-1 mb-2 flex-shrink-0"><b>Direct Payment Master List</b></h6>

    <form name="fn" method="post" action="dplist_01.php" class="bg-white p-2 mb-2 border border-dark flex-shrink-0">
        <div class="row gx-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Location:</label>
                <?php
                $sqlquery = "select locsno, concat(locloc,' - ',locnam) locloc from locmast order by locloc";                            
                $result = mysqli_query(db_connection(), $sqlquery);
                $o->GenerateDropDownList("f_locsno", "$f_locsno", "locsno", "locloc");
                ?>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Payment Type:</label>
                <?php
                $sqlquery = "select syssno, sysrno from sysmast where sysrtp=36 order by sysrno";                            
                $result = mysqli_query(db_connection(), $sqlquery);
                $o->GenerateDropDownList("f_dptyp", "$f_dptyp", "syssno", "sysrno");
                ?>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">From Date:</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="f_from_date" id="f_from_date" value="<?php echo $f_from_date; ?>" autocomplete="off">
                    <button class="btn btn-outline-dark" type="button" onclick="cal1.popup();"><img src="img/cal.gif" width="14" height="14"></button>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">To Date:</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="f_to_date" id="f_to_date" value="<?php echo $f_to_date; ?>" autocomplete="off">
                    <button class="btn btn-outline-dark" type="button" onclick="cal2.popup();"><img src="img/cal.gif" width="14" height="14"></button>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" name="search" class="btn btn-primary btn-sm w-100 fw-bold border-dark">SEARCH</button>
            </div>
        </div>
    </form>

    <div class="border border-dark flex-grow-1 d-flex flex-column bg-white" style="overflow: hidden;">
        <div class="bg-dark text-white fw-bold py-1 px-2 flex-shrink-0" style="font-size: 0.75rem;">
            Direct Payment Records
        </div>
        <div class="flex-grow-1" style="overflow-y: auto;">
            <table class="table table-hover mb-0 table-compact">
                <thead class="table-header-sticky text-dark">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">Direct Pay ID</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 20%;">Pay To</th>
                        <th style="width: 15%;">Bank Account</th>
                        <th style="width: 20%;">Remark</th>
                        <th class="text-end" style="width: 10%;">Total Amt</th>
                        <th class="text-center" style="width: 8%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if search was executed
                    if($result_list !== false) {
                        if($record_count > 0) {
                            $counter = 1;
                            while($row = mysqli_fetch_assoc($result_list)) {
                                $disp_date = ytod($row['inhddt']);
                                $disp_sup  = ($row['inhsupnam'] != '') ? $row['inhsupnam'] : '-';
                                $disp_bank = ($row['bankacc'] != '') ? $row['bankacc'] : '-';
                                $disp_rem  = ($row['inhrem'] != '') ? $row['inhrem'] : '-';
                                $disp_val  = number_format($row['inhval'], 2);
                                
                                echo "<tr>";
                                echo "<td class='text-dark'>{$counter}</td>";
                                echo "<td class='fw-bold text-primary'>{$row['inhdno']}</td>";
                                echo "<td class='text-dark'>{$disp_date}</td>";
                                echo "<td class='text-dark'>{$disp_sup}</td>";
                                echo "<td class='text-dark'>{$disp_bank}</td>";
                                echo "<td class='text-dark'><div style='max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;' title='{$disp_rem}'>{$disp_rem}</div></td>";
                                echo "<td class='text-end fw-bold text-dark'>{$disp_val}</td>";
                                echo "<td class='text-center'>
                                        <button type='button' class='btn btn-sm btn-outline-primary py-0 px-2 fw-bold border-dark' style='height:24px; font-size:0.7rem;' 
                                            data-bs-toggle='modal' data-bs-target='#lineItemModal' 
                                            onclick=\"loadModalData('{$row['inhsno']}', '{$row['inhdno']}')\">
                                            More
                                        </button>
                                      </td>";
                                echo "</tr>";
                                $counter++;
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center fw-bold text-dark py-4'>No records found matching your criteria.</td></tr>";
                        }
                    } else {
                        // Initial State
                        echo "<tr><td colspan='8' class='text-center  py-4'>Press SEARCH to load records.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="bg-white border-top border-dark p-1 text-end flex-shrink-0 text-dark fw-bold" style="font-size: 0.75rem;">
            Showing <?php echo $record_count; ?> records
        </div>
    </div>
</div>

<div class="modal fade" id="lineItemModal" tabindex="-1" aria-labelledby="lineItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white py-2 border-bottom border-dark">
        <h6 class="modal-title fw-bold" id="lineItemModalLabel">Line Items for: <span id="modalDocNo" class="text-white"></span></h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 bg-white">
         <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
             <table class="table table-hover align-middle mb-0 table-compact">   
                 <thead class="bg-white text-dark" style="position: sticky; top: 0; z-index: 1; border-bottom: 2px solid #000000;">
                     <tr>
                         <th class="text-center" style="width: 5%;">LNO</th>
                         <th style="width: 25%;">Account Code</th>
                         <th style="width: 50%;">Description</th>
                         <th class="text-end" style="width: 20%;">Amount</th>
                     </tr>
                 </thead>    
                 <tbody id="modalTableBody">
                     </tbody>
                 <tfoot class="bg-white text-dark border-top border-dark" style="position: sticky; bottom: 0; z-index: 1;">
                     <tr>
                         <th colspan="3" class="text-end fw-bold py-2">Total Value:</th>
                         <th class="text-end fw-bold text-dark py-2 fs-6" id="modalTotalVal">0.00</th>
                     </tr>
                 </tfoot>
             </table>
         </div>
      </div>
      <div class="modal-footer py-1 bg-white border-top border-dark">
        <button type="button" class="btn btn-dark btn-sm fw-bold border-dark" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Initialize calendars
var cal1 = new calendar2(document.fn.f_from_date); cal1.year_scroll = true; cal1.time_comp = false;
var cal2 = new calendar2(document.fn.f_to_date); cal2.year_scroll = true; cal2.time_comp = false;

$(document).ready(function() {
    $('#f_locsno, #f_dptyp').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        placeholder: "-- Select --"
    });
});
</script>

<?php
Class HtmlFormsObjects
{
  function GenerateDropDownList($HtmlObjectName,$HtmlObjectDefaultValue,$DBFieldToValue,$DBFieldToDescription)
  {
    global $result;      
    echo "<select class='form-select form-select-sm border-dark text-dark' id='$HtmlObjectName' name='$HtmlObjectName'> \n";
    echo"<option value=''></option>";
    if($result && mysqli_num_rows($result) > 0) {
        mysqli_data_seek($result,0);
        while ($rec = mysqli_fetch_array($result))
        {
         if ($HtmlObjectDefaultValue == $rec["$DBFieldToValue"])
          echo "<option selected value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
         else
          echo "<option value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
        }
    }
    echo "</select> \n";    
  }      
}

function ytod($ddt)
{
 if ($ddt=="" || $ddt=="0000-00-00" || $ddt == null) return "";
 else {
     $year = substr($ddt, 0, 4); $month = substr($ddt, 5, 2); $day = substr($ddt, 8, 2);
     return  "$day/$month/$year";
 }
}

function dtoy($ddt)
{
 if ($ddt) {
     $vl=explode('/',$ddt); 
     if(count($vl) == 3) {
         $day = $vl[0]; $month = $vl[1]; $year = $vl[2];
         return "$year-$month-$day";
     }
 }
 return ""; 
}
?>
</body>
</html>