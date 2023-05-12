<?php
// table1 --begin
// header("Content-Type: text/html; charset=utf-8");
include("./inputFile_pdo_conn.php");
// PDO連線檔匯入
// include("./fileInput_conn.php");
// mySqli連線檔匯入

//table2 --begin
if (isset($_POST["start_date"]) && isset($_POST["end_date"]) && isset($_POST["serial_num"])) {
    if ((!empty($_POST["start_date"])) && (!empty($_POST["end_date"])) && (!empty($_POST["serial_num"]))) {
        $setDate = date("Y-m-d",  strtotime($_POST["start_date"]));
        $setDate1 = date("Y-m-d", strtotime($_POST["end_date"]));
        $serial_num = $_POST["serial_num"];
        $result_ok = $_POST["result_ok"];
        // 輸入時間，轉換所需格式

        // $sql_setData = "SELECT DATE(ptTime) AS 'PTIME',SUM(nowResult) AS 'OK', 
        //                 SUM(case nowResult when '0' then 1 ELSE 0 END) AS 'NG'
        //                 FROM checktable  
        //                 WHERE DATE(ptTime) BETWEEN '$setDate' AND '$setDate1'
        //                 GROUP BY DATE(ptTime);";

        $sql_setData = "SELECT * FROM checktable 
        WHERE DATE(ptTime)  BETWEEN '$setDate' AND '$setDate1'
        AND workList3 LIKE '%$serial_num%' 
        AND nowResult = '$result_ok'";

        $result_setData = $db_link->prepare($sql_setData);
        $result_setData->execute();

        if ($result_setData->rowCount() == 0) {
            echo "<br><li>查無資料</li></ul>";
            exit();
        }

        $result_row = $result_setData->fetchAll(PDO::FETCH_ASSOC);
        $result_json = json_encode($result_row);
        echo $result_json;
    }else{
        echo "請輸入開始日期、結束日期、工號";
    }
}
//table2 --end
?>

