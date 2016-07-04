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

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

$smarty->assign('rec', $rec);
$smarty->assign('cur', 'page');

/**
 * 单页面列表
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['page_list']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['page_add'],
        'href' => 'page.php?rec=add'
    ));

    // 赋值给模板
    $smarty->assign('page_list', $hbdata->get_page_nolevel());

    $smarty->display('page.htm');
}

/**
 * 单页面添加
 */
elseif ($rec == 'add') {
    $smarty->assign('ur_here', $_LANG['page_add']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['page_list'],
        'href' => 'page.php'
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('page_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('page_list', $hbdata->get_page_nolevel());

    $smarty->display('page.htm');
}

elseif ($rec == 'insert') {
    if (empty($_POST['page_name']))
        $hbdata->hbdata_msg($_LANG['page_name'] . $_LANG['is_empty']);

    if (!$check->is_unique_id($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_wrong']);

    if ($hbdata->value_exist('page', 'unique_id', $_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'page_add');

    $sql = "INSERT INTO " . $hbdata->table('page') . " (id, unique_id, parent_id, page_name, content ,keywords, description)" . " VALUES (NULL, '$_POST[unique_id]', '$_POST[parent_id]', '$_POST[page_name]', '$_POST[content]', '$_POST[keywords]', '$_POST[description]')";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['page_add'] . ': ' . $_POST[page_name]);
    $hbdata->hbdata_msg($_LANG['page_add_succes'], 'page.php');
}

/**
 * 单页面编辑
 */
elseif ($rec == 'edit') {
    $smarty->assign('ur_here', $_LANG['page_edit']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['page_list'],
        'href' => 'page.php'
    ));

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';

    $query = $hbdata->select($hbdata->table('page'), '*', '`id` = \'' . $id . '\'');
    $page = $hbdata->fetch_array($query);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('page_edit'));

    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign('page_list', $hbdata->get_page_nolevel(0, 0, $id));
    $smarty->assign('page', $page);

    $smarty->display('page.htm');
}

elseif ($rec == 'update') {
    if (empty($_POST['page_name']))
        $hbdata->hbdata_msg($_LANG['page_name'] . $_LANG['is_empty']);

    if (!$check->is_unique_id($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_wrong']);

    if ($hbdata->value_exist('page', 'unique_id', $_POST['unique_id'], "AND id != '$_POST[id]'"))
        $hbdata->hbdata_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'page_edit');

    $sql = "UPDATE " . $hbdata->table('page') . " SET unique_id = '$_POST[unique_id]', parent_id = '$_POST[parent_id]', page_name = '$_POST[page_name]', content = '$_POST[content]', keywords = '$_POST[keywords]', description = '$_POST[description]' WHERE id = '$_POST[id]'";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['page_edit'] . ': ' . $_POST['page_name']);
    $hbdata->hbdata_msg($_LANG['page_edit_succes'], 'page.php', '', '3');
}

/**
 * 单页面删除
 */
elseif ($rec == 'del') {
    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'page.php');

    $page_name = $hbdata->get_one("SELECT page_name FROM " . $hbdata->table('page') . " WHERE id = '$id'");
    $is_parent = $hbdata->get_one("SELECT id FROM " . $hbdata->table('page') . " WHERE parent_id = '$id'");

    if ($id == '1') {
        $hbdata->hbdata_msg($_LANG['page_del_wrong'], 'page.php', '', '3');
    } elseif ($is_parent) {
        $_LANG['page_del_is_parent'] = preg_replace('/d%/Ums', $page_name, $_LANG['page_del_is_parent']);
        $hbdata->hbdata_msg($_LANG['page_del_is_parent'], 'page.php', '', '3');
    } else {
        if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
            $hbdata->create_admin_log($_LANG['page_del'] . ': ' . $page_name);
            $hbdata->delete($hbdata->table('page'), "id = $id", 'page.php');
        } else {
            $_LANG['del_check'] = preg_replace('/d%/Ums', $page_name, $_LANG['del_check']);
            $hbdata->hbdata_msg($_LANG['del_check'], 'page.php', '', '30', "page.php?rec=del&id=$id");
        }
    }
}
?>