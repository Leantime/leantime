<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $config->sitename; ?></title>

<meta name="description" content="TimelineJS example">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

<?php echo $frontController->includeAction('general.header'); ?>
   
<!-- HTML5 shim, for IE6-8 support of HTML elements--><!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

<link rel="stylesheet" href="/includes/templates/zypro/css/style.default.css" type="text/css" />
<link rel="stylesheet" href="/includes/templates/zypro/css/style.custom.php?color=<?php echo $config->mainColor; ?>" type="text/css" />
<link rel="stylesheet" href="/includes/templates/zypro/css/responsive-tables.css">
<link rel="stylesheet" href="/includes/templates/zypro/css/bootstrap-timepicker.min.css" type="text/css" />
<link rel="stylesheet" href="/includes/templates/zypro/css/bootstrap-fileupload.min.css" type="text/css" />

<script type="text/javascript" src="/includes/templates/zypro/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery-migrate-1.1.1.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery-ui-1.9.2.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/modernizr.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery.cookie.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/tinymce/jquery.tinymce.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/chosen.jquery.min.js"></script>

<script type="text/javascript" src="/includes/templates/zypro/js/jquery.uniform.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js//bootstrap-fileupload.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.pie.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.symbol.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.fillbetween.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.crosshair.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.stack.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.time.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery.isotope.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/responsive-tables.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/custom.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/chart.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery.form.js"></script>

<script type="text/javascript" src="/includes/templates/zypro/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery.datatable-columnFilter.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery.dataTables.sorting.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/tableHandling.js"></script>

<script type="text/javascript" src="/includes/templates/zypro/js/fullcalendar.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/wysiwyg.js"></script>

<!-- Quickselect  -->
<script
	src="/includes/libs/Quickselect/quicksilver.js"
	type="text/javascript"></script>
<script
	src="/includes/libs/Quickselect/jquery.quickselect.js"
	type="text/javascript"></script>
	
<link rel="stylesheet" type="text/css"
	href="/includes/libs/Quickselect/jquery.quickselect.css" />

<!-- Tablsorter Simple  -->
<script
	src="/includes/modules/general/templates/js/jquery.tablesorter.min.js"
	type="text/javascript"></script>
<script
	src="/includes/modules/general/templates/js/jquery.tablesorter.pager.js"
	type="text/javascript"></script>

<script type="text/javascript">
    jQuery(document).ready(function(){
        // dynamic table
        jQuery('#dyntable2').dataTable({
            "sPaginationType": "full_numbers",
            "aaSortingFixed": [[0,'asc']],
            "fnDrawCallback": function(oSettings) {
                jQuery.uniform.update();
            }
        });
        
        jQuery('#dyntable').dataTable( {
            "bScrollInfinite": true,
            "bScrollCollapse": true,
            "sScrollY": "300px"
        });
        
    });

</script>
<!--<script type="text/javascript" src="/includes/templates/zypro/js/forms.js"></script>-->
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="includes/templates/zypro/js/excanvas.min.js"></script><![endif]-->

<!--###HEAD##-->

</head>



<body>

<div class="mainwrapper">
    
    <div class="header">
        <div class="logo">
            <a href="/"><img src="<?php echo $config->logoPath; ?>" style="width:260px; height:110px;" alt="" /></a>
        </div>
        <div class="headerinner">
        	
        	<?php echo $frontController->includeAction('general.headMenu'); ?>
        	
        	<div class="userloggedinfo">
            	<?php echo $frontController->includeAction('general.loginInfo'); ?>	
            </div>
            
        </div>
    </div>
        
    <div class="leftpanel">
        
        <div class="leftmenu">    

        	<?php echo $frontController->includeAction('general.menu'); ?>

        </div><!--leftmenu-->
        
    </div><!-- leftpanel -->
   

    <div class="rightpanel" style="position: relative;">
        
        <ul class="breadcrumbs">
            <li><a href='/calendar/showMyCalendar'><span id='headClock'></span></a></li>
        </ul>

      	<?php echo $frontController->run(); ?>
               
        
        <div class='footer' >
    		  <?php echo $frontController->includeAction('general.footer'); ?>
    	</div>
    	
    </div><!--rightpanel-->   

</div><!--mainwrapper-->
<script type="text/javascript">
    jQuery(document).ready(function() {
        
    
    });
    var weekday=new Array(7);
		weekday[0] = "Sunday";
		weekday[1] = "Monday";
		weekday[2] = "Tuesday";
		weekday[3] = "Wednesday";
		weekday[4] = "Thursday";
		weekday[5] = "Friday";
		weekday[6] = "Saturday";
	
	var months = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
		
	(function startTime() {
		
		var today = new Date();
		var day = weekday[today.getDay()];
		var year = today.getFullYear();
		var month = months[parseFloat(today.getUTCMonth())];
		var dayOfMonth = today.getUTCDate();
		var h = today.getHours();
		var m = today.getMinutes();
		var s = today.getSeconds();
		var abbr = 'AM';
		
		// add a zero in front of numbers<10
		m = checkTime(m);
		s = checkTime(s);
		
		if ( h > 12 ) {
			h -= 12;
			abbr = 'PM';
		} else if ( h == 12 ) {
			abbr = 'PM';
		}
		document.getElementById('headClock').innerHTML = day+", "+month+" "+dayOfMonth+"th "+year+" "+h+":"+m+":"+s+" "+abbr;
		var t = setTimeout(
			function() {
				startTime()
			}, 500
		);
		
	})();
	
	function checkTime(i) {
		
		if ( i < 10 ) 
		  i = "0" + i;

		return i;	
	}
</script>

<!--###FOOTER###-->
</body>
</html>
