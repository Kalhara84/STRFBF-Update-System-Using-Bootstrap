<?php
$config_use_sessions = TRUE;
session_start();
   
if (!$_SESSION["validated"]) 
{ 
    header("Location: login.php");
    return;
}

if(isset($_GET['cmdopt']))
    $te_cmdopt = $_GET['cmdopt'];
else
    $te_cmdopt = $_POST['cmdopt'];
     
$o = new HtmlFormsObjects;   
$te_usdcsssno   = $_SESSION["susdcsssno"];
$te_usdlocsno   = $_SESSION["susdlocsno"];

require_once dirname(__FILE__) . '/db.php';

$te_fpdf = $_SERVER['DOCUMENT_ROOT'].'tfpdf/fpdf_fpdi/font/';
if(!defined('FPDF_FONTPATH')) { define('FPDF_FONTPATH', $te_fpdf); }
require_once $_SERVER['DOCUMENT_ROOT'].'tfpdf/tfpdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'tfpdf/fpdf_fpdi/fpdi.php';

function ytod($ddt)
{
 if ($ddt=="0000-00-00" || empty($ddt)) return "";
 else {
     $year = substr($ddt, 0, 4); $month = substr($ddt, 5, 2); $day = substr($ddt, 8, 2);
     return  "$day/$month/$year";
 }
}

function dtoy($ddt)
{
if ($ddt) {
    $vl=explode('/',$ddt); 
    $day = $vl[0]; $month = $vl[1]; $year = $vl[2];
    return "$year-$month-$day";
}
else {return ""; }
}

// --- PDF GENERATION FUNCTION ---
function generate_invoice_pdf($te_inhdno)
{
    ob_start(); 
    error_reporting(0);
    
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
                 
    $result_pr = mysqli_query(db_connection(), $query_pr) or die("No header records..!".mysqli_error(db_connection()));
    
    if(mysqli_num_rows($result_pr)) {
        $fields_pr = mysqli_fetch_array($result_pr);
        
        $pdf->SetFont('Helvetica', '', 10);
        
        $pdf->Text(40, 45, $te_inhdno);
        $pdf->Text(155, 38, ytod($fields_pr['inhddt']));
        
        $pdf->Text(150, 45, $fields_pr['locnam']);
        $pdf->Text(150, 52, $fields_pr['paytype'] .' - '. $fields_pr['coqdes']);
        
        $pdf->Text(40, 52, $fields_pr['inhsupnam']);
        $pdf->Text(150, 75, $fields_pr['inhourref']);
        
        $pdf->Text(40, 59, $fields_pr['inhbank']);
        $pdf->Text(150, 59, $fields_pr['inhchqno']);
        
        $pdf->Text(40, 66, $fields_pr['inhbranch']);
        $pdf->Text(150, 66, ytod($fields_pr['inhchqddt']));
        
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetXY(40, 70);
        $pdf->MultiCell(70, 3, $fields_pr['gendesauto'], 0, 'L', false);
        
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 78);
        $pdf->MultiCell(140, 5, $fields_pr['inhrem'], 0, 'L', false);
        
        $sql_lines = "SELECT inltran.inllno, g_genmast.gendes, inltran.inldes, inltran.inlamt 
                      FROM inltran 
                      LEFT JOIN g_genmast ON inltran.inlgensno = g_genmast.gensno 
                      WHERE inlinhsno='{$fields_pr['inhsno']}' 
                      ORDER BY inllno ASC";
        $res_lines = mysqli_query(db_connection(), $sql_lines);
        
        $pdf->SetFont('Helvetica', '', 8);
        
        $ly = 100; 
        $row_height = 6;
        $tot_val = 0;
        
        while($row = mysqli_fetch_array($res_lines)) {
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
        
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(160, 122); 
        $pdf->Cell(35, 6, number_format($tot_val, 2), 0, 0, 'R');
        
        $pdf->Output('DIRECT_PAYMENT_' . $te_inhdno . '.pdf', 'I');
        ob_end_flush(); 
        exit;
    } else {
        die("Document data not found for printing.");
    }
}
// ------------------------------------

// --- AJAX HANDLER REPLACING SAJAX ---
if (isset($_POST['ajax_action'])) {
    ob_clean();
    $action = $_POST['ajax_action'];
    $args = isset($_POST['args']) ? $_POST['args'] : array();
    $allowed_functions = array('next_dno', 'load_gen', 'generate_invoice_pdf');
    if (in_array($action, $allowed_functions) && function_exists($action)) {
        if($action == 'generate_invoice_pdf') {
            generate_invoice_pdf($args[0]);
        } else {
            $result = call_user_func_array($action, $args);
            echo $result;
        }
    }
    exit;
}

// --- PDF GENERATION HANDLER ---
if (isset($_GET['generate_pdf'])) {
    $te_inhdno = mysqli_real_escape_string(db_connection(), $_GET['generate_pdf']);
    generate_invoice_pdf($te_inhdno);
    exit;
}

//-------------------------Check User Rights [BEGIN]-------------------------------------
$te_usropt="usryn";
if($te_cmdopt=='add')
    $te_usropt="usraddyn";
if($te_cmdopt=='ame')
    $te_usropt="usrameyn";
if($te_cmdopt=='del')
    $te_usropt="usrdelyn";
    
if($te_cmdopt=='add')
    $te_savebutt="SAVE";
if($te_cmdopt=='ame')
    $te_savebutt="UPDATE";
if($te_cmdopt=='del')
    $te_savebutt="DELETE"; 

$te_class="class='btn btn-info'"; 

$te_prgnam = $_SERVER['PHP_SELF'];
if($_SESSION["susdadmin"]!= "Y")
{
    //include "a_usrval.php";
}

$val_key     = "n";  

if(isset($_GET['cmdopt']))
{ 
    $te_cmdopt = $_GET['cmdopt'];
}
else 
{
    $te_cmdopt = $_POST['cmdopt'];
}

$te_enttime     = strftime('%H:%M:%S');
$te_entddt      = strftime ("%Y-%m-%d");
$te_inhddt      = strftime("%d/%m/%Y");
$te_inhchqddt   = strftime("%d/%m/%Y");
$te_sysddt      = strftime("%d/%m/%Y");

$te_inhamt      = '';
$te_inhval      = '';
$te_inhcashamt  = '0.00';
$te_inhchqamt   = '0.00';
$te_inhgensno   = '';
$te_inhgensnoauto = '';
$te_inhchqno    = '';
$te_inhourref   = '';
$te_inhbank     = '';
$te_inhbranch   = '';
$te_inhrem      = '';

// $te_inhsupsno   = '';
// $te_inhsupsnoauto = '';
$te_inhsupnam   = '';

$te_inhcoqsno   = '';
$te_inhcoqsnoauto = '';

$te_inhdptypsno = '';
$te_inhdptypsnoauto = '';

if(empty($_POST['inhdptypsno']) && empty($_POST['ok']) && empty($_POST['save'])) {
    $sql_def_ptyp = "SELECT syssno, sysrno FROM sysmast WHERE sysrtp=36 AND sysrno LIKE '%Other%' LIMIT 1";
    $res_def_ptyp = @mysqli_query(db_connection(), $sql_def_ptyp);
    if ($res_def_ptyp && @mysqli_num_rows($res_def_ptyp)) {
        $row_def_ptyp = mysqli_fetch_assoc($res_def_ptyp);
        $te_inhdptypsno = $row_def_ptyp['syssno'];
        $te_inhdptypsnoauto = $row_def_ptyp['sysrno'];
    }
}

