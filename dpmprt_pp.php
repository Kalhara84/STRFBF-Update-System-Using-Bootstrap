<?php
$config_use_sessions = TRUE;
session_start();
   
if (!$_SESSION["validated"]) 
{ 
    header("Location: login.php");
    return;
}

$te_cmdopt = 'inq'; 
     
$o = new HtmlFormsObjects;   
$te_sDatabase   = $_SESSION["sDatabase"];
$te_sHostname   = $_SESSION["sHostname"];
$te_sPort       = $_SESSION["sPort"];
$te_sUsername   = $_SESSION["sUsername"];
$te_sPassword   = $_SESSION["sPassword"];

$rConn = mysql_connect("$te_sHostname:$te_sPort", $te_sUsername, $te_sPassword)
         or die("Could not connect : ".mysql_error());
$rDB   = mysql_select_db($te_sDatabase,$rConn) or die("Could not select database");

// --- AJAX HANDLER ---
if (isset($_POST['ajax_action'])) {
    ob_clean();
    $action = $_POST['ajax_action'];
    $args = isset($_POST['args']) ? $_POST['args'] : array();
    $allowed_functions = array('next_dno');
    if (in_array($action, $allowed_functions) && function_exists($action)) {
        $result = call_user_func_array($action, $args);
        echo $result;
    }
    exit;
}

if(isset($_POST['print'])) 
{
    $te_inhdno = mysql_real_escape_string($_POST['inhdno']);

    ob_start(); 
    error_reporting(0);
    
    $te_ = $_SERVER['DOCUMENT_ROOT'].'tfpdf/fpdf_fpdi/font/';
    if(!defined('FPDF_FONTPATH')) { define('FPDF_FONTPATH', $te_); }
    
    require_once $_SERVER['DOCUMENT_ROOT'].'tfpdf/tfpdf.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'tfpdf/fpdf_fpdi/fpdi.php';
    
    $pdf = new FPDI('L', 'mm', 'A5');
    $pdf->AddPage();
    
    $pages = $pdf->setSourceFile('dpinv.pdf'); 
    $page = $pdf->ImportPage(1);
    $pdf->useTemplate($page, 0, 0);

    $query_pr = "SELECT inhsno, inhddt, inhrem, inhsupnam,
                        inhcoqsno, coq.sysdes as coqdes, inhval,
                        inhcashamt, inhchqamt, inhgensno, gen.gendes as gendesauto, 
                        inhchqno, inhchqddt, inhourref, inhbank, inhbranch,
                        inhlocsno, loc.locloc, loc.locnam,
                        inhdptypsno, ptyp.sysrno as paytype
                 FROM inhtran            
                 LEFT JOIN sysmast coq ON inhcoqsno=coq.syssno 
                 LEFT JOIN g_genmast gen ON inhgensno=gen.gensno
                 LEFT JOIN locmast loc ON inhlocsno=loc.locsno
                 LEFT JOIN sysmast ptyp ON inhdptypsno=ptyp.syssno
                 WHERE inhdno='$te_inhdno' AND inhtyp='30'";
                 
    $result_pr = mysql_query($query_pr) or die("No header records..!".mysql_error());
    
    if(mysql_num_rows($result_pr)) {
        $fields_pr = mysql_fetch_array($result_pr);
        
        $pdf->SetFont('Helvetica', '', 10);
        
        // --- Render Header Information ---
        
        $pdf->Text(40, 45, $te_inhdno); // Payment No
        $pdf->Text(155, 38, ytod($fields_pr['inhddt'])); // Date
        
        $pdf->Text(150, 45, $fields_pr['locnam']); // Location
        $pdf->Text(150, 52, $fields_pr['paytype'] .' - '. $fields_pr['coqdes']); // Pay Type
        
        $pdf->Text(40, 52, $fields_pr['inhsupnam']); // Pay To (Supplier Name)
        $pdf->Text(150, 75, $fields_pr['inhourref']); // RBF Inv No.
        
        $pdf->Text(40, 59, $fields_pr['inhbank']); // Bank
        $pdf->Text(150, 59, $fields_pr['inhchqno']); // Chq No
        
        $pdf->Text(40, 66, $fields_pr['inhbranch']); // Branch
        $pdf->Text(150, 66, ytod($fields_pr['inhchqddt'])); // Chq Date
        
        $pdf->SetFont('Helvetica', '', 9);
        // Account No
        $pdf->SetXY(40, 70);
        $pdf->MultiCell(70, 3, $fields_pr['gendesauto'], 0, 'L', false);
        
        $pdf->SetFont('Helvetica', '', 10);
        // Remarks
        $pdf->SetXY(30, 78);
        $pdf->MultiCell(140, 5, $fields_pr['inhrem'], 0, 'L', false);
        
        
        // --- Fetch and Render Line Items ---
        $sql_lines = "SELECT inltran.inllno, g_genmast.gendes, inltran.inldes, inltran.inlamt 
                      FROM inltran 
                      LEFT JOIN g_genmast ON inltran.inlgensno = g_genmast.gensno 
                      WHERE inlinhsno='{$fields_pr['inhsno']}' 
                      ORDER BY inllno ASC";
        $res_lines = mysql_query($sql_lines);
        
        $pdf->SetFont('Helvetica', '', 8);
        
        $ly = 100; 
        $row_height = 6;
        $tot_val = 0;
        
        while($row = mysql_fetch_array($res_lines)) {
            $acc_code = substr($row['gendes'], 0, 35);
            $desc = substr($row['inldes'], 0, 60);
            
            $pdf->SetXY(10, $ly);
            $pdf->MultiCell(80, 3, $acc_code, 0, 'L', false);

            $pdf->SetXY(75, $ly);
            $pdf->MultiCell(80, 3, $desc, 0, 'L', false);
            
            $pdf->SetXY(160, $ly - 3.5);
            $pdf->Cell(35, $row_height, number_format($row['inlamt'], 2), 0, 0, 'R');
            
            $tot_val += $row['inlamt'];
            $ly += $row_height; 
        }
        
        // --- Render Totals Footer ---
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(160, 122); 
        $pdf->Cell(35, 6, number_format($tot_val, 2), 0, 0, 'R');
        
        // Output PDF to browser
        $pdf->Output('DIRECT_PAYMENT_' . $te_inhdno . '.pdf', 'I');
        ob_end_flush(); 
        exit;
    } else {
        die("Document data not found for printing.");
    }
}
// ------------------------------------

