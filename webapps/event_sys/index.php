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
            msg_redirect('index.php?op=edit&eid='.$measure['eid'],'增加成功');
            $email_arr = get_email_arr($pdo,$measure['eid']);
            $esub = get_event_info($pdo,$measure['eid']);
            $subject = "您关注的事件：".$esub['base']['subject']."有更新";
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = "详情点击："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$measure['eid']."'>这里查看</a>";
            $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
        }else{
            msg_redirect('index.php?op=edit&eid='.$measure['eid'],'增加失败');
        }
    }
    break;
case 'm_edit':
	$eid = intval($params['m_eid_edit']);
	$content = $params['content_m'];
    $measure['eid'] = intval($params['m_eid_edit']);
    $measure['mid'] = intval($params['mid']);
    $measure['measure'] = trim($params['c_measure']);
    $measure['muser'] = trim($params['c_user']);
    $measure['mtime'] = strtotime($params['c_time_'.$measure['mid']]);
    $measure['status'] = intval($params['mstatus']);
    if(!$measure['measure'] || !$measure['muser'] || !$measure['mtime']){
        msg_redirect('index.php?op=edit&eid='.$measure['eid'],'填写完整再提交！');
    }else{
        if(update_measure($pdo,$measure) || update_content($pdo,$content,$eid)){
            msg_redirect('index.php?op=edit&eid='.$measure['eid'],'更新成功');
            $email_arr = get_email_arr($pdo,$measure['eid']);
            $esub = get_event_info($pdo,$measure['eid']);
            $subject = "您关注的事件：".$esub['base']['subject']."有更新";
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = "详情点击："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$measure['eid']."'>这里查看</a>";
            $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
        }else{
            msg_redirect('index.php?op=edit&eid='.$measure['eid'],'更新失败');
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
            $event_attr['division'] = intval($params['division']);
            $event_attr['htmlData'] = $htmlData;
            $event_attr['addtime'] = time();
            $isinsert = insert_event($pdo,$event_attr);
            if($isinsert){
                if($params['select3'] || $params['mailgroup']){
                $params['select3'] = implode(',',$params['select3']);
                $params['mailgroup'] = trim($params['mailgroup'],',');
                $params['select3'] = $params['select3'].",".$params['mailgroup'];
                $params['select3'] = trim($params['select3'],',');
                $params['select3'] = explode(',',$params['select3']);
                foreach($params['select3'] as $v){
                    $to .= $v.","; 
                    $subject = "有新事件 ".$event_attr['subject']." 添加";
                    $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
                    $body = "事件标题：".$event_attr['subject']."<br>"."事件描述：".$event_attr['description']."<br>"."事件影响：".$event_attr['affect']."<br>"."事件类型：".$cfg['etype'][$event_attr['etypeid']]."<br>"."事件等级：".$event_attr['level']."<br>"."责任部门：".$cfg['division'][$event_attr['division']]."<br>"."详情："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$isinsert."'>点击查看详情</a>";
                    $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
                    $smtp->debug = false;
                    $smtp->sendmail($v,'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']); 
                }
                $to = trim($to,',');
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
	case 'params':
		$params_name = isset($params['params_name'])?$params['params_name']:"";
		$params_value = isset($params['params_value'])?$params['params_value']:"";
		$event_params = get_events_by_params ($pdo,array("params_name"=>$params_name,"params_value"=>$params_value,));
		$template = 'event_params'; 
		break;
    case 's_add':
       // $schedule['stypeid'] = intval($params['s_stypeid']);
        $schedule['s_subject'] = trim($params['s_subject']);
        $schedule['s_user'] = trim($params['s_user']);
       // $schedule['s_division'] = intval($params['s_division']);
        $schedule['s_time'] = strtotime($params['s_start']);
        $schedule['eid'] = intval($params['s_eid']);
        if(!$schedule['s_subject'] || !$schedule['s_user'] || !$schedule['s_time']){
            msg_redirect('index.php?op=edit&eid='.$schedule['eid'],'add schedule failed');
        }
        if(insert_schedule($pdo,$schedule)){
            //if(update_event_stype($pdo,$schedule)){
                msg_redirect('index.php?op=edit&eid='.$schedule['eid'],'add schedule success');
                $email_arr = get_email_arr($pdo,$schedule['eid']);
            $esub = get_event_info($pdo,$schedule['eid']);
            $subject = "您关注的事件：".$esub['base']['subject']."有更新";
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = "详情点击："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$schedule['eid']."'>这里查看</a>";
            $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
            //}else{
              //  msg_redirect('index.php?op=detail&eid='.$schedule['eid'],'will not update event stypeid');
           // }
        }else{
            msg_redirect('index.php?op=edit&eid='.$schedule['eid'],'add schedule failed');
        }
        break;
	case 'c_add':
		$comment['comment'] = trim($params['m_comment']);
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
                if(insert_attention($pdo,$att)){
                    msg_redirect('index.php','添加关注成功');
                }else{
                    msg_redirect('index.php','添加关注失败');
                }
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
			if(del_att($pdo,$att['uid'],$att['eid'])){
				msg_redirect('index.php','取消关注成功');
			}else{
				msg_redirect('index.php','取消关注失败');
			}
		}
	}
	break;
    case 'r_add':
        $report['eid'] = intval($params['r_eid']);
        $report['r_user'] = trim($params['r_user']);
        $report['r_division'] = intval($params['r_division']);
        $report['content'] = trim($params['report']);
        $report['measure'] = trim($params['measure']);
        if(!$params['r_time']){
            $report['r_time'] = time();
        }else{
            $report['r_time'] = strtotime($params['r_time']);
        }
        if(get_event_report($pdo,$report['eid'])){
            update_event_report($pdo,$report);
            msg_redirect('index.php?op=detail&eid='.$report['eid'],'edit report success');
        }else{
            insert_event_report($pdo,$report);
            msg_redirect('index.php?op=detail&eid='.$report['eid'],'add report success');
        }
        $email_arr = get_email_arr($pdo,$report['eid']);
            $esub = get_event_info($pdo,$report['eid']);
            $subject = "您关注的事件：".$esub['base']['subject']."有更新";
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = "详情点击："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$report['eid']."'>这里查看</a>";
            $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
        break;
    case 'c_edit':
        $htmlData = '';
        $eid = intval($params['c_eid']);
        $content = $params['content'];
        if (!empty($content)) {
                if (get_magic_quotes_gpc()) {
                        $htmlData = stripslashes($content);
                } else {
                        $htmlData = $content;
                }
        }
        if(update_content($pdo,$content,$eid)){
            msg_redirect('index.php?op=edit&eid='.$eid,'modify content success!');
            $email_arr = get_email_arr($pdo,$eid);
            $esub = get_event_info($pdo,$eid);
            $subject = "您关注的事件：".$esub['base']['subject']."有更新";
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = "详情点击："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$eid."'>这里查看</a>";
            $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
        }else{
            msg_redirect('index.php?op=edit&eid='.$eid,'failed!');
        } 
        break;
    case 'e_edit':
        $event['eid'] = intval($params['eid']);
        $event['subject'] = trim($params['subject']);
        $event['description'] = trim($params['description']);
        $event['affect'] = trim($params['affect']);
        $event['solvetime'] = strtotime($params['solvetime']);
        $event['etypeid'] = intval($params['etypeid']);
        $event['level'] = intval($params['level']);
        $event['createtime'] = strtotime($params['createtime']);
        $event['closetime'] = time();
        //$event['fuser'] = trim($params['fuser']);
        $event['division'] = intval($params['division']);
        $event['who'] = trim($params['who']);
        $event['summary'] = trim($params['summary']);
        $event['islock'] = intval($params['islock']);
        $lock_state = intval($params['lock_state']);

        if($params['select3']){
           foreach($params['select3'] as $v){
                     $to .= $v.",";
                     $subject = "有新事件 ".$event['subject']." 添加";
                     $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
                     $body = "事件标题：".$event['subject']."<br>"."事件描述：".$event['description']."<br>"."事件影响：".$event['affect']."<br>"."事件类型：".$cfg['etype'][$event['etypeid']]."<br>"."事件等级：".$event['level']."<br>"."责任部门：".$cfg['division'][$event['division']]."<br>"."详情："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$event['eid']."'>点击查看详情</a>";
                     $smtp = new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
                     $smtp->debug = false;
                     $smtp->sendmail($v,'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
                 }
                 $toyet = get_event_to($pdo,$event['eid']);
                 $to .= $toyet['tomail'].",";
                 $to = trim($to,',');
                 update_event_to($pdo,$event['eid'],$to); 
        } 
        if (($lock_state==0) && ($event['islock']==1)){
            if($event['createtime']>$event['solvetime']){
                msg_redirect('index.php?op=edit&eid='.$event['eid'],'关闭时间不能小于创建时间');
            }
           // $measure = get_event_measure($pdo,$event['eid']);
            // if(!$measure) msg_redirect('index.php?op=edit&eid='.$event['eid'],'必须填写改进措施之后才能关闭');
            $event['islock'] = 0;
            $subject = $event['subject']."即将关闭";
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = "事件标题：".$event['subject']."<br>"."事件描述：".$event['description']."<br>"."事件影响：".$event['affect']."<br>"."解决时间：".$params['solvetime']."<br>"."事件类型：".$cfg['etype'][$event['etypeid']]."<br>"."事件等级：".$event['level']."<br>"."责任部门：".$cfg['division'][$event['division']]."<br>"."责任人：".$event['who']."<br>"."详情："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$event['eid']."'>点击查看详情</a>";

//".<br><a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=checkclose&eid=".$event['eid']."&ok=1'>关闭</a>";
            $smtp =   new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach ($cfg['checkermail'] as $v){
                $smtp->sendmail($v,'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
        }
        
            $event['affecttime'] = ceil(($event['solvetime']-$event['createtime'])/60);
            if(update_event($pdo,$event)){
                msg_redirect('index.php?op=edit&eid='.$event['eid'],'edit event success');
            }else{
                msg_redirect('index.php?op=edit&eid='.$event['eid'],'edit event failed');
            }
       // }
            break;
    case 'checkclose':
        if(!session_id())session_start();
        $eid = intval($params['eid']);
        if(isset($_SESSION['user']) && $_SESSION['user']===true && isset($_SESSION['name'])) $username = $_SESSION['name'];
        if(isset($_SESSION['user']) && $_SESSION['user']===true && isset($_SESSION['name']) && $cfg['close'][$username]==1){
            $ok = intval($params['ok']);
            if($ok === 1) checkclose($pdo,$eid);
            $esub = get_event_info($pdo,$eid);
            $email_arr = get_email_arr($pdo,$eid);
            $subject = "您关注的事件 ".$esub['base']['subject']." 已经关闭";
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            $body = "详情点击："."<a href='http://".$_SERVER['HTTP_HOST']."/index.php?op=detail&eid=".$eid."'>这里查看</a>";
            $smtp =   new smtp($cfg['smtp']['server'],$cfg['smtp']['port'],true,$cfg['smtp']['user'],$cfg['smtp']['password'],$cfg['smtp']['sender']);
            $smtp->debug = false;
            foreach($email_arr as $k=>$v){
                $smtp->sendmail($v['email'],'alert@anjuke.com',$subject,$body,$cfg['smtp']['mailtype']);
            }
            header("Location:index.php");
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
                $my_att_event = get_my_att($pdo,$uid);
                $page = $params['page'];
                if(!$page)$page = 1;
                $total = count($my_att_event);
                $offset = 5;
                $allpage = ceil($total/$offset);
                if(!$tt) $tt="oo";
                if($tt=="oo"){
                if($page){
                $limit = (($page-1) * $offset).', '.$offset;
                $my_event_page = get_my_att_page($pdo,$limit,$uid);
                }
            }
                $template = 'myevent';
            }
        }
        break;
    case 's_edit':
		$eid = intval($params['s_eid_edit']);
		$content = $params['content_s'];
        $schedule['sid'] = intval($params['s_sid']);
        //$schedule['stypeid'] = intval($params['edit_stypeid']);
        $schedule['s_subject'] = trim($params['edit_subject']);
        $schedule['s_user'] = trim($params['edit_suser']);
        //$schedule['s_division'] = intval($params['edit_division']);
        $schedule['s_time'] = strtotime($params['edit_stime_'.$schedule['sid']]);
        if(!$schedule['s_subject'] || !$schedule['s_user'] || !$schedule['s_time']) msg_redirect('index.php?op=detail&eid='.$params['eid'],'empty any option!');
        if(update_schedule($pdo,$schedule) || update_content($pdo,$content,$eid)){
            msg_redirect('index.php?op=edit&eid='.$params['eid'],'Oh Yeah! success!!'); 
        }else{
            msg_redirect('index.php?op=edit&eid='.$params['eid'],'edit schedule failed');
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
        $by_division = get_by_division($pdo);
        $by_type = get_by_type($pdo);
        $by_who = get_by_who($pdo);
        $by_affecttime = get_by_affecttime($pdo);
        $nowyear = date('Y',time());
        $nowmonth = date('m',time());
        //每月时间统计
        for($i=1;$i<=intval($nowmonth);$i++){
            $p = sprintf("%02d",$i);
            $param = $nowyear.$p;
            $daysnum = cal_days_in_month(CAL_GREGORIAN, $i, $nowyear);
            $thecount = get_every_month_event($pdo,$param);
            $affect_time = get_every_month_affect_time($pdo,$param);
            $the_month_time = $daysnum * 24 * 60;
            $the_month_useable = floor((($the_month_time-$affect_time['total'])/$the_month_time)*100);
            $event_graph_month[$p]['date'] = "new Date($nowyear$p,0)";
            $event_graph_month[$p]['value'] = $thecount['total'];
            $event_graph_useable[$p]['date'] = "new Date($nowyear$p,0)";
            $event_graph_useable[$p]['value'] = $the_month_useable;
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
    case 'all':
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




    default:

        if(!session_id()) session_start();
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
        $template = 'home';
}

$current_nav='index';
require_once '../libraries/decorator.inc.php';
