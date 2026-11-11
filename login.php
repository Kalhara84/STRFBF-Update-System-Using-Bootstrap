<?php
/* ==============================================================================
   FILE: login.php
   PURPOSE: User Authentication and Login screen.
   MATCHING DESIGN: Image 1 (RBF Login screen)

   ASSET & CODE SOURCES / DOCUMENTATION NOTE:
   - CSS Framework: "css/bootstrap.min.css" (Bootstrap v3.3.7, stored locally in css/ folder)
   - Custom Styling: "css/custom.css" (Custom stylesheet matching Image 1 layout & colors)
   - Preloader CSS: "css/preloader.css" (Local preloader styling)
   - jQuery Library: "js/jquery-1.10.2.min.js" (Local jQuery library)
   - Form Validation: "js/jquery.validate.min.js" (Client-side validation plugin)
   - Login Handler Script: "js/login.js" (Local form validation rules & handlers)
   - Images:
       * "img/login-back2.jpg" - Fluid 3D wavy background wallpaper
       * "img/login-side.jpg"  - Right-side card illustration with laptop bar-chart
       * Circular "RBF" badge  - Rendered via CSS (.brand-badge in css/custom.css)
   ============================================================================== */

// Hide PHP notices and warnings on the page
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
// The session starts below; logout.php clears it when the user signs out.

$config_use_sessions = TRUE;  
// Start a new session
session_start();

$te_entddt  = strftime("%Y-%m-%d"); 

if( !isset($_SESSION["error"] ))
{
 $_SESSION["error"] = "";
}

$te_error="";  

// Set the time zone and get today's date and time
date_default_timezone_set('Asia/Colombo');
$te_enttime = strftime('%H:%M:%S');
$te_entddt  = strftime ("%Y-%m-%d");
#echo "$te_entddt $te_enttime <br>";


#echo "The time is " . date("h:i:sa");

// Create empty session values if they are not set yet
if( !isset($_SESSION["sDatabase"] ))
{
 $_SESSION["sDatabase"] = "";
}

if( !isset($_SESSION["sHostname"] ))
{
 $_SESSION["sHostname"] = "";
}

if( !isset($_SESSION["sPort"] ))
{
 $_SESSION["sPort"] = "";
}

if( !isset($_SESSION["sUsername"] ))
{
 $_SESSION["sUsername"] = "";
}

if( !isset($_SESSION["sPassword"] ))
{
 $_SESSION["sPassword"] = "";
}

if( !isset($_SESSION["rConn"] ))
{
 $_SESSION["rConn"] = "";
}

if( !isset($_SESSION["rDB"] ))
{
 $_SESSION["rDB"] = "";
}

if( !isset($_SESSION["sOS"] ))
{
 $_SESSION["sOS"] = "";
}

$_SESSION["sLG"] = "";  

if( !isset($_SESSION["sback"] ))
{
 $_SESSION["sback"];
}


// System settings: W = Windows, w = WAMP
$sOS    = "W"; // W-windows L- linux
$_SESSION["sOS"] = "W";
$_SESSION["wx"] = "w"; // for w-wamp  x-xampp

if( !isset($_SESSION["sFontdir"] ))
{
 $_SESSION["sFontdir"];
}
// $_SESSION["sFontdir"]=$_SERVER['DOCUMENT_ROOT'].'/tfpdf/font';    // cloud
// Folder paths used by other pages (fonts, uploads, images)
$_SESSION["sFontdir"]=$_SERVER['DOCUMENT_ROOT'].'/tfpdf/font/';

if( !isset($_SESSION["sFontdir_fpdi"] ))
{
 $_SESSION["sFontdir_fpdi"];
}
$_SESSION["sFontdir_fpdi"]=$_SERVER['DOCUMENT_ROOT'].'/tfpdf/fpdf_fpdi/font/';

if( !isset($_SESSION["sDnloaddir"] ))
{
 $_SESSION["sDnloaddir"];
}
$_SESSION["sDnloaddir"]=$_SERVER['DOCUMENT_ROOT'].'/strbf/uploads/';

if( !isset($_SESSION["sImgdir"] ))
{
 $_SESSION["sImgdir"];
}
$_SESSION["sImgdir"]=$_SERVER['DOCUMENT_ROOT'].'/strbf/img/';


// Database connection details (the last set of values is the one used)
$_SESSION["sDatabase"] = "ctecomco_strbf";
$_SESSION["sHostname"] = "localhost";
$_SESSION["sPort"]     = "3306";
$_SESSION["sUsername"] = "root";
$_SESSION["sPassword"] = "mypass";


