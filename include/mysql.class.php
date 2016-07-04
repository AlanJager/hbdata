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
    private $hbData_link;
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
            if (!$this->hbData_link = @mysql_pconnect($this->dbhost, $this->dbuser, $this->dbpass)) {
                $this->error('Can not pconnect to mysql server');
                return false;
            }
        } else {
            if (!$this->hbData_link = @mysql_connect($this->dbhost, $this->dbuser, $this->dbpass, true)) {
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

        if (mysql_select_db($this->dbname, $this->hbData_link) === false) {
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
            $this->version = mysql_get_server_info($this->hbData_link);
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
        $query = mysql_query($this->sql, $this->hbData_link);
        return $query;
    }

    /**
     * return err message and stop the app
     * @param string $msg
     * @return string $msg
     */
    function error($msg = '')
    {
        $msg = $msg ? "HbDataPHP Error: $msg" : '<b>MySQL server error report</b><br>' . $this->error_msg;
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
    function num_rows($query) {
        return @ mysql_num_rows($query);
    }

    /**
     * use the return value of query to count columns of the result set
     * @param $query
     * @return int
     */
    function num_fields($query) {
        return mysql_num_fields($query);
    }

    /**
     * free the cache of the query set
     * @return int
     */
    function free_result() {
        return mysql_free_result($this->result);
    }

    /**
     * get last operated id
     * @return int
     */
    function insert_id() {
        return mysql_insert_id();
    }
}