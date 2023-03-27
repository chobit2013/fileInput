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
    <script src="jsmin.js" integrity="sha512-yQJVqoTPFSC73MaslsQaVJ0zHku4Cby3NpQzweSYju+kduWspfF4HmJ3zAo1QGERfsoXdf45q54ph8XTjOlp8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="inputFile.css">
    <style>
    </style>
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
                <input type="submit" value="送出">
            </form>
        </div>
</body>

</html>

<?php
// header("Content-Type: text/html; charset=utf-8");
include("./inputFile_pdo_conn.php");
// include("./fileInput_conn.php");
// 連線檔匯入
// echo "<br>";

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
            $newRowArr[5] = "";
            // 結果
            // 去除右側空白(包括空格、制表符、換行符等)

            // var_dump($newRowArr);
            // 檢查格式

            if ($newRowArr[4] > 9000) {
                $newRowArr[5] = trim("0");
            } elseif ($newRowArr[4] < 7000) {
                $newRowArr[5] = trim("0");
            } else {
                $newRowArr[5] = trim("1");
            }
            //壓力值判斷為true或false

            if (strlen($newRowArr[1]) === 0) {
                continue;
            }
            if (strlen($newRowArr[2]) === 0) {
                continue;
            }
            if (strlen($newRowArr[3]) === 0) {
                continue;
            }
            //陣列元素為0，則跳過

            // if (strlen($newRowArr[0]) === 19) {
            //     echo "A";
            // }
            // if (strlen($newRowArr[0]) === 30) {
            //     echo "B";
            // }
            // if (strlen($newRowArr[0]) === 30) {
            //     echo "C";
            // }
            // if (strlen($newRowArr[0]) === 30) {
            //     echo "D";
            // }
            // if (strlen($newRowArr[0]) === 19) {
            //     echo "E";
            // }

            // foreach($newRowArr as $item => $value){
            //     echo $value."<br>";
            // }
            // 檢查格式

            $sql_repeatData = "SELECT workList1 FROM checktable WHERE workList1 = '$newRowArr[1]' 
                                UNION SELECT workList2 FROM checktable WHERE workList2 = '$newRowArr[2]'";

            $result_repeatData1 = $db_link->query($sql_repeatData);

            if ($result_repeatData1->rowCount() == 0) {
                // if (($result_repeatData1->rowCount() == 0) or ($result_repeatData2->rowCount() == 0) or ($result_repeatData3->rowCount() == 0)) {
                //PDO rowCount()
                //檢查資料庫中一筆資料是否和匯入資料有相同
                $sql_insert = "INSERT INTO checktable (ptTime,workList1,workList2,workList3,prValue,nowResult) 
                                VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]','$newRowArr[5]')";

                $db_link->query($sql_insert);
            } else {
                $sql_copy = "INSERT INTO checktable_repeat 
                                SELECT ptTime, workList1, workList2, workList3, prValue, nowResult 
                                FROM checktable 
                                WHERE workList3 = '$newRowArr[3]'";

                $sql_update = "UPDATE checktable 
                                SET ptTime = '$newRowArr[0]', workList1 = '$newRowArr[1]', workList2 = '$newRowArr[2]', workList3 = '$newRowArr[3]', prValue = '$newRowArr[4]', nowResult = '$newRowArr[5]' 
                                WHERE workList3 = '$newRowArr[3]'";

                $db_link->query($sql_copy);
                $db_link->query($sql_update);
            }
        }
        exit();
    } else {
        exit();
    }
} else {
    exit();
}

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

        $result_setData = $db_link->query($sql_setData);
        // var_dump($result_setData);
        if ($result_setData->rowCount() == 0) {
            exit();
        }

        $bar_title_date = array();
        $bar_title_ok = array();
        $bar_title_ng = array();
        echo "<div class='allTable'>";
        while ($result_row = $result_setData->fetch()) {
            //PDO寫法
            echo "<table class='table1'><tr><th>時間</th><th>OK</th><th>NG</th></tr>";
            echo "<tr><td>" . $result_row[0] . "</td><td>" . $result_row[1] . "筆OK</td><td class='td1'>" . $result_row[2] . "筆NG</td>";
            echo "</table><br>";
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