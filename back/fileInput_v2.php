<?php
ini_set("default_charset","utf-8");
$fileName = "y_2023(3).txt";
$oldFile = fopen($fileName, "r");
$newFile = fopen("y_2023(v2).txt","w"); 

if($fileName){
    //$contents = fread($oldFile, filesize($fileName));
   
      
        while($row = fgets($oldFile)){
          $row = str_replace('"','', $row);
          $rowArr = explode("\t",$row);
          /*
          $ColArr = explode(" ", $rowArr[2]);
          if ($ColArr[0] == "下午")
          {
            $ColArr[1] = date("H:i:s", strtotime($ColArr[1]." +12hour"));
          }
          $newRow[0] = $rowArr[1]." ".$ColArr[1];
          $newRow[1] = $rowArr[3];
          $newRow[2] = $rowArr[4];
          $newRow[3] = $rowArr[5];
          $newRow[4] = $rowArr[6];
          $newRow[5] = $rowArr[7];
          $newRow[6] = $rowArr[8];
          $newRow[7] = $rowArr[9];
          var_dump($newRow);
          */
          
          foreach($rowArr as $cols)
          {
            //$cols = str_replace('"','', $cols);
            echo trim($cols)."\n";
          }
          break;
          //var_dump($rowArr);
          //if ($ColArr[0] == "下午") break;
        }

// $txt_file = fopen('abc.txt','r');
// $a = 1;
// while ($line = fgets($txt_file)) {
//  echo($a." ".$line)."<br>";
//  $a++;
// }
// fclose($txt_file);


}else{
    echo "檔案不存在";
}

?>