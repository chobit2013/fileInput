<?php
$dbDetails = array(
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'hota888',
    'db' => 'class',
);

$table = "checktable_errlog";

$primaryKey = "workList3";

$columns = array(
    array("db" => "ptTime", "dt" => 0),
    array("db" => "workList1", "dt" => 1),
    array("db" => "workList2", "dt" => 2),
    array("db" => "workList3", "dt" => 3),
    array("db" => "prValue", "dt" => 4),
);

require 'ssp.class.php';

echo json_encode(
    SSP::simple($_GET,$dbDetails,$table,$primaryKey,$columns)
);

?>