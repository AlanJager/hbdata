<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: AlanJager
 * Release Date: 2016-7-15
 */
define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');

//权限判断
//require ('auth.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

$smarty->assign('rec', $rec);

/**
 * 角色列表
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['permission_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['add_permission'],
        'href' => 'permission.php?rec=add'
    ));


    $sql = "SELECT * FROM " . $hbdata->table('permissions') . " ORDER BY ID ASC";
    $query = $hbdata->query($sql);
    while ($row = $hbdata->fetch_array($query)) {
        $permission_list[] = array (
            "permission_id" => $row['ID'],
            "permission_title" => $row['Title'],
            "permission_description" => $row['Description']
        );
    }

    // 赋值给模板
    $smarty->assign('cur', 'permission');
    $smarty->assign('permission_list', $permission_list);

    $smarty->display('permission.htm');
} else if ($rec == "add") {
    $smarty->assign('ur_here', $_LANG['permission_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['permission_list'],
        'href' => 'permission.php'
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('add_permission'));

    $smarty->display('permission.htm');
} else if ($rec == "insert") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'permission.php');
    }

    //TODO verify permission title
    $permission_title = $_POST['permission_title'];

    //TODO verify permission description
    $permission_description = $_POST['permission_description'];

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'add_permission');


    $rbac->Roles->add($permission_title, $permission_description);
    $hbdata->create_admin_log($_LANG['add_permission'] . ': ' . $_POST['$permission_title']);
    $hbdata->hbdata_msg($_LANG['permission_add_success'], 'permission.php');
} else if ($rec = "edit") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'permission.php');
    }

    $smarty->assign('ur_here', $_LANG['permission_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['permission_list'],
        'href' => 'permission.php'
    ));


    //TODO

    $smarty->display('permission.htm');
} else if ($rec = "update") {

} else if ($rec = "del") {

}
