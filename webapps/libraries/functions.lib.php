<?php
/***************************************************
 * 数据库操作
 **************************************************/

class SolrDb {
    private function SolrDb() {}
    private static $link = array();
    public static function getLink($host, $user, $pass, $name) {
    	$key = md5($host.$user.$pass.$name);
        if (isset(self::$link[$key])) {
            return self::$link[$key];
        }
        $link = new PDO("mysql:host=$host;dbname=$name;", $user, $pass);
        $link ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $link->exec('SET CHARACTER SET utf8');
        $link->exec('SET NAMES utf8');
        self::$link[$key]=$link;
        return self::$link[$key];
    }
}

function pdo_fetch($pdo, $sql, $params=array()) {
    $rst = pdo_fetch_all($pdo, $sql, $params);
    if ($rst) return $rst[0];
    return false;
}

function pdo_fetch_column($pdo, $sql, $params=array()) {
    $rst = pdo_fetch_all($pdo, $sql, $params);
    if ($rst) return reset($rst[0]);
    return false;
}

function pdo_fetch_all($pdo, $sql, $params=array()) {
    $sth = $pdo->prepare($sql);
    $sth->execute($params);
    $sth->setFetchMode(PDO::FETCH_ASSOC);
    $rst = $sth->fetchAll();
    if (empty($rst)) return false;
    return $rst;
}

/***************************************************
 * db host
 **************************************************/
