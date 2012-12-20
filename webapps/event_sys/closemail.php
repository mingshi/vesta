<?php
require_once '/var/www/html/shijian/webapps/libraries/common.lib.php';
require_once '/var/www/html/shijian/webapps/event_sys/smtp.class.php';

if(!session_id())session_start();
if(!isset($_SESSION['user']) || $_SESSION['user'] != true || !isset($_SESSION['name'])){
    msg_redirect('oauthlogin.php','您还未登录');
}else{
    $user = $_SESSION['name'];
    if ($user=='yundu'){
        $result = get_checkclose($pdo);
        foreach ($result as $k){
            $mail_level[$k['level']][] = $k;
        }
        foreach ($mail_level as $m=>$n){
            $body = '';
            foreach ($n as $p){
                $division = '';
                foreach ($p['division'] as $key) {$division.= $cfg['division'][$key].',';}
                $division = rtrim($division, ',');
                $body.= get_mail_body($p['eid'],$p['subject'],$p['description'],$p['affect'],$p['etypeid'],$p['level'],$division,$cfg);
            }
            $subject = "[事件关闭]";
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $smtp =   new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach ($cfg['checkermail'][$m] as $v){
                $smtp->sendmail($v,'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
        }
    }
    else msg_redirect('index.php','您无权发送邮件');
}
