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


// 开启SESSION
session_start();


// error_reporting
error_reporting(E_ERROR | E_PARSE);


// 调整时区
if (PHP_VERSION >= '5.1') {
    date_default_timezone_set('PRC');
}


include_once ('../data/config.php');


// 定义常量
define('ROOT_PATH', str_replace(ADMIN_PATH . '/include/init.php', '', str_replace('\\', '/', __FILE__)));
define('ROOT_URL', preg_replace('/' . ADMIN_PATH . '\//Ums', '', dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) . "/"));
define('IS_ADMIN', true);

if (!file_exists(ROOT_PATH . "data/system.hbdata")) {
    header("Location: ../install/index.php\n");
    exit();
}


require (ROOT_PATH . 'include/smarty/Smarty.class.php');
require (ROOT_PATH . 'include/mysql.class.php');
require (ROOT_PATH . 'include/common.class.php');
require (ROOT_PATH . ADMIN_PATH . '/include/action.class.php');
require (ROOT_PATH . 'include/check.class.php');
require (ROOT_PATH . 'include/firewall.class.php');
require (ROOT_PATH  . 'admin/include/PhpRbac/autoload.php');

// 实例化类
$hbdata = new Action($dbhost, $dbuser, $dbpass, $dbname, $prefix, HBDATA_CHARSET);
$check = new Check();
$firewall = new Firewall();
$rbac = new PhpRbac\Rbac();


// 定义系统标示
define('HBDATA_SHELL', $hbdata->get_one("SELECT value FROM " . $hbdata->table('config') . " WHERE name = 'hash_code'"));
define('HBDATA_ID', 'admin_' . substr(md5(HBDATA_SHELL . 'admin'), 0, 5));


// 防火墙
$firewall->hbdata_firewall();


// 设置页面缓存和编码
header('content-type: text/html; charset=' . HBDATA_CHARSET);
header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');


// 开启缓冲区
ob_start();


// SMARTY配置
$smarty = new smarty();
// 目录变量
$smarty->config_dir = ROOT_PATH . 'include/smarty/Config_File.class.php';
// 模板存放目录
$smarty->template_dir = ROOT_PATH . ADMIN_PATH . '/templates';
// 编译目录
$smarty->compile_dir = ROOT_PATH . 'cache/' . ADMIN_PATH;
// 左定界符
$smarty->left_delimiter = '{';
// 右定界符
$smarty->right_delimiter = '}';


// 如果编译和缓存目录不存在则建立
if (!file_exists($smarty->compile_dir))
    mkdir($smarty->compile_dir, 0777);


// 验证管理员
$smarty->assign("user", $_USER = $hbdata->admin_check($_SESSION[HBDATA_ID]['user_id'], $_SESSION[HBDATA_ID]['shell']));


// 读取站点信息
$smarty->assign("site", $_CFG = $hbdata->get_config());


// 系统模块
$_MODULE = $hbdata->hbdata_module();


// 载入语言文件
foreach ($_MODULE['lang'] as $lang_file) {
    // 载入系统语言文件
    require ($lang_file); 
}


// 工作空间
$smarty->assign("workspace", $hbdata->hbdata_workspace());


// 通用信息调用
$smarty->assign("lang", $_LANG);
// 显示设置
$_DISPLAY = unserialize($_CFG['display']);
// 自定义属性
$_DEFINED = unserialize($_CFG['defined']); 


// Smarty 过滤器
function remove_html_comments($source, & $smarty) {
    return $source = preg_replace('/<!--.*{(.*)}.*-->/U', '{$1}', $source);
}
$smarty->register_prefilter('remove_html_comments');

?>