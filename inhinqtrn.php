<?php
 $config_use_sessions = TRUE;
 session_start();
 if(!$_SESSION["validated"])
 {
   header("Location: login.php");
   return;
 }


//-------------------------Check User Rights [BEGIN]-------------------------------------

  /*
$te_usropt="usryn";
$te_prgnam = $_SERVER['PHP_SELF'];
if ($_SESSION["susdadmin"] != "Y")
{
 include "a_usrval.php";
}
 */
//-------------------------Check User Rights [CLOSE]-------------------------------------
$o = new HtmlFormsObjects;

$te_usdusd	    = $_SESSION["susdusd"];
$te_grlucdnew	= $te_usdusd;
$te_grlenttime  = strftime('%H:%M:%S');
$te_grlentddt	= strftime("%Y-%m-%d");

$te_sDatabase	= $_SESSION["sDatabase"];
$te_sHostname	= $_SESSION["sHostname"];
$te_sPort		= $_SESSION["sPort"];
$te_sUsername	= $_SESSION["sUsername"];
$te_sPassword	= $_SESSION["sPassword"];
$te_slocsno	    = $_SESSION["slocsno"];
$te_os          = $_SESSION["sOS"];

$rConn = mysql_connect("$te_sHostname:$te_sPort", $te_sUsername, $te_sPassword) or
					die("could not connect : ".mysql_error());
$rDB 	 = mysql_select_db($te_sDatabase,$rConn) or die("Could not select database");

require("sajax.php");
//-------------------sajax_init()-------------------//
sajax_init();
//$sajax_debug_mode = 1;
sajax_export("load_dno");
sajax_export("load_dnodd");  //dropdown
sajax_handle_client_request();
//--------------------------------------------------//

$te_inhddt 	= strftime("%d/%m/%Y");
$te_inhcomsno	= "";
$te_inhtyp	= "";  //
$te_inhdno	= "";

$te_sysddt = strftime("%d/%m/%Y");
$te_sysddt = "var te_sysddt = '".$te_sysddt."';\n";
$te_ok='n';
if (isset($_GET['inhsno']))
{
 $te_inhsno =$_GET['inhsno'];
 $te_fromcanlist =$_GET['fromcanlist'];

 $te_get='y';$val_key='y';
 $sql_ = "select inhsno,inhdno from inhtran where inhsno='$te_inhsno'";
 //echo "$sql_<br>";
 $result_ = mysql_query($sql_) or die("sql error inh");
 if(mysql_num_rows($result_))
 {
  $fields_ = mysql_fetch_array($result_, MYSQL_BOTH);
  $te_inhsno = $fields_['inhsno'];
  $te_inhdno = $fields_['inhdno'];
  $te_ok='y';
 }
}
else
{
 $te_get='n';
}

if(isset($_POST['ok']))
{
 $te_inhdno = $_POST['inhdno'];
 $te_inhsno = $_POST['inhsno'];
 $te_syssno = $_POST['syssno'];
 $te_syssnoauto = $_POST['syssnoauto'];
}

?>

<HTML>
<HEAD>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/bootstrap5.min.css">
<TITLE>Inquire Inventory Transactions</TITLE>

<link rel='stylesheet' href='css/mystyle.css' />

<style type='text/css'>
body,table
{
  font-size:10px;
  font-family: Times New Roman;
}

/* class freeze_col is used to freeze the headers of the table. Only the styles position, top & z-index is used. */
.freeze_col{
	position: sticky;
    top: 0;
    z-index: 1;

	height: 30px;
	background-color: #ff8c00;
}
</style>
<script type="text/JavaScript" src="js/formvalidation.js"></script>
<script language="JavaScript" src="js/calendar2.js"></script>
<SCRIPT LANGUAGE="JavaScript" SRC="js/autocomplete.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<?php
 sajax_show_javascript();
?>

var isIE = (navigator.appName == "Microsoft Internet Explorer");
var isIEWin = (isIE && navigator.userAgent.indexOf("Win") != -1);
var isIEMac = (isIE && navigator.userAgent.indexOf("Mac") != -1);

function window_onload()
{
  document.inhinqtrn.inhdno.focus();
}


//-----------------------------------------------------------------
function enter(nextfield)
{
 if(window.event && window.event.keyCode == 13)
 {
   nextfield.focus();
   return false;
 }
 else
  return true;
}


//----------------------------------valkey()--------------------------//
function valkey()
{

 var fld =document.inhinqtrn.inhsno
 if (chkBlank(fld) == false)
 {
  fld.focus();
  return false;
 }
  return true;
}


//--------------------------------- [ load_dno] ---------------------------------//

function load_dno()
{
 var inhdno=document.getElementById('inhdno').value
 //alert(inhdno)
 x_load_dno(inhdno,load_dno_x);
}

function load_dno_x(str_dno)
{
 //alert (str_dno)
 //document.getElementById('debug').innerHTML = str_dno
 var frm = document.inhinqtrn
 var the_array = str_dno.split("####");
 document.inhinqtrn.inhsno.options.length = the_array.length
 for(x=0;x<the_array.length;x++)
 {
  var lines = the_array[x].split("");
  document.inhinqtrn.inhsno.options[x].value = lines[0]  //lines[0]
  document.inhinqtrn.inhsno.options[x].text = lines[1]
 }

}

function load_dnodd()
{
 var te_syssno = document.inhinqtrn.syssno.value*1
 if(te_syssno>0)
 {
  x_load_dnodd(te_syssno,load_dnodd_x);
 }
}

function load_dnodd_x(str_dno)
{
 //alert (str_dno)
 //document.getElementById('debug').innerHTML = str_dno
 var frm = document.inhinqtrn
 var the_array = str_dno.split("####");
 document.inhinqtrn.inhsno.options.length = the_array.length
 for(x=0;x<the_array.length;x++)
 {
  var lines = the_array[x].split("");
  document.inhinqtrn.inhsno.options[x].value = lines[0]  //lines[0]
  document.inhinqtrn.inhsno.options[x].text = lines[1]
 }

}



function popwindowdes()
{
  var theURL = 'g_inhinqtrndes.php';
  var width  = 600;
  var height = 300;
  newWindow = window.open(theURL,'newWindow','toolbar=no,menubar=no,resizable=no,scrollbars=no,status=no,location=no,width='+width+',height='+height);
  newWindow.focus();
}

function popfpl(te_inlsno)
{
 var URLpara = "";
 URLpara    += 'inlsno=' + te_inlsno
 var theURL  = 'inq-inlfpl.php?' + URLpara;
 var width   = 800;
 var height  = 600;
 newWindow   = window.open(theURL,'newWindow','toolbar=no,menubar=no,resizable=yes,scrollbars=yes,status=yes,location=no,width='+width+',height='+height);
 newWindow.focus();
}



</SCRIPT>
</HEAD>


<BODY onLoad='window_onload();' bgcolor=''>
<label id='debug' name='debug'></label>
<FORM NAME="inhinqtrn" METHOD="POST" ACTION="inhinqtrn.php" RUNAT='SERVER'>
  <div name="divkey" class='myLayersClassv'>


<table align="center" width='100%' border='0' style='position:relative'>
<tr>

<td width="3%" class='lbl' class='lbl' align='right'>Type:</td>

