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


    $sql = "SELECT * FROM " . $hbdata->table('admin') . " ORDER BY user_id ASC";
    $query = $hbdata->query($sql);
    while ($row = $hbdata->fetch_array($query)) {
        $user_role_list[] = array (
            "user_id" => $row['user_id'],
            "user_name" => $row['user_name'],
        );
    }

    $result_set = array();
    foreach ($user_role_list as $user_role) {
        $user_id = $user_role['user_id'];
        $sql = "SELECT * FROM " . $hbdata->table('userroles') . " where UserID = " . $user_id;
        $query = $hbdata->query($sql);
        while ($row = $hbdata->fetch_array($query)) {
            $user_role_result_set[] = array (
                "role_id" => $row['RoleID'],
            );
        }

        foreach ($user_role_result_set as $user_role_info) {
            $role_id = $user_role_info['role_id'];
            $sql = "SELECT * FROM " . $hbdata->table('roles') . "where ID = " . $role_id;
            $query = $hbdata->query($sql);
            while ($row = $hbdata->fetch_array($query)) {
                $role_list[] = array (
                    "role_title" => $row['Title'],
                );
                $user_role['role_title'] = $role_list;
            }
            array_push($result_set, $user_role);
        }
    }

    // 赋值给模板
    $smarty->assign('test', $var);
    $smarty->assign('cur', 'user_role');
    $smarty->assign('user_role_list', $result_set);

    $smarty->display('user_role.htm');
} else if ($rec == "add") {

} else if ($rec == "insert") {

} else if ($rec = "edit") {

} else if ($rec = "update") {

} else if ($rec = "del") {

}
