<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: AlanJager
 * Release Date: 2016-7-5
 */

if (!defined('IN_HBDATA')) {
    die('Accident occured, please try again.');
}



/**
 * 系统安装
 * @name Install
 * @version v1.0
 * @author AlanJager
 */
class Install {
    var $sqlcharset;
    function Install($sqlcharset) {
        $this->sqlcharset = $sqlcharset;
    }

    /**
     * 判断是否为rec操作项
     */
    function is_rec($rec) {
        if (preg_match("/^[a-z_]+$/", $rec)) {
            return true;
        }
    }

    /**
     * 判断用户名是否规范
     */
    function is_username($username) {
        if (preg_match("/^[a-zA-Z]{1}([0-9a-zA-Z]|[._]){3,19}$/", $username)) {
            return true;
        }
    }

    /**
     * 判断密码是否规范
     */
    function is_password($password) {
        if (preg_match("/^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]{6,22}$/", $password)) {
            return true;
        }
    }

    /**
     * 判断 文件/目录 是否可写
     */
    function check_writeable($file) {
        if (file_exists($file)) {
            if (is_dir($file)) {
                $dir = $file;
                if ($fp = @fopen("$dir/test.txt", 'w')) {
                    @fclose($fp);
                    @unlink("$dir/test.txt");
                    $writeable = 1;
                } else {
                    $writeable = 0;
                }
            } else {
                if ($fp = @fopen($file, 'a+')) {
                    @fclose($fp);
                    $writeable = 1;
                } else {
                    $writeable = 0;
                }
            }
        } else {
            $writeable = 2;
        }

        return $writeable;
    }

    /**
     * 数据库导入
     */
    function sql_execute($sql) {
        global $link;

        $sqls = $this->sql_split($sql);
        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                if (trim($sql) != '') {
                    mysql_query($sql, $link);
                }
            }
        } else {
            mysql_query($sqls, $link);
        }
        return true;
    }

    /**
     * 数据分离
     */
    function sql_split($sql) {
        global $prefix;
        if ($this->version() > '4.1' && $this->sqlcharset) {
            $sql = preg_replace("/TYPE=(InnoDB|MyISAM)( DEFAULT CHARSET=[^; ]+)?/", "TYPE=\\1 DEFAULT CHARSET=" . $this->sqlcharset, $sql);
        }

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

    // 仿真 Adodb 函数
    function get_one($sql, $limited = false) {
        global $link;

        if ($limited == true) {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = mysql_query($sql, $link);
        if ($res !== false) {
            $row = mysql_fetch_row($res);

            if ($row !== false) {
                return $row[0];
            } else {
                return '';
            }
        } else {
            return false;
        }
    }

    /**
     * 返回 MySQL 服务器的信息
     */
    function version() {
        global $link;
        if (empty($this->version)) {
            $this->version = mysql_get_server_info($link);
        }
        return $this->version;
    }
}