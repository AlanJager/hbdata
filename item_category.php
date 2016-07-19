<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: Fiery
 * Release Date: 2016-7-15
 */

define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');

$module = $_REQUEST['module'];

//检测是否为正确的module。
//如果错误，则返回404
if (!$check->is_module($module, $hbdata->read_system())){
    $hbdata->hbdata_msg($_LANG['no_module'], 'index.php', 2);
}
// 验证并获取合法的ID，如果不合法将其设定为-1
$cat_id = $firewall->get_legal_id('category', $_REQUEST['id'], $_REQUEST['unique_id']);
if ($cat_id == -1) {
    $hbdata->hbdata_msg($GLOBALS['_LANG']['page_wrong'], ROOT_URL);
} else {
    $where = ' WHERE cat_id IN (' . $cat_id . $hbdata->hbdata_child_id('category', $module, $cat_id) . ')';
}

// 获取分页信息
$page = $check->is_number($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
$limit = $hbdata->pager($module, ($_DISPLAY[$module] ? $_DISPLAY[$module] : 10), $page, $hbdata->rewrite_category_url('item_category', $module , $cat_id), $where);

/* 获取列表 */
if ($module == 'product') {
    $sql = "SELECT id, cat_id, name, price, content, image, add_time, description, show_price FROM " . $hbdata->table('product') . $where . " ORDER BY id DESC" . $limit;

} else {
    $sql = "SELECT id, title, content, image, cat_id, add_time, click, description, sort FROM " . $hbdata->table($module) . $where . " ORDER BY sort ASC" . $limit;
}
$query = $hbdata->query($sql);

while ($row = $hbdata->fetch_array($query)) {
    $url = $hbdata->rewrite_category_url('item', $module, $row['id']);
    $add_time = date("Y-m-d", $row['add_time']);
    $add_time_short = date("m-d", $row['add_time']);
    $image = $row['image'] ? ROOT_URL . $row['image'] : '';

    // 如果描述不存在则自动从详细介绍中截取
    $description = $row['description'] ? $row['description'] : $hbdata->hbdata_substr($row['content'], 200, false);

    if ($module == 'product') {
        // 生成缩略图的文件名
        $image = explode(".", $row['image']);
        $thumb = ROOT_URL . $image[0] . "_thumb." . $image[1];

        // 格式化价格
        $price = $row['price'] > 0 ? $hbdata->price_format($row['price']) : $_LANG['price_discuss'];

        $item_list[] = array (
            "id" => $row['id'],
            "cat_id" => $row['cat_id'],
            "name" => $row['name'],
            "price" => $price,
            "thumb" => $thumb,
            "add_time" => $add_time,
            "description" => $description,
            "show_price" => $row['show_price'],
            "url" => $url
        );
    } else {
        $item_list[] = array (
            "id" => $row['id'],
            "cat_id" => $row['cat_id'],
            "title" => $row['title'],
            "image" => $image,
            "add_time" => $add_time,
            "add_time_short" => $add_time_short,
            "click" => $row['click'],
            "description" => $description,
            "url" => $url
        );
    }

}

// 获取分类信息
$sql = "SELECT * FROM " . $hbdata->table('category') . " WHERE cat_id = '$cat_id' AND category = '$module'";

$query = $hbdata->query($sql);
$cate_info = $hbdata->fetch_array($query);

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title($module . '_category', $cat_id));
$smarty->assign('keywords', $cate_info['keywords']);
$smarty->assign('description', $cate_info['description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle', '0', $module . '_category', $cat_id, $cate_info['parent_id']));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('ur_here', $hbdata->ur_here($module, $cat_id));
$smarty->assign('cate_info', $cate_info);
$smarty->assign('item_category', $hbdata->get_category('category', 0, $cat_id, $module));
$smarty->assign('item_list', $item_list);
$smarty->assign('cur', $module);
$smarty->assign('item_tree',$_LANG[$module . '_tree']);

$smarty->display('item_category.dwt');

?>