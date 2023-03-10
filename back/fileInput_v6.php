<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>檔案上傳</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-JavaScript-Templates/3.20.0/js/tmpl.min.js" integrity="sha512-yQJVqoTPFSC73MaslsQaVJ0zHku4Cby3NpQzweSYju+kduWspfF4HmJ3zAo1QGERfsoXdf45q54ph8XTjOlp8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        .table1 {
            border: 4px solid green;
            border-collapse: collapse;
        }

        .table1 th,
        .table1 td {
            border: 1.5px solid green;
            padding: 5px;
            text-align: left;
        }
    </style>
</head>

<body>
    <form action="" method="post" enctype="multipart/form-data">
        <p>查詢時間區間內結果</p>
        <br>
        <label for="uploadFile">上傳檔案</label>
        <br>
        <input type="file" name="uploadFile" accept=".txt" id="uploadFile">
        <br>
        <br>
        <label for="uploadDate">輸入起始日期</label>
        <br>
        <input type="date" name="uploadDate" id="uploadDate">
        <br>
        <br>
        <label for="uploadDate_end">輸入結束日期</label>
        <br>
        <input type="date" name="uploadDate_end" id="uploadDate_end">
        <br>
        <br>
        <input type="submit" value="送出">
        <button onclick="deleteText()">刪除畫面資料</button>
    </form>
    <br>
    <script>
        function deleteText() {
            let element = document.getElementsByClassName('table1');
            element.parentNode.removeChild(element);
        };
    </script>
</body>
</html>

<?php
header("Content-Type: text/html; charset=utf-8");
include("./fileInput_conn.php");
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
            // if($result_repeatData){
            //奇怪寫法
            if ($result_repeatData->num_rows == false) {
                $sql_insert = "INSERT INTO checktable (ptTime,workList1,workList2,workList3,prValue,nowResult) VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]','$newRowArr[5]')";
                //匯入資料到資料庫，$newRowArr[0]是個值，需要" "
                $db_link->query($sql_insert);
            } else {
                // echo $db_link->error;
                // echo "檢測到匯入檔案的資料重複" . "<br>";
                continue;
            }
            // }
        }
    } else {
        echo "<script>alert('請重新上傳資料');</script>";
        exit();
    }

    if (empty($_POST["uploadDate"]) || empty($_POST["uploadDate_end"])) {
        echo "<script>alert('請填寫日期');</script>";
        exit();
    }



    if (!empty($_POST["uploadDate"] && $_POST["uploadDate_end"])) {
        $setDate = date("Y-m-d",  strtotime($_POST["uploadDate"]));
        $setDate1 = date("Y-m-d",  strtotime($_POST["uploadDate_end"]));
        //輸入時間，轉換所需格式
        // echo $setDate."<br>";
        // echo $setDate1."<br>";

        $sql_setDate = "SELECT ptTime, nowResult FROM checktable WHERE ptTime BETWEEN '$setDate' AND '$setDate1'";
        // echo $sql_setDate."<br>";

        $result_setData = $db_link->query($sql_setDate);
        // echo gettype($result_setData);

        if ($result_setData->num_rows == 0) {
            echo "查無資料";
            exit();
        }

        while ($row_setDate = $result_setData->fetch_row()) {
            echo "<table class='table1'><tr><th>ptTime</th><th>nowResult</th></tr>";
            foreach ($result_setData as $item => $value) {
                echo "<tr><td>" . $value["ptTime"] . "</td><td>" . $value["nowResult"] . "</td></tr>";
            }
            echo "</table>";
            exit();
        }
    }
}
?>