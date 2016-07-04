<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: Firery
 * Release Date: 2016-7-4
 */

if(!defined('IN_HBDATA')){
    die('Hacking attempt');
}

class Backup
{
    var $sqlcharset;
    function Backup($sqlcharset)
    {
        $this->sqlcharset = $sqlcharset;
    }

    /**
     * 生成数据库备份文件
     * @param $table 所选数据表
     * @param $vol_size 每个分卷文件大小
     * @param int $startfrom
     * @param int $currsize
     * @return mixed|string
     */
    function sql_dumptable($table, $vol_size, $startfrom = 0, $currsize = 0)
    {
        global $startrow;

        $allow_max_size = intval(@ ini_get('upload_max_filesize')); // 单位M
        if ($allow_max_size > 0 && $vol_size > ($allow_max_size * 1024)) {
            $vol_size = $allow_max_size * 1024; // 单位K
        }

        if ($vol_size > 0) {
            $vol_size = $vol_size * 1024;
        }

        if (!isset($tabledump)) {
            $tabledump = '';
        }
        $offset = 100;
        if (!$startfrom) {
            $tabledump = "DROP TABLE IF EXISTS `$table`;\n";
            $createtable = $GLOBALS['hbdata']->query("SHOW CREATE TABLE $table");
            $create = $GLOBALS['hbdata']->fetch_array($createtable);
            $tabledump .= $create[1] . ";\n\n";
            if ($GLOBALS['hbdata']->version() > '4.1' && $this->sqlcharset) {
                $tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=[a-zA-Z0-9]+/", "DEFAULT CHARSET=" . $this->sqlcharset, $tabledump);
            }
        }
        $tabledumped = 0;
        $numrows = $offset;
        while ($currsize + strlen($tabledump) < $vol_size && $numrows == $offset) {
            $tabledumped = 1;
            $rows = $GLOBALS['hbdata']->query("SELECT * FROM $table LIMIT $startfrom, $offset");
            $numfields = $GLOBALS['hbdata']->num_fields($rows);
            $numrows = $GLOBALS['hbdata']->num_rows($rows);
            while ($row = $GLOBALS['hbdata']->fetch_array($rows)) {
                $comma = "";
                $tabledump .= "INSERT INTO $table VALUES(";
                for($i = 0; $i < $numfields; $i++) {
                    $tabledump .= $comma . "'" . $GLOBALS['hbdata']->escape_string($row[$i]) . "'";
                    $comma = ",";
                }
                $tabledump .= ");\n";
            }
            $startfrom += $offset;
        }
        $startrow = $startfrom;
        $tabledump .= "\n";
        return $tabledump;
    }

    /**
     * 获取文件拓展名
     * @param $filename
     * @return string
     */
    function fileext($filename)
    {
        return trim(substr(strrchr($filename, '.'), 1));
    }

    /**
     * 判断文件名是否规范
     * @param $file_name
     * @return bool
     */
    function is_backup_file($file_name)
    {
        if (preg_match("/^[a-zA-Z0-9_]+.sql$/", $file_name)) {
            return true;
        }
    }
}

?>