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

// 验证并获取合法的ID，如果不合法将其设定为-1
$id = $firewall->get_legal_id('product', $_REQUEST['id'], $_REQUEST['unique_id']);
$cat_id = $hbdata->get_one("SELECT cat_id FROM " . $hbdata->table('product') . " WHERE id = '$id'");
$parent_id = $hbdata->get_one("SELECT parent_id FROM " . $hbdata->table('product_category') . " WHERE cat_id = '" . $cat_id . "'");
if ($id == -1)
    $hbdata->hbdata_msg($GLOBALS['_LANG']['page_wrong'], ROOT_URL);
    
/**
 * 获取产品信息
 */
$query = $hbdata->select($hbdata->table('product'), '*', '`id` = \'' . $id . '\'');
$product = $hbdata->fetch_array($query);

// 格式化数据
$product['price'] = $product['price'] > 0 ? $hbdata->price_format($product['price']) : $_LANG['price_discuss'];
$product['add_time'] = date("Y-m-d", $product['add_time']);

// 生成缩略图的文件名
$image = explode(".", $product['image']);
$product['thumb'] = ROOT_URL . $image[0] . "_thumb." . $image[1];
$product['image'] = ROOT_URL . $product['image'];

// 格式化自定义参数
foreach (explode(',', $product['defined']) as $row) {
    $row = explode('：', str_replace(":", "：", $row));

    if ($row['1']) {
        $defined[] = array (
            "arr" => $row['0'],
            "value" => $row['1']
        );
    }
}

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title('product_category', $cat_id, $product['name']));
$smarty->assign('keywords', $product['keywords']);
$smarty->assign('description', $product['description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle', '0', 'product_category', $cat_id, $parent_id));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('ur_here', $hbdata->ur_here('product_category', $cat_id, $product['name']));
$smarty->assign('product_category', $hbdata->get_category('category', 0, $cat_id, 'product'));
$smarty->assign('product', $product);
$smarty->assign('defined', $defined);

$smarty->display('product.dwt');
?>