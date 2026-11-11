<?php
  $config_use_sessions = TRUE;  
  session_start();

  if (!$_SESSION["validated"]) 
  { 
    header("Location: login.php");
    return;
  }
//-------------------------Check User Rights [BEGIN]-------------------------------------  

$te_usdusd = $_SESSION["susdusd"];
echo "$te_usdusd<br>";

  $te_usropt="usryn";
  $te_prgnam = $_SERVER['PHP_SELF'];
  if ($_SESSION["susdadmin"] != "Y") 
  { 
   //include "a_usrval.php";
  }
//-------------------------Check User Rights [CLOSE]------------------------------------- 
?>
<HTML>
  <HEAD>
    <TITLE>Direct Payments Request</TITLE> 
     <link href="css/ent.css" rel="stylesheet" type="text/css"  />
     <script src="js/ent_dashboard_dpmm.js"></script>

     <STYLE></STYLE>

     <SCRIPT LANGUAGE="JavaScript">
       // Tab Name | URL | * (Default Selected Tab)
       var tabs = new Array
       (
        new Array(""),

        new Array("(1b)ADD Payment Request|dpmenter_bs.php?cmdopt=add"),
        new Array("(2)AMEND-Payment Request|dpmenter_bs.php?cmdopt=ame"),
        new Array("(3)MD Approvals|md_approvals.php"),
        
        new Array("(4)PRINT|dpmprt_pp.php"),
        //new Array("(5)Cancel POD|inhcan_po.php"),
        
        new Array("(7)INQ-BS|dp_inq02.php"),
        new Array("(8)INQ-ALL_DOC|inhinqtrn.php"),
        new Array("(11)List-2|dplist_01.php")

       );
       te_title="Direct Payments (30)";
    </SCRIPT>
  </HEAD>
  <BODY onload="tabOnLoad()">
    <form>
        <DIV ID=divTabStrip></DIV>
        <DIV ID=divTabFrame></DIV>
    </form>
  </BODY>
</HTML>
