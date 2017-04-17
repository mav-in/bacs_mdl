<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

header("Content-Type: application/x-suggestions+json; charset=windows-1251");
//header('Content-type: application/json; charset=utf-8');
// подключение в БД MySQL


//$query = mysql_query('SELECT * FROM '.addslashes($_GET['tbl']).' WHERE id='.intval($_GET['id']).' LIMIT 1');

$result = array(addslashes($_GET['tbl']));
echo php2json($result);

// рекурсивная функция формирования json-последовательности.
function php2json($obj){
    $msg = "";
    foreach ($obj as $value) {
       $msg .= $value;
    }
    return $msg;
}