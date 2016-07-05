<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: tr3e
 * Release Date: 2016-7-4
 */
if (!defined('IN_HBDATA')) {
    die('Hacking attempt');
}

require_once 'JSON.php';
include_once ('../../../../data/config.php');

// 定义常量
define('ROOT_PATH', str_replace(ADMIN_PATH . '/include/kindeditor/php/init.php', '', str_replace('\\', '/', __FILE__)));
define('ROOT_URL', preg_replace('/admin\/include\/kindeditor\/php' . '\//Ums', '', dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) . "/"));

?>