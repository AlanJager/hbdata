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
    $smarty->assign('action_link', array (
        'text' => $_LANG['add_user_role'],
        'href' => 'user_role.php?rec=add'
    ));


    $sql = "SELECT * FROM " . $hbdata->table('admin') . " ORDER BY user_id ASC";
    $query = $hbdata->query($sql);
    while ($row = $hbdata->fetch_array($query)) {
        $user_role_list[] = array (
            "user_id" => $row['user_id'],
            "user_name" => $row['user_name'],
        );
    }

    $testSet = array();
    $val = -1;
    //array_push($testSet, $sql);
    foreach ($user_role_list as $user_role) {
        $user_id = $user_role['user_id'];
        $sql = "SELECT * FROM " . $hbdata->table('userroles') . " where UserID = " . $user_id;
        $query = $hbdata->query($sql);
        while ($row = $hbdata->fetch_array($query)) {
            $user_role_result_set[] = array (
                "role_id" => $row['RoleID'],
            );
        }

        foreach ($user_role_result_set as $user_role) {
            $role_id = $user_role['role_id'];
            $sql = "SELECT * FROM " . $hbdata->table('roles') . "where ID = " . $role_id;
            $query = $hbdata->query($sql);
            while ($row = $hbdata->fetch_array($query)) {
                $role_list[] = array (
                    "role_title" => $row['Title'],
                );
                $user_role['role_title'] = $role_list;
            }

            $val = $user_role['user_id'];
        }

    }

    // 赋值给模板
    $smarty->assign('test', $val);
    $smarty->assign('cur', 'user_role');
    $smarty->assign('user_role_list', $user_role_list);

    $smarty->display('user_role.htm');
} else if ($rec == "add") {

} else if ($rec == "insert") {

} else if ($rec = "edit") {

} else if ($rec = "update") {

} else if ($rec = "del") {

}
