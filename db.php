<?php

class DB {

    private $_report;
    private $_connection;
    private $_callHiddenMethods;
    private $_allowedMethods;

    public static $debug = false;

    public function __construct() {
        $this->_allowedMethods = array('getTableFieldsInfo');
        $this->_callHiddenMethods = false;

    }

    public function _allowCallingHiddenMethods() {
        $this->_callHiddenMethods = true;
    }

    public function setDBConnection(&$connection) {
        $this->_connection = $connection;
    }

    public function unsetDBConnection() {
        mysql_close($this->_connection);
        unset($this->_connection);
    }

    public function runQuery($query) {

        if (static::$debug == true)
            var_dump($query);

        if (isset($this->_connection))
            $query_result = mysql_query($query, $this->_connection);
        else
            $query_result = mysql_query($query);

        if (!$query_result) {
            var_dump($query);
            var_dump(mysql_error());
            echo $this->trace_message();
            exit();
        }

        return $query_result;
    }

    /**
     * Runs the resource that is retured by DB::runQuery
     *
     * @param resource $resource
     * @return array
     */
    public function fetchResult($resource) {
        $n = array();
        if (!is_resource($resource)) {
            return $n;
        }

        while ($row = mysql_fetch_assoc($resource)) {
            $n[] = $row;
        }

        return $n;
    }

    protected function trace_message() {
        $trace = debug_backtrace();
        foreach ($trace as $key => $item)
            $string .= '#' . ($key + 1) . ' Function <b>' . $item['function'] . '</b> in <b>"' . $item['file'] . '"</b> at line <b>' . $item['line'] . '</b>. <br />';

        return $string;
    }

    /**
     * Inserts one item into a table
     *
     * @param string $table The name of the table
     * @param array $items Array of fields to insert (if some is not specified the default value is assumed)
     * @return int The new ID of the item
     */
    public function insertTableRow($table, $items) {
        $this->fixQuotes($items);
        $fields = $this->getTableFieldsInfo($table);

        $query = "INSERT INTO `$table` (";
        foreach ($fields as $info)
            $query .= '`' . $info->name . '`, ';

        $query = trim($query, ', ');
        $query .= ") VALUES(";

        foreach ($fields as $info) {

            if (isset($items[$info->name])) {
                if (strtoupper($items[$info->name]) == "NULL")
                    $val = "NULL, ";
                else
                    $val = "'" . $items[$info->name] . "', ";
            } else
                $val = "'" . $items[$info->def] . "', ";
            //

            $query .= $val;
        }

        $query = trim($query, ', ');
        $query .= ")";
        //println($query);
        $r = $this->runQuery($query);
        //print $query.'<br />';
        return mysql_insert_id();

    }

    /**
     * Updates a table
     *
     * @param string $table The name of the table
     * @param int $id The ID of the row to update
     * @param array $items Array of new values ($items['columnName']['value'])
     * @return TRUE on success or FALSE on error
     */
    public function updateTableRow($table, $id, $items) {
        $this->fixQuotes($items);
        $fields = $this->getTableFieldsInfo($table);

        $query = "UPDATE `$table` SET ";
        foreach ($fields as $info) {
            if (isset($items[$info->name])) {
                if ($items[$info->name] == 'NULL')
                    $query .= "`" . $info->name . "` = NULL, ";
                else
                    $query .= "`" . $info->name . "` = '" . $items[$info->name] . "', ";
            }
        }
        $query = trim($query, ', ');
        $query .= " WHERE ID='$id'";

        $r = $this->runQuery($query);

        return $r;
    }

    /**
     * Deletes an element from a table
     *
     * @param string $table The name of the table
     * @param int $id The ID of the row to update
     * @return TRUE on success or FALSE on error
     */
    public function deleteTableRow($table, $id) {
        $query = "DELETE FROM `$table` WHERE ID = '$id'";

        $r = $this->runQuery($query);

        return $r;
    }

