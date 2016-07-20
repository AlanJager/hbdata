<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: 昊
 * Release Date: 2016-7-13
 */
define('IN_HBDATA', true);

require (dirname(__FILE__).'/include/init.php');

//权限判断
require ('auth.php');

$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

//赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'category_manage');





/**
 * 分类列表
 */
if ($rec == 'default'){
    $smarty->assign('ur_here', $_LANG['category']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['category_add'],
        'href' => 'category_manage.php?rec=add'
    ));

    // 赋值给模板
    $smarty->assign('category_manage', $_MODULE['column']);

    $smarty->display('category_manage.htm');

}

/**
 *分类添加
 */
if ($rec == 'add'){
    $smarty->assign('ur_here', $_LANG['category_add']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['category_list'],
        'href' => 'category_manage.php'
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('category_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('category_manage', $_MODULE['column']);

    $smarty->display('category_manage.htm');

}

/**
 *分类插入
 */
if ($rec == 'insert'){
    if (empty($_POST['category_name']))
        $hbdata->hbdata_msg($_LANG['category_name'] . $_LANG['is_empty']);
    if (empty($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique'] . $_LANG['is_empty']);
    if (!$check->is_unique_id($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_wrong']);
    if ($check->unique_id_exist($_POST['unique_id'], $_MODULE['column']))
        $hbdata->hbdata_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'category_add');

    $hbdata->edit_module($_POST['unique_id'],'add');
    $hbdata->add_module_access($_POST['unique_id'],$_POST['category_name']);
    $hbdata->add_category_lang($_POST['unique_id'],$_POST['category_name']);
    $hbdata->create_admin_log($_LANG['category_add'] . ': ' . $_POST['unique_id']);//need to fix
    $hbdata->hbdata_msg($_LANG['category_add_succes'], 'category_manage.php');//need to fix
}

/**
 *分类编辑
 */
if ($rec == 'edit'){
    $smarty->assign('ur_here', $_LANG['category_edit']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['category_list'],
        'href' => 'category_manage.php'
    ));
    // 获取分类信息
    $unique_id = $_REQUEST['category_unique_id'];
    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('category_edit'));
    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign('unique_id', $unique_id);
    $smarty->display('category_manage.htm');

}

/**
 *分类更新
 */
if($rec == 'update'){
    if (empty($_POST['old_category_name']))
        $hbdata->hbdata_msg($_LANG['old_category_name'] . $_LANG['is_empty']);
    if (empty($_POST['new_category_name']))
        $hbdata->hbdata_msg($_LANG['new_category_name'] . $_LANG['is_empty']);
    if (empty($_POST['old_unique_id']))
        $hbdata->hbdata_msg($_LANG['old_unique'] . $_LANG['is_empty']);
    if (empty($_POST['new_unique_id']))
        $hbdata->hbdata_msg($_LANG['new_unique'] . $_LANG['is_empty']);
    
    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'category_edit');
    $hbdata->edit_module($_POST['new_unique_id'],'alter',$_POST['old_unique_id']);
    $hbdata->add_module_access($_POST['new_unique_id'],$_POST['new_category_name']);
    $hbdata->add_category_lang($_POST['new_unique_id'],$_POST['new_category_name']);
    $hbdata->create_admin_log($_LANG['category_edit'] . ': ' . $_POST['new_unique_id']);
    $hbdata->hbdata_msg($_LANG['category_edit_succes'], 'category_manage.php');

}

/**
 *分类删除
 */
if($rec == 'del'){
    //验证并获取合法的别名
    $unique_id = $check->is_letter($_REQUEST['category_unique_id']) ? $_REQUEST['category_unique_id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'category_manage.php?module=' . $module);
    $module_name=$_LANG[$unique_id];
    if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
        $hbdata->edit_module($unique_id,"del");
        $hbdata->create_admin_log($_LANG['category_del'] . ': ' . $_POST['unique_id']);
        $hbdata->hbdata_msg($_LANG['category_del_succes'], 'category_manage.php');
    } else {
        $_LANG['del_check'] = preg_replace('/d%/Ums', $module_name, $_LANG['del_check']);
        $hbdata->hbdata_msg($_LANG['del_check'], 'category_manage.php', '', '30', "category_manage.php?rec=del&category_unique_id=$unique_id");
    }

}


?>