$te_inhlocsno     = 1;
$te_inhlocsnoauto = 'HARDCODED LOC';

$js_sysddt  = "var te_sysddt = '$te_sysddt';\n";

$te_grnyear     = strftime("%Y");
$te_usdusd      = $_SESSION["susdusd"];
$te_ucdnew      = $te_usdusd;
               
$sql_usd       = "select usdsno from usdmast where usdusd='$te_usdusd'";
$result_usd    = mysqli_query(db_connection(), $sql_usd) or die("SQL Error :".mysqli_error(db_connection()));
$fields        = mysqli_fetch_array($result_usd);
$te_usdsno     = $fields['usdsno']; 
$js_usdsno     = "var te_usdsno='$te_usdsno';\n";

$h_typ='30';

$fields_colnames_inh = array();$ve_oldval=array();$ve_newval=array();
$res=mysqli_query(db_connection(), "SHOW COLUMNS FROM inhtran");
while ($x = mysqli_fetch_assoc($res))
{
    $fields_colnames_inh[] = $x['Field'];
}

$fields_colnames_inl = array();$ve_oldval=array();$ve_newval=array();
$res=mysqli_query(db_connection(), "SHOW COLUMNS FROM inltran");
while ($x = mysqli_fetch_assoc($res))
{
    $fields_colnames_inl[] = $x['Field'];
} 
?>
<?php
$ve_work  = "";
if((isset($_POST['save'])) ||(isset($_POST['ok'])) )
{
    // $te_inhlocsno     = $_POST['inhlocsno'];
    // $te_inhlocsnoauto = $_POST['inhlocsnoauto'];

    $te_inhdno        = $_POST['inhdno'];   
    $te_inhdnoauto    = $_POST['inhdnoauto'];
    
    $te_inhdptypsno     = $_POST['inhdptypsno'];
    $te_inhdptypsnoauto = $_POST['inhdptypsnoauto'];   
       
    if($te_cmdopt=='add')
    {
        $te_inhdno        = $_POST['inhdno1'];
        $te_inhdno1       = $_POST['inhdno1'];
    }
}
  