    /**
     * Deletes rows from a table where a list of conditions are verified
     *
     * @example $DB->deleteWhere('object', array("pluginID"=>">10", "type"=>"='O'"));
     *
     * @param string $table
     * @param array $conditionsArray The array of conditions for the WHERE clause
     * @param string $operator [optional] 'AND' or 'OR'
     *
     * @return int The number of rows deleted
     */
    public function deleteWhere($table, $conditionsArray, $operator = 'AND') {
        $rows = 0;

        //build WHERE clause
        if (is_array($conditionsArray)) {

            foreach ($conditionsArray as $column => $value)
                $condition[] = $column . $value;

            $condition = implode(' ' . $operator . ' ', $condition);

            $query = "DELETE FROM `$table` WHERE $condition";

            $r = $this->runQuery($query);
            $rows = mysql_affected_rows();
        }

        return $rows;
    }

    /**
     * Deletes foreign keys from tables
     *
     * @param string $table Table name
     * @param mixed $column The name of the foreign key
     * @param mixed $val Value of the foreign_key to delete
     * @example $DB->deleteForeignKey('product_option', 'product_id', 22);
     *
     * @return int
     */
    public function deleteForeignKey($table, $column, $val) {
        $deletedRows = 0;

        $query = "DELETE FROM `$table` WHERE `$column` = '$val'";
        $r = $this->runQuery($query);
        /*
         foreach ($keysArray as $table=>$column) {
         $query = "DELETE FROM `$table` WHERE `$column` = '$val'";
         $trace = debug_backtrace();
         $message = 'Function <b>'.$trace[0]['function'].'</b> in <b>"'.$trace[0]['file'].'"</b> at line <b>'.$trace[0]['line'].'</b>.';

         $r = mysql_query($query) or die(mysql_error().'<br />'.$message);
         $deletedRows += mysql_affected_rows();
         }
         */

        return $r;
    }

