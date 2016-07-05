<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: AlanJager
 * Release Date: 2016-7-4
 */
define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');

// 验证并获取合法的ID，如果不合法将其设定为-1
$module_name = $firewall->get_legal_module_name($_REQUEST['module']);
$id = $firewall->get_legal_id($module_name, $_REQUEST['id'], $_REQUEST['unique_id']);
$cat_id = $hbdata->get_one("SELECT cat_id FROM " . $hbdata->table($module_name) . " WHERE id = '$id'");
$parent_id = $hbdata->get_one("SELECT parent_id FROM " . $hbdata->table($module_name . '_category') . " WHERE cat_id = '" . $cat_id . "'");
if ($id == -1)
    $hbdata->hbdata_msg($GLOBALS['_LANG']['page_wrong'], ROOT_URL);

/**
 * 获取module_detail信息
 */
$query = $hbdata->select($hbdata->table($module_name), '*', '`id` = \'' . $id . '\'');
$module = $hbdata->fetch_array($query);

// 格式化数据
//$product['price'] = $product['price'] > 0 ? $hbdata->price_format($product['price']) : $_LANG['price_discuss'];
$module['add_time'] = date("Y-m-d", $product['add_time']);

// 生成缩略图的文件名
if ($hbdata->field_exist($hbdata->table($module_name), 'image'))
{
    $image = explode(".", $module['image']);
    $product['thumb'] = ROOT_URL . $image[0] . "_thumb." . $image[1];
    $product['image'] = ROOT_URL . $product['image'];
}


// 格式化自定义参数
//foreach (explode(',', $product['defined']) as $row) {
//    $row = explode('：', str_replace(":", "：", $row));
//
//    if ($row['1']) {
//        $defined[] = array (
//            "arr" => $row['0'],
//            "value" => $row['1']
//        );
//    }
//}

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title($module_name . '_category', $cat_id, $module['name']));
$smarty->assign('keywords', $module['keywords']);
$smarty->assign('description', $module['description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle', '0', $module . '_category', $cat_id, $parent_id));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('ur_here', $hbdata->ur_here($module_name . '_category', $cat_id, $module['name']));
$smarty->assign('module_category', $hbdata->get_category($module_name . '_category', 0, $cat_id));
$smarty->assign('module', $module);
$smarty->assign('defined', $defined);

$smarty->display('module.dwt');
?>