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

//获得module
$module = $_REQUEST['module'];

//检测是否为正确的module。
//如果错误，则返回404
if (!$check->is_module($module, $hbdata->read_system())){
    echo '404';die;
}

//rec 操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

//赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('module', $module);
$smarty->assign('cur', $module.'_category');





/**
 * 分类列表
 */
if ($rec == 'default'){

    $smarty->assign('ur_here', $_LANG[$module.'_category']);
    $smarty->assign('action_link', array (
        'text' => $_LANG[$module.'_category_add'],
        'href' => 'item_category.php?module='.$module.'&rec=add'
    ));

    // 赋值给模板
    $smarty->assign('item_category', $hbdata->get_category_nolevel($module , 'category'));

    $smarty->display('item_category.htm');

}

/**
 *分类添加
 */
if ($rec == 'add'){

    $smarty->assign('ur_here', $_LANG[$module.'_category_add']);
    $smarty->assign('action_link', array (
        'text' => $_LANG[$module.'_category'],
        'href' => 'item_category.php?module='.$module
    ));

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token($module.'_category_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('item_category', $hbdata->get_category_nolevel($module , 'category'));

    $smarty->display('item_category.htm');

}

/**
 *分类插入
 */
if ($rec == 'insert'){

    if (empty($_POST['cat_name']))
        $hbdata->hbdata_msg($_LANG[$module.'_category_name'] . $_LANG['is_empty']);

    if (!$check->is_unique_id($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_wrong']);

    if ($hbdata->value_exist('item_category', 'unique_id', $_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], $module.'_category_add');

    $sql = "INSERT INTO " . $hbdata->table('category') . " (cat_id, unique_id, parent_id, cat_name, keywords, description, sort, category)" . " VALUES (NULL, '$_POST[unique_id]', '$_POST[parent_id]', '$_POST[cat_name]', '$_POST[keywords]', '$_POST[description]', '$_POST[sort]', '$module')";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG[$module.'_category_add'] . ': ' . $_POST['cat_name']);
    $hbdata->hbdata_msg($_LANG[$module.'_category_add_succes'], 'item_category.php?module='.$module);

}

/**
 *分类编辑
 */
if ($rec == 'edit'){

    $smarty->assign('ur_here', $_LANG[$module.'_category_edit']);
    $smarty->assign('action_link', array (
        'text' => $_LANG[$module.'_category'],
        'href' => 'item_category.php?module='.$module
    ));

    // 获取分类信息
    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : '';
    $query = $hbdata->select($hbdata->table('category'), '*', '`cat_id` = \'' . $cat_id . '\'');
    $cat_info = $hbdata->fetch_array($query);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token($module.'_category_edit'));

    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign($module.'_category', $hbdata->get_category_nolevel($module , 'category', '0', '0', $cat_id));
    $smarty->assign('cat_info', $cat_info);

    $smarty->display('item_category.htm');

}

/**
 *分类更新
 */
if($rec == 'update'){

    if (empty($_POST['cat_name']))
        $hbdata->hbdata_msg($_LANG[$module.'_category_name'] . $_LANG['is_empty']);

    if (!$check->is_unique_id($_POST['unique_id']))
        $hbdata->hbdata_msg($_LANG['unique_id_wrong']);

    if ($hbdata->value_exist($module.'_category', 'unique_id', $_POST['unique_id'], "AND cat_id != '$_POST[cat_id]'"))
        $hbdata->hbdata_msg($_LANG['unique_id_existed']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], $module.'_category_edit');

    $sql = "update " . $hbdata->table('category') . " SET cat_name = '$_POST[cat_name]', unique_id = '$_POST[unique_id]', parent_id = '$_POST[parent_id]', keywords = '$_POST[keywords]' ,description = '$_POST[description]', sort = '$_POST[sort]' WHERE cat_id = '$_POST[cat_id]'";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG[$module.'_category_edit'] . ': ' . $_POST['cat_name']);
    $hbdata->hbdata_msg($_LANG[$module.'_category_edit_succes'], 'item_category.php?module='.$module);

}

/**
 *分类删除
 */
if($rec == 'del'){

    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'item_category.php?module='.$module);
    $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table($module.'_category') . " WHERE cat_id = '$cat_id'");
    $is_parent = $hbdata->get_one("SELECT id FROM " . $hbdata->table($module) . " WHERE cat_id = '$cat_id'") . $hbdata->get_one("SELECT cat_id FROM " . $hbdata->table($module.'_category') . " WHERE parent_id = '$cat_id'");

    if ($is_parent) {
        $_LANG[$module.'_category_del_is_parent'] = preg_replace('/d%/Ums', $cat_name, $_LANG[$module.'_category_del_is_parent']);
        $hbdata->hbdata_msg($_LANG[$module.'_category_del_is_parent'], 'item_category.php?module='.$module, '', '3');
    } else {
        if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
            $hbdata->create_admin_log($_LANG[$module.'_category_del'] . ': ' . $cat_name);
            $hbdata->delete($hbdata->table('category'), "cat_id = $cat_id", 'item_category.php?module='.$module);
        } else {
            $_LANG['del_check'] = preg_replace('/d%/Ums', $cat_name, $_LANG['del_check']);
            $hbdata->hbdata_msg($_LANG['del_check'], 'item_category.php?module='.$module, '', '30', "item_category.php?module=".$module."&rec=del&cat_id=$cat_id");
        }
    }

}


?>
