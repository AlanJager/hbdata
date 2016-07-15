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
    $smarty->assign('ur_here', $_LANG['user_role_manage']);


    $user_role_list = getAllUsers();

    $result_set = array();

    foreach ($user_role_list as $user_role) {
        $user_id = $user_role['user_id'];
        $user_role_result_set = getAllRolesForUser($user_id);

        foreach ($user_role_result_set as $user_role_info) {
            $role_id = $user_role_info['role_id'];
            $role = getRoleByRoleID($role_id);
            $user_role['role_title'] = $role;
            array_push($result_set, $user_role);
        }
    }

    // 赋值给模板
    $smarty->assign('test', $var);
    $smarty->assign('cur', 'user_role');
    $smarty->assign('user_role_list', $result_set);

    $smarty->display('user_role.htm');
} else if ($rec = "edit") {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'user_role.php');
    }

    $smarty->assign('ur_here', $_LANG['user_role_manage']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['user_role_list'],
        'href' => 'user_role.php'
    ));

    // get verified user id
    $user_id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';

    // get roles of cur user
    $user_roles = getAllRolesForUser($user_id);
    $user_role_infos = array();
    foreach ($user_roles as $user_role) {
        $role_id = $user_role['role_id'];
        $role = getRoleByRoleID($role_id);
        $user_role['role_title'] = $role[0]['role_title'];
        $user_role['role_description'] = $role[0]['role_description'];
        array_push($user_role_infos, $user_role);
    }


    $all_roles = getAllRoles();


    // TODO delete same value
    $sub_arr = array_intersect($all_roles, $user_role_infos);

    $smarty->assign('user_info', getUserInfoByUserID($user_id));
    $smarty->assign('role_list', $all_roles);
    $smarty->assign('total_roles', count($all_roles));

    $smarty->display('user_role.htm');

} else if ($rec = "update") {

}

/**
 * 返回用户信息
 * @param $user_id
 * @return array
 */
function getUserInfoByUserID($user_id)
{
    $user_sql = "SELECT * FROM " . $GLOBALS['hbdata']->table('admin') . " where user_id = " . $user_id;
    $user_query = $GLOBALS['hbdata']->query($user_sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($user_query)) {
        $user_info[] = array (
            "user_id" => $row['user_id'],
            "user_name" => $row['user_name'],
        );
    }

    return $user_info[0];
}

/**
 * 返回所有用户信息
 * @return array
 */
function getAllUsers()
{
    $sql = "SELECT * FROM " . $GLOBALS['hbdata']->table('admin') . " ORDER BY user_id ASC";
    $query = $GLOBALS['hbdata']->query($sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
        $user_list[] = array (
            "user_id" => $row['user_id'],
            "user_name" => $row['user_name'],
        );
    }

    return $user_list;
}

/**
 * 返回用户所有角色
 * @param user_id
 * @return array
 */
function getAllRolesForUser($user_id) {
    $sql = "SELECT * FROM " . $GLOBALS['hbdata']->table('userroles') . " where UserID = " . $user_id;
    $query = $GLOBALS['hbdata']->query($sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
        $user_role_result_set[] = array (
            "role_id" => $row['RoleID'],
        );
    }
    return $user_role_result_set;
}

/**
 * 根据角色ID返回角色信息
 * @return array
 */
function getRoleByRoleID($role_id) {
    $sql = "SELECT * FROM " . $GLOBALS['hbdata']->table('roles') . "where ID = " . $role_id;
    $query = $GLOBALS['hbdata']->query($sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
        $role_list[] = array (
            "role_id" => $row['ID'],
            "role_title" => $row['Title'],
            "role_description" => $row['Description']
        );
    }
    return $role_list;
}

/**
 * 返回所有角色信息
 * @return array
 */
function getAllRoles() {
    $role_sql = "SELECT * FROM " . $GLOBALS['hbdata']->table('roles') . " ORDER BY ID ASC";
    $role_query = $GLOBALS['hbdata']->query($role_sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($role_query)) {
        $role_list[] = array (
            "role_id" => $row['ID'],
            "role_title" => $row['Title'],
            "role_description" => $row['Description']
        );
    }
    return $role_list;
}