<td width="15%" >
<?php

 $w=260;
 $wpx =$w.'px';
 $t ="7px";
 $sqlquery= "select syssno,concat(sysdes,' - ',sysrno) sysdes from sysmast where sysrtp='8' order by sysdes";
 $result = mysql_query($sqlquery,$rConn) or die("no records");

 $o->GenerateDropDownListauto("syssno","$te_syssno","syssno","sysdes",
     "this.form.syssnoauto.value=this.options[this.selectedIndex].text;load_dnodd();hide_div();",
     "return enter(document.forms[0].locsnoauto)","$wpx","$t");
 $w -=23;
 $wpx=$w.'px';
?>

 <INPUT TYPE="text" NAME="syssnoauto" id="syssnoauto" VALUE="<?=$te_syssnoauto?>"
  ONKEYUP="autoComplete(this,this.form.syssno,'text',true);load_dnodd();hide_div();"
  style="position:absolute;top:<?=$t?>;z-index:10;width:<?=$wpx?>;"
  onkeypress="return enter(document.forms[0].locsnoauto)" onBlur=""></INPUT></td>

<th class='lbl' width='10%'>#:</th>

     <td width='12%'>
     <INPUT TYPE="text" NAME="inhdno" id="inhdno" size="30" maxlength='30' VALUE="<?=$te_inhdno?>"
      onblur="load_dno();"
      onkeypress="return enter(document.inhinqtrn.ok)">
</td>


	 <th class='lbl' width='20%'>Select:</th>
     <td width='15%'>
	  <?php

       $sqlquery = "select inhsno,concat(inhdno,'-',inhddt) inh from inhtran
                left join commast on inhcomsno=comsno
                left join locmast on inhlocsno=locsno
			    where inhdno='$te_inhdno' order by inhdno";

       $sqlquery = "select inhsno,concat(inhdno,'-',typ.sysdes,'-',inhddt,'-',locloc) inh from inhtran
                left join commast on inhcomsno=comsno
                left join locmast on inhlocsno=locsno
                left join sysmast typ on inhtyp=typ.sysrno and typ.sysrtp=8
			    where inhdno='$te_inhdno' order by inhdno";

	   $result = mysql_query($sqlquery) or die("Error inh");
	   $o->GenerateDropDownList("inhsno","$te_inhsno","inhsno","inh","","return enter(document.inhinqtrn.ok);");
	  ?>
     </td>

     </tr>
     </table>
     <table align="center" width='100%' border='0' style='position:relative'>
     <tr>
     <td align='right'>
      <input type='submit' value='CHECK' name='ok' onClick='return valkey();' class='submit'></input>

      <?php
      if($_SESSION["sLG"]=='MD')
      {
       echo "<input type='button' value='DASHBD' onclick=\"window.location='dashboard_md.php'\" class='submit'></input>";
      }
      elseif ($_SESSION["sLG"]=='MAN')
      {
       echo "<input type='button' value='DASHBD' onclick=\"window.location='dashboard_man.php'\" class='submit'></input>";
      }
      else
      {
       //echo "<input type='button' value='MENU' onclick=\"window.location='menu.php'\" class='submit'></input>";
      }
      ?>




      <!--input type="button" name="reload" value="RELOAD" OnClick ="window.location='inhinqtrn.php';" class='submit'></input> -->
     </td>
    </tr>
   </table>

     </div>
  <br>

<?php


if(isset($_POST['ok']))
{
 $te_ok='y';
 $te_inhsno = $_POST['inhsno'];
 $te_inhdno = $_POST['inhdno'];
}

