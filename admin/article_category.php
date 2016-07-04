/**
* hbData
* --------------------------------------------------------------------------------------------------
* 版权所有 2016-
* 网站地址:
* --------------------------------------------------------------------------------------------------
* Author: Firery
* Release Date: 2016-7-4
*/


<?php

define('IN_HBDATA', true);

require (dirname(__FILE__).'/include/init.php');

//rec 操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

//赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'article_category');

if ($rec == 'default'){
    /**
     * 分类列表
     */

    $smarty->assign('ur_here', $_LANG['article_category']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article_category_add'],
        'href' => 'article_category.php?rec=add'
    ));

    // 赋值给模板
    $smarty->assign('article_category', $hbdata->get_category_nolevel('article_category'));

    $smarty->display('article_category.htm');

} elseif ($rec == 'add'){
    /**
     *分类添加
     */
    $smarty->assign('ur_here', $_LANG['article_category_add']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article_category'],
        'href' => 'article_category.php'
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('article_category_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('article_category', $hbdata->get_category_nolevel('article_category'));

    $smarty->display('article_category.htm');

} elseif ($rec == 'insert'){
    /**
     *
     */
    if (empty($_POST['cat_name']))
        $hbdata->hbdata_msg($_LANG['article_category_name'] . $_LANG['is_empty']);

    if (!$check->is_unique_id($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_wrong']);

    if ($hbdata->value_exist('article_category', 'unique_id', $_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'article_category_add');

    $sql = "INSERT INTO " . $hbdata->table('article_category') . " (cat_id, unique_id, parent_id, cat_name, keywords, description, sort)" . " VALUES (NULL, '$_POST[unique_id]', '$_POST[parent_id]', '$_POST[cat_name]', '$_POST[keywords]', '$_POST[description]', '$_POST[sort]')";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['article_category_add'] . ': ' . $_POST['cat_name']);
    $hbdata->hbdata_msg($_LANG['article_category_add_succes'], 'article_category.php');

} elseif ($rec == 'edit'){
    /**
     *分类编辑
     */
    $smarty->assign('ur_here', $_LANG['article_category_edit']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article_category'],
        'href' => 'article_category.php'
    ));

    // 获取分类信息
    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : '';
    $query = $hbdata->select($hbdata->table('article_category'), '*', '`cat_id` = \'' . $cat_id . '\'');
    $cat_info = $hbdata->fetch_array($query);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('article_category_edit'));

    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign('article_category', $hbdata->get_category_nolevel('article_category', '0', '0', $cat_id));
    $smarty->assign('cat_info', $cat_info);

    $smarty->display('article_category.htm');

} elseif($rec == 'update'){
    /**
     *
     */
    if (empty($_POST['cat_name']))
        $hbdata->hbdata_msg($_LANG['article_category_name'] . $_LANG['is_empty']);

    if (!$check->is_unique_id($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_wrong']);

    if ($hbdata->value_exist('article_category', 'unique_id', $_POST['unique_id'], "AND cat_id != '$_POST[cat_id]'"))
        $hbdata->hbdata_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'article_category_edit');

    $sql = "update " . $hbdata->table('article_category') . " SET cat_name = '$_POST[cat_name]', unique_id = '$_POST[unique_id]', parent_id = '$_POST[parent_id]', keywords = '$_POST[keywords]' ,description = '$_POST[description]', sort = '$_POST[sort]' WHERE cat_id = '$_POST[cat_id]'";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['article_category_edit'] . ': ' . $_POST['cat_name']);
    $hbdata->hbdata_msg($_LANG['article_category_edit_succes'], 'article_category.php');

} elseif($rec == 'del'){
    /**
     *分类删除
     */
    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'article_category.php');
    $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table('article_category') . " WHERE cat_id = '$cat_id'");
    $is_parent = $hbdata->get_one("SELECT id FROM " . $hbdata->table('article') . " WHERE cat_id = '$cat_id'") . $hbdata->get_one("SELECT cat_id FROM " . $hbdata->table('article_category') . " WHERE parent_id = '$cat_id'");

    if ($is_parent) {
        $_LANG['article_category_del_is_parent'] = preg_replace('/d%/Ums', $cat_name, $_LANG['article_category_del_is_parent']);
        $hbdata->hbdata_msg($_LANG['article_category_del_is_parent'], 'article_category.php', '', '3');
    } else {
        if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
            $hbdata->create_admin_log($_LANG['article_category_del'] . ': ' . $cat_name);
            $hbdata->delete($hbdata->table('article_category'), "cat_id = $cat_id", 'article_category.php');
        } else {
            $_LANG['del_check'] = preg_replace('/d%/Ums', $cat_name, $_LANG['del_check']);
            $hbdata->hbdata_msg($_LANG['del_check'], 'article_category.php', '', '30', "article_category.php?rec=del&cat_id=$cat_id");
        }
    }

}

?>