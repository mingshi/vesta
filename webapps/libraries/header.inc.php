<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>OPS Event & Cost</title>
<link rel="stylesheet" type="text/css" href="css/theme.css" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script>
    function ud_get_cookie(c_name) {
        if (document.cookie.length == 0) return "";

        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start == -1) return "";

        c_start = c_start + c_name.length+1;
        c_end = document.cookie.indexOf(";", c_start);
        if (c_end == -1) c_end = document.cookie.length;
        return unescape(document.cookie.substring(c_start, c_end));
    }

    var StyleFile = "theme" + ud_get_cookie("theme") + ".css";
    document.writeln('<link rel="stylesheet" type="text/css" href="css/' + StyleFile + '">');
</script>
<link rel="stylesheet" type="text/css" href="css/validation.css" />
<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/common.js"></script>
<script type="text/javascript" src="js/livevalidation_standalone.compressed.js"></script>
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="css/ie-sucks.css" />
<![endif]-->
</head>
<?php
if(!session_id()) session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']==false){
    header('Location:login.php');
}




?>
