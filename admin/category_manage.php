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

////获得module
//$module = $_REQUEST['module'];
//
////检测是否为正确的module。
////如果错误，则返回404
//if (!$check->is_module($module, $hbdata->read_system())){
//   echo '404';die;
//}
//
////rec 操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

//赋值给模板
$smarty->assign('rec', $rec);
//$smarty->assign('module', $module);
//$smarty->assign('cur', $module.'_category');





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
    $category_name=$_POST['category_name'];
    echo $category_name;
/*    if (empty($_POST['category_name']))
        $hbdata->hbdata_msg($_LANG['category_name'] . $_LANG['is_empty']);
    if (empty($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique'] . $_LANG['is_empty']);
    if (!$check->is_unique_id($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_wrong']);

    if ($check->unique_id_exist($_POST['unique_id'], $_MODULE['column']))
        $hbdata->hbdata_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'category_add');

    //$hbdata->add_module($_POST['category_name'], $_POST['unique_id']);

    $hbdata->create_admin_log($_LANG['category_add'] . ': ' . $_POST['unique_id']);//need to fix
    $hbdata->hbdata_msg($_LANG['category_add_succes'], 'category_manage.php');//need to fix*/
    //$hbdata->edit_module($category_name,'add');
    $hbdata->create_table($category_name);
    $hbdata->hbdata_msg($_LANG['category_add_succes'], 'category_manage.php');//need to fix*/
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
    if (empty($_POST['category_name']))
        $hbdata->hbdata_msg($_LANG['category_name'] . $_LANG['is_empty']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'category_edit');

    $hbdata->create_admin_log($_LANG['category_edit'] . ': ' . $_POST['unique_id']);
    $hbdata->hbdata_msg($_LANG['category_edit_succes'], 'category_manage.php');

}

/**
 *分类删除
 */
if($rec == 'del'){

//    $category_id = $check->is_number($_REQUEST['category_id']) ? $_REQUEST['category_id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'category_manage.php');
//    $category_name = $hbdata->get_one("SELECT category_name FROM " . $hbdata->table('category_manage') . " WHERE category_id = '$category_id'");
    //del_module();

//    $hbdata->create_admin_log($_LANG['category_del'] . ': ' . $_REQUEST['category_unique_id']);
//    $hbdata->hbdata_msg($_LANG['del_check'], 'article_category.php', '', '30', "article_category.php?rec=del&cat_id=$cat_id");
//    $hbdata->delete($hbdata->table('article_category'), "cat_id = $cat_id", 'article_category.php');

}


?>