function get_mail_body($eid,$subject,$description,$affect,$etypeid,$level,$division,$cfg){
    return '<table  border="0" cellspacing="0" cellpadding="0" style="width:377.5pt;">
<tbody>

<tr>
<td colspan="2" style="width:374.5pt;border:solid gray 1.0pt;background:gray;padding:0cm 5.4pt 0cm 5.4pt;">
<p align="center" style="text-align:center"><b>
<a href="http://'.$cfg['hostname'].'/index.php?op=detail&eid='.$eid.'" style="font-size:10.5pt;color:#262626;text-decoration:none;">
<span style="font-size:10.5pt;color:white">[#'.$eid.']'.$subject.'</span>
</a>
</b></p>
</td>
</tr>

<tr>
<td style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><b><span style="font-size:10.5pt;color:#595959;">事件描述</span></b></p>
</td>
<td style="width:311.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><span style="font-size:10.5pt;color:#595959">'.$description.'</span></p>
</td>
</tr>

<tr>
<td style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><b><span style="font-size:10.5pt;color:#595959;">事件影响</span></b></p>
</td>
<td style="width:311.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><span style="font-size:10.5pt;color:#595959">'.$affect.'</span></p>
</td>
</tr>

<tr>
<td style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><b><span style="font-size:10.5pt;color:#595959;">事件类型</span></b></p>
</td>
<td style="width:311.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><span style="font-size:10.5pt;color:#595959">'.$cfg['etype'][$etypeid].'</span></p>
</td>
</tr>

<tr>
<td style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><b><span style="font-size:10.5pt;color:#595959;">事件等级</span></b></p>
</td>
<td style="width:311.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><span style="font-size:10.5pt;color:#595959">L'.$level.'</span></p>
</td>
</tr>

<tr>
<td style="width:63.4pt;border:solid gray 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><b><span style="font-size:10.5pt;color:#595959;">责任部门</span></b></p>
</td>
<td style="width:311.1pt;border-top:none;border-left:none;border-bottom:solid gray 1.0pt;border-right:solid gray 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;white-space: nowrap;">
<p><span style="font-size:10.5pt;color:#595959">'.$division.'</span></p>
</td>
</tr>

<tr>
<td colspan="2" style="width:374.5pt;border:solid gray 1.0pt;border-top:none;background:#A6A6A6;padding:0cm 5.4pt 0cm 5.4pt;">
<p align="right" style="text-align:right"><b><u>
<span><a href="http://'.$cfg['hostname'].'/index.php?op=detail&eid='.$eid.'" style="font-size:10.5pt;color:#262626">查看详情</a></span>
</u></b></p>
</td>
</tr>

</tbody>
</table><br />';
}

function update_view_count ($pdo,$eid){
    $count_old = pdo_fetch($pdo,'select view_count from event where eid='.$eid);
    $count = $count_old['view_count'];
    $count = $count + 1;
    $sql = "update event set view_count=? where eid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($count,$eid));
}

function get_division($pdo,$eid){
    return pdo_fetch_all($pdo, 'select division from division where eid = '.$eid);
}

function get_search_division($pdo,$division){
    $diff_time = time() - 3600 * 24 * 30;
    $result = pdo_fetch_all($pdo, 'select eid from division where division = '.$division);
    $array = array();
    foreach ($result as $m) $array[]=$m['eid'];
    $event = array();
    foreach ($array as $k){
        $event[] =pdo_fetch($pdo,'select * from event where eid=?',array($k));
    }
    foreach ($event as $p=>$q){
        if($q['createtime']<$diff_time) unset($event[$p]);
    }
    $event = array_reverse($event);
    foreach ($event as $k=>$v){
        $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
        $divisionx = array();
        foreach ($division as $m){
            $divisionx[] = $m['division'];
        }
        $event[$k]['division'] = $divisionx;
    }
    return $event;
}

function search_division($pdo,$eid,$division){
    return pdo_fetch_all($pdo, 'select * from division where eid = '.$eid.' and division = '.$division);
}

function insert_division($pdo,$eid,$division){
    $sql = "insert into division set eid=?,division=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($eid,$division));
    return $pdo->lastInsertId();
}

function delete_division($pdo,$eid){
    $sql = "delete from division where eid=?";
	$sth = $pdo->prepare($sql);
	$sth ->execute(array($eid));
	return $sth->rowCount();
}

function get_event_unlock($pdo){
    $result = pdo_fetch_all($pdo, 'select * from event where islock=0 or islock=2 order by createtime desc');
    foreach ($result as $k=>$v){
        $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
        $divisionx = array();
        foreach ($division as $m){
            $divisionx[] = $m['division'];
        }
        $result[$k]['division'] = $divisionx;
    }
    return $result;
}

function get_by_division($pdo){
    $diff_time = time() - 3600 * 24 * 30;
    $result = pdo_fetch_all($pdo, 'select eid from event where createtime >= '.$diff_time);
    foreach($result as $k) $eid[]=$k['eid'];
    foreach($eid as $m){
        $division = pdo_fetch_all($pdo, 'select division from division where eid = '.$m);
        $divisionx = array();
        foreach ($division as $n){
            $divisionx[] = $n['division'];
        }
        $array[]=$divisionx;
    }
    foreach ($array as $o){
        foreach ($o as $p=>$q){
            $divisiony[$q] += 1;
        }
    }
    arsort($divisiony);
    foreach ($divisiony as $r=>$s){
        $divisionz[]=array('division'=>$r,'total'=>$s);
    }
    return $divisionz;
}

function get_by_type($pdo){
    $diff_time = time() - 3600 * 24 * 30;
    return pdo_fetch_all($pdo, 'select etypeid,count(1) as total from event where createtime >= '.$diff_time.' group by etypeid order by total desc limit 10');
}

function get_by_who($pdo){
    $diff_time = time() - 3600 * 24 * 30;
    return pdo_fetch_all($pdo, 'select who,count(1) as total from event where createtime >= '.$diff_time.' group by who order by total desc limit 10');
}

function get_by_affecttime($pdo){
    $diff_time = time() - 3600 * 24 * 30;
    return pdo_fetch_all($pdo, 'select eid,affecttime from event where createtime >= '.$diff_time.' order by affecttime desc limit 10');
}

function get_event_count($pdo){
    return pdo_fetch($pdo,'select count(*) as total from event order by islock asc');
}

function get_event_page_att($pdo,$limit,$uid){
    $nowid = pdo_fetch($pdo,'select eid from event where islock=0 order by createtime desc limit 1');
    if(!empty($nowid)){
        $event_page = pdo_fetch_all($pdo,'select * from event where eid <> '.$nowid['eid'].' AND islock=0 order by createtime desc limit '.$limit);
        foreach($event_page as $k=>$v){
            $event_page[$k]['att'] = pdo_fetch_all($pdo,'select * from attention where uid='.$uid.' and eid='.$v['eid']);
            $event_page[$k]['comment_count'] = pdo_fetch($pdo,'select count(*) from comment where eid='.$v['eid']);
        }
    }else{
        $event_page = pdo_fetch_all($pdo,'select * from event where islock=0 order by createtime desc limit '.$limit);
        foreach($event_page as $k=>$v){
            $event_page[$k]['att'] = pdo_fetch_all($pdo,'select * from attention where uid='.$uid.' and eid='.$v['eid']);
            $event_page[$k]['comment_count'] = pdo_fetch($pdo,'select count(*) from comment where eid='.$v['eid']);
        }
    }
    return $event_page;
}

function get_event_page($pdo,$limit){
    $nowid = pdo_fetch($pdo,'select eid from event where islock=0 order by createtime desc limit 1');
    if(!empty($nowid)){
        $event_page = pdo_fetch_all($pdo,'select * from event where eid <> '.$nowid[eid].' AND islock=0 order by createtime desc limit '.$limit);
        foreach($event_page as $k=>$v){
            $event_page[$k]['comment_count'] = pdo_fetch($pdo,'select count(*) from comment where eid='.$v['eid']);
        }
        return $event_page;
    }else{
        $event_page = pdo_fetch_all($pdo,'select * from event WHERE islock=0 order by createtime desc limit '.$limit);
        foreach($event_page as $k=>$v){
            $event_page[$k]['comment_count'] = pdo_fetch($pdo,'select count(*) from comment where eid='.$v['eid']);
        }
        return $event_page;
    }    
}

function get_all_event_page($pdo,$limit){
    $event_page = pdo_fetch_all($pdo,'select * from event order by createtime desc limit '.$limit);
    foreach($event_page as $k=>$v){
        $event_page[$k]['comment_count'] = pdo_fetch($pdo,'select count(*) from comment where eid='.$v['eid']);
    }
    return $event_page;
}

function get_my_att($pdo,$uid){
    return pdo_fetch_all($pdo,'select a.time,e.* from attention a left join event e ON e.eid=a.eid where a.uid='.$uid.' order by e.createtime desc');
}

function get_my_att_page($pdo,$limit,$uid){
   return pdo_fetch_all($pdo,'select a.time,e.* from attention a left join event e ON e.eid=a.eid where a.uid='.$uid.' order by e.createtime desc limit '.$limit); 
}

function get_event_unlock_now($pdo){
    $event_unlock['event'] = pdo_fetch($pdo,'select * from event where islock=0 order by createtime desc limit 1');
    $event_unlock['schedule'] = pdo_fetch_all($pdo,'select * from schedule where eid=?',array($event_unlock['event']['eid']));
    $event_unlock['comment_count'] = pdo_fetch($pdo,'select count(*) from comment where eid='.$event_unlock['event']['eid']);
    return $event_unlock;
}

function get_event_unlock_now_att($pdo,$uid){
    $event_unlock['event'] = pdo_fetch($pdo,'select * from event where islock=0 order by createtime desc limit 1');
    if(!empty($event_unlock['event'])){
        $event_unlock['schedule'] = pdo_fetch_all($pdo,'select * from schedule where eid=?',array($event_unlock['event']['eid']));
        $event_unlock['att'] = pdo_fetch_all($pdo,'select * from attention where uid='.$uid.' and eid='.$event_unlock['event']['eid']);
        $event_unlock['comment_count'] = pdo_fetch($pdo,'select count(*) from comment where eid='.$event_unlock['event']['eid']);
    }
    return $event_unlock;
}

function insert_event($pdo,$params){
    $sql = "insert into event set subject=?,content=?,description=?,affect=?,etypeid=?,level=?,createtime=?,addtime=?,fuser=?,islock=?,stypeid=?,summary=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['subject'],$params['htmlData'],$params['description'],$params['affect'],$params['etypeid'],$params['level'],$params['createtime'],$params['addtime'],$params['fuser'],$params['islock'],$params['stypeid'],$params['summary']));
    return $pdo->lastInsertId();
}

function get_event_info($pdo,$id){
    $event_info['base'] = pdo_fetch($pdo,'select * from event where eid=?',array($id));
	$event_info['relate'] = pdo_fetch_all ($pdo,'select eid,subject from event where subject like "%'.$event_info['base']['subject'].'%"');
    $event_info['schedule'] = pdo_fetch_all($pdo,'select * from schedule where eid=? order by s_time asc',array($id));
    $event_info['report'] = pdo_fetch($pdo,'select * from report where eid=?',array($id));
    $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($id));
    foreach ($division as $key) $event_info['division'][] = $key['division'];
    return $event_info;
}

