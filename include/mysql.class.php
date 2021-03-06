<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: AlanJager
 * Release Date: 2016-7-4
 */
if (!defined('IN_HBDATA')) {
    die('Accident occured, please try again.');
}

/**
 * 数据库表通用操作
 * @name DbMysql
 * @version v1.0
 * @author AlanJager
 */
class DbMysql {
    private $dbhost;
    // 数据库主机
    private $dbuser;
    // 数据库用户名
    private $dbpass;
    // 数据库用户名密码
    private $dbname;
    // 数据库名
    private $hbdata_link;
    // 数据库连接标识
    private $prefix;
    // 数据库前缀
    private $charset;
    // 数据库编码，GBK,UTF8,gb2312
    private $sql;
    // sql执行语句
    private $result;
    // 执行query命令的结果资源标识
    private $error_msg;
    // 数据库错误提示

    /**
     * DbMysql constructor.
     * @param $dbhost
     * @param $dbuser
     * @param $dbpass
     * @param string $dbname
     * @param $prefix
     * @param string $charset
     * @return DbMysql
     */
    function DbMysql($dbhost, $dbuser, $dbpass, $dbname = '', $prefix, $charset = 'utf8')
    {
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->dbname = $dbname;
        $this->prefix = $prefix;
        $this->charset = strtolower(str_replace('-', '', $charset));
        $this->connect();
    }

    /**
     * connect to mysql
     * @return boolean
     */
    function connect()
    {

        if (!$this->hbdata_link = @mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname)) {
            $this->error('Can not connect to mysql server');
            return false;
        }

        if ($this->version() > '4.1') {
            if ($this->charset) {
                $this->query("SET character_set_connection=" . $this->charset . ", character_set_results=" . $this->charset .
                    ", character_set_client=binary");
            }
            if ($this->version() > '5.0.1') {
                $this->query("SET sql_mode=''");
            }
        }

