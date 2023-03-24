<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>檔案上傳</title>
</head>

<body>
        <form action="" method="post" enctype="multipart/form-data">
        <label for="upload">檔案匯入檢核</label>
        <br>
        <br>
        <input type="file" name="uploadFile" accept=".txt" id="upload">
        <br>
        <br>
        <input type="submit" value="上傳檔案">
        </form>
</body>
</html>

<?php
    if (isset($_FILES["uploadFile"])) {
        if ($_FILES["uploadFile"]["error"] > 0) {
            echo "錯誤代碼:" . $_FILES["uploadFile"]["error"] . "<br>";
        } else {

            echo "檔案名稱:" . $_FILES["uploadFile"]["name"] . "<br>";
            echo "檔案類型:" . $_FILES["uploadFile"]["type"] . "<br>";
            echo "檔案大小:" . $_FILES["uploadFile"]["size"] . "<br>";
            echo "暫存名稱:" . $_FILES["uploadFile"]["tmp_name"] . "<br>";

            $fileTmpDir = dirname(dirname(__FILE__)) . "\project_file_tmp\\";
            // 儲存檔案路徑
            echo "檔案儲存路徑:" . $fileTmpDir . "<br>";
            // echo gettype($fileTmpDir)."<br>";
            // 取檔案類型

            $allowedType = array("txt");
            // 允許檔案副檔名類型
            $uploadFileName = $_FILES["uploadFile"]["name"];
            $uploadFileType = strtolower(pathinfo($uploadFileName,PATHINFO_EXTENSION));
            // 小寫檔案副檔名
            
            if(in_array($uploadFileType,$allowedType)){
                // 檔案副檔名等於定義允許的副檔名
                echo "上傳檔案類型符合格式"."<br>";
                if (file_exists($fileTmpDir . $_FILES["uploadFile"]["name"])) {
                    echo "檔案已存在，請勿重複上傳相同檔案" . "<br>";
                    exit;
                    // 防止資料再度匯入資料庫
                } else {
                    move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $fileTmpDir . $_FILES["uploadFile"]["name"]);
                    echo "檔案上傳成功"."<br>";
                    // 檔案上傳到指定路徑
                }
            }else{
                echo "上傳檔案類型不符合格式";
                exit;
                //不符合格式離開程式
            }

            header("Content-Type: text/html; charset=utf-8");

            // $startTime = microtime(true);
            // 計算起始時間
    
            include("inputFile_conn.php");
            // 連線檔匯入
            echo "<br>";

            $fileName = $fileTmpDir.$_FILES["uploadFile"]["name"];
            // 讀取新路徑檔案
            $newFileName = $fileTmpDir.pathinfo($_FILES["uploadFile"]["name"],PATHINFO_FILENAME)."(v5)".".txt";
            // 新路徑檔案檔名(不包含副檔名)
            // echo $newFileName;

            $oldFile = fopen($fileName, "r");
            $newFile = fopen($newFileName, "w");
            // 開啟檔案
            
            fread($oldFile, filesize($fileName));
            $title = array(0 => "日期", 1 => "工件1", 2 => "工件2", 3 => "工件3", 4 => "壓力值", 5 => "結果\n");
            $result = implode("\t", $title);
            fwrite($newFile, $result);
            fclose($oldFile);
            fclose($newFile);
            // 檔案關閉
            
            $oldFile = fopen($fileName, "r");
            $newFile = fopen($newFileName, "a");
            // 開啟檔案，續寫內容
            
            $calculate_value = 0;
            $num = 1;
            //計數器設定

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
                    $newRowArr[5] = trim("YES");
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

                $sql_insert = "INSERT INTO checktable (ptTime,workList1,workList2,workList3,prValue,nowResult) VALUES('$newRowArr[0]','$newRowArr[1]','$newRowArr[2]','$newRowArr[3]','$newRowArr[4]','$newRowArr[5]')";
                //$newRowArr[0]是個值，需要" "
                
                $db_link->query($sql_insert);
            
                // if(mysqli_query($db_link,$sql_insert)){
                //     echo "匯入資料庫成功";
                // }else{
                //     echo "匯入資料庫失敗";
                // }
                // 檢核匯入資料
            
                $result = implode("\t", $newRowArr);
                // 陣列轉字串
            
                fwrite($newFile, $result);

                // $endTime = microtime(true);
                // $totalTime = $endTime - $startTime;
                // echo "程式執行時間:".$totalTime."秒";

                $calculate_value += $num;
                //計數器
            }
            echo "匯入".$calculate_value."列資料到資料庫";            
        }
    }
?>