function get_relate_event($pdo,$subject){
    return pdo_fetch_all ($pdo,'select * from event where subject like "%'.$subject.'%"');
}

function get_email_arr($pdo,$eid){
    return pdo_fetch_all($pdo,'select a.uid,u.email from attention a left join user u on u.id=a.uid where a.eid=?',array($eid));
}

function get_user_list($pdo){
    return pdo_fetch_all($pdo,'select * from user');
}

function get_event_search($pdo,$where){
    $result =  pdo_fetch_all($pdo,'select * from event where '.$where.' order by createtime desc');
    foreach ($result as $k=>$v){
        $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
        $divisionx = array();
        foreach ($division as $m){
            $divisionx[] = $m['division'];
        }
        $result[$k]['division'] = $divisionx;
    }
    return $result;
}

function get_event_search_page($pdo,$where,$limit){
    return pdo_fetch_all($pdo,'select * from event where '.$where.' limit '.$limit);
}

function getuser($pdo,$username){
    return pdo_fetch($pdo,'select * from user where username=?',array($username));
}

function get_event_to($pdo,$eid){
    return pdo_fetch($pdo,'select tomail from event where eid=?',array($eid));
}

function get_event_measure($pdo,$eid){
    return pdo_fetch_all($pdo,'select * from measure where eid='.$eid);
}

