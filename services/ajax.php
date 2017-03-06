<?php
require_once "../inc/db.php";
require_once "../inc/functions.php";
require_once "../inc/database.class.php";
$dbname = new database($db_config);
$g = new database($db_config);
$g1 = new database($db_config);
$mainArr = getAllticketMembers($g);
$clientOpenArr = clientOpenTickets($dbname);
$clientNonOpenArr = clientNonOpenTickets($dbname);
$counttotalTickets = gettotalTickets($dbname);
$counttotalOpenTickets = gettotalOpenTickets($dbname);
$counttotalClosedTickets = gettotalClosedTickets($dbname);
$timeNow = getCurrentTime();

$clientArr = array();
$arrayOpenCount = count($clientOpenArr);
$arraynonOpenCount = count($clientNonOpenArr);

if($arrayOpenCount  >$arraynonOpenCount) {
    $frstArray = $arrayOpenCount;
    $secondArray = $arraynonOpenCount;
    for ($i = 0; $i < $frstArray; $i++) {
    $org_id = $clientOpenArr[$i]['org_id'];
    $clientArr[$i]['org_id'] = $clientOpenArr[$i]['org_id'];
    $clientArr[$i]['totalticket'] = $clientOpenArr[$i]['totalticket'];
    $clientArr[$i]['name'] = $clientOpenArr[$i]['name'];
    for ($j=0; $j < $secondArray; $j++) { 
        if($org_id ==$clientNonOpenArr[$j]['org_id']){
            $clientArr[$i]['totalclosedticket'] = $clientNonOpenArr[$j]['totalclosedticket'];
            break;
        }else{
            $clientArr[$i]['totalclosedticket'] = 0;
        }
    }
}
	} else {
	    $frstArray = $arraynonOpenCount;
	    $secondArray = $arrayOpenCount;
	    for ($i = 0; $i < $secondArray; $i++) {
	    $org_id = $clientNonOpenArr[$i]['org_id'];
	    $clientArr[$i]['org_id'] = $clientNonOpenArr[$i]['org_id'];
	    $clientArr[$i]['totalclosedticket'] = $clientNonOpenArr[$i]['totalclosedticket'];
	    $clientArr[$i]['name'] = $clientNonOpenArr[$i]['name'];
	    for ($j=0; $j < $frstArray; $j++) { 
	        if($org_id ==$clientOpenArr[$j]['org_id']){
	            $clientArr[$i]['totalticket'] = $clientOpenArr[$j]['totalticket'];
	            break;
	        }else{
	            $clientArr[$i]['totalticket'] = 0;
	        }
	    }
	}
}

$returnData['members'] = $mainArr;
$returnData['clients'] = $clientArr;
$returnData['totalTickets'] = $counttotalTickets;
$returnData['openTickets'] = $counttotalOpenTickets;
$returnData['closedTickets'] = $counttotalClosedTickets;
$returnData['timeNow'] = $timeNow;

print_r(json_encode($returnData));

?>

