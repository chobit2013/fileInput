<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>檔案上傳</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-JavaScript-Templates/3.20.0/js/tmpl.min.js" integrity="sha512-yQJVqoTPFSC73MaslsQaVJ0zHku4Cby3NpQzweSYju+kduWspfF4HmJ3zAo1QGERfsoXdf45q54ph8XTjOlp8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="inputFile.css">
    <style>
        .table1 {
            border: 2px solid green;
            border-collapse: collapse;
        }

        .table1 th,
        .table1 td {
            border: 1px solid green;
            padding: 5px;
            text-align: left;
        }

        .td1 {
            color: red;
        }
    </style>
</head>

<body>
    <form action="" method="post" enctype="multipart/form-data">
        <h3>查詢資料結果</h3>
        <br>
        <label for="uploadFile">上傳檔案</label>
        <br>
        <input type="file" name="uploadFile" accept=".txt" id="uploadFile">
        <br>
        <br>
        <input type="submit" value="送出">
    </form>

    <form action="" method="post" enctype="multipart/form-data">
        <br>
        <br>
        <label for="uploadDate">輸入起始日期</label>
        <br>
        <input type="date" name="uploadDate" id="uploadDate" required>
        <br>
        <br>
        <label for="uploadDate_end">輸入結束日期</label>
        <br>
        <input type="date" name="uploadDate_end" id="uploadDate_end" required>
        <br>
        <br>
        <input type="submit" value="送出">
    </form>
    <br>
</body>

</html>

<?php
// header("Content-Type: text/html; charset=utf-8");
include("./inputFile_conn.php");
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

            if ($rowArr[0]) {
                $rowArr[0] = date("Y-m-d", strtotime($rowArr[0]));
            }
            // 用strtotime轉換日期為日期格式

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

            // var_dump($newRowArr);
            // 檢查格式

            if ($newRowArr[4] > 9000) {
                $newRowArr[5] = trim("NG");
            }
            if ($newRowArr[4] < 7000) {
                $newRowArr[5] = trim("NG");
            }
            if ($newRowArr[4] < 9000 && $newRowArr[4] > 7000) {
                $newRowArr[5] = trim("OK");
            }

            if (strlen($newRowArr[1]) === 0) {
                if ($newRowArr) {
                    continue;
                };
            }
            // 長度為0的話則跳出
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
            // 檢查格式

            $sql_repeatData = "SELECT workList1, workList2, workList3 FROM checktable WHERE workList1 = '$newRowArr[1]' OR workList2 = '$newRowArr[2]' OR workList3 = '$newRowArr[3]'";
            //檢查資料庫中一筆資料是否和匯入資料有相同
            $result_repeatData = $db_link->query($sql_repeatData);

            if ($result_repeatData->num_rows == false) {
                $sql_insert1 = "INSERT INTO checktable (ptTime,workList1,workList2,workList3,prValue,nowResult) VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]','$newRowArr[5]')";
                //匯入資料到資料庫，$newRowArr[0]是個值，需要" "
                $db_link->query($sql_insert1);
            } else {
                // echo $db_link->error;
                // echo "檢測到匯入檔案的資料重複" . "<br>";
                continue;
            }
        }
    }
}

if (isset($_POST["uploadDate"]) && isset($_POST["uploadDate"])) {
    // if (empty($_POST["uploadDate"]) || empty($_POST["uploadDate_end"])) {
    //     echo "請填寫日期";
    //     exit();
    // }
    //沒填日期
    if (!empty($_POST["uploadDate"] && $_POST["uploadDate_end"])) {
        $setDate = date("Y-m-d",  strtotime($_POST["uploadDate"]));
        $setDate1 = date("Y-m-d",  strtotime($_POST["uploadDate_end"]));
        //輸入時間，轉換所需格式
        // echo $setDate."<br>";
        // echo $setDate1."<br>";


        $sql_setDate_view = "CREATE VIEW table_view1 AS SELECT Date(ptTime) AS 'pTime', nowResult as result 
                            FROM checktable";
        $db_link->query($sql_setDate_view);

        $sql_setDate_operator = "SELECT pTime, SUM(result='OK'), SUM(result='NG') 
                                FROM table_view1 
                                WHERE  pTime BETWEEN '$setDate' AND '$setDate1' 
                                GROUP BY pTime";
        //$sql_setDate_view_operator= "SELECT a.date, SUM(a.result='OK'), SUM(a.result='NG') FROM (SELECT Date(ptTime) AS 'date', nowResult as result FROM checktable) as a WHERE  a.date BETWEEN '$setDate' AND '$setDate1' GROUP BY a.date";

        $result_setData = $db_link->query($sql_setDate_operator);
        // var_dump($result_setData_operator);


        while($result_setData_row =  $result_setData -> fetch_row()) {
            echo "<table class='table1'><tr><th>時間</th><th>結果OK</th><th>結果NG</th></tr>";
            echo "<tr><td>" . $result_setData_row[0] . "</td><td>" . $result_setData_row[1] . "筆OK</td><td class='td1'>" . $result_setData_row[2] . "筆NG</td></tr>";
            echo "</table><br>";
        }
        
        // 改寫
        // foreach($result_setData as $itme => $value) {
        //     echo "<table class='table1'><tr><th>時間</th><th>結果OK</th><th>結果NG</th></tr>";
        //     echo "<tr><td>" . $value["pTime"] . "</td><td>" . $value["SUM(result='OK')"] . "筆OK</td><td class='td1'>" . $value["SUM(result='NG')"] . "筆NG</td></tr>";
        //     echo "</table><br>";
        // }
    }
}
?>