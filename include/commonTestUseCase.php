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
require (dirname(__FILE__) . '/common.class.php');
require (dirname(__FILE__) . '/util.php');

function testCommonChildId()
{
    $common = new Common('localhost', 'root', '', 'hbData', 'hbData', 'utf8', 0);
    echo $common->hbdata_child_id("article_category", 1);
    $common->close();
}
//testCommonChildId();


