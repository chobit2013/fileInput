<?php
$startTime = microtime(true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>檔案上傳</title>
    <link rel="stylesheet" href="inputFile.css">
</head>

<body>
    <div class="wrapper">
        <div class="inputForm">
            <form action="" method="post" enctype="multipart/form-data">
                <h3>查詢資料結果</h3>
                <br>
                <label class="label_uploadFile" for="uploadFile">上傳檔案
                    <br>
                    <span><img class="upload_png" src="./upload.png"></span>
                    <input type="file" name="uploadFile" accept=".txt" id="uploadFile" class="uploadFile">
                </label>
                <br>
                <br>
                <input type="submit" value="送出">
            </form>
            <br>
            <form action="" method="post" enctype="multipart/form-data">
                <label for="uploadDate">輸入起始日期</label>
                <br>
                <br>
                <input type="date" name="uploadDate" id="uploadDate" required>
                <br>
                <br>
                <input type="submit" value="日期查詢資料">
            </form>
        </div>
</body>

</html>

<?php
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

            // if ((strlen($newRowArr[1]) === 0) || (strlen($newRowArr[2]) === 0) || (strlen($newRowArr[3]) === 0)) {
            //     $sql_insert1 = "INSERT INTO checktable_errlog (ptTime, workList1, workList2, workList3, prValue)
            //                     VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]')";
            //     $checkErr = $db_link->prepare($sql_insert1);
            //     $checkErr->execute();
            //     continue;
            // }
            //判斷空白

            $check_workList1 = substr($newRowArr[1], 0, 18);
            $check_workList2 = substr($newRowArr[2], 0, 18);
            $check_workList3 = substr($newRowArr[3], 0, 18);
            $sql_check = "SELECT * FROM checktable_bom 
                            WHERE workList1 = '$check_workList1' 
                            AND workList2 = '$check_workList2'
                            AND workList3 = '$check_workList3'";
            $result_check = $db_link->prepare($sql_check);
            $result_check->execute();

            if($result_check->rowCount() == 0){
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

        echo "<ul><li>已匯入檔案</li>
                <br><li>本次上傳檔案共" . $data_sum . "筆資料</li>
                <br><li>本次匯入主資料表共" . ($result_row1_1["count1_1"] - $result_row1["count1"]) . "筆資料</li>
                <br><li>本次匯入歷時紀錄表共" . ($result_row2_1["count2_1"] - $result_row2["count2"]) . "筆資料</li>
                <br><li>本次匯入錯誤紀錄表共" . ($data_sum - ($result_row1_1["count1_1"] - $result_row1["count1"]) - ($result_row2_1["count2_1"] - $result_row2["count2"])) . "筆資料</li>
                <br><li>主資料表總計" . $result_row1_1["count1_1"] . "筆資料</li>
                <br><li>歷時紀錄表總計" . $result_row2_1["count2_1"] . "筆資料</li>
                <br><li>錯誤紀錄表總計" . $result_row3_1["count3_1"] . "筆資料</li>
                <br><li>請選擇日期查詢</li></ul>";
    } else {
        echo "<ul><li>請匯入文字檔格式檔案</li></ul>";
    }
}else{
    echo "<ul><li>請匯入文字檔格式檔案或選擇日期</li></ul>";
};

if (isset($_POST["uploadDate"])) {
    if (!empty($_POST["uploadDate"])) {
        $setDate = date("Y-m-d",  strtotime($_POST["uploadDate"]));
        $setDate1 = date("Y-m-d", strtotime($_POST["uploadDate"] . "+ 7 days"));
        //輸入時間，轉換所需格式
        // echo $setDate."<br>";
        // echo $setDate1."<br>";

        $sql_setData = "SELECT DATE(ptTime),SUM(nowResult) AS 'OK', 
                        SUM(case nowResult when '0' then 1 ELSE 0 END) AS 'NG'
                        FROM checktable  
                        WHERE DATE(ptTime) BETWEEN '$setDate' AND '$setDate1'
                        GROUP BY DATE(ptTime);";

        $result_setData = $db_link->prepare($sql_setData);
        $result_setData->execute();
        // var_dump($result_setData);
        if ($result_setData->rowCount() == 0) {
            echo "<li>查無資料</li></ul>";
            exit();
        }
        echo "</ul>";
        $bar_title_date = array();
        $bar_title_ok = array();
        $bar_title_ng = array();
        echo "<div class='allTable'>";
        while ($result_row = $result_setData->fetch()) {
            //PDO寫法
            echo "<table class='table1'><tr><th>時間</th><th>OK</th><th>NG</th></tr>
                    <tr><td>" . $result_row[0] . "</td><td>" . $result_row[1] . "筆OK</td><td class='td1'>" . $result_row[2] . "筆NG</td>
                    </table><br>";
            array_push($bar_title_date, $result_row[0]);
            array_push($bar_title_ok, $result_row[1]);
            array_push($bar_title_ng, $result_row[2]);
        }
        echo "</div>";
        // var_dump($bar_title_date);
        // var_dump($bar_title_ok);
        // var_dump($bar_title_ng);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="./jquery-3.5.1.min.js"></script>
    <script src="./DataTables/datatables.min.js"></script>
    <link rel="stylesheet" href="./DataTables/datatables.min.css">
</head>

<body>
    <p>錯誤紀錄表</p>
    <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>時間</th>
                <th>工件1</th>
                <th>工件2</th>
                <th>工件3</th>
                <th>壓力值</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql_query = "SELECT * FROM checktable_errlog";
            $result_query = $db_link->prepare($sql_query);
            $result_query->execute();
            
            while($result_table = $result_query->fetch()) {
                echo "<tr>
                <td>$result_table[0]</td>
                <td>$result_table[1]</td>
                <td>$result_table[2]</td>
                <td>$result_table[3]</td>
                <td>$result_table[4]</td>   
                </tr>";
            }
            // var_dump($result_table);
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th>時間</th>
                <th>工件1</th>
                <th>工件2</th>
                <th>工件3</th>
                <th>壓力值</th>
            </tr>
        </tfoot>
    </table>
    <script>
        $(document).ready(function() {
            $('#example').DataTable({});
        });
    </script>
</body>

</html>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="./chart.js"></script>
</head>

<body>
    <div class="myChart" style="width:800px">
        <canvas id="myChart"></canvas>
    </div>
    </div>

    <script>
        const ctx = document.getElementById('myChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php
                            foreach ($bar_title_date as $item => $value) {
                                echo "'" . $value . "'" . ",";
                            }
                            ?>],
                datasets: [{
                        label: 'OK',
                        data: [<?php
                                foreach ($bar_title_ok as $item => $value) {
                                    echo "'" . $value . "'" . ",";
                                }
                                ?>],
                        borderWidth: 3
                    },
                    {
                        label: 'NG',
                        data: [<?php
                                foreach ($bar_title_ng as $item => $value) {
                                    echo "'" . $value . "'" . ",";
                                }
                                ?>],
                        borderWidth: 3
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: '資料結果'
                    }
                }
            }
        });
    </script>
</body>

</html>



<?php
$endTime = microtime(true);
$totalTime = $endTime - $startTime;
echo "程式執行時間:" . $totalTime . "秒";
?>