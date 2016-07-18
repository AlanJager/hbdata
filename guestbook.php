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

$sub = 'insert|del';
$subbox = array(
    "module" => 'guestbook',
    "sub" => $sub
);

require (dirname(__FILE__) . '/include/init.php');

// 开启SESSION
session_start();

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

/**
 * 留言板
 */
if ($rec == 'default') {
    // SQL查询条件
    $where = " WHERE if_show = '1'";

    // 获取分页信息
    $page = $check->is_number($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $limit = $hbdata->pager('guestbook', 10, $page, $hbdata->rewrite_url('guestbook'), $where);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('guestbook'));

    $sql = "SELECT * FROM " . $GLOBALS['hbdata']->table('guestbook') . $where . " ORDER BY id DESC" . $limit;
    $query = $GLOBALS['hbdata']->query($sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
        $add_time = date("Y-m-d", $row['add_time']);

        // 获取管理员回复
        $reply = "SELECT content, add_time FROM " . $hbdata->table('guestbook') . " WHERE reply_id = '$row[id]'";
        $reply = $hbdata->fetch_array($hbdata->query($reply));
        $reply_time = date("Y-m-d", $reply['add_time']);

        $guestbook[] = array (
            "id" => $row['id'],
            "title" => $row['title'],
            "name" => $row['name'],
            "content" => $row['content'],
            "add_time" => $add_time,
            "reply" => $reply['content'],
            "reply_time" => $reply_time
        );
    }

    // 初始化回复方式
    $contact_type = array ('email', 'tel', 'qq');
    foreach ($contact_type as $value) {
        $selected = ($value == $post['contact_type']) ? ' selected="selected"' : '';
        $option .= "<option value=" . $value . $selected . ">" . $_LANG['guestbook_' . $value] . "</option>";
    }

    // 赋值给模板-meta和title信息
    $smarty->assign('page_title', $hbdata->page_title('guestbook'));
    $smarty->assign('keywords', $_CFG['site_keywords']);
    $smarty->assign('description', $_CFG['site_description']);

    // 赋值给模板-导航栏
    $smarty->assign('nav_top_list', $hbdata->get_nav('top'));
    $smarty->assign('nav_middle_list', $hbdata->get_nav('middle', 0, 'guestbook', 0));
    $smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

    // 赋值给模板-数据
    $smarty->assign('rec', $rec);
    $smarty->assign('insert_url', $_URL['insert']);
    $smarty->assign('option', $option);
    $smarty->assign('guestbook', $guestbook);
    $smarty->assign('ur_here', $hbdata->ur_here('guestbook'));

    $smarty->display('guestbook.dwt');
}

/**
 * 留言添加
 */
if ($rec == 'insert') {
    $ip = $hbdata->get_ip();
    $add_time = time();
    $captcha = $check->is_captcha($_POST['captcha']) ? strtoupper($_POST['captcha']) : '';

    // 如果限制必须输入中文则修改错误提示
    $include_chinese = $_CFG['guestbook_check_chinese'] ? $_LANG['guestbook_include_chinese'] : '';

    // 验证主题
    if ($check->is_illegal_char($_POST['title'])) {
        $wrong['title'] = $_LANG['guestbook_title'] . $_LANG['illegal_char'];
    } elseif (!check_guestbook($_POST['title'], 200)) {
        $wrong['title'] = preg_replace('/d%/Ums', $include_chinese, $_LANG['guestbook_title_wrong']);
    }

    // 验证联系人
    if ($check->is_illegal_char($_POST['name'])) {
        $wrong['name'] = $_LANG['guestbook_name'] . $_LANG['illegal_char'];
    } elseif (!check_guestbook($_POST['name'], 200)) {
        $wrong['name'] = preg_replace('/d%/Ums', $include_chinese, $_LANG['guestbook_name_wrong']);
    }

    // 验证回复方式
    if (empty($_POST['contact_type'])) {
        $wrong['contact'] = $_LANG['guestbook_contact_type_empty'];
    } elseif ($_POST['contact_type'] == 'email') {
        if (!$check->is_email($_POST['contact']))
            $wrong['contact'] = $_LANG['guestbook_email_wrong'];
    } elseif ($_POST['contact_type'] == 'tel') {
        if (!$check->is_telphone($_POST['contact']))
            $wrong['contact'] = $_LANG['guestbook_tel_wrong'];
    } elseif ($_POST['contact_type'] == 'qq') {
        if (!$check->is_qq($_POST['contact']))
            $wrong['contact'] = $_LANG['guestbook_qq_wrong'];
    }

    // 验证留言内容
    if ($check->is_illegal_char($_POST['content'])) {
        $wrong['content'] = $_LANG['guestbook_content'] . $_LANG['illegal_char'];
    } elseif (!check_guestbook($_POST['content'], 300)) {
        $wrong['content'] = preg_replace('/d%/Ums', $include_chinese, $_LANG['guestbook_content_wrong']);
    }

    // 判断验证码
    if ($_CFG['captcha'] && md5($captcha . HBDATA_SHELL) != $_SESSION['captcha'])
        $wrong['captcha'] = $_LANG['captcha_wrong'];

    // AJAX验证表单
    if ($_REQUEST['do'] == 'callback') {
        if ($wrong) {
            foreach ($_POST as $key => $value) {
                $wrong_json[$key] = $wrong[$key];
            }
            echo json_encode($wrong_json);
        }
        exit;
    }

    // 检查IP是否频繁留言
    if (is_water($ip))
        $hbdata->hbdata_msg($_LANG['guestbook_is_water'], $_URL['guestbook']);

    if ($wrong) {
        foreach ($wrong as $key => $value) {
            $wrong_format .= $wrong[$key] . '<br>';
        }
        $hbdata->hbdata_msg($wrong_format, $_URL['guestbook']);
    }

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'guestbook');

    // 安全处理用户输入信息
    $_POST = $firewall->hbdata_foreground($_POST);

    $sql = "INSERT INTO " . $hbdata->table('guestbook') . " (id, title, name, contact_type, contact, content, ip, add_time)" . " VALUES (NULL, '$_POST[title]', '$_POST[name]', '$_POST[contact_type]', '$_POST[contact]', '$_POST[content]', '$ip', '$add_time')";
    $hbdata->query($sql);

    $hbdata->hbdata_msg($_LANG['guestbook_insert_success'], $_URL['guestbook']);
}

/**
 * 防灌水
 * @param $ip
 * @return bool
 */
function is_water($ip) {
    $unread_messages = $GLOBALS['hbdata']->get_one("SELECT COUNT(*) FROM " . $GLOBALS['hbdata']->table('guestbook') . " WHERE ip = '$ip' AND if_read = 0 AND reply_id = 0");

    // 如果管理员未回复的留言数量大于3
    if ($unread_messages >= '3')
        return true;
}

/**
 * 检查是否包含中文字符且长度符合要求
 * @param $value
 * @param $length
 * @return bool
 */
function check_guestbook($value, $length) {
    if ($GLOBALS['check']->ch_length($value, $length)) {
        return true;
    }
}
?>