if(isset($_POST['save']))
{       
    $sql_ = "lock tables inhtran write,inltran write,stkmast write,logmast write,sysmast write,locmast write,ostmast write";
    $result_ = mysqli_query(db_connection(), $sql_);
    
    $te_inhddt        = dtoy($_POST['inhddt']);
    $te_inhrem        = $_POST['inhrem'];       
    
    // $te_inhsupsno     = $_POST['inhsupsno'];
    $te_inhsupnam     = $_POST['inhsupnam'];

    $te_inhcoqsno     = $_POST['inhcoqsno'];
    $te_inhcoqsnoauto = $_POST['inhcoqsnoauto'];
    
    $te_inhamt        = isset($_POST['inhval']) ? $_POST['inhval'] : '0.00';
    $te_inhval        = $_POST['inhval'];
    
    $te_inhcashamt = 0.00;
    $te_inhchqamt  = 0.00;
    if (stripos($te_inhcoqsnoauto, 'CASH') !== false) {
        $te_inhcashamt = $te_inhamt;
    } else {
        $te_inhchqamt = $te_inhamt;
    }

    $te_inhgensno     = $_POST['inhgensno'];
    $te_inhchqno      = $_POST['inhchqno'];
    $te_inhchqddt     = dtoy($_POST['inhchqddt']);
    $te_inhourref     = $_POST['inhourref'];
    $te_inhbank       = $_POST['inhbank'];
    $te_inhbranch     = $_POST['inhbranch'];
    

    // Capture the new line item fields
    $te_hinlgensno  = $_POST['hinlgensno'];         
    $te_hinldes     = $_POST['hinldes'];
    $te_hinlamt     = $_POST['hinlamt'];
    $te_hinlsno     = $_POST['hinlsno'];
    
    $a_inlgensno    = explode('  ',$te_hinlgensno);  
    $a_inldes       = explode('  ',$te_hinldes);
    $a_inlamt       = explode('  ',$te_hinlamt);
    $a_inlsno       = explode('  ',$te_hinlsno);
  
    $rec_count      = count($a_inlamt);

    $sql_query = "select inhsno from inhtran where inhlocsno='$te_inhlocsno' and inhtyp='$h_typ' and inhdno='$te_inhdno' ";
    $result    = mysqli_query(db_connection(), $sql_query);
    
    if($te_cmdopt=='add')
    {
        $te_inhdno = next_dno($h_typ,$te_inhlocsno,'add');  
        $te_inhsno  = next_auto_increment('inhtran');                                                                                                                                                                                                                                                                                                   
        $sql_h = "INSERT INTO inhtran (inhtyp,inhlocsno,inhdno,inhddt,inhrem,inhdptypsno,
                   inhsupnam,inhcoqsno,inhcashamt,inhchqamt,inhgensno,inhchqno,inhchqddt,inhourref,inhbank,inhbranch,
                   inhval,inhpodcmplflg,
                   inhucdnew,inhentddt,inhenttime) 
                       values('$h_typ','$te_inhlocsno','$te_inhdno','$te_inhddt','$te_inhrem','$te_inhdptypsno',
                       '$te_inhsupnam','$te_inhcoqsno','$te_inhcashamt','$te_inhchqamt','$te_inhgensno','$te_inhchqno','$te_inhchqddt','$te_inhourref','$te_inhbank','$te_inhbranch',
                       '$te_inhval','n',
                       '$te_ucdnew','$te_entddt','$te_enttime')"; 
        $result_h = mysqli_query(db_connection(), $sql_h) or die("Could not Insert to inh</BR>".mysqli_error(db_connection()));
        echo "<div class='alert alert-success text-center py-1 mb-1'>Inserted Document No: '$te_inhdno'</div>";
        
        for($i=0;$i<$rec_count;$i++)
        {   
            if($a_inlamt[$i] > 0) { // Safety check to prevent empty rows
                $te_inllno = $i +1;
                $te_inlsno  = next_auto_increment('inltran');   
                $sql_ah = "INSERT INTO inltran(inllno,inlinhsno,inlgensno,inldes,inlamt,inlucdnew,inlentddt,inlenttime)
                             VALUES ('$te_inllno','$te_inhsno','$a_inlgensno[$i]','$a_inldes[$i]','$a_inlamt[$i]',
                             '$te_ucdnew','$te_entddt','$te_enttime')";        
                $result_al = mysqli_query(db_connection(), $sql_ah) or die("MYSQL Error ins inl insert : <br>".mysqli_error(db_connection()));
            }
        }       
  
        echo "<div class='alert alert-success text-center py-1 mb-1'>Line record(s) inserted</div>";                
        
        $te_inhdno_saved = $te_inhdno;
        $te_inhdno = next_dno($h_typ,$te_inhlocsno,'add');
        
        // Trigger PDF printing
        echo "<form id='pdfForm_add' method='GET' action='" . $_SERVER['PHP_SELF'] . "' target='_blank' style='display:none;'>
            <input type='hidden' name='generate_pdf' value='" . htmlspecialchars($te_inhdno_saved) . "'>
        </form>
        <script>
            setTimeout(function() {
                document.getElementById('pdfForm_add').submit();
            }, 1000);
        </script>";  
    }
  
    if($te_cmdopt=='ame')                   
    {
        if(!mysqli_num_rows($result))
        {
            echo "<div class='alert alert-danger text-center py-1 mb-1'>Document No: '$te_inhdnoauto' not found..!</div>";
        }
        else
        {
            $fields     = mysqli_fetch_array($result, MYSQLI_BOTH);
            $te_inhsno  = $fields['inhsno'];
            
            $sql_query = "select * from inhtran where inhsno='$te_inhsno' ";
            $result_inh  = mysqli_query(db_connection(), $sql_query);
            $fields_inh  = mysqli_fetch_array($result_inh);
            if(mysqli_num_rows($result_inh))
            {
                $ve_oldval=array();$ve_newval=array();   
                foreach ($fields_colnames_inh as $f)
                {
                    $ve_oldval[$f]=$fields_inh[$f];
                }  
            }
            $sql_ame_h = "UPDATE inhtran SET  inhdno        ='$te_inhdno', 
                                              inhddt        ='$te_inhddt',
                                              inhsupnam     = '$te_inhsupnam',
                                              inhcoqsno      = '$te_inhcoqsno',
                                              inhcashamt     = '$te_inhcashamt',
                                              inhchqamt      = '$te_inhchqamt',
                                              inhgensno      = '$te_inhgensno',
                                              inhchqno       = '$te_inhchqno',
                                              inhchqddt      = '$te_inhchqddt',
                                              inhourref      = '$te_inhourref',
                                              inhbank        = '$te_inhbank',
                                              inhbranch      = '$te_inhbranch',
                                              inhval ='$te_inhval',
                                              inhrem        ='$te_inhrem',
                                              inhucdame    ='$te_ucdnew',
                                              inhameddt    ='$te_entddt',
                                              inhametime    ='$te_enttime'
                                              WHERE 
                                              inhsno ='$te_inhsno'";
            $result_bh = mysqli_query(db_connection(), $sql_ame_h) or die("Could not Update header:<br>".mysqli_error(db_connection()));
            
            $sql_query ="select * from inhtran where inhsno='$te_inhsno'";
            $result_inh = mysqli_query(db_connection(), $sql_query);
            $fields_inh = mysqli_fetch_array($result_inh);
            foreach ($fields_colnames_inh as $f)
            {
                $ve_newval[$f]=$fields_inh[$f];
            }  
            $log='';
            foreach ($fields_colnames_inh as $f)
            {
                if ($ve_newval[$f]!=$ve_oldval[$f] && $f!='inhucdame' && $f!='inhameddt' && $f!='inhametime')
                {
                    $log.="$f-->$ve_oldval[$f] to $ve_newval[$f]...";    
                }
            }   
            if($log!='')
            {
                $te_logucd=$_SESSION["susdusd"];
                $te_logentddt=strftime("%Y-%m-%d");
                $te_logenttime=strftime('%H:%M:%S');
                $sql_log1 = "insert into logmast
                              (logucd,logddt,logtime,logprgnam,logaction,logdes,loginhsno,loginhtyp,loginhdno)
                               values
                              ('$te_logucd','$te_logentddt','$te_logenttime','b_issenter.php','BBGRN ADD',
                               'Amend BBGRN  $te_inhdno::$log','$te_inhsno','$h_typ','$te_inhdno')";
                $result =mysqli_query(db_connection(), $sql_log1);
            }
            
            echo "<div class='alert alert-success text-center py-1 mb-1'>Header updated - $te_inhdno</div>";
            
            $sql_  = "update inltran set inldelflg='Y' where inlinhsno='$te_inhsno'";
            $result_    = mysqli_query(db_connection(), $sql_) or die("Could not update del flg inl ame");
            
            for($i=0;$i<$rec_count;$i++)
            { 
                if($a_inlamt[$i] <= 0) continue; // Skip invalid entries

                $te_inlsno=$a_inlsno[$i];
                $sql_query = "select * from inltran where inlsno='$te_inlsno' ";
                $result_inl_l  = mysqli_query(db_connection(), $sql_query);
                $fields_inl_l  = mysqli_fetch_array($result_inl_l);
                if(mysqli_num_rows($result_inl_l))
                {
                    $ve_oldval=array();$ve_newval=array();   
                    foreach ($fields_colnames_inl as $f)
                    {
                        $ve_oldval[$f]=$fields_inl_l[$f];
                    }  
                }
                    
                $sql_inl = "select inlsno,inllno from inltran where inlsno='$te_inlsno'";
                $result_inl = mysqli_query(db_connection(), $sql_inl);
                if(mysqli_num_rows($result_inl))
                {
                    $fields_inl= mysqli_fetch_array($result_inl,MYSQLI_BOTH);
                    $te_inllno  = $fields_inl['inllno'];
               
                    $sql_sqlgrl     = "Update inltran set
                        inlgensno    ='$a_inlgensno[$i]',
                        inldes       ='$a_inldes[$i]',
                        inlamt       ='$a_inlamt[$i]',
                        inlucdame    ='$te_ucdnew',
                        inlameddt    ='$te_entddt',
                        inlametime   ='$te_enttime',
                        inldelflg    =''
                        where inlsno ='$te_inlsno'";
                    $result = mysqli_query(db_connection(), $sql_sqlgrl) or die("Could not Update inl<br>".mysqli_error(db_connection()));
                    
                    $sql_query ="select * from inltran where inlsno='$te_inlsno'";
                    $result_inl_l = mysqli_query(db_connection(), $sql_query);
                    $fields_inl_l = mysqli_fetch_array($result_inl_l);
                    foreach ($fields_colnames_inl as $f)
                    {
                        $ve_newval[$f]=$fields_inl_l[$f];
                    }  
                    $log='';
                    foreach ($fields_colnames_inl as $f)
                    {
                        if ($ve_newval[$f]!=$ve_oldval[$f] && $f!='inlucdame' && $f!='inlameddt' && $f!='inhametime')
                        {
                            $log.="$f-->$ve_oldval[$f] to $ve_newval[$f]...";    
                        }
                    }   
                    if($log!='')
                    {
                        $te_logucd=$_SESSION["susdusd"];
                        $te_logentddt=strftime("%Y-%m-%d");
                        $te_logenttime=strftime('%H:%M:%S');
                        $sql_log1 = "insert into logmast
                                      (logucd,logddt,logtime,logprgnam,logaction,logdes,loginhsno,loginhtyp,loginhdno,loginlsno,loginllno)
                                     values
                                     ('$te_logucd','$te_logentddt','$te_logenttime','b_issenter.php','AMEBBGRN',
                                      'AMEBBGRN $te_inhdno::$log','$te_inhsno','$h_typ','$te_inhdno','$te_inlsno','$te_inllno')";
                        $result =mysqli_query(db_connection(), $sql_log1);
                    }      
                }
                else
                {   
                    $sql_ ="select inllno from inltran where inlinhsno='$te_inhsno' order by inllno desc";
                    $result_ = mysqli_query(db_connection(), $sql_);
                    if(mysqli_num_rows($result_))
                    {
                        $fields_ = mysqli_fetch_array($result_);
                        $te_inllno  = $fields_['inllno']+1;
                    }     
                    else
                    {
                        $te_inllno=1;
                    }   
                 
                    $te_inlsno = next_auto_increment('inltran'); 
               
                    $sql_ah = "INSERT INTO inltran(inllno,inlinhsno,inlgensno,inldes,inlamt,
                               inlucdnew,inlentddt,inlenttime)
                        VALUES ('$te_inllno','$te_inhsno','$a_inlgensno[$i]','$a_inldes[$i]','$a_inlamt[$i]',
                                 '$te_ucdnew','$te_entddt','$te_enttime')";
                    $result_al = mysqli_query(db_connection(), $sql_ah) or die("error insert inltran MYSQL Error ins inl ame2 : <br>".mysqli_error(db_connection()));
                }
            } 
            $sql_  = "delete from inltran where inlinhsno='$te_inhsno' and inldelflg='Y'";
            $result_    = mysqli_query(db_connection(), $sql_) or die("Could not delete del flg inl ");

            $sql_log = "insert into logmast(logucd,logddt,logtime,logprgnam,logaction,logdes)
                         values ('$te_usdusd','$te_entddt','$te_enttime','b_issenter.php','$te_cmdopt',
                         'Amend Line  AMEISSUETRANSFER \" $te_inlsno\"')";
            mysqli_query(db_connection(), $sql_log);
            
            // Trigger PDF printing
            echo "<form id='pdfForm_ame' method='GET' action='" . $_SERVER['PHP_SELF'] . "' target='_blank' style='display:none;'>
                <input type='hidden' name='generate_pdf' value='" . htmlspecialchars($te_inhdno) . "'>
            </form>
            <script>
                setTimeout(function() {
                    document.getElementById('pdfForm_ame').submit();
                }, 1000);
            </script>";
  
        }
    }

    if($te_cmdopt=='add' || $te_cmdopt=='ame')         
    {
        // Total calculated using the new inlamt field
        $sql_ = "select sum(inlamt) inhval from inltran where inlinhsno='$te_inhsno'";
        $result_ = mysqli_query(db_connection(), $sql_) or die("SQL Error sql_ inlsum  : <br>".mysqli_error(db_connection()));
        $te_inhval=0;
        if(mysqli_num_rows($result_))
        {
            $fields_= mysqli_fetch_array($result_,MYSQLI_BOTH);
            $te_inhval = $fields_['inhval'];
        }
        
        $sql_upd_inh    = "UPDATE inhtran SET inhval ='$te_inhval' WHERE inhsno ='$te_inhsno'";
        $result_upd_inh = mysqli_query(db_connection(), $sql_upd_inh) or die("Could not Update header:<br>".mysqli_error(db_connection()));
    }
 
    if($te_cmdopt=='del')
    {       
        if(!mysqli_num_rows($result))
        {
            echo "<div class='alert alert-danger text-center py-1 mb-1'>Document No: '$te_inhdnoauto' not found..!</div>";
        }
        else
        {
            $fields     = mysqli_fetch_array($result, MYSQLI_BOTH);
            $te_inhsno  = $fields['inhsno'];
            
            $query_inl = "select inlsno from inltran where inlinhsno='$te_inhsno' order by inllno";
            $result_inl = mysqli_query(db_connection(), $query_inl) or die("sql error inl:<br>".mysqli_error(db_connection()));
            $ok_flg='y';

            if($ok_flg=='y')
            {              
                $sql_del = "DELETE FROM inltran where inlinhsno='$te_inhsno'";
                $result  = mysqli_query(db_connection(), $sql_del) or die("Could not delete cnl");
                echo "<div class='alert alert-success text-center py-1 mb-1'>' $te_inhdnoauto ' line deleted</div>";
                             
                $sql_dell = "DELETE FROM inhtran where inhsno='$te_inhsno'";
                $result  = mysqli_query(db_connection(), $sql_dell) or die("Could not delete cnh");
                echo "<div class='alert alert-success text-center py-1 mb-1'> ' $te_inhdnoauto ' header deleted</div>";  
                
                $sql_log = "insert into logmast(logucd,logddt,logtime,logprgnam,logaction,logdes)
                             values ('$te_usdusd','$te_entddt','$te_enttime','b_issenter.php','DELBBISSUE',
                             'DELBBISSUE \" $te_inhdno\"')";
                mysqli_query(db_connection(), $sql_log);
            }
        }
    } 
 
    $sql_ = "unlock tables";
    $result_ = mysqli_query(db_connection(), $sql_);
}