function get_event_comment($pdo,$eid){
    return pdo_fetch_all($pdo,'select * from comment where eid='.$eid);
}

function get_event_report($pdo,$eid){
    return pdo_fetch($pdo,'select * from report where eid=?',array($eid));
}

function get_user_info($pdo,$username){
    return pdo_fetch($pdo,'select * from user where username=?',array($username));
}

function get_my_mailgroup($pdo,$uid){
    return pdo_fetch_all($pdo,'select * from mailgroup where uid=?',array($uid));
}

function get_week_event($pdo){
    $result = pdo_fetch_all($pdo,"select * from event where YEARWEEK(FROM_UNIXTIME(`createtime`,'%Y-%m-%d %H:%i:%s'),1)=YEARWEEK(now(),1) order by createtime desc");
    foreach ($result as $k=>$v){
        $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
        $divisionx = array();
        foreach ($division as $m){
            $divisionx[] = $m['division'];
        }
        $result[$k]['division'] = $divisionx;
    }
    return $result;
}

function get_last_week($pdo){
    $result = pdo_fetch_all($pdo,"select * from event where YEARWEEK(FROM_UNIXTIME(`createtime`,'%Y-%m-%d %H:%i:%s'),1)=(YEARWEEK(now(),1)-1) order by createtime desc");
    foreach ($result as $k=>$v){
        $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
        $divisionx = array();
        foreach ($division as $m){
            $divisionx[] = $m['division'];
        }
        $result[$k]['division'] = $divisionx;
    }
    return $result;
}

