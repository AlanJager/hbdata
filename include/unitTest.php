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
require (dirname(__FILE__) . '/include/mysql.class.php');
require (ROOT_PATH . 'include/mysql.class.php');

function testDbMysqlClass()
{
    $dbMysql = new DbMysql('localhost', 'root', '', 'hbDate', 'hbData');

    echo $dbMysql->connect();
}