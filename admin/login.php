<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: Anewnoob
 * Release Date: 2016-7-4
 */
define('IN_HBDATA', true);
define('NO_CHECK', true);

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 赋值给模板
$smarty->assign('rec', $rec);
/**
 * 登录页登录验证
 */
if ($rec == 'default') {
    // 赋值给模板
    $smarty->assign('page_title', $_LANG['login']);
    $smarty->display('login.htm');
}

/**
 * 登录验证
 */
elseif ($rec == 'login') {
    if ($check->is_captcha(trim($_POST['captcha'])) && $_CFG['captcha'])
        $_POST['captcha'] = strtoupper(trim($_POST['captcha']));

    if (!$_POST['user_name']) {
        $hbdata->hbdata_msg($_LANG['login_input_wrong'], 'login.php', 'out');
    } elseif (md5($_POST['captcha'] . HBDATA_SHELL) != $_SESSION['captcha'] && $_CFG['captcha']) {
        $hbdata->hbdata_msg($_LANG['login_captcha_wrong'], 'login.php', 'out');
    }

    $_POST['user_name'] = $check->is_username(trim($_POST['user_name'])) ? trim($_POST['user_name']) : '';
    $_POST['password'] = $check->is_password(trim($_POST['password'])) ? trim($_POST['password']) : '';

    $query = $hbdata->select($hbdata->table(admin), '*', "user_name = '$_POST[user_name]'");
    $user = $hbdata->fetch_array($query);

    if (!is_array($user)) {
        $hbdata->create_admin_log($_LANG['login_action'] . ': ' . $_POST['user_name'] . " ( " . $_LANG['login_user_name_wrong'] . " ) ");
        $hbdata->hbdata_msg($_LANG['login_input_wrong'], 'login.php', 'out');
        // 登录失败清除验证码
        unset($_SESSION['captcha']);
    } elseif (md5($_POST['password']) != $user['password']) {
        if ($_POST['password']) {
            $hbdata->create_admin_log($_LANG['login_action'] . ': ' . $_POST['user_name'] . " ( " . $_LANG['login_password_wrong'] . " ) ");
        }
        $hbdata->hbdata_msg($_LANG['login_input_wrong'], 'login.php', 'out');
        // 登录失败清除验证码
        unset($_SESSION['captcha']);
    }

    $_SESSION[HBDATA_ID]['user_id'] = $user['user_id'];
    $_SESSION[HBDATA_ID]['shell'] = md5($user['user_name'] . $user['password'] . HBDATA_SHELL);
    $_SESSION[HBDATA_ID]['ontime'] = time();

    $last_login = time();
    $last_ip = $hbdata->get_ip();
    $sql = "update " . $hbdata->table('admin') . " SET last_login = '$last_login', last_ip = '$last_ip' WHERE user_id = " . $user['user_id'];
    $hbdata->query($sql);
    $hbdata->create_admin_log($_LANG['login_action'] . ': ' . $_LANG['login_success']);
    $hbdata->hbdata_header(ROOT_URL . ADMIN_PATH . '/index.php');
}

/**
 * 退出登录
 */
elseif ($rec == 'logout') {
    unset($_SESSION[HBDATA_ID]);
    $hbdata->hbdata_header(ROOT_URL . ADMIN_PATH . '/login.php');
}

/**
 * 密码重置
 */
elseif ($rec == 'password_reset') {
    $user_id = $check->is_number($_REQUEST['uid']) ? $_REQUEST['uid'] : '';
    $code = preg_match("/^[a-zA-Z0-9]+$/", $_REQUEST['code']) ? $_REQUEST['code'] : '';

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('password_reset'));

    if ($user_id && $code) {
        if (!$hbdata->check_password_reset($user_id, $code)) {
            $hbdata->hbdata_msg($_LANG['login_password_reset_fail'], ROOT_URL . ADMIN_PATH . '/login.php？rec=password_reset', 'out');
        }
        $smarty->assign('user_id', $user_id);
        $smarty->assign('code', $code);
        $smarty->assign('action', 'reset');
    } else {
        $smarty->assign('action', 'default');
    }

    // 赋值给模板
    $smarty->assign('page_title', $_LANG['login_password_reset']);
    $smarty->display('login.htm');
}

/**
 * 重置密码提交
 */
elseif ($rec == 'password_reset_post') {
    $action = $_POST['action'] == 'reset' ? 'reset' : 'default';

    if ($action == 'default') {
        // 验证用户名
        if (!$hbdata->value_exist('admin', 'user_name', $_POST['user_name']) || !$hbdata->value_exist('admin', 'email', $_POST['email']))
            $hbdata->hbdata_msg($_LANG['login_password_reset_wrong'], ROOT_URL . ADMIN_PATH . '/login.php?rec=password_reset', 'out');

        // CSRF防御令牌验证
        $firewall->check_token($_POST['token'], 'password_reset');

        // 生成密码找回码
        $user = $hbdata->fetch_array($hbdata->select($hbdata->table('admin'), '*', "user_name = '$_POST[user_name]' AND email = '$_POST[email]'"));
        $time = time();
        $code = substr(md5($user['user_name'] . $user['email'] . $user['password'] . $time . $user['last_login'] . HBDATA_SHELL) , 0 , 16) . $time;
        $site_url = rtrim(ROOT_URL, '/');

        $body = $user['user_name'] . $_LANG['login_password_reset_body_0'] . ROOT_URL . ADMIN_PATH . '/login.php?rec=password_reset' . '&uid=' . $user['user_id'] . '&code=' . $code . $_LANG['login_password_reset_body_1'] . $_CFG['site_name'] . '. ' . $site_url;

        // 发送密码重置邮件
        if ($hbdata->send_mail($user['email'], $_LANG['login_password_reset'], $body)) {
            $hbdata->hbdata_msg($_LANG['login_password_mail_success'] . $user['email'], ROOT_URL . ADMIN_PATH . '/login.php', 'out', '30');
        } else {
            $hbdata->hbdata_msg($_LANG['mail_send_fail'], ROOT_URL . ADMIN_PATH . '/login.php?rec=password_reset', 'out', '30');
        }
    } elseif ($action == 'reset') {
        // 验证密码
        if (!$check->is_password($_POST['password'])) {
            $hbdata->hbdata_msg($_LANG['manager_password_cue'], '', 'out');
        } elseif (($_POST['password_confirm'] !== $_POST['password'])) {
            $hbdata->hbdata_msg($_LANG['manager_password_confirm_cue'], '', 'out');
        }

        $user_id = $check->is_number($_POST['user_id']) ? $_POST['user_id'] : '';
        $code = preg_match("/^[a-zA-Z0-9]+$/", $_POST['code']) ? $_POST['code'] : '';

        // 重置密码
        if ($hbdata->check_password_reset($user_id, $code)) {
            $sql = "UPDATE " . $hbdata->table('admin') . " SET password = '" . md5($_POST['password']) . "' WHERE user_id = '$user_id'";
            $hbdata->query($sql);
            $hbdata->hbdata_msg($_LANG['login_password_reset_success'], ROOT_URL . ADMIN_PATH . '/login.php', 'out', '15');
        } else {
            $hbdata->hbdata_msg($_LANG['login_password_reset_fail'], ROOT_URL . ADMIN_PATH . '/login.php', 'out', '15');
        }
    }
}
?>