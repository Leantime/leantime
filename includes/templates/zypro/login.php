<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />


<title><!--###TITLE###--></title>
<!--###HEADER###-->

<link rel="stylesheet" href="/includes/templates/zypro/css/style.default.css" type="text/css" />
<link rel="stylesheet" href="/includes/templates/zypro/css/style.custom.php?color=<!--###MAINCOLOR###-->" type="text/css" />
<link rel="stylesheet" href="/includes/templates/zypro/css/responsive-tables.css">
<script type="text/javascript" src="/includes/templates/zypro/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery-migrate-1.1.1.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery-ui-1.9.2.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/modernizr.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery.cookie.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/jquery.uniform.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/flot/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/responsive-tables.js"></script>
<script type="text/javascript" src="/includes/templates/zypro/js/custom.js"></script>

<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="includes/templates/zypro/js/excanvas.min.js"></script><![endif]-->
</head>

<script type="text/javascript">
    jQuery(document).ready(function(){
    	
    	if(jQuery('.login-alert .alert').text() != ''){
    		jQuery('.login-alert').fadeIn();
    	}
    	
    	
        jQuery('#login').submit(function(){
            var u = jQuery('#username').val();
            var p = jQuery('#password').val();
            if(u == '' && p == '') {
                jQuery('.login-alert').fadeIn();
                return false;
            }
        });
    });
</script>
</head>

<body class="loginpage">

<div class="loginpanel">
    <div class="loginpanelinner">

        <div class="logo animate0 bounceIn"><img src="/includes/templates/zypro/images/leantime-blueBg.png" alt="" /></div>
        <form id="login" action="/dashboard/show" method="post">

             <div class="inputwrapper login-alert">
                <div class="alert alert-error"><!--###INFO###--></div>
            </div>
            <div class="inputwrapper animate1 bounceIn">
                <input type="text" name="username" id="username" placeholder="Enter any username" />
            </div>
            <div class="inputwrapper animate2 bounceIn">
                <input type="password" name="password" id="password" placeholder="Enter any password" />
            </div>
            <div class="inputwrapper animate3 bounceIn">
                <button name="login" value="Login">Login</button>
            </div>
            <div class="inputwrapper animate4 bounceIn">
            	  <a href="mailto:info@intheleantime.com" style="color:#fff; float:right; margin-top:10px;">Forgot password</a>
                <br />
              
            </div>
            
        </form>
    </div><!--loginpanelinner-->
</div><!--loginpanel-->

<div class="loginfooter">
    <p></p>
</div>

</body>
</html>
