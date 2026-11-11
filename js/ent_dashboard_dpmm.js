
// DXImageTransform effects, assign the below DXImageTransformFilter variable to one of these desired effects.
// More on DXImageTransform effects: http://msdn.microsoft.com/library/default.asp?url=/workshop/author/filter/filters.asp
var DXImageTransformLibrary = new Array
(
	"FILTER=progid:DXImageTransform.Microsoft.Wheel(duration=2, spokes=5)",
	"FILTER=progid:DXImageTransform.Microsoft.Barn(duration=2, orientation=horizontal)",
	"FILTER=progid:DXImageTransform.Microsoft.Blinds(duration=2, bands=5)",
	"FILTER=progid:DXImageTransform.Microsoft.CheckerBoard(duration=2)",
	"FILTER=progid:DXImageTransform.Microsoft.Fade(duration=2)",
	"FILTER=progid:DXImageTransform.Microsoft.GradientWipe(duration=2, wipeStyle=0)",
	"FILTER=progid:DXImageTransform.Microsoft.Iris(duration=2,irisStyle=STAR)",
	"FILTER=progid:DXImageTransform.Microsoft.Iris(duration=2,irisStyle=CIRCLE)",
	"FILTER=progid:DXImageTransform.Microsoft.Pixelate(duration=2, maxSquare=40)",
	"FILTER=progid:DXImageTransform.Microsoft.Wheel(duration=2, spokes=5)",
	"FILTER=progid:DXImageTransform.Microsoft.RandomDissolve(duration=0.5)",
	"FILTER=progid:DXImageTransform.Microsoft.Spiral(duration=2)",
	"FILTER=progid:DXImageTransform.Microsoft.Stretch(duration=2, stretchStyle=push)",
	"FILTER=progid:DXImageTransform.Microsoft.Strips(duration=2, motion=rightdown)"
);


// Assign this to one of the above DXImageTransform effects, or null for random effects.
	/*
		e.g.
		var DXImageTransformFilter = "FILTER=progid:DXImageTransform.Microsoft.Fade(duration=2)";
		or
		var DXImageTransformFilter = DXImageTransformLibrary[4];
		or
		var DXImageTransformFilter = null;
	*/
var DXImageTransformFilter = null;

/***************************************************************************************************/

var HTML = "";

function tabOnClick(ID, parent)
{ 
	var oElement = null;
	for (var i = 1; i < tabs.length; i++)
	{
		oElement = document.getElementById(i);
		oElement.className = "tabOff";
	}	

	oElement = document.getElementById(ID);
	oElement.className = "tabOn";
	var tab = tabs[parent][0].split("|");

	HTML= "<IFRAME SRC='"+tab[1]+" 'CLASS='tabFrame'>&nbsp";
    HTML += "</IFRAME>";
	divTabFrame.innerHTML = HTML;
	document.body.focus();
}

function tabLoadParents()
{

	for (var i = 1; i < tabs.length; i++)
	{
		var tab = tabs[i][0].split("|");
		HTML += "<INPUT TYPE='BUTTON' ID="+i+" CLASS='tabOff' VALUE='"+tab[0]+"' onClick='tabOnClick("+i+", "+i+")'>&nbsp";

    }

	if (window.cmdfrom) {
    	HTML += "<input type='button' value='MENU' name='menu' CLASS='tabOff' onClick=" + "window.location='menu"+cmdfrom+".php'" + ">"
    }
    else
    {
	    HTML += "<input type='button' value='MENU' name='menu' CLASS='tabOff' onClick=" + "window.location='dashboard_dpmm.php'" + ">"
    }


	
}

function tabOnLoad()
{
	HTML += "<TABLE BORDER='0' CELLPADDING='0' CELLSPACING='0' WIDTH='100%'>";
	HTML += "<TD ALIGN='LEFT'>&nbsp;&nbsp;";

	tabLoadParents();

	HTML += "</TD></TR>";
	HTML += "</TABLE>";	

	divTabStrip.innerHTML = HTML;

	
	for (var i = 1; i < tabs.length; i++)
	{
		var tab = tabs[i][0].split("|");

		if (tab[2] == "*")
		{
			tabOnClick(i, i, 0);

			break;			
		}
	}
}


document.onkeyup = KeyCheck;       
function KeyCheck()
{
   var KeyID = window.event.keyCode;
   if(KeyID == 27)
   {
    parent.location="dashboard_podm.php";
   }
}

