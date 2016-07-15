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
    $smarty->assign('ur_here', $_LANG['role_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['add_role'],
        'href' => 'role.php?rec=add'
    ));


    $sql = "SELECT * FROM " . $hbdata->table('roles') . " ORDER BY ID ASC";
    $query = $hbdata->query($sql);
    while ($row = $hbdata->fetch_array($query)) {
        $role_list[] = array (
            "role_id" => $row['ID'],
            "role_title" => $row['Title'],
            "role_description" => $row['Description']
        );
    }

    // 赋值给模板
    $smarty->assign('cur', 'role');
    $smarty->assign('role_list', $role_list);

    $smarty->display('role.htm');
} else if ($rec == "add") {
    $smarty->assign('ur_here', $_LANG['role_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['role_list'],
        'href' => 'role.php'
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('add_role'));

    $smarty->display('role.htm');
} else if ($rec == "insert") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'role.php');
    }

    //TODO verify role name
    $role_title = $_POST['role_title'];

    //TODO verify role description
    $role_description = $_POST['role_description'];

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'add_role');


    $rbac->Roles->add($role_title, $role_description);
    $hbdata->create_admin_log($_LANG['add_role'] . ': ' . $_POST['$role_title']);
    $hbdata->hbdata_msg($_LANG['add_role_success'], 'role.php');
} else if ($rec == "edit") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'role.php');
    }

    $smarty->assign('ur_here', $_LANG['role_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['role_list'],
        'href' => 'role.php'
    ));


    //TODO

    $smarty->display('role.htm');
} else if ($rec == "update") {

} else if ($rec == "del") {

}
