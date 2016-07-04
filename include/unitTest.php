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

/**
 * 测试用例
 * @author AlanJager
 */
define('IN_HBDATA', true);
require (dirname(__FILE__) . '/mysql.class.php');

function testDbMysqlConnect()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    if($dbMysql->connect() == false)
    {
        echo "connect failed";
    } else {
        echo "success";
    }
}
//testDbMysqlClass();

function testDbMysqlAffectedRows()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    $sql = "select * from 'hbDataarticle'";
    $dbMysql->query($sql);
    echo $dbMysql->affected_rows();
}
//testDbMysqlAffectedRows();


function testDbMysqlresult()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    $sql = "select * from 'hbDataarticle'";
    $dbMysql->query($sql);
    echo $dbMysql->result();
}
testDbMysqlresult();