$te_sDatabase = $_SESSION["sDatabase"];
$te_sHostname = $_SESSION["sHostname"];
$te_sPort     = $_SESSION["sPort"];
$te_sUsername = $_SESSION["sUsername"];
$te_sPassword = $_SESSION["sPassword"];

$sTable    = "usdmast";

// Connect to the MySQL database
$connect = new mysqli("$te_sHostname", $te_sUsername, $te_sPassword,$te_sDatabase,$te_sPort) or die("Could not connect : ".mysqli_error($connect));


//print_r($_SESSION); 
// User is not logged in until the checks below pass
if(!isset($_SESSION["validated"] ))
{
 $_SESSION["validated"];       
}
$_SESSION["validated"] = false;

// Company number and company name
if( !isset($_SESSION["scomsno"]))
{
 $_SESSION["scomsno"];
}
$_SESSION["scomsno"]=1;

if( !isset($_SESSION["scomnam"]))
{
 $_SESSION["scomnam"];
}
$_SESSION["scomnam"]='Ravi Breeder Farm';


// Run this block when the Sign In button is pressed
if(isset($_POST['submit']))
{      		
 #$te_usdusd = ucwords($_POST['usdusd']); 
 
 // Read the user name and password typed in the form
 $te_usdusd = $_POST['usdusd']; 
 $te_usdpwd = $_POST['usdpwd']; 
 
 if($te_usdusd!='' and $te_usdpwd!='')
 { 
  // Find the user in the database
  $sql_query = "SELECT * FROM usdmast WHERE usdusd = '$te_usdusd' and usdactflg!='n' ";    	
  //$sql_query = "SELECT * FROM usdmast WHERE  BINARY  usdusd = BINARY '$te_usdusd' "; (Case Sesitivity CHECK)  
  //echo "$sql_query"; 
  $result = mysqli_query($connect,$sql_query) or die("Error".mysqli_error($connect));
  if(mysqli_num_rows($result)) 
  {
  }
  else
  {
   $error[] = "<p class='bg-warning'><i class='glyphicon glyphicon-remove-circle red' ></i><b>Invalid User Code</b></p>";
  }
   
  $fields = mysqli_fetch_array($result, MYSQLI_BOTH);
  $te_usdsno  = $fields['usdsno'];
  $te_usdstkrolyn  = $fields['usdstkrolyn'];
  // Compare the typed password with the saved password
  if ($te_usdpwd==$fields['usdpwd'])
  {
  }
  else
  {
   $error[] = "<p class='bg-danger'><i class='glyphicon glyphicon-remove-circle red' ></i><b>Invalid Password</b></p>";
  }
  
  // If there is any error, login is not allowed; otherwise save the user details in the session
  if(isset($error))
  {
   // with error
   $_SESSION["validated"] = false;
  }
  else
  {
   if( !isset($_SESSION["susdusd"]))
   {
    $_SESSION["susdusd"];
   }
   $_SESSION["susdusd"]=$fields['usdusd']; 
   $_SESSION["susdstkrolyn"]=$fields['usdstkrolyn']; 
   
   if( !isset($_SESSION["susdsno"]))
   {
    $_SESSION["susdsno"];
   }
   $_SESSION["susdsno"]=$fields['usdsno']; 
       
   if( !isset($_SESSION["susdnam"]))
   {
    $_SESSION["susdnam"];
   }
   $_SESSION["susdnam"]=$fields['usdnam'];  
    
   if( !isset($_SESSION["susdadmin"]))
   {
    $_SESSION["susdadmin"];
   }  
   $_SESSION["susdadmin"]=$fields['usdadmin'];  
   
   if( !isset($_SESSION["susdlocsno"]))
   {
    $_SESSION["susdlocsno"];
   }
   $_SESSION["susdlocsno"]=$fields['usdlocsno'];
   
   if( !isset($_SESSION["susdcomsno"]))
   {
    $_SESSION["susdcomsno"];
   }
   $_SESSION["susdcomsno"]=$fields['usdcomsno'];
   
   if( !isset($_SESSION["susdcomcom"]))
   {
    $_SESSION["susdcomcom"];
   }
   $_SESSION["susdcomcom"]=$fields['usdcomcom'];
   
   if( !isset($_SESSION["susdcomnam"]))
   {
    $_SESSION["susdcomnam"];
   }
   $_SESSION["susdcomnam"]=$fields['usdcomnam'];
   
   if( !isset($_SESSION["susdcatsno"]))
   {
    $_SESSION["susdcatsno"];
   }
   $_SESSION["susdcatsno"]=$fields['usdcatsno'];
     
   if( !isset($_SESSION["susddes"] ))
   {
    $_SESSION["susddes"];
   }
   $_SESSION["susddes"]=$fields['usddes'];
        
   if( !isset($_SESSION["susduno"]))
   {
    $_SESSION["susduno"];
   }
   $_SESSION["susduno"]=$fields['usduno'];
   if(!isset($_SESSION["susdspadmin"]))
   {
    $_SESSION["susdspadmin"];
   }
   $_SESSION["susdspadmin"]=$fields['usdspadmin']; 
   
   
   
   // Check how old the password is (it expires after 30 days)
   $te_usdpwdddt=$fields['usdpwdddt'];
   $ok_flg='y';
   if($te_usdpwdddt=='' || $te_usdpwdddt=="0000-00-00")
   {
    $ok_flg='n';   
   }
   if($te_usdpwdddt>"0000-00-00") 
   {
	$query_      ="SELECT DATEDIFF('$te_entddt','$te_usdpwdddt')as datedif";
    $result_     = mysqli_query($connect,$query_);
    $fields_     = mysqli_fetch_array($result_, MYSQLI_BOTH);
    $te_noofdays = $fields_['datedif'];  
   }
   else 
   {
    $te_noofdays=999;
   }
   if($te_noofdays>30)
   {
    $_SESSION["validated"]=false;         
    $error[] = "<p class='bg-danger'><i class='glyphicon glyphicon-remove-circle red' ></i><b>Password Expired; Please Contact Administrator</b></p>";     
   } 
   // Only complete login when all checks, including password expiry, pass.
   $_SESSION["validated"] = !isset($error);
   if ($_SESSION["validated"]) {
       session_regenerate_id(true);
       // These values are used by dashboard.php. Missing email is shown as Not provided.
       $_SESSION['dashboard_email'] = isset($fields['usdemail']) ? $fields['usdemail'] : '';
       $_SESSION['dashboard_password_expiry'] = date('Y-m-d', strtotime($te_usdpwdddt . ' +30 days'));
   }
   
   // Remember Me: save the user name and password in cookies
   if(isset($_POST['usdspcopyadd']))
   {
    $te_usdspcopyaddopt="checked";
    setcookie('rememberusd', $te_usdusd,mktime (0, 0, 0, 12, 31, 2019));     
    setcookie('rememberpwd', $te_usdpwd,mktime (0, 0, 0, 12, 31, 2019));     
   }
   else
   {
	$te_usdspcopyaddopt="";
   }
   
   // Send the user to the correct page by user category
   $te_usdcatsno =$fields['usdcatsno']*1;  
   
   if($_SESSION['validated'] && ($te_usdcatsno == '34' or $te_usdcatsno == '0' or $te_usdcatsno == '')) //normal user
   { 	  	    
    $_SESSION["alert"]=true;
    
    $te_logucd=$_SESSION["susdusd"];
    $te_logentddt=strftime("%Y-%m-%d");
    $te_logenttime=strftime('%H:%M:%S');
    
    // Save a login record in the log table
    if($te_logucd!='2000')
    {
     $sql_log = "insert into logmast_login
       	      (logucd,logddt,logtime,logprgnam,logaction,logdes)
                values
               ('$te_logucd','$te_logentddt','$te_logenttime','login.php','LOGIN',
                'LOGIN')";
     //echo "$sql_log<br>";
     $result =mysqli_query($connect,$sql_log) or die("sql error logmast<br>".mysqli_error($connect) );  
    }
    
    header('Location: dashboard.php');
    exit;
   }
   // All other signed-in user categories also start at the new dashboard.
   if ($_SESSION['validated'])
   {
    header('Location: dashboard.php');
    exit;
   }
  }
 }
}
else
{

 // Page opened normally: fill the form from the saved cookies
 $te_ = $_COOKIE['rememberusd'];
 $te_usdusd=$te_;	 
 
 $te_ = $_COOKIE['rememberpwd'];
 $te_usdpwd=$te_;	 
}