function get_cost_month($pdo,$key,$k,$i){
    return pdo_fetch_all($pdo,"select * from cost where ftype=? and stype=? and month(FROM_UNIXTIME(time))=?",array($key,$k,$i));
}

function get_who ($pdo,$realname){
    return pdo_fetch_all($pdo,'select * from event where who=?',array($realname));
}

function insert_schedule($pdo,$params){
    $sql = "insert into schedule set eid=?,s_subject=?,s_user=?,s_time=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['eid'],$params['s_subject'],$params['s_user'],$params['s_time']));
    return $pdo->lastInsertId();
}

function insert_comment($pdo,$params){
    $sql = "insert into comment set eid=?,comment=?,user=?,mtime=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['eid'],$params['comment'],$params['user'],$params['mtime']));
    return $pdo->lastInsertId();
}

function insertuser($pdo,$username,$realname,$email){
    $sql = "insert into user set username=?,realname=?,email=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($username,$realname,$email));
    return $pdo->lastInsertId();
}

function insert_mail_group($pdo,$mailgroup){
    $sql = "insert into mailgroup set uid=?,gname=?,mail_arr=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($mailgroup['uid'],$mailgroup['gname'],$mailgroup['mail_arr']));
    return $pdo->lastInsertId();
}

function insert_measure($pdo,$measure){
    $sql = "insert into measure set eid=?,measure=?,muser=?,mtime=?,status=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($measure['eid'],$measure['measure'],$measure['muser'],$measure['mtime'],$measure['status']));
    return $pdo->lastInsertId();
}

function update_event_stype($pdo,$params){
    $sql = "update event set stypeid=? where eid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['stypeid'],$params['eid']));
    return $sth->rowCount();
}

function update_event_to($pdo,$eid,$to){
    $sql = "update event set tomail=? where eid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($to,$eid));
    return $sth->rowCount();
}

function checkclose($pdo,$eid){
    $sql = "update event set islock=1 where eid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($eid));
    return $sth->rowCount();
}

function update_content($pdo,$content,$eid){
    $sql = "update event set content=? where eid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($content,$eid));
    return $sth->rowCount();
}

function update_measure($pdo,$measure){
    $sql = "update measure set measure=?,muser=?,mtime=?,status=? where mid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($measure['measure'],$measure['muser'],$measure['mtime'],$measure['status'],$measure['mid']));
    return $sth->rowCount();
}

function update_event_report($pdo,$params){
    $sql = "update report set r_user=?,r_division=?,content=?,measure=?,r_time=? where eid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['r_user'],$params['r_division'],$params['content'],$params['measure'],$params['r_time'],$params['eid']));
    return $sth->rowCount();
}

function check_attention($pdo,$att){
    $array =  pdo_fetch_all($pdo,'select * from attention where uid='.$att['uid'].' and eid='.$att['eid']);
    if (empty($array)){return 0;}
    else return 1;
}

function check_open_date($pdo,$eid,$time){
    $array =  pdo_fetch_all($pdo,'select * from measure where eid='.$eid.' and mtime>'.$time);
    if (empty($array)){return 0;}
    else return 1;
}

function insert_attention($pdo,$att){
    $sql = "insert into attention set eid=?,uid=?,time=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($att['eid'],$att['uid'],$att['time']));
    return $pdo->lastInsertId();
}

function del_att($pdo,$uid,$eid){
	$sql = "delete from attention where uid=? and eid=?";
	$sth = $pdo->prepare($sql);
	$sth ->execute(array($uid,$eid));
	return $sth->rowCount();
}

function del_event($pdo,$eid){
	$sql = "delete from event where eid=?";
	$sth = $pdo->prepare($sql);
	$sth ->execute(array($eid));
	return $sth->rowCount();
}

function delete_my_mailgroup($pdo,$id){
    $sql = "delete from mailgroup where id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($id));
    return $sth->rowCount();
}

