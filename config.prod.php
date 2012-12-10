<?php
$cfg['database']['host']='10.10.8.35';
$cfg['database']['port']='3306';
$cfg['database']['user']='uvesta';
$cfg['database']['password']='pvesta';
$cfg['database']['dbname']='vesta';


$cfg['ldapurl'] = "http://10.11.6.164/ldap/2.1/ldap_anjuke_login.php";
$cfg['hostname'] = "vesta.corp.anjuke.com";

$cfg['checkermail'] = array(
      '1' => array('justinni@anjukeinc.com'),
      '2' => array('enzhang@anjuke.com'),
      '3' => array('kevinkuang@anjukeinc.com'),
      '4' => array('kevinkuang@anjukeinc.com','daoyuanwang@anjuke.com'),
      '5' => array('kevinkuang@anjukeinc.com','daoyuanwang@anjuke.com'),
      '6' => array('kevinkuang@anjukeinc.com','daoyuanwang@anjuke.com'),
);

$cfg['smtp'] = array(
    'server' => 'smtp.anjuke.com',
    'port' => '25',
    'user' => 'alert@anjuke.com',
    'password' => 'alert@ajk_ops',
    'mailtype' => 'HTML',
    'sender' => 'alert@anjuke.com',
);

$cfg['etype'] = array(
    '1' => '运营商故障',
    //'2' => '网络故障',
    '3' => '硬件故障',
    '4' => '人为故障',
    '5' => '程序bug',
    '6' => '安全事故',
    //'7' => '配置问题',
);
$cfg['stype'] = array(
    '1' => '监控发现',
    '2' => '部门处理',
);
$cfg['level'] = array(
    '1' => '1',
    '2' => '2',
    '3' => '3',
    '4' => '4',
    '5' => '5',
    '6' => '6',
);
$cfg['division'] = array(
    '1' => '二手房',
    '2' => '好租',
    '3' => '新房',
    '4' => '金铺',
    '5' => '总部运维',
    '6' => '第三方',
    '7' => 'DW',
    '8' => 'IT',
    '9' => '总部开发',
);
$cfg['islock'] = array(
    '0' => '开',
    '1' => '关',
);
$cfg['user'] = array(
    '1' => 'mingshi',
    '2' => 'zhiwensun',
    '3' => 'leichen_sh',
    '4' => 'cathyzhang',
    '5' => 'peterzhu',
    '6' => 'gywang',
    '7' => 'zorrozuo',
    '8' => 'liming',
    '9' => 'fzhou',
    '10' => 'tomleng',
    '11' => 'kevinkuang',
    '12' => 'wellerkong',
    '13' => 'canzhang',
);
$cfg['authority'] = array(
    'mingshi' => '1',
    'liming' => '1',
    'fzhou' => '1',
    'gywang' => '1',
    'zhongshengchen' => '1',
    'wellerkong' => '1',
    'canzhang' => '1',
    'jizhang' => '1',
    'tomleng' => '1',
    'Keithlan' => '1',
    'huashengliao' => '1',
    'kaicai' => '1',
    'yundu' => '1',
);

$cfg['close'] = array(
    '1'=> array('justinni'=>'1',),
    '2'=> array('enzhang'=>'1',),
    '3'=> array('kevinkuang'=>'1',),
    '4'=> array('kevinkuang'=>'1','daoyuanwang'=>'1',),
    '5'=> array('kevinkuang'=>'1','daoyuanwang'=>'1',),
    '6'=> array('kevinkuang'=>'1','daoyuanwang'=>'1',),
);

$cfg['color'] = array(
    '1' => '#C72C95',
    '2' => '#D8E0BD',
    '3' => '#B3DBD4',
    '4' => '#69A55C',
    '5' => '#B5B8D3',
    '6' => '#F4E23B',
    '7' => '#000000',
);

//cost
$cfg['ftype'] = array(
    '1' => 'IDC',
    '2' => 'CDN',
    '3' => 'SMS',
);
$cfg['rtype'] = array(
    '1' => array(
        '1' => 'idc01',
        '2' => 'idc02',
    ),
    '2' => array(
        '1' => 'cdn01',
        '2' => 'cdn02',
    ),
    '3' => array(
        '1' => 'sms01',
        '2' => 'sms02',
    ),
);