// On the local machine (127.0.0.1) fill the login details automatically
$te_remoteip = $_SERVER['REMOTE_ADDR'];
// Debug print of the IP address is disabled: any text printed before the page starts puts the browser in Quirks Mode
//echo "$te_remoteip <br>";
//$te_usdusd='ADMIN';
//$te_usdpwd='789';

if($te_remoteip=='127.0.0.1')
{
 $te_usdusd='2000';
 $te_usdpwd='2000 rbf';
 
}


$te_ = $_SERVER['DOCUMENT_ROOT'];
//echo "$te_ <br>";
?> 

<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<!-- All CSS and JS files are loaded from the local css and js folders, so the page works offline -->
<?php
$te=$_SERVER['DOCUMENT_ROOT'].'/ffmal/css/bootstrap.min.css';
//echo "$te<br>";
//echo "<link type='text/css' href='$te' rel='stylesheet'>";
?>

<!-- Bootstrap styles -->
<link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />

<!-- Our own styles -->
<link rel="stylesheet" href="css/custom.css?v=2">
<link rel="stylesheet" href="css/preloader.css">

<!-- Help old Internet Explorer (below IE 9) to understand HTML5 tags -->
<!--[if lt IE 9]>
	<script src="js/html5shiv.min.js"></script>
<![endif]-->

