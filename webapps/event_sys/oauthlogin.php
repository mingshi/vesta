<?php
require_once '../libraries/common.lib.php';
$client_id = 'sl11011';/*在oAuth注册的应用名*/
$client_secret = 'd32a2b01';/*在oAuth注册的应用密码*/
$oauth_url = 'https://auth.corp.anjuke.com';/*线上oAuth地址*/ 
if(!session_id()) session_start();
/*
* 用户身份认证，
* 成功则返回用户域账户名和访问令牌
*/
function login_with_oauth($client_id, $client_secret, $oauth_url){
    if(isset($_REQUEST['code']) && $_REQUEST['code']){
        /*2、用临时令牌，申请访问令牌*/
        $data = array(
            "client_id"=>$client_id,
            "client_secret"=>$client_secret,
            "grant_type"=>'authorization_code',/*默认*/
            "code"=>$_REQUEST['code'],/*临时令牌*/
        );

        header("HTTP/1.1 302 Found");
        header("Location: " . $oauth_url.'/token.php?'.http_build_query($data));
        exit();

    }
    if(isset($_REQUEST['access_token']) && $_REQUEST['access_token']){
        /*3、用AccessToken,获取info*/
        $access_token = $_REQUEST['access_token'];

        $data = array(
                "oauth_token"=>$access_token,/*只要一个$access_token，是不是很危险？？*/
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $oauth_url."/resource.php");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $info = curl_exec($ch);
        if($info) return $info;
        else return false;
        exit();
    }
    /*1、获取临时令牌RequestToken*/
    header("HTTP/1.1 302 Found");
    $array = array(
            "client_id"=>$client_id,
            "response_type"=>"code"/*默认*/
    );
    header("Location: " . $oauth_url.'/authorize.php?'.http_build_query($array));
    exit;

}
/**
 * 用户注册流程，
 * 用访问令牌到oauth获取用户详细信息
 */
function get_info_from_ldap($access_token, $oauth_url){
    $data = array(
            "oauth_token"=>$access_token,
            "getinfo"=>true,
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $oauth_url."/resource.php");
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $info = curl_exec($ch);
    if($info) return $info;
    else return false;
}


$info = login_with_oauth($client_id, $client_secret, $oauth_url);
if(!$info){
    echo "登录失败，请重试，若多次尝试失败请联系管理员";exit;
}else{
    $info = json_decode($info,true);
    $_SESSION['user'] = true;
    $_SESSION['name'] = $info['username'];
    setcookie('username',$info['username'],time()+3600*24*7);
    //setcookie('password',$passwd,time()+3600*24*7);
    //$info = $res['result'];
    if(!getuser($pdo,$info['username'])){
        $detail = get_info_from_ldap($info['access_token'], $oauth_url);
        $detail = json_decode($detail,true);
        $realname = $detail['chinese_name'];
        $email = $detail['email'];
        $username = $info['username'];
        $uid = insertuser($pdo,$username,$realname,$email);
        $_SESSION['uid'] = $uid;
        $_SESSION['realname'] = $realname;
    }else{
        $_SESSION['uid'] = get_user_info($pdo,$info['username']);
        $_SESSION['realname'] = $_SESSION['uid']['realname'];
        $_SESSION['uid'] = $_SESSION['uid']['id'];
    }   
    header('Location:index.php');
}
/*检查用户是否注册，已注册，则正常登录*/
/***************/
/*检查用户是否注册，未注册，则继续获取详细信息*/
//$info = get_info_from_ldap($access_token, $oauth_url);
//var_dump($info);
//if(!$info){
//    echo "注册失败，读不到此人域信息，请重试，若多次尝试失败请联系管理员";exit;
//}
