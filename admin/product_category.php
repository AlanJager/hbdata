<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: anewnoob
 * Release Date: 2016-7-4
 */
define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'article_category');

/**
 * 分类列表
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['article_category']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article_category_add'],
        'href' => 'article_category.php?rec=add'
    ));

    // 赋值给模板
    $smarty->assign('article_category', $dou->get_category_nolevel('article_category'));

    $smarty->display('article_category.htm');
}

/**
 * 分类添加
 */
if ($rec == 'add') {
    $smarty->assign('ur_here', $_LANG['article_category_add']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article_category'],
        'href' => 'article_category.php'
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('article_category_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('article_category', $dou->get_category_nolevel('article_category'));

    $smarty->display('article_category.htm');
}

/**
 * 分类插入
 */
if ($rec == 'insert') {
    if (empty($_POST['cat_name']))
        $dou->dou_msg($_LANG['article_category_name'] . $_LANG['is_empty']);

    if (!$check->is_unique_id($_POST['unique_id']))
        $dou->dou_msg($_LANG['unique_id_wrong']);

    if ($dou->value_exist('article_category', 'unique_id', $_POST['unique_id']))
        $dou->dou_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'article_category_add');

    $sql = "INSERT INTO " . $dou->table('article_category') . " (cat_id, unique_id, parent_id, cat_name, keywords, description, sort)" . " VALUES (NULL, '$_POST[unique_id]', '$_POST[parent_id]', '$_POST[cat_name]', '$_POST[keywords]', '$_POST[description]', '$_POST[sort]')";
    $dou->query($sql);

    $dou->create_admin_log($_LANG['article_category_add'] . ': ' . $_POST['cat_name']);
    $dou->dou_msg($_LANG['article_category_add_succes'], 'article_category.php');
}

/**
 * 分类编辑
 */
if ($rec == 'edit') {
    $smarty->assign('ur_here', $_LANG['article_category_edit']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article_category'],
        'href' => 'article_category.php'
    ));

    // 获取分类信息
    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : '';
    $query = $dou->select($dou->table('article_category'), '*', '`cat_id` = \'' . $cat_id . '\'');
    $cat_info = $dou->fetch_array($query);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('article_category_edit'));

    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign('article_category', $dou->get_category_nolevel('article_category', '0', '0', $cat_id));
    $smarty->assign('cat_info', $cat_info);

    $smarty->display('article_category.htm');
}

/**
 * 分类更新
 */
if ($rec == 'update') {
    if (empty($_POST['cat_name']))
        $dou->dou_msg($_LANG['article_category_name'] . $_LANG['is_empty']);

    if (!$check->is_unique_id($_POST['unique_id']))
        $dou->dou_msg($_LANG['unique_id_wrong']);

    if ($dou->value_exist('article_category', 'unique_id', $_POST['unique_id'], "AND cat_id != '$_POST[cat_id]'"))
        $dou->dou_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'article_category_edit');

    $sql = "update " . $dou->table('article_category') . " SET cat_name = '$_POST[cat_name]', unique_id = '$_POST[unique_id]', parent_id = '$_POST[parent_id]', keywords = '$_POST[keywords]' ,description = '$_POST[description]', sort = '$_POST[sort]' WHERE cat_id = '$_POST[cat_id]'";
    $dou->query($sql);

    $dou->create_admin_log($_LANG['article_category_edit'] . ': ' . $_POST['cat_name']);
    $dou->dou_msg($_LANG['article_category_edit_succes'], 'article_category.php');
}

/**
 * 分类删除
 */
if ($rec == 'del') {
    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : $dou->dou_msg($_LANG['illegal'], 'article_category.php');
    $cat_name = $dou->get_one("SELECT cat_name FROM " . $dou->table('article_category') . " WHERE cat_id = '$cat_id'");
    $is_parent = $dou->get_one("SELECT id FROM " . $dou->table('article') . " WHERE cat_id = '$cat_id'") . $dou->get_one("SELECT cat_id FROM " . $dou->table('article_category') . " WHERE parent_id = '$cat_id'");

    if ($is_parent) {
        $_LANG['article_category_del_is_parent'] = preg_replace('/d%/Ums', $cat_name, $_LANG['article_category_del_is_parent']);
        $dou->dou_msg($_LANG['article_category_del_is_parent'], 'article_category.php', '', '3');
    } else {
        if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
            $dou->create_admin_log($_LANG['article_category_del'] . ': ' . $cat_name);
            $dou->delete($dou->table('article_category'), "cat_id = $cat_id", 'article_category.php');
        } else {
            $_LANG['del_check'] = preg_replace('/d%/Ums', $cat_name, $_LANG['del_check']);
            $dou->dou_msg($_LANG['del_check'], 'article_category.php', '', '30', "article_category.php?rec=del&cat_id=$cat_id");
        }
    }
}

?>
