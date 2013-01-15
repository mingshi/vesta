<?php
require_once '/var/www/html/shijian/webapps/libraries/common.lib.php';
require_once '/var/www/html/shijian/webapps/event_sys/smtp.class.php';


$event_unlock = get_event_unlock($pdo);

$today_unlock = array();
foreach ($event_unlock as $key){
    $measure = check_open_date($pdo,$key['eid'],time());
    if (!empty($measure)){
        $key['measure'] = $measure;
        $today_unlock[] = $key;
    }
}
$result = array();
foreach ($today_unlock as $key){
    if ($key['who']!="") $result[$key['who']][] = $key;
    if ($key['who']!=$key['measure'][0]['muser']) $result[$key['measure'][0]['muser']][] = $key;
}

foreach ($result as $k=>$key){
    $who = $k;
    $name = file_get_contents("data.txt");
    $array = json_decode($name);
    $who_mail = "";
    foreach ($array as $p){
        $ch_name = preg_replace('/[\x00-\x7F]/', '',$p->key);
        if ($ch_name == $who) {
            $who_mail = $p->value;
            break;
        }
    }
    if ($who_mail){
        $body = '<span>'.$who.'，您所负责尚未关闭的事件如下：</span><br /><br />';
        foreach($key as $v){
            $division = '';
            foreach ($v['division'] as $key) {$division.= $cfg['division'][$key].',';}
            $division = rtrim($division, ',');
            $body.= get_mail_body($v['eid'],$v['subject'],$v['description'],$v['affect'],$v['etypeid'],$v['level'],$division,$cfg);
        }
        $subject = "安居客未关闭事件日报";
        $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
        $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
        $email_arr = array(
            //'0' => $who_mail,
            '1' => 'yundu@anjuke.com',
            //'0' => 'cimena1989@163.com',
        );
        foreach($email_arr as $k=>$v){
            $smtp->sendmail($v,"",$subject,$body,$cfg['smtp']['mailtype']);
        }
        echo $body;
    }
}

?>
