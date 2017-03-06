<?php

class database {

    public $dbhost;
    public $dbuser;
    public $dbpwd;
    public $dbname;
    public $tableName = "";
    public $coloumns;
    public $last_insert_id;
    public $affected_rows;
    public $singleDataSet;
    public $rowcount;
    private $mysqli;
    private $currentSelectQueryResult;

    public function __construct($config) {
        $this->mysqli = new mysqli($config[0], $config[1], $config[2], $config[3]);
    }

    private function makeJSON() {
        $args = func_get_args();
        $num = func_num_args();
        $jsonArray = array();
        for ($i = 0; $i < $num; $i = $i + 2) {
            $jsonArray[$args[$i]] = $args[$i + 1];
        }
        $json = json_encode($jsonArray);
        return $json;
    }

    public function selectTable($tableName) {
        $this->tableName = $tableName;

        if (mysqli_connect_errno()) {
            throw new Exception("Connect failed: " . mysqli_connect_error());
            exit();
        }
        $query = "SHOW columns FROM " . $tableName;

        $result = $this->mysqli->query($query) or die("Session Expired. Please Refresh Page and Login Again.");
        $coloumnNames = array();
        $commaFieldName = "SELECT ";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $coloumnNames[] = $row;
                $commaFieldName = $commaFieldName . $row['Field'] . ", ";
            }
            $this->coloumns = $this->makeJSON("coloumn", $coloumnNames);
            return substr($commaFieldName, 0, -2) . " FROM " . $this->tableName;
        } else {
            throw new Exception("No coloumns in the selected table");
        }
    }

    // Can be used for any query, like joins, or other complex queries.
    public function selectQuery($selectQuery, $show = false) {
        if ($show == true) {
            echo $selectQuery;
        }
        $result = $this->mysqli->query($selectQuery) or die($this->mysqli->error . __LINE__);
        $this->rowcount = $result->num_rows;
        $this->currentSelectQueryResult = $result;
        return $result;
    }

    public function getNextRow() {
        if ($this->currentSelectQueryResult->num_rows > 0) {
            if ($row = $this->currentSelectQueryResult->fetch_assoc()) {
                $this->singleDataSet = json_decode(json_encode($row));
            }
        }
    }

    // to be used for getting values from a SINGLE TABLE ONLY
    public function selectQuerySingleRow($selectQuery, $show = false) {
        $result = $this->selectQuery($selectQuery, $show);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->singleDataSet = json_decode(json_encode($row));
        }
        return $result;
    }

    public function selectQueryInArray($selectQuery, $show = false) {
        $result = $this->selectQuery($selectQuery, $show);
        $my_array = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $my_array[] = $row;
            }
        }
        return $my_array;
    }

    public function insertQuery($query) {
        if ($this->mysqli->query($query) === TRUE) {
            $this->last_insert_id = $this->mysqli->insert_id;
            $this->affected_rows = $this->mysqli->affected_rows;
            return true;
        } else {
            return false;
        }
    }

    public function insertQueryJson($jsonData, $tablename = "") {
        if ($tablename == "") {
            if ($this->tableName != "") {
                $tablename = $this->tableName;
            } else {
                throw new Exception("NO TABLE SELECTED IN THE CLASS. EITHER SELECT A TABLE OR SET IT IN DATABASE CLASS");
            }
        }
        $query = $this->createInsertQueryFromJson($jsonData, $tablename);

        $result = $this->insertQuery($query);
        return $result;
    }

    public function updateQuery($query) {
        $this->insertQuery($query);
    }

    public function updateQueryJson($jsonData, $jsonCondition, $tablename = "") {
        if ($tablename == "") {
            if ($this->tableName != "") {
                $tablename = $this->tableName;
            } else {
                throw new Exception("NO TABLE SELECTED IN THE CLASS. EITHER SELECT A TABLE OR SET IT IN DATABASE CLASS");
            }
        }
        $query = $this->createUpdateQueryFromJson($jsonData, $jsonCondition, $tablename);

        if ($this->mysqli->query($query) === TRUE) {
            $this->last_insert_id = $this->mysqli->insert_id;
            return true;
        } else {
            return false;
        }
    }

    public function deleteQuery($query) {
        $val = $this->insertQuery($query);
        return $val;
    }

    public function close() {
        mysqli_close($this->mysqli);
    }

    public function createInsertQueryFromJson($jsonData, $tablename) {
        $json = json_decode($jsonData, true);
        $keystemp = array_keys($json);
        $keys = array();
        $discardedKeys = array();
        $jsonColoumns = json_decode($this->coloumns);
        for ($i = 0; $i < count($keystemp); $i++) {
            $flag = 0;
            for ($j = 0; $j < count($jsonColoumns->coloumn); $j++) {
                //echo $jsonColoumns->coloumn[$j]->Field . '=' . $keystemp[$i] . ",    ";
                if ($jsonColoumns->coloumn[$j]->Field == $keystemp[$i]) {
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 1) {
                $keys[] = $keystemp[$i];
            } else {
                $discardedKeys[] = $keystemp[$i];
            }
        }
        $insertQuery = "INSERT INTO " . $tablename . "(<fields>) VALUES (<values>)";
        $fields = "";
        $values = "";
        for ($k = 0; $k < count($keys); $k++) {
            $fields = $fields . "`" . $keys[$k] . "`,";
            $values = $values . "'" . mysqli_real_escape_string($this->mysqli, str_replace('<br/>', chr(10), $json[$keys[$k]])) . "',";
        }
        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);
        $insertQuery = str_replace("<fields>", $fields, $insertQuery);
        $insertQuery = str_replace("<values>", $values, $insertQuery);
        return $insertQuery;
    }

    public function realEscapeString($word) {
        return $this->mysqli->real_escape_string($word);
    }

    public function createUpdateQueryFromJson($jsonData, $jsonUpdateCondition, $tablename = "", $logical = "AND") {
        if ($tablename == "") {
            if ($this->tableName != "") {
                $tablename = $this->tableName;
                $this->selectTable($tablename);
            } else {
                throw new Exception("NO TABLE SELECTED IN THE CLASS. EITHER SELECT A TABLE OR SET IT IN DATABASE CLASS");
            }
        }
        $json = json_decode($jsonData, true);
        $jsonUpdate = json_decode($jsonUpdateCondition, true);

        $keystemp = array_keys($json);
        $keysUpdate = array_keys($jsonUpdate);
        $keys = array();
        $discardedKeys = array();
        $jsonColoumns = json_decode($this->coloumns);
        for ($i = 0; $i < count($keystemp); $i++) {
            $flag = 0;
            for ($j = 0; $j < count($jsonColoumns->coloumn); $j++) {
                //echo $jsonColoumns->coloumn[$j]->Field . '=' . $keystemp[$i] . ",    ";
                if ($jsonColoumns->coloumn[$j]->Field == $keystemp[$i]) {
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 1) {
                $keys[] = $keystemp[$i];
            } else {
                $discardedKeys[] = $keystemp[$i];
            }
        }
        $updateQuery = "UPDATE " . $tablename . " SET <fields-value-pair> WHERE <condition>";
        $fieldsvaluepair = "";
        for ($k = 0; $k < count($keys); $k++) {
            $fieldsvaluepair = $fieldsvaluepair . "`" . $keys[$k] . "`=" . "'" . mysqli_real_escape_string($this->mysqli, str_replace('<br/>', chr(10), $json[$keys[$k]])) . "', ";
        }
        $fieldsvaluepair = substr($fieldsvaluepair, 0, -2);
        $updateQuery = str_replace("<fields-value-pair>", $fieldsvaluepair, $updateQuery);
        $where = "";
        for ($l = 0; $l < count($keysUpdate); $l++) {
            $where = $where . "`" . $keysUpdate[$l] . "`=" . "'" . mysqli_real_escape_string($this->mysqli, str_replace('<br/>', chr(10), $jsonUpdate[$keysUpdate[$l]])) . "' " . $logical . " ";
        }
        $where = substr($where, 0, -4);
        $updateQuery = str_replace("<condition>", $where, $updateQuery);
        //echo $updateQuery;
        return $updateQuery;
    }
}
