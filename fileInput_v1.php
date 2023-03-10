<?php
header("Content-Type: text/html; charset=utf-8");
$filename = "y_2023(3).txt";
$myfile = fopen($filename, "r");
$newfile = fopen("y_2023(v1).txt","w"); 


if ($filename) {
    $contents = fread($myfile, filesize($filename));
    $charTab_row = explode("\n", $contents);
    //字串拆行，轉陣列


    foreach ($charTab_row as $item => $value) {
        $charTab_row[$item] = explode("\t", $value);
        $charTab_row[$item][0] = "";
        $add = "";
        $add =  $charTab_row[$item][1].$charTab_row[$item][2];

        $add_needle= "\"\"";
        $add_replace = "";
        $add_result = str_replace($add_needle,$add_replace,$add_result);
        //中間2個引號""消除
        $add_result2 = str_replace(" ","",$add_result1);
        //中間空白刪除
        
        // $add_result3 = preg_replace("","\t",$charTab_row);

        $charTab_row[$item][0] = $add_result2;
        unset($charTab_row[$item][1]);
        //刪除第一行
        unset($charTab_row[$item][2]);
        //刪除第二行
    }


    foreach($charTab_row as $item){ 
        $charTab_str = implode("\t",$item);
        //陣列轉字串
        echo $charTab_str;
        fwrite($newfile,$charTab_str);
    }


} else {
    echo "檔案不存在";
}