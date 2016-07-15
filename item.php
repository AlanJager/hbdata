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
$id = $firewall->get_legal_id($module, $_REQUEST['id'], $_REQUEST['unique_id']);
$cat_id = $hbdata->get_one("SELECT cat_id FROM " . $hbdata->table($module) . " WHERE id = '$id'");
$parent_id = $hbdata->get_one("SELECT parent_id FROM " . $hbdata->table('category') . " WHERE cat_id = '" . $cat_id . "'");
if ($id == -1)
    $hbdata->hbdata_msg($GLOBALS['_LANG']['page_wrong'], ROOT_URL);

/* 获取详细信息 */
$query = $hbdata->select($hbdata->table($module), '*', '`id` = \'' . $id . '\'');
$item = $hbdata->fetch_array($query);

// 格式化数据
$item['add_time'] = date("Y-m-d", $item['add_time']);

if ($module == 'product') {
    $item['price'] = $item['price'] > 0 ? $hbdata->price_format($item['price']) : $_LANG['price_discuss'];
    $image = explode(".", $item['image']);
    $item['thumb'] = ROOT_URL . $image[0] . "_thumb." . $image[1];
    $item['image'] = ROOT_URL . $item['image'];
}

// 格式化自定义参数
foreach (explode(',', $item['defined']) as $row) {
    $row = explode('：', str_replace(":", "：", $row));

    if ($row['1']) {
        $defined[] = array (
            "arr" => $row['0'],
            "value" => $row['1']
        );
    }
}

// 访问统计
$click = $item['click'] + 1;
$hbdata->query("update " . $hbdata->table($module) . " SET click = '$click' WHERE id = '$id'");

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title($module . '_category', $cat_id, $item['title']));
$smarty->assign('keywords', $item['keywords']);
$smarty->assign('description', $item['description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle', '0', $module . 'category', $cat_id, $parent_id));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('ur_here', $hbdata->ur_here($module, $cat_id, $item['title']));
$smarty->assign('item_category', $hbdata->get_category('category', 0, $cat_id, $module));
$smarty->assign('lift', $hbdata->lift($module, $id, $cat_id));
$smarty->assign('item', $item);
$smarty->assign('defined', $defined);
$smarty->assign('cur', $module);

$smarty->display('item.dwt');


?>