if($te_ok=='y')
{
 $sql_ok = "select * from inhtran where inhsno='$te_inhsno'";
 //echo "$sql_ok<br>";
 $result_ok = mysql_query($sql_ok) or die("sql error inh");
 if(!mysql_num_rows($result_ok))
 {
  die ("Document Not Found");
 }
 $val_key='y';


 $fields = mysql_fetch_array($result_ok, MYSQL_BOTH);
 $te_inhsno    = $fields['inhsno'];

 echo "<DIV NAME='divkey1' id='divkey1' CLASS='myLayersClassv'>";

 $query_h = "select inhsno,inhtyp,inhlocsno,inhdno,inhddt,inhdlvddt,inhsupsno,supnam,inhsupinvno,inhsupinvddt,
                inhfsl,cusnam,
                loc.locnam loc, locfr.locnam locfr, locto.locnam locto, inhrlono,
                inhbatsno,batbat,batddt,
                inhval,inhcashamt,inhchqamt,inhcreditamt,
                inhchqno,inhchqddt,inhsupnam,
                inhvatpcn,inhvatamt,inhsvatamt,
                inhnbtamt,
                inhdispcn,inhdisamt,
                inhdes,inhourref,
                inhpodcmplflg,
                inhforcerecno,inhgrnsno,
                inhprdsno,
	            inhrem,inhdispsno,inhisssno,
	            inhucdnew,inhentddt,inhenttime,inhucdame,inhameddt,inhametime,
	            inhcanucd,inhcanddt,inhcantime,inhcanreason,
	            inhappucd,inhappddt,inhapptime,
	            typ.sysdes typ,grhgrh,biltyp.sysdes biltyp,biltyp.syssno biltypsno,
	            coq.sysrno coq,
	            genctlsub,gendes,drvnam,
	            inhwbserialno,inhvehno,
	            inhourwgt,inhsupwgt,inhqty,
	            usdnam,
	            extr.sysdes extr,inhrlono,
                inhdlvtypsno,dlvtyp.sysdes dlvtyp,

                frvlc.vlcvlc frvlc, tovlc.vlcvlc tovlc,
	            inhpodsno,inhporeqsno,
	            inhlenborsno,lendbor.sysdes lendbor,
	            inhpotypsno,potyp.sysrno potyp,inhsorsno,
	            inhcagsno,cagcag,
	            inhvacsno,vacddt,vacevent
	            from inhtran
                   LEFT JOIN supmast ON inhsupsno=supsno
                   LEFT JOIN cusmast ON inhcussno=cussno
                   LEFT JOIN locmast ON inhlocsno=locsno
                   left join locmast loc on inhlocsno=loc.locsno
                   left join locmast locto on inhtolocsno=locto.locsno
                   left join locmast locfr on inhfrlocsno=locfr.locsno

                   left join commast on inhcomsno=comsno
                   left join g_grhmast on inhgrhsno=grhsno
                   left join g_genmast on inhgensno=gensno
                   LEFT JOIN drvmast   on inhdrvsno=drvsno
                   LEFT JOIN usdmast   on inhrepusdsno=usdsno
                   left join sysmast typ on inhtyp=typ.sysrno and typ.sysrtp=8
                   left join sysmast extr on inhextrfsno=extr.sysrno
                   left join sysmast dlvtyp on inhdlvtypsno = dlvtyp.syssno
                   left join sysmast coq on inhcoqsno = coq.syssno
                   left join sysmast lendbor on inhlenborsno = lendbor.syssno
                   left join sysmast potyp   on inhpotypsno = potyp.syssno
                   left join sysmast biltyp  on inhsobbilltypsno = biltyp.syssno


                   left join batmast on inhbatsno = batsno

                   left join cagmast on inhcagsno = cagsno
                   left join vacmast on inhvacsno = vacsno

                   left join vlcmast frvlc on inhfrvlcsno = frvlc.vlcsno
                   left join vlcmast tovlc on inhtovlcsno = tovlc.vlcsno
				where inhsno='$te_inhsno' /* and (inhcanucd='' or inhcanucd is null) */";

 //echo "$query_h<br>";
 $result_h     = mysql_query($query_h) or die("sql error inh..!".mysql_error());
 $fieldh       = mysql_fetch_array($result_h, MYSQL_BOTH);
 $te_inhsno    = $fieldh['inhsno'];
 $te_inhtyp    = $fieldh['inhtyp'];
 $te_inhlocsno = $fieldh['inhlocsno'];

 $te_inhdno    = $fieldh['inhdno'];
 $te_inhddt    = $fieldh['inhddt'];
 $te_inhdlvddt = $fieldh['inhdlvddt'];
 $te_typ    = $fieldh['typ'];
 $te_biltyp = $fieldh['biltyp'];
 $te_biltypsno = $fieldh['biltypsno'];

 $te_loc    = $fieldh['loc'];

 $te_locfr    = $fieldh['locfr'];
 $te_locto    = $fieldh['locto'];
 $te_inhrlono = $fieldh['inhrlono'];
 $te_inhsupnam = $fieldh['inhsupnam'];

 $te_inhqty    = $fieldh['inhqty'];
 $te_inhprdsno = $fieldh['inhprdsno'];



 $te_frvlc = $fieldh['frvlc'];
 $te_tovlc = $fieldh['tovlc'];

 $te_inhsupsno   = $fieldh['inhsupsno'];
 $te_inhsupinvno = $fieldh['inhsupinvno'];
 $te_inhsupinvddt= $fieldh['inhsupinvddt'];

 $te_inhdispsno  = $fieldh['inhdispsno'];
 $te_inhisssno   = $fieldh['inhisssno'];

 $te_supnam      = $fieldh['supnam'];
 $te_cusnam      = $fieldh['cusnam'];

 $te_inhfarsno   = $fieldh['inhfarsno'];
 $te_farfar      = $fieldh['farfar'].' - '.$fieldh['farnam'];
 $te_grhgrh      = $fieldh['grhgrh'];
 $te_genctlsub   = $fieldh['genctlsub'];
 $te_gendes      = $fieldh['gendes'];

 $te_drvnam      = $fieldh['drvnam'];
 $te_inhwbserialno = $fieldh['inhwbserialno'];
 $te_inhvehno      = $fieldh['inhvehno'];
 $te_inhourwgt     = $fieldh['inhourwgt'];
 $te_inhsupwgt     = $fieldh['inhsupwgt'];




 $te_usdnam      = $fieldh['usdnam'];

 $te_inhpodsno   = $fieldh['inhpodsno'];
 $te_inhporeqsno = $fieldh['inhporeqsno'];
 $te_inhgrnsno   = $fieldh['inhgrnsno'];
 $te_inhsorsno   = $fieldh['inhsorsno'];


 $te_dlvtyp       = $fieldh['dlvtyp'];
 $te_inhdlvtypsno = $fieldh['inhdlvtypsno'];

 $te_coq          = $fieldh['coq'];
 $te_lendbor      = $fieldh['lendbor'];

 $te_inhpotypsno  = $fieldh['inhpotypsno'];
 $te_potyp        = $fieldh['potyp'];

 $te_extr        = $fieldh['extr'];
 $te_inhdes      = $fieldh['inhdes'];
 $te_inhrem      = $fieldh['inhrem'];
 $te_inhorref    = $fieldh['inhourref'];
 $te_inhforcerecno = $fieldh['inhforcerecno'];

 $te_inhval      = $fieldh['inhval'];
 $te_inhcashamt  = $fieldh['inhcashamt'];
 $te_inhchqamt   = $fieldh['inhchqamt'];
 $te_inhcreditamt  = $fieldh['inhcreditamt'];

 $te_inhchqno   = $fieldh['inhchqno'];
 $te_inhchqddt  = $fieldh['inhchqddt'];

 $te_batbat     = $fieldh['batbat'];
 $te_batddt     = $fieldh['batddt'];
 $te_inhpodcmplflg = $fieldh['inhpodcmplflg'];

 $te_inhcagsno  = $fieldh['inhcagsno'];
 $te_cagcag     = $fieldh['cagcag'];
 $te_inhvacsno  = $fieldh['inhvacsno'];
 $te_vacddt     = $fieldh['vacddt'];
 $te_vacevent   = $fieldh['vacevent'];

 $te_inhdispcn   = $fieldh['inhdispcn'];
 $te_inhdisamt   = $fieldh['inhdisamt'];
 $te_inhvatpcn   = $fieldh['inhvatpcn'];
 $te_inhvatamt   = $fieldh['inhvatamt'];

 $te_inhsvatamt  = $fieldh['inhsvatamt'];
 $te_inhnbtamt   = $fieldh['inhnbtamt'];

 $te_inhucdnew = $fieldh['inhucdnew']."-".$fieldh['inhentddt']."-".$fieldh['inhenttime'];
 $te_inhucdame = $fieldh['inhucdame']."-".$fieldh['inhameddt']."-".$fieldh['inhametime'];
 $te_inhucdcan = $fieldh['inhcanucd']."-".$fieldh['inhcanddt']."-".$fieldh['inhcantime'].' '.$fieldh['inhcanreason'];

 $te_inhappucd = $fieldh['inhappucd']."-".$fieldh['inhappddt']."-".$fieldh['inhapptime'];
 if($te_rtcdno!='')
 {
  $te_inhdes .= 'Return #:'.$te_rtcdno;
 }
 if($te_issdno!='')
 {
  $te_inhdes .= 'Issue #:'.$te_issdno;
 }

 echo "<TABLE ALIGN='CENTER' WIDTH='100%' border='0'>";
 echo "<tr>";
 echo "<td class='lbl'>Doc No: </td><td>$te_inhdno type:$te_typ sno:$te_inhsno</td>";
 echo "<td class='lbl'>Date:</td>   <td>$te_inhddt</td>";
 $sql_ = "select locnam from locmast
	             where locsno='$te_inhlocsno'";
 //echo "$sql_ <br>";
 $result_     = mysql_query($sql_) or die("sql error inh_ ..!".mysql_error());
 $fields_     = mysql_fetch_array($result_, MYSQL_BOTH);
 $te_inhlocnam = $fields_['locnam'];

 echo "<td class='lbl'>Location:</td>   <td>$te_inhlocnam</td>";
 echo "</tr><tr>";
 echo "<td class='lbl'>Type:</td>   <td>$te_inhtyp</td>";
 echo "<td class='lbl'>Dlv Date:</td>   <td>$te_inhdlvddt</td>";

 echo "</tr><tr>";
 if($te_inhtyp=='21')
 {
  echo "<td class='lbl'>Delivery Type:</td> <td>$te_dlvtyp sno:$te_inhdlvtypsno</td>";
  echo "</tr><tr>";
 }


 echo "<td class='lbl'>Supplier:</td>   <td>$te_supnam $te_inhsupnam Sno: $te_inhsupsno</td>";
 echo "<td class='lbl'>Supplier Invoice:</td><td>$te_inhsupinvno</td>";
 echo "<td class='lbl'>Supplier Invoice Date:</td><td>$te_inhsupinvddt</td>";

 echo "</tr>";
 if($te_inhpodsno>0)
 {
  $sql_ = "select inhdno,inhddt
	            from inhtran
				where inhsno='$te_inhpodsno'";

  //echo "$sql_ <br>";
  $result_     = mysql_query($sql_) or die("sql error inh_ ..!".mysql_error());
  $fields_     = mysql_fetch_array($result_, MYSQL_BOTH);

  $te_poddno   = $fields_['inhdno'];
  $te_podddt   = ytod($fields_['inhddt']);
  echo "<tr>";
  echo "<td class='lbl'>PO #: </td><td>$te_poddno</td>";
  echo "<td class='lbl'>PO Date: </td><td>$te_podddt</td>";
  echo "</tr>";
 }

 echo "<tr>";
 echo "<td class='lbl'>Lend/Borrow: </td><td>$te_lendbor</td>";
 echo "</tr>";

 echo "<tr>";
 echo "<td class='lbl'>Qty: </td><td>".number_format($te_inhqty,2)."</td>";
 echo "</tr>";

 if($te_inhprdsno>0)
 {
  $sql_ = "select inhdno,inhddt
	            from inhtran
				where inhsno='$te_inhprdsno'";

  //echo "$sql_ <br>";
  $result_     = mysql_query($sql_) or die("sql error inh_ ..!".mysql_error());
  $fields_     = mysql_fetch_array($result_, MYSQL_BOTH);

  $te_prddno   = $fields_['inhdno'];
  $te_prdddt   = ytod($fields_['inhddt']);
  echo "<tr>";
  echo "<td class='lbl'>Formula Prod #: </td><td>$te_prddno</td>";
  echo "<td class='lbl'>Formula Prod Date: </td><td>$te_prdddt</td>";
  echo "</tr>";
 }

 if($te_inhporeqsno>0)
 {
  $sql_ = "select inhdno,inhddt
	            from inhtran
				where inhsno='$te_inhporeqsno'";

  //echo "$sql_ <br>";
  $result_     = mysql_query($sql_) or die("sql error inh_ ..!".mysql_error());
  $fields_     = mysql_fetch_array($result_, MYSQL_BOTH);

  $te_poreqdno   = $fields_['inhdno'];
  $te_poreqddt   = ytod($fields_['inhddt']);
  echo "<tr>";
  echo "<td class='lbl'>PO Req #: </td><td>$te_poreqdno</td>";
  echo "<td class='lbl'>PO Req Date: </td><td>$te_poreqddt</td>";
  echo "</tr>";
 }

 echo "<tr>";

 echo "<td class='lbl'>Customer/Oth:</td>  <td>$te_cuscus $te_cusnam</td>";

 echo "</tr><tr>";
 echo "<td class='lbl'>Cash:</td>  <td>".number_format($te_inhcashamt,2)."</td>";
 echo "<td class='lbl'>Chq:</td>  <td>".number_format($te_inhchqamt,2)."</td>";
 echo "<td class='lbl'>Credit:</td>  <td>".number_format($te_inhcreditamt,2)."</td>";

 echo "</tr><tr>";
 echo "<td>Chq No:</td><td>$te_inhchqno</td>";
 echo "<td>Chq Date:</td><td>$te_inhchqddt</td>";
 echo "<td class='lbl'>Value:</td><td>".number_format($te_inhval,2)."</td>";

 echo "</tr><tr>";

 echo "<td class='lbl'>From Location:</td>  <td>$te_frvlc</td>";
 echo "<td class='lbl'>To Location:</td>  <td>$te_tovlc</td>";

 echo "</tr><tr>";

 echo "<td class='lbl'>Rep:</td>  <td>$te_usdnam</td>";
 echo "<td class='lbl'>:</td>  <td></td>";

 echo "</tr><tr>";
 echo "<td class='lbl'>-----:</td>  <td> </td>";
 echo "<td class='lbl'>Schedule(Batch):</td>  <td>$te_batbat $te_batddt</td>";

 echo "</tr><tr>";
 echo "<td class='lbl'>Driver:</td>  <td>$te_drvnam</td>";

 echo "</tr><tr>";
 echo "<td class='lbl'>Weigh Bridge Serial:</td>  <td>$te_inhwbserialno</td>";
 echo "<td class='lbl'>Vehicle #:</td>  <td>$te_inhvehno</td>";

 echo "</tr><tr>";
 echo "<td class='lbl'>RBF Wgt:</td>  <td>$te_inhourwgt</td>";
 echo "<td class='lbl'>Supplier Wgt:</td>  <td>$te_inhsupwgt</td>";




 echo "</tr><tr>";
 echo "<td class='lbl'>Vat %:</td> <td>".number_format($te_inhvatpcn,2)."</td>";
 echo "<td class='lbl'>Vat Amt:</td> <td>".number_format($te_inhvatamt,2)."</td>";
 echo "<td class='lbl'>Nbt Amt:</td><td>".number_format($te_inhnbtamt,2)."</td>";
 echo "<td></td> <td></td>";
 echo "</tr><tr>";

 echo "<td class='lbl'>SVat Amt:</td> <td>".number_format($te_inhsvatamt,2)."</td>";
 echo "<td class='lbl'>:</td><td></td>";
 echo "<td></td> <td></td>";
 echo "</tr><tr>";

 echo "<td class='lbl'>Dis.Pcn:</td> <td>".number_format($te_inhdispcn,2)."</td>";
 echo "<td class='lbl'>Dis.Amt:</td><td>".number_format($te_inhdisamt,2)."</td>";
 echo "<td></td> <td></td>";
 echo "</tr><tr>";

 echo "<td class='lbl'>Our Ref:</td><td>$te_inhourref</td>";
 echo "<td class='lbl'>Dispatch #:</td><td>$te_dispdno</td>";
 echo "<td></td> <td></td>";
 echo "</tr><tr>";

 $sql_ = "select locnam from cagmast
                 left join locmast on caglocsno
				where cagsno='$te_inhcagsno'";
 //echo "$sql_ <br>";
 $result_     = mysql_query($sql_) or die("sql error inh_ ..!".mysql_error());
 $fields_     = mysql_fetch_array($result_, MYSQL_BOTH);
 $te_caglocnam = $fields_['locnam'];


 echo "<td class='lbl'>Cage:</td><td>$te_cagcag - $te_caglocnam</td>";
 echo "<td class='lbl'>Vaccine:</td><td>$te_vacddt $te_vacevent</td>";
 echo "<td></td> <td></td>";
 echo "</tr><tr>";

 echo "<td class='lbl'>Remarks:</td> <td>$te_inhrem $te_inhdes</td>";
 echo "<td class='lbl'>Accounts Ref:</td><td>$te_grhgrh</td>";

 echo "</tr><tr>";

 echo "<td class='lbl'>:</td> <td>$te_inhforcerecno</td>";
 echo "<td class='lbl'>:</td><td></td>";

 echo "</tr><tr>";

 if($te_inhtyp=='21' or $te_gendes!='')
 {
  echo "<td class='lbl'>inhpodcmplflg:</td> <td>$te_inhpodcmplflg</td>";
  echo "<td class='lbl'>GL Account:</td><td>$te_genctlsub - $te_gendes</td>";

  echo "</tr><tr>";

 }


 echo "<td class='lbl'>Po Type:</td> <td>$te_potyp</td>";
 echo "<td class='lbl'>GL Account:</td><td>$te_gendes</td>";
 echo "</tr><tr>";

 echo "<td class='lbl'>SOB Bill Type:</td> <td>$te_biltyp Sno:$te_biltypsno</td>";
 echo "</tr><tr>";


 $sql_ = "select inhdno from inhtran
				where inhsno='$te_inhsorsno'";

 //echo "$sql_ <br>";
 $result_     = mysql_query($sql_) or die("sql error inh_ ..!".mysql_error());
 $fields_     = mysql_fetch_array($result_, MYSQL_BOTH);

 $te_sordno   = $fields_['inhdno'];


 echo "<td class='lbl'>Service Order:</td> <td>$te_sordno</td>";
 echo "<td class='lbl'>:</td><td></td>";
 echo "</tr><tr>";



 echo "<td class='lbl'>Entry:</td>   <td>$te_inhucdnew</td>";
 echo "<td class='lbl'>Amend:</td>   <td>$te_inhucdame</td>";
 echo "</tr><tr>";
 echo "<td class='lbl'>Cancel:</td>   <td>$te_inhucdcan</td>";
 echo "<td class='lbl'>Print:</td>   <td>$te_inhucdprt</td>";

 echo "</tr><tr>";
 echo "<td class='lbl'>Approved:</td>   <td>$te_inhappucd</td>";

 echo "</table>";

 echo "</DIV><br>";
 echo "<DIV NAME='divkey2' CLASS='myLayersClassv'>";

 $query_l = "select inlsno,inluntsno,unt.sysrno unt,
                inlstksno,stkstk,stkdes,untstk.sysrno untstk,stkrm,
                inlisssno,
                inldispcn,inldisamt,inlvatamt,
                inlreqpodsno,inlbatsno,
                inlqty,inlfmlqty,inlwgt,inlfreeqty,inlcpr,inlavgcpr,
                inlorgqty,
                inlspr,inlval,inlamt,
                inldes,inlg2shortdes,inlposhortg2des,
                inlrettypsno,rettyp.sysdes rettyp,

                inlpodsno,inlsalsno,inlseparateg2yn,
                gendes,genctlsub,
                ccnccn,ccndes,jobjob,jobdes,
                fmhfmh,
                inlappyn,inlappucd,inlappddt,inlapptime
                from inltran
                left join sysmast unt on inluntsno=unt.syssno

                left JOIN stkmast stk on inlstksno=stk.stksno
                left join sysmast untstk on stkuntsno=untstk.syssno
                left join sysmast rettyp on inlrettypsno=rettyp.syssno
                left join g_genmast on inlgensno=gensno
                left join c_ccnmast on inlccnsno=ccnsno
                left join jobmast on inljobsno=jobsno
                left join fmhmast on inlfmhsno=fmhsno
              where inlinhsno='$te_inhsno' order by stkdes,inllno";
 //echo "$query_l<br>";
 $result_l = mysql_query($query_l) or die("sql error inl:<br>".mysql_error());

 $i=0;
 echo "<TABLE ALIGN='CENTER' BORDER='1' WIDTH='100%'>";
 echo "<tr class='freeze_col' style='background-color:yellowgreen;'><th width='1%'>#</th>";
 echo "<th width='10%'>Item Des</th><th width='3%'>Details</th><th width='3%'>Unit</th><th width='3%'>Qty</th>";

 echo "<th width='3%'>CPR/Spr</th>";
 echo "<th width='3%'>Value</th>";
 echo "<th width='3%'>Sales Grs(Qty*Spr)</th>";
 echo "<th width='3%'>Avg.Cpr</th>";
 echo "<th width='3%'>Avg.Val</th>";
 echo "<th width='10%'>Des</th>";
 echo "<th width='10%'>Sno</th>";

 if($te_inhtyp=='32')
 {
  echo "<th width='10%'>Wgt</th>";
 }

 if($te_inhtyp=='01')
 {
  echo "<th width='10%'>Po #</th>";
  echo "<th width='10%'>Po Date</th>";
 }

 if($te_inhtyp=='03')   // sales ret
 {
  echo "<th width='10%'>Inv #</th>";
  echo "<th width='10%'>Inv Qty</th>";
 }

 if($te_inhtyp=='21')
 {
  echo "<th width='10%'>Req #</th>";
  echo "<th width='10%'>Req Date</th>";
  echo "<th width='10%'>Grn #</th>";
  echo "<th width='10%'>Grn Qty</th>";
 }

 if($te_inhtyp=='26')
 {
  echo "<th width='10%'>PO #</th>";
  echo "<th width='10%'>PO Qty</th>";
  echo "<th width='10%'>Grn #</th>";
  echo "<th width='10%'>Grn Qty</th>";
 }

 if($te_inhtyp=='14' or $te_inhtyp=='03')
 {
  echo "<th width='3%'>Dis %</th>";
  echo "<th width='3%'>Dis Amt</th>";
  echo "<th width='3%'>Vat Amt</th>";
 }

 if($te_inhtyp=='07')
 {
  echo "<th width='3%'>Org.Qty</th>";
 }

 if($te_inhtyp=='13') // other issues
 {
  echo "<th width='3%'>A/c</th>";
 }
 echo "<th width='3%'>Approved</th>";


 echo "</tr>";
 $i=1;
 $te_inlvatamt = 0;
 $te_inhval=0;
 while($fieldl = mysql_fetch_array($result_l, MYSQL_BOTH))
 {
  $te_inlsno    = $fieldl['inlsno'];
  $te_inllno    = $fieldl['inllno'];
  $te_inldes    = $fieldl['inldes'];
  $te_inlqty    = $fieldl['inlqty'];
  $te_inlfmlqty    = $fieldl['inlfmlqty'];
  $te_inlwgt    = $fieldl['inlwgt'];
  $te_inlfreeqty = $fieldl['inlfreeqty'];

  $te_inldispcn = $fieldl['inldispcn'];
  $te_inldisamt = $fieldl['inldisamt'];
  $te_inlvatamt = $fieldl['inlvatamt'];

  $te_inlspr    = $fieldl['inlspr'];
  $te_inlcpr    = $fieldl['inlcpr'];
  $te_inlavgcpr = round($fieldl['inlavgcpr'],2);
  $te_inlavgval = round($te_inlqty*$te_inlavgcpr,2);

  $te_inlorgqty = $fieldl['inlorgqty'];


  if($te_inlspr>0 and $te_inlcpr==0)
  {
   $te_inlcpr=$te_inlspr;
  }

  $te_inlval    = $fieldl['inlval'];
  $te_inlamt    = $fieldl['inlamt'];
  if($te_inhtyp=='03')
  {
   $te_inlval    = round($te_inlqty*$te_inlcpr,2);
  }

  $te_inlgrsval  = round($te_inlqty*$te_inlspr,2);

  if($te_inlval==0)
  {
   $te_inlval = round($te_inlqty*$te_inlcpr,2);
  }
  $te_inlstksno = $fieldl['inlstksno'];
  $te_stkstk    = $fieldl['stkstk'];
  $te_stkdes    = $fieldl['stkdes'];
  $te_stkrm     = $fieldl['stkrm'];

  $te_inlstksno = $fieldl['inlstksno'];
  $te_rettyp    = $fieldl['rettyp'];
  $te_fmhfmh    = $fieldl['fmhfmh'];
  $te_unt       = $fieldl['unt'];
  $te_inluntsno = $fieldl['inluntsno'];
  $te_untstk       = $fieldl['untstk'];
  if($te_unt=='')
  {
   $te_unt=$te_untstk;
  }

  $te_inlisssno = $fieldl['inlisssno'];
  $te_inlpodsno = $fieldl['inlpodsno'];
  $te_inlreqpodsno = $fieldl['inlreqpodsno'];
  $te_inlsalsno = $fieldl['inlsalsno'];
  $te_inlbatsno = $fieldl['inlbatsno'];
  $te_inlseparateg2yn = $fieldl['inlseparateg2yn'];
  $te_genctlsub = $fieldl['genctlsub'];
  $te_gendes = $fieldl['gendes'];
  $te_ccnccn = $fieldl['ccnccn'];
  $te_ccndes = $fieldl['ccndes'];

  $te_jobjob = $fieldl['jobjob'];
  $te_jobdes = $fieldl['jobdes'];
  $te_inlappyn = $fieldl['inlappyn'];
  $te_inlappucd = $fieldl['inlappucd'];
  $te_inlappddt = $fieldl['inlappddt'];
  $te_inlapptime = $fieldl['inlapptime'];

  $te_inldes.=$te_ccnccn.' '.$te_ccndes.' '.$te_jobjob.' '.$te_jobdes;


  $te_inlbatbat='';
  $sql_bat= "select batbat from batmast where batsno='$te_inlbatsno'";
  //echo "$query_bat<br>";
  $result_bat = mysql_query($sql_bat) or die("sql error sql_bat<br>".mysql_error());
  if(mysql_num_rows($result_bat))
  {
   $fields_bat = mysql_fetch_array($result_bat, MYSQL_BOTH);
   $te_inlbatbat = 'Batch: '.$fields_bat['batbat'];
  }


  if($te_inlqty!=0 or $te_inlamt!=0)
  {
   $query_ = "select inhrem from inltran
                    LEFT JOIN inhtran ON inlinhsno=inhsno
 				where inlsno='$te_inlsno'";

   //echo "$query_<br>";
   $result_ = mysql_query($query_) or die("sql error inh..!".mysql_error());
   $fieldh_ = mysql_fetch_array($result_, MYSQL_BOTH);
   $te_inhrem    = $fieldh_['inhrem'];
   if($te_typ=='PURCHASE ORDER')
   {
    $te_inldes.=$te_inhrem;
    $te_inldes  .=chr(13).chr(10);
    $te_inldes = wordwrap("$te_inldes",100,"\n",1);
   }

   //RZ********************
   if($te_inlpodsno>0)
   {
    $query_pod= "select inhdno,inhddt,supnam,inlqty
                from inltran left join inhtran on inlinhsno=inhsno
                             left join supmast on inhsupsno=supsno
                where inlsno='$te_inlpodsno'";
    //echo "$query_pod<br>";
    $result_pod = mysql_query($query_pod) or die("sql error query_pod: pod<br>".mysql_error());
    $fields_pod = mysql_fetch_array($result_pod, MYSQL_BOTH);
    $te_poddno    = $fields_pod['inhdno'];
    $te_podddt    = ytod($fields_pod['inhddt']);
    $te_poqty     = $fields_pod['inlqty'];
   }
   $te_reqpodsno='';$te_reqpoddno='';$te_reqpodddt='';$te_reqpoqty='';
   if($te_inlreqpodsno>0)
   {
    $query_reqpod= "select inhsno,inhdno,inhddt,supnam,inlqty
                from inltran left join inhtran on inlinhsno=inhsno
                             left join supmast on inhsupsno=supsno
                where inhsno='$te_inlreqpodsno' and inlstksno='$te_inlstksno'";
    //echo "$query_reqpod<br>";
    $result_reqpod = mysql_query($query_reqpod) or die("sql error query_reqpod: pod<br>".mysql_error());
    $fields_reqpod = mysql_fetch_array($result_reqpod, MYSQL_BOTH);
    $te_reqpodsno    = $fields_reqpod['inhsno'];
    $te_reqpoddno    = $fields_reqpod['inhdno'];
    $te_reqpodddt    = ytod($fields_reqpod['inhddt']);
    $te_reqpoqty     = $fields_reqpod['inlqty'];
   }

   $te_trfto='';$te_trffrom='';

   if($te_inhtyp=='30')
   {
	$te_inlval=$te_inlamt;
   	$te_stkstk=$te_genctlsub;
   	$te_stkdes=$te_gendes;
   }

   echo "<tr class='detailline'>";
   echo "<td align='center'>$te_inllno</td>";
   echo "<td>$te_stkstk - $te_stkdes - $te_stkrm - $te_inlstksno</td>";
   echo "<td>Ret Type:$te_rettyp Formula:$te_fmhfmh</td>";

   echo "<td>$te_unt</td>";
   echo "<td align='right'>".number_format($te_inlqty,2)."</td>";

   echo "<td align='right'>".number_format($te_inlcpr,2)."</td>";
   echo "<td align='right'>".number_format($te_inlval,2)."</td>";
   echo "<td align='right'>".number_format($te_inlgrsval,2)."</td>";

   echo "<td align='right'>".number_format($te_inlavgcpr,2)."</td>";
   echo "<td align='right'>".number_format($te_inlavgval,2)."</td>";

   $tot_inlqty   += $te_inlqty;
   $tot_inlval   += $te_inlval;
   $tot_gross    +=$te_inlqty*$te_inlspr;

   $tot_inldisamt+=$te_inldisamt;
   $tot_inlvatamt+=$te_inlvatamt;

   $tot_inlavgval+=$te_inlavgval;

   $te_inldes.=' '.$te_spit;
   echo "<td>$te_inldes $te_inlbatbat</td>";

   echo "<td align='right'>$te_inlsno</td>";

   if($te_inhtyp=='32')
   {
	echo "<td align='right'>".number_format($te_inlwgt,2)."</td>";
   }
   if($te_inhtyp=='01')
   {
    echo "<td>$te_poddno </td>";
    echo "<td>$te_podddt Qty: $te_poqty</td>";
   }

   if($te_inhtyp=='03')
   {
    $te_salqty=0;$te_salnos='';
    $sql_sal = "select inhdno,inhddt,inlqty,inlspr,inlavgcpr
                   from inltran left join inhtran on inlinhsno=inhsno
                   where inlsno='$te_inlsalsno' and inlqty>0
                   and (inhcanucd='' or inhcanucd is null)";
    //echo "$sql_sal<br>";
    $result_sal = mysql_query($sql_sal) or die("SQL Error sql_sal : <br>".mysql_error());
    if(mysql_num_rows($result_sal))
    {
     while($fields_sal = mysql_fetch_array($result_sal, MYSQL_BOTH))
     {
      $te_inhsaldno = $fields_sal['inhdno'];
      $te_inlspr    = $fields_sal['inlspr'];
      $te_inlavgcpr = $fields_sal['inlavgcpr'];

      $te_salqty += $fields_sal['inlqty'];
      $te_salnos .=$te_inhsaldno.' Avg: '.$te_inlavgcpr.' Spr: '.$te_inlspr;

     }
    }
    echo "<td>$te_salnos</td>";
    echo "<td>$te_salqty</td>";
   }

   $te_grnnos='';
   if($te_inhtyp=='21')
   {
    echo "<td>$te_reqpoddno </td>";
    echo "<td>$te_reqpodddt Qty: $te_reqpoqty</td>";

    $te_grnqty=0;
    $sql_grn = "select inhdno,inhddt,inlqty
                   from inhtran left join inltran on inhsno=inlinhsno
                   where inlpodsno='$te_inlsno' and inlstksno='$te_inlstksno' and inlqty>0
                   and (inhcanucd='' or inhcanucd is null)";
    //echo "$sql_grn<br>";
    $result_grn = mysql_query($sql_grn) or die("SQL Error sql_grn : <br>".mysql_error());
    if(mysql_num_rows($result_grn))
    {
     while($fields_grn = mysql_fetch_array($result_grn, MYSQL_BOTH))
     {
      $te_inhgrndno = $fields_grn['inhdno'];
      $te_inhgrndno = $fields_grn['inhdno'];

      $te_grnqty += $fields_grn['inlqty'];
      $te_grnnos .=$te_inhgrndno.' ';

     }
    }
    echo "<td>$te_grnnos</td>";
    echo "<td>$te_grnqty</td>";
   }

   if($te_inhtyp=='26') // PO req
   {

    $te_podnos='';$te_grnnos='';
    $te_podqty=0;$te_grnqty=0;
    $sql_pod = "select distinct inlsno
                   from inhtran left join inltran on inhsno=inlinhsno
                   where inhporeqsno='$te_inhsno' and inlstksno='$te_inlstksno' and inlqty>0
                   and (inhcanucd='' or inhcanucd is null)";
    //echo "$sql_pod<br>";
    $result_pod = mysql_query($sql_pod) or die("SQL Error sql_pod : <br>".mysql_error());
    if(mysql_num_rows($result_pod))
    {
     while($fields_pod = mysql_fetch_array($result_pod, MYSQL_BOTH))
     {
      $te_inlpodsno = $fields_pod['inlsno'];

      $sql_ ="select inhdno,inlqty from inltran left join inhtran on inlinhsno=inhsno
              where inlsno='$te_inlpodsno'
                    and (inhcanucd='' or inhcanucd is null)";
      //echo "$sql_<br>";
      $result_ = mysql_query($sql_) or die("sql error updq : <br>".mysql_error());
      $fields_ = mysql_fetch_array($result_);
      $te_podnos.= $fields_['inhdno'].' ';
      $te_podqty += $fields_['inlqty'];

      $sql_grn = "select distinct inlsno
                   from inhtran left join inltran on inhsno=inlinhsno
                   where inlpodsno='$te_inlpodsno' and inlstksno='$te_inlstksno' and inlqty>0
                   and (inhcanucd='' or inhcanucd is null)";
      //echo "$sql_grn<br>";
      $result_grn = mysql_query($sql_grn) or die("SQL Error sql_grn : <br>".mysql_error());
      if(mysql_num_rows($result_grn))
      {
       while($fields_grn = mysql_fetch_array($result_grn, MYSQL_BOTH))
       {
        $te_inlgrnsno = $fields_grn['inlsno'];
        $sql_ ="select inhdno,inlqty from inltran left join inhtran on inlinhsno=inhsno
              where inlsno='$te_inlgrnsno'
                    and (inhcanucd='' or inhcanucd is null)";
        //echo "$sql_<br>";
        $result_ = mysql_query($sql_) or die("sql error updq : <br>".mysql_error());
        $fields_ = mysql_fetch_array($result_);
        $te_grnnos.= $fields_['inhdno'].' ';
        $te_grnqty += $fields_['inlqty'];
       }
      }
     }
    }
    echo "<td>$te_podnos</td>";
    echo "<td>".number_format($te_podqty,2)."</td>";
    echo "<td>$te_grnnos</td>";
    echo "<td>".number_format($te_grnqty,2)."</td>";
   }

   if($te_inhtyp=='14' or $te_inhtyp=='03')
   {
	echo "<td align='right'>".number_format($te_inldispcn,2)."</td>";
    echo "<td align='right'>".number_format($te_inldisamt,2)."</td>";
    echo "<td align='right'>".number_format($te_inlvatamt,2)."</td>";
   }

   if($te_inhtyp=='07')
   {
    echo "<td align='right'>".number_format($te_inlorgqty,2)."</td>";
   }

   if($te_inhtyp=='13') // other issues
   {
	echo "<td>$te_genctlsub $te_gendes</td>";
   }

   echo "<td>$te_inlappyn $te_inlappucd $te_inlappddt $te_inlapptime</td>";
   echo "<td align='left'><input type='button' name='fpl' id='fpl' value='$te_grnqty' class='button'
            onclick='popfpl(\"$te_inlsno\")'></input></td>";


   echo "</tr>";

   $i+=1;
  }
 }
 echo "<input type=hidden name='inlcount' id='inlcount' value='".$i."'></input>";
 echo "<tr align='center' class='a'>";
 echo "<td colspan='3'></td><th align='left' class='totalline'>Hash Total</th>";


 echo "<th align='right' class='totalline'>".number_format($tot_inlqty,2)."</th>";
 echo "<td></td>";
 echo "<th align='right' class='totalline'>".number_format($tot_inlval,2)."</th>";
 echo "<th align='right' class='totalline'>".number_format($tot_gross,2)."</th>";
 echo "<th align='right' class='totalline'>".number_format($tot_inlavgval,2)."</th>";

 echo "<td></td>";
 echo "<td></td>";
 if($te_inhtyp=='14')
 {
  echo "<th width='10%'></th>";
 }
 if($te_inhtyp=='32')
 {
  echo "<th width='10%'></th>";
 }

 if($te_inhtyp=='01')
 {
  echo "<th width='10%'></th>";
  echo "<th width='10%'></th>";
 }

 if($te_inhtyp=='03')   // sales ret
 {
  echo "<th width='10%'></th>";
  echo "<th width='10%'></th>";
 }

 if($te_inhtyp=='21')
 {
  echo "<th width='10%'></th>";
  echo "<th width='10%'></th>";
  echo "<th width='10%'></th>";
  echo "<th width='10%'></th>";
 }

 if($te_inhtyp=='26')
 {
  echo "<th width='10%'></th>";
  echo "<th width='10%'></th>";
  echo "<th width='10%'></th>";
  echo "<th width='10%'></th>";
 }

 if($te_inhtyp=='14' or $te_inhtyp=='03')
 {
  echo "<th width='3%'></th>";
  echo "<th width='3%'>".number_format($tot_inldisamt,2)."</th>";
  echo "<th width='3%'>".number_format($tot_inlvatamt,2)."</th>";
 }

 if($te_inhtyp=='07')
 {
  echo "<th width='3%'></th>";
 }

 if($te_inhtyp=='13') // other issues
 {
  echo "<th width='3%'></th>";
 }


 echo "</tr><tr>";
 echo "</tr>";

 echo "</table>";


 if($te_inhtyp=='21')
 {
  echo "<TABLE ALIGN='CENTER' BORDER='0' WIDTH='100%'>";
  echo "<tr class='columnhead' style='background-color:yellowgreen;'>";

  echo "<th width='10%'>Grn No</th>";
  echo "<th width='3%'>Grn Qty</th>";
  echo "<th width='3%'>Cancel</th>";
  echo "</tr>";
  $sql_inl = "select inlsno,inlqty,stkstk,stkdes from inltran
              left join stkmast on inlstksno=stksno
              where inlinhsno='$te_inhsno'";
  $result_inl = mysql_query($sql_inl) or die("sql error inl : <br>".mysql_error());
  while($fields_inl = mysql_fetch_array($result_inl,MYSQL_BOTH))
  {
   $te_inlpodsno = $fields_inl['inlsno'];
   $te_inlpodqty = $fields_inl['inlqty'];
   $te_stkstk = $fields_inl['stkstk'];
   $te_stkdes = $fields_inl['stkdes'];
   echo "<tr>";
   echo "<th width='10%'>$te_stkstk - $te_stkdes</th>";
   echo "<th width='10%'>".number_format($te_inlpodqty,0)."</th>";
   echo "</tr>";

   $sql_grn = "select inhdno,inhcanucd,inlqty from inltran left join inhtran on inlinhsno=inhsno
               where inlpodsno='$te_inlpodsno'";
   $result_grn = mysql_query($sql_grn) or die("sql error grn : <br>".mysql_error());
   $tot_grnqty=0;
   while($fields_grn = mysql_fetch_array($result_grn,MYSQL_BOTH))
   {
    $te_grndno = $fields_grn['inhdno'];
    $te_grnqty = $fields_grn['inlqty'];
    $te_grncanucd = $fields_grn['inhcanucd'];
    echo "<tr>";
    echo "<th width='10%'>$te_grndno</th>";
    echo "<th width='10%'>".number_format($te_grnqty,0)."</th>";
    echo "<th width='10%'>$te_grncanucd</th>";
    echo "</tr>";
    if($te_grncanucd=='')
    {
     $tot_grnqty+=$te_grnqty;
    }
   }
   echo "<tr>";
   echo "<th width='10%'>POD Qty:".number_format($te_inlpodqty,0)."</th>";
   echo "<th width='10%'>Grn Qty:".number_format($tot_grnqty,0)."</th>";
   echo "</tr>";
   if($te_inlpodqty>$tot_grnqty)
   {
    $sql_ame_h = "UPDATE inhtran SET inhpodcmplflg =''
                                     WHERE inhsno ='$te_inhsno'";
    //echo "$sql_ame_h<br>";
    $result_bh = mysql_query($sql_ame_h) or die("Could not Update header:<br>".mysql_error());
   }
  }
 }
 echo "</table>";



 echo "</div>";

}
?>



