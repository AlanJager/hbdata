<?php
/**
 * Created by PhpStorm.
 * User: 昊
 * Date: 2016/7/4
 * Time: 13:40
 */
define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

$smarty->assign('rec', $rec);

/**
 * +----------------------------------------------------------
 * 管理员列表
 * +----------------------------------------------------------
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

/**
 * +----------------------------------------------------------
 * 管理员添加处理
 * +----------------------------------------------------------
 */
elseif ($rec == 'add') {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'manager.php');
    }

    $smarty->assign('ur_here', $_LANG['manager']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['manager_list'],
        'href' => 'manager.php'
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('manager_add'));

    $smarty->display('manager.htm');
}

elseif ($rec == 'insert') {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'manager.php');
    }

    // 验证用户名
    if (!$check->is_username($_POST['user_name']))
        $hbdata->hbdata_msg($_LANG['manager_username_cue']);

    // 验证密码
    if (!$check->is_password($_POST['password']))
        $hbdata->hbdata_msg($_LANG['manager_password_cue']);

    // 验证确认密码
    if ($_POST['password_confirm'] !== $_POST['password'])
        $hbdata->hbdata_msg($_LANG['manager_password_confirm_cue']);

    $password = md5($_POST['password']);
    $add_time = time();

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'manager_add');

    $sql = "INSERT INTO " . $hbdata->table('admin') . " (user_id, user_name, email, password, action_list, add_time)" . " VALUES (NULL, '$_POST[user_name]', '$_POST[email]', '$password', 'ADMIN', '$add_time')";
    $hbdata->query($sql);
    $hbdata->create_admin_log($_LANG['manager_add'] . ': ' . $_POST['user_name']);
    $hbdata->hbdata_msg($_LANG['manager_add_succes'], 'manager.php');
}

/**
 * +----------------------------------------------------------
 * 管理员编辑
 * +----------------------------------------------------------
 */
elseif ($rec == 'edit') {
    $smarty->assign('ur_here', $_LANG['manager']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['manager_list'],
        'href' => 'manager.php'
    ));

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';

    $query = $hbdata->select($hbdata->table('admin'), '*', '`user_id` = \'' . $id . '\'');
    $manager_info = $hbdata->fetch_array($query);

    if ($_USER['action_list'] != 'ALL' && $manager_info['user_name'] != $_USER['user_name']) {
        $hbdata->hbdata_msg($_LANG['without'], 'manager.php');
    }

    // 超级管理员修改普通管理员信息无需旧密码
    if ($_USER['action_list'] == 'ALL' && $manager_info['user_name'] != $_USER['user_name']) {
        $if_check = false;
    } else {
        $if_check = true;
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('manager_edit'));

    $smarty->assign('if_check', $if_check);
    $smarty->assign('manager_info', $manager_info);

    $smarty->display('manager.htm');
}

elseif ($rec == 'update') {
    $query = $hbdata->select($hbdata->table('admin'), '*', '`user_id` = \'' . $_POST['id'] . '\'');
    $manager_info = $hbdata->fetch_array($query);

    // 判断管理员账号是否符合规范
    if (!$check->is_username($_POST['user_name'])) {
        $hbdata->hbdata_msg($_LANG['manager_username_cue']);
    }

    // 超级管理员修改普通管理员信息无需旧密码
    if (!($_USER['action_list'] == 'ALL' && $manager_info['user_name'] != $_USER['user_name'])) {
        if (!$_POST['old_password']) {
            $hbdata->hbdata_msg($_LANG['manager_old_password_cue']);
        } elseif (md5($_POST['old_password']) != $manager_info['password']) {
            $hbdata->create_admin_log($_LANG['manager_edit'] . ': ' . $_POST['user_name'] . " ( " . $_LANG['manager_old_password_cue'] . " )");
            $hbdata->hbdata_msg($_LANG['manager_old_password_cue']);
        }
    }

    // 如果有输入新密码，则验证新密码
    if ($_POST['password']) {
        if (!$check->is_password($_POST['password'])) {
            $hbdata->hbdata_msg($_LANG['manager_password_cue']);
        } elseif ($_POST['password_confirm'] != $_POST['password']) {
            $hbdata->hbdata_msg($_LANG['manager_password_confirm_cue']);
        }

        $update_password = ", password = '" . md5($_POST['password']) . "'";
    }

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'manager_edit');

    $sql = "UPDATE " . $hbdata->table('admin') . " SET user_name = '$_POST[user_name]',  email = '$_POST[email]'" . $update_password . " WHERE user_id = '$_POST[id]'";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['manager_edit'] . ': ' . $_POST['user_name']);

    $hbdata->hbdata_msg($_LANG['manager_edit_succes'], 'manager.php');
}

/**
 * +----------------------------------------------------------
 * 管理员删除
 * +----------------------------------------------------------
 */
elseif ($rec == 'del') {
    if ($_USER['action_list'] != 'ALL') {
        $hbdata->hbdata_msg($_LANG['without'], 'manager.php');
    }

    // 验证并获取合法的ID
    $user_id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'manager.php');

    $user_name = $hbdata->get_one("SELECT user_name FROM " . $hbdata->table('admin') . " WHERE user_id = '$user_id'");

    if ($user_name == $_USER['user_name'] || ($_USER['action_list'] != 'ALL' && $manager_info['user_name'] != $_USER['user_name'])) {
        $hbdata->hbdata_msg($_LANG['manager_del_wrong'], 'manager.php', '', '3');
    } else {
        if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
            $hbdata->create_admin_log($_LANG['manager_del'] . ': ' . $user_name);
            $hbdata->delete($hbdata->table('admin'), "user_id = $user_id", 'manager.php');
        } else {
            $_LANG['del_check'] = preg_replace('/d%/Ums', $user_name, $_LANG['del_check']);
            $hbdata->hbdata_msg($_LANG['del_check'], 'manager.php', '', '30', "manager.php?rec=del&id=$user_id");
        }
    }
}

/**
 * +----------------------------------------------------------
 * 操作记录
 * +----------------------------------------------------------
 */
elseif ($rec == 'manager_log') {
    $smarty->assign('ur_here', $_LANG['manager_log']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['manager_list'],
        'href' => 'manager.php'
    ));
    $smarty->assign('cur', 'manager_log');

    // 验证并获取合法的分页ID
    $page = $check->is_number($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $limit = $hbdata->pager('admin_log', 15, $page, 'manager.php?rec=manager_log');

    $sql = "SELECT * FROM " . $hbdata->table('admin_log') . " ORDER BY id DESC" . $limit;
    $query = $hbdata->query($sql);
    while ($row = $hbdata->fetch_array($query)) {
        $create_time = date("Y-m-d H:i:s", $row['create_time']);
        $user_name = $hbdata->get_one("SELECT user_name FROM " . $hbdata->table('admin') . " WHERE user_id = " . $row['user_id']);

        $log_list[] = array (
            "id" => $row['id'],
            "create_time" => $create_time,
            "user_name" => $user_name,
            "action" => $row['action'],
            "ip" => $row['ip']
        );
    }

    // 赋值给模板
    $smarty->assign('log_list', $log_list);

    $smarty->display('manager.htm');
}
?>