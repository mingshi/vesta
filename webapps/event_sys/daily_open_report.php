<?php
require_once '/var/www/html/shijian/webapps/libraries/common.lib.php';
require_once '/var/www/html/shijian/webapps/event_sys/smtp.class.php';


$event_unlock = get_event_unlock($pdo);

$today_unlock = array();
foreach ($event_unlock as $key){
    if (!check_open_date($pdo,$key['eid'],time())){
        $today_unlock[] = $key;
    }
}
$result = array();
foreach ($today_unlock as $key){
    if ($key['who']!="") $result[$key['who']][] = $key;
}

foreach ($result as $key){
    $who = $key[0]['who'];
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
        $body = '<span>'.$who.'，您所负责尚未关闭的事件如下：</span><br />';
        foreach($key as $v){
            $body.='<table border="1" cellspacing="0" cellpadding="0" width="500">
<tbody>

<tr style="height:12.95pt">
<td width="633" colspan="2" valign="top" style="width:474.5pt;border:solid gray 1.0pt;background:gray;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt"><p align="center" style="text-align:center"><b><a href="http://'.$cfg['hostname'].'/index.php?op=detail&eid='.$v['eid'].'" style="font-size:10.5pt;color:#262626;text-decoration:none;"><span style="font-size:10.5pt;color:white">[#'.$v['eid'].']'.$v['subject'].'</span></a></b></p>
</td>
</tr>

<tr style="height:12.95pt">
<td width="85" valign="top" style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><b><span style="font-size:10.5pt;color:#595959">事件描述</span></b></p>
</td>
<td width="548" valign="top" style="width:411.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><span style="font-size:10.5pt;color:#595959">'.$v['description'].'</span></p>
</td>
</tr>

<tr style="height:12.95pt">
<td width="85" valign="top" style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><b><span style="font-size:10.5pt;color:#595959">事件影响</span></b></p>
</td>
<td width="548" valign="top" style="width:411.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><span style="font-size:10.5pt;color:#595959">'.$v['affect'].'</span></p>
</td>
</tr>

<tr style="height:12.95pt">
<td width="85" valign="top" style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><b><span style="font-size:10.5pt;color:#595959">事件类型</span></b></p>
</td>
<td width="548" valign="top" style="width:411.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><span style="font-size:10.5pt;color:#595959">'.$cfg['etype'][$v['etypeid']].'</span></p>
</td>
</tr>

<tr style="height:12.95pt">
<td width="85" valign="top" style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><b><span style="font-size:10.5pt;color:#595959">事件等级</span></b></p>
</td>
<td width="548" valign="top" style="width:411.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><span style="font-size:10.5pt;color:#595959">L'.$v['level'].'</span></p>
</td>
</tr>

<tr style="height:12.95pt">
<td width="85" valign="top" style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><b><span style="font-size:10.5pt;color:#595959">责任部门</span></b></p>
</td>
<td width="548" valign="top" style="width:411.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p><span style="font-size:10.5pt;color:#595959">'.$cfg['division'][$v['division']].'</span></p>
</td>
</tr>

<tr style="height:12.95pt">
<td width="633" colspan="2" valign="top" style="width:474.5pt;border:solid gray 1.0pt;border-top:none;background:#A6A6A6;padding:0cm 5.4pt 0cm 5.4pt;height:12.95pt">
<p align="right" style="text-align:right"><b><u><span><a href="http://'.$cfg['hostname'].'/index.php?op=detail&eid='.$v['eid'].'" style="font-size:10.5pt;color:#262626">查看详情</a></span></u></b></p>
</td>
</tr>

</tbody>
</table><br />';
        }
        $subject = "安居客未关闭事件日报";
        $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
        $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
        $email_arr = array(
            //'0' => $who_mail,
            '1' => 'yundu@anjuke.com',
        );
        foreach($email_arr as $k=>$v){
            $smtp->sendmail($v,"",$subject,$body,$cfg['smtp']['mailtype']);
        }
        echo $body;
    }
}

?>