    /**
     * Select foreign keys from tables
     *
     * @param array $keysArray Array of (table=>column_name) links to be deleted
     * @param mixed $val Value of the foreign_key to delete
     * @example $DB->selectForeignKey(array('product_option' => 'product_id', 'product_junction' => 'productID'), 22);
     *
     * @return array The resulting row of the query
     */
    public function selectForeignKey($table, $column, $val, $orderBy = null) {
        $rows = null;

        $query = "SELECT * FROM `$table` WHERE `$column` = '$val'";
        if (isset($orderBy))
            $query .= " ORDER BY $orderBy";

        $r = $this->runQuery($query);

        if ($r) {
            while ($row = mysql_fetch_assoc($r)) {
                $this->removeSlashes($row);
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Returns a row from a table for a specific ID
     *
     * @param string $table The name of the table
     * @param int $id The primary ID
     *
     * @return array The matching row
     */
    public function selectTableRow($table, $id) {
        $row = null;

        $query = "SELECT * FROM `$table` WHERE ID = '$id'";
        $r = $this->runQuery($query);
        if ($r) {
            $row = mysql_fetch_assoc($r);
            $this->removeSlashes($row);
        }

        return $row;
    }

    /**
     * Selects rows from a table where a list of conditions are verified
     *
     * @example $DB->selectWhere('object', array("pluginID"=>">10", "type"=>"='O'"));
     *
     * @param string $table The name of the table
     * @param array $conditionsArray [optional] The array of conditions for the WHERE clause. If NULL get all rows and $operator is ignored
     * @param string $operator [optional] 'AND' or 'OR'
     *
     * @return array The array of rows returned. REMEMBER: if only 1 row is returned you acces it as $data[0]
     */
    public function selectWhere($table, $conditionsArray = null, $operator = 'AND', $orderBy = 'ID') {
        return $this->selectFieldsWhere($table, null, $conditionsArray, $operator, $orderBy);
    }

    public function selectFieldsWhere($table, $fieldsArray = null, $conditionsArray = null, $operator = 'AND', $orderBy = 'ID') {
        $rows = 0;

        //build WHERE clause
        if (is_array($conditionsArray)) {

            foreach ($conditionsArray as $column => $value)
                $condition[] = $column . $value;

            $condition = implode(' ' . $operator . ' ', $condition);

        } else
            $condition = 1;

        if (is_array($fieldsArray)) {
            foreach ($fieldsArray as $value)
                $fields[] = $value;
            $fields = implode(', ', $fields);
        } else
            $fields = '*';

        $query = "SELECT $fields FROM `$table` WHERE $condition ORDER BY $table.$orderBy";
        $r = $this->runQuery($query);

        if ($r) {
            while ($row = mysql_fetch_assoc($r)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function updateForeignKey($table, $column, $value, $items) {
        $this->fixQuotes($items);
        $fields = $this->getTableFieldsInfo($table);

        $query = "UPDATE `$table` SET ";
        foreach ($fields as $info) {
            if (isset($items[$info->name])) {
                if ($items[$info->name] == 'NULL')
                    $query .= "`" . $info->name . "` = NULL, ";
                else
                    $query .= "`" . $info->name . "` = '" . $items[$info->name] . "', ";
            }
        }
        $query = trim($query, ', ');
        $query .= " WHERE `$column` = '$value'";
        $r = $this->runQuery($query);
        //print $query;

        return $r;
    }

    /**
     * Empty all entries in selected table
     */
    public function emptyTable($table) {
        $query = "TRUNCATE TABLE `$table`";
        $r = $this->runQuery($query);
        return $r;
    }

    /**
     +   * Returns the value of a column in a table for the matching ID
     +   * @param object $table
     +   * @param object $id
     +   * @param object $column
     +   * @return
     +   */
    public function selectColumn($table, $id, $column) {
        $query = "SELECT `$column` FROM `$table` WHERE id = '$id'";
        $r = $this->runQuery($query);

        if ($r) {
            $row = mysql_fetch_assoc($r);
        }

        return $row[$column];
    }

    //----------------------------------- PROTECTED -----------------------------------//
    /**
     * Returns an object containing all the fields info of a table
     *
     * @param strin $table The name of the table
     * @return object The fields info as an array of objects like:
     *    blob:         $fieldInfo->blob
     *    max_length:   $fieldInfo->max_length
     *    multiple_key: $fieldInfo->multiple_key
     *    name:         $fieldInfo->name
     *    not_null:     $fieldInfo->not_null
     *    numeric:      $fieldInfo->numeric
     *    primary_key:  $fieldInfo->primary_key
     *    table:        $fieldInfo->table
     *    type:         $fieldInfo->type
     *    default:      $fieldInfo->def
     *    unique_key:   $fieldInfo->unique_key
     *    unsigned:     $fieldInfo->unsigned
     *    zerofill:     $fieldInfo->zerofill
     */
    protected function getTableFieldsInfo($table) {
        $query = "SELECT * FROM `$table`";
        $result = $this->runQuery($query);
        while ($meta = mysql_fetch_field($result)) {
            if ($meta)
                $fieldInfo[] = $meta;
        }
        mysql_free_result($result);
        return $fieldInfo;
    }

    /**
     * Fixes single quotes in a string (or array of strings) to store it into the DB without error
     * @param mixed $str The string or array of strings to fix (by reference)
     */
    protected function fixQuotes(&$str) {
        //need to do this because live and localhost behave differently
        //echo $str;

        if (is_array($str)) {
            foreach ($str as $key => $text) {
                if (get_magic_quotes_gpc()) {
                    $str[$key] = stripslashes($text);
                }
                $str[$key] = mysql_real_escape_string($text);
            }
        } else {

            if (get_magic_quotes_gpc()) {
                $str = stripslashes($str);
            }

            $str = mysql_real_escape_string($str);
        }

    }

    /**
     * Remove slashes previously added in fixQuotes
     * @param mixed $str The string or array of string to remove slashes from
     * @sdocreturn
     */
    protected function removeSlashes(&$str) {
        if (is_array($str)) {
            foreach ($str as $i => $s) {
                $str[$i] = stripslashes($s);
            }
        } else {
            $str = stripslashes($str);
        }
    }

    /**
     * Returns everything from a table
     * @param object $table
     * @return
     */
    public function selectAll($table, $order_by = null) {
        $query = "SELECT * FROM `$table`" . (isset($order_by) ? " ORDER BY $order_by" : "");
        $r = mysql_query($query) or die(mysql_error() . '<br />' . trace_message(debug_backtrace()));

        if ($r) {
            while ($row = mysql_fetch_assoc($r)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function getConfigDescription($attribute, $value) {
        $data = $this->selectWhere('config', array(
            'attribute' => "='$attribute'",
            'value' => "='$value'"
        ));
        if (count($data) > 0)
            return $data[0]['description'];
        else {
            return false;
        }
    }

    public function getConfigID($attribute, $value) {
        $data = $this->selectWhere('config', array(
            'attribute' => "='$attribute'",
            'value' => "='$value'"
        ));
        if (count($data) > 0)
            return $data[0]['ID'];
        else {
            return false;
        }
    }

    public function getConfigValue($attribute, $description) {
        $data = $this->selectWhere('config', array(
            'attribute' => "='$attribute'",
            'description' => "='$description'"
        ));
        if (count($data) > 0)
            return $data[0]['value'];
        else {
            return false;
        }
    }

    public function getConfigAttribute($value, $description) {
        $data = $this->selectWhere('config', array(
            'description' => "='$description'",
            'value' => "='$value'"
        ));
        if (count($data) > 0)
            return $data[0]['attribute'];
        else {
            return false;
        }
    }

    public function tableExists($table) {
        $query = "show tables like '$table'";
        $result = mysql_query($query);
        return (mysql_num_rows($result) > 0);
    }

    public function updateConfig($attribute, $value, $description) {

        $exists = $this->getConfigDescription($attribute, $value);
        if ($exists !== false)
            $r = $this->updateTableRow('config', $this->getConfigID($attribute, $value), array('description' => $description));
        else {
            $r = $this->insertTableRow('config', array(
                'attribute' => $attribute,
                'value' => $value,
                'description' => $description
            ));
        }
        return $r;
    }

    /**
     * Update Where function.
     *
     * @param Table Name
     * @param Conditions for where clause
     * @param Items to update
     */
    public function updateWhere($table, $conditionsArray, $itemsArray, $operator = 'AND') {

        //build WHERE clause
        if (is_array($conditionsArray)) {

            foreach ($conditionsArray as $column => $value)
                $condition[] = $column . $value;

            $condition = implode(' ' . $operator . ' ', $condition);

        } else
            $condition = 1;

        if (is_array($itemsArray)) {

            //This should be redone eventually. getTableFieldsInfo is inefficent and looping through the array twice is pointless.
            $tableInfo = $this->getTableFieldsInfo($table);
            foreach ($tableInfo as $object) {
                $fields[] = $object->name;
            }

            foreach ($itemsArray as $key => $item) {
                if (in_array($key, $fields)) {
                    $updateArr[] = "$key='$item'";
                }
            }

            $query = "UPDATE `$table` SET " . implode(', ', $updateArr) . " WHERE $condition";

            $r = $this->runQuery($query);
            //return mysql_num_rows($r) > 0;
            return $r;
        }
        return false;

    }


    public function getEnumValues($table, $column) {
        $values = null;

        $sql = "SHOW COLUMNS FROM $table LIKE '$column'";

        if ($result = mysql_query($sql)) {// If the query's successful
            $enum = mysql_fetch_object($result);
            preg_match_all('~\'([^\']*)\'~', $enum->Type, $values);
        } else {
            die("Unable to fetch enum values: " . mysql_error());
        }

        return $values[1];
    }

    /**
     * __call magic method. Invoked to allow access to private or protected methods. Selectively.
     */
    public function __call($name, $args) {

        if ($this->_callHiddenMethods && in_array($name, $this->_allowedMethods)) {
            switch (count($args)) {
                case 0 :
                    return $this->$name();
                case 1 :
                    return $this->$name($args[0]);
                case 2 :
                    return $this->$name($args[0], $args[1]);
                case 3 :
                    return $this->$name($args[0] . $args[1], $args[2]);
                case 4 :
                    return $this->$name($args[0] . $args[1], $args[2], $args[3]);
                case 5 :
                    return $this->$name($args[0] . $args[1], $args[2], $args[3], $args[4]);
                default :
                    return call_user_func_array(array(
                        &$this,
                        $name
                    ), $args);
            }
        }
    }

}

