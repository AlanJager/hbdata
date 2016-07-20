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
require (ROOT_PATH  . 'admin/include/PhpRbac/autoload.php');
$rbac = new PhpRbac\Rbac();

//权限判断
require ('auth.php');
//获得name
$name = $_REQUEST['name'];
// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

$smarty->assign('rec', $rec);
$smarty->assign('name', $name);
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
    
    //脚本
    /*$id = $rbac->Permissions->titleId('admin/system.php');
    $rbac->Permissions->add('admin/system.php?manage', '查看系统设置', $id);
    $id = $rbac->Permissions->titleId('admin/nav.php');
    $rbac->Permissions->add('admin/nav.php?manage', '查看导航栏', $id);
    $id = $rbac->Permissions->titleId('admin/show.php');
    $rbac->Permissions->add('admin/show.php?manage', '查看幻灯广告', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=about');
    $rbac->Permissions->add('admin/page.php?name=about&manage', '查看公司简介', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=honor');
    $rbac->Permissions->add('admin/page.php?name=honor&manage', '查看企业荣誉', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=history');
    $rbac->Permissions->add('admin/page.php?name=history&manage', '查看发展历程', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=contact');
    $rbac->Permissions->add('admin/page.php?name=contact&manage', '查看联系我们', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=job');
    $rbac->Permissions->add('admin/page.php?name=job&manage', '查看人才招聘', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=market');
    $rbac->Permissions->add('admin/page.php?name=market&manage', '查看营销网络', $id);
    $id = $rbac->Permissions->titleId('admin/category_manage.php');
    $rbac->Permissions->add('admin/category_manage.php?manage', '查看分类管理', $id);

    $id = $rbac->Permissions->titleId('admin/backup.php');
    $rbac->Permissions->add('admin/backup.php?manage', '查看备份情况', $id);
    $id = $rbac->Permissions->titleId('admin/theme.php');
    $rbac->Permissions->add('admin/theme.php?manage', '查看模板', $id);
    $id = $rbac->Permissions->titleId('admin/manager.php');
    $rbac->Permissions->add('admin/manager.php?manage', '查看用户', $id);
    $id = $rbac->Permissions->titleId('admin/role.php');
    $rbac->Permissions->add('admin/role.php?manage', '查看角色', $id);
    $id = $rbac->Permissions->titleId('admin/guestbook.php');
    $rbac->Permissions->add('admin/guestbook.php?manage', '查看留言', $id);
    $id = $rbac->Permissions->titleId('admin/item_category.php?module=product');
    $rbac->Permissions->add('admin/item_category.php?module=product&manage', '查看商品分类', $id);
    $id = $rbac->Permissions->titleId('admin/item_category.php?module=article');
    $rbac->Permissions->add('admin/item_category.php?module=article&manage', '查看文章分类', $id);
    $id = $rbac->Permissions->titleId('admin/item.php?module=product');
    $rbac->Permissions->add('admin/item.php?module=product&manage', '查看商品', $id);
    $id = $rbac->Permissions->titleId('admin/item.php?module=article');
    $rbac->Permissions->add('admin/item.php?module=article&manage', '查看文章', $id);

    $id = $rbac->Permissions->titleId('admin/page.php?rec=update');
    $rbac->Permissions->remove($id, false);
    $id = $rbac->Permissions->titleId('admin/page.php?name=about&manage');
    $rbac->Permissions->add('admin/page.php?name=about&rec=update', '更新公司简介', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=honor&manage');
    $rbac->Permissions->add('admin/page.php?name=honor&rec=update', '更新企业荣誉', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=history&manage');
    $rbac->Permissions->add('admin/page.php?name=history&rec=update', '更新发展历程', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=contact&manage');
    $rbac->Permissions->add('admin/page.php?name=contact&rec=update', '更新联系我们', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=job&manage');
    $rbac->Permissions->add('admin/page.php?name=job&rec=update', '更新人才招聘', $id);
    $id = $rbac->Permissions->titleId('admin/page.php?name=market&manage');
    $rbac->Permissions->add('admin/page.php?name=market&rec=update', '更新营销网络', $id);*/

    //获得用户权限内的页面
    $user = $_USER['user_id'];
    $can_empty = $rbac->check('admin/page.php?manage', $user);
    $page_perm = $hbdata->getUserPageInPerm($user);
    
    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('page_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('page_perm', $page_perm);
    $smarty->assign('can_empty', $can_empty);
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

    $sql = "INSERT INTO " . $hbdata->table('page') . " (id, unique_id, parent_id, page_name, content ,keywords, description,sort)" . " VALUES (NULL, '$_POST[unique_id]', '$_POST[parent_id]', '$_POST[page_name]', '$_POST[content]', '$_POST[keywords]', '$_POST[description]', '$_POST[sort]')";
    $hbdata->query($sql);
    $hbdata->add_page_access($_POST['parent_id'], $_POST['unique_id'], $_POST['page_name']);

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

    //获得用户权限内的页面
    $user = $_USER['user_id'];
    $can_empty = $rbac->check('admin/page.php?manage', $user);
    $page_perm = $hbdata->getUserPageInPerm($user);

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';

    $query = $hbdata->select($hbdata->table('page'), '*', '`id` = \'' . $id . '\'');
    $page = $hbdata->fetch_array($query);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('page_edit'));

    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign('page_perm', $page_perm);
    $smarty->assign('can_empty', $can_empty);
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

    $sql = "UPDATE " . $hbdata->table('page') . " SET unique_id = '$_POST[unique_id]', parent_id = '$_POST[parent_id]', page_name = '$_POST[page_name]', content = '$_POST[content]', keywords = '$_POST[keywords]', description = '$_POST[description]',sort = '$_POST[sort]' WHERE id = '$_POST[id]'";
    $hbdata->query($sql);
    $hbdata->del_page_access($_POST['old_unique_id']);
    $hbdata->add_page_access($_POST['parent_id'], $_POST['unique_id'], $_POST['page_name']);

    $hbdata->create_admin_log($_LANG['page_edit'] . ': ' . $_POST['page_name']);
    $hbdata->hbdata_msg($_LANG['page_edit_succes'], 'page.php?name='.$_REQUEST['name'], '', '3');
}

/**
 * 单页面删除
 */
elseif ($rec == 'del') {
    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'page.php');

    $page_name = $hbdata->get_one("SELECT page_name FROM " . $hbdata->table('page') . " WHERE id = '$id'");
    $unique_id = $hbdata->get_one("SELECT unique_id FROM ".$hbdata->table('page')."WHERE id = '$id'");
    $is_parent = $hbdata->get_one("SELECT id FROM " . $hbdata->table('page') . " WHERE parent_id = '$id'");

    if ($id == '1') {
        $hbdata->hbdata_msg($_LANG['page_del_wrong'], 'page.php?name='.$name, '', '3');
    } elseif ($is_parent) {
        $_LANG['page_del_is_parent'] = preg_replace('/d%/Ums', $page_name, $_LANG['page_del_is_parent']);
        $hbdata->hbdata_msg($_LANG['page_del_is_parent'], 'page.php?name='.$name, '', '3');
    } else {
        if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
            $hbdata->create_admin_log($_LANG['page_del'] . ': ' . $page_name);
            $hbdata->del_page_access($name);
            $hbdata->delete($hbdata->table('page'), "id = $id", 'page.php');
        } else {
            $_LANG['del_check'] = preg_replace('/d%/Ums', $page_name, $_LANG['del_check']);
            $hbdata->hbdata_msg($_LANG['del_check'], 'page.php?name='.$name, '', '30', "page.php?name=$name&rec=del&id=$id");
        }
    }
}

?>