?>
<?php
$val_key="n";
if(isset($_POST['ok']))
{
    $sql_ok    = "select inhsno,inhddt,inhrem,locloc,locnam as inhdlvtosnoauto,inhlocsno,inhsupnam,
                       inhcoqsno,coq.sysdes coqdes, inhval,inhdlvtosno,
                       inhcashamt, inhchqamt, inhgensno, inhchqno, inhchqddt, inhourref, inhbank, inhbranch
                       from inhtran            
                      left join locmast on inhdlvtosno=locsno
                      /*left join supmast on inhsupsno=supsno*/ 
                      left join sysmast coq on inhcoqsno=coq.syssno 
                      
                      where  inhdno='$te_inhdno' and inhtyp='$h_typ'";
    $result_ok = mysqli_query(db_connection(), $sql_ok) or die("sql error inh ok:<br>".mysqli_error(db_connection()));
    if(mysqli_num_rows($result_ok))
    {
        if ($te_cmdopt=='add')   
        { 
            $val_key="n";
            echo "<div class='alert alert-danger text-center py-1 mb-1'>'$te_inhdnoauto' already exists!</div>";
        }
        else
        {
            $val_key="y";
            if ($te_cmdopt=='del')
            {
                $te_ro="readonly";
            }  
            $rec                = mysqli_fetch_array($result_ok);
            $te_inhsno          = $rec['inhsno'];
            $te_inhddt          = ytod($rec['inhddt']);
            $te_inhrem          = $rec['inhrem'];
            // $te_inhsupsno       = $rec['inhsupsno'];
            // $te_inhsupsnoauto   = $rec['supnam'];
            $te_inhsupnam       = $rec['inhsupnam'];
            $te_inhcoqsno       = $rec['inhcoqsno'];
            $te_inhcoqsnoauto   = $rec['coqdes'];
            $te_inhval          = trim(str_replace(',', '', $rec['inhval']));
            $te_inhgensno       = trim($rec['inhgensno']);
            
            $te_inhchqno        = $rec['inhchqno'];
            $te_inhchqddt       = ytod($rec['inhchqddt']);
            $te_inhourref       = $rec['inhourref'];
            $te_inhbank         = $rec['inhbank'];
            $te_inhbranch       = $rec['inhbranch'];
            
            $sql_display= "select inl.inlsno, inl.inlgensno, inl.inldes, inl.inlamt, gen.gendes 
                           from inltran inl
                           left join g_genmast gen on inl.inlgensno = gen.gensno
                           WHERE inl.inlinhsno='$te_inhsno' order by inl.inlsno";        
            $result_display = mysqli_query(db_connection(), $sql_display) or die("sql error inl ok :<br>".mysqli_error(db_connection()));
            $i=0;
            while ($fields = mysqli_fetch_array($result_display,MYSQLI_BOTH))
            {      
                $te_inlgensno      = trim($fields['inlgensno']);
                $te_inlgensno_text = trim($fields['gendes']); 
                $te_inldes         = trim($fields['inldes']);
                $te_inlamt         = trim(str_replace(',', '', $fields['inlamt']));
                $te_inlsno         = trim($fields['inlsno']);
                
                $ve_work .= "ve_work['".$i."'] = {
                              inlgensno       : '".$te_inlgensno."',
                              inlgensno_text  : '".addslashes($te_inlgensno_text)."',
                              inldes          : '".addslashes($te_inldes)."',
                              inlamt          : '".$te_inlamt."',
                              inlsno          : '".$te_inlsno."',
                              del: ' '};\n";          
                $i+=1;
                
                $te_inlgensno      ="";
                $te_inldes         ="";
                $te_inlamt         ="";
                $te_inlsno         ="";
            }
        }
    }   
    else
    {
        if ($te_cmdopt=='ame') 
        {
            $val_key="n";
            echo "<div class='alert alert-danger text-center py-1 mb-1'>$te_inhdnoauto  Not found</div>";
        }
        else
        {
            $val_key="y";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title></title>
<link href="css/bootstrap5.min.css" rel="stylesheet">
<link href="css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="css/select2-bootstrap-5-theme.min.css" />

<script src="jquery/jquery-1.11.3.min.js"></script>
<script src="js/bootstrap5.bundle.min.js"></script>
<script src="js/select2.min.js"></script>

<script language="JavaScript" src="js/formvalidation.js"></script>
<script language="JavaScript" src="js/calendar2.js"></script>  
 
<SCRIPT LANGUAGE="JavaScript">
var isIE    = (navigator.appName == "Microsoft Internet Explorer");
var isIEWin = (isIE && navigator.userAgent.indexOf("Win") != -1);
var isIEMac = (isIE && navigator.userAgent.indexOf("Mac") != -1);
    
var ve_work = new Array();
var lineItemCount = 0;

<?php
 echo "$js_usdsno";
 echo "$js_sysddt";
?>

function x_next_dno(arg1, arg2, arg3, arg4, callback) {
    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', { ajax_action: 'next_dno', args: [arg1, arg2, arg3, arg4] }, function(data) { callback(data); });
}

function showdiv(obj) 
{
 document.getElementById(obj).style.display = "block";
}
function hidediv(obj)
{
 if(document.getElementById(obj).style.display == "block")
  document.getElementById(obj).style.display = "none";
}
 
function next_dno() 
{ 
 var te_inhlocsno = document.fn.inhlocsno.value;
 var te_inhdptypsno = document.fn.inhdptypsno.value;

 if('<?php echo $te_cmdopt?>'=='add')
 {
  if(te_inhlocsno>0) {
   x_next_dno('<?php echo $h_typ?>',te_inhlocsno,'<?php echo $te_cmdopt?>',te_inhdptypsno,next_dno_x);   
  } else {      
   document.fn.inhdno.value = "";       
  } 
 }
 if('<?php echo $te_cmdopt?>'!='add')
 {
  if(te_inhlocsno>0) {          
   x_next_dno('<?php echo $h_typ?>',te_inhlocsno,'<?php echo $te_cmdopt?>',te_inhdptypsno,next_amedno_x);   
  } else {
   document.fn.inhdno.options.length = 0
   document.fn.inhdnoauto.value      = "";
  }
 }
}

function next_dno_x(str_inhdno) 
{  
 var inhdno  = document.getElementById('inhdno')
 if(str_inhdno.substr(0,2)=='nf') {
  alert(str_inhdno.substr(3))
  document.fn.inhdno.value = "";
 } else {
  document.fn.inhdno1.value = str_inhdno;
 }      
}

function next_amedno_x(str_inhdno) 
{
 var the_array = str_inhdno.split("####");  
 document.fn.inhdno.options.length = the_array.length
 for(x=0;x<the_array.length;x++) 
 {
  var lines = the_array[x].split("  ");
  document.fn.inhdno.options[x].value     = lines[0]  
  document.fn.inhdno.options[x].text  = lines[1]
 } 
 document.fn.inhdno.value     = "";
 document.fn.inhdnoauto.value = "";
} 

function valkey()
{   
 var fld = document.forms[0].inhlocsno;
 if (chkBlank(fld) == false) { fld.focus(); return false; }
 var fld = document.forms[0].inhdptypsno;
 if (chkBlank(fld) == false) { fld.focus(); return false; } 
 if('<?php echo $te_cmdopt?>'=='add') {
  var fld =document.forms[0].inhdno1;
  if (chkBlank(fld) == false) return false;
 } else {
  var fld =document.forms[0].inhdno;
  if (chkBlank(fld) == false) return false;
 }
 return true;
}

function valdata()
{   
 var fld =document.fn.inhddt
 if (chkBlank(fld) == false) { fld.focus(); return false; }
 if(fld.value) {
  if (isDate(fld.value)==false) { fld.focus(); return false; }
 }
 var dt1 = document.fn.inhddt   
 if(comparedates(dt1.value,te_sysddt)==true) {
  alert ("Server date is "+ te_sysddt +"\nDate Should lesser than to Server date");
  fld.focus();
  return false;        
 }
 
 /*
 var fld = document.fn.inhsupsno;
 if (chkBlank(fld) == false) { fld.focus(); return false; }
 */
 
 var fld = document.fn.inhcoqsno;
 if (chkBlank(fld) == false) { fld.focus(); return false; }
 
 var fld = document.fn.inhsupnam;
 if (chkBlank(fld) == false) { fld.focus(); return false; }
 
 return true; 
} 

// -------------------------------------------------------------
// DYNAMIC ROW FUNCTIONS 
// -------------------------------------------------------------

function loadRowGen(rowId, preserveValue) {
    var genSelect = $('#' + rowId + ' .gensno-select');
    var currentVal = preserveValue || genSelect.val();
    
    genSelect.empty().append('<option value="">-- Select Account --</option>');
    
    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', { ajax_action: 'load_gen', args: [] }, function(data) {
        var the_array = data.split("####");
        for(var x=0; x<the_array.length; x++) {
            var lines = the_array[x].split("  ");
            if(lines[0]) {
                genSelect.append(new Option(lines[1], lines[0], false, false));
            }
        }
        
        if (currentVal) {
            genSelect.val(currentVal);
        }
        
        genSelect.trigger('change.select2');
        calculateTotal(); 
    });
}

