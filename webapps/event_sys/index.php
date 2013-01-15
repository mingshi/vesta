<?php
require_once '../libraries/common.lib.php';
require_once 'smtp.class.php';
error_reporting(0);
$op = isset($params['op'])?$params['op']:"";
switch($op){
case 'add':
        if(!session_id()) session_start();
        if(isset($_SESSION['user']) && $_SESSION['user'] === true){
            $uid = $_SESSION['uid'];
            $mymailgroup = get_my_mailgroup($pdo,$uid);
            $template = 'event_add';
        }else{
            msg_redirect('oauthlogin.php','please login in first');
        }
        break;
case 'm_add':
    $measure['eid'] = intval($params['m_eid_add']);
    $measure['measure'] = trim($params['measure']);
    $measure['muser'] = trim($params['muser']);
    $measure['mtime'] = strtotime($params['mtime']);
    $measure['status'] = intval($params['status']);
    if(!$measure['measure'] || !$measure['muser'] || !$measure['mtime']){
        msg_redirect('index.php?op=edit&eid='.$measure['eid'],'填写完整再提交！');
    }else{
        if(insert_measure($pdo,$measure)){
            $email_arr = get_email_arr($pdo,$measure['eid']);
            $esub = get_event_info($pdo,$measure['eid']);
            $division = '';
            foreach ($esub['division'] as $key) {$division.= $cfg['division'][$key].',';}
            $division = rtrim($division, ',');
            $subject = "[事件更新]  ".$esub['base']['subject'];
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = get_mail_body($esub['base']['eid'],$esub['base']['subject'],$esub['base']['description'],$esub['base']['affect'],$esub['base']['etypeid'],$esub['base']['level'],$division,$cfg);
            $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
            msg_redirect('index.php?op=edit&eid='.$measure['eid'],'');

        }else{
            msg_redirect('index.php?op=edit&eid='.$measure['eid'],'增加失败');
        }
    }
    break;
case 'm_del':
    $mid = intval($params['mid']);
    $eid = intval($params['eid']);
    if(delete_measure($pdo,$mid)){
        msg_redirect('index.php?op=edit&eid='.$eid);
    }else{
        msg_redirect('index.php?op=edit&eid='.$eid,'删除失败！');
    }
    break;
case 'do_add':
    /*
        $user_list = get_user_list($pdo);
        $user_json = "[";
        foreach($user_list as $k=>$val){
            $val['email'] = trim($val['email']);
            $email = explode('@',$val['email']);
            $email_f = $email[0]; 
            $user_json .= '{"key":'.'"'.$val['realname'].$email_f.'","value":'.'"'.$val['email'].'"},';
        }
        $user_json = trim($user_json,',')."]";
        file_put_contents('/tmp/data.txt',$user_json);exit;
     */
        $htmlData = '';
        $content = $params['content'];
        $content = preg_replace ("/"."font-family"."([\s\S]*)".";"."/iU", "", $content);
        $content = preg_replace ("/"."white-space"."([\s\S]*)".";"."/iU", "", $content);
        if (!empty($content)) {
                if (get_magic_quotes_gpc()) {
                        $htmlData = stripslashes($content);
                } else {
                        $htmlData = $content;
                }
        }
        if(!trim($params['subject'])){
            msg_redirect('index.php?op=add','empty subject!');
        }elseif(!trim($params['user'])){
            msg_redirect('index.php?op=add','empty user!');
        }else{
            $event_attr['subject'] = trim($params['subject']);
            $event_attr['description'] = trim($params['description']);
            $event_attr['affect'] = trim($params['affect']);
            $event_attr['summary'] = trim($params['summary']);
            $event_attr['etypeid'] = intval($params['etype']);
            $event_attr['stypeid'] = intval($params['stype']);
            $event_attr['level'] = intval($params['level']);
            if(!$params['createtime']){
                $event_attr['createtime'] = time();
            }else{
                $event_attr['createtime'] = strtotime($params['createtime']);
            }
            $event_attr['fuser'] = trim($params['user']);
            $event_attr['islock'] = intval($params['islock']);
            $event_attr['htmlData'] = $htmlData;
            $event_attr['addtime'] = time();
            $isinsert = insert_event($pdo,$event_attr);
            if($isinsert){
                for($i=1;$i<=20;$i++){
                    if ($params['division'.$i])$params['division'][] = $params['division'.$i];
                }
                $params['division'] = array_unique($params['division']);
                foreach ($params['division'] as $key){
                    insert_division($pdo,$isinsert,$key);
                }

            $division = '';
            foreach ($params['division'] as $key) {$division.= $cfg['division'][$key].',';}
            $division = rtrim($division, ',');
                if($params['select3'] || $params['mailgroup']){
                $params['select3'] = implode(',',$params['select3']);
                $params['mailgroup'] = trim($params['mailgroup'],',');
                $params['select3'] = $params['select3'].",".$params['mailgroup'];
                $params['select3'] = trim($params['select3'],',');
                $params['select3'] = explode(',',$params['select3']);
                foreach($params['select3'] as $v){
                    $to .= $v.",";   
                }
                $to = trim($to,',');
                $subject = "[新事件]  ".$event_attr['subject'];
                $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
                $body = get_mail_body($isinsert,$event_attr['subject'],$event_attr['description'],$event_attr['affect'],$event_attr['etypeid'],$event_attr['level'],$division,$cfg);
                $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
                $smtp->debug = false;
                $smtp->sendmail($to,'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
                update_event_to($pdo,$isinsert,$to); 
            }
                msg_redirect('index.php','add event success!');
            }else{
                msg_redirect('index.php?op=add','add event failed!');
            }
        }
        break;
    case 'detail':
        if(!session_id())session_start();
        $eid = intval($params['eid']);
        if(isset($_SESSION['user']) && $_SESSION['user']===true && isset($_SESSION['name'])){
            $username = $_SESSION['name'];
        }else{
            $_SESSION['eid'] = $eid;
            msg_redirect('oauthlogin.php');	
        }

        update_view_count ($pdo,$eid);
        $event_info = get_event_info($pdo,$eid);
        $report = get_event_report($pdo,$eid);
        $measure = get_event_measure($pdo,$eid);
		$comment = get_event_comment($pdo,$eid);
        if(!$event_info) msg_redirect('index.php','Parameter error OR No result!');
        $template = 'event_detail';
        break;
    case 'edit':
        if(!session_id())session_start();
        if(isset($_SESSION['user']) && $_SESSION['user']===true && isset($_SESSION['name'])){
            $username = $_SESSION['name'];
        }
        $eid = intval($params['eid']);
        update_view_count ($pdo,$eid);
        $event_info = get_event_info($pdo,$eid);
        $report = get_event_report($pdo,$eid);
        $measure = get_event_measure($pdo,$eid);
		$comment = get_event_comment($pdo,$eid);
        if(!$event_info) msg_redirect('index.php','Parameter error OR No result!');
        $template = 'event_edit';
        break;
    case 'week':
        $event_week = get_week_event($pdo);
        $template = 'week_event';
        break;
    case 'lastweek':
        $event_week = get_last_week($pdo);
        $template = 'last_week';
        break;
    case 'month':
        $event_month = get_month_event($pdo);
        $template = 'month_event'; 
        break;
    case 'lastmonth':
        $event_month = get_last_month($pdo);
        $template = 'last_month';
        break;
	case 'params':
		$params_name = isset($params['params_name'])?$params['params_name']:"";
		$params_value = isset($params['params_value'])?$params['params_value']:"";
        if ($params_name == "division"){
            $event_params = get_search_division($pdo,$params_value);
        }
		else $event_params = get_events_by_params($pdo,array("params_name"=>$params_name,"params_value"=>$params_value,));
		$template = 'event_params'; 
		break;
    case 'relate':
        $relate_name = isset($params['relate_name'])?$params['relate_name']:"";
        $event_params = get_relate_event ($pdo,$relate_name);
        $template = 'event_params';
        break;
    case 's_add':
        $schedule['s_subject'] = trim($params['s_subject']);
        $schedule['s_user'] = trim($params['s_user']);
        $schedule['s_time'] = strtotime($params['s_start']);
        $schedule['eid'] = intval($params['s_eid']);
        if(!$schedule['s_subject'] || !$schedule['s_user'] || !$schedule['s_time']){
            msg_redirect('index.php?op=edit&eid='.$schedule['eid'],'add schedule failed');
        }
        if(insert_schedule($pdo,$schedule)){
            $email_arr = get_email_arr($pdo,$schedule['eid']);
            $esub = get_event_info($pdo,$schedule['eid']);
            $division = '';
            foreach ($esub['division'] as $key) {$division.= $cfg['division'][$key].',';}
            $division = rtrim($division, ',');
            $subject = "[事件更新]  ".$esub['base']['subject'];
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = get_mail_body($esub['base']['eid'],$esub['base']['subject'],$esub['base']['description'],$esub['base']['affect'],$esub['base']['etypeid'],$esub['base']['level'],$division,$cfg);
            $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
            msg_redirect('index.php?op=edit&eid='.$schedule['eid'],'');
        }else{
            msg_redirect('index.php?op=edit&eid='.$schedule['eid'],'add schedule failed');
        }
        break;
	case 'c_add':
		$comment['comment'] = nl2br($_POST['m_comment']);
		$comment['eid'] = intval($params['c_eid']);
		$comment['user'] = trim($params['c_user']);
		$comment['mtime'] = time();
		if(!$comment['comment'] || !$comment['user'] || !$comment['mtime']){
            msg_redirect('index.php?op=detail&eid='.$comment['eid'],'add comment failed');
        }
		if(insert_comment($pdo,$comment)){
			msg_redirect('index.php?op=detail&eid='.$comment['eid'],'评论成功');
		}
		else{
			msg_redirect('index.php?op=detail&eid='.$comment['eid'],'add comment failed');
		}
		break;
    case 'attention':
        if(!session_id())session_start();
        if(!isset($_SESSION['user']) || $_SESSION['user'] != true || !isset($_SESSION['name'])){
            msg_redirect('oauthlogin.php','请先登录再添加');
        }else{
            $att['uid'] = $_SESSION['uid'];
            $att['eid'] = intval($params['eid']);
            $att['time'] = time();
            if(!$att['uid'] || !$att['eid']){
                msg_redirect('index.php','参数有问题');
            }else{
                if(!check_attention($pdo,$att)){
                    if(insert_attention($pdo,$att)){
                        msg_redirect('index.php?op=myatt','添加关注成功');
                    }else{
                        msg_redirect('index.php?op=myatt','添加关注失败');
                    }
                }
                else msg_redirect('index.php','已经关注该事件');
            }
        }
        break;
     case 'delatt':
	if(!session_id())session_start();
	if(!isset($_SESSION['user']) || $_SESSION['user'] != true || !isset($_SESSION['name'])){
		msg_redirect('oauthlogin.php','请先登录再添加');	
        }else{
		$att['uid'] = $_SESSION['uid'];
		$att['eid'] = intval($params['eid']);
		if(!$att['uid'] || !$att['eid']){
                	msg_redirect('index.php','参数有问题');
            	}else{
            if(check_attention($pdo,$att)){
			    if(del_att($pdo,$att['uid'],$att['eid'])){
				    msg_redirect('index.php?op=myatt','取消关注成功');
			    }else{
				    msg_redirect('index.php?op=myatt','取消关注失败');
			    }
            }
            else msg_redirect('index.php?op=myatt','尚未关注该事件');
		}
	}
	break;
    case 'e_edit':
        $eid = intval($params['eid']);

        $s_sid = trim($params['s_sid']);
        $s_sid = substr($s_sid,0,-1); 
        $sids = array();
        $sids = (explode(',',$s_sid));
        if ($sids[0]){
        foreach ($sids as $sid){
            $schedule['sid'] = intval($sid);
            $schedule['s_subject'] = trim($params['edit_subject_'.$schedule['sid']]);
            $schedule['s_user'] = trim($params['edit_suser_'.$schedule['sid']]);
            $schedule['s_time'] = strtotime($params['edit_stime_'.$schedule['sid']]);
            if(!$schedule['s_subject'] || !$schedule['s_user'] || !$schedule['s_time']) msg_redirect('index.php?op=edit&eid='.$eid,'填写完整事件处理过程再提交!');
            else update_schedule($pdo,$schedule);
        }}



        $m_mid = trim($params['m_mid']);
        $m_mid = substr($m_mid,0,-1);
        $mids = array();
        $mids = (explode(',',$m_mid));
        if ($mids[0]){
        foreach ($mids as $mid){
            $measure['mid'] = intval($mid);
            $measure['measure'] = trim($params['c_measure_'.$measure['mid']]);
            $measure['muser'] = trim($params['c_user_'.$measure['mid']]);
            $measure['mtime'] = strtotime($params['c_time_'.$measure['mid']]);
            $measure['status'] = intval($params['mstatus_'.$measure['mid']]);
            if(!$measure['measure'] || !$measure['muser'] || !$measure['mtime']){
                msg_redirect('index.php?op=edit&eid='.$eid,'填写完整改进措施再提交！');
            }else{
                if(update_measure($pdo,$measure)){
                    $email_arr = get_email_arr($pdo,$eid);
                    $esub = get_event_info($pdo,$eid);
                    $division = '';
                    foreach ($esub['division'] as $key) {$division.= $cfg['division'][$key].',';}
                    $division = rtrim($division, ',');
                    $subject = "[事件更新]  ".$esub['base']['subject'];
                    $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
                    $body = get_mail_body($esub['base']['eid'],$esub['base']['subject'],$esub['base']['description'],$esub['base']['affect'],$esub['base']['etypeid'],$esub['base']['level'],$division,$cfg);
                    $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
                    $smtp->debug = false;
                    foreach($email_arr as $k=>$v){
                        $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
                    }
                }
            }
        }}

        $htmlData = '';
        $content = $params['content'];
        $content = preg_replace ("/"."font-family"."([\s\S]*)".";"."/iU", "", $content);
        $content = preg_replace ("/"."white-space"."([\s\S]*)".";"."/iU", "", $content);
        if (!empty($content)) {
                if (get_magic_quotes_gpc()) {
                        $htmlData = stripslashes($content);
                } else {
                        $htmlData = $content;
                }
        }
        if(update_content($pdo,$content,$eid)){
            $email_arr = get_email_arr($pdo,$eid);
            $esub = get_event_info($pdo,$eid);
            $division = '';
            foreach ($esub['division'] as $key) {$division.= $cfg['division'][$key].',';}
            $division = rtrim($division, ',');
            $subject = "[事件更新]  ".$esub['base']['subject'];
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = get_mail_body($esub['base']['eid'],$esub['base']['subject'],$esub['base']['description'],$esub['base']['affect'],$esub['base']['etypeid'],$esub['base']['level'],$division,$cfg);
            $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
        }
        $event['eid'] = intval($params['eid']);
        $event['subject'] = trim($params['subject']);
        $event['description'] = trim($params['description']);
        $event['affect'] = trim($params['affect']);
        $event['solvetime'] = strtotime($params['solvetime']);
        $event['etypeid'] = intval($params['etypeid']);
        $event['level'] = intval($params['level']);
        $event['createtime'] = strtotime($params['createtime']);
        $event['closetime'] = time();
        for($i=1;$i<=20;$i++){
            if ($params['division'.$i]) $divisionu[] = $params['division'.$i];
        }
        $divisionu = array_unique($divisionu);
        delete_division($pdo,$event['eid']);
        foreach ($divisionu as $key){
            insert_division($pdo,$event['eid'],$key);
        }
        $division = '';
        foreach ($divisionu as $key) {$division.= $cfg['division'][$key].',';}
        $division = rtrim($division, ',');
        $event['who'] = trim($params['who']);
        $event['summary'] = trim($params['summary']);
        $event['islock'] = intval($params['islock']);
        $lock_state = intval($params['lock_state']);

        if($params['select3']){
            foreach($params['select3'] as $v){
                $to .= $v.",";
            }
                $toyet = get_event_to($pdo,$event['eid']);
                $to .= $toyet['tomail'].",";
                $to = trim($to,',');
                $subject = "[事件更新]  ".$event['subject'];
                $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
                $body = get_mail_body($event['eid'],$event['subject'],$event['description'],$event['affect'],$event['etypeid'],$event['level'],$division,$cfg);
                $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
                $smtp->debug = false;
                $smtp->sendmail($to,'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
                update_event_to($pdo,$event['eid'],$to); 
        } 
        if ((($lock_state==0)||($lock_state==2)) && (($event['islock']==1)||($event['islock']==2))){
            if($event['createtime']>$event['solvetime']){
                msg_redirect('index.php?op=edit&eid='.$event['eid'],'关闭时间不能小于创建时间');
            }
            if(!$event['who']){
                msg_redirect('index.php?op=edit&eid='.$event['eid'],'未填写责任人的事件无法关闭');
            }
            foreach ($mids as $mid){
                $measure_s = intval($params['mstatus_'.$measure['mid']]);
                if ($measure_s==0) msg_redirect('index.php?op=edit&eid='.$event['eid'],'有未完成的改进措施');
            }
            $event['islock'] = 2;
        }
            if ($event['solvetime']!=0){
                $event['affecttime'] = ceil(($event['solvetime']-$event['createtime'])/60);
            }
            else $event['affecttime'] = 0;
            if(update_event($pdo,$event)){
                msg_redirect('index.php?op=edit&eid='.$event['eid'],'edit event success');
            }
            break;
    case 'checkclose':
        if(!session_id())session_start();
        $eid = intval($params['eid']);
        $who = trim($params['who']);
        $info = get_event_info($pdo,$eid);
        $level = $info['base']['level'];
        if(isset($_SESSION['user']) && $_SESSION['user']===true && isset($_SESSION['name'])) $username = $_SESSION['name'];
        if(isset($_SESSION['user']) && $_SESSION['user']===true && isset($_SESSION['name']) && $cfg['close'][$level][$username]==1){
            $ok = intval($params['ok']);
            if($ok === 1) checkclose($pdo,$eid);
            $esub = get_event_info($pdo,$eid);
            $division = '';
            foreach ($esub['division'] as $key) {$division.= $cfg['division'][$key].',';}
            $division = rtrim($division, ',');
            $email_arr = get_email_arr($pdo,$eid);
            $subject = "[事件关闭]  ".$esub['base']['subject'];
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = get_mail_body($esub['base']['eid'],$esub['base']['subject'],$esub['base']['description'],$esub['base']['affect'],$esub['base']['etypeid'],$esub['base']['level'],$division,$cfg);
            $smtp =   new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
            header("Location:index.php?op=detail&eid=".$eid);
        }
        else{
            msg_redirect('index.php?op=detail&eid='.$eid,'您无权关闭该事件');
        }
        break;
    case 'myatt':
        if(!session_id())session_start();
        if(!isset($_SESSION['user']) || $_SESSION['user'] != true || !isset($_SESSION['name'])){
            msg_redirect('oauthlogin.php','您还未登录');
        }else{
            $user = $_SESSION['name'];
            $uid = $_SESSION['uid'];
            if(!$uid){
                msg_redirect('index.php','参数错误');
            }else{
                $name = file_get_contents("data.txt");
                $array = json_decode($name);
                foreach ($array as $key){
                    $en_name = preg_replace('/([\x80-\xff]*)/i','',$key->key);
                    if ($en_name == $user) {
                        $ch_name = preg_replace('/[\x00-\x7F]/', '',$key->key);
                        break;
                    }
                }
                //$userinfo = get_user_info($pdo,$user);
                //$my_who_event = get_who($pdo,$userinfo['realname']);
                $my_who_event = get_who($pdo,$ch_name);
                $my_att_event = get_my_att($pdo,$uid);
                $page = $params['page'];
                if(!$page)$page = 1;
                $total = count($my_att_event);
                $offset = 5;
                $allpage = ceil($total/$offset);
                if($page){
                $limit = (($page-1) * $offset).', '.$offset;
                $my_event_page = get_my_att_page($pdo,$limit,$uid);
            }
                $template = 'myevent';
            }
        }
        break;
	case 's_del':
		$sid = intval($params['sid']);
		$eid = intval($params['eid']);
		if(delete_schedule($pdo,$sid)){
			msg_redirect('index.php?op=edit&eid='.$eid);
		}else{
			msg_redirect('index.php?op=edit&eid='.$eid,'删除失败！');
		}
    break;	
		
    case 'report':
        $start = intval(strtotime($params['start']));
        $end = intval(strtotime($params['stop']));
        if (($start&&!$end)||(!$start&&$end)) msg_redirect('index.php?op=report','日期参数不全');
        else {
            if ($start>$end){
                $p = $start;
                $start = $end;
                $end = $p;
            }
            $month_event = get_month_event_point($pdo,$start,$end);
        }
        $division = array();
        $who = array();
        foreach ($month_event as $k) {
            if ($k['affecttime']) {
                foreach ($k['division'] as $m){
                    $division[$m]+=ceil($k['affecttime']*((7-$k['level'])/6));
                }
            }
            else continue;
        }
        foreach ($month_event as $k) {
            if ($k['affecttime']) $who[$k['who']]+=ceil($k['affecttime']*((7-$k['level'])/6));
            else continue;
        }
        arsort($division);
        arsort($who);
        $by_division = get_by_division($pdo);
        $by_type = get_by_type($pdo);
        $by_who = get_by_who($pdo);
        $by_affecttime = get_by_affecttime($pdo);
        $nowyear = date('Y',time());
        $nowmonth = date('m',time());
        //每月时间统计

        $preyear = $nowyear-1;
        $premonth = $nowmonth+1;
        for($i=intval($premonth);$i<=12;$i++){
            $p = sprintf("%02d",$i);
            $param = $preyear.$p;
            $daysnum = cal_days_in_month(CAL_GREGORIAN, $i, $preyear);
            $thecount = get_every_month_event($pdo,$param);
            $affect_time = get_every_month_affect_time($pdo,$param);
            $the_month_time = $daysnum * 24 * 60;
            $the_month_useable = floor((($the_month_time-$affect_time['total'])/$the_month_time)*100);
            $the_month_useable = number_format((($the_month_time-$affect_time['total'])/$the_month_time)*100,2);
            $event_graph_month[$p]['date'] = "new Date($preyear$p,0)";
            $event_graph_month[$p]['value'] = $thecount['total'];
            $event_graph_useable[$p]['date'] = "new Date($preyear$p,0)";
            if ($the_month_useable>="100.00"){$event_graph_useable[$p]['value'] = 100;}
            else $event_graph_useable[$p]['value'] = $the_month_useable;
        }

        for($i=1;$i<=intval($nowmonth);$i++){
            $p = sprintf("%02d",$i);
            $param = $nowyear.$p;
            $daysnum = cal_days_in_month(CAL_GREGORIAN, $i, $nowyear);
            $thecount = get_every_month_event($pdo,$param);
            $affect_time = get_every_month_affect_time($pdo,$param);
            $the_month_time = $daysnum * 24 * 60;
            $the_month_useable = floor((($the_month_time-$affect_time['total'])/$the_month_time)*100);
            $the_month_useable = number_format((($the_month_time-$affect_time['total'])/$the_month_time)*100,2);
            $event_graph_month[$p]['date'] = "new Date($nowyear$p,0)";
            $event_graph_month[$p]['value'] = $thecount['total'];
            $event_graph_useable[$p]['date'] = "new Date($nowyear$p,0)";
            if ($the_month_useable>="100.00"){$event_graph_useable[$p]['value'] = 100;}
            else $event_graph_useable[$p]['value'] = $the_month_useable;
        }

        foreach($event_graph_month as $k=>$v){
            $total = $total+$v['value'];
            $graph_data .= "{date:".$v['date'].",value:".$v['value']."},"; 
        }
        foreach($event_graph_useable as $k=>$v){
            $useable_data .= "{date:".$v['date'].",value:".$v['value']."},";
        }
        $graph_data = trim($graph_data,',');
        $avg = ceil($total/count($event_graph_month));
        $useable_data = trim($useable_data,',');
        $useable_data = "[".$useable_data."]";
        $graph_data = "[".$graph_data."]";
        //类别趋势

        for($i=intval($premonth);$i<=12;$i++){
            $p = sprintf("%02d",$i);
            $param = $preyear.$p;
            foreach($cfg['etype'] as $k=>$v){
                $month_type[$param][$v] = get_month_type_event($pdo,$param,$k);
            }
        }

        for($i=1;$i<=intval($nowmonth);$i++){
            $p = sprintf("%02d",$i);
            $param = $nowyear.$p;
            foreach($cfg['etype'] as $k=>$v){
                $month_type[$param][$v] = get_month_type_event($pdo,$param,$k);
            }
        }
        foreach($month_type as $k=>$v){
            $month_type_graph .= "{month:".$k.",";
            foreach($v as $key=>$val){
                $month_type_graph .= $key.":".$val['total'].",";
            }
            $month_type_graph = trim($month_type_graph,',');
            $month_type_graph .= "},";
        }
        $month_type_graph = trim($month_type_graph,',');
        $month_type_graph = "[".$month_type_graph."]";

        //事业部趋势
        for($i=intval($premonth);$i<=12;$i++){
            $p = sprintf("%02d",$i);
            $param = $preyear.$p;
            foreach($cfg['division'] as $k=>$v){
                $month_division[$param][$v] = get_month_division_event($pdo,$param,$k);
            } 
        }

        for($i=1;$i<=intval($nowmonth);$i++){
            $p = sprintf("%02d",$i);
            $param = $nowyear.$p;
            foreach($cfg['division'] as $k=>$v){
                $month_division[$param][$v] = get_month_division_event($pdo,$param,$k);
            }   
        }
        foreach($month_division as $k=>$v){
            $month_division_graph .= "{month:".$k.",";
            foreach($v as $key=>$val){
                $month_division_graph .= $key.":".$val['total'].",";
            }   
            $month_division_graph = trim($month_division_graph,',');
            $month_division_graph .= "},";
        }   
        $month_division_graph = trim($month_division_graph,',');
        $month_division_graph = "[".$month_division_graph."]";
        $template = 'report';

//echo '<pre>';print_r($graph_data);print_r($useable_data);exit;

        break;
    case 'ajax':
        $type = trim($params['type']);
        if($type == "monthsearch"){
            $start = trim($params['start']);
            $stop = trim($params['stop']);
            $start = strtotime($start);
            $stop = strtotime($stop);
            $stop = strtotime('+1 month',$stop);
            if(!$start || !$stop || !is_numeric($start) || !is_numeric($stop)){
                $result = "查询条件有误!";
            }else{
                $result = get_month_search_event($pdo,$start,$stop);
                $new_result = array();
                foreach ($result as $r) {
                    $new_result[] = array("date" => $r['m'], "value" => $r['total']);
                }
                echo json_encode($new_result);
                exit;
            }
        }
        if($type == "useablesearch"){
            $start = trim($params['start']);
            $stop = trim($params['stop']);
            $start = strtotime($start);
            $stop = strtotime($stop);
            $stop = strtotime('+1 month',$stop);
            if(!$start || !$stop || !is_numeric($start) || !is_numeric($stop)){
                $result = "查询条件有误!";
            }else{
                $result = get_month_search_useable($pdo,$start,$stop);
                $new_result = array();
                foreach($result as $r){
                    $year = substr($r['m'],0,3);
                    $month = substr($r['m'],4,5);
                    $thedaysnum = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                    $totaltime = $thedaysnum*24*60;
                    $per = number_format((($totaltime-$r['total'])/$totaltime),2)*100;
                    $new_result[] = array("date" => $r['m'], "value" => $per);
                }
                echo json_encode($new_result);
                exit;
            }
        }

        if($type == "typesearch"){
            $mw = $params['mw'];
            $start = trim($params['start']);
            $stop = trim($params['stop']);
            if($mw == 1){
                $start = strtotime($start);
                $stop = strtotime($stop);
                $stop = strtotime('+1 month',$stop);
                $data = get_month_search_type($pdo,$start,$stop);
                foreach($data as $k=>$v){
                    $new_data[$v['m']][$cfg['etype'][$v['etypeid']]] = $v['total'];
                }
                foreach($new_data as $k=>$v){
                    foreach($cfg['etype'] as $key=>$val){
                        if($new_data[$k][$val]){
                            $data_res[$k][$val] = $v[$val];
                        }else{
                            $data_res[$k][$val] = 0;
                        }
                    }
                }
            }else if($mw == 2){
                $start = strtotime($start);
                $stop = strtotime($stop);
                $data = get_week_type_search_event($pdo,$start,$stop);
                foreach($data as $k=>$v){
                    $new_data[$v['w']][$cfg['etype'][$v['etypeid']]] = $v['t'];
                }
                foreach($new_data as $k=>$v){
                    foreach($cfg['etype'] as $key=>$val){
                        if($new_data[$k][$val]){
                            $data_res[$k][$val] = $v[$val];
                        }else{
                            $data_res[$k][$val] = 0;
                        }
                    }
                }
            }
            echo json_encode($data_res);exit;
        }

        if($type == "divisionsearch"){
            $mw = $params['mw'];
            $start = trim($params['start']);
            $stop = trim($params['stop']);
            if($mw == 1){
                $start = strtotime($start);
                $stop = strtotime($stop);
                $stop = strtotime('+1 month',$stop);
                $data = get_month_search_event_divison($pdo,$start,$stop);
                foreach($data as $k=>$v){
                    $new_data[$v['m']][$cfg['division'][$v['division']]] = $v['total'];
                }   
                foreach($new_data as $k=>$v){
                    foreach($cfg['division'] as $key=>$val){
                        if($new_data[$k][$val]){
                            $data_res[$k][$val] = $v[$val];
                        }else{
                            $data_res[$k][$val] = 0;
                        }   
                    }   
                }   
            }else if($mw == 2){
                $start = strtotime($start);
                $stop = strtotime($stop);
                $data = get_week_search_event_division($pdo,$start,$stop);
                foreach($data as $k=>$v){
                    $new_data[$v['w']][$cfg['division'][$v['division']]] = $v['t'];
                }
                foreach($new_data as $k=>$v){
                    foreach($cfg['division'] as $key=>$val){
                        if($new_data[$k][$val]){
                            $data_res[$k][$val] = $v[$val];
                        }else{
                            $data_res[$k][$val] = 0;
                        }   
                    }   
                } 
            }
           echo json_encode($data_res);exit; 
        }

        break;
    default:

       /* if(!session_id()) session_start();
        if(isset($_SESSION['user']) && $_SESSION['user']===true && isset($_SESSION['name'])){
            $user = $_SESSION['name'];
            $uid = $_SESSION['uid'];
            $event_unlock_now = get_event_unlock_now_att($pdo,$uid);
        }else{
            $user = "guest";
            $event_unlock_now = get_event_unlock_now($pdo);
        }
        $page = $params['page'];
        if(!$page)$page = 1;
        $total = get_event_count($pdo);
        $offset = 50;
        $allpage = ceil($total['total']/$offset);
        if(!$op) $op="page";
        if($op=="page"){
            if($page){
                $limit = (($page-1) * $offset).', '.$offset;
                if($user=="guest"){
                    $event_page = get_event_page($pdo,$limit);
                }else{
                    $uid = $_SESSION['uid'];
                    $event_page = get_event_page_att($pdo,$limit,$uid);
                }
            }
        }
        $template = 'home';*/
        if(!session_id()) session_start();
        $eid = $_SESSION['eid'];
        if ($eid) {
            $_SESSION['eid'] = '';
            msg_redirect('index.php?op=detail&eid='.$eid);
        }
        if(isset($_SESSION['user']) && $_SESSION['user']===true && isset($_SESSION['name'])){
            $user = $_SESSION['name'];
            //$user = $_SESSION['realname'];
            $uid = $_SESSION['uid'];
        }else{
            $user = "guest";
        }
        $page = $params['page'];
        if(!$page)$page = 1;
        $total = get_event_count($pdo);
        $offset = 10;
        $allpage = ceil($total['total']/$offset);
            if($page){
                $limit = (($page-1) * $offset).', '.$offset;
                    $event_page = get_all_event_page($pdo,$limit);
            }
        $template = 'event_all';
        break;
}

$current_nav='index';
require_once '../libraries/decorator.inc.php';
