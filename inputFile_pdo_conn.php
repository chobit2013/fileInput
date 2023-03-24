<?php
$db_host = "localhost";
$db_username = "root";
$db_password = "hota888";
$db_name = "class";

try{
    $db_link = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8",$db_username,$db_password);
    // echo "資料庫連接成功";
}catch(PDOException $e){
    echo "資料庫連接失敗，訊息:{$e->getMessage()}.<br>";
}
?>