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
$cat_id = $firewall->get_legal_id('article_category', $_REQUEST['id'], $_REQUEST['unique_id']);
if ($cat_id == -1) {
    $hbdata->hbdata_msg($GLOBALS['_LANG']['page_wrong'], ROOT_URL);
} else {
    $where = ' WHERE cat_id IN (' . $cat_id . $hbdata->hbdata_child_id('article_category', $cat_id) . ')';
}
    
// 获取分页信息
$page = $check->is_number($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
$limit = $hbdata->pager('article', ($_DISPLAY['article'] ? $_DISPLAY['article'] : 10), $page, $hbdata->rewrite_url('article_category', $cat_id), $where);

/* 获取文章列表 */
$sql = "SELECT id, title, content, image, cat_id, add_time, click, description FROM " . $hbdata->table('article') . $where . " ORDER BY id DESC" . $limit;
$query = $hbdata->query($sql);

while ($row = $hbdata->fetch_array($query)) {
    $url = $hbdata->rewrite_url('article', $row['id']);
    $add_time = date("Y-m-d", $row['add_time']);
    $add_time_short = date("m-d", $row['add_time']);
    $image = $row['image'] ? ROOT_URL . $row['image'] : '';

    // 如果描述不存在则自动从详细介绍中截取
    $description = $row['description'] ? $row['description'] : $hbdata->hbdata_substr($row['content'], 200, false);

    $article_list[] = array (
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

// 获取分类信息
$sql = "SELECT cat_id, cat_name, parent_id FROM " . $hbdata->table('article_category') . " WHERE cat_id = '$cat_id'";
$query = $hbdata->query($sql);
$cate_info = $hbdata->fetch_array($query);

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title('article_category', $cat_id));
$smarty->assign('keywords', $cate_info['keywords']);
$smarty->assign('description', $cate_info['description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle', '0', 'article_category', $cat_id, $cate_info['parent_id']));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('ur_here', $hbdata->ur_here('article_category', $cat_id));
$smarty->assign('cate_info', $cate_info);
$smarty->assign('article_category', $hbdata->get_category('article_category', 0, $cat_id));
$smarty->assign('article_list', $article_list);

$smarty->display('article_category.dwt');
?>