function addLineItem(data) {
    var r_gensno = data ? data.inlgensno : '';
    var r_gensno_text = data ? data.inlgensno_text : '';
    var r_des = data ? data.inldes : '';
    var r_amt = data ? data.inlamt : '0.00';
    var r_sno = data ? data.inlsno : '';

    var rowId = 'lineRow_' + lineItemCount;
    
    var optionHtml = r_gensno ? '<option value="' + r_gensno + '" selected="selected">' + r_gensno_text + '</option>' : '<option value="">-- Select Account --</option>';
    
    var html = '<tr id="' + rowId + '">' +
        '<td class="text-center align-middle lno-cell" style="width:5%;">' + (lineItemCount + 1) + '</td>' +
        '<td style="width:25%;">' +
            '<select class="form-select form-select-sm gensno-select">' +
                optionHtml +
            '</select>' +
        '</td>' +
        '<td style="width:45%;"><input type="text" class="form-control form-control-sm des-input" value="' + r_des + '" placeholder="Description"></td>' +
        '<td style="width:20%;"><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end amt-input prevent-negative" value="' + r_amt + '" onkeyup="calculateTotal()" onchange="calculateTotal()"></td>' +
        '<td class="text-center align-middle" style="width:5%;">' +
            '<input type="hidden" class="sno-input" value="' + r_sno + '">' +
            '<button type="button" class="btn btn-danger btn-sm py-0 px-2" onclick="removeLineItem(\'' + rowId + '\')">&times;</button>' +
        '</td>' +
    '</tr>';
    
    $('#lineItemsBody').append(html);
    
    var genSelect = $('#' + rowId + ' .gensno-select');
    
    genSelect.select2({
        theme: 'bootstrap-5',
        width: '100%'
    }).on('change', function() {
        calculateTotal();
    });
    
    loadRowGen(rowId, r_gensno);
    
    lineItemCount++;
    reindexRows();
    calculateTotal();
}

