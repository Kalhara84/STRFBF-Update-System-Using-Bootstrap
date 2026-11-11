
/**
 * DHTML date validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 */
// Declaring valid date character, minimum year and maximum year
// changes : 22/08/2005   added eval in compare dates function

var dtCh= "/";
var minYear=1900;
var maxYear=2100;

function isInteger(s){
	var i;
    for (i = 0; i < s.length; i++){   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag){
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year){
	// February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   } 
   return this
}

function isDate(dtStr){
	var daysInMonth = DaysArray(12)
	var pos1=dtStr.indexOf(dtCh)
	var pos2=dtStr.indexOf(dtCh,pos1+1)
	var strDay=dtStr.substring(0,pos1)
	var strMonth=dtStr.substring(pos1+1,pos2)
	var strYear=dtStr.substring(pos2+1)
	
//alert(strDay)
//alert(strMonth)
//alert(strYear)
	
	strYr=strYear
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (pos1==-1 || pos2==-1){
		alert("The date format should be : dd/mm/yyyy")
		return false
	}
	if (strMonth.length<1 || month<1 || month>12){
		alert("Please enter a valid month")
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
		alert("Please enter a valid day")
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
		alert("Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
		return false
	}
	if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
		alert("Please enter a valid date")
		return false
	}
return true
}

/*
function chkBlank(objName)
{

te_on=objName.value;
te_on=te_on.replace(/^\s*|\s*$/g,"");

//te_on=trim(te_on);

if (te_on == '')
   {alert("Blank Not Allowed")
   return false
   }
   return true
}
*/

function chkBlank(objName,msg)
{

te_on=objName.value;
te_on=te_on.replace(/^\s*|\s*$/g,"");

//te_on=trim(te_on);

if (te_on == '')
{
	if(msg)
	{
	 msgx=msg+" Blank Not Allowed";
	}
	else
	{msgx="Blank Not Allowed";
	}
	
   alert(msgx)
   return false
}
return true
}

function chkNumeric(objName,minval,maxval,comma,period,hyphen)
{
// only allow 0-9 be entered, plus any values passed
// (can be in any order, and don't have to be comma, period, or hyphen)
// if all numbers allow commas, periods, hyphens or whatever,
// just hard code it here and take out the passed parameters
var checkOK = "0123456789" + comma + period + hyphen;
var checkStr = objName;
var allValid = true;
var decPoints = 0;
var allNum = "";

for (i = 0;  i < checkStr.value.length;  i++)
{
ch = checkStr.value.charAt(i);
for (j = 0;  j < checkOK.length;  j++)
if (ch == checkOK.charAt(j))
break;
if (j == checkOK.length)
{
allValid = false;
break;
}
if (ch != ",")
allNum += ch;
}
if (!allValid)
{	
alertsay = "Please enter only these values \""
alertsay = alertsay + checkOK + "\" in the \"" + checkStr.name + "\" field."
alert(alertsay);
return (false);
}

// set the minimum and maximum
var chkVal = allNum;
var prsVal = parseInt(allNum);
if (chkVal != "" && !(prsVal >= minval && prsVal <= maxval))
{
alertsay = "Please enter a value greater than or "
alertsay = alertsay + "equal to \"" + minval + "\" and less than or "
alertsay = alertsay + "equal to \"" + maxval + "\" in the \"" + checkStr.name + "\" field."
alert(alertsay);
return (false);
}
}


function chklen(objName,minlen,maxlen)
{
var checkStr = objName;
if (checkStr.value.length < minlen)
   {
   alertsay = "Minimum Length is  \"" + minlen + "\" for \"" + checkStr.name + "\" field."
   alert(alertsay);
   return (false);
   }
   
if (checkStr.value.length > maxlen)
   {
   alertsay = "Maximum Length is  \"" + maxlen + "\" for \"" + checkStr.name + "\" field."
   alert(alertsay);
   return (false);
   }

   return (true);
}


function comparedates (value1, value2) {
   var date1, date2;
   var month1, month2;
   var year1, year2;

   date1 = value1.substring (0, value1.indexOf ("/"));
   month1 = value1.substring (value1.indexOf ("/")+1, value1.lastIndexOf ("/"));
   year1 = value1.substring (value1.lastIndexOf ("/")+1, value1.length);

   date2 = value2.substring (0, value2.indexOf ("/"));
   month2 = value2.substring (value2.indexOf ("/")+1, value2.lastIndexOf ("/"));
   year2 = value2.substring (value2.lastIndexOf ("/")+1, value2.length);

// changed by mf on 22/08/2005
   date1=eval(date1);
   month1=eval(month1);
   year1=eval(year1);

   date2=eval(date2);
   month2=eval(month2);
   year2=eval(year2);
   
  
   if (year1 > year2) return true;
   else if (year1 < year2) return false;
   else if (month1 > month2) return true;
   else if (month1 < month2) return false;
   else if (date1 > date2) return true;
   else if (date1 < date2) return false;
   else return false;
} 

//---------------------------[ equaldates ]---------------------------//
// by dinesh on 29/12/2005
function equaldates (value1, value2) {
   var date1, date2;
   var month1, month2;
   var year1, year2;

   date1 = value1.substring (0, value1.indexOf ("/"));
   month1 = value1.substring (value1.indexOf ("/")+1, value1.lastIndexOf ("/"));
   year1 = value1.substring (value1.lastIndexOf ("/")+1, value1.length);

   date2 = value2.substring (0, value2.indexOf ("/"));
   month2 = value2.substring (value2.indexOf ("/")+1, value2.lastIndexOf ("/"));
   year2 = value2.substring (value2.lastIndexOf ("/")+1, value2.length);


   date1=eval(date1);
   month1=eval(month1);
   year1=eval(year1);

   date2=eval(date2);
   month2=eval(month2);
   year2=eval(year2);
   
  if(((date1==date2)&&(month1==month2)&&(year1==year2)))
   	return true;
  else 
  	return false;
} 

//---------------------------[ dd/mm/yyyy to mm/dd/yyyy ]---------------------------//

function dtom(ddt) {
	
	var date = ddt.substring (0, ddt.indexOf ("/"));
   	var month = ddt.substring (ddt.indexOf ("/")+1, ddt.lastIndexOf ("/"));
   	var year = ddt.substring (ddt.lastIndexOf ("/")+1, ddt.length);

   	return (month + "/" + date + "/" + year)
}

//---------------------------[ mm/dd/yyyy to dd/mm/yyyy ]---------------------------//

function mtod(ddt) {
	
	var date = setLength(ddt.getDate(),'2','0');
	var month = setLength((ddt.getMonth() + 1),'2','0');
	var year = setLength(ddt.getYear(),'4','0');
	
	return (date + "/" + month + "/" +  year)
}

//--------------------------[ nextdate(frddt,days,symbol) ]--------------------------//

function nextdate(frddt,days,symbol){
	var symbol;
	myDate=new Date (dtom(frddt));
	
	if(symbol=='+')
		var nextddt=new Date((myDate.getTime()) + (days*24*60*60*1000));
	if(symbol=='-')
		var nextddt=new Date((myDate.getTime()) - (days*24*60*60*1000));
	
	var date = setLength(nextddt.getDate(),'2','0');
	var month = setLength((nextddt.getMonth() + 1),'2','0');
	var year = setLength(nextddt.getYear(),'4','0');
	
	var nextddt = date + "/" + month + "/" + year;
	return nextddt;
}

//---------------------[ setLength(theNumber,theLength,theSymbol) ]---------------------//

function setLength(theNumber,theLength,theSymbol){
theNumber = "" + theNumber; //parseFloat(theNumber);
theLength = parseInt(theLength);
while (theNumber.length<theLength){
	theNumber = theSymbol + theNumber;
	}
return theNumber;
}

//  End -->


document.onkeyup = KeyCheck;       
function KeyCheck()
{
   var KeyID = window.event.keyCode;
   if(KeyID == 27)
   {
    //parent.location="menu_ham_sidebar2.php";   blocked esc 
   }
}
function KeyCheck()
{
   var KeyID = window.event.keyCode;
   if(KeyID == 8)
   {
    //parent.location="menu_ham_sidebar2.php";   //blocked esc 
   }
}