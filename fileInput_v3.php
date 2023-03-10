<?php
header("Content-Type: text/html; charset=utf-8");
$fileName = "y_2023(3).txt";
$oldFile = fopen($fileName,"r");
$newFile = fopen("y_2023(v3).txt","w");


while($row = fgets($oldFile))
{
    $row = str_replace('"','',$row);
    $rowArr = explode("\t",$row);
    //自字串轉陣列

    $colArr = explode(" ",$rowArr[2]);
    //切割出獨立陣列，但是針對這字串而不是二維陣列
    // var_dump($colArr);

    if(($colArr[0] == "下午")){
        $colArr[1] = date("H:i:s", strtotime(($colArr[1])."+12hour"));
        //date()函數
        //在php.ini timezone設定後會顯示現在時區時間
        //要取得檔案時間，需將陣列裡字串轉成時間
    }

    $newRowArr[0] = trim($rowArr[1]." ".$colArr[1]);
    $newRowArr[1] = trim($rowArr[3]);
    $newRowArr[2] = trim($rowArr[4]);
    $newRowArr[3] = trim($rowArr[5]);
    $newRowArr[4] = trim($rowArr[6]);
    $newRowArr[5] = trim($rowArr[7]);
    $newRowArr[6] = trim($rowArr[8]);
    $newRowArr[7] = trim($rowArr[9]);
    $newRowArr[8] = trim($rowArr[10]);
    
    print_r($newRowArr);

    
    $result = implode("\t",$newRowArr);
    // 陣列轉字串
    fwrite($newFile,$result);

}

?>