function delete_measure($pdo,$id){
    $sql = "delete from measure where mid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($id));
    return $sth->rowCount();
}

function delete_schedule($pdo,$id){
    $sql = "delete from schedule where sid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($id));
    return $sth->rowCount();
}

function insert_event_report($pdo,$params){
    $sql = "insert into report set eid=?,r_user=?,r_division=?,content=?,measure=?,r_time=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['eid'],$params['r_user'],$params['r_division'],$params['content'],$params['measure'],$params['r_time']));
    return $pdo->lastInsertId();
}


function update_event($pdo,$params){
    $sql = "update event set subject=?,description=?,etypeid=?,affect=?,level=?,createtime=?,solvetime=?,affecttime=?,islock=?,closetime=?,who=?,summary=? where eid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['subject'],$params['description'],$params['etypeid'],$params['affect'],$params['level'],$params['createtime'],$params['solvetime'],$params['affecttime'],$params['islock'],$params['closetime'],$params['who'],$params['summary'],$params['eid']));
    return $sth->rowCount();
}

function update_schedule($pdo,$params){
    $sql = "update schedule set s_subject=?,s_user=?,s_time=? where sid=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['s_subject'],$params['s_user'],$params['s_time'],$params['sid']));
    return $sth->rowCount();
}

function get_time_affect($affecttime){
    $day = floor($affecttime/(24*60*60));
    $hour = floor(($affecttime-$day*24*60*60)/(60*60));
    $minute = floor(($affecttime-$day*24*60*60-$hour*60*60)/60);
    $second = $affecttime-$day*24*60*60-$hour*60*60-$minute*60;
    return $day.":".$hour.":".$minute.":".$second;
}

function get_today_schedule($pdo,$eid,$rangea,$rangeb){
    return pdo_fetch($pdo,"select s_subject from schedule where eid=? AND s_time>=".$rangea." AND s_time<=".$rangeb." order by sid desc limit 1",array($eid));
}

function get_today_data($pdo,$rangea,$rangeb){
    $critical = pdo_fetch_all($pdo,"select * from event where createtime>=".$rangea." AND createtime<=".$rangeb." AND level<=4");
    $nostop = pdo_fetch_all($pdo,"select * from event where createtime<=".$rangea." OR createtime>=".$rangeb);
    $todaynostop = array();
    foreach($nostop as $key=>$v){
        $nowschedule = get_today_schedule($pdo,$v['eid'],$rangea,$rangeb);
        if ($nowschedule['s_subject'] != "") $todaynostop[]=$v;
    }
    $data['critical'] = $critical;
    $data['nostop'] = $todaynostop;
    return $data;
}


function get_now_schedule($pdo,$eid){
    return pdo_fetch($pdo,"select s_subject from schedule where eid=? order by sid desc limit 1",array($eid));
}

function get_lastmonth_event($pdo,$type){
    return pdo_fetch($pdo,"select count(*) as total from event where FROM_UNIXTIME(createtime,'%Y-%m')=date_format(DATE_SUB(curdate(),INTERVAL 1 MONTH),'%Y-%m') and etypeid=?",array($type));
}

function get_thismonth_event($pdo,$type){
    return pdo_fetch($pdo,"select count(*) as total from event where FROM_UNIXTIME(createtime,'%Y-%m')=date_format(now(),'%Y-%m') and etypeid=?",array($type));
}

function get_month_event($pdo){
    $result = pdo_fetch_all($pdo,"select * from event where FROM_UNIXTIME(createtime,'%Y-%m')=date_format(now(),'%Y-%m') order by createtime desc");
    foreach ($result as $k=>$v){
        $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
        $divisionx = array();
        foreach ($division as $m){
            $divisionx[] = $m['division'];
        }
        $result[$k]['division'] = $divisionx;
    }
    return $result;
}

