<?php
require_once '../libraries/common.lib.php';
error_reporting(0);
$op = isset($params['op'])?$params['op']:"";
if($op=="search"){
    $keyword = trim($params['keyword']);
    $who = trim($params['who']);
    $islock = intval($params['status']);
    $division = $params['division'];
    if (empty($division)) $division = array('1','2','3','4','5','6','7','8','9');
    $levels = intval($params['levels']);
    $levele = intval($params['levele']);
    if ($levels > $levele){
        $level = $levele;
        $levele = $levels;
        $levels = $level;
    }
    $etype = intval($params['etype']);
    $start = intval(strtotime($params['start']));
    $end = intval(strtotime($params['stop']));
    if (!empty($end)) $end+=86400;
    $islock != 2 ? $where = $where." islock=".$islock : $where = $where." 1";
    !empty($start) ? $where = $where." and createtime>=".$start : $where = $where." and 1";
    !empty($end) ? $where = $where." and createtime<=".$end : $where = $where." and 1";
    !empty($keyword) ? $where = $where." and subject like '%".$keyword."%'" : $where = $where." and 1";
    !empty($who) ? $where = $where." and who like '%".$who."%'" : $where = $where." and 1";
    !empty($levels) ? $where = $where." and level>=".$levels : $where = $where." and 1";
    !empty($levele) ? $where = $where." and level<=".$levele : $where = $where." and 1";
    //!empty($division) ? $where = $where." and division=".$division : $where = $where." and 1";
    !empty($etype) ? $where = $where." and etypeid=".$etype : $where = $where." and 1";
}elseif($params['stypeid']){
    $where = "stypeid=".$params['stypeid'];
}elseif($params['division'] && !$op) {
    $where = "division=".$params['division'];
}elseif($op=="thisweek"){
	$where = "YEARWEEK(FROM_UNIXTIME(`createtime`,'%Y-%m-%d %H:%i:%s'),1)=YEARWEEK(now(),1)";
}elseif($op=="thismonth"){
	$where = "FROM_UNIXTIME(createtime,'%Y-%m')=date_format(now(),'%Y-%m')";
}elseif($op=="keys"){
    $kwds = trim($params['keywds']);
    $where = "subject like '%".$kwds."%' OR description like '%".$kwds."%'";
}
elseif($op=="searchlist"){
    if(intval($params['etypeid'])) $where = "etypeid=".intval($params['etypeid']);
    if(intval($params['level'])) $where = "level=".intval($params['level']);
    $division = array('1','2','3','4','5','6','7','8','9');
}
$event_list = get_event_search($pdo,$where);
foreach ($event_list as $k=>$v){
    $i = 0;
    foreach ($division as $m){
        $result = search_division($pdo,$v['eid'],$m);
        if (!empty($result)) $i=1;
    }
    if ($i==0) unset($event_list[$k]);
}
if(!empty($event_list)){
        $total = count($event_list);
}else{
        $total = 0;
}
$template = 'searchresult';
require_once '../libraries/decorator.inc.php';
