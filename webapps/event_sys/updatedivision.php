<?php
require_once '../libraries/common.lib.php';

$result = pdo_fetch_all($pdo, 'select eid,division from event');


foreach ($result as $key){
    $sql = "insert into division set eid=?,division=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($key['eid'],$key['division']));
    echo $pdo->lastInsertId();
    echo '<br>';

}

?>