function removeLineItem(rowId) {
    $('#' + rowId).remove();
    reindexRows();
    calculateTotal();
}

function reindexRows() {
    $('#lineItemsBody tr').each(function(index) {
        $(this).find('.lno-cell').text(index + 1);
    });
}

function calculateTotal() {
    var total = 0;
    var isValid = true;
    var validationMsg = "";
    var lineHasAmountWithoutAccount = false;
    
    $('.amt-input').each(function() {
        var amt = parseFloat($(this).val()) || 0;
        var gensno = $(this).closest('tr').find('.gensno-select').val();
        
        total += amt;
        
        // If an amount is entered but no account is selected
        if (amt > 0 && (!gensno || gensno.trim() === '')) {
            lineHasAmountWithoutAccount = true;
        }
    });
    
    if (lineHasAmountWithoutAccount) {
        isValid = false;
        validationMsg += " * Please select an Account Code for all lines containing an amount.<br>";
    }
    
    var displayTotal = total.toFixed(2);
    $('#totalVal').text(displayTotal);
    
    var amountPaid = parseFloat($('#inhval').val()) || 0;
    amountPaid = amountPaid.toFixed(2);
    
    if (displayTotal !== amountPaid) {
        isValid = false;
        validationMsg += ' * Line Total must equal Amount Paid (' + amountPaid + ')';
        $('#totalVal').removeClass('text-success').addClass('text-danger');
    } else {
        $('#totalVal').removeClass('text-danger').addClass('text-success');
    }
    
    // Toggle the save button state and messaging
    if (!isValid) {
        $('#saveBtn').prop('disabled', true);
        $('#totalMsg').html(validationMsg).removeClass('text-success').addClass('text-danger');
    } else {
        $('#saveBtn').prop('disabled', false);
        $('#totalMsg').html('').removeClass('text-danger').addClass('text-success');
    }
}

function save_click() {   
    var test = valdata();
    if(!test) return false; 
    
    var te_hinlgensno = "";
    var te_hinldes = "";
    var te_hinlamt = "";
    var te_hinlsno = "";
    
    var rowCount = 0;
    var rows = $('#lineItemsBody tr');
    
    for(var i=0; i<rows.length; i++) {
        var row = $(rows[i]);
        var gensno = row.find('.gensno-select').val() || " ";
        var des = row.find('.des-input').val() || " ";
        var amt = parseFloat(row.find('.amt-input').val()) || 0;
        var sno = row.find('.sno-input').val() || " ";
        
        if(amt > 0) {
            rowCount++;
            te_hinlgensno += "  " + gensno;
            te_hinldes += "  " + des; 
            te_hinlamt += "  " + amt;
            te_hinlsno += "  " + sno;
        }
    }
    
    if(rowCount === 0) {
        alert('Please enter at least one valid line item (Amount > 0).');
        return false;
    }
    
    // --- VALIDATION ---
    var lineTotal = parseFloat($('#totalVal').text()) || 0;
    var amountPaid = parseFloat($('#inhval').val()) || 0;
    
    if (lineTotal.toFixed(2) !== amountPaid.toFixed(2)) {
        alert("Cannot Save: The Line Total (" + lineTotal.toFixed(2) + ") does not match the Amount Paid (" + amountPaid.toFixed(2) + ").");
        return false;
    }
    
    var frm = document.fn;
    frm.hinlgensno.value = te_hinlgensno.substring(2);
    frm.hinldes.value = te_hinldes.substring(2);
    frm.hinlamt.value = te_hinlamt.substring(2);
    frm.hinlsno.value = te_hinlsno.substring(2);
    
    if(!confirm("Save Y/N) ..?")) return false;
    hide_div();
    return true;           
}  

function enter(nextfield)
{
 if(window.event && window.event.keyCode == 13) { nextfield.focus(); return false; } else { return true; }
}

function window_onload() 
{
 if('<?php echo $val_key?>'!='y') {
  if('<?php echo $te_cmdopt?>'=='add') { document.fn.inhdptypsno.focus(); } else { document.fn.inhdptypsno.focus(); }
  next_dno();
 } else {
  document.fn.inhddt.focus();
 }    
}

function hide_div()
{
 var divdat = document.getElementById('divhead')
 if(divdat) { divdat.style.display    = "none"; } 
 var divline = document.getElementById('lineItemsSection')
 if(divline) { divline.style.display    = "none";   } 
}

