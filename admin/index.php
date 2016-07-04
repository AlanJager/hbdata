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
define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'index_' . $rec);

/**
 * +----------------------------------------------------------
 * 系统信息
 * +----------------------------------------------------------
 */
if ($rec == 'default') {
    $sys_info['os'] = PHP_OS;
    $sys_info['ip'] = $_SERVER['SERVER_ADDR'];
    $sys_info['web_server'] = $_SERVER['SERVER_SOFTWARE'];
    $sys_info['php_ver'] = PHP_VERSION;
    $sys_info['mysql_ver'] = $hbdata->version();
    $sys_info['gd'] = extension_loaded("gd") ? $_LANG['yes'] : $_LANG['no'];
    $sys_info['charset'] = strtoupper(HBDATA_CHARSET);
    $sys_info['build_date'] = date("Y-m-d", $_CFG['build_date']);
    $update_date = unserialize($_CFG['update_date']);
    $sys_info['update'] = $update_date['system']['update'];
    $sys_info['patch'] = $update_date['system']['patch'];
    $sys_info['logo'] = ROOT_URL . 'theme/' . $_CFG['site_theme'] . '/images/' . $_CFG['site_logo'];
    $sys_info['max_filesize'] = ini_get('upload_max_filesize');
    $sys_info['num_page'] = $hbdata->num_rows($hbdata->query("SELECT * FROM " . $hbdata->table('page')));
    $sys_info['num_product'] = $hbdata->num_rows($hbdata->query("SELECT * FROM " . $hbdata->table('product')));
    $sys_info['num_article'] = $hbdata->num_rows($hbdata->query("SELECT * FROM " . $hbdata->table('article')));

    // 提示应该被删除的目录未被删除
    if ($hbdata->dir_status(ROOT_PATH . 'install') != 'no_exist') $warning[] = $_LANG['warning_install_exists'];
    if ($hbdata->dir_status(ROOT_PATH . 'upgrade') != 'no_exist') $warning[] = $_LANG['warning_upgrade_exists'];
    if ($extend == 'on') $warning[] = $_LANG['warning_extend_exists'];

    // 写入目录监测信息
    $sys_info['folder_exists'] = $warning;

    // 赋值给模板
    $smarty->assign('cur', 'index');
    $smarty->assign('page_list', $hbdata->get_page_nolevel());
    $smarty->assign('sys_info', $sys_info);
    $smarty->assign("log_list", $hbdata->get_admin_log($_SESSION[HBDATA_ID]['user_id'], 4));
    $smarty->assign('localsite', $hbdata->hbdata_localsite());

    $smarty->display('index.htm');
}

/**
 * +----------------------------------------------------------
 * 清除缓存及已编译模板
 * +----------------------------------------------------------
 */
elseif ($rec == 'clear_cache') {
    $hbdata->hbdata_clear_cache(ROOT_PATH . 'cache');
    $hbdata->hbdata_msg($_LANG['clear_cache_success']);
}

?>