function get_month_event_point($pdo,$start,$end){
    if ($start!=0 && $end!=0) {
        $result = pdo_fetch_all($pdo, "select eid,who,level,affecttime from event where createtime >= ".$start." and createtime <= ".$end." order by createtime desc");
        foreach ($result as $k=>$v){
            $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
            $divisionx = array();
            foreach ($division as $m){
                $divisionx[] = $m['division'];
            }
            $result[$k]['division'] = $divisionx;
        }
        return $result;
    }
    else{
        $diff_time = time() - 3600 * 24 * 30;
        //return pdo_fetch_all($pdo, "select who,division,level,affecttime from event where FROM_UNIXTIME(createtime,'%Y-%m')=date_format(now(),'%Y-%m') order by createtime desc");
        $result = pdo_fetch_all($pdo, "select eid,who,level,affecttime from event where createtime >= ".$diff_time." order by createtime desc");
        foreach ($result as $k=>$v){
            $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
            $divisionx = array();
            foreach ($division as $m){
                $divisionx[] = $m['division'];
            }
            $result[$k]['division'] = $divisionx;
        }
        return $result;
    }
}

function get_last_month($pdo){
    $result = pdo_fetch_all($pdo,"select * from event where FROM_UNIXTIME(createtime,'%Y-%m')=date_format(DATE_SUB(curdate(),INTERVAL 1 MONTH),'%Y-%m') order by createtime desc");
    foreach ($result as $k=>$v){
        $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
        $divisionx = array();
        foreach ($division as $m){
            $divisionx[] = $m['division'];
        }
        $result[$k]['division'] = $divisionx;
    }
    return $result;
}

function get_events_by_params($pdo,$params){
    $diff_time = time() - 3600 * 24 * 30;
	$result =  pdo_fetch_all($pdo,"select * from event where ".$params['params_name']." = '".$params['params_value']."' and createtime >= ".$diff_time." order by createtime desc");
    foreach ($result as $k=>$v){
        $division = pdo_fetch_all($pdo,'select division from division where eid=?',array($v['eid']));
        $divisionx = array();
        foreach ($division as $m){
            $divisionx[] = $m['division'];
        }
        $result[$k]['division'] = $divisionx;
    }
    return $result;
}

function get_every_month_event($pdo,$i){
    return pdo_fetch($pdo,"select count(*) as total from event where date_format(FROM_UNIXTIME(createtime),'%Y%m')=?",array($i));
}

function get_every_month_affect_time($pdo,$i){
    return pdo_fetch($pdo,"select sum(affecttime) as total from event where level <= 4 AND date_format(FROM_UNIXTIME(createtime),'%Y%m')=?",array($i));
}

function get_month_type_event($pdo,$i,$k){
    return pdo_fetch($pdo,"select count(*) as total from event where EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(createtime))=? and etypeid=?",array($i,$k));
}

function get_month_division_event($pdo,$i,$k){
    $result = pdo_fetch_all($pdo,"select eid from event where EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(createtime))=?",array($i));
    foreach ($result as $l) $eid[] = $l['eid'];
    foreach ($eid as $n=>$id){
        $array = pdo_fetch_all($pdo, 'select * from division where eid = '.$id.' and division = '.$k);
        if (empty($array))unset($eid[$n]);
        else $eid[$n]=$array[0];
    }
    $count = count($eid);
    return array('total'=>$count);
    //return pdo_fetch($pdo,"select count(*) as total from event where EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(createtime))=? and division=?",array($i,$k));
}