$(document).ready(function() {
    // Initialize Top Level Select2s
    /*
    $('#inhsupsno').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    $('#inhsupsno').on('select2:select', function (e) {
        $('#inhsupsnoauto').val(e.params.data.text);
    });
    */
    
    // Initialize Header Bank Account Select2
    $('#inhgensno').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    $('#inhgensno').on('select2:select', function (e) {
        $('#inhgensnoauto').val(e.params.data.text);
    });

    // Auto-fill first line item description from Remarks
    $('#inhrem').on('input change', function() {
        var remarksText = $(this).val().toUpperCase();
        $('#lineItemsBody tr:first-child .des-input').val(remarksText);
    });

    <?php
    if(isset($_POST['ok']) && ($te_cmdopt!='add')) {
        echo "$ve_work\n";
    ?>
        // Preload amendment rows
        for(var key in ve_work) {
            if(ve_work.hasOwnProperty(key)) {
                addLineItem(ve_work[key]);
            }
        }
    <?php
    } else if ($val_key=='y' && $te_cmdopt=='add') {
    ?>
        // Provide empty rows for new addition
        addLineItem();
        addLineItem();
        addLineItem();
    <?php
    }
    ?>
    
    // Run initial validation check
    calculateTotal();
});
</SCRIPT>
</head>
<body onLoad="window_onload()">
<div class="container-fluid py-3">
    <label id='debug' name='debug'></label>

    <form name='fn' method='post' action='dpmenter_bs.php' runat='server'>
        <div id='divkey' class='mb-4'>
            <div class="row align-items-center">
                
                <input type="hidden" name="inhlocsno" id="inhlocsno" value="<?php echo $te_inhlocsno; ?>">
                <input type="hidden" name="inhlocsnoauto" id="inhlocsnoauto" value="<?php echo $te_inhlocsnoauto; ?>">

                <div class="col-md-3">
                    <label class='form-label fw-bold'>Payment Type:</label>
                    <?php
                    $sqlquery = "select syssno,sysrno from sysmast where sysrtp=36 order by sysrno";                            
                    $result   = mysqli_query(db_connection(), $sqlquery) or die("Sql Error sqlquery:<br>".mysqli_error(db_connection()));
                    $o->GenerateDropDownListauto("inhdptypsno","$te_inhdptypsno","syssno","sysrno",
                         "this.form.inhdptypsnoauto.value=this.options[this.selectedIndex].text;next_dno();hide_div();",
                         "return enter(document.fn.inhdno)");
                    ?>
                    <input type="hidden" name="inhdptypsnoauto" id="inhdptypsnoauto" value="<?php echo $te_inhdptypsnoauto?>">
                </div>
                
                <div class="col-md-4">
                    <label class='form-label fw-bold'>Payment Request #:</label>
                    <?php 
                    if($te_cmdopt=='add') 
                    {
                        echo "<input type='text' name='inhdno1' id='inhdno1' class='form-control form-control-sm border-success bg-warning bg-opacity-10' readonly value='$te_inhdno' onkeypress='return enter(document.fn.ok)'>";            
                    }
                    else 
                    { 
                        $sql_ref = "select inhdno,inhsupnam,concat(inhdno,' - ',inhrem) inhdnox from inhtran 
                                    where inhlocsno='$te_inhlocsno' and inhtyp='$h_typ' and inhappucd=''";
                        if ($te_inhdptypsno != "") {
                            $sql_ref .= " and inhdptypsno='$te_inhdptypsno'";
                        }
                        $sql_ref .= " and (inhcanucd='' or inhcanucd is null) order by inhsno desc";  
                        $result  = mysqli_query(db_connection(), $sql_ref) or die("sql error inh select : <br>".mysqli_error(db_connection()));
                        $o->GenerateDropDownList("inhdno","$te_inhdno","inhdno","inhdnox","","");  
                    }
                    ?>
                </div>

                <div class="col-md-2 text-end mt-4">
                    <input type="submit" NAME='ok' id='ok' value="OK" class='btn btn-primary btn-sm px-4' onClick='return valkey();'>
                </div>
            </div>
        </div>

        <hr>

        <?php if($val_key=='y') echo "<div id='divhead'>\n"; else echo "<div id='divhead' style='display:none'>\n"; ?>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Date:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="inhddt" id="inhddt" value="<?php echo $te_inhddt?>" onkeypress="return enter(document.fn.inhsupnam);">
                        <button class="btn btn-outline-secondary" type="button" onclick="cal.popup();"><img src="img/cal.gif" width="16" height="16" alt="Pick Date"></button>
                    </div>
                    <SCRIPT LANGUAGE="JavaScript">var cal = new calendar2(document.fn.inhddt); cal.year_scroll = true; cal.time_comp = false;</SCRIPT>       
                </div>
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Supplier Name:</label>
                    <input type="text" class="form-control form-control-sm" name="inhsupnam" id="inhsupnam" onblur="this.value=this.value.toUpperCase();" value="<?php echo $te_inhsupnam; ?>" onkeypress="return enter(document.fn.inhval);">
                </div>
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Amount Paid:</label>
                    <INPUT TYPE="number" step="0.01" class="form-control form-control-sm" NAME="inhval" id="inhval" VALUE="<?php echo $te_inhval?>" onkeypress="return enter(document.fn.inhcoqsno);" onkeyup="calculateTotal()" onchange="calculateTotal()">
                </div>
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Payment Method:</label>
                    <?php   
                      $sqlquery= "select syssno,concat(sysrno,' - ',sysdes) syssys from sysmast where sysrtp=33 ORDER BY sysrno";            
                      $result  = mysqli_query(db_connection(), $sqlquery) or die("no records");
                      $o->GenerateDropDownListauto("inhcoqsno","$te_inhcoqsno","syssno","syssys",
                      "this.form.inhcoqsnoauto.value=this.options[this.selectedIndex].text;",
                      "return enter(document.fn.inhbank);");  
                    ?>
                    <input type="hidden" name="inhcoqsnoauto" id="inhcoqsnoauto" value="<?php echo $te_inhcoqsnoauto; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Bank (Chq):</label>
                    <INPUT TYPE="text" class="form-control form-control-sm" NAME="inhbank" id="inhbank" onblur='this.value=this.value.toUpperCase();' maxlength='50' VALUE="<?php echo $te_inhbank?>" onkeypress="return enter(document.fn.inhbranch);">
                </div>
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Bank Branch:</label>
                    <INPUT TYPE="text" class="form-control form-control-sm" NAME="inhbranch" id="inhbranch" onblur='this.value=this.value.toUpperCase();' maxlength='50' VALUE="<?php echo $te_inhbranch?>" onkeypress="return enter(document.fn.inhgensno);">
                </div>
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Bank Account:</label>
                    <?php
                    $sqlquery = "select gensno, gendes from g_genmast where genbnkprefix!='' order by gendes"; 
                    $result   = mysqli_query(db_connection(), $sqlquery) or die("Sql Error sqlquery gen:<br>".mysqli_error(db_connection()));
                    $o->GenerateDropDownListauto("inhgensno","$te_inhgensno","gensno","gendes",
                         "this.form.inhgensnoauto.value=this.options[this.selectedIndex].text;",
                         "return enter(document.fn.inhchqno)");
                    ?>
                    <input type="hidden" name="inhgensnoauto" id="inhgensnoauto" value="<?php echo $te_inhgensnoauto; ?>">
                </div>
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Chq No:</label>
                    <INPUT TYPE="text" class="form-control form-control-sm" NAME="inhchqno" id="inhchqno" onblur='this.value=this.value.toUpperCase();' maxlength='50' VALUE="<?php echo $te_inhchqno?>" onkeypress="return enter(document.fn.inhchqddt);">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class='form-label fw-bold'>Dated Date (Chq):</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="inhchqddt" id="inhchqddt" value="<?php echo $te_inhchqddt?>" onkeypress="return enter(document.fn.inhourref);">
                        <button class="btn btn-outline-secondary" type="button" onclick="cal2.popup();"><img src="img/cal.gif" width="16" height="16" alt="Pick Date"></button>
                    </div>
                    <SCRIPT LANGUAGE="JavaScript">var cal2 = new calendar2(document.fn.inhchqddt); cal2.year_scroll = true; cal2.time_comp = false;</SCRIPT>       
                </div>
                <div class="col-md-3">
                    <label class='form-label fw-bold'>RBF Invoice No:</label>
                    <INPUT TYPE="text" class="form-control form-control-sm" NAME="inhourref" id="inhourref" onblur='this.value=this.value.toUpperCase();' maxlength='50' VALUE="<?php echo $te_inhourref?>" onkeypress="return enter(document.fn.inhrem);">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Remarks:</label>
                    <textarea class="form-control form-control-sm" name='inhrem' id='inhrem' rows='1' onChange='this.value=this.value.toUpperCase();' onkeypress='return enter(document.fn.inlstksnoauto);'><?php echo $te_inhrem?></textarea>
                </div>
            </div>
        </div>

        <?php if($val_key=='y') echo "<div id='lineItemsSection'>\n"; else echo "<div id='lineItemsSection' style='display:none'>\n"; ?>
            <div class="card card-body bg-light mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLineItem();">
                        + Add Line
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover align-middle bg-white mb-2" id="lineItemsTable">   
                        <thead class="table-info text-center">
                            <tr>
                                <th>LNO</th>
                                <th>Account Code</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>    
                        <tbody id="lineItemsBody">
                            </tbody>
                        <tfoot class="table-info">
                            <tr>
                                <th colspan="2" class="text-start text-danger" id="totalMsg" style="font-size:0.75rem;"></th>
                                <th class="text-end">Total Value:</th>
                                <th id="totalVal" class="text-end fw-bold">0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <input type="hidden" NAME='hinlgensno' id='hinlgensno'>
            <input type="hidden" NAME='hinldes' id='hinldes'>
            <input type="hidden" NAME='hinlamt' id='hinlamt'>
            <input type='hidden' name='hinlsno' id='hinlsno'>
            <input type='hidden' id='cmdopt' name='cmdopt' value='<?php echo $te_cmdopt?>'>
            
            <div class="text-center mt-3">
                <input type="submit" NAME='save' id="saveBtn" value="<?php echo $te_savebutt?>" <?php echo $te_class ?> onClick='return save_click();'>
                <input type="reset" value="RESET" class='btn btn-secondary btn-sm'>
            </div>
        </div>

    </form>
    
    <hr>

    <div class="table-responsive">
        <table class='table table-bordered table-striped table-sm'>    
            <thead class="table-info">
                <tr>
                    <th>#</th>
                    <th>Doc #</th>
                    <th>Date</th>
                    <th>Delivery</th>
                    <th>Details</th>
                    <th>Sno</th>
                </tr>
            </thead>    
            <tbody id="lineItemsList">
            <?php
            $sql_query= "select inhsno,inhdno,inhddt,inhdlvddt,inhquotationno,inhmrqreqby,locloc,inhsupnam from inhtran left join locmast on inhlocsno=locsno /*left join supmast on inhsupsno=supsno*/ where inhtyp='01' and inhgrntyp='' order by inhdno desc limit 10";
            $result = mysqli_query(db_connection(), $sql_query)or die("Error:<br>".mysqli_error(db_connection()));
            $x=0;
            while ($fields = mysqli_fetch_array($result, MYSQLI_BOTH))
            {
             $te_inhsno    =  $fields['inhsno'];
             $te_inhdno    =  $fields['inhdno'];
             $te_inhddt    =  ytod($fields['inhddt']);
             $te_inhdlvddt =  ytod($fields['inhdlvddt']);
             $te_inhsupnam =  $fields['inhsupnam'];
             $te_locloc    =  $fields['locloc'];
             $te_inhquotationno    =  $fields['inhquotationno'];
             $te_inhmrqreqby       =  $fields['inhmrqreqby'];
             $x+=1;
             echo "<tr>";
             echo "<th class='text-end'>$x.</th>";
             echo "<td>$te_locloc-$te_inhdno</td>";      
             echo "<td>$te_inhddt</td>";
             echo "<td>$te_inhdlvddt</td>";
             echo "<td>$te_inhsupnam $te_inhquotationno $te_inhmrqreqby </td>";       
             echo "<td>$te_inhsno</td>";       
             echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<?php
function next_auto_increment($tablename)
{
 $next_increment = 0;
 $qShowStatus    = "SHOW TABLE STATUS LIKE '$tablename'";
 $qShowStatusResult = mysqli_query(db_connection(), $qShowStatus) or die ( "Query failed: " . mysqli_error(db_connection()) . "  " . qShowStatus );
 while ($row = mysqli_fetch_assoc($qShowStatusResult))
 {
  $next_increment = $row['Auto_increment'];
 }
 mysqli_free_result($qShowStatusResult);
 return $next_increment;
}
            
Class HtmlFormsObjects
{
  function GenerateDropDownListauto($HtmlObjectName,$HtmlObjectDefaultValue,$DBFieldToValue,$DBFieldToDescription,$action,$onkeyp)
  { 
    global $result;
    echo "<select class='form-select form-select-sm' id='$HtmlObjectName' name='$HtmlObjectName' onChange='$action' Onkeypress='$onkeyp'> \n";
    mysqli_data_seek($result,0);
    echo "<option></option> \n";
    while ($rec = mysqli_fetch_array($result))
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
    echo"<option></option>";
    mysqli_data_seek($result,0);
    while ($rec = mysqli_fetch_array($result))
    {
     if ($HtmlObjectDefaultValue == $rec["$DBFieldToValue"])
      echo "<option selected value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
     else
      echo "<option value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
    }
    echo "</select> \n";    
  }      
}

function next_dno($te_inhtyp,$te_inhlocsno,$te_cmdopt,$te_inhdptypsno='')
{
 if($te_inhtyp=="") { return ""; }   
 $te_facprefix='';   
 $sql_fac = "SELECT locloc FROM locmast where locsno='1'";
 $result_fac = mysqli_query(db_connection(), $sql_fac) or die('SQL Error sql_facm');
    
 if(mysqli_num_rows($result_fac)) {
  $fields_fac = mysqli_fetch_array($result_fac, MYSQLI_BOTH);
  $te_locloc = $fields_fac['locloc'];
 }
 $te_locloc=trim($te_locloc);
 if($te_locloc=="") { return ""; }
 $pfxlen=1;
 if($te_cmdopt=='add')
 {  
  $sql_dno  = "SELECT inhdno FROM inhtran where inhlocsno='$te_inhlocsno' and inhtyp='$te_inhtyp' order by inhdno desc limit 1";
  $result_dno = mysqli_query(db_connection(), $sql_dno) or die('SQL Error sql_dno');
  $sql_dno  = "SELECT inhdno FROM inhtran where 1 and inhtyp='$te_inhtyp' order by inhdno desc limit 1";
  $result_dno = mysqli_query(db_connection(), $sql_dno) or die('SQL Error sql_dno');
    
  if(mysqli_num_rows($result_dno)) {
   $fields_dno = mysqli_fetch_array($result_dno, MYSQLI_BOTH);
   $te_inhdno1 = substr($fields_dno['inhdno'],$pfxlen);
   $te_inhdno1 = intval($te_inhdno1) + 1;
   $te_inhdno1 = str_pad($te_inhdno1,'7','0',STR_PAD_LEFT);  
   $te_inhdno = "R".$te_inhdno1;
  } else  {
   $te_inhdno= 'R'."1000001";
  } 
  return $te_inhdno;
 }
 if($te_cmdopt=='ame' or $te_cmdopt=='del')
 {
  $sql_dno = "select inhdno,concat(inhdno,' - ',inhrem) inhrpritem from inhtran 
              /*left join supmast on inhsupsno=supsno*/ 
              where inhlocsno='$te_inhlocsno' and inhtyp='$te_inhtyp' and inhappucd='' ";
  if($te_inhdptypsno != '') {
      $sql_dno .= " and inhdptypsno='$te_inhdptypsno' ";
  }
  $sql_dno .= " order by inhrpritem desc";     
  $result_dno = mysqli_query(db_connection(), $sql_dno) or die("no Documents ame  <br>".mysqli_error(db_connection()));
  $te_return  = chr(25).chr(25)."####"; 
  while($fields_dno = mysqli_fetch_array($result_dno,MYSQLI_BOTH)) {
   $te_inhdno    = $fields_dno['inhdno'];
   $te_inhrpritem    = $fields_dno['inhrpritem'];
   $te_return .= "$te_inhdno"."  "."$te_inhrpritem"."####";    
  }
  return substr($te_return,0,-4);
 }
}

function load_gen()
{
 $sql = "select gensno, gendes from g_genmast where genctl!='1005' order by gendes";
 $result = mysqli_query(db_connection(), $sql) or die('SQL Error load_gen');
 $te_return = '  ####'; 
 while($fields = mysqli_fetch_array($result, MYSQLI_BOTH)) {
  $te_gensno = $fields['gensno'];
  $te_gendes = $fields['gendes'];
  $te_return .= "$te_gensno" . "  " . "$te_gendes" . "####";
 }
 return substr($te_return, 0, -4);
}

?>