<?php
if(!session_id()) session_start();
if(isset($_SESSION['user']) && $_SESSION['user']===true){
    unset($_SESSION['user']);
    unset($_SESSION['uid']);
    session_destroy();
    header("Location:index.php");
    $postinfo = array(
	    "client_id"=>'sl11011',
	    "client_secret"=>'d32a2b01',
	);
    header("Location: " . 'http://auth.corp.anjuke.com/logout.php?'.http_build_query($postinfo));
}