</body>
</html>


<?php

Class HtmlFormsObjects
{


    function GenerateDropDownListauto($HtmlObjectName,$HtmlObjectDefaultValue,$DBFieldToValue,$DBFieldToDescription,$action,$onkeyp,$w,$t)
    {
      global $result;
	  $w1=$w-20;
      echo "<select id='$HtmlObjectName' name='$HtmlObjectName' onChange='$action'
      Onkeypress='$onkeyp'
      style='position:absolute;top:$t;z-index:0;width:$w;clip:rect(0px,$w, 22px, $w1);'> \n";
  	  mysql_data_seek($result,0);
  	  echo "<option>---</option> \n";
      while ($rec = mysql_fetch_array($result))
      {
        if ($HtmlObjectDefaultValue == $rec["$DBFieldToValue"])
        {
          echo "<option selected value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
        }
        else
        {
          echo "<option value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
        }
      }
      echo "</select> \n";
    }
      function GenerateDropDownList($HtmlObjectName,$HtmlObjectDefaultValue,$DBFieldToValue,$DBFieldToDescription,$action,$onkeyp)
            {
         global $result;

         echo "<select id='$HtmlObjectName' name='$HtmlObjectName' onChange='$action' Onkeypress='$onkeyp'> \n";
         echo"<option></option>";
  	 mysql_data_seek($result,0);
         while ($rec = mysql_fetch_array($result)){

                    if ($HtmlObjectDefaultValue == $rec["$DBFieldToValue"]){
                         echo "<option selected value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
                    }
                    else{
                         echo "<option value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
                    }
         }
         echo "</select> \n";

      }


      function GenerateDropDownListdno($HtmlObjectName,$HtmlObjectDefaultValue,$DBFieldToValue,$DBFieldToDescription,$action,$onkeyp){
         global $result;

         echo "<select id='$HtmlObjectName' name='$HtmlObjectName' onChange='$action' Onkeypress='$onkeyp'
         style='position:absolute;top:8px;z-index:-1;width:110;clip:rect(0px,110px, 22px, 90);'> \n";
         echo"<option></option>";
  	 mysql_data_seek($result,0);
         while ($rec = mysql_fetch_array($result)){

                    if ($HtmlObjectDefaultValue == $rec["$DBFieldToValue"]){
                         echo "<option selected value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
                    }
                    else{
                         echo "<option value='".$rec[$DBFieldToValue]."'>$rec[$DBFieldToDescription]</option> \n";
                    }
         }
         echo "</select> \n";

      }


}

