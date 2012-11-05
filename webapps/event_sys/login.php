<?php
require_once '../libraries/common.lib.php';
$op = isset($params['op'])?$params['op']:"";
if(!session_id()) session_start();
if(isset($_SESSION['user']) && $_SESSION['user']===true){
    msg_redirect('index.php','你已经登录过了，无需再次登录');
}elseif($op=="login"){
    $username = $params['username'];
    $passwd = $params['passwd'];
    if(!$username || !$passwd){
        msg_redirect('login.php','填写完整再登录！！NND');
    }else{
            $_SESSION['user'] = false;
            $data = array('u'=>$username,'p'=>$passwd,'f'=>'getinfo');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $cfg['ldapurl']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $res = curl_exec($ch);
            $res = json_decode($res,true);
            curl_close($ch);
            if($res['result']){
                $_SESSION['user'] = true;
                $_SESSION['name'] = $username;
                setcookie('username',$username,time()+3600*24*7);
                setcookie('password',$passwd,time()+3600*24*7);
                $info = $res['result'];
                if(!getuser($pdo,$username)){
                    $realname = $info[0]['chinesename'];
                    $email = $info[0]['email'];
                    $username = $username;
                    $uid = insertuser($pdo,$username,$realname,$email);
                    $_SESSION['uid'] = $uid;
                }else{
                    $_SESSION['uid'] = get_user_info($pdo,$username);
                    $_SESSION['uid'] = $_SESSION['uid']['id'];
                }
                header('Location:index.php'); 
            }else{
                msg_redirect('login.php','登录失败');
            } 
    }
}else{
    $template = 'login';
    require_once (SC_PATH.'/event_sys/'.$template.'.html');
}
?>
