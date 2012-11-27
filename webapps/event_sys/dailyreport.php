<?php
require_once '/var/www/html/shijian/webapps/libraries/common.lib.php';
require_once '/var/www/html/shijian/webapps/event_sys/smtp.class.php';


$now =  time();
$hour = date('H',$now);
$minute = date('i',$now);
$second = date('s',$now);

$today = $now - $hour*60*60 - $minute*60 - $second;
$rangea = $today-23400;
$rangeb = $today+63000;


$todaydata = get_today_data($pdo,$rangea,$rangeb);

if($todaydata['critical']){
    $body = "<table style='word-break:break-all;word-wrap:break-all;border-collapse: collapse;clear: both;text-align: center;font-size: 14px;table-layout: fixed;border-top:2px solid #FFFFFF;border-bottom:2px solid #FFFFFF;border-left:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>
        <thead>1)今日发生的重大事件(事件等级>=P3)：<br />
            <tr style='background-color:#4F81BC;color:white;'>
                <th width = 40px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>编号</th>
                <th width = 250px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>名称</th>
                <th width = 70px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>事件等级</th>
                <th width = 152px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>发生时间</th>
                <th width = 130px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>影响时长(分钟)</th>
                <th width = 250px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>事件影响</th>
                <th width = 250px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>当前进展</th>
            </tr>
        </thead><tbody>";

    foreach($todaydata['critical'] as $key=>$v){
        $nowschedule = get_now_schedule($pdo,$v['eid']);
        $body .= "
            <tr style='background-color:#EAEDF6;color:#7B7B7B;'>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'><a href='http://".$cfg['hostname']."/index.php?op=detail&eid=".$v['eid']."'>".$v['eid']."</a></td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".$v['subject']."</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>";
for($a=0;$a<$v['level'];$a++){$body.= "<img border='0' height='15px' src='http://ops-event.yundu.dev.anjuke.com/images/level/reds.png'>";}
for($a=0;$a<(6-$v['level']);$a++){$body.= "<img border='0' height='15px' src='http://ops-event.yundu.dev.anjuke.com/images/level/grays.png'>";}
$body .= "</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".date('m-d H:i:s',$v['createtime'])."</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".$v['affecttime']."</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".$v['affect']."</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".$nowschedule['s_subject']."</td>
            </tr>";
    }
}
else{
    $body = "<table><thead>1)今日没有重大事件发生。<br /></thead><tbody>";
}



if($todaydata['nostop']){        
    $body .= "</tbody></table><br /><br /><br />
        <table style='word-break:break-all;word-wrap:break-all;border-collapse: collapse;clear: both;text-align: center;font-size: 14px;table-layout: fixed;border-top:2px solid #FFFFFF;border-bottom:2px solid #FFFFFF;border-left:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>
    <thead>2)今日未关闭事件进展：<br />
        <tr style='background-color:#4F81BC;color:white;'>
            <th width = 40px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>编号</th>
            <th width = 250px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>名称</th>
            <th width = 70px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>事件等级</th>
            <th width = 152px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>发生时间</th>
            <th width = 130px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>影响时长(分钟)</th>
            <th width = 250px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>事件影响</th>
            <th width = 250px style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>当前进展</th>
        </tr>
    </thead><tbody>";

    foreach($todaydata['nostop'] as $key=>$v){
        $nowschedule = get_now_schedule($pdo,$v['eid']);
        $body .= "
            <tr style='background-color:#EAEDF6;color:#7B7B7B;'>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'><a href='http://".$cfg['hostname']."/index.php?op=detail&eid=".$v['eid']."'>".$v['eid']."</a></td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".$v['subject']."</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>";
for($a=0;$a<$v['level'];$a++){$body.= "<img border='0' height='15px' src='http://ops-event.yundu.dev.anjuke.com/images/level/reds.png'>";}
for($a=0;$a<(6-$v['level']);$a++){$body.= "<img border='0' height='15px' src='http://ops-event.yundu.dev.anjuke.com/images/level/grays.png'>";}
$body .= "</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".date('m-d H:i:s',$v['createtime'])."</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".$v['affecttime']."</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".$v['affect']."</td>
                <td style='border-bottom:2px solid #FFFFFF;border-right:2px solid #FFFFFF;'>".$nowschedule['s_subject']."</td>
            </tr>";
    }
}
else{
    $body .= "</tbody></table><br /><br /><br /><table><thead>2)今日未关闭事件没有新的进展。<br /></thead><tbody>";
}


$body .= "</tbody></table>";

$subject = "安居客事件管理日报";
$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
$smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
$email_arr = array(
        '0' => 'yundu@anjuke.com',
);
/*$email_arr = array(
        '0' => 'dl-tech-ops@anjuke.com',
        '1' => 'justin@anjukeinc.com',
        '2' => 'kevinkuang@anjukeinc.com',
        '3' => 'haisongchang@anjuke.com',
        '4' => 'sarahdu@anjuke.com',
        '5' => 'zmhu@anjuke.com',
        '6' => 'fzhou@anjuke.com',
        '7' => 'enzhang@anjuke.com',
        '9' => 'lenyemeng@anjuke.com',
        '9' => 'wbsong@anjuke.com',
);*/
foreach($email_arr as $k=>$v){
        $smtp->sendmail($v,'事件系统',$subject,$body,$cfg['smtp']['mailtype']);
}


echo $body;
