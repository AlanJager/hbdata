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

//权限判断
require ('auth.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'theme');

/**
 * 模板列表
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['theme']);

    $theme_enable = $hbdata->get_theme_info($_CFG['site_theme']);
    $theme_array = $hbdata->get_subdirs(ROOT_PATH . 'theme/');
    foreach ($theme_array as $unique_id) {
        if ($unique_id == $_CFG['site_theme']) continue;
        $theme_list[] = $hbdata->get_theme_info($unique_id);
    }

    $smarty->assign('theme_enable', $theme_enable);
    $smarty->assign('theme_list', $theme_list);

    $smarty->display('theme.htm');
}


/**
 * 模板启用
 */
if ($rec == 'enable') {
    if ($check->is_extend_id($unique_id = $_REQUEST['unique_id'])) {
        $theme_array = $hbdata->get_subdirs(ROOT_PATH . 'theme/');
        if (in_array($unique_id, $theme_array)) { // 判断删除操作的模板是否真实存在
            // 替换系统设置中模板值
            $hbdata->query("UPDATE " . $hbdata->table('config') . " SET value = '$unique_id' WHERE name = 'site_theme'");
            $hbdata->hbdata_clear_cache(ROOT_PATH . "cache"); // 更新缓存
        }
    }

    $hbdata->hbdata_header('theme.php');
}

/**
 * 删除模板
 */
elseif ($rec == 'del') {
    // 载入扩展功能
    include_once (ROOT_PATH . ADMIN_PATH . '/include/cloud.class.php');
    $hbdata_cloud = new Cloud('cache');

    if ($check->is_extend_id($unique_id = $_REQUEST['unique_id'])) {
        $theme_array = $hbdata->get_subdirs(ROOT_PATH . 'theme/');
        if (in_array($unique_id, $theme_array)) { // 判断删除操作的模板是否真实存在
            $hbdata->del_dir(ROOT_PATH . 'theme/' . $unique_id);
            $hbdata_cloud->change_updatedate('theme', $unique_id, true); // 删除更新时间记录
            $hbdata->create_admin_log($_LANG['theme_del'] . ': ' . $unique_id);
        }
    }

    $hbdata->hbdata_header('theme.php');
}
?>