        if (mysqli_select_db($this->hbdata_link, $this->dbname) === false) {
            $this->error("NO THIS DBNAME: " . $this->dbname);
            return false;
        }
        return true;
    }

    /**
     * get version info of the server
     * @return version
     */
    function version()
    {
        if (empty($this->version)) {
            $this->version = mysqli_get_server_info($this->hbdata_link);
        }
        return $this->version;
    }

    /**
     * execute sql
     * @param string $sql
     * @return array $query
     */
    function query($sql) {
        $this->sql = $sql;
        $query = mysqli_query($this->hbdata_link, $this->sql);
        $this->result = $query;
        return $query;
    }

    /**
     * return err message and stop the app
     * @param string $msg
     * @return string $msg
     */
    function error($msg = '')
    {
        $msg = $msg ? "hbdataPHP Error: $msg" : '<b>MySQL server error report</b><br>' . $this->error_msg;
        exit($msg);
    }

    /**
     * return last affected rows
     * @return int
     */
    function affected_rows()
    {
        return mysqli_affected_rows($this->hbdata_link);
    }

    /**
     * return a certain column start at a certain row
     * @param int $row
     * @param string $field
     * @return string
     */
    function result($row = 0)
    {
        return @ mysqli_result($this->result, $row);
    }

    /**
     * 实现一个mysql_result
     * @param $res
     * @param int $row
     * @param int $col
     * @return bool
     */
    function mysqli_result($res,$row=0,$col=0){
        $numrows = mysqli_num_rows($res);
        if ($numrows && $row <= ($numrows-1) && $row >=0){
            mysqli_data_seek($res,$row);
            $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
            if (isset($resrow[$col])){
                return $resrow[$col];
            }
        }
        return false;
    }

    /**
     * use the return value of query to count rows of the result set
     * @param $query
     * @return int
     */
    function num_rows($query)
    {
        return @ mysqli_num_rows($query);
    }

    /**
     * use the return value of query to count columns of the result set
     * @param $query
     * @return int
     */
    function num_fields($query)
    {
        return mysqli_num_fields($query);
    }


    /**
     * get last operated id
     * @return int
     */
    function insert_id()
    {
        return mysqli_insert_id($this->hbdata_link);
    }

    /**
     * get next increment id
     * @param $table
     * @return mixed
     */
    function auto_id($table)
    {
        return $this->get_one("SELECT auto_increment FROM information_schema.`TABLES` WHERE  TABLE_SCHEMA='" . $this->dbname . "' AND TABLE_NAME = '" . trim($this->table($table)) . "'");
    }

    /**
     * count
     * @param $sql
     * @param bool $limited
     * @return bool|string
     */
    function get_one($sql, $limited = false)
    {
        if ($limited == true)
            $sql = trim($sql . ' LIMIT 1');

        $res = $this->query($sql);
        if ($res !== false) {
            $row = mysqli_fetch_row($res);
            if ($row !== false)
                return $row[0];
            else
                return '';
        } else {
            return false;
        }
    }

    /**
     * return a table name with prefix
     * @param $str
     * @return string
     */
    function table($str)
    {
        return "`" . $this->prefix . $str . "`";
    }

    /**
     * make query result into a array
     * @param $query
     * @return array
     */
    function fetch_row($query)
    {
        return mysqli_fetch_row($query);
    }

    /**
     * make query result into a key => value array
     * @param $query
     * @return array
     */
    function fetch_assoc($query)
    {
        return mysqli_fetch_assoc($query);
    }

    /**
     * make query result into a key => value array and also a int value for the row the one record map
     * @param $query
     * @return array
     */
    function fetch_array($query)
    {
        return mysqli_fetch_array($query);
    }

    /**
     * close database connection
     * @return bool
     */
    function close()
    {
        return mysqli_close($this->hbdata_link);
    }

    /**
     * get all records
     * @return bool
     */
    function select_all($table)
    {
        return $this->query("SELECT * FROM " . $table);
    }

    /**
     * if table exists
     * @return bool
     */
    function table_exist($table)
    {
        if($this->num_rows($this->query("SHOW TABLES LIKE '" . trim($this->table($table), "`") . "'")) == 1)
        {
            return true;
        }
    }

    /**
     * if field exist in table
     * @param string $table
     * @param string $field
     * @return bool
     */
    function field_exist($table, $field)
    {
        $sql = "SHOW COLUMNS FROM " . $table;
        $query = $this->query($sql);
        while($row = mysqli_fetch_array($query, MYSQL_ASSOC))
            $array[] = $row['Field'];
        if (in_array($field, $array))
            return true;
    }

    /**
     * if duplicate value exists
     * @param $table
     * @param $field
     * @param $value
     * @param string $where
     * @return bool
     */
    function value_exist($table, $field, $value, $where = '')
    {
        $sql = "SELECT $field FROM `" . $this->table($table) . "` WHERE $field = '$value'" . $where;
        $number = $this->num_rows($this->query($sql));
        if ($number > 0)
            return true;
    }

    /**
     * use condition to get record
     * @param $table
     * @param string $columnName
     * @param string $condition
     * @param string $debug
     * @return array
     */
    function select($table, $columnName = "*", $condition = '', $debug = '')
    {
        $condition = $condition ? ' Where ' . $condition : NULL;
        if ($debug) {
            echo "SELECT $columnName FROM $table $condition";
        } else {
            $query = $this->query("SELECT $columnName FROM $table $condition");
            return $query;
        }
    }

    /**
     * delete value by condition
     * @param $table
     * @param $condition
     * @param string $url
     */
    function delete($table, $condition, $url = '')
    {
        if ($this->query("DELETE FROM $table WHERE $condition")) {
            if (!empty($url)) {
                $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['del_succes'], $url);
            }
        }
    }

    /**
     * escaping special characters
     * @param $string
     * @return string
     */
    function escape_string($string)
    {
        if (PHP_VERSION >= '4.3') {
            return mysqli_real_escape_string($this->hbdata_link, $string);
        } else {
            return mysqli_escape_string($this->hbdata_link, $string);
        }
    }

    /**
     * get all the query set into array
     * @param $table
     * @param string $order_by
     * @param string $where
     * @return array
     */
    function fetch_array_all($table, $order_by = '', $where = '')
    {
        $order_by = $order_by ? " ORDER BY " . $order_by : '';
        $where = $where ? " WHERE " . $where : '';
        $query = $this->query("SELECT * FROM ". $table . $where . $order_by);
        while ($row = $this->fetch_assoc($query)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 根据分类获取某一分类下的所有内容
     * @param $table
     * @param $category
     * @param string $order_by
     * @return mixed
     */
    function fetch_array_all_by_category($table, $category, $order_by = ''){
        $order_by = $order_by ? " ORDER BY " . $order_by : '';
        $query = $this->query("SELECT * FROM " . $table . " WHERE category='$category'"  . $order_by);
        while ($row = $this->fetch_assoc($query)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * offer the func for database import
     * @param $sql
     * @return bool
     */
    function fn_execute($sql)
    {
        $sqls = $this->fn_split($sql);
        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                if (trim($sql) != '')
                    $this->query($sql);
            }
        } else {
            $this->query($sqls);
        }
        return true;
    }

    /**
     * offer func for split query result
     * @param $sql
     * @return array
     */
    function fn_split($sql)
    {
        if ($this->version() > '4.1' && $this->sqlcharset)
            $sql = preg_replace("/TYPE=(InnoDB|MyISAM)( DEFAULT CHARSET=[^; ]+)?/", "TYPE=\\1 DEFAULT CHARSET=" . $this->sqlcharset, $sql);

        $sql = str_replace("\r", "\n", $sql);
        $ret = array ();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                    $ret[$num] .= $query;
            }
            $num++;
        }
        return ($ret);
    }
}
