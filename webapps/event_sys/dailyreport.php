<?php
require_once '/var/www/html/shijian/webapps/libraries/common.lib.php';
require_once '/var/www/html/shijian/webapps/event_sys/smtp.class.php';

$todaydata = get_today_data($pdo);
if($todaydata['critical']){
$body = "
<table>
    <thead>
    1)当天重大事件(>=P3):<br />
        <tr style='background-color:#0099CC;'>
            <th>编号</th>
            <th>名称</th>
            <th>发生时间</th>
            <th>影响时长(分钟)</th>
            <th>事件影响</th>
            <th>事故类型</th>
            <th>时间等级</th>
            <th>当前进展</th>
           
        </tr>
    </thead>
    <tbody>";
}
    if($todaydata['critical']){ foreach($todaydata['critical'] as $key=>$v){
        $nowschedule = get_now_schedule($pdo,$v['eid']);
        $body .= "
        <tr>
            <td>".$v['eid']."</td>
            <td><a href='http://".$cfg['hostname']."/index.php?op=detail&eid=".$v['eid']."'>".$v['subject']."</a></td>
            <td>".date('Y-m-d H:i:s',$v['createtime'])."</td>
            <td>".$v['affecttime']."</td>
            <td>".$v['affect']."</td>
            <td>".$cfg['etype'][$v['etypeid']]."</td>
            <td>".$cfg['level'][$v['level']]."</td>
            <td>".$nowschedule['s_subject']."</td>
        </tr>";
    }}else{ $body .= '当日没有重大事故<br />'; }
if($todaydata['common']){        
$body .= "
    </tbody>
</table>
<br />
<br />
<br />

<table>
    <thead>
    2)当天其他事件(<3):<br />
        <tr style='background-color:#0099CC;'>
            <th>编号</th>
            <th>名称</th>
            <th>发生时间</th>
            <th>影响时长(分钟)</th>
            <th>事件影响</th>
            <th>事故类型</th>
            <th>时间等级</th>
            <th>当前进展</th>
            

        </tr>
    </thead>
    <tbody>";
}
    if($todaydata['common']){ foreach($todaydata['common'] as $key=>$v){
        $nowschedule = get_now_schedule($pdo,$v['eid']);
        $body .= "
        <tr>
            <td>".$v['eid']."</td>
            <td><a href='http://".$cfg['hostname']."/index.php?op=detail&eid=".$v['eid']."'>".$v['subject']."</a></td>
            <td>".date('Y-m-d H:i:s',$v['createtime'])."</td>
            <td>".$v['affecttime']."</td>
            <td>".$v['affect']."</td> 
            <td>".$cfg['etype'][$v['etypeid']]."</td>
            <td>".$cfg['level'][$v['level']]."</td>
            <td>".$nowschedule['s_subject']."</td>
        </tr>";   
    }}else{ $body .= '当日没有其他事故<br />'; }
if($todaydata['nostop']){        
$body .= "
    </tbody>
</table>

<br />
<br />
<br />

<table>
    <thead>
    3)未结束事件进展:<br />
        <tr style='background-color:#0099CC;'>
            <th>编号</th>
            <th>名称</th>
            <th>发生时间</th>
            <th>影响时长(分钟)</th>
            <th>事件影响</th>
            <th>事故类型</th>
            <th>时间等级</th>
            <th>当前进展</th>


        </tr>
    </thead>
    <tbody>";
}
    if($todaydata['nostop']){ foreach($todaydata['nostop'] as $key=>$v){
        $nowschedule = get_now_schedule($pdo,$v['eid']);
        $body .= "
        <tr>
            <td>".$v['eid']."</td>
            <td><a href='http://".$cfg['hostname']."/index.php?op=detail&eid=".$v['eid']."'>".$v['subject']."</a></td>
            <td>".date('Y-m-d H:i:s',$v['createtime'])."</td>
            <td>".$v['affecttime']."</td>
            <td>".$v['affect']."</td>
            <td>".$cfg['etype'][$v['etypeid']]."</td>
            <td>".$cfg['level'][$v['level']]."</td>
            <td>".$nowschedule['s_subject']."</td>
        </tr>";
    }}else{ $body .= '当日没有未关闭事故<br />'; }
$body .= "
    </tbody>
</table>
";

$subject = "当天事件基本情况";
$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
$smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
$email_arr = array(
        '0' => 'dl-tech-ops@anjuke.com',
        '1' => 'justin@anjukeinc.com',
        '2' => 'kevinkuang@anjukeinc.com',
        '3' => 'haisongchang@anjuke.com',
        '4' => 'sarahdu@anjuke.com',
        '5' => 'zmhu@anjuke.com',
        '6' => 'fzhou@anjuke.com',
        '7' => 'peterchen@anjuke.com',
        '8' => 'enzhang@anjuke.com',
        '9' => 'lenyemeng@anjuke.com',
        '10' => 'wbsong@anjuke.com',
);
foreach($email_arr as $k=>$v){
        $smtp->sendmail($v,'事件系统',$subject,$body,$cfg['smtp']['mailtype']);
}
