<?php
$db_host = "localhost";
$db_username = "root";
$db_password = "hota888";
$db_name = "class";
//順序有固定
$db_link = @new mysqli($db_host,$db_username,$db_password,$db_name);
// if($db_link->connect_error !=""){
//     echo "資料庫連接失敗";
// }else{
//     echo "資料庫接成功";
// }
$db_link->query("SET NAMES 'utf8'");
?>