$h_typ = '30';
$val_key = "n";  

$te_inhlocsno = '';
$te_inhlocsnoauto = '';
$te_inhdptypsno = '';
$te_inhdptypsnoauto = '';
$te_inhdno = '';
$te_inhsno = '';

$te_inhddt = '';
$te_inhrem = '';
$te_inhsupnam = '';
$te_inhcoqsnoauto = '';
$te_inhval = '0.00';
$te_inhgensnoauto = '';
$te_inhchqno = '';
$te_inhchqddt = '';
$te_inhourref = '';
$te_inhbank = '';
$te_inhbranch = '';
$te_entddt = '';
$te_enttime = '';
$te_ucdnew = '';

if(isset($_POST['ok']))
{
    $te_inhlocsno     = $_POST['inhlocsno'];
    $te_inhlocsnoauto = $_POST['inhlocsnoauto'];   
    $te_inhdno        = $_POST['inhdno'];   
    $te_inhdptypsno     = $_POST['inhdptypsno'];
    $te_inhdptypsnoauto = $_POST['inhdptypsnoauto'];

    $sql_ok = "SELECT inhsno, inhddt, inhrem, locloc, locnam as inhdlvtosnoauto, inhlocsno, inhsupnam,
                      inhcoqsno, coq.sysdes coqdes, inhval, inhdlvtosno,
                      inhcashamt, inhchqamt, inhgensno, gen.gendes as gendesauto, 
                      inhchqno, inhchqddt, inhourref, inhbank, inhbranch, inhentddt, inhenttime, inhucdnew
               FROM inhtran            
               LEFT JOIN locmast ON inhlocsno=locsno
               LEFT JOIN sysmast coq ON inhcoqsno=coq.syssno 
               LEFT JOIN g_genmast gen ON inhgensno=gen.gensno
               WHERE inhdno='$te_inhdno' AND inhtyp='$h_typ'";
               
    if ($te_inhlocsno != '') {
        $sql_ok .= " AND inhlocsno='$te_inhlocsno'";
    }

    $result_ok = mysql_query($sql_ok) or die("sql error inh ok:<br>".mysql_error());
    
    if(mysql_num_rows($result_ok)) 
    {
        $val_key = "y";
        $rec = mysql_fetch_array($result_ok);     
        
        $te_inhsno          = $rec['inhsno'];
        $te_inhddt          = ytod($rec['inhddt']);
        $te_inhrem          = $rec['inhrem'];
        $te_inhsupnam       = $rec['inhsupnam'];
        $te_inhcoqsnoauto   = $rec['coqdes'];
        $te_inhval          = number_format($rec['inhval'], 2);
        $te_inhgensnoauto   = $rec['gendesauto'];
        $te_inhchqno        = $rec['inhchqno'];
        $te_inhchqddt       = ytod($rec['inhchqddt']);
        $te_inhourref       = $rec['inhourref'];
        $te_inhbank         = $rec['inhbank'];
        $te_inhbranch       = $rec['inhbranch'];
        $te_entddt          = ytod($rec['inhentddt']);
        $te_enttime         = $rec['inhenttime'];
        $te_ucdnew          = $rec['inhucdnew'];
    }   
    else
    {
        $val_key = "n";
        echo "<script>alert('Document Not found');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Direct Payment Print</title>
<link href="css/bootstrap5.min.css" rel="stylesheet">
<link href="css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="css/select2-bootstrap-5-theme.min.css" />

<script src="jquery/jquery-1.11.3.min.js"></script>
<script src="js/bootstrap5.bundle.min.js"></script>
<script src="js/select2.min.js"></script>

<style>
    body {
        height: 100vh;
        overflow: hidden;
        background-color: #f4f6f9;
        font-size: 0.85rem;
    }
    .wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 8px 15px;
    }
    .flex-shrink-0 { flex-shrink: 0; }
    .flex-grow-1 { flex-grow: 1; }
    
    /* Condensed Form Elements */
    .form-label { font-size: 0.75rem; margin-bottom: 0.1rem; color: #495057; }
    .select2-container .select2-selection--single { height: 30px !important; }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 28px !important; font-size: 0.8rem; padding-top: 0; }
    .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.8rem; height: 30px;}
    
    /* Condensed Header Data */
    .inquiry-label {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 0;
        line-height: 1;
    }
    .inquiry-value {
        font-weight: 600;
        font-size: 0.85rem;
        color: #212529;
        margin-bottom: 4px;
        padding-bottom: 2px;
        border-bottom: 1px dashed #dee2e6;
        min-height: 22px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Compact Table */
    .table-compact th, .table-compact td {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
    }
    .table-header-sticky {
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: #f8f9fa;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
</style>

<SCRIPT LANGUAGE="JavaScript">
function x_next_dno(arg1, arg2, arg3, arg4, callback) {
    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', { ajax_action: 'next_dno', args: [arg1, arg2, arg3, arg4] }, function(data) { callback(data); });
}

function next_dno() 
{ 
 var te_inhlocsno = document.fn.inhlocsno.value;
 var te_inhdptypsno = document.fn.inhdptypsno.value;

 if(te_inhlocsno > 0) {          
  x_next_dno('<?php echo $h_typ?>', te_inhlocsno, 'inq', te_inhdptypsno, next_amedno_x);   
 } else {
  document.fn.inhdno.options.length = 0;
 }
}

function next_amedno_x(str_inhdno) 
{
 var the_array = str_inhdno.split("####");  
 document.fn.inhdno.options.length = the_array.length;
 for(x=0;x<the_array.length;x++) 
 {
  var lines = the_array[x].split("");
  document.fn.inhdno.options[x].value = lines[0];  
  document.fn.inhdno.options[x].text  = lines[1];
 } 
 document.fn.inhdno.value = "";
} 

function valkey()
{   
 var fld1 = document.forms[0].inhlocsno;
 if (fld1.value == "") { alert("Select Location"); fld1.focus(); return false; }
 
 var fld2 = document.forms[0].inhdptypsno;
 if (fld2.value == "") { alert("Select Payment Type"); fld2.focus(); return false; } 
 
 var fld3 = document.forms[0].inhdno;
 if (fld3.value == "") { alert("Select Document No"); fld3.focus(); return false; }
 
 return true;
}

$(document).ready(function() {
    $('#inhdno').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});
</SCRIPT>
</head>
<body>
<div class="wrapper">
    <h6 class="border-bottom pb-1 mb-2 flex-shrink-0"><b>Direct Payment Printing</b></h6>

    <form name='fn' method='post' action='dpmprt_pp.php' class="bg-white p-2 rounded mb-2 shadow-sm border flex-shrink-0">
        <div class="row gx-2 align-items-end">
            <div class="col-md-3">
                <label class='form-label fw-bold'>Location:</label>
                <?php
                $sqlquery = "select concat(locloc,' - ',locnam) locloc,locsno from locmast where locsno=1 order by locloc";                            
                $result   = mysql_query($sqlquery) or die("Sql Error sqlquery:<br>".mysql_error());    
                $o->GenerateDropDownListauto("inhlocsno","$te_inhlocsno","locsno","locloc",
                     "this.form.inhlocsnoauto.value=this.options[this.selectedIndex].text;next_dno();", "");
                ?>
                <input type="hidden" name="inhlocsnoauto" id="inhlocsnoauto" value="<?php echo $te_inhlocsnoauto?>">
            </div>

            <div class="col-md-3">
                <label class='form-label fw-bold'>Payment Type:</label>
                <?php
                $sqlquery = "select syssno,sysrno from sysmast where sysrtp=36 order by sysrno";                            
                $result   = mysql_query($sqlquery) or die("Sql Error sqlquery:<br>".mysql_error());    
                $o->GenerateDropDownListauto("inhdptypsno","$te_inhdptypsno","syssno","sysrno",
                     "this.form.inhdptypsnoauto.value=this.options[this.selectedIndex].text;next_dno();", "");
                ?>
                <input type="hidden" name="inhdptypsnoauto" id="inhdptypsnoauto" value="<?php echo $te_inhdptypsnoauto?>">
            </div>
            
            <div class="col-md-4">
                <label class='form-label fw-bold'>Direct Pmnt #:</label>
                <?php 
                $sql_ref = "select inhdno, inhsupnam,concat(inhdno,' - ',inhrem) inhdnox from inhtran where inhlocsno='$te_inhlocsno' and inhtyp='$h_typ'";
                if ($te_inhdptypsno != "") { $sql_ref .= " and inhdptypsno='$te_inhdptypsno'"; }
                $sql_ref .= " order by inhsno desc";  
                $result  = mysql_query($sql_ref) or die("sql error inh select : <br>".mysql_error());   
                $o->GenerateDropDownList("inhdno","$te_inhdno","inhdno","inhdnox","","");  
                ?>
            </div>

            <div class="col-md-2">
                <button type="submit" NAME='ok' id='ok' class='btn btn-success btn-sm w-100 fw-bold' onClick='return valkey();'>
                    DISPLAY
                </button>
            </div>
        </div>
    </form>

    <?php if($val_key == 'y'): ?>
        <div class="card shadow-sm mb-2 flex-shrink-0">
            <div class="card-header py-1 px-2 d-flex justify-content-between align-items-center" style="font-size: 0.75rem;">
                <div>
                    <span class="fw-bold me-3">Header Details : <?php echo $te_inhdno; ?></span>
                    <span class="badge bg-info text-light">By: <?php echo $te_ucdnew; ?> on <?php echo $te_entddt; ?> @ <?php echo $te_enttime; ?></span>
                </div>
                
                <form method="post" action="dpmprt_pp.php" target="_blank" class="m-0 p-0">
                    <input type="hidden" name="inhdno" value="<?php echo $te_inhdno; ?>">
                    <button type="submit" name="print" class="btn btn-sm btn-success text-white fw-bold shadow-sm py-0" style="height: 24px;">
                        &#128438; PRINT PDF
                    </button>
                </form>
            </div>
            
            <div class="card-body bg-white p-2">
                <div class="row gx-3">
                    <div class="col-md-3">
                        <div class="inquiry-label">Date</div>
                        <div class="inquiry-value"><?php echo ($te_inhddt != '') ? $te_inhddt : '&nbsp;'; ?></div>
                        
                        <div class="inquiry-label">Bank (Chq)</div>
                        <div class="inquiry-value"><?php echo ($te_inhbank != '') ? $te_inhbank : '&nbsp;'; ?></div>
                        
                        <div class="inquiry-label">Dated Date (Chq)</div>
                        <div class="inquiry-value" style="margin-bottom:0; border-bottom:none;"><?php echo ($te_inhchqddt != '') ? $te_inhchqddt : '&nbsp;'; ?></div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="inquiry-label">Pay To</div>
                        <div class="inquiry-value text-primary"><?php echo ($te_inhsupnam != '') ? $te_inhsupnam : '&nbsp;'; ?></div>
                        
                        <div class="inquiry-label">Bank Branch</div>
                        <div class="inquiry-value"><?php echo ($te_inhbranch != '') ? $te_inhbranch : '&nbsp;'; ?></div>
                        
                        <div class="inquiry-label">RBF Invoice No</div>
                        <div class="inquiry-value" style="margin-bottom:0; border-bottom:none;"><?php echo ($te_inhourref != '') ? $te_inhourref : '&nbsp;'; ?></div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="inquiry-label">Amount Paid</div>
                        <div class="inquiry-value text-success"><?php echo ($te_inhval != '') ? $te_inhval : '0.00'; ?></div>
                        
                        <div class="inquiry-label">Bank Account</div>
                        <div class="inquiry-value"><?php echo ($te_inhgensnoauto != '') ? $te_inhgensnoauto : '&nbsp;'; ?></div>
                        
                        <div class="inquiry-label">Remarks</div>
                        <div class="inquiry-value" style="margin-bottom:0; border-bottom:none;"><?php echo ($te_inhrem != '') ? $te_inhrem : '&nbsp;'; ?></div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="inquiry-label">Cash/Chq</div>
                        <div class="inquiry-value"><?php echo ($te_inhcoqsnoauto != '') ? $te_inhcoqsnoauto : '&nbsp;'; ?></div>
                        
                        <div class="inquiry-label">Chq No</div>
                        <div class="inquiry-value"><?php echo ($te_inhchqno != '') ? $te_inhchqno : '&nbsp;'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-secondary flex-grow-1 d-flex flex-column" style="overflow: hidden;">
            <div class="card-header bg-secondary text-white fw-bold py-1 px-2 flex-shrink-0" style="font-size: 0.75rem;">
                Line Items
            </div>
            
            <div class="flex-grow-1" style="overflow-y: auto; background-color: white;">
                <table class="table table-striped table-hover align-middle mb-0 table-compact">   
                    <thead class="table-header-sticky">
                        <tr>
                            <th class="text-center border-bottom-0" style="width: 5%;">LNO</th>
                            <th class="border-bottom-0" style="width: 25%;">Account Code</th>
                            <th class="border-bottom-0" style="width: 50%;">Description</th>
                            <th class="text-end border-bottom-0" style="width: 20%;">Amount</th>
                        </tr>
                    </thead>    
                    <tbody>
                        <?php
                        $sql_lines = "SELECT inltran.inllno, g_genmast.gendes, inltran.inldes, inltran.inlamt 
                                      FROM inltran 
                                      LEFT JOIN g_genmast ON inltran.inlgensno = g_genmast.gensno 
                                      WHERE inlinhsno='$te_inhsno' 
                                      ORDER BY inllno ASC";
                        $res_lines = mysql_query($sql_lines) or die("SQL Error Lines: " . mysql_error());
                        
                        $total_amt = 0;
                        
                        if(mysql_num_rows($res_lines) > 0) {
                            while($row = mysql_fetch_array($res_lines)) {
                                $total_amt += $row['inlamt'];
                                $disp_gendes = ($row['gendes'] != '') ? $row['gendes'] : '-';
                                $disp_inldes = ($row['inldes'] != '') ? $row['inldes'] : '-';
                                
                                echo "<tr>";
                                echo "<td class='text-center'>{$row['inllno']}</td>";
                                echo "<td>{$disp_gendes}</td>";
                                echo "<td>{$disp_inldes}</td>";
                                echo "<td class='text-end fw-bold'>" . number_format($row['inlamt'], 2) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-muted py-2'>No line items found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="bg-light border-top p-2 d-flex justify-content-end align-items-center flex-shrink-0">
                <span class="fw-bold me-3">Total Value:</span>
                <span class="fw-bold text-success" style="font-size: 1.1rem;"><?php echo number_format($total_amt, 2); ?></span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
Class HtmlFormsObjects
{
  function GenerateDropDownListauto($HtmlObjectName,$HtmlObjectDefaultValue,$DBFieldToValue,$DBFieldToDescription,$action,$onkeyp)
  { 
    global $result;
    echo "<select class='form-select form-select-sm' id='$HtmlObjectName' name='$HtmlObjectName' onChange='$action' Onkeypress='$onkeyp'> \n";
    mysql_data_seek($result,0);
    echo "<option value=''>-- Select --</option> \n";
    while ($rec = mysql_fetch_array($result))
    {  
     if ($HtmlObjectDefaultValue == $rec["$DBFieldToValue"])
       echo "<option selected value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
     else
       echo "<option value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
    }
    echo "</select> \n";
  }
    
  function GenerateDropDownList($HtmlObjectName,$HtmlObjectDefaultValue,$DBFieldToValue,$DBFieldToDescription,$action,$onkeyp)
  {
    global $result;      
    echo "<select class='form-select form-select-sm' id='$HtmlObjectName' name='$HtmlObjectName' onChange='$action' Onkeypress='$onkeyp'> \n";
    echo"<option value=''>-- Select Document --</option>";
    if($result && mysql_num_rows($result) > 0) {
        mysql_data_seek($result,0);
        while ($rec = mysql_fetch_array($result))
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

function next_dno($te_inhtyp,$te_inhlocsno,$te_cmdopt,$te_inhdptypsno='')
{
 if($te_inhtyp=="") { return ""; }   
 
 $sql_dno = "select inhdno, inhsupnam, concat(inhdno,' - ',inhrem) inhrpritem from inhtran where inhlocsno='$te_inhlocsno' and inhtyp='$te_inhtyp' ";
 if($te_inhdptypsno != '') {
     $sql_dno .= " and inhdptypsno='$te_inhdptypsno' ";
 }
 $sql_dno .= " order by inhrpritem desc";     
 $result_dno = mysql_query($sql_dno) or die("no Documents ame  <br>".mysql_error());
 $te_return  = chr(25).chr(25)."####"; 
 while($fields_dno = mysql_fetch_array($result_dno,MYSQL_BOTH)) {
  $te_inhdno    = $fields_dno['inhdno'];
  $te_inhsupnam   = $fields_dno['inhsupnam'];
  $te_inhrpritem    = $fields_dno['inhrpritem'];
  $te_return .= "$te_inhdno".""."$te_inhrpritem"."####";    
 }
 return substr($te_return,0,-4);
}
?>
</body>
</html>