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
require (dirname(__FILE__) . '/util.php');

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
    $sql = "select * from `hbDataarticle`";
    $dbMysql->query($sql);
    echo $dbMysql->affected_rows();
}
//testDbMysqlAffectedRows();


function testDbMysqlresult()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    $sql = "select * from `hbDataarticle`";
    $dbMysql->query($sql);
    var_dump($dbMysql->result());
}
//testDbMysqlresult();

function testDbMysqlNumRows()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    $sql = "select * from `hbDataarticle`";
    echo $dbMysql->num_rows($dbMysql->query($sql));
}
//testDbMysqlNumRows();

function testDbMysqlInsertId()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    $sql = "INSERT INTO `hbData`.`hbDataarticle` (`id`, `cat_id`, `title`, `defined`, `content`, `image`, `keywords`, `add_time`, `click`, `description`, `sort`) VALUES (NULL, '1', '23', '23', '23', '', '23', '0', '0', '2323', '0');";
    $dbMysql->query($sql);
    echo $dbMysql->insert_id();
}
//testDbMysqlInsertId();

function testDbMysqlAutoId()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    $table = $dbMysql->table('article');
    echo $dbMysql->auto_id('article');
}
//testDbMysqlAutoId();

function testDbMysqlFetch()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    $sql = "SELECT * FROM `hbDataarticle` WHERE 1";
    echo $dbMysql->fetch_array($dbMysql->query($sql));
}
//testDbMysqlFetch();

function testDbMysqlComplexQuery()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    echo $dbMysql->select_all("article");
    echo $dbMysql->table_exist("article") == true ? "success" : "failed";
    echo $dbMysql->field_exist("article", "id") == true ? "success" : "failed";
    echo $dbMysql->value_exist("article", "id", 1) == true ? "success" : "failed";
    $dbMysql->close();
}
//testDbMysqlComplexQuery();

function testDbMysqlFetchArrayAll()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    arrayDump($dbMysql->fetch_array_all("article", 'sort ASC'));
    $dbMysql->close();
}
//testDbMysqlFetchArrayAll();