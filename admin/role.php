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

    $role_list = $hbdata->getAllRoles($_USER['user_id']);

    // 赋值给模板
    $smarty->assign('cur', 'role');
    $smarty->assign('role_list', $role_list);

    $smarty->display('role.htm');
}
else if ($rec == "add") {
    $smarty->assign('ur_here', $_LANG['role_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['role_list'],
        'href' => 'role.php'
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('add_role'));

    $smarty->display('role.htm');
}
else if ($rec == "insert") {
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
}
else if ($rec == "edit") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'role.php');
    }

    $smarty->assign('ur_here', $_LANG['role_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['role_list'],
        'href' => 'role.php'
    ));



    $role_id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';
    if (! $role_id) {
        $hbdata->hbdata_msg($LANG['illegal'], 'role.php', '', 2);
    }

    $role_info = $hbdata->getRoleByRoleID($role_id);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('edit_role'));

    $smarty->assign('role_info', $role_info);

    $smarty->display('role.htm');
}
else if ($rec == "update") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'role.php');
    }

    $role_id = $_POST['id'];

    //TODO verify role name
    $role_title = $_POST['role_title'];

    //TODO verify role description
    $role_description = $_POST['role_description'];

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'edit_role');

    $sql = $hbdata->updateRoleByRoleID($role_id, $role_title, $role_description);

    $hbdata->create_admin_log($_LANG['edit_role'] . ': ' . $_POST['$role_title']);
    $hbdata->hbdata_msg($_LANG['role_edit_success'], 'role.php');
}
else if ($rec == "del") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'role.php');
    }

    //TODO
    $role_id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';

    if ($rbac->Roles->remove($role_id, true)){
        $hbdata->create_admin_log($_LANG['edit_role'] . ': ' . $_POST['$role_title']);
        $hbdata->hbdata_msg($_LANG['role_delete_success'], 'role.php');
    } else {
        $hbdata->hbdata_msg($_LANG['role_delete_fail'], 'role.php');
    }
}
else if ($rec == "edit_role_permission") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'role.php');
    }

    $smarty->assign('ur_here', $_LANG['role_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['role_list'],
        'href' => 'role.php'
    ));


    $role_id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';



    $smarty->assign("permission_list", $hbdata->getAllPermissions($role_id));
    $smarty->assign('role_id', $role_id);


    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('edit_role'));

    $smarty->display('role.htm');
}
else if ($rec == 'update_role_permission') {

    $role_id = $_POST['role_id'];

    $permission_list = $hbdata->getAllPermissions($role_id);

    foreach ($permission_list as $permission) {
        $rbac->Permissions->unassign($role_id, $permission['permission_id']);
        if ($_POST[$permission['permission_id']]) {
            $rbac->Permissions->assign($role_id, $permission['permission_id']);
        }
    }

    //TODO
    //$hbdata->create_admin_log($_LANG['add_permission'] . ': ' . $_POST['permission_description']."-->".$_POST['role_id']);
    $hbdata->hbdata_msg($_LANG['role_permission_add_success'], 'role.php');

}

?>
