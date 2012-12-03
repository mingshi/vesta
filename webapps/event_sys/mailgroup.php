<?php
require_once '../libraries/common.lib.php';
$op = isset($params['op'])?$params['op']:"";
if(!session_id())session_start();
if(!isset($_SESSION['user']) || $_SESSION['user'] != true || !isset($_SESSION['name'])){
    msg_redirect('oauthlogin.php');
}else{
    switch($op){
        default:
            $uid = $_SESSION['uid'];
            $mymailgroup = get_my_mailgroup($pdo,$uid);
            if($mymailgroup){
                foreach($mymailgroup as $k=>$v){
                    $mymailgroup[$k]['mail_arr'] = explode(',',$v['mail_arr']);
                }
            }
            $template = 'mailgroup';
            break;
        case 'add':
            $mail_arr = "";
            $mail = $params['select3'];
            $groupname = trim($params['groupname']);
            if(!$mail) msg_redirect('mailgroup.php','没有选择用户');
            if(!$groupname) msg_redirect('mailgroup.php','没有填写群组名');
            $uid = $_SESSION['uid'];
            foreach($mail as $v){
                $mail_arr .= $v.",";
            }
            $mail_arr = trim($mail_arr,',');
            $mailgroup['uid'] = $uid;
            $mailgroup['gname'] = $groupname;
            $mailgroup['mail_arr'] = $mail_arr;
            insert_mail_group($pdo,$mailgroup);
            msg_redirect('mailgroup.php');
        case 'del':
            $id = intval($params['id']);
            delete_my_mailgroup($pdo,$id);
            msg_redirect('mailgroup.php');
    }
    $current_nav='index';
    require_once '../libraries/decorator.inc.php';
}
