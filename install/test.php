<?php

header("Content-type: text/html; charset=utf-8");
$dbHost="127.0.0.1";
$dbPwd="YrsjYBADErC5PyKc";
$dbUser="an_juanpao_cn";
$conn =  mysqli_connect($dbHost, $dbUser, $dbPwd);
$sqlfile = 'db.sql';
$dbStr= file_get_contents($sqlfile);
$dbPrefix = empty($_POST['dbprefix']) ? 'db_' : trim('');
$config['dbPrefix']="";
$sqlFormat = sql_split($dbStr, $dbPrefix,'');

/**
 * 执行SQL语句
 */
$str ="";
$counts = count($sqlFormat);
for ($i = $n; $i < $counts; $i++) {
    $sql = trim($sqlFormat[$i]);
    if (strstr($sql, 'INSERT')) {
        echo $sql;

    }
}
die();
mysqli_query($conn,$dbStr);