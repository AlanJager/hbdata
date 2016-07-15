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
    $smarty->assign('ur_here', $_LANG['manager']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['manager_add'],
        'href' => 'manager.php?rec=add'
    ));

    $sql = "SELECT * FROM " . $hbdata->table('admin') . " ORDER BY user_id ASC";
    $query = $hbdata->query($sql);
    while ($row = $hbdata->fetch_array($query)) {
        $add_time = date("Y-m-d", $row['add_time']);
        $last_login = date("Y-m-d H:i:s", $row['last_login']);

        $manager_list[] = array (
            "user_id" => $row['user_id'],
            "user_name" => $row['user_name'],
            "email" => $row['email'],
            "action_list" => $row['action_list'],
            "add_time" => $add_time,
            "last_login" => $last_login
        );
    }

    // 赋值给模板
    $smarty->assign('cur', 'manager');
    $smarty->assign('manager_list', $manager_list);

    $smarty->display('manager.htm');
}
?>