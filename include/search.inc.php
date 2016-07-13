<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: AlanJager
 * Release
 *
 * Date: 2016-7-4
 */
if (!defined('IN_HBDATA')) {
    die('Hacking attempt');
}

//initialize
$module = $check->is_letter($_REQUEST['module']) ? $_REQUEST['module'] : 'product';
switch ($module) {
    case 'product' : //product module
        $name_field = 'name';
        $smarty->assign('keyword', $keyword);
        $search_url = '?s=';
        break;
    default :
        $name_field = 'title';
        $smarty->assign('keyword_article', $keyword);
        $search_url = '?module=article&s=';
}

//filter condition
$where = " WHERE " . $name_field . " LIKE '%$keyword%'";

//get pagers information
$page = $check->is_number($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$limit = $hbdata->pager($module, ($_DISPLAY[$module] ? $_DISPLAY[$module] : 10), $page, ROOT_URL . $search_url . $keyword, $where, '', true);

//get search result set
$sql = "SELECT * FROM " . $hbdata->table($module) . $where . " ORDER BY id DESC" . $limit;
$query = $hbdata->query($sql);

while ($row = $hbdata->fetch_array($query)) {
    $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table('product_category') . " WHERE cat_id = '$row[cat_id]'");
    $url = $hbdata->rewrite_url($module, $row['id']);
    $add_time = date("Y-m-d", $row['add_time']);
    $add_time_short = date("m-d", $row['add_time']);

    $description = $row['description'] ? $row['description'] : $hbdata->hbdata_substr($row['content'], 150);

    //get thumbnail filename
    $image = explode('.', $row['image']);
    $thumb = ROOT_URL . $image[0] . '_thumb.' . $image[1];
    $price = $row['price'] > 0 ? $hbdata->price_format($row['price']) : $_LANG['price_discuss'];

    $search_list[] = array (
        "id" => $row['id'],
        "cat_id" => $row['cat_id'],
        "name" => $row[$name_field],
        "title" => $row[$name_field],
        "price" => $price,
        "thumb" => $thumb,
        "cat_name" => $cat_name,
        "add_time" => $add_time,
        "add_time_short" => $add_time_short,
        "click" => $row['click'],
        "description" => $description,
        "url" => $url
    );
}

$search_results = preg_replace('/d%/Ums', $keyword, $_LANG['search_results']);

//assign value to meta and title
$smarty->assign('page_title', $hbdata->page_title('search', '', $search_results));
$smarty->assign('keywords', $_CFG['site_keywords']);
$smarty->assign('description', $_CFG['site_description']);

//assign value to navigation bar
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle'));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

//assign value to data
$smarty->assign('ur_here', $search_results);
$smarty->assign('search_module', $module);
$smarty->assign('product_category', $hbdata->get_category('category', 0, '', 'product'));
$smarty->assign('article_category', $hbdata->get_category('category', 0, '', 'article'));
$smarty->assign('search_list', $search_list);

$smarty->display('search.dwt');

//end processes except cur one
exit();
