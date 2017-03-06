<?php
error_reporting(0);
date_default_timezone_set('Asia/Kolkata');
function getAllticketMembers($g) {
    $mainArr = array();
    $query = "SELECT count(`ost_ticket`.`ticket_id`) as ticketswithstaff,`ost_staff`.`firstname`,`ost_staff`.`lastname` FROM `ost_ticket_status`,`ost_staff`,`ost_ticket` WHERE `ost_staff`.`staff_id`=`ost_ticket`.`staff_id` and `ost_ticket`.`status_id`=`ost_ticket_status`.`id`  AND  `ost_ticket_status`.`state`='open' GROUP BY `ost_staff`.`staff_id` ORDER BY ticketswithstaff DESC, `ost_staff`.`firstname` ASC , `ost_staff`.`lastname` ASC";
    $result = $g->selectQuerySingleRow($query);
    if ($g->rowcount > 0) {
        for ($i = 0; $i < $g->rowcount; $i++) {
            $mainArr[$i]['totaltickets'] = $g->singleDataSet->ticketswithstaff;
            $mainArr[$i]['firstname'] = $g->singleDataSet->firstname;
            $mainArr[$i]['lastname'] = $g->singleDataSet->lastname;
            $g->getNextRow();
        }
    }
    return $mainArr;
}

function clientOpenTickets($g) {
    $openArr = array();
    $query = 'SELECT count(`ost_ticket__cdata`.`ticket_id`) as totalticket,`ost_user`.`org_id`,`ost_organization`.`name` FROM `ost_ticket__cdata`,`ost_ticket`,`ost_user`,`ost_organization`,`ost_ticket_status` WHERE `ost_ticket__cdata`.`ticket_id`=`ost_ticket`.`ticket_id` AND `ost_user`.`id`=`ost_ticket`.`user_id` AND `ost_organization`.`id`=`ost_user`.`org_id` AND `ost_ticket`.`status_id`=`ost_ticket_status`.`id` AND  `ost_ticket_status`.`state`="open" GROUP BY `ost_user`.`org_id` ORDER BY `ost_ticket__cdata`.`ticket_id` ASC';
    $result = $g->selectQuerySingleRow($query);
    if ($g->rowcount > 0) {
        for ($i = 0; $i < $g->rowcount; $i++) {
            $openArr[$i]['org_id'] = $g->singleDataSet->org_id;
            $openArr[$i]['totalticket'] = $g->singleDataSet->totalticket;
            $openArr[$i]['name'] = $g->singleDataSet->name;
            $g->getNextRow();
        }
    }
    return $openArr;
}

function clientNonOpenTickets($g) {
    $nonopenArr = array();
    $query = 'SELECT count(`ost_ticket__cdata`.`ticket_id`) as totalticket,`ost_user`.`org_id`,`ost_organization`.`name` FROM `ost_ticket__cdata`,`ost_ticket`,`ost_user`,`ost_organization`,`ost_ticket_status` WHERE `ost_ticket__cdata`.`ticket_id`=`ost_ticket`.`ticket_id` AND `ost_user`.`id`=`ost_ticket`.`user_id` AND `ost_organization`.`id`=`ost_user`.`org_id` AND `ost_ticket`.`status_id`=`ost_ticket_status`.`id` AND  `ost_ticket_status`.`state`<>"open" GROUP BY `ost_user`.`org_id` ORDER BY `ost_ticket__cdata`.`ticket_id` ASC';
    $result = $g->selectQuerySingleRow($query);
    if ($g->rowcount > 0) {
        for ($i = 0; $i < $g->rowcount; $i++) {
            $nonopenArr[$i]['org_id'] = $g->singleDataSet->org_id;
            $nonopenArr[$i]['totalclosedticket'] = $g->singleDataSet->totalticket;
            $nonopenArr[$i]['name'] = $g->singleDataSet->name;
            $g->getNextRow();
        }
    }
    return $nonopenArr;
}

function gettotalTickets($g){
    $query = "SELECT count(`ticket_id`) AS `total` FROM `ost_ticket` WHERE 1";
    $result = $g->selectQuerySingleRow($query);
    if ($g->rowcount > 0) {
        return $g->singleDataSet->total;
    } else {
        return 0;
    }
}

function gettotalOpenTickets($g){
    $query = 'SELECT count(`ost_ticket`.`ticket_id`) as totalticket FROM `ost_ticket`,`ost_ticket_status` WHERE `ost_ticket`.`status_id`=`ost_ticket_status`.`id` AND  `ost_ticket_status`.`state`="open"';
    $result = $g->selectQuerySingleRow($query);
    if ($g->rowcount > 0) {
        return $g->singleDataSet->totalticket;
    } else {
        return 0;
    }
}

function gettotalClosedTickets($g){
    $query = 'SELECT count(`ost_ticket`.`ticket_id`) as totalticket FROM `ost_ticket`,`ost_ticket_status` WHERE `ost_ticket`.`status_id`=`ost_ticket_status`.`id` AND  `ost_ticket_status`.`state`<>"open"';
    $result = $g->selectQuerySingleRow($query);
    if ($g->rowcount > 0) {
        return $g->singleDataSet->totalticket;
    } else {
        return 0;
    }
}

function getCurrentTime() {
    return date('h:i A');
}