<!-- jQuery, common script and form validation plugin -->
<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/script.js"></script>
<script type="text/javascript" src="js/jquery.validate.min.js"></script>
<!-- Login page design is in css/custom.css -->

<script type="text/javascript">
  //$(window).load(function() {
   //$('.preloader').fadeOut('slow');   // work
   //$(".preloader").delay(5000).fadeOut("slow");
//});


//   $(window).load(function() {
//   $(".loader").delay(2000).fadeOut("slow");
//   $("#overlayer").delay(2000).fadeOut("slow");
// })
</script>

</head>

<body class="login-body">

<!-- Preloader area (not used now) -->
<div class="preloader"></div>

<!-- Login card: left side = login form, right side = picture and tip -->
<div class="login-card">
  <div class="row login-row">

    <!-- LEFT SIDE: logo and login form -->
    <div class="col-sm-6 login-left">

      <!-- Logo and title matching Image 1 -->
      <div class="login-brand">
        <span class="brand-badge">RBF</span>
        <span class="brand-title">Login</span>
      </div>

      <!-- Login form: sends user name and password to this same page -->
      <form class="login-form" method="post" action="login.php" name='login' id="login">

        <?php

        // Show the error messages (wrong user name or password)
        if(isset($error))
        {
         foreach($error as $error)
         {
          echo $error;
         }
        }

        ?>

        <!-- User name field -->
        <div class="form-group">
          <input type="text" id="fake_user" style="display:none">       <!-- to stop autocomplete -->
          <input type="password" id="fake_pass" style="display:none">   <!-- to stop autocomplete -->
          <input type="text" name="usdusd" id="usdusd" autocomplete="off" value="<?= $te_usdusd?>" class="form-control input-lg" placeholder="User Name">
          <small class="hidden" id="user_small">User Name Required</small>
        </div>

        <!-- Password field -->
        <div class="form-group">
          <input type="password" name="usdpwd" value="<?= $te_usdpwd?>" class="form-control input-lg" placeholder="Password">
          <small class="hidden" id="user_pass">Password Required</small>
        </div>

        <!-- Remember Me check box and Forgot password link -->
        <div class="form-group login-options">
          <label class="login-remember"><input type="checkbox" name="usdspcopyadd" id="usdspcopyadd" <?= $te_usdspcopyaddopt ?> value=""> Remember Me</label>
          <a href='usdforgot.php' class="login-forgot">Forgot Password..?</a>
        </div>

        <!-- Sign In button -->
        <div class="form-group">
          <input type="submit" name="submit" value="SIGN IN" class="btn btn-block btn-lg btn-login" tabindex="5">
        </div>
      </form>
    </div>

    <!-- RIGHT SIDE: picture (img/login-side.jpg) and tip text. Hidden on small phones -->
    <div class="col-sm-6 login-right hidden-xs">
      <div class="login-tip">
        <small>PRO TIP #1</small>
        <h2>Speed up<br>data entry</h2>
        <p>Use the Tab key to move quickly from one field to the next.</p>
      </div>
    </div>

  </div>
</div>

<!-- Local js/login.js contains the existing jQuery validation rules.
     It uses js/jquery-1.10.2.min.js and js/jquery.validate.min.js already included above. -->
<script src="js/login.js"></script>

<!-- All scripts are loaded in the head from the local js folder -->
</body>
</html>
