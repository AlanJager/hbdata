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
    private $pconnect;
    // 持久链接,1为开启,0为关闭
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
     * @param int $pconnect
     * @return boolean
     */
    function DbMysql($dbhost, $dbuser, $dbpass, $dbname = '', $prefix, $charset = 'utf8', $pconnect = 0)
    {
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->dbname = $dbname;
        $this->prefix = $prefix;
        $this->charset = strtolower(str_replace('-', '', $charset));
        $this->pconnect = $pconnect;
        $this->connect();
    }

    /**
     * connect to mysql
     * @return boolean
     */
    function connect()
    {
        if ($this->pconnect) {
            if (!$this->hbdata_link = @mysql_pconnect($this->dbhost, $this->dbuser, $this->dbpass)) {
                $this->error('Can not pconnect to mysql server');
                return false;
            }
        } else {
            if (!$this->hbdata_link = @mysql_connect($this->dbhost, $this->dbuser, $this->dbpass, true)) {
                $this->error('Can not connect to mysql server');
                return false;
            }
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

        if (mysql_select_db($this->dbname, $this->hbdata_link) === false) {
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
            $this->version = mysql_get_server_info($this->hbdata_link);
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
        $query = mysql_query($this->sql, $this->hbdata_link);
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
        return mysql_affected_rows();
    }

    /**
     * return a certain column start at a certain row
     * @param int $row
     * @param string $field
     * @return string
     */
    function result($row = 0)
    {
        return @ mysql_result($this->result, $row);
    }

    /**
     * use the return value of query to count rows of the result set
     * @param $query
     * @return int
     */
    function num_rows($query)
    {
        return @ mysql_num_rows($query);
    }

    /**
     * use the return value of query to count columns of the result set
     * @param $query
     * @return int
     */
    function num_fields($query)
    {
        return mysql_num_fields($query);
    }

    /**
     * free the cache of the query set
     * @return int
     */
    function free_result()
    {
        return mysql_free_result($this->result);
    }

    /**
     * get last operated id
     * @return int
     */
    function insert_id()
    {
        return mysql_insert_id();
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
            $row = mysql_fetch_row($res);
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
        return $this->prefix . $str;
    }

    /**
     * make query result into a array
     * @param $query
     * @return array
     */
    function fetch_row($query)
    {
        return mysql_fetch_row($query);
    }

    /**
     * make query result into a key => value array
     * @param $query
     * @return array
     */
    function fetch_assoc($query)
    {
        return mysql_fetch_assoc($query);
    }

    /**
     * make query result into a key => value array and also a int value for the row the one record map
     * @param $query
     * @return array
     */
    function fetch_array($query)
    {
        return mysql_fetch_array($query);
    }

    /**
     * close database connection
     * @return bool
     */
    function close()
    {
        return mysql_close($this->hbdata_link);
    }

    /**
     * get all records
     * @return bool
     */
    function select_all($table)
    {
        return $this->query("SELECT * FROM " . "`" . $table . "`");
    }

    /**
     * if table exists
     * @return bool
     */
    function table_exist($table)
    {
        if($this->num_rows($this->query("SHOW TABLES LIKE '" . $this->table($table) . "'")) == 1)
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
        $sql = "SHOW COLUMNS FROM `" . $this->table($table) . "`";
        $query = $this->query($sql);
        while($row = mysql_fetch_array($query, MYSQL_ASSOC))
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

    
}
