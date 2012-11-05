<?php
if(!session_id()) session_start();
if(isset($_SESSION['user']) && $_SESSION['user']===true){
    unset($_SESSION['user']);
    unset($_SESSION['uid']);
    session_destroy();
    header("Location:index.php");
}
