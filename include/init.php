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

// error_reporting
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

// close set_magic_quotes_runtime
@ set_magic_quotes_runtime(0);

// set timezone
if (PHP_VERSION >= '5.1') {
    date_default_timezone_set('PRC');
}

// get root dir of cur website
$root_url = dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) . "/";
define('ROOT_PATH', str_replace('include/init.php', '', str_replace('\\', '/', __FILE__)));
define('ROOT_URL', !defined('ROUTE') ? $root_url : str_replace('include/', '', $root_url)); // use route.php or ?

if (!file_exists(ROOT_PATH . "data/system.hbdata")) {
    header("Location: " . ROOT_URL . "install/index.php\n");
    exit();
}

require_once (ROOT_PATH . 'data/config.php'); // rewrite model config.php will be invoked in route.php at first time
require (ROOT_PATH . 'include/smarty/Smarty.class.php');
require (ROOT_PATH . 'include/mysql.class.php');
require (ROOT_PATH . 'include/common.class.php');
require (ROOT_PATH . 'include/action.class.php');
require (ROOT_PATH . 'include/check.class.php');
require (ROOT_PATH . 'include/firewall.class.php');

// initialize class
$hbdata = new Action($dbhost, $dbuser, $dbpass, $dbname, $prefix, HBDATA_CHARSET);
$check = new Check();
$firewall = new Firewall();

// sys identification
define('HBDATA_SHELL', $hbdata->get_one("SELECT value FROM " . $hbdata->table('config') . " WHERE name = 'hash_code'"));
define('HBDATA_ID', 'hbdata_' . substr(md5(HBDATA_SHELL . 'hbdata'), 0, 5));

// read site information
$_CFG = $hbdata->get_config();

if (!defined('EXIT_INIT')) {
    //set cache and encode
    header('Cache-control: private');
    header('Content-type: text/html; charset=' . HBDATA_CHARSET);

    // if redirect to mobile version
    if ($hbdata->is_mobile() && $_COOKIE['client'] != 'pc' && !$_CFG['mobile_closed']) {
        $content_url = str_replace(ROOT_URL, '', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $hbdata->hbdata_header(ROOT_URL . M_PATH . '/' . $content_url);
    }

    //firewall
    $firewall->hbdata_firewall();

    // SMARTY配置
    $smarty = new smarty();
    $smarty->config_dir = ROOT_PATH . 'include/smarty/Config_File.class.php'; // 目录变量
    $smarty->template_dir = ROOT_PATH . 'theme/' . $_CFG['site_theme']; // 模板存放目录
    $smarty->compile_dir = ROOT_PATH . 'cache'; // 编译目录
    $smarty->left_delimiter = '{'; // 左定界符
    $smarty->right_delimiter = '}'; // 右定界符

    // if do not have compile or cache dir, create it
    if (!file_exists($smarty->compile_dir))
        mkdir($smarty->compile_dir, 0777);

    // sys module
    $_MODULE = $hbdata->hbdata_module();

    // load language file
    foreach ((array) $_MODULE['lang'] as $lang_file) {
        require ($lang_file);
    }
    $_LANG['copyright'] = preg_replace('/d%/Ums', $_CFG['site_name'], $_LANG['copyright']);

    // load module file
    foreach ((array) $_MODULE['init'] as $init_file) {
        require ($init_file);
    }

    // if close site
    if ($_CFG['site_closed']) {
        // set page encode
        header('Content-type: text/html; charset=' . HBDATA_CHARSET);

        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><div style=\"margin: 200px; text-align: center; font-size: 14px\"><p>" . $_LANG['site_closed'] . "</p><p></p></div>";
        exit();
    }

    // common information invoke
    $smarty->assign("lang", $_LANG);
    $smarty->assign("site", $_CFG);
    $smarty->assign("url", $_URL = $hbdata->hbdata_url()); // module URL
    $smarty->assign("open", $_OPEN = $_MODULE['open']); // module status
    $_DISPLAY = unserialize($_CFG['display']); // visibility setting
    $_DEFINED = unserialize($_CFG['defined']); // customize attribute

    // Smarty filter
    function remove_html_comments($source, & $smarty) {
        global $_CFG;
        $theme_path = ROOT_URL . 'theme';
        $source = preg_replace('/\"\.*\/images\//Ums', '"images/', $source);
        $source = preg_replace('/\"images\//Ums', "\"theme/$_CFG[site_theme]/images/", $source);
        $source = preg_replace('/link href\=\"([A-Za-z0-9_-]+)\.css/Ums', "link href=\"theme/$_CFG[site_theme]/$1.css", $source);
        $source = preg_replace('/theme\//Ums', "$theme_path/", $source);
        $source = preg_replace('/^<meta\shttp-equiv=["|\']Content-Type["|\']\scontent=["|\']text\/html;\scharset=(?:.*?)["|\'][^>]*?>\r?\n?/i', '', $source);
        return $source = preg_replace('/<!--.*{(.*)}.*-->/U', '{$1}', $source);
    }
    $smarty->register_prefilter('remove_html_comments');
}

// open buffer zone
ob_start();
