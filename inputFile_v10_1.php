<?php
// $startTime = microtime(true);
?>

<?php
// table1 --begin
// header("Content-Type: text/html; charset=utf-8");
include("./inputFile_pdo_conn.php");
// PDO連線檔匯入
// include("./fileInput_conn.php");
// mySqli連線檔匯入

$data_sum = 0;
// 計算匯入資料

$sql_count1 = "SELECT COUNT(*) as count1 FROM checktable";
$result_count1 = $db_link->prepare($sql_count1);
$result_count1->execute();
$result_row1 = $result_count1->fetch(PDO::FETCH_ASSOC);
// echo $result_row1["count1"];
// 計算前總表原筆數

$sql_count2 = "SELECT COUNT(*) as count2 FROM checktable_repeat";
$result_count2 = $db_link->prepare($sql_count2);
$result_count2->execute();
$result_row2 = $result_count2->fetch(PDO::FETCH_ASSOC);
// echo $result_row["count2"];
// 計算前重複紀錄表原筆數

$sql_count3 = "SELECT COUNT(*) as count3 FROM checktable_errlog";
$result_count3 = $db_link->prepare($sql_count3);
$result_count3->execute();
$result_row3 = $result_count3->fetch(PDO::FETCH_ASSOC);
// echo $result_row["count2"];
// 計算前錯誤紀錄表原筆數


