<?php
require_once '../libraries/common.lib.php';
error_reporting(0);
$op = isset($params['op'])?$params['op']:"";
if($op=="search"){
    $keyword = trim($params['keyword']);
    $islock = intval($params['status']);
    $level = intval($params['level']);
    $etype = intval($params['etype']);
    $start = intval(strtotime($params['start']));
    $end = intval(strtotime($params['stop']));
    $islock != 2 ? $where = $where." islock=".$islock : $where = $where." 1";
    !empty($start) ? $where = $where." and createtime>=".$start : $where = $where." and 1";
    !empty($end) ? $where = $where." and createtime<=".$end : $where = $where." and 1";
    !empty($keyword) ? $where = $where." and subject like '%".$keyword."%'" : $where = $where." and 1";
    !empty($level) ? $where = $where." and level=".$level : $where = $where." and 1";
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
    if(intval($params['division'])) $where = "division=".intval($params['division']);
}
$event_list = get_event_search($pdo,$where);
if(!empty($event_list)){
        $total = count($event_list);
}else{
        $total = 0;
}
$template = 'searchresult';
require_once '../libraries/decorator.inc.php';
