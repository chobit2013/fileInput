<?php
header("Content-Type: text/html; charset=utf-8");

// $startTime = microtime(true);
// 計算起始時間

include("./fileInput_conn.php");
//連線檔匯入

$fileName = "y_2023(3-1).txt";
$oldFile = fopen($fileName, "r");
$newFile = fopen("y_2023(v4).txt", "w");
// 開啟檔案

fread($oldFile, filesize($fileName));
$title = array(0 => "日期", 1 => "工件1", 2 => "工件2", 3 => "工件3", 4 => "壓力值", 5 => "結果\n");
$result = implode("\t", $title);
fwrite($newFile, $result);
fclose($oldFile);
fclose($newFile);
// 檔案關閉

$fileName = "y_2023(3-1).txt";
$oldFile = fopen($fileName, "r");
$newFile = fopen("y_2023(v4).txt", "a");
// 開啟檔案，續寫內容

while ($row = fgets($oldFile)) {
    $row = str_replace('"', '', $row);
    $rowArr = explode("\t", $row);

    // 字串轉陣列
    foreach ($rowArr as $item => $value) {
        $rowArr[$item] = trim($value);
        // 去除字串兩側空白
    }

    // var_dump($rowArr);
    $colArr = explode(" ", $rowArr[2]);
    if ($colArr[0] == "下午") {
        $colArr[1] = date("H:i:s", strtotime($colArr[1] . "+ 12hour"));
    }
    //用strtotime轉換時間為時間格式

    if ($rowArr[0]) {
        $rowArr[0] = date("Y-m-d", strtotime($rowArr[0]));
    }
    //用strtotime轉換日期為日期格式

    $newRowArr[0] = trim($rowArr[0] . " " . $colArr[1]);
    // 日期
    $newRowArr[1] = trim($rowArr[3]);
    // 工件1
    $newRowArr[2] = trim($rowArr[4]);
    // 工件2
    $newRowArr[3] = trim($rowArr[5]);
    // 工件3
    $newRowArr[4] = trim($rowArr[10]);
    // 結果
    // 去除右側空白(包括空格、制表符、換行符等)
    $newRowArr[5] = "";
    // 壓力值

    // 檢查格式
    // var_dump($newRowArr);

    if ($newRowArr[4] > 9000) {
        $newRowArr[5] = "NG" . "\n";
    }
    if ($newRowArr[4] < 7000) {
        $newRowArr[5] = "NG" . "\n";
    }
    if ($newRowArr[4] < 9000 && $newRowArr[4] > 7000) {
        $newRowArr[5] = "OK" . "\n";
    }

    if (strlen($newRowArr[1]) === 0) {
        if ($newRowArr) {
            continue;
        };
    }
    if (strlen($newRowArr[2]) === 0) {
        if ($newRowArr) {
            continue;
        };
    }
    if (strlen($newRowArr[3]) === 0) {
        if ($newRowArr) {
            continue;
        };
    }

    // var_dump($newRowArr);

    $sql_insert = "INSERT INTO checktable (ptTime,workList1,workList2,workList3,prValue,nowResult) VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]','$newRowArr[5]')";
    //$newRowArr[0]是個值，需要" "

    $db_link->query($sql_insert);

    // if(mysqli_query($db_link,$sql_insert)){
    //     echo "Ya";
    // }else{
    //     echo "NO~~~";
    // }
    //檢核有無匯入資料

    $result = implode("\t", $newRowArr);
    // 陣列轉字串

    fwrite($newFile, $result);
    // $endTime = microtime(true);
    // $totalTime = $endTime - $startTime;
    // echo "程式執行時間:".$totalTime."秒";
}
?>