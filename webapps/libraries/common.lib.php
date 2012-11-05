<?php
define('SC_PATH',realpath(dirname(__FILE__).'/../'));

require_once SC_PATH.'/libraries/config.inc.php';
if (file_exists(SC_PATH.'/../config.prod.php')) {
    require_once SC_PATH.'/../config.prod.php';
}
require_once SC_PATH.'/libraries/functions.lib.php';

$pdo = SolrDb::getLink(
    $cfg['database']['host'],
    $cfg['database']['user'],
    $cfg['database']['password'],
    $cfg['database']['dbname']
);//实例化pdo链接

$pdo_cost = SolrDb::getLink(
    $cfg['database_cost']['host'],
    $cfg['database_cost']['user'],
    $cfg['database_cost']['password'],
    $cfg['database_cost']['dbname']
);

$params = get_params();

if(!isset($_COOKIE['queue_session_id'])||!$_COOKIE['queue_session_id']){
    setcookie('queue_session_id',rand_string(12));
}
