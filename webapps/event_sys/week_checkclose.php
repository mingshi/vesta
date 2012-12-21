<?php
require_once '/var/www/html/shijian/webapps/libraries/common.lib.php';
require_once '/var/www/html/shijian/webapps/event_sys/smtp.class.php';

$result = get_checkclose($pdo);

if (empty($result)){
    $body = '没有需要确认关闭的事件';
}
else{
    $body = '';
    foreach ($result as $m){
        $division = '';
        foreach ($m['division'] as $key) {$division.= $cfg['division'][$key].',';}
        $division = rtrim($division, ',');
        $body.= get_mail_body($m['eid'],$m['subject'],$m['description'],$m['affect'],$m['etypeid'],$m['level'],$division,$cfg);
    }
    $body.='<span><a href="http://'.$cfg['hostname'].'/closemail.php">确认发送</a></span>';
}
$subject = "[关闭邮件发送确认]";
$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
$smtp =   new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
$smtp->debug = false;
$smtp->sendmail($cfg['checkclose'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);

echo $body;


?>
