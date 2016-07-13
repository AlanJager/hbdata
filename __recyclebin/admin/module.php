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

// 载入语言文件
require ('../languages/zh_cn/admin/module.lang.php');
require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 压缩包上传
include_once (ROOT_PATH . 'include/upload.class.php');
$cache_dir = ROOT_PATH . 'cache/';
$hbdata_upload = new Upload($cache_dir, '', 'zip', 5120);

$smarty->assign('rec', $rec);
$smarty->assign('cur', 'module');

/**
 * 扩展列表
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['module']);

    $smarty->assign('get', urlencode(serialize($_GET)));
    $smarty->assign('localsite', $hbdata->hbdata_localsite('module'));

    $smarty->display('module.htm');
}

/**
 * 安装本地模块
 */
if ($rec == 'local') {
    $smarty->assign('ur_here', $_LANG['module']);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('module_local'));

    $smarty->display('module.htm');
}

/**
 * 安装本地模块
 */
if ($rec == 'install') {
    // 判断是否有上传文件
    if ($_FILES['zipfile']['name'] == '') {
        $hbdata->hbdata_msg($_LANG['module_file_empty'], 'module.php?rec=local');
    } else {
        $zipfile_name = preg_replace('/.zip/i', '', $_FILES['zipfile']['name']);
    }

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'module_local');

    if ($hbdata_upload->upload_image('zipfile', $zipfile_name))
        $hbdata->hbdata_header('cloud.php?rec=handle&type=module&mode=local&cloud_id=' . $zipfile_name);
}

/**
 * 模板卸载页面
 */
if ($rec == 'uninstall') {
    $smarty->assign('ur_here', $_LANG['module']);

    // 载入待删除模块
    $zipfile_list = glob($cache_dir . '*.zip');
    if (is_array($zipfile_list)) {
        foreach ($zipfile_list as $zipfile) {
            $uninstall_list[] = preg_replace('/.zip/i', '', basename($zipfile));
        }
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('module_uninstall'));

    $smarty->assign('uninstall_list', $uninstall_list);
    $smarty->display('module.htm');
}

/**
 * 卸载模块
 */
if ($rec == 'del') {
    // 载入扩展功能
    include_once (ROOT_PATH . ADMIN_PATH . '/include/cloud.class.php');
    $hbdata_cloud = new Cloud('cache');

    // CSRF防御令牌验证
    $firewall->check_token($_REQUEST['token'], 'module_uninstall');

    if ($check->is_extend_id($extend_id = $_REQUEST['extend_id'])) {
        $module_zip = $cache_dir . $extend_id . '.zip'; // 模块压缩包
        $module_dir = $cache_dir . $extend_id; // 模块目录

        if ($hbdata_cloud->file_unzip($module_zip, $module_dir)) {
            $hbdata_cloud->modify_theme_dir($module_dir); // 将解压得到的扩展目录中的模板文件夹名改成当前启用的模板文件夹名
            $hbdata_cloud->clear_module($extend_id);
            $hbdata_cloud->change_updatedate('module', $extend_id, true); // 删除更新时间记录
            $hbdata->del_dir($module_dir);
            @unlink($module_zip);
            $hbdata->query("DELETE FROM " . $hbdata->table('nav') . " WHERE module = '$extend_id'");
            $hbdata->query("DELETE FROM " . $hbdata->table('nav') . " WHERE module = '$extend_id" . "_category'");
            $hbdata->create_admin_log($_LANG['module_uninstall_success'] . ': ' . $extend_id);

            $hbdata->hbdata_header('module.php?rec=uninstall');
        } else {
            $hbdata->hbdata_msg($_LANG['module_unzip_wrong'], 'module.php?rec=uninstall');
        }
    } else {
        $hbdata->hbdata_msg($_LANG['module_uninstall_wrong'], 'module.php?rec=uninstall');
    }
}
?>