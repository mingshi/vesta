<?php
$cfg['database']['host']='';
$cfg['database']['port']='3306';
$cfg['database']['user']='uvesta';
$cfg['database']['password']='pvesta';
$cfg['database']['dbname']='vesta';


$cfg['ldapurl'] = "";
$cfg['hostname'] = "";

$cfg['checkermail'] = array(
      '1' => array(''),
);

$cfg['smtp'] = array(
    'server' => '',
    'port' => '25',
    'user' => '',
    'password' => '',
    'mailtype' => 'HTML',
    'sender' => '',
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
);
$cfg['islock'] = array(
    '0' => '开',
    '1' => '关',
);
$cfg['user'] = array(
);
$cfg['authority'] = array(
);

$cfg['close'] = array(
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
