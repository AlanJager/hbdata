<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: 昊
 * Release Date: 2016-7-4
 */
define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');
require (ROOT_PATH . 'include/sitemap.class.php');

if (!intval($GLOBALS['_CFG']['sitemap'])) {
    exit();
}

$domain = ROOT_URL;
$today = date('Y-m-d');

$sm = new SiteMap($domain, $today);
header('Content-type: application/xml; charset=utf-8');
die($sm->build_sitemap());

?>