function get_month_search_event_divison($pdo,$start,$stop){
    //$array =  pdo_fetch_all($pdo,"SELECT EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(createtime)) m,division, count(*) total FROM event WHERE createtime>=? and createtime<=? GROUP BY 1,2",array($start,$stop));
    $array =  pdo_fetch_all($pdo,"SELECT EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(createtime)) m,eid FROM event WHERE createtime>=? and createtime<=? GROUP BY 1,2",array($start,$stop));
    foreach ($array as $k){
        $month[$k['m']][] = $k['eid'];
    }
    foreach ($month as $l=>$n){
        foreach ($n as $o=>$m){
            $result = pdo_fetch_all($pdo, 'select division from division where eid = '.$m);
            $division = array();
            foreach ($result as $m){
                $division[] = $m['division'];
            }
            $month[$l][$o] = $division;
        }
    }
    foreach ($month as $p=>$q){
        $count = array();
        foreach ($q as $r){
            foreach ($r as $s){
                $count[$s] += 1;
            }
        }
        $countx[$p] = $count;
    }
    foreach ($countx as $t=>$u){
        foreach ($u as $v=>$w){
            $return[]=array('m'=>$t,'division'=>$v,'total'=>$w);
        }
    }
    return $return;
}

function get_week_search_event_division($pdo,$start,$stop){
    //$arrayo =  pdo_fetch_all($pdo,"select DATE_FORMAT(FROM_UNIXTIME(createtime),'%Y%u') w,division,count(*) t from event WHERE createtime>=? and createtime<=? group by 1,2",array($start,$stop));
    $array =  pdo_fetch_all($pdo,"SELECT DATE_FORMAT(FROM_UNIXTIME(createtime),'%Y%u') w,eid FROM event WHERE createtime>=? and createtime<=? GROUP BY 1,2",array($start,$stop));
    foreach ($array as $k){
        $month[$k['w']][] = $k['eid'];
    }
    foreach ($month as $l=>$n){
        foreach ($n as $o=>$m){
            $result = pdo_fetch_all($pdo, 'select division from division where eid = '.$m);
            $division = array();
            foreach ($result as $m){
                $division[] = $m['division'];
            }
            $month[$l][$o] = $division;
        }
    }
    foreach ($month as $p=>$q){
        $count = array();
        foreach ($q as $r){
            foreach ($r as $s){
                $count[$s] += 1;
            }
        }
        $countx[$p] = $count;
    }
    foreach ($countx as $t=>$u){
        foreach ($u as $v=>$w){
            $return[]=array('w'=>$t,'division'=>$v,'t'=>$w);
        }
    }
    return $return;
}

function get_month_search_event($pdo,$start,$stop){
    return pdo_fetch_all($pdo,"SELECT EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(createtime)) m, count(*) total FROM event WHERE createtime>=? and createtime<=? GROUP BY 1",array($start,$stop));
}

function get_month_search_type($pdo,$start,$stop){
    return pdo_fetch_all($pdo,"SELECT EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(createtime)) m,etypeid,count(*) total FROM event WHERE createtime>=? and createtime<=? group by 1,2",array($start,$stop));
}

function get_week_type_search_event($pdo,$start,$stop){
    return pdo_fetch_all($pdo,"select DATE_FORMAT(FROM_UNIXTIME(createtime),'%Y%u') w,etypeid,count(*) t from event WHERE createtime>=? and createtime<=? group by 1,2",array($start,$stop));
}

function get_month_search_useable($pdo,$start,$stop){
    return pdo_fetch_all($pdo,"SELECT EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(createtime)) m, SUM(affecttime) total FROM event WHERE createtime>=? and createtime<=? GROUP BY 1",array($start,$stop));
}

function get_params(){
    $params = array_merge($_GET,$_POST);
    foreach($params as $params_key=>$params_value){
        $params[$params_key]=is_array($params_value)?$params_value:trim($params_value);
    }
    return $params;
}

function rand_string($len=6,$type='',$addChars='') {
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    $chars   =   str_shuffle($chars);
    $str     =   substr($chars,0,$len);
    return $str;
}

function msg_redirect($url,$msg=""){
    header("Content-type: text/html; charset=utf-8");
    $script="<script>";
    if($msg){
        $script.="alert('".$msg."');";
    }
    if($url=='back'){
        $script.= "history.go(-1);";
    }else{
        $script.="window.location ='".$url."';";
    }
    $script.="</script>";
    echo $script;exit;
}
