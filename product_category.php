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
$cat_id = $firewall->get_legal_id('product_category', $_REQUEST['id'], $_REQUEST['unique_id']);
if ($cat_id == -1) {
    $hbdata->hbdata_msg($GLOBALS['_LANG']['page_wrong'], ROOT_URL);
} else {
    $where = ' WHERE cat_id IN (' . $cat_id . $hbdata->hbdata_child_id('product_category', $cat_id) . ')';
}

// 获取分页信息
$page = $check->is_number($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
$limit = $hbdata->pager('product', ($_DISPLAY['product'] ? $_DISPLAY['product'] : 10), $page, $hbdata->rewrite_url('product_category', $cat_id), $where);

/**
 * 获取产品列表
 */
$sql = "SELECT id, cat_id, name, price, content, image, add_time, description, show_price FROM " . $hbdata->table('product') . $where . " ORDER BY id DESC" . $limit;
$query = $hbdata->query($sql);

while ($row = $hbdata->fetch_array($query)) {
    $url = $hbdata->rewrite_url('product', $row['id']); // 获取经过伪静态产品链接
    $add_time = date("Y-m-d", $row['add_time']);

    // 如果描述不存在则自动从详细介绍中截取
    $description = $row['description'] ? $row['description'] : $hbdata->hbdata_substr($row['content'], 150, false);

    // 生成缩略图的文件名
    $image = explode(".", $row['image']);
    $thumb = ROOT_URL . $image[0] . "_thumb." . $image[1];

    // 格式化价格
    if($row['show_price'] == true){
        $price = $row['price'] > 0 ? $hbdata->price_format($row['price']) : $_LANG['price_discuss'];
    }
    else{
        $price = "    ";
    }

    $product_list[] = array (
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
}

// 获取分类信息
$sql = "SELECT * FROM " . $hbdata->table('product_category') . " WHERE cat_id = '$cat_id'";
$query = $hbdata->query($sql);
$cate_info = $hbdata->fetch_assoc($query);

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title('product_category', $cat_id));
$smarty->assign('keywords', $cate_info['keywords']);
$smarty->assign('description', $cate_info['description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle', '0', 'product_category', $cat_id, $cate_info['parent_id']));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('ur_here', $hbdata->ur_here('product_category', $cat_id));
$smarty->assign('cate_info', $cate_info);
$smarty->assign('product_category', $hbdata->get_category('product_category', 0, $cat_id));
$smarty->assign('product_list', $product_list);

$smarty->display('product_category.dwt');
?>