function load_dno($te_inhdno)
{
 $te_inhdno=trim($te_inhdno);
 $sql_dno = "select inhsno,concat(inhdno,'-',typ.sysdes,'-',inhddt,'-',locloc) inh from inhtran
                left join commast on inhcomsno=comsno
                left join locmast on inhlocsno=locsno
                left join sysmast typ on inhtyp=typ.sysrno and typ.sysrtp=8
			    where inhdno='$te_inhdno' order by inhdno";
 //return $sql_dno;
 $result_dno = mysql_query($sql_dno) or die("no Documents");
 $te_return = chr(25).chr(25)."----####";
 while ($fields_dno = mysql_fetch_array($result_dno,MYSQL_BOTH))
 {
  $te_inhsno	= $fields_dno['inhsno'];
  $te_inh   	= $fields_dno['inh'];
  $te_return .= "$te_inhsno".""."$te_inh"."####";
 }
 return substr($te_return,0,-4);
}

function load_dnodd($te_inhtypsno)
{

 $sql_dno = "select inhsno,concat(inhdno,'-',typ.sysdes,'-',inhddt,'-',locloc) inh from inhtran
                left join commast on inhcomsno=comsno
                left join locmast on inhlocsno=locsno
                left join sysmast typ on inhtyp=typ.sysrno and typ.sysrtp=8
			    where typ.syssno='$te_inhtypsno' order by inhdno";
 //return $sql_dno;
 $result_dno = mysql_query($sql_dno) or die("no Documents");
 $te_return = chr(25).chr(25)."----####";
 while ($fields_dno = mysql_fetch_array($result_dno,MYSQL_BOTH))
 {
  $te_inhsno	= $fields_dno['inhsno'];
  $te_inh   	= $fields_dno['inh'];
  $te_return .= "$te_inhsno".""."$te_inh"."####";
 }
 return substr($te_return,0,-4);
}


function ytod($ddt) {
 if ($ddt=="0000-00-00")
     return "";
 else {
	 $year = substr($ddt, 0, 4);
     $month = substr($ddt, 5, 2);
     $day = substr($ddt, 8, 2);
     return  "$day/$month/$year";
 }
}

function dtoy($ddt) {
	if ($ddt) {
		$vl=split('/',$ddt);
		$day = $vl[0];
		$month = $vl[1];
		$year = $vl[2];
		return "$year-$month-$day";
	}
	else {
		return "";
	}
}

?>

