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

$body = '<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>';

if (!$todaydata['critical'] && !$todaydata['nostop']){
    $body .= "恭喜，今日没有重大事件发生，您可以登录"."<a href='http://".$cfg['hostname']."/index.php'>事件管理系统</a>查看更多";

}
else{
    if($todaydata['critical']){
        $body .= "1)今日重大事件(事件等级>=P3)：<br /><table style='width:1000px;word-break:break-all;text-align: center;font-size: 15px;table-layout: fixed;border-top:1px solid #FFFFFF;border-bottom:1px solid #FFFFFF;border-left:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>
                <tbody><tr style='background-color:#4F81BC;color:white;'>
                    <th width = 50px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>编号</th>
                    <th width = 190px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>事件名称</th>
                    <th width = 100px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>事件等级</th>
                    <th width = 80px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>发生时间</th>
                    <th width = 120px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>影响时长(分钟)</th>
                    <th width = 170px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>事件影响</th>
                    <th width = 270px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>进展</th>
                </tr>";

        foreach($todaydata['critical'] as $key=>$v){
            $nowschedule = get_now_schedule($pdo,$v['eid']);
            $body .= "
                <tr style='background-color:#EAEDF6;color:#7B7B7B;'>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'><a href='http://".$cfg['hostname']."/index.php?op=detail&eid=".$v['eid']."'>".$v['eid']."</a></td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>".$v['subject']."</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>";
    for($a=0;$a<$v['level'];$a++){$body.= "<img border='0' height='15px' src='http://vesta.corp.anjuke.com/images/level/reds.png'>";}
    for($a=0;$a<(6-$v['level']);$a++){$body.= "<img border='0' height='15px' src='http://vesta.corp.anjuke.com/images/level/grays.png'>";}
    $body .= "</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>".date('H:i:s',$v['createtime'])."</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>".$v['affecttime']."</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;text-align: left;'>".$v['affect']."</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;text-align: left;'>".$nowschedule['s_subject']."</td>
                </tr>";
        }
    }
    else{
        $body .= "1)今日没有重大事件发生。<br /><table><tbody>";
    }



    if($todaydata['nostop']){        
        $body .= "</tbody></table><br /><br /><br />
            2)未关闭事件当前进展：<br /><table style='width:1000px;word-break:break-all;text-align: center;font-size: 15px;table-layout: fixed;border-top:1px solid #FFFFFF;border-bottom:1px solid #FFFFFF;border-left:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'><tbody>
            <tr style='background-color:#4F81BC;color:white;'>
                <th width = 50px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>编号</th>
                <th width = 190px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>事件名称</th>
                <th width = 100px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>事件等级</th>
                <th width = 80px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>发生时间</th>
                <th width = 120px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>影响时长(分钟)</th>
                <th width = 170px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>事件影响</th>
                <th width = 270px style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>进展</th>
            </tr>";

        foreach($todaydata['nostop'] as $key=>$v){
            $nowschedule = get_now_schedule($pdo,$v['eid']);
            $body .= "
                <tr style='background-color:#EAEDF6;color:#7B7B7B;'>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'><a href='http://".$cfg['hostname']."/index.php?op=detail&eid=".$v['eid']."'>".$v['eid']."</a></td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>".$v['subject']."</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>";
    for($a=0;$a<$v['level'];$a++){$body.= "<img border='0' height='15px' src='http://vesta.corp.anjuke.com/images/level/reds.png'>";}
    for($a=0;$a<(6-$v['level']);$a++){$body.= "<img border='0' height='15px' src='http://vesta.corp.anjuke.com/images/level/grays.png'>";}
    $body .= "</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>".date('H:i:s',$v['createtime'])."</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;'>".$v['affecttime']."</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;text-align: left;'>".$v['affect']."</td>
                    <td style='border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF;text-align: left;'>".$nowschedule['s_subject']."</td>
                </tr>";
        }
    }


    $body .= "</tbody></table></body></html>";
}
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