if (isset($_FILES["uploadFile"])) {

    $fileTmpDir = dirname(dirname(__FILE__)) . "\project_file_tmp\\";
    $allowedType = array("txt");
    // 允許檔案副檔名類型

    $uploadFileName = $_FILES["uploadFile"]["name"];
    $uploadFileType = strtolower(pathinfo($uploadFileName, PATHINFO_EXTENSION));
    // 小寫檔案副檔名

    if (in_array($uploadFileType, $allowedType)) {
        // 檔案副檔名等於定義允許的副檔名
        // echo "上傳檔案類型符合格式" . "<br>";
        move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $fileTmpDir . $_FILES["uploadFile"]["name"]);
        // echo "檔案上傳成功" . "<br>";
        // 檔案上傳到指定路徑

        $fileName = $fileTmpDir . $_FILES["uploadFile"]["name"];
        $oldFile = fopen($fileName, "r");

        while ($row = fgets($oldFile)) {
            $row = str_replace('"', '', $row);
            $rowArr = explode("\t", $row);
            // 字串轉陣列

            foreach ($rowArr as $item => $value) {
                $rowArr[$item] = trim($value);
                // 去除字串兩側空白
            }

            $colArr = explode(" ", $rowArr[2]);
            if ($colArr[0] == "下午") {
                $colArr[1] = date("H:i:s", strtotime($colArr[1] . "+ 12hour"));
            }
            // 用strtotime轉換時間為時間格式
            // var_dump($colArr);
            if ($rowArr[0]) {
                $rowArr[0] = date("Y-m-d", strtotime($rowArr[0]));
            }
            // 用strtotime轉換日期為日期格式，符合資料庫表格資料型態

            $newRowArr[0] = trim($rowArr[0] . " " . $colArr[1]);
            // 日期
            $newRowArr[1] = trim($rowArr[3]);
            // 工件1
            $newRowArr[2] = trim($rowArr[4]);
            // 工件2
            $newRowArr[3] = trim($rowArr[5]);
            // 工件3
            $newRowArr[4] = trim($rowArr[10]);
            // 壓力值
            // 去除右側空白(包括空格、制表符、換行符等)
            $newRowArr[5] = "";
            // 結果

            // var_dump($newRowArr);
            // echo "<br>";
            // 檢查格式

            $data_sum++;
            //計算次數累積

            $check_workList1 = substr($newRowArr[1], 0, 18);
            $check_workList2 = substr($newRowArr[2], 0, 18);
            $check_workList3 = substr($newRowArr[3], 0, 18);
            $sql_check = "SELECT * FROM checktable_bom 
                            WHERE workList1 = '$check_workList1' 
                            AND workList2 = '$check_workList2'
                            AND workList3 = '$check_workList3'";
            $result_check = $db_link->prepare($sql_check);
            $result_check->execute();

            if ($result_check->rowCount() == 0) {
                $sql_insert1 = "INSERT INTO checktable_errlog (ptTime, workList1, workList2, workList3, prValue)
                VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]')";
                $checkErr = $db_link->prepare($sql_insert1);
                $checkErr->execute();
                continue;
            }

            if ($newRowArr[4] > 9000) {
                $newRowArr[5] = trim("0");
            } elseif ($newRowArr[4] < 7000) {
                $newRowArr[5] = trim("0");
            } else {
                $newRowArr[5] = trim("1");
            }
            //判斷壓力值為0或1            

            $sql_repeatData = "SELECT workList3 
                FROM checktable 
                WHERE workList3 = '$newRowArr[3]'";
            $result_repeatData1 = $db_link->prepare($sql_repeatData);
            $result_repeatData1->execute();

            if ($result_repeatData1->rowCount() == 0) {
                $sql_insert2 = "INSERT INTO checktable (ptTime, workList1, workList2, workList3, prValue, nowResult) 
                                VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]','$newRowArr[5]')";
                $result_insert2 = $db_link->prepare($sql_insert2);
                $result_condition = $result_insert2->execute();

                if (!$result_condition) {
                    $sql_insert3 = "INSERT INTO checktable_errlog (ptTime, workList1, workList2, workList3, prValue) 
                    VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]')";
                    $result_insert3 = $db_link->prepare($sql_insert3);
                    $result_insert3->execute();

                    // $error = $result_insert3->errorInfo();
                    // print_r($error);
                    //顯示PDO錯誤訊息
                }
            } else {
                $sql_copy = "INSERT INTO checktable_repeat 
                                    SELECT ptTime, workList1, workList2, workList3, prValue, nowResult 
                                    FROM checktable 
                                    WHERE workList3 = '$newRowArr[3]'";

                $sql_update = "UPDATE checktable 
                                    SET ptTime = '$newRowArr[0]', workList1 = '$newRowArr[1]', workList2 = '$newRowArr[2]', workList3 = '$newRowArr[3]', prValue = '$newRowArr[4]', nowResult = '$newRowArr[5]' 
                                    WHERE workList3 = '$newRowArr[3]'";

                $result_copy = $db_link->prepare($sql_copy);
                $result_copy->execute();
                $result_update = $db_link->prepare($sql_update);
                $result_update->execute();
            }
        }

        $sql_count1_1 = "SELECT COUNT(*) as count1_1 FROM checktable";
        $result_count1_1 = $db_link->prepare($sql_count1_1);
        $result_count1_1->execute();
        $result_row1_1 = $result_count1_1->fetch(PDO::FETCH_ASSOC);
        // 計算後總表筆數

        $sql_count2_1 = "SELECT COUNT(*) as count2_1 FROM checktable_repeat";
        $result_count2_1 = $db_link->prepare($sql_count2_1);
        $result_count2_1->execute();
        $result_row2_1 = $result_count2_1->fetch(PDO::FETCH_ASSOC);
        // 計算後重複紀錄表計算筆數

        $sql_count3_1 = "SELECT COUNT(*) as count3_1 FROM checktable_errlog";
        $result_count3_1 = $db_link->prepare($sql_count3_1);
        $result_count3_1->execute();
        $result_row3_1 = $result_count3_1->fetch(PDO::FETCH_ASSOC);
        // echo $result_row["count2"];
        // 計算後錯誤紀錄表原筆數

        // echo "<ul><li>已匯入檔案</li>
        //         <br><li>本次上傳檔案共" . $data_sum . "筆資料</li>
        //         <br><li>本次匯入主資料表共" . ($result_row1_1["count1_1"] - $result_row1["count1"]) . "筆資料</li>
        //         <br><li>本次匯入歷時紀錄表共" . ($result_row2_1["count2_1"] - $result_row2["count2"]) . "筆資料</li>
        //         <br><li>本次匯入錯誤紀錄表共" . ($data_sum - ($result_row1_1["count1_1"] - $result_row1["count1"]) - ($result_row2_1["count2_1"] - $result_row2["count2"])) . "筆資料</li>
        //         <br><li>主資料表總計" . $result_row1_1["count1_1"] . "筆資料</li>
        //         <br><li>歷時紀錄表總計" . $result_row2_1["count2_1"] . "筆資料</li>
        //         <br><li>錯誤紀錄表總計" . $result_row3_1["count3_1"] . "筆資料</li>
        //         <br><li>請選擇日期查詢</li></ul>";

        // $sql_query_err = "SELECT * FROM checktable_errlog";
        // $result_query_err = $db_link->prepare($sql_query_err);
        // $result_query_err->execute();
        // $err_arr = array();
        // while ($row = $result_query_err -> fetch(PDO::FETCH_ASSOC)) {
        //     $err_arr[] = array_values($row);
        
        // }
        // $err_json = json_encode(array('data' => $err_arr));
        // echo $err_json;
        
    } else {
        echo "<ul><li>請匯入文字檔格式檔案</li></ul>";
    }
}
//table1 --end

?>

<?php
// $endTime = microtime(true);
// $totalTime = $endTime - $startTime;
// echo "程式執行時間:" . $totalTime . "秒";
?>
