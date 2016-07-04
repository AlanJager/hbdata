<?php
define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'guestbook');

/**
 * +----------------------------------------------------------
 * 留言列表
 * +----------------------------------------------------------
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['guestbook']);

    // SQL查询条件
    $where = " WHERE reply_id = '0'";

    // 验证并获取合法的分页ID
    $page = $check->is_number($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $limit = $hbdata->pager('guestbook', 15, $page, 'guestbook.php', $where);

    $sql = "SELECT id, title, name, contact_type, contact, if_show, if_read, ip, add_time FROM " . $hbdata->table('guestbook') . $where . " ORDER BY id DESC" . $limit;
    $query = $hbdata->query($sql);
    while ($row = $hbdata->fetch_array($query)) {
        $if_show = $row['if_show'] ? $_LANG['display'] : $_LANG['hidden'];
        $add_time = date("Y-m-d", $row[add_time]);

        $book_list[] = array (
            "id" => $row['id'],
            "title" => $row['title'],
            "name" => $row['name'],
            "contact_type" => $row['contact_type'],
            "contact" => $row['contact'],
            "if_show" => $if_show,
            "if_read" => $row['if_read'],
            "ip" => $row['ip'],
            "add_time" => $add_time
        );
    }

    $smarty->assign('book_list', $book_list);
    $smarty->display('guestbook.htm');
}

/**
 * +----------------------------------------------------------
 * 留言查看
 * +----------------------------------------------------------
 */
elseif ($rec == 'read') {
    $smarty->assign('ur_here', $_LANG['guestbook_read']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['guestbook_list'],
        'href' => 'guestbook.php'
    ));

    $id = trim($_REQUEST['id']);

    // 获取留言信息
    $query = $hbdata->select($hbdata->table(guestbook), '*', '`id` = \'' . $id . '\'');
    $guestbook = $hbdata->fetch_array($query);
    $guestbook['add_time'] = date("Y-m-d", $guestbook['add_time']);

    // 获取管理员回复
    $sql = "SELECT content, add_time FROM " . $hbdata->table('guestbook') . " WHERE reply_id = '$id'";
    $query = $hbdata->query($sql);
    $reply = $hbdata->fetch_array($query);
    $reply['add_time'] = date("Y-m-d", $reply['add_time']);

    // 将留言信息更新为已读
    $read = "UPDATE " . $hbdata->table('guestbook') . " SET if_read = '1' WHERE id = '$id'";
    $hbdata->query($read);

    $smarty->assign('guestbook', $guestbook);
    $smarty->assign('reply', $reply);
    $smarty->display('guestbook.htm');
}

/**
 * +----------------------------------------------------------
 * 留言回复
 * +----------------------------------------------------------
 */
elseif ($rec == 'reply') {
    $name = time();
    $ip = $hbdata->get_ip();
    $add_time = time();

    $sql = "INSERT INTO " . $hbdata->table('guestbook') . " (id, name, content, ip, add_time, reply_id)" .
        " VALUES (NULL, '$_USER[user_name]', '$_POST[content]', '$ip', '$add_time', '$_POST[id]')";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['guestbook_reply'] . ': ' . $_POST[title]);

    $hbdata->hbdata_msg($_LANG['guestbook_insert_success'], 'guestbook.php');
}

/**
 * +----------------------------------------------------------
 * 显示或隐藏
 * +----------------------------------------------------------
 */
elseif ($rec == 'show_hidden') {
    $id = trim($_REQUEST['id']);
    $if_show = $hbdata->get_one("SELECT if_show FROM " . $hbdata->table('guestbook') . " WHERE id = '$id'");
    $if_show = $if_show ? 0 : 1;

    // 更新留言信息显示状态
    $read = "UPDATE " . $hbdata->table('guestbook') . " SET if_show = '$if_show' WHERE id = '$id'";
    $hbdata->query($read);

    echo "<em class=" . ($if_show ? 'd' : 'h') . "><b>$_LANG[display]</b><s>$_LANG[hidden]</s></em>";
}

/**
 * +----------------------------------------------------------
 * 批量留言删除
 * +----------------------------------------------------------
 */
elseif ($rec == 'del_all') {
    if (is_array($_POST['checkbox'])) {
        $checkbox = $hbdata->create_sql_in($_POST['checkbox']);

        // 删除留言
        $sql = "DELETE FROM " . $hbdata->table('guestbook') . " WHERE id " . $checkbox;
        $hbdata->query($sql);

        $hbdata->create_admin_log($_LANG['guestbook_del'] . ": GUESTBOOK " . addslashes($checkbox));
        $hbdata->hbdata_msg($_LANG['del_succes'], 'guestbook.php');
    } else {
        $hbdata->hbdata_msg($_LANG['guestbook_select_empty'], 'guestbook.php');
    }
}
?>